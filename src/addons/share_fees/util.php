<?php

# Share Fees Addon

require_once ADDON_DIR . 'share_fees/info.php';

class addon_share_fees_util
{

    /**
     * Used internally
     * @internal
     */
    private static $_instance;
    public $attachment_info = 0;
    public $active = 0;
    public $required = 0;
    public $attaching_user_group = 0;
    public $attached_to_user_group = 0;
    public $attachment_type = 0;
    public $post_login_redirect = 0;
    public $store_category_display = 0;
    public $use_attached_messages = 0;
    private $user_share_fees_attachment_table = 'geodesic_addon_share_fees_attachments';
    private $user_share_fees_configuration_table = 'geodesic_addon_share_fees_settings';

    /**
     * Setup basic data for attachment type passed in
     *
     * @return boolean false if type doesn't exist| returns true if setup properly
     * @since Version 7.4.5
     */

    public function __construct()
    {
        $this->getInfo();
        $this->active = (int)$this->attachment_info['active'];
        $this->required = (int)$this->attachment_info['required'];
        $this->attached_to_user_group = (int)$this->attachment_info['attached_to_user_group'];
        $this->attaching_user_group = (int)$this->attachment_info['attaching_user_group'];
        $this->post_login_redirect = (int)$this->attachment_info['post_login_redirect'];
        $this->store_category_display = (int)$this->attachment_info['store_category_display'];
        $this->use_attached_messages = (int)$this->attachment_info['use_attached_messages'];
        return true;
    }

    /**
     * Gets an instance of the addon_share_fees_util class.
     * @return addon_share_fees_util
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            $c = __class__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    public function core_registration_check_info($vars)
    {
        $info = $vars['info'];
        $registerClass = $vars['this'];

        $this->getInfo();

        if ($this->attachment_info->active) {
            //is attachment set
            if ($registerClass->registered_variables["user_attachment_id"]) {
                //if attachment check valid user attached to and that registrant can be attached to attached user
                if (!$this->checkUserToAttachmentRegistration($registerClass->registration_group, $registerClass->registered_variables["user_attachment_id"])) {
                    $registerClass->error_found++;
                    $registerClass->error['feeshareattachment'] = 'error';
                }
            } elseif ($feeShareAttachment->required) {
                //there is no attachment and this attachment is required
                $registerClass->error_found++;
                $registerClass->error['feeshareattachment'] = 'required';
            }
        }
    }

    public static function auction_final_feesOrderItem_cron_close_listings($vars)
    {
        $cron = geoCron::getInstance();
        $listing = $vars['listing'];
        $order = $vars['order'];
        //echo "<p>";
        //var_dump($order);
        //echo "</p>";
        if ($this->active) {
                //check if this seller is attached so that paid_out_to can be set
            $paid_out_to = $this->getUserAttachedTo($listing->seller);
            if ($paid_out_to != 0) {
                //set paid_out_to in the order item
                $item->setPaidOutTo($paid_out_to);
            }
        }

        $user_id = $order->getBuyer();
        if (!$user_id) {
            //something wrong with user?
            return;
        }
        $user = geoUser::getUser($user_id);
        if (!$user) {
            //something wrong with user?
            return;
        }
        if ($user->balance_freeze > 0) {
            //Account FROZEN!!!  Do not let them pay using account balance!!!
            return;
        }
        if (!$order->getInvoice()) {
            //error getting invoice, can't go without an invoice
            return;
        }
        //$total = $order->getInvoice()->getInvoiceTotal();
        //$newBalance = ($total + $user->account_balance);
        //if (($newBalance) < 0 && $user->account_balance < 0 && $user->date_balance_negative > 0 && ((geoUtil::time() - $user->date_balance_negative) > ($gateway->get('negative_time') * 86400))){
            //NOT using balance, they have been negative too long
        //  return;
        //}
        //if ($gateway->get('allow_negative') || ($newBalance) >= 0) {
        //  if ($newBalance < 0 && abs($newBalance) > $gateway->get('negative_max')){
                //at the max amount allowed for negative, don't charge to account balance.
        //      return;
        //  }
            //if it gets here, then all the checks are go, so charge the final fees
            //to the account balance
        //  self::_processOrder($order);
        //}
    }


    /**
     * Get the configuration values for the specified attachment type id in an array.  Called in construct and populates attachment vars for current attachment type passed to constructor
     *
     * @return boolean|array false if type doesn't exist|Returns an array with all the attachment type info
     * @since Version 7.4.5
     */
    private function getInfo()
    {
        if ($this->attachment_info == 0) {
            $db = DataAccess::getInstance();
            $row = $db->GetRow("SELECT * FROM " . $this->user_share_fees_configuration_table, array());
            $this->attachment_info = $row;
            $this->active = $this->attachment_info['active'];
        }
        return;
    }

