<?php

// addon_manage.php


class Addon_Manage
{

    const AUTO_UPLOAD = false;

    var $addons;

    /**
     * Db class
     *
     * @var DataAccess
     */
    var $db;

    /**
     * Addon class
     *
     * @var geoAddon
     */
    var $addon;

    var $actions_exclusive;
    public function __construct()
    {
         //if we cant find the admin site object, we cant do squat!
        if (!Singleton::isInstance('Admin_site')) {
            return false;
        }

        $this->db = DataAccess::getInstance();
        $this->addon = geoAddon::getInstance();

        $this->get_addon_details();
    }

    /**
     * Handle AutoUpload addons
     *
     * @return boolean
     */
    function autoUpload()
    {

        if (!$_FILES['auto_installer']) {
            return true;
        }
        #die(print_r($_FILES['auto_installer'],1));
        $file = $_FILES['auto_installer'];
        $file_resource = $file['tmp_name'];
        $info = pathinfo($file['name']);
        $extension = $info['extension'];

        if ($extension != 'zip') {
            //not a zip file
            return false;
        }
        $zip = zip_open($file_resource);

        if (false) {
            //method 1 to extract zip
            $zip->extractTo("../addons/", array('storefront/setup.xml'));
            $zip->close();
        }

        $CJAX = geoCJAX::getInstance();

        if (!is_writable(ADDONS_DIR)) {
            $CJAX->alert("Make sure " . ADDONS_DIR . " is writable (chmod 777)");
            exit("Make sure to CHMOD " . ADDONS_DIR . " 777!");
        }
        //method 2

            //following block works great. but testing this one other.
        if ($zip) {
            $i = 0;
            $files = array();
            while ($zip_entry = zip_read($zip)) {
                $i++;
                $current_resource = zip_entry_name($zip_entry);
                if (zip_entry_open($zip, $zip_entry, "r")) {
                    if (1 === $i) {
                        $addon_dir = $current_resource;
                        if (is_dir(ADDONS_DIR . $addon_dir)) {
                            //$CJAX->alert("Addons already installed. Please unistall and remove from addons directory before installing.");
                            //exit("addon already Installed");
                        }
                        mkdir(ADDONS_DIR . $addon_dir);
                        $r = chown(ADDONS_DIR . $addon_dir, get_current_user());
                    }
                    //handle the installer  setup.xml
                    if ($current_resource === $addon_dir . "setup.xml") {
                        $files['setup']['file'] =  $r = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                        if (!$r) {
                            exit("setup.xml is missing from addon. $current_resource -$setup_xml");
                        }
                        continue;
                    }
                    $files[] = $current_resource;
                    if (!file_exists(ADDONS_DIR . $current_resource)) {
                        $fp = fopen("zip/" . $current_resource, "w");
                        $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        fwrite($fp, "$buf");

                        //CREATE NEW FILES
                        $newfile =  fopen(ADDONS_DIR . $current_resource, "w+");
                        fwrite($newfile, "$buf");
                        fclose($newfile);



                        if ($current_resource == "$addon_dir/setup.xml") {
                        }
                        zip_entry_close($zip_entry);
                        fclose($fp);
                    }
                }
            }
            zip_close($zip);


            die('<pre>' . print_r($files, 1));
        }

        if ($addon_dir) {
            //$xml_file = file_get_contents("../addons/$addon_dir/setup.xml");
            $xml_file = "../addons/$addon_dir/setup.xml";
            $xml_entry = simplexml_load_file($xml_file);

            echo print_r($xml_entry, 1);
        }

        return true;
    }

    function display_addon_tools()
    {
        if (self::AUTO_UPLOAD && defined('IAMDEVELOPER')) {
            //to allow to upload addons
            $this->autoUpload();
        }

        //display addon manage page.
        $admin = geoAdmin::getInstance();
        $admin->v()->addJScript('js/clickable_tooltips.js');
        $admin->v()->addCssFile('css/addon_manage.css');
        $body .= '<div class="addon_div table-responsive" id="tooltip_fun_addons"><table class="table table-hover table-striped table-bordered" cellpadding=2 cellspacing=1 border=0> ';

        $this->show_enabled_addons($body);
        $this->show_disabled_addons($body);
        $this->show_not_installed_addons($body);
        $body .= '</table></div>';

        if (self::AUTO_UPLOAD  && defined('IAMDEVELOPER')) {
            $body .= $this->show_addon_installer();
        }
        geoAdmin::display_page($body);
    }

