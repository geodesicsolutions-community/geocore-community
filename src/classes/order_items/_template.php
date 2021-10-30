<?php

//order_items/_template.php
/**
 * This is a "developer template" that documents most, if not all, of what an
 * order item can do in the system.
 *
 * @package System
 * @since Version 4.0.0
 */



/**
 * Developers: use this file as a template to create a new order item.
 *
 * If this is a 3rd party order item,
 * note that you can have order items inside of addons.  See the examle addon, in the folder
 * order_items in that addon.<br />
 *
 * Steps:
 *
 * 1.  Rename the file to something that does not start with an underscore, all files that start
 *     with an underscore are ignored by the order item system.  Pick something that will be unique
 *     to whatever the order item is, to prevent name collisions.
 * 2.  Rename the class name (below) to match the filename.  For instance, if the filename is
 *     my_order_item.php, the class name needs to be my_order_itemOrderItem.  Make sure you keep it
 *     so that it still extends the geoOrderItem class.
 * 3.  Change the class variable $type to match the filename, for instance if the filename is
 *     my_order_item.php, the line should read:<br />
 *     protected $type = "my_order_item";
 * 4.  Change the class var $defaultProcessOrder to be a number that represents the order it will be
 *     processed, and order it will be displayed in various places.  This is important, for instance
 *     if the defaultProcessOrder is higher than the defaultProcessOrder of the tax item, this item will not be
 *     used when calculating tax, and it will appear below the tax item when viewing the cart.  It should normally
 *     need to be something below 1000 for any "normal" order items.
 * 5.  Implement each of the template functions below that will be used by this order item, and delete
 *     or comment out the functions that you know you will not need to use and that are marked as
 *     optional.  Make sure you keep the required functions however (read the comments on each
 *     function).  As a way to keep track of what you have finished, as you go once you have
 *     implemented a function, remove the TODO comment at the top of the function.
 * 6.  Only functions that are not implemented by the parent class geoOrderItem are in this template,
 *     but you are not limited to those functions, if you wish you can over-write any of the functions
 *     already implemented in geoOrderItem, for instance if you wanted to change the behavior of getCost().
 *
 *  Note: If a method is defined as static, that means it will be called statically by the
 *  system, so keep this in mind when implimenting each method.
 *
 * @package System
 * @since Version 4.0.0
 */
class _templateOrderItem extends geoOrderItem
{

    /**
     * Set this to match the filename and the class name.
     *
     * If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = "_template";

    /**
     * Optional, use this as a hassle-free way to determine the type without having to hard-code
     * the type everywhere else, instead use self::type
     *
     */
    const type = '_template';

    /**
     * Needs to be the order that this item will be processed.  This is the default
     *
     * for example:  when computing tax the "tax function" (tax.php, defaultProcessOrder of 20,000)
     * will get all "totals" of all orderitems with a $defaultProcessOrder below 20,000 to get the
     * total amount to charge the tax on.
     *
     * System order item #'s:
     *
     *  * < 1000 - "normal" order item (such as listing)
     *  * 10,000 - subtotal order item
     *  * 20,000 - tax order item
     *
     * Note: total is handled by system, always at very bottom.
     *
     * note: different items can have the same defaultProcessOrder value.  Different criteria
     * then determine order like alphabetical
     *
     * @var int
     */
    protected $defaultProcessOrder = 10;
    /**
     * Optional, use this as a hassle-free way to determine the process order without having to hard-code
     * the # everywhere else, instead use self::defaultProcessOrder
     *
     */
    const defaultProcessOrder = 10;


    /**
     * Whether to display this in the admin panel.
     *
     * **Required.**
     *
     * **Used:** in admin, PricePlanItemManage class in various places.
     *
     * Return true to display this order item planItem settings in the admin,
     * or false to hide it in the admin.
     *
     * @return bool
     */
    public function displayInAdmin()
    {
        //TODO: implement...
        return true;
    }

    /**
     * Shows the configuration for plan item settings in the admin panel.
     *
     * **Optional.**
     *
     * **Used:** In admin, during ajax call to display config settings for a particular
     * price plan item.
     *
     * If this method exists, a config button will be displayed beside the item, and when
     * the config button is pressed, whatever this function returns will be displayed
     * below the item using an ajax call.
     *
     * @param geoPlanItem $planItem
     * @return string
     */
    public function adminPlanItemConfigDisplay($planItem)
    {
        //TODO: implement or remove...
        return '<div>
				This is where extra configuration settings would be displayed.
			</div>';
    }

    /**
     * Save any configuration changes for the plan item in the admin panel.
     *
     * **Optional.**
     *
     * **Used:** In admin, during ajax call to update config settings for a particular
     * price plan item.
     *
     * This is only used if adminPlanItemConfigDisplay() is used.
     *
     * @param geoPlanItem $planItem
     * @return bool If return true, message "settings saved" will be displayed, if return
     *  false, message "settings not saved" will be displayed.
     */
    public function adminPlanItemConfigUpdate($planItem)
    {
        //TODO: implement or remove...
        //example of saving a setting
        $value = $_POST['my_setting'];
        //Remember to do any input cleaning / validation!

        $planItem->set('my_setting', $value);

        //NOTE:  $planItem->save() is called automatically for you by the system
        //after this method is done, so there is no need to save the setting
        //changes here.

        return true;
    }

    /**
     * Get the basic details for this order item, used when showing list of order items in admin.
     *
     * **Optional**, but *required* if displayInAdmin() returns true.
     *
     * **Used:** in admin, display items in *Orders > Manage Items* (only for main items, not for sub-items)
     *
     * This should return an array containing details that will be used to display
     * the order item in the list of order items.  It is only used to show the
     * item type and the item title, allowing to use a title based on something
     * specific to the order item (for instance, a classified ad's title).
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        //TODO: implement or remove if displayInAdmin returns false...
        $session_variables = $this->get('session_variables');
        $title = $session_variables['classifieds_title'];
        if (strlen($title) > 20) {
            $title = '<span title="' . $title . '">' . geoString::substr($title, 0, 17) . '...' . '</span>';
        }
        $title = $this->getId() . ' - ' . $title;

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    /**
     * Display detailed informatino about this order item in the admin panel.
     *
     * **Optional.**
     * **Used:** In admin, when displaying an order item's details
     *
     * Return HTML for displaying or editing any information about this item, to
     * be displayed in the admin.  Should also call any children of this item.
     *
     * The other function that should work with this one, is adminItemUpdate, but note
     * that this is not typical.  Only time you would use adminItemUpdate is to
     * allow the admin to apply changes directly to the order item.  Note that the system
     * already does 'built in' changes, like changing the status.
     *
     * @param int $item_id
     * @return string
     */
    public static function adminItemDisplay($item_id)
    {
        //TODO: Implement or remove
        if (!$item_id) {
            return '';
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() != self::type) {
            //not right item type, need to do this check since adminItemDisplay()
            //is called for all order item types.
            return '';
        }

        $html = 'Details for item #' . $item_id;

        //Call children and let them display info about themselves as well
        $children = geoOrderItem::getChildrenTypes(self::type);
        $html .= geoOrderItem::callDisplay('adminItemDisplay', $item_id, '', $children);

        return $html;
    }

