<?php

header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
include_once("../config.php");

if (!$_REQUEST["key"]) {
    $_REQUEST["key"] = 0;
}

$template = file_get_contents("main.html");

// Generate URL paths
function isSecure()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
}
$http = isSecure() ? 'https' : 'http';
$url_path = str_replace("install_redirect.php", 'index.php', $_SERVER["PHP_SELF"]);
if ($_REQUEST['key'] == $_REQUEST['total']) {
    $redirect_url = "$http://" . $_SERVER["HTTP_HOST"] . $url_path . "?a=site";
} else {
    $redirect_url = "$http://" . $_SERVER["HTTP_HOST"] . $url_path . "?a=sql&key=" . $_REQUEST["key"];
}

// Replace header info
$header = "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"1;URL=" . $redirect_url . "\">";
$header .= "</head>";
$template = str_replace("</head>", $header, $template);

// Create main body
$file = file_get_contents("sql.html");
$body = "<br><br>Step " . $_REQUEST["key"] . " of " . ($_REQUEST["total"]) . " completed.<br><br>";
$file = str_replace("(!MAINBODY!)", $body, $file);

$template = str_replace("(!MAINBODY!)", $file, $template);

$template = str_replace("(!HEADER!)", "", $template);
echo $template;
