<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.4.4-41-ga8d7c9f
##
##################################

if (class_exists('classes_AJAX') or die()) {
}

class CLASSES_AJAXController_OffsiteVideos extends classes_AJAX
{

    public $messages = null;

    public function __construct()
    {
        $db = DataAccess::getInstance();
        $this->messages = $db->get_text(true, 10);
    }

    public function deleteVideo()
    {
        $this->jsonHeader();

        $adminId = (int)$_POST['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        //init the session, this one is a normal ajax call so don't need to do
        //fancy stuff
        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_POST['userId'] : null;
        $cart->init(true, $userId);
        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem('offsite_videos', $price_plan, $category);

        //which slot to delete
        $deleteSlot = (int)$_POST['videoSlot'];

        $order_item = $cart->getChildItem('offsite_videos');
        if (!$order_item && $cart->item && $cart->item->getType() == 'listing_edit') {
            //it is listing edit.
            $order_item = offsite_videosOrderItem::addNewItem();

            $order_item->set('video_slots', offsite_videosOrderItem::getPreExistingVideos());
            //save the order item
            $order_item->save();
        }
        if (!$order_item) {
            //failsafe, this shouldn't happen unless multiple people editing at once or something
            return $this->_returnError('Order item not found, cannot delete.');
        }

        $min = (int)$planItem->minVideos;
        $max = (int)offsite_videosOrderItem::getMaxVideos();
        $free = (int)(geoPC::is_ent()) ? $planItem->freeVideos : 0;
        $cost = $planItem->get('costPerVideo', 0);

        //get current videos
        $existingVideos = $order_item->get('video_slots');

        //remove the requested video
        $slotUrls = array();
        $deleted = false;
        foreach ($existingVideos as $slotNum => $slot) {
            if ($slotNum == $deleteSlot) {
                //don't include this one!  this is the one being deleted...
                $deleted = true;
                continue;
            }
            $slotUrls[$slotNum] = $slot['video_id'];
        }
        if (!$deleted) {
            //did not find anything to delete!
            return $this->_returnError($this->messages[500936]);
        }
        //process remaining videos, let processing get rid of empty space and all that
        offsite_videosOrderItem::processSlots($order_item, $slotUrls);
        $order_item->save();

        //return results
        $data = $this->_returnChangedVideos(array(), $order_item, $min, $max, $free, $cost);
        $data['msg'] = $this->messages[500937];
        echo $this->encodeJSON($data);
    }

    public function sortVideos()
    {
        //set the header to signify this is returning json
        $this->jsonHeader();

        $adminId = (int)$_POST['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        //init the session, this one is a normal ajax call so don't need to do
        //fancy stuff
        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_POST['userId'] : null;
        $cart->init(true, $userId);
        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem('offsite_videos', $price_plan, $category);

        $order_item = $cart->getChildItem('offsite_videos');
        if (!$order_item && $cart->item && $cart->item->getType() == 'listing_edit') {
            //it is listing edit.
            $order_item = offsite_videosOrderItem::addNewItem();

            $order_item->set('video_slots', offsite_videosOrderItem::getPreExistingVideos());
            //save the order item
            $order_item->save();
        }
        if (!$order_item) {
            //sanity check, shouldn't be able to get here
            return $this->_returnError('Internal Error, please try again.  Debug: Could not get order item to update data for item.');
        }

        $min = (int)$planItem->minVideos;
        $max = (int)offsite_videosOrderItem::getMaxVideos();

        //parse the order into an array
        parse_str($_POST['videoSlots'], $inputData);
        $slots = $inputData['offsite_video_slot'];
        if (!is_array($slots) || !$slots) {
            //no order returned...
            return $this->_returnError($this->messages[500938]);
        }

        //data to be returned
        $data = array();

        //Unlike other methods, this one does all the actual work instead of
        //passing it off to the image order item to do.

        //clear errors and junk
        $existingSlots = $order_item->get('video_slots');

        $slotUrls = array();

        $applyChanges = true;
        //it's going to be an array like array (0 => 2, 1 => 3, 2 => 1, 3 => 4)
        //so it's our job to re-sort them, the value is the "old" display
        //order, the key is the "new" display order - 1.

        for ($i = 1; $i <= $max; $i++) {
            $orderIndex = $i - 1;
            if (isset($existingSlots[$slots[$orderIndex]])) {
                $slotUrls[$i] = $existingSlots[$slots[$orderIndex]]['video_id'];
            }
        }

        offsite_videosOrderItem::processSlots($order_item, $slotUrls);
        $order_item->save();

        //must get entire section at once, since the re-sorting will have messed
        //up the order of all the element containers
        $data = array_merge($data, offsite_videosOrderItem::mediaDisplay());

        $data['msg'] = $this->messages[500939];

        echo $this->encodeJSON($data);
    }

    public function uploadVideo()
    {
        $this->jsonHeader();
        $adminId = (int)$_POST['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        //massage things a bit to get them in the right places...

        //first, set up the session
        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_POST['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem('offsite_videos', $price_plan, $category);

        $min = (int)$planItem->minVideos;
        $max = (int)offsite_videosOrderItem::getMaxVideos();
        $free = (int)(geoPC::is_ent()) ? $planItem->freeVideos : 0;
        $cost = $planItem->get('costPerVideo', 0);

        //let offsite_video videos do the actual work
        geoOrderItem::callUpdate('mediaCheckVars', null, 'offsite_videos');
        if (!$cart->errors) {
            geoOrderItem::callUpdate('mediaProcess', null, 'offsite_videos');
        }

        if ($cart->errors > 0) {
            $msg = $cart->getErrorMsg('offsite_videos');
            if (!$msg) {
                //just fail-safe, shouldn't get here though
                $msg = $this->messages[500940];
            }
            $data['error'] = $msg;
        } else {
            $data['msg'] = $this->messages[500941];
        }

        //figure out what slots were changed
        $order_item = $cart->getChildItem('offsite_videos');
        if ($order_item) {
            $data = $this->_returnChangedVideos($data, $order_item, $min, $max, $free, $cost);
        }

        echo $this->encodeJSON($data);
    }

    private function _returnChangedVideos($data, $order_item, $min, $max, $free, $cost)
    {
        $cart = geoCart::getInstance();
        $changedSlots = $order_item->get('latest_changes');
        $uploadSlots = $order_item->get('video_slots');
        $slotErrors = $order_item->get('slot_errors');

        $slotData = array();
        $editSlot = 1;
        $parentType = ($order_item->getParent()->getType());
        for ($i = 1; $i <= $max; $i++) {
            if (!isset($changedSlots[$i]) && isset($uploadSlots[$i])) {
                //no changes to this one, no reason to update it, unless it is being edited
                if (isset($uploadSlots[$i])) {
                    //mark editing just in case
                    $editSlot = $i + 1;
                }
                continue;
            }
            if (isset($uploadSlots[$i])) {
                $slot = $uploadSlots[$i];
                $slot['empty'] = false;
                $editSlot = $i + 1;
            } else {
                $slot = array();
                $slot['empty'] = true;
            }
            $slot['required'] = ($i <= $min);
            if ($cost && $parentType != 'listing_edit') {
                $slot['cost'] = ($free >= $i) ? $cart->site->messages[500927] : geoString::displayPrice($cost);
            }
            if (isset($slotErrors[$i])) {
                $slot['error'] = $slotErrors[$i];
            }
            $slot['slotNum'] = $i;
            $slotData[$i] = $slot;
        }

        if ($editSlot <= $max) {
            if (!isset($slotData[$editSlot])) {
                //oops have to create it...
                if (isset($uploadSlots[$editSlot])) {
                    $slot = $uploadSlots[$editSlot];
                    $slot['empty'] = false;
                } else {
                    $slot = array();
                    $slot['empty'] = true;
                }
                $slot['required'] = ($editSlot <= $min);
                if ($cost) {
                    $slot['cost'] = ($free >= $editSlot) ? $cart->site->messages[500927] : geoString::displayPrice($cost);
                }
                if (isset($slotErrors[$editSlot])) {
                    $slot['error'] = $slotErrors[$editSlot];
                }
                $slot['slotNum'] = $editSlot;
                $slotData[$editSlot] = $slot;
            }
            $slotData[$editSlot]['editing'] = true;
        }

        $data['edit_slot'] = $editSlot;
        $tpl_vars = $cart->getCommonTemplateVars();
        foreach ($slotData as $slotNum => $slot) {
            //go through each one and get the slot HTML specifically for that slot

            $tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
            $tpl->assign($tpl_vars);
            $tpl->assign('slot', $slot);
            $tpl->assign('slotNum', $slotNum);
            $tpl->assign('in_ajax', true);
            $data['changed_slots'][] = array ('slotNum' => $slotNum, 'contents' => $tpl->fetch('offsite_videos/upload_slot.tpl'));
        }
        return $data;
    }

    /**
     * Run AFTER cart->init() has been already run.  This checks to make sure user
     * is currently in the middle of editing or placing something new and that they
     * are on the images step.
     * @return unknown_type
     */
    private function _validateCartStep()
    {
        //simulate server error, un-comment line below
        //return;

        $cart = geoCart::getInstance();

        $step = $cart->cart_variables['step'];
        $userId = (int)$_POST['userId'];
        $adminId = (int)$_POST['adminId'];

        $session = geoSession::getInstance();

        $sessionUser = $session->getUserId();

        $checkUser = ($adminId) ? $adminId : $userId;

        if ($checkUser && !$sessionUser) {
            //user was logged in, now logged out

            return $this->_returnError($this->messages[500692], 'errorSession');
        }
        if ($sessionUser != $checkUser) {
            //user different than when started?

            return $this->_returnError($this->messages[500693], 'errorSession');
        }

        //check to make sure there is an item in there
        if (!$cart->item) {
            //oops, no item in cart, can't go forward.  Not on images step error msg
            return $this->_returnError($this->messages[500694] . ' ' . print_r($cart->user_data, 1), 'errorSession');
        }

        //make sure the step is not one of the built in ones
        if ($step !== 'combined' && strpos($step, ':') === false) {
            //They are on a built-in step, not media.  Not on images step error msg
            return $this->_returnError($this->messages[500694], 'errorSession');
        }

        //make sure the order items that are OK to be attached to
        $validItems = geoOrderItem::getParentTypesFor('offsite_videos');
        $validItems[] = 'offsite_videos'; //offsite_videos would be the item if they clicked on edit button in cart.

        if (!in_array($cart->item->getType(), $validItems)) {
            //oops! this isn't a valid order item...  Not on images step error msg
            return $this->_returnError($this->messages[500694], 'errorSession');
        }

        //got this far, they should be on images step...
        return true;
    }

    /**
     * Internal method to easily "throw an error", it even returns false so you
     * can return the method call if you need to return false anyways.  Note that
     * this calls app bottom, so you should be finished with any cleanup before
     * calling this.
     *
     * @param string $errorMsg Error message to display to user, if blank it will
     *  display "err txt" so don't leave it blank.
     * @param string $errField by default it is "error", but can pass in "errorSession"
     *  to make it throw a session related error.  This gets interpreted as the
     *  key to the error message so js needs to know what to do with it (it
     *  is built to handle "error" and "errorSession" automatically as errors)
     *
     * @return bool Always returns false.
     */
    private function _returnError($errorMsg, $errField = 'error')
    {
        if (!strlen($errorMsg)) {
            //make sure message has something in it, if this happens then admin
            //has blanked out the text message.
            $errorMsg = 'err txt';
        }
        $data = array ($errField => $errorMsg);
        include GEO_BASE_DIR . 'app_bottom.php';

        echo $this->encodeJSON($data);
        return false;
    }
}
