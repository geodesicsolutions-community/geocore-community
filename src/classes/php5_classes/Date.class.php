<?php
//Date.class.php
/**
 * Holds the geoDate utility class.
 * 
 * @package System
 * @since Version 4.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

/**
 * Holds utility method(s) for manipulating stuff dealing with dates.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoDate
{
	
	/**
	 * Formats a unix timestamp to user readable full date
	 *
	 * @param int $timestamp
	 * @param string $format Format to use, compatible with PHP's date() function.
	 * @return string date
	 * @todo Make it use the built-in settings for how to display dates on the site.
	 */
	public static function toString($timestamp,$format = "l F j o h:m:s A")
	{
		if($timestamp) return date($format,$timestamp);
	}
}