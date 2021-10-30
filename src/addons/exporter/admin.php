<?php

// addons/exporter/admin.php

# Example Addon

class addon_exporter_admin extends addon_exporter_info
{

    //function to initialize pages, to let the page loader know the pages exist.
    //this will only get run if the addon is installed and enabled.
    function init_pages()
    {
        menu_page::addonAddPage('addon_exporter', '', 'Export', 'exporter', $this->icon_image);
    }

    private $_file;

    public function getFile()
    {
        if (!isset($this->_file)) {
            $this->_file = geoFile::getInstance('addon_exporter')
                ->jailTo(ADDON_DIR . 'exporter/exports/');
        }
        return $this->_file;
    }

    public function display_addon_exporter()
    {
        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $view->addCssFile('css/calendarview.css')
            ->addJScript('../js/calendarview.js');

        if (!is_writable(ADDON_DIR . 'exporter/exports/')) {
            geoAdmin::m('The export folder (' . ADDON_DIR . 'exporter/exports/) is not writable, will not be able to export to file.  Ensure the folder is writable (CHMOD 777) if you wish to save exports on the server.', geoAdmin::ERROR);
        }

        $optionals = array ();
        for ($i = 1; $i <= 20; $i++) {
            $optionals[$i] = $db->get_site_setting('optional_field_' . $i . '_name');
        }

        $tpl_vars = array (
            'categories' => $this->getCategoryOptions(),
            'states' => $this->getStatesOptions(),
            'countries' => $this->getCountriesOptions(),
            'optionals' => $optionals,
            'notices' => geoAdmin::m()
        );

        $tpl_vars['loadSettings'] = DataAccess::getInstance()->GetAll("SELECT * FROM " . self::SETTINGS_TABLE . " ORDER BY `last_updated`");

        $view->setBodyVar($tpl_vars)
            ->setBodyTpl('admin/search.tpl', 'exporter')
            ->addCssFile(array('../addons/exporter/style.css','css/calendarview.css'))
            ->addJScript(array('../addons/exporter/search.js'));
        return true;
    }

    public function update_addon_exporter()
    {
        $settings = $_POST;

        $valid_types = array ('xml', 'csv');

        $exportType = (in_array($settings['exportType'], $valid_types)) ? $settings['exportType'] : 'xml';

        $call = $exportType . 'Export';
        $this->$call($settings, true);
        geoView::getInstance()->setRendered(true);
    }

