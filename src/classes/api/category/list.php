<?php

//getListing.php
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
## ##    7.5.3-36-gea36ae7
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}
//get a list of all categories (optionally starting from a given top-level category (the 'parent'))

if (isset($args['parent']) && !is_numeric($args['parent'])) {
    return $this->failure('Error:  not a valid category parameter.');
}

/* To get the entire tree including subcategories, could use this:
 *
 * require_once(CLASSES_DIR.'site_class.php');
 * $site = Singleton::getInstance('geoSite');
 * return_type = 3 makes this spit back just the options array (no dropdown html)
 * $categories = $site->get_category_dropdown('xml',$params['parent'],1,0,'',3);
 * //results in:
 * //$categories = array(
 * //		'value' => category id#
 * //		'label' => category name
 * //);
*/

$db = DataAccess::getInstance();
$parent = ($args['parent']) ? $args['parent'] : 0;
$sql = "SELECT lang.category_name as name, lang.category_id as id FROM " . geoTables::categories_table . " as cat, " .
geoTables::categories_languages_table . " as lang WHERE cat.category_id=lang.category_id AND cat.parent_id = ? ";


//check for hidden categories
$hiddenCategories = $db->get_site_setting('api_hidden_categories');
if ($hiddenCategories) {
    $hiddenCategories = explode(',', $hiddenCategories);
    foreach ($hiddenCategories as $cat) {
        $cat = intval(trim($cat));
        if ($cat) {
            $sql .= " AND cat.category_id != $cat ";
        }
    }
}

$sql .= " AND lang.language_id = ? ORDER BY cat.display_order ASC";

$result = $db->Execute($sql, array($parent, $db->getLanguage()));
if (!$result) {
    return $this->failure('Error: database error');
}
$categories = array();
for ($i = 0; $cat = $result->FetchRow(); $i++) {
    $categories[$i] = array(
                'value' => $cat['id'],
                'label' => geoString::fromDB($cat['name'])
    );
}
return $categories;
