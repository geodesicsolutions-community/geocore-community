<?php
//addons/debugger_log/logme.php
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
//turn on logging for this user.

//For use by Geo Support or advanced users
//To turn this file on, set the following var to true.
$enable_logme = false;

//This file can be used to turn on the debug_log cookie,
//and set it to a unique value, to make things like session
//handling easy.

//Instruct users that have problems with logging in, or are constantly
//logged out, to use this file to turn logging on.  Access it by going
//directly to: addons/debugger_log/logme.php


if (!$enable_logme || !@is_writeable('log.php')){
	//message that shows if the file is not writable
?>
Logging has been disabled. If you experience problems, please contact us.
<?php
	return false;
}elseif (isset($_POST['logme'])){
	//add a cookie for this user.
	$expire = time() + (60 * 60 * 24 * 14); //set expire to be 2 weeks from now
	
	//create a unique value, to be able to track the user's actions easier
	// for purposes of session debugging
	if (isset($_COOKIE['debug_log'])){
		$rand = $_COOKIE['debug_log'];
	} else {
		$rand = chr(rand(65,97)).chr(rand(65,97)).chr(rand(65,97)).'-'.sha1(rand());
	}
	setcookie('debug_log',$rand,$expire,'/');
	//add data to log file.
	$date = date('[F d, Y :: H:i:s] -- ');
	//fix where same user reports different user agents
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
			
	//remove extra space in IE7 for MSN 9.1
	$user_agent = str_replace('  ',' ',$user_agent);
			
	//remove the extra weird thingy added sometimes on MSIE 6.0 MSN 9.1??
	$user_agent = str_replace('; yplus 5.1.04b','',$user_agent);
	$message = "{$date}{$_SERVER['REMOTE_ADDR']} : [NOTICE] Starting Session Logging.  Cookie: {$rand}
Data:\n";
	$message .= "\$_SERVER['REMOTE_ADDR'] = {$_SERVER['REMOTE_ADDR']}
\$_SERVER['HTTP_USER_AGENT'] = {$_SERVER['HTTP_USER_AGENT']}
(altered) \$_SERVER['HTTP_USER_AGENT'] = {$user_agent}
\$_SERVER['HTTP_ACCEPT_ENCODING'] = {$_SERVER['HTTP_ACCEPT_ENCODING']}
Current \$_COOKIE['classified_session'] = {$_COOKIE['classified_session']}
User-Input Data:
";

	foreach ($_POST as $key => $value){	
		if ($key != 'submit' && $key != 'logme' && strlen(trim($value)) > 0){
			$message .= "Field {$key} :{$value}\n";
		}
	}
	$message .= "---End Of Entry---\n";
	
	//make sure $message does not close the comment.
	$message = str_replace('*/','*[slashy]',$message);
	if (!$handle = fopen('log.php','a')){
		//file open failed.
		echo 'Error opening log file to write data.';
		return false;
	}
	if (fwrite($handle, $message) === false){
		//write failed
		echo 'Error writting data to log file.';
		return false;
	}
	fclose($handle);
	//Message after data is sent, and logging turned on
	//(Since primary use is session logging, message reflects that):
?>
<h1>Thank you!</h1>
<h2>Information Submitted & Session Logging Enabled</h2>
<strong>Next Step:</strong> go <a href="../../">back to the main site</a>, and attempt to log in.
<br /><br />
If you wish to report additional information to technical support, come back to this page and there will
be a form to submit directly to technical support.<br /><br />

<a href="../../">Back to the main site</a>
<?php
//end of that message.

} elseif (isset($_COOKIE['debug_log'])) {
//Message when user re-visits logme.php after cookie is already set
//(Since primary use is session logging, message reflects that):
?><h1>Log-In Trouble Report Form</h1>
Session logging enabled.  Use the form below to report any additional information directly
to technical support.
<form action="logme.php?logme=1" method="post">
<input type="hidden" name="logme" value="1" />
<label>Message: <br />
<textarea name="New_Message"></textarea>
<br /><input type="submit" name="submit" value="Send Message" />
</form>
<?php
//end of that message
} else {
//Message they see when they first visit the logme.php file.
//(Since primary use is session logging, message reflects that,
// note that any fields can be added or removed as needed):
?>
<h1>Log-In Trouble Report Form</h1>
In order to help us find the cause of log-in problems, please fill out the form below as thoroughly as possible, and click "Start logging my sessions".  
<br /><br />Then return to the main site, and attempt to log in again, to have the log-in attempt logged so we can see what is happening.
<br /><br />
This will provide technical support with information to help troubleshoot the problem, and will turn on "session logging" for your computer.<br /><br />
We appreciate any cooperation, and will work to resolve the issues as fast as possible.  We apologize for any inconvenience.
<br /><br />
<strong>All fields are optional</strong>, and will be used strickly for helping technical support fix login issues.  If you are not sure
what a particular question is asking for, enter "unknown" for that field.<br /><br />

<form action="logme.php?logme=1" method="post">
<input type="hidden" name="logme" value="1" />
<ol>
	<li><label>Browser Used: (Internet Explorer, AOL, Firefox, MSN, etc.)
		<br /><input type="text" name="browser" /></label></li>
	<li><label>Browser Version (usually found in Help > About): <input type="text" name="browser_version" /></label></li>
	<li><label>Operating System (Windows XP, Windows Vista, OS X, Etc.): <input type="text" name="os" /></label></li>
	<li><label>Computer Type (PC, Laptop PC, Mac, Mobile Device, WebTV, etc.): <input type="text" name="computer_type" /></label></li>
	<li><label>Internet Connection Type/Provider (broadband "directly connected", broadband wireless connection, wireless "hotspot", mobile internet using cell phone broadband, dialup, etc):
		<br /><textarea name="connection"></textarea></label></li>
	<li><label>Currently behind any firewalls or "spam" blockers? (if yes, provide as much info as possible, and whether you have attempted to log in with feature turned off)
		<br /><textarea name="firewall"></textarea></label></li>
	<li><label>Have you recently installed/upgraded any internet-related software, including automatic updates? (if yes, provide any information)
		<br /><textarea name="software_upgrade"></textarea></label></li>
	<li><label>Additional notes/information (like when problem started, etc):</li>
	<textarea name="extra_info"></textarea></label></li>
</ol>
<input type="submit" name="submit" value="Start logging my sessions" />
</form>
<?php
//end of that message
}
//Message that is at the bottom of each page
//(Since primary use is session logging, message reflects that):
?>
<br /><br />
<strong>Note: </strong> Any information you provide, or that is logged using session logging, will be used by technical support,
in order to troubleshoot log-in problems.  It will NOT be used for any other purposes.  If
you wish to turn session logging back off, clear your cookies.  (The cookie that turns logging on will automatically expire in 2 weeks.)
