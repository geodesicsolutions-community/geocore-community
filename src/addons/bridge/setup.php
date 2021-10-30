<?php

//addons/example/setup.php

# Bridge

class addon_bridge_setup
{
    function install()
    {
        $db = $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $sql = 'CREATE TABLE IF NOT EXISTS `geodesic_bridge_installations` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(3) NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `active` (`active`),
  KEY `type` (`type`)
)';
        $result = $db->Execute($sql);
        if (!$result) {
            $admin->userError('DB Error, could not insert table needed for installation.');
            return false;
        }
        return true;
    }

    function uninstall()
    {
        $db = $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $sql = 'DROP TABLE IF EXISTS `geodesic_bridge_installations`';
        $result = $db->Execute($sql);
        if (!$result) {
            $admin->userError('DB Error, could not remove table `geodesic_bridge_installations` during uninstallation, you may need to remove this table manually.');
        }
        return true;
    }
}
