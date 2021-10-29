<?php
//requirement_test.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
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

//tests all requirements of the new software.
if (!(isset($no_show_tests) && $no_show_tests)){
	//being run as standalone
	$pass = '<span style="color:green; font-weight:bold;">PASS</span> ';
	$fail = '<span style="color:red; font-weight:bold;">FAIL</span> ';
	$allpass = true;
	echo (check_zend_installed() ? $pass : $fail ).'Zend Optimizer Installed?<br>';
	if (data_access_exists()){
		echo $pass.'Encoded Files Uploaded?<br>&nbsp > &nbsp; &nbsp; ';
		if (check_products_upload() && check_data_access_upload()){
			echo $pass .'products.php Uploaded in BINARY mode?<br />&nbsp > &nbsp; &nbsp; ';
			echo $pass.'classes/DataAccess.class.php AND classes/php5_dir/DataAccess.class.php files Uploaded in BINARY mode?<br>&nbsp > &nbsp; &nbsp; ';
			
			//check the license
			include_once('../../../app_top.common.php');
			include_once('../../../products.php');
			$product_configuration = geoPC::getInstance();
			echo ($product_configuration->verify_license() ? $pass : $fail ). 'License Installed?<br>';
		}else {
			echo (check_products_upload() ? $pass : $fail ).'products.php Uploaded in BINARY mode?<br />&nbsp > &nbsp; &nbsp; ';
			echo (check_data_access_upload() ? $pass : $fail ).'classes/DataAccess.class.php AND classes/php5_dir/DataAccess.class.php files Uploaded in BINARY mode?<br>';
			$allpass = false;
		}
	} else {
		echo $fail.'Encoded Files Uploaded?<br>';
		$allpass = false;
	}
	
	if (!$allpass){
		echo 'UNABLE TO RUN MORE TESTS UNTIL ABOVE PROBLEMS ARE FIXED<br>';
	} else {
		echo $pass.'OVERALL STATUS?';
	}
}
function check_zend_installed(){
	if (function_exists('zend_loader_enabled')){
		if (zend_loader_enabled()){
			//it appears that is valid.
			return true;
		}
	}
	//either the function did not exist, or the loader is disabled.
	return false;
}
function data_access_exists(){
	if (file_exists('../../../classes/DataAccess.class.php') && file_exists('../../../classes/php5_classes/DataAccess.class.php')){
		return true;
	} else {
		return false;
	}
}
function check_products_upload(){
	//$hash = sha1_file('../products.php');
	//var_dump($hash);
	$contents = file_get_contents('../../../products.zend.php');
	if (strpos($contents,'@Zend;') && strpos($contents, "\x0d") == false){
		//did not find any newlines, so must be invalid.
		return false;
	}
	return true;
}
function check_data_access_upload(){
	$php4 = file_get_contents('../../../classes/DataAccess.class.zend.php');
	$php5 = file_get_contents('../../../classes/php5_classes/DataAccess.class.zend.php');
	
	if (strpos($php4,'@Zend;') && strpos($php5,'@Zend;') && (strpos($php4, "\x0d") == false || strpos($php5, "\x0d") == false)){
		//did not find any newlines, so must be invalid.
		return false;
	}
	return true;
}
if (!function_exists('file_get_contents')){
	function file_get_contents($file){
		$if = fopen($file,'r');
		$contents = '';
		while (!feof($if)){
			$contents .= fread($if, 8192);
		}
		fclose($if);
		return $contents;
	}
}
?>