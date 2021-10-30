<?php

//UserDetailChange.php


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
