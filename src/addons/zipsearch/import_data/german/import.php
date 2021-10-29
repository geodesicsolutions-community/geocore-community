<?php

//addons/zipsearch/import_data/german/import.php
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

class zipsearch_import_german extends zipsearch_import_parent
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
    const order = 500;

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
        return 'german';
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
        $info = "German Postal Code Data, last updated " . self::lastUpdated . ".<br /><br />
		<strong>Warning:</strong>  Conflicts with US zip data, do not try to use on same site as 
		US zip data or you will have unexpected results.";
        if (defined('IAMDEVELOPER')) {
            $info .= "<br /><br /><strong>Developer Info:</strong> Source unknown.";
        }
        return $info;
    }

    /**
     * The label as displayed in the admin panel, something like US Zip Codes.
     *
     * @return string
     */
    public function getLabel()
    {
        return "Germany";
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
        $enclosure = "'";

        $file = geoFile::getInstance('zipsearch');


        $filename = $file->absolutize('german/data/' . $step);

        $handle = fopen($filename, "r");

        if (!$handle) {
            return "<p class='page_note_error'>Error reading import file, cannot import data!</p>";
        }

        //get first line...  Note that first line is not encapsulated.
        $legend = array (
            'postalcode' => '0',
            'latitude' => '1',
            'longitude' => '2'
        );

        while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
            if (count($data) < 3) {
                //something wrong
                continue;
            }
            $zip = $data[$legend['postalcode']];
            //in data, it uses , for decimal...
            $lat = $data[$legend['latitude']];
            $long = $data[$legend['longitude']];
            $this->addPostcode($zip, $lat, $long);
        }

        fclose($handle);

        $return = '<br /><br />Imported <strong>' . $this->newCodes . '</strong> postal code entries.';

        if ($this->dupCodes) {
            $return .= '<br /><br />' . $this->dupCodes . ' duplicate postcode entries were skipped.';
        }

        return $return;
    }
}
