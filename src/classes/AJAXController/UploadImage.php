<?php

/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/

##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    17.12.0-9-ge80f84f
##
##################################

if (class_exists('classes_AJAX') or die()) {
}

class CLASSES_AJAXController_UploadImage extends classes_AJAX
{

    public $messages = null;

    public function __construct()
    {
        $db = DataAccess::getInstance();
        $this->messages = $db->get_text(true, 10);
    }

    /**
     * This is the new PLUpload file uploading routine
     */
    public function upload()
    {
        $this->jsonHeader();
        $adminId = (int)$_GET['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }
        if ($_SERVER['HTTP_USER_AGENT'] === 'Shockwave Flash' && isset($_GET['ua'])) {
            //manually set the user agent...
            $_SERVER['HTTP_USER_AGENT'] = $_GET['ua'];
        }

        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_GET['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        //Manually call checkVars and process on main images order item,
        //let it do all the work of processing the image.  Since we are
        //processing here and not through the cart, the step will not be
        //incremented before we're finished.

        geoOrderItem::callUpdate('mediaCheckVars', null, 'images');
        if (!$cart->errors) {
            geoOrderItem::callUpdate('mediaProcess', null, 'images');
        }

        if ($cart->errors > 0) {
            //oops! return error
            $msg = $cart->getErrorMsg('images');
            if (!$msg) {
                //just fail-safe, shouldn't get here though
                $msg = 'Unknown error, please try again.';
            }
            return $this->_error($msg);
        } else {
            $data['msg'] = $this->messages[500691];
        }
        $data['preview'] = $this->imagesBox();
        echo $this->encodeJSON($data);
    }
    /**
     * Edit the image title (label)
     */
    public function editTitle()
    {
        $this->jsonHeader();
        $adminId = (int)$_GET['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_GET['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        //Since this is simply editing the title, don't bother calling image
        //order item to do it for us...
        $image_id = (int)$_POST['image_id'];
        $newTitle = trim($_POST['title']);
        //make sure the image ID is in the array of images captured...
        $images_captured = imagesOrderItem::getImagesCaptured();
        $image_id_found = false;
        if ($images_captured && $image_id) {
            foreach ($images_captured as $slot => $info) {
                if ($info['id'] == $image_id) {
                    //found it!
                    $image_id_found = true;
                    break;
                }
            }
        }
        if (!$image_id_found) {
            //image ID not found...
            $msgs = $cart->db->get_text(true, 10);
            return $this->_error($msgs[500689]);
        }
        $cart->site->get_badword_array();
        $cart->site->get_ad_configuration();

        //clean up the title...
        $newTitle = $cart->site->check_for_badwords($newTitle);
        $newTitle = geoImage::shortenImageTitle($newTitle, $cart->site->ad_configuration_data->MAXIMUM_IMAGE_DESCRIPTION);
        //update the image, be sure to set the listing ID to 0 so image is not used
        //until it is approved.
        $cart->db->Execute("UPDATE " . geoTables::images_urls_table . " SET `classified_id` = 0, `image_text`=? WHERE `image_id`=$image_id", array($newTitle));
        $data['img_title'] = $newTitle;
        $data['success'] = 'success';
        echo $this->encodeJSON($data);
    }

    /**
     * Sort images, using jquery sortable stuff...  Expects an array ordered by the
     * new sort order, with values set to the image ID
     */
    public function sortDrag()
    {
        $this->jsonHeader();
        $adminId = (int)$_GET['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_GET['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        $imagesPreview = $_POST['imagesPreview'];

        $msgs = $cart->db->get_text(1, 10);

        //OK... get current images
        $oldImages = imagesOrderItem::getImagesCaptured();

        if (!$oldImages || !$imagesPreview) {
            return $this->_error($msgs[500684]);
        }
        $images_captured = $ids_used = $all_ids = array();
        foreach ($oldImages as $img) {
            $all_ids[(int)$img['id']] = (int)$img['id'];
        }
        foreach ($imagesPreview as $image_id) {
            $image_id = (int)$image_id;
            if (isset($all_ids[$image_id]) && !isset($ids_used[$image_id])) {
                $images_captured[] = array (
                    'type' => 1,
                    'id' => $image_id,
                    );
                $ids_used[$image_id] = $image_id;
            }
        }
        array_unshift($images_captured, '');
        unset($images_captured[0]);

        //make sure there aren't any left over...
        if (count($all_ids) !== count($ids_used)) {
            //there were less items in the list than there is supposed to be...
            return $this->_error($msgs[500684]);
        }
        //save images
        //die ('images: '.print_r($images_captured,1));
        imagesOrderItem::setImagesCaptured($images_captured);

        $data['preview'] = $this->imagesBox();
        $data['msg'] = $msgs['500686'];
        echo $this->encodeJSON($data);
    }

    /**
     * Used when sort number is entered into input
     */
    public function sortInput()
    {
        $this->jsonHeader();
        $adminId = (int)$_GET['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_GET['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        $image_id = (int)$_POST['image_id'];
        $display_order = (int)$_POST['sort'];

        $max = imagesOrderItem::getMaxImages();
        $msgs = $cart->db->get_text(1, 10);

        if (!$image_id || $display_order <= 0 || $display_order > $max) {
            //invalid
            return $this->_error($msgs[500684]);
        }

        //OK... get current images
        $oldImages = imagesOrderItem::getImagesCaptured();

        if (!$oldImages) {
            //hmm, could not get current images captured...
            return $this->_error($msgs[500684]);
        }
        $images_captured = array();
        $image_found = $slot_found = false;
        foreach ($oldImages as $i => $img) {
            if ((int)$img['id'] === $image_id) {
                //record once we come across the one being moved so we know it is
                //actually valid
                $image_found = true;
            }
            if ((int)$i == $display_order) {
                //we display this one here...
                $slot_found = true;
                $images_captured[] = array('type' => 1,'id' => $image_id);
            }
            if ((int)$img['id'] !== $image_id) {
                $images_captured[] = $img;
            }
        }
        if (!$image_found) {
            //oops, never found the one we are inserting...
            return $this->_error($msgs[500685]);
        }
        if (!$slot_found) {
            //We did find the image that is being moved, but did not find the
            //slot to move it to...  It must be in range though, so just assume
            //it is on the end.
            $images_captured[] = array('type' => 1,'id' => $image_id);
        }
        //ok straighten out the indexes...
        array_unshift($images_captured, '');
        unset($images_captured[0]);

        //save images
        imagesOrderItem::setImagesCaptured($images_captured);

        $data['preview'] = $this->imagesBox();
        $data['msg'] = $msgs['500686'];
        echo $this->encodeJSON($data);
    }

    /**
     * Delete an image
     */
    public function delete()
    {
        $this->jsonHeader();
        $adminId = (int)$_GET['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_GET['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        $image_id = (int)$_POST['image_id'];

        $imagesCaptured = imagesOrderItem::getImagesCaptured('cart', true);
        $image_found = $image_slot = false;
        foreach ($imagesCaptured as $slot => $img) {
            if ($img['id'] == $image_id) {
                $image_found = true;
                $image_slot = (int)$slot;
                break;
            }
        }

        if (!$image_found) {
            return $this->_error($this->messages[500681]);
        }
        //delete the image
        $removeResult = imagesOrderItem::removeImage($image_id, $image_slot);
        if (!$removeResult) {
            //problem removing image
            return $this->_error($this->messages[500682]);
        }
        //re-get the images captured
        $imagesCaptured = imagesOrderItem::getImagesCaptured('cart', true);

        //clean things up...  Get rid of any gaps
        $imagesCaptured = array_values($imagesCaptured);
        array_unshift($imagesCaptured, '');
        unset($imagesCaptured[0]);

        //save the new order
        //apply changes to order item
        imagesOrderItem::setImagesCaptured($imagesCaptured);
        //note: don't put in the image slot # if text is blank, as blank text
        //indicates no message to be displayed.
        $data['msg'] = ($this->messages[500683]) ? $this->messages[500683] . $image_slot : '';
        $data['preview'] = $this->imagesBox();
        if (!count($imagesCaptured)) {
            //make sure preview has length to it when there are no images to show
            $data['preview'] .= ' ';
        }

        echo $this->encodeJSON($data);
    }

    /**
     * Rotates an image (default 90 degrees clockwise)
     */
    public function rotate()
    {
        $this->jsonHeader();
        $adminId = (int)$_GET['adminId'];
        if ($adminId) {
            define('IN_ADMIN', 1);
            $_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
        }

        $session = geoSession::getInstance();
        $session->initSession();

        $cart = geoCart::getInstance();
        //start up the cart
        $userId = ($adminId) ? (int)$_GET['userId'] : null;
        $cart->init(true, $userId);

        if (!$this->_validateCartStep()) {
            //invalid it seems?
            return;
        }

        //data to be returned
        $data = array();

        $image_id = (int)$_POST['image_id'];

        //make sure the requested image belongs to this cart
        $imagesCaptured = imagesOrderItem::getImagesCaptured('cart', true);
        $image_found = $image_slot = false;
        foreach ($imagesCaptured as $slot => $img) {
            if ($img['id'] == $image_id) {
                $image_found = true;
                $image_slot = (int)$slot;
                break;
            }
        }
        if (!$image_found) {
            return $this->_error($this->messages[500681]);
        }

        //grab the data about the requested image
        $db = DataAccess::getInstance();
        $sql = "SELECT * FROM " . geoTables::images_urls_table . " WHERE `image_id` = ?";
        $row = $db->GetRow($sql, array($image_id));
        if (!$row) {
            return $this->_error($this->messages[500681]);
        }

        //for extensibility, allow rotating to any angle (for our immediate purposes, 270 degrees CCW is the only choice we really need)
        $degrees = (int)$_POST['degrees'];
        if (!$degrees) {
            $degrees = 270;
        }

        //make a new image and rotate it to the specified angle
        $main = imagerotate(imagecreatefromjpeg($row['file_path'] . $row['full_filename']), $degrees, 0);

        //overwrite the original image with the newly-rotated one
        if (!imagejpeg($main, $row['file_path'] . $row['full_filename'])) {
            //problem removing image
            return $this->_error("File Error");
        }

        if ($row['thumb_filename'] && $row['full_filename'] !== $row['thumb_filename']) {
            //there is a thumbnail. rotate it, too!
            $thumb = imagerotate(imagecreatefromjpeg($row['file_path'] . $row['thumb_filename']), $degrees, 0);
            if (!imagejpeg($thumb, $row['file_path'] . $row['thumb_filename'])) {
                //problem removing image
                return $this->_error("File Error");
            }
        }

        if ($row['image_width'] != $row['image_height']) {
            //image is not square, so swap its dimensions as stored in the db
            if (abs($degrees % 360) == 90 || abs($degrees % 360) == 270) {
                //since it's possible to specify a custom rotation angle, we ACTUALLY only want to do this swap when changing by exactly 90 degrees in either direction
                //working out the new dimensions for some other rotation angle is an exercise left to whomever tries to code that... ;)

                $db->Execute(
                    "UPDATE " . geoTables::images_urls_table . " SET `image_width` = ?, `image_height` = ?, `original_image_width` = ?, `original_image_height` = ? WHERE `image_id` = ?",
                    array($row['image_height'], $row['image_width'], $row['original_image_height'], $row['original_image_width'], $row['image_id'])
                );
            }
        }
        $data['rotation'] = 'complete';
        echo $this->encodeJSON($data);
    }

    /**
     * Get the image preview box to refresh that part in ajax calls
     */
    private function imagesBox()
    {
        $cart = geoCart::getInstance();

        $max = imagesOrderItem::getMaxImages();

        $imagesCaptured = imagesOrderItem::getImagesCaptured('cart', true);
        $tpl_vars = $cart->getCommonTemplateVars();
        $tpl_vars['images']['images_data'] = imagesOrderItem::getImgsData($imagesCaptured, 100, 100);
        $tpl_vars['images']['max'] = $max;
        $cart->site->get_ad_configuration();
        $tpl_vars['images']['maximum_image_description'] = $cart->site->ad_configuration_data->MAXIMUM_IMAGE_DESCRIPTION;
        $imgInfo = imagesOrderItem::getImageData();
        if (geoMaster::is('site_fees') && $imgInfo['cost_per_image'] > 0 && $imgInfo['number_free_images'] < $max) {
            foreach ($tpl_vars['images']['images_data'] as $i => $img) {
                //show price
                if ($i <= $imgInfo['number_free_images']) {
                    $tpl_vars['images']['images_data'][$i]['cost'] = $cart->site->messages[500679];
                } else {
                    $tpl_vars['images']['images_data'][$i]['cost'] = geoString::displayPrice($imgInfo['cost_per_image']);
                }
            }
            $images['pricing'] = $imgInfo;
        } else {
            $images['pricing'] = false;
        }
        $tpl = new geoTemplate('system', 'order_items');
        $tpl->assign($tpl_vars);
        return $tpl->fetch('images/preview.tpl');
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
        $userId = (int)$_GET['userId'];
        $adminId = (int)$_GET['adminId'];

        $session = geoSession::getInstance();

        $sessionUser = $session->getUserId();

        $checkUser = ($adminId) ? $adminId : $userId;

        if ($checkUser && !$sessionUser) {
            //user was logged in, now logged out

            return $this->_error($this->messages[500692]);
        }
        if ($sessionUser != $checkUser) {
            //user different than when started?

            return $this->_error($this->messages[500693]);
        }

        //check to make sure there is an item in there
        if (!$cart->item) {
            //oops, no item in cart, can't go forward.  Not on images step error msg
            return $this->_error($this->messages[500694]);
        }

        //make sure the step is not one of the built in ones
        if ($step !== 'combined' && strpos($step, ':') === false) {
            //They are on a built-in step, not image upload.  Not on images step error msg
            return $this->_error($this->messages[500694]);
        }

        //make sure the order items that are OK to be attached to
        $validItems = geoOrderItem::getParentTypesFor('images');
        $validItems[] = 'images'; //images would be the item if they clicked on edit button in cart.

        if (!in_array($cart->item->getType(), $validItems)) {
            //oops! this isn't a valid order item...  Not on images step error msg
            return $this->_error($this->messages[500694]);
        }

        //got this far, they should be on images step...
        return true;
    }

    /**
     * Internal method to return error for PLUpload format
     * @param int $code
     * @param string $message
     * @return boolean Always returns false
     */
    private function _error($message)
    {
        echo $this->encodeJSON(array ('error' => array('code' => -100, 'message' => $message)));
        return false;
    }
}
