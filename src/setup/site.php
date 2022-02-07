<?php

/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		Site Module
 *	Filename:	site.php
 */

function site($db, $product, &$template, $error = 0)
{
    // Get the define file and put the data in here
    require('product.php');

    // Site url, root file, email config, admin email
    $sql_query = "select * from " . $product['config_table'];
    $result = $db->Execute($sql_query);
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>';
        return 1;
    } else {
        $conf = $result->FetchRow();
    }

    $file = file_get_contents("site.html");
    $file = "<form name=url_data action=index.php?a=site_save method=post>\n\t" .
            $file .
            "</form>";

    $template = str_replace("(!MAINBODY!)", $file, $template);
    $template = str_replace("(!BACK!)", "<input type=button name=back value=\"<< Back\" onClick=\"history.go(-1)\">", $template);

    $template = str_replace(
        "(!INTRO!)",
        "<br>Now we will set up the Site Configuration data for your site. These fields can be changed later through your software's Admin Control Panel.\n" .
                            "The Wizard has prepopulated the fields below for you based upon information gathered thus far in the installation process. Please verify that all paths below are correct and make any necessary adjustments.<br><br>
							Unless instructed otherwise by your host, ensure that all URL Fields use this format: <br><br><strong>http://www.yoursitename.com</strong><br><br>\n",
        $template
    );

    $url_path = str_replace("setup/index.php", "", $_SERVER["PHP_SELF"]);

    // Application URL
    $template = str_replace("(!APP_URL_LABEL!)", "Application URL:", $template);

    $string = '';
    if ($error['url']) {
        $string .= '<input name="conf[url]" type="text" size="70">';
    } else {
        $url = htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . $url_path . 'index.php', ENT_QUOTES, 'UTF-8');
        $string .= '<input name="conf[url]" type="text" size="70" value="' . $url . '">';
    }
    if ($error['url']) {
        $error_string .= "<br>Please enter a valid URL.";
    }
    $template = str_replace("(!APP_URL_FIELD!)", $string, $template);
    $template = str_replace("(!APP_URL_ERROR!)", $error_string, $template);

    // Application file name
    $template = str_replace("(!APP_FILENAME_LABEL!)", "Application Filename:", $template);
    $string = "<input name=conf[filename] type=text size=70 value=\"";
    if ($error['filename']) {
        $string .= "\">";
    } elseif (is_array($product['filename'])) {
    // && ($conf[$product['filename'][0]] || $conf[$product['filename'][1]]))
    //      || $conf[$product['filename']])
        if ($conf[$product['filename'][0]]) {
            $string .= $conf[$product['filename'][0]] . "\">";
        } else {
            $string .= "index.php\">";
        }
    } elseif ($conf[$product['filename']]) {
        $string .= $conf[$product['filename']] . "\">";
    } else {
        $string .= "index.php\">";
    }
    if ($error['filename']) {
        $error_string = "Please enter a valid URL.";
    }
    $template = str_replace("(!APP_FILENAME_FIELD!)", $string, $template);
    $template = str_replace("(!APP_FILENAME_ERROR!)", $error_string, $template);


    // Email
    $template = str_replace("(!EMAIL_LABEL!)", ''/*"Email Configuration:"*/, $template);
    $string = "<input type=radio name=conf[email_config] value=1 ";
    if ($conf[$product['email_config']] == 1 || !$conf[$product['email_config']]) {
        $string .= "checked";
    }
    $string .= "> 1 - (recommended) allows the setting of additional headers (reply-to,...etc)\"";
    $string = '';
    $template = str_replace("(!EMAIL_SETTING_1!)", $string, $template);
    $string = "<input type=radio name=conf[email_config] value=2 ";
    if ($conf[$product['email_config']] == 2) {
        $string .= "checked";
    }
    $string .= "> 2 - allows only \"from\" header to be set\"";
    $string = '';
    $template = str_replace("(!EMAIL_SETTING_2!)", $string, $template);
    $string = "<input type=radio name=conf[email_config] value=3 ";
    if ($conf[$product['email_config']] == 3) {
        $string .= "checked";
    }
    $string .= "> 3 - allows no headers or \"from\" to be set (for Yahoo hosting the lead email on the account is used as the return email address)\"";
    $string = '';
    $template = str_replace("(!EMAIL_SETTING_3!)", $string, $template);
    if ($error['email_config']) {
        str_replace("(!EMAIL_ERROR!)", "Please select one value from above.", $template);
    }

    // Admin email
    $template = str_replace("(!ADMIN_EMAIL_LABEL!)", "Admin E-mail address:", $template);
    $string = "<input name=\"conf[admin_registration_email]\" type=\"text\" size=\"70\" ";
    if (!$error['admin_registration_email']) {
        if (!$install[$product_type]["admin_registration_email"]) {
            $string .= "value=\"geoproducts@mygeoproducts.com\"";
        } else {
            $string .= "value=\"" . $install[$product_type]["admin_registration_email"] . '"';
        }
    }
    $string .= ">";
    if ($error['admin_registration_email']) {
        $error_string = "Please enter a valid email address.";
    }
    $template = str_replace("(!ADMIN_EMAIL_FIELD!)", $string, $template);
    $template = str_replace("(!ADMIN_EMAIL_ERROR!)", $error_string, $template);

    // Submit button
    $template = str_replace("(!SUBMIT!)", "<div id='submit_button'><input type='submit' class='theButton' value='Save'></div>", $template);

    // Take out excess tags that werent used
    // already done at bottom of index.
    //$template = preg_replace("|\(![a-zA-Z_]+!\)|", "", $template);

    return 0;
}

