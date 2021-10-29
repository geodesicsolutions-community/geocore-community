<?php 
//BrowsingFilter.class.php
/**
 * Holds the geoBrowsingFilter class.
 *
 * @package System
 * @since Version 7.1.0
 */
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    17.05.0-12-g574c933
##
##################################
/**
 * System for activating, storing, and deactivating browsing filters.
 *
 * @package System
 * @since Version 7.1.0
 */
class geoBrowsingFilter
{
	/**
	 * the thing being filtered on; usually a field of some sort
	 * @internal
	 * @var String
	 */
	private $target;
	/**
	 * the type of filter used for this target (for convenience...also see self::$_types[$target])
	 * @internal
	 * @var String
	 */
	private $type;
	/**
	 * user-entered value for this filter (often a specific price, range, or string)
	 * @internal
	 * @var mixed
	 */
	private $value;
	/**
	 * convenience flag to hold whether this filter is for a category-specific question
	 * @internal
	 * @var bool
	 */
	private $is_cs;
	/**
	 * flag for whether this is a multi-level (leveled) field
	 * @internal
	 * @var bool
	 */
	private $is_leveled;
	
	/**
	 * True to indicate a filter's value should be printed as a price (with formatting). False to leave it alone
	 * @internal 
	 * @var bool
	 */
	private $show_as_price;

	//making these powers-of-two, just in case there's ever some reason for something to have more than one type
	const SCALAR = 1; //searchable text (for now, identical to PICKABLE)
	const RANGE = 2; //range of numbers, as in a price
	const BOOL = 4; //on or off -- category-specific checkboxes, and perhaps eventually "has images," etc
	const DATE_RANGE = 8; //like RANGE, but use calendar inputs
	const PICKABLE = 16; //like SCALAR, but with pre-defined choices from a pre-valued dropdown

	/**
	 * map filter targets to their types
	 * @internal
	 * @var String
	 */
	private static $_types = array(
			'price' => self::RANGE, //price is between x and y (include auction prices?)
			'image' => self::BOOL, //has images? (yes/no)
			/*
			 * probably don't need these...
				'start_time' => self::DATE_RANGE,
				'time_left' => self::DATE_RANGE, //should this be scalar time?
			*/

	);

	/**
	 * Internal use.
	 * @internal
	 * @var String
	 */
	private static $_activeFilters;

	/**
	 * NOTE: constructor should not be called directly. use getFilter() instead.
	 * @internal
	 * @param String $target
	 * 
	 */
	private function __construct($target)
	{
		if(!$this->isValidTarget($target)) {
			trigger_error('ERROR FILTER: '.$target.' is not a valid filter target');
			throw new Exception('Invalid filter target');
		}
		$this->target = $target;
		$this->type = self::$_types[$target];
		$this->is_cs = (strpos($target, 'cs_') === 0);
		$this->is_leveled = (strpos($target, 'leveled_')===0);
		$this->show_as_price = self::targetIsCostType($target);
	}

	/**
	 * Internal use.
	 * @internal
	 * @var String
	 */
	private static $_typesInitialized = false;
	
	/**
	 * If there is a currently-active filter for this target, return it. Otherwise, make a new one.
	 * NOTE: this is similar to, but not exactly a Singleton design pattern, so it doesn't use the typical "getInstance()" verbiage
	 * @param String $target the target of the filter to get
	 * @return geoBrowsingFilter a filter that applies to $target
	 */
	public static function getFilter($target)
	{
		if(isset(self::$_activeFilters[$target])) {
			return self::$_activeFilters[$target];
		}
		if(!self::$_typesInitialized) {
			self::_initializeTypes();
		}
		try {
			$filter = new geoBrowsingFilter($target);
		} catch(Exception $e) {
			return false;
		}
		
		return $filter;
	}

