<?php
//ListingFeed.class.php
/**
 * Holds the geoListingFeed class, which can render RSS and other types of things
 * based on a group of listings.
 * 
 * @package System
 * @since Version 5.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    17.05.0-17-g706b8bd
## 
##################################

/**
 * Class that helps to render RSS or other types of feeds based on list of listings.
 * 
 * @package System
 * @since Version 5.0.0
 */
class geoListingFeed
{
	/**
	 * Internal use
	 * @internal private cache var
	 * @var Array
	 */
	private $_settings = array();
	/**
	 * Internal use
	 * @internal private cache var
	 * @var Array
	 */
	private $_selections = array ();
	/**
	 * Instance of the geoTableSelect object used to build the query
	 * @var geoTableSelect
	 */
	private $_feedQuery;
	/**
	 * Listing data for listings to show in feed
	 * @var Mixed
	 */
	private $_resultSet;

	/**
	 * The type of this feed
	 * @var String one of the class constants: RSS_FEED, OODLE_FEED, GENERIC_FEED
	 */
	private $_feedType;
	
	/**
	 * Use this constant to specify the value can be set in the URL.
	 * @var string
	 */
	const URL_SET = 'set';
	
	/**
	 * Use this constant to specify that the value should be read from a cookie
	 * @var string
	 */
	const COOKIE_SET = 'cookie';
	
	/**
	 * Type of feed is RSS feed.
	 * @var string
	 */
	const RSS_FEED = 'rss';
	
	/**
	 * Type of feed is oodle feed.
	 * @var string
	 */
	const OODLE_FEED = 'oodle';
	
	/**
	 * Type of feed is generic (script defined)
	 * @var string
	 */
	const GENERIC_FEED = 'generic';
	
	/**
	 * BACKWARDS COMPATIBILITY ONLY, do not use
	 * @var string
	 */
	const OODLE_IMG_THUMBNAIL = 'thumb';
	
	/**
	 * BACKWARDS COMPATIBILITY ONLY, do not use
	 * @var unknown_type
	 */
	const OODLE_IMG_FULL = 'full';
	
	/**
	 * When using image_url, this is choice to use the thumb not the full image
	 * @var string
	 */
	const IMG_THUMB = 'thumb';
	
	/**
	 * When using image_url, this is choice to use the full not the thumb image
	 * @var string
	 */
	const IMG_FULL = 'full';
	
	const CAT_NAME = 'cat_name';
	
	const CAT_NAME_ID = 'cat_name_id';
	
	/**
	 * The geoListingFeed constructor. Used to create a new geoListingFeed. Not used to create coffee.
	 */
	public function __construct ()
	{
		//since when generating a feed, the query will only be used once, so
		//don't have to bother with using a copy...
		$this->_feedQuery = DataAccess::getInstance()->getTableSelect(DataAccess::SELECT_FEED);
	}
	
	/**
	 * Set the type of feed based on one of the built-in feed types.
	 * 
	 * @param string $feedType Either {@link geoListingFeed::RSS_FEED} or {@link geoListingFeed::OODLE_FEED}
	 */
	public function setFeedType ($feedType)
	{
		$this->_feedType = $feedType;
		switch ($feedType) {
			case geoListingFeed::RSS_FEED:
				//RSS feed, set defaults
				
				if (!isset($this->tpl_file)) $this->tpl_file = 'rss_listings.tpl';
				if (!$this->maxListings || $this->maxListings <= 0) {
					//default to 20
					$this->maxListings = 20;
				}
				//make it get the image extras (like width, height, etc)
				$this->lead_image_extras = true;
				
				//downcast accented characters within the title.
				$this->clean_title = 1;
				break;
				
			case geoListingFeed::OODLE_FEED:
				//OODLE feed, set defaults
				
				if (!isset($this->tpl_file)) $this->tpl_file = 'oodle_feed.tpl';
				if (!$this->maxListings || $this->maxListings <= 0) {
					//default to 20 or 500 if oodle
					$this->maxListings = 500;
				}
				//make it NOT get the image extras (like width, height, etc)
				$this->lead_image_extras = false;
				//clean description and get rid of HTML
				$this->clean_description = $this->clean_description_html = true;
				//turn on image URL
				$this->imageUrl = 1;
				//turn on getting seller info
				$this->sellerData = 1;
				
				break;
				
			case geoListingFeed::GENERIC_FEED:
				//break ommited on purpose
				
			default:
				//generic defaults
				
				if (!isset($this->tpl_file)) $this->tpl_file = 'rss_listings.tpl';
				if (!$this->maxListings || $this->maxListings <= 0) {
					//default to 20
					$this->maxListings = 20;
				}
				
				break;
		}
	}
	
