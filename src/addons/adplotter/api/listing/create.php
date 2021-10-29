<?php
//create.php
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
## ##    7.6.3-12-g806e890
## 
##################################

if (!defined('IN_GEO_API')){
	exit('No access.');
}

//use this API call to create a new listing

//first, let's validate the user's login and that he's allowed to place a new listing
$username = $args['username'];
$password = $args['password'];
if(!geoPC::getInstance()->verify_credentials($username, $password)) {
	return $this->failure('Invalid user credentials', 1, 5);
}
//login is good; get seller data object
$seller = geoUser::getUser($username);


$db = DataAccess::getInstance();
$currentListingCount = $db->GetOne("SELECT count(*) FROM ".geoTables::classifieds_table." WHERE `seller` = ? AND `live` = 1", array($seller->id));

$itemType = (int)$args['item_type'];
if(!in_array($itemType, array(1,2))) {
	return $this->failure('Missing or invalid required data: item_type', 8);
}

$plan_id = ($itemType == 1) ? $seller->price_plan_id : $seller->auction_price_plan_id;
$maxAds = $db->GetOne("SELECT `max_ads_allowed` FROM ".geoTables::price_plans_table." WHERE `price_plan_id` = ?", array($plan_id));

if($currentListingCount+1 > $maxAds) {
	return $this->failure('Too many listings for this user', 2);
}

//if here, we should be good to add a listing, assuming the data is OK

$category = (int)$args['category'];
$title = $args['title'];
$description = $args['description'];
$price = $args['price'];
$images = $args['images'];

$regions = $args['regions'];
if($regions && !is_array($regions)) {
	$regions = array($regions);
}
for($i=0; $i<count($regions); $i++) {
	$id = geoRegion::getRegionIdByBestGuess($regions[$i]);
	if($id) {
		$regions[$i] = $id;
	} else {
		//found nothing to use for this region (likely blank input)
		//completely unset() it so that it doesn't get used later and break things
		unset($regions[$i]);
	}
}

if(!geoCategory::isEnabled($category)) {
	return $this->failure('Missing or invalid required data: category', 7);
}

$title = str_replace(array("\n","\r","\t"),"", trim($title));
$title = geoString::breakLongWords($title, $db->get_site_setting('max_word_width'), " ");
$title = geoFilter::replaceDisallowedHtml($title);
$title = geoFilter::badword($title);
$title = geoString::toDB($title);
if(!$title || strlen($title) < 1) {
	return $this->failure('Missing or invalid required data: title', 3);
}

$description = trim($description);
$description = geoString::breakLongWords($description, $db->get_site_setting('max_word_width'), " ");
$description = geoFilter::replaceDisallowedHtml($description);
$description = geoFilter::badword($description);
$description = geoString::toDB($description);

$price = preg_replace('/[^0-9.]*/','',$price); //remove anything that's not part of a number
if(!$price) {
	$price = 0.00;
}
$price = round($price, 2);

$precurrency = $args['precurrency'] ? $args['precurrency'] : $db->get_site_setting('precurrency');
$precurrency = geoString::toDB($precurrency);
$postcurrency = $args['postcurrency'] ? $args['postcurrency'] : $db->get_site_setting('postcurrency');
$postcurrency = geoString::toDB($postcurrency);

$duration = (int)$args['duration'];
if(!$duration) {
	return $this->failure('Missing or invalid required data: duration', 4);
}
$start = geoUtil::time();
$end = $start + $duration;

$email = $seller->email ? geoString::toDB($seller->email) : '';

$sql = "INSERT INTO ".geoTables::classifieds_table." (seller, title, description, price, precurrency, postcurrency, date, ends, item_type, email) VALUES (?,?,?,?,?,?,?,?,?,?)";
$result = $db->Execute($sql, array($seller->id, $title, $description, $price, $precurrency, $postcurrency, $start, $end, $itemType, $email));
if(!$result) {
	return $this->failure('Database failure adding new listing', 5);
}
$newListingId = $db->Insert_Id();
if(!$newListingId) {
	//this is mostly a sanity check
	return $this->failure('Database failure adding new listing', 5);
}

//add listing to its category now
geoCategory::setListingCategory($newListingId, $category);

//also set up the regions
if($regions) {
	geoRegion::setListingEndRegions($newListingId, $regions);
}

