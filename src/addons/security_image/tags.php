<?php

//addons/security_image/tags.php
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
## ##    7.5.3-36-gea36ae7
##
##################################

# Security Image Addon
require_once ADDON_DIR . 'security_image/info.php';
/**
 * Expects one function for each tag.  Function name should be the same as
 * the tag name.  Can also have a constructor if anything needs to be constructed.
 *
 */
class addon_security_image_tags extends addon_security_image_info
{
    public $text;

    public function __construct()
    {
        $this->text = geoAddon::getText('geo_addons', 'security_image');
    }

    public function secure_image($params, Smarty_Internal_Template $smarty)
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $reg = geoAddon::getRegistry($this->name);
        $self = geoAddon::getUtil($this->name);
        if (!$self->check_setting('login')) {
            //don't use for login
            return '';
        }
        $tpl_vars = array ();

        $tpl_vars['w'] = $reg->width;
        $tpl_vars['h'] = $reg->height;
        $tpl_vars['label'] = $this->text['login_box_label'];
        $tpl_vars['imageType'] = $reg->imageType;

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'login_box.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
