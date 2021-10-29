<?php
//addons/share_fees/admin.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2014 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-102-g925bc56
##
##################################

# Share Fees Addon

require_once ADDON_DIR . 'share_fees/info.php';

class addon_share_fees_admin extends addon_share_fees_info {
	
	private $_allAttachmentTypes = array();
	private $_sharedFeeData = array();
	private $types_shared = array("auction_final_fees");
	private $types_shared_labels = array("auction final fee");
	private $encapsulate = true;
	
	public function init_pages () {
		
		menu_page::addonAddPage('shared_fee_edit','','Settings',$this->name,'fa-share-alt-square');
		menu_page::addonAddPage('shared_fee_payments','','Payments Due',$this->name,'fa-share-alt-square');
		menu_page::addonAddPage('export_shared_fee_payments_due','','Export Payments Due',$this->name,'fa-share-alt-square');
			
	}	
	
	public function init_text($language_id)
	{
		$return = array (
				'share_fees_registration_choice_label' => array (
						'section' => 'Registration Form',
			 			'name' => 'Label for user group choice field',
						'desc' => '',
						'type' => 'input',
						'default' => 'Choose group to share fees with'
				),
				'share_fees_registration_choice_required' => array (
						'section' => 'Registration Form',
						'name' => 'error message that appears when the registrant does not choose a user to attach to when it is required',
						'desc' => '',
						'type' => 'input',
						'default' => 'You MUST choose group to share fees with'
				),		
				'share_fees_registration_attachment_error' => array (
						'section' => 'Registration Form',
						'name' => 'error message that appears when there is a general error in attaching a user to another',
						'desc' => '',
						'type' => 'input',
						'default' => 'Error attaching to that user.  Please try your user choice again'
				),						
				'share_fees_user_management_field_label' => array (
						'section' => 'User Information Display Page',
			 			'name' => 'Label for field that displays current user choice',
						'desc' => '',
						'type' => 'input',
						'default' => 'User you are currently attached to:'
				),		
				'share_fees_share_message' => array (
						'section' => 'User Information Display Page',
						'name' => 'label for the message that the attached to user shares with all users attached to them.  This message appears at the top of the client side user home page for attached users',
						'desc' => '',
						'type' => 'input',
						'default' => 'Shared Message'
				),		
				'no_share_fees_share_message' => array (
						'section' => 'User Information Display Page',
						'name' => 'Displayed to the attached to user if they are not sharing a message with users attached to them',
						'desc' => '',
						'type' => 'input',
						'default' => 'No message shared'
				),		
				'share_fees_share_message_label' => array (
						'section' => 'User Information Display Page',
						'name' => 'Label for message of attached to user if they are sharing a message with users attached to them',
						'desc' => '',
						'type' => 'input',
						'default' => 'Message shared with users attached to you'
				),		
				'share_fees_share_user_attached_to_label' => array (
						'section' => 'User Information Display Page',
						'name' => 'Label for field that displays the user they are attached to',
						'desc' => '',
						'type' => 'input',
						'default' => 'Message shared with users attached to you'
				),		
				'share_fees_share_user_not_attached' => array (
						'section' => 'User Information Display Page',
						'name' => 'message displayed to the attaching user if they are not currently attached to a user',
						'desc' => '',
						'type' => 'input',
						'default' => 'Currently not attached'
				),						
				'share_fees_user_management_choice_label' => array (
						'section' => 'User Information Edit Form',
			 			'name' => 'Label for user group choice field',
						'desc' => '',
						'type' => 'input',
						'default' => 'Choose group to share fees with'
				),		
				'share_fees_message_to_share_edit_label' => array (
						'section' => 'User Information Edit Form',
						'name' => 'label next to field where attached to user places the message they wish to shared to users attached to them',
						'desc' => '',
						'type' => 'input',
						'default' => 'Message to share with users attached to you'
				),		
				'share_fees_attachment_required_edit' => array (
						'section' => 'User Information Edit Form',
						'name' => 'Error message displayed if user attachment is required and user has not chosen a user to attach to',
						'desc' => '',
						'type' => 'input',
						'default' => 'User attachment required.  Please choose a user to attach to'
				),		
				'share_fees_attachment_error_edit' => array (
						'section' => 'User Information Edit Form',
						'name' => 'Error message displayed if there was an issue with user attachment.  Possibly attempted to attach to a user that cannot be attached to',
						'desc' => '',
						'type' => 'input',
						'default' => 'Error attaching to that user.  Please try again.'
				),		
				'share_fees_search_choices' => array (
						'section' => 'Search Form',
						'name' => 'Label for the field to choose a user attached storefront to search from',
						'desc' => '',
						'type' => 'input',
						'default' => 'Choose a store to search'
				),		
				'share_fees_message_shared_label' => array (
						'section' => 'Client Side Home Page',
						'name' => 'Label for message shared from the user you are attached to',
						'desc' => '',
						'type' => 'input',
						'default' => 'Shared message'
				),		
				'share_fees_message_sharing_label' => array (
						'section' => 'Client Side Home Page',
						'name' => 'Label for message the attached to user is sharing with those attached to them',
						'desc' => '',
						'type' => 'input',
						'default' => 'Message Sharing with others'
				)
				
				
		);
		return $return;
	}
	
