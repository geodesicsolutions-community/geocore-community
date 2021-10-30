<?php

//addons/storefront/info.php



# Storefront Addon
class addon_storefront_info
{

    var $name = 'storefront';
    var $version = '2.4.4';
    var $core_version_minimum = '17.01.0';
    var $title = 'Storefront';
    var $author = "Geodesic Solutions LLC.";
    var $description = 'This is the Storefront Addon.';
    var $auth_tag = 'geo_addons';

    var $icon_image = 'images/menu_storefront.gif';
    var $upgrade_url = 'http://geodesicsolutions.com/component/content/article/53-added-value/59-storefront.html?directory=64';
    var $author_url = 'http://geodesicsolutions.com';
    var $info_url = 'http://geodesicsolutions.com/component/content/article/53-added-value/59-storefront.html?directory=64';

    var $core_events = array (
        'Browse_ads_display_browse_result_addHeader',
        'Browse_ads_display_browse_result_addRow',
        'Browse_module_display_browse_result_addRow',
        'Browse_module_display_browse_result_addHeader',
        'Search_classifieds_BuildResults_addHeader',
        'Search_classifieds_BuildResults_addRow',
        'notify_Display_ad_display_classified_after_vars_set',
        'User_management_information_display_user_data_plan_information',
        'client_menu',
        'tags_user_management_home_menu',
        'my_account_home_add_box',
        'my_account_links_add_link',
        'addon_SEO_rewriteUrl',
        'my_account_admin_options_display',
        'my_account_admin_options_update',
        'admin_display_page_attachments_edit_end',
        'module_title_add_text',
        'geoFields_getDefaultFields',
        'notify_user_remove'
    );
    var $pages = array (
        'list_stores',
        'home',
        'control_panel'
    );
    var $tags = array (
        'client_menu',
        'control_panel_link',
        'storefront_name',
        'list_stores_link'
    );

    public $listing_tags = array (
        'storefront_link',
        );

    var $pages_info = array (
        'list_stores' => array ('main_page' => 'basic_page.tpl', 'title' => 'List Storefronts'),
        'home' => array ('main_page' => 'Storefront_Templates/Default.tpl', 'title' => 'User Storefront Pages',
                            'alternate_templates' => array(
                                'Storefront_Templates/Canopy_Black.tpl',
                                'Storefront_Templates/Canopy_Blue.tpl',
                                'Storefront_Templates/Canopy_Gray.tpl',
                                'Storefront_Templates/Canopy_Green.tpl',
                                'Storefront_Templates/Canopy_Lite_Blue.tpl',
                                'Storefront_Templates/Canopy_Red.tpl',
                                'Storefront_Templates/Skyline.tpl',
                            )),
        'control_panel' => array('main_page' => 'basic_page.tpl', 'title' => 'User Storefront Control Panel'),
    );

    //TABLES for internal user
    const SUBSCRIPTIONS_TABLE = "`geodesic_addon_storefront_subscriptions`";

    public function __construct()
    {
        //add listing details sub-template pages based on what product this is
        if (geoMaster::is('classifieds')) {
            $this->pages[] = 'classifieds_details_sub_template';
            $this->pages_info['classifieds_details_sub_template'] = array (
                'main_page' => 'listing_classified.tpl',
                'title' => 'Classified Details {main_body} sub-template'
            );
        }
        if (geoMaster::is('auctions')) {
            $this->pages[] = 'auctions_details_sub_template';
            $this->pages_info['auctions_details_sub_template'] = array (
                'main_page' => 'listing_auction.tpl',
                'title' => 'Auction Details {main_body} sub-template'
            );
        }
    }
}

