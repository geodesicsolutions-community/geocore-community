<?php
//Number.class.php
/**
 * This has a class in it.  Classes are usefull for schooling purposes.  If you
 * couldn't guess, it has to do with manipulating numbers.
 * 
 * @package System
 * @since Version 4.0.0
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
## ##    7.6.3-112-g2dd4d21
## 
##################################


/**
 * Utility functions relating to number conversion/manipulation/validation ect.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoNumber
{
	/**
	 * Used internally
	 * @internal
	 */
	private static $_number_format, $_hide_cents;
	
	const EARTH_RADIUS_KM = 6378.1;
	const EARTH_RADIUS_M = 3963.1676;
	
	const UNITS_MILES = 'M';
	const UNITS_KM = 'km';
	
	/**
	 * Figure out the 2nd latitudal point, given origin lat & long (in decimal
	 * degrees), the distance, the bearing (in degrees), and the unit of measurement to use.
	 * 
	 * @param float $lat1 origin latitude, in decimal degrees
	 * @param float $long1 origin longitude, in decimal degrees
	 * @param float $distance The distance, in the units specified
	 * @param int $deg The bearing angle, in degrees
	 * @param string $units Either geoNumber::UNITS_MILES for miles, or geoNumber::UNITS_KM
	 *   for using kilometers.
	 * @return float The second latitudal point, in degrees
	 * @since Version 5.2.0
	 */
	public static function lat2 ($lat1, $long1, $distance, $deg, $units = self::UNITS_MILES)
	{
		//The formula we will be using to find the second latitude point:
		//lat2 = asin(sin(lat1)*cos(d/R) + cos(lat1)*sin(d/R)*cos(??))
		//Where R is radius of the earth in the unit of measurment desired
		
		//needs to be in radians
		$lat1 = deg2rad($lat1);
		//lol we don't actually use $long1, just accepting it for completness sake...
		//so no need to convert to radians.
		
		$d = $distance;
		$R = ($units==self::UNITS_KM)? self::EARTH_RADIUS_KM : self::EARTH_RADIUS_M;
		$angle = deg2rad($deg);
		
		$lat2 = asin(sin($lat1)*cos($d/$R) + cos($lat1)*sin($d/$R)*cos($angle));
		
		return rad2deg($lat2);
	}
	
	/**
	 * Figure out the 2nd longitudal point, given the origin lat & long, the distance,
	 * the bearing (in degrees), and the unit of measurement to use.
	 * 
	 * @param float $lat1 The origin latitude, in decimal degrees
	 * @param float $long1 The origin longitude, in decimal degrees
	 * @param float $distance
	 * @param int $deg
	 * @param string $units
	 * @return float The 2nd long point in decimal degrees
	 * @since Version 5.2.0
	 */
	public static function long2 ($lat1, $long1, $distance, $deg, $units = self::UNITS_MILES)
	{
		//To calculate long2, using the following formula:
		//lon2 = lon1 + atan2(sin(??)*sin(d/R)*cos(lat1), cos(d/R)???sin(lat1)*sin(lat2))
		
		$d = $distance;
		$R = ($units==self::UNITS_KM)? self::EARTH_RADIUS_KM : self::EARTH_RADIUS_M;
		$angle = deg2rad($deg);
		$lat2 = self::lat2($lat1,$long1,$distance,$deg,$units);
		
		//needs to be in radians
		$lat1 = deg2rad($lat1);
		$lat2 = deg2rad($lat2);
		$long1 = deg2rad($long1);
		
		$long2 = $long1 + atan2(sin($angle)*sin($d/$R)*cos($lat1), cos($d/$R)-sin($lat1)*sin($lat2));
		
		return rad2deg($long2);
	}
	
	/**
	 * Figure out distance between points given lat and long for 2 points
	 * 
	 * @param float $lat1
	 * @param float $long1
	 * @param float $lat2
	 * @param float $long2
	 * @param string $units
	 * @return float
	 * $since Version 5.2.0
	 */
	public static function distanceBetweenPoints ($lat1, $long1, $lat2, $long2, $units = self::UNITS_MILES)
	{
		//we'll use the spherical law of cosines to figure out the distance, the formula:
		//d = acos(sin(lat1)*sin(lat2)+cos(lat1)*cos(lat2)*cos(long2???long1))*R
		
		$R = ($units==self::UNITS_KM)? self::EARTH_RADIUS_KM : self::EARTH_RADIUS_M;
		
		//needs to be in radians
		$lat1 = deg2rad($lat1);
		$lat2 = deg2rad($lat2);
		$long1 = deg2rad($long1);
		$long2 = deg2rad($long2);
		
		return acos(sin($lat1)*sin($lat2)+cos($lat1)*cos($lat2)*cos($long2-$long1))*$R;
	}
	
	/**
	 * Takes a string and converts it into a number, taking into consideration the 
	 * site-wide number format setting.
	 *
	 * @param string $number
	 * @param bool $allowNegative If true, will allow negative numbers (param added
	 *   in Version 5.1.1)
	 * @return float|int
	 */
	public static function deformat ($number, $allowNegative = false)
	{
		if (!isset(self::$_number_format)) {
			//get the number format, but then save it locally so we don't
			//have to keep getting it over and over.
			$db = DataAccess::getInstance();
			self::$_number_format = $db->get_site_setting('number_format');
		}
		
		switch (self::$_number_format) {
			case 1:
				# European, . for thousands, comma for decimal
				$number=str_replace(".","",$number);
				$number=str_replace(",",".",$number);
				break;
			case 2:
				# Japanese...no decimal point
				$number=str_replace(",","",$number);
				$number=str_replace(".","",$number);
			case 0:
			default:
				# American, comma for thousands, dot for decimal
				$number = str_replace(',','',$number);
				break;
		}
		//remove anything that isn't number-like, in case they entered $123
		//or whatever the currency is for them.
		$search = ($allowNegative)? '/[^-0-9.]*/' : '/[^0-9.]*/';
		
		$number = preg_replace($search,'',$number);
		
		//change it to float.  We used to use int if no decimal point, but that limits
		//numbers to be no more than 2147483647 (or 9223372036854775807 in 64bit)
		$number = floatval($number);
		//fix weird problems with floating point
		$number = round($number, 4);
		
		return $number;
	}
	
	/**
	 * displays a number in a localized format.
	 * useful mainly for compatibility with non-american number formats, since MySQL stores all floats with . decimal points
	 * could also be used with american numbers and 2nd param TRUE to strip commas
	 *
	 * @param string $number expected to be in American (i.e. db-native) format (e.g. 1,038.23)
	 * @param bool $noGroup if this is true, the returned number will not be grouped by thousands (i.e. no commas in American format)
	 * @return float|int
	 */
	public static function format ($number, $noGroup=false)
	{
		if (!isset(self::$_number_format) || !isset(self::$_hide_cents)) {
			//set settings locally to speed up multiple calls to this method.
			$db = DataAccess::getInstance();
			self::$_number_format = $db->get_site_setting('number_format');
			self::$_hide_cents = $db->get_site_setting('hide_cents');
		}
		//number of decimal places, if using euro or american formats.
		$decimals = 2;
		if ((int)$number == (float)$number) {
			//int of num and float of num are "lazy equal" so it must be an int
			$number = (int)$number;
			if (self::$_hide_cents) {
				//no cents, and set to hide cents when cents are 0
				$decimals = 0;
			}
		} else {
			//float and int are different values, therefore number is float (has cents on it)
			$number = (float)$number;
		}
		
		switch(self::$_number_format){
			case 1:
				# European -- swap dots and commas
				if($noGroup) {
					$number = number_format($number, $decimals, ',', '');
				} else {
					$number = number_format($number, $decimals, ',', '.');
				}
				break;
			case 2:
				# Japanese -- no decimals, but thousands grouping
				$number = number_format($number, 0, '', ',');
				break;
			case 3:
				# all formatting stripped -- pure integer
				$number = number_format($number, 0, '', '');
				break;
			case 0:
			default:
				# American
				if($noGroup) {
					$number = number_format($number, $decimals, '.', '');
				} else {
					$number = number_format($number, $decimals, '.', ',');
				}
				break;
		}
		
		return $number;
	}
	
	/**
	 * Formats a phone number according to site settings
	 *
	 * @param string $phone_number The number to be formatted
	 * @return string the formatted phone number
	 */
	public static function phoneFormat($phone_number)
	{
		$db = DataAccess::getInstance();
		$phone_number = trim($phone_number);
		if (!$phone_number) {
			return '';
		}
		$ereg1 = $db->get_site_setting("phone_regex_piece1");
		$ereg2 = $db->get_site_setting("phone_regex_piece2");
		$ereg3 = $db->get_site_setting("phone_regex_piece3");
		$ereg_setting = "/^([0-9]{".$ereg1."})([0-9]{".$ereg2."})([0-9]{".$ereg3."})$/";

		$PhoneNumbers = preg_replace( "/[^0-9]/", "", $phone_number ); // Strip out non-numerics
		if (preg_match($ereg_setting, $PhoneNumbers, $NumberParts)) {
			$format = $db->get_site_setting("phone_format");
			switch($format)
			{
				case 0: return $phone_number; break;
				case 1: return "(" . $NumberParts[1] . ") " . $NumberParts[2] . "-" . $NumberParts[3]; break;
				case 2: return $NumberParts[1] . "-" . $NumberParts[2] . "-" . $NumberParts[3]; break;
				case 3: return $NumberParts[1] . "." . $NumberParts[2] . "." . $NumberParts[3]; break;
				case 4: return "(" . $NumberParts[1] . ") " . $NumberParts[2] . "." . $NumberParts[3]; break;
				case 5: return $NumberParts[1] . " " . $NumberParts[2] . " " . $NumberParts[3]; break;
				case 6: return $NumberParts[1] . $NumberParts[2] . $NumberParts[3]; break;
				default: return "(" . $NumberParts[1] . ") " . $NumberParts[2] . "-" . $NumberParts[3]; break;
			}
		}
		//if falls through to here, return no formatting
   		return $phone_number;
	}
	
	/**
	 * Converts file size to human readable format, like 1.2 MB or 500.23 KB.
	 * 
	 * @param int $filesize The filesize, in Bytes.
	 * @since Version 6.0.0
	 */
	public static function filesizeFormat ($filesize)
	{
		//force filesize to be integer
		$filesize = (int)$filesize;
		
		$totalSize = $filesize.' Bytes';
		
		if ($filesize > 1024) {
			$kb = $filesize/1024;
			$totalSize = round($kb,2).' KB';
			if ($kb > 1024) {
				$mb = $kb/1024;
				$totalSize = round($mb,2).' MB';
				if ($mb > 1024) {
					$gb = $mb/1024;
					$totalSize = round($gb,2).' GB';
					if ($gb > 1024) {
						$tb = $gb/1024;
						$totalSize = round($tb,2).' TB';
					}
				}
			}
		}
		return $totalSize;
	}
}
