<?php

//addons/example/tags.php
/**
 * Optional file, used for addon tags on the client side.
 *
 * Remember to rename the class name, replacing "example" with
 * the folder name for your addon.
 *
 * @package ExampleAddon
 */


# Example Addon

/**
 * Expects one function for each tag.  Function name should be the same as
 * the tag name.  Can also have a constructor if anything needs to be constructed.
 *
 * NOTE:  We make it extend the addon_example_info simply so that the addon information
 * is easily available using local variables, like $this->name and such.
 *
 * @package ExampleAddon
 */
class addon_example_tags extends addon_example_info
{
    /**
     * Since this tag is defined in $tags in the info.php file, it can
     * be called using the {addon} tag syntax:
     *
     * {addon author='geo_addons' addon='example' tag='tag_name1'}
     *
     * @param array $params The array of parameters that were part of the tag,
     *   not including the built-in tag parameters such as addon and author.
     * @param Smarty_Internal_Template $smarty The internal template object, for
     *   the current template that is being rendered
     * @return string Text to be inserted, or results of calling {@see geoTemplate::loadInternalTemplate()}
     */
    public function tag_name1($params, Smarty_Internal_Template $smarty)
    {
        /*
         * 2 ways to do things:
         *
         * Method 1: Use smarty template loaded internally(Recommended):
         *
         * This method is ideal for replacing a tag with a bunch
         * of text, and helps to keep business logic seperate
         * from view, yada yada...  And by using geoTemplate::loadInternalTemplate()
         * that will make your tag have a lot of added "customization" options.
         *
         *
         *
         * Method 2: just return or echo the HTML text:
         *
         * See function tag_name2 for method 2
         */

        //You will almost always be setting template variables, here is an example
        //of the easiest way to do this:
        $tpl_vars = array();

        //Example of passing in variable display_hello_world which will be accessible
        //in your smarty template as {$display_hello_world}
        $tpl_vars['display_hello_world'] = true;

        //You would set any other template variables here
        $tpl_vars['var1'] = 'value 1';
        $tpl_vars['var2'] = 'value 2';

        //NOTE:  The following will have NO EFFECT!!
        $view->addCssFile(geoTemplate::getUrl('css', 'addon/example/example_external_css_file_NOT_ADDED.css'));
        /*
         * ABOVE HAS NO EFFECT!!!  This is because this tag is being called at
         * the time the template is rendered, it is "too late" to make any calls
         * that would add things to the {head_html}.  Anything like this, you
         * will need to create a second method for the tag, the
         * tag name + "_auto_add_head", see the example in this class:
         *
         *    tag_name1_auto_add_head()
         *
         * for a demonstration of how to do this.
         */


        /*
         * Now, return the results of loading an internal template.  This will do
         * a lot of the advanced stuff for us, like allow the tag's contents to
         * be assigned to a template variable in the parent template, or allowing
         * the template variables to be over-written by tag parameters passed in
         * for the tag.  It also allows the parent template variables to be
         * accessible inside the tag_name1.tpl template.  In this case we will
         * use the template tag_name1.tpl which is an addon template, you can
         * see the contents in "templates/tag_name1.tpl" inside the example
         * addon folder.
         */
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'tag_name1.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    /**
     * All tags are called "on the fly" at the time the template is being rendered.
     * This means that at that point, it will be too late to do anything like
     * adding contents to the {head_html} tag through $geoView->addCssFile() or
     * similar.
     *
     * To allow tags to add things to the {header_html}, we've extended the Smarty
     * system so that it caches the tags that are in each template.  Then before
     * rendering the template, it will pre-parse those tags.  Any addon tags that
     * have a method in the tag class, with the same name as the tag, appended
     * with "_auto_add_head", will be called "in time" to allow adding things
     * that affect the {header_html}.
     *
     * Since this method exists in the example, it will be called any time a template
     * that has the tag_name1 tag in it, is being rendered, and this will be called
     * in time to allow adding CSS or JS to the {head_html}.
     *
     * Note that this would be called simply by having the addon tag in a template
     * being used, regardless of whether that tag actually "renders" or not, meaning
     * this will be called even if the actual addon tag is embedded in an "if"
     * block and ends up not getting displayed.
     */
    public function tag_name1_auto_add_head()
    {
        //This is where you might make calls to add CSS, JS, or anything else that
        //would be added to the {head_html} tag.
        $view = geoView::getInstance();

        //this is just an example...  The CSS file does not actually contain any
        //CSS for the tag...  This is just to demonstrate how you would add CSS
        //to be called in the {head_html} whenever a specific tag is being used
        //on the page.
        $view->addCssFile(geoTemplate::getUrl('css', 'addon/example/example_external_css_file.css'));

        //This method is only called to allow adding / manipulating the View class
        //and it does not expect any return value
    }

    /**
     * Since this tag is defined in $tags in the info.php file, it can
     * be called using the {addon} tag syntax:
     *
     * {addon author='geo_addons' addon='example' tag='tag_name2'}
     *
     * @param array $params The array of parameters that were part of the tag,
     *   not including the built-in tag parameters such as addon and author.
     * @param Smarty_Internal_Template $smarty The internal template object, for
     *   the current template that is being rendered
     * @return string Text to be inserted, or results of calling {@see geoTemplate::loadInternalTemplate()}
     */
    public function tag_name2($params, Smarty_Internal_Template $smarty)
    {
        /*
         * 2 ways to do things:
         *
         * Method 1: Use smarty template loaded internally(Recommended):
         *
         * see function tag_name1 for method 1
         *
         *
         * Method 2: just echo or return the HTML text:
         *
         * This method is ideal for short 1-2 line of text that would not
         * really benifit by using a smarty template.  Using this way bypasses
         * all of the advanced functionality for addon tags, such as being able
         * to assign the contents of the addon tag to a variable by adding
         * assign='var_name' to the {addon} tag.
         */

        //NOTE:  While it is "possible" to echo out contents, it is discouraged,
        //and should only ever be used as a quick shortcut, for instance when
        //trying to quickly convert {php} snippet into an addon tag.
        //You should never echo or output text in a "production" addon, as it is
        //considered bad practice.
        echo 'Example echo in tag 2!';

        //You can also just return the contents, or can even do both!  When there
        //is both echo'd contents, and contents returned, both are displayed.

        return 'Example return Tag text 2.';
    }

    /**
     * Since this tag is defined in $listing_tags in the info.php file, it can
     * be called using the {listing} tag syntax:
     *
     * {listing addon='example' tag='listing_tag_example'}
     *
     * Unlike normal {addon} tags, {listing} tag will always have $params['listing_id']
     * set in the $params passed in.  If the tag is used somewhere that a listing
     * ID can't be pre-determined, it will never call this function in the first
     * place.
     *
     * NOTE: {listing} tags added in Geo version 7.1.0
     *
     * @param array $params The array of parameters that were part of the tag,
     *   not including the built-in tag parameters such as addon and author.
     * @param Smarty_Internal_Template $smarty The internal template object, for
     *   the current template that is being rendered
     * @return string Text to be inserted, or results of calling {@see geoTemplate::loadInternalTemplate()}
     */
    public function listing_tag_example($params, Smarty_Internal_Template $smarty)
    {
        /*
         * Since this is defined in $info->listing_tags, that means it needs to
         * use the {listing} tag to display.  Part of the "special functionality"
         * of a listing tag is that in the $params passed in, it will always have
         * the listing ID which you can see like so:
         */
        $listing_id = $params['listing_id'];

        /*
         * Note on Backwards/Sideways Compatibility: In order to allow the same
         * tag to be used for different "tag types", all tags will always have
         * just 2 parameters passed in.  You are able to use the same tag in
         * different tag variables, you would just need to code it so that it would
         * work for either one.
         */

        //Get the listing object
        $listing = geoListing::getListing($listing_id);

        //NOTE: we are using method 1 as documented in tag_name1() function in
        //this class.  We recommend this method to allow for maximum
        //customization options

        //This is the array of template vars we will send into the internal template
        $tpl_vars = array();

        //pass in the listing title. Remember, the $listing object holds the raw
        //listing info as it is stored in the DB!  Don't forget to format any
        //fields as appropriate
        $tpl_vars['listing_title'] = geoString::fromDB($listing->title);

        //Set any other template vars you need
        $tpl_vars['title_color'] = 'green';

        /*
         * Now, return the results of loading an internal template.  This will do
         * a lot of the advanced stuff for us, like allow the tag's contents to
         * be assigned to a template variable in the parent template, or allowing
         * the template variables to be over-written by tag parameters passed in
         * for the tag.  It also allows the parent template variables to be
         * accessible inside the listing_tag_example.tpl template.  In this case we will
         * use the template listing_tag_example.tpl which is an addon template, you can
         * see the contents in "templates/listing_tag_example.tpl" inside the example
         * addon folder.
         */
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'listing_tag_example.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
