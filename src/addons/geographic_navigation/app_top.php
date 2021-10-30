<?php

//app_top.php


defined('GEO_BASE_DIR') or die('NO_ACCESS');


if (defined('IN_ADMIN')) {
    //don't run
    return;
}
$db = DataAccess::getInstance();
if (geoPC::is_print() && $db->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, that includes geographic navigation
    return;
}

if (stripos($_SERVER['SCRIPT_NAME'], $db->get_site_setting('classifieds_file_name')) === false && stripos($_SERVER['SCRIPT_NAME'], 'AJAX.php') === false) {
    //not on index.php or ajax, so we don't care about regions stuff (and would really rather not touch it at all)
    return;
}

if (stripos($_SERVER['SCRIPT_NAME'], 'AJAX.php') !== false && $_GET['controller'] !== 'ModuleControls') {
    //on further thought, the only AJAX where we really want to allow this is ModuleControls (at least for now)
    //because doing it in other places, like with RegionSelect on the registration page (which is always subdomainless), causes CORS issues
    return;
}

$geoNavReg = geoAddon::getRegistry('geographic_navigation');

$region = $_GET['region'];
if ($region && $geoNavReg->useLegacyUrls == 1) {
    $newRegion = false;
    if (stripos($region, 'country') === 0) {
        //looking for an old country

        $newRegion = $db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_countries` WHERE `country_id` = ?", array(intval(substr($region, 7))));
    }
    if (stripos($region, 'state') === 0) {
        //looking for an old state
        $newRegion = $db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_states` WHERE `state_id` = ?", array(intval(substr($region, 5))));
    }
    if (stripos($region, 'region') === 0) {
        //looking for an old region
        $newRegion = $db->GetOne("SELECT `upgrade_region_id` FROM `geodesic_addon_geographic_regions` WHERE `id` = ?", array(intval(substr($region, 6))));
    }
    if ($newRegion) {
        //found the new region id -- 301 to its page
        $util = geoAddon::getUtil('geographic_navigation');
        $movedTo = $util->getBaseUrl() . 'region=' . $newRegion;
        header('Location: ' . $movedTo, 301);
        include GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }
}
unset($region);

$region_id = 0;

if ($geoNavReg->subdomains == 'on') {
    //check sub-domain
    $pc = geoPC::getInstance();

    $util = geoAddon::getUtil('geographic_navigation');

    //figure out the main part minus subdomain
    $host = geoPC::cleanHostName($_SERVER['HTTP_HOST']);
    //remove www if at start, to allow silly stuff like www.region.example.com
    $host = preg_replace('/^www\./', '', $host);

    //get "main part" of domain name, minus "not attached to license" part of subdomain
    $mainHost = $pc->validateMainDomain($host);

    //now figure out what subdomain is
    $subdomain = ($host != $mainHost) ? substr($host, 0, strpos($host, $mainHost)) : '';

    //clean it up
    $subdomain = $util->subdomainClean($subdomain);

    if (strlen($subdomain)) {
        //now find what region, country, or state matches that subdomain
        $region_id = $db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE `unique_name`=? AND `enabled`='yes'", array ($subdomain));
        if ($region_id) {
            //let view know about subdomain
            geoView::getInstance()->geographic_navigation_subdomain = $subdomain;
        }
    } elseif (!isset($_GET['region']) && $_COOKIE['region']) {
        //user did not enter a subdomain or a specific region link, but there is still a region set by cookie (and subdomains are on)
        //find the subdomain that should be in use, and redirect to it
        $subdomain = $db->GetOne("SELECT `unique_name` FROM " . geoTables::region . " WHERE `id` = ? AND `enabled` = 'yes'", array((int)$_COOKIE['region']));
        if ($subdomain && !($_GET['a'] || $_POST['b'] || $_POST['c'])) { //don't do the redirect unless we're already on the home page
            $redirect = geoAddon::getUtil('geographic_navigation')->getLinkForRegion($region_id, $subdomain);
            header("Location: $redirect");
            require GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }
    }
    //unset stuff since this is at global level, stop global var polution!
    unset($pc, $util, $host, $mainHost, $subdomain, $row);
}

$cookieDomain = geoSession::getInstance()->getCookieDomainName();

if (!$region_id) {
    //region ID not specified by subdomain, so use cookie or GET var
    $util = geoAddon::getUtil('geographic_navigation');
    if (isset($_GET['region'])) {
        //Set the cookie according to what they clicked on
        $expires = geoUtil::time() + 60 * 60 * 24 * 7 * 20;//5 months
        if ($_GET['region'] && $util->checkRegionId(trim($_GET['region']))) {
            $region_id = trim($_GET['region']);
            setcookie("region", $region_id, $expires, "/", $cookieDomain);
        } else {
            //set to 0 most likely, so clearing the region selected
            setcookie("region", '', 0, '/', $cookieDomain);
        }
    } elseif (isset($_COOKIE['region'])) {
        //They have cookie set for region, so filter according to their cookie

        if ($_COOKIE['region'] && $util->checkRegionId(trim($_COOKIE['region']))) {
            $region_id = trim($_COOKIE['region']);
        }
    }
}

