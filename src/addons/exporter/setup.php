<?php

//addons/exporter/setup.php

# Exporter Addon
require_once ADDON_DIR . 'exporter/info.php';

class addon_exporter_setup extends addon_exporter_info
{
    public function upgrade($old_version)
    {
        $db = DataAccess::getInstance();

        $sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_exporter_searches`";

        $sqls [] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_exporter_settings` (
  `name` varchar(128) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `copy_to` varchar(255) NOT NULL,
  `export_type` enum('xml','csv') NOT NULL,
  `settings` text NOT NULL,
  `created` int(11) NOT NULL,
  `last_updated` int(11) NOT NULL,
  PRIMARY KEY (`name`),
  KEY `created` (`created`),
  KEY `last_updated` (`last_updated`)
)";

        return $this->runSqls($sqls);
    }

    public function install()
    {
        $sqls [] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_exporter_settings` (
  `name` varchar(128) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `copy_to` varchar(255) NOT NULL,
  `export_type` enum('xml','csv') NOT NULL,
  `settings` text NOT NULL,
  `created` int(11) NOT NULL,
  `last_updated` int(11) NOT NULL,
  PRIMARY KEY (`name`),
  KEY `created` (`created`),
  KEY `last_updated` (`last_updated`)
)";

        return $this->runSqls($sqls);
    }

    private function runSqls($sqls)
    {
        $db = DataAccess::getInstance();

        foreach ($sqls as $sql) {
            $result = $db->Execute($sql);
            if (!$result) {
                $fail[] = $db->ErrorMsg();
            }
        }
        if (!empty($fail)) {
            $admin = geoAdmin::getInstance();
            foreach ($fail as $f) {
                $admin->userError('Database execution error. ' . $f);
            }
            return false;
        }
        return true;
    }
}
