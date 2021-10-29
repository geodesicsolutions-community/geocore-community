<?php
/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		Email Module
 *	Filename:	email.php
 */

function email($db, $product, &$template)
{
	$text = "<b>Be sure to test your e-mail settings.</b><br /><br />  Once the setup process is complete, be sure to send a test e-mail from the admin control panel, and adjust advanced e-mail settings as needed.";

	//$sql_query = "select * from ".$product['config_table'];
	//$result = $db->Execute($sql_query);
	//if(!$result)
	//{
	//	echo "Error executing query: ".$sql_query.'<br>';
	//	return 1;
	//}
	//else
	//	$data = $result->FetchRow();

	// Replace (!MAINBODY!) with file template
	$file = file_get_contents("email.html");
	$file = "<form name=save action=".INSTALL."?a=congrats method=post>".$file."</form>";
	$template = str_replace("(!MAINBODY!)", $file, $template);
	$template = str_replace("(!BACK!)", "<input type=button name=back value=\"<< Back\" onClick=\"history.go(-1)\">", $template);

	$separator = "\r\n";

	/* $from = "From: ".$data['site_email'].$separator;
	$from .= "Reply-to: ".$data['site_email'].$separator;
	$additional = "-f".$data['site_email'];
	$message = "This is a test email for your new Geodesic Software. The email was successfully sent!!!!.";
	$subject = "Test";

	if ($data['email_configuration'] == 1)
	{
		if(!@mail($data['site_email'], $subject, $message, $from, $additional))
		{
			// Safe mode is enabled
			// So try type 2
			if(mail($data['site_email'], $subject, $message, $from))
			{
				$text .= "<br><br>We detected that you have safe mode enabled on your PHP installation and have changed your email settings to reflect this.";
				$sql_query = "update ".$product['config_table']." set ".$data['email_configuration']." = 2";
				$db->Execute($sql_query);
			}
		}
	}
	elseif ($data['email_configuration'] == 2)
	{
		mail($data['site_email'], $subject, $message, $from);
	}
	else
		mail($data['site_email'], $subject, $message);
*/
	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
	
	// Send message to us and let us know that the installation went well
	$message = "SERVER NAME IS".$_SERVER['SERVER_NAME']."\n\n\n";
	$message .= "An installation was completed on a server called ".$_SERVER['SERVER_NAME'].".\n";
	$message .= "The host is ".$_SERVER['HTTP_HOST'].".\n";
	$message .= "PHP_Self is ".$_SERVER['PHP_SELF'].".\n";
	$message .= "The File is ".__FILE__.".\n";
	$message .= "PHPInfo:\n".$phpinfo;
	$subject = "Installation on server ".$_SERVER['SERVER_NAME'].".";

	@mail('installations@geodesicsolutions.com', $subject, $message);

	$template = str_replace("(!MESSAGE!)", $text, $template);
	$template = str_replace("(!SAVE!)", '<div id="submit_button"><a href="index.php?a=congrats" style="padding-top:.25em;">Continue</a></div>', $template);

	return 0;
}
?>
