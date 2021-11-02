<?php

//addons/example/info.php
# NOTE: If you are viewing the source, note that you can view phpdocs in the
# docs/ folder.
/**
 * Required file.  Used to register what this addon can do with the addon
 * system. Also holds information about this addon that may be viewed in
 * the addon management page in the admin.
 *
 * This is the only required file for an addon; the rest of the files
 * should only be used if the functionality provided by those files are needed.
 *
 * This file is included with each page load if the addon is installed
 * and enabled, so it needs to be kept "lean". It should only contain
 * the class and variables documented in this example. This is NOT the
 * place to put custom classes, functions, or large (in data size)
 * variables, as doing so will add more overhead to every page load.
 *
 * Remember to rename the class name, replacing "example" with
 * the folder name for your addon.
 *
 * Note that this version of the Example Addon is only compatible with
 * at least Geo 5.0, since stuff has changed since previous versions.
 *
 * @package ExampleAddon
 */


# Example Addon

# Note: PHPDocs can be found in docs/ folder.

/**
 * Required class for addons, this is the Addon information class, used by the addon system to determine what the addon
 * can do.  Also contains info about the addon that can be viewed in the addon management
 * page in the admin.
 *
 * @package ExampleAddon
 */
class addon_example_info
{
    #### Required Vars ####

    /**
     * Required, this must be the same as the addon's folder name, and is used
     * as part of the name for all classes throughout the addon.
     * Be sure to use something that is unique to prevent name conflicts.
     *
     * Required. NOT HTML Friendly. Must be exactly the same as the
     * folder named used for the addon.
     *
     * @var string
     */
    public $name = 'example';

    /**
     * Required, this version number is compared to the version held in the database
     * to determine whether the update script must be run.
     *
     * Required. NOT HTML friendly. Stored in database.  Used by addon
     * system for addon upgrades.
     *
     * @var string
     */
    public $version = '2.5.5';

    /**
     * Optional, if the Geodesic base software's version is not at least this number, the addon will not install/enable/upgrade
     *
     * If this is not present, the system will allow this addon to function on any version of the Geo software
     *
     * @since Geo version 4.0.5 -- this var will have no effect in versions before Geo 4.0.5.
     * @var string
     */
    public $core_version_minimum = '7.4beta1';

    /**
     * Required, the title of the addon that will be displayed in the menu
     * and on the addon management page.
     *
     * Required. HTML friendly. For display purposes only.
     *
     * @var string
     */
    public $title = 'Example Addon';

    /**
     * Required, the author, used in the addon management page.
     *
     * Required. HTML friendly. For display purposes only.
     *
     * @var string
     */
    public $author = "Geodesic Solutions LLC.";

    /**
     * Required, holds the content for the "Full Addon Description" on the addon
     * management page. Use this for special instructions and general
     * information about the addon.
     *
     * Required (even if empty string). HTML friendly. For display
     * purposes only.
     *
     * @var string
     */
    public $description = 'This is an example addon.  It can be used by developers as a starting
point when creating a new addon.<br /><br />
<strong>Notes:</strong><br />
- This addon <strong>will conflict with e-mail addon!</strong>  This is <strong>not a bug</strong>, it is designed to work this way to demonstrate the use of the $exclusive variable in info.php.
<br />
- Broken image - This addon will have a broken image.  This is also just to demonstrate a feature of the addon management system, to demonstrate how to specify an icon for the addon.  Since the icon image
does not actually exist, there is a broken image instead.  See info.php in the addon for more details.';

    /**
     * Required, used in referencing tags.  Also may have other uses in the future.
     *
     * Should be something unique, used by all of your addons.  Do NOT
     * use Geodesic Solutions' value of "geo_addons" as you may create
     * conflicts with future versions.
     *
     * For future compatibility, this should be safe to use as a file name.
     *
     * Required to ensure future compatibility. NOT HTML friendly. Used to
     * prevent name conflicts.
     *
     * @var string alpha-numeric, "-", and "_" chars only
     */
    public $auth_tag = 'geo_addons';


    #### Optional Vars ####