    /**
     * Returns array of user id and username or storefront name (if set) that can be attached to for choices in registration and user information edit
     *
     * @return array user id,user display
     * @since Version 7.4.6
     */
    public function attachableUsers()
    {
        //initially this is only for attachment type 1 which only allows attachment to one user groups users

        $attachable_users = array();

        //get the user group(s) that can be attached to for this attachment type
        //TODO: create options for those with and without a storefront
        if ($this->attached_to_user_group != 0) {
            //select array of user ids from db attached to this user group
            $db = DataAccess::getInstance();
            $sql = "SELECT
					" . geoTables::user_groups_price_plans_table . ".id,
					" . geoTables::userdata_table . ".username,
					geodesic_addon_storefront_user_settings.storefront_name

				FROM
					" . geoTables::user_groups_price_plans_table . "
				INNER JOIN " . geoTables::userdata_table . " ON " . geoTables::user_groups_price_plans_table . ".id = " . geoTables::userdata_table . ".id
				LEFT JOIN geodesic_addon_storefront_user_settings ON " . geoTables::userdata_table . ".id = geodesic_addon_storefront_user_settings.owner

				WHERE
				 	" . geoTables::user_groups_price_plans_table . ".group_id = ?";

            $attachable_users = $db->Execute($sql, array($this->attached_to_user_group));
            //now we have an array with user id, username and storefront name
            //need to combine so that storefront name where there is one otherwise use username
            //reset($attachable_users);
            $usable_attachable_users = array();
            foreach ($attachable_users as $value) {
                if (($value['storefront_name'] == null) || (strlen(trim($value['storefront_name'])) == 0)) {
                    $usable_attachable_users[$value['id']] = $value['username'];
                } else {
                    $usable_attachable_users[$value['id']] = $value['storefront_name'];
                }
            }
            asort($usable_attachable_users); //return array sorted by storefront name/username
            return $usable_attachable_users;
        } else {
            //no user group configured to attach to
            return false;
        }
    }
    /**
     * Accepts the user id to see if the user can use the attachment type based on their user group
     *
     * @param int $attaching_user_id
     * @param int $user_attached_to_id
     * @return boolean false if attaching user cannot attach or user attached to | boolean true if user can attach to attached to user
     * @since Version 7.4.6
     */
    public function checkUserToAttachment($attaching_user_id = 0, $user_attached_to_id = 0)
    {
        if (($attaching_user_id == 0) || ($user_attached_to_id == 0)) {
            return false;
        }
        $db = DataAccess::getInstance();
        //get the attaching user's user group
        $attaching_user_group_id = (int)$db->GetOne("SELECT group_id FROM " . geoTables::user_groups_price_plans_table . " where id = ?", array($attaching_user_id));
        if ($attaching_user_group_id) {
            if ($this->attaching_user_group != $attaching_user_group_id) {
                return false;
            }
        } else {
            //no user group found
            return false;
        }

        //get the attached to user's user group
        $user_attached_to_group_id  = (int)$db->GetOne("SELECT group_id FROM " . geoTables::user_groups_price_plans_table . " where id = ?", array($user_attached_to_id));
        if ($user_attached_to_group_id) {
            if ($this->attached_to_user_group != $user_attached_to_group_id) {
                return false;
            }
        } else {
            //no user group found
            return false;
        }
        //everythings good the attaching user group CAN attach to the attaching to user group
        return true;
    }

    /**
     * Accepts the username and checks the username and storefront names in the system against that.  Used in registration to get the user id to attach to by default
     *
     * @param int $username
     * @return boolean false if no user found in the system and the user id if there is one
     * @since Version 7.4.6
     */