	private function getAttachmentTypeDetails () {
		$db = DataAccess::getInstance();
				
		$sql = "SELECT * FROM geodesic_addon_share_fees_settings";
		//echo $sql." using ".$attachment_type_id."<Br>\n";
		$shared_fee_data = $db->GetRow($sql, array());
	
		if ($shared_fee_data)
		{
			//db varialbes within array returned.  Add settings to it.
			$this->_sharedFeeData = $shared_fee_data;
			return true;
		} else {
			//returned wrong count from db
			echo "no shared fee data from db<br>\n";
			return false;
		}
	}
	
	public function display_shared_fee_edit() {
	
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$addon = geoAddon::getInstance();		
		$view = geoView::getInstance();
		
		$attachment_type_id = 1;
		$tpl_vars['attachment_type']= $attachment_type_id;
	
		if ($this->getAttachmentTypeDetails()) {
			//display the details for the attachment type
			//get the user groups in software to show in user group attachment
			$all_user_groups = $db->Execute("select * from ".geoTables::groups_table." order by name");
				
			if ($all_user_groups->RecordCount() > 1) {
				while ($groups = $all_user_groups->FetchRow()) {
					$group_list_from[$groups['group_id']] = $groups['name'];
					$group_list_to[$groups['group_id']] = $groups['name'];
				}
				$tpl_vars['group_list_from'] = $group_list_from;
				$tpl_vars['group_list_to'] = $group_list_to;
	
				$tpl_vars['not_enough_user_groups'] = 0;
	
				//set types of fees to be shared
				//get the fee types shared into an array
				$all_fee_types = explode(",",$this->_sharedFeeData['fee_types_shared']);
				foreach ($all_fee_types as $value) {
					$this->_sharedFeeData['fee_types_list'][$value] = 1;
				}

				$tpl_vars['attachment_data'] = $this->_sharedFeeData;
			} else {
				//minimum of two user groups required to use that user attachment type
				$tpl_vars['not_enough_user_groups'] = 1;
			}
				
			$view->setBodyTpl('admin/edit_shared_fees.tpl',$this->name)
			->setBodyVar($tpl_vars);
		} else {
			//something return
			echo "something wrong with getting the attachment type details for :".$attachment_type_id."<br>\n";
			return false;
		}
		return true;
	}
	