/**
 * Storefront Changelog
 *
 * 2.4.4 - Geo 18.02.0
 *  - Improved responsiveness and accessibility of sorting custom pages/categories on touch devices
 *
 * 2.4.3 - Geo 17.12.0
 *  - Show a friendlier error message when invalid data given to Contact forms
 *  - Make sure the link to a user's Storefront in his profile page doesn't show when it shouldn't
 *  - Fixed a case where disabling the Storefront Subscription plan item would still allow free-for-all storefronts to be used
 *
 * 2.4.2 - Geo 17.09.0
 *  - Fixed State Filter dropdown on List Stores page not always showing all available states
 *  - Fixed Sort Order being different when set to 0 versus when unspecified
 *
 * 2.4.0 - Geo 17.05.0
 *  - Fixed broken links to storefronts from search results
 *
 * 2.4.0 - REQUIRES 17.01.0
 *  - Fixed a missing text label in the My Account Info page
 *  - Implemented new admin design
 *
 * 2.3.4 - Requires Geo 16.09.0
 *  - Softened template text to localization db
 *
 * 2.3.3 - Geo 16.07.0
 *  - Fixed fatal error in contact form
 *  - Repaired ability to submit custom pages with WYSIWYG turned off
 *  - glyphicon-move no longer missing after editing category name or custom page
 *
 * 2.3.2 - Geo 16.03.0
 *  - Fixed missing character in userCP template selection
 *
 * 2.3.1 - Geo 16.02.1
 *  - Corrected a regression in the plan item settings template
 *
 * 2.3.0 - Geo 16.02.0
 * - Fixed order item not reporting price correctly in some cases
 * - Switched "browsing" template to use main software's "grid view" for consistency
 * - Substantial changes throughout, to support new design
 *
 * 2.2.1 - Geo 7.6.2
 *  - Fixed Unlimited-duration listings not appearing in Storefront
 *  - Fixed default storefront data not being initialized when using the new "everyone gets a storefront" feature
 *  - Hid subscription info from My Account Information page when using the new "everyone gets a storefront" feature
 *  - respond to core event at admin-user-delete by removing data about the to-be-deleted user from storefront db tables
 *
 * 2.2.0 - Geo 7.6.0
 *  - Fixed style-less login page showing when trying to view a storefront listing with must-be-logged-in on
 *  - Added a feature to make surefronts available automatically to all users in a given price plan, without need for a subscription
 *
 * 2.1.9 - Geo 7.5.2
 *  - Fixed weird SEO redirect bug
 *  - Fixed broken storefront links in browse results
 *  - Fixed "unlimited duration" listings not appearing in storefront browsing
 *
 * 2.1.8 - Geo 7.5.0
 *  - Improved RWD display of storefront logos in a couple of places
 *
 * 2.1.7 - Geo 7.4.5
 *  - Fixed a case where a user who at one time had a recurring subscription that expired would be unable to buy a new one.
 *  - Fixed an incomplete subscription renewal link appearing when it shouldn't
 *
 * 2.1.6 - Geo 7.4.4
 *  - Fixed List Stores page showing the wrong page title
 *  - Fixed users being able to purchase a recurring subscription while already having one
 *
 * 2.1.5 - Geo 7.4.3
 *  - Fixed storefront contact form "extra fields" not processing correctly.
 *  - Fixed missing "return false" on add/remove WYSIWYG link
 *
 * 2.1.4 - Geo 7.4.0
 *  - Removed use of CJAX altogether
 *  - Converted all PrototypeJS to jQuery
 *
 * 2.1.3 - Geo 7.3.5
 *  - Fixed "No Reserve" icon appearing for "Buy Now Only" auctions
 *  - Correct height scaling when editing categories having subcategories
 *  - Corrected user page content being urldecoded without the corresponding encode
 *  - Fixed old logos not being deleted from the filesystem
 *
 * 2.1.2 - Geo 7.3.4
 *  - Fixed a bug that could sometimes cause listings to not appear in storefront browsing
 *  - Updated link to user's storefront from his My Account Info page to be SEO-rewritten when applicable
 *
 * 2.1.1 - Geo 7.3.2
 *  - fix for editor height setting no longer used causing page editor box to look funky
 *
 * 2.1.0 - Geo 7.3.0
 *  - Version bump that should have happened with rc2, to push CSS updates
 *
 * 2.0.5 - Geo 7.3rc2
 *  - Changes to allow not adding the li tags around the list stores link, to work
 *    with the new design.
 *  - Added {add_footer_html} to control panel category/page scripts, to defer loading it until prototype is present, when using the defer option
 *  - Updated addon to use RWD
 *
 * 2.0.4 - Geo 7.2.6
 *  - Added functionality for a core event that was supposed to be used for modules but never written into the addon
 *
 * 2.0.3 - Geo 7.2.5
 *  - Prevent "contact us" form from breaking on user's edit
 *  - Fix listing display not using category-specific Fields to Use
 *  - Make SEO re-written URL use URL encode for titles if in a RSS feed.
 *
 * 2.0.2 - Geo 7.2.3
 *  - Fix missing City data on List Stores page
 *
 * 2.0.1 - Geo 7.2.0
 *  - Add missing upgrade path
 *
 * 2.0.0 - Geo 7.2beta1
 *  - Added the ability for users to create a level of subcategories for each main category in their stores.
 *
 * 1.9.6 - Geo 7.1.3
 *  - Change to make sure stuff is done correctly when copying a listing
 *  - Fixed upgrade to include versions 1.0.12 and 1.0.13.  Done post-7.1.3 but
 *    does not justify addon version bump.
 *
 * 1.9.5 - Geo 7.1.0
 *  - Compatibility changes for 7.1.0, which are backwards compatible with 7.0
 *  - Using new {listing} tag for some of the listing info, and text moved
 *  - Now uses "display in..." locations for where to show the link
 *  - Organized addon text into sections
 *  - Added "My Account" button to all Storefront Control Panel pages (for consistency with other My Account pages)
 *  - Reduced default dimensions of List Stores images by a little over half
 *
 * 1.9.4 - Geo 7.0.3
 *  - Fixed a Stack Overflow in IE.
 *  - Fixed Save/Cancel buttons sometimes being hidden when editing pages in StoreCP
 *
 * 1.9.3 - Geo 7.0.2
 *  - List Stores page now works correctly with new Regions
 *  - Fixed state/country not showing in browsing
 *
 * 1.9.2 - Geo 7.0.1
 *  - Add more protection to prevent duplicate logo filenames across users
 *  - Don't 404 when trying to show a storefront that hasn't been created yet when using SEO URLs and a username containing special characters
 *  - Added new parameter to getDisplayDetails() in order items
 *  - Move "Display Link" settings to Fields to Use (for consistency with other, similar settings)
 *
 * 1.9.1 - Geo 7.0.0
 *  - Fixed a rare bug that could cause stores to be created with invalid SEO urls
 *  - Changes to use new region format for displaying country / state
 *
 * 1.9.0 - Geo 6.0.4
 *  - Fixed a bug that allowed a user to select a trial period multiple times
 *  - Added a template for emails sent by the Contact form
 *
 * 1.8.11 - Geo 6.0.3
 *  - Prevent a PHP Warning on traffic page
 *  - Fixed issue with showing storefront link in search results when link showing
 *    is not specifically enabled for any categories
 *  - Fixed list stores and category re-written links to not re-write in event
 *    that there are extra params, to prevent those params from getting lost.
 *  - Allow storefront browse listings page to handle optional fields of type 'cost' and 'date' correctly
 *
 * 1.8.10 - Geo 6.0.2
 *  - Restored missing pagination to list-of-listings page
 *  - Fixed table sort links linking to wrong page
 *  - Fixed a Fatal Error when purchasing a new "trial" storefront subscription
 *
 * 1.8.9 - Geo 6.0.0
 *  - Changes to make storefront subscription price able to be edited in admin cart
 *  - Changes for Smarty 3.0
 *  - Made $storefront_id set in storefront pages for access by templates
 *  - Fixed a bug where the SEO addon would errantly rewrite "sort" links in browsing tables
 *  - Moved list of storefront trials used into an addon table (instead of base geodesic_userdata table)
 *  - Made the Restore Default Pages control panel button appear even when pages already exist
 *  - Order item changes for 6.0
 *  - Integrating with Geographic Navigation to allow those filters to apply to storefront list
 *
 * 1.8.7 - Geo 5.2.1
 *  - Fixed a bug that made store subscriptions expire earlier than they should have (grace period backwards)
 *
 * 1.8.6 - Geo 5.2.0
 *  - Changes to make storefront subscription work in admin panel cart
 *
 * 1.8.5 - Geo 5.1.4
 *  - Fixed a bug that could cause listings to not appear in storefronts if the site's sort order was set to one of the "ending soonest" variants
 *  - Fixed a bug that caused the "sold" image not to appear on classifieds marked as sold when it should.
 *  - Changes to preserve sub-domain currently being used for geographic navigation
 *
 * 1.8.4 - Geo 5.1.3
 *  - Fixed a bug where the Previous/Next Listing buttons could pull listings from other stores
 *
 * 1.8.3 - Geo 5.1.2
 *  - Fixed search results to not print a blank column when the storefront results column isn't in use
 *  - Fixed it to set "page" class before loading modules, something required to properly load modules.
 *  - Change to subscription order item to allow discounts to apply
 *
 * 1.8.2 - Geo 5.1.1
 *  - Allow translation of emails sent from Contact forms
 *
 * 1.8.1 - Geo 5.1.0
 *  - Fix bug preventing translation of 'Stores' link tab
 *
 * 1.8.0 - Geo 5.0.3
 *  - Filter descriptions by using the same code as the base software, for consistency
 *  - Text in the User Control Panel is now added to the database for translation
 *  - Newsletter subjects are now specialchardecoded properly
 *  - Fixed issue where display ad template assigned to storefront wasn't used
 *  - Changes for updated license system
 *
 * 1.7.5 - Geo 5.0.2
 *  - Fixed issue with editing pages, where the text in the textarea was not
 *    properly escaped, resulting in weird issues in certain cases.
 *  - Added back the [add/remove editor] link to edit page and newsletter WYSIWYGs.
 *  - Fixes for W3C compliance
 *  - added ability for title module to create/display the <title> in storefront pages
 *  - improved logic for when to show storefront links during a search
 *  - Made upload logo detect file mime type "new" way
 *
 * 1.7.4 - Geo 5.0.1
 *  - Fixed CSS for control panel
 *
 * 1.7.3 - Geo 5.0.0
 *  - add tag for stores tab on front page
 *
 * 1.7.2 - Geo 5.0.0RC2
 *  - using new CSS for 'no active stores' message on list all stores page
 *
 * 1.7.1 - Geo 5.0.0RC1
 *  - Added the $pages_info var which specifies info on the addon's pages.
 *  - Made the upgrade allow the overall system to handle copying over the default template.
 *
 * 1.7.0 - REQUIRES 4.2.0beta or higher
 *  - New storefronts will have some pages and a category pre-populated (controllable via templates/default_pages/)
 *  - Added ability to use a page other than the main category as initial landing page for a storefront
 *  - List All Stores page now has an SEO-capable url, as well as a new page of options in the Admin
 *  - Changes for file-based templates
 *  - added default storefront logo
 *  - Applied new design template files
 *
 * 1.6.0 - Geo 4.1.3
 *  - Added ability to modify listing details templates independently of the main software
 *  - Changed title to simply "Storefront".
 *  - Got rid of admin page for "tags" - it was ugly and out of date.
 *  - Fixed previous/next ad buttons linking outside of storefront
 *
 * 1.5.1 - Geo 4.1.2
 *  - Now requires at least 4.1.1 so we don't worry about backwards compatibility
 *  - Added ability to cancel recurring billing for storefront subscriptions.
 *  - removed cjax initialization that wasn't actually doing anything
 *  - fixed formatting of storefront link column when link not present
 *  - allow a single email address to register for multiple newsletters
 *
 * 1.5.0 - Geo 4.1.0
 *  - Added ability for subscriptions to be recurring.  Changes "should" be
 *    backwards-compatible with previous Geo versions.
 *  - Added new column to subscriptions table for recurring billing.
 *  - Added cron job to delete expired storefront subscriptions, and allow for calls
 *    to recurring billing right before deletion.
 *  - Fixed it so that it shows storefront subscription add button even if cart
 *    session does not exist currently for the user.
 *  - In admin on subscription choices, made it display links to all the price
 *    plan settings to make things easier, if main software is at least 4.1.0
 *  - Fixed browsing hooks ignoring category-specific display link settings
 *  - cleaned up alerts for subscribing to a newsletter
 *  - newsletter added cookie now works independantly for each store
 *  - fixed Enter button doesn't submit newsletter subscribe form correctly in IE
 *  - fixed listing 'entry date' fields not being populated
 *  - added core event for My Account Home settings (won't be available for use until Geo 4.1, but no harm in having the listener here now)
 *
 * 1.4.0 - Geo 4.0.9
 *  - Fixed URL re-write to get certain titles correctly (such as storefront category)
 *  - Fixed storefront category to be child of reverse auctions as well
 *  - added upgrade catch-all for fixing 1.2/1.3 database mess
 *
 * 1.3.6 - Geo 4.0.8
 *  - fixed reference to old username to link correctly (fix wrong url with Object in URL)
 *
 * 1.3.5 - Geo 4.0.8
 *  - got rid of references to old stores.php in favor of new, SEO-rewritable URL format
 *  - fixed edit storefront category not saving the right category
 *
 * 1.3.4 - Geo 4.0.7
 *  - added custom "storefront name" field, which can affect the URL of the store when using SEO
 *  - Fix applied for addon license checks
 *
 * 1.3.3 - Geo 4.0.6
 *  - Fixed SEO URLs, made it integrate with SEO
 *  - added a missing text entry to database  (no internal version bump this time)
 *  - restore switches to disallow traffic reports and newsletters
 *  - fixed "store off" switch not actually turning the store off
 *  - restricted editing whether a subscription period is a Trial or not, because of the way used trials are stored in the db
 *  - add ability to send newsletter emails as HTML
 *  - add "preview" link to control panel header
 *  - Made it check license
 *
 * 1.3.2 - Geo 4.0.5
 *  - Fixed fatal error introduced in 1.3.0 with the new storefront control panel.
 *  - Fixed link to use proper setting, before it broke on sites that had SSL
 *    URL set but SSL turned off, it still used the SSL URL setting.
 *  - Fixed a bug that caused newly subscribed store owners to be unable to set "customization" settings
 *
 * 1.3.1 - Geo 4.0.4
 *  - Fixed display of (!STOREFRONT_LINK!) to work properly.
 *
 * 1.3.0 - Geo 4.0.4
 *  - moved "storefront manager" functionality into a page in the My Account section
 *  - added Admin controls to constrain image sizes in list stores page
 *  - added ability for Admin to manipulate a user's storefront expiration date
 *
 * 1.2.1 - Geo 4.0.1
 *  - fixed a bug in the upgrade that caused logos to disappear
 *
 * 1.2.0 - Geo 4.0.0
 *  - Added new text
 *  - Made link show in my account links
 *  - Added ability to specify store's logo's dimensions on the "list all stores" page
 *  - Made compatible with my account links "cart" section (and more new text)
 *
 * 1.1.1 - Geo 4.0.0RC11
 *  - Added new text
 *  - Internal version bump so new text gets added to development DBs
 *  - Changed get/set of the plan item "enabled" setting to use getEnabled and setEnabled,
 *      since that is now a built-in var name
 *  - links to "renew storefront subscription" will now go away
 *      if user is inelligible to buy a store subscription
 *
 * 1.1.0 - Geo 4.0.0RC11
 *  - fixed prices displaying as 0 in store lists
 *  - Fixed totals on reports to be correct.
 *  - Fixed the strip slashed templates to only strip slashes if
 *      really needed.
 *  - Stoped using geoFilter::url() since that has been removed.
 *  - Fixed storefront manager to save location of manager on page properly
 *  - Added text and used text, for step labels in cart concerning storefront
 *      order items.
 *  - Listing tables now respect the default-sort-order admin settings
 *  - Updated order items to use getDisplayDetails ($inCart)
 *  - Added hooks for new My Account stuff
 *
 * 1.0.13 - Geo 4.0.0RC10
 *  - fixed pre/post currency not appearing in Price column of listing tables
 *  - Moved User_management_information_display_user_data to
 *      storefront_subscription for changes made to how that page does things
 *  - fixed non-owners unable to subscribe to store newsletter
 *
 * 1.0.12 - Geo 4.0.0RC9
 *  - First version using changelog block for Storefront addon
 *  - Added text for page title & buttons for subscription renewal page
 *  - Fixed storefront templates to strip slashes from them, during update
 *  - Change tag used in my account to say my storefront
 *  - Added ability to change storefront category when editing listing
 *
 */
