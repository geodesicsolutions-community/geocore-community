<?php

//addons/attention_getters/info.php

# Attention Getters Addon

class addon_attention_getters_info
{
    //The following are required variables
    public $name = 'attention_getters';
    public $version = '3.1.0';
    public $core_version_minimum = '17.01.0';
    public $title = 'Attention Getters';
    public $author = "Geodesic Solutions LLC.";
    public $icon_image = "menu_attn_getter.gif";
    public $description = 'This addon enables the use of attention getters.  This addon is compatible
	with Geo 4.0+.';
    public $auth_tag = 'geo_addons';
}
/*
 * CHANGELOG - Attention Getters
 *
 * v3.1.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * v3.0.0 - Geo 16.01.0
 *  - New attention getters for the new design
 *  - Fixed order item not reporting price correctly in some cases
 *
 * v2.1.8 - Geo 7.3.1
 *  - Made it still send js / css on combined step so it still loads for specific
 *    categories
 *
 * v2.1.7 - Geo 7.3.0
 *  - Fixed image display when adding listing in admin using create order
 *
 * v2.1.6 - Geo 7.0.1
 *  - Added new parameter to getDisplayDetails() in order item
 *
 * v2.1.5 - Geo 6.0.4
 *  - Implementing changes needed to comply with bug 346, which includes changes
 *    that rely on 6.0.4, so upping the min version as well.
 *
 * v2.1.4 - Geo 6.0.0
 *  - Changes needed for Smarty 3.0
 *  - Fixed a bug that would cause the attention getter image to not appear on the final cart summary page during a listing renewal where the selection was not changed
 *  - Made price replacement work for 0 costs
 *  - Updates for order item in 6.0
 *
 * v2.1.3 - Geo 5.1.4
 *  - Change to order item to "clean it up" some.
 *
 * v2.1.2 - Geo 5.0.3
 *  - Changes needed for updated licensing system.
 *
 * v2.1.1 - Geo 5.0.1
 *  - Make attention getters aware of 'directory_listing' listing type
 *
 * v2.1.0 - Geo 5.0.0
 *  - Fixed broken link and missing image in choices template
 *  - Template for attention getter selection impoved for use in new design
 *
 * v2.0.0 - Geo 4.1.3
 *  - Added a bunch of new attention getter images
 *  - All images now stored in directory /addons/attention_getters/images/
 *  - Attention getters not added into database until addon is installed.
 *  - now using a SMARTY template to build the attention getter choices
 *
 * v1.2.7 - Geo 4.0.7
 *  - Fix applied for addon license checks
 *
 * v1.2.6 - Geo 4.0.6
 *  - Check the license attached
 *
 * v1.2.5 - Geo 4.0.4
 *  - Made it so that if the setting for whether to show attention getters is
 *    turned off, it does not make the extras page display when it doesn't need
 *    to.
 *
 * v1.2.4 - Geo 4.0.3
 *  - Added missing error message
 *
 * v1.2.3 - Geo 4.0.0RC11
 *  - fixed typos in the admin text tool
 *  - Updated order items to use getDisplayDetails ($inCart)
 *
 * v1.2.2 - Geo 4.0.0RC10
 *  - fixes for changes to how order items work
 *  - initial changlog creation
 */
//leave whitespace at the end of this, or Eclipse dies
