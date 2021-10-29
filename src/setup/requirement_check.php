<?php
/*
 *	Copyright (c) 2004 Geodesic Solutions, LLC
 *	GeoInstaller
 *	All rights reserved
 *	http://www.geodesicsolutions.com
 *
 *	Module:		Version Module
 *	Filename:	version.php
 */

function php_version_check(& $version_num){
	$version_num = phpversion();
	$version_comp = version_compare($version_num, "5.4.0");
	$result = '';
	if ($version_comp == -1){
		//failed requirement.
		return false;
	} else {
		//passed requirement.
		return true;
	}
}

function mysql_check (& $text, $php_check){
	if (!function_exists('mysql_connect') && !function_exists('mysqli_connect')){
		//mysql not even installed.
		$text .= 'MySQL not installed, or not configured to work properly with PHP.';
		return false;
	}
	$adodbLocation = '../classes/adodb/adodb.inc.php';
	if (file_exists('../config.php')) {
		include '../config.php';
	}
	if ($php_check && isset($db_host) && $db_host != 'your_database_hostname' && strlen($db_host) && file_exists($adodbLocation)){
		//if config.php is already set up, attempt to get mysql server version.
		include_once($adodbLocation);
		@$db = ADONewConnection($db_type);
		
		@$db->Connect($db_host, $db_username, $db_password, $database);
		$info = $db->ServerInfo();
		if (is_array($info)){
			$mysql_version = $info['version'];
			if (strlen($mysql_version)){
				$version_comp = version_compare($mysql_version, '4.1.0');
				
				$text .= 'MySQL '.$mysql_version;
				
				if ($version_comp == -1){
					//mysql is a less version.
					return false;
				} else {
					return true;
				}
			}
		}
	}
	$reason = '(database connection settings not configured in config.php) - Version will be checked at the db connection step.';
	if (!$php_check) {
		//not checked, since if before PHP 5, the mysql check will cause a fatal
		//syntax error.
		$reason = '(Not checked since PHP requirement failed)';
	}
	$text = 'MySQL - Version not known '.$reason;
	//version not known, but mysql is at least installed, so proceed.
	return true;
}

function ioncube_ini_check (){
	$loaded = extension_loaded('ionCube Loader');
	if ($loaded){
		return true;
	}
	return false;
}




function replaceBlock(&$template, $block, $replace)
{
	//using preg-replace apparently is too complicated to work efficiently, use below
	//method instead.
	$start = strpos($template,'(!'.$block.'!)');
	$length = (strpos($template,'(!/'.$block.'!)') - $start) + strlen('(!/'.$block.'!)');
	$search = substr($template, $start, $length);
	//for debugging:
	//echo "start: $start length: $length search: <pre>".htmlspecialchars($search)."</pre>";
	$template = str_replace($search, $replace, $template);
}

