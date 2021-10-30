<?php

//system/getSetting.php


if (!defined('IN_GEO_API')) {
    exit('No access.');
}
//This is a simple API function to get specified setting, sort of like calling
//DataAccess::get_site_setting()

$setting = $args['setting'] . '';

if (!$setting) {
    //no setting specified, return false
    return false;
}

return $this->db->get_site_setting($setting);
