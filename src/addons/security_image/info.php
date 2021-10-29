<?php

//addons/security_image/info.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.09.0-106-ge989d1f
##
##################################

# Security Image Addon

class addon_security_image_info
{
    //The following are required variables
    var $name = 'security_image';
    var $version = '3.6.0';
    var $core_version_minimum = '17.01.0';
    var $title = 'Security Image';
    var $author = "Geodesic Solutions LLC.";
    var $icon_image = 'menu_security_image.gif';
    var $description = 'Allows placement of a security image on the registration, login, and messaging pages.<br />
<br />
When enabled, this addon\'s settings are accessed through <em style="white-space:nowrap">Site Setup > Security Image</em><br /><br />
<strong>NEW in Security Image 2.0</strong>:  Admin settings re-designed, additional image customization abilities added (relies on supported features of GD library).';
    //used in referencing tags, and maybe other uses in the future.
    var $auth_tag = 'geo_addons';

    ##Optional Vars##
    //if these vars are included, they will be used.

    var $upgrade_url = 'http://geodesicsolutions.com/component/content/article/54-access-security/70-security-image.html?directory=64'; //[ Check For Upgrades ] link
    var $author_url = 'http://geodesicsolutions.com'; //[ Author's Site ] link
    var $info_url = 'http://geodesicsolutions.com/component/content/article/54-access-security/70-security-image.html?directory=64'; //[ More Info ] link

    //[ Tag Details ] link.  This is an example of linking relatively...
    //Note that you can link by relative location for any of the URL's...
    var $tag_info_url = 'index.php?mc=addon_security_image_admin&page=addon_security_image_tag_help';
    var $tags = array ('secure_image');
    var $core_events = array (
        'registration_check_info'
    );
    //internal use, set this to true to display more helpful info
    const DEBUG = false;

    const RECAPTCHA_API_SERVER = 'http://www.google.com/recaptcha/api';
    const RECAPTCHA_API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';
    const RECAPTCHA_UNSECURE_PUBLIC_KEY = '6LcAHrwSAAAAAH41KUiA20Was_GW_Q6iu6c9G967';
    const RECAPTCHA_UNSECURE_PRIVATE_KEY = '6LcAHrwSAAAAAGkZ2Da6zzzX4F3Xx8jc3gARBj8F';
}

/*
 * CHANGELOG for security image addon
 *
 * v3.6.0 -- REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * v3.5.1 -- Geo 16.01.0
 *  - Default text/template changes to support new design
 *
 * v3.5.0 -- Geo 7.4.5
 *  - Implement latest version of ReCAPTCHA, including new themes and the "No CAPTCHA" checkbox validation
 *
 * v3.4.5 -- Geo 7.4.0
 *  - Changes to stop using prototype on front side
 *
 * v3.4.4 -- Geo 7.3.0
 *  - Changes to use add_footer_html
 *
 * v3.4.3 -- Geo 7.2.5
 *  - Fixed ReCAPTCHA stealing keyboard focus when changing categories in the single-page listing process.
 *
 * v3.4.2 -- Geo 7.2.3
 *  - Fixed the recaptcha option to work when it is being displayed on
 *    AJAX call. (See bug 885)
 *
 * v3.4.1 -- Geo 7.1.0
 *  - Changed to not require security image on admin cart pages
 *  - "modernized" tag to work with new stuff for internal template loading
 *
 * v3.4.0 -- Geo 6.0.0
 *  - Changes for Smarty 3.0
 *  - Added Security Images to Listing Placement, Anonymous Listing Placement, and Forgot Password
 *  - Added ability to not show security images on messaging if the user is already logged in
 *  - Fixed an error that prevented the addon from being uninstalled
 *
 * v3.3.2 -- Geo 5.1.5
 *  - Surround the reCaptcha box with a div with inline-block set, so that it
 *    does not cause a newline in Chrome and IE
 *
 * v3.3.1 -- Geo 5.1.3
 *  - Fixed setting the font size to work properly.
 *
 * v3.3.0 -- Geo 5.1.2
 *  - Added ability to use recaptcha, requires 5.1.2 because of changes needed in base code.
 *
 * v3.2.1 -- Geo 5.0.0
 *  - Fixed a bug preventing the messaging forms from submitting
 *
 * v3.2.0 -- Geo 5.0.0
 *  - Applied changes for new design.
 *
 * v3.1.3 -- Geo 4.1.3
 *  - Fixed it to reference the file setting instead of hard-coding index.php
 *
 * v3.1.2 -- Geo 4.1.2
 *  - Fixed W3C compliance issues with in-line JS.
 *  - Fixed JS problems, some specific to certain browsers
 *
 * v3.1.1 -- Geo 4.0.9
 *  - Change security image to go through addon page system, no more license
 *    errors caused because it's being accessed from addons dir!
 *  - Changed all settings to use addon registry instead of site settings.
 *
 * v3.1.0 -- Geo 4.0.6
 *  - changelog creation
 *  - HTML moved from addon text into SMARTY templates
 */
//leave a blank line at the bottom, for Eclipse
