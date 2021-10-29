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

// ----------------- Design
menu_category::addMenuCategory('design', $parent_key, 'Design', 'fa-paint-brush', '', '', $head_key);

    menu_page::addPage('design_settings', 'design', 'Settings', 'fa-paint-brush', 'design.php', 'DesignManage');
        menu_page::addPage('design_clear_combined', 'design_settings', 'Clear Combined CSS & JS', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');

    menu_page::addPage('design_sets', 'design', 'Template Sets', 'fa-paint-brush', 'design.php', 'DesignManage');
        menu_page::addPage('design_sets_copy', 'design_sets', 'Copy Set', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_download', 'design_sets', 'Download Set', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_upload', 'design_sets', 'Upload Set', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_scan', 'design_sets', 'Re-Scan Template Attachments', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_export', 'design_sets', 'Export pre-5.0 design to template set', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_change_mode', 'design_sets', 'Change Design Mode', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_change_editing', 'design_sets', 'Change Template Sets Editing', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_create_main', 'design_sets', 'Create Main Template Set', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_import_text', 'design_sets', 'Import Text', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_sets_delete', 'design_sets', 'Delete Template Set', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');

    menu_page::addPage('design_manage', 'design', 'Template Manager', 'fa-paint-brush', 'design.php', 'DesignManage');
        menu_page::addPage('design_preview_file', 'design_manage', 'Preview', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_new_folder', 'design_manage', 'New Folder', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_new_file', 'design_manage', 'New File', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_upload_file', 'design_manage', 'Upload File', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_edit_file', 'design_manage', 'Edit', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_rename_file', 'design_manage', 'Rename/Move', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_delete_files', 'design_manage', 'Delete', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_download_file', 'design_manage', 'Download', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('design_copy_files', 'design_manage', 'Copy/Paste Files', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');

    menu_page::addPage('page_attachments', 'design', 'Page Attachments', 'fa-paint-brush', 'design.php', 'DesignManage');
        menu_page::addPage('page_attachments_edit', 'page_attachments', 'Edit', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('page_attachments_apply_defaults', 'page_attachments', 'Apply Default Attachment', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');
        menu_page::addPage('page_attachments_restore_template', 'page_attachments', 'Restored Template', 'fa-paint-brush', 'design.php', 'DesignManage', 'sub_page');

    menu_page::addPage('text_search', 'design', 'Site Text Search', 'fa-code', 'search_text.php', 'SearchText');