	/**
	 * For use in displaying filter values, determine if a given "number-type" field should use "price" formatting.
	 * @internal
	 * @param String $target
	 * @return boolean
	 */
	private static function targetIsCostType($target)
	{
		if($target === 'price') {
			//this one's easy
			return true;
		}
		if(strpos($target, 'cs_') === 0 || strpos($target, 'leveled_') === 0) {
			//cat-spec and leveled fields cannot show as price
			return false;
		}
		$category = self::getActiveCategory();
		$fields = geoFields::getInstance(0, $category);
		if($fields->$target->field_type === 'cost') {
			return true;
		}
		return false;
	}
	
	/**
	 * optional field and cat-spec types are dynamic; this initializes them.
	 * @internal
	 */
	private static function _initializeTypes() {
		
		$db = DataAccess::getInstance();
		$category = self::getActiveCategory();
		
		$fields = geoFields::getInstance(0, $category);
		
		$sql = "SELECT * FROM ".geoTables::browsing_filters_settings." WHERE `category` = ? AND `enabled` = 1";
		$settings = $db->Execute($sql, array($category));
		
		while($settings && $target = $settings->FetchRow()) {
			$name = $target['field'];
			$type = 0; //be sure to reset $type to 0 each time, to prevent bleeding
			if(strpos($name, 'optional_field_') === 0) {
				//this is an optional field
				
				if(!$fields->$name->is_enabled) {
					//field not enabled. do not set!
					continue;
				}
				$optional_type = $fields->$name->field_type;
				if($optional_type === 'number' || $optional_type === 'cost') {
					$type = self::RANGE;
				} elseif($optional_type === 'textarea' || $optional_type === 'text') {
					$type = self::SCALAR;
				} elseif($optional_type === 'date') {
					$type = self::DATE_RANGE;
				} elseif($optional_type === 'dropdown') {
					$type = self::PICKABLE;
				}
			} elseif(strpos($name, 'cs_') === 0) {
				//this is a cat-spec question
				$question_id = substr($name, 3);
				$cs_type = $db->GetOne("SELECT `choices` FROM ".geoTables::questions_table." WHERE `question_id` = ?", array($question_id));
				if(is_numeric($cs_type) || $cs_type === 'none') {
					$type = self::PICKABLE;
				} elseif($cs_type === 'check') {
					$type = self::BOOL;
				} elseif($cs_type === 'number') {
					$type = self::RANGE;
				} elseif($cs_type === 'date') {
					$type = self::DATE_RANGE;
				}  else {
					//not a valid question, or no longer active? do not set.
					continue;
				}
			} else if (strpos($name, 'leveled_')===0) {
				//leveled field
				$type = self::PICKABLE;
			}
			if($type) {
				self::$_types[$name] = $type;
			} else {
				//These aren't the droids you're looking for. You can go about your business. Move along.
				//(Didn't find a dynamic type, which means this field is either statically-typed in the declaration of self::$_types or doesn't exist)
			}
		}

		self::$_typesInitialized = true;
	}

	/**
	 * Determine if a given target is among the known, valid types
	 * @param String $target
	 * @return bool
	 */
	public function isValidTarget($target)
	{
		return (bool)isset(self::$_types[$target]);
	}

	/**
	 * Accessor for this filter's type variable
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Accessor for this filter's target variable
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Internal use.
	 * @internal
	 * @var String
	 */
	private $dependency = null;

	/**
	 * Get the target of the filter this filter depends on, if any
	 * @return String
	 */
	public function getDependency()
	{
		if(isset($this->dependency)) {
			return $this->dependency;
		}
		$db = DataAccess::getInstance();
		$this->dependency = ''.$db->GetOne("SELECT `dependency` FROM ".geoTables::browsing_filters_settings." WHERE `category` = ? and `field` = ?", array(self::getActiveCategory(), $this->target));
		return $this->dependency;		
	}
	
