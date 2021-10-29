<?php

//changelogs.php
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
## ##    7.3.0
##
##################################

/**
 * Generates a list of changelogs
 */

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Geodesic Solutions Changelogs</title>

<style>
    ul.nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    ul.nav li {
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    ul.nav li a {
        display: inline-block;
        padding: 6px;
        margin: 4px;
        border: 1px solid #777;
        border-radius: 5px;
        text-decoration: none;
        color: black;
    }
    ul.nav li a:hover {
        background-color: #dfd;
    }
</style>
</head>
<body>
<h1>Changelogs</h1>
<p>Below is a list of changelogs available.</p>

<ul class="nav">
<?php
require 'versions.php';

$changelogs = array();
$high = '';
foreach ($versions as $version => $info) {
    if (isset($info['changelog'])) {
        /*
         * Ideas for when we start getting more releases:
         *
         * - If version ends in .0.0 do special styling or perhaps add extra break or something, maybe mention
         *   it's major feature release
         * - If version ends in .0 do special styling, maybe mention it's minor feature release
         * - OR could set up sub-lists based on major/minor release like:
         *
         * - 6.0.0
         *   - 6.0.1
         *   - 6.0.2
         * - 6.1.0
         *   - 6.1.1
         *   - 6.1.2
         *   - 6.1.3
         *
         * Will just need to experiment with different ideas once we get enough
         * versions to warrant this.
         */
        ?>
        <li>
            <a href="<?php echo $info['changelog']; ?>"><?php echo $version;?><?php if ($high) {
                echo ' - ' . $high;
                     }?></a>
        </li>
        <?php
        //reset high
        $high = '';
    } elseif (!$high && !isset($info['norelease']) && strpos($version, 'beta') === false && strpos($version, 'rc') === false) {
        $high = $version;
    }
    //don't bother going past version 6.0.0
    if ($version == '6.0.0') {
        break;
    }
}

?>
    <li>
        <a href="http://geodesicsolutions.com/RELEASE_NOTES.php" onclick="window.open(this.href); return false;">
            Previous Releases
        </a>
    </li>
</ul>
</body>
</html> 