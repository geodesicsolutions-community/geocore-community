<?php

//addons/example/util.php
/**
 * Optional file.  Used for core events. Also a good place to
 * put custom functions that are used by your addon but not used
 * by the addon system directly.
 *
 * Remember to rename the class name, replacing "example" with
 * the folder name for your addon.
 *
 * @author Geodesic Solutions, LLC
 * @package ExampleAddon
 */

/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2013 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/

##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    2.5.3-11-gbaacf2d
##
##################################

# Example Addon

/**
 * It should already be included, but just to make sure since we extend the
 * example info class, require it here.
 */
require_once ADDON_DIR . 'example/info.php';

/**
 * This is where to put functions that the main part of the addon will use.
 *
 * Also, if this is attached to any "core events", as defined in info.php,
 * a function for each core event is needed.  The requirements of each core
 * event are different, as described by the function specific to that core event.
 *
 * Note that if the core event is not "registered" in
 * {@link addon_example_info::$core_events}, the functions
 * for that core event will not ever get used.
 *
 * We've made it extend the info class so we can get information easily.
 *
 * @package ExampleAddon
 */
class addon_example_util extends addon_example_info
{
    #### Internal Methods - Not used by system ####

    /**
     * This is an example of how to use addon settings using an addon registry
     *
     * It doesn't really do anything..
     */
    public function useAddonRegistryExample()
    {
        //Do you need to get some simple setting?  Use an addon registry like
        //demonstrated below:

        $settingsRegistry = geoAddon::getRegistry('example');

        //A registry can contain settings, with values that can be a string,
        //array, int, etc. - pretty much any data type except for object.

        //TIP: Try to avoid using a registry to save complex data.  It is best
        //suited for "setting = value" type settings.  It can handle arrays but
        //avoid using it as a shortcut save method for something that should
        //really be saved in it's own DB table or cache file.

        //Here's a fancy way to get a setting:
        $mySetting = $settingsRegistry->mySetting;
        //here's another way, works the same:
        $mySetting = $settingsRegistry->get('mySetting');

        //another way to get a setting, where you define what it is set to if
        //the setting is currently not set at all:
        $my_setting_array = $settingsRegistry->get('setting_array', array('an array'));

        //now $my_setting_array is set to array('an array') if that setting was
        //not previously set.

        //See documentation in geoRegistry class in the
        //classes/php5_classes/Registry.class.php file.

        //Oh and the way to save something if you didn't bother looking at
        //that geoRegistry class:  2 ways:

        $settingsRegistry->mySetting = 'value';
        //or
        $settingsRegistry->set('mySetting', 'value');

        //don't forget to save the registry when you make changes on it
        $settingsRegistry->save();
    }

    /**
     * This function actually does something.  It's used by our eWidget order
     * item to get the page id to use.
     * @param string $pageName
     * @return string
     */
    public function getPageId($pageName)
    {
        $page_id = "addons/example/{$pageName}";

        return $page_id;
    }

    #### Core Events -- Used by system ####

    #### Filter core events ####
    /**
     * Function called at end of $site_class->display_page() (if using db based
     * templates) or as template pre-compile filter, or in chunks for cacheing.
     * It is passed the full page text and is expected to return "manipulated"
     * text.
     *
     * Note that other addons might manipulate the page text before
     * or after this function is called in this addon.  The addons are
     * called according to their order in the admin.
     *
     * Note:  Text will probably be sent through in "chunks" and the results
     * may be cached.  If you need this to be run for the full page text only,
     * and cannot have the output filtered, use {@link addon_example_util::core_filter_display_page_nocache()}
     * instead.
     *
     * @param String $full_text full text of the entire page
     * @return String Text for the page to display.
     */
    public function core_filter_display_page($full_text)
    {
        //see PHPDocs for this function for documentation on requirements.

        //this is just example addon, don't need to manipulate the text...
        return $full_text;
    }

    /**
     * Function called at end of $site_class->display_image.  It is
     * passed the image tag and is expected to return the "manipulated" tag.
     *
     * Note that other addons might manipulate the image tag before
     * or after this function is called in this addon.  The addons are
     * called according to their order in the admin.
     *
     * @param string $image_tag
     * @return string Manipulated text for image tag.
     */
    public function core_filter_display_image($image_tag)
    {
        //see PHPDocs for this function for documentation on requirements.

        //this is just example addon, don't need to manipulate the text...
        return $image_tag;
    }

    /**
     * Function called at end of $site_class->display_page (when using db-based
     * templates) or as template post-filter (if using file based templates)
     * It is  passed the full page text and is expected to return "manipulated"
     * text.  The output of this addon is not cached.
     *
     * Note that other addons might manipulate the page text before
     * or after this function is called in this addon.  The addons are
     * called according to their order in the admin.
     *
     * Using this instead of the {@link addon_example_util::core_display_page()}
     * core event will potentially slow down page loads.  This function
     * is always called when displaying any page, even if the page is not cached.
     *
     * Note that both this, and the normal filter_display_page are both called on
     * each page load, the difference is that the results of this filter is not
     * recorded in the cache system, and that this will be getting the entire page
     * all at once instead of in chunks.
     *
     * @param string $full_text full text of the entire page
     * @return string Text for the page to display.
     * @since 3.1.0
     */
    public function core_filter_display_page_nocache($full_text)
    {
        //see PHPDocs for this function for documentation on requirements.

        //this is just example addon, don't need to manipulate the text...
        return $full_text;
    }

    /**
     * Function called in app_top.main.php - this allows your addon to specify
     * additional "checks" for if the page should be in SSL mode or not, or
     * optionally do it's own checks if there are checks needed that can't
     * be done by looking at the get vars.
     *
     * Note that this only happens if the admin has turned on the setting
     * "Force SSL URL" in "Site Setup > General Settings"
     *
     * In this case, you are filtering an array of "checks", not a string. Or
     * boolean true if an addon before this one returned true (see below for
     * what this means)
     *
     * The structure of the array is as follows:
     *
     * array (
     *  array (
     *      'get_key1' => 'get_var',
     *      'get_key2' => 'get_var2'
     *  )
     *  array (
     *      'get_key1' => 'get_var3'
     *  )
     * )
     *
     * If the above array was returned, that would mean that if the url ends in:
     *
     * ?get_key1=get_var&get_key2=get_var2...
     * OR
     * ?get_key1=get_var3...
     *
     * The above would be considered URL's that need to be viewed in SSL mode.
     *
     * Note that the URL can have additional vars as well, for example the
     * following URL would also match:
     * ?get_key1=get_var3&additional_test=additional_value
     *
     * If a value is set to PHP NULL then that means the var should not be
     * set in the URL at all, for instance:
     * array ('a' => 4, 'b' => null)
     * would mean ?a=4 matches, but NOT ?a=4&b=123 since the b value is set.
     *
     * If you return boolean true, that indicates you did your own checks and
     * your addon says that the current page being viewed should be viewed in
     * SSL mode.
     *
     * @param array|bool $sslChecks An array of arrays of checks, as described
     *  above, or bool true if another addon used that special case.
     * @return array|bool Array following the same structure, with additional
     *  checks added, or possibly some of the existing keys altered/removed if
     *  desired.  OR boolean true if the var passed in is true, or if this addon
     *  does it's own checks and says that the current page should be viewed
     *  in SSL mode
     * @since Geo Version 4.0.4
     */
    public function core_filter_ssl_url_checks($sslChecks)
    {
        //see PHPDocs for this function for documentation on requirements.

        if ($sslChecks === true) {
            //An addon that was called before this one, has already returned
            //true, meaning that the other addon says the current URL should
            //be viewed in SSL mode. So to be nice, we'll just pass the "true"
            //value along.

            return $sslChecks;
        }
        //change this number to 1 or 2 to demonstrate the different ways...
        $check = 0;
        //We can do 1 of 2 things:

        if ($check == 1) {
            //1: We could add our own checks to the array:
            //Make all the "user management" pages use SSL
            $sslChecks[] = array ('a' => 4);
        } elseif ($check == 2) {
            //2: We could do our own checks..  This should be used for special
            //cases only, where the 1st method just won't work.

            if (strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
                //Just a silly check, we are saying if there is no "www." in the
                //url, then it should use SSL mode.
                $sslChecks = true;
            }
        }
        //note that without changing anything here, $sslChecks is not modified.
        return $sslChecks;
    }

    /**
     * Function called at end of geoFilter::replaceDisallowedHtml()
     *
     * Filter the HTML
     *
     * @param string $html
     * @return string Filtered text, or original text if no filtering is needed.
     * @since Geo Version 4.0.4
     */
    public function core_filter_geoFilter_replaceDisallowedHtml($html)
    {
        //This is called on most, if not all, user input for listing details.
        //If you wanted to remove something from the listing details at the
        //point the listing is being placed, this would be a good way to do it.

        //FILTER Text here.
        $filtered_html = $html;

        return $filtered_html;
    }

    /**
     * Function called at end of geoFilter::listingDescription()
     *
     * Filter the description for display on category browsing pages
     *
     * @param string $desc
     * @return string Filtered text, or original text if no filtering is needed.
     * @since Geo Version 4.0.4
     */
    public function core_filter_geoFilter_listingDescription($desc)
    {
        //If you wanted to filter the description before it is displayed on
        //category browsing pages, this is the way to do it.

        //FILTER the desc here..

        return $desc;
    }

    /**
     * Function called at end of geoFilter::listingShortenDescription()
     *
     * Filter the description for display on category browsing pages
     *
     * @param string $desc
     * @return string Filtered text, or original text if no filtering is needed.
     * @since Geo Version 4.0.4
     */
    public function core_filter_geoFilter_listingShortenDescription($desc)
    {
        //If you wanted to filter the description before it is displayed on
        //category browsing pages, this is the way to do it.

        //FILTER the desc here..

        //NOTE: If this is being called, you know that listingDescription was
        //probably already called for the same text.

        return $desc;
    }

    /**
     * Filter the array of e-mail information right before it is used to send
     * an e-mail.  This is called in geoEmail::sendMail() so if that method is
     * bypassed to send the e-mail (not common), this may get bypassed as well.
     *
     * @param array $email The array of email information to be filtered.
     * @return array the modified / filtered array of e-mail information.
     * @since Geo version 7.0.3
     */
    public function core_filter_email($email)
    {
        //$email is an array of email info, that you can edit.  Use this method
        //to edit e-mail contents or e-mail subject for instance.

        //FILTER the different e-mail data here...

        return $email;
    }

    /**
     * Allows manipulating the categories shown as options during listing placement.
     * For instance, for only allowing certain categories based on User Group or some other criteria
     * @param geoTableSelect $query
     * @since GeoCore 7.0.3
     */
    public function core_filter_listing_placement_category_query($query)
    {
        //$query is a geoTableSelect object used to build the category query
        //objects are passed by reference, so no need to return it when done!
    }

