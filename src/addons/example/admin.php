<?php

//addons/example/admin.php
/**
 * Optional file, used to add and display admin pages specific to this
 * addon. Also used to setup addon text for this addon.
 *
 * Remember to rename the class name, replacing "example" with
 * the folder name for your addon.
 *
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
## ##    2.5.0
##
##################################

# Example Addon

/**
 * In charge of doing stuff on the admin side, this is optional.
 *
 * @package ExampleAddon
 */
class addon_example_admin extends addon_example_info
{
    /**
     * Optional function. Initialize admin pages and let the admin page loader know the pages exist.
     * This will only get run if the addon is installed and enabled.
     *
     * Add Categories syntax, see {@link menu_category::addMenuCategory()}
     *
     * Add a new addon page, see {@link menu_page::addonAddPage()}
     *
     * See the function source for an example of how to initialize a new
     * category, create two simple admin pages, and link to those pages
     * from the menu.
     *
     * @param string $menuName The name of the menu that is being loaded for
     *   the admin panel.  The default is core_admin, this is not used much yet
     *   so for now expect it to always be "core_admin"
     * @see Admin.class.php for documentation on addPage and addMenuCategory
     */
    public function init_pages($menuName)
    {
        //Used to initialize the admin pages.  See admin/php5_classes/Admin.class.php
        //for documentation on the addonAddPage and addMenuCategory

        /*
         * Add Categories syntax (read documentation in Admin.class.php)
         * NOTE: Addon system automatically creates a category and places all "main page" pages
         *  in that category.  The category created would be "Addon Management > [Addon Title]",
         *  but if you wish to also create another category, this is how to do it.
         *
         * Add category menu syntax:
         */
        #menu_category::addMenuCategory($index,$parent,$title,$image,$filename,$classname);



        /*
         * Note: For $parent, you can specify an empty string to have it only put in
         *  the auto-created category for the addon.  See menu_page::addonAddPage documentation.
         *
         * Add a new page addon page Syntax (read documentation in Admin.class.php):
         */
        #menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type, $replace_existing);


        /*
         * No matter what, the system will create a category under addon management and will place
         * all pages there in addition to any parent category you specify.  If you want, just call
         * addonAddPage with an empty string for the parent, and let the system do the rest.
         *
         * Oh, and this is also just a way to remind you that HTML is allowed in the title of the page.
         */
        menu_page::addonAddPage('addon_example_hello_world', '', 'Addons are <em style="text-decoration: underline;">Fun!</em>', 'example', $this->icon_image);

        //Add an admin category, under the Addon category.
        menu_category::addMenuCategory('addon_example_admin', 'beta_tools', 'Example Addon Category', '', '', '');
            //add main config
            menu_page::addonAddPage('addon_example_main_config', 'addon_example_admin', 'Example General Settings', 'example', $this->icon_image);
            //add extra config
            menu_page::addonAddPage('addon_example_extra_config', 'addon_example_admin', 'Example Extra Settings', 'example', $this->icon_image);
            //add tag help page.
            menu_page::addonAddPage('addon_example_tag_help', 'addon_example_admin', 'Example Tag Help', 'example', $this->icon_image);
    }

    /**
     * This internal variable is NOT used directly by the addon system.
     * It is the array returned by {@link addon_example_admin::init_text()}
     *
     * @var array Used as return value for function {@link addon_example_admin::init_text()}
     * @see addon_example_admin::init_text()
     */
    private static $default_addon_text = array
    (
        'text_index1' => array ( //text_index1 is the text_id
            'name' => 'First Text Message', //name is used in the admin section for editing text messages
            'desc' => 'This is the first text message that the example addon uses.', //desc is used in the admin section for editing text messages
            'type' => 'textarea', //type is either textarea, or input, and designates what form will be used to edit the text in the admin.
            'default' => 'Default Text Value' //default is used when installing the addon, to set the default value for the text.
        ),
        'text_index2' => array (
            'name' => 'Second Text Message',
            'desc' => 'This is the second message that the example addon uses.  It is not actually used anywhere on the client side..',
            'type' => 'input',
            'default' => 'Default Text Value'
        )
    );

