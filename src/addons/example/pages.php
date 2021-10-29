<?php

//addons/example/pages.php
# NOTE: If you are viewing the source, note that you can view phpdocs in the
# docs/ folder.
/**
 * Optional file, used to display an entire page.  Works kind of like
 * the tags, except that the tag being replaced is the main body tag.
 *
 * Note that this version of the Example Addon is only compatible with
 * at least Geo 4.0, since stuff has changed since previous versions.
 *
 * @package ExampleAddon
 * @since Geo Version 4.0.0
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
## ##    ccda4ac
##
##################################

# Example Addon

# Note: PHPDocs can be found in docs/ folder.

/**
 * Addon page class, used by the addon system to display pages on the client side.
 *
 * @package ExampleAddon
 * @since Geo Version 4.0.0
 */
class addon_example_pages
{
    /**
     * Works just like the tag functions.
     *
     * @return string Text to replace the main body tag, if not specifying a template file.
     */
    public function page1()
    {
        //2 ways to do things:
        //Method 1: Use smarty template (Recommended):
        //This method is ideal for replacing main body with a bunch
        //of text, and helps to keep business logic seperate
        //from view, yada yada...

        //First, get instance of the view class
        $view = geoView::getInstance();

        //get an instance of our info page, so we don't have stuff
        //hard coded in this file.
        $myInfo = Singleton::getInstance('addon_example_info');

        //use the template file addons/example/templates/page1.tpl:
        $view->setBodyTpl('page1.tpl', $myInfo->name);

        //example of assigning a template variable that is local in scope to the template
        //file.  see that function for more details.
        $view->setBodyVar('display_hello_world', true);


        //Method 2: just return the HTML text
        //See function tag_name2 for method 2

        //Note: you can actually do both, specify a template file and return text, in which
        //case the template will be included, and the returned text will be appended to the
        //end.

        return '';
    }

    /**
     * Works just like the tag functions.
     *
     * @return string Text to be inserted, if not using a template.
     */
    public function page2()
    {
        //2 ways to do things:
        //Method 1: Use smarty template (Recommended):
        //see function page1 for method 1

        //Method 2: just return the HTML text
        //This method is ideal for short 1-2 line of text that
        //would not really benifit by using a smarty template.

        return 'Example Page text 2.';
    }

    /**
     * This is an internal page, this page is used during purchasing an eWidget,
     * but this method is NOT used to display it.
     *
     * @return string Text to display
     */
    public function youAreCool()
    {
        //This page should not be visited directly, go ahead and display
        return 'Page used internally only.';
    }

    /**
     * This is an internal page, this page is used during purchasing an eWidget,
     * but this method is NOT used to display it.
     *
     * @return string Text to display
     */
    public function almostFinished()
    {
        //This page should not be visited directly, go ahead and display
        return 'Page used internally only.';
    }
}