	public function update_shared_fee_edit() {
		//save this data for this attachment type
	
		$db = DataAccess::getInstance();
		$shared_update_data = $_POST['shared_fee_data'];
		$fee_types_shared = $_POST['fee_types_shared'];
	
		$shared_update_data['active'] = ($shared_update_data['active'] == 1)? 1 : 0;
		$shared_update_data['required'] = ($shared_update_data['required'] == 1)? 1 : 0;
		$shared_update_data['post_login_redirect'] = ($shared_update_data['post_login_redirect'] == 1)? 1 : 0;
		$shared_update_data['display_store_category_choices'] = ($shared_update_data['display_store_category_choices'] == 1)? 1 : 0;
		$shared_update_data['use_attached_messages'] = ($shared_update_data['use_attached_messages'] == 1)? 1 : 0;
	
		//check that two user groups do not match
				
		if ($shared_update_data['group_attach_from'] != $shared_update_data['group_attach_to']) {
			if ($shared_update_data['fee_share'] == 0) {
				//set to inactive no matter the active setting because there is no percentage to share.
				$shared_update_data['active'] = 0;
			}
	
			//insert the types of fees that will be shared
			$types_shared = '';
			foreach ($fee_types_shared as $value) {
				if (strlen(trim($types_shared)) > 0)
					$types_shared .= ','.$value;
				else
					$types_shared .= $value;
			}
	
			$sql = "UPDATE geodesic_addon_share_fees_settings SET `percentage_fee_shared`=?, `attaching_user_group`=?, `attached_to_user_group`=?,
				`active`=? , `required` = ?, `post_login_redirect` = ?, `store_category_display` = ?, `use_attached_messages` = ?, fee_types_shared =?";
			$update_result = $db->Execute($sql,
					array($shared_update_data['fee_share'], $shared_update_data['group_attach_from'], $shared_update_data['group_attach_to'],
							$shared_update_data['active'], $shared_update_data['required'], $shared_update_data['post_login_redirect'],
							$shared_update_data['display_store_category_choices'],$shared_update_data['use_attached_messages'],$types_shared));
			if (!$update_result) {
				geoAdmin::m("DB error when attempting to edit value: ".$db->ErrorMsg(),geoAdmin::ERROR);
				return false;
			}
	
	
					
		} else {
			//user groups should not match
			geoAdmin::m("user groups should not match");
		}
	}
	