function requirement_check(&$template)
{
	//replace with images eventually, instead of text...
	$failed = '<span class="failed"><img src="images/no.gif" alt="no" title="no"></span>';
	$passed = '<span class="passed"><img src="images/yes.gif" alt="yes" title="yes"></span>';
	$not_needed = '---';
	
	$package = 'ioncube';
	
	$overall_pass = '<p class="passed">All minimum requirements met.</p>';
	$overall_pass .= '<p><label><input type="checkbox" name="license" id="license" /> I have read and agree to the <a href="../docs/license.html">License Agreement</a></label></p>';
	
	$overall_fail = '<p class="body_txt1"><div style="text-align: left; background-color: #FFF; padding: 5px; border: 1px solid #EA1D25;"><span class="failed">IMPORTANT: As shown above, one or more of your server\'s minimum requirements have not been met.  These requirements must be met in order to continue with this installation.
	<br><br>NOTE: The IonCube Loaders are FREELY available for your host to download and install on your server. There is NO COST to your host, since the version that needs to be installed is the
	"decryption" version.
	<br><br>Hosting Trouble? Find our recommended hosting solutions by <a href="http://geodesicsolutions.com/resources.html" class="login_link">CLICKING HERE</a>.</span></div></p>
<p>Please <a href="mailto:support@geodesicsolutions.com" class="login_link">Contact Geodesic Support</a> if you need assistance.</p>';
	
	$continue_pass = '<div id="submit_button">
	<input type="submit" value="Continue" class="theButton" />
</div>';
	
	//
	
	$continue_fail = '';
	if (defined('IAMDEVELOPER')){
		//allow to keep going even if req fail, if developer..
		$continue_fail = $continue_pass;
		$overall_fail .= '<p><label><input type="checkbox" name="license" id="license" /> I have read and agree to the <a href="../docs/license.html" class="login_link">License Agreement</a></label></p>';
	}
	//req text
	$php_version_req = 'PHP Version 5.4.0+';
	$mysql_req = 'MySQL Version 4.1.0+';
	$ioncube_ini_req = 'ionCube Loader';
	
	//start out with passed message, then replace if one of the requirements fail.
	$overall = $overall_pass;
	//start out with the continue as pass, then replace if one of the requirements fail.
	$continue = $continue_pass;
	
	
	////PHP VERSION CHECK
	$php = php_version_check($version_num);
	$php_text = 'PHP '.$version_num;
	
	if (!$php){
		//php failed, and the requirement is PHP 5.4 so show message about
		//ability to use Geo 3.1
		$overall = $overall_fail;
		$continue = $continue_fail;
	}
	//replace php version text
	$template = str_replace('(!PHP_VERSION_TEXT!)',$php_text,$template);
	//replace php version check result
	$template = str_replace('(!PHP_VERSION_RESULT!)',($php)? $passed : $failed, $template);
	//replace php version req text
	$template = str_replace('(!PHP_VERSION_REQ!)', $php_version_req, $template);
	
	
	////MYSQL CHECK
	$mysql = mysql_check($text, $php);
	//replace mysql text
	$template = str_replace('(!MYSQL_TEXT!)', $text, $template);
	//replace mysql check result
	$template = str_replace('(!MYSQL_RESULT!)',($mysql)? $passed : $failed, $template);
	//replace php version req text
	$template = str_replace('(!MYSQL_REQ!)', $mysql_req, $template);
	if (!$mysql){
		$overall = $overall_fail;
		$continue = $continue_fail;
	}
	////IONCUBE INI CHECK
	$ioncube_ini = ioncube_ini_check();
	//replace ioncube ini req text
	$template = str_replace('(!IONCUBE_INI_REQ!)', $ioncube_ini_req, $template);
	
	
	//See which loader will be used
	if ($package == 'ioncube') {
		if ($ioncube_ini){
			//ioncube ini will be used.
			
			//change failed message to the not needed message, since one of the requirements was met.
			$ioncube_ini_text = 'Installed';
		} else {
			//use all the default returned text values.
			//keep the failed message as failed, since all 3 requirements failed.
			$url = "http://www.ioncube.com/loader_installation.php";
			if (file_exists('../ioncube/loader-wizard.php')){
				$url = "../ioncube/loader-wizard.php";
			}
			
			$ioncube_ini_text = '<span style="font-weight: bold; color:#EA1D25;">Not Installed.</span> <a href="'.$url.'">You can find instructions here.</a>';
						
			$overall = $overall_fail;
			$continue = $continue_fail;
		}
		
		$ionPass = $passed;
		$ionFail = $failed;
	}
	//replace package text
	if ($package == 'both') {
		$packageTxt = 'Server checked for at least "one" of the following:';
	} else if ($package == 'ioncube') {
		$packageTxt = 'Using Ioncube Package, requires Ioncube Loader';
	}
	if ($notice) {
		$template = str_replace('(!NOTICE!)', $notice, $template);
	} else {
		replaceBlock($template, 'NOTICE_BLOCK','');
	}
	//replace package text
	$template = str_replace('(!PACKAGE_TEXT!)',$packageTxt,$template);
	
	//replace ini result
	$template = str_replace ('(!IONCUBE_INI_RESULT!)',($ioncube_ini)? $ionPass : $ionFail, $template);
	
	//replace runtime text
	$template = str_replace('(!IONCUBE_INI_TEXT!)', $ioncube_ini_text, $template);
	
	//replace overall text
	$template = str_replace('(!OVERALL_RESULT!)',$overall, $template);
	//replace continue button yo.
	$template = str_replace('(!CONTINUE!)',$continue,$template);
	
	$search = array ('(!NOTICE!)','(!ZEND_BLOCK!)','(!/ZEND_BLOCK!)',
		'(!ION_BLOCK!)','(!/ION_BLOCK!)','(!NOTICE_BLOCK!)','(!/NOTICE_BLOCK!)');
	$template = str_replace($search, '', $template);
	return 0;
}

if (!defined('GEO_SETUP')) {
	$template = file_get_contents('main.html');
	if (!$template) $template = '(!MAINBODY!)';
	$template_main = file_get_contents('requirement_check.html');
	requirement_check($template_main);
	
	$template = str_replace(array('(!MAINBODY!)','(!HEADER!)'),array($template_main,''),$template);
	echo $template;
}
