<?php

//addons/storefront/setup.php

# storefront Addon
require_once ADDON_DIR . 'storefront/info.php';

class addon_storefront_setup extends addon_storefront_info
{

    function install()
    {
        $db = DataAccess::getInstance();

        //To avoid table name conflicts, make sure to prefix any tables with
        //the module name.

        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_users` (
		  `store_id` int(11) NOT NULL,
		  `user_email` varchar(255) NOT NULL default '0',
		  KEY `store_id` (`store_id`)
		)";



        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_user_settings` (
		  `owner` int(11) NOT NULL,
		  `logo` varchar(255) NOT NULL,
		  `logo_width` varchar(10) NOT NULL,
		  `logo_height` varchar(10) NOT NULL,
		  `logo_list_width` varchar(10) NOT NULL,
		  `logo_list_height` varchar(10) NOT NULL,
		  `welcome_message` text NOT NULL,
		  `home_link` text NOT NULL,
		  `store_off` varchar(2) NOT NULL,
		  `display_newsletter` varchar(2) NOT NULL,
		  `storefront_name` VARCHAR(255),
		  `seo_name` VARCHAR(255),
		  `default_page` INT(1)
		)";


        $sql[] = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_group_subscriptions_choices` (
			  `group_id` int(11) NOT NULL,
			  `choice_id` int(11) NOT NULL
			);
			";


        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_newsletter` (
		  `id` int(11) NOT NULL auto_increment,
		  `owner` int(11) NOT NULL default '0',
		  `subject` text NOT NULL,
		  `content` text NOT NULL,
		  `time` int(11) NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		)AUTO_INCREMENT=1" ;

        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_pages` (
		  `page_id` int(11) NOT NULL auto_increment,
		  `owner` int(11) NOT NULL,
		  `page_link_text` text NOT NULL,
		  `page_name` text NOT NULL,
		  `page_body` text NOT NULL,
		  `display_order` int(11) DEFAULT '0' NOT NULL,
		  PRIMARY KEY  (`page_id`),
		  KEY `owner` (`owner`)
		) AUTO_INCREMENT=1" ;

        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_categories` (
		  `category_id` int(11) NOT NULL auto_increment,
		  `owner` int(11) NOT NULL,
		  `category_name` text NOT NULL,
		  `display_order` int(11) NOT NULL,
		  `parent` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY  (`category_id`),
		  KEY `owner` (`owner`),
		  KEY `display_order` (`display_order`)
		) ;
		";


        $sql[] = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_subscriptions` (
			  `subscription_id` int(11) NOT NULL auto_increment,
			  `expiration` int(11) NOT NULL,
			  `user_id` int(11) NOT NULL,
			  `onhold_start_time` int(11) NOT NULL,
			  `recurring_billing` int(14) NOT NULL default '0',
			  PRIMARY KEY  (`subscription_id`),
			  KEY `user_id` (`user_id`)
			) AUTO_INCREMENT=1" ;

        $sql[] = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_subscriptions_choices` (
			  `period_id` int(14) NOT NULL auto_increment,
			  `display_value` tinytext NOT NULL,
			  `value` int(11) NOT NULL default '0',
			  `amount` decimal(14,4) NOT NULL default '0.00',
			  `trial` int(1) NOT NULL default '0',
			  PRIMARY KEY  (`period_id`)
			) AUTO_INCREMENT=1" ;
        //TODO: Is this used?
        $sql[] = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_template_modules` (
			  `module_id` int(11) NOT NULL,
			  `template_id` int(11) NOT NULL,
			  `connection_time` int(11) NOT NULL
			)" ;


        $sql[] = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_traffic` (
			  `log_id` int(11) NOT NULL auto_increment,
			  `owner` int(11) NOT NULL,
			  `time` int(11) NOT NULL,
			  `uvisits` int(11) NOT NULL,
			  `tvisits` int(11) NOT NULL,
			  PRIMARY KEY  (`log_id`)
			) AUTO_INCREMENT=1" ;

        $sql[] = "
			CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_traffic_cache` (
			  `log_id` int(11) NOT NULL auto_increment,
			  `owner` int(11) NOT NULL,
			  `ip` varchar(16) NOT NULL,
			  `time` int(11) NOT NULL,
			  PRIMARY KEY  (`log_id`)
			) AUTO_INCREMENT=1" ;


        $sql[] = "INSERT IGNORE INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `field_type`) VALUES ('0', '0', 'addon_storefront_display_link', 1, 'other')";

        foreach ($sql as $q) {
            $r = $db->Execute($q);
            if (!$r) {
                //query failed, display message and return false.
                geoAdmin::m('Database execution error, installation failed.  Debug info: Query: ' . $q . ' Error: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        $this->configureTrialTable();

        $imagesdir = dirname(__file__) . DIRECTORY_SEPARATOR . 'images';
        if (!is_writable($imagesdir)) {
            $error = "Please be sure to assign the proper permissions (CHMOD 777) to $imagesdir directory inside storefront addon directory.";
            geoAdmin::m($error, geoAdmin::NOTICE);
            require_once ADMIN_DIR . 'php5_classes/Notifications.class.php';
            Notifications::add($error);
        }

        $this->setDefaults();
        //init the cron job
        $this->initCron();

        return true;
    }

    function uninstall()
    {
        //script to uninstall the storefront addon.
        $db = $cron = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        #/*//tables to keep
        $no_drop = array(
        'geodesic_addon_storefront_users',
        'geodesic_addon_storefront_user_settings',
        'geodesic_addon_storefront_subscriptions_choices',
        'geodesic_addon_storefront_categories',
        'geodesic_addon_storefront_pages',
        'geodesic_addon_storefront_subscriptions',
        'geodesic_addon_storefront_traffic'
        );
        #*/

        $tables = geoAddon::getUtil('storefront', true)->tables();


        $error = false;
        $no_drop = array_flip($no_drop);
        foreach ($tables as $table) {
            //making sure we don't get rid of  tables that don't need to be removed
            if (isset($no_drop[$table])) {
                continue;
            }

            $sql = 'DROP TABLE IF EXISTS `' . $table . '`';
            $r = $db->Execute($sql);
            if ($r === false) {
                $error = true;
                //query failed, display message and return false.
                geoAdmin::m('Database execution error, uninstallation failed.  Debug: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                continue;
            }
        }

        $sql = "DROP TABLE IF EXISTS `geodesic_addon_storefront_trials_used`";
        $r = $db->Execute($sql);
        if ($r === false) {
            $error = true;
            geoAdmin::m('Database execution error, uninstallation failed.  Debug: ' . $db->ErrorMsg(), geoAdmin::ERROR);
        }

        if ($error) {
            return false;
        }

        //remove the cron
        $task = 'storefront:expire_storefront_subscriptions';

        //remove the cron task
        $remove_task_result = $cron->rem($task);

        return true;
    }

    function upgrade($old_version)
    {
        $sqls = array();

        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $inserted_fields = false;
        switch ($old_version) {
            case '1.0.0':
                //Most likely, coming from an update to Geo 4.0

            case '1.0.1':
            case '1.0.2':
            case '1.0.3':
            case '1.0.4':
            case '1.0.5':
            case '1.0.6':
            case '1.0.7':
                //make sure all other needed tables are created
                $this->install();

                //As more needed changes between 1.0 and 1.0.7 to structure are found, add them here.
                $sqls [] = "ALTER TABLE `geodesic_addon_storefront_traffic` CHANGE `store_id` `owner` INT( 11 ) NOT NULL";
                $sqls [] = "ALTER TABLE `geodesic_addon_storefront_traffic_cache` CHANGE `store_id` `owner` INT( 11 ) NOT NULL";
                $sqls [] = "ALTER TABLE `geodesic_addon_storefront_newsletter` CHANGE `store_id` `owner` INT( 11 ) NOT NULL";
                $sqls [] = "DROP TABLE IF EXISTS `geodesic_addon_storefront_display`";
                //force default settings
                $this->setDefaults();

                //convert settings in old table to new registry
                $data = $db->GetRow("SELECT * FROM `geodesic_addon_storefront_display`");
                $settings = geoAddon::getRegistry('storefront', true);
                $forceDefaultDisplay = true;
                if ($data && is_array($data)) {
                    foreach ($data as $key => $value) {
                        $settings->$key = $value;
                    }
                }
                //set any settings from site settings
                $data = $db->get_site_settings(1);
                $skiplist = array('storefront_url');
                foreach ($data as $key => $value) {
                    if (strpos($key, 'storefront_') === 0 && !in_array($key, $skiplist)) {
                        //storefront setting
                        $db->set_site_setting($key, false);//remove it from being set in global site settings

                        $newkey = substr($key, 11);
                        $settings->$newkey = $value;
                    }
                }
                $settings->save();

                //break omitted on purpose

            case '1.0.8':
            case '1.0.9':
            case '1.0.10':
                //it would be nice if we did all the altering we needed, at some point.
                $sqls [] = "ALTER TABLE `geodesic_addon_storefront_categories` CHANGE `user_id` `owner` INT( 11 ) NOT NULL DEFAULT '0'";
                $sqls [] = "ALTER TABLE `geodesic_addon_storefront_pages` CHANGE `user_id` `owner` INT( 11 ) NOT NULL DEFAULT '0'";
                //break omitted on purpose
            case '1.0.11':
            case '1.0.12':
            case '1.0.13':
                //break intentionally omitted
            case '1.1.1':
            case '1.2.0':
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `logo_list_width` varchar(10) NOT NULL";
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `logo_list_height` varchar(10) NOT NULL";
                //break intentionally omitted
            case '1.2.1':
                //fresh installs of 1.1.1 and 1.2.0 didn't have the logo_list columns added to the DB, but upgrades did (oops)
                //need to check to see if the columns exist, and add them if they don't
                $check = "select logo_list_width, logo_list_height from `geodesic_addon_storefront_user_settings` LIMIT 1";
                $columns = $db->Execute($check);
                if (!$columns) {
                    //query failed, which means columns don't exist
                    $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `logo_list_width` varchar(10) NOT NULL";
                    $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `logo_list_height` varchar(10) NOT NULL";
                }


                //allow sorting pages

                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_pages` ADD COLUMN `display_order` int(11) DEFAULT '0' NOT NULL";

                geoAdmin::m('In this version of the Storefront addon, the floating Storefront Manager has been replaced by the Storefront Control Panel page, which may be found in a user\'s My Account page.', geoAdmin::NOTICE);
                geoAdmin::m('The old (!STOREFRONT_MANAGER!) tag now provides an alternate link to the control panel', geoAdmin::NOTICE);


                //check for using old User Management Home page
                if ($db->get_site_setting('my_account_home_type') != 1) {
                    geoAdmin::m('We have detected that you are using the old User Management Home page. To allow your users access to the new Storefront Control Panel, see the "control_panel_link" tag that is now available.');
                }

                //break intentionally omitted

            case '1.3.0':
                //break intentionally omitted
            case '1.3.1':
                //make sure all users with stores have a row in the settings table
                $sql = "SELECT user_id from geodesic_addon_storefront_subscriptions";
                $result = $db->Execute($sql);
                while ($user = $result->FetchRow()) {
                    $sql = "SELECT owner FROM geodesic_addon_storefront_user_settings WHERE owner = ?";
                    $owner = $db->GetOne($sql, array($user['user_id']));
                    if ($owner > 0 && $owner == $user['user_id']) {
                        //good to go
                    } else {
                        //create user settings row, so we have some place to save settings
                        $sql = "INSERT INTO geodesic_addon_storefront_user_settings (owner) VALUES (?)";
                        $insert_result = $db->Execute($sql, array($user['user_id']));
                        if (!$insert_result) {
                            trigger_error("DEBUG STORE: failed to create settings row. MySQL said: " . $db->ErrorMsg());
                        }
                    }
                }
                //break intentionally omitted

            case '1.3.2':
            case '1.3.3':
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `storefront_name` VARCHAR(255)";
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_pages` ADD COLUMN `display_order` int(11) DEFAULT '0' NOT NULL";
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `seo_name` VARCHAR(255)";
                //break intentionally omitted
            case '1.3.4':
            case '1.3.5':
            case '1.3.6':
            case '1.3.7':
            case '1.4.0':
            case '1.4.1':
                //add recurring billing column for subscriptions
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_subscriptions` ADD `recurring_billing` INT( 14 ) NOT NULL default '0'";
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_subscriptions` ADD INDEX `recurring_billing` ( `recurring_billing` ) ";
                //break intentionally omitted

            case '1.5.0':
            case '1.5.1':
            case '1.6.0':
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `default_page` INT(1)";
                //set some default settings
                $reg = geoAddon::getRegistry('storefront', true);
                $reg->set('list_max_stores', 25);
                $reg->set('max_logo_width', 200);
                $reg->set('max_logo_height', 50);
                $reg->set('max_logo_width_in_store', 450);
                $reg->set('max_logo_height_in_store', 110);
                $reg->set('list_description_length', 30);
                $reg->set('list_show_logo', 1);
                $reg->set('list_show_title', 1);
                $reg->set('list_show_num_items', 1);
                $reg->set('list_show_description', 1);
                $reg->set('list_show_city', 1);
                $reg->set('list_show_state', 1);
                $reg->set('list_show_zip', 1);
                $reg->save();
            case '1.7.0':
            case '1.7.2':
            case '1.7.3':
            case '1.7.4':
            case '1.7.5':
            case '1.8.0':
            case '1.8.1':
            case '1.8.2':
            case '1.8.3':
            case '1.8.4':
            case '1.8.5':
            case '1.8.6':
            case '1.8.7':
            case '1.8.8':
                //breaks intentionally omitted
                $this->configureTrialTable();
            case '1.8.9':
            case '1.8.10':
            case '1.8.11':
            case '1.9.0':
            case '1.9.1':
                //move old Display Storefront Link setting to Fields to Use
                $cats = $db->Execute("SELECT `category_id` FROM `geodesic_categories` WHERE `display_storefront_link` = '1'");
                while ($cats && $cat = $cats->FetchRow()) {
                    $sqls[] = "INSERT IGNORE INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `field_type`) VALUES ('0', '" . $cat['category_id'] . "', 'addon_storefront_display_link', 1, 'other')";
                    $sqls[] = "INSERT IGNORE INTO " . geoTables::field_locations . " (`group_id`,`category_id`,`field_name`,`display_location`) VALUES
						(0, {$cat['category_id']}, 'addon_storefront_display_link', 'browsing')";
                }
                $inserted_fields = true;
            case '1.9.2':
            case '1.9.3':
                if (!geoPC::is_whitelabel()) {
                    geoAdmin::m(
                        "Notice: a Recommended Template Change exists for this update.
					<a href='https://geodesicsolutions.org/wiki/designers/changes_to_note/#storefront_addon_main_pagestorefront_default_templatetpl_703' target=\"_blank\">More info</a>",
                        geoAdmin::NOTICE
                    );
                }
            case '1.9.4':
                //add fields to use locations...
                if (!$inserted_fields) {
                    $location = 'browsing';
                    $cats = $db->Execute("SELECT `group_id`, `category_id` FROM " . geoTables::fields . " WHERE `field_name`='addon_storefront_display_link' AND `is_enabled`=1");
                    foreach ($cats as $row) {
                        $sqls[] = "INSERT IGNORE INTO " . geoTables::field_locations . " (`group_id`,`category_id`,`field_name`,`display_location`) VALUES
								({$row['group_id']}, {$row['category_id']}, 'addon_storefront_display_link', 'browsing')";
                    }
                    $inserted_fields = true;
                }
            case '1.9.5':
            case '1.9.6':
            case '2.0.0':
                $sqls[] = "ALTER TABLE `geodesic_addon_storefront_categories` ADD `parent` int(11) NOT NULL DEFAULT '0'";
            case '2.0.1':
            case '2.0.2':
            case '2.0.3':
            case '2.0.4':
            case '2.0.5':
            default:
                break;
        }
        foreach ($sqls as $sql) {
            $db->Execute($sql);
        }

        //fix 1.2/1.3 database structure
        if (strpos($old_version, '1.2') == 0 || strpos($old_version, '1.3') == 0) {
            $this->checkDbStructure();
        }
        //init the cron job
        $this->initCron();
        return true;
    }


    public function configureTrialTable()
    {
        //refactor the old way of storing trial information in the main userdata table

        //create new db table
        $db = DataAccess::getInstance();
        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_storefront_trials_used` (
			user_id INT(1) NOT NULL,
			trial_id INT(1) NOT NULL
		)";
        $r = $db->Execute($sql);
        if (!$r) {
            return false;
        }

        //move data from old location to new
        $sql = "SELECT `storefront_trials_used`, `id` FROM `geodesic_userdata`";
        $r = $db->Execute($sql);
        $add = $db->Prepare("INSERT INTO `geodesic_addon_storefront_trials_used` (`user_id`, `trial_id`) VALUES (?,?)");
        while ($r && $line = $r->FetchRow()) {
            $user = $line['id'];
            $trials = explode(',', $line['storefront_trials_used']);
            foreach ($trials as $trial) {
                $db->Execute($add, array($user, $trial));
            }
        }

        //clear old data
        $sql = "UPDATE `geodesic_userdata` SET `storefront_trials_used` = ''";
        $r = $db->Execute($sql);
        return true;
    }

