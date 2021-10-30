<?php
//ArrayTools.class.php
/**
 * A great little file really, holding tools that relate to arrays.
 * 
 * @package System
 * @since Version 4.0.0
 */



/**
 * Utility functions for manipulating arrays
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoArrayTools {
	
	/**
	 * Used mainly by category navigation modules to sort their categories down instead of across
	 *
	 * @param array $input array to resort
	 * @param int $num_cols number of columns across (usually an admin setting)
	 * @return array input array, resorted
	 */
	public static function sortDown ($input, $num_cols)
	{
		/*
		 * 	This function is an attempt to fix some kooky math that was happening in previous attempts at this functionality.
		 *  To prevent future breakage, the current logic is given here
		 *  Make sure you understand how it works now before tinkering with it :)
		 
				basic logic: given a "coordinate" (current_row, current_col),
				count the number of "spaces" to the left of this coordinate, then add the row number
	
				Example:
					12 categories, 5 columns, alpha-sort down
	
					Original array: 0 1 2 3 4 5 6 7 8 9 A B
					
					End goal:	In order: 0 3 6 8 A 1 4 7 9 B 2 5
					0 3 6 8 A
					1 4 7 9 B
					2 5
					
				Let's say we want the value to put in coordinate (0,3) [in this example, 8].
						(REMEMBER: Coordinates are zero-indexed in both directions!)

				There are two "long" columns and one "short" column to the left of our target.
				$longOffset = 2 long columns * 3 elements of each long column = 6
				$shortOffset = 1 short column * 2 elements in each short column = 2
				$current_row = 0
				$longOffset + $shortOffset + $current_row = 6 + 2 + 0 = 8 //<-- what we're looking for! :)
				
				Getting the 9 just below is similar math, but $current_row = 1
				6 + 2 + 1 = 9

				To get the "5" at coordinate (2,1):
				$longOffset = 1 (because there is 1 full long-column (column 0) previous to it) * 3 (3 elements in a long-column) = 3
				$shortOffset = 0 (because we're not done with long columns yet, and short columns aren't even considered)
				$current_row = 2
				$longOffset + $shortOffset + $current_row = 3 + 0 + 2 = 5

		*/
		if(!is_array($input) || !intval($num_cols)) {
			//invalid function input
			trigger_error('ERROR CATNAV: invalid input to geoArrayTools::sortDown()');
			return false;
		}
		
		$total = count($input);
		
		if($total <= $num_cols) {
			//only one row, no need to do any fancy sorting -- return as-is to save CPU cycles. :)
			trigger_error('DEBUG CATNAV: input to geoArrayTools::sortDown() has only one row -- short-circuiting!');
			return $input;
		}

		$numLongCols = $total % $num_cols; // the amount of columns that will have extra. think 10 categories with 3 colums = 1 long column

		//number of elements in a long and short column
		$longColumnHeight = ceil($total / $num_cols);
		$shortColumnHeight = ($numLongCols == 0) ? $longColumnHeight : ($longColumnHeight - 1);
		
		$input_numerical = array_values( $input ); // convert associative to numeric array

		$return = array();
		
		// iterator variables
		$current_col = 0;
		$current_row = 0;
		for ( $x=0; $x < $total; $x++ ) 
		{
			if ( $current_col >= $num_cols )
			{
				$current_col = 0;
				$current_row++;
			}

			$isLongCol = ($current_col <= $numLongCols) ? true : false;

			$previousLongCols = ($isLongCol) ? $current_col : $numLongCols;
			$longOffset = $previousLongCols * $longColumnHeight; //find the number of entries in those previous long cols
			
			if($isLongCol) {
				//this is a long column -- there are no short columns yet
				$shortOffset = 0;
			} else {
				//this is a short column
				$previousShortCols = $current_col - $numLongCols;
				$shortOffset = $previousShortCols * $shortColumnHeight;
			} 
			
			$target = $longOffset + $shortOffset + $current_row; //add to current row to find index to get
			$return[] = $input_numerical[$target];
			$current_col++;
		}
		return $return;
	}
	
	
	/**
	 * Recursively check for a given value in a multi-dimensional array.  For
	 * docs on parameters, see documentation on PHP's in_array() function.
	 * 
	 * @param mixed $needle
	 * @param array $haystack
	 * @param bool $strict
	 * @return bool
	 * @since Version 5.0.0
	 */
	public static function inArray ($needle, $haystack, $strict = false)
	{
		if (!is_array($haystack)) {
			//sanity check
			return false;
		}
		if (in_array($needle, $haystack, $strict)) {
			//found
			return true;
		}
		foreach ($haystack as $val) {
			if (is_array($val) && self::inArray($needle, $val, $strict)) {
				//found recursively
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Search for and unset the given needle, from the given haystack, recursively.
	 * 
	 * See docs on PHP's array_search() for input parameters.
	 * 
	 * @param mixed $needle
	 * @param array $haystack
	 * @param bool $strict
	 * @return array|bool Returns false if invalid input entered, otherwise returns
	 *   the array with the needle recursively removed.
	 * @since Version 5.0.0 
	 */
	public static function searchAndDestroy ($needle, $haystack, $strict = false)
	{
		if (!is_array($haystack)) {
			//sanity check
			return false;
		}
		$key = array_search($needle, $haystack, $strict);
		if ($key !== false) {
			//found it!  now DESTROY it!
			unset($haystack[$key]);
		}
		
		foreach ($haystack as $key => $val) {
			if (is_array($val)) {
				//search/destroy recursively!
				$haystack[$key] = self::searchAndDestroy($needle, $val, $strict);
			}
		}
		return $haystack;
	}
	
	/**
	 * Search for and replace the given needle, from the given haystack, recursively.
	 * 
	 * See docs on PHP's array_search() for input parameters.
	 * 
	 * @param mixed $needle
	 * @param mixed $replace
	 * @param array $haystack
	 * @param bool $strict
	 * @return array|bool Returns false if invalid input entered, otherwise returns
	 *   the array with the needle recursively removed.
	 * @since Version 5.0.0 
	 */
	public static function searchAndReplace ($needle, $replace, $haystack, $strict = false)
	{
		if (!is_array($haystack)) {
			//sanity check
			return false;
		}
		$key = array_search($needle, $haystack, $strict);
		if ($key !== false) {
			//found it!  now REPLACE it!
			if ($key === $needle && !is_numeric($key) && !is_array($replace) && $replace) {
				//special case, the key = value, so replace key and value...
				unset($haystack[$key]);
				$haystack[$replace] = $replace;
			} else {
				$haystack[$key] = $replace;
			}
		}
		
		foreach ($haystack as $key => $val) {
			if (is_array($val)) {
				//search/destroy recursively!
				$haystack[$key] = self::searchAndReplace($needle, $replace, $val, $strict);
			}
		}
		return $haystack;
	}
	
	/**
	 * Converts an array into a CSV line (or lines, if array is multi-dimensional)
	 * 
	 * @param array $array
	 * @param bool $fromDB If true, will convert each string in the array using
	 *   geoString::fromDB() automatically.
	 * @return string
	 * @since Version 5.0.0
	 */
	public static function toCSV ($array, $fromDB = false)
	{
		if (!is_array($array)) {
			return '';
		}
				
		$csv = "";
		$cleanArray = array();
		foreach ($array as $entry) {
			if (is_array($entry)) {
				//multidimensional array
				$csv .= self::toCSV($entry, $fromDB).PHP_EOL;
			} else {
				if ($fromDB) {
					$entry = geoString::fromDB($entry);
				}
				$entry = str_replace('"','""', $entry);
				$entry = '"'.$entry.'"';
				$cleanArray[] = $entry;
			}
		}
		if ($cleanArray) {
			$csv .= implode(',', $cleanArray);
		}
		return $csv;
	}
	
	/**
	 * Converts all the string values in an array from the charset_from to the
	 * charset_to using the geoString::convertCharset() tool.  Does not alter
	 * any array values that are not strings.  Also works on multi-dimensional
	 * arrays.
	 * 
	 * @param array $array
	 * @param string $charset_from
	 * @param string $charset_to
	 */
	public static function convertCharset ($array, $charset_from, $charset_to)
	{
		if (is_string($array)) {
			//This is a string, so convert it using the string's method
			return geoString::convertCharset($array, $charset_from, $charset_to);
		}
		
		if (is_array($array)) {
			//step through and recursively convert each value
			$return = array();
			foreach ($array as $key => $val) {
				$return[$key] = self::convertCharset($val, $charset_from, $charset_to);
			}
			return $return;
		}
		
		//it isn't a string or an array, return the value un-touched.
		return $array;
	}
}