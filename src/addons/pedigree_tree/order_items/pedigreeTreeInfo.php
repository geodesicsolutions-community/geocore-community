<?php

//order_items/pedigreeTreeInfo.php

# Pedigree tree order item in addon

class pedigreeTreeInfoOrderItem extends geoOrderItem
{
    protected $type = "pedigreeTreeInfo";
    const type = 'pedigreeTreeInfo';
    protected $defaultProcessOrder = 35;
    const defaultProcessOrder = 35;
    const addonName = 'pedigree_tree';

    public static function detailsCheckVars_getMoreDetailsEnd()
    {
        $cart = geoCart::getInstance();

        $cat = $cart->item->getCategory();
        $groupId = (int)$cart->user_data['group_id'];

        $fields = geoFields::getInstance($groupId, $cat);//group ID for current user logged in..

        if (!$fields->addon_pedigree_tree->is_enabled) {
            //not enabled, nothing more to do

            return;
        }

        $util = geoAddon::getUtil('pedigree_tree');
        //clean input
        $cart->site->session_variables['pedigreeTree'] = $util->cleanTree($cart->site->session_variables['pedigreeTree']);

        $reg = geoAddon::getRegistry(self::addonName);

        $util->checkRequired($cart->site->session_variables['pedigreeTree'], 'b[pedigreeTree]', 0, $reg->maxReqGens);
    }

    public static function detailsDisplay_getMoreDetailsEnd()
    {
        $cart = geoCart::getInstance();
        $reg = geoAddon::getRegistry(self::addonName);
        //need to attach ourselves to main item
        $order_item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);

        $cat = $cart->item->getCategory();
        $groupId = (int)$cart->user_data['group_id'];

        $fields = geoFields::getInstance($groupId, $cat);//group ID for current user logged in..


        if (!$fields->addon_pedigree_tree->is_enabled) {
            if ($cart->isCombinedStep()) {
                //if addon is enabled, always add the CSS to the page, so that
                //it works on combined page which may be loaded before category
                $pre = (defined('IN_ADMIN')) ? '../' : '';
                geoView::getInstance()->addCssFile($pre . geoTemplate::getURL('css', 'addon/pedigree_tree/tree.css'));
            }

            //not enabled
            if ($order_item && $order_item->getId()) {
                //remove the order item
                $cart->order->detachItem($order_item->getId());
                geoOrderItem::remove($order_item->getId());
            }
            //nothing else to do
            return;
        }

        if (!$order_item) {
            //create new
            $order_item = new pedigreeTreeInfoOrderItem();
            $order_item->setType(self::type);
            $order_item->setParent($cart->item);//this is a child of the parent
            $order_item->setOrder($cart->order);

            $order_item->save();//make sure it's serialized
            $cart->order->addItem($order_item);
            trigger_error('DEBUG CART: Attached pedigree tree');
        }


        $tpl = new geoTemplate(geoTemplate::ADDON, self::addonName);
        $tpl->assign($cart->getCommonTemplateVars());
        $tpl->assign('maxGen', $reg->maxGens);
        $tpl->assign('currentGen', 1);
        $tpl->assign('gender', 'sire');
        $tpl->assign('fieldName', 'b[pedigreeTree]');
        $tpl->assign('data', $cart->site->session_variables['pedigreeTree']);
        $tpl->assign('errors', $cart->getErrorMsgs());
        $return = array ();

        $msgs = geoAddon::getText('geo_addons', self::addonName);

        $return['section_head'] = $msgs['placement_section_title'];
        $return['section_desc'] = $msgs['placement_section_desc'];

        $tpl->assign('iconSet', $reg->iconSet);
        if ($reg->iconSet && $reg->iconSet != 'none') {
            $tpl->assign('icon_sire', "images/addon/pedigree_tree/icon_sets/{$reg->iconSet}/sire.gif");
            $tpl->assign('icon_dam', "images/addon/pedigree_tree/icon_sets/{$reg->iconSet}/dam.gif");
        }