	/**
	 * Convenience method, almost identical to calling:
	 * $feed->getTableSelect()->where($expression, $named)
	 * 
	 * Only difference is that this method returns the geoListingFeed class to 
	 * make it possible to chain geoListingFeed methods.
	 * 
	 * @param string $expression See docs at {@see geoTableSelect::where()}
	 * @param string $named (optional) See docs at {@see geoTableSelect::where()}
	 * @return geoListingFeed Returns this to allow geoListingFeed method chaining.
	 * @since Version 6.0.0
	 */
	public function where ($expression, $named = null)
	{
		$this->_feedQuery->where($expression, $named);
		return $this;
	}
	
	/**
	 * Convenience method, almost identical to calling:
	 * $feed->getTableSelect()->columns($columns, $table, $reset)
	 * 
	 * Only difference is that this method returns the geoListingFeed class to 
	 * make it possible to chain geoListingFeed methods.
	 * 
	 * @param string $columns See docs at {@see geoTableSelect::columns()}
	 * @param string $table See docs at {@see geoTableSelect::columns()}
	 * @param bool $reset See docs as {@see geoTableSelect::columns()}
	 * @return geoListingFeed Returns this to allow geoListingFeed method chaining.
	 * @since Version 6.0.0
	 */
	public function columns ($columns, $table = null, $reset = false)
	{
		$this->_feedQuery->columns($columns, $table, $reset);
		return $this;
	}
	
	/**
	 * Gets the {@see geoTableSelect} geoTableSelect object that is used for
	 * generating the result set for the listing feed, to allow customizing
	 * the feed result set.
	 * 
	 * @return geoTableSelect
	 * @since Version 6.0.0
	 */
	public function getTableSelect ()
	{
		return $this->_feedQuery;
	}
	
	/**
	 * DO NOT USE - use where() method instead.  This method is deprecated and
	 * will be removed in a future release.
	 * 
	 * @param string $whereClause
	 * @return geoListingFeed Returns self to allow chaining.
	 * @deprecated In version 6.0.0, March 1, 2011.  This will be removed in a
	 *   future version.
	 */
	public function addWhereClause ($whereClause)
	{
		$this->_feedQuery->where($whereClause);
		
		//return this to allow chaining.
		return $this;
	}
	
	/**
	 * DO NOT USE - use the columns() method instead. This method is deprecated
	 * and will be removed in a future release.
	 * 
	 * @param string|array $selectColumn See docs on {@see geoTableSelect::columns()}
	 * @deprecated In version 6.0.0, March 1, 2011.  This will be removed in a
	 *   future version.
	 */
	public function addSelectColumn ($selectColumn)
	{
		$this->_feedQuery->columns($selectColumn);
	}
	
