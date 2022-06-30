<?php

/**
 * Holds the geoCategory class.
 *
 * @package System
 * @since Version 4.0.0
 */

/**
 * Utility class that holds various methods to do stuff with categories in the system.
 * @package System
 * @since Version 4.0.0
 * @todo Clean this class up a bunch and optimize it.
 */
class geoCategory
{
    /**
     * Used internally
     * @internal
     */
    private static $_instance;

    /**
     * Gets an instance of the geoCategory class.
     * @return geoCategory
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            $c = __class__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    /**
     * Get the parent values for the specified category ID in an array.
     *
     * @param int $category_id
     * @param bool $use_bottom If true, will also return the requested cat info
     *   as part of the array of parent values, handy for things like displaying
     *   the full breadcrumb.
     * @param bool $cleanName If true, will clean the category_name value for each category
     * @return boolean|array Returns an array with all the parent cat info, the
     *   array index is the parent cat level and the info is same as returned
     *   by {@see geoCategory::getInfo()}
     * @since Version 7.4.0
     */
    public function getParents($category_id, $use_bottom = false, $cleanName = false)
    {
        $category_id = (int)$category_id;
        if (!$category_id) {
            //failsafe
            return false;
        }

        $categories = array();
        $catInfo = $this->getInfo($category_id, $cleanName);

        while ($catInfo) {
            if ($catInfo['id'] != $category_id || $use_bottom) {
                //only add parents
                $categories[] = $catInfo;
            }
            if ($catInfo['parent']) {
                $catInfo = $this->getInfo($catInfo['parent'], $cleanName);
            } else {
                $catInfo = false;
            }
        }

        return array_reverse($categories);
    }

    /**
     * Gets info about a category
     *
     * @param int $category_id
     * @param bool $cleanName If true, will clean the category_name index
     * @return array|bool Returns info about value, or false on error.
     * @since Version 7.4.0
     */
    public function getInfo($category_id, $cleanName = false)
    {
        //Note: we don't ask for the leveled field ID here since that is unique
        //to the value ID.
        $db = DataAccess::getInstance();
        $category_id = (int)$category_id;
        $language_id = (int)geoSession::getInstance()->getLanguage();

        $row = $db->GetRow("SELECT * FROM " . geoTables::categories_table . " v, " . geoTables::categories_languages_table . " l WHERE l.category_id=v.category_id AND l.language_id={$language_id} AND v.category_id=?", array($category_id));

        $row = $this->normInfo($row);
        //unescape name
        $row['name'] = geoString::fromDB($row['name']);
        if ($cleanName) {
            //some places need it raw, others need it already cleaned...
            $row['category_name'] = $row['name'];
        }
        $row['description'] = geoString::fromDB($row['description']);
        $row['category_image'] = geoString::fromDB($row['category_image']);

        $row['excluded_list_types'] = array();
        //get excluded list types
        $all = $db->Execute("SELECT * FROM " . geoTables::category_exclusion_list . " WHERE `category_id`=?", array($category_id));
        if ($all) {
            foreach ($all as $exclude_row) {
                $row['excluded_list_types'][$exclude_row['listing_type']] = 1;
            }
        }
        return $row;
    }

    /**
     * Normalize the info for a category, so that it has names that match leveled fields
     *
     * @param array $info
     * @return array
     * @since Version 7.4.0
     */
    public function normInfo($info)
    {
        //do a few things to normalize with leveled fields...
        $info['parent'] = $info['parent_id'];
        $info['id'] = $info['category_id'];
        $info['name'] = $info['category_name'];
        return $info;
    }


    /**
     * -----------------------------------------------------------------------
     *
     * OLD STUFF!!
     *
     * -----------------------------------------------------------------------
     */


