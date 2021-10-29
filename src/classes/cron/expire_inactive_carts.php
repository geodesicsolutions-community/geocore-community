<?php

//expire_inactive_carts.php
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
## ##    6.0.7-2-gc953682
##
##################################

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$this->log('Top of expire_inactive_carts!', __line__);

/*
 * NOTE: if there is ever a problem where a cart is not expired
 * but the order is activated, this will cause that order to be
 * removed, along with everything in the order.  In such a case,
 * fix the CAUSE (cart not being expired once order is active),
 * do NOT do anything silly like disable this cron job or something.
 */

//First, expire any anon carts that do not have session existing any more

$olderThanAnon = time() - $this->db->get_site_setting('session_timeout_client');
$olderThanUser = time() - $this->db->get_site_setting('cart_expire_user');

if ($olderThanUser === false) {
    //user expiration not set, set it to default of 1 week.
    $this->db->set_site_setting('cart_expire_user', 604800);
    $olderThanUser = 604800;
}

$this->log('Getting cart sessions, for anonymous last touched: ' . $olderThanAnon . ', for normal user last touched: ' . $olderThanUser, __line__);

$sql = "SELECT `id`, `order` FROM " . geoTables::cart . "
	WHERE (user_id=0 AND last_time < ?)";
$query_data = array($olderThanAnon);
if ($olderThanUser) {
    //only if NOT set to 0
    //NOTE: we always get rid of ANON expired carts, since their sessions have expired
    //there is just no way to even use them once the user's session has expired.
    $sql .= " OR (user_id != 0 AND last_time < ?)";
    $query_data [] = $olderThanUser;
}

$oldCarts = $this->db->GetAll($sql, $query_data);
if ($oldCarts === false) {
    $this->log("ERROR: Db query error, sql: $sql error: " . $this->db->ErrorMsg(), __line__);
    return false;
}

if (count($oldCarts)) {
    $this->log('Found ' . count($oldCarts) . ' old carts to remove, starting processing.', __line__);
    foreach ($oldCarts as $row) {
        //get the order and kill it
        if (intval($row['order'])) {
            $this->log('Removing order # ' . $row['order'] . ' attached to cart #' . $row['id'], __line__);

            if (!geoOrder::remove(intval($row['order']))) {
                //Error removing order, do not proceed with this cart
                $this->log('Order removal failed!  geoOrder::remove(' . $row['order'] . ') returned false.');
                continue;
            }
        } else {
            //order not set correctly, this shouldn't happen so let them know if it does.
            $this->log('Weird, there is no order for cart #' . $row['id'] . ', this could mean there is a problem as there should always be an order # for every cart.', __line__);
        }

        //remove the cart, let the geoCart class do the work for us
        geoCart::remove($row['id']);
    }
    $this->log('Finished removing old carts.', __line__);
} else {
    $this->log('Nothing to do, no old carts found.', __line__);
}

return true;
