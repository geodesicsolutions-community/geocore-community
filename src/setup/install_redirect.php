<?php

//Fix for stupid sites that have magic_quotes_runtime turned on...  Must turn it off!
if (function_exists('set_magic_quotes_runtime') && get_magic_quotes_runtime()) {
	//must check for function first, since function will be removed from PHP in
	//future, along with ability to turn this stupid setting on.  Hooray!
	set_magic_quotes_runtime(false);
}

header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
include_once("../config.php");
//include_once("product.php");


if (!$_REQUEST["key"])
	$_REQUEST["key"] = 0;

$template = file_get_contents("main.html");

// Generate URL paths
$url_path = str_replace("install_redirect.php",$_REQUEST["install"],$_SERVER["PHP_SELF"]);
if ($_REQUEST['key'] == $_REQUEST['total']){
	$redirect_url = "http://".$_SERVER["HTTP_HOST"].$url_path."?a=site";
} else {
	$redirect_url = "http://".$_SERVER["HTTP_HOST"].$url_path."?a=sql&key=".$_REQUEST["key"];
}

// Replace header info
$header = "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"1;URL=".$redirect_url."\">";
$header .= "</head>";
$template = str_replace("</head>", $header, $template);

// Create main body
$file = file_get_contents("sql.html");
$body = "<br><br>Step ".$_REQUEST["key"]." of ".($_REQUEST["total"])." completed.<br><br>";
$file = str_replace("(!MAINBODY!)", $body, $file);

$template = str_replace("(!MAINBODY!)", $file, $template);

$template = str_replace("(!HEADER!)", "", $template);
echo $template;
