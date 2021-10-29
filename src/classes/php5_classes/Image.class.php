<?php
//Image.class.php
/**
 * This file holds the geoImage class, which is responsible for stuff dealing
 * with images and image processing.
 * 
 * @package System
 * @since Version 4.0.4
 */
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    17.10.0-9-g499da68
## 
##################################

/**
 * This class is responsible for a lot of the image processing, along with 
 * other aspects like generating the image tag for thumbnails.
 * 
 * @package System
 * @since Version 4.0.4
 */
class geoImage
{
	/**
	 * Set this to true to make it e-mail the admin user any time an image is
	 * deleted.
	 * @var bool
	 */
	const debug_image_delete = false;
	
	/**
	 * Instance of the DataAccess object, for easy access by methods in this
	 * class.
	 * @var DataAccess
	 */
	public $db;
	
	/**
	 * The configuration.  Currently coded to be ADO result set in object form,
	 * but will be re-coded to be an array (since $db->GetNextObject()) is known
	 * to be slower than working with arrays)
	 * @var unknown_type
	 */
	public $ad_config;
	
	/**
	 * An instance of geoImage
	 * @var geoImage
	 */
	private static $_instance;
	
	/**
	 * Used to cache for the page load, the image extensions allowed.
	 * 
	 * @var array
	 */
	private $_extensions = array();
	
	
	/**
	 * This is private to force new instantiation to take place in getInstance.
	 * 
	 * It also sets up $this->db.
	 */
	private function __construct()
	{
		$this->db = DataAccess::getInstance();
	}

