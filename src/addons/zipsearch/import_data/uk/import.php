<?php

//addons/zipsearch/import_data/uk/import.php

class zipsearch_import_uk extends zipsearch_import_parent
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
    const order = 200;

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
        return 'uk';
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
        $info = "UK Postcode District Data, was
		last updated " . self::lastUpdated . ".";
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
        return "United Kindom";
    }
}
