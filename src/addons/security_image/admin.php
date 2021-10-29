<?php

//addons/security_image/admin.php
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
## ##    16.09.0-106-ge989d1f
##
##################################

# Security Image Addon

class addon_security_image_admin extends addon_security_image_info
{
    var $admin_site;
    var $error;
    var $error_count;

    var $allowed_settings = array (
        'secure_registration',
        'secure_login',
        'secure_messaging',
        'login_override',
        'secure_listing',
        'secure_listing_anon',
        'secure_forgot_pass',
        'width',
        'height',
        'numChars',
        'fontSize',
        'useRandomColors',
        'rmin',
        'rmax',
        'gmin',
        'gmax',
        'bmin',
        'bmax',
        'allowedChars',
        'useBlur',
        'useEmboss',
        'useSketchy',
        'useNegative',
        'useDistort',
        'distort',
        'useGrid',
        'numGrid',
        'useLines',
        'lines',
        'useNoise',
        'numNoise',
        'useRefresh',
        'refreshUrl'
    );

    //shows abilities
    var $abilities;

    public function __construct()
    {
        //shows abilities
        $abilities = array ();

        $abilities['imagecreatetruecolor'] = function_exists('imagecreatetruecolor');
        $abilities['imagecreate'] = function_exists('imagecreate');
        $abilities['imageline'] = function_exists('imageline'); //for grids and lines
        $abilities['imagesetpixel'] = function_exists('imagesetpixel'); //for noise
        $abilities['imagettftext'] = function_exists('imagettftext'); //fancy fonts
        $abilities['imagestring'] = function_exists('imagestring'); //not fancy fonts
        $abilities['imagepng'] = function_exists('imagepng');
        $abilities['imagegif'] = function_exists('imagegif');
        $abilities['imagejpeg'] = function_exists('imagejpeg');
        $abilities['imagewbmp'] = function_exists('imagewbmp');
        $abilities['imagefilter'] = function_exists('imagefilter');
        $ver_info = (function_exists('gd_info')) ? gd_info() : array();
        preg_match('/(\d)\.(\d)\.(\d)/', $ver_info['GD Version'], $match);
        $abilities['gd_version'] = $match;

        $this->abilities = $abilities;
    }

    function init_pages()
    {
        menu_page::addonAddPage('security_image_config', '', 'Settings', 'security_image', 'fa-shield');
    }

    function init_text($language_id)
    {
        $security_text = array();
        $security_text['registration_label'] = array
        (
            'name' => 'Register label',
            'desc' => 'labels the security image used in register process',
            'type' => 'input',
            'default' => 'Access Code*'
        );

        $security_text['login_box_label'] = array
        (
            'name' => 'Login Box label',
            'desc' => 'labels the security image used on the main page in the login box',
            'type' => 'input',
            'default' => 'Access Code:'
        );
        $security_text['message_label'] = array
        (
            'name' => 'Send Message to Seller label',
            'desc' => 'labels the security image used in contacting a seller',
            'type' => 'input',
            'default' => 'Access Code*'
        );
        $security_text['login_label'] = array
        (
            'name' => 'Login Page label',
            'desc' => 'labels the security image used on the login page',
            'type' => 'input',
            'default' => ''
        );
        $security_text['listing_label'] = array
        (
            'name' => 'Listing Page label',
            'desc' => 'labels the security image used on the listing details page',
            'type' => 'input',
            'default' => 'Access Code*'
        );
        $security_text['listing_anon_label'] = array
        (
            'name' => 'Listing Page (Anonymous) label',
            'desc' => 'labels the security image used on the listing details page when placing a listing anonymously (Requires Anonymous Listing addon)',
            'type' => 'input',
            'default' => 'Access Code*'
        );
        $security_text['forgot_pass_label'] = array
        (
            'name' => 'Forgot Pass Page label',
            'desc' => 'labels the security image used on the forgot password page',
            'type' => 'input',
            'default' => 'Access Code'
        );
        $security_text['error'] = array
        (
            'name' => 'Security Image Error',
            'desc' => 'Message displayed when incorrect code is entered',
            'type' => 'input',
            'default' => '<br>Access Code does not match'
        );
        $security_text['login_html'] = array
        (
            'name' => 'Old Login Page HTML',
            'desc' => 'NOT USED -- shown here so that you may retrieve any customizations from previous versions and move them to the new templates',
            'type' => 'textarea',
            'default' => ''
        );
        $security_text['login_box_html'] = array
        (
            'name' => 'Old Front Page HTML',
            'desc' => 'NOT USED -- shown here so that you may retrieve any customizations from previous versions and move them to the new templates',
            'type' => 'textarea',
            'default' => ''
        );
        $security_text['registration_html'] = array
        (
            'name' => 'Old Registration Page HTML',
            'desc' => 'NOT USED -- shown here so that you may retrieve any customizations from previous versions and move them to the new templates',
            'type' => 'textarea',
            'default' => ''
        );
        $security_text['message_html'] = array
        (
            'name' => 'Old Send Message HTML',
            'desc' => 'NOT USED -- shown here so that you may retrieve any customizations from previous versions and move them to the new templates',
            'type' => 'textarea',
            'default' => ''
        );

        return $security_text;
    }

