<?php

//addons/bridge/util.php
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
## ##    6.0.7-2-gc953682
##
##################################

# Bridge

class addon_bridge_util
{
    private $installs;
    public $settings = array();
    public $install_info;
    public $ignore_critical_logs = false;

    public function initInstalls()
    {
        if (isset($this->installs) && is_array($this->installs)) {
            return true;
        }
        $this->installs = array();
        $db = DataAccess::getInstance();
        $sql = 'SELECT * FROM `geodesic_bridge_installations` WHERE `active`= 1';
        $result = $db->Execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $result->FetchRow()) {
            $filename = ADDON_DIR . 'bridge/bridges/' . $row['type'] . '.php';
            if (!file_exists($filename)) {
                continue;
            }
            $classname = 'bridge_' . $row['type'];
            require_once($filename);
            if (class_exists($classname)) {
                $this->installs[$row['id']] = new $classname(); //need to NOT use singleton...
                $settings = unserialize($row['settings']);
                if (method_exists($this->installs[$row['id']], 'setSettings')) {
                    $this->installs[$row['id']]->setSettings($settings);
                    $this->installs[$row['id']]->install_info = $row; //for logging
                }
            }
        }
    }

    public function getInstall($install_id)
    {
        $this->initInstalls();
        if (isset($this->installs[$install_id])) {
            return $this->installs[$install_id];
        }
        return false;
    }

    public function core_session_create($var)
    {
        $this->initInstalls();
        $keys = array_keys($this->installs);
        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'session_create')) {
                $this->installs[$key]->session_create($var);
            }
        }
    }

    public function core_session_touch($var)
    {
        $this->initInstalls();
        $keys = array_keys($this->installs);
        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'session_touch')) {
                $this->installs[$key]->session_touch($var);
            }
        }
    }

    public function core_session_login($var)
    {
        if (isset($var['userid']) && $var['userid'] == 1) {
            //main admin user, we don't mess with that
            return;
        }

        $this->initInstalls();
        $keys = array_keys($this->installs);

        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'session_login')) {
                $this->installs[$key]->session_login($var);
            }
        }
    }

    public function core_session_logout($var)
    {
        if (isset($var['userid']) && $var['userid'] == 1) {
            //main admin user, we don't mess with that
            return;
        }
        $this->initInstalls();
        $keys = array_keys($this->installs);
        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'session_logout')) {
                $this->installs[$key]->session_logout($var);
            }
        }
    }

    public function core_user_edit($var)
    {
        $this->initInstalls();
        $keys = array_keys($this->installs);
        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'user_edit')) {
                $this->installs[$key]->user_edit($var);
            }
        }
    }

    public function core_user_register($var)
    {
        $this->initInstalls();
        $keys = array_keys($this->installs);
        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'user_register')) {
                $this->installs[$key]->user_register($var);
            }
        }
    }

    public function core_user_remove($var)
    {
        $this->initInstalls();
        $keys = array_keys($this->installs);
        foreach ($keys as $key) {
            if (method_exists($this->installs[$key], 'user_remove')) {
                $this->installs[$key]->user_remove($var);
            }
        }
    }


    //function to log stuff
    public function log($message, $critical = false)
    {
        //When logging, can use the current bridge info, stored in $this->install_info.
        if ($critical && !$this->ignore_critical_logs && isset($this->install_info['email']) && strlen(trim($this->install_info['email'])) > 0) {
            //This is critical log message, ignore critical logs is turned off, the e-mail is set for this installation,
            //so e-mail the log message.
            $to = $this->install_info['email'];
            $subject = 'CRITICAL BRIDGE ERROR';
            $content = "
			An error occured in the Bridge Addon that may need your attention.  Read below for more information.<br />
			<h2>Bridge Installation</h2>
			{$this->install_info['name']} ({$this->install_info['id']})
			<h2>Bridge Type</h2>
			{$this->install_info['type']}
			<h2>Error Message</h2>
			<pre>$message</pre><br /><br />Sent by Bridge Addon.";

            geoEmail::sendMail($to, $subject, $content, 0, 0, 0, 'text/html');
        }

        //TODO:  Add script to log messages so they can be read in the admin
    }
}