    /**
     * Display detailed information (possibly with editing options) about an item that is recurring billing capable.
     *
     * **Optional.**
     * **Used:** In admin, when displaying a recurring billing details
     *
     * Return HTML for displaying or editing any information about recurring
     * billing, specific to this item type. Should also call any children of
     * this item.
     *
     * The other function that should work with this one, is adminItemUpdate, for times
     * that it is useful for the admin to edit something about the item.
     *
     * @param geoRecurringBilling $recurringBilling the recurring billing object
     * @return array See the file source for documentation on the various options
     *   that can be used in the returned array.
     * @since Version 4.1.0
     */
    public static function adminRecurringDisplay($recurringBilling)
    {
        //TODO: Implement or remove

        //if there is any important info specific to this item type that
        //should be displayed, do it here.

        //a lot of items don't have anything specific to display. If that is
        //the case, either return false, or remove this method all together.
        return false;

        //To display something matching the general format of the rest of the
        //fields:
        $return [] = array (
            'label' => 'Info Label',
            'value' => 'Info Value',
        );

        //Or if you want to display the entire box for some reason:
        $return [] = array (
            'entire_box' => '<div>Entire box</div>',
        );
        //Can add as many different settings as needed, just keep adding to the array
        //and the 2 formats above can be mixed in the return.
        return $return;
    }

    /**
     * Used to save changes to an item, any changes made possible with adminItemDisplay.
     *
     * **Optional.**
     * **Used:** *NOT currently used* - this functionality was never fully implemented.
     *
     * Used to save changes to details of an item in the admin.  This is called
     * using ajax, but any messages added using geoAdmin::message() will display
     * to the client side.
     *
     * @param int $item_id
     */
    public static function adminItemUpdate($item_id)
    {
        //TODO: Implement or remove
    }

    /**
     * Get the order item type's title.
     *
     * **Optional.**
     * **Used:** In admin, when displaying the order item type for a particular item, used
     * in various places in the admin.
     *
     * @return string
     */
    public function getTypeTitle()
    {
        //TODO: implement or remove...
        //this is what it uses by default if the method getTypeTitle() does not exist.
        return ucwords(str_replace('_', ' ', self::type));
    }

    /**
     * Show information about a user on the user info page in the admin.
     *
     * **Optional.**
     *
     * **Used:** in Admin_site::display_user_data() (in file admin/admin_site_class.php)
     *
     * Can be used to display or gather information for a specific user, when viewing the user's details
     * inside the admin.  Useful for things like displaying a site balance, for example.
     *
     * @param int $user_id
     * @return string Text to add to page.
     */
    public static function Admin_site_display_user_data($user_id)
    {
        //TODO: implement or remove...
        //just a simple example of adding a setting/value to the view user page
        $html = geoHTML::addOption('_template', 'setting');
        return $html;
    }

    /**
     * Update information about the user in the admin panel.
     *
     * **Optional.**
     *
     * **Used:** in Admin_user_management::update_users_view() (in file admin/admin_user_management_class.php)
     *
     * Used to update information about a user that may have been collected in Admin_site_display_user_data()
     *
     * @param int $user_id ID NOT VERIFIED at time this is called!
     */
    public static function Admin_user_management_update_users_view($user_id)
    {
        //TODO: implement or remove...
    }

    /**
     * Display information about a user on the edit user form, in format to allow editing said information.
     *
     * **Optional.**
     *
     * **Used:** in Admin_user_management::edit_user_form() (in file admin/admin_user_management_class.php)
     *
     * Can be used to display or gather information for a specific user, when
     * on the page to edit user's information inside the admin.  Useful for things
     * like displaying and allowing edit a site balance, for example.
     *
     * Text returned will be inside of a form already, and when the form is submitted
     * you can be notified via ___ function.
     *
     * @param int $user_id
     * @return string Text to add to page.
     */
    public static function Admin_user_management_edit_user_form($user_id)
    {
        //TODO: Implement or remove.
        return '';
    }

    /**
     * Update user information in the admin panel.
     *
     * **Optional.**
     *
     * **Used:** in Admin_user_management::update_users_view() (in file admin/admin_user_management_class.php)
     *
     * Used to update information about a user that may have been collected in Admin_site_display_user_data()
     *
     * @param int $user_id ID NOT VERIFIED at time this is called!
     */
    public static function Admin_user_management_update_user_info($user_id)
    {
        //TODO: Implement or remove.
    }

    /**
     * Used when initiailizing an item, when the item already exists.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::initItem()
     *
     * This is used when the cart is loading and the order item is being initialized,
     * when the item already exists.  For example on the second page load during process
     * of placing a listing.  Can use this as a notice (just remember to return true!),
     * or as a way to block an order item from being loaded for whatever reason,
     * just return false.  To allow the item to be initialized just return true.
     *
     * @return bool Need to return true if it's ok to restore item, false otherwise
     */
    public function geoCart_initItem_restore()
    {
        //TODO: implement or remove...
        return true;
    }

    /**
     * Called when creating a new order item, can be used to block new order item from being started.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::initItem()
     *
     * Used when creating a new item.  Usually for use when adding a new item to the cart, so will
     * usually only be called if this is a main order item with no parents.
     *
     * @return bool Need to return true if it's ok to create item, false otherwise
     */
    public function geoCart_initItem_new()
    {
        //TODO: implement or remove...
        return true;
    }

    /**
     * Whether or not this order item can be used by someone not logged in.
     *
     * **Optional.**
     *
     * **Used:** mainly in geoCart::initItem() but can be called elsewhere.
     *
     * Used when no one is logged in, to determine if anonymous sessions
     * are allowed to use this item type.
     *
     * If this function is not defined, it will be assumed that this item
     * is NOT allowed with anonymous sessions, and will not allow this item
     * to be used without first logging in.
     *
     * @return bool Need to return true if item allowed to be used in an
     *  anonymous environment, false otherwise.
     */
    public static function anonymousAllowed()
    {
        //TODO: implement or remove...

        //return true: this item can be used when user not logged in.
        //assumes false if this function not defined.
        return true;
    }

