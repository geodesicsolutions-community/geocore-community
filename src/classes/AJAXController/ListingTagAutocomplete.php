<?php

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
## ##    7.3beta4-140-g5d0a99b
##
##################################

if (class_exists('classes_AJAX') or die()) {
}

class CLASSES_AJAXController_ListingTagAutocomplete extends classes_AJAX
{

    public function __construct()
    {
    }

    private function _cleanTag($tag)
    {
        $tag = geoFilter::cleanUrlTitle(trim($tag));

        //lowercase all tags
        $tag = strtolower($tag);

        //badword replacement
        $tag = geoFilter::badword($tag);

        return trim($tag);
    }

    public function getSuggestions()
    {
        $tags_entered = (isset($_GET['tags'])) ? trim($_GET['tags']) : '';
        $showCounts = (isset($_GET['showCounts']) && $_GET['showCounts']);

        $tags = explode(',', $tags_entered);

        $cleanPre = array();

        if (!$showCounts && strpos($tags_entered, ',') !== false) {
            //only suggest something for tag that is "entered last"
            $pre = explode(',', substr($tags_entered, 0, strrpos($tags_entered, ',')));

            //clean up pre
            foreach ($pre as $key => $val) {
                $tag = geoFilter::cleanListingTag($val);
                if (strlen($tag)) {
                    $cleanPre[] = $tag;
                }
            }
            if (count($cleanPre)) {
                $pre = implode(', ', $cleanPre) . ', ';
            } else {
                $pre = '';
            }
            $tag = geoFilter::cleanListingTag(substr($tags_entered, (strrpos($tags_entered, ',') + 1)));
        } else {
            $pre = '';
            $tag = geoFilter::cleanListingTag($tags_entered);
        }
        $matches = array ();
        if (strlen($tag) > 0) {
            //what we are searching for...
            $finding = addcslashes(geoString::toDB($tag), '\\%_') . '%';

            $db = DataAccess::getInstance();

            $lTable = geoTables::classifieds_table;
            $tTable = geoTables::tags;

            if ($showCounts) {
                //only show tags attached to "live" listings
                $rows = $db->GetAll("SELECT `tag`, COUNT(`listing_id`) as `count` FROM $tTable, $lTable WHERE $tTable.`listing_id`=$lTable.`id` AND `live`=1 AND $tTable.`tag` LIKE ? GROUP BY `tag` ORDER BY `count` DESC, `tag` ASC LIMIT 5", array($finding));
            } else {
                //not showing counts, so can show suggestions from tags even if listing is closed
                $rows = $db->GetAll("SELECT DISTINCT `tag` FROM $tTable WHERE `tag` LIKE ? ORDER BY `tag` LIMIT 5", array($finding));
            }
            if ($rows) {
                foreach ($rows as $row) {
                    $tag = geoString::fromDB($row['tag']);
                    if (in_array($tag, $cleanPre)) {
                        //already have this as a suggestion
                        continue;
                    }
                    $match['label'] = $match['value'] = $pre . $tag;
                    if ($showCounts) {
                        $match['label'] .= " ({$row['count']})";
                    }
                    $matches[] = $match;
                }
            } else {
                //to show DB error messages, un-comment the following
                //$matches[]['label'] = $db->ErrorMsg();
            }
        }

        $this->jsonHeader();
        echo $this->encodeJSON($matches);
    }
}
