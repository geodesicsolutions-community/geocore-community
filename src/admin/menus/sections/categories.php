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

// ----------------- CATEGORIES
menu_category::addMenuCategory('categories', $parent_key, 'Categories', 'fa-folder-open', '', '', $head_key);

    menu_page::addPage('category_config', 'categories', 'Manage Categories', 'fa-folder-open', 'categories.php', 'CategoriesManage');
        menu_page::addPage('category_manage', 'category_config', 'Manage Category', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_create', 'category_config', 'Add Category', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_edit', 'category_config', 'Edit Category', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_create_bulk', 'category_config', 'Bulk Add Categories', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_delete', 'category_config', 'Delete Category', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_edit_bulk', 'category_config', 'Mass Edit Category', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_enabled', 'category_config', 'Enable/Disable Category', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_move', 'category_config', 'Move Categories', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_copy', 'category_config', 'Copy Categories', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_durations', 'category_config', 'Category Specific Durations', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
            menu_page::addPage('category_durations_delete', 'category_durations', 'Delete Duration', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_templates', 'category_config', 'Category Specific Templates', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('categories_questions', 'category_config', 'Category Questions', 'fa-folder-open', 'admin_category_questions_class.php', 'Admin_category_questions', 'sub_page');
            menu_page::addPage('categories_questions_add', 'categories_questions', 'New Category Question', 'fa-folder-open', 'admin_category_questions_class.php', 'Admin_category_questions', 'sub_page');
            menu_page::addPage('categories_questions_edit', 'categories_questions', 'Edit Category Question', 'fa-folder-open', 'admin_category_questions_class.php', 'Admin_category_questions', 'sub_page');
            menu_page::addPage('categories_questions_delete', 'categories_questions', 'Delete Category Question', 'fa-folder-open', 'admin_category_questions_class.php', 'Admin_category_questions', 'sub_page');
        menu_page::addPage('category_copy_parts', 'category_config', 'Copy Category Attachments', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');
        menu_page::addPage('category_rescan_listings', 'category_config', 'Reset Listing Breadcrumbs', 'fa-folder-open', 'categories.php', 'CategoriesManage', 'sub_page');

    menu_page::addPage('dropdowns', 'categories', 'Pre-Valued Dropdowns', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions');