    //The following are not required, but if these vars exist, they will be used.

    /**
     * Optional, used on the addon management page and any admin pages. The
     * path is relative to this addon's folder.
     *
     * As an example, if this value is set to "icon.gif", the icon
     * used for this addon would be physically located at
     * "addons/example/icon.gif".
     *
     * Optional. NOT HTML friendly.  For display purposes only.  Used as part of "src" in an image tag.
     *
     * @var string
     */
    public $icon_image = 'menu_example.gif';


    /**
     * Optional, URL for upgrades link, used in link [ Check For Upgrades ].  If not used, the link will
     * not show for this addon.
     *
     * Optional. For display purposes only. Holds URL used in link.
     *
     * @var string
     */
    public $upgrade_url = 'https://geodesicsolutions.org';

    /**
     * Optional, URL for author site link, used in link [ Author's Site ].  If not used, the link will
     * not show for this addon.
     *
     * Optional. For display purposes only. Holds URL used in link.
     *
     * @var string
     */
    public $author_url = 'https://geodesicsolutions.org';

    /**
     * Optional, URL for more info link, used in link [ More Info ].  If not used, the link will
     * not show for this addon.
     *
     * Optional. For display purposes only. Holds URL used in link.
     *
     * @var string
     */
    public $info_url = 'https://geodesicsolutions.org';

    /**
     * Optional, URL for tag info link, used in link [ Tag Details ],  If not used, the link will not show for this addon.
     * In the example value, it uses a link relative to the admin,
     * linking to an admin page added by this addon.  Note that the use of a relative link is
     * for demonstration only, any URI can be used, and that use of a relative link can be
     * used for any of the URL variables, not just the tag info url.
     *
     * Optional. For display purposes only. Holds URL used in link.
     *
     * @var string
     */
    public $tag_info_url = 'index.php?mc=addon_example_admin&page=addon_example_tag_help';

    /**
     * Optional, Array of tag names.  Used to "register" certain tags to be used by this addon.
     * It should be an array of tag names.  Note that to have a tag replaced,
     * in the template it needs:
     *
     * template addon tag example:
     * {addon author='geo_addons' addon='example' tag='tag_name1'}
     *                   ||                ||            ||
     *               $auth_tag            $name        $tags[]
     *
     * If the tag name is not defined in the $tags array, it will not get used,
     * even if the tag replacement function exists in the tags.php file.
     *
     * Optional. Only needed if using addon tags. See {@link tags.php} for more info.
     *
     * @var array
     */
    public $tags = array (
        'tag_name1',
        'tag_name2');

    /**
     * Optional, array of tag names used for listing tags.  Used to "register" certain
     * listing tags to be used by this addon.  Note that to have a tag replaced,
     * in the template it needs:
     *
     * {listing addon='example' tag='listing_tag_example'}
     *                   ||                  ||
     *                 $name            $listing_tags[]
     *
     * If the tag name is not defined in the $listing_tags array, it will not
     * get used, even if the tag replacement function exists in the tags.php file.
     *
     * Optional.  Only needed if using the listing addon tags.  See {@link tags.php}
     * for more info.
     *
     * @var array
     */
    public $listing_tags = array (
        'listing_tag_example',
        );

    /**
     * Optional, Array of page names.  Used to "register" certain pages to be used by this addon.
     * It should be an array of page names.  The URL to one of these pages would be:
     *
     * index.php?a=ap&addon=example&page=page1 -- displays hello world page.
     *
     * Optional.  Only needed if using addon pages.  See {@link pages.php} for info.
     *
     * @var array
     */
    public $pages = array (
        'page1',
        'page2',
        //These 2 are so we can attach templates to pages to use when purchasing
        //an eWidget, they are not meant to be used to visit directly.
        'youAreCool',
        'almostFinished'
    );