    #### Special Core Events ####
    /**
     * Function called when the system generates an e-mail to be sent out.
     * If this addon sends an e-mail, the email index in
     * {@link addon_example_info::$exclusive} should be
     * set to true to prevent triggering multiple addons that send e-mails.
     *
     * NOTE: Geo plans to stop using this core event to send e-mails in a future
     * release, instead sending e-mails from the core system files.  At that time
     * we may add an "overwrite" type core event to allow an addon to still
     * send e-mails, but at that time the "email" core event will no longer work.
     *
     * @param Array $email_data
     */
    public function core_email($email_data)
    {
        //the $email_data variable is an associative array, as described below.
        //the following is for demonstration only.
        $email_data = array (   'to' => 'to_email@email.com',
                                'from' => 'from_email@email.com',
                                'subject' => 'Subject of e-mail.',
                                'content' => 'Main body of e-mail.',
                                'type' => 'text/plain' //for now, the system only
                                                        //sends plaintext e-mails.
                                                        //HTML e-mails will be implemented
                                                        //later down the road.
                                );
        //Notify type core event, return value not used or required.
    }
    /**
     * Function called when the system generates an error or debug message.
     *
     * Note that the geo software uses this as a means to trigger debug messages.
     *
     * @param array $error_data
     */
    public function core_errorhandle($error_data)
    {
        //error_data that is passed in will be an array with the following
        //structure:
        $error_data = array(    'errno' => 5, //Error level for the error.  See
                                            //php documentation for the different
                                            //error levels.
                                'errstr' => 'The error message.',
                                'errfile' => 'the_file_the_error_was_generated_or_triggered.php',
                                'errline' => 456, //the line number the error is generated or triggered.
                                'errcontext' => null //See the php.net documentation
                                                    //for the function set_error_handler()
                                                    //to see what this is.
        );

        //Notify type core event, return value not used or required.
    }

    #### Auth Core Events ####

    /**
     * Function to determine if admin can log in.  This is used
     * after the user and pass have already been validated, and
     * the user id is NOT 1.  If the user_id is 1, it is automatically
     * validated, and this core event is not called.
     *
     * @param array $login_data See function contents for details
     * @return bool|null should return true, false, or NULL:
     *  NULL:  Neither allow nor deny access.
     *  TRUE:  Grant access and do not process any other addons
     *   that also use this core event.
     *  FALSE: Deny access and do not process any other addons
     *   that also use this core event.
     */
    public function core_auth_admin_login($login_data)
    {
        //$login_data is an array that follows this structure:
        $login_data = array(
            'id' => '', //numeric user id
            'username' => '', //username string for user logging in.
            'password' => '', //password string, in plain-text
            'status' => '' //integer, not used, =1
        );
    }

    /**
     * Function to determine if given page in the admin can be displayed.
     * Is also used to determine if a page should be in the menu.  This is only
     * called if the user id is NOT 1.  If the user_id is 1, all
     * pages can be displayed, and this core event is not called.
     *
     * This is a "return not null" type core event, anything returned that is
     * not null will cause it to not process any other addons and return that
     * as the value.
     *
     * @param string $page_index The page's "index", as specified by page=___ in the URL.
     * @return Bool|null True, False, or NULL:
     *  NULL:  Neither allow nor deny access.
     *  TRUE:  Grant access and do not process any other addons
     *   that also use this core event.
     *  FALSE: Deny access and do not process any other addons
     *   that also use this core event.
     */
    public function core_auth_admin_display_page($page_index)
    {
        $page_index = 'page_index';//page index is what page= is set to in URL

        //We are neither granting, nor denying access, returning null says we
        //don't care too much either way.  (return true to grant access, false
        //to deny access)
        return null;
    }

    /**
     * Function to determine if given page in the admin can be updated, using
     * the built-in page loading backend that automatically calls update functions.
     * This is only called if the user id is NOT 1.  If the user_id is 1, all
     * pages can be updated, and this core event is not called.
     *
     * NOTE:  As of 3.0.1, not all functionality that makes changes in
     * the database use the built-in page loading to call the update
     * function. Such instances will bypass this.  In 3.1, all functionality
     * that makes changes in the database should be moved over to use the
     * page loading back end, but until then this core event should not be
     * relied upon solely for making sure no changes can be made to the
     *  database.
     *
     * @param string $page_index The page's "index", as specified by page=___ in the URL.
     * @return bool|null True, False, or NULL:
     *  NULL:  Neither allow nor deny access.
     *  TRUE:  Grand access and do not process any other addons
     *   that also use this core event.
     *  FALSE: Deny access and do not process any other addons
     *   that also use this core event.
     */
    public function core_auth_admin_update_page($page_index)
    {
        $page_index = 'page_index';//page index is what page= is set to in URL

        //We are neither granting, nor denying access, returning null says we
        //don't care too much either way.  (return true to grant access, false
        //to deny access)
        return null;
    }

    /**
     * Function to determine if current user can edit a listing on the
     * client side.  Only used if user_id != 1.  If the user_id for current
     * user logged into the client side is 1, the user will always be able to
     * edit listings, and this core event is bypassed.
     *
     * @param int | true $var
     *      If an int, will check that listing id for anonymity (requires anonymous addon).
     *      For places that is not desired, pass in true (or any non-numeric).
     * @return bool|null True, False, or NULL:
     *  NULL:  Neither allow nor deny access.
     *  TRUE:  Grand access and do not process any other addons
     *   that also use this core event.
     *  FALSE: Deny access and do not process any other addons
     *   that also use this core event.
     */
    public function core_auth_listing_edit($var)
    {
        //$var will be an int or boolean true.
        $var = true;
        //OR
        $var = $listing_id; //could be an int, which would be the listing ID, and only be used if user not logged in.

        //We are neither granting, nor denying access, returning null says we
        //don't care too much either way.  (return true to grant access, false
        //to deny access)
        return null;
    }

    /**
     * Method to determine if the user with specified userId is allowed to use
     * her password to log in as another user on the "client" side, after he
     * has first logged into the admin panel with her "normal" user/pass login.
     *
     * As with most(all) other auth core events, this is only called if the
     * user is NOT the main admin user with user ID of 1.
     *
     * This is a feature added in 4.1.0 Enterprise editions, if not on 4.1 or
     * not an Enterprise edition this core event will simply not ever get called.
     *
     * @param $vars
     * @return bool|null true, false, or NULL:
     *  NULL:  Neither allow nor deny access.
     *  TRUE: Grant access and do not process any other addons
     *   that also use this core event.
     *  FALSE: Deny access and do not process any other addons
     *   that also use this core event.
     * @since Version 4.1.0
     */
    public function core_auth_admin_user_login($vars)
    {
        //$vars will be an array like so:
        $vars = array (
            'userId' => $user_id,
            'session' => $admin_session_id
        );

        //We are neither granting, nor denying access, returning null says we
        //don't care too much either way.  (return true to grant access, false
        //to deny access)
        return null;
    }

    /**
     * Function to determine if current user can delete a listing on the
     * client side.  Only used if user_id != 1.  If the user_id for current
     * user logged into the client side is 1, the user will always be able to
     * delete listings, and this core event is bypassed.
     *
     * @param NULL $var Not used.
     * @return bool|null True, False, or NULL:
     *  NULL:  Neither allow nor deny access.
     *  TRUE:  Grand access and do not process any other addons
     *   that also use this core event.
     *  FALSE: Deny access and do not process any other addons
     *   that also use this core event.
     */
    public function core_auth_listing_delete($var)
    {
        //$var is not used, but still needs to be part of function.  It will always be null.
        $var = null;

        //We are neither granting, nor denying access, returning null says we
        //don't care too much either way.  (return true to grant access, false
        //to deny access)
        return null;
    }


    #### Overload Core Events ####

