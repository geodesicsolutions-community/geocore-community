<?php
//addons/bulk_uploader/tokenizer.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
## 
##################################

/**
 * When a remote image URL is used in the Bulk Uploader, this class copies the image to a local folder and saves a "token" linking the remote URL to its local copy.
 *
 * In this way, if the same remote image is used in a future upload (common when using Revolving Inventory), the system can identify the corresponding local resource
 *  and use that instead of incurring the time and bandwidth costs to download the image anew.abstract
 *  
 *  Advanced users can use generate_tokens.php to create tokens for a CSV file prior to attempting to upload it through the main uploader 
 */

class geoBulkUploaderImageTokenizer
{	
	private static $_stats;
	/**
	 * Gets a local token for a remote image URL, or creates one if it doesn't already exist
	 * @param String $remoteUrl
	 * @param boolean $extantOnly if true, function only returns a result if a local token exists prior to execution, and returns boolean false otherwise
	 * @return String|boolean image path for use in bulk uploading, or false. See also the $extantOnly parameter
	 */
	public static function getToken($remoteUrl, $extantOnly=false)
	{
		if(!self::_doWriteCheck()) {
			return false;
		}
		
		$db = DataAccess::getInstance();
		//if this URL has already been remotely tokenized, return its local url
		$local = $db->GetOne('SELECT `local_path` FROM `geodesic_addon_bulk_uploader_image_tokens` WHERE `remote_url` = ?', array($remoteUrl));
		if($local) {
			if(is_readable($local)) {
				//everything looks good -- return the path to the local image
				self::$_stats['extant']++;
				if($extantOnly) {
					//what we're really looking for here is a fully-qualified URL instead of a local path
					$url = str_replace(GEO_BASE_DIR, geoFilter::getBaseHref(), $local);
					return $url;
				} else {
					return $local;
				}
			} elseif($extantOnly) {
				//only interested in tokens that already exist
				return false;
			} else {
				//found an entry in the db, but the actual file isn't readable
				//nuke the db entry and let it try to recreate everything
				self::$_stats['recreate']++;
				$db->Execute("DELETE FROM `geodesic_addon_bulk_uploader_image_tokens` WHERE `local_path` = ?", array($local));
			}
		} elseif($extantOnly) {
			//only interested in tokens that already exist
			return false;
		}
		
		//if not, download it and save a token
		$tokenDir = ADDON_DIR.'bulk_uploader/tokens/';		
		$local = $tokenDir.geoImage::generateFilename($tokenDir);
		$settings = self::_getOldSettings();
		
		$imageResource = geoImage::resize($remoteUrl, $settings['full_w'], $settings['full_h']);
		if(!$imageResource) {
			//could not download this image, or it is something other than an image
			self::$_stats['not_image']++;
			return false;
		}
		$writeSuccess = imagejpeg($imageResource['image'], $local, $settings['quality']);
		imagedestroy($imageResource); //get rid of the resource to save memory, since we're likely doing this several times
		if($writeSuccess) {
			//created local image OK
			//save to DB and return path to local copy
			$db->Execute('INSERT INTO `geodesic_addon_bulk_uploader_image_tokens` (`remote_url`,`local_path`) VALUES (?,?)', array($remoteUrl, $local));
			self::$_stats['new']++;
			return $local;
		} else {
			//something didn't work right -- spit back the original URL and let it use that
			self::$_stats['write_fail']++;
			return $remoteUrl;
		}
	}
	
	public static function getStats()
	{
		return self::$_stats;
	}
	
	private static $_didWriteCheck = false;
	private static $_isWritable;
	private static function _doWriteCheck()
	{
		if(self::$_didWriteCheck) {
			return self::$_isWritable;
		}
		
		
		$tokenDir = ADDON_DIR.'bulk_uploader/tokens/';
		if(!is_writable($tokenDir)) {
			geoAdmin::m($tokenDir.' is not writable. Attempting to CHMOD 777.', geoAdmin::NOTICE);
			chmod($tokenDir, '0777');
			if(is_writable($tokenDir)) {
				geoAdmin::m('CHMOD Success. Continuing.', geoAdmin::NOTICE);
				self::$_isWritable = true;
			} else {
				geoAdmin::m('CHMOD Failed. You will need to manually write-enable (CHMOD 777) this directory: '.$tokenDir, geoAdmin::NOTICE);
				self::$_isWritable = false;
			}
		} else {
			self::$_isWritable = true;
		}
		self::$_didWriteCheck = true;
		return self::$_isWritable;
		
	}
	
	private static function _getOldSettings() 
	{
		//get image settings out of the super old-school legacy table
		$sql = "SELECT lead_picture_width as thumb_w, lead_picture_height as thumb_h,
				maximum_full_image_width as full_w, maximum_full_image_height as full_h,
				url_image_directory as remote_path, image_upload_path as local_path, photo_quality as quality
				FROM ".geoTables::ad_configuration_table;
		return DataAccess::getInstance()->GetRow($sql);
	}
}