	public function display_shared_fee_payments() {
		$view = geoView::getInstance();
		$db = DataAccess::getInstance();
		
		$this->getAttachmentTypeDetails();
		if ($_GET['export_shared_fees'])
			$export_shared_fees = intval($_GET['export_shared_fees']);
	
		if ($export_shared_fees == 1) {
			$this->display_export_attachment_payments_due();
		}
	
		if ($_GET['manually_pay'])
			$manually_pay = intval($_GET['manually_pay']);
	
		if ($_GET['specific_user'])
			$specific_user = intval($_GET['specific_user']);
	
		if ($manually_pay) {
			//mark this order item as paid before displaying list
			echo "setting itemid: ".$manually_pay." to paid<br>\n";
			$sql = "UPDATE ".geoTables::order_item." SET paid_out = ?, paid_out_date = ? WHERE id = ? LIMIT 1";
			$update_result = $db->Execute($sql,array(1, geoUtil::time(), $manually_pay));
			if (!$update_result) {
				$this->menu_loader->userFailure('Database Error'); return false;
			}
		}
	
		//get all the current fees to be paid out by user
		$sql = "select
				user.id as userid,
				user.username,
				user.firstname,
				user.lastname,
				user.seller_buyer_data,
				item.cost,
				item.type,
				item.created,
				item.id as itemid
				from
				geodesic_userdata as user
				inner join geodesic_order_item as item on user.id = item.paid_out_to
				where item.paid_out = 0 and (item.paid_out_to != null or item.paid_out_to != 0)";
		if ($specific_user != 0)
			$sql .= " and user.id = ?";
			
		$sql .=	" order by username,created";
		if ($specific_user != 0)
			$payments_due_result = $db->Execute( $sql, array($specific_user));
		else
			$payments_due_result = $db->Execute( $sql);
		if ( !$payments_due_result ) {
			$this->menu_loader->userFailure('Database Error'); return false;
		} elseif ($payments_due_result->RecordCount() == 0) {

			$tpl_vars['no_payments_due_out'] = "There are currently no shared payouts due users.";
				
			$view->setBodyTpl('admin/list_fees_due.tpl',$this->name)->setBodyVar($tpl_vars);
		} else {
			if ($specific_user != 0) {
				//display each row/transaction as only displaying results for one user
				$user_total = 0;
				$iteration = 0;
				$due_out_transactions = array();
				$tpl_vars = array();
				while ($payments_due = $payments_due_result->fetchRow() ) {
					if ($payments_due['type'] == "auction_final_fees") {
						$due_out_transactions[$iteration]['type_of_fee'] = "auction final fee";
					} else {
						//something wrong
					}
					//get listing id for auction involved.
					$sql="select val_string from ".geoTables::order_item_registry." where order_item = ? and index_key = ? ";
					$listing_id = $db->GetOne( $sql ,array($payments_due['itemid'],'listing'));
					if ($listing_id) {
						$listing = geoListing::getListing($listing_id,true,true);
					} else {
						//something odd
						echo "no results from listing_id_result";
					}
					$due_out_transactions[$iteration]['listing_title'] = geoString::fromDB($listing->title);
					$due_out_transactions[$iteration]['listing_id'] = $listing_id;
					$due_out_transactions[$iteration]['listing_started'] = geoDate::toString($listing->date);
					$due_out_transactions[$iteration]['listing_ends'] = geoDate::toString($listing->ends);
					$due_out_transactions[$iteration]['itemid'] = $payments_due['itemid'];
					if ($listing->ends < geoUtil::time())
						$due_out_transactions[$iteration]['expired'] = "expired";
					else
						$due_out_transactions[$iteration]['expired'] = "<style color='green'>not expired</style>";
						
					$due_out_transactions[$iteration]['cost'] = geoNumber::format(($this->_sharedFeeData["percentage_fee_shared"]/100) * $payments_due['cost']);
					if ($payments_due['seller_buyer_data'] != null) {
						$paypal_id = unserialize(geoString::fromDB($payments_due['seller_buyer_data']));
						$user_paypal_id = $paypal_id['paypal_id'];
						$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
	
						$paypal_url = "https://www.paypal.com/cgi-bin/webscr?";
						$paypal_url .= "receiver_email=".urlencode($user_paypal_id);
						//$paypal_url .= "&return=".urlencode($return_url);
						//TODO: add Paypal IPN verification
						//$paypal_url .= "&notify_url=".urlencode($return_url);
						$paypal_url .= "&business=".urlencode($user_paypal_id);
						$paypal_url .= "&cmd=_xclick";
						$paypal_url .= "&item_name=".urlencode(geoString::fromDB($listing->title));
						$paypal_url .= "&image_url=".urlencode('');
						$paypal_url .= "&item_number=$listing_id";
						$paypal_url .= "&quantity=1";
						$paypal_url .= "&shipping=0";
						$paypal_url .= "&handling=0";
						$paypal_url .= "&currency_code=".$db->get_site_setting('postcurrency');
						$paypal_url .= "&amount=".geoNumber::format(($this->_sharedFeeData["percentage_fee_shared"]/100) *$payments_due['cost']);
						$paypal_url .= "&invoice=".$payments_due['itemid'];
						$paypal_url .= "&num_cart_items=1";
						$paypal_url .= "&first_name=".urlencode($payments_due['firstname']);
						$paypal_url .= "&last_name=".urlencode($payments_due['lastname']);
						$paypal_url .= "&payer_id=".urlencode($payments_due['userid']);
	
						$paypal_link = "<a href=".$paypal_url.">".$user_paypal_id."</a>";
	
					} else {
						$paypal_link = 'no paypal id set';
					}
					$due_out_transactions[$iteration]['paypal_link'] = $paypal_link;
					$tpl_vars['userid'] = $payments_due['userid'];
					$tpl_vars['username'] = $payments_due['username'];
					$tpl_vars['firstname'] = $payments_due['firstname'];
					$tpl_vars['lastname'] = $payments_due['lastname'];
						
					$user_total = $user_total + (($this->_sharedFeeData["percentage_fee_shared"]/100) * $payments_due['cost']);
					$iteration++;
				}
				//set the specific user information for display in the template
				$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
				$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
				$tpl_vars['due_out_transactions'] = $due_out_transactions;
				$tpl_vars['user_total'] = geoNumber::format($user_total);
	
				//get last ten items paid out
				$sql = "SELECT
				user.id as userid,
				user.username,
				user.firstname,
				user.lastname,
				user.seller_buyer_data,
				item.cost,
				item.type,
				item.created,
				item.id as itemid,
				paid_out_date
				FROM
				geodesic_userdata as user
				INNER JOIN geodesic_order_item as item on user.id = item.paid_out_to
				WHERE item.paid_out = 1
					and user.id = ?
				ORDER BY created DESC LIMIT 10";
	
				$payments_paid_result = $db->Execute( $sql, array($specific_user));
				if ( !$payments_paid_result ) {
					$this->menu_loader->userFailure('Database Error'); return false;
				} elseif ($payments_paid_result->RecordCount() == 0) {
					$tpl_vars['no_payments_made_yet'] = "No payments have been made to this user yet";
				} else {
					$paid_out_total = 0;
					$iteration = 0;
					$paid_out_transactions = array();
					while ($payments_made = $payments_paid_result->fetchRow() ) {
						if ($payments_made['type'] == "auction_final_fees") {
							$paid_out_transactions[$iteration]['type_of_fee'] = "auction final fee";
						} else {
							//something wrong
						}
						//get listing id for auction involved.
						$sql="select val_string from ".geoTables::order_item_registry." where order_item = ? and index_key = ? ";
						$listing_id = $db->GetOne( $sql ,array($payments_made['itemid'],'listing'));
						if ($listing_id) {
							$listing = geoListing::getListing($listing_id,true,true);
						} else {
							//something odd
							echo "no results from listing_id_result";
						}
						$paid_out_transactions[$iteration]['listing_title'] = geoString::fromDB($listing->title);
						$paid_out_transactions[$iteration]['listing_id'] = $listing_id;
						$paid_out_transactions[$iteration]['listing_started'] = geoDate::toString($listing->date);
						$paid_out_transactions[$iteration]['listing_ends'] = geoDate::toString($listing->ends);
						$paid_out_transactions[$iteration]['cost'] = geoNumber::format(($this->_sharedFeeData["percentage_fee_shared"]/100) * $payments_made['cost']);
						$paid_out_transactions[$iteration]['paid_out_date'] = geoDate::toString($payments_made['paid_out_date']);
						$iteration++;
					}
					$tpl_vars['paid_out_transactions'] = $paid_out_transactions;
				}
	
				$sql = "SELECT
				user.id as userid,
				SUM(item.cost) as total_paid,
				item.type
				FROM
				geodesic_userdata as user
				INNER JOIN geodesic_order_item as item on user.id = item.paid_out_to
				WHERE item.paid_out = 1
				and user.id = ?";
				$total_paid_result = $db->Execute( $sql, array($specific_user));
				$display_total_paid = $total_paid_result->fetchRow();
				$tpl_vars['total_paid_out'] = geoNumber::format($display_total_paid['total_paid']);
				$tpl_vars['specific_user'] = $specific_user;
	
				$view->setBodyTpl('admin/user_itemized.tpl',$this->name)
				->setBodyVar($tpl_vars);
	
			} else {
				//loop through result until hit a different user id
				//get total and then display next to each user
				$current_user_total = 0;
				$current_number_of_transactions = 0;
				$iteration = 0;
				$all_user_total = 0;
				$all_transactions_total = 0;
				$current_user_id = 0;
				while ($payments_due = $payments_due_result->fetchRow()) {
					if (($current_user_id != $payments_due['userid']) && ($current_user_id != 0)) {
						$current_user_total=0;
						$current_number_of_transactions = 0;
						$iteration++;
					}
					$current_user_total = $current_user_total + (($this->_sharedFeeData["percentage_fee_shared"]/100) * $payments_due['cost']);
					$all_user_total = $all_user_total + (($this->_sharedFeeData["percentage_fee_shared"]/100) * $payments_due['cost']);
					$current_number_of_transactions++;
					$all_transactions_total++;
					if ($payments_due['seller_buyer_data'] != null) {
						$paypal_id = unserialize(geoString::fromDB($payments_due['seller_buyer_data']));
						$user_paypal_id = $paypal_id['paypal_id'];
						$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
	
						$paypal_url = "https://www.paypal.com/cgi-bin/webscr?";
						$paypal_url .= "receiver_email=".urlencode($user_paypal_id);
						//$paypal_url .= "&return=".urlencode($return_url);
						//TODO: add Paypal IPN verification
						//$paypal_url .= "&notify_url=".urlencode($return_url);
						$paypal_url .= "&business=".urlencode($user_paypal_id);
						$paypal_url .= "&cmd=_xclick";
						$paypal_url .= "&item_name=".urlencode('shared fees');
						$paypal_url .= "&image_url=".urlencode('');
						$paypal_url .= "&item_number=$listing_id";
						$paypal_url .= "&quantity=1";
						$paypal_url .= "&shipping=0";
						$paypal_url .= "&handling=0";
						$paypal_url .= "&currency_code=".$db->get_site_setting('postcurrency');
						$paypal_url .= "&amount=".geoNumber::format($current_user_total);
						$paypal_url .= "&invoice=".urlencode($payments_due['userid']);
						$paypal_url .= "&num_cart_items=1";
						$paypal_url .= "&first_name=".urlencode($payments_due['firstname']);
						$paypal_url .= "&last_name=".urlencode($payments_due['lastname']);
						$paypal_url .= "&payer_id=".urlencode($payments_due['userid']);
	
						$paypal_link = "<a href=".$paypal_url.">".$user_paypal_id."</a>";
	
					} else {
						$paypal_link = 'no paypal id set';
					}
					$due_out_transactions[$iteration]['paypal_link'] = $paypal_link;
					$due_out_transactions[$iteration]['user_total'] = geoNumber::format($current_user_total);
					$due_out_transactions[$iteration]['userid'] = $payments_due['userid'];
					$due_out_transactions[$iteration]['username'] = $payments_due['username'];
					$due_out_transactions[$iteration]['firstname'] = $payments_due['firstname'];
					$due_out_transactions[$iteration]['lastname'] = $payments_due['lastname'];
					$due_out_transactions[$iteration]['transaction_total'] = $current_number_of_transactions;
					$due_out_transactions[$iteration]['last_transaction_date'] = date ('F j, Y', $payments_due['created']);
					$current_user_id = $payments_due['userid'];
				}
				$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
				$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
				$tpl_vars['all_user_total'] = geoNumber::format($all_user_total);
				$tpl_vars['due_out_transactions'] = $due_out_transactions;
				$tpl_vars['all_transaction_total'] = $all_transactions_total;
	
				$view->setBodyTpl('admin/list_fees_due.tpl',$this->name)->setBodyVar($tpl_vars);
	
			}
		}
	}
	
