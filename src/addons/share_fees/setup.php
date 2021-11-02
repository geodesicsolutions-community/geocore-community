<?php

# Shared Fees Addon
require_once ADDON_DIR . 'share_fees/info.php';

class addon_share_fees_setup extends addon_share_fees_info
{

    function install()
    {
        $db = $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        //To avoid table name conflicts, make sure to prefix any tables with
        //the module name.

        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_share_fees_attachments` (
  			`attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  			`attached_user` int(11) NOT NULL,
  			`attached_to` int(11) NOT NULL,
  			`attachment_type` int(11) NOT NULL,
  			PRIMARY KEY (`attachment_id`),
  			KEY `attached_user` (`attached_user`,`attached_to`,`attachment_type`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";



        $sql[] = "
		CREATE TABLE IF NOT EXISTS `geodesic_addon_share_fees_settings` (
			`attachment_type_id` int(11) NOT NULL AUTO_INCREMENT,
			`attachment_label` tinytext NOT NULL,
  			`attachment_type_desc` text NOT NULL,
  			`max_attachments` int(11) NOT NULL,
  			`active` int(11) NOT NULL DEFAULT '0',
  			`percentage_fee_shared` int(11) NOT NULL,
  			`attaching_user_group` int(11) NOT NULL,
  			`attached_to_user_group` int(11) NOT NULL,
  			`required` int(3) NOT NULL DEFAULT '0',
  			`post_login_redirect` int(11) NOT NULL DEFAULT '0',
  			`store_category_display` int(11) NOT NULL DEFAULT '0',
  			`use_attached_messages` int(11) NOT NULL DEFAULT '0',
  			`fee_types_shared` text,
  			PRIMARY KEY (`attachment_type_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

        $sql[] = "
		INSERT IGNORE INTO `geodesic_addon_share_fees_settings` (`attachment_type_id`, `attachment_label`, `attachment_type_desc`, `max_attachments`, `active`, `percentage_fee_shared`, `attaching_user_group`, `attached_to_user_group`, `required`, `post_login_redirect`, `store_category_display`, `use_attached_messages`, `fee_types_shared`) VALUES
			(1, 'Share Fees', 'This allows the attaching user to share the final fees the site would collect from them with the user they have attached to.  \r\n\r\nThe number of users attached to would share the percentage of the final fees collected by the site', 1, 0, 50, 0, 0, 0, 0, 0, 0, 'auction_final_fees');
			";

        foreach ($sql as $q) {
            $r = $db->Execute($q);
            if (!$r) {
                //query failed, display message and return false.
                geoAdmin::m('Database execution error, installation failed.  Debug info: Query: ' . $q . ' Error: ' . $db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        $admin->userNotice('Database tables created successfully.');

        return true;
    }

    function uninstall()
    {
        //script to uninstall the share fees addon.
        $db = DataAccess::getInstance();

        //leave geodesic_addon_share_fees_attachments
        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_share_fees_settings`";
        foreach ($sqls as $sql) {
            $result = $db->Execute($sql);
            if (!$result) {
                //query failed, return false.
                return false;
            }
        }
        $admin->userNotice('Share fees database tables removed successfully.');
        return true;
    }
}