function site_save($db, $conf, $product)
{
    // Get the define file and put the data in here
    include('product.php');
    $save_in_new_site_settings = array(
    'site_email',
    'registration_admin_email',
    'admin_email_bcc',
    );
    $errors = 0;

    if (strlen(trim($conf['url'])) == 0) {
        $error_code['url'] = true;
        $errors++;
    }

    if (strlen(trim($conf['filename'])) == 0) {
        $error_code['filename'] = true;
        $errors++;
    }
    if (strlen(trim($conf['admin_registration_email'])) == 0) {
        $error_code['admin_registration_email'] = true;
        $errors++;
    }

    if ($errors > 0) {
        return $error_code;
    }

    $query = array();
    $new_q = array();
    foreach ($conf as $key => $value) {
        if (is_array($product[$key])) {
            foreach ($product[$key] as $second => $new_value) {
                if ($product[$key]) {
                    if (in_array(strtolower($new_value), $save_in_new_site_settings)) {
                        $new_q[] = "('" . strtolower($new_value) . "', '{$conf[$key]}' )";
                    } else {
                        $query[] = $new_value . " = '" . $conf[$key] . "', ";
                    }
                }
            }
        } else {
            if ($product[$key]) {
                if (in_array(strtolower($product[$key]), $save_in_new_site_settings)) {
                    $new_q[] = "('" . strtolower($product[$key]) . "', '{$conf[$key]}' )";
                } else {
                    $query[] = $product[$key] . " = '" . $conf[$key] . "', ";
                }
            }
        }
    }
    if (count($new_q)) {
        $sql = 'REPLACE INTO `geodesic_site_settings` (`setting`, `value`) VALUES ' . implode(', ', $new_q);
        $result = $db->Execute($sql);
        if (!$result) {
            echo 'Error executing query: ' . $sql . '<br />' . $db->ErrorMsg();
            exit;
        }
    }
    if (count($query)) {
        $sql_query = "update `" . $product['config_table'] . "` set ";
        foreach ($query as $value) {
            $sql_query .= $value;
        }
        $sql_query = rtrim($sql_query, " ,");
        //echo $sql_query.'<br>';
        $result = $db->Execute($sql_query);
        if (!$result) {
            echo "Error executing query: " . $sql_query . '<br>';
            exit;
        }
    }

    $sql_query = "update " . $product['userdata'] . " set " . $product['admin_email'] . " = '" . $conf['admin_registration_email'] . "'";
    //echo $sql_query.'<Br>';
    $result = $db->Execute($sql_query);
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>' . $db->ErrorMsg();
        exit;
    } else {
        return 0;
    }
}
