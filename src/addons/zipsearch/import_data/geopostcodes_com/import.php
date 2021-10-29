<?php
//addons/zipsearch/import_data/geopostcodes_com/import.php
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

class zipsearch_import_geopostcodes_com extends zipsearch_import_parent
{
	/**
	 * Human readable date for last time the data was updated (or date of when the data was
	 * first added to list of stuff that could be imported)
	 * 
	 * @var string
	 */
	const lastUpdated = "";
	
	const link = "<a href='http://www.geopostcodes.com/' onclick='window.open(this.href); return false;'>geopostcodes.com</a>";
	
	/**
	 * The order for this data type import
	 * 
	 * @var int
	 */
	const order = 1000;
	
	/**
	 * The order in which to display this import type.
	 * @return int
	 */
	public function getOrder()
	{
		return self::order;
	}
	
	/**
	 * Whether or not to disable the check-box on the list of imports to run.
	 * @return bool
	 */
	public function disableCheck ()
	{
		$steps = $this->getSteps();
		return ($steps[1] === 'none');
	}
	
	/**
	 * The type, should be the folder's name
	 * @return string
	 */
	public function getType()
	{
		return 'geopostcodes_com';
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
		$info = "Import Data purchased from <strong>".self::link."</strong>.";
		if (defined('IAMDEVELOPER')) {
			$info .= "<br /><br /><strong>Developer Info:</strong> No import data provided with addon, data must be purchased from site and uploaded to data folder.";
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
		//let parent scan the data dir
		$steps = parent::getSteps();
		
		if (!count($steps)) {
			$steps[1]='none';
		}
		return $steps;
	}
	
	public function getTooltip ($steps)
	{
		$deleteOld = ($steps[1]==='none')? '' : '(If you are updating post code data and 
		there are previously uploaded files in the folder, delete the old files and upload the new ones)';
		$updateSteps = '';
		if ($steps[1] !== 'none') {
			$updateSteps = "
			<br /><br />
			<strong>Update Data Instructions:</strong>
			<ol><li>Download the updated postal code data CSV file(s) from ".self::link.".</li>
			<li>Delete any files previously uploaded, and upload the new CSV file(s) to the folder
			in step 3 above.</li>
			<li>Run this import tool to import the updated data.</li>
			</ol>";
		}
		
		$label = "This option allows you to import zip data purchased from the site ".self::link.".
		<br /><br />Instructions to use data from ".self::link.":
		<ol><li>Purchase postal codes for countries desired from ".self::link.".</li>
		<li>Download the purchased data file(s) in CSV format to your local computer, and save somewhere for safe keeping.</li>
		<li style='white-space: nowrap;'>Upload the CSV data file(s) to the following folder on your site (this folder will already exist):
		<br /><br /><strong>addons/zipsearch/import_data/geopostcodes_com/data/</strong>
		<br /><br />
		</li>
		<li>Use the import on the page you are currently viewing to import the data.
		</ol>
		$updateSteps
		<br />
		Note that we did not create, do not own, and are in no way affiliated with ".self::link.", we just have an import routine
		that is capable of importing postal data purchased from that site.
		<br /><br />
		<strong>Warning:</strong>  <strong>Canada</strong> postal code data from geopostcodes.com is <strong>not compatible</strong>.
		Most other countries should work, but not all countries have been tested.  If you run into
		problems with imported data from this site, contact us at support@geodesicsolutions.com
		or start a support ticket on http://geodesicsolutions.com.<br /><br />";
		
		return $label;
	}
	
	/**
	 * The label as displayed in the admin panel, something like US Zip Codes.
	 * 
	 * @return string
	 */
	public function getLabel ()
	{
		$steps = $this->getSteps();
		$label = "CSV Data file(s) purchased from ".self::link.geoHTML::showTooltip('Data from geopostcodes.com', $this->getTooltip($steps), 1, true);
		
		if (isset($_GET['step'])) {
			//don't show all extra info
			return $label;
		}
		
		if ($steps[1]==='none') {
			$label .= " <strong style='color: red;'>(Option Disabled: No import files found.)</strong>";
		}
		
		$tabed = "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ";
		
		if ($steps[1]!=='none') {
			$label .= "<br />{$tabed}<strong>".self::link." file(s) found:</strong> ".implode(', ',$steps);
		}
		
		return $label;
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
		$delimiter = ';';
		$enclosure = '"';
		
		$file = geoFile::getInstance('zipsearch');
		if ($step==='none') {
			return "<br /><p class='page_note_error'><strong>No Data Found!</strong>  Upload any data files purchased from ".self::link." 
			to the folder <strong>addons/zipsearch/import_data/geopostcodes_com/data/</strong>, then
			<strong>Refresh this page</strong> to import that data.<br /><br />
			Note that the data purchased from ".self::link." is purchased <strong>per-site</strong>,
			so it is not able to be distributed with the Zip Import Addon.</p>";
		}
		
		$filename = $file->absolutize('geopostcodes_com/data/'.$step);
		
		$handle = fopen($filename, "r");
		
		if (!$handle) {
			return "<p class='page_note_error'>Error reading import file, cannot import data!</p>";
		}
		
		//get first line...  Note that first line is not encapsulated.
		$legend = array_flip(fgetcsv($handle, 0, $delimiter, $enclosure));
		
		$count = 0;
		$db = DataAccess::getInstance();
		
		$query = $db->Prepare("INSERT INTO `geodesic_zip_codes` (`zipcode`, `latitude`, `longitude`) VALUES (?, ?, ?)");
		
		while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== FALSE) {
			if (count($data) < 3) {
				//something wrong
				continue;
			}
			$zip = $data[$legend['postcode']];
			$lat = $data[$legend['latitude']];
			$long = $data[$legend['longitude']];
			$this->addPostcode($zip, $lat, $long);
		}
		
		fclose ($handle);
		$return = '<br /><br />Imported <strong>'.$this->newCodes.'</strong> postal code entries from data file (<strong>'.$step.'</strong>).';
		
		if ($this->dupCodes) {
			$return .= '<br /><br />'.$this->dupCodes.' duplicate postcode entries were skipped.';
		}
		return $return;
	}
}