    /**
     * Optional function that should return details about the text
     * that this addon will be using on the client side.
     * @param Int $language_id
     * @return Array Associative array as documented by {@link addon_example_admin::$default_addon_text}
     */
    public function init_text($language_id)
    {
        //The language id can be ignored, or if you already somehow
        //know what language a certain id will be, you can give details
        //specific to that language.

        //For this example, we will just ignore the language id.

        //return the class var, so that it can be documented to be used
        //in phpdocs
        return self::$default_addon_text;
    }

    /**
     * Will be called to display the page addon_example_hello_world, which was added in
     * {@link addon_example_admin::init_pages()}.  If creating your own admin page, the function name must be
     * display_INDEX () where INDEX is the index specified when addonAddPage() is called in
     * {@link addon_example_admin::init_pages()}.
     *
     * You can either specify a template to be used to display the main part of the page (recommended),
     * or you can add HTML to the body of the page using {@link geoView::addBody()}.
     */
    public function display_addon_example_hello_world()
    {
        //responsible for adding stuff to be displayed in the main part of the page.  Note: v() is alias for getView()

        //This one uses a smarty template to render the main part of the admin:
        geoView::getInstance()->setBodyTpl('hello_world_admin.tpl', 'example');

        /*
         * Thats it!  The geoAdmin class takes care of the rest of the
         * page rendering, all we do is set the template and assign any
         * vars.  You can have more control over
         * how the page is displayed if you need, see geoAdmin class
         * documentation on that.
         */

        /*
         * If our template needs some settings, we can do that too:
         * Note: The following is just an example of how to pass variables to
         * be used by the body template.
         */

        $vars = array();
        //access this in template using {$setting1}
        $vars ['setting1'] = 'First Setting.';

        //accessed in template: {$setting2}
        $vars ['setting2'] = 'Second Setting.';

        $vars ['product_version'] = $this->version;

        //Works just like the smarty function assign() works, except that
        //the var is local to only the body template.
        geoView::getInstance()->setBodyVar($vars);
    }


    /**
     * Will be called to display the page addon_example_main_config, which was added in
     * {@link addon_example_admin::init_pages()}.  If creating your own admin page, the function name must be
     * display_INDEX () where INDEX is the index specified when addonAddPage() is called in
     * {@link addon_example_admin::init_pages()}.
     *
     * You can either specify a template to be used to display the main part of the page (recommended),
     * or you can add HTML to the body of the page using {@link geoView::addBody()}.
     */
    public function display_addon_example_main_config()
    {
        //responsible for adding stuff to be displayed in the main part of the page.

        //be sure to display any messages at the top of the page, if there are any.
        $html = geoAdmin::getInstance()->message();

        //This is just an example addon, there are not any settings to display.
        $html .= "There are no settings for the example addon.<br />\n";

        //Just to demonstrate, to have update_addon_example_main_config() be run, something
        //like this must been done:
        $html .= "<form action=\"\" method=\"POST\"><input type=\"submit\" name=\"auto_save\" value=\"Example Auto Update\" /></form>";

        //Add to the body of the admin page.
        geoAdmin::getInstance()->v()->addBody($html);

        //Note that unlike previous versions prior to geo 4.0, you had to call display_page.  Now that is done for you,
        //you just need to either add to the body of the page, or specify what template to use for the body of the page.
    }

    /**
     * Will be called to display the page addon_example_extra_config, which was added in
     * {@link addon_example_admin::init_pages()}.  If creating your own admin page, the function name must be
     * display_INDEX () where INDEX is the index specified when addonAddPage() is called in
     * {@link addon_example_admin::init_pages()}.
     *
     * You can either specify a template to be used to display the main part of the page (recommended),
     * or you can add HTML to the body of the page using {@link geoView::addBody()}.
     */
    public function display_addon_example_extra_config()
    {
        //responsible for adding stuff to be displayed in the main part of the page.

        //be sure to display any messages at the top of the page, if there are any.
        $html = geoAdmin::getInstance()->message();

        //This is just an example addon, there are not any settings to display.
        $html .= "There are no extra settings for the example addon.<br />\n";

        //Just to demonstrate, to have update_addon_example_main_config() be run, something
        //like this must been done:
        $html .= "<form action=\"\" method=\"POST\"><input type=\"submit\" name=\"auto_save\" value=\"Example Auto Update\" /></form>";

        //add the text to the body of the page.
        geoAdmin::getInstance()->v()->addBody($html);
    }