    function show_addon_installer()
    {
        //$CJAX = geoCJAX::getInstance();
        //$CJAX->link = true;
        //$call = $CJAX->call("AJAX.php?controller=AddonManage&action=autoInstall");
        $html = "
		<fieldset>
			<legend>Auto Installer</legend>
			<div>
			<form id='frm_auto_installer' method='post' action=''>
		";
        $html .= geoHTML::addOption("<div style='position:relative;padding-top:6px'>Select a File:</div>", geoHTML::input("file", "auto_installer") . "<input type='submit' value='Upload'/>");
        $html .= "</form></div></fieldset>";
        return $html;
    }

    public function display_edit_addon_text()
    {
        $view = geoView::getInstance();
        if (!isset($_GET['addon'])) {
            $all_addons = $this->addon->getTextAddons();
            if (!count($all_addons)) {
                $body .= "No Configurable Addon Text.";
            } else {
                $body .= 'Addons with text to edit (click to edit):<br /><br />';
            }
            foreach ($all_addons as $name => $admin) {
                $body .= '<a href="index.php?mc=addon_management&page=edit_addon_text&addon=' . $name . '">' . $admin->title . '</a><br />
';
            }
            geoAdmin::display_page($body);
            return true;
        }
        //editing text specific to one addon.
        $admin = $this->addon->getTextAddons($_GET['addon']);
        if (!$admin) {
            $body .= 'No text found for Addon.';
            geoAdmin::display_page($body);
            return false;
        }

        $tpl_vars = array();

        $info = Singleton::getInstance('addon_' . $_GET['addon'] . '_info');

        $tpl_vars['adminMsgs'] = geoAdmin::m();
        $tpl_vars['addon_title'] = $info->title;
        $tpl_vars['addon_auth_tag'] = $info->auth_tag;
        $sql = 'SELECT language, language_id FROM ' . geoTables::pages_languages_table;
        $result = $this->db->GetAll($sql);

        $text_info = $admin->init_text(1);
        $languages = array();
        foreach ($result as $row) {
            $languages[$row['language_id']] = $row['language'];
            $current_text = geoAddon::getTextRaw($info->auth_tag, $info->name, $row['language_id']);
            foreach ($text_info as $index => $data) {
                //reset text to default link
                $link = '<a href="javascript:void(0)" onclick="document.getElementById(\'tag' . $index . '_l' . $row['language_id'] . '\').value=\'' . geoString::specialChars(addslashes(str_replace(array("\r", "\n"), array('', '\\' . "\n"), $data['default']))) . '\'">Reset to Default</a>';

                $data['lang'][$row['language_id']] = geoString::fromDB($current_text[$index]);
                $text_info[$index] = $data;
            }
        }
        $tpl_vars['languages'] = $languages;
        $tpl_vars['text_info'] = $text_info;

        $view->setBodyTpl('addon_manage/editText.tpl')
            ->setBodyVar($tpl_vars);
        return;
    }
    function update_edit_addon_text()
    {
        $name = $_GET['addon'];
        $tags = $_POST['tag'];
        $auth_tag = $_POST['auth_tag'];
        foreach ($tags as $lang => $lang_tags) {
            foreach ($lang_tags as $index => $text) {
                $this->addon->setText($auth_tag, $name, $index, $text, $lang);
            }
        }
        return true;
    }

    private function _getAddonTooltip($info, $db_info)
    {
        $tpl = new geoTemplate('admin');
        $tpl->assign('info', $info);
        $tpl->assign('info_db', $db_info);
        $tpl->assign('white_label', geoPC::is_whitelabel());

        if (isset($info->exclusive) && $info->exclusive && isset($info->core_events) && count($info->core_events)) {
            //show any details if there are conflicts.
            $conflicts = array();
            foreach ($info->core_events as $action) {
                if (isset($this->actions_exclusive[$action]) && count($this->actions_exclusive[$action]) > 1) {
                    foreach ($this->actions_exclusive[$action] as $other_addon) {
                        if ($other_addon->name != $info->name) {
                            //if it is also enabled, go eeek!!!
                            if ($this->addons[$other_addon->name]['db']['enabled']) {
                                $conflicts[$other_addon->name] = "<strong style=\"color: red;\">{$other_addon->title} - ALERT - NEED TO DISABLE ADDON</strong>";
                            } else {
                                $conflicts[$other_addon->name] = "<strong>{$other_addon->title}</strong>";
                            }
                        }
                    }
                }
            }
            if (count($conflicts) > 0) {
                $tpl->assign('conflicts', $conflicts);
            }
        }
        return geoString::specialChars($tpl->fetch('addon_manage/infoBox.tpl'));
    }

