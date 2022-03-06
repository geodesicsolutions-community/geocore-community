<?php

if (!class_exists('admin_AJAX')) {
    exit;
}


class addon_SEO_ADMIN_ajax extends admin_AJAX
{
    public $registry_id;
    private $settings;
    private $item_name;
    private $item_number;
    private $custom_value;
    private $current_order;
    private $value;
    private $status;
    private $update_status;
    private $current_text;
    private $hide;
    private $temp_registry_id;
    protected $mode;
    private $geti, $use_old_redirects;

    private $_allow_conflicts = false;


    public function replaceAccents()
    {
        $cjax = geoCJAX::getInstance();

        //get setting
        $accents = ($cjax->get('replaceAccents')) ? 1 : false;

        //save setting
        $reg = geoAddon::getRegistry('SEO');
        $reg->replaceAccents = $accents;
        $reg->save();
        $message = ($accents) ? 'convert accented characters' : 'remove accented characters';
        geoAdmin::m('Saved changes, will now ' . $message . ' found in titles in re-written URLs.', geoAdmin::SUCCESS, 4);
    }
    public function replaceAnd()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $cjax = geoCJAX::getInstance();

        //get setting
        $and = $and_ = $cjax->get('replaceAnd');

        //clean it
        $util = geoAddon::getUtil('SEO');
        $and = $util->cleanish($and);
        if ($and != $and_) {
            //let user know we had to clean it
            $cjax->alert('Invalid characters (only a-z, 0-9, and - allowed), "' . geoString::specialChars($and_) . '" has been changed to "' . $and . '".');
        }
        $reg = geoAddon::getRegistry('SEO');
        $reg->replaceAnd = $and;
        $reg->save();

        $cjax->set_value('replaceAnd', $and);

