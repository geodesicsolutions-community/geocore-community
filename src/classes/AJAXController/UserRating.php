<?php

//UserRating.php
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
## ##    7.4beta2-7-g76b5461
##
##################################

if (class_exists('classes_AJAX') or die('NO ACCESS')) {
}

class CLASSES_AJAXController_UserRating extends classes_AJAX
{
    public function SetRating()
    {
        $db = DataAccess::getInstance();

        //full session setup, since this is ajax
        $session = geoSession::getInstance();
        $session->initSession();
        $from = $session->getUserId();

        $about = (int)$_POST['about'];
        $rating = (int)$_POST['newRating'];
        if (!$from || !$about || $rating < 1 || $rating > 5) {
            //something's broken or fishy. stop and do nothing.
            die("BAD DATA");
        }

        if (geoUserRating::setIndividualRating($about, $from, $rating)) {
            echo 'SAVED';
        }
    }
}
