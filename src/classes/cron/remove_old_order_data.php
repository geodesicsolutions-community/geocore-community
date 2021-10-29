<?php

//remove_old_order_data.php
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
$this->log('Top of remove_old_order_data!', __line__);

//figure out how old are we talkin
$age = $this->db->get_site_setting('order_data_age');

if (!$age) {
    $this->log('Removing old orders is disabled (time is set to 0), not removing any old orders.', __line__);
    return true;
}
//now find orders that are older than that
$age = geoUtil::time() - $age;

$allOrders = $this->db->GetAll("SELECT `id` FROM " . geoTables::order . " WHERE `created` < $age");
if (count($allOrders)) {
    //theres work to be done
    $this->log('Found ' . count($allOrders) . ' old orders to be removed.  Working on it.', __line__);
    foreach ($allOrders as $row) {
        geoOrder::removeData($row['id']);
    }
    $this->log('Finished removing all orders.', __line__);
} else {
    $this->log('No old orders found.', __line__);
}

//now remove old order items (to get rid of any order items that may have been stranded some how)
$allItems = $this->db->GetAll("SELECT `id` FROM " . geoTables::order_item . " WHERE `created` < $age AND `order`='0'");
if (count($allItems)) {
    //theres work to be done
    $this->log('Found ' . count($allItems) . ' old ghost items to be removed.  Working on it.', __line__);
    foreach ($allItems as $row) {
        geoOrderItem::removeData($row['id']);
    }
    $this->log('Finished removing all old items.', __line__);
} else {
    $this->log('No old ghost items found to remove.', __line__);
}

//Do "orphaned item cleanup" - look for any old order items where the order ID is set, but order is not valid, and set order to 0 so it will be removed next round.
$allItems = $this->db->GetAll("SELECT `id`, `order` FROM " . geoTables::order_item . " WHERE `created` < $age AND `order`!='0'");
if (count($allItems)) {
    //theres work to be done
    $this->log('Found ' . count($allItems) . ' old items that think they have orders, going to check each one.  Working on it.', __line__);
    foreach ($allItems as $row) {
        $item = geoOrderItem::getOrderItem($row['id'], true);
        if (is_object($item) && !$item->getOrder()) {
            //this item has no real order, so fix it
            $this->log('Order item ID#' . $row['id'] . ' has order set to ' . $row['order'] . ' but that order could not be retrieved, so setting order to 0.', __line__);
            $item->setOrder(0);
            $item->save();
            //now, the item will be removed next time this is called since the order is set to 0.
        } elseif (!is_object($item)) {
            //not a valid item some how??!?
        }
        unset($item);
    }
    $this->log('Finished checking for items with dead orders.', __line__);
} else {
    $this->log('No old items with orders found to check.', __line__);
}

return true;
