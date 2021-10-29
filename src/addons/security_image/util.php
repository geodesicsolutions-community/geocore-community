<?php

//addons/security_image/util.php
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
class addon_security_image_util extends addon_security_image_info
{
    var $db;
    var $recaptchaError;

    public function core_registration_check_info($vars)
    {
        $info = $vars['info'];
        $registerClass = $vars['this'];
        $api = $vars['api'];

        if ($api) {
            //don't check security image when in api call
            return;
        }
        if ($this->check_setting('registration') && !$this->check_security_code($info["securityCode"])) {
            //add error
            $registerClass->error['securityCode'] = "error";
            $registerClass->error_found++;
        }
    }

    // Constructor
    function __construct()
    {
        $this->db = DataAccess::getInstance();
    }
    // Checks the security code with an entered value
    public function check_security_code($enteredCode)
    {
        $reg = geoAddon::getRegistry($this->name);

        if ($reg->imageType == 'recaptcha') {
            //validate against recaptcha

            $params = array ();
            //params needed by recaptcha.. see http://code.google.com/apis/recaptcha/docs/verify.html
            $secret = $reg->recaptcha_private_key;
            $remoteip = $_SERVER["REMOTE_ADDR"];
            $response = $_POST["g-recaptcha-response"];

            $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response&remoteip=$remoteip";
            $check = geoPC::urlGetContents($url);
            $data = json_decode($check, true);

            if ($data['success'] == 'true') {
                return true;
            } else {
                $this->recaptchaError = $data['error-codes'][0];
                return false;
            }
        } else {
            //use built-in checking
            $sql = "select securityString from geodesic_sessions where classified_session = ?";
            $result = $this->db->Execute($sql, array($_COOKIE["classified_session"]));
            $result = $result->FetchRow();
            if (!isset($result['securityString']) || strlen($result['securityString']) == 0 || strtoupper($result["securityString"]) != strtoupper($enteredCode)) {
                //case-insensitive check - did not match, or there is no security string found for this session.
                return false;
            }
            return true;
        }
    }
    // Access function for 'secure_registration'
    function check_setting($section)
    {
        $reg = geoAddon::getRegistry($this->name);
        if ($section == 'messaging' && $reg->login_override == 1 && geoSession::getInstance()->getUserId()) {
            //user has already logged in and override switch is set, so don't show security image
            return false;
        }
        if (defined('IN_ADMIN') && IN_ADMIN) {
            //this is probably the admin cart...no need to show security images there.
            return false;
        }
        return $reg->get('secure_' . $section);
    }

    function getJs()
    {
        $tpl = new geoTemplate('addon', 'security_image');
        $tpl->assign('imageType', geoAddon::getRegistry($this->name)->imageType);
        return $tpl->fetch('js.tpl');
    }
    public function getHTML($error, $text, $section, $include_js = true)
    {
        $reg = geoAddon::getRegistry($this->name);

        if (!$text) {
            $text = geoAddon::getText('geo_addons', 'security_image');
        }

        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);

        $tpl->assign('imageType', $reg->imageType);

        if ($reg->imageType == 'recaptcha') {
            $tpl->assign('recaptcha_theme', $reg->recaptcha_theme);
            $tpl->assign('recaptcha_server', (geoSession::isSSL()) ? self::RECAPTCHA_API_SECURE_SERVER : self::RECAPTCHA_API_SERVER);
            $tpl->assign('recaptcha_pub_key', $reg->recaptcha_pub_key);
            $tpl->assign('recaptcha_error', $this->recaptchaError);
            //needs to know if loaded in ajax
            $tpl->assign('is_ajax', geoAjax::isAjax());
        }

        $js = ($include_js) ? $this->getJs() : '';

        $tpl->assign('error', ($error) ? $text['error'] : '');
        $tpl->assign('label', $text[$section . '_label']);

        //auto vars available to each one
        $reg = geoAddon::getRegistry($this->name);

        $tpl->assign('w', $reg->width);
        $tpl->assign('h', $reg->height);



        $html = $tpl->fetch($section . '.tpl');
        $return = $js . $html;

        return $return;
    }
}