	/**
	 * gets an instance of geoImage using the Singleton method.
	 *
	 * @return geoImage
	 */
	public static function getInstance ()
	{
		if (!(isset(self::$_instance) && is_object(self::$_instance))){
			$c = __class__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * Set the configuration that will be used when processing images and generating
	 * image tags.
	 * 
	 * @param $ad_config
	 */
	public function setAdConfig ($ad_config)
	{
		$this->ad_config = $ad_config;
	}
	
	/**
	 * Copy the images for a specific listing ID, and returns the images
	 * captured array that can be used to attach those new images to a new
	 * listing.
	 * 
	 * @param int $id The listing ID to copy the images from
	 * @return array The images captured array, to be used to "attach" the new
	 *  images to a new listing.
	 */
	public function copyImages ($id)
	{
		trigger_error('DEBUG CART: Here for image creation.');
		$sql = "SELECT * FROM ".geoTables::images_urls_table." WHERE `classified_id` = ? ORDER BY `display_order` ASC";
				
		$image_urls_result = $this->db->Execute($sql, array($id));
		if(!$image_urls_result){
			trigger_error('ERROR CART SQL: Sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
			return false;
		}
		$images_captured = array();
		while($image_urls = $image_urls_result->FetchRow())
		{
			trigger_error('DEBUG CART: Here for image creation.');
			// Check if it is a URL-referenced or an uploaded image
			if(strpos($image_urls['image_url'], '://') === false)
			{
				//this is NOT a url-referenced image (does not have a protocol, e.g. http:// or https://) 
				//it is stored locally -- create new files and filenames
				
				trigger_error('DEBUG CART: Here for image creation.');
				// Set the file extension
				$current_extension = $this->getExtension($image_urls['mime_type']);
				if (!$current_extension){
					//skip this image
					continue;
				}
				// Uploaded image
				do
				{
					srand((double)microtime()*1000000);
					$filename_root = rand(1000000,9999999);
					$filepath = stripslashes($this->ad_config->IMAGE_UPLOAD_PATH).$filename_root.".".$current_extension;
					
				} while (file_exists($filepath));
				
				copy($image_urls['file_path'].$image_urls['full_filename'], $filepath);
				$full_filename = $filename_root.".".$current_extension;
				$full_url = $this->ad_config->URL_IMAGE_DIRECTORY.$full_filename;
				
				// thumbnail
				if($image_urls['thumb_url'].'' !== '0') {
					trigger_error('DEBUG CART: Using thumb URL because it exists.');
					do {
						srand((double)microtime()*1000000);
						$thumbname_root = rand(1000000,9999999);
						$thumbpath = stripslashes($this->ad_config->IMAGE_UPLOAD_PATH).$thumbname_root.".".$current_extension;
						
					} while (file_exists($thumbpath));
					copy($image_urls['file_path'].$image_urls['thumb_filename'], $thumbpath);
					$thumb_filename = $thumbname_root.".".$current_extension;
					$thumb_url = $this->ad_config->URL_IMAGE_DIRECTORY.$thumb_filename;
				} else {
					//no thumbnail to copy
					trigger_error('DEBUG CART: NOT using image thumbnail, original image thumbnail is 0.');
					$thumb_filename = '0';
					$thumb_url = '0';
				}
				
				$image_urls['full_filename'] = $full_filename;
				$image_urls['image_url'] = $full_url;
				$image_urls['thumb_url'] = $thumb_url;
				$image_urls['thumb_filename'] = $thumb_filename;
				$image_urls['date_entered'] = geoUtil::time();
				$image_urls['file_path'] = stripslashes($this->ad_config->IMAGE_UPLOAD_PATH);
				trigger_error('DEBUG CART: Here for image creation.');
			} else {
				//this is a url-referenced (hotlinked) image (NOT stored locally)
				//probably bulk-uploaded
				
				//don't change the filename, since we can't manipulate the file
				
				//just update the timestamp and go on
				$image_urls['date_entered'] = geoUtil::time();
				trigger_error('DEBUG CART COPY_IMAGES: this image is HOTLINKED -- skipping new image creation and using old url: '.$image_urls['image_url']);
				
				//TODO: fix the bulk uploader so that it imports images instead of hotlinking 
				
			}
			
			$first_part = $second_part = $query_data = array();
			foreach($image_urls as $key => $value)
			{
				if(is_int($key))
					continue;
				elseif($key == "classified_id")
					continue;
				elseif($key == "image_id")
					continue;
				trigger_error('DEBUG CART: Here for image creation.');
				$first_part [] = "`$key`";
				
				$second_part[] = "?";
				$query_data[] = $value;
			}
			$sql = "INSERT INTO ".geoTables::images_urls_table." (".implode(', ',$first_part).") values (".implode(', ',$second_part).")";
			
			$image_insert_result = $this->db->Execute($sql, $query_data);
			if(!$image_insert_result){
				trigger_error('ERROR SQL: Sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
				return false;
			}

			$insert_id = $this->db->Insert_ID();

			// Now add it to the images captured array
			$images_captured[$image_urls['display_order']]['type'] = 1;
			$images_captured[$image_urls['display_order']]['id'] = $insert_id;
			trigger_error('DEBUG CART: Here for image creation, captured: <pre>'.print_r($images_captured[$image_urls['display_order']],1).'</pre>');
		}
		return $images_captured;
	}
	
	/**
	 * Gets an extension given the mime type.  Note that this is currently a
	 * normal "non static" method, but since there is no real reason for it
	 * to be that way since it is not currently working like "an object"
	 * (like the geoListing class does), we may change it to be a static method
	 * in a future version.
	 * 
	 * @param string $mime_type
	 * @return string|null The extension if found, or null if the extension is
	 *  not known for the given mime type.
	 */
	public function getExtension($mime_type){
		trigger_error('DEBUG CART: Here for image creation, mime_type: '.$mime_type);
		if (strlen($mime_type) == 0){
			trigger_error('DEBUG CART: Here for image creation, mime type is bad.');
			return;
		}
		if (isset($this->_extensions[$mime_type])){
			trigger_error('DEBUG CART: Here for image creation, extension of '.$this->_extensions[$mime_type].' for mime type of '.$mime_type.' already retrieved.');
			return $this->_extensions[$mime_type];
		}
		$sql = "SELECT `extension` FROM ".geoTables::file_types_table." WHERE `mime_type` = ? LIMIT 1";
		$extension = $this->db->GetRow($sql, array($mime_type));
		if($extension === false){
			trigger_error('ERROR SQL CART: Sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
			return;
		}
		$this->_extensions[$mime_type] = $extension['extension'];
		// Set the file extension
		trigger_error('DEBUG CART: Here for image creation, extension: <pre>'.print_r($extension,1).'</pre>');
		return ($extension['extension']);
	}
	
	/**
	 * Gets the <image> tag for a thumbnail to be used to display the thumbnail
	 * for a given image.
	 * 
	 * @param int $classified_id
	 * @param int $max_width
	 * @param int $max_height
	 * @param bool $skip_table_data
	 * @param int $affiliate_id
	 * @param string $aff_type Either 'aff' or 'store' depending on if displaying
	 *  image for affiliate page or storefront.
	 * @param bool $force_full_image
	 * @param bool dynamic_dims if true, leave width/height specifications out of the final image tag, in favor of max-width
	 * @return string|bool The image tag to display a thumbnail for the given
	 *  listing, or false if there is a problem getting the data.
	 */
	public static function display_thumbnail ($classified_id,$max_width=0,$max_height=0,$skip_table_data=0,$affiliate_id=0,$aff_type='aff',$force_full_image=0,$dynamic_dims=false)
	{
		$db = DataAccess::getInstance();
		$tpl = new geoTemplate('system', 'classes');
		$tpl_vars = array();
		$messages = $db->get_text(true); 
		//1 passed into function means caller will handle placement -- don't add a TD
		//0 means wrap thumbnail in a <td> tag (caller must handle the rest of the table)
		$tpl_vars['writeTD'] = ($skip_table_data) ? false : true;
		
		$tpl_vars['thumbMaxWidth'] = $db->get_site_setting('thumbnail_max_width');
				
		$alt_data = '';
		if($classified_id) {
			$alt_data = geoString::specialChars(geoListing::getTitle($classified_id));
		}
		
		$link = '';
		if ($affiliate_id){
			switch($aff_type){
				//for extensibility, other affiliate types may be added later as cases here
				case 'aff':
						$link = Singleton::getInstance('geoSite')->configuration_data['affiliate_url'].'?aff='.$affiliate_id.'&amp;a=2&amp;b='.$classified_id;
						break;
				case "store":
					//break intentionally omitted
				default:
					$link = $db->get_site_setting('classifieds_file_name')."?a=ap&amp;addon=storefront&amp;page=home&amp;store=".$affiliate_id."&amp;listing=".$classified_id;
					break;
			}
		} else {
			$link = $db->get_site_setting('classifieds_file_name')."?a=2&amp;b=".$classified_id;
		}
		$tpl_vars['link'] = $link;
		
		if ($db->get_site_setting('popup_image_while_browsing')){
			$tpl_vars['popup'] = true;
		}
		
		$sql = "select * from ".geoTables::images_urls_table." where classified_id = ".(int)$classified_id." order by display_order asc, image_id desc limit 1";
		$image_url_result = $db->Execute($sql);
		if (!$image_url_result) {
			return false;
		} elseif ($image_url_result->RecordCount() == 1) {
			$show_image_url = $image_url_result->FetchNextObject();
			
			if(!$show_image_url->IMAGE_WIDTH || !$show_image_url->IMAGE_HEIGHT || !$show_image_url->MIME_TYPE) {
				//don't have image dimensions -- try to get them!
				$dims = self::getRemoteDims($show_image_url->IMAGE_ID);
				$show_image_url->IMAGE_WIDTH = $dims['width'];
				$show_image_url->IMAGE_HEIGHT = $dims['height'];
				$show_image_url->MIME_TYPE = $dims['mime'];
			}

			// Setup the thumbnail size for below
			if ($max_width != 0) {
				$current_max_width = $max_width;
			} else {
				$current_max_width = $db->get_site_setting('thumbnail_max_width');
			}
			if ($max_height != 0) {
				$current_max_height = $max_height;
			} else {
				$current_max_height = $db->get_site_setting('thumbnail_max_height');
			}
			
			$dims = geoImage::getScaledSize($show_image_url->IMAGE_WIDTH, $show_image_url->IMAGE_HEIGHT, $current_max_width, $current_max_height, true);
			$final_image_width = $dims['width'];
			$final_image_height = $dims['height'];

			if (($db->get_site_setting('photo_or_icon') == 1) || (($final_image_width) && ($final_image_height))) {

				if (strlen($show_image_url->ICON) > 0) {
					$width = $final_image_width;
					$height = $final_image_height;
					$url = geoTemplate::getUrl('',$show_image_url->ICON);
				} elseif (($show_image_url->THUMB_URL) && ($show_image_url->THUMB_URL != "0") && (!$force_full_image)) {
					$width = $final_image_width;
					$height = $final_image_height;
					if (file_exists($show_image_url->FILE_PATH.$show_image_url->THUMB_FILENAME)) {
						$url = $show_image_url->THUMB_URL;
					} else {
						$url = $show_image_url->IMAGE_URL;
					}
				} elseif ($show_image_url->IMAGE_URL) {
					$width = $final_image_width;
					$height = $final_image_height;
					$url = $show_image_url->IMAGE_URL;
				} else {
					//display the photo icon
					$url = geoTemplate::getUrl('',$messages[500796]);
				}
			} else {
				//echo geoTemplate::getUrl('',$messages[500796]);." is the photo icon 1<br />";
				$url = geoTemplate::getUrl('',$messages[500796]);
			}

			$tpl_vars['imageID'] = $show_image_url->IMAGE_ID;
			$tpl_vars['imgTag'] = geoImage::display_image($url, $width, $height,$show_image_url->MIME_TYPE,1,$alt_data,$dynamic_dims);
		}
		$tpl->assign($tpl_vars);
		return $tpl->fetch('Image/display_thumbnail.tpl');
	}
	
	/**
	 * Gets an <image> tag for the given URL and other criteria.
	 * 
	 * @param string $url
	 * @param int $width
	 * @param int $height
	 * @param string $mime_type
	 * @param bool $icon If $mime_type evaluates to false (empty string, null, etc)
	 *  then this isn't used.  If this is true, will use an icon instead of the
	 *  image.
	 * @param string $alt_data Alt string to put into the alt attribute on the
	 *  image tag.
	 * @param bool dynamic_dims if true, leave width/height specifications out of the final image tag, in favor of max-width
	 * @return string|bool The image tag, or false/null if something went wrong.
	 */
	public static function display_image ($url, $width=0, $height=0, $mime_type=0, $icon=0, $alt_data='', $dynamic_dims=false)
	{
		$db = DataAccess::getInstance();
		
		$overload = geoAddon::triggerDisplay('overload_Site_display_image', array ('url'=>$url, 'width' => $width, 'height' => $height,'mime_type' => $mime_type, 'icon' => $icon), geoAddon::OVERLOAD);
		if ($overload !== geoAddon::NO_OVERLOAD) {
			return geoAddon::triggerDisplay('filter_display_image',$overload,geoAddon::FILTER);
		}

		if($alt_data && (strpos($alt_data, "'") !== false || strpos($alt_data, "<") !== false)) {
			/*
			* found an unescaped apostrophe or less-than in the alt text
			* this text gets inserted directly as the alt attribute, so need to make sure it is encoded
			*
			* in some places that call this function, the caller has already done the encode
			* so don't do it here unless we found critically-unencoded characters that will otherwise
			* break things
			*/ 
			$alt_data = geoString::specialChars($alt_data, null, ENT_QUOTES);
		}
		
		if(!$mime_type) {
			//no mime tpe given, so try to detect it
			$image = getimagesize($url);
			$mime_type = $image['mime'];
		}

		if((strpos($mime_type, "image/") === false) && (strlen(trim($mime_type)) > 0)) {
			//not an image
			// Get the icon out of the database
			$sql_query = "select icon_to_use from ".geoTables::file_types_table." WHERE mime_type = ?";
			$result = $db->GetOne($sql_query, array($mime_type));
			if(!$result) {
				return false;
			}

			$iconLocation = geoTemplate::getUrl('',$result);
			$size = getimagesize($iconLocation);
			$tpl = new geoTemplate('system','classes');
			$tpl_vars = array();
			$tpl_vars['src'] = $iconLocation;
			$tpl_vars['width'] = $size[0];
			$tpl_vars['height'] = $size[1];
			$tpl_vars['alt'] = $alt_data;
			$tpl_vars['dynamic_dims'] = $dynamic_dims;
			$tpl_vars['maxHeight'] = $height;
			$tpl->assign($tpl_vars);
			$return = $tpl->fetch('Image/display_image.tpl');

			return geoAddon::triggerDisplay('filter_display_image',$return,geoAddon::FILTER);
			
		} else {
			//this is an image; show it!
			$tpl = new geoTemplate('system','classes');
			$tpl_vars = array();
			$tpl_vars['src'] = $url;
			$tpl_vars['width'] = $width;
			$tpl_vars['height'] = $height;
			$tpl_vars['alt'] = $alt_data;
			$tpl_vars['dynamic_dims'] = $dynamic_dims;
			$tpl_vars['maxHeight'] = $height;
			$tpl->assign($tpl_vars);
			$return = $tpl->fetch('Image/display_image.tpl');

			return geoAddon::triggerDisplay('filter_display_image',$return,geoAddon::FILTER);
		}
	}
	
	/**
	 * This is an alias of {@see geoFile::getMimeType()}, see that method for
	 * more details.  This calls that method passing in false for the 4th param
	 * $inJail.
	 * 
	 * @param string $file The absolute location for the file to detect mime
	 *   type for.
	 * @param string $uploadName The name that is "reported" as the actual filename,
	 *   will be used to look up the mime type according to extension if no other
	 *   methods work to determine the mime type.
	 * @param string $defaultMime The mime type set in file info when uploading file, will
	 *   be used if no other methods work to determine the mime type.
	 * @return string|bool The mime type, or false on failure to detect mime type.
	 * @since Version 4.1.0
	 */
	public static function getMimeType ($file, $uploadName = '', $defaultMime = '')
	{
		return geoFile::getInstance()->getMimeType($file, $uploadName, $defaultMime, false);
	}
	
	/**
	 * When called, returns JSON error and stops rest of page load.
	 * 
	 * @param string $errorMsg
	 * @param string $errField
	 */
	public static function _returnError ($errorMsg, $errField = 'error')
	{
		$errorMsg = "<div style='text-align: left;'>$errorMsg</div>";
		$data = array ($errField => $errorMsg);
		include GEO_BASE_DIR . 'app_bottom.php';
		//echo "keys: ".print_r($_FILES,1)."\n";
		//$data['imagesDisplay'] .=  "Images: <pre>".print_r($imagesCaptured,1)."</pre><br />image title: {$_POST['imageTitle']}";
		
		echo geoAjax::getInstance()->encodeJSON($data);
		exit;
	}
	
	/**
	 * Generate a new filename in the form of 123456789.[ext], making sure file
	 * does not already exist.
	 * 
	 * @param string $uploadPath Location the file will be used
	 * @param string $ext The file's extension
	 * @return string The filename relative to $uploadPath
	 */
	public static function generateFilename($uploadPath, $ext = '.jpg')
	{
		do {
			//keep generating file names until one is found that does not exist yet.
			$try = rand(1000000,9999999);
			$filepath = $uploadPath.$try.$ext;
		} while (file_exists($filepath));
		return $try.$ext;
	}
	
	/**
	 * Shortens the image title by a given max length, cutting off the title on
	 * a word boundry if possible to prevent broken words.
	 * 
	 * @param string $text
	 * @param int $maxLength
	 * @return string
	 * @since Version 4.1.0
	 */
	public static function shortenImageTitle ($text, $maxLength)
	{
		$maxLength = (int)$maxLength;
		if (!$maxLength) {
			//max length 0, don't allow description
			return '';
		}
		$text = trim($text);
		//get rid of extra white-space
		$text = preg_replace('/[\s]+/', ' ', $text);
		
		if (strlen($text) <= $maxLength) {
			//nothing to do
			return $text;
		}
		//strip it down to one place bigger
		$text = substr($text, 0, $maxLength+1);
		//get location of first space
		$spacePosition = strrpos($text, ' ');
		if ($spacePosition) {
			//break it at the space so it's clean
			$text = substr ($text, 0, $spacePosition);
		}
		if (strlen($text) > $maxLength) {
			//must be all one large text, chop it up w/o concern for word boundries
			$text = substr($text, 0, $maxLength);
		}
		return $text;
	}
	/**
	 * Get the scaled width and height of something given the original and max
	 * width and height.
	 * 
	 * @param int $w Original width.
	 * @param int $h Original height
	 * @param int $maxW Max amount for width
	 * @param int $maxH Max size of height
	 * @param bool $scaleUp If true, and the start width and height are both less
	 *   than the max width / height, it will scale the size up. {@since Version 7.2.0}
	 * @return array An array like this: array (width => #, height => #)
	 * @since Version 4.1.0
	 */
	public static function getScaledSize ($w, $h, $maxW, $maxH, $scaleUp = false)
	{
		//sanity checks
		$w = (int)$w;
		$h = (int)$h;
		$maxW = (int)$maxW;
		$maxH = (int)$maxH;
		if (!($w > 0 && $h > 0 && ($maxW > 0 || $maxH > 0))) {
			//can't have any 0 or negative width or heights.
			return array ('width' => $w, 'height' => $h);
		}
		
		if ($scaleUp && $w < $maxW && $h < $maxH) {
			//width and height are both less than the max, and it wants to scale
			//the image up, so do just that...
			
			//first scale up the width
			//w1/h1 = w2/h2 - we have w1 h1 and w2, solf for h2 = h2 = w2*h1/w1 
			$h = ($maxW*$h) / $w;
			$w = $maxW;
			
			//Now at this point, width is going to match max width (scaled up),
			//and height is going to be proportional to that...  It will either
			//be less than or more than the height..  If it's more, then it will
			//get scaled by the normal checks below to scale it down if needed!
		}
		
		//calculate the scalled down image dimensions.  Don't go trying to make
		//this more complicated by adding another 
		//if (endWidth > maxWidth && endHeight > maxHeight) block
		//in there, because you don't need it if you are smart about it,
		//like we are below:
		if ($maxW && $w > $maxW) {
			//Scale down by width, width is more than max allowed.
			//Here's the formula to wrap your head around:
			//w1/h1 = w2/h2 :: (we have w1 h1 and w2, solve for h2) :: h2 = w2*h1/w1
			$h = ($maxW * $h) / $w;
			$w = $maxW;
		}
		if ($maxH && $h > $maxH) {
			//AND/OR scale down by height, height is more than max allowed.
			//Here's the formula to wrap your head around:
			//w1/h1 = w2/h2 :: (we have w1 h1 and h2, solve for w2) :: h2*w1/h1 = w2
			$w = ($maxH * $w) / $h;
			$h = $maxH;
		}
		//NOTE: We do ceil() on values at very end only to avoid over-rounding things
		//along the way
		return array ('width'=> ceil($w), 'height' => ceil($h));
	}
	
	/**
	 * Resize an image given the max width and height, and send back some info
	 * on the created image like the new width and height, and image resource.
	 * 
	 * @param string $filename
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param bool $alwaysResize If false, and current width and height are less
	 *  than the given max width and height, will return false.
	 * @return array|bool An array in the form array('image'=>$imageResource, 'width'=>int, 'height'=>int)
	 */
	public static function resize ($filename, $maxWidth, $maxHeight, $alwaysResize = true)
	{
		$db = DataAccess::getInstance();
		$image_dimensions = getimagesize($filename);
		if (!$image_dimensions) {
			//internal error could not process your image
			//diagnostics will have to be done outside this method.
			return false;
		}
		$startWidth = $endWidth = $image_dimensions[0];
		$startHeight = $endHeight = $image_dimensions[1];
		$type = $image_dimensions[2];
		
		$scaled = self::getScaledSize($startWidth, $startHeight, $maxWidth, $maxHeight);
		$endWidth = $scaled['width'];
		$endHeight = $scaled['height'];
		
		if (!$alwaysResize && $startWidth == $endWidth && $startHeight == $endHeight) {
			//don't re-size, this one is small
			return false;
		}
		
		//create re-sized file
		$copied = 0;
		//self::_returnError('image dimensions: <pre>'.print_r($image_dimensions,1).'</pre>');
		$extension = $imgCreateFunc = null;
		switch ($type) {
			case IMAGETYPE_GIF: 
				//gif image
				$extension = ".gif";
				$imgCreateFunc = 'imagecreatefromgif';
				break;
				
			case IMAGETYPE_JPEG:
				//jpg image
				$extension = '.jpg';
				$imgCreateFunc = 'imagecreatefromjpeg';
				break;
				
			case IMAGETYPE_PNG:
				//png image
				$extension = '.png';
				$imgCreateFunc = 'imagecreatefrompng';
				break;
				
			case IMAGETYPE_BMP:
				//bmp image, nothing can be done for this one...
				$extension = '.bmp';
				break;
				
			default:
				//not a type we can resize, still try to figure out extension
				$sql = "SELECT `extension` FROM ".geoTables::file_types_table." WHERE `mime_type` LIKE '{$image_dimensions['mime']}' AND `accept` = 1";
				$row = $db->GetRow($sql);
				if ($row && $row['extension']) {
					$extension = ".{$row['extension']}";
				}
				break;
		}
		
		if ($imgCreateFunc && function_exists($imgCreateFunc)) {
			$srcImage = $imgCreateFunc($filename);
			if (!$srcImage) {
				//TODO: problem
				return false;
			}
			$destImage = false;
			$oldschool = $db->get_site_setting('imagecreatetruecolor_switch');
			if (function_exists("imagecreatetruecolor") && !$oldschool) {
				//not a gif, imagecreatetruecolor works, and switch is not set to old school
				$destImage = imagecreatetruecolor($endWidth, $endHeight);
				if (function_exists('imagecolorallocate') && function_exists('imagefill')) {
					//make it white background by default in case image has transparancy
					imagefill($destImage,0,0,imagecolorallocate($destImage,255,255,255));
				}
			}
			if (!$destImage) {
				//attempt to use old school imagecreate
				$destImage = imagecreate($endWidth,$endHeight);
			}
			if ($srcImage && $destImage) {
				if (!$oldschool) {
					//preferred method, resample
					$copied = imagecopyresampled($destImage,$srcImage,0,0,0,0,$endWidth,$endHeight,$startWidth,$startHeight);
				} else {
					//dumbed down method, see if old resize works
					$copied = imagecopyresized($destImage,$srcImage,0,0,0,0,$endWidth,$endHeight,$startWidth,$startHeight);
				}
				//don't need srcimage anymore.
				imagedestroy($srcImage);
			}
		}
		if ($copied && $destImage) {
			return array (
				'image' => $destImage,
				'width' => $endWidth,
				'height' => $endHeight
			);
		}
		return false;
	}
	
	/**
	 * Sometimes 2 images can end up in the same "slot" (display_order value)
	 * for a given listing.  This method fixes that by removing
	 * the older image where there is more than one image in a "slot" for a listing.
	 * 
	 * @param int $listingId The listing ID to check.
	 * @since Version 4.0.9
	 */
	public static function fixDuplicates ($listingId)
	{
		$listingId = (int)$listingId;
		
		if (!$listingId) {
			//invalid id
			return false;
		}
		
		$db = DataAccess::getInstance();
		
		//get all the images attached
		$sql = "SELECT `image_id`, `display_order` FROM ".geoTables::images_urls_table." WHERE `classified_id` = ? ORDER BY `display_order`, `image_id`";
		$images = $db->GetAll($sql, array($listingId));
		if (!$images) {
			//sql error or no images found
			return false;
		}
		$found = array();
		foreach ($images as $image) {
			if (isset($found[$image['display_order']])) {
				//duplicate found.
				$first = $found[$image['display_order']];
				//see which one is newer, and keep that one
				$removeId = ($first['image_id'] > $image['image_id'])? $image['image_id'] : $first['image_id'];
				//remove the image
				self::remove($removeId);
				
				//update which one is set
				if ($image['image_id'] == $removeId) $image = $first;
			}
			$found[$image['display_order']] = $image;
		}
	}
	
	/**
	 * Removes an image specified by the image ID in the images table, from the
	 * file system and removes the record from the database.
	 * @param int $imageId
	 * @return bool true if successful, false otherwise
	 * @since Version 4.0.9
	 */
	public function remove ($imageId)
	{
		$imageId = (int)$imageId;
		if (!$imageId) {
			//invalid ID
			return false;
		}
		geoAddon::triggerUpdate('notify_image_remove', $imageId);
		$db = DataAccess::getInstance();
		$sql = "SELECT * FROM ".geoTables::images_urls_table." WHERE `image_id`=?";
		$imgData = $db->GetRow($sql, array($imageId));
		if (!$imgData) {
			//either sql error or no image found
			return false;
		}
		if (self::debug_image_delete) {
			$email_subject = "deleting image $imageId - ".$imgData['thumb_filename']." and ".$imgData['full_filename'];
			$email_message = "directory path to images being deleted: ".$imgData["file_path"]."\n\n";
			$email_message .= "deleting full size image file: ".$imgData['full_filename']."\n\n";
			$email_message .= "deleting thumb size image file: ".$imgData['thumb_filename']."\n\n";
			geoEmail::sendMail($db->get_site_setting('site_email'),$email_subject,$email_message);
		}
		if ($imgData['full_filename']) {
			unlink($imgData['file_path'].$imgData['full_filename']);
		}
		if ($imgData['thumb_filename']) {
			unlink($imgData['file_path'].$imgData['thumb_filename']);
		}
		$sql = "DELETE FROM ".geoTables::images_urls_table." WHERE `image_id` = ?";
		$result = $db->Execute($sql, array($imageId));
		
		if (!$result) {
			//$cart->site->body .=$sql."<br />\n";
			return false;
		}
		return true;
	}
	
	/**
	 * Transforms a local image path into its full URL
	 * e.g. "user_images/myImage.jpg" => "http://mysite.com/user_images/myImage.jpg"
	 * @param String $url
	 * @return String the full URL
	 */
	public static function absoluteUrl($url)
	{
		if(!$url) {
			return '';
		}
		
		$base = geoFilter::getBaseHref();
		$url = (strpos($url,'http') === 0) ? $url : $base.$url;
		
		return $url;
	}
	
	/**
	 * Gets the dimensions of an image for which they're not already saved in the database.
	 * Most likely, such an image was Bulk Uploaded or added by a similar addon that skips processing in the name of speed
	 * @param int $image_id
	 * @return Array containing width/height/mime of selected image, or boolean false on failure.
	 */
	public static function getRemoteDims($image_id)
	{
		if(!$image_id) {
			//nothing to get!
			return false;
		}
		
		$db = DataAccess::getInstance();
		$url = $db->GetOne("SELECT `image_url` FROM ".geoTables::images_urls_table." WHERE `image_id` = ?", array($image_id));
		if(!$url) {
			//couldn't get image
			return false;
		}
		$data = getimagesize($url);
		if(!$data) {
			//not a valid remote image, or allow_url_fopen not on
			//if image is on https, be sure server is compiled with OpenSSL
			return false;
		}
		$update = "UPDATE ".geoTables::images_urls_table." SET `image_width` = ?, `image_height` = ?, `original_image_width` = ?, `original_image_height` = ?, `mime_type` = ? WHERE `image_id` = ?";
		$result = $db->Execute($update, array($data[0], $data[1], $data[0], $data[1], $data['mime'], $image_id));
		if(!$result) {
			return false;
		}
		
		//return results, as well
		$return = array(
				'width' => $data[0],
				'height' => $data[1],
				'mime' => $data['mime']
				);
		return $return;
	}
}