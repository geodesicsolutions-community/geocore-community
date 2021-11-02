<?php

//addons/discount_codes/info.php

# Discount Codes Addon

class addon_discount_codes_info
{
    //The following are required variables
    var $name = 'discount_codes';
    var $version = '2.3.0';
    var $core_version_minimum = '17.01.0';
    var $title = 'Discount Codes';
    var $author = "Geodesic Solutions LLC.";
    var $icon_image = 'menu_discount_codes.gif';
    var $description = 'This addon enables the use of discount codes that apply a percentage discount to a user\'s entire cart';
    var $auth_tag = 'geo_addons';

    const DISCOUNT_TABLE = '`geodesic_addon_discount_codes`';
    const DISCOUNT_GROUPS_TABLE = '`geodesic_addon_discount_codes_groups`';
}

/**
 * Discount code Changelog
 *
 * v2.3.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * v2.2.8 - Geo 16.01.0
 * - Fixed order items not reporting price correctly in some cases
 *
 * v2.2.7 - Geo 7.3.2
 * - Get rid of old calendar icons for date input
 *
 * v2.2.6 - Geo 7.0.1
 * - Added new parameter to getDisplayDetails() in order item
 *
 * v2.2.5 - Geo 6.0.0
 *  - Fix for new cart changes and recurring billing form
 *
 * v2.2.4 - Geo 6.0.0
 *  - Changes to work on Geo cart in 6.0, not backwards compatible
 *  - Changes to order items for 6.0
 *
 * v2.2.3 - Geo 6.0.0
 *  - Fixed problem with using group specific discount codes.
 *  - Fixed discount form to submit to correct location when in admin cart.
 *  - Changes for Smarty 3.0
 *
 * v2.2.2 - Geo 5.2.0
 *  - Changes to make discount codes work in admin panel cart
 *
 * v2.2.1 - Geo 5.1.3
 *  - Fixed issue preventing discount code from being usable on normal order.
 *  - Fixed when using discount code with special HTML chars
 *  - Made it save discount_id in data so we can get stats easier, and set that
 *    value for already existing order items that have items that match
 *  - Added status for each discount code, including list of users that have and have not
 *    used the applicable discount code.
 *  - Fixed issue with adding/editing codes in non Enterprise editions.
 *
 *
 * v2.2.0 - Geo 5.1.2
 *  - Added ability for discount codes to apply to recurring billing items.
 *  - Ability to specify start and end times and only allow code usage during those times
 *  - Ability to limit each code by specific user group
 *
 * v2.1.9 - Geo 5.1.2
 *  - Fixed display of discount on billing page, or other places outside the main cart
 *
 * v2.1.8 - Geo 5.1.0
 *  - Fixed missing pagination on "listings using this discount code" page
 *
 * v2.1.7 - Geo 5.0.3
 *  - Changes for updated license system
 *
 * v2.1.6 - Geo 4.1.3
 *  - Changed reference to use classifieds_file_name so that SSL is not "lost"
 *    by the update discount code form.
 *
 * v2.1.5 - Geo 4.0.9
 *  - Fixed fatal error introduced by 2.1.4
 *
 * v2.1.4 - Geo 4.0.8
 *  - restored ability to tally the number of listings a given discount code has been used for
 *
 * v2.1.3 - Geo 4.0.7
 *  - Fix applied for addon license checks
 *
 * v2.1.2 - Geo 4.0.6
 *  - Added license checks
 *
 * v2.1.1 - Geo 4.0.3
 *  - implemented "cross debit" system:
 *    the cross-debit system allows a user to place a listing using a discount code that is "linked" to another user (the "target" user)
 *    when this happens, the cost of the base listing (but not any Extras) comes out of the target user's tokens or account balance
 *
 *    to enable cross-debiting, the "undocumented" site setting 'joe_edwards_discountLink' must be turned on
 *    and the discount code to be used must then have a target user id 'attached' to it through the admin
 *
 * v2.1.0 - Geo 4.0.1
 *  - re-implemented email "hijacking" via beta switch: joe_edwards_discountLink
 *
 * v2.0.2 - Geo 4.0.0
 *  - Made it not display discount code entry on any page except for main cart view
 *  - Fixed it to not use fromDB and toDB when getting/setting discount code, and make
 *    upgrade script fix values that were already double-encoded
 *
 * v2.0.1 - Geo 4.0.0RC11
 *  - started using changelog
 *  - Updated order items to use getDisplayDetails ($inCart)
 *
 */
