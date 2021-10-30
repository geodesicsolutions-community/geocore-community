<?php

//addons/bulk_uploader/setup.php


# bulk_uploader Addon

require_once ADDON_DIR . 'bulk_uploader/info.php';

class addon_bulk_uploader_setup extends addon_bulk_uploader_info
{

    var $table_prefix = "geodesic_addon_bulk_uploader";

    function install()
    {
        $db = DataAccess::getInstance();
        if (!defined('GEO_BASE_DIR')) {
            define('GEO_BASE_DIR', BASE_DIR);
        }

        $sql[] = "
		CREATE TABLE IF NOT EXISTS `{$this->table_prefix}_log` (
		  `log_id` int(10) unsigned NOT NULL auto_increment,
		  `listing_id_list` text NOT NULL,
		  `user_id_list` text NOT NULL,
		  `insert_time` int(10) unsigned NOT NULL default '0',
		  `user_label` text NOT NULL default '',
		  PRIMARY KEY  (`log_id`)
		) AUTO_INCREMENT=1";


        $sql[] = "
		CREATE TABLE IF NOT EXISTS `{$this->table_prefix}_session` (
		  `id` varchar(32) NOT NULL default '',
		  `name` varchar(32) NOT NULL default '',
		  `value` text NOT NULL,
		  `vid` int(11) NOT NULL default '0',
		  `time` int(11) NOT NULL default '0',
		  KEY `id` (`id`)
		)";

        $sql[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_revolving` (
			`id` int(1) NOT NULL AUTO_INCREMENT,
			`label` VARCHAR(255) NOT NULL,
			`next_run` int(1) NOT NULL,
			PRIMARY KEY (`id`)
		)";

        $sql[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_revolving_map` (
			`revolving_id` VARCHAR(255) NOT NULL,
			`listing_id` int(1) NOT NULL,
			`uid` VARCHAR(255) NOT NULL
		)";

        $sql[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_listings` (
			`upload_id` int(1) NOT NULL,
			`listing_id` int(1) NOT NULL
		)";

        $sql[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_multipart` (
			`id` int(1) NOT NULL auto_increment,
			`count` int(1) NOT NULL,
			`gap` int(1) NOT NULL,
			`last_run` int(1) NOT NULL DEFAULT 0,
			`completed` int(1) NOT NULL DEFAULT 0,
			`settings` mediumtext NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) AUTO_INCREMENT=1";

        $sql[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_registry` (
			`index_key` varchar(255) NOT NULL,
			`val_string` varchar(255) NOT NULL DEFAULT '',
			`val_text` text NOT NULL DEFAULT '',
			`val_complex` longtext NOT NULL DEFAULT '',
			PRIMARY KEY (`index_key`)
		)";

        $sql[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_image_tokens` (
				`id` INT(1) NOT NULL AUTO_INCREMENT,
				`remote_url` tinytext NOT NULL DEFAULT '',
				`local_path` tinytext NOT NULL DEFAULT '',
				PRIMARY KEY (`id`)
		)";


        foreach ($sql as $q) {
            $result = $db->Execute($q);
            if (!$result) {
                $fail[] = $db->ErrorMsg();
            }
        }

        if (!empty($fail)) {
            //query failed, display message and return false.

            foreach ($fail as $f) {
                geoAdmin::m('Database execution error, installation failed.' . $f, geoAdmin::ERROR);
            }
            return false;
        }

        //set up the Revolving Inventory cron task
        $task = 'bulk_uploader:renew_revolving_inventory';
        $type = 'addon';
        $interval = 86400; //run task once a day. each individual upload will run only once a week.
        $cron = geoCron::getInstance();
        $cron_add = $cron->set($task, $type, $interval);
        if (!$cron_add) {
            geoAdmin::m('Revolving Cron Install Failed.', geoAdmin::ERROR);
            return false;
        }
        $db->set_site_setting('bulk_revolve_period', 7); //number of days to wait after updating each individual file before looking at it again

        //multipart upload cron
        $task = 'bulk_uploader:multipart_process';
        $type = 'addon';
        $interval = 3600; //check once an hour
        $cron_add = $cron->set($task, $type, $interval);
        if (!$cron_add) {
            geoAdmin::m('Multipart Cron Install Failed.', geoAdmin::ERROR);
            return false;
        }

        //If it made it all the way, then the installation was a success...
        return true;
    }

    function uninstall()
    {
        $db = DataAccess::getInstance();

        $sql[] = "DROP TABLE IF EXISTS `{$this->table_prefix}_session`;";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_revolving`";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_log`";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_listings`";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_revolving_map`";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_multipart`";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_registry`";
        $sql[] = "DROP TABLE IF EXISTS `geodesic_addon_bulk_uploader_image_tokens`";
        foreach ($sql as $q) {
            $result = $db->Execute($q);
            if (!$result) {
                $fail[] = $db->ErrorMsg();
            }
        }

        if (!empty($fail)) {
            //query failed, display message and return false.
            foreach ($fail as $f) {
                geoAdmin::m('Database execution error, uninstallation failed.' . $f, geoAdmin::ERROR);
            }
            return false;
        }

        //remove cron tasks
        $cron = geoCron::getInstance();
        $cron->rem('bulk_uploader:renew_revolving_inventory');
        $cron->rem('bulk_uploader:multipart_process');

        return true;
    }

    function upgrade($from_version = false)
    {
        $db = DataAccess::getInstance();
        $cron = geoCron::getInstance();

        if (version_compare($from_version, '2.4.0', '<=')) {
            //install the revolving inventory cron
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_revolving` (
				`id` int(1) NOT NULL AUTO_INCREMENT,
				`label` VARCHAR(255) NOT NULL,
				`next_run` int(1) NOT NULL,
				PRIMARY KEY (`id`)
			)";
            $result = $db->Execute($sql);
            if (!$result) {
                geoAdmin::m('db error adding revolving table', geoAdmin::ERROR);
                return false;
            }
            $task = 'bulk_uploader:renew_revolving_inventory';
            $type = 'addon';
            $interval = 86400; //run once a day
            $cron_add = $cron->set($task, $type, $interval);
            if (!$cron_add) {
                geoAdmin::m('Cron Install Failed.', geoAdmin::ERROR);
                return false;
            }
            $db->set_site_setting('bulk_revolve_period', 7); //number of days to wait after updating each individual file before looking at it again
        }

        if (version_compare($from_version, '2.5.0', '<=')) {
            $sql = "ALTER TABLE `geodesic_addon_bulk_uploader_log` ADD `user_label` TEXT NOT NULL default ''";
            $result = $db->Execute($sql);
            if (!$result) {
                geoAdmin::m('db error adding user label', geoAdmin::ERROR);
                return false;
            }
        }

        if (version_compare($from_version, '2.6.1', '<=')) {
            if (!is_dir(ADDON_DIR . 'bulk_uploader' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'revolving')) {
                mkdir(ADDON_DIR . 'bulk_uploader' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'revolving', 0777);
            }
        }

        if (version_compare($from_version, '3.1.1', '<=')) {
            //new, normalized table for logging uploads
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_listings` (
				`upload_id` int(1) NOT NULL,
				`listing_id` int(1) NOT NULL
			)";
            $result = $db->Execute($sql);
            if (!$result) {
                geoAdmin::m('db error adding new log table', geoAdmin::ERROR);
                return false;
            }
            //convert existing log entries to new system
            $sql = "SELECT * FROM `geodesic_addon_bulk_uploader_log` WHERE `listing_id_list` <> ''";
            $result = $db->GetAll($sql);
            $insert = $db->Prepare("INSERT INTO `geodesic_addon_bulk_uploader_listings` (`upload_id`,`listing_id`) VALUES (?, ?)");
            $clear = $db->Prepare("UPDATE `geodesic_addon_bulk_uploader_log` SET `listing_id_list` = '' WHERE `log_id` = ?");
            foreach ($result as $old) {
                $listings = explode(',', $old['listing_id_list']);
                foreach ($listings as $listingId) {
                    $db->Execute($insert, array($old['log_id'], $listingId));
                }
                //clear old log, so we know we've transitioned this one
                $db->Execute($clear, array($old['log_id']));
            }


            //create new revolving map table and move data over from the old way of storing it
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_revolving_map` (
			`revolving_id` VARCHAR(255) NOT NULL,
			`listing_id` int(1) NOT NULL,
			`uid` VARCHAR(255) NOT NULL
			)";
            if (!$db->Execute($sql)) {
                geoAdmin::m('db error adding map table ' . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }

            //get list of current revolving sessions
            $sql = "SELECT `label` FROM `geodesic_addon_bulk_uploader_revolving`";
            $labels = $db->GetAll($sql);
            $reg = geoAddon::getRegistry('bulk_uploader', true);
            $uidLog = $db->Prepare("INSERT INTO `geodesic_addon_bulk_uploader_revolving_map` (revolving_id, listing_id, uid) VALUES (?,?,?)");
            foreach ($labels as $l) {
                $oldSettings = $reg->{$l['label']};
                $oldMap = $oldSettings->uids_mapped;
                foreach ($oldMap as $listingId => $uniqueVal) {
                    //add mapping to db
                    $db->Execute($uidLog, array($l['label'], $listingId, $uniqueVal));
                }
                //remove mapping from registry
                $oldSettings->uids_mapped = false;
                $reg->{$l['label']} = $oldSettings;
            }
            $reg->save();
        }
        if (version_compare($from_version, '3.1.2', '<=')) {
            //on some installs, this field was created as an INT. It should be VARCHAR
            $sql = "ALTER TABLE `geodesic_addon_bulk_uploader_revolving_map` CHANGE `uid` `uid` VARCHAR( 255 ) NOT NULL";
            if (!$db->Execute($sql)) {
                geoAdmin::m('database error: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        if (version_compare($from_version, '3.3.2', '<=')) {
            //add table for delayed uploads
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_multipart` (
					`id` int(1) NOT NULL auto_increment,
					`count` int(1) NOT NULL,
					`gap` int(1) NOT NULL,
					`last_run` int(1) NOT NULL DEFAULT 0,
					`completed` int(1) NOT NULL DEFAULT 0,
					`settings` mediumtext NOT NULL DEFAULT '',
					PRIMARY KEY (`id`)
				) AUTO_INCREMENT=1";
            if (!$db->Execute($sql)) {
                geoAdmin::m('database error adding delayed upload table', geoAdmin::ERROR);
                return false;
            }
            //add cron task for multipart uploads
            $task = 'bulk_uploader:multipart_process';
            $type = 'addon';
            $interval = 3600; //check once an hour
            if (!$cron) {
                $cron = geoCron::getInstance();
            }
            $cron_add = $cron->set($task, $type, $interval);
            if (!$cron_add) {
                geoAdmin::m('Multipart Cron Install Failed.', geoAdmin::ERROR);
                return false;
            }
        }

        if (version_compare($from_version, '3.4.0', '<=')) {
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_registry` (
					`index_key` varchar(255) NOT NULL,
					`val_string` varchar(255) NOT NULL DEFAULT '',
					`val_text` text NOT NULL DEFAULT '',
					`val_complex` longtext NOT NULL DEFAULT '',
					PRIMARY KEY (`index_key`)
				)";
            if (!$db->Execute($sql)) {
                geoAdmin::m('database error adding registry table', geoAdmin::ERROR);
                return false;
            }
            //move data from old addon registry to new BulkUploaderRegistry
            $sql = "SELECT * FROM `geodesic_addon_registry` WHERE addon = 'bulk_uploader'";
            $result = $db->Execute($sql);
            while ($result && $l = $result->FetchRow()) {
                $r = $db->Execute(
                    'INSERT INTO `geodesic_addon_bulk_uploader_registry` (`index_key`,`val_string`,`val_text`,`val_complex`) VALUES (?,?,?,?)',
                    array($l['index_key'],$l['val_string'],$l['val_text'],$l['val_complex'])
                );
                if (!$r) {
                    geoAdmin::m('registry import error: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
            //remove old data
            $db->Execute("DELETE FROM `geodesic_addon_registry` WHERE `addon` = 'bulk_uploader'");
        }
        if (version_compare($from_version, '3.5.0', '<')) {
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_bulk_uploader_image_tokens` (
				`id` INT(1) NOT NULL AUTO_INCREMENT,
				`remote_url` tinytext NOT NULL DEFAULT '',
				`local_path` tinytext NOT NULL DEFAULT '',
				PRIMARY KEY (`id`)
			)";
            if (!$db->Execute($sql)) {
                geoAdmin::m('database error adding token table', geoAdmin::ERROR);
                return false;
            }
        }
        return true;
    }
}
