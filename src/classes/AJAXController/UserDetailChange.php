<?php

//UserDetailChange.php
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
## ##    7.0.1-18-g337b864
##
##################################

if (class_exists('classes_AJAX') or die('NO_ACCESS')) {
}

class CLASSES_AJAXController_UserDetailChange extends classes_AJAX
{
    public function edit()
    {
        $email = trim($_POST['value']);
        $db = DataAccess::getInstance();
        $session = geoSession::getInstance();

        $messages = $db->get_text(true, 37);

        $session->initSession();

        if ($session->getUserId() <= 0) {
            die('NO ACCESS');
        }

        geoAjax::jsonHeader();

        $data = array();
        $data['email'] = $email;

        if (geoString::isEmail($email) || strlen($email) == 0) {
            $sb = geoSellerBuyer::getInstance();
            $sb->setUserSetting($session->getUserId(), 'paypal_id', $email);

            $data['message'] = $messages[500214];
            if (strlen($email) == 0) {
                //clear the email var so that it doesn't try to put JSON in the empty field
                unset($data['email']);
            }
        } else {
            $data['error'] = $messages[500213];
        }
        echo $this->encodeJSON($data);
    }
}
