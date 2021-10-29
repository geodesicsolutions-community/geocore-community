<?php

//addons/adplotter/setup.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.6.1-32-gfe4163a
##
##################################

# subscription_pricing Addon
require_once ADDON_DIR . 'adplotter/info.php';

class addon_adplotter_setup extends addon_adplotter_info
{

    public function install()
    {
        $db = DataAccess::getInstance();

        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_adplotter_affiliate_registrations`(
				`email` varchar(255) NOT NULL,
				PRIMARY KEY(`email`)
				)";
        if (!$db->Execute($sql)) {
            geoAdmin::m('Failed creating aff table', geoAdmin::ERROR);
            return false;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_adplotter_category_map`(
				`adplotter_name` VARCHAR(255) NOT NULL,
				`geo_id` INT(1) DEFAULT 0,
				PRIMARY KEY(`adplotter_name`)
				)";
        if (!$db->Execute($sql)) {
            geoAdmin::m('Failed creating category table', geoAdmin::ERROR);
            return false;
        }
        return true;

        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_adplotter_image_dispatch`(
				`listing` INT(255) NOT NULL,
				`image_order` INT(1) NOT NULL,
				`url` TEXT NOT NULL,
				PRIMARY KEY(`listing`,`image_order`)
				)";
        if (!$db->Execute($sql)) {
            geoAdmin::m('Failed creating dispatch table', geoAdmin::ERROR);
            return false;
        }
        return true;
    }

    public function upgrade($old_version)
    {
        $db = DataAccess::getInstance();
        if (version_compare($old_version, "1.1.0", "<")) {
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_adplotter_affiliate_registrations`(
					`email` varchar(255) NOT NULL,
					PRIMARY KEY(`email`)
					)";
            if (!$db->Execute($sql)) {
                geoAdmin::m('Failed creating aff table', geoAdmin::ERROR);
                return false;
            }
        }
        if (version_compare($old_version, "1.2.0", "<=")) {
            //some earlier versions accidentally made this column an INT
            $sql = "ALTER TABLE `geodesic_addon_adplotter_affiliate_registrations` CHANGE `email` `email` VARCHAR(255) NOT NULL";
            if (!$db->Execute($sql)) {
                geoAdmin::m('Failed altering aff table', geoAdmin::ERROR);
                return false;
            }
        }
        if (version_compare($old_version, "1.3.0", "<=")) {
            //add image dispatch table
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_adplotter_image_dispatch`(
				`listing` INT(255) NOT NULL,
				`image_order` INT(1) NOT NULL,
				`url` TEXT NOT NULL,
				PRIMARY KEY(`listing`,`image_order`)
				)";
            if (!$db->Execute($sql)) {
                geoAdmin::m('Failed creating dispatch table', geoAdmin::ERROR);
                return false;
            }
        }

        return true;
    }

    public function uninstall()
    {
        $db = DataAccess::getInstance();
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_adplotter_category_map`";
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_adplotter_affiliate_registrations`";
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_adplotter_image_dispatch`";

        foreach ($sqls as $sql) {
            $db->Execute($sql);
        }
        return true;
    }

    public function disable()
    {
        //addon disabled. signal adplotter to stop using the API (and go ahead and turn off the setting)
        //this is one exception to the rule that disabling an addon shouldn't affect its settings, because there's a remote server involved
        $reg = geoAddon::getRegistry($this->name);
        if ($reg->enabled == 1) {
            require_once ADDONS_DIR . 'adplotter/admin.php';
            $addonAdmin = new addon_adplotter_admin();
            if (!$addonAdmin->AdPlotterUnregister()) {
                geoAdmin::m('Failed to send Stop notification to adplotter', geoAdmin::ERROR);
                return false;
            }
            $reg->enabled = 0;
            $reg->save();
            return true;
        } else {
            //not enabled previously, so nothing to do
            return true;
        }
    }
}
