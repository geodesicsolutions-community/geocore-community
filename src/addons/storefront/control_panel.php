<?php

//addons/storefront/control_panel.php

class geoStoreCP
{
    public function __construct()
    {
        //make sure we have JS libraries and CSS
        $view = geoView::getInstance();
        $view->addCssFile(geoTemplate::getUrl('css', 'addon/storefront/control_panel.css'));
        //using text everywhere, so may as well load it up here, too
        $view->msgs = geoAddon::getText('geo_addons', 'storefront');
    }

    public function display_pages($success)
    {
        $this->_commonHeaderDisplay($success);
        $tpl_vars = array();
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();
        $tables = $util->tables();
        $store_id = $util->owner;

        //get the storefront categories
        $sql = "SELECT `category_id`, `category_name` FROM " . $tables->categories . " WHERE `owner` = ? AND `parent` = 0 ORDER BY `display_order`";
        $categories = $db->GetAll($sql, array($store_id));

        $sql = "SELECT `category_id`, `category_name` FROM " . $tables->categories . " WHERE `owner` = ? AND `parent` = ? ORDER BY `display_order`";
        $getSubcategories = $db->Prepare($sql);

        $cats = array();
        foreach ($categories as $cat) {
            $cats[$cat['category_id']] = array(
                'url' => $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;category=' . $cat['category_id'],
                'category_name' => $cat['category_name'],
                'category_id' => $cat['category_id'],
            );

            $subs = $db->Execute($getSubcategories, array($store_id, $cat['category_id']));
            foreach ($subs as $sub) {
                $cats[$cat['category_id']]['subcategories'][$sub['category_id']] = $sub['category_name'];
            }
        }

        if (count($cats)) {
            $tpl_vars['categories'] = $this->_categories = $cats;
        }
        $tpl_vars['category_count'] = count($cats);

        $tpl_vars['home_cat'] = $util->home_link;

        //get the storefront pages
        $sql = "SELECT * FROM " . $tables->pages . " WHERE `owner` = ? ORDER BY `display_order`";
        $pages = $db->GetAll($sql, array($store_id));
        $storefront_pages = array();

        foreach ($pages as $page) {
            $storefront_pages[$page['page_id']] = array(
                'url' => $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;p=' . $page['page_id'],
                'link_text' => $page['page_link_text'],
                'page_id' => $page['page_id'],
                'name' => $page['page_name'],
                'body' => geoString::fromDB($page['page_body']),
                'display_order' => $page['display_order'],
                'selected' => (($page['page_id'] == $util->default_page) ? true : false)
            );
        }
        if (count($storefront_pages)) {
            $tpl_vars['pages'] = $storefront_pages;
        } else {
            $tpl_vars['no_pages'] = true;
        }


        $tpl_vars['page_count'] = count($storefront_pages);
        $tpl_vars['messages'] = geoAddon::getText('geo_addons', 'storefront');

        geoView::getInstance()->setBodyTpl('control_panel/pages.tpl', 'storefront')
            ->setBodyVar($tpl_vars);
    }

