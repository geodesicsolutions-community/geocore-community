<?php

//addons/example/order_items/eWidget.php
/**
 * Optional file.  System will parse the order_items/ directory, and use any
 * valid order item files found.  Your addon can create multiple order items.
 *
 * Note that any file found in the order_items/ directory that starts with an
 * underscore _ will be ignored by the system.
 *
 * This example order item is configured to work for a specific purpose (so there
 * is a "working" example order item for you to "start" from), it does not
 * document everything that order items can do.  To see the full
 * order item documentation, see the "template" order item found in the main
 * software at:
 *
 * classes/order_items/_template.php
 *
 * See that file for further documentation.
 *
 * @author Geodesic Solutions, LLC
 * @package ExampleAddon
 * @since Version 4.0.0
 */


# Example Addon

/**
 * This order item is designed to allow users on your site to purchase an
 * eWidget.
 *
 * This order item does everything you might think a widget order item might
 * do:
 *
 * - set the cost in the admin, under price plan cost specifics, under the plan
 *   item settings
 * - user can add widget to their cart by clicking on "add widget" button -
 *   button is displayed automatically in main cart view, and also in the
 *   "listing cart" "add item" section of the my account links module.
 * - This item has a 2 page process for adding a widget to the cart.  Since
 *   this is just an example, the pages don't do much, they just demonstrate
 *   how to set up different steps for adding an item to the cart.
 * - Allows user to preview the widget.  (all preview does is display an image,
 *   since this is an example)
 * - All the built-in processes for order items will work, such as viewing
 *   pending widgets, the system auto-activating widget once it's paid for,
 *   if admin checks "needs admin approval" the widget does NOT auto activate,
 *   etc.
 *
 * Since this "Widget" addon is designed to work as specified above, it will
 * not demonstrate or document everything order items can actually do.  For
 * that, look at the template order item found in the main software at:
 *
 * classes/order_items/_template.php
 *
 * Use the file above as a starting point for creating your own order item if
 * you are creating one that differs drastically from what this widget does.
 *
 * You can create as many order items inside of an addon, by creating an
 * order_items/ directory, and placing your order items in that directory.
 *
 * This example Widget order item demonstrates a "parent" order item, or an
 * order item that has no "parents" of it's own, so it will be added directly
 * to an order.  A parent order item will not be attached to another order item.
 *
 * Other "parent" order items used in the main software:  auction, classified,
 * listing renew/upgrade, listing edit, subscription renewal
 *
 * A "child" order item is one that is attached to a parent order item.  Parent
 * order items don't have to have children, but children DO have to have a
 * parent.  Examples of "child" order items are: bolding extra, better placement
 * extra, featured listing extra (levels 1-5), account tokens attached, and
 * listing images.
 *
 * Note that the class name should match the file name.  The syntax for the
 * order item class name is:
 *
 * [File Name]OrderItem
 *
 * The class name does not include the ending ".php" of course.  And it is case
 * sensitive.
 *
 * @package ExampleAddon
 */
class eWidgetOrderItem extends geoOrderItem
{

    /**
     * Note that this is the same as the file name (w/o .php) and the start
     * of the class name.
     *
     * See {@link _templateOrderItem::$type} for full documentation.
     *
     * @var string
     */
    protected $type = "eWidget";

    /**
     * Isn't used by system, just used internally to access the name of the
     * order item easily.
     *
     */
    const type = 'eWidget';

    /**
     * The "process" order, or the order that this item is going to be processed
     * relative to other order items.  If this has an order of 5 and another
     * item has an order of 10, this item will appear before the other wherever
     * item stuff appears.
     *
     * See {@link _templateOrderItem::$defaultProcessOrder} for full documentation.
     *
     * @var int
     */
    protected $defaultProcessOrder = 10;

    /**
     * Just here for easy internal reference, not used by system.  Should be
     * same as other number.
     * @var int
     */
    const defaultProcessOrder = 10;


    /**
     * Required by order item system.
     *
     * See {@link _templateOrderItem::displayInAdmin()} for full documentation.
     *
     * @return bool
     */
    public function displayInAdmin()
    {
        //We do display this item in the admin.
        return true;
    }