    public function getIdByUsername($username = '')
    {
        if ($username == '') {
            return false;
        }
        //check the username/storefrontname
        //urldecode value passed
        $db = DataAccess::getInstance();
        //check to see if storefront in use.  If not just check geodesic_userdata
        $util = geoAddon::getUtil("storefront");
        if ($util) {
            $sql = "SELECT geodesic_userdata.id
					FROM
					geodesic_userdata left join geodesic_addon_storefront_user_settings
					ON
					geodesic_userdata.id = geodesic_addon_storefront_user_settings.owner WHERE geodesic_userdata.username = ? OR geodesic_addon_storefront_user_settings.storefront_name = ?";
            //echo $sql."<br>getting username/storefront for: ".$username."<bR>\n";
            $id_for_passed_username = $db->GetRow($sql, array($username,$username));
            if ($id_for_passed_username) {
                return $id_for_passed_username['id'];
            } else {
                //no match in the database
                return false;
            }
        } else {
            //storefront not in use.  Just check geodesic_userdata only
            $sql = "SELECT geodesic_userdata.id
					FROM
					geodesic_userdata WHERE geodesic_userdata.username = ?";
            //echo $sql."<br>getting username for: ".$username."<bR>\n";
            $id_for_passed_username = $db->GetRow($sql, array($username));
            if ($id_for_passed_username) {
                return $id_for_passed_username['id'];
            } else {
                //no match in the database
                return false;
            }
        }
    }

    /**
     * Accepts the user group id and the id of the user attached to.   and checks the username and storefront names in the system against that.  Used in registration to get the user id to attach to by default
     *
     * @param int $attaching_user_group_id
     * @param int $user_attached_to_id
     * @return boolean true if user group id passed in matches the user group id of the attached to user passed in also....otherwise returns false
     * @since Version 7.4.6
     */
    public function checkUserToAttachmentRegistration($attaching_user_group_id = 0, $user_attached_to_id = 0)
    {
        if (($attaching_user_group_id == 0) || ($user_attached_to_id == 0)) {
            return false;
        }

        //get the attaching user group can attach
        if ($this->attaching_user_group != $attaching_user_group_id) {
            return false;
        }

        //get the attached to user's user group
        $db = DataAccess::getInstance();
        $user_attached_to_group_id  = (int)$db->GetOne("SELECT group_id FROM " . geoTables::user_groups_price_plans_table . " where id = ?", array($user_attached_to_id));
        //echo "SELECT group_id FROM ".geoTables::user_groups_price_plans_table." where id = ".$user_attached_to_id."<br>\n";
        if ($user_attached_to_group_id) {
            if ($this->attached_to_user_group != $user_attached_to_group_id) {
                return false;
            }
        } else {
            //no user group found
            return false;
        }
        //everythings good.  user attaching and attached to are in the correct user groups
        return true;
    }

    /**
     * Accepts the user id and checks if that user id can be attached to
     *
     * @param int $user_attached_to_id
     * @return boolean true if user id passed in can be attached to (is in the correct user group)
     * @since Version 7.4.6
     */
    //checks the attached user to see if they can be attached to
    public function checkAttachableUser($user_attached_to_id = 0)
    {
        if (($user_attached_to_id == 0) || ($this->attached_to_user_group == 0)) {
            return false;
        }

        //get the attached to user's user group
        $db = DataAccess::getInstance();
        $user_attached_to_group_id  = (int)$db->GetOne("SELECT group_id FROM " . geoTables::user_groups_price_plans_table . " where id = ?", array($user_attached_to_id));
        if ($user_attached_to_group_id) {
            if ($this->attached_to_user_group != $user_attached_to_group_id) {
                return false;
            }
        } else {
            //no user group found
            return false;
        }
        //everythings good.  user attaching and attached to are in the correct user groups
        return true;
    }


    /**
     * Accepts user id and attachment type id to see if there is an attachment or not for that user of that type.
     *
     * @param int $user_id
     * @param int $attachment_type_to_check
     * @return boolean false if attachment type of this type does not exist for this user or if attachment exists the id of the attached to user
     * @since Version 7.4.6
     */

    public function getUserAttachedTo($user_id = 0)
    {
        if ($user_id == 0) {
            return 0;
        }

        $db = DataAccess::getInstance();
        //get the users user group
        $sql = "SELECT attached_to FROM " . $this->user_share_fees_attachment_table . " where attached_user = ?";
        //echo $sql."<br>getting attachment for: ".$user_id."<br>\n";
        $user_attached_to_id = $db->GetOne($sql, array($user_id));
        if (($user_attached_to_id) && ($user_attached_to_id != 0)) {
            return (int)$user_attached_to_id;
        } else {
            //no attachment
            return 0;
        }
    }

    /**
     * Accepts user id and attachment type id to remove that users attachment types from the database.
     *
     * @param int $user_id
     * @param int $attachment_type_to_check (if 0 then use the class attachment_type_id
     * @return boolean false if attachment type of this type does not exist for this user or if attachment exists the id of the attached to user
     * @since Version 7.4.6
     */