    /**
     * Initializes feed criteria
     *
     * @param geoListingFeed $feed
     * @param array $settings
     */
    public function initFeedCriteria($feed, $settings)
    {
        $feed->maxListings = (int)$settings['maxListings'];

        switch ($settings['item_type']) {
            case '1':
                $feed->type = 'classified';
                break;

            case '2':
                $feed->type = 'all_auction';
                break;

            case 'indif':
                //break omitted on purpose
            default:
                $feed->type = 'all';
                break;
        }
        //we are manually setting whether listings should be live or not
        $feed->skipLive = 1;
        $classTable = geoTables::classifieds_table;
        $db = DataAccess::getInstance();//used for quoting

        if ($settings['live'] !== 'indif') {
            //we care about if live or not
            $feed->where("$classTable.`live`=" . (((int)$settings['live']) ? 1 : 0), 'live');
        }

        if ($settings['image'] !== 'indif') {
            //whether or not listing has images
            $sign = ($settings['image'] == '1') ? '>' : '=';
            $feed->where("$classTable.`image` {$sign} 0");
        }

        if (strlen(trim($settings['price']['low']))) {
            //low price is set
            $lowPrice = geoNumber::deformat($settings['price']['low']);
            $feed->where($db->quoteInto("$classTable.`price` >= ?", $lowPrice));
        }

        if (strlen(trim($settings['price']['high']))) {
            //high price is set
            $highPrice = geoNumber::deformat($settings['price']['high']);
            $feed->where($db->quoteInto("$classTable.`price` <= ?", $highPrice));
        }

        //Date filters
        if (strlen(trim($settings['date']['start']['low']))) {
            //starts low
            $parts = explode('-', trim($settings['date']['start']['low']));
            $start = (int)mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
            if ($start) {
                $feed->where("$classTable.`date` >= $start");
            }
        }

        if (strlen(trim($settings['date']['start']['high']))) {
            //starts high
            $parts = explode('-', trim($settings['date']['start']['high']));
            $start = (int)mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]);
            if ($start) {
                $feed->where("$classTable.`date` <= $start");
            }
        }

        if (strlen(trim($settings['date']['end']['low']))) {
            //ends low
            $parts = explode('-', trim($settings['date']['end']['low']));
            $start = (int)mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
            if ($start) {
                $feed->where("$classTable.`ends` >= $start");
            }
        }

        if (strlen(trim($settings['date']['end']['high']))) {
            //ends high
            $parts = explode('-', trim($settings['date']['end']['high']));
            $start = (int)mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]);
            if ($start) {
                $feed->where("$classTable.`ends` <= $start");
            }
        }

        if (strlen(trim($settings['date']['duration']['low']['num'])) && (int)$settings['date']['duration']['low']['multiplier']) {
            //duration low
            $duration = (int)($settings['date']['duration']['low']['num'] * $settings['date']['duration']['low']['multiplier']);
            if ($duration) {
                $feed->where("$classTable.`duration` >= $duration");
            }
        }

        if (strlen(trim($settings['date']['duration']['high']['num'])) && (int)$settings['date']['duration']['high']['multiplier']) {
            //duration high
            $duration = (int)($settings['date']['duration']['high']['num'] * $settings['date']['duration']['high']['multiplier']);
            if ($duration) {
                $feed->where("$classTable.`duration` <= $duration");
            }
        }

        if ($settings['bolding'] !== 'indif') {
            //must have bolding
            $feed->where("$classTable.`bolding` = " . (($settings['bolding'] == '1') ? 1 : 0));
        }

        if ($settings['better_placement'] !== 'indif') {
            //must have better placement
            if ($settings['better_placement'] == '1') {
                $feed->where("$classTable.`better_placement` > 0");
            } else {
                $feed->where("$classTable.`better_placement` = 0");
            }
        }

        if ($settings['attention_getter'] !== 'indif') {
            //must have attention getter
            $feed->where("$classTable.`attention_getter` = " . (($settings['attention_getter'] == '1') ? 1 : 0));
        }
        //featured ad levels
        if (isset($settings['featured_ad']) && $settings['featured_ad']) {
            //must have featured ad
            $feed->where("$classTable.`featured_ad` = 1");
        }
        if (isset($settings['featured_ad_2']) && $settings['featured_ad_2']) {
            //must have featured ad 2
            $feed->where("$classTable.`featured_ad_2` = 1");
        }
        if (isset($settings['featured_ad_3']) && $settings['featured_ad_3']) {
            //must have featured ad 3
            $feed->where("$classTable.`featured_ad_3` = 1");
        }
        if (isset($settings['featured_ad_4']) && $settings['featured_ad_4']) {
            //must have featured ad 4
            $feed->where("$classTable.`featured_ad_4` = 1");
        }
        if (isset($settings['featured_ad_5']) && $settings['featured_ad_5']) {
            //must have featured ad 5
            $feed->where("$classTable.`featured_ad_5` = 1");
        }

        if (count($settings['category']) > 1) {
            $cats = implode(', ', $settings['category']);
            $catListTable = geoTables::listing_categories;

            $subQuery = "SELECT * FROM $catListTable WHERE $catListTable.`listing`=$classTable.`id` AND $catListTable.`category` IN ($cats)";

            $feed->where("EXISTS ($subQuery)", 'category');
            //we just did category filtering, so don't need listingfeed to do it.
            $feed->catId = 0;
        } elseif (count($settings['category'])) {
            //single category... easy, let feed do it
            $feed->catId = $settings['category'][0];
        }

        //TODO:  filter by location

        //TODO:  Filter by optionals

        $feed->userId = 0;
    }

    public function xmlExport($settings, $pushToBrowser)
    {
        $feed = new geoListingFeed();

        /****  FILTER CRITERIA  ****/

        $this->initFeedCriteria($feed, $settings);

        /****  OUTPUT Formatting ****/

        //order by category by default
        $lc = geoTables::listing_categories;
        $c = geoTables::classifieds_table;
        $feed->getTableSelect()->order("$lc.`category`")
            ->join($lc, "$lc.`listing`=$c.`id`", array('`category`'))
            ->where("$lc.`is_terminal`='yes'");

        $feed->catFormat = $settings['fieldFormat']['category'];

        $date_format = false;
        switch ($settings['fieldFormat']['date']) {
            case 'date_time':
                $date_format = 'm/d/Y - H:i:s';
                break;

            case 'date':
                $date_format = 'm/d/Y';
                break;

            case 'custom':
                $date_format = $settings['fieldFormat']['date_custom'];
                break;

            case 'unix':
                //break ommited on purpose
            default:
                //nothing to do, using unix timestamps
                break;
        }

        if ($date_format) {
            $feed->dateFormat = $date_format;
        }

        if ($settings['fieldFormat']['category'] != 'id') {
            $feed->categoryName = 1;
        }

        if (in_array('img_url_all', $settings['show_extra'])) {
            //show all images
            $feed->imageCount = 0;
            $settings['show'][] = 'image';
        } elseif (in_array('img_url_1', $settings['show_extra'])) {
            //show first image
            $feed->imageCount = 1;
            $settings['show'][] = 'image';
        } else {
            //don't show any images
            $feed->imageCount = -1;
        }

        if (in_array('extra_questions', $settings['show_extra'])) {
            $feed->extraQuestions = 1;
        }
        //remove the currency fields if they exist...
        $feed->removeCurrencyColumns = 1;

        //remove anything not requested
        $feed->removeNonRequestedColumns = 1;

        $show = array ();
        foreach ($settings['show'] as $field) {
            $show[$field] = 1;
        }
        $feed->show = $show;

        $feed->debug = false;

        //let whoever needs to know, this is being called in an RSS feed file...
        define('IN_GEO_RSS_FEED', 1);

        //This is a generic feed (we'll be manually configuring all the options)
        $feed->setFeedType(geoListingFeed::GENERIC_FEED);

        $feed->clean_all = 1;
        $feed->tpl_type = geoTemplate::ADDON;
        $feed->tpl_resource = $this->name;
        $feed->tpl_file = 'admin/export_types/xml.tpl';

        $feed->generateSql();

        $feed->generateResultSet();

        //do the rest over there
        $this->writeFeed($feed, $settings['filename'], $pushToBrowser, '.xml', 'application/xhtml+xml');
    }

    public function csvExport($settings, $pushToBrowser)
    {
        //die ('Settings: <pre>'.print_r($settings,1));
        $feed = new geoListingFeed();

        /****  FILTER CRITERIA  ****/

        $this->initFeedCriteria($feed, $settings);

        /****  OUTPUT Formatting ****/

        //order by category by default
        $feed->orderByClause = "ORDER BY `category`";

        $feed->catFormat = $settings['fieldFormat']['category'];

        $date_format = false;
        switch ($settings['fieldFormat']['date']) {
            case 'date_time':
                $date_format = 'm/d/Y - H:i:s';
                break;

            case 'date':
                $date_format = 'm/d/Y';
                break;

            case 'custom':
                $date_format = $settings['fieldFormat']['date_custom'];
                break;

            case 'unix':
                //break ommited on purpose
            default:
                //nothing to do, using unix timestamps
                break;
        }

        if ($date_format) {
            $feed->dateFormat = $date_format;
        }

        if ($settings['fieldFormat']['category'] != 'id') {
            $feed->categoryName = 1;
        }

        if (in_array('img_url_all', $settings['show_extra'])) {
            //show all images
            $feed->imageCount = 0;
            $settings['show'][] = 'image';
        } elseif (in_array('img_url_1', $settings['show_extra'])) {
            //show first image
            $feed->imageCount = 1;
            $settings['show'][] = 'image';
        } else {
            //don't show any images
            $feed->imageCount = -1;
        }

        if (in_array('extra_questions', $settings['show_extra'])) {
            $feed->extraQuestions = 1;
        }
        //remove the currency fields if they exist...
        $feed->removeCurrencyColumns = 1;

        //remove anything not requested
        $feed->removeNonRequestedColumns = 1;

        //TODO:  Add way to specify what the multiple item columns (like images) are seperated with

        $show = array ();
        foreach ($settings['show'] as $field) {
            $show[$field] = 1;
        }
        $feed->show = $show;

        $feed->debug = false;

        //let whoever needs to know, this is being called in an RSS feed file...
        define('IN_GEO_RSS_FEED', 1);

        //This is a generic feed (we'll be manually configuring all the options)
        $feed->setFeedType(geoListingFeed::GENERIC_FEED);

        $feed->clean_all = 1;
        $feed->tpl_type = geoTemplate::ADDON;
        $feed->tpl_resource = $this->name;
        $feed->tpl_file = 'admin/export_types/csv.tpl';

        //Need to be able to add plugin for exporting CSV line
        $feed->add_smarty_plugins_dir = ADDON_DIR . 'exporter/smarty_plugins';
        //open file handle to output...
        //see  http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
        $feed->csvHandle = $handle = fopen('php://output', 'w');

        $feed->generateSql();

        $feed->generateResultSet();

        //do the rest over there
        $this->writeFeed($feed, $settings['filename'], $pushToBrowser, '.csv', 'text/csv');
        fclose($handle);
    }

    public function writeFeed($feed, $local_filename, $pushToBrowser, $ext, $mime)
    {
        //clean the filename
        $local_filename = preg_replace('/[^a-zA-Z0-9_]*/', '', $local_filename);

        if ($local_filename) {
            $file = $this->getFile();

            $local_filename .= $ext;

            $filename = $file->absolutize($local_filename);

            if ($filename && is_writable(dirname($filename))) {
                $size = file_put_contents($filename, $feed);
                //make it writable
                chmod($filename, 0777);

                if ($pushToBrowser) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: ' . $mime);
                    header('Content-Disposition: attachment; filename="' . $local_filename . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . $size);
                    readfile($filename);
                    geoView::getInstance()->setRendered(true);
                }
                return true;
            }
        }

        if ($pushToBrowser) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="export' . $ext . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            $filesize = (function_exists('mb_strlen')) ? mb_strlen($feed, '8bit') : strlen($feed);
            header("Content-Length: $filesize");
            //display the feed
            echo $feed;
            geoView::getInstance()->setRendered(true);
        }
    }

    private $_fileHandle;

    public function ob_write_file($buffer)
    {
        if (!$this->_fileHandle) {
            //no file handle? reutrn false
            return false;
        }
        fwrite($this->_fileHandle, $buffer);
        //we do not write anything to browser
        return '';
    }


    function getCategoryOptions()
    {
        $this->get_category_dropdown(0, 1);
        return $this->dropdown_body;
    }
    var $category_dropdown_name_array = array();
    var $dropdown_body;
    var $category_dropdown_id_array = array();

    function get_category_dropdown($category_id = 0, $no_main = 0, $dropdown_limit = 0)
    {
        if (count($this->category_dropdown_name_array) == 0) {
            if (!$no_main) {
                array_push($this->category_dropdown_name_array, "All Categories");
                array_push($this->category_dropdown_id_array, 0);
            }

            $this->get_all_subcategories_for_dropdown($dropdown_limit);
        } else {
            reset($this->category_dropdown_name_array);
        }

        //build the select statement
        //array_reverse($this->category_dropdown_name_array);
        //array_reverse($this->category_dropdown_id_array);
        foreach ($this->category_dropdown_name_array as $key => $value) {
            $this->dropdown_body .= "<option value=\"{$this->category_dropdown_id_array[$key]}\">{$this->category_dropdown_name_array[$key]}</option>\n\t\t";
        }
        return true;
    }

    function get_all_subcategories_for_dropdown($dropdown_limit = 0, $category_id = 0)
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $sql = 'SELECT ' . $db->geoTables->categories_table . ".category_id as category_id,
			" . $db->geoTables->categories_table . ".parent_id as parent_id," . $db->geoTables->categories_languages_table . ".category_name as category_name
			FROM " . $db->geoTables->categories_table . "," . $db->geoTables->categories_languages_table .
            " WHERE " . $db->geoTables->categories_table . ".category_id = " . $db->geoTables->categories_languages_table . ".category_id " .
            "AND " . $db->geoTables->categories_languages_table . ".language_id = 1 " .
            'ORDER BY ' . $db->geoTables->categories_table . '.parent_id, ' . $db->geoTables->categories_table . '.display_order, ' . $db->geoTables->categories_languages_table . ".category_name";
        $results = $db->Execute($sql);
        if (!$results) {
            echo('ERROR SQL ADMIN_SITE_CLASS: Query: ' . $sql . ' Error: ' . $db->ErrorMsg());
            return false;
        }
        trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After sql executed, before data gotten.');
        $categories = array();
        while ($row = $results->FetchRow()) {
            $categories[$row['parent_id']][$row['category_id']]['category_name'] = $row['category_name'];
            //$categories[$row['parent_id']][$row['category_id']]['category_id']=$row['category_id'];
        }
        $this->add_sub_categories_for_dropdown($categories, $category_id);
    }
    function add_sub_categories_for_dropdown(&$show_category, $parent, $dropdown_limit = 0)
    {
        $ids = array_keys($show_category[$parent]);
        foreach ($ids as $id) {
            $pre_stage = "";
            for ($i = 1; $i <= $this->stage; $i++) {
                $pre_stage .= "&nbsp;&nbsp;&nbsp;";
            }
            array_push($this->category_dropdown_name_array, $pre_stage . urldecode(stripslashes($show_category[$parent][$id]["category_name"])));
            array_push($this->category_dropdown_id_array, $id);
            if (isset($show_category[$id]) && (($this->stage + 1 <= $dropdown_limit) || ($dropdown_limit == 0))) {
                $this->stage++;
                $this->add_sub_categories_for_dropdown($show_category, $id);
                $this->stage--;
            }
        }
    }
    function getStatesOptions()
    {
        $db = true;
        include GEO_BASE_DIR . "get_common_vars.php";

        $result = $db->GetAll("select abbreviation,name from {$db->geoTables->states_table} order by abbreviation");
        if (false === $result) {
            $admin = geoAdmin::getInstance();
            $admin->userError();
            return "<option>An error occurred</option>";
        }

        $options = "";
        foreach ($result as $state) {
            $options .= "
				<option value=\"{$state['abbreviation']}\">{$state['abbreviation']} - {$state['name']}</option>";
        }
        return $options;
    }

    function getCountriesOptions()
    {
        $db = true;
        include GEO_BASE_DIR . "get_common_vars.php";

        $result = $db->GetAll("select abbreviation,name from {$db->geoTables->countries_table} order by abbreviation");
        if (false === $result) {
            $admin = geoAdmin::getInstance();
            $admin->userError("Error");
            return "<option>An error occurred</option>";
        }

        $options = "";
        foreach ($result as $country) {
            $options .= "
				<option value=\"{$country['name']}\">{$country['abbreviation']} - {$country['name']}</option>";
        }
        return $options;
    }
}