	/**
	 * Generates the SQL query based on everything specified for what is to be
	 * retrieved.
	 * 
	 * @return string
	 */
	public function generateSql ()
	{
		if (!isset($this->_feedType)) {
			//make sure defaults are set
			$this->setFeedType(self::GENERIC_FEED);
		}
		require_once(CLASSES_DIR.'site_class.php');
		$site = Singleton::getInstance('geoSite');
		
		if ($this->catId == self::URL_SET) {
			if (isset($_GET['catId'])) {
				$this->catId = intval($_GET['catId']);
			} else {
				$this->catId = 0;
			}
		}
		
		$classTable = geoTables::classifieds_table;
		$listCatTable = geoTables::listing_categories;
		if ($this->catId) {
			//needs to be in category
			$category_id = (int)$this->catId;
			
			$cat_subquery = "SELECT * FROM $listCatTable WHERE $listCatTable.`listing`=$classTable.`id`
				AND $listCatTable.`category`=$category_id";
			
			$this->_feedQuery->where("EXISTS ($cat_subquery)", 'category');
		}
		
		//Allow addons to alter things, for instance allow geo nav addon to do it's thing
		geoAddon::triggerUpdate('notify_ListingFeed_generateSql', $this);
		
		if ($this->userId == self::URL_SET && isset($_GET['userId'])) {
			$this->userId = intval($_GET['userId']);
		}
		$this->userId = (int)$this->userId;
		if ($this->userId > 0) {
			$this->_feedQuery->where("$classTable.`seller` = $this->userId");
		}
		
		//do the type of listing
		if ($this->type == self::URL_SET && isset($_GET['type'])) {
			$this->type = trim($_GET['type']);
		}
		
		switch ($this->type) {
			case 'all_auction':
				if (!geoMaster::is('auctions')) {
					//can't do this type
					break;
				}
				$this->_feedQuery->where("$classTable.`item_type` = 2", 'item_type');
				break;
			
			case 'reverse':
				if (!geoMaster::is('auctions')) {
					//can't do this type
					break;
				}
				//special type for custom reverse auctions
				$this->_feedQuery->where("$classTable.`item_type` = 2")
					->where("$classTable.`auction_type` = 3");
				break;
			
			case 'buy_now':
				if (!geoMaster::is('auctions')) {
					//can't do this type
					break;
				}
				$this->_feedQuery->where("$classTable.`item_type` = 2")
					->where("$classTable.`buy_now` > 0");
				break;
				
			case 'buy_now_only':
				if (!geoMaster::is('auctions')) {
					//can't do this type
					break;
				}
				$this->_feedQuery->where("$classTable.`item_type` = 2")
					->where("$classTable.`buy_now_only` = 1");
				break;
			
			case 'dutch':
				if (!geoMaster::is('auctions')) {
					//can't do this type
					break;
				}
				$this->_feedQuery->where("$classTable.`item_type` = 2")
					->where("$classTable.`auction_type` = 2");
				break;
			
			case 'classified':
				if (!geoMaster::is('classifieds')) {
					//can't do this type
					break;
				}
				$this->_feedQuery->orWhere("$classTable.`item_type`=1", 'item_type')
					->orWhere("$classTable.`item_type`=3",'item_type');
				break;
			
			case 'all':
				//break ommited on purpose
			default:
				//no criteria to add to where clause
				break;
		}
		if (!$this->_feedQuery->hasOrder()) {
			if ($this->orderBy == self::URL_SET && isset($_GET['orderBy'])) {
				$this->orderBy = trim($_GET['orderBy']);
			}
			$seed = rand(); //used for any of the featured ones
			switch ($this->orderBy) {
				case 'featured_1':
					$this->_feedQuery->where("$classTable.`featured_ad` = 1")
						->order("RAND($seed)");
					break;
					
				case 'featured_2':
					$this->_feedQuery->where("$classTable.`featured_ad_2` = 1")
						->order("RAND($seed)");
					break;
					
				case 'featured_3':
					$this->_feedQuery->where("$classTable.`featured_ad_3` = 1")
						->order("RAND($seed)");
					break;
					
				case 'featured_4':
					$this->_feedQuery->where("$classTable.`featured_ad_4` = 1")
						->order("RAND($seed)");
					break;
					
				case 'featured_5':
					$this->_feedQuery->where("$classTable.`featured_ad_5` = 1")
						->order("RAND($seed)");
					break;
					
				case 'hottest':
					$this->_feedQuery->order("$classTable.`viewed` DESC");
					break;
					
				case 'expiring':
					$this->_feedQuery->order("$classTable.`ends` ASC");
					break;
					
				case 'old':
					//date asc
					$this->_feedQuery->order("$classTable.`date` ASC");
					break;
					
				case 'new':
					//break ommited on purpose
				default:
					//default to new listings
					//date desc
					$this->_feedQuery->order("$classTable.`date` DESC");
					break; 
			}
		}
		//only show live listings
		if (!$this->skipLive) {
			$this->_feedQuery->where("$classTable.`live` = 1", 'live');
		}
		
		$feed->maxListings = (int)$feed->maxListings;
		$this->_feedQuery->limit($this->maxListings);
		
		//figure out what other columns should be retrieved
		$fields = (isset($this->fields))? (array)$this->fields : array();
		foreach ($this->show as $field => $useField) {
			if ($useField) {
				
				if($field === 'location_state' || $field === 'location_country') {
					//don't look for these old fields directly
					//just skip them for now and their values will get dropped in later (in processListing())
						//BUT! make sure to still set their labels in this loop 
				} else {
				
					$this->_feedQuery->columns("`$field`");
					
					if ($field == 'price') {
						//get pre and post currency as well
						$this->_feedQuery->columns(array("`precurrency`", "`postcurrency`"));
						if (geoMaster::is('auctions')) {
							//get min/starting/buy now bids, to use for price in auctions
							$this->_feedQuery->columns(array("`minimum_bid`", "`starting_bid`", "`buy_now`", "`buy_now_only`", "`item_type`"));
						}
					}
				}
			}
			if ($useField && !in_array($field, $fields)) {
				$fields[$field] = (isset($this->label[$field]))? $this->label[$field]: '';
			}
		}
		$this->fields = $fields;
		unset ($fields);
		if ($this->imageUrl || $this->leadImage || ($this->show['image'] && $this->imageCount == 1)) {
			$imagesTable = geoTables::images_urls_table;
			
			$on = array (
				"$imagesTable.`classified_id`=$classTable.id",
				"($imagesTable.`display_order`=1 OR $imagesTable.`display_order` IS NULL)"
			);
			$cols = array ('`thumb_url`','`image_url`');
			if ($this->lead_image_extras) {
				$cols[] = '`image_width`';
				$cols[] = '`image_height`';
				$cols[] = '`image_text`';
				$cols[] = '`image_id`';
				$cols['image_display_order'] = '`display_order`';
			}
			
			$this->_feedQuery->join($imagesTable, $on, $cols, geoTableSelect::JOIN_LEFT);
			unset ($on, $cols, $imagesTable);//free up memory
		}
	}
	
