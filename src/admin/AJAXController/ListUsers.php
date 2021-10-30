<?php

//ListUsers.php


/**
 * This file shows how to write server-side code for AJAX requests
 * Use this file as a template for new
 *
 * AJAX requests should be sent to admin/AJAX.php in the following form:
 *   admin/AJAX.php?controller=Example&action=foo&data=oof
 *
 * This request will do the following:
 *  1. Include AJAXController/Example.php which defines the
 *      ADMIN_AJAXController_Example class.
 *  2. Creates an object from ADMIN_AJAXController_Example.
 *  3. Passes $GET, minus controller and action, to the 'foo' method
 *      of ADMIN_AJAXController_Example.
 *
 * See the admin_AJAX::dispatch() function in AJAX.php for more details.
 *
 */

// DON'T FORGET THIS
if (!class_exists('admin_AJAX')) {
    exit;
}


/**
 *           DEFAULT VALUES FOR DATA
 *  data[0] = '`geodesic_userdata`.username';   // sorting style;
 *  data[1] = 'DESC';       // sorter order;
 *  data[2] = 25;           // number to show;
 *  data[3] = 0;            // page;
 */
class ADMIN_AJAXController_ListUsers extends admin_AJAX
{

    function getUsers($data)
    {
        // Get a $db object
        $db = true;
        include GEO_BASE_DIR . "get_common_vars.php";

        $site = Singleton::getInstance('admin_site');
        $sort = $data['order'];
        $sql = "SELECT * FROM " . $db->geoTables->userdata_table . ", " . $db->geoTables->user_groups_price_plans_table . ", " . $db->geoTables->logins_table . " 
				WHERE " . $db->geoTables->userdata_table . ".id = " . $db->geoTables->user_groups_price_plans_table . ".id 
				AND " . $db->geoTables->userdata_table . ".id = " . $db->geoTables->logins_table . ".id 
				AND " . $db->geoTables->userdata_table . ".id != 1 ";

        $theData = Ajax::decodeJSON($data['data']);

        if ($theData[2] && !$theData[3]) {
            $limit = 'LIMIT 0,' . $theData[2];
        } elseif ($theData[2] && $theData[3]) {
            $limit = 'LIMIT ' . (($theData[3] - 1) * $theData[2]) . ',' . $theData[2];
        } elseif (!$theData[2] && $theData[3]) {
            $limit = 'LIMIT ' . (($theData[3] - 1) * 25) . ',25';
        } else {
            $limit = 'LIMIT 0,25';
        }

        if ($theData[0] && $theData[1]) {
            $order = 'ORDER BY ' . $theData[0] . ' ' . $theData[1];
        } elseif ($theData[0] && !$theData[1]) {
            $order = 'ORDER BY ' . $theData[0] . ' ASC';
        } elseif (!$theData[0] && $theData[1]) {
            $order = 'ORDER BY ' . $db->geoTables->userdata_table . '.username ' . $theData[1];
        } else {
            $order = 'ORDER BY ' . $db->geoTables->userdata_table . '.username ASC';
        }

        $rs = $db->Execute($sql . $order . ' ' . $limit);
        if (!$rs) {
            //echo $db->ErrorMsg();
        } elseif ($rs->RecordCount() > 0) {
            $send .= "";
            //$send .= $sql.$order.' '.$limit;
            $verify_icon = '';
            if ($db->get_site_setting('verify_accounts')) {
                $txt = $db->get_text(true, 59);
                $verify_icon = " <img src=\"../" . geoTemplate::getUrl('', $txt[500952]) . "\" alt='' />";
            }

            $site->row_count = 0;

            //special case to not show "suspended" for anonymous user
            $anonR = geoAddon::getRegistry('anonymous_listing');
            if ($anonR) {
                $anonUser = $anonR->anon_user_id;
            }


            while ($row = $rs->fetchRow()) {
                $site->row_count++;
                //$user_status = $site->get_current_status($db,$row['id']);
                $user_status = (($row['status'] == 1) ? "active" : "suspended");
                if ($row['id'] == $anonUser) {
                    $user_status = 'System User';
                }
                $verified = '';
                if ($verify_icon && $row['verified'] == 'yes') {
                    $verified = $verify_icon;
                }
                $send .= "<tr class=\"" . $site->get_row_color() . "\">
							<td class=\"small_font\"><a href=\"index.php?mc=users&page=users_view&b={$row['id']}\">{$row['username']}</a>{$verified}</td>
							<td class=\"small_font\"><a href=\"index.php?mc=users&page=users_view&b={$row['id']}\">" . stripslashes($row['lastname']) . "</a></td>
							<td class=\"small_font\"><a href=\"index.php?mc=users&page=users_view&b={$row['id']}\">" . stripslashes($row['firstname']) . "</a></td>
							<td class=\"small_font center\">" . $user_status . "</td>";
                if (geoMaster::is('classifieds')) {
                    $send .= "
							<td class=\"small_font center\"><a href='index.php?mc=pricing&page=pricing_edit_plans&g=" . $row['price_plan_id'] . "'>" . $site->get_price_plan_name($db, $row['price_plan_id']) . "</a></td>";
                }
                if (geoMaster::is('auctions')) {
                    $send .= "
							<td class=\"small_font center\"><a href='index.php?mc=pricing&page=pricing_edit_plans&g=" . $row['auction_price_plan_id'] . "'>" . $site->get_price_plan_name($db, $row['auction_price_plan_id']) . "</a></td>";
                }
                $send .= "
							<td class=\"small_font center\">" . ($row['date_joined'] ? date("M d, Y", $row['date_joined']) : '-') . "</td>
							<td class=\"small_font nowrap\" style='text-align: center;'>
								" . geoHTML::addButton('view', "index.php?mc=users&page=users_view&b={$row['id']}");

                if ($row['id'] != $anonUser) {
                    //don't show "remove" button for the anonymous user
                    $send .= geoHTML::addButton('remove', "index.php?mc=users&page=users_remove&b={$row['id']}", false, '', 'mini_cancel');
                }
                $send .= "</td></tr>";
            }
        } else {
            $send = "<tr><td colspan=\"8\" class='page_note_error'>No users currently in the database.</td></tr>";
        }
        echo $send;
        //return true;
    }