    function show_enabled_addons(&$body)
    {
        $count = 0;
        $row_class = '';
        $text_addons = $this->addon->getTextAddons();
        $page_addons = $this->addon->getPageAddons();

        foreach ($this->addons as $name => $addon) {
            if (!isset($addon['db']['enabled']) || !$addon['db']['enabled']) {
                //only show enabled.
                continue;
            }
            $count++;
            if ($count == 1) {
                //need to show top of table.
                //show enabled addons.
                    $body .= "<thead><tr class='addon_hdr_enabled'><th colspan='8'>Enabled Addons</th></tr>
						<tr class='col_hdr_top'>
						<th class='medium_font_light' style='width: 60px;'>&nbsp;</th>
						<th class='medium_font_light' style='width: 70px;'>&nbsp;</th>
						<th class='medium_font_light'>Addon</th>
						<th class='medium_font_light' style='width: 65px;'>Version</th>
						" . (geoPC::is_whitelabel() ? "" : "<th class='medium_font_light'>Author</th>") . "
						<th class='medium_font_light' style='width: 100px;'>Status</th>
						<th class='medium_font_light' style='width: 64px;'>Text</th>
						<th class='medium_font_light' style='width: 68px;'>Page</th>
					</tr></thead>";
            }
            $upgrade_txt = '';
            $status = '<span class="color-primary-one">Installed & Enabled</span>';

            $disable = "<a id='{$name}_disabled' href='#' onclick='jQuery.post(\"AJAX.php?controller=AddonManage&action=_action&addon={$name}&task=disable\", function(msg){if(msg.trim().length > 0){gjUtil.addMessage(msg);}else{gjUtil.addMessage(\"<div class=success><ul><li>Addon Disabled. Reloading...</li></ul></div>\"); setTimeout(location.reload(),3000);}}); return false;' class='btn btn-warning btn-xs'><i class='fa fa-toggle-off'></i> Disable</a>";

            //generate tooltip.
            $message_body = $this->_getAddonTooltip($addon['info'], $addon['db']);

            if (strlen($addon['db']['version']) && $addon['db']['version'] != $addon['info']->version) {
                $upgrade_txt = '<strong>Upgrade to ' . $addon['info']->version . ' >></strong>';
                $upgrade = "<br /><a href='#' onclick='jQuery.post(\"AJAX.php?controller=AddonManage&action=_action&addon={$name}&task=upgrade\", function(msg){if(msg.trim().length > 0){gjUtil.addMessage(msg);}else{gjUtil.addMessage(\"<div class=success><ul><li>Addon Upgraded. Reloading...</li></ul></div>\"); setTimeout(location.reload(),3000);}}); return false;'>" . $upgrade_txt . "</a>";
                $status = '<span style="color: red">Needs Upgrade</span>';
            } else {
                $upgrade = ''; //don't print upgrade links for addons not needing upgrades
            }
            $row_class = ($row_class == 'row_color1') ? 'row_color2' : 'row_color1';

            if (array_key_exists($name, $text_addons)) {
                $text_link = "<a href=\"index.php?mc=addon_management&page=edit_addon_text&addon={$name}\" class='btn btn-info btn-xs'><i class='fa fa-pencil'></i> Edit Text</a>";
            } else {
                $text_link = "&nbsp;";
            }

            if (array_key_exists($name, $page_addons)) {
                $page_link = "<a href=\"index.php?page=page_attachments&addon={$name}\" class='btn btn-info btn-xs'><i class='fa fa-file'></i> Edit Page</a>";
            } else {
                $page_link = "&nbsp;";
            }

                $body .= "
					<tr class=\"$row_class\">
					<td class=\"medium_font\" style='text-align: center;'>$disable</td>
					<td class=\"medium_font\">&nbsp;</td>
					<td class=\"medium_font\" tooltip=\"$message_body\">{$addon['info']->title}</td>
					<td class=\"medium_font\" style=\"text-align: center; white-space: nowrap;\">{$addon['db']['version']}{$upgrade}</td>
					" . (geoPC::is_whitelabel() ? "" : "<td class=\"medium_font\" style='white-space: nowrap;' tooltip=\"$message_body\">{$addon['info']->author}</td>") . "
					<td class=\"medium_font\" style='white-space: nowrap; text-align: center;'>$status</td>
					<td class=\"medium_font\" style='text-align: center;'>$text_link</td>
					<td class=\"medium_font\" style='text-align: center;'>$page_link</td>
					</tr>";
        }
    }

    function show_disabled_addons(&$body)
    {
        $count = 0;
        $addon_obj = geoAddon::getInstance();
        foreach ($this->addons as $name => $addon) {
            if ((isset($addon['db']['enabled']) && $addon['db']['enabled']) || (!isset($addon['db']['enabled']))) {
                //only show disabled.
                continue;
            }
            $count++;
            $row_color = ($row_color == 'row_color1') ? 'row_color2' : 'row_color1';
            if ($count == 1) {
                //first time, so show the top of the table.
                //show enabled addons.
                    $body .= '<thead><tr class="addon_hdr_disabled"><th colspan="8">Disabled Addons</th></tr>
						<tr class="col_hdr_top">
							<th class="medium_font_light">&nbsp;</th>
							<th class="medium_font_light">&nbsp;</th>
							<th class="medium_font_light">Addon</th>
							<th class="medium_font_light">Version</th>
							' . (geoPC::is_whitelabel() ? "" : "<th class='medium_font_light'>Author</th>") . '
							<th class="medium_font_light">Status</th>
							<th class="medium_font_light">&nbsp;</th>
							<th class="medium_font_light">&nbsp;</th>
						</tr></thead>';
            }


            $uninstall = "<a id='{$name}_disabled' href='#' onclick='jQuery.post(\"AJAX.php?controller=AddonManage&action=_action&addon={$name}&task=uninstall\", function(msg){if(msg.trim().length > 0){gjUtil.addMessage(msg);}else{gjUtil.addMessage(\"<div class=success><ul><li>Addon Uninstalled. Reloading...</li></ul></div>\"); setTimeout(location.reload(),3000);}}); return false;' class='btn btn-danger btn-xs'>Uninstall</a>";
            $enable = "<a href='#' onclick='jQuery.post(\"AJAX.php?controller=AddonManage&action=_action&addon={$name}&task=enable\", function(msg){if(msg.trim().length > 0){gjUtil.addMessage(msg);}else{gjUtil.addMessage(\"<div class=success><ul><li>Addon Enabled. Reloading...</li></ul></div>\"); setTimeout(location.reload(),3000);}}); return false;' class='btn btn-success btn-xs'><i class='fa fa-toggle-on'></i> Enable</a>";

            $status = '<span class="color-primary-one">Installed</span> & <span style="color: #F7AD02">Disabled</span>';


            $conflicts = array();
            if (isset($addon['info']->exclusive) && $addon['info']->exclusive && isset($addon['info']->core_events) && count($addon['info']->core_events)) {
                //show any details if there are conflicts.
                foreach ($addon['info']->core_events as $action) {
                    if (count($this->actions_exclusive[$action]) > 1) {
                        foreach ($this->actions_exclusive[$action] as $other_addon) {
                            if ($other_addon->name != $addon['info']->name && $this->addons[$other_addon->name]['db']['enabled']) {
                                $enable = '--Conflicts--';
                                continue(2);
                            }
                        }
                    }
                }
            }
            //generate tooltip.
            $message_body = $this->_getAddonTooltip($addon['info'], $addon['db']);

            if (strlen($addon['db']['version']) && $addon['db']['version'] != $addon['info']->version) {
                $upgrade_txt = '<strong>Upgrade to ' . $addon['info']->version . ' >></strong>';
                $upgrade = "<br /><a href='#' onclick='jQuery.post(\"AJAX.php?controller=AddonManage&action=_action&addon={$name}&task=upgrade\", function(msg){if(msg.trim().length > 0){gjUtil.addMessage(msg);}else{gjUtil.addMessage(\"<div class=success><ul><li>Addon Upgraded. Reloading...</li></ul></div>\"); setTimeout(location.reload(),3000);}}); return false;'>" . $upgrade_txt . "</a>";
                $status = '<span style="color: red">Needs Upgrade</span>';
            } else {
                $upgrade = '';
            }

            $body .= "
				<tr class=\"$row_color\">
				<td class=\"medium_font\" style='text-align: center;'>$enable</td>
				<td class=\"medium_font\" style='text-align: center;'>$uninstall</td>
				<td class=\"medium_font\" tooltip=\"$message_body\">{$addon['info']->title}</td>
				<td class=\"medium_font\" style='text-align: center;'>{$addon['db']['version']}$upgrade</td>
				" . (geoPC::is_whitelabel() ? "" : "<td class=\"medium_font\" tooltip=\"$message_body\">{$addon['info']->author}</td>") . "
				<td class=\"medium_font\" style='text-align: center; white-space: nowrap;'>$status</td>
				<td class=\"medium_font\">&nbsp;</td>
				<td class=\"medium_font\">&nbsp;</td>
				</tr>\n";
        }
    }

    function show_not_installed_addons(&$body)
    {
        $count = 0;

        //array of purchasable add-ons.  Should be index => title.
        $purchase = array();
        //$purchase['index'] = array('Addon Title','https://addon_url_link.com',## - product number this addon is included with as part of package);
        /*
         * product numbers:
         * 1 Enterprise
         * 2 Premier
         * 4 Basic
         * 8 Lite
         * ------
         * 16 Classifieds
         * 32 Auctions
         * 64 GeoCore
         * 128 Print
         */
        $gtStatus = geoPC::geoturbo_status();

        $purchase['anonymous_listing'] = array('Anonymous Listing', 'http://geodesicsolutions.com/component/content/article/55-miscellaneous/77-anonymous-listing.html?directory=64',1);
        $purchase['attention_getters'] = array ('Attention Getters','http://geodesicsolutions.com/component/content/article/53-added-value/67-attention-getters.html?directory=64',1);
        $purchase['bulk_uploader'] = array('Bulk Uploader', 'http://geodesicsolutions.com/component/content/article/52-importing-exporting/60-bulk-uploader.html?directory=64',0);
        $purchase['discount_codes'] = array ('Discount Codes','http://geodesicsolutions.com/component/content/article/53-added-value/69-discount-codes.html?directory=64',1);
        $purchase['google_maps'] = array('Google Maps','http://geodesicsolutions.com/component/content/article/50-browsing-enhancements/78-google-maps.html?directory=64',0);
        $purchase['multi_admin'] = array('Multi-Admin','http://geodesicsolutions.com/component/content/article/54-access-security/61-multi-admin.html?directory=64',0);
        $purchase['pedigree_tree'] = array('Pedigree Tree','http://geodesicsolutions.com/component/content/article/50-browsing-enhancements/79-pedigrees.html?directory=64',0);
        $purchase['signs_flyers'] = array ('Signs & Flyers','http://geodesicsolutions.com/component/content/article/53-added-value/68-signs-flyers.html?directory=64',1);
        $purchase['social_connect'] = array ('Social Connect (Facebook)', 'http://geodesicsolutions.com/component/content/article/51-third-party-integrations/340-social-connect-addon.html?directory=64');
        if (!$gtStatus) {
            $purchase['storefront'] = array ('Storefront Addon','http://geodesicsolutions.com/component/content/article/53-added-value/59-storefront.html?directory=64',1);
        }
        $purchase['SEO'] = array ('Search Engine Friendly URLs','http://geodesicsolutions.com/component/content/article/50-browsing-enhancements/66-seo.html?directory=64',1);
        if (!$gtStatus) {
            $purchase['tokens'] = array ('Tokens', 'http://geodesicsolutions.com/component/content/article/53-added-value/341-tokens-add-on.html?directory=64',1);
        }
        $purchase['zipsearch'] = array('Zip/Postal Code Search','http://geodesicsolutions.com/component/content/article/50-browsing-enhancements/62-zip-postal-code.html?directory=64',0);
        $purchase['exporter'] = array('Listing Exporter','http://geodesicsolutions.com/component/content/article/52-importing-exporting/75-listing-export.html?directory=64',0);
        $purchase['sharing'] = array('Sharing','http://geodesicsolutions.com/component/content/article/51-third-party-integrations/299-sharing-add-on.html?directory=64',0);
        $purchase['social_connect'] = array('Social Connect','http://geodesicsolutions.com/component/content/article/51-third-party-integrations/340-social-connect-addon.html?directory=64',0);
        $purchase['twitter_feed'] = array('Twitter Feed','http://geodesicsolutions.com/component/content/article/51-third-party-integrations/284-twitter-feed-add-on.html?directory=64',0);
        if (!geoPC::is_whitelabel() && !$gtStatus) {
            $purchase['mobile_api'] = array('Mobile API','http://geodesicsolutions.com/component/content/article/53-added-value/356-mobile-api-app.html?directory=64',0);
        }

        //used as an example..
        $addon_obj =& geoAddon::getInstance();
        $row_color = '';

        $header = "<thead><tr class='addon_hdr_notinstalled'><th colspan='8'>Not Installed Addons</th></tr>
							<tr class='col_hdr_top'>
							<th class='medium_font_light'>&nbsp;</th>
							<th class='medium_font_light'>&nbsp;</th>
							<th class='medium_font_light'>Addon</th>
							<th class='medium_font_light'>Version</th>
							" . (geoPC::is_whitelabel() ? "" : "<th class='medium_font_light'>Author</th>") . "
							<th class='medium_font_light'>Status</th>
							<th class='medium_font_light'>&nbsp;</th>
							<th class='medium_font_light'>&nbsp;</th>
						</tr>
						</thead>
							";


        foreach ($this->addons as $name => $addon) {
            if (isset($addon['db']['enabled']) || $addon_obj->isInstalled($name)) {
                //only show not installed
                continue;
            }
            $count++;
            $row_color = ($row_color == 'row_color1') ? 'row_color2' : 'row_color1';
            if ($count == 1) {
                //first time, show top of table.
                //show enabled addons.
                $body .= $header;
            }

            $status = '<span style="color: red;">Not Installed</span>';
            $install = "<a id='{$name}_enable' href='#' onclick='jQuery.post(\"AJAX.php?controller=AddonManage&action=_action&addon={$name}&task=install\", function(msg){if(msg.trim().length > 0){gjUtil.addMessage(msg);}else{gjUtil.addMessage(\"<div class=success><ul><li>Addon Installed. Reloading...</li></ul></div>\"); setTimeout(location.reload(),3000);}}); return false;' class='btn btn-primary btn-xs'>Install</a>";

            //generate tooltip.
            $message_body = $this->_getAddonTooltip($addon['info'], $addon['db']);

            $body .= "<tr class=\"$row_color\">
			<td class=\"medium_font\">&nbsp;</td>
			<td class=\"medium_font\" style='text-align: center;'>$install</td>
			<td class=\"medium_font\" tooltip=\"$message_body\">{$addon['info']->title}</td>
			<td class=\"medium_font\" style='text-align: center;'>{$addon['info']->version}</td>
			" . (geoPC::is_whitelabel() ? "" : "<td class=\"medium_font\" tooltip=\"$message_body\">{$addon['info']->author}</td>") . "
			<td class=\"medium_font\" style='text-align: center;'>$status</td>
			<td class=\"medium_font_light\">&nbsp;</td>
			<td class=\"medium_font_light\">&nbsp;</td>
			</tr>\n";
        }
        $message_body_p = 'Addon not found.  You can purchase this addon from <a href="http://geodesicsolutions.com">geodesicsolutions.com</a>.<br /><br />
