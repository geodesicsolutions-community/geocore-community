<?php
// GeoInstaller
@include("../config.php");

define('GEO_SETUP',1);

//Fix for mis-configured / mis-informed sites that have magic_quotes_runtime turned on...  Must turn it off!
if (function_exists('set_magic_quotes_runtime') && get_magic_quotes_runtime()) {
	//must check for function first, since function will be removed from PHP in
	//future, along with ability to turn this stupid setting on.  Hooray!
	set_magic_quotes_runtime(false);
}

if(isset($_GET['step']) && isset($_POST['license']) && $_GET["step"] == 'config.php' && $_POST['license'] != "on")
{
	die('You must agree to the License Agreement to proceed with setup. Please <a href="index.php">go back</a>, read the License Agreement, and click the appropriate checkbox before continuing setup.');
}

if (!include("product.php")){
	die ('Error:  Setup file missing, or server permissions do now allow server includes.  Make sure all setup files are uploaded.');
}

// Get rid of notices
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

// Work around for PHP < 4.3.0
if(!function_exists('file_get_contents'))
{
	function file_get_contents($file)
	{
		$file = file($file);
		return !$file ? false : implode('', $file);
	}
}

// Defines
/*
 *	product[PRODUCT][x] syntax:
 *	In this file:
 *	define('INSTALL', '<Filename of this file>');
 *	i.e.  define('INSTALL', 'index.php');
 *	define('DB_TYPE', '<Database software here>');
 *	i.e.  define('DB_TYPE', 'mysql');
 *	Note that the database software will be all lower case
 *
 *	In included product files:
 *	define('PRODUCT', '<Product name here>');
 *	i.e. define('PRODUCT', 'Auctions Enterprise');
 *	This would be for instance auctions enterprise
 */
define('INSTALL', 'index.php');
define('DB_TYPE', (isset($db_type) && strlen($db_type))? $db_type : 'mysql');

$step = (isset($_GET["step"]))? $_GET['step'] : ((isset($_REQUEST['a']))? $_REQUEST['a'] :'requirement_check');

