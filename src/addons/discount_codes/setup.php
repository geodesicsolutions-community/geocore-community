<?php

//addons/discount_codes/setup.php
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
## ##    7.0.0
##
##################################

# Discount Codes Addon

require_once ADDON_DIR . 'discount_codes/info.php';

class addon_discount_codes_setup extends addon_discount_codes_info
{

    public function install()
    {
        $db = DataAccess::getInstance();

        $sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_discount_codes` (
  `discount_id` int(11) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `discount_code` varchar(255) NOT NULL,
  `discount_percentage` double(5,2) NOT NULL default '0.00',
  `starts` int(11) NOT NULL default '0',
  `ends` int(11) NOT NULL default '0',
  `apply_normal` tinyint(4) NOT NULL default '1',
  `apply_recurring` tinyint(4) NOT NULL default '0',
  `is_group_specific` tinyint(4) NOT NULL default '0',
  `active` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `discount_email` tinytext NOT NULL,
  PRIMARY KEY  (`discount_id`),
  KEY `user_id` (`user_id`),
  KEY `discount_code` (`discount_code`),
  KEY `starts` (`starts`),
  KEY `ends` (`ends`),
  KEY `apply_normal` (`apply_normal`),
  KEY `apply_recurring` (`apply_recurring`)
) AUTO_INCREMENT=19";

        $sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_discount_codes_groups` (
  `discount_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  UNIQUE KEY `discount_id` (`discount_id`,`group_id`)
)";
        foreach ($sqls as $sql) {
            $result = $db->Execute($sql);
            if (!$result) {
                //query failed, return false.
                return false;
            }
        }

        //execute successful, install worked.
        return true;
    }

    public function uninstall()
    {
        $db = DataAccess::getInstance();

        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_discount_codes`";
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_discount_codes_groups`";
        foreach ($sqls as $sql) {
            $result = $db->Execute($sql);
            if (!$result) {
                //query failed, return false.
                return false;
            }
        }
        return true;
    }

    public function upgrade($old_version)
    {
        $menu_loader = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $ignore_db_errors = false;

        //whether or not table name is fixed
        $table_renamed = false;
        $sqls = array();
        $tableName = 'geodesic_addon_discount_codes';

        switch ($old_version) {
            case '1.0.0':
                //break omited on purpose
            case '1.0.1':
                if (!$db->tableExists('geodesic_addon_discount_codes')) {
                    $sqls[] = "RENAME TABLE `geodesic_classifieds_discount_codes` TO `geodesic_addon_discount_codes`";
                    $table_renamed = true;
                    $tableName = 'geodesic_classifieds_discount_codes';
                }
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` CHANGE `discount_code` `discount_code` VARCHAR( 255 ) NOT NULL";
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` ADD INDEX `discount_code` ( `discount_code` )";

                $menu_loader->userNotice('Notice: Length of discount code has been shortened to a max length of 255 characters, to optimize database performance and improve the speed.');

                //break ommitted on purpose..

            case '1.1.0':
            case '1.1.1':
            case '2.0.0':
                //break ommitted on purpose on preceeding cases
            case '2.0.1':
                //need to fix any discount code values
                $rows = $db->GetAll("SELECT `order_item` FROM `geodesic_order_item_registry` WHERE `index_key`='discount_code'");
                foreach ($rows as $row) {
                    $orderItem = geoOrderItem::getOrderItem($row['order_item']);
                    if ($orderItem) {
                        $code = $orderItem->get('discount_code');
                        if ($code) {
                            //un-do the double-encoding
                            $code = geoString::fromDB($code);
                            $orderItem->set('discount_code', $code);
                            $orderItem->save();
                        }
                    }
                }
                //break ommited on purpose

            case '2.0.2':
            case '2.1.0':
            case '2.1.1':
            case '2.1.2':
            case '2.1.3':
            case '2.1.4':
            case '2.1.5':
            case '2.1.6':
            case '2.1.7':
            case '2.1.8':
                //break ommitted on purpose on preceeding cases
            case '2.1.9':
                if (!$table_renamed && !$db->tableExists('geodesic_addon_discount_codes')) {
                    $sqls[] = "RENAME TABLE `geodesic_discount_codes` TO `geodesic_addon_discount_codes`";
                    $table_renamed = true;
                    $tableName = 'geodesic_discount_codes';
                }
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` ADD `starts` INT NOT NULL DEFAULT '0' AFTER `discount_percentage` ,
						ADD `ends` INT NOT NULL DEFAULT '0' AFTER `starts` ,
						ADD `apply_normal` TINYINT NOT NULL DEFAULT '1' AFTER `ends` ,
						ADD `apply_recurring` TINYINT NOT NULL DEFAULT '0' AFTER `apply_normal`,
						ADD `is_group_specific` TINYINT NOT NULL DEFAULT '0' AFTER `apply_recurring`";
                //add indexes to make it speedy
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` ADD INDEX `starts` ( `starts` )";
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` ADD INDEX `ends` ( `ends` )";
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` ADD INDEX `apply_normal` ( `apply_normal` )";
                $sqls[] = "ALTER TABLE `geodesic_addon_discount_codes` ADD INDEX `apply_recurring` ( `apply_recurring` )";
                //set starts to current time for all discount codes
                $sqls[] = "UPDATE `geodesic_addon_discount_codes` SET `starts`='" . geoUtil::time() . "' WHERE `starts`='0'";
                //add new table for discount groups
                $sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_discount_codes_groups` (
  `discount_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  UNIQUE KEY `discount_id` (`discount_id`,`group_id`)
)";
                //break ommited on purpose

            case '2.2.0':
                //need to set discount_id to each of the discount code order items,
                //for statistic purposes...
                $rows = $db->GetAll("SELECT `order_item` FROM `geodesic_order_item_registry` WHERE `index_key`='discount_code'");
                $discounts = array();
                foreach ($rows as $row) {
                    $orderItem = geoOrderItem::getOrderItem($row['order_item']);
                    if ($orderItem) {
                        $code = $orderItem->get('discount_code');
                        if (!isset($discounts[$code])) {
                            //get info about code
                            $discounts[$code] = $db->GetRow(
                                "SELECT * FROM `$tableName` WHERE `discount_code`=?",
                                array(geoString::toDB($code))
                            );
                        }
                        if (isset($discounts[$code]['discount_id'])) {
                            $orderItem->set('discount_id', $discounts[$code]['discount_id']);

                            $order = $orderItem->getOrder();
                            if (is_object($order) && $order->getStatus() == 'active' && $orderItem->getStatus() !== 'active') {
                                //fix order item status to be active.. previous bug
                                //kept order items from being changed to active.
                                $orderItem->setStatus('active');
                            }

                            $orderItem->save();
                        }
                    }

                    unset($orderItem);
                }

                //break ommitted on purpose

            case '2.2.1':
                //break ommitted on purpose

            default:
                //don't recognize this update?  oops...
                break;
        }

        if (count($sqls) > 0) {
            //go through each query and run it
            foreach ($sqls as $sql) {
                $result = $db->Execute($sql);
                if (!$result) {
                    $menu_loader->userError('DB Error, unable to complete upgrade.  Debug: SQL Query: ' . $sql . '<br />Error Msg: ' . $db->ErrorMsg());
                    if (!$ignore_db_errors) {
                        //if there is a problem running a query, and ignore db errors is off (set at top of function), then don't allow upgrade.
                        return false;
                    }
                }
            }
        }
        //upgrade successful.
        return true;
    }
}