    /**
     * Initialize the cart steps that are required to create this item.
     *
     * **Required.**
     *
     * **Used:** in geoCart::initSteps() (and possibly other locations)
     *
     * NOTE: This is best viewed in the PHP API documentation to see all the markup, available on the geodesicsolutions.com website.
     *
     * If this order item has any of it's own steps it wants to display or process as
     * part of the sell process, it needs to add them to the cart here, by getting an
     * instance of the cart, and $cart->addStep('item_name:step_name');.
     *
     * It also needs to call any children order items to do the same, as only parents
     * are called by the Cart system.
     *
     * Format of steps:
     * ================
     *
     *     <ORDER_ITEM_NAME>:<STEP_NAME>
     *
     * Example:
     * ========
     *
     * **Example Step Name:**
     *
     *     _template:details
     *
     *
     * CheckVars / Process Methods
     * ===========================
     *
     * When the process gets to the step _template:details, if $_REQUEST['process'] is
     * defined, then it will make the following static method calls:
     *
     *     _template::<STEP_NAME>CheckVars(); - if return true, then:
     *     _template::<STEP_NAME>Process(); - if return true, then it will continue on to next step
     *
     * Display Method
     * ==============
     *
     * If *$_REQUEST['process']* is NOT defined, or &lt;STEP_NAME&gt;CheckVars() or &lt;STEP_NAME&gt;Process()
     * either return false, then it will call:
     *
     *     _template::<STEP_NAME>Display();
     *
     * That display function is responsible for displaying the page, then including app_bottom.php,
     * then exiting.  If it does not exit, the system will display a site error.
     *
     * Step Label
     * ==========
     *
     * If the below optional method exists, it will also call that method to determine the "label"
     * for the step, to be displayed in templates that show the progress.  The method below
     * should return a string to display as the name of the step, or an empty string if you
     * wish to hide the step from the user:
     *
     *     _template::<STEP_NAME>Label();
     *
     * (Of course, above you would replace &lt;STEP_NAME&gt; with "details" if your step was "_template:details")
     *
     * @param bool $allPossible If true, initialize every single possible step, do
     *   not skip steps based on user info / user group or price plan settings.
     *   Skip steps based on "site wide" settings is still OK, for instance a check
     *   for whether auctions is turned on.  If this is true, this is being
     *   called in the admin panel to determine all of the possible steps, to allow
     *   combining the steps during the listing process.
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        //TODO: implement...
        $cart = geoCart::getInstance(); //get instance of cart
        $cart->addStep('_template:details'); //add step for details.  Note that this is an invalid step without
                                            //the class methods _template::detailsCheckVars(), _template::detailsProcess(),
                                            //and _template::detailsDisplay() are created.  Even if not all 3 are needed,
                                            //they are still required to exist, in order to be able to add a step
                                            //The 4th (optional) method you can have is _template::detailsLabel() which
                                            //should return the label of the step, that will be used by some templates
                                            //to display the progress to the user.  If it returns an empty string, the step
                                            //is not displayed to the user.

        //Note that you are not limited to only adding 1 step, and you are not required to add any steps at all.

        //CONDITIONAL NOTE:  If you add steps added based on conditions, make sure if
        //$allPossible is true, that you still initialize every possible step

        //get steps from children as well.  Children items are not called automatically, to allow parent items to
        //have more control over "children" items.
        $children = geoOrderItem::getChildrenTypes('_template');
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    /**
     * Whether or not a seperate cart should be used just for creating this order item.
     *
     * **Required.**
     *
     * **Used:** in geoCart::initItem()
     *
     * Whether or not a seperate cart should be used just for this order
     * item or not.  The alternate cart would be in addition to a "primary" cart
     * that may have things in it already, and this item would be the ONLY thing
     * in the cart.
     *
     * It is typical to not use this (return false), an example of when this may want
     * to be used, is to allow adding to a site balance so that a user can pay for the
     * rest of their cart.
     *
     * @return bool True to force creating "parellel" cart just
     *   for this item, false otherwise.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;

        //If this is a recurring item (this->isRecurring() returns true),
        //usually the following would be used:

        //for recurring items, should be stand-alone if recurring is possible
        $cart = geoCart::getInstance();
        return $cart->isRecurringPossible();
    }

    /**
     * Notification for when new cart session is being created.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::initSession()
     *
     * This will be called when a cart is being created for the first time.  Keep in mind
     * this is done before steps are initialized and before the first item is added to the cart.
     *
     * The most common use of this will be for special case items, such as something like
     * a shipping calculator or something.
     */
    public static function geoCart_initSession_new()
    {
        //TODO: implement or remove...
    }

    /**
     * Notification for when a cart session is being restored.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::initSession()
     *
     * This will be called on any cart page load when the cart session has already
     * been created. Keep in mind this is done before steps are initialized.
     *
     * The most common use of this will be for special case items, such as something like
     * a shipping calculator or something.
     *
     */
    public static function geoCart_initSession_update()
    {
        //TODO: implement or remove...
    }

    /**
     * Get the parent order item types for this order item.
     *
     * **Required.**
     *
     * **Used:** In geoOrderItem class when loading the order item types, to get the
     * defailt parent types.
     *
     * This should return an array of the different order items that this
     * order item is a child of.  If this is a main order item type, it
     * should return an empty array.
     *
     * Note that more parent types can be added later using geoOrderItem::addParentTypeFor()
     * but only if this method (getParentTypes) returns an array with at least
     * one parent type.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        //TODO: implement...
        //for "parent" order item, returne empty string.
        return array();

        //example of how to make this a child of all listing order items (would need to
        //comment out the first return for it to reach this one)
        return array('classified','auction','renew_upgrade','listing_change_admin',);
    }

    /**
     * Notification, ideal place to "adopt" another parent order item's child order items.
     *
     * **Optional.**
     *
     * **Used:** In geoOrderItem::loadTypes() at the end, after all the order item
     * types have been "loaded".
     *
     * This is the recommended place to make any needed calls to
     * {@link geoOrderItem::addParentTypeFor()} if the order item needs to adopt
     * a child order item (thus the ending of _adoptions).  This will be called
     * before the similar call to geoOrderItem_loadTypes_obituary.
     */
    public static function geoOrderItem_loadTypes_adoptions()
    {
        //TODO: Implement or remove.

        //most order items do not need this.
        return;

        //Example of how you would go about adopting all of "some_item"'s children:
        $children = geoOrderItem::getChildrenTypes('some_item');

        //adopt all those cute children!
        foreach ($children as $child) {
            geoOrderItem::addParentTypeFor($child, self::type);
        }
    }

