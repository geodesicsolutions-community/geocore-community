<?php

//addons/adplotter/admin.php

# Adplotter Link addon

class addon_adplotter_admin extends addon_adplotter_info
{

    public function init_pages()
    {
        menu_page::addonAddPage('adplotter_config', '', 'Configure', 'adplotter', $this->icon_image);
        menu_page::addonAddPage('adplotter_advanced', '', 'Advanced Settings', 'adplotter', $this->icon_image);
    }

    function init_text($language_id)
    {
        $return = array();
        $return['affLinkDesc'] = array (
                'name' => 'Affiliate Link Description',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'General',
                'default' => 'This site is affiliated with the AdPlotter listing syndication engine.<br /> 
				We have automatically created a FREE account for you to use on AdPlotter.com, where you can post a classified listing to hundreds of different sites simultaneously.<br />
				To login to this account, go to <a href="http://adplotter.com">AdPlotter.com</a> and use the following login credentials:',
        );
        $return['affLinkUserLabel'] = array (
                'name' => 'Affiliate Link Username Label',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'General',
                'default' => 'User ID:',
        );
        $return['affLinkPassLabel'] = array (
                'name' => 'Affiliate Link Password Label',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'General',
                'default' => 'Password:',
        );
        $return['listingSuccessMailSubject'] = array (
                'name' => 'Subject',
                'desc' => 'followed by site domain name (e.g. example.com)',
                'type' => 'textarea',
                'section' => 'Listing Success Email',
                'default' => 'AdPlotter delivered your ad to ',
        );
        $return['listingSuccessMailIntroduction'] = array (
                'name' => 'Introduction',
                'desc' => "Precedes user's name",
                'type' => 'textarea',
                'section' => 'Listing Success Email',
                'default' => 'Hello',
        );
        $return['listingSuccessMailBody1'] = array (
                'name' => 'Body1',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'Listing Success Email',
                'default' => 'Your ad was successfully placed on',
        );
        $return['listingSuccessMailBody2'] = array (
                'name' => 'Body2',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'Listing Success Email',
                'default' => 'in the category: ',
        );
        $return['listingSuccessMailBody3'] = array (
                'name' => 'Body3',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'Listing Success Email',
                'default' => 'This ad is live and ready to be viewed at the following link:',
        );
        $return['listingSuccessMailAbout'] = array (
                'name' => 'About',
                'desc' => '',
                'type' => 'textarea',
                'section' => 'Listing Success Email',
                'default' => 'is independently owned and operated, and part of the AdPlotter network of classified sites.',
        );
        return $return;
    }

    public function display_adplotter_config()
    {
        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars['adminMsgs'] = geoAdmin::m();
        $tpl_vars['enabled'] = $reg->enabled;
        $status = $this->AdPlotterGetStatus();
        $tpl_vars['status_code'] = $status['response_code'];
        if ($status['response_code'] <= -1) {
            $tpl_vars['status_text'] = $status['response_msg'];
        }

        $tpl_vars['affiliate_code'] = $reg->affiliate_code;
        $tpl_vars['aff_tooltip'] = geoHTML::showTooltip('AdPlotter Affiliate Code', "If you have an AdPlotter Affiliate Code, enter it here. If not, you can leave this blank.");

        //get local mapping
        $db = DataAccess::getInstance();
        $result = $db->Execute("SELECT * FROM `geodesic_addon_adplotter_category_map`");
        $ap_geo_cat_map = array();
        foreach ($result as $c) {
            $ap_geo_cat_map[geoString::fromDB($c['adplotter_name'])] = $c['geo_id'];
        }

        //get foreign mapping
        $util = geoAddon::getUtil($this->name);
        $ap_cats = $util->getAdPlotterCategories();
        $parentKeys = $util->getParentKeys();

        //build first level of DDLs only. Subcategories will be handled by AJAX later as needed
        require_once(CLASSES_DIR . 'site_class.php');
        $site = Singleton::getInstance('geoSite');
        $depth = $db->get_site_setting('levels_of_categories_displayed_admin') ? $db->get_site_setting('levels_of_categories_displayed_admin') : 3;
        foreach ($ap_cats as $topCat => $subCats) {
            //parent first
            $selected = $ap_geo_cat_map[$topCat] ? $ap_geo_cat_map[$topCat] : 0;
            $tpl_vars['ap_parents'][] = array('name' => geoString::toDB($topCat),
                    'ddl' => $site->get_category_dropdown("cat_map[" . geoString::toDB($topCat) . "]", $selected, 0, 0, 'Do Not Use', 2, $depth),
                    'has_subs' => count($subCats) ? true : false,
                    'parent_key' => $parentKeys[$topCat]
                );
        }

        geoView::getInstance()->setBodyTpl('admin/config.tpl', $this->name)->setBodyVar($tpl_vars);
    }

