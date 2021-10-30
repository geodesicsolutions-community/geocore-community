<?php

/**
 * This file figures out the URL based on the specified file, then do a re-direct
 * to that file.
 */

require_once 'app_top.common.php';

$file = geoFile::cleanPath($_GET['file']);

if (!$file) {
    echo "Invalid File!";
} else {
    $url = geoTemplate::getUrl('', $file);
    //do a 301 redirect

    $baseUrl = geoTemplate::getBaseUrl();

    header('Location: ' . $baseUrl . $url, true, 303);
}

include GEO_BASE_DIR . 'app_bottom.php';
