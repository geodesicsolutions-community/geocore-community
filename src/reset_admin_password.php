<?php

# -------- ADMIN PASSWORD RESET TOOL -------- #

/**
In the event that the admin password is lost, the
  only option is to reset the password using this tool.
  It is not possible to retrieve the current admin password.
  To reset, follow these steps carefully:

1.  Edit the settings in this file according to the instructions
    provided for each setting.
2.  Upload the file with the changed settings.
3.  Using an Internet browser, go to the admin page, and click on the
    link "Reset Admin Password".
4.  Change the first setting on this file back to 0 to turn the tool off.
5.  Upload the file with the setting turned off.
      Note:  For security, you will not be able to log into admin while
             this tool is turned on.  This is so that you do not accidently
             leave the tool turned on.

*/

#-------------SETTINGS--------------#

# TURN ON/OFF THE PASSWORD RESET TOOL
#  set to 1 to turn tool on, 0 to turn off.
define('TURN_ON_RESET_PASSWORD_TOOL', 0);

# ADMIN USERNAME
#  Set this to the desired admin username.  This is what you
#  will use to log into admin after the password is reset.
$admin_username = "admin";

# ADMIN PASSWORD
#  Set this to the desired admin password.  This is what you
#  will use to log into the admin after the password is reset.
$admin_password = "geodesic";

#-----------END OF SETTINGS--------------#

#--------DO NOT EDIT BELOW THIS LINE!-------#

function reset_admin_pass($user, $pass)
{
    if (!(defined('TURN_ON_RESET_PASSWORD_TOOL') && TURN_ON_RESET_PASSWORD_TOOL)) {
        //do not process if tool is not turned on.
        exit;
    }
    if (strlen($user) == 0 || strlen($pass) == 0) {
        //do not process if username or password not set correctly.
        die('ERROR: CHECK SETTINGS');
    }

    define('IN_ADMIN', 1);
    include "app_top.common.php";

    $product_configuration = geoPC::getInstance();

    //Just to be on safe side, store pass in plain text, it will be switched
    //the first time the admin logs in afterwards
    $hash_type = 'core:plain';//$db->get_site_setting('admin_pass_hash');
    $salt = '';
    $hashed_pass = $product_configuration->get_hashed_password($user, $pass, $hash_type);

    if (is_array($hashed_pass)) {
        $salt = '' . $hashed_pass['salt'];
        $hashed_pass = '' . $hashed_pass['password'];
    }

    //make sure that username is not already taken.
    $sql = 'SELECT * FROM ' . $db->geoTables->logins_table . ' WHERE username = ? AND id != 1';
    $result = $db->Execute($sql, array($user));
    if (!$result) {
        die('ERROR: DATABASE ERROR');
    }
    if ($result->RecordCount() > 0) {
        die('ERROR: USERNAME ALREADY IN USE');
    }

    //insert into logins table.
    $sql = 'UPDATE ' . geoTables::logins_table . ' SET `username` = ?, `password` = ?, `hash_type`=?, `salt`=? WHERE `id` = 1';
    $result = $db->Execute($sql, array($user, $hashed_pass, $hash_type, $salt));

    //update userdata table
    $sql = "UPDATE " . geoTables::userdata_table . " SET `username`=? WHERE id=1";
    $result = $db->Execute($sql, array($user));

    //make sure to remove all admin sessions:
    $sql = 'DELETE FROM ' . $db->geoTables->session_table . ' WHERE `user_id` = 1';
    $result = $db->Execute($sql);
    if (!$result) {
        die('ERROR: DATABASE ERROR: REMOVING ADMIN SESSIONS<br>Query: ' . $sql . '<br>Error: ' . $db->ErrorMsg());
    }
    ?>
<html>
<head>
<title>Admin Password Reset Tool</title>
<style type="text/css">
<!--
div.code {
    border: thin dashed gray;
    padding: 5px;
    margin-bottom: 15px;
    float:left;
    clear:both;
}
li, div {
    clear:both;
}
-->
</style>
</head>
<body>
<h1 style="color:green;">Admin Password Reset Tool - Password Successfully Reset</h1>
<p>The admin username and password have been reset according to the settings in the file <strong>reset_admin_password.php</strong>.<br /><br />
Admin login will be disabled until you turn the tool back off:</p>
<ol>
<li>Edit the file <strong>reset_admin_password.php</strong> and find the lines that look similar to this:
<div class="code">
# TURN ON/OFF THE PASSWORD RESET TOOL<br />
#  set to 1 to turn tool on, 0 to turn off.<br />
define('TURN_ON_RESET_PASSWORD_TOOL', <strong style="color:red;">1</strong>);<br />
</div>
<div>And <strong style="color:red;">change the 1 to a 0</strong>, so that it looks like this:</div>
<div class="code">
# TURN ON/OFF THE PASSWORD RESET TOOL<br />
#  set to 1 to turn tool on, 0 to turn off.<br />
define('TURN_ON_RESET_PASSWORD_TOOL', <strong style="color:red;">0</strong>);<br />
</div></li>
<li>Upload the file with the changes.</li>
<li>Log into the admin, using the username and password that were set in the <strong>reset_admin_password.php</strong> file.  If it still does not give you the form to log in, that means the tool is still "turned on".
</li>
</ol>
</body>
</html>
    <?php
}

if (defined('TURN_ON_RESET_PASSWORD_TOOL') && TURN_ON_RESET_PASSWORD_TOOL && isset($_GET['reset_password']) && $_GET['reset_password'] == sha1('reset_the_pass_now')) {
    //run the function.
    reset_admin_pass($admin_username, $admin_password);
}
?>