    public function update_adplotter_config()
    {
        $reg = geoAddon::getRegistry($this->name);
        //get initial enabled state
        $initial = $reg->enabled;

        //save cat map to db
        $cat_map = $_POST['cat_map'];
        $db = DataAccess::getInstance();
        $save = $db->Prepare("REPLACE INTO `geodesic_addon_adplotter_category_map` (adplotter_name, geo_id) VALUES (?,?)");
        foreach ($cat_map as $ap => $geo) {
            $db->Execute($save, array($ap, $geo));
        }

        $reg->enabled = $_POST['enabled'] == 1 ? 1 : 0;
        $reg->affiliate_code = $_POST['affiliate_code'];
        if ($initial != $reg->enabled) {
            //state has changed
            if ($reg->enabled == 1) {
                if (!$this->AdPlotterRegister()) {
                    //registration request failed
                    geoAdmin::m('AdPlotter did not receive the register request. This is most likely a network error. Try again in a few minutes.', geoAdmin::ERROR);
                    $reg->enabled = 0;
                }
            } else {
                if (!$this->AdPlotterUnregister()) {
                    //registration request failed
                    geoAdmin::m('AdPlotter did not receive the unregister request. This is most likely a network error. Try again in a few minutes.', geoAdmin::ERROR);
                    $reg->enabled = 1;
                }
            }
        } elseif ($reg->enabled) {
            //already enabled, so this is a refresh
            if (!$this->AdPlotterRefresh()) {
                geoAdmin::m('AdPlotter did not receive the refresh request. This is most likely a network error. Try again in a few minutes.', geoAdmin::ERROR);
            }
        }
        $reg->save();
        return true;
    }

    public function display_adplotter_advanced()
    {
        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars['adminMsgs'] = geoAdmin::m();

        $db = DataAccess::getInstance();
        $result = $db->Execute("SELECT `group_id`, `name` FROM " . geoTables::groups_table);
        $groups = array();
        foreach ($result as $group) {
            $groups[$group['group_id']] = $group['name'];
        }
        $tpl_vars['default_group'] = $reg->default_group;
        $tpl_vars['groups'] = $groups;

        geoView::getInstance()->setBodyTpl('admin/advanced.tpl', $this->name)->setBodyVar($tpl_vars);
    }

    public function update_adplotter_advanced()
    {
        $reg = geoAddon::getRegistry($this->name);
        $reg->default_group = (int)$_POST['default_group'];
        $reg->save();
        return true;
    }


    public function AdPlotterRegister()
    {
        $db = DataAccess::getInstance();
        $fields = array();
        $fields['action_cmd'] = 'register';
        $fields['precurrency'] = $db->get_site_setting('precurrency');
        $fields['postcurrency'] = $db->get_site_setting('postcurrency');

        $api = Singleton::getInstance('geoAPI');
        $fields['key'] = $api->getKeyFor();

        $fields['categories'] = json_encode($_POST['cat_map']);

        $result = $this->_doAdPlotterRequest($fields);

        if ($result['response_code'] == 0) {
            //registration request received OK
            return true;
        } else {
            //something went wrong. store the error for potential debugging
            $reg = geoAddon::getRegistry($this->name);
            $reg->last_error_code = $result['response_code'];
            $reg->last_error_msg = $result['response_msg'];
            $reg->last_error_time = geoUtil::time();
            $reg->last_error_type = $fields['action_cmd'];
            $reg->save();
            return false;
        }
    }

    public function AdPlotterRefresh()
    {
        $db = DataAccess::getInstance();
        $fields = array();
        $fields['action_cmd'] = 'refresh';

        $fields['categories'] = json_encode($_POST['cat_map']);

        $result = $this->_doAdPlotterRequest($fields);

        if ($result['response_code'] == 0) {
            //registration request received OK
            return true;
        } else {
            //something went wrong. store the error for potential debugging
            $reg = geoAddon::getRegistry($this->name);
            $reg->last_error_code = $result['response_code'];
            $reg->last_error_msg = $result['response_msg'];
            $reg->last_error_time = geoUtil::time();
            $reg->last_error_type = $fields['action_cmd'];
            $reg->save();
            return false;
        }
    }

    public function AdPlotterGetStatus()
    {
        $fields = array();
        $fields['action_cmd'] = 'regstatus';
        $result = $this->_doAdPlotterRequest($fields);
        return $result;
    }

    public function AdPlotterUnregister()
    {
        $fields = array();
        $fields['action_cmd'] = 'unregister';
        $result = $this->_doAdPlotterRequest($fields);

        if ($result['response_code'] == 0) {
            //unregistration request received OK
            return true;
        } else {
            //something went wrong. store the error for potential debugging
            $reg = geoAddon::getRegistry($this->name);
            $reg->last_error_code = $result['response_code'];
            $reg->last_error_msg = $result['response_msg'];
            $reg->last_error_time = geoUtil::time();
            $reg->last_error_type = $fields['action_cmd'];
            $reg->save();
            return false;
        }
    }

    private function _doAdPlotterRequest($fields)
    {
        //common api fields
        $db = DataAccess::getInstance();
        $fields['url'] = str_replace($db->get_site_setting('classifieds_file_name'), '', $db->get_site_setting('classifieds_url'));
        $fields['auth'] = sha1($fields['url'] . 'sup3rsALtY@' . $fields['action_cmd']); //do not change the salt phrase without notifying AdPlotter

        $notifyUrl = "http://api.adplotter.com/ProcessRawRequest.ashx?Action=GeoCoreAction";

        $result = geoPC::urlPostContents($notifyUrl, $fields);
        return json_decode($result, true);
    }
}