	/**
	 * Process a listing's data and get additional info for the listing "on the fly",
	 * this is meant to be called by the actual template.
	 * 
	 * @param array $params
	 * @param Smarty_Internal_Template $smarty
	 */
	public function processListing ($params, $smarty)
	{
		$listing = $params['listing'];
		if (!$listing) {
			//nothing to do
			return;
		}
		
		//cache listing in geoListing class
		geoListing::addListingData($listing);
		
		$db = DataAccess::getInstance();
		$base = geoFilter::getBaseHref();
		
		if ($this->imageUrl) {
			$imageUrl = (($this->defaultImgType == self::IMG_THUMB || !$listing['image_url']) && $listing['thumb_url'])? $listing['thumb_url'] : $listing['image_url'];
			if ($imageUrl && strpos($imageUrl, 'http') !== 0) {
				//prepend it with base URL, the URL does not contain the
				//full URL
				$imageUrl = $base . $imageUrl;
			}
			$listing['imageUrl'] = $imageUrl;
		} else if (($this->show['image'] && $this->imageCount != -1) || $this->leadImage) {
			if ($this->show['image'] && $this->imageCount != 1) {
				//Get images for each listing, have to get more than 1 image for each listing.
				$imageCount = (int)$this->imageCount;
				$limit = ($imageCount)? " LIMIT $imageCount" : '';
				$stmt = $db->Prepare("SELECT * FROM `geodesic_classifieds_images_urls` WHERE `classified_id`=? ORDER BY `display_order`$limit");
			} else {
				$stmt = false;
			}
			
			if ($listing['image'] > 0 || $this->leadImage) {
				if ($stmt) {
					$rows = $db->Execute($stmt, array($listing['id']));
				} else if (isset($listing['image_id']) && $listing['image_id'] !== null) {
					//it's in listing data already
					$row = array (
						'thumb_url' => $listing['thumb_url'],
						'image_url' => $listing['image_url'],	
						'image_width' => $listing['image_width'],
						'image_height' => $listing['image_height'],
						'image_text' => $listing['image_text'],
						'display_order' => $listing['image_display_order'],
					);
					
					$rows = array ($row);
				} else {
					//listing with no images to show
					$rows = array();
				}
				
				$listing['images'] = null;
				foreach ($rows as $row) {
					//figure out the size
					$thumbUrl = $row['thumb_url'];
					if ($thumbUrl == 0) {
						//Thumbnail not created, use full image url
						$thumbUrl = $row['image_url'];
					}
					$dim = geoImage::getScaledSize((int)$row['image_width'], (int)$row['image_height'], $this->imageWidth, $this->imageHeight);
					$x = $dim['width'];
					$y = $dim['height'];
					
					//use "images" field, to preserve the # images in case
					//template wants to use that to display image count
					if (strpos($thumbUrl, 'http') !== 0) {
						//prepend it with base URL, the URL does not contain the
						//full URL
						$thumbUrl = $base . $thumbUrl;
					}
					$listing['images'][$row['display_order']] = array (
						'url' => $thumbUrl,
						'width' => $x,
						'height' => $y,
						'text' => $row['image_text']
					);
					
					if ($this->leadImage && $row['display_order'] == 1) {
						//set lead width and height
						if ($this->leadWidth != $this->imageWidth || $this->leadHeight != $this->imageHeight) {
							//figure out lead pic width and height
							$dim = geoImage::getScaledSize((int)$row['image_width'], (int)$row['image_height'], $this->leadWidth, $this->leadHeight);
							$x = $dim['width'];
							$y = $dim['height'];
						}
						$listing['images'][1]['lead_width'] = $x;
						$listing['images'][1]['lead_height'] = $y;
					}
				}
			}
		}
		
		if(!$listing['category']) {
			//must need to get category the new way
			$listing['category'] = (int)$db->GetOne("SELECT `category` FROM ".geoTables::listing_categories." WHERE `listing`=? AND `is_terminal`='yes' AND `category_order`=0", array($listing['id']));
		}
		
		$listing['title'] = htmlentities(geoString::fromDB($listing['title']), ENT_QUOTES | ENT_DISALLOWED, geoString::getCharset(), false);
		
		if ($this->clean_all) {
			require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';
			$varsToUpdate = _listing_placement_commonOrderItem::getListingVarsToUpdate();
			
			foreach ($listing as $key => $val) {
				if (is_numeric($key) || !isset($varsToUpdate[$key])) {
					//ignore
					continue;
				}
				
				switch ($varsToUpdate[$key]) {
					case 'toDB':
						if (is_array($val) && $key == 'seller_buyer_data' && geoPC::is_ent()) {
							//special case
							$val = unserialize($val);
						}
						$val = geoString::fromDB($val);
						break;
					case 'int':
						$val = intval($val);
						break;
					case 'float':
						$val = floatval($val);
						break;
					case 'bool':
						$val = (($val)? true: false);
						break;
					default:
						//not altered, for fields like "date"
						break;
				}
				if (array_key_exists($key,$translations)) {
					$key = $translations[$key];
				}
				$listing[$key] = $val;
			}
		}
		
		if ($this->clean_description) {
			//clean up description
			if (!$this->clean_all) $listing['description'] = geoString::fromDB($listing['description']);
			//convert br's to newlines
			if ($this->clean_description_html) {
				$listing['description'] = preg_replace('/<br[\s]*\/?>/i'," \n", $listing['description']);
				//get rid of tags
				$listing['description'] = geoFilter::replaceDisallowedHtml($listing['description'], true);
			}
			//get rid of ]]> which would end the cdata
			$listing['description'] = trim(str_replace(']]>', '', $listing['description']));
			
			$listing['description'] = htmlentities(geoString::fromDB($listing['description']), ENT_QUOTES | ENT_DISALLOWED, geoString::getCharset(), false);
		}
		
		if ($this->show['price']) {
			//format the price
			$price = $listing['price'];
			if (isset($listing['item_type']) && $listing['item_type']==2) {
				//account for auctions, which will not use "price" field to store the current price
				if ($listing['buy_now_only']==1) {
					$price = $listing['buy_now'];
				} else {
					if ($listing['auction_type']==3) {
						//reverse auction, just use min bid which is really max bid
						$price = $listing['minimum_bid'];
					} else {
						$price = max($listing['minimum_bid'], $listing['starting_bid']);
					}
				}
			}
			$priceType = $this->formatZeroPriceAsText == 1 ? 'listing' : null; //replace 0-price with "N/A" if wanted
			$listing['price'] = geoString::displayPrice($price, $listing['precurrency'], $listing['postcurrency'], $priceType);
		}
		if ($this->removeCurrencyColumns) {
			unset ($listing['precurrency'], $listing['postcurrency']);
		}
		if ($this->removeNonRequestedColumns) {
			//There are some fields that are always retrieved despite $this->show settings,
			//this unsets the ones not used by $this->show.
			$extras = array ('id','title','description','date','category');
			foreach ($extras as $field) {
				if (!isset($this->show[$field]) || !$this->show[$field]) {
					//didn't want to use this one
					unset($listing[$field]);
				}
			}
		}
		if (isset($this->dateFormat)) {
			//convert date fields
			$dateFields = array ('date','ends','start_time','end_time');
			foreach ($dateFields as $dField) {
				if (isset($listing[$dField])) {
					if($dField === 'ends' && $listing[$dField] == 0) {
						//unlimited duration listing
						$listing[$dField] = '-';
					} else {
						$listing[$dField] = date($this->dateFormat, $listing[$dField]);
					}
				}
			}
		}
		
		if (isset($listing['category']) && $this->categoryName) {
			$catInfo = geoCategory::getBasicInfo($listing['category']);
			$listing['category_name'] = $catInfo['category_name'];
		}
		
		if ($this->sellerData && isset($listing['seller']) && $listing['seller'] > 1) {
			$user = geoUser::getUser($listing['seller']);
			if ($user) {
				$listing['seller_data'] = $user->toArray();
			}
		}
		
		if ($this->extraQuestions && isset($listing['id'])) {
			//Get the category questions
			$questions = $db->GetAll("SELECT * FROM ".geoTables::classified_extra_table."
				WHERE `classified_id`=?", array((int)$listing['id']));
			foreach($questions as $q) {
				$q['value'] = geoString::fromDB($q['value']);
				$q['name'] = geoString::fromDB($q['name']);
				$listing['questions'][] = $q;
			}
		}
		
		//add in the state/country values from geoRegion, if they've been requested
		if($this->show['location_state']) {
			$listing['location_state'] = geoRegion::getStateNameForListing($listing['id']);
		}
		if($this->show['location_country']) {
			$listing['location_country'] = geoRegion::getCountryNameForListing($listing['id']);
		}
		
		$smarty->assign('listing', $listing);
	}
	
	/**
	 * Generates the result set of listings which is stored internally.
	 */
	public function generateResultSet ()
	{
		$getListings = true;
		$db = DataAccess::getInstance();
		
		if (!defined('IN_ADMIN') && $db->get_site_setting('site_on_off')) {
			//get valid IP's
			if (!geoUtil::isAllowedIp()) {
				$getListings = false;
			}
		}
		
		if ($getListings) {
			//NOTE:  Using Execute allows result set to be iterated using foreach...
			$this->_resultSet = $db->Execute(''.$this->_feedQuery);
		} else {
			$this->_resultSet = array ();
			//use site off item to display
			$this->emptyItem = $this->siteOffItem;
		}
	}
	
	/**
	 * Magic method that renders the feed
	 */
	public function __toString ()
	{
		//set the content type to xml
		if (!$this->debug) header ("Content-Type: application/xml;");
		
		$tpl_type = (isset($this->tpl_type))? $this->tpl_type : geoTemplate::SYSTEM;
		$tpl_resource = (isset($this->tpl_resource))? $this->tpl_resource : 'ListingFeed';
		
		$tpl = new geoTemplate($tpl_type,$tpl_resource);
		
		//register smarty function to process each listing
		$tpl->registerPlugin('function', 'process_listing', array ($this, 'processListing'));
		
		if ($this->_resultSet->RecordCount() > 0) {
			$tpl->assign('listings', $this->_resultSet);
		} else {
			$tpl->assign('listings', 0);
		}
		$tpl->assign($this->_settings);
		
		//set up filter for URL's
		if (self::$_seoUtil === 'noset') {
			/*
			 * NOTE:  Normally, do NOT call addons directly, this is special
			 * case to super-optimize the code for really long feeds.
			 */
			self::$_seoUtil = geoAddon::getUtil('SEO');
		}
		//note: we ALWAYS register this plugin now even if seo is not on, otherwise
		//throws smarty error
		$tpl->registerPlugin('modifier', 'rewriteUrl',array('geoListingFeed', 'rewriteUrl'));
		
		if ($this->add_smarty_plugins_dir) {
			$tpl->addPluginsDir($this->add_smarty_plugins_dir);
		}
		
		$result = $tpl->fetch($this->tpl_file);
		
		if ($this->debug) {
			//if debug, flush messages so they display
			trigger_error('FLUSH MESSAGES');
		}
		
		return $result;
	}
	
	/**
	 * Magic method that gets the given setting using the syntax $feed->setting
	 * @param string $name
	 * @return mixed
	 */
	public function __get ($name)
	{
		if (isset($this->_settings[$name])) {
			return $this->_settings[$name];
		}
		return null;
	}
	
	/**
	 * Magic method that sets the setting using the syntax $feed->setting = 'value'
	 * @param String $name
	 * @param String $value
	 */
	public function __set ($name, $value)
	{
		$this->_settings[$name] = $value;
	}
	
	/**
	 * Magic method that allows seeing if the given setting is set using syntax
	 * isset($feed->setting)
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->_settings[$name]);
	}
	
	/**
	 * Magic method that allows un-setting a setting using syntax unset($feed->setting)
	 * 
	 * @param string $name
	 */
	public function __unset($name)
	{
		unset($this->_settings[$name]);
	}
	
	/**
	 * Internal use
	 * @internal
	 */
	static $_seoUtil = 'noset';
	
	/**
	 * Since listing feed can potentially be displaying listings in the thousands,
	 * it must be as efficient as possible.  This method is used to re-write URL's
	 * using SEO addon, but bypassing the normal methods to do so as those methods
	 * are not callibrated for this use so are not efficient enough for our needs.
	 * 
	 * @param string $string The URL to be re-written
	 * @return string The re-written URL (or the original URL if it should not be
	 *   re-written)
	 */
	public static function rewriteUrl ($string) {
		if (self::$_seoUtil === 'noset') {
			self::$_seoUtil = geoAddon::getUtil('SEO');
		}
		
		if (self::$_seoUtil) {
			return self::$_seoUtil->rewriteUrl($string, true);
		}
		return $string;
	}
}