    public function update_pages($data)
    {
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();
        $tables = $util->tables();

        if (isset($_GET['create_pages']) && $_GET['create_pages']) {
            //reset to default pages
            //first, delete all existing pages
            $sql = "DELETE FROM `geodesic_addon_storefront_pages` WHERE `owner` = ?";
            $result = $db->Execute($sql, array($util->owner));
            if (!$result) {
                return false;
            }
            //now call the util's initial page setup function
            return $util->configureDefaultUserPages($util->owner);
        }

        if (isset($data['home_cat'])) {
            //updating top form

            //set home link
            $util->home_link = $data['home_cat'];
            $util->default_page = $data['default_page'];

            if (strlen($data['new_cat']) > 0) {
                //add a new category
                $sql = "INSERT INTO " . $tables->categories . " (owner, category_name) VALUES (?,?)";
                $result = $db->Execute($sql, array($util->owner, $data['new_cat']));
                if (!$result) {
                    return false;
                }
            }

            if (strlen($data['new_page']) > 0) {
                //add a new page
                $sql = "INSERT INTO " . $tables->pages . " (owner, page_link_text, page_name) VALUES (?,?,?)";
                $result = $db->Execute($sql, array($util->owner, $data['new_page'],$data['new_page']));
                if (!$result) {
                    return false;
                }
            }
            return true;
        }


        if (isset($_GET['del_cat']) && $_GET['del_cat'] > 0) {
            //delete a category
            $delete = intval($_GET['del_cat']);

            //first, if this is a subcategory, move any listings to its parent
            $parent = $db->GetOne("SELECT `parent` FROM " . $tables->categories . " WHERE `category_id` = ?", array($delete));
            if ($parent > 0) {
                $db->Execute("UPDATE " . geoTables::classifieds_table . " SET `storefront_category` = ? WHERE `storefront_category` = ? AND `seller` = ?", array($parent,$delete,$util->owner));
            }
            //now remove the actual requested category along with any subcategories
            $sql = "DELETE FROM " . $tables->categories . " WHERE owner = ? and (category_id = ? OR `parent` = ?)";
            $result = $db->Execute($sql, array($util->owner, $delete, $delete));
            if (!$result) {
                return false;
            } else {
                return true;
            }
        }

        if (isset($_GET['del_page']) && $_GET['del_page'] > 0) {
            $delete = intval($_GET['del_page']);
            //delete a category
            $sql = "DELETE FROM " . $tables->pages . " WHERE owner = ? and page_id = ?";
            $result = $db->Execute($sql, array($util->owner, $delete));
            if (!$result) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function display_customize($success)
    {
        $db = DataAccess::getInstance();
        $tpl_vars = array();
        $this->_commonHeaderDisplay($success);

        $util = geoAddon::getUtil('storefront');

        $tpl_vars['current_logo'] = $util->logo;

        $tpl_vars['logo_width'] = $util->logo_width;
        $tpl_vars['logo_height'] = $util->logo_height;
        $tpl_vars['logo_list_width'] = $util->logo_list_width;
        $tpl_vars['logo_list_height'] = $util->logo_list_height;
        $tpl_vars['welcome_message'] = $util->welcome_message;
        $tpl_vars['storefront_name'] = ($util->storefront_name) ? $util->storefront_name : geoUser::userName($util->owner);

        $store_templates = array();
        $user = geoUser::getUser($util->owner);
        $tpl_vars['current_template'] = $user->storefront_template_id;
        //get storefront templates

        //make sure the template ID is good.
        $file_templates = require(geoTemplate::getFilePath('main_page', 'attachments', 'templates_to_page/addons/storefront/home.php'));

        foreach ($file_templates[1] as $tpl_id => $data) {
            //figure out name
            $name = $data;
            if (strpos($name, '_lang') !== false) {
                $name = substr($name, 0, strpos($name, '_lang'));
            }
            //remove .tpl and turn underscores into spaces
            $name = str_replace(array('_','.tpl'), array(' ',''), $name);

            //grab everything after the final slash in the path (if there is one)
            $name = strpos($name, '/') !== false ? substr($name, strrpos($name, '/') + 1) : $name;

            $store_templates[] = array (
                    'template_id' => $tpl_id,
                    'name' => $name
            );
        }


        if (count($store_templates) == 1) {
            //only one template available
            $tpl_vars['single_template'] = $store_templates[0];
            $tpl_vars['template_choices'] = array();
        } else {
            $tpl_vars['template_choices'] = $store_templates;
        }

        $tpl_vars['messages'] = geoAddon::getText('geo_addons', 'storefront');


        geoView::getInstance()->setBodyTpl('control_panel/customize.tpl', 'storefront')
            ->setBodyVar($tpl_vars);
    }

    public function update_customize($data)
    {
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();
        $site = Singleton::getInstance('geoSite'); //used for checking mime types and badwords

        //upload a new image, if there is one
        $logo = $_FILES['logo'];
        if ($logo['name']) {
            $site->get_ad_configuration();
            $site->get_image_file_types_array();

            //on some servers, we need to move the temp file first, and THEN check the mime-type
            $delayCheck = ($site->ad_configuration_data->IMAGE_UPLOAD_TYPE == 1) ? true : false;

            if (!$delayCheck) {
                //make sure this fits site-wide allowed upload settings
                $mime = geoImage::getMimeType($logo['tmp_name'], $logo['name'], $logo['mime_type']);
                if (!$site->image_accepted_type($mime)) {
                    //bad mime type
                    return false;
                }
            }

            $logo_info = pathinfo($logo['name']);
            $tmp_file  = $logo['tmp_name'];
            $images_dir = dirname(__file__) . '/images/';

            $seed = 1;
            do {
                $seed = $seed * 10 + rand(0, 9); //add another digit to the end of the random portion of the filename
                $filename = 'logo' . $util->owner . '_' . $seed . "." . $logo_info['extension'];
            } while (is_file($images_dir . $filename));


            $destination = $images_dir . $filename;

            if (!is_writable($images_dir)) {
                return false;
            }

            $old_logo = $util->logo;

            if ($old_logo) {
                unlink($images_dir . $old_logo);
            }

            $image_uploaded = move_uploaded_file($tmp_file, $destination);
            $image_info = getimagesize($destination);

            if ($delayCheck) {
                $mime = $image_info['mime'];
                if (!$site->image_accepted_type($mime)) {
                    //bad mime type
                    return false;
                }
            }

            if ($image_uploaded) {
                //a new image was uploaded
                //use the new dimensions for width/height fields

                $util->logo = $filename;
                $util->logo_width = $image_info[0];
                $util->logo_height = $image_info[1];
                $util->logo_list_width = $image_info[0];
                $util->logo_list_height = $image_info[1];
            }
        } else {
            //no new image -- might be updating dimensions on an old one
            if (is_numeric($data['logo_width'])) {
                $util->logo_width = $data['logo_width'];
            }
            if (is_numeric($data['logo_height'])) {
                $util->logo_height = $data['logo_height'];
            }
            if (is_numeric($data['logo_list_width'])) {
                $util->logo_list_width = $data['logo_list_width'];
            }
            if (is_numeric($data['logo_list_height'])) {
                $util->logo_list_height = $data['logo_list_height'];
            }
        }

        //check list sizes against admin max
        $reg = geoAddon::getRegistry('storefront');
        if ($reg) {
            //list stores page
            $max_width = $reg->max_logo_width;
            $max_height = $reg->max_logo_height;
            $old_width = $util->logo_list_width;
            $old_height = $util->logo_list_height;

            if ($max_width && $old_width > $max_width) {
                $reductionRatio = $old_width / $max_width;
                $new_width = $max_width;
                $new_height = round($old_height / $reductionRatio);

                //changed dimensions, so update "old" vars before checking height
                $old_width = $new_width;
                $old_height = $new_height;
            }
            if ($max_height && $old_height > $max_height) {
                $reductionRatio = $old_height / $max_height;
                $new_height = $max_height;
                $new_width = round($old_width / $reductionRatio);
            }
            if ($new_width) {
                $util->logo_list_width = $new_width;
            }
            if ($new_height) {
                $util->logo_list_height = $new_height;
            }

            //logo inside stores
            $new_width = $new_height = false; //clear vars
            $max_width = $reg->max_logo_width_in_store;
            $max_height = $reg->max_logo_height_in_store;
            $old_width = $util->logo_width;
            $old_height = $util->logo_height;

            if ($max_width && $old_width > $max_width) {
                $reductionRatio = $old_width / $max_width;
                $new_width = $max_width;
                $new_height = round($old_height / $reductionRatio);

                //changed dimensions, so update "old" vars before checking height
                $old_width = $new_width;
                $old_height = $new_height;
            }
            if ($max_height && $old_height > $max_height) {
                $reductionRatio = $old_height / $max_height;
                $new_height = $max_height;
                $new_width = round($old_width / $reductionRatio);
            }
            if ($new_width) {
                $util->logo_width = $new_width;
            }
            if ($new_height) {
                $util->logo_height = $new_height;
            }
        }

        //update storefront name (don't allow any html)
        $newName = strip_tags(geoString::specialCharsDecode($data['storefront_name']));
        //also, check for / remove badwords
        $newName = trim($site->check_for_badwords($newName));

        if ($newName) {
            //double-check availability (ajax does that earlier, but someone could Man-in-the-Middle it if he really wanted to)
            if (is_numeric($newName)) {
                //pure-numeric store names won't fly
                return false;
            }
            $sql = "select username from geodesic_userdata where username = ? and id <> ?";
            $result = $db->Execute($sql, array($newName, $util->owner));
            if ($result->RecordCount() > 0) {
                //this is someone else's username
                return false;
            }

            //assign to display value
            $show_name = $newName;

            //now format the name for display in URLs
            if ($newName) {
                $newName = preg_replace("/[^a-zA-Z0-9_]+/", ' ', $newName); //replace any invalid characters with whitespace
                $newName = preg_replace("/\s+/", '-', $newName); //replace any whitespace with hyphens

                //check cleaned name against other names already stored in the DB.
                $sql = "select seo_name from geodesic_addon_storefront_user_settings where seo_name = ? AND owner <> ?";
                $result = $db->Execute($sql, array($newName, $userid));
                if ($result->RecordCount() > 0) {
                    //name already in use
                    return false;
                }
                $seo_name = $newName;
            }
        } else {
            //store name blank -- default to username
            $newName = geoUser::userName($util->owner);
            $show_name = $newName;
            //clean it for the URL
            $newName = preg_replace("/[^a-zA-Z0-9_]+/", ' ', $newName); //replace any invalid characters with whitespace
            $newName = preg_replace("/\s+/", '-', $newName); //replace any whitespace with hyphens

            //make sure this isn't a duplicate of someone else's name. if for some odd reason it is, add hyphens to the end until it is not
            $checkName = $db->Prepare("select seo_name from geodesic_addon_storefront_user_settings where seo_name = ? AND owner <> ?");
            do {
                $nameExists = $db->Execute($checkName, array($newName, $userid))->RecordCount() > 0;
                $newName = ($nameExists) ? $newName . '-' : $newName;
            } while ($nameExists);

            $seo_name = $newName;
        }
        //update names
        $util->storefront_name = $show_name; //"pretty" version of the name used for tag-replacement
        $util->seo_name = $seo_name; //"safe" version of the name for use in SEF urls

        //update welcome note
        $newNote = geoFilter::replaceDisallowedHtml($data['welcome_note']);
        $util->welcome_message = geoString::specialCharsDecode($newNote);

        if (isset($data['storefrontTemplate'])) {
            $id = intval($data['storefrontTemplate']);
            $user = geoUser::getUser($util->owner);
            $user->storefront_template_id = $id;
        }

        return true;
    }

    public function display_newsletter($success)
    {
        $this->_commonHeaderDisplay($success);
        $tpl_vars = array();
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();

        $tpl_vars['display_newsletter'] = $util->display_newsletter;

        $sql = "select * from `geodesic_addon_storefront_users` where `store_id` = ? order by `user_email` ASC";
        $result = $db->Execute($sql, array($util->owner));
        if (!$result) {
            die($db->ErrorMsg());
        }
        $tpl_vars['current_sub_count'] = $result->RecordCount();
        if ($result->RecordCount() > 0) {
            $emails = array();
            while ($line = $result->FetchRow()) {
                $emails[] = $line['user_email'];
            }
            $tpl_vars['emails'] = $emails;
        }
        $tpl_vars['messages'] = geoAddon::getText('geo_addons', 'storefront');
        geoView::getInstance()->setBodyTpl('control_panel/newsletter.tpl', 'storefront')
            ->setBodyVar($tpl_vars);
    }

    public function update_newsletter($data)
    {
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();

        if (isset($data['display_newsletter'])) {
            //update options
            $util->display_newsletter = ($data['display_newsletter'] == 1) ? 1 : 0;

            if ($data['do_remove'] == 1) {
                //remove users
                $remove = $data['removeThese'];
                foreach ($remove as $r) {
                    $sql = "delete from geodesic_addon_storefront_users where store_id = ? and user_email = ?";
                    $result = $db->Execute($sql, array($util->owner, $r));
                    if (!$result) {
                        return false;
                    }
                }
            }


            return true;
        }

        if (isset($data['newsletter_body'])) {
            //send email newsletter to subscribers
            $db = DataAccess::getInstance();

            //undo input filtering, so we can recognize html tags
            $content = geoString::specialCharsDecode($data['newsletter_body']);
            //now check for bad html
            $content = geoFilter::replaceDisallowedHtml($content);

            //decode subject, and then remove ALL tags (since HTML is worthless in a subject)
            $subject = trim(strip_tags(geoString::specialCharsDecode($data['newsletter_subject'])));

            $owner = $util->owner;
            $from = geoUser::getData($owner, 'email');

            //get subscribers
            $tables = $util->tables();
            $sql = "SELECT * FROM " . $tables->users . " WHERE store_id = ?";
            $result = $db->Execute($sql, array($owner));
            if ($result === false) {
                die('Error:' . $db->ErrorMsg());
            }
            $storefrontSubscribers = array();
            while ($emailAddress = $result->FetchRow()) {
                $storefrontSubscribers[] = $emailAddress["user_email"];
            }

            $type = (strlen($content)) == strlen(strip_tags($content)) ? 'text/plain' : 'text/html';
            foreach ($storefrontSubscribers as $to) {
                geoEmail::sendMail($to, $subject, $content, $from, 0, 0, $type);
            }
            return true;
        }
    }

    public function display_main($success)
    {
        $this->_commonHeaderDisplay($success);
        $tpl_vars = array();
        $db = DataAccess::getInstance();
        $util = geoAddon::getUtil('storefront');
        $tables = $util->tables();
        $store_id = $util->owner;

        //traffic reports
        $reg = geoAddon::getRegistry('storefront');
        if ($reg->get('show_traffic', 1)) {
            $r = $db->GetRow("SELECT sum(uvisits) u,sum(tvisits) t FROM {$tables->traffic} WHERE `owner`=?", array($store_id));
            $tpl_vars['tvisits'] = $r['t'];
            $tpl_vars['uvisits'] = $r['u'];


            $traffic = array();
            $day = 60 * 60 * 24;
            //get traffic data

            //last 30 days, by day
            $traffic['lastMonth'] = $this->_assembleTrafficData($store_id, $day, 30);

            //last year, by month
            $traffic['lastYear'] = $this->_assembleTrafficData($store_id, $day * 30, 12);

            //last 3 years, by year
            $traffic['lastThreeYears'] = $this->_assembleTrafficData($store_id, $day * 365, 3);
            $tpl_vars['traffic'] = $traffic;
            $tpl_vars['show_traffic'] = true;
        } else {
            $tpl_vars['show_traffic'] = false;
        }

        $tpl_vars['date_format'] = $db->get_site_setting('date_field_format_short');

        trigger_error('DEBUG STOREFRONT TRAFFIC DISPLAY:  count of traffic array:<br>' . count($traffic) . "<br>show traffic: " . $tpl_vars['show_traffic']);

        geoView::getInstance()->setBodyTpl('control_panel/main.tpl', 'storefront')
            ->setBodyVar($tpl_vars);
    }

    /**
     * Does all the fancy number cruching to populate data for traffic tables.
     *
     * Looks backwards a given number of periods of given size.
     * For example: assembleTrafficData( (60*60*24), 30) would return data from the last 30 days, grouped by day
     *
     * @return Array $return as follows:
     * (
     *      'max' => highest number of total visits in this data set
     *      'periods' => Array(
     *          'from' => timestamp of period start time
     *          'to' => timestamp of period end time
     *          'total' => total visits during period
     *          'unique' => unique visits during period
     *      )
     * )
     */

    private function _assembleTrafficData($store_id, $period, $numPeriods)
    {
        trigger_error('DEBUG STOREFRONT TRAFFIC DISPLAY: top of _assembleTrafficData<br>store_id: ' . $store_id . "<Br>period: " . $period . "<br>numPeriods: " . $numPeriods);
        //first, some common data sources
        $db = DataAccess::getInstance();
        $util = geoAddon::getUtil('storefront');
        $tables = $util->tables();

        //and some arrays to put stuff in!
        $starts = $return = array();

        //figure out earliest timestamp
        $lookback = $numPeriods * $period;
        $firstTime = $util->timeToDate(geoUtil::time()) - $lookback;

        $sql = "SELECT * FROM " . $tables->traffic . " WHERE `owner`=? AND `time` >= ? ORDER BY `time` DESC";
        trigger_error('DEBUG STOREFRONT TRAFFIC DISPLAY:  Running:<br>' . $sql . "<br>store_id: " . $store_id . "<br>firsttime: " . $firstTime);
        $data = $db->GetAll($sql, array($store_id, $firstTime));

        if (!$data) {
            //no traffic data yet
            return false;
        }

        //get starting timestamps for this group of periods (e.g. the earliest timestamp for each day of the month)
        for ($i = 0; $i < $numPeriods; $i++) {
            $starts[$i] = $util->timeToDate($firstTime + ($period * $i));
        }

        foreach ($data as $record) {
            //figure out which period this record goes in
            $slot = false;
            foreach ($starts as $s) {
                if (($record['time'] >= $s) && ($record['time'] < ($s + $period))) {
                    //period is in this time slot
                    $slot = $s;
                    break;
                }
            }
            //now slot record's data into that period
            if ($slot) {
                $return['periods'][$slot]['total'] += $record['tvisits'];
                $return['periods'][$slot]['unique'] += $record['uvisits'];
            } else {
                //something's wrong -- didn't find a start time for this period
            }
        }

        //set start/end times for each time bracket, so template can find them easily
        $max = 0;
        foreach ($return['periods'] as $key => $ret) {
            $return['periods'][$key]['from'] = $key;
            $return['periods'][$key]['to'] = $key + $period - 1;

            //find the size of the largest data point, for calculating a bar of 100% width
            if ($ret['total'] > $max) {
                $max = $ret['total'];
            }
        }
        $return['max'] = $max;

        return $return;
    }

    public function update_main($data)
    {
        //update store on/off switch
        $user = geoUser::getUser(geoSession::getInstance()->getUserId());
        $user->storefront_on_hold = ($data['store_on'] == 1) ? 0 : 1;
        $db = DataAccess::getInstance();

        $sql = "select expiration, onhold_start_time, recurring_billing from geodesic_addon_storefront_subscriptions where user_id = ?";
        $row = $db->GetRow($sql, array($user->id));

        //log time turned off, or refund for time spent off
        if ($data['store_on'] == 1) {
            //just turned store on -- figure out how long it's been off for and add to subscription

            $onhold_began = $row['onhold_start_time'];
            $recurringId = (int)$row['recurring_billing'];

            if ($onhold_began > 0) {
                $onhold_ended = geoUtil::time();
                $onholdDuration = ($onhold_ended >= $onhold_began && $recurringId == 0) ? ($onhold_ended - $onhold_began) : 0;
                $newExpiration = $row['expiration'] + $onholdDuration;
                $sql = "update geodesic_addon_storefront_subscriptions set expiration = ?, onhold_start_time = 0 where user_id = ?";
                $result = $db->Execute($sql, array($newExpiration, $user->id));
                if (!$result) {
                    return false;
                }
            }
        } elseif ($row['recurring_billing'] == 0) {
            //turning store off -- mark current time so we can later refund the time the store was off
            $timeTurnedOff = geoUtil::time();
            $sql = "update geodesic_addon_storefront_subscriptions set onhold_start_time = ? where user_id = ?";
                $result = $db->Execute($sql, array($timeTurnedOff, $user->id));
            if (!$result) {
                return false;
            }
        }

        return true;
    }


    private function _commonHeaderDisplay($success)
    {
        $view = geoView::getInstance();

        //so WYSIWYG goes
        $view->editor = $view->forceEditor = true;

        //get WYSIWYG height for expando-pants box
        $view->editorHeight = DataAccess::getInstance()->get_site_setting('desc_wysiwyg_height');

        if ($_GET['action'] === 'update') {
            //updated stuff -- report on success
            $tpl = new geoTemplate('addon', 'storefront');
            $tpl->assign('show', $success);
            $tpl->assign('msgs', geoAddon::getText('geo_addons', 'storefront'));
            $html = $tpl->fetch('control_panel/success_fail.tpl');
            $view->success_fail = $html;
        }

        //find out if store is turned on or off
        $view->user_id = $user_id = geoSession::getInstance()->getUserId();
        $user = geoUser::getUser($user_id);
        $view->store_is_on = ($user->storefront_on_hold == 0) ? true : false;

        //set page to go back to after form submits
        if ($_POST['data']['fromPage']) {
            $view->action_type = $_POST['data']['fromPage'];
        } elseif ($_GET['action_type']) {
            $view->action_type = $_GET['action_type'];
        } else {
            $view->action_type = 'main';
        }

        //check registry to see if newsletters are enabled
        $reg = geoAddon::getRegistry('storefront');
        $view->show_newsletter = $reg->get('allow_newsletter', 1);
        $view->show_traffic = $reg->get('show_traffic', 1);
    }

    public function doAjax($post_data)
    {
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();
        $tables = $util->tables();
        $store_id = $util->owner;

        //process form changes

        //adding a subcategory
        if ($post_data['action'] === 'add_subcategory') {
            $parent = $post_data['parent'];
            $newName = $post_data['name'];
            $sql = "INSERT INTO " . $tables->categories . " (owner, category_name, parent) VALUES (?,?,?)";
            $result = $db->Execute($sql, array($store_id, $newName, $parent));
            return; //return regardless of result. page will reload and show modified data.
        }

        if ($post_data['action'] === 'edit_subcategory') {
            $id = $post_data['edit'];
            $newName = $post_data['name'];
            $sql = "UPDATE " . $tables->categories . " SET `category_name` = ? WHERE `owner` = ? AND `category_id` = ?";
            $result = $db->Execute($sql, array($newName, $store_id, $id));
            echo ($result) ? $newName : $post_data['name']; //return what the name is now, changed or not
            return;
        }

        //Category Order
        if (isset($post_data['category_order'])) {
            $orders = explode('&amp;', $post_data['category_order']);

            foreach ($orders as $key => $value) {
                $orders[$key] = substr($value, (strpos($value, '=') + 1));
            }

            //now $orders is an array of IDs, in the user's order
            $display_order = 1;
            foreach ($orders as $cat_id) {
                $sql = "UPDATE " . $tables->categories . " SET display_order = ? WHERE category_id = ? AND owner = ?";
                $result = $db->Execute($sql, array($display_order, $cat_id, $store_id));
                if (!$result) {
                    //echo 'fail: '.$db->ErrorMsg().'<br />';
                }
                $display_order++;
            }
            return true;
        }

        //Other Pages Order
        if (isset($post_data['page_order'])) {
            $orders = explode('&amp;', $post_data['page_order']);
            foreach ($orders as $key => $value) {
                $orders[$key] = substr($value, (strpos($value, '=') + 1));
            }

            //now $orders is an array of IDs, in the user's order
            $display_order = 1;
            foreach ($orders as $page_id) {
                $sql = "UPDATE " . $tables->pages . " SET display_order = ? WHERE page_id = ? AND owner = ?";
                $result = $db->Execute($sql, array($display_order, $page_id, $store_id));
                if (!$result) {
                    //echo 'fail: '.$db->ErrorMsg().'<br />';
                }
                $display_order++;
            }
            return true;
        }

        //Category Name
        if (isset($post_data['cat_id']) && is_numeric($post_data['cat_id'])) {
            $newName = trim($post_data['new_name']);
            if (strlen($newName) > 0) {
                $sql = "UPDATE " . $tables->categories . " SET category_name = ? WHERE category_id = ? AND owner = ?";
                $result = $db->Execute($sql, array($newName, $post_data['cat_id'], $store_id));
                if (!$result) {
                    //query error -- grab old name and return that
                    echo 'ERROR';
                } else {
                    //return new name to script
                    // (this is the case that should happen most often)
                    echo $newName;
                }
            } else {
                //invalid input -- grab old name and return that
                $sql = "SELECT category_name FROM " . $tables->categories . " WHERE category_id = ? and owner = ?";
                echo $db->GetOne($sql, array($post_data['cat_id'], $store_id));
            }
            return true;
        }

        //Other Page name and body
        if (isset($post_data['page_id']) && is_numeric($post_data['page_id'])) {
            $newName = trim($post_data['new_name']);
            $newLink = trim($post_data['new_link']);
            $newBody = geoString::specialCharsDecode(($post_data['new_body']));

            $return = '';

            //update name
            if (strlen($newName) > 0) {
                $sql = "UPDATE " . $tables->pages . " SET page_name = ? WHERE page_id = ? AND owner = ?";
                $result = $db->Execute($sql, array($newName, $post_data['page_id'], $store_id));
                if (!$result) {
                    //query error
                    $return = 'ERROR';
                } else {
                    //return new name to script
                    // (this is the case that should happen most often)
                    $return .= $newName;
                }
            } else {
                //cannot have a blank name -- use the old one
                $sql = "SELECT page_name FROM " . $tables->pages . " WHERE page_id = ? and owner = ?";
                $return = $db->GetOne($sql, array($post_data['page_id'], $store_id));
            }

            $return .= '~~!~~';

            //update link text
            if (strlen($newName) > 0) {
                $sql = "UPDATE " . $tables->pages . " SET page_link_text = ? WHERE page_id = ? AND owner = ?";
                $result = $db->Execute($sql, array($newLink, $post_data['page_id'], $store_id));
                if (!$result) {
                    //query error
                    $return = 'ERROR';
                } else {
                    //return new link text to script
                    // (this is the case that should happen most often)
                    $return .= $newLink;
                }
            } else {
                //cannot have a blank link -- use the old one
                $sql = "SELECT page_link_text FROM " . $tables->pages . " WHERE page_id = ? and owner = ?";
                $return = $db->GetOne($sql, array($post_data['page_id'], $store_id));
            }

            $return .= '~~!~~';

            //update body
            //no "if(strlen)" here, so body can optionally be set blank!
            $sql = "UPDATE " . $tables->pages . " SET page_body = ? WHERE page_id = ? AND owner = ?";
            $result = $db->Execute($sql, array(geoString::toDB($newBody), $post_data['page_id'], $store_id));

            //regardless of result, pull whatever is now the page body to return to page
            $sql = "SELECT page_body FROM " . $tables->pages . " WHERE page_id = ? and owner = ?";
            $return .= geoString::fromDB($db->GetOne($sql, array($post_data['page_id'], $store_id)));



            //return data to ajax call
            echo $return;

            return true;
        }
    }
}
