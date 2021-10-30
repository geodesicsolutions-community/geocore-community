<?php

//addons/social_connect/info.php

# Facebook Connect
class addon_social_connect_info
{
    public $name = 'social_connect';
    public $title = 'Social Connect';
    public $version = '2.3.1';
    public $core_version_minimum = '17.01.0';
    public $description = 'Allows users to log into the site using social websites like Facebook.';
    public $author = 'Geodesic Solutions LLC.';
    public $auth_tag = 'geo_addons';
    public $author_url = 'http://geodesicsolutions.com';

    public $tags = array (
        'facebook_login_button',
        'facebook_session_profile_picture',
        'facebook_listing_profile_picture',
        //Still needs more testing, specifically for people with LOTS of friends
        //and needs to be optimized to work better when lots of friends or lots
        //of users use FB.
        //'facebook_session_app_friends',
    );

    public $listing_tags = array (
        'facebook_listing_profile_picture',
        );

    public $core_events = array (
        'display_login_bottom',
        'display_registration_code_form_top',
        'display_registration_form_top',
        'User_management_information_display_user_data',
        'Admin_site_display_user_data',
        'user_information_edit_form_display',
        'user_information_edit_form_update',
    );

    public $pages = array (
        'merge_accounts',
    );

    public $pages_info = array (
        'merge_accounts' => array ('main_page' => 'user_management_page.tpl', 'title' => 'Merge Accounts'),
    );
}

/**
 * Social Connect Changelog
 *
 * 2.3.1 - Geo 17.09.0
 *  - Fixed a bug that could cause new users to not be placed in the correct User Group
 *
 * 2.3.0 - Geo 17.04.0
 *  - Workaround an API change made by Facebook
 *
 * 2.2.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 2.1.6 - Geo 16.09.0
 *  - Fixed price plan expirations not being set on new user registration
 *
 * 2.1.5 - Geo 16.01.0
 *  - Default template/css changes to support new design
 *
 * 2.1.4 - Geo 7.6.0
 *  - Alter login call to conform to Facebook Platform v2.4 (must now explicitly request user's email address)
 *
 * 2.1.3 - Geo 7.4.5
 *  - Upgrade database structure to support new, longer facebook identifier tokens
 *
 * 2.1.2 - Geo 7.4.0
 *  - Fixed when FB account matches admin, it did not clear the FB user info resulting
 *    in not being logged in, but showing the FB profile
 *  - Updated FB api to 3.2.3 from https://github.com/facebook/facebook-php-sdk
 *  - Changes to fake the UA when it looks like it is using IE11, otherwise the
 *    sessions reset.  Without this, IE11 fails to log in with FB connect.  Hope
 *    this can someday (soon) be removed.
 *
 * 2.1.1 - Geo 7.3.2
 *  - Fix to properly check for suspended status, and not log in when suspended
 *
 * 2.1.0 - Geo 7.3.0
 *  - Updated Facebook SDK to latest available 3.2.2
 *  - When settings are saved, it does a test to see if app ID and secret
 *    appear to be correct
 *  - Fixed it trying to save user's country the old v6 way during user creation
 *
 * 2.0.1 - Geo 7.2.2
 *  - Integrated social connect addon with the JIT login process
 *
 * 2.0.0 - Geo 7.1.0
 *  - bumped to 2.0 because people always get confused once numbers start getting
 *    above 10
 *  - Changed tags to use {listing} tag
 *  - Updated tags to use new internal template loading from 7.1
 *
 * 1.0.10 - Geo 7.0.4
 *  - Improved handling of SSL for some server configurations
 *
 * 1.0.9 - Geo 7.0.3
 *  - Updated the main facebook tag to use &amp; so that it passes w3 validation.
 *
 * 1.0.8 - Geo 7.0.2
 *  - Updated the bundled Facebook API SDK library to the latest available.
 *  - Made change to facebook API SDK that makes it work on servers that give CURL error 77,
 *    hopefully this change will be made part of Facebook SDK.  Facebook bug:
 *    http://developers.facebook.com/bugs/413096665421696
 *
 * 1.0.7 - Geo 7.0.0
 *  - Compatibility changes for 7.0 licensing
 *
 * 1.0.6 - Geo 6.0.4
 *  - Added hook for when logging in, so bridges are notified when user logs in
 *    with facebook.
 *
 * 1.0.5 - Geo 6.0.4
 *  - Updated Facebook PHP SDK library to latest version
 *  - Add admin setting to turn on/off logging out of Facebook.  Some sites doing
 *    so causes endless re-direct, so need to be able to turn it off.  It defaults
 *    to be turned off.
 *
 * 1.0.4 - Geo 6.0.3
 *  - Fix to trim after filtering username so it uses "facebook_user" and not username that
 *    is only a space.
 *  - change to make it use first part of e-mail before the @ as "last resort" for
 *    username if no other info will work.
 *  - change the username "clean" to convert accents using built-in functionality,
 *    as suggested by client
 *
 * 1.0.3 - Geo 6.0.3
 *  - Fix to use FB username if that is available for the user
 *  - Clean the username, remove chars not allowed in main software
 *
 * 1.0.2 - Geo 6.0.3
 *  - Record the last login IP and time when user is logged in with FB.
 *
 * 1.0.1 - Geo 6.0.2
 *  - Changes to hide the settings when in demo mode
 *  - Updated the Facebook PHP SDK to latest 3.1.1
 *  - Few tweaks to make login work more consistently
 *  - Fix in base code to make login button work for brand new created session, so
 *    requires 6.0.2
 *  - Fix problem with where it goes after login, bug #258
 *  - Change logout to log out of FB instead of letting session clear, bug #270
 *  - Force FB connection when trying to look at my account or cart pages, bug #262
 *  - Make sure new registered user starts with account balance/subscription, bug #259
 *  - Fix problem when trying to force un-link FB connection
 *
 * 1.0.0 - Geo 6.0.0
 *  - Addon Created
 *
 */
