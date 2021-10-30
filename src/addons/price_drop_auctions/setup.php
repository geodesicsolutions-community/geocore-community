<?php

//addons/price_drop_auctions/setup.php


# Pedigree Tree

require_once ADDON_DIR . 'price_drop_auctions/info.php';

class addon_price_drop_auctions_setup extends addon_price_drop_auctions_info
{
    public function install()
    {
        if (!geoMaster::is('auctions')) {
            geoAdmin::m('Auctions must be enabled to use this addon.', geoAdmin::ERROR);
            return false;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_price_drop_auctions`(
			`listing_id` INT(1) NOT NULL,
			`starting_price` DOUBLE(10,2) NOT NULL DEFAULT 0.00,
			`current_price` DOUBLE(10,2) NOT NULL DEFAULT 0.00,
			`minimum_price` DOUBLE(10,2) NOT NULL DEFAULT 0.00,
			`last_drop` INT(1) NOT NULL DEFAULT 0,
			`next_drop` INT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY(`listing_id`),
			KEY `next_drop` (`next_drop`),
			KEY `current_price` (`current_price`),
			KEY `minimum_price` (`minimum_price`)
		)";
        $r = DataAccess::getInstance()->Execute($sql);


        //instantiate Cron task
        $task = 'price_drop_auctions:price_drop';
        $type = 'addon';
        $interval = 300; //run task every 5 minutes
        if (!geoCron::getInstance()->set($task, $type, $interval)) {
            geoAdmin::m('Cron Install Failed.', geoAdmin::ERROR);
            return false;
        }

        //default config settings
        $reg = geoAddon::getRegistry($this->name, true);
        $reg->delay_low = 5;
        $reg->delay_high = 30;
        $reg->drop_amount_low = 5;
        $reg->drop_amount_high = 15;
        $reg->save();

        return true;
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS `geodesic_addon_price_drop_auctions`";
        $r = DataAccess::getInstance()->Execute($sql);
        return true;
    }
}