    function getNumUsers($data)
    {
        $theData = Ajax::decodeJSON($data['data']);

        $sql = "SELECT COUNT(*) AS total_users FROM " . geoTables::userdata_table . "," . geoTables::user_groups_price_plans_table .
                " WHERE " . geoTables::userdata_table . ".id = " . geoTables::user_groups_price_plans_table . ".id and " . geoTables::userdata_table . ".id != 1 ";
        $users = DataAccess::getInstance()->GetOne($sql);


        $page = ($theData[3]) ? $theData[3] : 1;
        $pageSize = $theData[2];

        $first_user = (($page - 1) * $pageSize) + 1;
        $last_user = ($page * $pageSize);

        if ($last_user > $users) {
            $last_user = $users;
        }
        $send = "Displaying " . $first_user . "-" . $last_user . " of " . $users;
        echo $send;
    }

    function getPages($data)
    {

        $sql = "SELECT COUNT(*) AS total_users FROM " . geoTables::userdata_table . "," . geoTables::user_groups_price_plans_table .
                " WHERE " . geoTables::userdata_table . ".id = " . geoTables::user_groups_price_plans_table . ".id and " . geoTables::userdata_table . ".id != 1 ";
        $users = DataAccess::getInstance()->GetOne($sql);

        $theData = Ajax::decodeJSON($data['data']);

        $pageSize = $theData[2];
        if ($pageSize) {
            $pages = ceil($users / $pageSize);
        } else {
            $pages = ceil($users / 25);
        }

        $page = ($theData[3]) ? $theData[3] : 1;

        $link = "javascript:geo_getPage(";
        $postLink = ");";
        if ($pages > 1) {
            $pagination = geoPagination::getHTML($pages, $page, $link, '', $postLink, false, false);
        }

        echo $pagination;
    }

    public static function getJavascript()
    {
        return file_get_contents("js/ListUsers.js");
    }
}
