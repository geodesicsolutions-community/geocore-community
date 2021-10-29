<?php
//ListingDisplay.class.php
/**
 * Holds the geoListingDisplay object.
 * 
 * @package System
 * @since Version 7.1.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    17.07.0-17-gc2a3d8e
## 
##################################

/**
 * This is basically a container that is responsible for helping to display {listing ...} tags.
 * 
 * This class is not meant to be used outside of the template system.
 * 
 * @package System
 * @since Version 7.1.0
 */
class geoListingDisplay
{
	/**
	 * Session vars
	 * @var array
	 */
	private static $_session_vars = array();
	/**
	 * config settings stored internally so we only have to get once
	 * @var array
	 */
	private static $_configuration_data = array();
	/**
	 * listing config settings stored internally so we only have to get once
	 * @var array
	 */
	private static $_ad_configuration_data = array();
	
	/**
	 * A list of all "listing tags", each one links to page showing listings that have that tag.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function listing_tags_links (geoListing $listing, $params, $smarty)
	{
		//Share common code with other tag that shows listing tags
		return self::_listing_tags($listing, 'listing_tags_links.tpl', $params, $smarty);
	}
	
	/**
	 * A list of all "listing tags", without links.  This is suitable for use in META tags if desired.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function listing_tags_list (geoListing $listing, $params, $smarty)
	{
		//Share common code with other tag that shows listing tags
		return self::_listing_tags($listing, 'listing_tags_list.tpl', $params, $smarty);
	}
	
	/**
	 * Shows the \"Verified\" icon if the seller uses a verified account.
	 * 
	 * @category general,seller
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function verified_account (geoListing $listing, $params, $smarty)
	{
		if (!$listing->seller || !geoUser::isVerified($listing->seller)) {
			return '';
		}
		$msgs = DataAccess::getInstance()->get_text(true, 59);
		$tpl_vars = array('link'=>$msgs[500957]);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'verified_account.tpl', geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Shows the \"Verified\" icon if the seller uses a verified account.
	 *
	 * @category general,seller
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function image_block (geoListing $listing, $params, $smarty)
	{
		$db = DataAccess::getInstance();
		$tpl_vars = array();
		$ad_configuration_data = self::_initAdConfig();
		
		$images = geoListing::getImages($listing->id);
		if (!$images) {
			//no images to show
			return '';
		}
		
		$image_count = count($images);
		
		//if we don't have enough images to fill up all the possible columns
		$tpl_vars['columns'] = $columns = ($image_count < $ad_configuration_data['photo_columns']) ? $image_count : $ad_configuration_data['photo_columns'];
		$tpl_vars['width'] = $width = floor(100/$columns);
		$tpl_vars['width_percentage'] = $width . '%';
		$tpl_vars['ad_configuration_data'] = $ad_configuration_data;
		
		$tpl_vars['image_link_destination_type'] = $db->get_site_setting('image_link_destination_type');
		
		
		$galleryStyle = $params['gallery_style'] ? $params['gallery_style'] : trim($db->get_site_setting('gallery_style'));
		
		//make sure it is set to something good...
		if(!in_array($galleryStyle, array('classic','gallery','gallery2','filmstrip','photoswipe'))) {
			$galleryStyle = 'photoswipe';
		}
		
		if($galleryStyle === 'photoswipe' && !defined('GEO_PHOTOSWIPE_LOADED')) {
			//photoswipe files aren't preloaded, so can't use it. probably trying to do this somewhere other than main listing display page
			//use filmstrip instead
			$galleryStyle = 'filmstrip';
			trigger_error('DEBUG IMAGE: cannot use photoswipe iamge block here; falling back on filmstrip');
		}
		
		$tpl_vars['gallery_style'] = $galleryStyle;
		
		//gallery or filmstrip views... both are similar to each other as far
		//as tpl vars needed
		
		$tpl_vars['images'] = $images;
		$tpl_vars['image_count'] = count($images);
		$dimensions = array(
			'max_width' => (($ad_configuration_data['maximum_image_width'])? $ad_configuration_data['maximum_image_width']:250),
			'max_height' => (($ad_configuration_data['maximum_image_height'])? $ad_configuration_data['maximum_image_height']:250),
			'max_thumb_width' => (($db->get_site_setting('maximum_thumb_width'))?$db->get_site_setting('maximum_thumb_width'):75),
			'max_thumb_height' => (($db->get_site_setting('maximum_thumb_height'))?$db->get_site_setting('maximum_thumb_height'):75),
			'max_full_width' => $ad_configuration_data['maximum_full_image_width'],
			'max_full_height' => $ad_configuration_data['maximum_full_image_height'],
			'max_gallery_main_width' => (($db->get_site_setting('gallery_main_width'))?$db->get_site_setting('gallery_main_width'):500),
			'max_gallery_main_height' => (($db->get_site_setting('gallery_main_height'))?$db->get_site_setting('gallery_main_height'):500),
		);
		$tpl_vars['dimensions'] = $dimensions;
		
		//need text from listing details page
		$tpl_vars['messages'] = $db->get_text(true, 1);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, "image_block/index.tpl", geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * The full-sized images block for this listing.  Designed to be used in 
	 * combination with the {$image_block_large_link} tag that you would place 
	 * near the top of the template, then put this tag near the bottom.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function image_block_large (geoListing $listing, $params, $smarty)
	{
		$images = geoListing::getImages($listing->id);
		if (!$images) {
			//no images to show
			return '';
		}
		$ad_configuration_data = self::_initAdConfig();
		$tpl_vars = array();
		$tpl_vars['images'] = $images;
		$tpl_vars['listing_id'] = $listing->id;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, "image_block_large.tpl", geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This will display a link that looks similar to "See all ## Photos" where 
	 * ## is automatically replaced with the number of images for the listing.  
	 * This is designed to be used right below the lead picture, then use the 
	 * large photo block lower in the template somewhere.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function image_block_large_link (geoListing $listing, $params, $smarty)
	{
		//This must be used along side image_block_large so we already know we
		//need to get image data... and it does cache image data once it is retrieved,
		//so just get image data
		
		$images = geoListing::getImages($listing->id);
		if (!count($images)) {
			return '';
		}
		
		$tpl_vars = array();
		
		$tpl_vars['imageCount'] = count($images);
		$tpl_vars['listing_id'] = $listing->id;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, "image_block_large_link.tpl", geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Shows the lead picture in slot 1 for listing, if there is one
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function lead_picture (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();
		$images = geoListing::getImages($listing->id);
		
		if (!count($images) || !isset($images[1])) {
			return '';
		}
		$ad_configuration_data = self::_initAdConfig();
		if (!$ad_configuration_data['lead_picture_width'] || !$ad_configuration_data['lead_picture_height']) {
			//don't use if size for lead pic is set to 0
			return '';
		}
		$tpl_vars['image'] = $images[1];
		
		
		return geoTemplate::loadInternalTemplate($params, $smarty, "lead_picture.tpl", geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Links to url link 1 on listing
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function url_link_1 (geoListing $listing, $params, $smarty)
	{
		return self::_url_links($listing, 1, $params, $smarty);
	}
	
	/**
	 * Links to url link 2 on listing
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function url_link_2 (geoListing $listing, $params, $smarty)
	{
		return self::_url_links($listing, 2, $params, $smarty);
	}
	
	/**
	 * Links to url link 1 on listing
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function url_link_3 (geoListing $listing, $params, $smarty)
	{
		return self::_url_links($listing, 3, $params, $smarty);
	}
	
	/**
	 * Shows the offsite videos
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function offsite_videos_block (geoListing $listing, $params, $smarty)
	{
		//offsite video data
		if (!$listing->e_offsite_videos) {
			$listing->e_offsite_videos = DataAccess::getInstance()->GetAll("SELECT * FROM ".geoTables::offsite_videos." WHERE `listing_id`={$listing->id} ORDER BY `slot`");
		}
		$offsite_videos = $listing->e_offsite_videos;
		
		if (!$offsite_videos) {
			//no offsite videos for this listing
			return '';
		}
		$tpl_vars = array ();
		$tpl_vars['offsite_videos'] = $offsite_videos;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'offsite_videos_block.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link allowing the client to send a notification to a friend about the current listing.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function notify_friend_link (geoListing $listing, $params, $smarty)
	{
		if (!$listing->live) {
			//don't show notify friend link
			return '';
		}
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
			);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'notify_friend_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link allowing the client to send a message to the seller of the listing.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function message_to_seller_link (geoListing $listing, $params, $smarty)
	{
		if (!$listing->email) {
			//don't show message to seller link
			return '';
		}
		if ($listing->show_contact_seller === 'no') {
			//no showing contact seller link
			return '';
		}
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'message_to_seller_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link text allowing the user to add the current listing to their favorites list in their user home page.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function favorites_link (geoListing $listing, $params, $smarty)
	{
		if (!$listing->live) {
			//don't show fav link if not live
			return '';
		}
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'favorites_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link text allowing the user to view the current sellers other listings.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function sellers_other_ads_link (geoListing $listing, $params, $smarty)
	{
		if (self::_isAnon($listing)) {
			//don't show fav link if anon placed
			return '';
		}
		if ($listing->show_other_ads=='no') {
			//set to not show seller's other ads link
			return '';
		}
		$tpl_vars = array (
			'seller' => $listing->seller,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'sellers_other_ads_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link text allowing the user to view all images at their full size on the same page.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function full_images_link (geoListing $listing, $params, $smarty)
	{
		$images = geoListing::getImages($listing->id);
		
		if (!count($images)) {
			//only show link if listing has images on it
			return '';
		}
		unset($images);
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'full_images_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link text allowing the user to view the listing details page in a page that is more print friendly.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function print_friendly_link (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'print_friendly_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link to view image slideshow for the listing, starting from image 1.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function image_slideshow_link (geoListing $listing, $params, $smarty)
	{
		$images = geoListing::getImages($listing->id);
		
		if (!$images) {
			//no images!
			return '';
		}
		
		$image_id = 0;
		foreach ($images as $image) {
			if (!strlen($image['icon']) && $image['id']) {
				//found which one to start from!
				$image_id = $image['id'];
				break;
			}
		}
		if (!$image_id) {
			//no suitable image found, perhaps all icons...  don't show link
			return '';
		}
		
		$tpl_vars = array (
			'image_id' => $image_id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'image_slideshow_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is where the sponsored by html is placed within sellers listings where that sellers group has "sponsered by" html has been placed.  If none of your groups use the sponsored by html fields this tag does not need to be placed within the listing display template.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function sponsored_by (geoListing $listing, $params, $smarty)
	{
		if (!$listing->seller) {
			//no seller set
			return '';
		}
		$seller = geoUser::getUser($listing->seller);
		
		if (!$seller) {
			//problem getting info about seller
			return '';
		}
		$sql = "SELECT `sponsored_by_code` FROM ".geoTables::groups_table." WHERE `group_id` = ".$seller->group_id;
		
		$sponsored_by_code = geoString::fromDB(DataAccess::getInstance()->GetOne($sql));
		if (!strlen(trim($sponsored_by_code))) {
			//sponsored by code not set for user group
			return '';
		}
		
		$tpl_vars = array (
			'sponsored_by_code' => $sponsored_by_code,
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'sponsored_by.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Link that will display in the listing allowing the user to see the previous listing within the category.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function previous_ad_link (geoListing $listing, $params, $smarty)
	{
		$view = geoView::getInstance();
		if($view->previous_ad_link) {
			//if view var has already been set for this, it's probably storefront trying to override the normal functionality
			return $view->previous_ad_link; 
		}
		
		//figure out the previous listing when sorting "standard" in the same category
		//as the listing
		$db = DataAccess::getInstance();
		
		$query = $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
		
		$cTable = geoTables::classifieds_table;
		$lcTable = geoTables::listing_categories;
		
		$cat_subquery = "SELECT * FROM $lcTable WHERE $lcTable.`listing`=$cTable.`id`
			AND $lcTable.`category`={$listing->category}";
		
		$query->where("EXISTS ($cat_subquery)", 'category');
		 
		$query->where(geoTables::classifieds_table.".`live`=1",'live')
			//set it to get one just before the current listing...
			->where(geoTables::classifieds_table.".`date` >= {$listing->date}")
			->where(geoTables::classifieds_table.".`better_placement` >= {$listing->better_placement}")
			->where(geoTables::classifieds_table.".`id` != {$listing->id}")
			//NOTE: order it backwards so the "last one" comes up first
			->order(geoTables::classifieds_table.".`better_placement` ASC")
			->order(geoTables::classifieds_table.".`date` ASC")
			//only bother getting the ID column
			->columns('`id`',null,true);
		
		
		$listing_id = $db->GetOne(''.$query);
		if (!$listing_id) {
			//no previous listings!
			return '';
		}
		
		$tpl_vars = array (
			'prev_listing_id' => $listing_id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'previous_ad_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Link that will display in the listing allowing the user to see the next listing within the category.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function next_ad_link (geoListing $listing, $params, $smarty)
	{
		$view = geoView::getInstance();
		if($view->next_ad_link) {
			//if view var has already been set for this, it's probably storefront trying to override the normal functionality
			return $view->next_ad_link;
		}
		//figure out the previous listing when sorting "standard" in the same category
		//as the listing
		$db = DataAccess::getInstance();
	
		$query = $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
		
		$cTable = geoTables::classifieds_table;
		$lcTable = geoTables::listing_categories;
		
		$cat_subquery = "SELECT * FROM $lcTable WHERE $lcTable.`listing`=$cTable.`id`
			AND $lcTable.`category`={$listing->category}";
		
		$query->where("EXISTS ($cat_subquery)", 'category');
			
		$query->where(geoTables::classifieds_table.".`live`=1",'live')
			//set it to get one just before the current listing...
			->where(geoTables::classifieds_table.".`date` <= {$listing->date}")
			->where(geoTables::classifieds_table.".`better_placement` <= {$listing->better_placement}")
			->where(geoTables::classifieds_table.".`id` != {$listing->id}")
			//NOTE: order it backwards so the "last one" comes up first
			->order(geoTables::classifieds_table.".`better_placement` DESC")
			->order(geoTables::classifieds_table.".`date` DESC")
			//only bother getting the ID column
			->columns('`id`',null,true);
	
	
		$listing_id = $db->GetOne(''.$query);
		if (!$listing_id) {
			//no next listings!
			return '';
		}
	
		$tpl_vars = array (
			'next_listing_id' => $listing_id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'next_ad_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Google Maps link to create a map to the location entered for this listing.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function mapping_link (geoListing $listing, $params, $smarty)
	{
		$mapping_location = trim(geoString::fromDB($listing->mapping_location));
		if (!$mapping_location) {
			//no mapping location set
			return '';
		}
		//url encode it
		$mapping_location = urlencode($mapping_location);
		
		$tpl_vars = array (
			'mapping_location' => $mapping_location,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'mapping_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Same as mapping_link, but uses Mapquest instead of Google Maps (not recommended for non-US addresses)
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function mapping_link_alternate (geoListing $listing, $params, $smarty)
	{
		$mapping_location = trim(geoString::fromDB($listing->mapping_location));
		if (!$mapping_location) {
			//no mapping location set
			return '';
		}
		//url encode it
		$mapping_location = urlencode($mapping_location);
	
		$tpl_vars = array (
			'mapping_location' => $mapping_location,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'mapping_link_alternate.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link text allowing the user to vote and leave comments about the current listing.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function vote_on_ad_link (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'vote_on_ad_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link text allowing the user to view the votes and comments attached to the current listing.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function show_ad_vote_comments_link (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'show_ad_vote_comments_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Number of votes in the leading vote category for this listing
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function voteSummary_votes (geoListing $listing, $params, $smarty)
	{
		$voteSummary = self::_voteSummary($listing);
		//This is unique, the vote summary is actually just getting vote info
		if ($params['assign']) {
			$smarty->assign($params['assign'], $voteSummary['votes']);
			return '';
		}
		return $voteSummary['votes'];
	}
	
	/**
	 * Total number of votes in all vote categories for this listing
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function voteSummary_total (geoListing $listing, $params, $smarty)
	{
		$voteSummary = self::_voteSummary($listing);
		if ($params['assign']) {
			$smarty->assign($params['assign'], $voteSummary['total']);
			return '';
		}
		//This is unique, the vote summary is actually just getting vote info
		return $voteSummary['total'];
	}
	
	/**
	 * Number of votes in the leading vote category, expressed as a percentage of the total number of votes
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function voteSummary_percent (geoListing $listing, $params, $smarty)
	{
		$voteSummary = self::_voteSummary($listing);
		if ($params['assign']) {
			$smarty->assign($params['assign'], $voteSummary['percent']);
			return '';
		}
		//This is unique, the vote summary is actually just getting vote info
		return $voteSummary['percent'];
	}
	
	/**
	 * Text (by default, a "thumbs up" or "thumbs down" image) used on the show votes page to describe the leading vote category
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function voteSummary_text (geoListing $listing, $params, $smarty)
	{
		$voteSummary = self::_voteSummary($listing);
		if ($params['assign']) {
			$smarty->assign($params['assign'], $voteSummary['text']);
			return '';
		}
		//This is unique, the vote summary is actually just getting vote info
		return $voteSummary['text'];
	}
	
	/**
	 * This is the link text allowing the user to view the bid history of the current auction.  You can turn off and on the visibility of this link while the auction is live within the admin.  The link will be visible when the auction ends
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function bid_history_link (geoListing $listing, $params, $smarty)
	{
		if ($listing->item_type!=2 || $listing->buy_now_only) {
			//this is not an auction!  Or it's a buy now only auction!
			return '';
		}
		$configuration_data = self::_initCatConfig();
		if ($listing->live && $configuration_data['bid_history_link_live'] != 1) {
			//don't show in times of living when its set to not show
			return '';
		}
		$tpl_vars = array (
			'listing_id' => $listing->id,
			'messages' => DataAccess::getInstance()->get_text(true, 1),
		);
		return geoTemplate::loadInternalTemplate($params, $smarty, 'bid_history_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the high bidder's username.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function high_bidder (geoListing $listing, $params, $smarty)
	{
		if ($listing->item_type!=2 || $listing->buy_now_only) {
			//this is not an auction!  Or it's a buy now only auction!
			return '';
		}
		$db = DataAccess::getInstance();
		$reverse_auction = ($listing->auction_type==3);
		
		$tpl_vars = array();
		$tpl_vars['reverse_auction'] = $reverse_auction;
		
		$sql = "SELECT `bidder` FROM ".geoTables::bid_table." WHERE `auction_id`='{$listing->id}'
			ORDER BY `bid` ".(($reverse_auction)? 'ASC' : 'DESC').", `time_of_bid` ASC";
		
		$user_id = (int)$db->GetOne($sql);
		if ($user_id) {
			$user = geoUser::getUser($user_id);
			$tpl_vars['high_bidder_username'] = $user->username;
		}
		
		$tpl_vars['messages'] = $db->get_text(true,1);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'high_bidder.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This shows the buy now price, and will adjust according to any buyer options selected (unlike buy_now_data which will show only base cost)
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function buy_now_price_adjusted (geoListing $listing, $params, $smarty)
	{
		if ($listing->item_type!=2 || $listing->auction_type==2 || $listing->buy_now==0) {
			//this is not an auction!  Or it's not right type of auction to have buy now link
			return '';
		}
		$tpl_vars=array();
		
		$tpl_vars['listing'] = $listing->toArray();
		if((int)$tpl_vars['listing']['buy_now'] == (float)$tpl_vars['listing']['buy_now']) {
			//if these quantities are soft-equal, the number is an integer. remove the decimal point and any trailing zeroes
			$tpl_vars['listing']['buy_now'] = (int)$tpl_vars['listing']['buy_now'];
		}
		
		$tpl_vars['hide_postcurrency'] = DataAccess::getInstance()->get_site_setting('hide_postcurrency');
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'buy_now_price_adjusted.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link to buy now.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function buy_now_link (geoListing $listing, $params, $smarty)
	{
		if ($listing->item_type!=2 || $listing->auction_type==2 || $listing->buy_now==0) {
			//this is not an auction!  Or it's not right type of auction to have buy now link
			return '';
		}
		$configuration_data = self::_initCatConfig();
		
		if ($listing->current_bid > 0) {
			if (!$configuration_data['buy_now_reserve']) {
				return '';
			}
			//there is bidder, but buy now can still happen if reserve is not
			//yet met, so see if it is met
			$reserve_met = ($listing->auction_type==3)? ($listing->current_bid<=$listing->reserve_price) : ($listing->current_bid>=$listing->reserve_price);
			if ($reserve_met) {
				//reserve is met already
				return '';
			}
		}
		
		if (!self::_canBid($listing)) {
			//cannot bid, don't show buy now link
			return '';
		}
		
		$tpl_vars = array();
		if (geoSession::getInstance()->getUserId() > 0) {
			$tpl_vars['query'] = "?a=1029&amp;b={$listing->id}&amp;d=1";
		} else {
			//not logged in, use login link
			require_once CLASSES_DIR . 'authenticate_class.php';
			$tpl_vars['query'] = '?a=10&amp;c='.urlencode(Auth::generateEncodedVars(array('a'=>'1029','b'=>(int)$listing->id,'d'=>'1')));
		}
		$tpl_vars['messages'] = DataAccess::getInstance()->get_text(true, 1);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'buy_now_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the time left in the current auction.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function time_remaining (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();
		
		$messages = DataAccess::getInstance()->get_text(true,1);
		
		$tpl_vars['delayed_start'] = $listing->delayed_start;
		//just so template has the data available if it want's to do thing different
		$tpl_vars['ends'] = $listing->ends;
		
		if($tpl_vars['ends'] == 0) {
			//this listing has unlimited duration. nothing to show here
			return '';
		}
		
		if ($tpl_vars['delayed_start'] == 0) {
			//only need to display the time remaining if the auction has started or is normal
						
			// Time remaining
			// Find weeks left
			$weeks = self::_dateDifference('w',geoUtil::time(),$listing->ends);
			$remaining_weeks = ($weeks * 604800);
		
			// Find days left
			$days = self::_dateDifference('d',(geoUtil::time()+$remaining_weeks),$listing->ends);
			$remaining_days = ($days * 86400);
		
			// Find hours left
			$hours = self::_dateDifference('h',(geoUtil::time()+$remaining_days),$listing->ends);
			$remaining_hours = ($hours * 3600);
		
			// Find minutes left
			$minutes = self::_dateDifference('m',(geoUtil::time()+$remaining_hours),$listing->ends);
			$remaining_minutes = ($minutes * 60);
		
			// Find seconds left
			$seconds = self::_dateDifference('s',(geoUtil::time()+$remaining_minutes),$listing->ends);
				
			$weeks_label = $messages[103191];
			$days_label = $messages[103192];
			$hours_label = $messages[103193];
			$minutes_label = $messages[103194];
			$seconds_label = $messages[103195];
				
			$time_left = '';
				
			if ($weeks > 0) {
				$time_left .= "$weeks $weeks_label, $days $days_label";
			} else if ($days > 0) {
				$time_left .= "$days $days_label, $hours $hours_label";
			} else if ($hours > 0) {
				$time_left .= "$hours $hours_label, $minutes $minutes_label";
			} else if ($minutes > 0) {
				$time_left .= "$minutes $minutes_label, $seconds $seconds_label";
			} else if ($seconds > 0) {
				$time_left .= "$seconds $seconds_label";
			}
			$tpl_vars['time_left'] = $time_left;
		}
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'time_remaining.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This displays the buyer options if there are any set for the auction.  
	 * It will allow the visitor to choose the various options the seller has 
	 * created for the item (for instance, if it was selling a shirt, there 
	 * might be shirt sizes or color options).  This will let the visitor 
	 * select the various options and it will update the price.
	 * 
	 * @param geoListing $listing
	 * @param array $params
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function buyer_option_selections (geoListing $listing, $params, $smarty)
	{
		$costOptions = geoListing::getCostOptions($listing->id);
		if (!$costOptions['groups']) {
			//no cost option groups
			return '';
		}
		
		$tpl_vars = $costOptions;
		if ($tpl_vars['hasCombined']) {
			//get the combined options.
			
			$tpl_vars['combined_json'] = json_encode($tpl_vars['combined']);
		}
		$tpl_vars['listing_id'] = $listing->id;
		$tpl_vars['precurrency'] = $listing->precurrency;
		$tpl_vars['postcurrency'] = $listing->postcurrency;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'buyer_option_selections.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the seller rating.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function seller_rating (geoListing $listing, $params, $smarty)
	{
		if (!geoMaster::is('auctions') || $listing->item_type!=2) {
			return '';
		}
		//Note: don't need to check anon because auctions cannot be anonymous
		$seller = geoUser::getUser($listing->seller);
		if (!$seller) {
			trigger_error('ERROR LISTING: Could not get seller info to show seller rating');
			return '';
		}
		$db = DataAccess::getInstance();
		$tpl_vars = array();
		
		$tpl_vars['listing_id'] = $listing->id;
		$tpl_vars['seller'] = $seller->toArray();
		$tpl_vars['score_image'] = '';
		
		if (($seller->feedback_score > 0) && ($seller->feedback_count != 0)) {
			$sql = "select filename from ".geoTables::auctions_feedback_icons_table." where begin <= ".$seller->feedback_score." AND end >= ".$seller->feedback_score;
			$tpl_vars['score_image'] = trim($db->GetOne($sql));
		} else if (($seller->feedback_score == 0) || ($seller->feedback_count == 0)) {
			$sql = "select filename from ".geoTables::auctions_feedback_icons_table." where begin = 0";
			$tpl_vars['score_image'] = trim($db->GetOne($sql));
		} else if ($seller->feedback_score < 0) {
			$sql = "select filename from ".geoTables::auctions_feedback_icons_table." where begin = -1";
			$tpl_vars['score_image'] = trim($db->GetOne($sql));
		}
		$tpl_vars['messages'] = $db->get_text(true,1);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'seller_rating.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the link to the feedback page.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function feedback_link (geoListing $listing, $params, $smarty)
	{
		if (!geoMaster::is('auctions') || $listing->item_type!=2) {
			return '';
		}
		
		$tpl_vars = array();
		
		$tpl_vars['listing_id'] = $listing->id;
		$tpl_vars['seller'] = $listing->seller;
		
		$tpl_vars['messages'] = DataAccess::getInstance()->get_text(true,1);
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'feedback_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the seller's feedback score.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function seller_number_rates (geoListing $listing, $params, $smarty)
	{
		if (!geoMaster::is('auctions') || $listing->item_type!=2) {
			return '';
		}
	
		$tpl_vars = array();
	
		$tpl_vars['listing_id'] = $listing->id;
		$tpl_vars['seller'] = $listing->seller;
		$seller = geoUser::getUser($listing->seller);
		if (!$seller) {
			return '';
		}
		$tpl_vars['feedback_count'] = $seller->feedback_count;
	
		$tpl_vars['messages'] = DataAccess::getInstance()->get_text(true,1);
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'seller_number_rates.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This displays the link to make a bid on the auction.  It is only displayed when the auction is live.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function make_bid_link (geoListing $listing, $params, $smarty)
	{
		if (!geoMaster::is('auctions') || $listing->item_type!=2) {
			return '';
		}
		if (!$listing->live || $listing->start_time > geoUtil::time() || $listing->buy_now_only) {
			//only shows when listing is live, not being previewed, and NOT buy now only
			return '';
		}
		
		$tpl_vars = array();
		
		$tpl_vars['query'] = '';
		if (geoSession::getInstance()->getUserId()) {
			if (self::_canBid($listing)) {
				//normal bid link
				$tpl_vars['query'] = "?a=1029&amp;b=".$listing->id;
			} else {
				$tpl_vars['is_banned'] = $listing->e_isBanned;
			}
		} else {
			//link goes to login to bid
			require_once CLASSES_DIR.'authenticate_class.php';
			$tpl_vars['query'] = "?a=10&amp;c=".urlencode(Auth::generateEncodedVars(array('a'=>'1029', 'b'=>$listing->id)));
		}
		
		$tpl_vars['messages'] = DataAccess::getInstance()->get_text(true,1);
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'make_bid_link.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This displays a link to "Pay Now" to the winning bidder.  This will only display something on auctions that are properly configured to use an on-site payment (such as Paypal), that are closed, and only to the winning bidder or admin user.  Note that it displays to the admin user so that the admin can see what it looks like, for design customization purposes.
	 *
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function on_site_purchase_link (geoListing $listing, $params, $smarty)
	{
		if (!geoMaster::is('auctions') || $listing->item_type!=2 || $listing->auction_type != 1) {
			//only valid for auctions, of type 1
			return '';
		}
		if (!$listing->live || $listing->start_time > geoUtil::time()) {
			//only shows when listing is live and start_time is not in future
			return '';
		}
		
		if ($listing->price_applies === 'item') {
			//do not show pay now on the listing details page, will cause
			//problems as the buyer could have purchased multiple
			return '';
		}
		
		if ($listing->final_price < $listing->reserve_price) {
			//don't show if final price is less than reserve price
			return '';
		}
		
		$browse_user_id = geoSession::getInstance()->getUserId();
		
		if (!$browse_user_id) {
			//required to be logged in to see
			return '';
		}
		
		$bidder_info = geoListing::getHighBidder($listing->id);
		if ($bidder_info['bidder'] == $browser_user_id || $browser_user_id == 1) {
			//current bidder is the one logged in OR it is admin user
			//(we allow admin user to see this, so they can make sure design looks
			// good)
			$payment_link_vars = array (
					'listing_id' => $listing->id,
					'winning_bidder_id' => $bidder_info['bidder'],
					'listing_details' => $listing->toArray(),
					'final_price' => $bidder_info['bid'],
					'current_user' => $browser_user_id,
					//THESE ARE NEW: as of version 7.1.0...
					'listing' => $listing,
					'params' => $params,
					'smarty' => $smarty,
					'listing_tag' => 'on_site_purchase_link',
			);
			return geoSellerBuyer::callDisplay('displayPaymentLinkListing',$payment_link_vars);
		}
		//it should not show anything if it got this far
		return '';
	}
	
	/**
	 * Shows the extra question names -- DEPRECATED / FOR BACK-COMPATIBILITY ONLY
	 *
	 * @category questions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 * @deprecated 7.3.2 use extra_questions() instead
	 */
	public static function extra_question_name (geoListing $listing, $params, $smarty)
	{
		self::_loadExtraQuestions($listing);
		
		$extra_questions = $listing->e_extra_questions;
		
		if (!$extra_questions) {
			//nothing here to show!
			return;
		}
		//expecting to be in format questions => .., values => ...
		$tpl_vars = array();
		foreach ($extra_questions as $question_id => $info) {
			$tpl_vars['questions'][] = $info['question'];
			$tpl_vars['answers'][] = $info;
		}
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'extra_question_name.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Shows the extra question values -- DEPRECATED / FOR BACK-COMPATIBILITY ONLY
	 *
	 * @category questions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 * @deprecated 7.3.2 use extra_questions() instead
	 */
	public static function extra_question_value (geoListing $listing, $params, $smarty)
	{
		self::_loadExtraQuestions($listing);
		
		$extra_questions = $listing->e_extra_questions;
		
		if (!$extra_questions) {
			//nothing here to show!
			return;
		}
		//expecting to be in format questions => .., values => ...
		foreach ($extra_questions as $question_id => $info) {
			$tpl_vars['questions'][] = $info['question'];
			$tpl_vars['answers'][] = $info;
		}
		$tpl_vars['add_nofollow_user_links'] = DataAccess::getInstance()->get_site_setting('add_nofollow_user_links');
		$tpl_vars['open_window_user_links'] = DataAccess::getInstance()->get_site_setting('open_window_user_links');
		return geoTemplate::loadInternalTemplate($params, $smarty, 'extra_question_value.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Shows the extra question names and values together (replaces old split functions)
	 *
	 * @category questions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 * @since 7.3.2
	 */
	public static function extra_questions (geoListing $listing, $params, $smarty)
	{
		self::_loadExtraQuestions($listing);
		
		$tpl_vars['questions'] = $listing->e_extra_questions;
		
		if (!$tpl_vars['questions']) {
			//nothing here to show!
			return;
		}
		$tpl_vars['add_nofollow_user_links'] = DataAccess::getInstance()->get_site_setting('add_nofollow_user_links');
		$tpl_vars['open_window_user_links'] = DataAccess::getInstance()->get_site_setting('open_window_user_links');
		// NOTE: 'questions' array has indicies: question, value, link 
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'extra_questions.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * This is the winning bidder's user id on a dutch auction.
	 * 
	 * @category auctions
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function winning_dutch_bidders (geoListing $listing, $params, $smarty)
	{
		if ($listing->item_type != 2 || $listing->auction_type != 2 || $listing->buy_now_only) {
			//wrong type of listing
			return '';
		}
		$db = DataAccess::getInstance();
		
		$printFriendly = (isset($params['print_friendly']) && $params['print_friendly']);
		
		//Get dutch winners
		$sql = "SELECT * FROM ".geoTables::bid_table." WHERE `auction_id`={$listing->id} ORDER BY `bid` DESC,`time_of_bid` ASC";
		$bid_result = $db->Execute($sql);
		if (!$bid_result) {
			trigger_error("ERROR SQL: When trying to get winning dutch bidders!  sql: $sql error: ".$db->ErrorMsg());
			return '';
		}
		$dutch_bidders = array();
		if ($bid_result->RecordCount() > 0) {
			$total_quantity = $listing->quantity;
			//echo "total items sold - ".$total_quantity."<br/>\n";
			$final_dutch_bid = 0;
			$quantity_bidder_receiving = 0;
			$show_bidder = $bid_result->FetchNextObject();
			do {
				if ($show_bidder->QUANTITY <= $total_quantity) {
					$quantity_bidder_receiving = $show_bidder->QUANTITY ;
					if ($show_bidder->QUANTITY == $total_quantity) {
						$final_dutch_bid = $show_bidder->BID;
						//echo $final_dutch_bid." is final bid after total = bid quantity<br/>\n";
					}
					$total_quantity = $total_quantity - $quantity_bidder_receiving;
				} else {
					$quantity_bidder_receiving = $total_quantity;
					$total_quantity = 0;
					$final_dutch_bid = $show_bidder->BID;
					//echo $final_dutch_bid." is final bid after total < bid quantity<br/>\n";
				}
		
				$local_key = count($dutch_bidders);
				$info = array();
				$info["bidder"] = $show_bidder->BIDDER;
				$info["quantity"] = $quantity_bidder_receiving;
				$info["bid"] = $show_bidder->BID;
				$info['bid_display'] = geoString::displayPrice($info['bid'], $listing->precurrency,$listing->postcurrency, 'listing');
				$user = geoUser::getUser($info['bidder']);
				if ($user) {
					//let the template have access to all user data, to allow more
					//customization options
					$info['bidder_info'] = $user->toArray();
				}
				$dutch_bidders[] = $info;
			} while (($show_bidder = $bid_result->FetchNextObject()) && ($total_quantity != 0) && ($final_dutch_bid == 0));
		}
		$tpl_vars = array();
		$messages = $db->get_text(true, (($printFriendly)? 69 : 1));
		$tpl_vars['col_txt'] = array(
			'userColumn' => (($printFriendly)? $messages[103366] : $messages[102711]),
			'priceColumn' => (($printFriendly)? $messages[103079] : $messages[102712]),
			'quantityColumn' => (($printFriendly)? $messages[103080] : $messages[102713]),
		);
		$tpl_vars['printFriendly'] = $printFriendly;
		$tpl_vars['dutchBidders'] = $dutch_bidders;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'winning_dutch_bidders.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * The name attached to the data field.
	 * 
	 * @category checkboxes
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function extra_checkbox_name (geoListing $listing, $params, $smarty)
	{
		$db = DataAccess::getInstance();
		$counter = 0;
		$columns = array();
		
		$configuration_data = self::_initCatConfig();
		
		$session_variables = self::$_session_vars;
		
		$language_id = geoSession::getInstance()->getLanguage();
		
		if (isset($session_variables['question_value'])) {
			//use passed in array
			$values = $session_variables['question_value'];
			$sql = $db->Prepare("SELECT `name`, `choices` FROM ".geoTables::questions_languages." WHERE `question_id`=? AND `language_id` = ?");
			
			foreach ($values as $key => $val) {
				if (strlen(trim($val)) == 0) {
					continue;
				}
				$show_special = $db->GetRow($sql, array($key, $language_id));
				if ($show_special['choices'] != 'check') {
					//this is a checkbox
					continue;
				}
				$key = ($configuration_data['checkbox_columns']) ? ($counter%$configuration_data['checkbox_columns']) : 0;
				$columns[$key][] = $val;
				$counter++;
			}
		} else {
			$sql = "SELECT langs.name FROM ".geoTables::classified_extra_table." as vals, ".geoTables::questions_languages." as langs WHERE vals.classified_id = {$listing->id} and vals.question_id = langs.question_id and langs.language_id = {$language_id} and checkbox = 1 order by vals.display_order asc";
			$result = $db->Execute($sql);
			if (!$result || $result->RecordCount() == 0) {
				return '';
			}
				
			while ($showResult = $result->FetchRow()) {
				$key = ($configuration_data['checkbox_columns']) ? ($counter%$configuration_data['checkbox_columns']) : 0;
				$columns[$key][] = geoString::fromDB($showResult["name"]);
				$counter++;
			}
		}
		if (!$counter) return '';
		
		$tpl_vars = array();
		$tpl_vars['columns'] = $columns;
		$tpl_vars['colCount'] = count($columns);
		$tpl_vars['colWidth'] = floor(100/count($columns));
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'extra_checkbox_name',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * The current category as well as links to parent categories this listing is in.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function category_tree (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array (
			'category_tree' => geoListing::getCategories($listing->id),
			'messages' => DataAccess::getInstance()->get_text(true,1),
			);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'category_tree.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * The current category as well as links to parent categories this listing
	 * is in, but using the breadcrumb format.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function category_breadcrumb (geoListing $listing, $params, $smarty)
	{
		$view = geoView::getInstance();
		if($view->category_tree) {
			//if category_tree view var is already set, Storefront is trying to override the default
			return $view->category_tree;
		}
		$tpl_vars = array (
			'category_tree' => geoListing::getCategories($listing->id),
			'messages' => DataAccess::getInstance()->get_text(true,1),
		);
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'category_breadcrumb.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Sellers username data.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function seller (geoListing $listing, $params, $smarty)
	{
		if (!$listing->seller) {
			//no seller for this one
			return '';
		}
		$tpl_vars = array();
		$anon = geoAddon::getRegistry('anonymous_listing');
		$tpl_vars['anon'] = false;
		if ($anon) {
			$anon_user_id = $anon->get('anon_user_id',false);
			$anon_user_name = $anon->get('anon_user_name','Anonymous');
		} else {
			$anon_user_id = false;
		}
		if ($anon && ($listing->seller == $anon_user_id)) {
			//this is anonymous -- don't show seller-specific stuff
			//$view->seller_label = '';
			$tpl_vars['anon'] = true;
			$tpl_vars['anon_username'] = $anon_user_name;
			$tpl_vars['anon_user_id'] = $anon_user_id;
		} else {
			$seller = geoUser::getUser($listing->seller);
			if ($seller) {
				$tpl_vars['seller_data'] = $seller->toArray();
			}
			$tpl_vars['listing_id'] = $listing->id;
		}
		$tpl_vars['show_contact_seller'] = $listing->show_contact_seller;
		return geoTemplate::loadInternalTemplate($params, $smarty, 'seller.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Displays the full primary region tree (breadcrumb) for the listing.
	 * 
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function primary_region_tree (geoListing $listing, $params, $smarty)
	{
		if (!$listing->id) {
			//just a failsafe
			return '';
		}
		$tpl_vars = array();
		
		$tpl_vars['region_trees'] = geoListing::getRegionTrees($listing->id);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'primary_region_tree.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Displays all the additional region trees (breadcrumbs) set for a listing 
	 * not including the primary region, each one separated by an HTML line break.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function additional_region_trees (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();

		$tpl_vars['region_trees'] = geoListing::getRegionTrees($listing->id);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'additional_region_trees.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Combination of the primary region tree, and all additional region trees,
	 * each tree (region breadcrumb) seperated by an HTML line break.
	 *
	 * @category general
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function all_region_trees (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();

		$tpl_vars['region_trees'] = geoListing::getRegionTrees($listing->id);
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'all_region_trees.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Displays the number of images for a listing.  This is the "real" count,
	 * not the number purchased.
	 *
	 * @category stats
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function number_images (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();
		
		$count = 0;
		
		//see if already retrieved images...
		if ($listing->e_images) {
			$count = count($listing->e_images);
		} else {
			//count them
			$sql = "SELECT COUNT(*) FROM ".geoTables::images_urls_table." WHERE `classified_id` = {$listing->id}";
			$count = (int)DataAccess::getInstance()->GetOne($sql);
		}
		
		$tpl_vars['number_images'] = $count;
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'number_images.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Displays the number of external videos for a listing.  This is the "real" count,
	 * not the number purchased.
	 *
	 * @category stats
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function number_videos (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();
		
		//count them
		$sql = "SELECT COUNT(*) FROM ".geoTables::offsite_videos." WHERE `listing_id` = {$listing->id}";
		$count = (int)DataAccess::getInstance()->GetOne($sql);
		
		$tpl_vars['number_videos'] = $count;
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'number_videos.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Displays the number of listing tags attached to a listing.
	 *
	 * @category stats
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function number_listing_tags (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();

		//count them
		$sql = "SELECT COUNT(*) FROM ".geoTables::tags." WHERE `listing_id` = {$listing->id}";
		$count = (int)DataAccess::getInstance()->GetOne($sql);
		
		$tpl_vars['number_listing_tags'] = $count;
	
		return geoTemplate::loadInternalTemplate($params, $smarty, 'number_listing_tags.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * For each multi-level field in the listing, shows each level's label
	 * and value, using &lt;ul&gt; (unordered list), with CSS class
	 * of "info" like is used for other listing information.
	 * 
	 * Can optionally show "only" values for specific field, by specifying
	 * only_field=# (replace # with the multi-level field number).
	 *
	 * @category multi_level_fields
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function multi_level_field_ul (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();

		$tpl_vars['leveled_fields'] = self::_getLeveledFields($listing, $params);
		
		if (!$tpl_vars['leveled_fields']) {
			//nothing to show
			return '';
		}
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'multi_level_field_ul.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * For each multi-level field in the listing, shows a breadcrumb.
	 * 
	 * Can optionally show "only" values for specific field, by specifying
	 * only_field=# (replace # with the multi-level field number).
	 *
	 * @category multi_level_fields
	 * @param geoListing $listing Listing object
	 * @param array $params Array of parameters passed in smarty tag
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function multi_level_field_breadcrumb (geoListing $listing, $params, $smarty)
	{
		$tpl_vars = array();

		$tpl_vars['leveled_fields'] = self::_getLeveledFields($listing, $params);
		
		if (!$tpl_vars['leveled_fields']) {
			//nothing to show
			return '';
		}
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'multi_level_field_breadcrumb.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Display "for 1 item" if price is for a single item, or there is only one
	 * item, or "for lot of #" if the price applies to the entire quantity, and
	 * there is more than one.  Designed to be used directly after or below the
	 * current price, minimum bid, buy now price, or any other prices like
	 * optional field prices that add cost.
	 * 
	 * @param geoListing $listing
	 * @param array $params
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	public static function price_for_how_many (geoListing $listing, $params, $smarty)
	{
		if($listing->auction_type == 2) {
			//dutch auction. this text does not apply
			return '';
		}
		$tpl_vars = array();
		
		$tpl_vars['quantity'] = $listing->quantity;
		$tpl_vars['price_applies'] = $listing->price_applies;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'price_for_how_many.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	public static function contact_seller_form (geoListing $listing, $params, $smarty)
	{
		if (!$listing->email) {
			//don't show message to seller link
			return '';
		}
		if ($listing->show_contact_seller === 'no') {
			//no showing contact seller link
			return '';
		}
		
		$db = DataAccess::getInstance();
		
		$current_user_id = geoSession::getInstance()->getUserId();
		if (!$current_user_id && $db->get_site_setting('seller_contact')) {
			//only show if logged in
			return '';
		}
		
		$tpl_vars = array();
		
		$tpl_vars['listing_id'] = $listing->id;
		$tpl_vars['listing'] = $listing->toArray();
		
		$seller = ($listing->seller)? geoUser::getUser($listing->seller) : false;
		$tpl_vars['seller'] = ($seller)? $seller->toArray() : array();
		
		//if an anonymous listing, be sure to show the correct username (from addon settings)
		$anon = geoAddon::getRegistry('anonymous_listing');
		if ($anon) {
			$anon_user_id = $anon->get('anon_user_id',false);
			$anon_user_name = $anon->get('anon_user_name','Anonymous');
			if ($listing->seller == $anon_user_id) {
				$tpl_vars['seller']['username'] = $anon_user_name;
			}
		}
		
		$tpl_vars['canAskPublicQuestion'] = ($current_user_id && $current_user_id != $seller->id && DataAccess::getInstance()->get_site_setting('public_questions_to_show')) ? true : false;
		
		$secure = geoAddon::getUtil('security_image');
		if ($secure && $secure->check_setting('messaging')) {
			$security_text =& geoAddon::getText('geo_addons','security_image');
			$tpl_vars['security_image'] = $secure->getHTML('', $security_text, 'message', false);
			$tpl_vars['security_js'] = $secure->getJs();
		}
		
		$tpl_vars['seller_contact'] = DataAccess::getInstance()->get_site_setting('seller_contact');
		
		$tpl_vars['messages'] = DataAccess::getInstance()->get_text(true, 6);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'contact_seller_form.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	public static function tell_a_friend_form (geoListing $listing, $params, $smarty)
	{
		if (!$listing->live) {
			//don't show notify friend link
			return '';
		}
		$tpl_vars = array ();
		
		$tpl_vars['listing_id'] = $listing->id;
		
		$secure = geoAddon::getUtil('security_image');
		
		if ($secure && $secure->check_setting('messaging')) {
			$security_text =& geoAddon::getText('geo_addons','security_image');
			
			$tpl_vars['security_image'] = $secure->getHTML('', $security_text, 'message', false);
			$tpl_vars['security_js'] = $secure->getJs();
		}
		
		$tpl_vars['messages'] = DataAccess::getInstance()->get_text(true, 4);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'tell_a_friend_form.tpl',
				geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	public static function user_rating (geoListing $listing, $params, $smarty)
	{
		$about = $listing->seller;
		return geoUserRating::render($about);
	}
	
	/**
	 * Used by the tags that show leveled fields, to get leveled fields for a listing.
	 * 
	 * @param geoListing $listing
	 * @param unknown $params
	 * @return Ambigous <multitype:, multitype:Ambigous <> , boolean, string, unknown, Mixed>
	 */
	private static function _getLeveledFields (geoListing $listing, $params)
	{
		$leveled_fields = geoListing::getLeveledValues($listing->id);
		
		if (isset($params['only_field']) && $params['only_field']) {
			//make it show ONLY specified field.
			$field_id = (int)$params['only_field'];
			if (!isset($leveled_fields[$field_id])) {
				$leveled_fields = array();
			} else {
				$leveled_fields = array(
						$field_id => $leveled_fields[$field_id],
				);
			}
		}
		
		return $leveled_fields;
	}
	
	/**
	 * Used for listing details page, when previewing listing, to set values
	 * for session vars so we can fake display stuff.
	 * 
	 * @param array $session_vars
	 */
	public static function addSessionVars ($session_vars)
	{
		self::$_session_vars = $session_vars;
	}
	
	/**
	 * Method to get session vars that were previously set by the system on the
	 * main display page.  If the array is empty, then the current page is not on the
	 * main display listing details page.
	 * 
	 * @return array
	 */
	public static function getSessionVars ()
	{
		return self::$_session_vars;
	}
	
	/**
	 * Initializes the category configuration and returns the cat settings
	 * @return array
	 */
	private static function _initCatConfig ()
	{
		if (empty(self::$_configuration_data)) {
			//populate settings
			$configuration_data = DataAccess::getInstance()->get_site_settings(true);
			$cat_id = geoView::getInstance()->getCategory();
			
			$catCfg = geoCategory::getCategoryConfig($cat_id, true);
		
			if ($catCfg && $catCfg['what_fields_to_use'] != 'site') {
				//there are category-specific settings for this category
				//merge them into the config array as 2nd parameter, so category settings take precedence if they exist
				$configuration_data = array_merge($configuration_data, $catCfg);
			}
			self::$_configuration_data = $configuration_data;
		}
		return self::$_configuration_data;
	}
	
	/**
	 * Initializes ad configuration and returns array of ad config settings
	 * @return array
	 */
	private static function _initAdConfig ()
	{
		if (empty(self::$_ad_configuration_data)) {
			self::$_ad_configuration_data = DataAccess::getInstance()->GetRow("SELECT * FROM ".geoTables::ad_configuration_table);
		}
		return self::$_ad_configuration_data;
	}
	
	/**
	 * Common method used by tag displaying thingies.
	 * 
	 * @param geoListing $listing
	 * @param string $file
	 * @param array $params
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	private static function _listing_tags ($listing, $file, $params, $smarty)
	{
		if (!$listing->id) {
			//listing ID not known?
			return '';
		}
		$tpl_vars = array();
		
		$tpl_vars['listing_tags_array'] = geoListing::getTags($listing->id);
		
		if (!$tpl_vars['listing_tags_array']) {
			//short-circuit... no tags for the listing, nothing to display
			return '';
		}
		
		//END OF SETTING UP...
		return geoTemplate::loadInternalTemplate($params, $smarty, $file, geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Parent method for the common stuff that is done for the different URL
	 * link methods, to cut down on code duplication
	 * 
	 * @param geoListing $listing
	 * @param int $url_number
	 * @param array $params
	 * @param Smarty_Internal_Template $smarty
	 * @return string
	 */
	private static function _url_links ($listing, $url_number, $params,  $smarty)
	{
		$field = 'url_link_'.(int)$url_number;
		$tpl_vars = array();
		$db = DataAccess::getInstance();
		
		//need text from listing details page
		$tpl_vars['messages'] = $db->get_text(true, 1);
		
		if (!strlen(trim($listing->$field))) {
			//URL not set!
			return '';
		}
		
		
		$url = trim(geoString::fromDB($listing->$field));
		
		if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
			$url = 'http://'.$url;
		}
		$tpl_vars['url'] = $url;
		$tpl_vars['add_nofollow_user_links'] = $db->get_site_setting('add_nofollow_user_links');
		$tpl_vars['open_window_user_links'] = $db->get_site_setting('open_window_user_links');
		return geoTemplate::loadInternalTemplate($params, $smarty, "$field.tpl", geoTemplate::SYSTEM, 'listing_details', $tpl_vars);
	}
	
	/**
	 * Loads the extra questions and values for the listing into the $listing->e_extra_questions
	 * var for use in the question tags.
	 * 
	 * @param geoListing $listing
	 */
	private static function _loadExtraQuestions ($listing)
	{
		if ($listing->e_extra_questions!==null) {
			//already retrieved...
			return;
		}
		$session_variables = self::$_session_vars;
		if ($session_variables && isset($session_variables['question_value'])) {
			$db = DataAccess::getInstance();
			$language_id = geoSession::getInstance()->getLanguage();
			$questions = array();
			//use passed in array
			$values = $session_variables['question_value'];
			$other = $session_variables['question_value_other'];
			$sql = $db->Prepare("SELECT `name`, `choices` FROM ".geoTables::questions_languages." WHERE `question_id`=? AND `language_id` = ?");
			
			foreach ($values as $key => $val) {
				if (strlen(trim($val)) == 0 && !(isset($other[$key]) && strlen(trim($other[$key])))) {
					continue;
				}
				$show_special = $db->GetRow($sql, array($key, $language_id));
				if ($show_special['choices'] == 'check') {
					//this is a checkbox
					continue;
				}
				$row = array();
				$row['question'] = geoString::fromDB($show_special["name"]);
				
				if (strlen(trim($other[$key]))) {
					//"other" box in use -- replace value
					$row['value'] = $other[$key];
				} else {
					$row['value'] = geoString::fromDB($val);
				}
				$row['link'] = false;
				if ($show_special['choices'] == 'url') {
					$href = $row['value'];
					if (strpos($href, "://") === false && strpos($href, "mailto:") === false) {
						//add http:// if there's no protocol given
						$href = 'http://' . $href;
					}
					$row['link'] = $href;
				} else if ($show_special['choices']=='date') {
					//format it for date
					$row['value'] = geoCalendar::display($row['value']);
				}
				$questions[$key] = $row;
			}
			$listing->e_extra_questions = $questions;
		} else {
			//let geoListing class populate it from the database
			geoListing::getExtraQuestions($listing->id);
		}
	}
	
	/**
	 * Figures out whether the listing is placed anonymously or not
	 * @param geoListing $listing
	 * @return bool
	 */
	private static function _isAnon ($listing)
	{
		if (!isset($listing->e_is_anon)) {
			if (!$listing->seller) {
				//no seller set, must be anon?
				$listing->e_is_anon = true;
			} else {
				$anon = geoAddon::getRegistry('anonymous_listing');
				$anon_user_id = ($anon)? $anon->get('anon_user_id',false) : false;
				
				$listing->e_is_anon = ($anon_user_id && $listing->seller == $anon_user_id);
			}
		}
		return $listing->e_is_anon;
	}
	
	/**
	 * Figures out if the current browsing user can bid on the given listing or not.
	 * 
	 * @param geoListing $listing
	 * @return bool
	 */
	private static function _canBid ($listing)
	{
		if (!isset($listing->e_canBid)) {
			$canBid = false;
			$user_id = geoSession::getInstance()->getUserId();
			if (!$user_id) {
				//we don't know who the user is, so just assume they can bid
				$canBid = true;
			} else {
				$configuration_data = self::_initCatConfig();
				$db = DataAccess::getInstance();
				$is_banned = false;
				if ($configuration_data['invited_list_of_buyers'] && $configuration_data['black_list_of_buyers']) {
					//both invite and blacklist are enabled, check both
					$invited_count = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::invitedlist_table." WHERE `seller_id` = {$listing->seller}");
					
					$is_invited = ($invited_count==0)? false : $db->GetOne("SELECT COUNT(*) FROM ".geoTables::invitedlist_table." WHERE `seller_id`={$listing->seller}
						AND `user_id`={$user_id}");
					
					$is_banned = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::blacklist_table." WHERE `seller_id`={$listing->seller}
						AND `user_id`={$user_id}");

					if ($is_invited==1) {
						//browsing user is actually on invited list, expired list does not matter.
						$canBid = true;
					} else if (!$is_banned && $invited_count == 0) {
						//browsing user is not on any list, but invited list is not
						//populated so allow them
						$canBid = true;
					}
				} else if ($configuration_data['invited_list_of_buyers']) {
					//check invited only
					
					$invited_count = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::invitedlist_table." WHERE `seller_id` = {$listing->seller}");
						
					$is_invited = ($invited_count==0)? false : $db->GetOne("SELECT COUNT(*) FROM ".geoTables::invitedlist_table." WHERE `seller_id`={$listing->seller}
						AND `user_id`={$user_id}");
					
					if ($invited_count==0 || $is_invited) {
						//either nothing on invited list, or they are invited, so can bid
						$canBid=true;
					}
				} else if ($configuration_data['black_list_of_buyers']) {
					//check black list only
					$is_banned = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::blacklist_table." WHERE `seller_id`={$listing->seller}
						AND `user_id`={$user_id}");
					
					if (!$is_banned) {
						//they are not banned, they can bid
						$canBid = true;
					}
				} else {
					//there are no restrictions on whether can bid or not..
					
					$canBid = true;
				}
				if (!$canBid) {
					//store whether is banned
					$listing->e_isBanned = $is_banned;
				}
			}
			$listing->e_canBid = $canBid;
		}
		return $listing->e_canBid;
	}
	
	/**
	 * Get the array of info used by vote summaries
	 * 
	 * @param geoListing $listing
	 * @return array
	 */
	private static function _voteSummary ($listing)
	{
		if (!isset($listing->e_voteSummary)) {
			$voteText = DataAccess::getInstance()->get_text(true, 115);
			$voteSummary = array();
			$totalVotes = $listing->one_votes + $listing->two_votes + $listing->three_votes;
			if ($totalVotes == 0) {
				//no votes yet!
				$voteSummary = array(
					'votes' => 0,
					'percent' => 0,
					'text' => $voteText[500903]
				);
			} else {
				//figure out which vote category has the most votes
				if ($listing->two_votes >= $listing->three_votes && $listing->two_votes > $listing->one_votes) {
					//plurality of votes are neutral.
					//use >= to prefer this over level 3 in a tie, and > to prefer level 1 over this in a tie
					$voteSummary = array(
						'votes' => $listing->two_votes,
						'percent' => round(($listing->two_votes / $totalVotes) * 100),
						'text' => $voteText[2010]
					);
				} else if($listing->three_votes > $listing->two_votes && $listing->three_votes > $listing->one_votes) {
					//plurality of votes are negative
					$voteSummary = array(
						'votes' => $listing->three_votes,
						'percent' => round(($listing->three_votes / $totalVotes) * 100),
						'text' => $voteText[2011]
					);
				} else {
					//plurality of votes are positive
					$voteSummary = array(
						'votes' => $listing->one_votes,
						'percent' => round(($listing->one_votes / $totalVotes) * 100),
						'text' => $voteText[2009]
					);
				}
			}
			$voteSummary['total'] = $totalVotes;
			$listing->e_voteSummary = $voteSummary;
		}
		return $listing->e_voteSummary;
	}
	
	/**
	 * Use to get difference of 2 times, but in specified interval
	 * 
	 * @param string $interval w d h m or s
	 * @param int $date1
	 * @param int $date2
	 * @return int
	 */
	private static function _dateDifference ($interval, $date1, $date2)
	{
		$difference =  $date2 - $date1;
		
		$intervals = array (
			'w'=>604800,
			'd'=>86400,
			'h'=>3600,
			'm'=>60,
			's'=>1,
			);
		
		if (!isset($intervals[$interval])) {
			//not a known interval...
			return $difference;
		}
		return (int) ($difference / $intervals[$interval]);
	}
	/**
	 * Pre-parse every single possible tag and assign it to the global view class,
	 * to allow backwards compatibility with older templates that do not use
	 * the {listing} tag.
	 */
	public static function preParseAllTags ()
	{
		$view = geoView::getInstance();
		
		$allTags = geoFields::getListingTagsMeta(array('tag'),true);
		
		//use generic template
		$tpl = new geoTemplate;
		$params = array();
		$listing = geoListing::getListing($view->listing_id);
		
		foreach ($allTags as $tag) {
			//Note: fields are hidden at bottom of browse display ad, so it's ok
			//that it bypasses the hidden field check in the geoListing::smartyDisplayTag()
			$view->$tag = self::$tag($listing, array(), $tpl);
		}
	}
}
