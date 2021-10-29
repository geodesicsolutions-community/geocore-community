<?php

//addons/contact_us/tags.php
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
## ##    7.1beta1-1010-gcba993f
##
##################################

# Contact us addon

require_once ADDON_DIR . 'contact_us/info.php';

class addon_contact_us_tags extends addon_contact_us_info
{
    public function contact_form_auto_add_head()
    {
        $secure = geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('messaging')) {
            geoView::getInstance()->addTop($secure->getJs());
        }
    }

    public function contact_form($params, Smarty_Internal_Template $smarty)
    {
        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars = array();
        if ($reg->show_ip) {
            $tpl_vars['ip'] = $ip = $_SERVER['REMOTE_ADDR'];
        }

        $tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);

        $tpl_vars['show_ip'] = $reg->show_ip;

        $secure = geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('messaging')) {
            $security_text = geoAddon::getText('geo_addons', 'security_image');
            $error = $errors['securityCode'];
            $section = "message";
            $tpl_vars['security_image'] = $secure->getHTML($error, $security_text, $section, false);
        }
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'contact_form.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
