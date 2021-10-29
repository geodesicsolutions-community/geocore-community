<?php

/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		Login Module
 *	Filename:	login.php
 */


function login($db, $product, &$template, $errors = 0)
{
    $sql_query = "select * from " . $product['logins_table'] . " where username = 'admin'";
    $result = $db->Execute($sql_query);
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>';
        exit;
    } else {
        $passwords = $result->FetchNextObject();
    }

    $file = file_get_contents("login.html");

    $file = "<form name=save action=" . INSTALL . "?a=login_save method=post>" . $file . "</form>";
    $template = str_replace("(!MAINBODY!)", $file, $template);
    $template = str_replace("(!BACK!)", "<input type=button name=back value=\"<< Back\" onClick=\"history.go(-1)\">", $template);

    $template = str_replace(
        "(!INTRO!)",
        "<b>Admin Login Information:</b><br><br>\n" .
                            "Please enter a password, write it down, and click the \"Save\" button.",
        $template
    );

    // Password
    $template = str_replace("(!PASSWORD_LABEL!)", "Admin Password:", $template);
    /*  Uncomment this if we start requiring alphanumeric passwords
    if($errors)
    {
        echo "<td><input type=text name=pass size=15 value=".decrypt($passwords->PASSWORD)."><br>";
        echo "Please enter an alphanumeric password only.  We require this for the security of your site.<br></td>\n\t</tr>\n\t";
    }
    else*/
    $template = str_replace("(!PASSWORD_FIELD!)", "<input type=text name=pass size=15>", $template);
    $template = str_replace("(!SUBMIT!)", "<div id='submit_button'><input type='submit' class='theButton' value='Save'></div>", $template);

    return 0;
}

function login_save($db, $product, $pass)
{
    if ($product['encrypt']) {
        $sql_query = "update " . $product['logins_table'] . " set password = \"" . encrypt_password($db, $pass, $product) . "\" where username = \"admin\"";
    } else {
        $sql_query = "update " . $product['logins_table'] . " set password = \"" . $pass . "\" where username = \"admin\"";
    }
    $result = $db->Execute($sql_query);
    if (!$result) {
        echo "Error executing query: " . $sql_query . '<br>';
        exit;
    }

    return 0;
}

// This is the get round initialization vector for
// the encrypt_password function
function get_iv($key, $iv_len)
{
    $iv = '';
    for ($i = 0; $i < $iv_len; $i++) {
        $iv .= chr($key[i] & 0xff);
    }

    return $iv;
}


function encrypt_password($db, $plain_text, $product, $iv_len = 32)
{
    // Get the key from the database
    $sql_query = "select password_key from " . $product['config_table'];
    $result = $db->Execute($sql_query);
    $key_result = $result->FetchNextObject();
    $key = $key_result->PASSWORD_KEY;

    // MD5 based block cypher with a resemblance to MDC.  It works in 128-bit CFB mode (whatever that means)...
    // Note: iv = initialization vector and its length must be between 0 and 512.
    $plain_text .= "\x13";
    $n = strlen($plain_text);
    if ($n % 16) {
        $plain_text .= str_repeat("\0", 16 - ($n % 16));
    }
    $i = 0;
    $enc_text = get_iv($key, $iv_len);
    $iv = substr($key ^ $enc_text, 0, 512);

    while ($i < $n) {
        $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
        $enc_text .= $block;
        $iv = substr($block . $iv, 0, 512) ^ $key;
        $i += 16;
    }

    //echo strlen(base64_encode($enc_text)) . ' is the length<br>';

    return base64_encode($enc_text);
}
