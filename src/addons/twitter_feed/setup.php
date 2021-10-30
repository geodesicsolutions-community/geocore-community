<?php

//addons/storefront/setup.php

# storefront Addon

require_once ADDON_DIR . 'twitter_feed/info.php';

class addon_twitter_feed_setup extends addon_twitter_feed_info
{
    public function install()
    {
        $db = DataAccess::getInstance();
        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_twitter_feed_timelines` (
			listing_id int(1) NOT NULL,
			href varchar(255) NOT NULL,
			data_id bigint(1) UNSIGNED NOT NULL,
			active int(1) NOT NULL default 0,
			PRIMARY KEY(listing_id)
		)";
        $result = $db->Execute($sql);
        if (!$result) {
            return false;
        }

        //default settings
        $reg = geoAddon::getRegistry('twitter_feed', true);
        $config = array(
            'tweet_limit' => 0,
            'default_href' => '',
            'default_data_id' => '',
            'width' => 0,
            'height' => 0,
            'link_color' => '',
            'border_color' => '',
            'chrome' => array(),
        );
        $reg->config = $config;
        $reg->save();
        return true;
    }

    public function uninstall()
    {
        $db = DataAccess::getInstance();
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_twitter_feed_usernames`"; //pre-2.0 table
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_twitter_feed_timelines`";
        foreach ($sqls as $sql) {
            if (!$db->Execute($sql)) {
                return false;
            }
        }
        return true;
    }

    public function upgrade($old_version)
    {
        if (version_compare($old_version, '2.0.0', '<')) {
            $db = DataAccess::getInstance();
            $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_twitter_feed_usernames`";
            $sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_twitter_feed_timelines` (
			listing_id int(1) NOT NULL,
			href varchar(255) NOT NULL,
			data_id bigint(1) UNSIGNED NOT NULL,
			active int(1) NOT NULL default 0,
			PRIMARY KEY(listing_id)
			)";
            foreach ($sqls as $sql) {
                if (!$db->Execute($sql)) {
                    return false;
                }
            }
            geoAdmin::m('Due to sweeping changes made to Twitter\'s API, the way this addon works has changed significantly. You may wish to create a test listing or see the User Manual to familiarize yourself with the changes.', geoAdmin::NOTICE);
        }
        return true;
    }
}