    /**
     * This will display the "plan item" settings when the admin clicks the
     * configure link next to this addon.
     *
     * See {@link _templateOrderItem::adminPlanItemConfigDisplay()} for full documentation.
     *
     * @param geoPlanItem $planItem The geoPlanItem object that holds the settings for this item and price plan
     * @return string
     */
    public function adminPlanItemConfigDisplay($planItem)
    {
        //normally we want to use smarty templates to display everything, but
        //a smarty temlate is slightly overkill for this.  You could still use
        //one though if you wanted.
        $txt = '<div>';

        //get the current cost, and format it to look nice
        $currentPrice = geoNumber::format($planItem->get('price', 0.00));
        //Note: No need to clean the value, we clean the value when saving the value

        //get the db so we can get pre and post currency to display the cost nicely
        $db = DataAccess::getInstance();

        $inputField = $db->get_site_setting('precurrency');
        //Note: for settings, it works best to use an array with the name
        //the same as the order item name
        $inputField .= "<input type='text' name='eWidget[price]' id='eWidget[price]'
			size='4' value='$currentPrice' />";
        $inputField .= $db->get_site_setting('postcurrency');

        $txt .= geoHTML::addOption('Cost for 1 Widget', $inputField);
        $txt .= '</div>';
        return $txt;
    }

    /**
     * This saves the plan item settings.
     *
     * See {@link _templateOrderItem::adminPlanItemConfigUpdate()} for full documentation.
     *
     * @param geoPlanItem $planItem The geoPlanItem object that holds the settings for this item and price plan
     * @return bool If return true, message "settings saved" will be displayed, if return
     *  false, message "settings not saved" will be displayed.
     */
    public function adminPlanItemConfigUpdate($planItem)
    {
        //example of saving a setting
        $cjax = geoCJAX::getInstance();

        $settings = $cjax->get('eWidget');

        if (is_array($settings)) {
            //clean the price
            $price = geoNumber::deformat($settings['price']);
            $planItem->set('price', $price);
            //Note that usually when setting something on a planItem, you will
            //need to save the plan item, but in this case that is done
            //automatically by the system after this function is done.
        }

        return true;
    }

    /**
     * This displays brief details of a widget when the widget is in a long
     * list of stuff.  Sometimes a short list.  Could even be the only thing
     * in the list, who knows.  It will be used to display the item in the admin
     * at Orders > Manage Items (plus a few other places in the admin)
     *
     * See {@link _templateOrderItem::adminDetails()} for full documentation.
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        //This is to display the details when this item shows up in a list of
        //other items.
        return array(
            'type' => 'eWidget',
            'title' => 'One of the 7 wonders.'
        );
    }

    /**
     * This will display info when viewing an item's details.  This is when
     * the admin clicks on an item in the admin at "Orders > Manage Items"
     *
     * See {@link _templateOrderItem::adminItemDisplay()} for full documentation.
     *
     * @param int $item_id
     * @return string What is going to be displayed
     */
    public static function adminItemDisplay($item_id)
    {
        //you could display info about the widget here.

        return 'This is an eWidget of the highest standards.';
    }

    /**
     * Since the default is to cap the first letter, and we don't want to do
     * that, let's not cap the e.
     *
     * See {@link _templateOrderItem::getTypeTitle()} for full documentation.
     *
     * @return string
     */
    public function getTypeTitle()
    {
        //used all over the place (in the admin) when displaying info about items.
        return 'eWidget';
    }

    /**
     * Lets display the number of widgets a user owns.  But since this is an
     * example, we didn't bother to keep track of that (if we did, we might have
     * added a new column to the userdata table during the addon's installation,
     * to keep track of the number)
     *
     * See {@link _templateOrderItem::Admin_site_display_user_data()} for full documentation.
     *
     * @param int $user_id
     * @return string Text to add to page.
     */
    public static function Admin_site_display_user_data($user_id)
    {
        //just a simple example of displaying something on the user info page

        //Note that an order item can also display info on the edit user info
        //page, and update settings when that page is saved, but we've removed
        //those from this eWidget order item as we have no use for it for
        //widgets.  See the _template.php order item for full documentation.

        $html = geoHTML::addOption('# eWidgets owned', 'Not entirely sure, we don\'t keep track of that.  This is just an example after all.');
        return $html;
    }