    public function display_security_image_config()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $reg = geoAddon::getRegistry('security_image');
        $view = geoView::getInstance();

        $tpl_vars = array();

        $tpl_vars['reg'] = $reg;

        $tpl_vars['recaptcha_pub_key'] = $reg->get('recaptcha_pub_key', self::RECAPTCHA_UNSECURE_PUBLIC_KEY);
        if ($tpl_vars['recaptcha_pub_key'] == self::RECAPTCHA_UNSECURE_PUBLIC_KEY && $reg->imageType == 'recaptcha') {
            //warn user
            $admin->userError("SECURITY WARNING:  You are still using the un-secure default reCAPTCHA keys!  To ensure best security, sign up for reCAPTCHA API keys, and enter the keys in the settings below.");
        }

        $tpl_vars['adminMsg'] = geoAdmin::m();
        $tpl_vars['abilities'] = $this->abilities;
        $tpl_vars['recaptcha_server'] = (geoSession::isSSL()) ? self::RECAPTCHA_API_SECURE_SERVER : self::RECAPTCHA_API_SERVER;
        $tpl_vars['recaptcha_theme'] = $reg->recaptcha_theme;

        $tpl_vars['fonts'] = $this->getFonts();
        $tpl_vars['fonts_dir'] = ADDON_DIR . 'security_image/fonts/';
        $tpl_vars['error'] = $this->error;

        $tpl_vars['anonEnabled'] = geoAddon::getInstance()->isEnabled('anonymous_listing');

        $view->setBodyVar($tpl_vars)
            ->setBodyTpl('admin/settings.tpl', 'security_image')
            ->addJScript(array('../addons/security_image/admin.js', '../addons/security_image/presets.js'))
            ->addCssFile('../addons/security_image/admin.css');

