<?php

//addons/zipsearch/app_top.php

if (isset($_POST["set_zip_filter"]) && isset($_POST["set_zip_filter_distance"]) && $_POST["set_zip_filter"]) {
    //new information given -- create cookies
    $expires = time() + 31536000;
    setcookie("zip_filter", "", 0, '/');
    setcookie("zip_distance_filter", "", 0, '/');
    if (strlen(trim($_POST["submit_zip_filter"])) > 0) {
        //local vars
        $originZip = $_POST["set_zip_filter"];
        $distance = $_POST["set_zip_filter_distance"];
        //cookies for future pageloads
        setcookie("zip_filter", $originZip, $expires, '/');
        setcookie("zip_distance_filter", $distance, $expires, '/');
        //cookie vars for this pageload
        $_COOKIE['zip_filter'] = $originZip;
        $_COOKIE['zip_distance_filter'] = $distance;
    } else {
        $originZip = 0;
        $distance = 0;
    }
    unset($expires);
} elseif (isset($_COOKIE["zip_distance_filter"]) && isset($_COOKIE["zip_filter"]) && $_COOKIE["zip_distance_filter"] && $_COOKIE["zip_filter"]) {
    //nothing new, but stil have the cookies from a previous pageload
    $originZip = $_COOKIE["zip_filter"];
    $distance = $_COOKIE["zip_distance_filter"];
} else {
    //not using zip filtering
    $originZip = 0;
    $distance = 0;
}

if (isset($_POST['clear_zip_filter'])) {
    //user clearing zip filter
    $originZip = 0;
    $distance = 0;
    setcookie('zip_filter', '', time() - 60 * 60 * 24, '/');
    setcookie('zip_distance_filter', '', time() - 60 * 60 * 24, '/');
    unset($_COOKIE['zip_filter'], $_COOKIE['zip_distance_filter']);
}

if (geoAddon::getRegistry('zipsearch')->enabled && $originZip) {
    $db->getTableSelect(DataAccess::SELECT_BROWSE)
        ->where(geoAddon::getUtil('zipsearch')->getSearchSql($originZip, $distance), 'zip');
}
//clear up memory and vars
unset($originZip, $distance);