    /**
     * Optional, Array of data arrays for each of the pages specified in the
     * $pages var (above).  This allows to specify info about each page, such
     * as what main_page template should be used from the default template set,
     * or what the page's "title" is, displayed in the admin panel for that
     * page.  Note that the title is able to have HTML in it.
     *
     * @var array Each element in the array should have a key matching one of the
     *   pages in the $pages var above, and the value should be array similar to:
     *   array ('main_page' => 'page_template_attachment.tpl', 'title' => 'Page Title in Admin')
     * @since Geo version 5.0.0
     */
    public $pages_info = array (
        'page1' => array ('main_page' => 'basic_page.tpl', 'title' => 'Example Page 1'),
        'page2' => array ('main_page' => 'basic_page.tpl', 'title' => 'Example Page 2'),
        'youAreCool' => array ('main_page' => 'cart_page.tpl', 'title' => 'You are Cool'),
        'almostFinished' => array ('main_page' => 'cart_page.tpl', 'title' => 'Almost Finished'),
    );

    /**
     * Optional, Array of core events (aka software hooks) for this addon.  Each
     * core event type works differently. See {@link util.php} for more
     * info and examples of how to use each core event.
     *
     * Core events so far:
     *
     * - filter -- type of core event, all core events prepended with filter_ act the same way.
     * - - filter_display_page
     * - - filter_display_image
     * - - filter_display_page_nocache -- Only in 3.1+, used in cache system.
     * - - filter_ssl_url_checks -- Only 4.0.4+ - filter an array, not a string
     * - - filter_geoFilter_replaceDisallowedHtml
     * - - filter_geoFilter_listingDescription
     * - - filter_geoFilter_listingShortenDescription
     * - - filter_email
     *
     * - email -- special core event, Sends an e-mail.  Soon to be deprecated.
     *
     * - notify -- type of core event, used for notification type events
     * - - notify_user
     * - - notify_user_remove
     * - - notify_Display_ad_display_classified_after_vars_set
     * - - notify_display_page
     * - - notify_geoListing_remove
     * - - notify_geoTemplate_loadTemplateSets
     * - - notify_new_bid_success
     * - - notify_image_insert
     * - - notify_image_remove
     * - - notify_ListingFeed_generateSql
     * - - notify_modules_preload
     * - - notify_geoPC_get_hash_types
     *
     * - errorhandle
     *
     * - app_bottom
     *
     * - auth -- type of core event, all core events prepended with auth_ act the same way.
     * - - auth_admin_login
     * - - auth_admin_display_page
     * - - auth_admin_update_page
     * - - auth_listing_edit
     * - - auth_listing_delete
     * - - auth_admin_user_login
     *
     * - overload -- type of core event, all core events prepended with overload_ act the same way.
     * - - overload_Notify_seller_notify_seller_
     * - - overload_Notify_friend_notify_friend_
     * - - overload_Site_display_image
     * - - overload_Browse_ads_display_browse_result - Browse_ads::display_browse_result()
     * - - overload_Browse_tag_display_browse_result - Browse_tag::display_browse_result()
     * - - overload_Search_classifieds_BuildResults - Search_classifieds::BuildResults()
     * - - overload_imagesOrderItem_processImages
     * - - overload_geoFilter_replaceDisallowedHtml
     * - - overload_geoFilter_listingDescription
     * - - overload_geoFilter_listingShortenDescription
     *
     * - session -- type of core event
     * - - session_create
     * - - session_touch
     * - - session_login
     * - - session_logout
     *
     * - user -- type of core event
     * - - user_register
     * - - user_edit
     * - - user_remove -- not implemented in software
     *
     * - registration -- type of core event, designed to allow adding additional
     *      data for registration information collected.
     * - - registration_add_field_display
     * - - registration_add_field_update
     * - - registration_add_variable
     * - - registration_check_info
     *
     * - misc -- Miscelanious core events
     * - - admin_update_insert_group
     * - - admin_display_page_attachments_edit_end
     * - - Browse_ads_display_browse_result_addRow
     * - - Browse_ads_display_browse_result_addHeader
     * - - Browse_tag_display_browse_result_addRow
     * - - Browse_tag_display_browse_result_addHeader
     * - - Browse_module_display_browse_result_addRow
     * - - Browse_module_display_browse_result_addHeader
     * - - Browse_ads_generate_query
     * - - Browse_featured_pic_generate_query
     * - - Browse_newest_ads_generate_query
     * - - Browse_tag_generate_query
     * - - Search_classifieds_search_form
     * - - Search_classifieds_generate_query
     * - - Search_classifieds_BuildResults_addHeader
     * - - Search_classifieds_BuildResults_addRow
     * - - my_account_home_add_box
     * - - User_management_information_display_user_data
     * - - User_management_information_display_user_data_plan_information
     * - - user_information_edit_form_display
     * - - user_information_edit_form_check_info
     * - - user_information_edit_form_update
     * - - my_account_links_add_link
     * - - geoFields_getDefaultLocations
     * - - geoFields_getDefaultFields
     * - - module_title_add_text
     * - - module_title_prepend_text
     * - - module_search_box_add_search_fields
     * - - admin_home_display_news
     * - - cron_close_listings_skip_listing
     * - - display_login_bottom
     * - - display_registration_code_form_top
     * - - display_registration_form_top
     * - - geoCategory_getListingCount
     * - - Admin_site_display_user_data
     * - - Admin_user_management_update_users_view
     * - - Admin_Group_Management_edit_group_display
     * - - Admin_Group_Management_edit_group_update
     *
     * Optional. Only needed if using any of the core events. See {@link util.php} for more info.
     *
     * @var array
     */
    public $core_events = array (
        //A normal addon would probably only use one or two of these, we are only
        //using all of them as a demonstration of how to do it.
        'filter_display_page',
        'filter_display_image',
        'filter_display_page_nocache',
        'filter_ssl_url_checks',
        'filter_geoFilter_replaceDisallowedHtml',
        'filter_geoFilter_listingDescription',
        'filter_geoFilter_listingShortenDescription',
        'filter_email',
        'filter_listing_placement_category_query',
        'email',
        'notify_user',
        'notify_user_remove',
        'notify_Display_ad_display_classified_after_vars_set',
        'notify_display_page',
        'notify_new_bid_success',
        'notify_image_insert',
        'notify_image_remove',
        'notify_ListingFeed_generateSql',
        'notify_modules_preload',
        'notify_sold_sign_status_changed',
        'notify_geoPC_get_hash_types',
        'errorhandle',
        'app_bottom',
        'auth_admin_login',
        'auth_admin_display_page',
        'auth_admin_update_page',
        'auth_listing_edit',
        'auth_listing_delete',
        'auth_admin_user_login',
        'overload_Notify_seller_notify_seller_',
        'overload_Notify_friend_notify_friend_',
        'overload_Site_display_image',
        'overload_Browse_ads_display_browse_result',
        'overload_Browse_tag_display_browse_result',
        'overload_Search_classifieds_BuildResults',
        'overload_imagesOrderItem_processImages',
        'overload_geoFilter_replaceDisallowedHtml',
        'overload_geoFilter_listingDescription',
        'overload_geoFilter_listingShortenDescription',
        'session_create',
        'session_touch',
        'session_login',
        'session_logout',
        'user_register',
        'user_edit',
        'user_remove',
        'registration_add_field_display',
        'registration_add_field_update',
        'registration_add_variable',
        'registration_check_info',
        'admin_update_insert_group',
        'admin_display_page_attachments_edit_end',
        'admin_category_manage_add_links',
        'admin_category_list_specific_icons',
        'Browse_ads_display_browse_result_addRow',
        'Browse_ads_display_browse_result_addHeader',
        'Browse_tag_display_browse_result_addRow',
        'Browse_tag_display_browse_result_addHeader',
        'Browse_ads_generate_query',
        'Browse_featured_pic_generate_query',
        'Browse_newest_ads_generate_query',
        'Browse_tag_generate_query',
        'Browse_module_display_browse_result_addRow',
        'Browse_module_display_browse_result_addHeader',
        'Search_classifieds_search_form',
        'Search_classifieds_generate_query',
        'Search_classifieds_BuildResults_addHeader',
        'Search_classifieds_BuildResults_addRow',
        'my_account_home_add_box',
        'User_management_information_display_user_data',
        'User_management_information_display_user_data_plan_information',
        'user_information_edit_form_display',
        'user_information_edit_form_check_info',
        'user_information_edit_form_update',
        'my_account_links_add_link',
        'geoFields_getDefaultLocations',
        'geoFields_getDefaultFields',
        'module_title_add_text',
        'module_title_prepend_text',
        'module_search_box_add_search_fields',
        'admin_home_display_news',
        'current_listings_add_action_button',
        'current_listings_end',
        'display_login_bottom',
        'display_registration_code_form_top',
        'display_registration_form_top',
        'geoCategory_getListingCount',
        'Admin_site_display_user_data',
        'Admin_user_management_update_users_view',
        'Admin_Group_Management_edit_group_display',
        'Admin_Group_Management_edit_group_update',
        'prevent_new_listing',
        'add_listing_icons',
        'use_listing_icons',
        'rewrite_single_url',
        'show_listing_alerts_table_headers',
        'show_listing_alerts_table_body',
        'display_add_listing_alert_field',
        'update_add_listing_alert_field',
        'delete_listing_alert',
        'check_listing_alert',
        'show_listing_alert_filter_data'
    );

