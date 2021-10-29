<?php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.02.1-5-gd0d9f7b
## 
##################################

//Anything that needs to be initiallized, started, or whatever at the beginning needs to go in here.
define ('IN_ADMIN',1);
define('CJAX_JS_DIR','../classes/cjax/core/js/');

require_once ("../app_top.common.php");

require_once (ADMIN_DIR."admin_site_class.php");
require_once (ADMIN_DIR."admin_authentication_class.php");
require_once (ADMIN_DIR . PHP5_DIR . 'Admin.class.php');

//error_reporting  (E_ERROR | E_WARNING | E_PARSE);

//create connection to the database.
$debug = 0;
$debug_cookie = 0;


if ($debug)
{
	foreach ($_COOKIE as $key => $value)
		echo $key." is the cookie and ".$value." is the value<br>\n";
}

//$debug_cookie = 1;

if ($debug_cookie)
{
	echo $HTTP_COOKIE_VARS["admin_classified_session"]." is HTTP_COOKIE_VARS-admin_classified_session cookie vars<Br>\n";
	echo $_COOKIE["admin_classified_session"]." is _COOKIE-admin_classified_session cookie vars<Br>\n";
}

$site = Singleton::getInstance('Admin_site');
$site->product_configuration = geoPC::getInstance();

if(!isset($product_configuration)||!$product_configuration){
	$product_configuration = geoPC::getInstance();
}

$auth = Singleton::getInstance('Admin_auth');
//$current_time = geoUtil::time();

$session = geoSession::getInstance();

$session->cleanSessions();
include_once ('../reset_admin_password.php');



if (defined('TURN_ON_RESET_PASSWORD_TOOL') && TURN_ON_RESET_PASSWORD_TOOL){
	//only show them the link.
	$auth->admin_login_form($db, $_REQUEST["b"]["username"], $_REQUEST["b"]["password"]);
	return false;
}
//figure out admin url.

$classified_session = $session->initSession();

if ($session->getUserId() == 0 || !$product_configuration->discover_type()){
	//they probably need to be logged in.
	
	$tpl = new geoTemplate();
	$templates_c = $tpl->compile_dir;
	#$templates_c = (dirname(dirname(__file__)).DIRECTORY_SEPARATOR.'templates_c');
	if(!is_writeable($templates_c)) {
		die("<b>Template Error:</b>  Make sure the directory <em>$templates_c/</em> is writable (CHMOD 777).");
	}
	
	$license = (isset($_POST['b']['license_key'])? $_POST['b']['license_key'] : 0);
	//echo $license;
	$userID = 0;
	if (isset($_POST['b']['username']) && (isset($_POST['b']['password']) || isset($_POST['b']['pvalidate']))) {
		//attempt to log user in.
		if ($_REQUEST['cookieexists'] == "false")
		{
			//javascript is not enabled, so send them back to the login form
			$auth->auth_messages["javascript"] = 'You must enable javascript to run this admin.';
			$auth->admin_login_form($db, $_POST["b"]["username"], $_POST["b"]["password"], $license);
			return (false);
		}
		//attempt to log them in.
		if (!isset($_POST['b']['sessionId'])){
			$auth->validate_login($_POST['b']);
			return false;
		}
		$authorized = $auth->login($db,$_POST["b"]["username"],$_POST["b"]["pvalidate"], $license,$session->getSessionId());
		if ($authorized && $product_configuration->discover_type())
		{
			$userID = $session->getUserId();
		}
	}
	if(!$userID && geoPC::is_trial() && defined( 'AJAX_REQUEST') && isset($_GET['trial_auto']) && $_GET['trial_auto']==1) {
		//allow it so that trial demos can have addons auto installed
		
	} else if( !$userID && defined( 'AJAX_REQUEST') && isset($_GET['cjax']) && $_GET['cjax']==1) {
		$CJAX = geoCJAX::getInstance();
		$CJAX->alert("Your admin session has expired, you will need to log into the admin again.");
		$CJAX->location();
		exit();
	} else if( !$userID && defined( 'AJAX_REQUEST') ) {
		die( 'Not authorized' );
	} else if (!$userId && geoAjax::isAjax()) {
		if (isset($_GET['json']) && $_GET['json']) {
			//expecting a json formatted response, so give them one listing the error
			
			$data = array (
				'error' => 'Your admin session has expired, you will need to refresh the page to <strong>log into the admin again</strong>.',
				'session_error' => 1
			);
			$ajaxObj = geoAjax::getInstance();
			$ajaxObj->jsonHeader();
			echo $ajaxObj->encodeJSON($data);
		} else {
			//a non-json response, just echo out the message.
			echo '<div class="error" style="padding: 5px; border: 1px solid #B00000; margin: 4px;">Your admin session has expired, you will need to refresh the page to <strong>log into the admin again</strong>.</div>';
		}
		include GEO_BASE_DIR . 'app_bottom.php';
		return false;
	} elseif (!$userID) {
		$password = (isset($_POST['b']['pvalidate']))? $_POST['b']['pvalidate'] : $_POST['b']['password'];
		$auth->admin_login_form($db, $_POST["b"]["username"], $password, $license);
		return false;
	}
}

require_once 'admin_ad_configuration_class.php';
// Check these on every page load
Notifications::defaultChecks();

$view = geoView::getInstance();
$view->admin_userid = $session->getUserId();
$view->admin_username = $session->getUserName();
$view->geoturbo_status = geoPC::geoturbo_status();


return true;