    /**
     * Just in case admin has changed price of eWidgets since this order item
     * was created, we'll use this to re-save the price.
     *
     * See {@link _templateOrderItem::geoCart_initItem_restore()} for full documentation.
     *
     * @return bool We'll return true to allow restoring.
     */
    public function geoCart_initItem_restore()
    {
        //actually, just let initItem_new do the work
        $this->geoCart_initItem_new();

        return true;
    }

    /**
     * When first creating this item, go ahead and set the cost to that as set
     * in the admin for the price plan used.
     *
     * See {@link _templateOrderItem::geoCart_initItem_new()} for full documentation.
     *
     * @return bool We'll return true to allow new
     */
    public function geoCart_initItem_new()
    {
        //get the cart, it's safe since this will only be called from the cart
        $cart = geoCart::getInstance();

        //make sure the price plan is set correctly
        $this->setPricePlan($cart->user_data['price_plan_id']);

        //get the plan item so we can get the price
        $planItem = $this->getPlanItem();

        //set the cost, default is $0 bucks.
        $this->setCost($planItem->get('price', 0));

        //return true to allow the item to be created
        return true;
    }

    /**
     * This is where we define what steps there are for adding an eWidget.
     *
     * See {@link _templateOrderItem::geoCart_initSteps()} for full documentation.
     *
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        //Get the cart to add some steps to it
        $cart = geoCart::getInstance();

        //we'll add 2 steps: youAreCool and almostFinished
        $cart->addStep('eWidget:youAreCool');
        $cart->addStep('eWidget:almostFinished');

        //Be sure to check out the order item _template.php for full docs
        //on this method.  We won't repeat ourselves here.  Well at least not
        //repeat the really long stuff.

        //get steps from children as well.  Children items are not called
        //automatically, to allow parent items to have more control over
        //their "children" items.
        $children = geoOrderItem::getChildrenTypes('eWidget');
        geoOrderItem::callUpdate('geoCart_initSteps', null, $children);
    }

    /**
     * Since we added a "youAreCool" step, we need to have 3 methods that handle
     * that step: a display, a check vars, and a process.  This one is the
     * display.
     *
     */
    public static function youAreCoolDisplay()
    {
        //This is responsible for all the nitty gritty details of displaying
        //a page, like setting up the page id and everything.  Because of this,
        //this method has a higher likelyhood of needing to be updated in new
        //releases if how a page is displayed changes.

        //get an instance of the cart, it will have a site class in it we can
        //use to display the page.
        $cart = geoCart::getInstance();

        //get the util class, we'll be using a few methods in it.
        $util = geoAddon::getUtil('example');

        //To display this step, we're going to use an example page.
        $cart->site->page_id = $util->getPageId('youAreCool');
        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->language_id = $cart->db->getLanguage();
        $cart->site->addon_name = 'example';

        //get the price
        $price = $cart->item->getCost();

        //get the view to set tpl vars on
        $view = geoView::getInstance();

        //Set common template vars that are used on most cart pages:
        $view->setBodyVar($cart->getCommonTemplateVars());

        //set the template vars, if any.
        $view->setBodyVar('price', $price);
        //let it know if we already know the answer
        $view->setBodyVar('cool_or_not', $cart->item->get('cool_or_not'));
        //let it know the cancel button too
        $view->setBodyVar('cancelButtonUrl', $cart->getCartBaseUrl() . '&amp;action=cancel');
        //let it know about error messages
        $view->setBodyVar('error_msgs', $cart->getErrorMsgs());

        //now set the tpl to use
        $view->setBodyTpl('youAreCool.tpl', 'example');//use styles from main cart pages

        //now display the page
        $cart->site->display_page();
    }


