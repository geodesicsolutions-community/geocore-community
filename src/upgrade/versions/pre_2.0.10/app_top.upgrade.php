<?php

 //app_top.upgrade.php

define('IN_UPGRADE', 1);
//make sure we have enough memory to load the upgrade script, since it takes
//more than normal.
include_once('../../../ini_tools.php');
//make sure it is at least 32 megs.
geoRaiseMemoryLimit('32M');

if (count($_GET) > 0 && !isset($_GET['locale'])) {
//FIX for strict mode
//only run if we are not on the first step, since the first step is where we
//verify the config.php file.
 //this initiates db connection for the upgrade script, along with e-mail tools.
    include_once('../../../app_top.common.php');

    $db->Execute('SET SESSION sql_mode=\'\'');
}