    /**
     * Internal
     * @internal
     */
    private static $_getInfoCache;
    /**
     * Get basic info about given category.  As of version 7.4, will return false
     * if category requested is disabled or any of it's parents are disabled.
     *
     * @param int $category_id
     * @param int $language_id
     * @return array
     */
    public static function getBasicInfo($category_id = 0, $language_id = 0)
    {
        $db = DataAccess::getInstance();
        if (!$category_id) {
            //TODO: get this text from the DB somewhere...
            return array('category_name' => "Main");
        }
        if (!$language_id) {
            $language_id = $db->getLanguage();
        }
        if (isset(self::$_getInfoCache[$category_id][$language_id])) {
            return self::$_getInfoCache[$category_id][$language_id];
        }

        $sql = "SELECT l.`category_name`,l.`seo_url_contents`,l.`category_cache`,l.`cache_expire`,l.`description`,c.`parent_id` FROM " . geoTables::categories_languages_table . " l, " . geoTables::categories_table . " c WHERE l.category_id=c.category_id AND c.enabled='yes' AND c.`category_id` = ? AND l.language_id = ? LIMIT 1";
        $result = $db->Execute($sql, array($category_id, $language_id));
        if (!$result || $result->RecordCount() == 0) {
            trigger_error('ERROR CATEGORY SQL: Cat not found for id: ' . $category_id . ', Sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            return false;
        }
        $show = $result->FetchRow();
        if ($show['parent_id'] > 0 && !geoCategory::isEnabled($show['parent_id'])) {
            //one of the parents is not enabled
            trigger_error('ERROR CATEGORY: One of the category parents is disabled');
            return false;
        }
        $show['category_name'] = geoString::fromDB($show['category_name']);
        $show['description'] = geoString::fromDB($show['description']);
        $show['seo_url_contents'] = geoString::fromDB($show['seo_url_contents']);
        //save it, so we don't query the db a bunch
        self::$_getInfoCache[$category_id][$language_id] = $show;
        return $show;
    }

    /**
     * Check if the given category and parent categories are enabled or not.
     *
     * @param int $category_id
     * @return boolean
     * @since Versio 7.4.0
     */
    public static function isEnabled($category_id)
    {
        $db = DataAccess::getInstance();

        $category_id = (int)$category_id;

        do {
            $row = $db->GetRow("SELECT `parent_id`, `enabled` FROM " . geoTables::categories_table . " WHERE `category_id`=$category_id AND `enabled`='yes'");
            $category_id = ($row) ? (int)$row['parent_id'] : 0;
        } while ($row && $row['enabled'] == 'yes' && $category_id > 0);

        return ($row && $row['enabled'] == 'yes');
    }

    /**
     * Add category data to local cache for use later in same page load.  This
     * mainly serves to greatly speed up SEO addon when there are a lot of category
     * links on the page, so it doesn't have to re-look
     * up data already retrieved.
     *
     * @param array $data The un-filtered results from DB for category.  Must
     *   contain category_id, language_id, category_name, and description to be
     *   of any use.
     * @since Version 5.1.2
     */
    public static function addCategoryResult($data)
    {
        $category_id = (int)$data['category_id'];
        $language_id = (int)$data['language_id'];
        $parent_id = (int)$data['parent_id'];

        if ($category_id && $language_id && isset($data['category_name'], $data['description'], $data['seo_url_contents'])) {
            $data['category_name'] = geoString::fromDB($data['category_name']);
            $data['description'] = geoString::fromDB($data['description']);
            $data['seo_url_contents'] = geoString::fromDB($data['seo_url_contents']);
            self::$_getInfoCache[$category_id][$language_id] = $data;
        }
    }

    /**
     * Allows adding multiple rows at once to local cache for use later.  Basically
     * just calls self::addCategoryResult() for each item in the array.
     *
     * @param array $data Array of category results
     * @since Version 5.1.2
     */
    public static function addCategoryResults($data)
    {
        foreach ($data as $row) {
            self::addCategoryResult($row);
        }
    }

    /**
     * Gets the name of the given category, already decoded.
     *
     * @param int $category_id
     * @param bool $justTheName if true, it acts like the method name sounds like,
     *   returning just the name.
     * @return string|stdClass
     */
    public static function getName($category_id, $justTheName = false)
    {
        //need to clean up a little
        $category_id = intval($category_id);
        if (!$category_id) {
            return 'Main';
        }
        $db = DataAccess::getInstance();

        $sql = "select category_name,description,title_module from " . geoTables::categories_languages_table . " where category_id = " . $category_id . " and language_id = " . $db->getLanguage();
        $r = $db->getrow($sql);
        if (!$r) {
            return false;
        }
        if ($justTheName) {
            return geoString::fromDB($r['category_name']);
        }
        $show = new stdClass();
        $show->CATEGORY_NAME = geoString::fromDB($r['category_name']);
        $show->DESCRIPTION = geoString::fromDB($r['description']);
        $show->TITLE_MODULE = geoString::fromDB($r['title_module']);
        return $show;
    }

    /**
     * return an array with a random category information
     *
     * @return array
     */
    public static function getRandomBasicInfo()
    {
        $db = DataAccess::getInstance();

        $language_id = $db->getLanguage();

        $sql = "SELECT `category_id`,`category_name`,`description` FROM " . geoTables::categories_languages_table . " WHERE language_id = ?  AND category_id !=? ORDER BY RAND() LIMIT 1";
        $result = $db->GetRow($sql, array($language_id,0));
        if ($result === false) {
            trigger_error('ERROR CATEGORY SQL: Random Cat not found, Sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            return false;
        }
        //use array_map, since all fields being returned (well except for the id) need to be geoString::fromDB
        $show = array_map(array('geoString','fromDB'), $result);

        return $show;
    }
    /**
     * Internal
     * @internal
     */
    private static $_getCategoryConfig_cache;
    /**
     * Gets the categories' info for the given category.
     *
     * @param int $category_id
     * @param bool $bubbleUpFields if true, bubble up through parents' "what fields to use" settings
     * @return array
     */
    public static function getCategoryConfig($category_id, $bubbleUpFields = false)
    {
        $db = DataAccess::getInstance();
        $category_id = intval($category_id);
        if (!$category_id) {
            return array();
        }

        $bubbleUpFields = $bubbleUpFields ? 1 : 0;

        if (isset(self::$_getCategoryConfig_cache[$category_id][$bubbleUpFields])) {
            return self::$_getCategoryConfig_cache[$category_id][$bubbleUpFields];
        }

        $sql = "SELECT * FROM " . geoTables::categories_table . " WHERE `category_id` = {$category_id} LIMIT 1";
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error('ERROR SQL CATEGORY: Sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
        }
        $cfg = $result->FetchRow();

        if ($cfg['what_fields_to_use'] == 'parent' && $bubbleUpFields) {
            if (!geoPC::is_ent() || $cfg['parent_id'] == 0) {
                //not enterprise -- must use site-wide settings
                // -- OR --
                //parent is 0 -- no category-specific settings in use
                $cfg = array('what_fields_to_use' => 'site');
            } else {
                //recurse up to parent
                $cfg = self::getCategoryConfig($cfg['parent_id'], 1);
            }
        }

        self::$_getCategoryConfig_cache[$category_id][$bubbleUpFields] = $cfg;
        return $cfg;
    }

    /**
     * Get the listing counts for the category requested.
     *
     * @param int $category_id
     * @param bool $force_on_fly If true, will calculate count on the fly instead
     *   of retrieving count stored in DB
     * @param bool $ignore_filters If true, query to "count" the listings will
     *   not start from the one with any browsing filters applied.
     * @return bool|array Boolean false if problem, or an associative array containing
     *   listing counts for requested category.
     * @since Version 6.0.4
     */
    public static function getListingCount($category_id, $force_on_fly = false, $ignore_filters = false)
    {
        $category_id = (int)$category_id;
        if (!$category_id) {
            return false;
        }
        $db = DataAccess::getInstance();

        if (!$force_on_fly && ($ignore_filters || !$db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere())) {
            //we can do things the easy way, get the pre-counted count
            $sql = "SELECT `category_count` as ad_count, `auction_category_count` as auction_count, (category_count+auction_category_count) as listing_count FROM " . geoTables::categories_table . "
				WHERE `category_id`=?";

            return $db->GetRow($sql, array($category_id));
        }
        //Manually count the listings in the requested category.

        $counts = array ('listing_count' => 0);



        $cTable = geoTables::classifieds_table;
        $lcTable = geoTables::listing_categories;

        if ($ignore_filters) {
            $query = new geoTableSelect($cTable);
        } else {
            $query = $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
        }

        $query->where($cTable . ".`live`=1", 'live');

        $cat_subquery = "SELECT * FROM $lcTable WHERE $lcTable.`listing`=$cTable.`id`
			AND $lcTable.`category`=$category_id";

        $query->where("EXISTS ($cat_subquery)", 'category');

        //Allow addons to alter query for counting listings
        $addon_vars = array (
            'category_id' => $category_id,
            'force_on_fly' => $force_on_fly,
            'ignore_filters' => $ignore_filters,
            'query' => $query,
        );
        geoAddon::triggerUpdate('geoCategory_getListingCount', $addon_vars);
        unset($addon_vars);

        if (geoMaster::is('classifieds')) {
            //get classifieds count
            $query->where($cTable . ".`item_type`=1", 'item_type');

            $counts['ad_count'] = (int)$db->GetOne('' . $query->getCountQuery());
            $counts['listing_count'] += $counts['ad_count'];
        }
        if (geoMaster::is('auctions')) {
            //get count for auctions
            //switch item_type check to work for auctions instead
            $query->where($cTable . ".`item_type` = 2", 'item_type');

            $counts['auction_count'] = (int)$db->GetOne('' . $query->getCountQuery());
            $counts['listing_count'] += $counts['auction_count'];
        }

        unset($query);//we are done, make sure mem is freed up
        return $counts;
    }

    /**
     * Sees if the given category has any kids.
     * @param int $category_id
     * @return bool
     */
    public static function hasChildren($category_id)
    {
        //TODO: cache stuff if possible
        //check input
        $category_id = intval($category_id);
        if ($category_id == 0) {
            return false;
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT COUNT(*) FROM " . geoTables::categories_table . " WHERE `parent_id` = ?";
        $count = (int)$db->GetOne($sql, array($category_id));
        return ($count > 0);
    }

    /**
     * Update the listing count on the given category ID.
     * @param int $category_id
     * @param bool $count_parents If false, will not count parent categories - param
     *   added in version 6.0.5
     */
    public static function updateListingCount($category_id, $count_parents = true)
    {
        $category_id = (int)$category_id;
        if (!$category_id) {
            return false;
        }
        $db = DataAccess::getInstance();

        $counts = self::getListingCount($category_id, true, true);

        if ($counts === false) {
            //oops!  can't do anything without counts
            return false;
        }

        $parts = array();
        $query_parts = array();

        if (isset($counts['ad_count'])) {
            $parts[] = "`category_count`=?";
            $query_parts[] = $counts['ad_count'];
        }

        if (isset($counts['auction_count'])) {
            $parts[] = "`auction_category_count`=?";
            $query_parts[] = $counts['auction_count'];
        }
        $query_parts[] = $category_id;
        $sql = "UPDATE " . geoTables::categories_table . " SET " . implode(', ', $parts) . " WHERE `category_id` = ? LIMIT 1";
        $db->Execute($sql, $query_parts);

        //mark this as done in the "update at bottom" array
        //that way, if this function manually gets called before the end of the script, it won't do any more work than it needs to at the actual bottom
        if (isset(self::$_updateCountsAtBottom[$category_id])) {
            self::$_updateCountsAtBottom[$category_id] = 0;
        }

        if ($count_parents) {
            //go through parents and update the parent categories as well
            $parent = self::getParent($category_id);
            if ($parent > 0) {
                self::updateListingCount($parent);
            }
        }
    }

    /**
     * Used by app_bottom.php to update the listing counts for any categories that have had their listings updated
     */
    public static function appBottom_updateAllListingCounts()
    {
        foreach (self::$_updateCountsAtBottom as $cat => $changed) {
            if ($changed === 1) {
                self::updateListingCount($cat);
            }
        }
    }

    /**
     * Add a category to the list of categories to have their counts updated at the end of page execution.
     * This is particularly useful for use in "bulk" routines, as it ensures each category will only be recounted a single time
     * @param int $category_id
     */
    public static function updateCategoryCountDelayed($category_id)
    {
        $category_id = (int)$category_id;
        if ($category_id) {
            self::$_updateCountsAtBottom[$category_id] = 1;
        }
    }

    /**
     * Internal
     * @internal
     */
    private static $_category_tree_array = array();


    /**
     * Gets a tree for the given category.  Alias of geoCategory->getParents($category,true,true)
     * @param int $category
     * @return array
     */
    public static function getTree($category)
    {
        $category = (int)$category;
        if (!$category) {
            return;
        }
        $tree = geoCategory::getInstance()->getParents($category, true, true);
        return $tree;
    }

    /**
     * Gets the HTML for new icon if there are new listings and setting is turned on,
     * otherwise returns empty string.
     *
     * @param int $category_id
     * @return string
     */
    public static function new_ad_icon_use($category_id = 0)
    {
        $db = DataAccess::getInstance();

        if ($db->get_site_setting('category_new_ad_limit') > 0 && $category_id) {
            $messages = $db->get_text(true);
            if (strlen($messages[500794])) {
                $date_limit = (geoUtil::time() - ($db->get_site_setting('category_new_ad_limit') * 3600));
                $db->preload_num_new_ads(geoUtil::time(), $date_limit);

                $count = $db->num_new_ads_in_category($category_id, geoUtil::time(), $date_limit);
                if ($count > 0) {
                    $tpl = new geoTemplate('system', 'classes');
                    return $tpl->fetch('Category/new_ad_image.tpl');
                }
            }
        }
        return '';
    }

    /**
     * Gets the parent category ID for the given category.  If it can't find the
     * parent, or 0 is specified, will return 0.  This is NOT efficient for running
     * multiple times for same category, so use once and save info.
     *
     * @param int $categoryId
     * @return int The parent category ID.
     * @since Version 5.0.0
     */
    public static function getParent($categoryId)
    {
        $categoryId = (int)$categoryId;
        if (!$categoryId) {
            return 0;
        }
        $db = DataAccess::getInstance();

        $sql = "SELECT `parent_id` FROM " . geoTables::categories_table . " WHERE `category_id`=$categoryId";
        $row = $db->GetRow($sql);
        if ($row && isset($row['parent_id'])) {
            return (int)$row['parent_id'];
        }
        return 0;
    }

    /**
     * Removes a specified category and all sub-categories, and anything "attached".
     * But it can have the option to "move" listings to the parent instead of
     * deleted.
     *
     * this does NOT re-count the parent category counts, it is up to calling
     * caller to do that this is designed to be called multiple times.
     *
     * As of version 6.0.6, will automatically update listing counts
     * for any categories where those might be affected by category removal
     *
     * @param int $categoryId
     * @param bool|int $moveTo If true, will move all listings in category being
     *   removed to the parent category.  If an int, will move listings to specified
     *   value used as category id to move to.
     * @param bool $recurse Used internally for whether this is recursive call or not,
     *   param added inversion 6.0.6
     * @return bool
     */
    public static function remove($categoryId, $moveTo = null, $recurse = false)
    {
        $categoryId = (int)$categoryId;
        if (!$categoryId) {
            //can't remove a non-existent category...
            return false;
        }
        $db = DataAccess::getInstance();

        if (!$recurse || $moveTo === true) {
            //we will need to figure out the parent ID if this is the initial call,
            //or if we don't know where we are moving the listings to...
            $sql = "SELECT `parent_id` FROM " . geoTables::categories_table . " WHERE `category_id`={$categoryId}";
            $parentId = $db->GetOne($sql);
            if ($parentId === false) {
                //error
                trigger_error('ERROR SQL: Error finding parent cat using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
                return false;
            }
            //just to be sure...
            $parentId = (int)$parentId;
        }

        if ($moveTo === true) {
            //specified that it should be moved, but not sure where to...

            $moveTo = (int)$parentId;
            if (!$moveTo || $moveTo == $categoryId) {
                //moving not possible, block removing this category when move
                //is specified but not able to determine where to move to
                trigger_error('ERROR STATS: Problem finding category to move to, can not proceed with category removal.');
                return false;
            }
        }
        //turn moveTo into int after this, we know if it's 0 we're deleting all
        //attached listings, otherwise we're moving attached listings to specified
        //category.
        $moveTo = (int)$moveTo;
        if ($moveTo == $categoryId) {
            //can't move to the same place!
            trigger_error('ERROR STATS: Problem moving listings to category, category from and to cannot be the same!');
            return false;
        }
        //get all sub-categories of this one and remove them
        //This needs to be super efficient, clean up vars after using them, etc.

        $sql = "SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `parent_id`={$categoryId}";
        $result = $db->Execute($sql);
        if (!$result) {
            //error
            trigger_error('ERROR SQL: Error finding sub-cates using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
            return false;
        }
        while ($row = $result->FetchRow()) {
            //don't use fancy $db->GetAll() as that loads them all into array at once, which
            //can take a ton more memory for sites with tons of cats.
            $subCat = (int)$row['category_id'];
            if ($subCat && $subCat != $categoryId) {
                $deleteSub = self::remove($subCat, $moveTo, true);
                if (!$deleteSub) {
                    //problem with deleting sub-category, do not proceed
                    return false;
                }
            }
        }

        //next, remove (or move) all listings in this category.
        if ($moveTo) {
            //"move" all listings to the moveTo location... Not the "best" solution,
            //this would only work if all the listings were re-scanned after this opperation.
            $sql = "SELECT * FROM " . geoTables::listing_categories . " WHERE `category` = {$categoryId} AND `is_terminal`='yes'";
            $listings_to_result = $db->Execute($sql);
            if (!$listings_to_result) {
                //error
                trigger_error('ERROR SQL: Error moving listings to new category using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
                return false;
            }
            while ($listing_row = $listings_to_result->FetchRow()) {
                //don't use fancy $db->GetAll() as that loads them all into array at once, which
                //can take a ton more memory for sites with tons of cats.
                geoCategory::setListingCategory($listing_row['listing'], $moveTo);
            }
        } else {
            //remove all listings in this category
            $cTable = geoTables::classifieds_table;
            $catTable = geoTables::listing_categories;
            $sql = "SELECT `id` FROM $cTable WHERE EXISTS (SELECT * FROM $catTable WHERE $catTable.`listing`=$cTable.`id` AND $catTable.`category`={$categoryId} AND $catTable.`is_terminal`='yes' AND `category_order`=0)";
            $result = $db->Execute($sql);
            if (!$result) {
                //error
                trigger_error('ERROR SQL: Error finding listings in category to remove using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
                return false;
            }
            while ($row = $result->FetchRow()) {
                $listingId = (int)$row['id'];
                if ($listingId) {
                    $deleteListing = geoListing::remove($listingId);
                    if (!$deleteListing) {
                        //problem removing one of the listings...
                        trigger_error('ERROR STATS: Problem removing a listing in a category, stopping removal of the category.');
                        return false;
                    }
                }
            }
        }

        //Need to do something special to find and remove all category filters
        $sql = "SELECT `filter_id` FROM " . geoTables::ad_filter_table . "
			WHERE `category_id`={$categoryId}";
        $select_filter_result = $db->Execute($sql);
        if (!$select_filter_result) {
            trigger_error('ERROR SQL: Error during cat removal using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
            return false;
        }
        while ($row = $select_filter_result->FetchRow()) {
            $sql = "DELETE FROM " . geoTables::ad_filter_categories_table . "
				WHERE `filter_id` = " . (int)$row["filter_id"];
            $delete_filter_result = $db->Execute($sql);
            if (!$delete_filter_result) {
                trigger_error('ERROR SQL: Error during cat removal using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
                return false;
            }
        }

        //special to remove languages for category questions
        $sql = "SELECT `question_id` FROM " . geoTables::questions_table . "
			WHERE `category_id`={$categoryId}";
        $select_question_result = $db->Execute($sql);
        if (!$select_question_result) {
            trigger_error('ERROR SQL: Error during cat removal using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
            return false;
        }
        while ($row = $select_question_result->FetchRow()) {
            $sql = "DELETE FROM " . geoTables::questions_languages . "
				WHERE `question_id` = " . (int)$row["question_id"];
            $delete_filter_result = $db->Execute($sql);
            if (!$delete_filter_result) {
                trigger_error('ERROR SQL: Error during cat removal using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
                return false;
            }
        }

        //remove "simple" things from categories that don't need anything more
        //than removing entries from the DB based on cat id
        $simpleRemoves = array (
            geoTables::ad_filter_table,//main ad filters page
            geoTables::ad_filter_categories_table,//alt category filters table
            geoTables::price_plans_categories_table,//category price plans
            geoTables::questions_table,//category questions
            geoTables::categories_languages_table,//category languages table
        );
        foreach ($simpleRemoves as $tableName) {
            //delete all entries that match this listing
            $sql = "DELETE FROM {$tableName} WHERE `category_id`={$categoryId}";
            $result = $db->Execute($sql);
            if (!$result) {
                trigger_error('ERROR SQL: Error removing stuff from category, using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
                return false;
            }
        }

        //move order items attached to this category to use cat 0
        $sql = "UPDATE " . geoTables::order_item . " SET `category`=0 WHERE `category`={$categoryId}";
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error('ERROR SQL: Error moving order items to 0 category, using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
            return false;
        }
        //remove any price plan items
        geoPlanItem::remove(null, $categoryId);

        //remove from category-specific fields to use
        geoFields::remove(null, $categoryId);

        //delete the actual category
        $sql = "DELETE FROM " . geoTables::categories_table . " WHERE `category_id`={$categoryId}";
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error('ERROR SQL: Error removing category, using sql: ' . $sql . ', Error: ' . $db->ErrorMsg());
            return false;
        }

        if (!$recurse && $parentId) {
            //now that category is deleted, and this is not recursively called,
            //and there is a parent, need to update the parent

            //now update the parent's listing count, this will automatically update
            //parents as well.
            geoCategory::updateListingCount($parentId);
        }
        return true;
    }

    /**
     * Gets the "top" parent ID for the given category ID by traveling up the
     * category tree.
     *
     * @param int $categoryId
     * @return boolean|number The top category ID or false if there was a problem.
     * @since Version 6.0.6
     */
    public static function getTopParent($categoryId)
    {
        $categoryId = (int)$categoryId;

        while (($parent = geoCategory::getParent($categoryId)) > 0) {
            $categoryId = $parent;
        }
        return $categoryId;
    }

    /**
     * Gets the HTML to add to the header for the specific category
     * @param int $catId
     * @return string
     */
    public static function getHeaderHtml($catId, $page_id = 0)
    {
        $db = DataAccess::getInstance();

        if (($page_id == 1 || $page_id == 69) && ($db->get_site_setting("show_category_head_element_in_details") == 1)) {
            //do not display the header elements in the listing details page
            return ('');
        }

        $start = "\n<!-- Category Specific Start -->\n";
        $end = "\n<!-- Category Specific End -->\n";

        $catId = (int)$catId;

        if (!$catId) {
            //get defautl text
            $text = $db->get_text(true, 3);
            $header = self::_parseTpl($text[500961]);
            return ($header ? $start . $header . $end : '');
        }

        //see "which header html" to use
        $cat = $db->GetRow("SELECT * FROM " . geoTables::categories_table . " WHERE `category_id`=?", array($catId));

        if (!$cat) {
            //could not get info for category use default
            return self::getHeaderHtml(0, $page_id);
        }

        $which = $cat['which_head_html'];
        $parent = $cat['parent_id'];

        $return = array();

        if ($which == 'parent') {
            return self::getHeaderHtml($parent, $page_id);
        }
        if ($which == 'cat' || $which == 'cat+default') {
            //append category specific!
            $langRow = $db->GetRow("SELECT `head_html` FROM " . geoTables::categories_languages_table . " WHERE `category_id`=? AND `language_id`=?", array($catId, $db->getLanguage()));
            if ($langRow && $langRow['head_html']) {
                $return[] = self::_parseTpl(geoTemplate::parseExternalTags(geoString::fromDB($langRow['head_html'])));
            }
        }
        if ($which == 'default' || $which == 'cat+default') {
            $text = $db->get_text(true, 3);
            if ($text[500961]) {
                $return[] = self::_parseTpl($text[500961]);
            }
        }

        $header = implode("\n\n", $return);
        return ($header ? $start . $header . $end : '');
    }
    /**
     * Get catgory values, in an array format that is expected by the multi-level
     * selection stuff.  This allows the multi-level field selection stuff able
     * to be used to select category as well.
     *
     * @param int $parent
     * @param int $listing_types_allowed
     * @param int $selected
     * @param int $page
     * @param int $language_id
     * @param int $level
     * @param int|bool $recurringClassPricePlan either a price plan ID to only show those categories (and their children) for which Recurring Classifieds are enabled on that plan, or bool false to disregard
     * @return boolean|array The array of values as needed to show in multi-level
     *   selection format, or false on error
     */
    public static function getCategoryLeveledValues($parent, $listing_types_allowed, $selected = 0, $page = 'all', $language_id = null, $level = null, $recurringClassPricePlan = false, $price_plan = 0)
    {
        $db = DataAccess::getInstance();
        $parent = (int)$parent;
        $selected = (int)$selected;
        $page = ($page == 'all') ? 'all' : (int)max(1, $page);

        $catClass = geoCategory::getInstance();

        if ($parent < 0) {
            //invalid input
            return false;
        }
        $return = array('values' => array(), 'maxValues' => 0, 'page' => 1, 'maxPages' => 1,
            'level' => 1);

        $language_id = (int)$language_id;
        if (!$language_id) {
            //get the current language id
            $language_id = (int)geoSession::getInstance()->getLanguage();
        }

        //figure out the level
        if (!$parent) {
            $return['level'] = $level = 1;
        } else {
            $parentInfo = $catClass->getInfo($parent);
            if ($parentInfo && $parentInfo['enabled'] = 'yes') {
                $return['level'] = $level = $parentInfo['level'];
            } else {
                $parent = 0;
                $return['level'] = $level = 1;
            }
        }

        $catTbl = geoTables::categories_table;
        $langTbl = geoTables::categories_languages_table;
        $query = new geoTableSelect($catTbl);
        $query->from($catTbl, array("$catTbl.category_id", "$catTbl.display_order"));

        $orderByAlpha = (bool)$db->get_site_setting('order_choose_category_by_alpha');

        $cat_img = ($orderByAlpha) ? ", $langTbl.category_image" : '';
        $order_by = ($db->get_site_setting('order_choose_category_by_alpha')) ? "$langTbl.category_name" : "$catTbl.display_order, $langTbl.category_name";

        if ($listing_types_allowed === 1) {
            //this is a classified -- get the list of categories that can't be used for classifieds
            $exclusions = self::getExcludedCategoriesByListingType('classifieds');
        } elseif ($listing_types_allowed === 2) {
            $exclusions = self::getExcludedCategoriesByListingType('auctions');
        } else {
            $exclusions = array();
        }

        //add category exclusions from exclude by price plan feature
        $exclusions = self::getExcludedCategoriesByPricePlan($parent, $price_plan, $exclusions);

        if ($exclusions) {
            $excludeCats = "$catTbl.category_id NOT IN (" . implode(',', $exclusions) . ')';
        }

        $query->join($langTbl, "$catTbl.category_id = $langTbl.category_id", "$langTbl.category_name{$cat_img}, $langTbl.description")
            ->where("$catTbl.`parent_id` = '$parent'", 'parent_id')
            ->where("$catTbl.`enabled` = 'yes'")
            ->where("$langTbl.language_id = {$language_id}", 'language_id')
            ->order($order_by);

        if ($excludeCats) {
            $query->where($excludeCats, 'exclude_listing_types');
        }


        if ($recurringClassPricePlan) {
            $validCats = self::getRecurringClassifiedCategoriesForPricePlan($recurringClassPricePlan);
            if (count($validCats) > 0) {
                $query->where("$catTbl.category_id IN (" . implode(',', $validCats) . ")", 'recurring_classified_categories');
            }
        }


        //kick the query over to any addons that care to modify which categories are shown
        geoAddon::triggerDisplay('filter_listing_placement_category_query', $query, geoAddon::FILTER);

        $return['maxValues'] = (int)$db->GetOne($query->getCountQuery());
        if (!$return['maxValues']) {
            //no use in running the normal query, we already know count is 0
            return $return;
        }

        $values_per_page = (int)$db->get_site_setting('leveled_max_vals_per_page');

        if ($return['maxValues'] > $values_per_page) {
            //calculate number of pages
            $return['maxPages'] = ceil($return['maxValues'] / $values_per_page);

            if ($page !== 'all' && $page <= $return['maxPages']) {
                //add limit
                $start = ($page - 1) * $values_per_page;

                $query->limit($start, $values_per_page);
                //this is the "actual" page we are on
                $return['page'] = $page;
            } elseif ($page === 'all' && $return['maxPages'] > 1) {
                //set the returned page to 'all'
                $return['page'] = 'all';
            }
        }
        $result = $db->Execute($query);
        if (!$result) {
            //error?
            trigger_error('ERROR SQL: Error getting leveled field values!');
            return false;
        }
        $foundSelected = false;
        $lastRow = null;
        $rows = array();
        foreach ($result as $row) {
            //unescape name
            $row['name'] = geoString::fromDB($row['category_name']);
            $row['id'] = $row['category_id'];
            if ($selected) {
                $row['selected'] = ($selected == $row['id']);
                if ($row['selected']) {
                    $foundSelected = true;
                }
            }
            $row['level'] = $level;
            $rows[$row['id']] = $lastRow = $row;
        }
        $return['values'] = $rows;
        if (!$foundSelected && $selected > 0) {
            //add selected to front / end!
            $query->where("$catTbl.`category_id`=$selected")->limit(0);
            $row = $db->GetRow($query);
            if ($row) {
                $row['name'] = geoString::fromDB($row['category_name']);
                $row['id'] = $row['category_id'];
                $row['level'] = $level;
                $row['selected'] = true;
                $row['is_off_page'] = true;

                //figure out if "before" or "after"
                $addBefore = ($page > 1);
                if ($addBefore) {
                    //it is "possible" that it should be before, so verify it
                    if ($orderByAlpha || $lastRow['display_order'] == $row['display_order']) {
                        //only order by alpha...
                        $check = array(
                            $lastRow['id'] => $lastRow['name'],
                            $row['id'] => $row['name'],
                            );
                        asort($check);
                        //now figure out which one is first in the array, that is the one
                        //that is first alphabetically.
                        $check = array_keys($check);
                        $addBefore = ($check[0] == $row['id']);
                    } else {
                        $addBefore = ($row['display_order'] < $lastRow['display_order']);
                    }
                }
                //now either add it "before" or "after" the return array...
                if ($addBefore) {
                    //add it before!
                    $return['values'] = array();
                    $return['values'][$row['id']] = $row;
                    foreach ($rows as $key => $val) {
                        $return['values'][$key] = $val;
                    }
                } else {
                    //add it after!
                    $rows[$row['id']] = $row;
                    $return['values'] = $rows;
                }
            }
        }

        return $return;
    }

    public static function getExcludedCategoriesByListingType($listingType)
    {
        $db = DataAccess::getInstance();
        $sql = "SELECT * FROM " . geoTables::category_exclusion_list . " WHERE `listing_type` = ?";
        $result = $db->Execute($sql, array($listingType));
        $return = array();
        foreach ($result as $line) {
            $return[] = $line['category_id'];
        }
        return $return;
    }

    public static function categoryIsExcludedFromListingType($category, $listingType)
    {
        $db = DataAccess::getInstance();
        $sql = "SELECT `category_id` FROM " . geoTables::category_exclusion_list . " WHERE `category_id` = ? AND `listing_type` = ?";
        $result = $db->GetOne($sql, array($category, $listingType));
        return (bool)$result;
    }

    public static function getExcludedCategoriesByPricePlan($parent = 0, $price_plan = 0, $exclusions = array())
    {
        if (($parent == 0) && ($price_plan != 0)) {
            $db = DataAccess::getInstance();

            $pricePlanCategoryBanTbl = geoTables::categories_exclude_per_price_plan_table;
            //check to see if there are banned categories
            $sql = "SELECT `main_category_id_banned` FROM " . $pricePlanCategoryBanTbl . " WHERE `price_plan_id` = " . $price_plan;
            $result = $db->Execute($sql);
            foreach ($result as $line) {
                array_push($exclusions, $line['main_category_id_banned']);
            }
        }
        return $exclusions;
    }


    /**
     * Recursively get an array of all categories that are children of the given category
     * @param int $parent
     */
    public static function getAllChildren($parent)
    {
        $parent = (int)$parent;
        $db = DataAccess::getInstance();

        $return = array();

        $sql = "SELECT category_id FROM " . geoTables::categories_table . " WHERE `parent_id` = ? AND `enabled` = 'yes'";
        $result = $db->Execute($sql, array($parent));
        if (!$result) {
            return false;
        }
        if ($result->RecordCount() == 0) {
            //no children, so return nothing
            return array();
        }

        foreach ($result as $child) {
            //add self to return data
            $return[] = $child['category_id'];
            //also add any children
            $kids = self::getAllChildren($child['category_id']);
            $return = array_merge($return, $kids);
        }
        return $return;
    }

    public static function getRecurringClassifiedCategoriesForPricePlan($pricePlan)
    {
        $pricePlan = (int)$pricePlan; //this is sometimes a user-facing (though hidden) value -- cleaning it is important!
        if (!$pricePlan) {
            return false;
        }
        $db = DataAccess::getInstance();
        //first, get all categories where recurring classifieds are directly enabled in this price plan
        $sql = "SELECT `category` FROM " . geoTables::plan_item . " WHERE `order_item` = 'classified_recurring' AND price_plan = ? AND `enabled` = 1";
        $result = $db->Execute($sql, array($pricePlan));
        $categories = array();
        foreach ($result as $enabled) {
            $categories[] = $enabled['category'];
        }
        $allCategories = $categories;
        //now also get all the children of each enabled category
        foreach ($categories as $cat) {
            $allCategories = array_merge($allCategories, self::getAllChildren($cat));
        }
        //this is an optimized/speedy way (faster than array_unique(), per php.net) to make sure each category only appears once
        $allCategories = array_keys(array_flip($allCategories));
        return $allCategories;
    }

    /**
     * Keep track of categories set by setListingCategory(), then update all their counts in app_bottom
     * @var Array
     */
    private static $_updateCountsAtBottom = array();

    public static function setListingCategory($listing_id, $category_id)
    {
        $listing_id = (int)$listing_id;
        $category_id = (int)$category_id;
        $cat_order = 0;

        if (!$listing_id) {
            trigger_error('ERROR CATEGORY: no listing ID, cannot set cat id for that listing');
            return false;
        }

        $db = DataAccess::getInstance();
        $catClass = geoCategory::getInstance();

        //get any current categories and mark them for count-updating later (only care about terminal cats for this, since that function recurses up automatically)
        $sql = "SELECT `category` FROM " . geoTables::listing_categories . " WHERE `listing` = ? AND `is_terminal` = 'yes'";
        $result = $db->Execute($sql, array($listing_id));
        if ($result) { //just for sanity
            foreach ($result as $row) {
                self::updateCategoryCountDelayed($row['category']);
            }
        }


        $sql = "DELETE FROM " . geoTables::listing_categories . " WHERE `listing` = ?";
        $db->Execute($sql, array($listing_id));

        $parents = $catClass->getParents($category_id, true);
        $inserted = array();
        foreach ($parents as $catInfo) {
            //loop through each region and add it
            if (isset($inserted[$catInfo['category_id']])) {
                //this one is already inserted, do not insert again
                continue;
            }
            $inserted[$catInfo['category_id']] = $catInfo['category_id'];
            $is_terminal = ($catInfo['category_id'] == $category_id) ? 'yes' : 'no';
            $sql = "INSERT INTO " . geoTables::listing_categories . " (listing,category,level,category_order,default_name,is_terminal) VALUES (?,?,?,?,?,?)";
            $qd = array($listing_id, $catInfo['category_id'], $catInfo['level'],
                    $cat_order, geoString::toDB($catInfo['name']), $is_terminal
            );
            $result = $db->Execute($sql, $qd);
            if (!$result) {
                trigger_error('ERROR SQL: failed to save regions: ' . $sql . ' Error: ' . $db->ErrorMsg() . ' :: qd: ' . print_r($qd, 1));
                return false;
            }
        }

        self::$_updateCountsAtBottom[$category_id] = 1;
    }

    /**
     * Used internally
     * @param string $content
     * @return string
     * @internal
     */
    private static function _parseTpl($content)
    {
        if (substr(trim($content), 0, 9) == 'template:') {
            $tpl = new geoTemplate(geoTemplate::MAIN_PAGE);
            return $tpl->fetch(str_replace('template:', '', trim($content)));
        }
        return $content;
    }
}