        geoAdmin::m('Saved changes, now any &quot;&amp;&quot; found in re-written URLS will be replaced with ' . $and, geoAdmin::SUCCESS, 4);
    }

    public function includeParentCategoryName()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $cjax = geoCJAX::getInstance();

        //get setting
        $include_parent_name = ($cjax->get('includeParentCategoryName')) ? 1 : false;

        //save setting
        $reg = geoAddon::getRegistry('SEO');
        $reg->includeParentCategoryName = $include_parent_name;
        $reg->save();
        $message = ($include_parent_name) ? 'Parent category names will be included in front of category names' : 'Parent category names will NOT be included in front of category names';
        geoAdmin::m('Saved changes, ' . $message, geoAdmin::SUCCESS, 4);
    }

    public function onOff()
    {
        //special case, don't use _changeSetting()
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = 'install';
        $s = $this->get('settings');
        $set = ($CJAX->get('rewrite_urls')) ? 1 : 0;
        $s['continue'] = $set;
        $s['skip'] = 1;
        //if they are turning on/off they have already switched over to
        //use settings "normally", not use old 1.0 templates for re-writting.
        $s['type'] = 1;
        $this->set('settings', $s);
        $this->save();
        $onoff = ($set) ? 'On' : 'Off';
        $admin->userSuccess('Re-Writing URLs are turned ' . $onoff);
        $CJAX->message($admin->getUserMessages(), 3);

        return;
    }

    public function useOldRedirects()
    {
        $set = $this->_changeSetting('use_old_redirects');
        $onoff = ($set) ? 'be used' : 'NOT be used';
        geoAdmin::m('When generating .htaccess file, re-directs for SEO 1.0 URLs will ' . $onoff, geoAdmin::SUCCESS, true);

        return;
    }

    public function forceSeoUrls()
    {
        $set = $this->_changeSetting('force_seo_urls');
        $onoff = ($set) ? 'on.' : 'off.';
        geoAdmin::m('Forcing SEO URLs turned ' . $onoff, geoAdmin::SUCCESS, true);

        return;
    }

    public function omitSymlink()
    {
        $set = $this->_changeSetting('omit_symlink');
        $onoff = ($set) ? 'omited.' : 'inserted.';
        geoAdmin::m('The FollowSymlinks line in the .htaccess is now ' . $onoff, geoAdmin::SUCCESS, true);

        return;
    }

    private function _changeSetting($setting_name)
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = 'install';
        $s = $this->get('settings');
        $set = ($CJAX->get($setting_name)) ? 1 : 0;
        $s[$setting_name] = $set;
        $this->set('settings', $s);
        $this->save();
        return $set;
    }
    public function WizardGenerateAll()
    {
        $this->mode = 'silence';
        $this->generateAll();
        $this->CreateHtaccess();
        include GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    public function goLive()
    {
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = 'install';
        $s = $this->get('settings');
        $s['continue'] = 1;
        $this->set('settings', $s);
        $this->save();
        //$this->mode = 'silence';
    //  $this->generateAll();
        //$this->CreateHtaccess();
        $CJAX->location('index.php?mc=addon_cat_SEO&page=addon_SEO_main_config');
        return;
    }

    public function firstTimeUsing()
    {
        $CJAX = geoCJAX::getInstance();
        //do stuff here, whether is a fresh install, or a new instalaltion.

        $install_type = $CJAX->get('type');

        $this->registry_id = 'install';
        $s = $this->get('settings');

        $s['type'] = $install_type;
        $s['continue'] = 1;

        $this->set('settings', $s);
        $this->save();

        $CJAX->location('index.php?mc=addon_cat_SEO&page=addon_SEO_main_config');
        exit();
    }

    public function generateAll()
    {
        $admin = $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $CJAX = geoCJAX::getInstance();
        if (!$this->geti) {
            $this->registry_id = 'install';
            $install_settings = $this->get('settings');
            $this->use_old_redirects = $install_settings['use_old_redirects'];
        }

        $this->registry_id = 'settings';
        $items = array_keys($this->get('items'));

        if (!$this->geti && !$this->checkUrls()) {
            return;
        }
        $template = '';
        $indexfile = $db->get_site_setting('classifieds_file_name');
        $site =  str_replace($indexfile, '', $db->get_site_setting('classifieds_url'));
        $out = array();
        foreach ($items as $setting_name) {
            $this->registry_id = $setting_name;
            $this->initRegistry();
            $setting['title'] = $this->get('title');
            $setting['text'] = $this->get('custom_text');
            $setting['order'] = $this->get('order');
            $setting['name'] = $this->get('name');
            $setting['status'] = $this->get('status');
            $setting['custom_text'] = $this->get('custom_text');
            $setting['type'] = $this->get('type');
            $setting['regex'] = $this->get('regex');
            $setting['regexhandler'] = $this->get('regexhandler');
            $setting['extension'] = $this->get('extension');

            $dash_name = str_replace(' ', '_', $setting_name);
            $part_count = 0;
            foreach ($setting['title'] as $item => $order_n) {
                if ($setting['status'][$item] != 0) {
                    $title[$setting['order'][$item]] = $order_n;
                    $title_by_name[$item] = $order_n;
                    $part_count++;
                }
            }
            if (strlen($setting['extension']) > 0) {
                $part_count++;
            }
            ksort($title, SORT_DESC);
            $new_text = $tpl_text = implode('/', $title);

            if (!$this->geti) {
                //applying changes
                foreach ($setting['title'] as $t => $n) {
                    if (strpos($t, 'custom_text') !== false) {
                        if ($setting['text'][$t]) {
                            $tpl_text = str_replace($n, $setting['text'][$t], $tpl_text);
                        }
                    }
                }

                $extension = (isset($setting['extension'])) ? $setting['extension'] : '';
                $setting['url_template'] = $tpl_text . $extension;

                $this->set('url_template', $tpl_text . $extension);
                $this->save();
                //echo 'setting_name: '.$setting_name."\n";

                if ($this->mode != 'silence') {
                    $count++;
                    $CJAX->update($dash_name . '_path', $site);
                    $search = array ('/\(![^!]+\_PAGE_ID!\)/','/\(![^!]+\_ID!\)/','/\(![^!]+\_TITLE!\)/');
                    $replace = array('3','456','Title_abc_123');
                    $template_url = preg_replace($search, $replace, $tpl_text) . $extension;
                    $CJAX->update($dash_name, $template_url);
                    //making sure this happends only one , because we do not need this items
                    //to be updated over and over
                    if ($count == count($items)) {
                        //echo "\ncount: $count = ".count($items)."\n";
                        $CJAX->update('updates_your_htaccess', "Update your .htaccess file now.");
                        $CJAX->update('confirm_generate_all', '');
                        //anything after click() will not work! (I believe is it because the click event gets all the attention)
                        $admin->userNotice('URL Settings applied to site.');
                        $admin->userNotice('Next Step:  Update your .htaccess file with the generated HTACCESS rules below.
						<br /><br />Failure to do this may result in broken links!');
                        $CJAX->update('confirm_generate_all', $admin->getUserMessages());
                        //$CJAX->message($admin->getUserMessages(),10);
                        $this->CreateHtaccess();
                    }
                }
            } else {
            //Creating for ... CreateHtaccess()
                $handler = $setting['regexhandler'];
                $group = 0;
                $orders = $setting['order'];
                asort($orders);
                foreach ($orders as $t_name => $order) {
                    if ($setting['status'][$t_name]) {
                        $setting['text'][$t_name] = trim($setting['text'][$t_name]);
                        if (strpos($t_name, 'custom_text') !== false) {
                            if ($setting['text'][$t_name]) {
                                $group++;
                                $new_text = str_replace($setting['title'][$t_name], '(' . $setting['text'][$t_name] . ')', $new_text);
                            }
                        } else {
                            //alert('tname:'.$t_name);
                            $group++;
                            $handler = str_replace("(!$t_name!)", '$' . $group, $handler);
                            $new_text = str_replace($setting['title'][$t_name], $setting['regex'][$t_name], $new_text);
                        }
                    }
                }

                if (isset($setting['extension'])) {
                    $extension = str_replace('.', '\.', $setting['extension']);
                }
                if ($this->geti) {
                    $out[$part_count][$setting_name]['template'] = $new_text . $extension;

                    if (!$out[$part_count][$setting_name]['regexhandler']) {
                        $out[$part_count][$setting_name]['regexhandler'] = $handler;
                    }

                    if (!$out[$part_count][$setting_name]['registry']) {
                        $out[$part_count][$setting_name]['registry'] = $setting_name;
                    }
                    //$out[$part_count][$setting_name]['regexhandler'] = $regexh;
                }
            }

            unset($title);
            unset($txt);
            unset($t_name);
        }
        if ($this->geti) {
            ksort($out);
            return $out;
        }
    }

    public function CreateHtaccess()
    {
        $CJAX = geoCJAX::getInstance();

        $this->registry_id = 'install';
        $install_settings = $this->get('settings');

        $this->registry_id = 'settings';
        $settings = $this->get('items');

        $db = DataAccess::getInstance();
        $this->checkUrls();
        $indexfile = $db->get_site_setting('classifieds_file_name');
        $sitepath =  str_replace($indexfile, '', $db->get_site_setting('classifieds_url'));
        $sitepath = preg_replace("/https?\:\/\/([^\/]+)\//", '', $sitepath);

        //getting rid of any extra slashes
        $sitepath = rtrim($sitepath, '/');
        $tpl = new geoTemplate('addon', 'SEO');

        $tpl->assign('sitepath', $sitepath);
        $tpl->assign('indexfile', $indexfile);
        $oldGeti = $this->geti;
        $this->geti = true;
        $this->use_old_redirects = $install_settings['use_old_redirects'];

        $items = $this->generateAll();
        $this->geti = $oldGeti;
        $tpl->assign('install_settings', $install_settings);

        if ($install_settings['use_old_redirects']) {
            $tpl->assign('index_regex', str_replace('.php', '(\.php)?', $indexfile));
        }
        $tpl->assign('items', $items);
        //echo '<pre>'.print_r($items,1).'</pre>';

        $return = '<textarea style="width: 100%;" rows="40" id="htaccessTextarea" readonly="readonly">' . $tpl->fetch('generate_contents.tpl') . '</textarea>';
        if (isset($_GET['inWizard'])) {
            echo $return;
        } else {
            //old school way
            $CJAX->update('htaccess', $return);
        }
        $reg = geoAddon::getRegistry('SEO');
        if ($reg->useUnderscore) {
            //it USED to be using underscores, but not any more now that the .htaccess has been re-written
            $reg->useUnderscore = false;
            $reg->save();
        }
        return;
    }




    public function quit()
    {
        include GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    public function generateAllConfirm()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $CJAX = geoCJAX::getInstance();
        $generate_all = $CJAX->call("AJAX.php?controller=addon_SEO&action=generateAll");
        $CJAX->update('confirm_generate_all', '');
        $quit = $CJAX->call("AJAX.php?controller=addon_SEO&action=quit", 'confirm_generate_all');
        echo "<div style='border: 2px solid red;'>"
            . geoHTML::addOption(
                "Are you sure you want to apply all settings?",
                geoHTML::addButton("Yes", $generate_all, true) . "&nbsp;&nbsp;" . geoHTML::addButton("No", $quit, true),
                "This will make any changes you've made go \"live\", and will require that you update your .htaccess file."
            )
        . "</div>";
        //echo geoHTML::addOption("Are you sure you want to apply all settings?",$message);
    }

    public function extension()
    {
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = $CJAX->get('r_id');
        $new_ext = $CJAX->get('ext');

        if (!$this->registry_id) {
            $CJAX->alert('Invalid registry id');
        }

        if ($new_ext == '~~edit_me~~') {
            //hide text
            $CJAX->hide('span_ext_text');

            //Change input to be the current extension
            $CJAX->set_value('ext', ltrim($this->get('extension'), '.'));
            //display it
            $CJAX->show('span_ext');

            $CJAX->focus('ext');
            $this->getCurrentUrl();
            include GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }
        $new_ext = trim($new_ext);
        if (strlen($new_ext) > 0) {
            //clean it
            $util = geoAddon::getUtil('SEO');
            $new_ext = $util->revise($new_ext, array('.'));
            //make sure it starts with a .
            $new_ext = '.' . ltrim($new_ext, '.');
        }


        $this->set('extension', $new_ext);
        $this->save();

        if (strlen($new_ext) == 0) {
            //if extension not set, make it display - so they know
            //it's not just broken.
            $new_ext = 'N/A';
        }
        //hide input and save button
        $CJAX->hide('span_ext');
        //set it back to edit me
        $CJAX->set_value('ext', '~~edit_me~~');
        //update static text
        $CJAX->update('span_ext_text', $new_ext);
        //display text
        $CJAX->show('span_ext_text');

        $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $admin->userSuccess("Setting saved.");
        $msg  = $admin->getUserMessages();
        $CJAX->message($msg, 3);
        echo $new_ext;
        $this->getCurrentUrl();
    }

    /**
     * Checks settings to see if there are conflicts.
     *
     * @param bool $show_errors If false, will not display errors using cjax.
     * @return bool
     */
    public function checkUrls($show_errors = true)
    {
        if ($this->_allow_conflicts) {
            //do not check for conflicts
            return true;
        }

        $admin = $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = 'settings';
        $items = array_keys($this->get('items'));

        //First, create an array of URL's to look through, like so:
        /*
         * array (
         *  part_count => int,
         *  parts => array (
         *      'name' => 'regex_int',
         *      'name' => 'regex_title',
         *      'name' => 'custom_text'
         *  ),
         *  ext => '.extension'
         * )
         */
        $urls = array();
        //make sure SEO addon util is included
        geoAddon::getUtil('SEO');
        $regex_title = addon_SEO_util::REGEX_TITLE;
        $regex_number = addon_SEO_util::REGEX_NUMBER;
        foreach ($items as $s_name) {
            $this->registry_id = $s_name;

            $title = $this->get('title');
            $order = $this->get('order');
            $status = $this->get('status');
            $custom_text = $this->get('custom_text');
            $regex = $this->get('regex');
            $extension = $this->get('extension');

            $count = 0;
            $parts = array();
            foreach ($title as $item => $title) {
                if ($status[$item] != 0) {
                    $count++;
                    if (strpos($item, 'custom_text') !== false) {
                        //custom text
                        $parts[$order[$item]] = $custom_text[$item];
                    } else {
                        if ($regex[$item] == $regex_number) {
                            $parts[$order[$item]] = 'REGEX_NUMBER';
                        } else {
                            $parts[$order[$item]] = 'REGEX_TITLE';
                        }
                    }
                }
            }
            //sort it
            ksort($parts);
            //get rid of un-used order ids
            $new_parts = array();
            foreach ($parts as $part) {
                $new_parts[] = $part;
            }
            $urls[$count][$s_name] = array (
                'parts' => $new_parts,
                'ext' => $extension
            );
        }
        $compared = array();
        $filename = str_replace('.php', '', $db->get_site_setting('classifieds_file_name'));

        foreach ($urls as $part_count => $count_urls) {
            $count_url_copy = $count_urls;
            foreach ($count_urls as $name1 => $url1) {
                //see if the first part matches old url's
                if ($this->use_old_redirects) {
                    if ($url1['parts'][0] == 'REGEX_TITLE' || $url1['parts'][0] == $filename) {
                        $compared[$name1]['OLD_URL'] = 1;
                    } else {
                        $compared[$name1]['OLD_URL'] = 0;
                    }
                }

                foreach ($count_url_copy as $name2 => $url2) {
                    if ($name1 == $name2) {
                        //don't compare to self
                        continue;
                    }
                    if (isset($compared[$name1][$name2]) || isset($compared[$name2][$name1])) {
                        //already compared these
                        continue;
                    }

                    if ($url1['ext'] != $url2['ext']) {
                        //They don't match, the extensions are different
                        $compared[$name1][$name2] = 0;
                    }

                    //compare each part, see if they match up
                    $parts1 = $url1['parts'];
                    $parts2 = $url2['parts'];
                    $is_match = 1;
                    foreach ($parts1 as $i => $part1) {
                        //go until we find one that does not match
                        $part2 = $parts2[$i];
                        if ($part1 == $part2) {
                            //they are the same, the most obvious clue that they match.
                            continue;
                        }
                        if ($part1 == 'REGEX_TITLE' || $part2 == 'REGEX_TITLE') {
                            //if either one is a regex title, it will always match the other
                            continue;
                        }
                        if ($part1 == 'REGEX_NUMBER' && is_numeric($part2)) {
                            //part 1 is regex number, part 2 is a number, they match
                            continue;
                        }
                        if ($part2 == 'REGEX_NUMBER' && is_numeric($part1)) {
                            //part 2 is regex number, and part 1 is numeric, they match
                            continue;
                        }
                        //if it drops down this far, then these 2 parts don't match up with
                        //each other, so these 2 URL's are unique!
                        $is_match = 0;
                        break;//don't continue, we found something unique between these
                    }
                    $compared[$name1][$name2] = $is_match;
                }
            }
        }
        $is_ok = 1;
        foreach ($compared as $url1 => $data) {
            foreach ($data as $url2 => $is_same) {
                if ($is_same) {
                    //well that stinks, they managed to have 2 conflicting urls
                    if ($show_errors) {
                        if ($url2 == 'OLD_URL') {
                            geoAdmin::m("URL settings for $url1 is conflicting with SEO 1.0 URLs,
								please make changes to the URL's settings so that the first part is not
								\"$filename\" and is not any type of dynamic title; OR turn off <em>Include SEO 1.0 URLs</em> in the
								<a href='#advanced_settings'>advanced settings</a>.", geoAdmin::ERROR);
                        } else {
                            geoAdmin::m("URL settings for $url1 and $url2 are conflicting (too similar),
								please make changes to either of those URLs to make it more unique.", geoAdmin::ERROR);
                        }
                    }
                    $is_ok = 0;
                }
            }
        }
        if (!$is_ok) {
            if ($show_errors) {
                geoAdmin::m('As you can see from the errors displayed, you have at least 1 URL that
					conflicts with another.  You can refresh the page to clear these messages.', geoAdmin::NOTICE, true, 0);
            }
            return false;
        }
        return true;
    }

    public function set_flag($flag_name = '', $flag_number = 0)
    {
        $CJAX = geoCJAX::getInstance();
        $egg = $CJAX->get('egg');


        $this->registry_id = $CJAX->get('r_id');

        if ($egg) {
            #alert('Settings reset executing now');
            $this->resetSettings();
        }

        if (!$this->registry_id) {
            $CJAX->alert(__function__ . ': registry id is invalid');
            return false;
        }
        $this->update_status = true;

        $this->item_name = $CJAX->get('item_name');
        $this->status = intval($CJAX->get('ivalue'));

        if (strpos($this->getItemType(), 'custom_text') !== false) {
            $this->custom_texts();
        }

        if (isset($flag_name)  && !is_array($flag_name) && $flag_name != '') {
            $this->item_name = $flag_name;
            $this->status = $flag_number;
        }

        if ($this->update_status) {
            $this->updateStatus();
        }
    }

    /**
     * this function is hard coded for each url
     *
     * @return $item_title
     */
    public function getItemdetails()
    {
        $item_title = array();

        switch ($this->registry_id) {
            case 'category':
                    $category = geoCategory::getRandomBasicInfo();
                if (!is_array($category) || empty($category) || $category['id'] != 0) {
                    while (!is_array($category) || $category['id'] == 0) {
                        $category = geoCategory::getRandomBasicInfo();
                    }
                }
                    $item_title['title'] = $category['name'];
                    $item_title['id'] = $category['id'];
                break;
            case 'listings':
                 $item_title['title'] = "(!LISTING_TITLE!)";
                 $item_title['id'] = 1000;
                break;
        }

        return $item_title;
    }

    public function getItemText()
    {
        $CJAX = geoCJAX::getInstance();

        if (!$this->item_name) {
            $CJAX->alert(__function__ . "Invalid item name!");
        }
        $texts = $this->get('custom_text');
        if ($texts) {
            return $texts[$this->item_name];
        }
    }

    private function getItemStatus()
    {
        $CJAX = geoCJAX::getInstance();

        if (!$this->item_name) {
            $CJAX->alert(__function__ . "Invalid item name!");
        }
        $status = $this->get('status');
        if ($status) {
            return $status[$this->item_name];
        }
    }

    public function custom_texts()
    {
        $CJAX = geoCJAX::getInstance();
        $this->cmd = $CJAX->get('cmd');
        $this->item_name = $CJAX->get('item_name');
        $this->custom_value = $CJAX->get('custom_value');
        $this->item_number = $CJAX->get('item_number');
        $this->registry_id = $CJAX->get('r_id');
        $this->status = intval($CJAX->get('ivalue'));
        $this->item_status = $this->getItemStatus();

        //we do not want to change the status untill the text is saved
        // at this point we are just displaying the option
        if (!$this->item_number) {
            $CJAX->alert("no item_number defined.");
            include GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }

        if (!$this->registry_id) {
            $CJAX->alert("Registry id is invalid:");
            include GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }

        if ($this->cmd == 'edit' && $CJAX->get('editing_text')) {
            if ($this->custom_value == '~~edit_me~~') {
                //hide the box
                $CJAX->hide("span_{$this->item_name}_text");
                //set value of input
                $texts = $this->get('custom_text');
                $CJAX->set_value($this->item_name, $texts[$this->item_name]);
                //make it visible
                $CJAX->show('span_' . $this->item_name);
                $CJAX->focus($this->item_name);
            } else {
                //save the value

                //clean the input to be suitable for URL's
                $value = trim($this->custom_value);

                //save it
                if (strlen($value) == 0) {
                    //Delete this custom text!

                    $cat_order = $this->get('order');
                    unset($cat_order[$this->item_name]);

                    $cat_title = $this->get('title');
                    unset($cat_title[$this->item_name]);

                    $cat_name = $this->get('name');
                    unset($cat_name[$this->item_name]);

                    $cat_status = $this->get('status');
                    unset($cat_status[$this->item_name]);

                    $cat_type = $this->get('type');
                    unset($cat_type[$this->item_name]);

                    $cat_desc = $this->get('desc');
                    unset($cat_desc[$this->item_name]);

                    $cat_custom_text = $this->get('custom_text');
                    unset($cat_custom_text[$this->item_name]);

                    $this->set('order', $cat_order);
                    $this->set('title', $cat_title);
                    $this->set('name', $cat_name);
                    $this->set('status', $cat_status);
                    $this->set('type', $cat_type);
                    $this->set('desc', $cat_desc);
                    $this->set('custom_text', $cat_custom_text);
                    $this->save();

                    geoAdmin::m('URL part <span style="text-decoration: underline;">removed</span>.', geoAdmin::SUCCESS, true, 0);
                    $CJAX->wait(4);
                    $CJAX->location();
                    include GEO_BASE_DIR . 'app_botton.php';
                    exit;
                }
                $util = geoAddon::getUtil('SEO');
                $value = $util->revise($value);

                $texts = $this->get('custom_text');
                $texts[$this->item_name] = $value;
                $this->set('custom_text', $texts);
                $this->save();
                //now update view
                //hide input and save
                $CJAX->hide('span_' . $this->item_name);
                //update value for editing again
                $CJAX->set_value($this->item_name, '~~edit_me~~');
                //update text
                $CJAX->update("span_{$this->item_name}_text", "&nbsp;$value&nbsp;");
                //show the text
                $CJAX->show("span_{$this->item_name}_text");
                //update url
                $this->getCurrentUrl();
                geoAdmin::m('Custom text updated.', geoAdmin::SUCCESS, 1);
            }
            include GEO_BASE_DIR . 'app_bottom.php';
            exit;
        }
    }

    private function getItemType($alternative = null)
    {
        if (!$this->item_name && !$alternative) {
            $CJAX->alert(__function__ . ': no item name?');
        }
        $types = $this->get('type');
        if ($alternative != null) {
            $type = $types[$alternative];
        } else {
            $type = $types[$this->item_name];
        }
        return $type;
    }

    public function updateFlag($value = 0)
    {
        if ($this->getItemStatus() != 3) {
            $CJAX = geoCJAX::getInstance();

            if (!$value) {
                $value = 0;
            }
            $CJAX->set_value('checkbox_' . $this->item_name, $value);
            $CJAX->update('cat_custom_' . $this->item_name, '');
        }
    }

    private function saveCustomText()
    {
        $CJAX = geoCJAX::getInstance();
        if (!$this->item_number) {
            $CJAX->alert("invalid  item_number");
            return false;
        }
        $this->item_status = $this->getItemStatus();


        $this->get_item_text = $this->getItemText();

        if ($this->custom_value == '') {
            $this->updateFlag(0);
            $CJAX->set_value('custom_values', $this->get_item_text);
            $CJAX->focus('custom_values');

            $admin = true;
            include(GEO_BASE_DIR . 'get_common_vars.php');
            $admin->userError("Sorry, Your custom text can not be empty.");
            $msg  = $admin->getUserMessages();
            $CJAX->message($msg, 4);
            include GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }

        $texts = $this->get('custom_text');
        $texts[$this->item_name] = $this->custom_value;
        $this->set('custom_text', $texts);
        $this->save();

        $this->updateFlag(1);
    }


    public function order($data = array())
    {
        $CJAX = geoCJAX::getInstance();

        $this->registry_id = $CJAX->get('r_id');

        if (!$this->registry_id) {
            $CJAX->alert("Registry id is invalid");
            return false;
        }
        $this->item_name = $CJAX->get('item_name');
        $this->position  = $CJAX->get('position');

        if (!$this->item_name) {
            $CJAX->alert("invalid setting name");
            return false;
        }
        if (!$this->position) {
            $CJAX->alert("a position is not set");
            return false;
        }

        $order = $this->get('order');
        if (!$order[$this->item_name]) {
            $CJAX->alert("failed to get \$order");
            return false;
        }
        $current_order = $order[$this->item_name];


        switch ($this->position) {
            case 'down':
                $switch_order = $current_order + 1;
                break;
            case 'up':
                $switch_order = $current_order - 1;
                break;
        }

        $order_keys = array_flip($order);
        $affected_row = $order_keys[$switch_order];

        $order[$affected_row] = $current_order;
        $order[$this->item_name] = $switch_order;
        $this->set('order', $order);
        $this->save();

        $info =  $this->getItemsOrder();
        $this->getCurrentUrl();
        echo $info['html'];
    }


    public function updateStatus()
    {
        if (!$this->item_name) {
            return false;
        }

        $status = $this->get('status');
        if ($status[$this->item_name] == 2) {
            //protected, don't change status
            $this->status = 2;
            return false;
        }
        $status[$this->item_name] = $this->status;
        $this->set('status', $status);
        $this->save();

        $this->getCurrentUrl();
    }

    private function DisplayEditTextbox($item_number)
    {
        $CJAX = geoCJAX::getInstance();

        if (!$this->item_number || $this->item_name == '') {
            $CJAX->alert("Invalid item_number of item_name.");
            return false;
        }

        if (!$this->registry_id) {
            $CJAX->alert("Invalid registry id.");
            return false;
        }
        $item = '';
        $texts = $this->get('custom_text');
        $text = $texts['custom_text_' . $this->item_number];
        $CJAX->textBox('custom_values', 'updater_element', "<b>Enter your custom text:</b>&nbsp;");
        $CJAX->set_value('custom_values', $text);

        $newtext = $CJAX->value('custom_values');
        $CJAX->link = true;
        $save_url = $CJAX->call("AJAX.php?controller=addon_SEO&action=set_flag&item_name=$this->item_name&cmd=update&item_number=$this->item_number&custom_value=$newtext&ivalue=$this->status&amp;r_id=$this->registry_id");

        $save = geoHTML::addButton("Save", $save_url, true);
        $cancel = geoHTML::addButton("Cancel", "'javascript:void(0)' onclick=\"CJAX.hide('updater'); return false;\"", true);

        $CJAX->update("updater_link", "&nbsp;$save&nbsp; &nbsp;$cancel", true);
        $CJAX->show('updater');
        $CJAX->focus('custom_values');
    }

    private function getItemsOrder()
    {
        $seo = geoAddon::getUtil('SEO');

        if ($seo) {
            return $seo->{__function__}();
        }
    }
    public function getCurrentUrl()
    {
        $CJAX = geoCJAX::getInstance();

        $orders = $this->get('order');
        $status = $this->get('status');
        $text = $this->get('custom_text');
        $parts = array();
        foreach ($orders as $name => $order) {
            if ($status[$name]) {
                if (strpos($name, 'custom_text_') !== false) {
                    $parts [$order] = $text[$name];
                } else {
                    $parts [$order] = '[' . strtoupper($name) . ']';
                }
            }
        }
        ksort($parts);
        $db = DataAccess::getInstance();
        $site = dirname($db->get_site_setting('classifieds_url'));
        $CJAX->update('span_current_url', "[$site] / " . implode($parts, ' / ') . $this->get('extension'));
    }
    public function addCustomText()
    {
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = $CJAX->get('r_id');

        $cat_order = $cat_title = $cat_name = $cat_status = $cat_desc = array();
        $custom_texts = $this->get('custom_text');

        //alert(print_r($custom_texts,1));

        if (!$this->registry_id) {
            $CJAX->alert('invalid registry id');
            return false;
        }

        if (empty($custom_texts)) {
            $CJAX->alert('unknow error!');
            return false;
        }

        $number = count($custom_texts);

        if ($number == 0) {
            $CJAX->alert('unknow error 2!');
            return false;
        }

        $new_number = $number + 1;


        $cat_order = $this->get('order');
        $cat_order["custom_text_{$new_number}"] = count($cat_order) + 1;

        $cat_title = $this->get('title');
        $cat_title["custom_text_{$new_number}"] = "(!CUSTOM_TEXT_{$new_number}!)";
        ;

        $cat_name = $this->get('name');
        $cat_name["custom_text_{$new_number}"] = "custom_text_{$new_number}";

        $cat_status = $this->get('status');
        $cat_status["custom_text_{$new_number}"] = 0;

        $cat_type = $this->get('type');
        $cat_type["custom_text_{$new_number}"] = 'custom_text';

        $cat_desc = $this->get('desc');
        $cat_desc["custom_text_{$new_number}"] = "You can customize this so that your custom text will show up on the browser url";

        $cat_custom_text = $this->get('custom_text');
        $cat_custom_text["custom_text_{$new_number}"] = "custom_part";

        $this->set('order', $cat_order);
        $this->set('title', $cat_title);
        $this->set('name', $cat_name);
        $this->set('status', $cat_status);
        $this->set('type', $cat_type);
        $this->set('desc', $cat_desc);
        $this->set('custom_text', $cat_custom_text);
        $this->save();
        geoAdmin::m('Added new custom text URL part.', geoAdmin::SUCCESS, true, 2);
        $CJAX->wait(2);
        $CJAX->location();
        include GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    public function resetUpgradeSettings()
    {
        $db = DataAccess::getInstance();

        $filename = $db->get_site_setting('classifieds_file_name');
        $index = str_replace('.php', '', $filename);

        $setting = "$index/listings/category(!CATEGORY_ID!).htm";
        $this->registry_id = 'category';
        $this->set('url_template', $setting);
        $this->save();

        #listings/category([0-9]*)/page([0-9]*)\.htm$ $1.php?a=5&b=$2&page=$3 [L]
        $setting = "$index/listings/category(!CATEGORY_PAGE_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'Category pages';
        $this->set('url_template', $setting);
        $this->save();

        ##/featured/category([0-9]*)\.htm$ $1.php?a=8&b=$2 [L]
        $setting = "$index/featured/category(!CATEGORY_ID!).htm";
        $this->registry_id = 'Category featured ad pics';
        $this->set('url_template', $setting);
        $this->save();

        #-/featured/category([0-9]*)/page([0-9]*)\.htm$ $1.php?a=8&b=$2&page=$3 [L]
        $setting = "$index/featured/category(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'Category featured ad pics pages';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/page([0-9]*)\.htm$ $1.php?a=2&b=$2 [L]
        $setting = "$index/listings/page(!LISTING_ID!).htm";
        $this->registry_id = 'listings';
        $this->set('url_template', $setting);
        $this->save();

        #/featured/page([0-9]*)\.htm$ $1.php?a=8&page=$2 [L]
        $setting = "$index/featured/page(!PAGE_ID!).htm";
        $this->registry_id = 'featured listings page';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/1day([0-9]*)\.htm$ $1.php?a=11&b=$2&c=4 [L]
        $setting = "$index/listings/1day(!LISTING_ID!).htm";
        $this->registry_id = 'listings that are 1 day new';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/1day([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=4&page=$3 [L]
        $setting = "$index/listings/1day(!LISTING_ID!)/page(!LISTING_ID_PAGE!).htm";
        $this->registry_id = 'listings that are 1 day new pages';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/1week([0-9]*)\.htm$ $1.php?a=11&b=$2&c=1 [L]
        $setting = "$index/listings/1week(!LISTING_ID!).htm";
        $this->registry_id = 'listings that are 1 week new';
        $this->set('url_template', $setting);
        $this->save();


        #listings/1week([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=1&page=$3 [L]
        $setting = "$index/listings/1week(!LISTING_ID!)/page(!LISTING_ID_PAGE!).htm";
        $this->registry_id = 'listings that are 1 week new pages';
        $this->set('url_template', $setting);
        $this->save();


        #\listings/2weeks([0-9]*)\.htm$ $1.php?a=11&b=$2&c=2 [L]
        $setting = "$index/listings/2weeks(!LISTING_ID!).htm";
        $this->registry_id = 'listings that are 2 weeks new';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/2weeks([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=2&page=$3 [L]
        $setting = "$index/listings/2weeks(!LISTING_ID!)/page(!LISTING_ID_PAGE!).htm";
        $this->registry_id = 'listings that are 2 weeks new pages';
        $this->set('url_template', $setting);
        $this->save();

        #/listings/3weeks([0-9]*)\.htm$ $1.php?a=11&b=$2&c=3 [L]
        $setting = "$index/listings/3weeks(!LISTING_ID!).htm";
        $this->registry_id = 'listings that are 3 weeks new';
        $this->set('url_template', $setting);
        $this->save();

        ##/listings/3weeks([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=3&page=$3 [L]
        $setting = "$index/listings/3weeks(!LISTING_ID!)/page(!LISTING_ID_PAGE!).htm";
        $this->registry_id = 'listings that are 3 weeks new pages';
        $this->set('url_template', $setting);
        $this->save();

        #/print/item([0-9]*)\.htm$ $1.php?a=14&b=$2 [L]
        $setting = "$index/print/item(!ITEM_ID!).htm";
        $this->registry_id = 'print item';
        $this->set('url_template', $setting);
        $this->save();


        #/images/item([0-9]*)\.htm$ $1.php?a=15&b=$2 [L]
        $setting = "$index/images/item(!IMAGE_ID!).htm";
        $this->registry_id = 'images browsing';
        $this->set('url_template', $setting);
        $this->save();


        #/other/seller([0-9]*)\.htm$ $1.php?a=6&b=$2 [L]
        $setting = "$index/other/seller(!SELLER_ID!).htm";
        $this->registry_id = 'other seller';
        $this->set('url_template', $setting);
        $this->save();


        #/other/seller([0-9]*)/page([0-9]*)\.htm$ $1.php?a=6&b=$2&page=$3 [L]
        $setting = "$index/other/seller(!SELLER_ID!)/page(!PAGE_ID!)/.htm";
        $this->registry_id = 'other seller page';
        $this->set('url_template', $setting);
        $this->save();
    }

    public function resetSetting()
    {
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = $CJAX->get('r_id');
        if (!$this->registry_id) {
            return false;
        }
        $this->temp_registry_id = $this->registry_id;
        $this->resetSettings(true);
        $CJAX->location();
        include GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    public function resetSettings($specify = false)
    {
        if (!$this->registry_id) {
            $CJAX->alert(__function__ . ': invalid registry id');
            return false;
        }

        $seo = geoAddon::getUtil('seo');
        if ($seo) {
            if ($specify) {
                $seo->temp_registry_id = $this->registry_id;
            }
            $seo->RegisterSettings($specify);
        }
    }
    public function resetAllSettings()
    {
    }

    public static $registry = array();
    private static $_pending_changes = array();
    private function initRegistry()
    {
        if (!$this->registry_id) {
            return false;
        }
        if (isset(self::$registry[$this->registry_id]) && is_object(self::$registry[$this->registry_id])) {
            return;
        }
        self::$registry[$this->registry_id] = new geoRegistry();
        self::$registry[$this->registry_id]->setName('addon_seo');
        self::$registry[$this->registry_id]->setId($this->registry_id);
        self::$registry[$this->registry_id]->unSerialize();
    }
    private function save()
    {
        foreach (self::$registry as $id => $reg) {
            if (is_object($reg) && self::$_pending_changes[$id]) {
                $reg->save();
                self::$_pending_changes[$id] = 0;
            }
        }
    }
    private function get($setting)
    {
        if (!$this->registry_id) {
            return false;
        }
        $this->initRegistry($this->registry_id);
        return self::$registry[$this->registry_id]->get($setting);
    }
    private function set($setting, $value)
    {
        if (!$this->registry_id) {
            return false;
        }
        $this->initRegistry($this->registry_id);
        self::$registry[$this->registry_id]->set($setting, $value);
        self::$_pending_changes[$this->registry_id] = 1;
    }
}