    public function removeUserAttachedTo($user_id = 0)
    {
        if ($user_id == 0) {
            return false;
        }
        if ($attachment_type_to_check == 0) {
            if ($this->attachment_type == 0) {
                return false;
            }
            //use attachment of class at construction
            $attachment_type_to_check = $this->attachment_type;
        } else {
            //use the attachment type passed
        }
        $db = DataAccess::getInstance();
        //get the users user group
        $remove_attachment = $db->Execute("DELETE FROM " . $this->user_share_fees_attachment_table . " where attached_user = ?", array($user_id));
        if ($remove_attachment) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Accepts user id and attachment type id to remove that users attachment types from the database.
     *
     * @param int $user_id
     * @return boolean false if attachment type of this type does not exist for this user or if attachment exists the id of the attached to user
     * @since Version 7.4.6
     */

    public function displayNameAttachedUser($user_id = 0)
    {
        if ($user_id == 0) {
            return false;
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT geodesic_userdata.username, geodesic_addon_storefront_user_settings.storefront_name
				FROM
				geodesic_userdata left join geodesic_addon_storefront_user_settings
				ON
				geodesic_userdata.id = geodesic_addon_storefront_user_settings.owner WHERE geodesic_userdata.id = ?";
        //echo $sql."<br>getting username/storefront for: ".$user_id."<bR>\n";
        $user_attached_to_id = $db->GetRow($sql, array($user_id));
        if ($user_attached_to_id) {
            if (($user_attached_to_id['storefront_name'] == null) || (strlen(trim($user_attached_to_id['storefront_name'])) == 0)) {
                return $user_attached_to_id['username'];
            } else {
                return $user_attached_to_id['storefront_name'];
            }
        } else {
            //no attachment
            return false;
        }
    }

    /**
     * Accepts the attached_to_user_id to check if that user has a current storefront subscription
     *
     * @param int $attached_to_user_id
     * @return boolean true if user passed has a current storefront subscription and false if not
     * @since Version 7.4.6
     */

    public function checkStorefrontUse($attached_to_user_id = 0)
    {
        if ($attached_to_user_id == 0) {
            return false;
        }

        $util = geoAddon::getUtil("storefront");
        if (!$util) {
            //another failsafe
            return false;
        }
        if ($util->userHasCurrentSubscription($attached_to_user_id) == 0) {
            //no subscription for seller
            return false;
        } else {
            //user has a subscription so
            return true;
        }
    }

    /**
     * Accepts the attaching user id and the attached to user id and inserts into database
     *
     * @param int $attaching_user_id
     * @param int $user_attached_to_id
     * @return boolean false if user attachment not inserted | boolean true if user attachment is inserted
     * @since Version 7.4.6
     */
    public function insertUserAttachment($attaching_user_id = 0, $user_attached_to_id = 0)
    {
        if (($attaching_user_id == 0) || ($user_attached_to_id == 0)) {
            return false;
        }
        $db = DataAccess::getInstance();
        //clear any current attachment as only one is allowed
        $sql = "DELETE FROM " . $this->user_share_fees_attachment_table . " WHERE attached_user = ?";
        $delete_user_attachment = $db->Execute($sql, array($attaching_user_id));
        if (!$delete_user_attachment) {
            return false;
        }

        $sql = "INSERT INTO " . $this->user_share_fees_attachment_table . "
				(attached_user, attached_to)
				VALUES
				( ?, ?)";
        $insert_shared_fee_attachment = $db->Execute($sql, array($attaching_user_id,$user_attached_to_id));
        if (!$insert_shared_fee_attachment) {
            return false;
        }

        //everythings good.  attachment inserted
        return true;
    }

    /**
     * Gets a list of user id's that are attached to a specific "attached to user" given a specific type of attachment
     *
     * @param int $user_attached_to_id
     * @param int $attachment_type
     * @return array user id's attached to user passed in given attachment type
     * @since Version 7.4.6
     */
    public function getUsersAttachedToUser($user_attached_to = 0)
    {
        if (($user_attached_to == 0) && (is_int($user_attached_to))) {
            return 0;
        }
        $db = DataAccess::getInstance();

        $sql = "SELECT `attached_user` FROM " . $this->user_share_fees_attachment_table . " WHERE `attached_to` = ? ";
        $attached_users_result = $db->Execute($sql, array($user_attached_to));
        $attached_users_array = array();
        if (!$attached_users_result) {
            return $attached_users_array;
        } elseif ($attached_users_result->RecordCount() > 0) {
            $i = 0;

            while ($attached_user = $attached_users_result->FetchRow()) {
                array_push($attached_users_array, $attached_user['attached_user']);
            }
            return $attached_users_array;
        } else {
            //no attached users
            return 0;
        }
    }

    /**
     * Accepts the user id passed and returns whether that user is attached to or does the attaching
     *
     * @param int $user_id
     * @return int 1 for a user that's attached to (part of user group attached to), 2 for user that does attaching (not part of user group attached to...all other user groups) or false if no user group found for user passed in
     * @since Version 7.4.6
     */
    public function checkAttachedorAttaching($user_id = 0)
    {
        if ($user_id == 0) {
            return false;
        }
        //check to see if the user id supplied can be attached to or does the attaching
        //should return 1 for attached to user and 2 for attaching user

        //get user group of current user
        $db = DataAccess::getInstance();
        $users_group_id  = (int)$db->GetOne("SELECT group_id FROM " . geoTables::user_groups_price_plans_table . " where id = ?", array($user_id));
        if ($users_group_id) {
            if ($this->attached_to_user_group != $users_group_id) {
                //this user does the attaching
                return 2;
            } else {
                //this user is attached to
                return 1;
            }
        } else {
            //no user group found
            return false;
        }
    }

    /**
     * Returns the message from that attached to user to the users attached to them
     *
     * @param int $attached_user_id
     * @return text message
     * @since Version 7.4.6
     */
    public function GetAttachedMessage($attached_user_id = 0)
    {
        if ($attached_user_id == 0) {
            return;
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT `attached_user_message` FROM " . geoTables::userdata_table . " where id = ?";
        $attached_message  = $db->GetRow($sql, array($attached_user_id));
        if ($attached_message) {
            return $attached_message['attached_user_message'];
        } else {
            return '';
        }
    }

    /**
     * Saves the passed user and saves the message they display to their attached users
     *
     * @param int $attached_user_id
     * @param text $message
     * @return boolean true if saved with no errors....false otherwise
     * @since Version 7.4.6
     */
    public function SaveAttachedMessage($attached_user_id = 0, $message = '')
    {
        if ($attached_user_id == 0) {
            return;
        }
        $db = DataAccess::getInstance();
        $saved_message  = $db->Execute("update " . geoTables::userdata_table . " SET `attached_user_message` = ? where id = ?", array($message, $attached_user_id));
        if ($saved_message) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the storefront category ids for the given store so it can be placed into an sql query in statement.  Allows to select all listings attached to storefront
     *
     * @param int $attached_user_id
     * @param int $storefront_category_table - passed in from storefront addon
     * @return text in statement placed directly into sql query true if saved with no errors....false otherwise
     * @since Version 7.4.6
     */
    public function getStoreCategoryInStatement($storeowner = 0, $storefront_category_table = '')
    {
        if (($storeowner == 0) || (strlen(trim($storefront_category_table)) == 0)) {
            return false;
        }
        $db = DataAccess::getInstance();

        $sql = "SELECT * FROM " . $storefront_category_table . " WHERE owner = ? ";
        $all_store_categories = $db->Execute($sql, array($storeowner));
        if (!$all_store_categories) {
            return '';
        } else {
            $store_in_statement = " IN (";
            $number_of_categories = 0;
            while ($categories = $all_store_categories->FetchRow()) {
                //build instatement to select listings attached to this storefront
                if ($number_of_categories == 0) {
                    $store_in_statement .= $categories['category_id'];
                } else {
                    $store_in_statement .= "," . $categories['category_id'];
                }
                $number_of_categories++;
            }
            $store_in_statement .= ")";
            return ($number_of_categories != 0 ? $store_in_statement : '' );
        }
    }

    /**
     * Returns the types of fees that are set to be shared by the admin....mainly for future as there is only auction final fees shared to begin with
     *
     * @return array contains all fee types set to be shared
     * @since Version 7.4.6
     */
    public function getFeeTypesShared()
    {
        $db = DataAccess::getInstance();

        $fee_types_list = $db->GetRow("SELECT fee_types_shared FROM " . $this->user_share_fees_configuration_table, array());
        if (!$fee_types_list) {
            return false;
        } else {
            $fee_types_array = explode(",", $fee_types_list['fee_types_shared']);
            return $fee_types_array;
        }
    }
}