    /**
     * Function that overloads the Notify_seller::notify_seller_() function.  This function is responsible for
     *  performing any tasks that the original function normally does.
     *
     * @param array $vars Associative array of variables that were passed to the function being over-loaded,
     *  see the function for more documentation.
     * @return Mixed If you want the original function to still continue, return the string geoAddon::NO_OVERLOAD.  Otherwise,
     *  return a value like the original function would normally return, to keep the original function from continuing.
     */
    public function core_overload_Notify_seller_notify_seller_($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'classified_id' => $classified_id, //the classified id, as passed to the original function
            'info' => $info,                    //the $info variable, as passed to the original function
            'this' => $this                     //an instance of the class that the original function resides in.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the Notify_friend::notify_friend_() function.  This function is responsible for
     *  performing any tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed to the function being over-loaded,
     *  see the function for more details.
     * @return Mixed If you want the original function to still continue, return the string geoAddon::NO_OVERLOAD.  Otherwise,
     *  return a value like the original function would normally return, to keep the original function from continuing.
     */
    public function core_overload_Notify_friend_notify_friend_($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'classified_id' => $classified_id, //the classified id, as passed to the original function
            'info' => $info,                    //the $info variable, as passed to the original function
            'this' => $this                     //an instance of the class that the original function resides in.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the geoImage::display_image() function.  This
     * function is responsible for performing any tasks that the original
     * function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return the string geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     */
    public function core_overload_Site_display_image($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'url' => $url,            //the url, as passed to the original function
            'width' => $width,      //the width, as passed to the original function
            'height' => $height,    //the height, as passed to the original function
            'mime_type' => $mime_type,//the mime_type, as passed to the original function
            'icon' => $icon,        //the icon, as passed to the original function
            'this' => $this_copy    //an instance of the class that the original function resides in.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the Browse_ads::display_browse_result()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return the string geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     */
    public function core_overload_Browse_ads_display_browse_result($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'browse_result' => $browse_result,    //the browse_result, as passed to the original function
            'featured' => $featured,            //the featured, as passed to the original function
            'auction' => $auction,              //the auction, as passed to the original function
            'this' => $this_copy                //an instance of the class that the original function resides in.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the Browse_tag::display_browse_result()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return the string geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     */
    public function core_overload_Browse_tag_display_browse_result($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'browse_result' => $browse_result,    //the browse_result, as passed to the original function
            'featured' => $featured,            //the featured, as passed to the original function
            'auction' => $auction,              //the auction, as passed to the original function
            'this' => $this_copy                //an instance of the class that the original function resides in.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the Search_classifieds::BuildResults()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return the string geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     * @since Geo Version 4.0.4
     */
    public function core_overload_Search_classifieds_BuildResults($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'result' => $result,      //the result, as passed to the original function
            'this' => $this_copy    //an instance of the class that the original function resides in.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the imagesOrderItem::processImages()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     * @since Geo Version 4.0.4
     */
    public function core_overload_imagesOrderItem_processImages($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'url_info' => $url_info,      //The array of image url post data, or false if using image URL's is turned off.
            'post_files' => $post_files,    //The $_FILES array.
            'skip_notfound_error' => $skip_notfound_error, //If false (default), it shows an error when no images are uploaded, if true it continues without throwing an error.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the geoFilter::replaceDisallowedHtml()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     * @since Geo Version 4.0.4
     */
    public function core_overload_geoFilter_replaceDisallowedHtml($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'text' => $text,      //The text to remove stuff from
            'remove_all' => $remove_all,    //false normally, if ture it should remove ALL html tags.
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the geoFilter::listingDescription()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     * @since Geo Version 4.0.4
     */
    public function core_overload_geoFilter_listingDescription($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'description' => $description,        //The text to clean up
        );

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    /**
     * Function that overloads the geoFilter::listingShortenDescription()
     * function.  This function is responsible for performing any
     * tasks that the original function normally does.
     *
     * @param Array $vars Associative array of variables that were passed
     *  to the function being over-loaded, see the function code source
     *  for more details.
     * @return Mixed If you want the original function to still continue,
     *  return geoAddon::NO_OVERLOAD.  Otherwise, return a value like
     *  the original function would normally return, to keep the original
     *  function from continuing.
     * @since Geo Version 4.0.4
     */
    public function core_overload_geoFilter_listingShortenDescription($vars)
    {
        //$vars is an associative array of all variables passed to the original function, not including
        //object vars that can be retrieved using the get_common_vars.php file.
        $vars = array (
            'description' => $description,        //The text to clean up
            'len' => $len,  //The length that the description needs to be shortened to.
        );

        //Note that the normal listingDescription function would have been called
        //for the text before this listingShortenDescription was called.

        //If you need to use the database object (or any other object retrieved using get_common_vars.php file),
        //do something like this:
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        //Now, $db is an instance of the database access object.

        //we are not really going to overload the function, so return
        //geoAddon::NO_OVERLOAD to let the function perform as normal.
        return geoAddon::NO_OVERLOAD;
    }

    #### Session Core Events ####

    /**
     * This is called when a new session is created.  This is done in addition to,
     * NOT instead of the normal session handling procedures.  It is called once
     * the session creation process is finished.  Note that nothing is expected to
     * be returned.
     *
     * @param string $session_id The session ID for the new session.
     */
    public function core_session_create($session_id)
    {
        $session_id = 'same value that is saved in the cookie classified_session';
    }

    /**
     * This is called when a session is touched, meaning the user has accessed a
     * page.  This is done in addition to,
     * NOT instead of the normal session handling procedures.  It is called once
     * the session touch process is finished.  Note that nothing is expected to
     * be returned.
     *
     * @param string $session_id The session ID for the new session.
     */
    public function core_session_touch($session_id)
    {
        $session_id = 'same value that is saved in the cookie classified_session';
    }

    /**
     * This is called when a user logs in.  This is done in addition to,
     * NOT instead of the normal session handling procedures.  It is called once
     * the session login process is finished.  Note that nothing is expected to
     * be returned.
     *
     * @param string $vars An associative array containing the username and
     *  plain-text password.
     */
    public function core_session_login($vars)
    {
        $vars = array(
            'username' => $username,
            'password' => $password //plain text password, or null if part of initial registration/confirmation
        );
    }

    /**
     * This is called when a user logs out.  This is done in addition to,
     * NOT instead of the normal session handling procedures.  It is called once
     * the session logout process is finished.  Note that nothing is expected to
     * be returned.
     *
     * @param mixed $vars An associative array containing the username
     */
    public function core_session_logout($vars)
    {
        $vars = array(
            'username' => $username
        );
    }

    #### User Core Events ####

    /**
     * This is called when a new user registers or is created by the admin.
     * This is done in addition to,
     * NOT instead of the normal procedures.  It is called once
     * the registration process is finished and the user is activated.
     * Note that nothing is expected to be returned.
     *
     * @param mixed $vars An associative array containing new users data
     */
    public function core_user_register($vars)
    {
    }

    /**
     * This is called when a user's info is changed.
     * This is done in addition to,
     * NOT instead of the normal procedures.  It is called once
     * the changes are already made.
     * Note that nothing is expected to be returned.
     *
     * @param mixed $vars An associative array containing changed user's data
     */
    public function core_user_edit($vars)
    {
        //vars will be an associative array of user data.
    }

    #### Registration Core Events ####

    /**
     * This is called when the registration form is being displayed, and allows
     * an addon to add additional fields to be displayed on the registration
     * form.
     *
     * @param array $registered_variables The registered vars set in the
     *   registration class.
     * @return array See method's inline comments for expected array
     * @since Geo Version 4.0.9
     */
    public function core_registration_add_field_display($registered_variables)
    {
        //Easiest is to return an array like below, with an index for "label" and "value":
        return array ('label' => 'Example Addon Label', 'value' => '<input type="text" value="Example Addon Value" />');

        //or return false to skip over
    }

    /**
     * This is a notify type core event, to allow saving of user input registration
     * information.  This can be called in 3 different scenarios, either inserting
     * user into the DB directly (the user ID will be specified), inserting
     * into the registration waiting approval, or moving from waiting approval
     * to user data (registration approved)
     *
     * @param array $vars See comments in method's source
     * @since Geo Version 4.0.9
     */
    public function core_registration_add_field_update($vars)
    {
        $vars = array(
            'user_id' => $id, //user ID or confirmation ID when inserting data for new registration
            'confirmation_id' => $id, //Only set if confirmation_step is 2.
            'confirmation_step' => 1, //either 0, 1, or 2, see comments below
            'registration_variables' => $this->registered_variables //registration vars set in register class
        );

        switch ($vars['confirmation_step']) {
            case 0:
                //add this user's info directly into DB, confirmation step skipped.
                //vars set will be user_id, confirmation_step, and registration_variables
                break;

            case 1:
                //must do email confirmation first, which uses a different ID
                //scheme -- so add the data to some confirmation table.
                //Vars set will be user_id (which will match with confirmation_id
                //in later step), confirmation_step, and registration_variables.
                break;

            case 2:
                //user confirmed -- transfer user's data from confirmation specific
                //location to main location
                //vars set will be user_id (user id in system), confirmation_id
                //(the ID used during confirmation step), confirmation_step, and
                //registration_variables
                break;
        }
        //this is a notification type core event, return value is ignored.
    }

    /**
     * Allow setting of "registered_variables" according to post params (user
     * input), so that they can be accessed later when saving the data.
     *
     * @param array $user_input
     * @return array See comments in method's source
     * @since Geo Version 4.0.9
     */
    public function core_registration_add_variable($user_input)
    {
        //User input is an array of user input as sent in the $_POST['c'] param,
        //sent through clean inputs of course which geoString::specialChars()
        //all the string vars to prevent XSS.

        //Expected to return an array like so:
        $return = array (
            'name' => 'my_var',
            'value' => 'my_value'
        );

        //you can return an array of those as well, like this:
        $return = array ($return);
        //returning false since this is example addon
        return false;
    }
    //registration_check_info
    /**
     * Allow checking info submitted during registration and "adding errors"
     * as is documented in the PHP comments in this method in-line.  This is a
     * notify core event, return value is ignored, errors are added directly
     * to the register class.
     *
     * @param array $vars
     * @since Geo Version 4.1.0
     */
    public function core_registration_check_info($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'info' => $info,//array of registration info submitted
            'this' => $registerClass,//Instance of the Register class where errors can be added
            'api' => $api, //bool, true if this called in API call, false otherwise
        );

        if ($checkFailed) {
            //If some checks on data in $info failed, add an error like this:
            $registerClass->error['example'] = "error";
            $registerClass->error_found++;
            //make a user-friendly looking error for API calls
            $registerClass->api_error = "Example Registration Error";

            //remember to check for such an error when displaying info
        }
    }


    #### Notify Core Events ####

    /**
     * This is called when one of the geoAdmin::user methods are called, such
     * as geoAdmin::userNotice().
     *
     * @param string $str The string that is to be displayed.
     * @since 3.1.0
     */
    public function core_notify_user($str)
    {
        //The $str will be a string of the user message.  This is a notify type
        //notify core event, so return value is not used or needed.
    }

    /**
     * Called when a user is being deleted, after all the misc. stuff is removed
     * but before the critical stuff is removed.
     *
     * @param int $user_id
     * @since Geo Version 6.0.0
     */
    public function core_notify_user_remove($user_id)
    {
    }

    /**
     * This is called from geoTemplate::loadTemplateSets() to allow adding additional
     * template sets to be loaded in addition to those loaded by the t_sets.php
     * file.
     *
     * @param array $vars An associative array of vars, see method source for
     *   documentation
     */
    public function core_notify_geoTemplate_loadTemplateSets($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'force_reload' => $force_reload,//the $force_reload var passed into the original function
        );

        //if you wanted to add a set to be loaded, this is how you would do it
        //The addTemplateSet() method "cleans" the name (see geoTemplate::cleanTemplateSetName())
        //but does not verify that the template set exists
        geoTemplate::addTemplateSet('example_template_set');

        //If you need to look at what template sets are already loaded (by t_sets.php
        //and by other addons), you can make a call similar to below, but note that
        //calling addTemplateSet() already makes the check to make sure the same template
        //set is not loaded more than once.
        $existingTemplateSets = geoTemplate::getTemplateSets();

        //this is a notify type core event, return value is ignored.
    }

    /**
     * This is called when display listing details, right after all the listing
     * template vars are set.  This would be a good place to change/remove a
     * specific template var, for instance if you did not want to display a
     * specific bit of info in certain cases.
     *
     * @param array $vars An associative array of vars
     * @since Geo Version 4.0.4
     */
    public function core_notify_Display_ad_display_classified_after_vars_set($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'id' => $id,//listing ID
            'return' => $return,//Whether to return the display (true) or display it (false)
            'preview' => $preview,//Whether this is a preview of a listing or not
            'autoDisplay' => $autoDisplay//can't remember what that one is for, I think whether to actually display the page or not.
        );

        //get the geoView class, so we can manipulate vars that were set
        $view = geoView::getInstance();

        //Here you could manipulate view vars if desired, for example add something
        //to the end of the title, like this:
        //$view->title .= " - Altered!!!";
        //or perhaps, hide the city:
        //$view->city_data = '[hidden]';
        //You get the idea..

        //This is a notify type core event, so return value is not used or needed
    }

    /**
     * This one is called when displaying the page, to allow you to edit any
     * template vars in the view class if desired.
     *
     * NOTE: For manipulating variables on the
     * listing details page, take a look at the core event
     * notify_Display_ad_display_classified_after_vars_set documented at
     * {@link addon_example_util::core_notify_Display_ad_display_classified_after_vars_set()}
     *
     * @param $vars
     * @since Geo Version 4.0.4
     */
    public function core_notify_display_page($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of site class being called from
            'preview_mode' => $preview_mode//whether this is in preview mode or not.
        );


        //get the geoView class, so we can manipulate vars that were set
        $view = geoView::getInstance();
        /*
         * Here you could manipulate view vars if desired, for example add something
         * to the end of a title (if such a var existed on this page), like this:
         * $view->title .= " - Altered!!!";
         * or perhaps, hide the city (again, if such a var existed on the page):
         * $view->city_data = '[hidden]';
         * You get the idea..
         *
         * Note that while we use "title" and "city_data" as simple
         * examples above, when on the listing details page if the site is using
         * "DB Based Templates", manipulating the variables that display in the
         * listing details "sub template" will not have an effect in this core
         * event, because by the time this core event is called those variable
         * have already been replaced in the template.  Look instead at the
         * core event core_notify_Display_ad_display_classified_after_vars_set
         * which does happen at the correct time to be able to manipulate any
         * template vars used on the listing details DB-based sub-template.
         *
         * Note on View Vars:  For help with manipulating view vars, see
         * the section linked below in our wiki:
         *
         * http://geodesicsolutions.com/support/wiki/developers/geoclass/geoview/start
         * (That link is subject to change, if it is broken, in the user manual
         * navigate to Geo Classes Tips in the Developers section.
         *
         */

        //This is a notify type core event, so return value is not used or needed
    }

    /**
     * This one is called right before the contents of the {head_html} are
     * put together, but right after the modules and addon tags are pre-loaded.
     *
     * This would be good place to add something to the head of the page, based on
     * if a particular module is loaded on the page or not.
     *
     * @param array $modules An array of module tags that were found when pre-loading
     *   all the tags that are going to be on the current page.
     * @since Geo version 6.0.2
     */
    public function core_notify_modules_preload($modules)
    {
        //returning, everything after this point is example of one way to use
        //this notice
        return;

        //This would be good place to, for instance, add some JS to the top of
        //the page if the search box 1 module is being used on the page.  This
        //might be useful if this addon adds something to the search box 1 module
        //contents that requires something added to the head section.

        if (isset($modules['module_search_box_1'])) {
            $view = geoView::getInstance();
            //just an example...  this is not an existing file.
            $view->addJScript('path/to/addon.js');
        }
    }

    /**
     * Called when the "sold" status of a classified changes, or when buy-now-only
     * auction is sold out, and the settings are set to mark such auctions as "sold"
     * rather than closing them.  In such cases, "is_auction" will be set in the
     * $vars array to easily distinguish when it is a sold-out buy-now-only auction
     * being marked as sold.
     *
     * @param array $vars See inline PHP comments for documentation
     * @since Geo Version 7.0.4
     */
    public function core_notify_sold_sign_status_changed($vars)
    {
        $vars = array(
            'listingId', //id of the listing that has changed
            'new_status', //updated sold status (1 or 0)
            'is_auction', //Quick check whether it is an auction or not.
                            //Added in version 7.2.2
        );

        //Note that is_auction will not be set at all for the "normal" use of changing
        //the sold status on or off, which only happens for classifieds.  The is_auction
        //will only be set and set to 1 when it is set as sold, as a result of
        //a buy-now-only auction being sold out, when the settings are set to mark such
        //auctions as sold rather than simply closing them once they are sold out.
    }

    /**
     * This one is called when a listing is being removed, usually the result
     * of a mass-removal, for instance if an entire category is being removed.
     *
     * Called from {@link geoListing::remove()}
     *
     * @param $vars
     * @since Geo Version 5.0.0
     */
    public function core_notify_geoListing_remove($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'listingId' => $listingId,//listing ID
            'isArchived' => $isArchived,//$isArchived, true if listing being removed
                                        //has already been copied to archive table, false otherwise
        );

        /**
         * If you have custom stuff that is "attached" to a listing, this is
         * where you would need to remove that data, or risk it staying in
         * the system "forever".
         */

        //This is a notify type core event, so return value is not used or needed
    }

    /**
     * This one is called when a new bid it inserted in the bids table, in
     * Auction_bid class, in the method insert_into_bid_table.
     *
     * @param $vars
     * @since Geo Version 6.0.0
     */
    public function core_notify_new_bid_success($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $bid = $vars['bid'];
        $current_time = $vars['current_time'];
        $quantity = $vars['quantity'];
        $buy_now_bid = $vars['buy_now_bid'];
        $bidder = $vars['bidder'];
        $auction_bid_class = $vars['this'];

        /**
         * If you have things you want to do when a new bid is inserted in the database
         * sucessfully, this is where you do it.
         */

        //This is a notify type core event, so return value is not used or needed
    }

    /**
     * Called when an image is being inserted in the database, at the time of
     * insertion which means usually during listing placement, which means before
     * the listing itself is created.
     *
     * @param array $image_info
     * @since Geo Version 6.0.0
     */
    public function core_notify_image_insert($image_info)
    {
        //$image_info is an associative array of information about the inserted
        //image, such as the image ID in the database.

        //This is a notify type core event, so return value is not used or needed
    }

    /**
     * Called when an image is being removed from database, which could happen
     * for a number of reasons.
     *
     * @param int $image_id The id of the image being removed
     * @since Geo Version 6.0.0
     */
    public function core_notify_image_remove($image_id)
    {
        //This is a notify type core event, so return value is not used or needed
    }

    /**
     * Called when a listing feed is being generated, right before it uses the
     * SQL query to get the results.  This is perfect place to manipulate what
     * listings are displayed by the feed.
     *
     * @param geoListingFeed $feed The feed object this notify call is being
     *   called from.
     */
    public function core_notify_ListingFeed_generateSql($feed)
    {
        //this is a notify type core event, so return falue is not used or needed.

        //Typically you would look at the $feed object, and apply any changes to the
        //table select query to alter what listings are part of the query, or
        //order or anything like that.

        $query = $feed->getTableSelect();

        //just an example, only show listings that are live
        $query->where("`geodesic_classifieds`.`live`=1");
    }

    /**
     * Called at the time that the password "hash types" list is being populated.
     * This notification is the best time to register custom hash types defined
     * by the addon.  See the method source for an example of usage.
     *
     * @since Geo version 7.1.0
     */
    public function core_notify_geoPC_get_hash_types()
    {
        //Use this as the time to register a hash type, like the example here.
        $geoPC = geoPC::getInstance();

        //NOTE: By specifying the name, this becomes a possible selection as the default
        //hash type to use in the admin at "Admin Tools & Settings > Security Settings > General Security Setings"
        //Also, note that this is a fully working example, it will hash the password
        //using a random generated salt with variable length.  We do NOT recommend
        //use of MD5 hash, in fact the opposite as it has been proven vulnerable
        //to various attacks. This is JUST an example, we only chose it due to it's simplicity
        //so that the focus is on "how" to implement your own custom hash, not to
        //create a custom hash that people might wish to use...
        $geoPC->register_hash_type($this->name, 'md5_Simple', 'Simple MD5 with Salt', 32, -1);

        //Since we specified the "type" as md5, it will use a function named
        //auth_md5 to generate the password, which you will find next.
    }

    /**
     * This method will be called to generate hashed password since we registered
     * a hash type of "md5_Simple" in the function core_notify_geoPC_get_hash_types()
     *
     * This method is called automatically by the system in order to generate
     * a hashed password.
     *
     * The method name matches what is passed into geoPC::register_hash_type() for the
     * $type (second parameter), pre-fixed with "auth_".  In this case it gave type of "md5_Simple" so
     * the method name is auth_md5_Simple
     *
     * @param string $username The username, passed since some hash methods use the
     *   username to further salt the hash
     * @param string $plaintext_pass The plain-text password
     * @param string $salt The salt, if this hash type uses a salt.  If this is passed
     *   in empty, you are expected to generate the salt yourself.  Otherwise you
     *   are expected to use the salt value as-is to generate the password
     * @return array|string If using salt, required to return array ('password'=>'hashed value','salt'=>'salt value'),
     *   But if salt is not used for this hash, can simply return the hashed value as a string.
     * @Since Geo version 7.1.0
     */
    public function auth_md5_Simple($username, $plaintext_pass, $salt)
    {
        //The function name is auth_ followed by the "type" used when registering
        //the hash type in core_notify_geoPC_get_hash_types()
        //So in this example, is auth_md5.

        //In this example we use a salt.  If you do not need to use salt, just ignore
        //that input.

        if (strlen($salt) == 0) {
            //Note: can skip this part if not using salt for this hash type.
            //The salt passed in is blank, so need to generate a new salt value.

            //we want to use variable length salt to make it slightly more complex
            //to attack.
            $length = mt_rand(100, 128);
            //use a pseudo random generated salt of variable length, using built-in
            //method to generate it.

            //NOTE: salt cannot be longer than 255 characters, that's how long the
            //db column is that stores the salt.
            $salt = geoPC::getInstance()->generate_new_pass($length, true);
        }

        //If using a hash with a salt, return value MUST be an array, with index
        //of 'password' and 'salt'.  If salt is not used, can just return a string.
        $hash = array();

        $hash['salt'] = $salt;
        //Now generate the hashed value, based on md5.  We do NOT recommend using
        //MD5, we advise AGAINST it if you are creating your own hash method, as
        //it is vulnerable to various attacks (among other reasons), This is meant
        //as a simple example only.
        $hash['password'] = md5("$salt:$username:$plaintext_pass:$salt");

        return $hash;
    }

    /**
     * This one is called when a new group is created in the admin.
     *
     * @param mixed $vars Associative array of vars passed from original function
     * @since Geo Version 4.0.4
     */
    public function core_admin_update_insert_group($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array (
            'group_id' => $group_id,
            'group_info' => $group_info
        );

        //CODE goes here.

        //This is a notify type core event, so return value is not used or needed
    }

    #### Misc. Core Events ####
    # This is where stuff that doesn't really fit into other event types goes.

    /**
     * Called at end of designManage::admin_display_page_attachments_edit()
     * This is handy to change the template used when editing attached templates
     * for a specific addon page.  The storefront addon uses this since it
     * uses the attachments in a non-traditional way for the "home" page.
     *
     * This is a notify type of event, the return value is ignored.
     *
     * @param array $tpl_vars The template vars that have already been assigned
     *   to the view body vars.
     */
    public function core_admin_display_page_attachments_edit_end($tpl_vars)
    {
        //$tpl_vars are the template vars sent to view class...

        if (!isset($tpl_vars['addon']) || $tpl_vars['addon'] != 'example' || $tpl_vars['pageName'] != 'examplePage') {
            //we checked to make sure the page attachments being edited were for
            //this addon, and for a specific page (examplePage which does not actually exist)

            //since this is not for the specific page, don't do anything.
            return;
        }

        //would do something here, perhaps change the template used to display
        //the page in a format more useful for how this page use's things.

        //For example, the storefront addon changes it to make more sense since
        //it re-tasks the category ID to be the template ID selected by each user,
        //to allow each storefront user to specify a specific template...
    }

    /**
     * Called for the "manage" lightbox in category editing, to allow addons to
     * add their own "category specific" links.  For instance, core addon uses this
     * to add browsing filters link.
     *
     * @param int $category_id The category ID to create the links for
     * @return This is ARRAY_ARRAY type, expects an array of links, each entry
     *   with format of array('href'=>'url','label'=>'link text label')
     * @since Geo version 7.4.0
     */
    public function core_admin_category_manage_add_links($category_id)
    {
        $links = array();
        $links[] = array (
            //EXAMPLE ONLY - this link does not go anywhere except admin home page!
            'href' => 'index.php?category=' . $category_id,
            'label' => 'Example Addon Link'
        );

        //note - can add multiple links if needed...

        return $links;
    }

    /**
     * Called for each category being displayed in the admin, to allow adding
     * additional icons showing what is "attached" to the category.  Note that
     * even if the category does not have things attached, it should still return
     * each icon that is "possible", with the "active" array key used to signify
     * if the icon should be displayed for the category.
     *
     * This is so that all the icons will line up, the categories that the icon
     * is not active for it will still show the image but "hidden" so that it is
     * just a blank spot for that category, that way each type of icon lines up
     * vertically in the list of categories.
     *
     * @param array $category_row Database row for the category in array format
     * @return This is ARRAY_ARRAY type, expects an array of links, each entry
     *   with format of array('title'=>'icon hover title','src'=>'icon/image/url.jpg','active'=>bool),
     *   see source of method for further info
     * @since Geo version 7.4.0
     */
    public function core_admin_category_list_specific_icons($category_row)
    {
        //the category ID
        $category_id = (int)$category_row['category_id'];
        $icons = array();
        //expects array of arrays...

        //do a check to see if this icon should be displayed...  (this is example,
        //so just set it to true to always show the icon)
        $show_this_icon = true;

        //Note that this is called once for EVERY category displayed, so if there
        //is some information you retrieve that is the same each time, it would be
        //a good idea to store in a local variable or similar, so you don't have
        //to keep retrieving it over and over.  Also make sure this is as "optimized"
        //as possible so it doesn't slow things down too much on sites showing 100
        //categories on the page.

        $icons[] = array (
            //EXAMPLE ONLY - this icon does not actually work..

            //Title displayed when you hover on the icon
            'title' => 'Example Icon',

            //image location relative to the admin folder to display if the
            //category does have this thing attached
            'src' => 'admin_images/icons/filter.png',

            //set to true to show the icon, or false to not show the icon for this
            //category
            'active' => $show_this_icon
        );

        //note - can add multiple icons if needed...

        return $icons;
    }

    /**
     * This is return array type of core event.  Expected to return array of
     * strings, each string will be inserted into it's own table column.  See
     * this method's source for more documentation.
     *
     * @param array $vars Associative array of stuff.
     * @return array an array of stuff to display, or false.
     * @since Geo Version 4.0.4
     */
    public function core_Browse_ads_display_browse_result_addRow($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Browse_ads class
            'show_classifieds' => $show_classifieds,//associative array of listing info
            'browse_fields' => $browse_fields,//array of fields that are set to be
                                            //shown when browsing, this var added
                                            //in version 5.1.0
            'auction' => $auction,//bool, whether this is displaying auction results or not
                                            //this var added in 5.1.4
            'featured' => $featured,//whether this is featured result or not, this var
                                            //added in 5.1.4
        );

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the results.  The defualt template adds the
        //surrounding <td></td> (as of 4.0.4)

        //To allow each addon to add multiple columns to a single listing, the
        //return is an array of items:
        $rows = array();

        //For this example, we'll add 1 item to display for each listing, to go along with our
        //1 header returned by the sister addHeader core event.:
        $rows[] = 'Example Data';

        return $rows;
    }

    /**
     * This is return array type of core event.  Expected to return an array that
     * will be used to display a header in browsing results.  See this method's
     * source for more documentation.
     *
     * @param array $vars Associative array of stuff.
     * @return array An array of arrays, See method's source for more documentation.
     * @since Geo Version 4.0.4
     */
    public function core_Browse_ads_display_browse_result_addHeader($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Browse_ads class
            'browse_fields' => $browse_fields,//array of fields that are set to be
                                            //shown when browsing, this var added
                                            //in version 5.1.0
            'auction' => $auction,//bool, whether this is displaying auction results or not
                                            //this var added in 5.1.4
            'featured' => $featured,//whether this is featured result or not, this var
                                            //added in 5.1.4
        );

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the "head" of the listing results table.
        //The defualt template adds the surrounding <td></td> (as of 4.0.4)

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //If not false, it should at the very least be an array with 1 index like
        //so:
        $headings = array();
        //first heading to add
        $headings [] = array (
            'text' => 'Example Header',//text that will be displayed for a column header.
            'label' => 'Example Label:',//text used as label for listing info when
                                        //used for list or gallery view.
                                        //Label added in version 7.1.0
            //'css' => 'alternate_css_class' //optional, if not used it uses
                //a default class that makes it look like the rest of the headings.
                //This also used on grid/gallery view on the surrounding element class.
        );
        //more headings could be added by adding more arrays...

        return $headings;
    }

    /**
     * This is return array type of core event.  Expected to return array of
     * strings, each string will be inserted into it's own table column.  See
     * this method's source for more documentation.
     *
     * @param array $vars Associative array of stuff.
     * @return array an array of stuff to display, or false.
     * @since Geo Version 5.1.0
     */
    public function core_Browse_tag_display_browse_result_addRow($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Browse_ads class
            'show_classifieds' => $show_classifieds,//associative array of listing info
            'tag_fields' => $tag_fields,//array of fields that are set to show on
                                    //browsing tags.  Var added in version 5.1.0
        );

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the results.  The defualt template adds the
        //surrounding <td></td> (as of 4.0.4)

        //To allow each addon to add multiple columns to a single listing, the
        //return is an array of items:
        $rows = array();

        //For this example, we'll add 1 item to display for each listing, to go along with our
        //1 header returned by the sister addHeader core event.:
        $rows[] = 'Example Data';

        return $rows;
    }

