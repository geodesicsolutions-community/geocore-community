<?php

//twitter_feed/CLASSES.ajax.php


// DON'T FORGET THIS
if (class_exists('classes_AJAX') or die()) {
}

class addon_profile_pics_CLASSES_ajax extends classes_AJAX
{


    public function savePic()
    {
        $check = $this->_checkSession();
        if ($check !== true) {
            return $this->_failure($check);
        }

        $pic_data = $_POST['pic_data'];
        $user_id = (int)$_POST['user_id'];

        $db = DataAccess::getInstance();
        $result = $db->Execute("REPLACE INTO `geodesic_addon_profile_pics` (`user_id`,`pic_data`) VALUES (?,?)", array($user_id, $pic_data));
        if (!$result) {
            return $this->_failure("Data Error");
        }

        $return = array(
            'status' => 'ok',
        );

        return $this->encodeJSON($return);
    }


    private function _failure($msg)
    {
        return $this->encodeJSON(array('status' => 'error','message' => $msg));
    }
    private function _checkSession()
    {
        $s = geoSession::getInstance();
        $s->initSession(); //this is ajax, so probably not initialized session yet
        $detected = $s->getUserId();

        if (defined('IN_ADMIN')) {
            if (defined('DEMO_MODE')) {
                return "Profile pics cannot be changed in this admin demo";
            } else {
                //in the admin and NOT on the main demo? Go ahead and let this person change things
                return true;
            }
        }

        $posted = (int)$_POST['user_id'];
        if ($detected == $posted) {
            //changing own profile pic
            return true;
        }
        //otherwise, not allowed
        return "Invalid Access";
    }
}
