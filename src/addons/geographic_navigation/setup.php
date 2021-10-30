<?php

//addons/geographic_navigation/setup.php

# geographic_navigation Addon
class addon_geographic_navigation_setup
{
    private function _newTableQueries($type = 'install')
    {
        if ($type == 'install' || $type == 'uninstall') {
            $sql [] = "DROP TABLE IF EXISTS `geodesic_addon_geographic_regions`;";
            $sql [] = "DROP TABLE IF EXISTS `geodesic_addon_geographic_listings`";
            $sql [] = "DROP TABLE IF EXISTS `geodesic_addon_geographic_users`";
        }
        if ($type == 'install' || $type == 'update') {
            $sql [] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_geographic_regions` (
  `id` int(11) NOT NULL auto_increment,
  `parent_region` int(11) NOT NULL,
  `parent_state` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `subdomain` varchar(255) NOT NULL default '',
  `display_order` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent_region` (`parent_region`),
  KEY `parent_state` (`parent_state`),
  KEY `label` (`label`),
  KEY `display_order` (`display_order`),
  KEY `subdomain` (`subdomain`)
) AUTO_INCREMENT=1 ;";

            $sql [] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_geographic_listings` (
  `listing` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `region_name` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  UNIQUE KEY `listing` (`listing`,`region_id`),
  KEY `level` (`level`)
)";
            $sql [] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_geographic_users` (
  `user` int(11) NOT NULL,
  `confirmation` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  UNIQUE KEY `user` (`user`,`confirmation`, `region_id`),
  KEY `level` (`level`)
)";
        }
        return $sql;
    }

    public function install()
    {
        $db = $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        //make it added to fields table, to enable by default
        $sql[] = "DELETE FROM " . geoTables::fields . " WHERE `field_name`='addon_geographic_navigation_location'";
        $sql[] = "INSERT INTO " . geoTables::fields . " (`field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `text_length`)
			VALUES ('addon_geographic_navigation_location', 1, 0, 1, 'other', 0)";
        $sql[] = "DELETE FROM " . geoTables::field_locations . " WHERE `field_name`='addon_geographic_navigation_location'";
        $sql[] = "INSERT INTO " . geoTables::field_locations . " (`group_id`, `category_id`, `field_name`, `display_location`)
			VALUES ('0', '0', 'addon_geographic_navigation_location', 'search_fields')";

        $sql = array_merge($sql, $this->_newTableQueries('install'));
        if ($sql) {
            foreach ($sql as $q) {
                $result = $db->Execute($q);
                if (!$result) {
                    $fail[] = $q . ' ' . $db->ErrorMsg();
                }
            }
        }
        $reg = geoAddon::getRegistry('geographic_navigation', true);
        //default to use 2 columns
        $reg->set('columns', 2);

        //make it combine with cat breadcrumb
        $reg->combineTree = 1;

        //turn on user use by default
        $reg->set('userUse', 1);

        $reg->save();

        return true;
    }

    /**
     * Optional, remove function if not needed.  This is run when an addon is un-installed.
     *
     * View the source to see an geographic_navigation that
     * removes the dummy database table that was
     * created in the geographic_navigation install routine.
     *
     * @return boolean True to continue un-installing the addon, False to halt
     *  un-install
     */
    public function uninstall()
    {
        //script to uninstall the geographic_navigation addon.

        //get $db connection and $cron object - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $db = $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        //remove fields to use
        $sql[] = "DELETE FROM " . geoTables::fields . " WHERE `field_name`='addon_geographic_navigation_location'";
        $sql[] = "DELETE FROM " . geoTables::field_locations . " WHERE `field_name`='addon_geographic_navigation_location'";

        $sql = array_merge($sql, $this->_newTableQueries('uninstall'));

        if ($sql) {
            foreach ($sql as $q) {
                $result = $db->Execute($q);
                if (!$result) {
                    $fail[] = $db->ErrorMsg();
                }
            }
            if (!empty($fail)) {
                foreach ($fail as $f) {
                    $admin->userError('Database execution error, installation failed.' . $f);
                }
                return false;
            }
        }

        return true;
    }

    public function upgrade($oldVersion)
    {
        $db = $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $sql = array();

        $showTagMessage = false;
        $reg = geoAddon::getRegistry('geographic_navigation', true);
        switch ($oldVersion) {
            case '1.0.0':
                //break ommited on purpose

            case '1.0.1':
                //move over old settings to new place

                $columns = $db->get_site_setting('addon_geographic_navigation_columns');
                $tree = $db->get_site_setting('addon_geographic_navigation_tree');

                if ($columns !== false) {
                    $db->set_site_setting('addon_geographic_navigation_columns', false);
                } else {
                    $columns = 1;
                }
                if ($tree !== false) {
                    $db->set_site_setting('addon_geographic_navigation_tree', false);
                } else {
                    $tree = 1;
                }

                //default to use 1 column
                $reg->set('columns', $columns);
                //default show full tree on
                $reg->set('tree', $tree);
                //break ommited on purpose

            case '1.0.2':
            case '1.0.3':
            case '1.0.4':
            case '1.0.5':
                //breaks ommited on above versions on purpose.

                //default to use in user
                $reg->set('userUse', 1);
                //break ommited on purpose

            case '2.0.0':
            case '2.0.1':
            case '2.0.2':
            case '2.0.3':
            case '2.0.4':
            case '2.1.0':
            case '2.2.0':
            case '2.2.1':
                //break ommited on above versions on purpose.
                $sql[] = "ALTER TABLE `geodesic_addon_geographic_regions` ADD `subdomain` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `label` ,
					ADD INDEX `subdomain` ( `subdomain` )";
                $sql[] = "ALTER TABLE `geodesic_states` ADD `subdomain` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `name`,
					ADD INDEX `subdomain` ( `subdomain` )";
                $sql[] = "ALTER TABLE `geodesic_countries` ADD `subdomain` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `name`,
					ADD INDEX `subdomain` ( `subdomain` )";
                //break ommited on purpose

            case '3.0.0':
                //user note about tags changing
                $admin->userNotice("The list regions tag has been replaced by the navigation
					addon tag.  Be sure to update your templates to use the new <strong>navigation</strong> addon
					tag.");

                //user note about fields to use
                $admin->userNotice("The settings now use the software's new Fields to Use system instead
				of saving fields to use settings in price plan settings.  You may need to make adjustments
				to the fields to use settings, as the old price plan settings are not able to be
				converted.");

                //add default field values to enable by default
                $sql[] = "REPLACE INTO " . geoTables::fields . " (`field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `text_length`)
					VALUES ('addon_geographic_navigation_location', 1, 0, 1, 'other', 0)";

                $sql[] = "REPLACE INTO " . geoTables::field_locations . " (`field_name`, `display_location`) VALUES ('addon_geographic_navigation_location','search_fields')";

                //remove old settings that aren't used any more
                $sql[] = "DELETE FROM " . geoTables::plan_item . " WHERE `order_item`='geographicNavRegions'";
                $sql[] = "DELETE FROM " . geoTables::plan_item_registry . " WHERE `plan_item` LIKE 'geographicNavRegions%'";

                //tree value has changed again!
                $reg->tree = ($reg->tree) ? 'full' : 'compact';

                //break ommited on purpose

            case '3.1.0':
            case '3.1.1':
            case '3.2.0':
            case '3.2.1':
            case '3.2.2':
            case '3.2.3':
            case '4.0.0':
            case '4.0.1':
            case '4.0.2':
            case '4.0.3':
            case '4.0.4':
            case '4.0.5':
            case '4.0.6':
                $sql[] = "ALTER TABLE `geodesic_countries` DROP `addon_geographic_navigation_used`";
                $sql[] = "ALTER TABLE `geodesic_countries` DROP `subdomain`";
                $sql[] = "ALTER TABLE `geodesic_states` DROP `addon_geographic_navigation_used`";
                $sql[] = "ALTER TABLE `geodesic_states` DROP `subdomain`";
                //break ommitted intentionally

            default:
                break;
        }
        $reg->save();
        //since it uses "if not exists"
        $sql = array_merge($sql, $this->_newTableQueries('update'));
        if ($sql) {
            foreach ($sql as $q) {
                $result = $db->Execute($q);
                if (!$result) {
                    $fail[] = $db->ErrorMsg();
                }
            }
            if (!empty($fail)) {
                $returnFalse = false;
                foreach ($fail as $f) {
                    if (strpos($f, 'Duplicate column name') !== false) {
                        //this error is OK, they just already have that column
                        continue;
                    }
                    $returnFalse = true;
                    $admin->userError('Database execution error, installation failed.' . $f);
                }
                if ($returnFalse) {
                    return false;
                }
            }
        }
        if ($showTagMessage) {
            $admin->userNotice("The list regions tag has been replaced by the navigation
				addon tag.  Be sure to update your templates to use the new <strong>navigation</strong> addon
				tag.");
        }

        return true;
    }
}
