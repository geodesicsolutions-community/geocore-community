<?php

//my_account_links.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.01.0-38-g6ec678f
##
##################################

/*  With the new design changes, this module can potentially be called 3+ times on a page for different output, but with the same basic data
    This dummy class is just an easy way to cache the back-end processing for that and only do it once per pageload
    The "ifdef" makes sure the class itself is only added once ;) */
if (!defined('MY_ACCOUNT_LINKS_VARCACHE_INIT')) {
    class module_my_account_links_varcache
    {

        public static $_tplVars = null;

        public static function loadVars()
        {
            if (self::$_tplVars) {
                return self::$_tplVars;
            }
            $db = DataAccess::getInstance();
            $user_id = intval(geoSession::getInstance()->getUserID());

            //TODO: Make this a setting set in admin...
            //for now, change this to 1 to make the total displayed for the cart include taxes and discounts.
            $fullCartTotal = false;

            if (!$user_id) {
                return false;
            }

            $tpl_vars = array();

            $cart = geoCart::getInstance();
            //Init the cart, but only the cart items, don't need all the overhead of
            //initializing everying in the cart.  If we are on a "cart" page, the cart
            //will already be initialized, in which case the init() will know to not
            //re-initilize itself.
            $cart->init(true);

            $bitmask = 1 + 2 + 4 + 8 + 16 + 32;

            if (geoPC::is_ent() && isset($cart->user_data['restrictions_bitmask'])) {
                $bitmask = $cart->user_data['restrictions_bitmask'];
            }
            $bitmask = (int)$bitmask;

            $links = array();
            $url_base = $db->get_site_setting('classifieds_file_name');

            $inCart = $tpl_vars['inCart'] = (isset($_GET['a']) && $_GET['a'] == 'cart') ? true : false;
            $tpl_vars['allFree'] = !geoMaster::is('site_fees');

            $messages = $db->get_text(true, 10208);

            if ($bitmask & 1) {
                //"place a listing" turned on

                /*
                 * CART Setup
                *
                */
                $cartNumItems = 0;
                if ($cart->order) {
                    foreach ($cart->order->getItem('parent') as $item) {
                        //get the number of "main" order items (no parent, processOrder < 1000)
                        $processOrder = $item->getProcessOrder();
                        if ($processOrder < 1000) {
                            //anything with process order less than 1000 is considered "normal"
                            $details = $item->getDisplayDetails(true);
                            //note: $details['total'] includes the cost of any children items ($details['cost'] does not)
                            $tpl_vars['cartItems'][] = array('title' => $details['title'], 'cost' => geoString::displayPrice($details['total']));
                            $cartNumItems++;
                        }
                    }
                }
                //cart data/link display
                $tpl_vars['cartItemCount'] = $cartNumItems;
                $tpl_vars['cartTotal'] = ($cart->order) ? $cart->getCartTotal() : 0;

                //cart "action"

                $cartLinks = array();

                $cartActionIndex = $tpl_vars['cartActionIndex'] = $cart->getAction();
                $tpl_vars['cartStepIndex'] = $cart->current_step;

                if ($cart->isInMiddleOfSomething()) {
                    //In middle of something
                    //get the text that will have actions
                    $vars = array('action' => '', 'step' => 'my_account_links');
                    //use getType as that will work even if in "stand alone" cart.
                    $itemType = $cart->item->getType();
                    $currentAction = $tpl_vars['cartAction'] = geoOrderItem::callDisplay('getActionName', $vars, '', $itemType);
                    //let the template know whether it is a stand-alone cart or not.
                    $tpl_vars['isStandalone'] = $cart->isStandaloneCart();
                } else {
                    //not adding normal item to cart, so must be on main cart page (or checking out)
                    //so show all buttons
                    $cartLinks = geoOrderItem::callDisplay('my_account_links_newButton', null, 'array');
                    foreach ($cartLinks as $a_name => $ldata) {
                        if (!isset($ldata['link'])) {
                            //automatically set all the links so order items don't have to bother with
                            //that part, but if they do, don't set it here.
                            $cartLinks[$a_name]['link'] = $url_base . "?a=cart&amp;action=new&amp;main_type=$a_name";
                        }
                    }
                }

                $tpl_vars['cartLinks'] = $cartLinks;

                //so we don't show the cart-specific template stuff if this section turned off by bitmask
                $tpl_vars['show_cart'] = true;

                $messages = $db->get_text(true, 10208);

                //active/expired listings
                $activeCount = (int)$db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `seller` = ? AND `live` = 1", array($user_id));
                $links['active_ads'] = array('link' => $url_base . "?a=4&amp;b=1", 'label' => $messages[500458], 'icon' => $messages[500459], 'badge' => $activeCount);

                $links['expired_ads'] = array('link' => $url_base . "?a=4&amp;b=2", 'label' => $messages[500460], 'icon' => $messages[500461]);
            }

            if ($bitmask & 2) {
                //check for unread messages
                $sql = "SELECT count(message_id) FROM " . geoTables::user_communications_table . " WHERE `read` <> '1' AND receiver_deleted = 0 AND `message_to` = " . $user_id;
                $tpl_vars['num_unread_messages'] = $unreadCount = (int)$db->GetOne($sql);
                $msg_needsAttention = ($unreadCount > 0) ? true : false;

                $links['my_messages'] = array('link' => $url_base . "?a=4&amp;b=8", 'label' => $messages[500472], 'icon' => $messages[500473], 'needs_attention' => $msg_needsAttention, 'badge' => $unreadCount);
                //link removed per Rob; bug#1632
                //$links['message_settings'] = array('link' => $url_base . "?a=4&amp;b=7", 'label' => $messages[500474], 'icon' => $messages[500475]);
            }

            if ($bitmask & 4) {
                $favoritesCount = (int)$db->GetOne("SELECT COUNT(`classified_id`) FROM " . geoTables::favorites_table . " as f, " . geoTables::classifieds_table . " as c WHERE f.classified_id=c.id AND f.`user_id` = ? AND c.live=1", array($user_id));
                $links['favorites'] = array('link' => $url_base . "?a=4&amp;b=10", 'label' => $messages[500462], 'icon' => $messages[500463], 'badge' => $favoritesCount);
            }

            if ($bitmask & 8) {
                $filterCount = (int)$db->GetOne("SELECT COUNT(`filter_id`) FROM " . geoTables::ad_filter_table . " WHERE `user_id` = ?", array($user_id));
                $links['ad_filters'] = array('link' => $url_base . "?a=4&amp;b=9", 'label' => $messages[500464], 'icon' => $messages[500465], 'badge' => $filterCount);
            }

            if (geoMaster::is('auctions')) {
                if ($bitmask & 32) {
                    //get the number of open feedbacks
                    $sql = "select auction_id from " . geoTables::auctions_feedbacks_table . " where rater_user_id=? AND done=0";
                    $result = $db->Execute($sql, array($user_id));
                    $tpl_vars['num_open_feedbacks'] = 0;
                    while ($auction = $result->FetchRow()) {
                        //make sure auctions still exist in the DB before counting
                        if (is_object(geoListing::getListing($auction['auction_id'], false, true))) {
                            $tpl_vars['num_open_feedbacks']++;
                        }
                    }

                    $links['feedback'] = array('link' => $url_base . "?a=4&amp;b=22", 'label' => $messages[500468], 'icon' => $messages[500469], 'badge' => $tpl_vars['num_open_feedbacks']);
                    if ($tpl_vars['num_open_feedbacks'] > 0) {
                        $links['feedback']['needs_attention'] = true;
                        //if there are open feedbacks, make the link go to the Open Feedbacks page (which there is otherwise no easy way to get to)
                        $links['feedback']['link'] = $url_base . "?a=4&amp;b=22&amp;c=1";
                    }
                }


                require_once CLASSES_DIR . 'user_management_list_bids.php';
                $numBidsObj = new Auction_list_bids();
                $numBids = (int)$numBidsObj->list_auctions_with_your_bid(true);
                $links['current_bids'] = array('link' => $url_base . "?a=4&amp;b=21", 'label' => $messages[500466], 'icon' => $messages[500467], 'badge' => $numBids);
                if ($numBids > 0) {
                    $links['current_bids']['needs_attention'] = true;
                }

                if ($bitmask & 16) {
                    //moved these to below "Current Bids" since they don't have badges
                    if ($db->get_site_setting('invited_list_of_buyers')) {
                        $links['whitelist'] = array('link' => $url_base . "?a=4&amp;b=20", 'label' => $messages[500478], 'icon' => $messages[500479]);
                    }
                    if ($db->get_site_setting('black_list_of_buyers')) {
                        $links['blacklist'] = array('link' => $url_base . "?a=4&amp;b=19", 'label' => $messages[500480], 'icon' => $messages[500481]);
                    }
                }
            }

            $links['user_info'] = array('link' => $url_base . "?a=4&amp;b=3", 'label' => $messages[500470], 'icon' => $messages[500471]);



            //ask addons if they'd like to add any links
            $extraVars = array('url_base' => $url_base);
            $addons = geoAddon::triggerDisplay('my_account_links_add_link', $extraVars, geoAddon::ARRAY_ARRAY);
            foreach ($addons as $addon_name => $addon_links) {
                if (!isset($addon_links['label'])) {
                    foreach ($addon_links as $name => $link) {
                        $links[$name] = $link;
                    }
                } else {
                    $links [$addon_name] = $addon_links;
                }
            }

            //Make sure user group is set for payment gateways before calling
            $sql = "SELECT `group_id` FROM " . geoTables::user_groups_price_plans_table . " WHERE `id` = " . intval(geoSession::getInstance()->getUserID());
            $groupId = $db->GetOne($sql);
            geoPaymentGateway::setGroup($groupId);

            //allow different payment gateways to display things on the user account home page
            geoPaymentGateway::callUpdate('User_management_home_body', $extraVars);
            //from account balance gateway as result of above call:


            //also allow items to add stuff if they need
            geoOrderItem::callUpdate('User_management_home_body', $extraVars);

            //since those work by assigning to view class, and modules are now loaded on-the-fly, need
            //to grab view vars to use for tpl_vars
            $view = geoView::getInstance();
            $tpl_vars['paymentGatewayLinks'] = $view->paymentGatewayLinks;
            $tpl_vars['orderItemLinks'] = $view->orderItemLinks;

            //set active page, so we can stylize it differently
            if ($_REQUEST['b'] && is_numeric($_REQUEST['b'])) {
                switch ($_REQUEST['b']) {
                    case 1:
                        $links['active_ads']['active'] = true;
                        break;
                    case 2:
                        $links['expired_ads']['active'] = true;
                        break;
                    case 3: //break intentionally omitted
                    case 4:
                        $links['user_info']['active'] = true;
                        break;
                    case 7:
                        $links['message_settings']['active'] = true;
                        break;
                    case 8:
                        $links['my_messages']['active'] = true;
                        break;
                    case 9: //break intentionally omitted
                    case 14:
                        $links['ad_filters']['active'] = true;
                        break;
                    case 10:
                        $links['favorites']['active'] = true;
                        break;
                    case 12: //break intentionally omitted
                    case 13:
                        //$links['signs_flyers']['active'] = true;
                        //addons are now responsible for setting this for themselves
                        break;
                    case 19:
                        $links['blacklist']['active'] = true;
                        break;
                    case 20:
                        $links['whitelist']['active'] = true;
                        break;
                    case 21:
                        $links['current_bids']['active'] = true;
                        break;
                    case 22:
                        $links['feedback']['active'] = true;
                        break;
                    default:
                        //do nothing
                }
            }
            //show my account?  Hint: Can over-ride this in template using {module show_my_account_section=0 tag='my_account_links'}
            $tpl_vars['show_my_account_section'] = $tpl_vars['show_account_finance_section'] = 1;


            $tpl_vars['links'] = $links;

            //let the template know things about this user, to assist in deciding what links to show
            //NOTE: not used by our code at present, but added at client request
            $tpl_vars['userData'] = geoUser::getData(geoSession::getInstance()->getUserId());

            self::$_tplVars = $tpl_vars;
            return self::$_tplVars;
        }
    }
    define('MY_ACCOUNT_LINKS_VARCACHE_INIT', 1);
}

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
        ->setModuleVar($show_module['module_replace_tag'], module_my_account_links_varcache::loadVars())
        ->addCssFile(geoTemplate::getUrl('css', 'module/my_account_links.css'));
