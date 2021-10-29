<?php

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.4.4-9-ga3c3711
##
##################################

class usernameImportItem extends geoImportItem
{
    protected $_name = "Username";
    protected $_description = "Uniquely identifies a user, and is used to log in to the system";
    protected $_fieldGroup = self::USER_LOGIN_FIELDGROUP;

    public $displayOrder = 0;

    //be super-cachey with db queries
    private $prep_checkLogin, $prep_checkConfirm;
    final protected function _cleanValue($value)
    {
        $value = trim($value);
        $value = trim(preg_replace('#\s+#si', ' ', $value));
        $len = strlen($value);

        $db = DataAccess::getInstance();

        if ($len == 0 || $len > $db->get_site_setting('max_user_length') || $len < $db->get_site_setting('min_user_length')) {
            trigger_error('ERROR IMPORT: bad username length: ' . $len);
            return false;
        }
        if (!preg_match('/^[-a-zA-Z0-9_. ]+$/', $value)) {
            trigger_error('ERROR IMPORT: username contains invalid characters: ' . $value);
            return false;
        }

        if (!$this->prep_checkLogin) {
            $this->prep_checkLogin = $db->Prepare("SELECT `id` FROM " . geoTables::logins_table . " WHERE `username` = ?");
        }
        if (!$this->prep_checkConfirm) {
            $this->prep_checkConfirm = $db->Prepare("SELECT `id` FROM " . geoTables::confirm_table . " WHERE `username` = ?");
        }
        $loginResult = $db->GetOne($this->prep_checkLogin, array($value));
        $confirmResult = $db->GetOne($this->prep_checkConfirm, array($value));
        if ($loginResult || $confirmResult) {
            trigger_error('ERROR IMPORT: username already exists: ' . $value);
            return false;
        }

        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        $db = DataAccess::getInstance();
        geoImport::$tableChanges['userdata']['username'] = " `username` = '{$value}' ";
        geoImport::$tableChanges['logins']['username'] = " `username` = '{$value}' ";
        geoImport::$crosstalk['username'] = $value;
        return true;
    }
}