    /**
     * Optional, If this associative array is set, is an array, and has a key for a core
     * event, other addons that also have exclusive set to true for that core
     * event cannot be enabled at the same time as this one.
     *
     * As an example, if an addon sends an e-mail, it would not want another
     * addon that also sends an e-mail to be enabled at the same time.
     * Otherwise, two e-mails would be sent.  However, if an
     * addon only logs the e-mails, it would be set to not exclusive
     * and allowed to be enabled at the same time as an exclusive email
     * addon.
     *
     * Optional. Only use this if you need to make a core event exclusive.
     *
     * @var Assoc array
     */
    public $exclusive = array (
    'filter_display_page' => false,
    'filter_display_image' => false,
    'email' => true,    //do not allow other addons with exclusive set for
                        //the e-mail core event to be enabled at the same
                        //time as this one.
    'errorhandle' => false, //does not matter if there are other addons that have
                            //exclusive set to true for errorhandle.
                            //NOTE: It is not required to set the index for a core event
                            //if it is false, it is done that way here for demonstration.

    //not all core events need to be specified here, if not set it defaults to false (not exclusive)
    //if there are no exclusive core events, the $exclusive var is not needed.
    );

    /**
     * Optional, if addon is enabled, at the time the enabled addons are being
     * initialized it will call this method (if it exists), and if it returns
     * false, it will not enable this addon.  If it returns false, no changes
     * are made in the database, it will only affect the current page load.
     *
     * Be sure to remove this method on addons that have no use for it (which
     * will be most addons), if this method does not exist, it will act normally.
     *
     * This functionality was added to allow commercial Addon developers to do
     * any license validation if they desire to do so, this would be the best
     * place to do licensing checks as failure would have no negative consequences
     * except that the addon would not be able to be used.
     *
     * @return bool Return true to allow this addon to be enabled for the
     *  current page load, or false to temporarily disable the addon for the
     *  current page load.
     * @since Geo version 4.0.6
     */
    public function enableCheck()
    {
        //Most addons to NOT need this method.
        //REMOVE this method if you have no use for it!

        //return true to allow addon to be enabled for this page load
        return true;

        /*
         * Tip for Commercial Addon Developers:  Use this method if you have a
         * way to somehow verify the license for the software and you encode
         * your addon PHP files.  We recommend placing the actual license checks
         * in a more "critical" file to make it a little more difficult to get
         * around the checks.
         */
    }
}

