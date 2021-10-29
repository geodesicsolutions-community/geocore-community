<?php

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################

class passwordImportItem extends geoImportItem
{
    protected $_name = "Password";
    protected $_description = "Secret phrase used to log in to the system";
    protected $_fieldGroup = self::USER_LOGIN_FIELDGROUP;

    public $requires = 'username'; //username is used in the creation of hashed passwords, and so must come first
    public $displayOrder = 1; //sort to behind username

    final protected function _cleanValue($value)
    {
        $value = trim($value);
        $len = strlen($value);

        $user = geoImport::$crosstalk['username'];
        //TODO: implement a method (special-case?) to force this to be AFTER username when being set up
        if (!$user) {
            trigger_error('ERROR IMPORT: missing username. cannot config password');
            return false;
        }

        $db = DataAccess::getInstance();

        if ($len == 0 || $len > $db->get_site_setting('max_pass_length') || $len < $db->get_site_setting('min_pass_length')) {
            trigger_error('ERROR IMPORT: bad password length: ' . $len);
            return false;
        }

        //do hashing if needed (get_hashed_password() will return plaintext password if no hashing is done)
        $hashed_pass = geoPC::getInstance()->get_hashed_password($user, $value, $db->get_site_setting('client_pass_hash'));
        return $hashed_pass;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['logins']['password'] = " `password` = '{$value}' ";
        $hash_type = DataAccess::getInstance()->get_site_setting('client_pass_hash');
        geoImport::$tableChanges['logins']['hash_type'] = " `hash_type` = '{$hash_type}' ";
        return true;
    }
}
