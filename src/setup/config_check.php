<?php

/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		config.php Checking Module
 *	Filename:	config_check.php
 */

function config_check(&$template, $error = 0)
{
    // Replace (!MAINBODY!) with file template
    $file = file_get_contents("config.html");
    $file = "<form name=version_comp action=" . INSTALL . "?a=config.php_check method=post>\n\t" . $file;
    $template = str_replace("(!MAINBODY!)", $file, $template);

    $errors = 0;

    $template = str_replace("(!CONFIG_LABEL!)", "Please enter the data below about your server.\n", $template);

    $error_text = "";

    /*
    if(strlen($error["file_perm"]) != 0)
        $template = str_replace("(!FILE_PERM_ERROR!)", $error["file_perm"], $template);
    else
        $template = str_replace("(!FILE_PERM_ERROR!)", "", $template);
    */

    $db_host_label = "Please enter your database hostname.";
    $db_host = "<input name=b[db_host] type=text size=55 ";
    if ($error) {
        $db_host .= "value=" . $error["db_host"];
    }
    $db_host .= ">";
    $template = str_replace("(!DB_HOST_LABEL!)", $db_host_label, $template);
    $template = str_replace("(!DB_HOST!)", $db_host, $template);
    if ($error["db_host_error"]) {
        $template = str_replace("(!DB_HOST_ERROR!)", $error["db_host_error"], $template);
    } else {
        $template = str_replace("(!DB_HOST_ERROR!)", "", $template);
    }

    $db_username_label = "Please enter your database username.";
    $db_username = "<input name=b[db_username] type=text size=55 ";
    if ($error["db_username"]) {
        $db_username .= "value=" . $error["db_username"];
    }
    $db_username .= ">";
    $template = str_replace("(!DB_USERNAME_LABEL!)", $db_username_label, $template);
    $template = str_replace("(!DB_USERNAME!)", $db_username, $template);
    if ($error["db_username_error"]) {
        $template = str_replace("(!DB_USERNAME_ERROR!)", $error["db_username_error"], $template);
    } else {
        $template = str_replace("(!DB_USERNAME_ERROR!)", "", $template);
    }

    $db_password_label = "Please enter your database password.";
    $db_password = "<input name=b[db_password] type=password size=55 ";
    if ($error) {
        $db_password .= "value=" . $error["db_password"];
    }
    $db_password .= ">";
    $template = str_replace("(!DB_PASSWORD_LABEL!)", $db_password_label, $template);
    $template = str_replace("(!DB_PASSWORD!)", $db_password, $template);
    if ($error["db_password_error"]) {
        $template = str_replace("(!DB_PASSWORD_ERROR!)", $error["db_password_error"], $template);
    } else {
        $template = str_replace("(!DB_PASSWORD_ERROR!)", "", $template);
    }

    $db_name_label = "Please enter your database name.";
    $db_name = "<input name=b[db_name] type=text size=55 ";
    if ($error) {
        $db_name .= "value=" . $error["db_name"];
    }
    $db_name .= ">";
    $template = str_replace("(!DB_NAME_LABEL!)", $db_name_label, $template);
    $template = str_replace("(!DB_NAME!)", $db_name, $template);
    if ($error["db_name_error"]) {
        $template = str_replace("(!DB_NAME_ERROR!)", $error["db_name_error"], $template);
    } else {
        $template = str_replace("(!DB_NAME_ERROR!)", "", $template);
    }
}

function write_config($config, $product_type)
{
    // Function deprecated till fixed
    return 0;
}
