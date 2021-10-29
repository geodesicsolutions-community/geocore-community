<?php

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
## ##    16.09.0-79-gb63e5d8
##
##################################

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- PRICING
if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
    menu_category::addMenuCategory('pricing', $parent_key, 'Pricing', 'fa-money', '', '', $head_key);

        menu_page::addPage('pricing_price_plans', 'pricing', 'Price Plans Home', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management');
            menu_page::addPage('pricing_edit_plans', 'pricing_price_plans', 'Edit Price Plan', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
            menu_page::addPage('pricing_delete_plans', 'pricing_price_plans', 'Delete Price Plan', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
            menu_page::addPage('pricing_increments', 'pricing_price_plans', 'Pricing Increments', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
            menu_page::addPage('pricing_lengths', 'pricing_price_plans', 'Listing Durations', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
                menu_page::addPage('pricing_lengths_add', 'pricing_lengths', 'Add', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
                menu_page::addPage('pricing_lengths_delete', 'pricing_lengths', 'Delete', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
            menu_page::addPage('listing_subscription_periods', 'pricing_price_plans', 'Listing Subscription Periods', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');
            menu_page::addPage('pricing_final_fees', 'pricing_price_plans', 'Pricing Final Fees', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page');

            menu_page::addPage('pricing_items', 'pricing_price_plans', 'Plan Items', 'fa-money', 'price_plan_items.php', 'PricePlanItemManage', 'sub_page');



    if (geoPC::is_print()) {
    }
}
