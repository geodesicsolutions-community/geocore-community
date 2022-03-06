<?php

/**
 * A listing object, basically a container object for particular listing.
 *
 * Since there are many listings, there can be many geoListing objects.  If you
 * want to get or set information for a listing, this is the object to use.
 *
 * It uses the magic methods __get and __set which enable getting a field by
 * calling $listing->field or setting a field by using $listing->field = $value.
 * The fields are NOT automatically encoded or decoded using that, you will
 * need to encode or decode the values as necessary depending on the field.
 *
 * @package System
 * @since Version 4.0.0
 */
class geoListing
{
	/**
	 * The listing data, un-encoded and everything.
	 * @var array
	 */
	private $_listing_data;

    private $_extra;

	/**
	 * Whether or not all the fields are retrieved, or just some of them.
	 * @var bool
	 */
	private $_all;

	/**
	 * Whether or not the current listing is in the expired table or not.
	 * @var bool
	 */
	private $_isExpired;

	/**
	 * Array of listing objects
	 * @var geoListing array
	 */
	private static $_listings;

	/**
	 * Array of fields that are hidden when user not logged in
	 * @var array
	 */
	private static $_hidden;

	private static $_recurringListings = [];

	/**
	 * Convienience function, gets the title for the specified listing.  It
	 * decodes the title for you using fromDB.
	 *
	 * If you know
	 * you will be needing more info that just the title, just get the whole
	 * listing instead, and decode it yourself.  This is kind of like calling
	 * geoString::fromDB(geoListing::getListing($listing_id)->title)
	 *
	 * @param int $listing_id
	 * @return string The title (already decoded from the db), or the listing ID if
	 *  the listing could not be found.
	 */
	public static function getTitle ($listing_id=0)
	{
		$listing = self::getListing($listing_id,false);
		if (is_object($listing)) {
			return geoString::fromDB($listing->title);
		}
		return $listing_id;
	}