    /**
     * Notification, ideal place to disable other order items.
     *
     * **Optional.**
     *
     * **Used:** In geoOrderItem::loadTypes() at the end, after all the order item
     * types have been "loaded", and after all the "adoptions" have taken place.
     *
     * This is the recommended place to make any needed calls to
     * {@link geoOrderItem::unregisterItemType()} if the order item needs to
     * "kill" an order item type (thus the ending of _obituary).  This will be
     * called after the call to geoOrderItem_loadTypes_adoptions.
     */
    public static function geoOrderItem_loadTypes_obituary()
    {
        //TODO: Implement or remove.

        //most order items do not need this.
        return;

        //Example of how you would go about removing some type of item, since
        //you just adopted all it's children in geoOrderItem_loadTypes_adoptions
        //we'll go ahead and replace it all together by calling:
        geoOrderItem::unregisterItemType('some_item');
    }

    /**
     * Get information about the order item for display purposes.
     *
     * **Required.**
     *
     * **Used:** Throughout the software, wherever order details are displayed.
     *
     * Used to get display details about item, and any child items as well, both in the main
     * cart view, and other places where the order details are displayed, including within
     * the admin.  Should return an associative array, that follows:
     *
     *  array(
     *      'css_class' => string, //leave empty string for default class, only applies in cart view
     *      'title' => string,
     *      'canEdit' => bool, //whether can edit it or not, only applies in cart view
     *      'canDelete' => bool, //whether can remove from cart or not, only applies in cart view
     *      'canPreview' => bool, //whether can preview the item or not, only applies in cart view
     *      'priceDisplay' => string, //price to display
     *      'cost' => double, //amount this adds to the total, what getCost returns but positive
     *      'total' => double, //amount this AND all children adds to the total
     *      'children' => array(), //optional, should be array of child items, with the index
     *                              //being the item's ID, and the contents being associative array like
     *                              //this one.  Careful not to get into any infinite loops...
     *  )
     *
     * @param bool $inCart True if this is being called from inside the cart, false otherwise. Note: do NOT
     *  try to use the geoCart object if $inCart is false.
     * @param bool $inEmail True if this is being used to build the text of an email notification sent by the system, false otherwise. Param added in 6.0.6
     * @return array|bool Either an associative array as documented above, or boolean false to hide this
     *  item from view.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        //TODO: implement...

        //NOTE: This function is sometimes called "outside" of the cart environment (when $inCart is
        //false), so it is best to not rely on geoCart object for anything.

        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => 'Template Title',//text that is displayed for this item in list of items purchased.
            'canEdit' => true, //show edit button for item, if displaying in cart?
            'canDelete' => true, //show delete button for item, if displaying in cart?
            'canPreview' => true, //show preview button for item, if displaying in cart?
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($this->getCost(), false, false, 'cart'), //Price as it is displayed
            'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
            'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        //THIS PART IMPORTANT:  Need to keep this part to make the item is able to have children.
        //You don't want your item to be sterile do you?

        //go through children...
        $order = $this->getOrder();//get the order
        $items = $order->getItem();//get all the items in the order
        $children = array();
        foreach ($items as $i => $item) {
            if (is_object($item) && $item->getType() != $this->getType() && is_object($item->getParent())) {
                $p = $item->getParent();//get parent
                if ($p->getId() == $this->getId()) {
                    //Parent is same as me, so this is a child of mine, add it to the array of children.
                    //remember the function is not static, so cannot use callDisplay() or callUpdate(), need to call
                    //the method directly.
                    $displayResult = $item->getDisplayDetails($inCart, $inEmail);
                    if ($displayResult !== false) {
                        //only add if they do not return bool false
                        $children[$item->getId()] = $displayResult;
                        $return['total'] += $children[$item->getId()]['total']; //add to total we are returning.
                    }
                }
            }
        }
        if (count($children)) {
            //add children to the array
            $return['children'] = $children;
        }
        return $return;
    }


    /**
     * Get information about the cost, if there is any cost for the order item
     *
     * **Required.**
     *
     * **Used:** By payment gateways to see what types of items are in the cart.
     *
     * Note that for backwards compatibility with older order items, this is implemented
     * in the parent geoOrderItem class, so if you leave it off it will "work".
     * It is still highly recommended to implement anyways in each order item,
     * simply because it's role will be much more important when the ability to
     * use the cart between users is implemented down the road.
     *
     * This is very similar to {@see _templateOrderItem::getDisplayDetails()} except that the
     * information is used by payment gateways and is specifically for information about what
     * the "cost" of something is for.
     *
     * Should return an associative array, that follows:
     *  array(
     *      'type' => string, //The order item type, should always be $this->getType()
     *      'extra' => mixed, //used to convey to payment gateways "custom information" that
     *                          may be needed by the gateway.  Most can set this to null.
     *      'cost' => double, //amount this adds to the total, what getCost returns
     *      'total' => double, //amount this AND all children adds to the total
     *      'children' => array(), //optional, should be array of child items, with the index
     *                              //being the item's ID, and the contents being associative array like
     *                              //this one.  Careful not to get into any infinite loops...
     *  )
     *
     * @return array|bool Either an associative array as documented above, or boolean false if
     *   this item has no cost (positive or negative, including children).
     * @since Version 6.0.0
     */
    public function getCostDetails()
    {
        //Most use this exactly AS-IS...

        $return = array (
                'type' => $this->getType(),
                'extra' => null,
                'cost' => $this->getCost(),
                'total' => $this->getCost(),
                'children' => array(),
        );

        //call the children and populate 'children'
        $order = $this->getOrder();//get the order
        $items = $order->getItem();//get all the items in the order
        $children = array();
        foreach ($items as $i => $item) {
            if (is_object($item) && $item->getType() != $this->getType() && is_object($item->getParent())) {
                $p = $item->getParent();//get parent
                if ($p->getId() == $this->getId()) {
                    //Parent is same as me, so this is a child of mine, add it to the array of children.
                    //remember the function is not static, so cannot use callDisplay() or callUpdate(), need to call
                    //the method directly.
                    $costResult = $item->getCostDetails();
                    if ($costResult !== false) {
                        //only add if they do not return bool false
                        $children[$item->getId()] = $costResult;
                        $return['total'] += $costResult['total']; //add to total we are returning.
                    }
                }
            }
        }
        if ($return['total'] == 0) {
            //total is 0, even after going through children!  no cost details to return
            return false;
        }
        if (count($children)) {
            //add children to the array
            $return['children'] = $children;
        }
        return $return;
    }