    /**
     * This is return array type of core event.  Expected to return an array that
     * will be used to display a header in browsing results.  See this method's
     * source for more documentation.
     *
     * @param array $vars Associative array of stuff.
     * @return array An array of arrays, See method's source for more documentation.
     * @since Geo Version 5.1.0
     */
    public function core_Browse_tag_display_browse_result_addHeader($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Browse_tag class
            'tag_fields' => $tag_fields,//array of fields that are set to show on
                                    //browsing tags.  Var added in version 5.1.0
        );

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the "head" of the listing results table.
        //The defualt template adds the surrounding <td></td> (as of 4.0.4)

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //If not false, it should at the very least be an array with 1 index like
        //so:
        $headings = array();
        //first heading to add
        $headings [] = array (
            'text' => 'Example Header',//text that will be displayed for a column header.
            //'cssClass' => 'alternate_css_class' //optional, if not used it uses
                            //a default class that makes it look like the rest of the headings.
        );
        //more headings could be added by adding more arrays...

        return $headings;
    }


    /**
     * This allows you to get the geoTableSelect query being used for the browse
     * query, and make any changes or additions to it before it is used to
     * get the browse results.
     *
     * This is an update core event, so the return value is ignored.
     *
     * @param array $vars Associative array of stuff.
     * @since Geo Version 6.0.3
     */
    public function core_Browse_ads_generate_query($vars)
    {
        $browse_object = $vars['this'];
        //NOTE:  this will be the cloned version already set up to return
        //results, to allow you to modify already made mods.
        $query = $vars['query'];

        //Example of manipulating a where clause already entered.. (in this example, forcing it to only TX state)
        $query->where(geoTables::classifieds_table . ".`location_state` LIKE 'TX'", 'location_state');

        //OR you can remove that where clause all the way, if it exists
        $query->where('', 'location_state');

        //OR you can add a new where clause that doesn't relate to anything, such
        //as for fields that the addon itself adds...

        //Can also affect the order, limit, or anything that is possible using
        //the {@see geoTableSelect} class.  This gives you MUCH more customization
        //abilities than what was previously available.
    }
    /**
     * This allows you to get the geoTableSelect query being used for the browse
     * query, and make any changes or additions to it before it is used to
     * get the browse results.
     *
     * This is an update core event, so the return value is ignored.
     *
     * @param array $vars Associative array of stuff.
     * @since Geo Version 6.0.3
     */
    public function core_Browse_featured_pic_generate_query($vars)
    {
        $browse_object = $vars['this'];
        //NOTE:  this will be the cloned version already set up to return
        //results, to allow you to modify already made mods.
        $query = $vars['query'];

        //Example of manipulating a where clause already entered.. (in this example, forcing it to only TX state)
        $query->where(geoTables::classifieds_table . ".`location_state` LIKE 'TX'", 'location_state');

        //OR you can remove that where clause all the way, if it exists
        $query->where('', 'location_state');

        //OR you can add a new where clause that doesn't relate to anything, such
        //as for fields that the addon itself adds...

        //Can also affect the order, limit, or anything that is possible using
        //the {@see geoTableSelect} class.  This gives you MUCH more customization
        //abilities than what was previously available.
    }

    /**
     * This allows you to get the geoTableSelect query being used for the browse
     * query, and make any changes or additions to it before it is used to
     * get the browse results.
     *
     * This is an update core event, so the return value is ignored.
     *
     * @param array $vars Associative array of stuff.
     * @since Geo Version 6.0.3
     */
    public function core_Browse_newest_ads_generate_query($vars)
    {
        $browse_object = $vars['this'];
        //NOTE:  this will be the cloned version already set up to return
        //results, to allow you to modify already made mods.
        $query = $vars['query'];

        //Example of manipulating a where clause already entered.. (in this example, forcing it to only TX state)
        $query->where(geoTables::classifieds_table . ".`location_state` LIKE 'TX'", 'location_state');

        //OR you can remove that where clause all the way, if it exists
        $query->where('', 'location_state');

        //OR you can add a new where clause that doesn't relate to anything, such
        //as for fields that the addon itself adds...

        //Can also affect the order, limit, or anything that is possible using
        //the {@see geoTableSelect} class.  This gives you MUCH more customization
        //abilities than what was previously available.
    }

    /**
     * This allows you to get the geoTableSelect query being used for the browse
     * query, and make any changes or additions to it before it is used to
     * get the browse results.
     *
     * This is an update core event, so the return value is ignored.
     *
     * @param array $vars Associative array of stuff.
     * @since Geo Version 6.0.3
     */
    public function core_Browse_tag_generate_query($vars)
    {
        $browse_object = $vars['this'];
        //NOTE:  this will be the cloned version already set up to return
        //results, to allow you to modify already made mods.
        $query = $vars['query'];

        //Example of manipulating a where clause already entered.. (in this example, forcing it to only TX state)
        $query->where(geoTables::classifieds_table . ".`location_state` LIKE 'TX'", 'location_state');

        //OR you can remove that where clause all the way, if it exists
        $query->where('', 'location_state');

        //OR you can add a new where clause that doesn't relate to anything, such
        //as for fields that the addon itself adds...

        //Can also affect the order, limit, or anything that is possible using
        //the {@see geoTableSelect} class.  This gives you MUCH more customization
        //abilities than what was previously available.
    }

    /**
     * This is return array type of core event.  Expected to return array of
     * strings, each string will be inserted into it's own table column (or
     * line on "featured pic" modules).  See
     * this method's source for more documentation.
     *
     * @param array $vars Associative array of stuff.
     * @return array an array of stuff to display, or false.
     * @since Geo Version 6.0.0
     */
    public function core_Browse_module_display_browse_result_addRow($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.

        //normally for modules, "this" will be the DataAccess object.  Only included here
        //to be consistent with other "Browse_*" events.
        $calling_class = $vars['this'];

        //the listing data array
        $listing_data = $vars['show_classifieds'];

        //array of which fields to display
        $fields = $vars['fields'];

        //The module settings array (which will include which module this is for)
        $module_info = $vars['show_module'];

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the results.  The defualt template adds the
        //surrounding <td></td> (as of 4.0.4)

        //To allow each addon to add multiple columns to a single listing, the
        //return is an array of items:
        $rows = array();

        //For this example, we'll add 1 item to display for each listing, to go along with our
        //1 header returned by the sister addHeader core event.:
        $rows['column_name'] = 'Example Data';

        return $rows;
    }

    /**
     * This is return array type of core event.  Expected to return an array that
     * will be used to display a header in module header.  On "featured pic" modules,
     * the text is used as the "label" for the info.  See this method's
     * source for more documentation.
     *
     * @param array $vars Associative array of stuff.
     * @return array An array of arrays, See method's source for more documentation.
     * @since Geo Version 6.0.0
     */
    public function core_Browse_module_display_browse_result_addHeader($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.

        //normally for modules, "this" will be the DataAccess object.  Only included here
        //to be consistent with other "Browse_*" events.
        $calling_class = $vars['this'];

        //array of which fields to display
        $fields = $vars['fields'];

        //The module settings array (which will include which module this is for)
        $module_info = $vars['show_module'];

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the "head" of the listing results table.
        //The defualt template adds the surrounding <td></td> (as of 4.0.4)

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //If not false, it should at the very least be an array with 1 index like
        //so:
        $headings = array();
        //first heading to add
        $headings ['column_name'] = array (
            'text' => 'Example Header',//text that will be displayed for a column header.
            'label' => 'Example Label:',//text used as label for listing info when
                                        //used for list or gallery view.
                                        //Label added in version 7.1.0
            //'css' => 'alternate_css_class' //optional, if not used it uses
                            //a default class that makes it look like the rest of the headings.
                            //This also used on grid/gallery view on the surrounding element class.
        );
        //more headings could be added by adding more arrays...

        return $headings;
    }

    /**
     * This is return array type of core event.  Expected to return array of
     * arrays as documented in this method's source.  See this method's source
     * for more documentation.
     *
     * Note that an addon can add it's own "search criteria" into the built-in
     * searches, by using this core event along with the core events:
     * - Search_classifieds_generate_query
     * - Search_classifieds_BuildResults_addHeader
     * - Search_classifieds_BuildResults_addRow
     *
     * @param array $vars Associative array of stuff.
     * @return array
     * @since Geo Version 4.0.4
     */
    public function core_Search_classifieds_search_form($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Search_classifieds class
            'search_fields' => $search_fields,//Array of fields that have checkbox for "search by" checked
                                            //in display field locations.  This var
                                            //added in version 5.1.0.
        );

        //This should be returning an array of arrays, that way the addon can
        //be adding multiple search criteria.
        $return = array();

        //lets add 2 "entries" to be added to the search page (as an example)
        $return [] = array (
            'label' => 'Example Addon Search Criteria 1',
            'data' => '<input type="text" name="dummyVar" value="search me! field 1" />',
            'skipBreakAfter' => true, //make it skip the <hr> after this one, so there is nothing between our 2 search fields
        );
        //here's the second one to add.
        $return [] = array (
            'label' => 'Example Addon Search Criteria 2',
            'data' => '<input type="text" name="dummyVar" value="search me! field 2" />',
            //if we don't want to skip the break after, don't need to include that part...
        );

        return $return;
    }

    /**
     * This allows you to get the geoTableSelect query being used for the search
     * query, and make any changes or additions to it before it is used to
     * get the search results.
     *
     * This is an update core event, so the return value is ignored.
     *
     * Note that an addon can add it's own "search criteria" into the built-in
     * searches, by using this core event along with the core events:
     * - Search_classifieds_search_form
     * - Search_classifieds_BuildResults_addHeader
     * - Search_classifieds_BuildResults_addRow
     *
     * @param array $vars Associative array of stuff.
     * @since Geo Version 6.0.0
     */
    public function core_Search_classifieds_generate_query($vars)
    {
        $vars = array (
            'this' => $searchClass,
        );

        $db = DataAccess::getInstance();

        //To manipulate the query, get the query being used for searches, like so:
        $query = $db->getTableSelect(DataAccess::SELECT_SEARCH);

        //Example of manipulating a where clause already entered.. (in this example, forcing it to only TX state)
        $query->where(geoTables::classifieds_table . ".`location_state` LIKE 'TX'", 'location_state');

        //OR you can remove that where clause all the way, if it exists
        $query->where('', 'location_state');

        //OR you can add a new where clause that doesn't relate to anything, such
        //as for fields that the addon itself adds...

        //Can also affect the order, limit, or anything that is possible using
        //the {@see geoTableSelect} class.  This gives you MUCH more customization
        //abilities than what was previously available.
    }

    /**
     * This is return array type of core event.  Expected to return an array that
     * will be used to display a header in search results.  See this method's
     * source for more documentation.
     *
     * Note that an addon can add it's own "search criteria" into the built-in
     * searches, by using this core event along with the core events:
     * - Search_classifieds_search_form
     * - Search_classifieds_generate_query
     * - Search_classifieds_BuildResults_addRow
     *
     * @param array $vars Associative array of stuff.
     * @return array An array of arrays, See method's source for more documentation.
     * @since Geo Version 4.0.4
     */
    public function core_Search_classifieds_BuildResults_addHeader($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Search_classifieds class
            'search_fields' => $search_fields,//array of fields that are set to be
                                            //shown when browsing, this var added
                                            //in version 5.1.0
        );

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the "head" of the search results table.
        //The defualt template adds the surrounding <td></td> (as of 4.0.4)

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //If not false, it should at the very least be an array with 1 index like
        //so:
        $headings = array();
        //first heading to add
        $headings [] = array (
            'text' => 'Example Header',//text that will be displayed for a column header.
            //'cssClass' => 'alternate_css_class' //optional, if not used it uses
                            //a default class that makes it look like the rest of the headings.
        );
        //more headings could be added by adding more arrays...

        return $headings;
    }
    /**
     * This is return array type of core event.  Expected to return array of
     * strings, each string will be inserted into it's own table column.  See
     * this method's source for more documentation.
     *
     * Note that an addon can add it's own "search criteria" into the built-in
     * searches, by using this core event along with the core events:
     * - Search_classifieds_search_form
     * - Search_classifieds_generate_query
     * - Search_classifieds_BuildResults_addHeader
     *
     * @param array $vars Associative array of stuff.
     * @return array an array of stuff to display, or false.
     * @since Geo Version 4.0.4
     */
    public function core_Search_classifieds_BuildResults_addRow($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'this' => $this,//instance of Search_classifieds class
            'listing_id' => $listing_id,//the listing's ID
            'search_fields' => $search_fields,//array of fields that are set to be
                                            //shown when browsing, this var added
                                            //in version 5.1.0
        );

        //The entire listing's details are not passed here, at least not as of
        //4.0.4 but we may pass it in a future version.  For now if you need
        //listing details, get it by using the following line:
        //$listing = geoListing::getListing($vars['listing_id']);

        //Like all "array" type returns for core events, if this returns false,
        //it will not be used.  (comment out next line to see something actually used)
        return false;

        //This method is used to add an additional "td" (at least when using
        //default system templates) onto the results.  The defualt template adds the
        //surrounding <td></td> (as of 4.0.4)

        //To allow each addon to add multiple columns to a single listing, the
        //return is an array of items:
        $rows = array();

        //For this example, we'll add 1 item to display for each listing, to go along with our
        //1 header returned by the sister addHeader core event.:
        $rows[] = 'Example Data';

        return $rows;
    }

    /**
     * Used to display info box on the "new" my account home page.  That's the one
     * with all the statistic-info-type boxes on it, not the old one with all the
     * my account links in the middle of the page with nothing else...
     *
     *
     * @param $vars null Nothing is passed in.
     * @return array See method's source for documentation.
     * @since Geo Version 4.0.4
     */
    public function core_my_account_home_add_box($vars)
    {
        //For this core event, $vars is just null, it is not used for anything.
        $vars = null;

        //This can return a "box array" as noted, or an array of box arrays if
        //wanting to display multiple boxes.

        //Box array syntax:
        $boxArray = array (
            'display' => true,  //boolean, whether or not to display the box.
                                //the display key is always required, even if false.
            'full' => false,    //boolean, whether this box takes up the full
                                //width (true), or if it should only take up half-width (false)
            'title' => 'Example Addon - Home Box',//will be used in the top "bar" of the box
            'rows' => array(), //see more info on rows syntax below
        );

        //Now, the "rows" part can have 1 of 2 formats:

        //First format, is display things in a "table":
        $boxArray['rows'][] = array (
            'table' => array (
                'row1' => array ( //row1 index name not important, just used for demonstration
                    'title' => 'Table title row 1',//used in first column of row 1
                    'link' => 'http://google.com',//optional, if supplied will make title link to the link
                    'link2' => 'http://google.com',//optional, required if want to display 2nd column though
                    'link2text' => 'Example 2nd column first row',//the 2nd column's text
                ),
                'row2' => array ( //row2 index name not important, just used for demonstration
                    'title' => 'Table title row 2',//used in first column of row 1
                    'link' => 'http://google.com',//optional, if supplied will make title link to the link
                    'link2' => 'http://google.com',//optional, required if want to display 2nd column though
                    'link2text' => 'Example 2nd column second row',//the 2nd column's text
                )
            )
        );

        //here's the second format you can use, much more simple if simple is what you need:
        $boxArray['rows'][] = array (
            'label' => 'The example label', //first part, not linked to anything
            'link' => 'http://google.com', //optional, if specified will link the "data" to this url
            'data' => 'Some data',//will display as send part
        );

        //if just returning 1 box, could just return that box array, like this:
        return $boxArray;

        //If you needed to create multiple boxes, you could return an array of boxes:
        $boxes = array ($boxArray);//we only have 1 box in the array but you could add more

        //return the array of box arrays
        return $boxes;
    }

    /**
     * Used to display info on the "client side" on "my account information" page
     * in the section that displays "my personal information" (rather than at the bottom
     * of the page as core_User_management_information_display_user_data_plan_information
     * does).
     *
     * Expected to return an associative array with key of 'label' and 'value', with values
     * for the left (label) and right (info) columns respectively.
     *
     * @return array See PHP source for working example for this function.
     * @since Geo Version 6.0.0
     */
    public function core_User_management_information_display_user_data()
    {
        //Nothing passed in.

        //Expected to return an array like this.
        return array (
            'label' => 'Example Addon',
            'value' => 'Example Value',
        );
    }

    /**
     * Used to display info on the "client side" on "my account information" page.
     *
     * This is a "return string" type core event, so will need to return the text
     * to be inserted into the page.  It will be displayed somewhere below the
     * price plan info (near the bottom somewhere).
     *
     * See the template for the page at system/user_management/information/user_data.tpl
     *
     * The contents of this will be iserted in that template where you see
     * {$addonPlanInfo} so design the contents to match.
     *
     * @param $vars Associative array, see method's source for documentation
     * @return string What is to be inserted into the user info page.
     * @since Geo Version 4.0.4
     */
    public function core_User_management_information_display_user_data_plan_information($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array (
            'this' => $this,//instance of User_management_information class
            'user_data' => $user_data//the user's data
        );

        //If you wanted to "play nice" and use same styles as built into the
        //page, you would do something like this:

        $info = "
<div class='my_current_info_title'>
	Example Addon Title
</div>
<div class='page_description'>
	Example Addon description, this is where you might put the description of this section of the user info.
</div>
<!--  To line up with other values on page, make a template kinda like this: -->
<table cellpadding='2' cellspacing='1' style='border:0px; width:100%;'>
	<tr>
		<td class='field_labels'>
			Example Label:
		</td>
		<td class='data_values'>
			Example Value
		</td>
	</tr>
</table>
		";

        //un-comment the next line to see it inserted into the user details page
        //return $info;

        //If you decide not to insert anything, returning empty string will do it
        return '';
    }

    /**
     * Used to display info on the "client side" on "my account information - edit" page
     * in the section that allows editing "my personal information"
     *
     * Expected to return an associative array, or even an array of arrays for multiple fields,
     * with each field having keys as specified below, note that
     * only 'label' and 'value' are required, rest are optional:
     *
     * label : label of the field
     * value : the value (such as the input text HTML tag to allow editing)
     * error : if there is error with the field
     * required : set to true if field required
     * type : if set to "single_checkbox" will display single checkbox
     *
     * @param $vars Vars passed in, as documented in source code of this method.
     * @return array See PHP source for working example for this function.
     * @since Geo Version 6.0.0
     */
    public function core_user_information_edit_form_display($vars)
    {
        //user id of current user
        $user_id = $vars['user_id'];
        //Info as it was passed into the edit_user_form() method.
        $info = $vars['info'];
        //instance of the class that is making the core event call
        $user_management_object = $vars['this'];

        //Expected to return an array like this.
        return array (
            'label' => 'Example Addon',
            'value' => '<input type="text" class="field" />',
        );
        //Or can return an array of fields if desired.
    }

    /**
     * Check any field values that may have been changed that were displayed by the
     * user_information_edit_form_display core event.  This is called as an
     * update core event, meaning the return value is ignored.  Typically if there
     * is a problem with the fields, it would add an error like it does in the
     * function that calls this.
     *
     * @param $vars Vars passed in, as documented in source code of this method.
     * @since Geo Version 7.3.0
     */
    public function core_user_information_edit_form_check_info($vars)
    {
        //Info as it was passed into the check_info() method.
        $info = $vars['info'];
        //instance of the class that is making the core event call
        $user_management_object = $vars['this'];

        //YOu would make any changes to the user info here.
    }

    /**
     * Save any field values that may have been changed that were displayed by the
     * user_information_edit_form_display core event.  This is called as an
     * update core event, meaning the return value is ignored.
     *
     * @param $vars Vars passed in, as documented in source code of this method.
     * @since Geo Version 6.0.0
     */
    public function core_user_information_edit_form_update($vars)
    {
        //user id of current user
        $user_id = $vars['user_id'];
        //Info as it was passed into the edit_user_form() method.
        $info = $vars['info'];
        //instance of the class that is making the core event call
        $user_management_object = $vars['this'];

        //YOu would make any changes to the user info here.
    }


    /**
     * Use in My Account Links module, to add a link to the my account links.
     * This is a return array type of core event.  Expected to return a
     * "link array" (Associative array as documented in method's source), or can
     * even return an array of "link arrays" if desired.
     *
     * @param array $vars Associative array, see method's source for full documentation.
     * @return array An array of arras, as documented in method's source.
     * @since Geo Version 4.0.4
     */
    public function core_my_account_links_add_link($vars)
    {
        //$vars is an associative array of variables passed from the original function.
        //See the original function for further docs on what each one is.
        $vars = array(
            'url_base' => $url_base //the recommended base url to use for links
        );

        //This can return a "link array" as noted, or an array of link arrays if
        //wanting to display multiple links.

        $return['storefront'] = array(
            'link' => $vars['url_base'] . "?a=ap&amp;addon=storefront&amp;page=home&amp;store=$user_id",
            'label' => $text,
            'icon' => $image,
            'active' => (($_REQUEST['addon'] == 'storefront' && $_REQUEST['page'] == 'home') ? true : false)
        );

        //Link array syntax:
        $linkArray = array (
            'link' => 'http://google.com',  //What this links to, should be a URL.  Recommended using
                            //the $vars['url_base'] for the start of it, if applicable.
            'label' => 'Example Account Link',  //Label (text) used for the link
            'icon' => '',//The full image tag for the link,
                         //uses the full image tag so that
                        //you can specify "alt", note that this is not used in 5.0 new design.
            'active' => false, //boolean, if true the "active" css will be used,
                                //by default this makes the bg color gray for a highlight effect.  Would
                                //set this to true when you detect the user
                                //is currently viewing the page being linked to.
        );

        //if just returning 1 link, could just return that link array, like this:
        return $linkArray;

        //If you needed to create multiple links, you could return an array of links:
        $links = array ($linkArray);//we only have 1 link in the array but you could add more

        //return the array of link arrays
        return $links;
    }

    /**
     * Mostly used in admin on fields to use page, this allows you to add additional
     * "display locations" that can be checked or un-checked in the admin panel
     * for each field.  For instance if you wanted to add a new location for
     * "Example Pages".
     *
     * @param array $vars Associative array, see method's source for full documentation.
     * @return array An array of locations in the format of method's source.
     * @since Geo Version 5.0.0
     */
    public function core_geoFields_getDefaultLocations($vars)
    {
        //These are the 2 vars that are passed in, to allow only adding, or not
        //adding locations based on whether at the category or group specific fields or not.
        $categoryId = $vars['categoryId'];
        $groupId = $vars['groupId'];

        //expected to return using following format:
        $return = array (
            'index' => 'Example Pages',
            //you can add as many locations as you want.
        );
        //That just added a new location, labeled Example Pages in the fields to use admin
        return $return;

        //Below is a snippet of code that might be used to get and use this location we just added

        $fields = geoFields::getInstance($groupId, $categoryId);//group ID for current user logged in..

        $displayIndexIn = $fields->getDisplayLocationFields('index', 'example');

        if ($displayIndexIn['title']) {
            //display the title in this location
            //...
        }
        if ($displayIndexIn['description']) {
            //display description in this location
            //...
        }
        if ($displayIndexIn['addon_example_widget']) {
            //display addon example widget field in this location
            //...
        }
    }

    /**
     * Mostly used in admin on fields to use page, this allows you to add additional
     * "fields" that can have the same settings as other fields in the fields to use admin panel.
     * For instance if you wanted to add a new field for "Widget".
     *
     * @param array $vars Associative array, see method's source for full documentation.
     * @return array An array of fields in the format of method's source.
     * @since Geo Version 5.0.0
     */
    public function core_geoFields_getDefaultFields($vars)
    {
        //These are the 2 vars that are passed in, to allow only adding, or not
        //adding fields based on whether at the category or group specific fields or not.
        $categoryId = $vars['categoryId'];
        $groupId = $vars['groupId'];

        //expected to return using following format:
        $return = array (
            'addon_example_widget' => array (
                /**
                 * NOTE: We HIGHLY recommend prepending the "field index" (addon_example_widget)
                 * with your addon name to avoid field name collisions with other addons
                 * or possible future added core fields
                 */
                'label' => 'Widgets',
                'type' => 'other',
                'type_label' => 'Example Field',//this one is optional
                'skipData' => array (), //this one is optional, to skip all the main
                    //checkboxes you would specify:
                    //array ('is_enabled', 'is_required', 'is_editable')
            ),
            //you can add as many fields as you want.
        );
        //That just added a new field, labeled Widgets in the fields to use admin
        return $return;

        //Below is an alternative way, to make the type_data serve as an additional on/off switch
        //that is used in the "field length" column.  This ability added in version 5.1.0
        $return = array (
            'addon_example_widget' => array (
                /**
                 * NOTE: We HIGHLY recommend prepending the "field index" (addon_example_widget)
                 * with your addon name to avoid field name collisions with other addons
                 * or possible future added core fields
                 */
                'label' => 'Widgets',
                'type' => 'other',
                'type_extra' => 'on_off',//let system know type_data serves as simple on/off switch
                'type_extra_label' => 'Additional On/Off Switch',//this is the label for the switch

                'type_label' => 'Example Field',//this one is optional
                'skipData' => array (), //this one is optional, to skip all the main
                    //checkboxes you would specify:
                    //array ('is_enabled', 'is_required', 'is_editable')

            ),
            //you can add as many fields as you want.
        );

        //Below is a snippet of code that might be used to get and use this field we just added,
        //this would be used elsewhere in your addon, say in a custom order item.

        $fields = geoFields::getInstance($groupId, $categoryId);//group ID for current user logged in..

        if ($fields->addon_example_widget->is_enabled) {
            //Do something when the field is enabled
            //...
        }
        if ($fields->addon_example_widget->is_required) {
            //do something when the field is required
            //...
        }
        if ($fields->addon_example_widget->can_edit) {
            //do something when the field can be edited
            //...
        }
        if ($fields->getField('addon_example_widget')->can_edit) {
            //the same thing as the if statement above this one, but without using
            //magic methods to get the field object
            //...
        }
    }

    /**
     * Used in module_title to allow addons to add text to the current page's title,
     * as displayed by the title module.  Just return the text to add to the page.
     *
     * Note that if text is returned here, the "default text" that is used on
     * misc. pages would not be used, but other "dynamic" title text would still
     * be displayed, like listing or category title.  This text is inserted AFTER
     * that text, but BEFORE the "page number" text if on a 2nd or nth page when
     * browsing categories.
     *
     * @return string Text to add to the current page's title that is displayed by
     *   the title module.
     * @since Geo Version 5.0.2
     */
    public function core_module_title_add_text()
    {
        //since this is just example, don't add anything
        return '';

        //But if wanted to, could add something to the title like this:
        return 'Page Title from Example Addon';
    }

    /**
     * Used in module_title to allow addons to add text to the current page's title,
     * as displayed by the title module.  Just return the text to add to the page.
     *
     * This one prepends text to front of title.
     *
     * @return string Text to add to the current page's title that is displayed by
     *   the title module.
     * @since Geo Version 5.1.2
     */
    public function core_module_title_prepend_text()
    {
        //since this is just example, don't add anything
        return '';

        //But if wanted to, could add something to the title like this:
        return 'Prepend Page Title from Example Addon';
    }

    /**
     * Used in module_search_box_1 to allow addons to add additional search criteria
     * to search box.
     *
     * @param array $vars See inline PHP comments for documentation
     * @return string Text to add to search box search criteria
     * @since Geo Version 5.1.2
     */
    public function core_module_search_box_add_search_fields($vars)
    {
        //The following vars are passed in from the module.
        $page = $vars['page'];
        $show_module = $vars['show_module'];
        //return text to add to search box
        return '';
    }

    /**
     * Used in admin panel home page, to add additional news feeds to the news
     * section.  Just return the text to add to the news section.
     *
     * Note that this is called in an AJAX call.
     *
     * @return string Text to add to the news section on the admin home page
     * @since Geo Version 5.1.2
     */
    public function core_admin_home_display_news()
    {
        //This is an example of how to display news feed from specific RSS feed

        //Change this URL to the RSS feed URL you want to pull the news feed from.
        $rss_url = 'http://geodesicsolutions.com/demo/rss_listings.php';

        $reader = new rss_reader($rss_url);

        //set the title displayed above the feed
        $reader->setTitle("Example Addon News Feed");

        //make it display 5 entries from feed
        $reader->setMaxEntries(5);

        return $reader->get_feed_html();
    }

    /**
     * Used in cron job that closes the listing, at the point before each listing
     * is closed.
     *
     * @param array $vars Associative array, see method's source for full documentation.
     * @return bool Return true to skip normal closing that is performed for the listing.
     * @since Geo Version 5.1.4
     */
    public function core_cron_close_listings_skip_listing($vars)
    {
        //The listing object for the listing that is about to be closed
        $listing = $vars['listing'];

        //if need the cron object, use this
        $cron = geoCron::getInstance();

        //return anything other than boolean true will NOT skip closing the listing.
        return false;

        //If you did want to skip closing this listing for whatever reason, you would:
        return true;
    }
    /**
     * Used on My Active Listings page, to add action buttons.
     * These are the buttons that appear when a "Manage This Listing" button is clicked.
     *
     * @param array $vars Associative array, see method's source for full documentation.
     * @return string HTML to insert as a button
     */
    public function core_current_listings_add_action_button($vars)
    {
        $listingId = $vars['listingId']; //numerical ID of the listing being displayed
        $html = ''; //return the HTML to show
        return $html;
    }

    /**
     * Called at end of My Active Listings page, to allow addons to manipulate page data.
     * @since 7.0.4
     */
    public function core_current_listings_end()
    {
        $view = geoView::getInstance(); //most commonly, use this to manipulate View variables
        $view->listings; //holds data on active listings to be shown on this page
        $view->pending; //holds data on pending listings to be shown on this page
    }

    /**
     * Used to display extra stuff at the very bottom of the login form, below all the normal
     * login buttons.
     *
     * @param $vars Associative array, see method's source for full documentation.
     * @return string Expects to return a string
     * @since Geo Version 6.0.0
     */
    public function core_display_login_bottom($vars)
    {
        //encode is the var passed into the login form function.  Added in
        //version 6.0.2.
        $encode = $vars['encode'];

        return '';
    }

    /**
     * Used to display extra stuff at the top of the registration code form page
     *
     * @return string Expects to return a string
     * @since Geo Version 6.0.0
     */
    public function core_display_registration_code_form_top()
    {
        return '';
    }

    /**
     * Used to display extra stuff at the top of the registration form page
     *
     * @return string Expects to return a string
     * @since Geo Version 6.0.0
     */
    public function core_display_registration_form_top()
    {
        return '';
    }

    /**
     * Allows addon to manipulate the query that counts how many listings are
     * in a particular category.  This is called from {@see geoCategory::getListingCount()}
     * called after the main parts have been added, but before the parts restricting it
     * to classified-only or auction-only count are done, and before it is converted
     * into a "count" query.
     *
     * This is a notification type event so the return value is ignored.
     *
     * @param array $vars See source code for documentation
     * @since Geo version 6.0.4
     */
    public function core_geoCategory_getListingCount($vars)
    {
        //The category ID where the listings are being counted.
        $category_id = $vars['category_id'];

        //Var as passed into geoCategory::getListingCount()
        $force_on_fly = $vars['force_on_fly'];

        //Var as passed into geoCategory::getListingCount()
        $ignore_filters = $vars['ignore_filters'];

        //The geoTableSelect object that will be used to generate the query.
        $query = $vars['query'];

        //You would manipulate the $query object to change or add where clauses,
        //see the geoTableSelect PHP Docs for more info
    }

    /**
     * Way to add stuff to admin panel user account info page.
     * @param int $user_id
     * @since Geo Version 6.0.0
     */
    public function core_Admin_site_display_user_data($user_id)
    {
        //$user_id is the user ID for user being displayed in admin panel.

        //here is example of how to display stuff
        return geoHTML::addOption("Example Label", "Example Data");
    }

    /**
     * Way to update user info in admin panel for user info page.
     * @param int $user_id
     * @since Geo Version 6.0.0
     */
    public function core_Admin_user_management_update_users_view($user_id)
    {
        //$user_id is user id of user being updated
        //would do stuff like saving data for the user based on form submitted.
    }

    /**
     * Allows adding more settings on group edit page in the "user group details"
     * section.  This is normal string return type, just return the text to add,
     * using normal leftColumn rightColumn classes.
     *
     * @param array $vars see source for docs on this.
     * @return string
     * @since Geo Version 6.0.0
     */
    public function core_Admin_Group_Management_edit_group_display($vars)
    {
        //the group ID being edited
        $group_id = $vars['group_id'];
        //the admin class object
        $admin_group_object = $vars['this'];

        return '';
    }

    /**
     * Allows updating settings on group edit page in the "user group details"
     * section.  This is called as "notify" type so return value is ignored.
     *
     * @param array $vars see source for docs on this.
     * @since Geo Version 6.0.0
     */
    public function core_Admin_Group_Management_edit_group_update($vars)
    {
        //the group ID being edited
        $group_id = $vars['group_id'];
        //the admin class object
        $admin_group_object = $vars['this'];

        //Note:  can add admin messages if there are problems...
    }

    /**
     * Allows an addon to restrict new listing creation based on arbitrary criteria
     * @param Array $userdata created by geoUser's toArray() for the current user
     * @return boolean true to stop new listing placement | false to allow it to continue
     */
    public function core_prevent_new_listing($userdata)
    {
        if ($userdata['firstname'] !== 'Awesome') {
            //this user isn't awesome enough to make a new listing
            geoCart::getInstance()->addErrorMsg('name_error', 'You must be more awesome to proceed.');
            return true;
        }
        return false;
    }

    /**
     * Returns true if an addon has "listing icons" to show. Listing icons are the "sold," "reserve met," etc image badges that appear on listings
     * @return boolean
     */
    public function core_use_listing_icons()
    {
        $reg = geoAddon::getRegistry($this->name);
        return (bool) $reg->use_neighborly;
    }

    /**
     * Used to add "listing icons" to a specific listing. Listing icons are the "sold," "reserve met," etc image badges that appear on listings.
     * See also {@link addon_example_util::core_use_listing_icons()}
     * @param Array $data raw listing data for the listing in question
     * @return string
     */
    public function core_add_listing_icons($data)
    {
        //TODO: implement
        return $htmlForIcons;
    }

    /**
     * Allows rewriting a single URL (typically via the SEO addon)
     * @param Array $vars
     * @return String
     */
    public function core_rewrite_single_url($vars)
    {
        $originalUrl = $vars['url'];
        $forceNoSSL = $vars['forceNoSSL']; //if true, rewritten url should always use http:, even if on an https: page
        return $rewrittenUrl;
    }

    /**
     * Used by "My Listing Filters" to add addon conditions to filters. Returns column header for "show all filters" page
     * @return string
     */
    public function core_show_listing_alerts_table_headers()
    {
        return 'header text';
    }
    /**
     * Used by "My Listing Filters" to add addon conditions to filters. Returns body text for a specific filter on "show all filters" page
     * @param int $filter_id ID of filter to return info for
     * @return string
     */
    public function core_show_listing_alerts_table_body($filter_id)
    {
        return 'body text';
    }
    /**
     * Used by "My Listing Filters" to add addon conditions to filters. Displays HTML for adding a new filter
     * @return string
     */
    public function core_display_add_listing_alert_field()
    {
        return 'form html';
    }
    /**
     * Used by "My Listing Filters" to add addon conditions to filters. Processes form data when adding a new filter
     * @param Array $vars
     */
    public function core_update_add_listing_alert_field($vars)
    {
        $filter_id = $vars['filter_id'];
        $info = $vars['info']; //$_POST['d'] from form
    }
    /**
     * Used by "My Listing Filters" to delete data associated with a filter
     * @param int $filter_id
     */
    public function core_delete_listing_alert($filter_id)
    {
    }
    /**
     * Used by "My Listing Filters" to determine whether a filter is matched for a given listing
     * @param unknown_type $vars
     * @return string one of NO_DATA|MATCH|NO_MATCH
     */
    public function core_check_listing_alert($vars)
    {
        $listing = $vars['listing'];
        $filter_id = $vars['filter_id'];

        if ($filterNotInUse) {
            return 'NO_DATA';
        } elseif ($filterMatches) {
            return 'MATCH';
        } else {
            return 'NO_MATCH';
        }
    }
    /**
     * Used by "My Listing Filters" as a convenience method for obtaining email-friendly result text.
     * @param int $filter_id
     * @return string
     */
    public function core_show_listing_alert_filter_data($filter_id)
    {
        //most addons can use this as-is to copy the values used on the "show all filters" table
        return $this->core_show_listing_alerts_table_headers() . ': ' . $this->core_show_listing_alerts_table_body($filter_id);
    }
}