	/**
	 * Gets the count of how many bids there currently are for the given
	 * listing ID.
	 * @param int $listing_id
	 * @return int|bool The current number of bids, or boolean false if there
	 *   was a problem getting the count.
	 */
	public static function bidCount ($listing_id)
	{
		$listing_id = (int)$listing_id;
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_bid_count!==null) {
				return $listing->e_bid_count;
			}
		}

		$db = DataAccess::getInstance();
		$sql = "SELECT count(*) total_bids FROM ".geoTables::bid_table." WHERE `auction_id`=?";
		$count = $db->GetOne($sql,array($listing_id));
		if ($count===false) {
			trigger_error('ERROR SQL: Query failed, sql: '.$sql.' Error: '.$db->ErrorMsg());
			return false;
		}
		$count = (int)$count;
		if ($saveCache) {
			//save cache for the listing object
			$listing->e_bid_count = $count;
		}
		return $count;
	}

	/**
	 * Get the high bidder result for the current high bidder of the given listing.
	 * Note that if the auction is a reverse auction, it will actually be returning
	 * the lowest bidder...
	 *
	 * @param int $listing_id
	 * @return boolean|array Boolean false if no high bidder found, array result
	 *   from the geodesic_auctions_bids table otherwise
	 * @since Version 7.1.0
	 */
	public static function getHighBidder ($listing_id)
	{
		$listing_id = (int)$listing_id;
		$saveCache = false;
		$db = DataAccess::getInstance();
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_high_bidder!==null) {
				return $listing->e_high_bidder;
			}
			$auction_type = $listing->auction_type;
		} else {
			//just in case this is done in environment that we don't want to
			//create a new object for each listing (like showing thousands of listings)
			$auction_type = $db->GetOne("SELECT `auction_type` FROM ".geoTables::classifieds_table." WHERE `id`={$listing_id}");
		}

		//change sorting based on if reverse auction or not
		$sort_by = ($auction_type==3)? 'ASC': 'DESC';

		$high_bidder = $db->GetRow("SELECT * FROM ".geoTables::bid_table." WHERE `auction_id`={$listing_id} ORDER BY `bid` $sort_by, `time_of_bid` ASC");

		if ($saveCache) {
			//save cache for the listing object
			$listing->e_high_bidder = $high_bidder;
		}
		return $high_bidder;
	}

	/**
	 * Way to easily get the bid details for a bid, given the listing ID,
	 * bidder, and quantity.  If multiple bids happen to match, will return the most
	 * recent bid.
	 *
	 * @param int $listing_id
	 * @param int $bidder
	 * @param int $quantity
	 * @return array|bool Return false if there is problem
	 * @since Version 7.2.0
	 */
	public static function getBid ($listing_id, $bidder, $quantity)
	{
		return DataAccess::getInstance()->GetRow("SELECT * FROM ".geoTables::bid_table." WHERE `auction_id`=? AND `bidder`=? AND `quantity`=? ORDER BY `time_of_bid` DESC",
				array((int)$listing_id, (int)$bidder, (int)$quantity));
	}

	/**
	 * Get the additional fees for an auction, in array format, with the total
	 * given in the index 'total' for the returned array.
	 *
	 * @param int $listing_id
	 * @since Version 7.2.0
	 */
	public static function getAuctionAdditionalFees ($listing_id)
	{
		$listing_id = (int)$listing_id;
		$saveCache = false;
		$db = DataAccess::getInstance();

		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_additional_fees!==null) {
				return $listing->e_additional_fees;
			}
			$listing_data = $listing->toArray();
		} else {
			//just in case this is done in environment that we don't want to
			//create a new object for each listing (like showing thousands of listings)

			$options = array();
			for ($i = 1; $i < 21; $i++) {
				$options[] = "`optional_field_{$i}`";
			}

			$listing_data = $db->GetRow("SELECT `seller`,`precurrency`,`postcurrency`,".implode(',',$options)." FROM ".geoTables::classifieds_table." WHERE `id`={$listing_id}");
		}
		$additional_fees = array();
		$userId = $listing_data['seller'];
		$category = (int)$db->GetOne("SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing`=$listing_id AND `is_terminal`='yes'");

		$additional_fees['raw']['total'] = 0;

		$groupId = ($userId)? geoUser::getUser($userId)->group_id : 0;

		$fields = geoFields::getInstance($groupId, $category);
		for ($i = 1; $i < 21; $i++) {
			//go through all the optional fields, see if they add cost, and if they do,
			//see if the value actually adds any cost (not 0 or blank field)
			$option = 'optional_field_'.$i;
			$fieldName = 'optional_field_'.$i;

			if ($fields->$fieldName->field_type=='cost' && $listing_data[$option]>0){
				//this optional field needs to be displayed.
				$additional_fees['raw'][$option] = $listing_data[$option];
				$additional_fees['raw']['total'] += $listing_data[$option];
			}
		}
		if ($additional_fees['raw']['total'] <= 0) {
			//no additional fees...  Set to false to indicate no additional fees
			$additional_fees = false;
		} else {
			//format the values
			foreach ($additional_fees['raw'] as $key => $val) {
				$additional_fees['formatted'][$key] = geoString::displayPrice($val,$listing_data['precurrency'],$listing_data['postcurrency']);
			}
		}

		if ($saveCache) {
			//save cache for the listing object
			$listing->e_additional_fees = $additional_fees;
		}
		return $additional_fees;
	}

	/**
	 * Gets array of tags for the given listing ID
	 * @param int $listing_id
	 * @return array
	 * @since Version 5.1.0
	 */
	public static function getTags ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_listing_tags!==null) {
				return $listing->e_listing_tags;
			}
		}
		$db = DataAccess::getInstance();

		$rows = $db->GetAll("SELECT `tag` FROM ".geoTables::tags." WHERE `listing_id`=$listing_id");
		$tags = array();
		foreach ($rows as $row) {
			$tags[] = geoString::fromDB($row['tag']);
		}
		if ($saveCache) {
			//save cache for the listing object
			$listing->e_listing_tags = $tags;
		}
		return $tags;
	}

	/**
	 * Gets an array of images for the listing specified, and sets a few things
	 * like what the scaled sizes should be.
	 *
	 * @param int $listing_id
	 * @param Object $result_set Can pass in a database result set for a set of
	 *   images.  This allows listing preview to work as it can be made to get
	 *   images that may not be "live" yet along with using an alternate display
	 *   order.  {@since Version 7.1.3}
	 * @return array
	 * @since Version 7.1.0
	 */
	public static function getImages ($listing_id, $result_set = null)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_images!==null) {
				return $listing->e_images;
			}
		}

		$db = DataAccess::getInstance();
		$ad_configuration_data = $db->GetRow("SELECT * FROM ".geoTables::ad_configuration_table);

		$dim = array(
			'max_width' => (($ad_configuration_data['maximum_image_width'])? $ad_configuration_data['maximum_image_width']:250),
			'max_height' => (($ad_configuration_data['maximum_image_height'])? $ad_configuration_data['maximum_image_height']:250),
			'max_thumb_width' => (($db->get_site_setting('maximum_thumb_width'))?$db->get_site_setting('maximum_thumb_width'):75),
			'max_thumb_height' => (($db->get_site_setting('maximum_thumb_height'))?$db->get_site_setting('maximum_thumb_height'):75),
			'max_full_width' => $ad_configuration_data['maximum_full_image_width'],
			'max_full_height' => $ad_configuration_data['maximum_full_image_height'],
			'max_gallery_main_width' => (($db->get_site_setting('gallery_main_width'))?$db->get_site_setting('gallery_main_width'):500),
			'max_gallery_main_height' => (($db->get_site_setting('gallery_main_height'))?$db->get_site_setting('gallery_main_height'):500),
			'max_lead_width' => $ad_configuration_data['lead_picture_width'],
			'max_lead_height' => $ad_configuration_data['lead_picture_height'],
		);
		//If the result set is "passed in", use the display order according to the
		//order that image results are returned back, not the value for display_order
		//column.
		$use_display_order = false;
		if ($result_set===null) {
			$sql = "SELECT * FROM ".geoTables::images_urls_table." WHERE `classified_id` = {$listing_id} ORDER BY `display_order`";
			$use_display_order=true;
			$result_set = $db->Execute($sql);
		}

		if (!$result_set || $result_set->RecordCount()==0) {
			//error with sql OR no images, can't do much
			if ($saveCache) {
				$listing->e_images = array();
			}
			return array();
		}
		$images = array();
		$display_order = 1;
		foreach ($result_set as $img) {
			//templates are expecting names to be little different
			$img['type'] = 1;
			$img['id'] = $img['image_id'];
			$img['url'] = $img['image_url'];

			if (!$img['image_width'] || !$img['image_height'] || !$img['mime_type']) {
				//don't have image dimensions -- try to get them!
				$imgDims = geoImage::getRemoteDims($img['id']);
				if ($imgDims) {
					$img['image_width'] = $imgDims['width'];
					$img['image_height'] = $imgDims['height'];
					$img['mime_type'] = $imgDims['mime'];
				}
			}

					//figure out scaled size dimensions
			if ($img['image_width'] && $img['image_height']) {
				$img['scaled']['image'] = geoImage::getScaledSize($img['image_width'],$img['image_height'],$dim['max_width'], $dim['max_height']);
				$img['scaled']['thumb'] = geoImage::getScaledSize($img['image_width'],$img['image_height'],$dim['max_thumb_width'], $dim['max_thumb_height']);
				$img['scaled']['gallery'] = geoImage::getScaledSize($img['image_width'],$img['image_height'],$dim['gallery_main_width'], $dim['gallery_main_height']);
				$img['scaled']['full'] = geoImage::getScaledSize($img['original_image_width'],$img['original_image_height'],$dim['max_full_width'], $dim['max_full_height']);
				//small_gallery is small gallery image size in gallery2 template
				$img['scaled']['small_gallery'] = geoImage::getScaledSize($img['image_width'],$img['image_height'],$dim['max_thumb_width'], $dim['max_thumb_height']);
				//large_gallery is large gallery image size in gallery2 template
				$img['scaled']['large_gallery'] = geoImage::getScaledSize($img['original_image_width'],$img['original_image_height'],$dim['max_gallery_main_width'], $dim['max_gallery_main_height']);
				if ($dim['max_lead_width'] && $dim['max_lead_height'] && $img['display_order']==1) {
					$img['scaled']['lead'] = geoImage::getScaledSize($img['original_image_width'], $img['original_image_height'],$dim['max_lead_width'],$dim['max_lead_height']);
				}
			}
			if (!$use_display_order) {
				$img['display_order'] = $display_order;
				$display_order++;
			}

			$images[$img['display_order']] = $img;
		}
		if ($saveCache) {
			$listing->e_images = $images;
		}
		return $images;
	}

	public static function isRecurring($listing_id)
	{
		if (!count(self::$_recurringListings)) {
			//get all recurring listings at once, so we only have to run this query once per pageload
			$db = DataAccess::getInstance();
			$result = $db->Execute("SELECT `listing_id` FROM ".geoTables::listing_subscription." WHERE `listing_id` = ?");
			foreach($result as $r) {
				self::$_recurringListings[$r['listing_id']] = 1;
			}
		}
		return (self::$_recurringListings[$listing_id] == 1) ? true : false;
	}

	/**
	 * Get array of multi-level field values.
	 *
	 * @param int $listing_id
	 * @return array
	 */
	public static function getLeveledValues ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_leveled!==null) {
				return $listing->e_leveled;
			}
		}

		$db = DataAccess::getInstance();
		$sql = "SELECT `field_value`, `default_name`, `leveled_field`, `level` FROM ".geoTables::listing_leveled_fields." WHERE `listing`=$listing_id
			ORDER BY `leveled_field`, `level`";
		$result = $db->Execute($sql);

		if (!$result || $result->RecordCount()==0) {
			//error with sql OR no images, can't do much
			if ($saveCache) {
				$listing->e_leveled = array();
			}
			return array();
		}

		$lField = geoLeveledField::getInstance();

		$fields = array();
		foreach ($result as $row) {
			$valInfo = $lField->getValueInfo($row['field_value'], true);
			if ($valInfo) {
				$fields[$valInfo['leveled_field']][$valInfo['level']] = $valInfo;
			} else {
				//possibly the value is no longer there, fall back on the
				//value saved in "default value" as the name

				$row['name'] = geoString::fromDB($row['default_name']);

				//at least try to load the level info, perhaps the level will still
				//be intact if not this specific value...
				$row['level_info'] = $lField->getLevel($row['leveled_field'], $row['level'], $db->getLanguage());
				$fields[$row['leveled_field']][$row['level']] = $row;
			}
		}

		if ($saveCache) {
			$listing->e_leveled = $fields;
		}
		return $fields;
	}

	/**
	 * Get auction cost options for specified listing.
	 *
	 * @param int $listing_id
	 * @return array
	 * @since Version 7.4.0
	 */
	public static function getCostOptions ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_cost_options!==null) {
				return $listing->e_cost_options;
			}
		}
		$db = DataAccess::getInstance();

		$costOptions = array (
			'groups' => array(),
			'hasCombined' => false,
			'combined' => array(),
		);

		//get buyer selections...
		$result = $db->Execute("SELECT * FROM ".geoTables::listing_cost_option_group." WHERE `listing`=? ORDER BY `display_order`",array($listing_id));
		if (!$result || $result->RecordCount() == 0) {
			//no groups
			if ($saveCache) {
				$listing->e_cost_options = $costOptions;
			}
			return $costOptions;
		}
		$hasCombined = $hasFileSlot = false;
		$groups = array();
		foreach ($result as $group) {
			$option_result = $db->GetAll("SELECT * FROM ".geoTables::listing_cost_option." WHERE `group`=? ORDER BY `display_order`", array((int)$group['id']));
			$options = array();
			foreach ($option_result as $option) {
				if($option['cost_added'] == (int)$option['cost_added']) {
					//force this to be an integer to prevent some javascript weirdness where it doesn't get treated as a number
					$option['cost_added'] = (int)$option['cost_added'];
				}
				if ($option['file_slot']>0) {
					$hasFileSlot = true;
				}
				$options[] = $option;
			}
			$group['options'] = $options;
			if ($group['quantity_type']=='combined') {
				$hasCombined = true;
			}
			$groups[] = $group;
		}
		$costOptions['hasCombined'] = $hasCombined;
		$costOptions['hasFileSlot'] = $hasFileSlot;
		$costOptions['groups'] = $groups;
		if ($hasCombined) {
			//get the combined options.
			$result = $db->Execute("SELECT * FROM ".geoTables::listing_cost_option_quantity." WHERE `listing`=?",array($listing_id));

			if (!$result) {
				//failsafe, DB error
				if ($saveCache) {
					$listing->e_cost_options = $costOptions;
				}
				return $costOptions;
			}
			$combos = array();
			foreach ($result as $combo) {
				$options = $db->GetAll("SELECT * FROM ".geoTables::listing_cost_option_q_option." WHERE `combo_id`=?",array((int)$combo['id']));
				foreach ($options as $combo_option) {
					$combo['options'][] = $combo_option['option_id'];
				}
				$combos[] = $combo;
			}
			$costOptions['combined'] = $combos;
		}
		if ($saveCache) {
			$listing->e_cost_options = $costOptions;
		}
		return $costOptions;
	}

	/**
	 * Gets array of regions, including "additional" regions if there are any set.
	 *
	 * @param int $listing_id
	 * @return array
	 * @since Version 7.1.0
	 */
	public static function getRegionTrees ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_regions!==null) {
				return $listing->e_regions;
			}
		}
		$db = DataAccess::getInstance();
		$region = geoRegion::getInstance();

		$rows = $db->Execute("SELECT * FROM ".geoTables::listing_regions." WHERE `listing`=$listing_id ORDER BY `region_order`, `level`");
		$regions = array();
		foreach ($rows as $row) {
			if ($row['level']>1 && !isset($regions[$row['region_order']][($row['level']-1)])) {
				//parents are shared with previous level...  Figure out which level
				$parent = (int)$db->GetOne("SELECT `parent` FROM ".geoTables::region." WHERE `id`=?",array($row['region']));
				if ($parent) {
					$use_order = false;
					foreach ($regions as $region_order => $levels) {
						foreach ($levels as $level => $parent_region) {
							if ($parent_region['id']==$parent) {
								//we found it!
								$use_order = $region_order;
								break(2);
							}
						}
					}
					if ($use_order!==false) {
						//we found which order it shares the parents of!  so add them first
						foreach ($regions[$use_order] as $level => $parent_region) {
							$regions[$row['region_order']][$level] = $parent_region;
							if ($parent_region['id']==$parent) {
								//we reached the parent, this is last one to do.
								break;
							}
						}
					}
				}
			}
			//populate the info, make sure to get all of the info for the region
			//as some of it may be useful for display purposes
			$info = ($row['region'])? $region->getRegionInfo($row['region']) : false;

			if (!$info) {
				//oops, could not get the region info, fall back on the default name
				$info = array (
					'id' => $row['region'],
					'name' => geoString::fromDB($row['default_name'])
					);
			}
			$regions[$row['region_order']][$row['level']] = $info;
		}
		if ($saveCache) {
			//save cache for the listing object
			$listing->e_regions = $regions;
		}
		return $regions;
	}

	/**
	 * Get array of categories for the given listing ID
	 *
	 * @param int $listing_id
	 * @return array
	 * @since Version 7.4.0
	 */
	public static function getCategories ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return false;
		}
		$db = DataAccess::getInstance();
		$category = (int)$db->GetOne("SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing`=$listing_id AND `is_terminal`='yes' AND `category_order` = 0");
		$categories = array();
		$rows = geoCategory::getInstance()->getParents($category,true, true);
		foreach ($rows as $row) {
			if (!defined('IN_ADMIN') && $row['enabled']=='no') {
				//this one is disabled, don't show it as part of categories...
				break;
			}
			$categories[] = $row;
		}
		return $categories;
	}

	/**
	 * Get the extra questions for the given listing.
	 *
	 * @param int $listing_id
	 * @return array Array of extra questions, each entry is an associative array
	 *   using index's of "question", "value" (for answer), and "link" which is the URL
	 *   for questions with type of "url" or false otherwise.  Question and value
	 *   are both going to be pre-formatted, and the index of each one is the question ID
	 * @since Version 7.2.1
	 */
	public static function getExtraQuestions ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_extra_questions!==null) {
				return $listing->e_extra_questions;
			}
		}
		$db = DataAccess::getInstance();
		$questions = array();
		$language_id = geoSession::getInstance()->getLanguage();

		$sql = "SELECT vals.question_id, langs.name as name, langs.choices as choices, vals.value as value from ".geoTables::classified_extra_table." as vals, ".geoTables::questions_languages." as langs where vals.classified_id = {$listing_id} and vals.question_id = langs.question_id and langs.language_id = $language_id and checkbox !=1 order by vals.display_order asc";
		$result = $db->Execute($sql);
		if (!$result || $result->RecordCount() == 0) {
			//problem running query, or no results...
			if ($saveCache) {
				$listing->e_extra_questions = array();
			}
			return array();
		}
		foreach ($result as $show_special) {
			$row = array();
			$row['question'] = geoString::fromDB($show_special["name"]);

			$row['value'] = geoString::fromDB($show_special['value']);
			$row['link'] = false;

			if ($show_special['choices'] == 'url') {
				$href = $row['value'];
				if (strpos($href, "://") === false && strpos($href, "mailto:") === false) {
					//add http:// if there's no protocol given
					$href = 'http://' . $href;
				}
				$row['link'] = $href;
			} else if ($show_special['choices'] == 'date') {
				$row['value'] = geoCalendar::display($row['value']);
			}
			$questions[$show_special['question_id']] = $row;
		}

		if ($saveCache) {
			//save cache for the listing object
			$listing->e_extra_questions = $questions;
		}
		return $questions;
	}

	/**
	 * Get the checkboxes for the given listing.
	 *
	 * @param int $listing_id
	 * @return array Array of checkboxes, in format checks[question_id]='checkbox name'
	 * @since Version 7.2.1
	 */
	public static function getCheckboxes ($listing_id)
	{
		$listing_id = (int)$listing_id;
		if (!$listing_id) {
			return array();
		}
		$saveCache = false;
		if (isset(self::$_listings[$listing_id])) {
			//listing object already exists so might as well use it
			$saveCache = true;
			$listing = self::getListing($listing_id);
			if ($listing->e_checkboxes!==null) {
				return $listing->e_checkboxes;
			}
		}
		$db = DataAccess::getInstance();
		$checks = array();
		$language_id = geoSession::getInstance()->getLanguage();
		$sql = "SELECT vals.question_id, langs.name FROM ".geoTables::classified_extra_table." as vals, ".geoTables::questions_languages." as langs WHERE vals.classified_id = {$listing_id} and vals.question_id = langs.question_id and langs.language_id = {$language_id} and checkbox = 1 order by vals.display_order asc";
		$result = $db->Execute($sql);
		if (!$result || $result->RecordCount() == 0) {
			if ($saveCache) {
				$listing->e_checkboxes = array();
			}
			return array();
		}

		foreach ($result as $row) {
			$checks[$row['question_id']] = geoString::fromDB($row['name']);
		}
		if ($saveCache) {
			//save cache for the listing object
			$listing->e_checkboxes = $checks;
		}
		return $checks;
	}

	/**
	 * Gets a listing according to the listing id specified, this is the main
	 * way to get yourself a listing object.
	 *
	 * @param int $listing_id
	 * @param bool $get_all If true, when fetching the listing, it gets all the
	 *  data for the listing all at once.  Handy to set to false if you know you
	 *  will only be using 1 or 2 columns so that it will be more efficient to only
	 *  retrieve those columns.
	 * @param bool $try_expired if true, will see if listing exists expired if it can't be found in active
	 * @param bool $force_refresh forces the system to get a fresh copy of the listing data, even if it's already been done this pageload.
	 *  Useful for places in the admin that update the listing directly but rely on this class for display after the fact
	 * @return geoListing|null If listing not found, will return null.
	 */
	public static function getListing($listing_id, $get_all = true, $try_expired = false, $force_refresh = false){
		$listing_id = intval($listing_id);
		if (!$listing_id){
			//invalid
			return null;
		}

		if($force_refresh && isset(self::$_listings[$listing_id])) {
			unset(self::$_listings[$listing_id]);
		}

		if (!isset(self::$_listings[$listing_id]) || (self::$_listings[$listing_id] === null && $try_expired)) {
			//listing has not been retrieved yet, or it has but is invalid but this time we are trying expired
			$db = DataAccess::getInstance();
			//get the listing and info
			self::$_listings[$listing_id] = new geoListing;
			self::$_listings[$listing_id]->_all = $get_all;
			self::$_listings[$listing_id]->_isExpired = false;

			if ($get_all){
				$sql = "SELECT * FROM ".geoTables::classifieds_table." WHERE `id`=? LIMIT 1";
				$listing_data = $db->GetRow($sql, array($listing_id));
				if (!$listing_data && $try_expired) {
					$sql = "SELECT * FROM ".geoTables::classifieds_expired_table." WHERE `id`=? LIMIT 1";
					$listing_data = $db->GetRow($sql, array($listing_id));
					self::$_listings[$listing_id]->_isExpired = true;
				}

				if ($listing_data) {
					//make sure ID is integer so strict comparisons work, since
					//the original way $listing->id worked was to be an int
					$listing_data['id'] = (int)$listing_data['id'];
					self::$_listings[$listing_id]->_listing_data = $listing_data;
					//need to explicitly set category the new way
					self::$_listings[$listing_id]->_listing_data['category'] = (int)$db->GetOne("SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing`=? AND `is_terminal`='yes' AND `category_order`=0", array($listing_id));
				} else {
					//invalid listing!
					self::$_listings[$listing_id] = null;
				}
			} else {
				//see if it is valid

				$row = $db->GetRow("SELECT `id` FROM ".geoTables::classifieds_table." WHERE `id`=$listing_id LIMIT 1");
				self::$_listings[$listing_id]->_listing_data = array ('id' => $listing_id);

				if (!$row && $try_expired) {
					//see if it's in expired
					$row = $db->GetRow("SELECT `id` FROM ".geoTables::classifieds_expired_table." WHERE `id`=$listing_id LIMIT 1");
					self::$_listings[$listing_id]->_isExpired = true;
				}
				if (!is_array($row) || !isset($row['id']) || $row['id'] != $listing_id) {
					//invalid listing
					self::$_listings[$listing_id] = null;
				}
			}
		}

		if (!$try_expired && isset(self::$_listings[$listing_id]) && self::$_listings[$listing_id]->_isExpired) {
			//it exists but it is expired, and we said no to getting expired
			return null;
		}

		if (is_object(self::$_listings[$listing_id]) && $get_all && !self::$_listings[$listing_id]->_all) {
			//get all the additional data about the listing, previously we didn't get all the data
			$db = DataAccess::getInstance();

			$table = (self::$_listings[$listing_id]->_isExpired)? geoTables::classifieds_expired_table: geoTables::classifieds_table;
			self::$_listings[$listing_id]->_listing_data = $db->GetRow("SELECT * FROM $table WHERE `id`=$listing_id LIMIT 1");
			//need to explicitly set category the new way
			self::$_listings[$listing_id]->_listing_data['category'] = (int)$db->GetOne("SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing`=? AND `is_terminal`='yes' AND `category_order`=0", array($listing_id));
			self::$_listings[$listing_id]->_all = true;
		}

		return self::$_listings[$listing_id];
	}

	/**
	 * Whether or not this listing is from the expired table or not.
	 * @return bool
	 */
	public function isExpired ()
	{
		return $this->_isExpired;
	}

	/**
	 * Finds out if this listing is locked for editing
	 *
	 * @return int 1 if locked, 0 otherwise
	 */
	public function isLocked ()
	{
		$originalOrderItemID = $this->order_item_id;
		$orderItem = geoOrderItem::getOrderItem($originalOrderItemID);
		if (is_object($orderItem)) {
			return $orderItem->get('locked', 0);
		}
		return 0;
	}

	/**
	 * Locks a listing, preventing other processes from modifying it until the lock is released
	 * Also can reverse that process by passing it 0 or false
	 *
	 * @param boolean|int $state true|1 to lock, false|0 to unlock
	 * @return boolean true on success, false on failure
	 */
	public function setLocked ($state = true)
	{
		//handle boolean values passed to function
		$state = (($state) ? 1 : false);

		$originalOrderItemID = $this->order_item_id;
		$orderItem = geoOrderItem::getOrderItem($originalOrderItemID);
		if (is_object($orderItem)) {
			$orderItem->set('locked', $state);
			$orderItem->save();
			return true;
		}
		return false;
	}

	/**
	 * Converts the listing's data into an array and returns it.
	 *
	 * @return array
	 */
	public function toArray ()
	{
		return $this->_listing_data;
	}

	/**
	 * Gets an array of all the order item ID's for parent items that have listing or listing_id set to
	 * this listing's ID.  Note that neither input vars will apply to the "original" order item for the
	 * listing.
	 *
	 * This does NOT verify order items.  It will also not get any order items if
	 * $listing->order_item_id is not set.
	 *
	 * @param bool $onlyActive If true, will only return order items current "active"
	 * @param bool $onlyLegit If true(default), will only return order items with the Order field set.
	 * @return array An array of order item ID's, with the first one being the "original"
	 *  order item (set as $listing->order_item_id).
	 */
	public function getAllOrderItems ($onlyActive = false, $onlyLegit = true)
	{
		if (!$this->id || !$this->order_item_id) {
			//no order items to get
			return array();
		}
		$extra = '';
		if ($onlyActive) {
			$extra .= " item.status = 'active' AND ";
		}
		if ($onlyLegit) {
			$extra .= " item.order != 0 AND ";
		}
		//Get all the items that go with this listing ID for "legit" items (that have orders)
		$sql = "SELECT item.id as item_id FROM `geodesic_order_item` as item, `geodesic_order_item_registry` as regi
		WHERE regi.order_item = item.id AND item.parent='0' AND $extra
		 (regi.index_key='listing_id' OR regi.index_key='listing')
		 AND regi.val_string = ?
		ORDER BY item.id";

		$all_items = DataAccess::getInstance()->GetAll($sql, array(''.$this->id));
		if (!isset($all_items[0]) || $all_items[0]['item_id'] != $this->order_item_id) {
			//item ID is not first in list, how did that happen?  Most likely it is because it is not
			//active.

			//We are going to force the first order item to always be used as the starter when getting order_items.
			array_unshift($all_items, array('item_id' => $this->order_item_id));
		}
		//put them in an array
		$return = array();
		foreach ($all_items as $row) {
			if (!in_array($row['item_id'], $return)) {
				$return[] = $row['item_id'];
			}
		}
		return $return;
	}

	/**
	 * If you get a set of listing's data, you can use this method to populate
	 * listing objects for each of the retrieved listings.
	 *
	 * That way it does not have to get info for the same listing twice.
	 *
	 * @param array $dataSet in array form, as if $db->GetAll($sql) was used.
	 * @since Version 5.0.0
	 */
	public static function addDataSet ($dataSet)
	{
		if (!is_array($dataSet)) {
			//expect data to be an array of listing info
			return;
		}
		foreach ($dataSet as $row) {
			self::addListingData($row);
		}
	}

	/**
	 * Add single listing's data so it doesn't have to be retrieved later.
	 * @param array $listing
	 * @since Version 5.1.2
	 */
	public static function addListingData ($listing)
	{
		if (!is_array($listing)) {
			//expect data to be an array of listing info
			return;
		}
		if (!isset($listing['id'])) {
			//no ID set? can't do anything without ID's
			return;
		}
		$listing_id = $listing['id'] = (int)$listing['id'];
		if (!$listing_id) {
			//invalid data set?
			return;
		}
		if (!isset(self::$_listings[$listing_id]) || self::$_listings[$listing_id] === null) {
			self::$_listings[$listing_id] = new geoListing;
			self::$_listings[$listing_id]->_all = false;//assume it is not all the data
			self::$_listings[$listing_id]->_isExpired = false;//assume it is not expired
			self::$_listings[$listing_id]->_listing_data = $listing;
		} else {
			if (!is_array(self::$_listings[$listing_id]->_listing_data)) {
				self::$_listings[$listing_id]->_listing_data = array();
			}
			self::$_listings[$listing_id]->_listing_data = array_merge(self::$_listings[$listing_id]->_listing_data, $listing);
		}
	}

	/**
	 * Used by custom smarty function {listing} to display some block of info
	 * related specifically to listing.  Note that this uses {@see geoTemplate::geoTemplate::loadInternalTemplate()}
	 * which in turn, applies the following common abilities:
	 *
	 * - assign : if this parameter is set, it will assign the output to the specified
	 *   variable in smarty instead of just displaying the output.
	 *
	 * - file, g_type, g_resource : these can be over-written with parameters, which
	 *   would make it use the specified values.  This would allow to force it to
	 *   use a different template than normal to display the contents.
	 *
	 * @param string $tag The tag to display
	 * @param array $params The params as passed into the smarty function
	 * @param Smarty_Internal_Template $smarty The smarty object as passed into
	 *   the smarty function.
	 * @return string The value as it should be returned by a smarty function.  Note
	 *   that if "assign" is one of the params, it will instead assign the output
	 *   to the variable specified in the smarty object, and will return Empty
	 *   string.
	 * @since Version 7.1.0
	 */
	public function smartyDisplayTag ($tag, $params, Smarty_Internal_Template $smarty)
	{
		$tag = trim($tag);
		//Check to make sure tag is not hidden
		if (self::_isHidden($tag)) {
			//this tag is hidden!
			return '';
		}


		if (is_callable(array('geoListingDisplay',$tag))) {
			return geoListingDisplay::$tag($this, $params, $smarty);
		}
		return '';
	}

	/**
	 * Used to display the specified field for the listing, used by the custom
	 * smarty function {listing ...} when the "field" is specified.
	 *
	 * @param string $field The field to display.
	 * @param array $params The params as passed into the smarty function
	 * @param Smarty_Internal_Template $smarty The smarty object as passed into
	 *   the smarty function.
	 * @return string The value as it should be returned by a smarty function.  Note
	 *   that if "assign" is one of the params, it will instead assign the output
	 *   to the variable specified in the smarty object, and will return Empty
	 *   string.
	 * @since Version 7.1.0
	 */
	public function smartyDisplayField ($field, $params, Smarty_Internal_Template $smarty)
	{
		//Check to make sure field is not hidden
		if (self::_isHidden($field)) {
			//this field is hidden!
			return '';
		}

		$value = null;
		if (isset($params['format'])) {
			if ($params['format']=='raw') {
				//use the raw value

				$value = $this->$field;
			} else if ($params['format']=='array') {
				//must be one of the things we know how to get...

				switch ($field) {
					case 'listing':
						//return array of all listing data
						$value = $this->toArray();
						break;

					case 'tags':
						//get listing tags
						$value = self::getTags($this->id);
						break;

					case 'order_items':
						//who knows, this might be useful to someone
						$value = $this->getAllOrderItems();
						break;

					case 'images':
						$value = self::getImages($this->id);
						break;

					case 'seller':
						$value = geoUser::getUser($this->seller);
						if ($value) {
							//convert to an array
							$value = $value->toArray();
						}
						break;

					case 'high_bidder':
						$value = null;
						if ($this->item_type==2) {
							//only work for auctions
							$value = self::getHighBidder($this->id);
							if ($value) {
								$user = geoUser::getUser($value['bidder']);
								if ($user) {
									//merge the user info into the high bidder info...
									$value = array_merge($value, $user->toArray());
								}
								unset($user);
							}
						}
						break;

					case 'leveled':
					case 'multi_level_fields':
						$value = self::getLeveledValues($this->id);
						break;

					case 'regions':
						$value = self::getRegionTrees($this->id);
						break;

					case 'extra_questions':
						//Added in version 7.2.1
						$value = self::getExtraQuestions($this->id);
						break;

					case 'checkboxes':
						//Added in version 7.2.1
						$value = self::getCheckboxes($this->id);
						break;

					default:
						//don't know what they want here...  give them empty array
						$value = array();
						break;
				}
			}
		} else {
			//get a normal "formatted" field

			if ($field == 'canonical_url') {
				//special case - get the full URL
				$value = $this->getFullUrl();
			} else if ($field == 'sponsored_by') {
				//special case...  get sponsored by code for user group of seller
				$seller_data = geoUser::getUser($this->seller);
				if ($seller_data) {
					$sql = "SELECT `sponsored_by_code` FROM ".geoTables::groups_table." WHERE `group_id` = ".$seller_data->group_id;
					$value = geoString::fromDB($this->db->GetOne($sql));
				} else {
					$value = '';
				}
			} elseif($field === 'member_since') {
				//special case -- don't show member_since for anonymous listings.
				$anon = geoAddon::getRegistry('anonymous_listing');
				if($anon && $this->seller == $anon->get('anon_user_id',false)) {
					$value = '';
				} else {
					//not anonymous, so do this the normal way
					$this->_loadFormatted();
					$value = (isset($this->e_formatted[$field]))? $this->e_formatted[$field] : '';
				}

			} else {
				//default behavior, get from browsing fields...
				$this->_loadFormatted();
				$value = (isset($this->e_formatted[$field]))? $this->e_formatted[$field] : '';
			}
		}

		//now $value will be set to whatever it should be.
		if (isset($params['assign'])) {
			$smarty->assign($params['assign'], $value);
			return '';
		}
		return $value;
	}
	/**
	 * For use in smarty plugins, tags, or anything really within a smarty environment,
	 * to try to get a listing ID based on the smarty params / environment.  Most
	 * commonly used by {listing} plugin.
	 *
	 * @param array $params The params as passed into the smarty function
	 * @param Smarty_Internal_Template $smarty The smarty object as passed into
	 *   the smarty function.
	 * @return int The listing ID or 0 if could not determine
	 * @since Version 7.4.0
	 */
	public static function smartyGetListingId ($params, Smarty_Internal_Template $smarty)
	{
		//figure out the listing ID
		$listing_id = 0;

		//first see if it was passed in through params
		if (isset($params['listing_id']) && (int)$params['listing_id']>0) {
			$listing_id = (int)$params['listing_id'];
		}

		//second see if this is listing details page, if so use the listing ID from that
		if (!$listing_id && geoView::getInstance()->listing_id) {
			$listing_id = (int)geoView::getInstance()->listing_id;
		}

		if (!$listing_id) {
			//last try to figure it out based on current template vars
			$raw = $smarty->getTemplateVars('listing');
			if ($raw && isset($raw['id'])) {
				$listing_id = (int)$raw['id'];
			}
			unset($raw);
		}
		if (!$listing_id) {
			//finally, try to see if it is in template var as $listing_id
			$listing_id = (int)$smarty->getTemplateVars('listing_id');
		}
		return $listing_id;
	}

	/**
	 * Used internally to figure out if the given field or tag is hidden or not
	 *
	 * @param string $field The field or tag
	 * @return boolean
	 * @since Version 7.1.0
	 */
	private static function _isHidden ($field)
	{
		if (!geoSession::getInstance()->getUserId()) {
			//user not logged in..
			if (!isset(self::$_hidden)) {
				//see if there is any fields to "hide"
				$regHidden = geoAddon::getRegistry('_core',true);
				$hiddenFields = $regHidden->hiddenFields;
				$hiddenFields = ($hiddenFields)? $hiddenFields : array();
				self::$_hidden = $hiddenFields;
			}
			if (isset(self::$_hidden[$field])) {
				//this field hidden!
				return true;
			}
		}
		return false;
	}

	/**
	 * Loads the formatted values for this listing, and assignes to e_formatted.
	 * used internally by displayField.
	 */
	private function _loadFormatted ()
	{
		if ($this->e_formatted) {
			//nothing to do, it's already loaded!
			return;
		}

		//generate formatted fields...

		//we will need default browsing text for this...
		$msgs = DataAccess::getInstance()->get_text(true, 3);

		//Use default text for normal category browsing...
		$text = array(
			'business_type' => array(
				1 => $msgs[1263],
				2 => $msgs[1264],
			),
			'time_left' => array(
				'weeks' => $msgs[103003],
				'days' => $msgs[103004],
				'hours' => $msgs[103005],
				'minutes' => $msgs[103006],
				'seconds' => $msgs[103007],
				'closed' => $msgs[100051]
			)
		);

		$browse = Singleton::getInstance('geoBrowse');
		$browse->messages = $msgs;

		//now set the formatted fields...
		$this->e_formatted = $browse->commonBrowseData($this->toArray(), $text, false, false);
	}

	/**
	 * Get the full URL for this listing, including any subdomains and all that fun stuff
	 * @return String
	 * @since 7.1.0
	 */
	public function getFullUrl()
	{
		$url = 'http://';
		$db = DataAccess::getInstance();

		//NOTE: subdomain parsing now part of SEO addon, if SEO is enabled as well
		//as geographic navigation and setting to
		$domain = substr($db->get_site_setting('classifieds_url'), (strpos($db->get_site_setting('classifieds_url'), ":")+3) );

		$url .= $domain;

		$url .= '?a=2&b='.$this->id; //mostly for use in emails, so & not encoded. caller can do it himself if required.

		$url = geoAddon::triggerDisplay('rewrite_single_url', array('url' => $url, 'forceNoSSL' => true), geoAddon::FILTER);
		if (is_array($url)) {
			//addon call returned the input array instead of a url string (meaning no addon chose to rewrite the url)
			//make sure to return only the important part
			$url = $url['url'];
		}

		return $url;
	}

	/**
	 * Convenience method to obtain the amount that selected Cost Options add to a bid
	 * @param int $listing
	 * @param int $bidder
	 * @return float
	 */
	public static function getCostOptionsPriceFromBid($listing_id, $bidder)
	{
		if(!$listing || !$bidder) {
			//missing data
			trigger_error('ERROR LISTING: cannot retrieve price of Cost Options without both a listing and bidder');
			return false;
		}
		//first, find the row in the bid table that has the selected options saved
		$db = DataAccess::getInstance();
		$selected_cost_options = $db->GetOne("SELECT `cost_options` FROM ".geoTables::bid_table." where `auction_id` = ? and `bidder` = ? ORDER BY `time_of_bid` DESC", array($listing_id, $bidder));
		$cost_options = json_decode($selected_cost_options, true);

		//get all possible Cost Options from the listing
		//then loop through them looking for a cost associated with the ones actually selected during this bid
		$listing_options = geoListing::getCostOptions($listing_id);
		$cost_options_cost = 0;
		foreach ($listing_options['groups'] as $group) {
			foreach ($group['options'] as $option) {
				if (in_array($option['id'], $cost_options)) {
					$cost_options_cost += $option['cost_added'];
				}
			}
		}
		return $cost_options_cost;
	}

	/**
	 * Remove the specified listing. Note that this removes the listing directly rather than archiving it.
	 * All attached images, questions, and bids are also removed.
	 *
	 * @param int $listingId
	 * @param bool $isArchived If true, will behave as it should when a listing
	 *   is merely being archived.  Note that it still does NOT add the listing
	 *   to the archived table, that should be done prior to calling this method.
	 *   This param added in version 5.2.0.
	 * @return bool Whether removal was a success or not.
	 * @since Version 5.0.0
	 */
	public static function remove ($listingId, $isArchived = false)
	{
		$listingId = (int)$listingId;

		if (!$listingId) {
			//can't remove without a proper listing ID
			return false;
		}
		$db = DataAccess::getInstance();

		//get listing's categories, and re-count them later
		$sql = "SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing` = ? AND `is_terminal` = 'yes'";
		$result = $db->Execute($sql, array($listingId));
		if($result) { //just for sanity
			foreach($result as $row) {
				geoCategory::updateCategoryCountDelayed($row['category']);
			}
		}

		//delete url images
		$sql = "SELECT `image_id` FROM ".geoTables::images_urls_table." WHERE `classified_id`=?";
		$get_url_result = $db->Execute($sql, array($listingId));
		if (!$get_url_result) {
			trigger_error('ERROR SQL: Error getting images, using sql: '.$sql.', Error: '.$db->ErrorMsg());
			return false;
		}
		while ($row = $get_url_result->FetchRow()) {
			//using this way as it takes less resources than something like FetchAll
			$imageId = (int)$row['image_id'];
			if ($imageId) {
				$removeImage = geoImage::remove($imageId);
				if (!$removeImage) {
					trigger_error('ERROR STATS: Stopping removal of listing, could not remove image(s).');
					return false;
				}
			}
		}

		//Remove cost options
		$sql = "SELECT `id` FROM ".geoTables::listing_cost_option_group." WHERE `listing`=?";
		$get_cost_option_result = $db->Execute($sql, array($listingId));
		if (!$get_cost_option_result) {
			trigger_error('ERROR SQL: Error getting cost options, using sql: '.$sql.', Error: '.$db->ErrorMsg());
			return false;
		}
		while ($row = $get_cost_option_result->FetchRow()) {
			$group_id = (int)$row['id'];
			if ($group_id) {
				$sql = "DELETE FROM ".geoTables::listing_cost_option." WHERE `group`=?";
				$option_result = $db->Execute($sql, array($group_id));
				if (!$option_result) {
					trigger_error('ERROR SQL: Error removing cost options, using sql: '.$sql.', Error: '.$db->ErrorMsg());
					return false;
				}
			}
		}

		if(self::isRecurring($listingId)) {
			//need to stop recurring processes on this listing
			$recurringId = $db->GetOne("SELECT `recurring_id` FROM ".geoTables::listing_subscription." WHERE `listing_id` = ?", array($listingId));
			if($recurringId) {
				geoRecurringBilling::remove($recurringId);
			}
		}

		//remove "simple" things from listings that don't need anything more
		//than removing entries from the DB
		$simpleRemoves = array (
			geoTables::classified_extra_table => 'classified_id',//extra questions
			geoTables::bid_table => 'auction_id',//bids
			geoTables::autobid_table => 'auction_id',//auto-bids
			geoTables::voting_table => 'classified_id',//voting
			geoTables::tags => 'listing_id',//tags
			geoTables::offsite_videos => 'listing_id',//offsite videos
			geoTables::listing_regions => 'listing',//regions
			geoTables::listing_cost_option_group => 'listing',//cost option groups
			geoTables::listing_cost_option_quantity => 'listing',//cost option combined quantity
			geoTables::listing_categories => 'listing', //categories
			geoTables::listing_subscription => 'listing_id', //recurring classifieds
		);
		if (!$isArchived) {
			//also remove from feedback tables
			$simpleRemoves[geoTables::auctions_feedbacks_table] = 'auction_id';//feedback
		}
		foreach ($simpleRemoves as $tableName => $colName) {
			//delete all entries that match this listing
			$sql = "DELETE FROM {$tableName} WHERE `{$colName}`={$listingId}";
			$result = $db->Execute($sql);
			if (!$result) {
				trigger_error('ERROR SQL: Error removing stuff from listing, using sql: '.$sql.', Error: '.$db->ErrorMsg());
				return false;
			}
		}

		//just in case there are any already retrieved listing data...
		if (isset(self::$_listings[$categoryId])) {
			//reset the listing data so there is nothing to go on to update things
			//in case something already has a reference to it.
			self::$_listings[$listingId]->_listing_data = array();

			//unset so it can't be requested later
			unset(self::$_listings[$listingId]);
		}

		//call for addons to do something when listing is removed...
		geoAddon::triggerUpdate('notify_geoListing_remove', array('listingId' => $listingId, 'isArchived' => $isArchived));

		//delete from classifieds table
		$sql = "DELETE FROM ".geoTables::classifieds_table." WHERE `id` = {$listingId}";
		$remove_result = $db->Execute($sql);
		if (!$remove_result) {
			trigger_error('ERROR SQL: Error removing listing, using sql: '.$sql.', Error: '.$db->ErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Allows object oriented listing objects.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		$name = strtolower($name);
		if (strpos($name, 'e_')===0) {
			//requesting e_* parameter...  which is "extra", so don't attempt to get
			//from the database or anything.
			$name = substr($name,2);
			return (isset($this->_extra[$name]))? $this->_extra[$name] : null;
		}
		if (!isset($this->_listing_data[$name]) && $name=='category') {
			//get the main category ID for the listing
			$db = DataAccess::getInstance();
			$sql = "SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing`=?
					AND `is_terminal`='yes' AND `category_order`=0";
			$this->_listing_data[$name] = (int)$db->GetOne($sql, array($this->id));
		}
		if (!$this->_all && !isset($this->_listing_data[$name])){
			$db = DataAccess::getInstance();
			$table = ($this->_isExpired)? geoTables::classifieds_expired_table : geoTables::classifieds_table;
			$sql = "SELECT `$name` FROM $table WHERE `id`=? LIMIT 1";
			$row = $db->GetRow($sql, array($this->id));
			$this->_listing_data[$name] = $row[$name];
		}

		if (isset($this->_listing_data[$name])){
			return $this->_listing_data[$name];
		}
		return false;
	}

	/**
	 * Allows object oriented listing objects.  (not meant to be called directly)
	 *
	 * Allows you to use $listing->price = 1.11 to set the price on a listing.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value){
		$name = strtolower($name);

		if (strpos($name,'e_')===0) {
			//this is "extra" data...
			$name = substr($name,2);
			$this->_extra[$name] = $value;
			return true;
		}

		if ($this->_isExpired && $name != 'order_item_id') {
			//if expired, do NOT allow setting values (unless it's order_item_id)
			return false;
		}

		if (isset($this->_listing_data[$name]) && $this->_listing_data[$name] == $value) {
			//nothing to change, it's already set to this.
			return true;
		}
		$this->_listing_data[$name] = $value;

		$db = DataAccess::getInstance();
		$table = ($this->_isExpired)? geoTables::classifieds_expired_table : geoTables::classifieds_table;
		$sql = "UPDATE $table SET `$name`=?  WHERE `id`=? LIMIT 1";
		$result = $db->Execute($sql, array($value, $this->_listing_data['id']));
		if ($result) {
			return true;
		}

		return false;
	}
}