	/**
	 * get the breadcrumb piece for this specific filter.
	 * 
	 * NOTE: breadcrumb trails for filters are shown at the top of the filter list
	 */
	public function getBreadcrumb()
	{
		switch($this->type) {
			case self::BOOL:
				$msgs = geoAddon::getText('geo_addons','core_display');
				$value = ($this->value) ? $msgs['browsing_filters_option_yes'] : $msgs['browsing_filters_option_no'];
				break;
			case self::SCALAR:
			case self::PICKABLE:
				$value = $this->value;
				if ($this->isLeveled()) {
					$value = $value['name'];
				}
				break;
			case self::RANGE:
			case self::DATE_RANGE:
				if($this->value['low'] == 0) {
					$value = "&lt; ";
					if($this->type == self::DATE_RANGE) {
						$this->value['high'] = geoCalendar::display((int)$this->value['high'], true);
					}
					if($this->show_as_price) {
						//this is a price, or otherwise adds cost, so use geoNumber::format to get the format to print
						$value .= geoNumber::format($this->value['high']);
					} else {
						if($this->type == self::RANGE && $this->value['high'] == (int)$this->value['high']) {
							//this is an integer, and not a price; print without decimals
							$value .= (int)$this->value['high'];
						} else {
							//not a price, but is a non-integer or a date, so show as normal
							$value .= $this->value['high'];
						}
					}
				} elseif($this->value['high'] == 100000000) {
					$value = "&gt; ";
					if($this->type == self::DATE_RANGE) {
						$this->value['low'] = geoCalendar::display((int)$this->value['low'], true);
					} 
					if($this->show_as_price) {
						//this is a price, or otherwise adds cost, so use geoNumber::format to get the format to print
						$value .= geoNumber::format($this->value['low']);
					} else {
						if($this->type == self::RANGE && $this->value['low'] == (int)$this->value['low']) {
							//this is an integer, and not a price; print without decimals
							$value .= (int)$this->value['low'];
						} else {
							//not a price, but is a non-integer or a date, so show as normal
							$value .= $this->value['low'];
						}
					}
				} else {
					$value = '';
					if($this->type == self::DATE_RANGE) {
						$this->value['high'] = geoCalendar::display((int)$this->value['high'], true);
						$this->value['low'] = geoCalendar::display((int)$this->value['low'], true);
					} 
					if($this->show_as_price) {
						//this is a price, or otherwise adds cost, so use geoNumber::format to get the format to print
						$value .= geoNumber::format($this->value['low']);
					} else {
						if($this->type == self::RANGE && $this->value['low'] == (int)$this->value['low']) {
							//this is an integer, and not a price; print without decimals
							$value .= (int)$this->value['low'];
						} else {
							//not a price, but is a non-integer or a date, so show as normal
							$value .= $this->value['low'];
						}
					}
					$value .= ' - ';
					if($this->show_as_price) {
						//this is a price, or otherwise adds cost, so use geoNumber::format to get the format to print
						$value .= geoNumber::format($this->value['high']);
					} else {
						if($this->type == self::RANGE && $this->value['high'] == (int)$this->value['high']) {
							//this is an integer, and not a price; print without decimals
							$value .= (int)$this->value['high'];
						} else {
							//not a price, but is a non-integer or a date, so show as normal
							$value .= $this->value['high'];
						}
					}
				}
				break;
			default:
				//not a defined type
				return false;
				break;
		}
		$tpl = new geoTemplate('addon', 'core_display');
		$tpl->assign('target', $this->target);
		$tpl->assign('friendlyName', self::getFriendlyName($this->target));
		$tpl->assign('value', $value);
		$tpl->assign('self', self::getPageUrl());
		return $tpl->fetch('browsing_filter/breadcrumb.tpl');
	}

