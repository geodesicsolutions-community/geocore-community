<?php
//addons/sharing/methods/craigslist.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

//NOTE: for a list of valid HTML that can be used on craigslist, see:
// http://www.craigslist.org/about/help/html_in_craigslist_postings/details

class addon_sharing_method_craigslist {
	
	public $name = 'craigslist';
	
	/**
	 * Gets the name of any methods that want to be used for this listing id.
	 * Note that this function being called in the first place implies that the listing in question is live and belongs to the current user
	 * @param int $listingId
	 * @return String the name of any available method, sans any formatting
	 */
	public function getMethodsForListing($listingId) {
		
		$listing = geoListing::getListing($listingId);
		$currentUser = geoSession::getInstance()->getUserId();
		if($listing->seller != $currentUser && $listing->seller != 1) {
			//only allow sharing to craigslist for the listing's seller and the site admin
			return false;
		}
		
		//return the text for this method's button
		$msgs = geoAddon::getText('geo_addons','sharing');
		return $msgs['method_btn_craigslist'];
			
	}
	
	/**
	 * Gets the full HTML to show in the "options" block of the main addon page.
	 * This function is responsible for any needed templatization to generate that HTML.
	 * @return String HTML
	 */
	public function displayOptions()
	{
		//get attached templates
		$file_templates = require (geoTemplate::getFilePath('main_page','attachments','templates_to_page/addons/sharing/craigslist_output.php'));
		
		foreach ($file_templates[1] as $tpl_id => $data) {
			//figure out name
			$name = $data;
			if (strpos($name, '_lang') !== false) {
				$name = substr($name, 0, strpos($name, '_lang'));
			}
			$name = str_replace('.tpl','',$name);
			
			$available_templates[] = array (
				'id' => $name,		
				'name' => str_replace('_',' ',$name)					 
			);
		}
		
		$tpl = new geoTemplate('addon','sharing');
		if(count($available_templates) == 1) {
			//only one template available
			$tpl->assign('singleTemplate', $available_templates[0]);
			$tpl->assign('templateChoices', false);
		} else {
			$tpl->assign('templateChoices', $available_templates);
		}
		$tpl->assign('msgs', geoAddon::getText('geo_addons','sharing'));
		$html = $tpl->fetch('methods/craigslist_options.tpl');
		return $html;
	}
	
	public function updateOptions()
	{
		$data = $_POST;
		
		$template = $data['selectedTemplate'];
		$template .= '.tpl'; //add the extension back on
		
		$listing = geoListing::getListing($data['listing']);
		
		$images = array();
		$db = DataAccess::getInstance();
		$sql = "SELECT * FROM ".geoTables::images_urls_table." WHERE classified_id = ? ORDER BY `display_order` ASC";
		$result = $db->Execute($sql, array($data['listing']));
		$imageBase = str_replace($db->get_site_setting('classifieds_file_name'), '', $db->get_site_setting('classifieds_url'));
		if($result && $result->RecordCount() > 0) {
			while($image = $result->FetchRow()) {
				$images[] = array(
					//this is split by client request to allow loading images onto other domains
					'base' => ((substr($image['image_url'],0,4) != 'http') ? $imageBase : ''),
					'filename' => $image['image_url']
				); 
			}
		}
		
		$msgs = geoAddon::getText('geo_addons','sharing');
		
		//get the HTML to either show or preview, depending on user's template choice
		//NOTE: this is *just* the main HTML block for craigslist. the "$shell" template below pretties it for display on Geo
		$tpl = new geoTemplate('main_page','');
		$listingArr = $listing->toArray();
		$listingArr['price'] = geoString::displayPrice($listing->price, $listing->precurrency, $listing->postcurrency);
		$tpl->assign('listing', $listingArr);
		$tpl->assign('seller', geoUser::getUser($listing->seller)->toArray());
		$tpl->assign('images', $images);
		$tpl->assign('msgs', $msgs);
		$html = $tpl->fetch($template);
		//trim comments' whitespace from the template output
		$html = trim($html);
		
		//not previewing, so show the HTML in a friendly manner
		$shell = new geoTemplate('addon','sharing');
		$shell->assign('msgs', $msgs);
		$shell->assign('title', geoString::fromDB($listing->title));
		$shell->assign('price', $listing->price);
		$shell->assign('preview', ($data['responseType'] == 'preview') ? true : false);
		$shell->assign('html', ($data['responseType'] == 'preview') ? $html : geoString::specialChars($html));
		$return = $shell->fetch('methods/craigslist_show_html.tpl');
		return $return;
		
	}
}