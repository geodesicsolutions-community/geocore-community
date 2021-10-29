<?php

/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		Registration Module
 *	Filename:	registration.php
 */


function registration($db, $product, &$template, $errors = 0, $config = 0)
{
    //$sql_query = "select * from ".$product['config_table'];
    //$result = $db->Execute($sql_query);
    //if(!$result)
    //  return false;
    //else
    //  $config_result = $result->FetchNextObject();
    $sql = 'select * from `geodesic_site_settings`';
    $result2 = $db->Execute($sql);
    if (!$result2) {
        return false;
    }
    while ($row = $result2->FetchRow()) {
        $config_result2[$row['setting']] = $row['value'];
    }

    $file = file_get_contents("registration.html");
    $file = "<form name=url_data action=" . INSTALL . "?a=registration_save method=post>\n\t" .
            $file .
            "</form>";

    $url_path = str_replace("setup/" . INSTALL, "", $_SERVER["PHP_SELF"]);

    $template = str_replace("(!MAINBODY!)", $file, $template);
    $template = str_replace("(!BACK!)", "<input type=button name=back value=\"<< Back\" onClick=\"history.go(-1)\">", $template);

    // Registration email address
    $template = str_replace("(!REGISTRATION_EMAIL_LABEL!)", "<b>Registration email address of admin:</b>", $template);
    $template = str_replace("(!REGISTRATION_EMAIL_DESCRIPTION!)", "Email address that will receive registration confirmation and success messages sent to admin.", $template);
    $string = "<input name=b[admin_registration_email] ";
    //if($config['admin_registration_email'])
    //  $string .= "value = \"".$config['admin_registration_email']."\"";
    //else
        $string .= "value = \"" . $config_result2['registration_admin_email'] . "\"";
    $string .= "type=text size=50>";
    $template = str_replace("(!REGISTRATION_EMAIL_FIELD!)", $string, $template);

    // Site email address
    $template = str_replace("(!SITE_EMAIL_LABEL!)", "<b>Site email address:</b>", $template);
    $template = str_replace("(!SITE_EMAIL_DESCRIPTION!)", "Email address that will be used to send out all emails from the server.", $template);
    $string = "<input name=b[site_email] ";
    if ($config['site_email']) {
        $string .= "value = \"" . $config['site_email'] . "\"";
    } else {
        $string .= "value = \"" . $config_result2['registration_admin_email'] . "\"";
    }
    $string .= "type=text size=50>";
    $template = str_replace("(!SITE_EMAIL_FIELD!)", $string, $template);

    // URL of register.php
    $template = str_replace("(!REGISTER_URL_LABEL!)", "<b>URL of register.php file:</b>", $template);
    $template = str_replace("(!REGISTER_URL_DESCRIPTION!)", "The register.php can be placed anywhere you like within your software's distribution files.", $template);
    $string = "<input name=b[url_register] ";
    $string .= "value=\"http://" . $_SERVER["SERVER_NAME"] . $url_path . "register.php\" ";
    $string .= "type=text size=65>";
    if ($errors == 1) {
        echo "Please enter a valid filename or none.  If you feel this is in error please leave it blank and set it in the admin after install in the registration configuration section.";
    }
    $template = str_replace("(!REGISTER_URL_FIELD!)", $string, $template);

    $template = str_replace("(!SAVE!)", "<div id='submit_button'><input type='submit' class='theButton' value='Save'></div>", $template);
}

function registration_save($db, $config, $product)
{
    $sql = 'REPLACE INTO `geodesic_site_settings` (`setting`,`value`) VALUES (\'site_email\', \'' . $config['site_email'] . '\'), ( \'registration_admin_email\', \'' . $config['admin_registration_email'] . '\')';
    $result = $db->Execute($sql);
    if (!$result) {
        echo 'Error executing query: ' . $sql . '<br />' . $db->ErrorMsg();
        exit;
    }

    $sql_query = "update " . $product['config_table'] . " set " . $product['url_register'] . " = \"" . $config['url_register'] . "\"";
    //echo $sql_query.'<br>';
    $result = $db->Execute($sql_query);
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>';
        exit;
    }

    // Update admin's email
    //$sql_query = "update ".$product['config_table']." set registration_admin_email = \"".$config['site_email']."\"";
    //$result = $db->Execute($sql_query);
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>';
        exit;
    }

    return 0;
}