if ($_GET['region'] === "0") {
    //if region is explicitly "0," then the user has asked for "All Regions"
    //do NOT do geo_ip auto-assignment for this session
    setcookie('region_skip_autoassign', 1, 0, '/', $cookieDomain);
    $_COOKIE['region_skip_autoassign'] = 1;
    //Also, if region is set to 0 we don't want search engines indexing this page...
    geoView::getInstance()->addTop('<meta name="robots" content="noindex" />');
}

if (!$region_id && $geoNavReg->geo_ip && $geoNavReg->geo_ip_apikey && $_COOKIE['region_skip_autoassign'] != 1 && !geoSession::getInstance()->is_robot() && (defined('GEO_INDEX') && GEO_INDEX)) {
    //try to get region by IP (only if region not set manually and using geo_ip is turned on) [also skip this for robots]
    //note for later: "ip-country" can be swapped for "ip-city" to return deeper results
    $ipLookupUrl = "http://api.ipinfodb.com/v3/ip-country/?key=" . $geoNavReg->geo_ip_apikey . "&ip=" . getenv('REMOTE_ADDR');
    $result = geoPC::urlGetContents($ipLookupUrl);
    $result = explode(';', $result);
    if ($result[0] === 'OK') {
        //get the name of the country for the current ip address
        $ip_country = $result[4];

        //see if there's a match for that country in the local db
        $levels = geoRegion::getLevelsForOverrides();
        $sql = "SELECT r.id FROM " . geoTables::region . " AS r, " . geoTables::region_languages . " AS l WHERE r.id=l.id AND r.level = ? AND l.name = ? AND r.enabled = 'yes'";
        $region_id = $db->GetOne($sql, array($levels['country'], geoString::toDB($ip_country)));
    }
    if ($region_id) {
        //found something

        if (geoAddon::getRegistry('geographic_navigation')->subdomains == 'on') {
            //using subdomains. if this region has a subdomain, redirect to it.
            $sql = "SELECT `unique_name` FROM " . geoTables::region . " WHERE id = ? AND enabled = 'yes'";
            $subdomain = $db->GetOne($sql, array($region_id));
            if ($subdomain && !($_GET['a'] || $_POST['b'] || $_POST['c'])) { //don't do the redirect unless we're already on the home page
                $redirect = geoAddon::getUtil('geographic_navigation')->getLinkForRegion($region_id, $subdomain);
                header("Location: $redirect");
                require GEO_BASE_DIR . 'app_bottom.php';
                exit();
            }
        }

        //not using subdomains or this region has no subdomain -- set the cookie directly
        $expires = geoUtil::time() + 60 * 60 * 24 * 7 * 20;//5 months
        setcookie("region", $region_id, $expires, "/", $cookieDomain);
    }
    unset($result, $expires); //clean up
}

//make sure other parts know what it is
$_COOKIE['region'] = $region_id;
$expires = geoUtil::time() + 60 * 60 * 24 * 7 * 20;//5 months
setcookie("region", $region_id, $expires, "/", $cookieDomain);



if ($region_id) {
    //add the filter

    //let view know about region
    geoView::getInstance()->geographic_navigation_region = $region_id;
    if (geoAddon::getRegistry('geographic_navigation')->combineTree) {
        $tpl = new geoTemplate(geoTemplate::ADDON, 'geographic_navigation');
        $tpl_vars = array();
        $util = geoAddon::getUtil('geographic_navigation');
        $tpl_vars['breadcrumb'] = $util->getBreadcrumbFor($region_id);
        $tpl_vars['base_url'] = $util->getBaseUrl();
        $tpl_vars['skipUl'] = true;
        $tpl_vars['msgs'] = geoAddon::getText('geo_addons', 'geographic_navigation');
        $tpl->assign($tpl_vars);
        geoView::getInstance()->category_tree_pre = $tpl->fetch('breadcrumb.tpl');
        unset($tpl, $tpl_vars);//free up memory
    }
    $browseQuery = $db->getTableSelect(DataAccess::SELECT_BROWSE);

    $util = geoAddon::getUtil('geographic_navigation');
    $util->applyFilter($browseQuery, $region_id);

    unset($browseQuery, $util);
}
//final cleanup of the rest of the vars we used...
unset($region_id, $geoNavReg);
