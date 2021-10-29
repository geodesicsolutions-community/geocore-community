<?php

//get_image.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.05.0-10-g42beced
##
##################################


require_once 'app_top.main.php';

if (!isset($_REQUEST["id"]) || !(int)$_REQUEST['id']) {
    //invalid or no image specified
    require GEO_BASE_DIR . 'app_bottom.php';
    exit;
}

$sql = "SELECT maximum_full_image_height, maximum_full_image_width
	FROM geodesic_classifieds_ad_configuration";
$ad_configuration = $db->GetRow($sql);

if ($ad_configuration === false) {
    //failsave, shouldn't get here..
    echo $sql . "<bR>\n";
    require GEO_BASE_DIR . 'app_bottom.php';
    exit;
}
if (!$ad_configuration) {
    //failsafe, shouldn't get here either...
    echo 'DB Error, no settings.';
    require GEO_BASE_DIR . 'app_bottom.php';
    exit;
}

$sql = "SELECT * FROM " . geoTables::images_urls_table . " WHERE `image_id` = ?";

$show_image = $db->GetRow($sql, array((int)$_REQUEST["id"]));

if (!$show_image || strlen(trim($show_image['image_url'])) == 0) {
    //invalid image requested, just show a blank page.
    //echo "DB Error, invalid image.  Error # ".__line__;
    require GEO_BASE_DIR . 'app_bottom.php';
    exit;
}

if ($debug_popup) {
    echo $image_width . " is image width before<br>\n";
    echo $image_height . " is image height before<br>\n";
    echo $ad_configuration["maximum_full_image_width"] . " is MAXIMUM_FULL_IMAGE_WIDTH<br>\n";
    echo $ad_configuration["maximum_full_image_height"] . " is MAXIMUM_FULL_IMAGE_HEIGHT<br>\n";
    echo $show_image["original_image_width"] . " is ORIGINAL_IMAGE_WIDTH<br>\n";
}

//get the prev and next text, which happens to be on page 157
$messages = $db->get_text(true, 157);

$tpl = new geoTemplate('system', 'other');
$tpl_vars = array();

if (!$show_image['original_image_width'] || !$show_image['original_image_height']) {
    $remoteDims = geoImage::getRemoteDims($show_image['image_id']);
    $show_image['original_image_width'] = $remoteDims['width'];
    $show_image['original_image_height'] = $remoteDims['height'];
}

$dimensions = geoImage::getScaledSize($show_image["original_image_width"], $show_image["original_image_height"], $ad_configuration["maximum_full_image_width"], $ad_configuration["maximum_full_image_height"]);
$image_width = $dimensions['width'];
$image_height = $dimensions['height'];
$tpl_vars['url'] = $show_image['image_url'];
if ($show_image['icon']) {
    $show_image["image_url"] = geoTemplate::getUrl('', $show_image['icon']);
    $tpl_vars['is_icon'] = true;
}

$tpl_vars['display_image'] = geoImage::display_image($show_image["image_url"], 0, $image_height, $show_image['mime_type'], 0, '', true);
$tpl_vars['display_image_text'] = $show_image['image_text'];
$tpl_vars['imageId'] = (int)$show_image['image_id'];//give it something unique for ID's

//in case template needs to know
$tpl_vars['maxWidth'] = $ad_configuration['maximum_full_image_width'];
$tpl_vars['maxHeight'] = $ad_configuration['maximum_full_image_height'];

//Get all images, we'll need this for accurate image #
if ($show_image['classified_id']) {
    $sql = "SELECT * FROM `geodesic_classifieds_images_urls` WHERE
			`classified_id` = ?
			ORDER BY `display_order` ASC";
    $allImages = $db->GetAll($sql, array($show_image['classified_id']));
} else {
    //can't get all associated images if no listing ID
    $allImages = array($show_image);
}
$tpl_vars['imageCount'] = count($allImages);

$prev = $thisPrev = $next = $thisCount = $imageNum = 0;
foreach ($allImages as $thisImage) {
    $thisCount++;
    if ($thisImage['image_id'] == $show_image['image_id']) {
        //this is the one
        $prev = $thisPrev;
        $imageNum = $thisCount;
    } elseif ($imageNum) {
        //this is the "next" image
        $next = $thisImage['image_id'];
        break;
    }
    $thisPrev = $thisImage['image_id'];
}
$tpl_vars['previous_image_id'] = $prev;
$tpl_vars['next_image_id'] = $next;
$tpl_vars['imageNum'] = $imageNum;
$tpl_vars['imageData'] = $show_image;
if ($prev || $next) {
    $tpl_vars['playing'] = $playing = (!isset($_GET['play']) || (isset($_GET['play']) && $_GET['play'])) ? true : false;
    $tpl_vars['paused'] = (!$playing);
    if (!$next) {
        //get the first image ID

        $tpl_vars['first_image_id'] = (int)$allImages[0]['image_id'];
    } else {
        //no use to get the first image ID
        $tpl_vars['first_image_id'] = 0;
    }
} else {
    //can neither pause or play, there is no more things to go through
    $tpl_vars['playing'] = $tpl_vars['paused'] = false;
    $tpl_vars['first_image_id'] = 0;
    $tpl_vars['imageCount'] = 1;
}
$tpl_vars['useSlideshow'] = $db->get_site_setting('useSlideshow');
$tpl_vars['startSlideshow'] = $db->get_site_setting('startSlideshow');

$tpl->setLanguage($db->getLanguage());
$tpl->assign($tpl_vars);

$tpl->display('lightbox_slideshow.tpl');


require GEO_BASE_DIR . 'app_bottom.php';
exit;