        $parent = $order_item->getParent();
        if ($parent && $parent->getType() == 'listing_edit' && !$fields->addon_pedigree_tree->can_edit) {
            //editing listing, but set to not be able to edit
            $tpl->assign('maxGen', $maxGen = geoAddon::getUtil(self::addonName)->getMaxGen($cart->site->session_variables['pedigreeTree']));
            $tplFile = ($maxGen > 4) ? 'listing_details/tree_unlimited.tpl' : 'listing_details/tree.tpl';
        } else {
            $tplFile = ($reg->maxGens > 4) ? 'listing_placement/tree_unlimited.tpl' : 'listing_placement/tree.tpl';
        }
        $pre = (defined('IN_ADMIN')) ? '../' : '';
        $return['full'] = $tpl->fetch($tplFile);
        geoView::getInstance()->addCssFile($pre . geoTemplate::getURL('css', 'addon/pedigree_tree/tree.css'));
        return $return;
    }

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

        $parent = $this->getParent();
        if (!$parent) {
            //Doh!  this should not happen.
            trigger_error('ERROR CART: Count not get parent, this should not happen!');
            return;
        }
        $util = geoAddon::getUtil(self::addonName);

        if ($parent->getType() == 'listing_edit') {
            //do things special for editing

            $force = ($activate) ? true : false;
            $session_variables = listing_editOrderItem::getSessionVars($parent, $force);

            $util->setTreeFor($parent->get('listing_id'), $session_variables['pedigreeTree']);
        } else {
            //either totally add them or totally remove them since we are activating
            //or deactivating a listing
            if ($activate) {
                //do activate actions here, such as setting listing to live
                $session_variables = $parent->get('session_variables');

                $util->setTreeFor($parent->get('listing_id'), $session_variables['pedigreeTree']);
            } elseif (!$activate && $already_active) {
                //do de-activate actions here, such as setting listing to not be live any more.
                //This is what would happen if an admin changes their mind
                //and later decides to change an item from being active to being pending.

                $util->removeTreeFor($parent->get('listing_id'));
            }
        }
        //NOTE: do not need to call children, parent does that for us :)
    }

    public static function copyListing($parentItem)
    {
        if (!class_exists('geoCart', false)) {
            //this copy listing needs a cart environment to copy tree
            //TODO: make it not need cart...
            return;
        }
        $cart = geoCart::getInstance();
        if (!$cart->site->session_variables) {
            $cart->site->session_variables = $parentItem->get('session_variables');
        }
        $session_variables = ($parentItem) ? $parentItem->get('session_variables') : $cart->site->session_variables;
        if ($cart->site->session_variables['pedigreeTree']) {
            trigger_error('DEBUG CART: Copy Listing Here');

            $item = geoOrderItem::getOrderItemFromParent($parentItem, self::type);

            if (!is_object($item)) {
                //create new
                $item = new pedigreeTreeInfoOrderItem();
                $parentUse = ($parentItem) ? $parentItem : $cart->item;
                $order = ($parentUse->getOrder()) ? $parentUse->getOrder() : $cart->order;
                if (!$order) {
                    //if we can't get the order, we can't do much
                    return false;
                }
                $item->setType(self::type);
                $item->setParent($parentUse);//this is a child of the parent
                $item->setOrder($order);

                $item->save();//make sure it's serialized
                $order->addItem($item);
            }
        }
        trigger_error('DEBUG CART: Copy Listing Here');
    }

    //required by system
    public function displayInAdmin()
    {
        return true;
    }

    //required by system, doesn't have to do anything though
    public static function geoCart_initSteps($allPossible = false)
    {
    }

    //required by system
    public static function geoCart_initItem_forceOutsideCart()
    {
        return false;
    }

    //required by system
    public static function getParentTypes()
    {
        return array(
            'classified',
            'classified_recurring',
            'auction',
            'listing_renew_upgrade',
            'listing_change_admin',
            );
    }

    //required by system
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        return false;
    }

    //required by system
    public function getCostDetails()
    {
        return false;
    }

    //required by system
    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
    }
}