    /**
     * The checkVars method for the "other_details" step.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::other_detailsCheckVars()
     *
     * Used by items that are displayed & processed at the built-in other details step, or
     * items that may have children at this step.  If a problem with input vars is found,
     * or it should not proceed to next step for whatever reason, use geoCart::addError()
     * to let the system know there is a problem.
     *
     * Note that this is called for all order items, so need to check to see if main type
     * warrents it checking vars first.
     *
     * This can be used as a template for other checkVars functions for specific not-built-in steps
     *
     */
    public static function geoCart_other_detailsCheckVars()
    {
        //TODO: implement or remove...
        $cart = geoCart::getInstance();
        //do checking of vars here

        if ($cart->main_type != self::type && !in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type))) {
            //item being added does not have anything to do with this item, so no need to check vars.
            return;
        }
        if (!$check) {
            //dummy check, to demonstrate how to tell the cart there is a problem and to not proceed to next step yet.
            $cart->addError();
        }

        //make sure to call check vars for children as well.
        $children = geoOrderItem::getChildrenTypes('_template');
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);
    }

    /**
     * The process method for the "other_details" step.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::other_detailsProcess()
     *
     * Used by items that are displayed & processed at the built-in other details step, or
     * items that may have children at this step.  Things like adding or removing an item
     * based on a checkbox selection should be done here.
     *
     * Note that this is called for all order items, so need to check to see if main type
     * warrents it processing for that main type first.
     *
     * This can be used as a template for other Process functions for specific not-built-in steps
     *
     */
    public static function geoCart_other_detailsProcess()
    {
        //TODO: implement or remove...
        $cart = geoCart::getInstance(); //get instance of cart

        if ($cart->main_type != self::type && !in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type))) {
            //item being added does not have anything to do with this item, so no need to do anything.
            return;
        }
        //Error checking should have been done in checkVars, if there were any errors this function
        //would not be run, so do not need to do error checking here.

        //Do any processing, like adding child item to order based on selection.

        //get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
    }

    /**
     * The display method for the "other_details" step.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::other_detailsDisplay()
     *
     * Used by items that are displayed & processed at the built-in other details step.
     * This will be called during other_details step, if that step is displayed, for
     * all items and children items, even if there are none of this type of item in
     * the cart yet.
     *
     * TODO: document this function further - needs to return an associative array like any of the listing extras do
     *
     *
     */
    public static function geoCart_other_detailsDisplay()
    {
        //TODO: implement or remove...
        //everything is done at checkvars step to prevent stuff

        //Don't need to call children, as children are always called.
    }

    /**
     * The label method for the "other_details" step.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::other_detailsLabel()
     *
     * Used by "main" items that are displayed & processed at the built-in other
     * details step.
     *
     * This should return the text label for the step.  For instance, if this item
     * were for placing a listing, and the "other details" step was used for listing
     * extras, this function should return something like "Listing Extras".  The
     * text is used by some templates to display the progress of adding something
     * to the cart.
     *
     * Note: if this function is not defined, the step will be set to "Extras" by
     * default in the cart.  If you want to "hide" the step from displaying, return
     * an empty string (depends on template to skip steps without a label to be totally
     * hidden)
     *
     * @return string The string to display to represent this step as is pertains
     *  to this order item.
     *
     */
    public static function geoCart_other_detailsLabel()
    {
        //TODO: implement or remove...

        return "Other Details";
    }

    /**
     * Display a preview of the item.
     *
     * **Optional.**  Required if in getDisplayDetails() you returned true for the array index of canPreview.
     *
     * **Used:** in geoCart::previewDisplay()
     *
     * Display a preview of the item.
     */
    public function geoCart_previewDisplay()
    {
        //TODO: implement or remove...
        $cart = geoCart::getInstance();

        $cart->site->display_page();
    }

    /**
     * Perform any additional steps that might be required when an item is being deleted from the cart.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::deleteProcess()
     *
     * The back-end already removes the item, all all children from the cart.  Use this function to do
     * any additional things needed, such as delete uploaded images, or if you expect that any children
     * may need to be called, as they will not be auto called from the system.  Can assume
     * $cart->item is the item that is being deleted, which will be the same type as this is.
     *
     */
    public static function geoCart_deleteProcess()
    {
        //TODO: implement or remove...
        $cart = geoCart::getInstance();

        //Do this FIRST: Go through any children, and call geoCart_deleteProcess for them...
        $original_id = $cart->item->getId();//need to keep track of what the ID of the item originally being deleted is.
        $items = $cart->order->getItem();
        foreach ($items as $k => $item) {
            if (is_object($item) && $item->getId() != $cart->item->getId() && is_object($item->getParent()) && $item->getParent()->getId() == $cart->item->getId()) {
                //$item is a child of this item...
                //Set the cart's main item to be $item, so that the deleteProcess gets
                //what it is expecting...
                $cart->initItem($item->getId(), false);
                //now call deleteProcess
                geoOrderItem::callUpdate('geoCart_deleteProcess', null, $item->getType());
            }
        }
        if ($cart->item->getId() != $original_id) {
            //change the item back to what it was originally, if it was changed.
            $cart->initItem($original_id);
        }


        //DO Any custom stuff needed here.
        $parent = $cart->item->getParent();
        if (is_object($parent)) {
            $session_vars = $parent->get('session_variables');
            $session_vars['featured_ad_3'] = 0;
            $parent->set('session_variables', $session_vars);
            $parent->save();
        }
    }


    /**
     * Whether to show the "other details" step for this order item.
     *
     * **Required.**
     *
     * **Used:** in geoCart::initSteps()
     *
     * Determine whether or not the other_details step should be added to the steps of adding this item
     * to the cart.  This should also check any child items if it does not need other_details itself.
     *
     * @return bool True to add other_details to steps, false otherwise.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        //TODO: implement...
        return true; //this item has stuff to display on other_details step.

        //if not true, you would still need to check children items like this:
        $children = geoOrderItem::getChildrenTypes(self::type);
        //can call directly, since this function is required.
        if (geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails', null, 'bool_true', $children)) {
            //one of the children want to display it, so return true.
            return true;
        }
        //none of the children returned true, so return false
        return false;
    }

    /**
     * Get the text used to add a new item of this type to the cart.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::cartDisplay()
     *
     * Used only for "parent" items, this should return the text to use for the new button displayed
     * in the cart view, for instance something like "Add New Classified".
     *
     */
    public static function geoCart_cartDisplay_newButton()
    {
        //TODO: implement or remove...
        if (self::isAnonymous()) {
            //if we do not allow to be used in anonymous
            //environment (no user logged in), this check
            //must be here.  If we do allow anon, this check can
            //be removed.
            return '';
        }

        return "Add new _Template!";
    }

    /**
     * Get button information about link to add one of these, for the my account links module.
     *
     * **Optional.**
     *
     * **Used:** in my_account_links module
     *
     * Used only for "parent" items, this should return an associative array:
     *
     *  array (
     *      'label' => 'Link Text',
     *      'icon' => '<img src="image.jpg" alt="new something" style="vertical-align: middle;" />'
     *  )
     *
     * Note that the icon is rarely used these days, as using icons tend to not
     * match with the new design.  Will probably want to use an empty string for the icon.
     *
     * @return array
     */
    public static function my_account_links_newButton()
    {
        //TODO: Implement or remove...
        if (self::isAnonymous()) {
            //if we do not allow to be used in anonymous
            //environment (no user logged in), this check
            //must be here.  If we do allow anon, this check can
            //be removed.
            return false;
        }
        return array (
            'label' => 'New _template',
            'icon' => '<img src="images/user_admin/my_acct_new.jpg" alt="new _template" style="vertical-align: middle;" />'
        );
    }

    /**
     * Used when copying a listing, to perform any extra tasks that might be needed.
     *
     * **Optional.**
     *
     * **Used:** In listing order items such as classifiedOrderItem or auctionOrderItem
     *
     * **NOT part of built-in cart system.**
     *
     * Special case, functionality built into individual order items.
     *
     * This can be used to copy or re-create anything needed to duplicate
     * the original listing.  See other order items that are children to get
     * some examples of how this can be utilized.  The one that does the most
     * stuff is the images order item.
     */
    public static function copyListing()
    {
        //TODO: implement or remove...
        $cart = geoCart::getInstance();

        if ($cart->site->session_variables['_template']) {
            //before this is called, the $cart->site->session_variables are populated with the
            //session vars as they were on the original listing.  This is a good way to see if
            //the item was attached to the original order item..

            //do stuff to copy things from the old listing to the new one here.
        }
    }

    /**
     * Get choices to show when editing a listing.
     *
     * **Optional.**
     *
     * **Used:** In listing_editOrderItem::geoCart_initSteps()
     *
     * **NOT part of built-in cart system.**
     *
     * Special case, functionality built into individual order items.
     *
     * This can be used to add additional things to be edited when editing
     * a listing.
     * @return array Associative array, as documented in function.
     */
    public static function listing_edit_getChoices()
    {
        //TODO: Implement or remove

        return false;

        //If we wanted to edit something specific to this item type,
        //we can do so (below requires appropriate functions added
        //that would be needed for a step named "our_edit_step"):
        return array ('_template:our_edit_step' => 'edit _template');
    }

    /**
     * Insert a new listing based on the session variables presented.
     *
     * **Optional.**
     *
     * **Used:** In _listing_placement_commonOrderItem::_insertListingFromSessionVars()
     *
     * **NOT part of built-in cart system.**
     *
     * Special case, functionality built into individual order items.
     *
     * This can be used to set listing vars manually instead of by using session
     * vars, at the time that session vars are being used to populate a listing
     *
     * @param array $vars array containing session_variables and listing_id
     * @since Version 5.1.0
     */
    public static function listing_insertListingFromSessionVars($vars)
    {
        //TODO: Implement or remove

        return null;

        $listing_id = $vars['listing_id'];
        $session_variables = $vars['session_variables'];

        //at this point, could manually make some changes that might not be
        //possible using simple session_variables array
    }

    /**
     * Perform some action when the order item's status changes.
     *
     * **Optional.**
     *
     * **Used:** In the admin when admin activates order or item, or on client side when payment is
     * made and settings are such that it does not need admin approval to activate the item.
     *
     * If this is not implemented here, the parent class will do common stuff for you, like call
     * child items and actually set the status
     *
     * This is responsible for actually changing the status of the item, as well as anything such
     * as activating/deactivating a listing depending on what the previous status is, and what it is
     * being changed to.  Use template function as a guide, and add customization where comments
     * specify to.  Remember to call children where appropriate if you decide not to call the parent
     * to do it for you.
     *
     * It can be assumed that if this function is called, all the checks as to whether the item should be
     * pending or not have already been done, however there may be other custom checks you wish to do.
     *
     * @param string $newStatus a string of what the new status for the item should be.  The statuses
     *   built into the system are active, pending, and pending_alter.
     * @param bool $sendEmailNotices If set to false, you should not send any e-mail notifications
     *   like might be normally done.  (if it's false, it will be because this is called
     *   from admin and admin said don't send e-mails)
     * @param bool $updateCategoryCount True if should update the category count
     */
    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
    {
        //TODO: implement or remove...
        if ($newStatus == $this->getStatus()) {
            //the status hasn't actually changed, so nothing to do
            return;
        }
        $activate = ($newStatus == 'active') ? true : false;

        $already_active = ($this->getStatus() == 'active') ? true : false;

        //allow parent to do common things, like set the status and
        //call children items
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);

        if ($activate) {
            //TODO: do activate actions here, such as setting listing to live
        } elseif (!$activate && $already_active) {
            //TODO: do de-activate actions here, such as setting listing to not be live any more.
            //This is what would happen if an admin changes their mind
            //and later decides to change an item from being active to being pending.
        }
        //NOTE: do not need to call children, parent does that for us :)
    }

    /**
     * Perform some action at the time an order's status is changing.
     *
     * **Optional.**
     *
     * **Used:** in geoOrder::processStatusChange
     *
     * This is *special case*, to allow doing something every time an order's status changes.
     * **Do NOT use this in order to change the status of the order item**, instead see the
     * processStatusChange() method for order items.
     *
     * @param geoOrder $order
     */
    public static function Order_processStatusChange($order)
    {
        //TODO: Implement or remove

        //Note: most should not use this, instead see processStatusChange.
    }

    /**
     * Perform some action at the time a recurring billing payment is being processed.
     *
     * **Optional.**
     *
     * **Used:** in geoRecurringBilling::processPayment
     *
     * This is **special case**, to allow doing something every time a payment signal
     * is received for recurring billing.  Note that this is only called for
     * transactions that are attached to a recurring billing and not any order
     *
     * This is notification, the return value is ignored.
     *
     * @param array $vars Associative array like array('recurring'=>geoRecurringBilling instance,
     *   'transaction' => geoTransaction instance.
     */
    public static function RecurringBilling_processPayment($vars)
    {
        //TODO: Implement or remove

        //Note: most should not use this
        return;

        //Here is what is passed in with $vars:
        //Instance of the geoRecurringBilling object "receiving" the payment
        $recurring = $vars['recurring'];
        //The transaction object containing the information about payment, such
        //as the amount
        $transaction = $vars['transaction'];
    }

    /**
     * Get the item info to be displayed in an e-mail.
     *
     * **Optional** (if not defined here, parent will return title - price) where
     * title is the title part returned by getDisplayDetails(false)
     *
     * **Used:** in geoOrder::processStatusChange
     *
     * Use this to display info about each main item, in the e-mail sent saying the
     * order has been approved.  To keep consistent, use this format:
     *
     *  ITEM TITLE [STATUS] - $COST
     *
     * Be sure you also add up any costs of sub-items of this item.
     *
     * @return string
     */
    public function geoOrder_processStatusChange_emailItemInfo()
    {
        //TODO: implement or remove or return empty string.

        //One option is to specify the title and let the super class do the rest,
        //like this:
        return parent::geoOrder_processStatusChange_emailItemInfo('Title of Item');
    }

    /**
     * Show information on the user information page under my account.
     *
     * **Optional.**
     *
     * **Used:** in User_management_information::display_user_data()
     *
     * Use this to display info on the user info page.  Stuff like displaying
     * current account balance, tokens remaining, etc.  This will appear below
     * the price plan info
     *
     * @return array Associative array, with
     *  the structure array ('label' => 'Left side','value' => 'Right side')
     */
    public static function User_management_information_display_user_data()
    {
        //TODO: implement or remove
        return array('label' => 'Info name', 'value' => 'Data value');
    }

    /**
     * Use this to add a link to the My Account Links module (or old user management home page)
     *
     * **Optional.**
     *
     * **Used:** in User_management_home::menu()
     *
     * Use this to add a link to the My Account Links module (or old user management home page)
     */
    public static function User_management_home_body()
    {
        //TODO: implement or remove

        //Sample of something to do
        $view = geoView::getInstance();

        $linkData = array();
        $linkData['label'] = 'text to show in My Account Links module';
        $linkData['icon'] = '(optional) icon to show to the left of above text';
        $linkData['link'] = '(optional) url to link both icon and label to';
        $linkData['active'] = false; //set this to true if user is currently viewing the linked page
        $linkData['needs_attention'] = false; // set to true to highlight the link as needing attention

        //this is a bit roundabout since $view's __get and __set are overloaded
        //but it does the trick
        $orderItemLinks = $view->orderItemLinks;
        $orderItemLinks[] = $linkData;
        $view->orderItemLinks = $orderItemLinks;
    }

    /**
     * Perform any extra actions needed when order item is being removed, or "refuse" to allow an order item to be removed.
     *
     * **Optional.**
     *
     * **Used:** from geoOrderItem::remove() when removing an order item.
     *
     * Use this function if you need to do things like remove a listing from the database, or delete
     * images or something.  Be sure to return true or the item will not be removed by the system.
     *
     * Note that normal back-end stuff like removing registry settings and removing the order item
     * from the DB are handled by the system, this function is primarily for special case stuff like
     * deleting files, or removing stuff from the DB that isn't part of normal order items.
     *
     * @return bool True to proceed with removing the item, false to stop the removal of the item.
     */
    public function processRemove()
    {
        //TODO: Implement or remove...
        return true;
    }

    /**
     * Perform any actions needed when removing the order item data (as part of archiving old information).
     *
     * **Optional.**
     *
     * **Used:** from geoOrderItem::removeData() when removing an order item's old data.
     *
     * Use this function if you need to do things like remove data from a custom table (note that
     * order item registry data is automatically removed for you).  This *should NOT affect anything
     * on the site*, like removing a listing or removing old images, as the listing this order item
     * is used on may still be live, we are just removing the order item's data.
     *
     * It's the equivelent of shredding old invoices after they become 5 years old, the stuff that
     * was purchased on those invoices still exist, but the records for the invoice are being deleted
     * since they are so old.
     *
     * Note that normal back-end stuff like removing registry settings and removing the order item
     * from the DB are handled by the system, this function is primarily for special case stuff as
     * mentioned above.
     *
     * @return bool True to proceed with removing the item, false to stop the removal of the item.
     */
    public function processRemoveData()
    {
        //TODO: Implement or remove...
        return true;
    }

    /**
     * Get the action name used for whatever the current action is.
     *
     * **Optional.**
     *
     * **Used:** in geoCart and my_account_links module
     *
     * This is used to display what the action is if this order item is the main type.  It should return
     * something like "adding new listing" or "editing images".
     *
     * @param array $vars Array containing the action and step to get the action name for
     * @return string
     */
    public static function getActionName($vars)
    {
        //TODO: Implement or remove...

        //this will be "interrupted" if adding this item to the cart is the one being interrupted,
        //or the action attempting to be run if this is the item that is doing the interrupting.
        $action = $vars['action'];
        //The step
        $step = $vars['step'];

        if ($step == 'my_account_links') {
            //this is a special case, this needs to be "short" text that is displayed
            //next to "In Progress:" when using my account links module
            //NOTE: if using text for this, add the text to the my account links module
            return ucwords(self::type);
        } else {
            //We COULD do something "special" depending on what the action and step is,
            //but most only do something special if step is "my_account_links".

            //NOTE: If using text for this step, add the text to the main cart display page.
            return 'Adding ' . ucwords(self::type);
        }
    }

    /**
     * Perform additional actions at the time the price plan is being set for the cart.
     *
     * **Optional.**
     *
     * **Used:** from geoCart::setPricePlan()
     *
     * Most should remove this function.  Its use would be to set some price plan setting
     * in addition to the settings auto retrieved.  An example of where it is used is in
     * the subscription order item.
     *
     * @param array $vars array ('price_plan' => int, 'category' => int)
     */
    public static function geoCart_setPricePlan($vars)
    {
        //TODO: Remove or implement...
    }

    /**
     * perform additional actions at the time a listing is being closed during the close listing cron job.
     *
     * **Optional.**
     *
     * **Used:** in file classes/cron/close_listings.php
     *
     * This is called for each listing that is being closed.  Note that the following things are
     * automatically done: the "live" column is set to 0, and user favorites for the listing
     * are removed.  Anything beyond that is up to being done in this function.
     *
     * @param array $vars Associative array, array('listing' => geoListing object)
     */
    public static function cron_close_listings($vars)
    {
        //TODO: Implement or remove...
        $listing = $vars['listing']; //a geoListing item.  see that class for more details.
        $cron = geoCron::getInstance();


        $cron->log('Top of template cron close listings.', __line__);

        //Do anything specific to this type of item here.

        //call children if needed
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::calUpdate('cron_close_listings', $vars, $children);
    }

    /**
     * Perform additional actions at the time a buy now auction is being closed early.
     *
     * **Optional.**
     *
     * **Used:** in process_bid in file classes/auction_bid_class.php
     *
     * This is called when a buy now auction is being closed.  Note
     * that the following things are automatically done: the "live"
     * column is set to 0, and user favorites for the listing
     * are removed, auction feedback inserted into db.  Anything
     * beyond that is up to being done in this function.
     *
     * @param array $vars Associative array, array('listing' => geoListing object)
     */
    public static function buy_now_close($vars)
    {
        //TODO: Implement or remove...
        $listing = $vars['listing']; //a geoListing item.  see that class for more details.

        //Do anything specific to this type of item here.

        //call children if needed
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::calUpdate('buy_now_close', $vars, $children);
    }

    /**
     * Perform additional actions at time that an auction final fee is added to the order.
     *
     * **Optional.**
     *
     * **Used:** In auction_final_fees order item (auction_final_feesOrderItem::cron_close_listings()
     *
     * NOT part of built-in cart system.
     *
     * Special case, functionality built into individual order items.
     *
     * This can be used to do stuff at the same time that an auction final
     * fee is added to the order.
     *
     * @param array $vars See docs in function
     */
    public static function auction_final_feesOrderItem_cron_close_listings($vars)
    {
        //TODO: implement or remove...
        //vars is an associative array of the listing object, and the order object.
        $listing = $vars['listing'];
        $order = $vars['order'];

        //do stuff here.  Things like adding tax to the order or something.
    }

    /**
     * Perform special actions right before the contents of the cart are retrieved.
     *
     * **Optional.**
     *
     * **Used:** in geoCart::_getCartItemDetails
     *
     * Most do not need to use this.
     *
     * This can be used for special items, that need to do stuff before
     * the contents of the cart are retrieved for display in the cart
     * view.  For example, the tax and sub-total items use this in order
     * to auto-add and auto-remove themself from the cart according to
     * if criteria in the cart are met.
     *
     */
    public static function geoCart_getCartItemDetails()
    {
        //TODO: implement or remove...  (most do not need to use this)
    }

    /**
     * Whether this order item can function as a recurring billing item.
     *
     * **Optional.**
     *
     * **Used:** throughout code.
     *
     * Specify whether or not this item is a recurring billing item or not, if
     * this method is not defined the superclass will return false.
     *
     * @return bool
     * @since Version 4.1.0
     */
    public function isRecurring()
    {
        //TODO: implement or remove...
        return false;
    }

    /**
     * Get the recurring interval for the item.
     *
     * **Optional.**
     *
     * **Used:** usually in recurring payment gateways.
     *
     * Required if isRecurring() returns true, otherwise it will
     * default to 0 (basically recurring being off).  This needs to return the
     * interval for the recurring billing, in seconds.
     *
     * @return int The recurring interval in seconds.
     * @since Version 4.1.0
     */
    public function getRecurringInterval()
    {
        //TODO: implement or remove...
        return 0;
    }
    /**
     * Get the recurring price for the item.
     *
     * **Optional.**
     *
     * **Used:** usually in recurring payment gateways.
     *
     * Required if isRecurring() returns true, otherwise it will
     * default to 0 (basically recurring being off).  This needs to return the
     * price for the recurring billing.
     *
     * @return float The recurring price
     * @since Version 4.1.0
     */
    public function getRecurringPrice()
    {
        //TODO: Implement or remove...
        return 0.00;

        //example of way to do it, this method uses getOrderTotal to get
        //recurring amount, so that special recurring children can alter
        //recurring price, such as recurring billing discount code or recurring
        //tax built into system
        return $this->getOrder->getOrderTotal();

        //Another example, this one geared toward "children" recurring items
        return $this->getCost();
    }

    /**
     * Get the recurring description for the item.
     *
     * **Optional.**
     *
     * **Used:** usually in recurring payment gateways.
     *
     * Required if isRecurring() returns true, otherwise it will
     * default to "Recurring Item".  This needs to return the description for
     * this item that will be used in the payment gatway transaction and possibly
     * other places for the recurring billing.
     *
     * @return string
     * @since Version 4.1.0
     */
    public function getRecurringDescription()
    {
        //TODO: Implement or remove...
        return 'Subscription for a user.';
    }

    /**
     * Get the start date for the recurring item.
     *
     * **Optional.**
     *
     * **Used:** usually in recurring payment gateways.
     *
     * Only used if isRecurring() returns true, if not implemented the
     * geoOrderItem superclass will return the current time.  This is expected
     * to return the timestamp for when the initial recurring billing first payment
     * should be made.  This will make the system allow for if something is already
     * paid for through a certain date.
     *
     * @return int Unix timestamp for when recurring start date should be.
     */
    public function getRecurringStartDate()
    {
        //TODO: Implement or remove...
        return geoUtil::time();
    }

    /**
     * Perform actions at the time the recurring billing status is changing.
     *
     * **Optional.**
     *
     * **Used:** in {@link geoRecurringBilling::updateStatus()} after gateway has updated
     * status on the recurring billing.
     *
     * Use this to make changes when a recurring billing has been updated, such
     * as updating the expiration of an item or extending a subscription.
     *
     * @param geoRecurringBilling $recurring
     * @since Version 4.1.0
     */
    public static function recurringBilling_updateStatus($recurring)
    {
        //TODO: Implement or remove...

        //Note: $recurring is the recurring billing object.
    }

    /**
     * Perform actions at the time recurring billing subscription is canceled.
     *
     * **Optional.**
     *
     * **Used:** in {@link geoRecurringBilling::cancel()} after gateway has processed
     * the cancelation.
     *
     * Use this to make changes when a recurring billing has been canceled, such
     * as updating the expiration of an item or removing a subscription.
     *
     * @param geoRecurringBilling $recurring
     * @since Version 4.1.0
     */
    public static function recurringBilling_cancel($recurring)
    {
        //TODO: Implement or remove...

        //Note: $recurring is the recurring billing object.
    }
}
