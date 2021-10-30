<?php

//SEO/app_top.php

# SEO Addon app_top file, for making sure urls are re-written.

//Do checks to see if we should do a 301 redirect, if the URL is "wrong"

//in admin, or not in geo environment
if (defined('IN_ADMIN') || !defined('GEO_BASE_DIR')) {
    return;
}

$seo = geoAddon::getUtil('SEO');
//problem getting SEO addon util object
if (!is_object($seo)) {
    unset($seo);
    return;
}

if ($seo->isON()) {
    //Tell view class that it should auto-add the <base ... /> tag for us to the
    //<head> section in any applicable templates.
    //We would normally do this type of thing elsewhere, but since we already need
    //to make use of the app_top, might as well do it here.

    geoView::getInstance()->addBaseTag = true;
}

//don't force re-write urls when posting
if (isset($_POST) && count($_POST) > 0) {
    return;
}

//no get vars
if (!isset($_GET) || count($_GET) == 0) {
    return;
}

//Not safe to force re-write URLs if server does not report request URI
if (!isset($_SERVER['REQUEST_URI']) || !$_SERVER['REQUEST_URI']) {
    return;
}

//SEO is turned off, or not forcing URLs to re-write
if (!$seo->isON('force_seo_urls')) {
    return;
}

//End of normal checks, at this point we will be looking at the URL used to see
//if it needs ot be changed or not, using 301 redirect.

//Build the "normal" URL
$parts = array();
$ignore_list = array('SEO_old_url');

foreach ($_GET as $key => $val) {
    if (in_array($key, $ignore_list)) {
        continue;
    }
    if (is_array($val)) {
        //There are no re-written URL's that are arrays!
        return;
    }
    $parts[] = "$key=$val";
}
unset($ignore_list);//done with var
if (count($parts) == 0) {
    //no parts to the url, no re-writting.
    return;
}

$before_url = $db->get_site_setting('classifieds_file_name') . '?' . implode('&amp;', $parts);
$after_url = $seo->rewriteUrl($before_url);

//This is NOT a re-written URL, or something wrong with figuring out the URLs.
if ($before_url == $after_url || !$before_url) {
    return;
}




//It gets this far: url should be re-written, now see if the URL is re-written to the right thing.
//be sure to preserve sub-domain
$siteUrl = geoFilter::getBaseHref();
if (strpos($after_url, 'http') !== 0) {
    //it is not re-written to include the full domain, so add the domain
    $after_url = $siteUrl . "$after_url";
}

if (strpos($_SERVER['REQUEST_URI'], '?') === false) {
    //The current URL used is in fact re-written, make sure it is re-written "correctly"
    $before_url = $_SERVER['REQUEST_URI'];
    $parts = explode('/', rtrim($siteUrl, '/'));
    //I hope they have their url settings set correctly!

    //Get rid of the first three parts in a "correctly set" url setting, the "http:", "", and "example.com"
    unset($parts[0], $parts[1], $parts[2]);
    if (count($parts)) {
        //Geo is installed in a sub-directory, remove the sub-directory from the beginning
        //since it will be added back later down
        $beginning = '/' . implode('/', $parts);
        //echo "beginning: $beginning<br />";
        if (strpos($before_url, $beginning) === 0) {
            $before_url = substr($before_url, strlen($beginning));
        }
        unset($beginning);//done with var
    }
    //now figure out the full "before" URL as it was re-written
    $before_url = rtrim($siteUrl, '/') . $before_url;

    //die ("TEST redirect: <br /><br />before: $before_url<br />after: $after_url<br /><br />URL Parts:<pre>".print_r($parts,1));

    if ($before_url == $after_url || urldecode($before_url) == $after_url) {
        //Looks like the current used URL is correctly re-written!
        unset($seo, $before_url, $after_url, $siteUrl, $parts);//free up mem & stop var bleeding
        return;
    }
} else {
    //the URL used is not re-written at all, but it is supposed to be...  No further checks needed, we know
    //that it needs to be re-directed to the re-writen URL.
}

//die ('Redirecting:<br /><br />URL before: '.$before_url.'<br /><br />URL after: '.$after_url);

//301 re-direct to proper URL
include GEO_BASE_DIR . 'app_bottom.php';
header('Location: ' . $after_url, true, 301);
exit;
