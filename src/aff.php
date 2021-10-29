<?php

//aff.php
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
## ##    7.5.3-36-gea36ae7
##
##################################

define('IN_AFF', 1);

//initialize everything.
require_once('app_top.aff.php');

if (!geoPC::is_ent()) {
    exit;
}

$_REQUEST['a'] = isset($_REQUEST['a']) ? intval($_REQUEST['a']) : 0;
switch ($_REQUEST["a"]) {
    case 2:
        //display a classified
        include(CLASSES_DIR . "browse_display_ad.php");
        $browse = new Display_ad($_REQUEST["page"], $_REQUEST["b"]);

        //$browse->classified_close($db);
        $listingId = (int)$_REQUEST['b'];
        $displaySuccess = false;
        if ($browse->classified_exists($listingId)) {
            $displaySuccess = $browse->display_classified($listingId);
        }
        if (!$displaySuccess) {
            $browse->browse_error();
        }

        break;

    case 3:
        //send communication
        include(CLASSES_DIR . "user_management_communications.php");
        $communication = new User_management_communications($db, $language_id, $affiliate_id, $product_configuration);
        if (($_REQUEST["b"]) && ($_REQUEST["d"])) {
            if (!$communication->send_communication($_REQUEST["b"], $_REQUEST["d"])) {
                $communication->site_error();
            } elseif (!$communication->communication_success($db)) {
                    $communication->site_error();
            }
        } elseif ($_REQUEST["b"]) {
            //display the home page
            if (!$communication->send_communication_form($_REQUEST["b"], $_REQUEST["c"], $affiliate_id)) {
                $communication->site_error();
            }
        } else {
            $communication->site_error();
        }
        break;

    case 5:
        //display a category
        //b will contain the category id
        include(CLASSES_DIR . "browse_affiliate_ads.php");
        $browse = new Browse_ads($affiliate_id, $language_id, $_REQUEST["b"], $_REQUEST["page"], 0, $affiliate_group_id);
        //$browse->classified_close($db);
        if ($affiliate_id) {
            if ($_REQUEST["b"]) {
                if (!$browse->browse($db, $_REQUEST["b"], $_REQUEST["c"])) {
                    $browse->browse_error();
                }
            } else {
                if (!$browse->browse($db, 0, $_REQUEST["c"])) {
                    $browse->browse_error();
                }
            }
        } else {
            $sql_query = "select classifieds_url from geodesic_classifieds_configuration";
            $get_url_result = $db->Execute($sql_query);
            if ($debug) {
                echo $sql_query . " in case 5<BR>\n";
            }
            if (!$get_url_result) {
                //echo $sql_query."<br>\n";
                return false;
            }
            $show_url = $get_url_result->FetchRow();
            header("Location: " . $show_url["classifieds_url"] . "?" . $_SERVER["QUERY_STRING"]);
        }
        exit;
        break;

    case 12:
        //notify a friend
        include(CLASSES_DIR . "browse_notify_friend.php");
        $browse = new Notify_friend($db, 0, $language_id, 0, 0, 0, $affiliate_id, $product_configuration);
        if (($_REQUEST["b"]) && ($_REQUEST["c"])) {
            if ($browse->verify_notify_friend($db, $_REQUEST["b"], $_REQUEST["c"])) {
                if ($browse->notify_friend_($db, $_REQUEST["b"], $_REQUEST["c"])) {
                    $browse->notify_success($db, $_REQUEST["b"]);
                } else {
                    $browse->site_error();
                }
            } elseif (!$browse->notify_friend_form($db, $_REQUEST["b"])) {
                $browse->site_error();
            }
        } elseif ($_REQUEST["b"]) {
            $browse->notify_friend_form($db, $_REQUEST["b"]);
        } else {
        }
        exit;
        break;

    case 13:
        //send a message to seller
        if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"]))) {
            if (($_REQUEST["b"]) && ($_REQUEST["c"])) {
                include_once(CLASSES_DIR . "browse_notify_seller.php");
                $browse = new Notify_seller($user_id, $language_id, 0, 0, 0, 0, $product_configuration);
                if ($browse->notify_seller_($_REQUEST["b"], $_REQUEST["c"])) {
                    $browse->notify_seller_success($_REQUEST["b"]);
                } elseif (!$browse->send_a_message_to_seller_form($_REQUEST["b"])) {
                    $browse->site_error();
                }
            } elseif ($_REQUEST["b"]) {
                include_once(CLASSES_DIR . "browse_notify_seller.php");
                $browse = new Notify_seller($user_id, $language_id, 0, 0, 0, 0, $product_configuration);
                $browse->send_a_message_to_seller_form($_REQUEST["b"]);
            } else {
                include_once(CLASSES_DIR . "browse_ads.php");
                $browse = new Browse_ads($user_id, $language_id, 0, $_REQUEST["page"]);
                if (!$browse->main($db)) {
                    $browse->browse_error();
                }
            }
        } else {
            include_once(CLASSES_DIR . "browse_ads.php");
            $browse = new Browse_ads($user_id, $language_id, 0, $_REQUEST["page"]);
            if (!$browse->main($db)) {
                $browse->browse_error();
            }
        }
        break;

    case 14:
        //display a classified in print friendly format
        if ($_REQUEST["b"]) {
            include(CLASSES_DIR . "browse_display_ad.php");
            $browse = new Display_ad($_REQUEST["page"], $_REQUEST["b"]);
            if ($browse->classified_exists($_REQUEST["b"])) {
                if (!$browse->display_classified($_REQUEST["b"], false, false, true, null, true)) {
                    $browse->site_error();
                }
            } else {
                $browse->site_error();
            }
        } else {
            //display the home page
            include(CLASSES_DIR . "browse_ads.php");
            $browse = new Browse_ads($user_id, $language_id, $_REQUEST["b"], $_REQUEST["page"]);
            if (!$browse->browse($db, $_REQUEST["b"], $_REQUEST["c"])) {
                $browse->site_error();
            }
        }
        break;

    case 15:
        //display a classified images in full size format
        if ($_REQUEST["b"]) {
            include(CLASSES_DIR . "browse_display_ad_full_images.php");
            $browse = new Display_ad_full_images($db, $user_id, $language_id, 0, $_REQUEST["page"], $_REQUEST["b"], $affiliate_id, $product_configuration, $affiliate_group_id);
            if (!$browse->display_classified_full_images($_REQUEST["b"])) {
                $browse->site_error();
            }
        } else {
            //display the home page
            include(CLASSES_DIR . "browse_ads.php");
            $browse = new Browse_ads($user_id, $language_id, $_REQUEST["b"], $_REQUEST["page"]);
            if (!$browse->browse($db, $_REQUEST["b"], $_REQUEST["c"])) {
                $browse->site_error();
            }
        }
        break;

    case 19:
        //search
        include(CLASSES_DIR . "search_class.php");
        $search_the_classifieds = new Search_classifieds($db, $language_id, $affiliate_id, $_REQUEST["c"], 0, 0, 0, 0, $product_configuration);
        if ($_REQUEST["b"]) {
            $search_the_classifieds->search($_REQUEST["b"], $affiliate_id);
            if (!$search_the_classifieds->search_form($db, $_REQUEST["c"], $affiliate_id)) {
                $search_the_classifieds->site_error();
            }
        } else {
            //show the edit userdata form
            if (!$search_the_classifieds->search_form($db, $_REQUEST["c"], $affiliate_id)) {
                $search_the_classifieds->site_error();
            }
        }
        exit;
        break;

    default:
        include(CLASSES_DIR . "browse_affiliate_ads.php");
        //echo "calling from default<BR>\n";
        $_REQUEST['b'] = isset($_REQUEST['b']) ? intval($_REQUEST['b']) : 0;
        $_REQUEST['page'] = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
        $_REQUEST['c'] = isset($_REQUEST['c']) ? intval($_REQUEST['c']) : 0;
        $browse = new Browse_ads($affiliate_id, $language_id, $_REQUEST["b"], $_REQUEST["page"], 0, $affiliate_group_id);
        $browse->browse($db, $_REQUEST["b"], $_REQUEST["c"]);
        exit;
} //end of switch ($_REQUEST["a"])

require GEO_BASE_DIR . 'app_bottom.php';