//images should be sent as an array of URLs
if($images && !is_array($images)) {
	//but if only a single URL is sent, try to accommodate that
	$images = array($images);
}
$imgCount = 0;
if($images && count($images) > 0) {
	//there are some images to process
	
	//get image size settings out of the db
	$sql = "SELECT lead_picture_width as thumb_w, lead_picture_height as thumb_h,
	maximum_full_image_width as full_w, maximum_full_image_height as full_h,
	url_image_directory as remote_path, image_upload_path as local_path, photo_quality as quality
	FROM ".geoTables::ad_configuration_table;
	$settings = $db->GetRow($sql);
	
	//This mirrors the "Faster" image upload method from the Bulk Uploader
	//store only the URLs at this run-time, then grab dimensions and hotlink each image from the remote server as it's called for
	foreach($images as $i) {
		if(!trim($i)) {
			//empty filename, skip
			continue;
		}
		$filename = str_replace(' ','%20',$i); //getimagesize doesn't like spaces in filenames
		
		//set up some common data for the image db record.
		//since we're using offsite images, delay getting the width/height until the first time each image is actually shown, by setting to 0 here
		$imageType = '';
		$width = $height = 0;
		$fullData = array('width' => $width, 'height' => $height, 'filepath' => $filename, 'filename' => $filename);
		//not making a thumbnail for this, but fill it with dummy data to make the insert query less messy
		$thumbData = array('width' => $width, 'height' => $height, 'filepath' => '', 'filename' => '');
		
		$sql = "INSERT INTO ".geoTables::images_urls_table." SET classified_id = ?,	
		image_url = ?, full_filename = ?, thumb_url = ?, thumb_filename = ?, file_path = ?,		
		image_width = ?, image_height = ?, original_image_width = ?, original_image_height = ?,
		date_entered = ?, display_order = ?, mime_type = ?, image_text = ?";
			
		$queryData = array(
			$newListingId,
			$fullData['filepath'],$fullData['filename'],$thumbData['filepath'],$thumbData['filename'],$settings['local_path'],
			$thumbData['width'],$thumbData['height'],$fullData['width'],$fullData['height'],
			geoUtil::time(),++$imgCount,$imageType,''
		);
		
		$r = $db->Execute($sql,$queryData);
		if(!$r) {
			return $this->failure("db error: ".$db->ErrorMsg());
		}
	}
	
}

//got through everything, so turn the listing on, add a count of any images, and report success to the caller
$listing = geoListing::getListing($newListingId);
if(!$listing) {
	//this is mostly a sanity check
	return $this->failure('Data error: Failed to reacquire listing data', 10);
}
$listing->live = 1;
$listing->image = count($images) ? count($images) : 0;
$url = $listing->getFullUrl();

//send success email
if ($db->get_site_setting('send_successful_placement_email')) {
	$msgs = geoAddon::getText('geo_addons', 'adplotter');
		
	$tpl = new geoTemplate(geoTemplate::ADDON, 'adplotter');
	
	$baseUrl = geoFilter::getBaseHref();
	$subject = $msgs['listingSuccessMailSubject'].$baseUrl;
	$tpl->assign('salutation', $seller->getSalutation());
	$tpl->assign('msgs', $msgs);
	$tpl->assign('baseURL', $baseUrl);
	$tpl->assign('category', geoCategory::getName($category,true));
	$tpl->assign('listingURL', $listing->getFullUrl());
		
	$message = $tpl->fetch('email_listing_complete.tpl');
	geoEmail::sendMail($seller->email,$subject,$message,0,0,0,'text/html');
}

//pingback to adplotter to let them know the process is complete
$fields = array();
$fields['action_cmd'] = 'success';
$fields['listing_id'] = $newListingId;
$fields['seller'] = $username;
$fields['url'] = str_replace($db->get_site_setting('classifieds_file_name'), '', $db->get_site_setting('classifieds_url'));
$fields['auth'] = sha1($fields['url'] . 'sup3rsALtY@'. $fields['action_cmd']); //do not change the salt phrase without notifying AdPlotter
$notifyUrl = "http://api.adplotter.com/ProcessRawRequest.ashx?Action=GeoCoreAction";
$result = geoPC::urlPostContents($notifyUrl, $fields);



return array('id' => $newListingId, 'url' => $url, 'images' => $images);






