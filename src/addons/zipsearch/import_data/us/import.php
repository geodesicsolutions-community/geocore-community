<?php
//addons/zipsearch/import_data/us/import.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.4.4-22-g21b6325
## 
##################################

class zipsearch_import_us extends zipsearch_import_parent
{
	/**
	 * Human readable date for last time the data was updated (or date of when the data was
	 * first added to list of stuff that could be imported)
	 * 
	 * @var string
	 */
	const lastUpdated = "August 17, 2010";
	
	/**
	 * The order for this data type import
	 * 
	 * @var int
	 */
	const order = 100;
	
	/**
	 * The order in which to display this import type.
	 * @return int
	 */
	public function getOrder()
	{
		return self::order;
	}
	
	/**
	 * The type, should be the folder's name
	 * @return string
	 */
	public function getType()
	{
		return 'us';
	}
	
	/**
	 * Gets the human readable date for last time the import data was updated
	 * @return string
	 */
	public function getLastUpdated ()
	{
		return self::lastUpdated;
	}
	
	/**
	 * Get info about this import data, such as the source of the data.  Also include
	 * anything that would be useful for developers is IAMDEVELOPER is defined.
	 * 
	 * @return string
	 */
	public function getInfo ()
	{
		$info = "US Postal Code Data obtained from http://www.free-zipcodes.com/ and was
		last updated ".self::lastUpdated.".";
		if (defined('IAMDEVELOPER')) {
			$info .= "<br /><br /><strong>Developer Info:</strong> The raw import data is split into multiple files for easier processing, using
			the linux command:<br />
\$ split --lines=10000 -a 1 zipcodes_2006.txt zipcodes_2006_";
		}
		return $info;
	}
	
	/**
	 * Get an array of steps, the index is important..  must be numeric indexes,
	 * start at 1 (NOT 0, step 0 would be skipped), and value should be useful
	 * to the processStep() function (for instance, the file name to import for
	 * this step, if there are multiple files used for the import)
	 * 
	 * @return array
	 */
	public function getSteps ()
	{
		//index for each step...  0 is used by system
		$file = geoFile::getInstance('zipsearch');
		
		$list = $file->scandir('us/data/', false);
		
		$steps = array();
		$i = 1;
		foreach ($list as $step) {
			$steps[$i] = $step;
			$i++;
		}
		return $steps;
	}
	
	/**
	 * The label as displayed in the admin panel, something like US Zip Codes.
	 * 
	 * @return string
	 */
	public function getLabel ()
	{
		return "United States";
	}
	
	/**
	 * Process the given step, importing all the data for that step.
	 * 
	 * @param mixed $step The value for the current step, the value used in the
	 *   array returned by getSteps()
	 * @return string Extra info to display for this step, such as number of
	 *   entries imported on this step or something similar.
	 */
	public function processStep ($step)
	{
		$file = geoFile::getInstance('zipsearch');
		$contents = $file->file_get_contents('us/data/'.$step);
		
		//clean up data, remove any added \r or stuff
		$contents = str_replace(array("\t","\r"),'',$contents);
		
		$rows = explode("\n",$contents);
		
		$db = DataAccess::getInstance();
		
		foreach ($rows as $row) {
			$columns = explode('||', $row);
			if (count($columns)!=6) {
				//not valid
				continue;
			}
			$this->addPostcode($columns[0], $columns[1], $columns[2]);
		}
		
		$return = '<br /><br />Imported <strong>'.$this->newCodes.'</strong> postal code entries.';
		
		if ($this->dupCodes) {
			$return .= '<br /><br />'.$this->dupCodes.' duplicate postcode entries were skipped.';
		}
		
		return $return;
	}
}