        return true;
    }

    public function update_security_image_config()
    {
        $reg = geoAddon::getRegistry($this->name);

        $settings = (array)$_POST['security_image'];

        $validTypes = array ('system','recaptcha');

        $imageType = (in_array($settings['imageType'], $validTypes)) ? $settings['imageType'] : 'system';

        $reg->imageType = $imageType;

        if ($imageType == 'recaptcha') {
            //recaptcha image, settings are easy
            $reg->secure_registration = (isset($settings['secure_registration']) && $settings['secure_registration']) ? 1 : false;
            $reg->secure_login = (isset($settings['secure_login']) && $settings['secure_login']) ? 1 : false;
            $reg->login_override = (isset($settings['login_override']) && $settings['login_override']) ? 1 : false;
            $reg->secure_messaging = (isset($settings['secure_messaging']) && $settings['secure_messaging']) ? 1 : false;
            $reg->secure_listing = (isset($settings['secure_listing']) && $settings['secure_listing']) ? 1 : false;
            $reg->secure_listing_anon = (isset($settings['secure_listing_anon']) && $settings['secure_listing_anon']) ? 1 : false;
            $reg->secure_forgot_pass = (isset($settings['secure_forgot_pass']) && $settings['secure_forgot_pass']) ? 1 : false;

            $validThemes = array ('light','dark');

            $reg->recaptcha_theme = (isset($settings['recaptcha_theme']) && in_array($settings['recaptcha_theme'], $validThemes)) ? $settings['recaptcha_theme'] : 'light';

            $recaptcha_pub_key = trim($settings['recaptcha_pub_key']);

            $keylen_error = "Did you make sure to copy the entire key, with all hyphens and underscores, but without any spaces?";

            if (!$recaptcha_pub_key) {
                $this->error['recaptcha_pub_key'] = 'Invalid public key entered.';
            } elseif (strlen($recaptcha_pub_key) !== 40) {
                $this->error['recaptcha_pub_key'] = 'Invalid public key entered, the key was not the correct length. ' . $keylen_error;
            } else {
                $reg->recaptcha_pub_key = $recaptcha_pub_key;
            }

            $recaptcha_private_key = trim($settings['recaptcha_private_key']);
            if ($recaptcha_private_key != 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx') {
                if (!$recaptcha_private_key) {
                    $this->error['recaptcha_private_key'] = 'Invalid private key entered.';
                } elseif (strlen($recaptcha_private_key) !== 40) {
                    $this->error['recaptcha_private_key'] = 'Invalid private key entered, the key was not the correct length.  ' . $keylen_error;
                } else {
                    $reg->recaptcha_private_key = $recaptcha_private_key;
                }
            }
        } else {
            //system image settings
            if (!$this->check_vars($settings)) {
                return false;
            }

            foreach ($settings as $key => $value) {
                if (in_array($key, $this->allowed_settings)) {
                    $reg->set($key, $value);
                }
            }
        }
        $reg->save();
        return true;
    }

    function check_vars(&$vars)
    {
        $this->error_count = 0;
        $orig_chars_allowed = $vars['allowedChars'];
        //remove white space
        $vars['allowedChars'] = preg_replace('/[\s]+/', '', $vars['allowedChars']);

        //sort, so it's easy to see if certain chars exist and see duplicates
        $chars = $chars = preg_split('//', $vars['allowedChars'], -1, PREG_SPLIT_NO_EMPTY);
        sort($chars, SORT_STRING);
        $vars['allowedChars'] = implode($chars);

        if (strlen($vars['allowedChars']) < 5) {
            //error with value entered
            $this->error['allowedChars'] = "<span class='small_font_error' id='error_allowedChars'>Must enter at least 5 valid characters.</span>";
            $this->error_count++;
        } elseif (strlen($vars['allowedChars']) != strlen($orig_chars_allowed)) {
            //some of the chars were invalid, so removed invalid chars
            $this->error['allowedChars'] = "<span class='small_font_error' id='error_allowedChars'>Invalid Characters were automatically removed.</span>";
        }
        if (($vars["height"] < 20 || $vars["height"] > 200) || !is_numeric($vars["height"])) {
            $this->error['height'] = "<span class='small_font_error' id='error_height'>Value must be between 20 and 200</span>";
            $this->error_count++;
        }
        if (($vars["width"] < 35 || $vars["width"] > 1000) || !is_numeric($vars["width"])) {
            $this->error['width'] = "<span class='small_font_error' id='error_width'>Value must be between 35 and 1000</span>";
            $this->error_count++;
        }
        if (($vars["numChars"] < 1 || $vars["numChars"] > 20) || !is_numeric($vars["numChars"])) {
            $this->error['numChars'] = "<span class='small_font_error' id='error_numChars'>Value must be between 1 and 20</span>";
            $this->error_count++;
        }
        if ($vars["fontSize"] < 1 || ($this->abilities['imagettftext'] && $vars["fontSize"] > 500) || (!$this->abilities['imagettftext'] && $vars['fontSize'] > 5) || !is_numeric($vars["fontSize"])) {
            $max = ($this->abilities['imagettftext']) ? '500' : '5';
            $this->error['fontSize'] = "<span class='small_font_error' id='error_fontSize'>Value must be between 1 and $max</span>";
            $this->error_count++;
        }
        if (($vars["lines"] < 1 || $vars["lines"] > 20) || !is_numeric($vars["lines"])) {
            $this->error['lines'] = "<span class='small_font_error' id='error_lines'>Value must be between 1 and 20</span>";
            $this->error_count++;
        }
        if (($vars["numGrid"] < 1 || $vars["numGrid"] > 60) || !is_numeric($vars["numGrid"])) {
            $this->error['numGrid'] = "<span class='small_font_error' id='error_numGrid'>Value must be between 1 and 60</span>";
            $this->error_count++;
        }
        $max_noise = floor(($vars['width'] * $vars['height']) / 1.5);
        if (($vars["numNoise"] < 50 || $vars["numNoise"] > $max_noise) || !is_numeric($vars["numNoise"])) {
            $this->error['numNoise'] = "<span class='small_font_error' id='error_numNoise'>Value must be between 50 and $max_noise (max depends on image width and height)</span>";
            $this->error_count++;
        }
        if (($vars["distort"] < 0 || $vars["distort"] > 1.0) || !is_numeric($vars["distort"])) {
            $this->error['distort'] = "<span class='small_font_error' id='error_distort'>Value must be between 0.0 and 1.0</span>";
            $this->error_count++;
        }
        $vars['refreshUrl'] = trim($vars['refreshUrl']);
        if ($vars['useRefresh'] && !( strlen($vars['refreshUrl']) > 2 && file_exists(GEO_BASE_DIR . $vars['refreshUrl']))) {
            $this->error['refreshUrl'] = "<span class='small_font_error' id='error_refreshUrl'>Must be valid file, relative to the base installation (same directory as index.php).</span>";
            $this->error_count++;
        }

        //check rgb
        if (
            !is_numeric($vars['rmin']) || !is_numeric($vars['rmax']) ||
            !is_numeric($vars['gmin']) || !is_numeric($vars['gmax']) ||
            !is_numeric($vars['bmin']) || !is_numeric($vars['bmax'])
        ) {
            //one of the min/max values is not numeric
            $this->error['secure_font_color'] = "<span class='small_font_error' id='error_secure_font_color'>Invalid entries, expecting only <strong>numbers</strong> 0-255 for min and max values.</span>";
            $this->error_count++;
        } elseif (
            $vars['rmin'] < 0 || $vars['rmin'] > $vars['rmax'] || $vars['rmax'] > 255 ||
            $vars['gmin'] < 0 || $vars['gmin'] > $vars['gmax'] || $vars['gmax'] > 255 ||
            $vars['bmin'] < 0 || $vars['bmin'] > $vars['bmax'] || $vars['bmax'] > 255
        ) {
            $this->error['secure_font_color'] = "<span class='small_font_error' id='error_secure_font_color'>Error: Invalid color range specified.  Make sure first value is less or equal to second value for each color, and that they do not exceed the range of 0-255.</span>";
            $this->error_count++;
        }
        if ($this->error_count == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getFonts()
    {
        $fonts = array();
        if ($handle = opendir(ADDON_DIR . 'security_image/fonts/')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && stristr($file, '.ttf')) {
                    $fonts[] = $file;
                }
            }
            closedir($handle);
        }
        return $fonts;
    }
}
