<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.09.0-79-gb63e5d8
##
##################################

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- ORDERS
menu_category::addMenuCategory('orders', $parent_key, 'Orders', 'fa-edit', '', '', $head_key);

    menu_page::addPage('orders_list', 'orders', 'Manage Orders', 'fa-edit', 'orders.php', 'OrdersManagement');
        menu_page::addPage('orders_list_order_details', 'orders_list', 'View Order', 'fa-edit', 'orders.php', 'OrdersManagement', 'sub_page');

if (geoPC::is_ent()) {
    menu_page::addPage('recurring_billing_list', 'orders', 'Manage Recurring Billing', 'fa-edit', 'recurring_billing.php', 'RecurringBillingManagement');
        menu_page::addPage('recurring_billing_details', 'recurring_billing_list', 'View Recurring Billing', 'fa-edit', 'recurring_billing.php', 'RecurringBillingManagement', 'sub_page');
}

    menu_page::addPage('orders_list_items', 'orders', 'Manage Items', 'fa-edit', 'items.php', 'OrderItemManagement');
        menu_page::addPage('orders_list_items_item_details', 'orders_list_items', 'View Order Item', 'fa-edit', 'items.php', 'OrderItemManagement', 'sub_page');
        menu_page::addPage('orders_list_items_item_unlock', 'orders_list_items', 'View Order Item', 'fa-edit', 'items.php', 'OrderItemManagement', 'sub_page');

    menu_page::addPage('admin_cart', 'orders', 'Create Order', 'fa-edit', 'cart.php', 'AdminCart');
        menu_page::addPage('admin_cart_select_user', 'admin_cart', 'Select User', 'fa-edit', 'cart.php', 'AdminCart');
        menu_page::addPage('admin_cart_delete', 'admin_cart', 'Delete Order', 'fa-edit', 'cart.php', 'AdminCart');
        menu_page::addPage('admin_cart_swap', 'admin_cart', 'Swap Cart Contents', 'fa-edit', 'cart.php', 'AdminCart');
        menu_page::addPage('admin_cart_edit_price', 'admin_cart', 'Edit Item Price', 'fa-edit', 'cart.php', 'AdminCart');
