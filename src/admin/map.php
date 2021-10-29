<?php

//map.php
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


class geoAdminMap
{
    public function display_site_map()
    {
        $admin = geoAdmin::getInstance();

        $admin->v()->hide_side_menu = 1; //make it not load side menu
        $admin->v()->hide_title = 1; //do not show title
        $admin->v()->hide_notifications = 1;//do not show admin notifications
        $admin->v()->addCssFile('css/site_map.css');
        $admin->setBodyTpl('site_map/index');
    }
}
