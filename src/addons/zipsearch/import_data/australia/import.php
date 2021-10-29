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

class zipsearch_import_australia extends zipsearch_import_parent
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
    const order = 400;

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
        return 'australia';
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
        $info = "Australian Postal Codes,
		last updated " . self::lastUpdated . ".";
        if (defined('IAMDEVELOPER')) {
            $info .= "<br /><br /><strong>Developer Info:</strong> Source Unkown.";
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
        return "Australia";
    }
}
