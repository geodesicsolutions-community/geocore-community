<?php

//getListing.php


if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//search among the listings in a specific category for a specific string
//NOTE: either or both of the category or search string may be blank/not present
//if neither are passed in, this should return ALL live listings

//NOTE THE SECOND: search_string should be url-encoded before being sent to this api call

//workaround for weird thing in some versions of iOS that replaces "" with "(null)"
foreach ($args as $key => $val) {
    if ($val === '(null)') {
        $args[$key] = '';
    }
}

$db = DataAccess::getInstance();
$searchStr = trim($args['search_string']);

//optionally, search in a specific category
$category = trim($args['category']);
//or only for featured listings
$onlyFeatured = ($args['onlyFeatured'] == 1);
//or only in a certain region
$inRegion = ($args['region']) ? intval(trim($args['region'])) : false;

//also handle pagination of search results -- if these aren't present, assume it's an older client trying to access all data at once
$page = trim($args['page']);
$page = (is_numeric($page) && $page > 0) ? $page : false;
$numResults = trim($args['numResults']);
$numResults = (is_numeric($numResults) && $numResults > 0) ? $numResults : false;

$sql = "SELECT * FROM `geodesic_classifieds` WHERE `live` = 1 ";

if ($category && is_numeric($category)) {
    //searching in a specific category (and its subcategories)
    //if this is not present, will search all categories
    $subQ = "SELECT * FROM `geodesic_listing_categories` WHERE `geodesic_listing_categories`.`listing`=`geodesic_classifieds`.`id` AND `geodesic_listing_categories`.`category`=" . (int)$category;

    //add it to the main query
    $sql .= " AND EXISTS ($subQ) ";
}

//check for hidden categories
$hiddenCategories = $db->get_site_setting('api_hidden_categories');
if ($hiddenCategories) {
    $hiddenCategories = explode(',', $hiddenCategories);
    $catList = array();
    foreach ($hiddenCategories as $cat) {
        $cat = intval(trim($cat));
        if ($cat && !in_array($cat, $catList)) {
            $catList[] = $cat;
        }
    }
    if ($catList) {
        $hiddenCategories = implode(', ', $catList);
        $subQ = "SELECT * FROM `geodesic_listing_categories` WHERE `geodesic_listing_categories`.`listing`=`geodesic_classifieds`.`id` AND `geodesic_listing_categories`.`category` IN ($hiddenCategories)";
        $sql .= "AND NOT EXISTS ($subQ) ";
    }
}

if ($onlyFeatured) {
    $sql .= " AND (`featured_ad`=1 OR `featured_ad_2`=1 OR `featured_ad_3`=1 OR `featured_ad_4`=1 OR `featured_ad_5`=1) ";
}

if ($inRegion) {
    $sql .= " AND `id` IN (
				SELECT `listing` FROM `geodesic_listing_regions` WHERE `region` = '" . $inRegion . "'
			) ";
}

if (strlen($searchStr) > 0) {
    //search for a specific string
    //if this is not present, should pull all listings in the specified category

    $searchTerms = explode(' ', geoString::fromDB($searchStr));
    $termSql = array();
    foreach ($searchTerms as $term) {
        $conditions = array();
        if ($int = intval($term)) {
            //this search term is numeric -- check it against zipcodes
            $conditions[] = "`location_zip` = '" . $term . "'";
        }

        $title = geoString::fromDB($term); //data comes in urlencoded -- undo that first
        //format string for searching in the database
        $title = geoString::specialChars($title);
        //set up to escape hardcoded SQL-specific search characters
        $find = array ('%','_');
        $replace = array ('\%','\_');
        $title = str_replace($find, $replace, geoString::toDB($title));

        //search in the title
        $conditions[] = "`title` LIKE '%" . $title . "%'";

        //note: description requires one less specialChars pass than other fields
        //so use $searchStr instead of $title
        $description = str_replace($find, $replace, geoString::toDB($term));
        $conditions[] = "`description` LIKE '%" . $description . "%'";

        //search `search_text` field (category-specific questions and listing tags) -- formatted the same as title
        $conditions[] = "`search_text` LIKE '%" . $title . "%'";

        if (geoPC::is_ent()) {
            for ($i = 1; $i < 20; $i++) {
                //search site-wide optional fields -- formatted the same as title
                $conditions[] = "`optional_field_$i` LIKE '%" . $title . "%'";
            }
        }
        $termSql[] = " (" . implode(' OR ', $conditions) . ") ";
    }
    $sql .= " AND (" . implode(' AND ', $termSql) . ") ";
}

$countSql = str_replace('SELECT * FROM', 'SELECT COUNT(`id`) FROM', $sql);

$sql .= " ORDER BY `date` DESC ";

if ($page && $numResults) {
    $sql .= " LIMIT " . (($page - 1) * $numResults) . "," . $numResults . " ";
}

$result = $db->Execute($sql);
if (!$result) {
    //db error:
    return $this->failure('search error:' . $db->ErrorMsg());
}

$totalResults = $db->GetOne($countSql);

$return = array();
while ($line = $result->FetchRow()) {
    //get the lead thumbnail for this listing
    $sql = "SELECT image_url, thumb_url FROM geodesic_classifieds_images_urls WHERE classified_id = ? ORDER BY display_order ASC LIMIT 1";
    $imgRow = $db->GetRow($sql, array($line['id']));
    $thumbnail = ($imgRow['thumb_url']) ? $imgRow['thumb_url'] : $imgRow['image_url']; //fallback on main image if thumb not set
    $thumbnail = geoImage::absoluteUrl($thumbnail);

    //figure out which price to show
    if ($line['item_type'] == 1) {
        $price = $line['price'];
    } else {
        $price = ($line['buy_now_only'] == 1) ? $line['buy_now'] : max($line['minimum_bid'], $line['starting_bid']);
    }
    $price = geoString::displayPrice($price);

    //assemble data to send to requester
    $listing = array(
                'listingId' => $line['id'],
                'title' => geoString::fromDB($line['title']),
                'description' => geoString::fromDB($line['description']),
                'price' => $price,
                'thumbnail' => $thumbnail,
                'totalResults' => $totalResults, //NOT the optimum place to put this, but it goes here for now so that old apps can still use this API call
    );
    $return[] = $listing;
}

return $return;
