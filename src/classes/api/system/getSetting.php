<?php

//system/getSetting.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    6.0.7-2-gc953682
##
##################################

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