Once you have purchased, download the zip file for the addon under the order details page.  Then unzip, and follow the instructions for how to upload and install the addon.';
        $message_body_u = 'Addon not found.  This addon should have been included with your product.  <strong>Make sure you upload all files.</strong><br /><br />
Missing files for folder: ';
        $install_u = 'Upload Files';
        $status = '<span style="color:red; ">Not Found</span>';
        $message_body_p = geoString::specialChars($message_body_p);
        $message_body_u = geoString::specialChars($message_body_u);
        foreach ($purchase as $name => $data) {
            if (!key_exists($name, $this->addons) && !is_dir(ADDON_DIR . $name)) {
                $count++;
                if ($count == 1) {
                    //first time, show top of table.
                    //show enabled addons.
                    $body .= $header;
                }
                $row_color = ($row_color == 'row_color1') ? 'row_color2' : 'row_color1';
                //$i++;
                $title = $data[0];
                $url = $data[1];
                //figure out if this is purchase, or just need to upload.

                $message_body = $message_body_u . geoString::specialChars('<strong>addons/' . $name . '/</strong>');
                $install = $install_u;

                $body .= "<tr id='tr_$name' class=\"$row_color\">
					<td class=\"medium_font\">&nbsp;</td>
					<td class=\"medium_font\" style='text-align: center;'>$install</td>
					<td class=\"medium_font\" tooltip=\"$message_body\">{$title}</td>
					<td class=\"medium_font\" style='text-align: center;'>N/A</td>
					" . (geoPC::is_whitelabel() ? "" : "<td class=\"medium_font\" tooltip=\"$message_body\">Geodesic Solutions LLC.</td>") . "
					<td class=\"medium_font\" style='text-align: center;'>$status</td>
					<td class=\"medium_font_light\">&nbsp;</td>
					<td class=\"medium_font_light\">&nbsp;</td>
					</tr>\n";
            }
        }

        if (false && $count) {
            //at least one shown, so output bottom of table.
            $body .= '
		</table>';
        }
    }

    function get_addon_details()
    {
        if (isset($this->addons) && is_array($this->addons)) {
            //we already ran this once.
            return true;
        }
        $folders_unsorted = array_diff(scandir(ADDON_DIR), array('.','..'));

        //stick teh folders in an array with index the lowercase version, so we
        //can then sort based on key so that uppercase does not come before
        //lowercase
        $folders = array ();
        foreach ($folders_unsorted as $folder) {
            $folders[strtolower($folder)] = $folder;
        }
        unset($folders_unsorted);

        //sort the folders by key
        ksort($folders);

        $this->addons = array();
        $this->actions_exclusive = array();
        $addon_obj =& geoAddon::getInstance();
        foreach ($folders as $filename) {
            if ($filename != '.' && $filename != '..' && is_dir(ADDON_DIR . $filename)) {
                //only include if it isn't an enabled addon...
                $filename = $addon_obj->getRealName($filename);

                $addon_dir = realpath(ADDON_DIR . $filename);
                if (file_exists($addon_dir . '/info.php')) {
                    include_once $addon_dir . '/info.php';
                    if (!class_exists('addon_' . $filename . '_info')) {
                        continue;
                    }
                    $this->addons [$filename]['info'] =& Singleton::getInstance('addon_' . $filename . '_info');
                    $this->addons [$filename]['db'] =& $this->addon->getInstalledInfo($filename);
                    //check to see if exclusive and valid set.
                    if (isset($this->addons[$filename]['info']->exclusive) && isset($this->addons[$filename]['info']->core_events) && count($this->addons[$filename]['info']->core_events)) {
                        //it attached to one or more core events, and is exclusive, so
                        //remember it.
                        foreach ($this->addons[$filename]['info']->core_events as $event_name) {
                            //remember which one it is exclusive to.

                            $core_exclusive = false;
                            //break up the if stmt so that it's easier to understand..
                            if (!is_array($this->addons[$filename]['info']->exclusive) && $this->addons[$filename]['info']->exclusive == true) {
                                //it is not an array, it means all core events for
                                //this addon are exclusive
                                $core_exclusive = true;
                            } elseif (
                                is_array($this->addons[$filename]['info']->exclusive) && isset($this->addons[$filename]['info']->exclusive[$event_name])
                                && $this->addons[$filename]['info']->exclusive[$event_name] == true
                            ) {
                                //the current core event is exclusive
                                $core_exclusive = true;
                            }
                            if ($core_exclusive) {
                                //remember core events that are exclusive
                                $this->actions_exclusive[$event_name][] =& $this->addons[$filename]['info'];
                            }
                        }
                    }
                }
            }
        }
    }
    var $showingTooltips;
}