    public function checkDbStructure()
    {
        //useful for upgrading to 1.3.x, to make sure all needed columns have been added to the database
        //(because there were a few bugs that got in the way of that, the first time around)

        //if any column already exists, its query will silently fail
        $sqls = array();
        $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `logo_list_width` varchar(10) NOT NULL";
        $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `logo_list_height` varchar(10) NOT NULL";
        $sqls[] = "ALTER TABLE `geodesic_addon_storefront_pages` ADD COLUMN `display_order` int(11) DEFAULT '0' NOT NULL";
        $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `storefront_name` VARCHAR(255)";
        $sqls[] = "ALTER TABLE `geodesic_addon_storefront_pages` ADD COLUMN `display_order` int(11) DEFAULT '0' NOT NULL";
        $sqls[] = "ALTER TABLE `geodesic_addon_storefront_user_settings` ADD COLUMN `seo_name` VARCHAR(255)";
        $db = DataAccess::getInstance();
        foreach ($sqls as $sql) {
            $db->Execute($sql);
        }
    }

    public function setDefaults()
    {
        $settings = geoAddon::getRegistry('storefront', true);
        if (!$settings) {
            return;
        }
        $settings->display_ad_title = 1;
        $settings->display_photo_icon = 1;
        $settings->display_price = 1;
        $settings->display_ad_description = 1;
        $settings->display_ad_description_where = 1;
        $settings->admin_display_reports = 1;
        $settings->admin_display_newsletter = 1;

        $settings->list_max_stores = 25;
        $settings->use_logo_for_store_links = 1;
        $settings->max_logo_width = 70;
        $settings->max_logo_height = 50;
        $settings->max_logo_width_in_store = 450;
        $settings->max_logo_height_in_store = 110;
        $settings->list_description_length = 30;
        $settings->list_show_logo = 1;
        $settings->list_show_title = 1;
        $settings->list_show_num_items = 1;
        $settings->list_show_description = 1;
        $settings->list_show_city = 1;
        $settings->list_show_state = 1;
        $settings->list_show_zip = 1;

        $settings->save();
    }

    public function initCron()
    {
        //add/update the cron task, since it's safe to call even if cron is already
        //added, go ahead and do this on every install or update
        $cron = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $task = 'storefront:expire_storefront_subscriptions';
        $type = 'addon';
        $interval = 3600;//run once an hour
        $cron_add = $cron->set($task, $type, $interval);
    }
}
