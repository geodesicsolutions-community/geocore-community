<?php
//Calendar.class.php
/**
 * Holds the geoCalendar utility class.
 * 
 * @package System
 * @since Version 4.0.0
 */


/**
 * Holds utility method(s) for manipulating stuff dealing with calendar selector.
 * 
 * @package System
 * @since Version 6.0.0
 */
class geoCalendar
{
	/**
	 * Internal use.
	 * @internal
	 */
	private static $_init = false;
	
	/**
	 * Initializes the calendar date picker JS for the current page load,
	 * including loading up language translation if on client side.
	 */
	public static function init ()
	{
		return;
		if (self::$_init) {
			//init already run
			return;
		}
		self::$_init = true;
		$view = geoView::getInstance();
		
		//first, add the JS needed
		$pre = (defined('IN_ADMIN'))? '../' : '';
		$view->addJScript($pre.'js/calendarview.js');
		
		if (!defined('IN_ADMIN')) {
			//add text stuff
			DataAccess::getInstance()->get_text(false,59);
			
			$tpl = new geoTemplate(geoTemplate::SYSTEM, 'other');
			
			$view->addTop($tpl->fetch('calendar_js.tpl'));
		}
	}
	
	/**
	 * Used for YYYYMMDD dates as they are stored in the DB for "date" type
	 * fields, converts to what is used in Calendar input field, YYYY-MM-DD
	 * @param string $date YYYYMMDD formatted date
	 * @return string YYYY-MM-DD formated date
	 */
	public static function toInput ($date)
	{
		if (!$date || strlen($date)!=8) {
			return '';
		}
		//from YYYYMMDD to YYYY-MM-DD
		return substr($date, 0, 4).'-'.substr($date,4,2).'-'.substr($date,6);
	}
	
	/**
	 * Used for YYYY-MM-DD dates to convert to YYYYMMDD to be stored in the DB.
	 * Also does a little cleaning to make sure no non-numeric inputs are entered.
	 *
	 * @param string $date YYYY-MM-DD formated date
	 * @return string YYYYMMDD formated date
	 */
	public static function fromInput ($date)
	{
		//why make it complicated, stripping out non-numeric chars serves to
		//get rid of dashes, and also sanitizes the input!
		//Note that this takes care of when fields still show value of YYYY-MM-DD,
		//it makes it blank.
		$date = preg_replace('/[^0-9]*/','',$date);
		
		if (strlen($date)!==8) {
			//wrong number of chars, make it blank
			return '';
		}
		
		return $date;
	}
	
	/**
	 * Formats the given date into "pretty" date to be displayed in various places
	 * in the software.
	 * 
	 * @param string $date The date in YYYYMMDD format
	 * @param bool $shortFormat
	 */
	public static function display ($date, $shortFormat = false)
	{
		if (strlen($date)!==8) {
			//not proper format...
			return '';
		}
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);
		
		$timestamp = mktime(12, 0, 0, $month, $day, $year);
		
		$db = DataAccess::getInstance();
		$format = $db->get_site_setting('date_field_format'.(($shortFormat)? '_short':''));
		
		return date ($format, $timestamp);
	}
}