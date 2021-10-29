<?php

//addons/subscription_pricing/app_top.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################

class addon_subscription_pricing_admin extends addon_subscription_pricing_info
{
    public function init_pages()
    {
        menu_page::addonAddPage('settings', '', 'Settings', $this->name, $this->icon_image);
    }

    public function display_settings()
    {
        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars['require_sub_all_users'] = $reg->require_sub_all_users;
        geoView::getInstance()->setBodyTpl('admin/settings.tpl', $this->name)->setBodyVar($tpl_vars);
    }

    public function update_settings()
    {
        $reg = geoAddon::getRegistry($this->name);
        $reg->require_sub_all_users = $_POST['require_sub_all_users'] == 1 ? 1 : 0;
        $reg->save();
    }
}
