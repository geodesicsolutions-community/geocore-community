<?php

//addons/security_image/setup.php
/**
 * Optional file.  Used to specify custom routines to run in addition to
 * the built-in addon system management.
 *
 * Can be used for the routines Install/Uninstall, Upgrade, and Enable/Disable
 *
 * Remember to rename the class name, replacing "example" with
 * the folder name for your addon.
 *
 * This file has no PHP5 equivalent in php5_files/ dir.
 *
 * @author Geodesic Solutions, LLC
 * @package example_addon
 */

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
 * This class is not required. If it, and the function for a particular
 * routine exist, then that function will be called IN ADDITION TO the
 * automated routines of the addon framework.
 *
 * @author Geodesic Solutions, LLC
 * @version 7.5.3-36-gea36ae7
 * @copyright Copyright (c) 2001-2009 Geodesic Solutions, LLC
 * @package example_addon
 */
class addon_security_image_setup extends addon_security_image_info
{

    public function upgrade($old_version)
    {
        //get $db connection - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $db = $admin = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        if (version_compare($old_version, '3.0.0', '<')) {
            //pre-3.0.0, run the 3.0.0 upgrades

            $admin->userNotice('New image customization features can be found on <a href="?page=security_image_config">Security Image admin page</a>.');
        }

        switch ($old_version) {
            case '3.0.0':
                $admin->userNotice('In this version of the Security Image addon, the HTML code for the images has been moved from Addon Text to new SMARTY templates.<br />
									<em>In most cases, you do not need to do anything as a result of this change.</em><br />
									However, if you require custom HTML for your security images, the templates may be found at addons/security_image/templates/.');
                break;
            default:
                break;
        }
        $this->_updateSettings();

        if (version_compare($old_version, '3.4.5', "<=")) {
            $reg = geoAddon::getRegistry($this->name, true);
            if ($reg->recaptcha_theme !== 'light' && $reg->recaptcha_theme !== 'dark') {
                //new recaptcha hass new theme names. if not one of these, make it light
                $reg->recaptcha_theme = 'light';
                $reg->save();
            }
        }

        return true;
    }
    public function install()
    {
        //script to install a fresh copy.
        //just let the update settings thingy take care of setting all the defaults
        $this->_updateSettings(true);
        return true;
    }

    private function _updateSettings($setup = false)
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $reg = geoAddon::getRegistry('security_image', true);

        //all the default settings that are reset if not set already
        $defaults = array (
            'imageType' => 'system',
            'width' => 125,
            'height' => 50,
            'numChars' => 4,
            'lines' => 3,
            'fontSize' => ((function_exists('imagettftext')) ? 22 : 5),
            'numNoise' => 150,
            'numGrid' => 8,
            'distort' => '.5',
            'refreshUrl' => 'addons/security_image/reload.gif',
            'allowedChars' => '2346789ABCDEFGHJKLMNPRTWXYZ',
            'rmin' => 0,
            'gmin' => 0,
            'bmin' => 0,
            'rmax' => 150,
            'gmax' => 150,
            'bmax' => 150,
            'recaptcha_pub_key' => self::RECAPTCHA_UNSECURE_PUBLIC_KEY,
            'recaptcha_private_key' => self::RECAPTCHA_UNSECURE_PRIVATE_KEY,
            'recaptcha_theme' => 'light',
        );

        //settings set if this is fresh install, these will over-write ones
        //above if there are dups
        $defaultsInstall = array (
            'secure_registration' => 1,
            'secure_login' => 1,
            'secure_messaging' => 1,
            'useRandomColors' => 1,
            'useDistort' => 1,
            'useGrid' => 0,
            'useLines' => 1,
            'useNoise' => 1,

        );
        //translate of new settings from the old ones for updates
        $settingTranslate = array(
            'useLines' => 'secure_use_lines',
            'lines' => 'secure_lines',
            'width' => 'secure_width',
            'height' => 'secure_height',
            'refreshUrl' => 'secure_refresh_url',
            'allowedChars' => 'secure_chars_allowed',
            'fontSize' => 'secure_font_size',
            'distort' => 'secure_distort',
            'useGrid' => 'secure_use_grid',
            'useNoise' => 'secure_use_noise',
            'useDistort' => 'secure_use_distort',
            'useRandomColors' => 'secure_random_colors',
            'useRefresh' => 'secure_use_refresh',
            'useBlur' => 'secure_blur',
            'useEmboss' => 'secure_emboss',
            'useSketchy' => 'secure_sketchy',
            'useNegative' => 'secure_negative',
            'numChars' => 'secure_chars',
            'numGrid' => 'secure_grid',
            'numNoise' => 'secure_noise',
            'rmin' => 'secure_font_color_r_min',
            'rmax' => 'secure_font_color_r_max',
            'gmin' => 'secure_font_color_g_min',
            'gmax' => 'secure_font_color_g_max',
            'bmin' => 'secure_font_color_b_min',
            'bmax' => 'secure_font_color_b_max',
            'secure_registration' => 'secure_registration',
            'secure_login' => 'secure_login',
            'secure_messaging' => 'secure_messaging',
        );

        if (!$setup) {
            //specific to upgrades

            foreach ($settingTranslate as $new => $legacy) {
                $val = $db->get_site_setting($legacy);
                //from reg
                $val = ($val === false && $reg->$val) ? $reg->$val : $val;
                if ($val !== false) {
                    //make it removed from old location
                    $db->set_site_setting($legacy, false);

                    //special case checks..
                    if ($new == 'allowedChars' && strlen($val) <= 3) {
                        //don't transfer it over
                        continue;
                    }
                    if ($new == 'refreshUrl') {
                        //since it's from a different relative location, don't even
                        //try to guess new location, just use default setting
                        continue;
                    }

                    $defaults[$new] = $val;
                }
            }
        } else {
            //it's an install, also set defaults for install
            $defaults = array_merge($defaults, $defaultsInstall);
        }

        //go through each one and set
        foreach ($defaults as $setting => $val) {
            if (!$reg->$setting) {
                $reg->$setting = $val;
            }
        }
        //save any changes
        $reg->save();
    }
}