    /**
     * Will be called to display the page addon_example_tag_help, which was added in
     * {@link addon_example_admin::init_pages()}.  If creating your own admin page, the function name must be
     * display_INDEX () where INDEX is the index specified when addonAddPage() is called in
     * {@link addon_example_admin::init_pages()}.
     *
     * You can either specify a template to be used to display the main part of the page (recommended),
     * or you can add HTML to the body of the page using {@link geoView::addBody()}.
     */
    public function display_addon_example_tag_help()
    {
        //responsible for creating & diplaying the entire page, including the header and footer.

        //This is just an information page, it needs no update functionality.
        //display information about the available tags by using a template

        //another way to add to the body...  since geoAdmin::getInstance()->v() returns
        //an instance of the view object, you can also just get the view object directly,
        //if you prefer to do it that way (as opposed to how the other example display
        //functions do it).
        $view = geoView::getInstance();
        $view->setBodyTpl('admin/tag_help.tpl', $this->name);
    }

    /**
     * Auto save function.  Name must be update_INDEX() where INDEX is
     * the index used in the addonAddPage() called in {@link addon_example_admin::init_pages()}.
     *
     * This function is automatically called if there is a POST variable named "auto_save" that
     * evaluates to true.  For increased security, it is recommended
     * to use the auto update function instead of saving anything to
     * the database from inside a display function.
     *
     * See documentation in source code for the function for an example of how to
     * use the update functions.
     *
     * The update functions go hand-in-hand with its display
     * counterpart {@link addon_example_admin::display_addon_example_main_config()}, and that
     * display function is called after the update function is finished.
     *
     * @return bool true if the update was a success, or false if it failed.  If no user
     *  messages have been added, the results will be displayed to the user whether or
     *  not settings have been saved or not.
     */
    public function update_addon_example_main_config()
    {

        //get an instance of the admin object, so we can add a message to display to the user.
        $admin = geoAdmin::getInstance();

        //lets add a message that will be displayed to the user.
        $admin->message("Settings saved, although nothing was actually saved since this is just an example addon.");

        //return true, since settings were saved.
        return true;
    }

    /**
     * Auto save function.  Name must be update_INDEX() where INDEX is
     * the index used in the addonAddPage() called in {@link addon_example_admin::init_pages()}.
     *
     * This function is automatically called if there is a POST variable named "auto_save" that
     * evaluates to true.  For increased security, it is recommended
     * to use the auto update function instead of saving anything to the
     * database from inside a display function.
     *
     * See documentation in source code for the function for an example of how to
     * use the update functions.
     *
     * The update function goes hand-in-hand with its display
     * counterpart {@link addon_example_admin::display_addon_example_extra_config()}, and that
     * display function is called after the update function is finished.
     *
     * @return bool true if the update was a success, or false if it failed.  If no user
     *  messages have been added, the results will be displayed to the user whether or
     *  not settings have been saved or not.
     */
    public function update_addon_example_extra_config()
    {
        //Actually, since we are only using the geoAdmin object for 1 call, we don't even need to assign it to a
        //variable really, like we did in the other update functions, but that is mostly up to personal
        //preference.  This is a shorter way to do it.

        //We can add several messages to be displayed in 1 "chained" step.
        geoAdmin::getInstance()->message("Settings saved, although nothing was actually saved since this is just an example addon.")
            ->message('This message is an example of the awesomeness of chaining functions.', geoAdmin::NOTICE);

        //TODO: Demonstrate some fancy ajax calls using geoCJAX and an addon ajax controller.

        //return true, since settings were saved.
        return true;
    }
}