if ($step != 'requirement_check') {
	//if we have ini_tools available, up the memory limit.
	if (file_exists('../ini_tools.php')){
		include_once('../ini_tools.php');
		geoRaiseMemoryLimit('32M');
	}
	
	//allow the adodb files to be in the setup folder.
	if (file_exists('adodb/adodb.inc.php')){
		include_once('adodb/adodb.inc.php');
	} else if (file_exists('../classes/adodb/adodb.inc.php')){
		include_once('../classes/adodb/adodb.inc.php');
	} else {
		die ('Error:  <strong>adodb/</strong> folder & drivers not found.  These are required for the setup to continue.  Please upload the adodb/ folder to the same location as it exists in the software package.
	<br /><br /><a href="mailto:support@geodesicsolutions.com">Contact support</a> if you require assistance.');
	}
}

if($step != 'requirement_check' && $step != 'config.php' && $step != 'dbtest' && $step != 'config.php_check')
{
	$db = ADONewConnection(DB_TYPE);
	if($persistent_connections)
	{
		if (!$db->PConnect($db_host, $db_username, $db_password, $database))
		{
			echo "ERROR!!!  Could not connect to database.<br>";
			exit;
		}
	}
	else
	{
		if (!$db->Connect($db_host, $db_username, $db_password, $database))
		{
			echo "ERROR!!!  Could not connect to database.<br>";
			exit;
		}
	}
	if (isset($strict_mode) && $strict_mode){
		$db->Execute('SET SESSION sql_mode=\'\'');
	}
}

// TODO Remove this later when GeoCore is more setup
if(isset($product_type) && $product_type == 3 && $step == 'config.php')
{
	$step = 'dbtest';
}
elseif(isset($product_type) && $product_type == 3 && $step == 'site')
{
	$step = 'congrats';
}

$template = file_get_contents("main.html");

//already done above, is this needed here for geocore?
//if(isset($_GET['step']))
//	$step = $_GET['step'];
if (!isset($product_type) || $product_type != 3){
	//ugly hack, need to re-do the setup process to not use product type...
	$product_type = 4;
}
switch($step)
{
	case 'requirement_check':
		/*	Start Checking requirements	*/
		include_once('requirement_check.php');
		$template_main = file_get_contents('requirement_check.html');
		requirement_check($template_main);
		$template = str_replace('(!MAINBODY!)',$template_main,$template);
		/*	End Checking PHP Version info	*/
		break;

	case 'config.php':
		/*	Start Checking config.php info	*/
		include_once("config_check.php");
		config_check($template);
		/*	End Checking config.php info	*/
		break;

	case 'config.php_check':
		/*	Start Checking config.php info	*/
		include_once("config_check.php");
		if($error = write_config($_REQUEST["b"], $product_type))
			config_check($template, $error);
		else
		{
			include_once("dbtest.php");
			dbtest($template);
		}
		/*	End Checking config.php info	*/
		break;

	case 'dbtest':
		/*	Start Database connections	*/
		include_once("dbtest.php");
		dbtest($template);
		/*	End Database connections	*/
		break;

	case $_GET['step']:
	case 'sql':
		/*	Start running sql statements	*/
		include_once("sql.php");
		$error = sql($db, $template);
		$template = run_upgrade($template, '../'.$install[$product_type]["upgrade"]);
		/*	End running sql statements	*/
		break;

	case 'site':
		/*	Start site information	*/
		include_once("site.php");
		
		$errors = site($db, $install[$product_type], $template);
		if($errors)
		{
			// Error reporting code
		}
		/*	End site information	*/
		break;

	case 'site_save':
		/*	Start saving site information	*/
		include_once("site.php");
		$file = file_get_contents("site_success.html");
		$error = site_save($db, $_REQUEST["conf"], $install[$product_type]);
		if(is_array($error))
		{
			// Get error code and call
			site($db, $install[$product_type], $template, $error);
		}
		else
		{
			$file = "<form action=".INSTALL."?a=login method=post>".$file."</form>";
			$template = str_replace("(!MAINBODY!)", $file, $template);

			// Successfully saved
			$template = str_replace("(!SUCCESS!)", "Your information was saved successfully!<br /><br />".
													"Please click the \"continue\" button to move on to the next step.",
													$template);
			$template = str_replace("(!CONTINUE!)", '<div id="submit_button"><a href="index.php?a=login" style="padding-top:.25em;">Continue</a></div>', $template);
		}
		/*	End saving site information	*/
		break;

	
	case 'login':
	case 'login_save':

	case 'image':
		/*	Start image information	*/
		include_once("image.php");
		image($template, $install[$product_type]);
		/*	End image information	*/
		break;

	case 'image_save':
		/*	Start saving image information	*/
		include_once("image.php");
		$errors = image_save($db, $install[$product_type], $_REQUEST["config"]);
		if($errors)
		{
			image($template,$product_type, $errors);
		}
		else
		{
			$file = file_get_contents("image_success.html");
			$file = "<form action=".INSTALL."?a=registration method=post>".$file."</form>";
			$template = $template = str_replace("(!MAINBODY!)", $file, $template);
			// Successfully saved
			$template = str_replace("(!SUCCESS!)", "Successfully saved your image information.  All of your directory permissions seem to be in order.<br /><br />".
									"Please click the \"continue\"  button to move on to the next step.",
									$template);
			$template = str_replace("(!CONTINUE!)", '<div id="submit_button"><a href="index.php?a=registration" style="padding-top:.25em;">Continue</a></div>', $template);
		}
		/*	End saving image information	*/
		break;

	case 'registration':
		/*	Start registration information	*/
		include_once("registration.php");
		registration($db, $install[$product_type], $template);
		/*	End registration information	*/
		break;

	case 'registration_save':
		/*	Start saving registration information	*/
		$config = $_REQUEST["b"];
		include_once("registration.php");
		$errors = registration_save($db, $config, $install[$product_type]);
		if($errors)
		{
			registration($db, $install[$product_type], $template, $errors, $config);
		}
		else
		{
			$file = file_get_contents("registration_success.html");
			$file = "<form action=".INSTALL."?a=email method=post>".$file."</form>";
			$template = $template = str_replace("(!MAINBODY!)", $file, $template);

			// Successfully saved
			$template = str_replace("(!SUCCESS!)", "Successfully saved your register.php file information.<br /><br />".
									"Please click the \"continue\"  button to move on to the next step.",
									$template);
			$template = str_replace("(!CONTINUE!)", '<div id="submit_button"><a href="index.php?a=email" style="padding-top:.25em;">Continue</a></div>', $template);
		}
		/*	End saving registration information	*/
		break;

	case 'email':
		/*	Start testing email information	*/
		include_once("email.php");
		$errors = email($db, $install[$product_type], $template);
		/*	End testing email information	*/
		break;

	case 'congrats':
		$file = file_get_contents("congrats.html");
		$template = str_replace("(!MAINBODY!)", $file, $template);

		if($product_type == 1)
		{
			$manual = "<a href=\"http://www.geodesicsolutions.com/products/auctions/enterprise/pdfmanual/enterprise_manual.pdf\">
				GeoAuctions Enterprise Product Manual</a>\n";

			// (!SOFTWARE_LINK!)
			$sql_query = "select auctions_url from geodesic_auctions_configuration";
			$result = $db->Execute($sql_query);
			$url = $result->FetchNextObject();
			$url_replace = "<a href=\"".$url->AUCTIONS_URL."\">".$url->AUCTIONS_URL."</a>";

			// (!ADMIN_LINK!)
			$path_parts = pathinfo($url->AUCTIONS_URL);
			$admin_path = $path_parts['dirname']."/admin/index.php";
			$admin_path = "<a href=\"".$admin_path."\">".$admin_path."</a>";
		}
		elseif($product_type == 2)
		{
			$manual = "<a href=\"http://www.geodesicsolutions.com/products/classifieds/enterprise/pdfmanual/enterprise_manual.pdf\">
				GeoClassifieds Enterprise Product Manual</a>\n";

			// (!SOFTWARE_LINK!)
			$sql_query = "select  classifieds_url from geodesic_classifieds_configuration";
			$result = $db->Execute($sql_query);
			$url = $result->FetchNextObject();
			$url_replace = "<a href=\"".$url->CLASSIFIEDS_URL."\">".$url->CLASSIFIEDS_URL."</a>";

			// (!ADMIN_LINK!)
			$path_parts = pathinfo($url->CLASSIFIEDS_URL);
			$admin_path = $path_parts['dirname']."/admin/index.php";
			$admin_path = "<a href=\"".$admin_path."\">".$admin_path."</a>";
		}
		elseif($product_type == 3)
		{
			// TODO change later
			$manual = "";

			$path_parts = pathinfo($_SERVER['PHP_SELF']);
			$path_parts['dirname'] = str_replace("setup", "", $path_parts['dirname']);

			$url_replace = "http://".$_SERVER['HTTP_HOST'].$path_parts['dirname'];
			$url_replace = "<a href=\"".$url_replace."\">".$url_replace."</a>";

			$admin_path = 'http://'.$_SERVER['HTTP_HOST'].$path_parts['dirname']."admin/index.php";
			$admin_path = "<a href=\"".$admin_path."\">".$admin_path."</a>";
		}
		elseif($product_type == 4)
		{
			$manual = "<a href=\"http://www.geodesicsolutions.com/products/classifieds/enterprise/pdfmanual/enterprise_manual.pdf\">
				GeoClassAuctions Enterprise Product Manual</a>\n";

			// (!SOFTWARE_LINK!)
			$sql_query = "select classifieds_url from geodesic_classifieds_configuration";
			$result = $db->Execute($sql_query);
			$url = $result->FetchNextObject();
			$url_replace = "<a href=\"".$url->CLASSIFIEDS_URL."\">".$url->CLASSIFIEDS_URL."</a>";

			// (!ADMIN_LINK!)
			$path_parts = pathinfo($url->CLASSIFIEDS_URL);
			$admin_path = $path_parts['dirname']."/admin/index.php";
			$admin_path = "<a href=\"".$admin_path."\">".$admin_path."</a>";
		}
		elseif(($product_type == 5) || ($product_type == 6))
		{
			// Check for auctions or classifieds
			$sql_query = "select auctions_url from geodesic_auctions_configuration";
			$result = $db->Execute($sql_query);
			if($result)
			{
				// Auctions
				$url = $result->FetchNextObject();

				$manual = "<a href=\"http://www.geodesicsolutions.com/products/auctions/enterprise/pdfmanual/enterprise_manual.pdf\">
					GeoAuctions Enterprise Product Manual</a>\n";

				// (!SOFTWARE_LINK!)
				$url_replace = "<a href=\"".$url->AUCTIONS_URL."\">".$url->AUCTIONS_URL."</a>";

				// (!ADMIN_LINK!)
				$path_parts = pathinfo($url->AUCTIONS_URL);
				$admin_path = $path_parts['dirname']."/admin/index.php";
				$admin_path = "<a href=\"".$admin_path."\">".$admin_path."</a>";
			}
			else
			{
				$sql_query = "select  classifieds_url from geodesic_classifieds_configuration";
				$result = $db->Execute($sql_query);
				$url = $result->FetchNextObject();

				$manual = "<a href=\"http://www.geodesicsolutions.com/products/classifieds/enterprise/pdfmanual/enterprise_manual.pdf\">
					GeoClassifieds Enterprise Product Manual</a>\n";

				// (!SOFTWARE_LINK!)
				$url_replace = "<a href=\"".$url->CLASSIFIEDS_URL."\">".$url->CLASSIFIEDS_URL."</a>";

				// (!ADMIN_LINK!)
				$path_parts = pathinfo($url->CLASSIFIEDS_URL);
				$admin_path = $path_parts['dirname']."/admin/index.php";
				$admin_path = "<a href=\"".$admin_path."\">".$admin_path."</a>";
			}
		}
		$template = str_replace("(!MANUAL!)", $manual, $template);
		//$template = str_replace("(!SOFTWARE_LINK!)", $url_replace, $template);
		$template = str_replace("(!ADMIN_LINK!)", $admin_path, $template);

		break;
}
// Put in product name
$template = str_replace("(!PRODUCT_NAME!)", $install[$product_type]['product_name'], $template);

$template = preg_replace("/<<[a-zA-Z_]*>>/", "", $template);
$template = preg_replace("/\(![a-zA-Z_]*!\)/", "", $template);
echo $template;