	/**
	 * Apply this browsing filter to the $query.  Note that this is done for
	 * you by the geoBrowsingFilter->activate() method for the main browsing
	 * query.
	 * 
	 * @param geoTableSelect $query The query to apply the filter to
	 * @param bool $unset If true, removes rather than adds the filter
	 * @param mixed $value optionally specify an explicit value to apply (usually leave this blank and get the value from $this->value)
	 */
	public function applyToQuery (geoTableSelect $query, $unset=false, $value=null)
	{
		$db = DataAccess::getInstance();
		
		$class = geoTables::classifieds_table;
		$extra = geoTables::classified_extra_table;
		
		if($unset) {
			$query->where('', 'browseFilter_'.$this->target);
			return true;
		}
		if ($value===null) {
			$value = $this->value;
		} else {
			$value = $this->cleanValue($value);
			if ($value===false) {
				//oops, can't do anything, invalid value
				return;
			}
		}
		
		if ($this->isCatSpec()) {
			$qid = (int)substr($this->target,3);
			if (!$qid) {
				//failsafe check, could not determine question ID
				return false;
			}
			$subQuery = new geoTableSelect($extra);
			$subQuery->where("$extra.`classified_id`=$class.`id`")
				->where("$extra.`question_id`=$qid");
		}
		
		if ($this->type==self::DATE_RANGE) {
			//clean calendar dates
			$value['low'] = (strlen($value['low']))? geoCalendar::fromInput($value['low']) : '';
			$value['high'] = (strlen($value['high']))? geoCalendar::fromInput($value['high']) : '';
		} else if ($this->type==self::RANGE) {
			//normal number range
			$value['low'] = (strlen($value['low']))? (float)$value['low'] : '';
			$value['high'] = (strlen($value['high']))? (float)$value['high'] : '';
		}
		
		switch($this->type) {
			case self::BOOL:
				if(!$this->isCatSpec()) {
					//not in use yet, but could be later, for things like "listings with images"
					$query->where("$class.`{$this->target}` > 0", 'browseFilter_'.$this->target);
				} else {
					//for bool, it must be a checkbox, so just check to make sure checkbox is 1
					$subQuery->where("$extra.`checkbox`=1");
					
					if ($value==1) {
						//DOES have checked...
						$query->where("EXISTS ($subQuery)", 'browseFilter_'.$this->target);
					} else {
						//does NOT have checked...
						$query->where("NOT EXISTS ($subQuery)", 'browseFilter_'.$this->target);
					}
				}
				break;
				
			case self::SCALAR:
				//break intentionally omitted
			case self::PICKABLE:
				if ($this->isLeveled()) {
					$levT = geoTables::listing_leveled_fields;
					$subQuery = new geoTableSelect($levT);
						
					$leveled_field = (int)$value['leveled_field'];
					$lev_value_id = (int)$value['id'];
					if (!$leveled_field || !$lev_value_id) {
						return false;
					}
					$subQuery->where("$levT.`listing`=$class.`id`")
						->where("$levT.`leveled_field`=$leveled_field")
						->where("$levT.`field_value`=$lev_value_id");
					$query->where("EXISTS($subQuery)", 'browseFilter_'.$this->target);
					unset($subQuery);
				} else if (!$this->isCatSpec()) {
					$query->where("$class.`{$this->target}` = '{$value}'", 'browseFilter_'.$this->target);
				} else {
					//check that value is the same
					$subQuery->where($db->quoteInto("$extra.`value`=?", $value));
					
					$query->where("EXISTS($subQuery)", 'browseFilter_'.$this->target);
				}
				break;
				
			case self::DATE_RANGE:
				//break intentionally omitted
			case self::RANGE:
				//Note: $value is already cleaned above
				if(!$this->isCatSpec()) {
					if($this->target === 'price') {
						//special case. set it up so that "price" can handle auction prices, as well
						$l = $value['low']; $h = $value['high']; $c = $class; //so this next line isn't any longer than it has to be ;)
						$query->where("($c.item_type = 1 AND $c.price >= $l AND $c.price <= $h) OR
										($c.item_type = 2 AND (
											($c.buy_now_only = 1 AND $c.buy_now >= $l AND $c.buy_now <= $h)
											OR
											($c.current_bid > 0 AND $c.current_bid >= $l AND $c.current_bid <= $h)
											OR
											($c.current_bid = 0 AND $c.starting_bid >= $l AND $c.starting_bid <= $h)
										))",'browseFilter_'.$this->target);
					} else {
						$query->where("$class.`{$this->target}` >= {$value['low']} AND $class.`{$this->target}` <= {$value['high']}", 'browseFilter_'.$this->target);
					}
				} else {
					if (strlen($value['low'])) {
						//low value entered
						$subQuery->where("$extra.`value`>={$value['low']}");
					}
					if (strlen($value['high'])) {
						//high value entered
						$subQuery->where("$extra.`value`<={$value['high']}");
					}

					$query->where("EXISTS($subQuery)", 'browseFilter_'.$this->target);
				}
				break;
				
			default:
				//not a defined type
				return false;
				break;
		}
		if (isset($subQuery)) {
			unset($subQuery);
		}
	}
	
	/**
	 * Gets the listing count with the filter applied.  This is useful for filters
	 * that have not been activated yet, to see how many listings match a specific
	 * filter
	 * 
	 * @param mixed $value Same value as would pass into activate
	 * @param int $inCategory Count for specified category
	 * @param bool $onlyLive If true(default), will only count live listings.
	 * @return int
	 */
	public function listingCount ($value, $inCategory = 0, $onlyLive = true)
	{
		$db = DataAccess::getInstance();
		$value = $this->cleanValue($value);
		if ($value===false) {
			//invalid value!
			return 0;
		}
		
		$query = $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
		if ($onlyLive) {
			//only count live!
			$query->where('`live` = 1', 'live');
		}
		if ($inCategory) {
			$cTable = geoTables::classifieds_table;
			$lcTable = geoTables::listing_categories;
			$cat_subquery = "SELECT * FROM $lcTable WHERE $lcTable.`listing`=$cTable.`id`
				AND $lcTable.`category`=$inCategory";
			
			$query->where("EXISTS ($cat_subquery)", 'category');
		}
		$this->applyToQuery($query, false, $value);
		return (int)$db->GetOne(''.$query->getCountQuery());
	}
	
	/**
	 * Sanitizes input variables according to the expected type
	 * @param mixed $value
	 * @return mixed
	 */
	public function cleanValue ($value)
	{
		switch($this->type) {
			case self::BOOL:
			case self::SCALAR:
			case self::PICKABLE:
				if ($this->isLeveled()) {
					//special case...
					if ($value && !is_array($value)) {
						//fresh from user input...  set it to array
						$value = geoLeveledField::getInstance()->getValueInfo($value);
					}
					if (!is_array($value) || !isset($value['id']) || !(int)$value['id']) {
						//invalid
						return false;
					}
					return $value;
				}
				if (is_array($value)) {
					return false;
				}
				break;
			case self::RANGE:
			case self::DATE_RANGE:
				if(!is_array($value) || (!isset($value['high']) || !isset($value['low']))) {
					return false;
				}
				break;
			default:
				//not a defined type
				return false;
				break;
		}
		return $value;
	}
	
	private static $addedNoindex = false;
	/**
	 * Activate this filter by applying it to the Query so that it affects browsing results
	 * @param mixed $value
	 */
	public function activate($value)
	{
		//$value is probably user input -- be sure to clean it as needed!
		$value = $this->cleanValue($value);
		if ($value===false) {
			//invalid value
			
			return false;
		}
		
		$db = DataAccess::getInstance();
		
		$this->value = $value;
		//apply filter to geoTableSelect browsing object
		$this->applyToQuery($db->getTableSelect(DataAccess::SELECT_BROWSE));

		//update cache
		self::$_activeFilters[$this->target] = $this;

		if($db->get_site_setting('noindex_sorted') && !self::$addedNoindex) {
 			geoView::getInstance()->addTop('<meta name="robots" content="noindex" />');
 			self::$addedNoindex = true;
		}
		
		//store filter to database (linked to this session)
		$this->store();
	}


	/**
	 * removes a single filter from use
	 */
	public function deactivate()
	{
		if(!isset(self::$_activeFilters[$this->target])) {
			//this filter isn't active...something's wrong
			return false;
		}
		
		//if there are any filters that are dependent on this one, remove them first
		foreach(self::$_activeFilters as $f) {
			if($f->getDependency() === $this->target) {
				$f->deactivate();
			}
		}

		//update database
		$session = geoSession::getInstance()->initSession();
		$db = DataAccess::getInstance();
		$sql = "DELETE FROM ".geoTables::browsing_filters." WHERE `session_id` = ? AND `target` = ?";
		$db->Execute($sql, array($session, $this->target));

		//remove cache var
		unset(self::$_activeFilters[$this->target]);
		
		//update table select to not use this filter
		$this->applyToQuery($db->getTableSelect(DataAccess::SELECT_BROWSE), true);
	}

	/**
	 * call after changing things to push to db
	 * use sesison id instead of user id so that this works for not-logged-in users, too!
	 */
	private function store()
	{
		$db = DataAccess::getInstance();
		switch($this->type) {
			case self::BOOL:
			case self::SCALAR:
			case self::PICKABLE:
				$columns = "value_scalar";
				$values = "?";
				if ($this->isLeveled()) {
					$data = array($this->value['id']);
				} else {
					$data = array($this->value);
				}
				break;
			case self::RANGE:
			case self::DATE_RANGE:
				$columns = "value_range_low, value_range_high";
				$values = "?,?";
				$data = array($this->value['low'], $this->value['high']);
				break;
			default:
				//not a defined type
				return false;
				break;
		}
		$session = geoSession::getInstance()->initSession();
		$sql = "REPLACE INTO ".geoTables::browsing_filters." (session_id, target, category, $columns) VALUES ('$session','{$this->target}','".self::getActiveCategory()."', $values)";
		$db->Execute($sql, $data);
	}

	/**
	 * loop through active filters and store() them all
	 */
	public static function storeAll()
	{
		foreach(self::$_activeFilters as $target => $filter)
		{
			$filter->store();
		}
	}

	/**
	 * clears all filters currently set
	 */
	public static function deactivateAll()
	{
		foreach(self::$_activeFilters as $target => $filter)
		{
			$filter->deactivate();
		}
	}

	/**
	 * for use on initial load; get all filters from DB and set into static class var
	 */
	public static function retrieveAll()
	{
		if(!$_GET['a']) {
			//special case: if on the main home page, don't retrieve any filters (but leave them in the db in case user goes back to that category)
			return;
		}
		$db = DataAccess::getInstance();
		$session = geoSession::getInstance()->initSession();
		$sql = "SELECT * FROM ".geoTables::browsing_filters." WHERE `session_id` = ?";
		$result = $db->Execute($sql, array($session));
		foreach($result as $stored) {
			$filter = self::getFilter($stored['target']);
			if(!$filter || $stored['category'] != self::getActiveCategory()) {
				//this filter belongs to a different category. trash it and move on.
				$sql = "DELETE FROM ".geoTables::browsing_filters." WHERE `session_id` = ? AND `target` = ?";
				$db->Execute($sql, array($session, $stored['target']));
				continue;
			}
			$type = $filter->getType();
			switch($type) {
				case self::BOOL:
				case self::SCALAR:
				case self::PICKABLE:
					$filter->activate($stored['value_scalar']);
					break;
				case self::RANGE:
				case self::DATE_RANGE:
					$filter->activate(array('low' => $stored['value_range_low'], 'high' => $stored['value_range_high']));
					break;
				default:
					//not a defined type
					return false;
					break;
			}
			self::$_activeFilters[$stored['target']] = $filter;
		}
	}
	
	/**
	 * gets the full url of the current page, sans filter params
	 */
	public static function getPageUrl()
	{
		$db = DataAccess::getInstance();
		$url = $db->get_site_setting('classifieds_file_name').'?';
		$gets = array();
		foreach($_GET as $key => $val) {
			if(in_array($key, array('filterValue','setFilter','resetFilter', 'resetAllFilters', 'page'))) {
				//don't add this -- it's part of the filters
				//also don't add "page" so that adding a new filter always forces the user to "page one"
				continue;
			}
			$gets[] = "$key=$val";
		}
		$url .= implode('&amp;', $gets);
		return $url;
		
	}
	
	/**
	 * Retrieves the current value, if the filter is active
	 * @return mixed
	 */
	public function getValue()
	{
		if (!$this->isActive()) {
			//value cannot be retrieved as it cannot be set if it is not active
			return false;
		}
		return $this->value;
	}
	
	/**
	 * a quick and lightweight way to find out if there is an active filter for the current target
	 * @return bool
	 */
	public function isActive()
	{
		return (bool)isset(self::$_activeFilters[$this->target]);
	}
	
	/**
	 * Accessor for is_cs flag
	 * @return bool
	 */
	public function isCatSpec()
	{
		return $this->is_cs;
	}
	
	/**
	 * Whether or not this field is a multi-level field
	 * @return boolean
	 */
	public function isLeveled ()
	{
		return $this->is_leveled;
	}
	
	/**
	 * Returns the number of filters currently active.
	 * Mostly useful for testing against 0 to hide things when no filters are in use
	 * @return int
	 */
	public static function countActiveFilters()
	{
		return count(self::$_activeFilters);
	}
	
	/**
	 * _browsingCategory is the actual category being viewed by the user
	 * @var int
	 * @see self::$_activeCategory
	 */
	private static $_browsingCategory = 0;
	/**
	 * Setter for _browsingCategory
	 * @param int
	 */
	public static function setBrowsingCategory($category) {
		self::$_browsingCategory = intval($category);
	}
	
	/**
	 * Gets the currently set browsing category
	 * @return int
	 * @since Version 7.1.3
	 */
	public static function getBrowsingCategory ()
	{
		return self::$_browsingCategory;
	}
	
	/**
	 * activeCategory is the category to use the filters from.
	 * It could be the browsingCategory, or it could be the next-highest parent with filters enabled
	 * @var int|bool active category, or false if not set
	 * @see self::$_browsingCategory
	 */
	private static $_activeCategory = false;
	/**
	 * Accessor for (@see self::$_activeCategory)
	 * @return int
	 */
	public static function getActiveCategory($cacheBuster=false)
	{
		if(is_numeric(self::$_activeCategory) && !$cacheBuster) {
			return self::$_activeCategory;
		}
		$db = DataAccess::getInstance();
		
		$current_category = self::$_browsingCategory;
		
		
		//see if this category has enabled filters. go up a level if not
		$sql = "SELECT * FROM ".geoTables::browsing_filters_settings." WHERE `category` = ?";
		do{
			$result = $db->Execute($sql, array($current_category));
			if(!$result) {
				//something's wrong...try to fail gracefully
				return 0;
			} elseif($result->RecordCount() > 0) {
				//settings exist for this category
				self::$_activeCategory = $current_category;
				return $current_category;
			} else {
				//not broken, but no settings for this category -- go up one.
				$current_category = geoCategory::getParent($current_category);
			}
		} while($current_category);
		//got here, so found no active settings for any categories
		self::$_activeCategory = 0;
		return 0;
	}
	
	/**
	 * Gets the admin-entered name for a filter
	 * @param String $target
	 * @return String
	 */
	public static function getFriendlyName($target)
	{
		$db = DataAccess::getInstance();
		if (strpos($target, 'leveled_')===0) {
			//leveled fields...  special case, names aren't stored normally
			$parts = explode('_',$target);
			$labels = geoLeveledField::getInstance()->getLevelLabels($parts[1], $parts[2]);
			
			return $labels[$db->getLanguage()];
		}
		$name = $db->GetOne("SELECT `name` FROM ".geoTables::browsing_filters_settings_languages." WHERE `category` = ? AND `field` = ? AND `language` = ?",
				array(self::getActiveCategory(), $target, $db->getLanguage()));
		$name = geoString::fromDB($name);
		return $name;
	}
}