/**
 * Example Addon Changelog
 *
 * This is not a complete changelog, this is mostly for internal use.  In the
 * user manual, see Development > Changes to Note
 *
 * 2.5.5 - Geo 7.4beta1
 *  - Added new hook admin_category_manage_add_links and admin_category_list_specific_icons
 *
 * 2.5.4 - Geo 7.3.0
 *  - Fixes to a few core events to remove parameters no longer used.
 *  - New core events for allowing addons to interface with My Listing Filters
 *  - Added user_information_edit_form_check_info
 *  - Added docs on using _auto_add_head for tags (although this ability has been
 *    in the software for a while now, was previously un-documented)
 *
 * 2.5.3 - Geo 7.2.2
 *  - Core event notify_sold_sign_status_changed updated for being called when
 *    "sold out" buy-now-only auctions are marked sold.
 *
 * 2.5.2 - Geo 7.1.4
 *  - new core event: rewrite_single_url
 *
 * 2.5.1 - Geo 7.1beta4
 *  - new core event notify_geoPC_get_hash_types, core_prevent_listing
 *  - Removed old hook no longer used, listing_display_add_action_button
 *
 * 2.5.0 - Geo 7.1beta1
 *  - Changes to 2 core events, Browse_module_display_browse_result_addHeader
 *    and Browse_ads_display_browse_result_addHeader for changes in 7.1 to add
 *    list and gallery view.
 *  - Updates to document the new $listing_tags ability
 *  - New core events: current_listings_end, notify_sold_sign_status_changed
 *
 * 2.4.0 - Geo 7.0.3
 *  - Added new core event for email filter
 *  - Bumped version to 2.4.0, only because 2.3.10 would have been confusing.
 *
 * 2.3.9 - Geo 6.0.6
 *  - Added new parameter to getDisplayDetails() in order items
 *
 * 2.3.8 - Geo 6.0.4
 *  - Added new core event geoCategory_getListingCount
 *
 * 2.3.7 - Geo 6.0.3
 *  - Added new core events
 *
 * 2.3.6 - Geo 6.0.2
 *  - Added new core event notify_modules_preload
 *
 * 2.3.5 - Geo 6.0.0
 *  - Removed core event that has been removed from software
 *  - Added core event(s) for new version
 *  - $menuName now passed into admin's init_pages() method.
 *  - Updated docs on register_check_info to mention setting registerClass->api_error
 *  - Updated way RSS reader works, to use setTitle() instead of setting title
 *    parameter directly.
 *  - Updated order item and templates to use new $cart->getCommonTemplateVars()
 *  - Made price replacement work for 0 costs
 *  - Changes to order item for 6.0
 *
 * 2.3.4 - Geo 5.2.0
 *  - Changes to make eWidget work in admin panel cart
 *  - Improvements to tag docs
 *
 * 2.3.3 - Geo 5.1.4
 *  - Added new hooks from Geo 5.1.4
 *  - Added auction and featured bool vars available to core events
 *    Browse_ads_display_browse_result_addRow and Browse_ads_display_browse_result_addHeader
 *
 * 2.3.2 - Geo 5.1.2
 *  - Added new hooks from Geo 5.1.2
 *
 * 2.3.1 - Geo 5.1.1
 *  - Added new hooks added in 5.1.1
 *
 * 2.3.0 - Geo 5.1.0
 *  - Updated for new hooks and stuff in 5.1.0
 *
 * 2.2.1 - Geo 5.0.2
 *  - Added docs for new module title hook added in 5.0.2
 *
 * 2.2.0 - Geo 5.0.0
 *  - Added docs on new core events added in 5.0.0
 *  - Added use of $pages_info which holds basic information about each addon
 *    page.
 *
 * 2.1.0 - Geo 4.1.0
 *  - Added registration_check_info core event
 *  - Added auth_admin_user_login core event
 *
 * 2.0.2 - Geo 4.0.6
 *  - Added functionality for $info->enableCheck()
 * 2.0.1 - Geo 4.0.5
 *  - Added $core_version_minimum var and docs for it, for "minor" new
 *    functionality added.
 * 2.0.0 - Geo 4.0.0
 *  - First version using changelog block for Example addon
 *  - Added order item (the eWidget)
 *  - Added pages
 *  - Added new core events (too many to note here)
 *  - Added payment gateways
 *  - Added seller-buyer payment gateways
 *  - Added API
 *  - Made everything PHP5 (Geo 4.0 requires PHP 5.2) and removed php5 specific
 *    files.
 *
 */
