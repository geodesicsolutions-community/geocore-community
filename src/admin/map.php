<?php

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
