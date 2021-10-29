<?php

//user_management_communications.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.02.0-6-g8fe9772
##
##################################

class User_management_communications extends geoSite
{
    var $debug_comm = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function list_communications()
    {
        if (!$this->userid) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        $this->page_id = 24;
        $this->get_text();
        $db = DataAccess::getInstance();
        $tpl_vars = array();
        $tpl_vars['helpLink'] = $this->display_help_link(389);


        $sql = "SELECT * FROM " . geoTables::user_communications_table . " WHERE (`message_to` = ? AND `receiver_deleted` = 0) OR (`message_from` = ? AND `sender_deleted` = 0) ORDER BY `date_sent` DESC";
        $result = $db->Execute($sql, array($this->userid,$this->userid));
        if (!$result) {
            $this->error_message = $this->internal_error_message;
            return false;
        } elseif ($result->RecordCount() > 0) {
            $tpl_vars['showCommunications'] = true;

            $communications = array();
            while ($data = $result->FetchRow()) {
                if ($data['message_from']) {
                    $sender = geoUser::userName($data['message_from']);
                } else {
                    $sender = $data['message_from_non_user'];
                }
                $sender = ($sender) ? $sender : '--';

                $message = array();
                $message['sender'] = $sender;
                $message['receiver_id'] = $data['message_to'];
                $message['receiver'] = geoUser::userName($data['message_to']);
                $message['sender_id'] = $data['message_from'] ? $data['message_from'] : false;
                $message['read'] = $data['read'];
                $message['listingTitle'] = geoListing::getTitle($data['regarding_ad']);
                $message['listingId'] = $data['regarding_ad'];
                $message['dateSent'] = date($this->configuration_data['entry_date_configuration'], $data['date_sent']);
                $message['viewLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=8&amp;c=1&amp;d=" . $data['message_id'];
                $message['deleteLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=8&amp;c=2&amp;d=" . $data['message_id'];

                if ($message['sender_id'] == $this->userid && $message['receiver_id'] != $this->userid) {
                    //this message was sent by this user (but not to himself)
                    $communications['sent'][] = $message;
                } else {
                    $communications['received'][] = $message;
                }
            }
            $tpl_vars['communications'] = $communications;
        } else {
            $tpl_vars['showCommunications'] = false;
        }

        $tpl_vars['commConfigLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=7";
        $tpl_vars['userManagementHomeLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4";
        geoView::getInstance()->setBodyTpl('communications/list_communications.tpl', '', 'user_management')
        ->setBodyVar($tpl_vars);
        $this->display_page($db);
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function view_this_communication($db, $communication_id = 0)
    {
        trigger_error('DEBUG MESSAGE: Top of view_this_communication()');
        $this->page_id = 25;
        $this->get_text();
        $db = DataAccess::getInstance();

        if (!$this->userid) {
            return false;
        }
        if (!$communication_id) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        $this->sql_query = "select * from " . geoTables::user_communications_table . " where message_id = " . $communication_id;
        $result = $db->Execute($this->sql_query);
        if (!$result) {
            $this->error_message = $this->internal_error_message;
            return false;
        } elseif ($result->RecordCount() != 1) {
            //wrong return count
            $this->error_message = $this->internal_error_message;
            return false;
        }

        $show = $result->FetchNextObject();

        if (!in_array($this->userid, array($show->MESSAGE_TO, $show->MESSAGE_FROM))) {
            //what kind of message goes best with cheese? Nacho Message!
            //(not your message, get it? haha...)
            $this->error_message = $this->data_missing_error_message;
            return false;
        }
        if ($show->MESSAGE_FROM) {
            $sender = geoUser::userName($show->MESSAGE_FROM);
        } else {
            $sender = $show->MESSAGE_FROM_NON_USER;
        }
        $sender = ($sender) ? $sender : '--';

        $tpl_vars['sender'] = $sender;
        $tpl_vars['sender_id'] = $show->MESSAGE_FROM ? $show->MESSAGE_FROM : false;
        $tpl_vars['formTarget'] = $this->configuration_data['classifieds_file_name'] . "?a=3&amp;b=reply";
        $tpl_vars['dateSent'] = date($this->configuration_data['entry_date_configuration'], $show->DATE_SENT);
        $tpl_vars['listingTitle'] = geoListing::getTitle($show->REGARDING_AD);
        $tpl_vars['message'] = str_replace("\n", "<br />", geoString::fromDB($show->MESSAGE));
        $tpl_vars['userManagementHomeLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4";
        $tpl_vars['canReply'] = true;

        if ($sender != '--') {
            $tpl_vars['comm_id'] = $communication_id;
            $newMessage['to'] = $show->MESSAGE_FROM;
            $newMessage['from'] = $this->userid;
            $newMessage['about'] = $show->REGARDING_AD;
            $tpl_vars['newMessage'] = $newMessage;
            $tpl_vars['isPublicQuestion'] = ($show->PUBLIC_QUESTION == 1) ? true : false;
        } else {
            $tpl_vars['canReply'] = false;
        }


        if ($this->userid == $show->MESSAGE_FROM) {
            //this is a message-sender viewing a "sent" message
            $tpl_vars['canReply'] = false;
        }

        if ($this->userid == $show->MESSAGE_TO) {
            //this is the person to whom this message was sent (MAY also be the sender, if he sent it to himself)
            if ($show->READ != 1) {
                //mark message as read
                $sql = "UPDATE " . geoTables::user_communications_table . " SET `read` = '1' WHERE `message_id` = '" . $communication_id . "'";
                $result = $db->Execute($sql);
            }
        }


        geoView::getInstance()->setBodyTpl('communications/view_communication.tpl', '', 'user_management')
            ->setBodyVar($tpl_vars);
        $this->display_page($db);
        return true;
    } //end of function view_this_communication

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_this_communication($db = null, $communication_id)
    {
        if (!$this->userid || !$communication_id) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        $is_admin = ($this->userid == 1  || geoAddon::triggerDisplay('auth_listing_delete', null, geoAddon::NOT_NULL)) ? true : false;

        $db = DataAccess::getInstance();

        if ($_REQUEST['public'] == 1 && $is_admin) {
            //remove this communication from being shown publicly, but not from the system altogether
            $sql = "UPDATE " . geoTables::user_communications_table . " SET `public_question` = 0 WHERE `message_id` = ?";
            $result = $db->Execute($sql, array($communication_id));
        } else {
            //this is one party to the message asking to delete it, but may still need to show it to the other party

            //first, gather info on the message
            $sql = "SELECT * FROM " . geoTables::user_communications_table . " WHERE `message_id` = ?";
            $message = $db->GetRow($sql, array($communication_id));

            if (!in_array($this->userid, array($message['message_from'], $message['message_to']))) {
                //this user is not a party to this message. Show an error and end.
                $this->error_message = $this->internal_error_message;
                return false;
            }

            if ($this->userid == $message['message_from']) {
                //this user is the message sender
                $sql = "UPDATE " . geoTables::user_communications_table . " SET `sender_deleted` = 1 WHERE `message_id` = ? AND `message_from` = ?";
                if (!$db->Execute($sql, array($communication_id, $this->userid))) {
                    $this->error_message = $this->internal_error_message;
                    return false;
                }
            }

            if ($this->userid == $message['message_to']) {
                //this user is the message receiver (NOTE: may also be the sender -- it IS possible to send a message to oneself)
                $sql = "UPDATE " . geoTables::user_communications_table . " SET `receiver_deleted` = 1 WHERE `message_id` = ? AND `message_to` = ?";
                if (!$db->Execute($sql, array($communication_id, $this->userid))) {
                    $this->error_message = $this->internal_error_message;
                    return false;
                }
            }

            //now, look again to see if BOTH the sender and receiver deletions are checked -- if so, go ahead and completely remove the message
            //also completely delete if the message is from a non-user (message_from==0) and this is the receiver doing a delete
            $sql = "SELECT `sender_deleted`, `receiver_deleted` FROM " . geoTables::user_communications_table . " WHERE `message_id` = ?";
            $delChk = $db->GetRow($sql, array($communication_id));
            if ($delChk['sender_deleted'] == 1 && $delChk['receiver_deleted'] == 1 || ($message['message_from'] == 0 && $delChk['receiver_deleted'] == 1)) {
                $sql = "DELETE FROM " . geoTables::user_communications_table . " WHERE `message_id` = ?";
                if (!$db->Execute($sql, array($communication_id))) {
                    $this->error_message = $this->internal_error_message;
                    return false;
                }
            }
        }

        return true;
    } //end of function delete_this_communication

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function communication_success()
    {
        $this->page_id = 45;
        $this->get_text();
        $tpl_vars = array();

        $tpl_vars['uid'] = $this->userid;
        $tpl_vars['userManagementHomeLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4";
        geoView::getInstance()->setBodyTpl('communications/communication_success.tpl', '', 'user_management')
            ->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function send_communication_form($to = 0, $classified_id = 0, $affiliate_id = 0)
    {
        if (!$to) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }
        $to_data = $this->get_user_data($to);
        if (!$to_data) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        $sender = geoSession::getInstance()->getUserId();

        $db = DataAccess::getInstance();
        $this->page_id = 45;
        $this->get_text();
        $tpl_vars = array();

        //make sure this contact is between the appropriate users (prevent spam)
        $sql = 'SELECT `message_id` FROM ' . geoTables::user_communications_table . ' WHERE (`message_to` = ? AND `message_from` = ?)';
        $contact = $db->Execute($sql, array($sender, $to));
        if (!$classified_id) {
            $tpl_vars['error'] = $this->messages[500748];
        } elseif ($contact->RecordCount() < 1) {
            //since a user in the system check message_from_non_user for this user's email address.
            //User may not have been logged in when initially contacting seller.  Not an issue for sites that force login to contact seller.
            $sql = 'SELECT `message_id` FROM ' . geoTables::user_communications_table . ' WHERE (`message_to` = ? AND `message_from_non_user` = ?) order by message_id desc limit 1 ';
            $from_data = $this->get_user_data($this->userid);
            $alt_contact = $db->Execute($sql, array($to, $from_data->EMAIL));
            if ($alt_contact->RecordCount() < 1) {
                //double checked - no messages from target to sender -- nothing to reply to!
                $tpl_vars['error'] = $this->messages[500748];
            }
        }

        if ($affiliate_id) {
            $tpl_vars['formTarget'] = $this->configuration_data['affiliate_url'] . "?a=3&amp;b=" . $to;
        } else {
            $tpl_vars['formTarget'] = $this->configuration_data['classifieds_url'] . "?a=3&amp;b=" . $to;
        }

        //message to
        if ($to_data->COMMUNICATION_TYPE == 1) {
            $tpl_vars['messageTo'] = $to_data->EMAIL;
        } else {
            $tpl_vars['messageTo'] = $to_data->USERNAME;
        }
        if ($this->userid == $to) {
            $tpl_vars['toMe'] = true;
        }

        //message from
        if ($this->userid) {
            $from_data = $this->get_user_data($this->userid);
            if ($from_data) {
                $tpl_vars['fromKnown'] = true;
                if ($from_data->COMMUNICATION_TYPE == 1) {
                    $tpl_vars['messageFrom'] = $from_data->EMAIL;
                } else {
                    $tpl_vars['messageFrom'] = $from_data->USERNAME;
                }
            }
        }

        if ($classified_id) {
            $tpl_vars['listingTitle'] = geoListing::getTitle($classified_id);
            $tpl_vars['classified_id'] = $classified_id;
        }

        $tpl_vars['userManagementHomeLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4";
        geoView::getInstance()->setBodyTpl('communications/send_communication_form.tpl', '', 'user_management')
            ->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    } //end of function send_communication_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function communications_configuration()
    {
        if (!$this->userid) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        $db = DataAccess::getInstance();
        $this->page_id = 26;
        $this->get_text();
        $tpl_vars = array();

        $sql = "select communication_type from " . $this->userdata_table . " where id = " . $this->userid;
        $commType = $db->GetOne($sql);

        $tpl_vars['formTarget'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=7&amp;z=1";
        $tpl_vars['helpLink'] = $this->display_help_link(1400);
        $tpl_vars['communicationType'] = $commType;
        $tpl_vars['userManagementHomeLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4";

        geoView::getInstance()->setBodyTpl('communications/configuration_form.tpl', '', 'user_management')
            ->setBodyVar($tpl_vars);
        $this->display_page($db);
        return true;
    }



//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_communication_configuration($configuration_information = 0)
    {

        if (!$this->userid || !$configuration_information) {
            //data missing
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        //update the communication configuration
        $db = DataAccess::getInstance();
        $newType = (int)$configuration_information["communication_type"];
        $sql = "update " . $this->userdata_table . " set communication_type = ? where id = ?";
        $result = $db->Execute($sql, array($newType, $this->userid));
        if (!$result) {
            $this->error_message = $this->internal_error_message;
            return false;
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
}
