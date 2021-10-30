<?php

// addons/enterprise_pricing/admin.php


# Example Addon

class addon_enterprise_pricing_admin extends addon_enterprise_pricing_info
{

    //open up access to admin pages unlocked by this addon
    function init_pages()
    {
        menu_page::addPage('pricing_new_price_plan', 'pricing', 'Add New Price Plan', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management'); //create new price plan
        menu_page::addPage('pricing_category_costs', 'pricing_price_plans', 'Category Specific Costs', 'fa-money', 'admin_price_plan_management_class.php', 'Price_plan_management', 'sub_page'); //category-specific pricing

        menu_page::addPage('users_group_add_plan', 'users_groups', 'Add Price Plan', 'fa-users', 'admin_group_management_class.php', 'Group_management', 'sub_page'); //add secondary price plan to group
        menu_page::addPage('users_new_group', 'users', 'Add New User Group', 'fa-users', 'admin_group_management_class.php', 'Group_management'); //add new user group
    }
}
