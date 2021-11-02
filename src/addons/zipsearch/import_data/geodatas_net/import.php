<?php

//addons/zipsearch/import_data/geodatas_net/import.php

class zipsearch_import_geodatas_net extends zipsearch_import_parent
{
    /**
     * Human readable date for last time the data was updated (or date of when the data was
     * first added to list of stuff that could be imported)
     *
     * @var string
     */
    const lastUpdated = "";

    const link = "<a href='http://www.geodatas.net/' onclick='window.open(this.href); return false;'>geodatas.net</a>";

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
    public function disableCheck()
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
        return 'geodatas_net';
    }

    /**
     * Gets the human readable date for last time the import data was updated
     * @return string
     */
    public function getLastUpdated()
    {
        return self::lastUpdated;
    }

    /**
     * Get info about this import data, such as the source of the data.  Also include
     * anything that would be useful for developers is IAMDEVELOPER is defined.
     *
     * @return string
     */
    public function getInfo()
    {
        $info = "Import Data purchased from <strong>" . self::link . "</strong>.";
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
    public function getSteps()
    {
        //let parent scan the data dir
        $steps = parent::getSteps();

        if (!count($steps)) {
            $steps[1] = 'none';
        }
        return $steps;
    }

    public function getTooltip($steps)
    {
        $deleteOld = ($steps[1] === 'none') ? '' : '(If you are updating post code data and
		there are previously uploaded files in the folder, delete the old files and upload the new ones)';
        $updateSteps = '';
        if ($steps[1] !== 'none') {
            $updateSteps = "
			<br /><br />
			<strong>Update Data Instructions:</strong>
			<ol><li>Download the updated postal code data CSV file(s) from " . self::link . ".  If the download
			is zipped, unzip it to a location on your computer.</li>
			<li>Delete any files previously uploaded, and upload the new CSV file(s) to the folder
			in step 3 above.</li>
			<li>Run this import tool to import the updated data.</li>
			</ol>";
        }

        $label = "This option allows you to import zip data purchased from the site " . self::link . ".
		<br /><br />Instructions to use data from " . self::link . ":
		<ol><li>Purchase postal codes for countries desired from " . self::link . ".</li>
		<li>Download the purchased data file(s) in CSV format to your local computer, and save somewhere for safe keeping.</li>
		<li style='white-space: nowrap;'>Un-zip and upload the CSV data file(s) to the following folder on your site (this folder will already exist):
		<br /><br /><strong>addons/zipsearch/import_data/geodatas_net/data/</strong>
		<br /><br />
		</li>
		<li>Use the import on the page you are currently viewing to import the data.
		</ol>
		$updateSteps
		<br />
		Note that we did not create, do not own, and are in no way affiliated with " . self::link . ", we just have an import routine
		that is capable of importing postal data purchased from that site.
		<br /><br />
		<strong>Warning:</strong>  Not all countries available on the 3rd party site have been tested.<br /><br />";

        return $label;
    }

    /**
     * The label as displayed in the admin panel, something like US Zip Codes.
     *
     * @return string
     */
    public function getLabel()
    {
        $steps = $this->getSteps();
        $label = "CSV Data file(s) purchased from " . self::link . geoHTML::showTooltip('Data from geodatas.net', $this->getTooltip($steps), 1, true);

        if (isset($_GET['step'])) {
            //don't show all extra info
            return $label;
        }

        if ($steps[1] === 'none') {
            $label .= " <strong style='color: red;'>(Option Disabled: No import files found.)</strong>";
        }

        $tabed = "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ";

        if ($steps[1] !== 'none') {
            $label .= "<br />{$tabed}<strong>" . self::link . " file(s) found:</strong> " . implode(', ', $steps);
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
    public function processStep($step)
    {
        $delimiter = ';';
        $enclosure = '"';

        $file = geoFile::getInstance('zipsearch');
        if ($step === 'none') {
            return "<br /><p class='page_note_error'><strong>No Data Found!</strong>  Upload any CSV data files purchased from " . self::link . "
			to the folder <strong>addons/zipsearch/import_data/geodatas_net/data/</strong>, then
			<strong>Refresh this page</strong> to import that data.<br /><br />
			Note that the data purchased from " . self::link . " is purchased <strong>per-site</strong>,
			so it is not able to be distributed with the Zip Import Addon.</p>";
        }

        $filename = $file->absolutize('geodatas_net/data/' . $step);

        $handle = fopen($filename, "r");

        if (!$handle) {
            return "<p class='page_note_error'>Error reading import file, cannot import data!</p>";
        }

        //get first line...  Note that first line is not encapsulated.
        $legend = array_flip(fgetcsv($handle, 0, $delimiter, $enclosure));

        while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
            if (count($data) < 3) {
                //something wrong
                continue;
            }
            $zip = $data[$legend['postalcode']];
            //in data, it uses , for decimal...
            $lat = str_replace(',', '.', $data[$legend['latitude']]);
            $long = str_replace(',', '.', $data[$legend['longitude']]);
            $this->addPostcode($zip, $lat, $long);
        }

        fclose($handle);

        $return = '<br /><br />Imported <strong>' . $this->newCodes . '</strong> postal code entries from data file (<strong>' . $step . '</strong>).';

        if ($this->dupCodes) {
            $return .= '<br /><br />' . $this->dupCodes . ' duplicate postcode entries were skipped.';
        }

        return $return;
    }
}