    /**
     * This checks all the input variables submitted as part of the youAreCool
     * step.  If there are any problems, this method will raid an error with
     * the cart and the cart will know not to go on.
     *
     */
    public static function youAreCoolCheckVars()
    {
        $cart = geoCart::getInstance();
        //check the settings!
        $settings = (isset($_POST['eWidget'])) ? $_POST['eWidget'] : false;
        if (!$settings) {
            //Add an error!
            $cart->addError()
                ->addErrorMsg('eWidget', 'Hey!  No pleading the 5th!  Answer the question, are you SO Cool or not?');
            //no need to do the rest of the checks!
            return;
        }
        if ($settings['cool_or_not'] != 'soCool') {
            //they answered that they are not so cool!
            //Throw a cart error, and tell them what for
            $cart->addError()
                ->addErrorMsg('eWidget', 'Oops!  You can\'t buy an eWidget until you are SO Cool!
				You must cool-up before you can buy one of these nifty eWidgets.');
        }
        //if it got this far, everything is AOK!
    }

    /**
     * This only happens if there are no errors raised when we checked the vars
     * before. This method should save any values that need to be saved for
     * the youAreCool step.
     *
     * This step can raise an error with the cart to make the cart not proceed
     * to the next step.  Just because you can, doesn't mean you should though.
     * It is considered bad practice to raise an error in this step, except for
     * special cases where raising an error in checkvars is not possible.
     * If at all possible, you should detect any problems
     * in the check vars stage and raised an error then.
     */
    public static function youAreCoolProcess()
    {
        //Nothing to really save, really.  Still need to define this method
        //though, even if not saving anything in it.

        //Just for good measure though, we'll save something.
        $cart = geoCart::getInstance();
        //since we checked the input var in checkvars, we know the only
        //value it would let through is soCool, so we dont' need to clean
        //the input here.  Just save it.
        $cart->item->set('cool_or_not', $_POST['eWidget']['cool_or_not']);

        //Just so you know what's going on above:  When on one of the steps
        //for adding a parent item, you are guaranteed that $cart->item will
        //be of the correct type.  $cart->item will be the main parent item.
    }

    /**
     * What is displayed for the youAreCool label
     *
     * @return string
     */
    public static function youAreCoolLabel()
    {
        return 'Coolness Checker';
    }

    /**
     * Since we added a "almostFinished" step, we need to have 3 methods that handle
     * that step: a display, a check vars, and a process.  This one is the
     * display.
     *
     */
    public static function almostFinishedDisplay()
    {
        //This is responsible for all the nitty gritty details of displaying
        //a page, like setting up the page id and everything.  Because of this,
        //this method has a higher likelyhood of needing to be updated in new
        //releases if how a page is displayed changes.

        //get an instance of the cart, it will have a site class in it we can
        //use to display the page.
        $cart = geoCart::getInstance();

        //get the util class, we'll be using a few methods in it.
        $util = geoAddon::getUtil('example');

        //To display this step, we're going to use an example page.
        $cart->site->page_id = $util->getPageId('youAreCool');
        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->language_id = $cart->db->getLanguage();
        $cart->site->addon_name = 'example';

        //get the view to set tpl for mainbody on
        $view = geoView::getInstance();

        //Set common template vars that are used on most cart pages:
        $view->setBodyVar($cart->getCommonTemplateVars());

        //let it know the cancel button too
        $view->setBodyVar('cancelButtonUrl', $cart->getCartBaseUrl() . '&amp;action=cancel');
        //let it know about error messages
        $view->setBodyVar('error_msgs', $cart->getErrorMsgs());

        //now set the tpl to use
        $view->setBodyTpl('almostFinished.tpl', 'example');//use styles from main cart pages

        //now display the page
        $cart->site->display_page();
    }


    /**
     * This checks all the input variables submitted as part of the almostFinished
     * step.  If there are any problems, this method will raid an error with
     * the cart and the cart will know not to go on.
     *
     */
    public static function almostFinishedCheckVars()
    {
        //OK Nothing to actually check on this step.  Still need to define this method
        //though or the step won't be considered "valid".
    }

    /**
     * This only happens if there are no errors raised when we checked the vars
     * before. This method should save any values that need to be saved for
     * the almostFinished step.
     *
     * This step can raise an error with the cart to make the cart not proceed
     * to the next step.  Just because you can, doesn't mean you should though.
     * It is considered bad practice to raise an error in this step, except for
     * special cases where raising an error in checkvars is not possible.
     * If at all possible, you should detect any problems
     * in the check vars stage and raised an error then.
     */
    public static function almostFinishedProcess()
    {
        //Nothing to process or save either.  Still need to define this method
        //though or the step won't be considered "valid".
    }

    /**
     * What is displayed for the almostFinished label
     *
     * @return string
     */
    public static function almostFinishedLabel()
    {
        return 'Almost...  Finished...';
    }

    /**
     * This guy is required by the system.  Like most order items, we'll just
     * be returning false here.
     *
     * See {@link _templateOrderItem::geoCart_initItem_forceOutsideCart()} for full documentation.
     *
     * @return bool True to force creating "parellel" cart just
     *  for this item, false otherwise.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }

    /**
     * This is used by the system to determine what, if any, default parents
     * this item can be a part of.  Since this is a main parent order item, it
     * will be returning an empty array.
     *
     * See {@link _templateOrderItem::getParentTypes()} for full documentation.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        //eWidget is a "parent" order item, so return empty array because
        //eWidget has no parents.
        return array();
    }

    /**
     * Used to display the item in various places, primarily in the main Cart
     * view on the client side.
     *
     * See {@link _templateOrderItem::getDisplayDetails()} for full documentation.
     *
     * @param bool $inCart True if this is being called from inside the cart, false otherwise. Note: do NOT
     *  try to use the geoCart object if $inCart is false.
     * @param bool $inEmail True if the results are going to be used in an e-mail
     *   notification
     * @return array|bool Either an associative array as documented above, or boolean false to hide this
     *  item from view.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $price = $this->getCost();
        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => 'An eWidget',//text that is displayed for this item in list of items purchased.
            'canEdit' => true, //show edit button for item, if displaying in cart?
            'canDelete' => true, //show delete button for item, if displaying in cart?
            'canPreview' => true, //show preview button for item, if displaying in cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //Price as it is displayed
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price, //amount this AND all children adds to the total (will add to it as we parse the children)
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
     * Required.
     * Used: By payment gateways to see what types of items are in the cart.
     *
     * This is very similar to {@see _templateOrderItem::getDisplayDetails()} except that the
     * information is used by payment gateways and is specifically for information about what
     * the "cost" of something is for.
     *
     * Should return an associative array, that follows:
     * array(
     *  'type' => string, //The order item type, should always be $this->getType()
     *  'extra' => mixed, //used to convey to payment gateways "custom information" that
     *                      may be needed by the gateway.  Most can set this to null.
     *  'cost' => double, //amount this adds to the total, what getCost returns
     *  'total' => double, //amount this AND all children adds to the total
     *  'children' => array(), //optional, should be array of child items, with the index
     *                          //being the item's ID, and the contents being associative array like
     *                          //this one.  Careful not to get into any infinite loops...
     * )
     *
     * @return array|bool Either an associative array as documented above, or boolean false if
     *   this item has no cost (positive or negative, including children).
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
        if ($return['total'] <> 0) {
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
     * Used to display a preview of our lovely eWidget
     *
     * See {@link _templateOrderItem::geoCart_previewDisplay()} for full documentation.
     *
     */
    public function geoCart_previewDisplay()
    {
        //For this one, we won't bother going through the normal channels. Instead
        //just use a template file directly.

        $tpl = new geoTemplate('addon', 'example');
        echo $tpl->display('eWidget_preview.tpl');
        //just in case, let the view class know the page has been rendered already
        geoView::getInstance()->setRendered(true);
    }

    /**
     * Required by system, to tell if this item uses the other details step.
     *
     * See {@link _templateOrderItem::geoCart_initSteps_addOtherDetails()} for full documentation.
     *
     * @return bool we'll say false.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        //normally if the main order item doesn't display other details step,
        //it checks with any children to see if they do.  We're an eWidget
        //though!  We don't need to check in with children, we know we'll never
        //display that page!

        return false;
    }

    /**
     * Used to display the "add new button" down there under the "add to cart"
     * box.
     *
     * See {@link _templateOrderItem::geoCart_cartDisplay_newButton()} for full documentation.
     *
     */
    public static function geoCart_cartDisplay_newButton()
    {
        if (self::isAnonymous()) {
            //We don't do this if they aren't logged in!
            return '';
        }

        return "Add new eWidget!";
    }

    /**
     * Used to display the Add new eWidget button in my account links module.
     *
     * See {@link _templateOrderItem::my_account_links_newButton()} for full documentation.
     *
     * @return array
     */
    public static function my_account_links_newButton()
    {
        //re-use the loading image.
        return array (
            'label' => 'New eWidget',
            'icon' => '<img src="images/loading.gif" alt="new eWidget" style="vertical-align: middle; width: 42px; height: 42px;" />'
        );
        //NOTE: we advise against animated gifs, we used the loading image for
        //the icon just for the fun of it.
    }

    /**
     * If this were a normal order item, most likely something would be done
     * here, something that is involved when activating or de-activating an
     * eWidget.
     *
     * See {@link _templateOrderItem::processStatusChange()} for full documentation.
     *
     * @param string $newStatus a string of what the new status for the item should be.  The statuses
     *  built into the system are active, pending, and pending_alter.
     * @param bool $sendEmailNotices If set to false, you should not send any e-mail notifications
     *  like might be normally done.  (if it's false, it will be because this is called
     *  from admin and admin said don't send e-mails)
     * @param bool $updateCategoryCount If true, should update the category count
     *  for any listings that may have activated or de-activated as result of
     *  the status change.
     */
    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
    {
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
            //do activate actions here, such as setting listing to live
            //This is just an example so it doesn't really do anything here...
        } elseif (!$activate && $already_active) {
            //do de-activate actions here, such as setting listing to not be live any more.
            //This is what would happen if an admin changes their mind
            //and later decides to change an item from being active to being pending.


            //This is just an example so it doesn't really do anything here...
        }
        //NOTE: do NOT need to call children, parent does that for us :)
    }