	public function display_export_shared_fee_payments_due() {
		if ($_GET['mark_as_paid_out'])
			$mark_as_paid_out = intval($_GET['mark_as_paid_out']);
	
		if ($_GET['specific_user'])
			$specific_user = intval($_GET['specific_user']);
		//export payment data for all or individual
		$db = DataAccess::getInstance();
		$this->getAttachmentTypeDetails();		
		$export_array = array("userid","username","firstname","lastname","amount_due","type_of_fee","date_created","order_item_id","order_id");
		$data = "";
		$next_line = "";
		$paid_out_in_statement = "";
		//exports all current payments due to be paid out
		//and sets paid_out to 1 for each of the items exported
		$sql = "select
				user.id as userid,
				user.username,
				user.firstname,
				user.lastname,
				user.seller_buyer_data,
				item.cost,
				item.type,
				item.created,
				item.id as itemid,
				item.order as orderid
				from
				geodesic_userdata as user
				inner join geodesic_order_item as item on user.id = item.paid_out_to
				where item.paid_out = 0 and (item.paid_out_to != null or item.paid_out_to != 0)";
	
		if ($specific_user != 0) {
			$sql .= " and user.id = ".$specific_user;
			$username = geoUser::userName($specific_user);
			$filename = "shared_payments_due_export_for_".$username.".csv";
		} else {
			$filename = "shared_payments_due_export.csv";
		}
		$sql .=	" order by userid,created";
	
		$payments_due_result = $db->Execute( $sql );
		if ( !$payments_due_result ) {
			echo $sql." is the sql<bR>\n";
			$this->menu_loader->userFailure('Database Error'); return false;
		} elseif ($payments_due_result->RecordCount() == 0) {
			$view = geoView::getInstance();
			$tpl_vars['no_payments_due_out'] = "There are currently no shared payouts due users to export.";
				
			$view->setBodyTpl('admin/list_fees_due.tpl',$this->name)->setBodyVar($tpl_vars);
			//echo $sql." is the sql<br>\n";
			//echo "there are no results";
		} else {
				
			if ($this->encapsulate)
			{
				$next_line = "\"";
				$next_line .= implode( "\",\"", $export_array );
				$next_line .= "\"";
			}
			else
			{
				$next_line = implode( ",", $export_array );
			}
	
			$data .= $next_line."\n";
	
			while ( $row = $payments_due_result->fetchRow() )
			{
				$next_line = "";
				if ($this->encapsulate)
					$next_line = "\"".$row['userid']."\",\"".$row['username']."\",\"".$row['firstname']."\",\"".$row['lastname']."\",\"".(($this->_sharedFeeData["percentage_fee_shared"]/100) * $row['cost'])."\",\"".$row['type']."\",\"".$row['created']."\",\"".$row['itemid']."\",\"".$row['orderid']."\"";
				else
					$next_line = $row['userid'].",".$row['username'].",".$row['firstname'].",".$row['lastname'].",".(($this->_sharedFeeData["percentage_fee_shared"]/100) * $row['cost']).",".$row['type'].",".$row['created'].",".$row['itemid'].",".$row['orderid'];
				if ($mark_as_paid_out) {
					//create in statement
					if (strlen(trim($paid_out_in_statement)) > 0) {
						$paid_out_in_statement .= ",".$row['itemid'];
					} else {
						$paid_out_in_statement = "(".$row['itemid'];
					}
						
				}
				$data .= $next_line."\n";
			}
				
			if ($mark_as_paid_out) {
				$paid_out_in_statement .= ")";
				//since all payments have just been exported set all paid_out to 1
				//for the paid out items above....not the whole table
				//the "in statement" contents should have been set in the above loop
					
				$sql = "update
					geodesic_order_item
					set
					paid_out = 1
					where geodesic_order_item.id in ".$paid_out_in_statement;
				$set_paid_out_result = $db->Execute( $sql );
			}
	
	
			header("Content-Disposition: attachment; filename={$filename}");
			die($data);
	
		}
	}	
}