    /**
     * Displays info on the account info page.
     *
     * See {@link _templateOrderItem::User_management_information_display_user_data()} for full documentation.
     *
     * @return array Associative array, with
     *  the structure array ('label' => 'Left side','value' => 'Right side')
     */
    public static function User_management_information_display_user_data()
    {
        //use it as an advertisement to buy an eWidget!  (this is just to
        //show how to display info, we do not suggest you use this page
        //to advertise a product, unless it makes sense to do so)
        return array('label' => 'eWidgets', 'value' => 'You should Buy Some!');
    }

    /**
     * Optional
     * Used: in User_management_home::menu()
     *
     * Use this to add a link to the My Account Links module (or old user management home page)
     */
    public static function User_management_home_body()
    {
        //TODO: implement or remove

        //Sample of something to do
        $view = geoView::getInstance();

        $linkData = array();
        $linkData['label'] = 'Find some eWidgets';
        $linkData['icon'] = '<img src="images/loading.gif" alt="new eWidget" style="vertical-align: middle; width: 42px; height: 42px;" />';
        $linkData['link'] = 'http://www.google.com/search?q=eWidget';
        $linkData['active'] = false; //set this to true if user is currently viewing the linked page
        $linkData['needs_attention'] = false; // set to true to highlight the link as needing attention

        //this is a bit roundabout since $view's __get and __set are overloaded
        //but it does the trick
        $orderItemLinks = $view->orderItemLinks;
        $orderItemLinks[] = $linkData;
        $view->orderItemLinks = $orderItemLinks;
    }

    /**
     * Optional.
     * Used: in geoCart and my_account_links module
     *
     * This is used to display what the action is if this order item is the main type.  It should return
     * something like "adding new listing" or "editing images".
     *
     * @param array $vars Array with the action and step to get the action name for.
     * @return string
     */
    public static function getActionName($vars)
    {
        return 'Adding eWidget';
    }

    //That's it!  There are more "built in" methods that can be used that are
    //optional, to see all those be sure to check out:
    //classes/order_items/_template.php
}
