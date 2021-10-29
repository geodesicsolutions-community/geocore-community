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
## 
##    17.07.0-15-g52acd14
## 
##################################

if( class_exists( 'classes_AJAX' ) or die());

class CLASSES_AJAXController_CostOptions extends classes_AJAX
{
	private $maxImages = null;
	private $messages;
	
	/**
	 * Ajax call to add a new cost option selection
	 */
	public function add ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		$fields = geoFields::getInstance($cart->user_data['group_id'], $cart->item->getCategory());
		
		if (!$fields->cost_options->is_enabled) {
			//failsafe
			return;
		}
		
		//data to be returned
		$data = array();
		
		$tpl_vars = $cart->getCommonTemplateVars();
		
		$tpl_vars['new'] = true;
		
		//add new always starts off as none
		$tpl_vars['quantity_type'] = 'none';
		
		$tpl_vars['maxImages'] = $this->maxImages;
		
		$template = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$template->assign($tpl_vars);
		
		$data['dialog'] = $template->fetch('shared/cost_options/edit.tpl');
		$data['dialog_title'] = $this->messages[502215];
		
		echo $this->encodeJSON($data);
	}
	
	/**
	 * ajax call to edit a selection of cost options.
	 */
	public function editGroup ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		$fields = geoFields::getInstance($cart->user_data['group_id'], $cart->item->getCategory());
		
		if (!$fields->cost_options->is_enabled) {
			//failsafe
			return;
		}
		
		//data to be returned
		$data = array();
		
		$session_variables = $cart->item->get('session_variables');
		if(!$session_variables['cost_options'] && $cart->item->getType() === 'listing_edit') {
			//this is an edit and the sessvars don't exist yet on the item; get them the long way
			require_once(CLASSES_DIR.'order_items/listing_edit.php');
			$session_variables = listing_editOrderItem::getSessionVars($cart->item, true);
		}
		//return print_r($cart->item,1);
		if (!$session_variables) {
			//failsafe
			return $this->_error($this->messages[502221]);
		}
		
		$tpl_vars = $cart->getCommonTemplateVars();
		
		$tpl_vars['group_id'] = $group_id = (int)$_POST['groupId'];
		
		if($cart->item->getType() === 'listing_edit') {
			//editing an existing group -- need to preserve its global database ID
			//(which shares a name with $group_id here, but is actually very different...) 
			$tpl_vars['db_group_id'] = $session_variables['cost_options'][$group_id]['group_id'];
		}
		
		
		$cost_options = $session_variables['cost_options'];
		if (!$cost_options || !isset($cost_options[$group_id])) {
			//problem
			return $this->_error('Selection not found, cannot edit.');
		}
		
		$quantity_type = (isset($cost_options[$group_id]['quantity_type']))? $cost_options[$group_id]['quantity_type'] : 'none';
		if (!in_array($quantity_type, array('none','individual','combined'))) {
			//invalid value, use none
			$quantity_type = 'none';
		}
		$tpl_vars['quantity_type'] = $quantity_type;
		
		//just in case it needs all the info (customizations or what not)
		$tpl_vars['session_variables'] = $session_variables;
		$tpl_vars['info'] = $cost_options[$group_id];
		
		$tpl_vars['maxImages'] = $this->maxImages;
		
		$template = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$template->assign($tpl_vars);
		
		$data['dialog'] = $template->fetch('shared/cost_options/edit.tpl');
		$data['dialog_title'] = $this->messages[502216];
		
		echo $this->encodeJSON($data);
	}
	
	/**
	 * Used for both add and editGroup - saves the new or changed cost option selection
	 * 
	 */
	public function update ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		$fields = geoFields::getInstance($cart->user_data['group_id'], $cart->item->getCategory());
		
		if (!$fields->cost_options->is_enabled) {
			//failsafe
			return;
		}
		
		//data to be returned
		$data = array();
		
		$session_variables = $cart->item->get('session_variables');
		if(!$session_variables['cost_options'] && $cart->item->getType() === 'listing_edit') {
			//this is an edit and the sessvars don't exist yet on the item; get them the long way
			require_once(CLASSES_DIR.'order_items/listing_edit.php');
			$parent_sv = listing_editOrderItem::getSessionVars($cart->item, true);
			//since this is an edit, we here want to avoid polluting session_variables with unchanged things, so only use the vars we're looking for
			$session_variables['cost_options'] = $parent_sv['cost_options'];
			//also store some of the extra junk and put it back in later, so it doesn't mess with counts
			$saved_group_ids = $session_variables['cost_options']['group_ids'];
			$saved_option_ids = $session_variables['cost_options']['option_ids'];
			unset($session_variables['cost_options']['group_ids'], $session_variables['cost_options']['option_ids']);
		}
		if (!$session_variables) {
			//failsafe
			return $this->_error($this->messages[502221]);
		}
		$tpl_vars = $cart->getCommonTemplateVars();
		
		$cost_option_limits = explode('|',$fields->cost_options->type_data);
			
		if (count($cost_option_limits)==2) {
			$tpl_vars['cost_option_max_groups'] = (int)$cost_option_limits[0];
			$tpl_vars['cost_option_max_options'] = (int)$cost_option_limits[1];
		} else {
			$tpl_vars['cost_option_max_groups'] = $tpl_vars['cost_option_max_options'] = 0;
		}
		$new = (isset($_POST['new']));
		
		if ($new) {
			//make sure not already at limit...
			if (count($session_variables['cost_options']) >= $tpl_vars['cost_option_max_groups']) {
				//Already at max number of selections...  Failsafe, this should also
				//already be blocked via UI
				return $this->_error($this->messages[502217]);
			}
		}
		
		//un-comment next line to reset if session vars get out of whack
		//unset($session_variables['cost_options']);
		
		if ($new) {
			$group_id = (isset($session_variables['cost_options']))? count($session_variables['cost_options']) : 0;
		} else {
			$group_id = (int)$_POST['group_id'];
		}
		//populate options
		$options = $_POST['options'];
		
		//don't include "new" ones in there
		unset($options['new']);
		
		//the current cost options quantity...
		$quantity_type = (isset($_POST['quantity_type']))? $_POST['quantity_type'] : 'none';
		if (!in_array($quantity_type, array('none','individual','combined'))) {
			//invalid value, use none
			$quantity_type = 'none';
		}
		$tpl_vars['quantity_type'] = $quantity_type;
		
		foreach ($_POST['options']['new']['label'] as $i => $label) {
			if (!strlen(trim($label)) && $i===0) {
				//first one, skip it as this is the dummy new one
				continue;
			}
			
			//note: we're doing cleaning by common function in a sec
			$file_slot = (isset($_POST['options']['new']['file_slot'][$i]))? (int)$_POST['options']['new']['file_slot'][$i] : 0;
			$options[] = array (
				'label' => trim($label),
				'cost_added' => $_POST['options']['new']['cost_added'][$i],
				'ind_quantity_remaining' => $_POST['options']['new']['ind_quantity_remaining'][$i],
				'file_slot' => $file_slot
				);
		}
		//whether or not there are errors with any options...
		$optionsClean = $this->_cleanOptions($options);
		
		$label = trim($_POST['label']);
		
		//wordrap
		$label = geoString::breakLongWords($label,$cart->db->get_site_setting('max_word_width'), " ");
		//check the value for badwords
		$label = geoFilter::badword($label);
		//check the value for disallowed html
		$label = geoFilter::replaceDisallowedHtml($label,0);
		
		//make sure it doesn't go over max length
		$label = substr($label,0,$fields->cost_options->text_length);
		
		//urlencode here to match/workaround the case where existing options NOT changed during a copy/edit are never decoded
		$label = geoString::toDB($label);
		
		$group = array (
			'label' => $label,
			'quantity_type' => $quantity_type,
			'options' => $options,
			);
		if (count($options)<2) {
			//at least 2 options required
			$group['error'] = $this->messages[502218];
		} else if (!$optionsClean) {
			//some error with options, add generic error at top since those option error
			//could be "hidden" below a scroll if there are a lot of options
			$group['error'] = $this->messages[502219];
		}
		if($cart->item->getType() === 'listing_edit') {
			//need to make sure these IDs are preserved during a listing edit or things go crazy sideways
			$group['listing_id'] = $cart->item->get('listing_id');
			$group['group_id'] = (int)$_POST['db_group_id'];
		}
		$session_variables['cost_options'][$group_id] = $group;
		if ($quantity_type === 'individual') {
			//go ahead and set the quantity
			$info = $this->_checkQuantity($session_variables);
			if ($info['quantity']>0 && !$info['noAdd']) {
				//send quantity to auto-update...
				$data['update_quantity'] = $info['quantity'];
			}
		}
		
		//reset combined quantities
		unset($session_variables['cost_options_quantity']);
		
		if($saved_group_ids || $saved_option_ids) {
			$session_variables['cost_options']['group_ids'] = $saved_group_ids;
			$session_variables['cost_options']['option_ids'] = $saved_option_ids;
		}
		
		$cart->item->set('session_variables',$session_variables);
		$cart->save();
		
		$tpl_vars['session_variables'] = $session_variables;
		$tpl_vars['ajax'] = true;
		$tpl_vars['precurrency'] = '';
		$tpl_vars['maxImages'] = $this->maxImages;
		
		$tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$tpl->assign($tpl_vars);
		
		$data['cost_options_box'] = $tpl->fetch('shared/cost_options/index.tpl');
		
		echo $this->encodeJSON($data);
	}
	
	/**
	 * Ajax call to sort the selections
	 */
	public function sortGroups ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		//data to be returned
		$data = array();
		
		$session_variables = $cart->item->get('session_variables');
		if (!$session_variables) {
			//failsafe
			return $this->_error($this->messages[502221]);
		}
		$tpl_vars = $cart->getCommonTemplateVars();
		
		//Go through the order and update the order of the groups
		
		$old_groups = $session_variables['cost_options'];
		$new_groups = array();
		
		foreach ($_POST['costgroup'] as $newI => $oldI) {
			$newI = (int)$newI;
			$oldI = (int)$oldI;
			if (!isset($old_groups[$oldI])) {
				//failsafe, something wrong during sort
				return $this->_error($this->messages[502220]);
			}
			$new_groups[$newI] = $old_groups[$oldI];
		}
		if (count($new_groups) !== count($old_groups)) {
			//failsafe, something wrong during sort
			return $this->_error($this->messages[502220]);
		}
		ksort($new_groups);
		//make sure to get rid of gaps etc.
		$new_groups = array_values($new_groups);
		
		$session_variables['cost_options'] = $new_groups;
		$cart->item->set('session_variables',$session_variables);
		$cart->save();
		
		$tpl_vars = $cart->getCommonTemplateVars();
		
		$tpl_vars['session_variables'] = $session_variables;
		$tpl_vars['ajax'] = true;
		$tpl_vars['precurrency'] = '';
		
		$tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$tpl->assign($tpl_vars);
		
		$data['cost_options_box'] = $tpl->fetch('shared/cost_options/index.tpl');
		
		echo $this->encodeJSON($data);
	}
	/**
	 * AJAX call to delete an entire cost option selection group
	 */
	public function deleteGroup ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		//data to be returned
		$data = array();
		
		$session_variables = $cart->item->get('session_variables');
		$tpl_vars = $cart->getCommonTemplateVars();
		if (!$session_variables) {
			//failsafe
			return $this->_error($this->messages[502221]);
		}
		
		$group_id = (int)$_POST['groupId'];
		
		$cost_options = $session_variables['cost_options'];
		if (!$cost_options || !isset($cost_options[$group_id])) {
			//failsafe, selection to remove not found
			return $this->_error($this->messages[502222]);
		}
		
		unset($cost_options[$group_id]);
		
		//reset cost option keys... since they do NOT go to the actual saved value
		$cost_options = array_values($cost_options);
		$session_variables['cost_options'] = $cost_options;
		//reset combined quantities
		unset($session_variables['cost_options_quantity']);
		
		$cart->item->set('session_variables',$session_variables);
		$cart->save();
		
		//selection removed
		$data['msg'] = $this->messages[502223];
		
		$tpl_vars = $cart->getCommonTemplateVars();
		
		$tpl_vars['session_variables'] = $session_variables;
		$tpl_vars['ajax'] = true;
		
		$tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$tpl->assign($tpl_vars);
		
		$data['cost_options_box'] = $tpl->fetch('shared/cost_options/index.tpl');
		
		echo $this->encodeJSON($data);
	}
	
	/**
	 * AJAX call to show dialog for editing the combined quantity.
	 */
	public function editCombinedQuantity ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		//data to be returned
		$data = array();
		
		$session_variables = $cart->item->get('session_variables');
		$tpl_vars = $cart->getCommonTemplateVars();
		if (!$session_variables) {
			//failsafe
			return $this->_error($this->messages[502221]);
		}
		
		//Make sure that there are no errors, as it is not good idea to try setting
		//quantities when there are problems with the options entered. At same time,
		//populate an array of "just combined options" to use
		
		$combined_options = array();
		$changes = false;
		foreach ($session_variables['cost_options'] as $group_id => $option_group) {
			if (isset($option_group['error'])) {
				//error remaining
				return $this->_error($this->messages[502224]);
			}
			if ($option_group['quantity_type']!=='combined') {
				//this one doesn't affect combined quantities
				continue;
			}
			foreach ($option_group['options'] as $option_id => $option) {
				if (isset($option['error'])) {
					//error remaining
					return $this->_error($this->messages[502224]);
				}
				if (!isset($option['comb_id'])) {
					//set a combined ID that will be used to identify this option in quantities
					$session_variables['cost_options'][$group_id]['options'][$option_id]['comb_id'] = 
						$option_group['options'][$option_id]['comb_id'] = $this->_combId($session_variables['cost_options']);
					$changes = true;
				}
			}
			$combined_options[] = $option_group;
		}
		if ($changes) {
			//save changes to session vars to save comb_id values
			$cart->item->set('session_variables',$session_variables);
			$cart->save();
		}
		if (count($combined_options)<=1) {
			//failsafe, should not get here usually
			return $this->_error($this->messages[502225]);
		}
		$tpl_vars['combined_options'] = $combined_options;
		$tpl_vars['cost_options_quantity'] = (isset($session_variables['cost_options_quantity']))? $session_variables['cost_options_quantity'] : array();
		
		$tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$tpl->assign($tpl_vars);
		
		$data['dialog'] = $tpl->fetch('shared/cost_options/edit_combined_quantity.tpl');
		$data['dialog_title'] = 'Set Combined Quantities';
		
		echo $this->encodeJSON($data);
	}
	
	/**
	 * AJAX call to update the combined quantities
	 */
	public function updateCombinedQuantity ()
	{
		$this->jsonHeader();
		$adminId = (int)$_GET['adminId'];
		if ($adminId) {
			define('IN_ADMIN',1);
			$_COOKIE['classified_session'] = $_COOKIE['admin_classified_session'];
		}
		
		$session = geoSession::getInstance();
		$session->initSession();
		
		$cart = geoCart::getInstance();
		//start up the cart
		$userId = ($adminId)? (int)$_GET['userId'] : null;
		$cart->init(true, $userId);
		
		if (!$this->_validateCartStep()) {
			//invalid it seems?
			return;
		}
		
		$new = (isset($_POST['new']));
		
		
		//data to be returned
		$data = array();
		
		$session_variables = $cart->item->get('session_variables');
		if (!$session_variables) {
			//failsafe
			return $this->_error($this->messages[502221]);
		}
		$tpl_vars = $cart->getCommonTemplateVars();
		
		//make sure the entries are valid.  This is user input after all!
		$cost_options_quantity = array();
		
		foreach ($_POST['cost_options_quantity'] as $key => $quantity) {
			$quantity = (int)$quantity;
			if (!$quantity) {
				//remove 0 quantity (or invalid entries)
				continue;
			}
			$comb_ids = explode('_',$key);
			//TODO: validate each combined ID
			
			$cost_options_quantity[$key] = $quantity;
		}
		$session_variables['cost_options_quantity'] = $cost_options_quantity;
		
		//go ahead and set the quantity
		$info = $this->_checkQuantity($session_variables);
		if ($info['quantity']>0 && !$info['noAdd']) {
			//send quantity to auto-update...
			$data['update_quantity'] = $info['quantity'];
		}
		
		$cart->item->set('session_variables',$session_variables);
		$cart->save();
		
		$tpl_vars['session_variables'] = $session_variables;
		$tpl_vars['ajax'] = true;
		$tpl_vars['precurrency'] = '';
		
		$tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$tpl->assign($tpl_vars);
		
		$data['cost_options_box'] = $tpl->fetch('shared/cost_options/index.tpl');
		
		echo $this->encodeJSON($data);
	}
	
	/**
	 * Generates a random unique "combined option ID", used for saving combined quantities,
	 * since at the time the quantities are being set the options do not yet have their
	 * unique option ID's.
	 * 
	 * Note that this generates an alpha-numeric value 4 characters in length.
	 * 
	 * @param array $cost_options
	 * @return string
	 */
	private function _combId ($cost_options)
	{
		$pc = geoPC::getInstance();
		do {
			$comb_id = $pc->generate_new_pass(4);
			$found = false;
			foreach ($cost_options as $group) {
				foreach ($group['options'] as $option) {
					if (isset($option['comb_id']) && $option['comb_id']===$comb_id) {
						//found another option with same ID so can't use that one
						$found = true;
						break (2);
					}
				}
			}
		} while ($found && $comb_id);
		return $comb_id;
	}
	
	private function _prepCombinedOptions ($session_variables)
	{
		//make sure there are some cost option quantity to show
		if (!isset($session_variables['cost_options_quantity']) || !count($session_variables['cost_options_quantity'])) {
			return array();
		}
		
		//first, lets do a hash table for all the various comb_id labels
		$labels = array();
		foreach ($session_variables['cost_options'] as $group) {
			foreach ($group['options'] as $option) {
				if (isset($option['comb_id'])) {
					$labels[$option['comb_id']] = $option['label'];
				}
			}
		}
		
		$combined = array();
		//now lets go through each of teh combined quantities and create an array
		//to use to display them
		foreach ($session_variables['cost_options_quantity'] as $hash => $quantity) {
			$option_parts = explode('_',$hash);
			$options = array();
			foreach ($option_parts as $part) {
				$options[] = $labels[$part];
			}
			$combined[] = array ('options' => $options, 'quantity' => $quantity);
		}
		return $combined;
	}
	
	/**
	 * Used to clean a set of options, setting "error" entry in the array for any
	 * that might have issues.
	 *
	 * @param array $options
	 * @return bool True if everything is fine, false if any had an error.
	 */
	private function _cleanOptions (&$options)
	{
		//Get the limits from the cart / fields settings
		$cart = geoCart::getInstance();
		$fields = geoFields::getInstance($cart->user_data['group_id'], $cart->item->getCategory());
		
		$text_length = (int)$fields->cost_options->text_length;
		$type_data = $fields->cost_options->type_data;
		$max_options = (int)substr($type_data, strpos($type_data,'|')+1);
		
		$cleaned = $labels = array();
		$errorFree = $first = true;
		$count = 0;
		foreach ($options as $raw) {
			$count++;
			if ($count > $max_options) {
				//we reached the max number, don't allow going further.  Since
				//there is UI "enforcement" of this, probably don't need error message
				break;
			}
			
			//make sure nothing "extra" is set since this is based on user input...
			$option = array();
				
			
			
			$label = trim($raw['label']);
			
			if ($label) {
				//clean it up, but don't bother unless it is non-empty
				
				//wordrap
				$label = geoString::breakLongWords($label,$cart->db->get_site_setting('max_word_width'), " ");
				//check the value for badwords
				$label = geoFilter::badword($label);
				//check the value for disallowed html
				$label = geoFilter::replaceDisallowedHtml($label,0);
				
				//make sure it doesn't go over max length
				$label = substr($label,0,$text_length);
				
				//urlencode here to match/workaround the case where existing options NOT changed during a copy/edit are never decoded
				$label = geoString::toDB($label);
			}
			
			//NOTE: We do NOT remove "blank" labels, as it allows for "nothing selected" option.
			//but we don't allow multiple with same value!
			if (in_array($label, $labels)) {
				$errorFree = false;
				$option['error'] = 'Duplicate option';
			} else if (!$first && !strlen($label)) {
				//and we don't allow blank entries other than in the first option
				$option['error'] = $this->messages[502226];
			}
			$labels[] = $option['label'] = $label;
			
			$option['cost_added'] = geoNumber::deformat($raw['cost_added']);
			
			/*
			 * Note that it still preserves the ind_quantity_remaining regardless
			 * of how the quantity is applied (cost_options_quantity) so that it
			 * can be changed without loosing the quantity data
			 */
			//make sure quantity remaining is a number and >= 0...
			$option['ind_quantity_remaining'] = max(0,(int)$raw['ind_quantity_remaining']);
			
			$option['file_slot'] = ((int)$raw['file_slot'] > 0 && (int)$raw['file_slot']<=$this->maxImages)? (int)$raw['file_slot'] : 0;
			
			if($raw['option_id']) {
				$option['option_id'] = (int)$raw['option_id'];
			}
			
			$cleaned[] = $option;
			$first = false;
		}
		$options = $cleaned;
		return $errorFree;
	}
	
	/**
	 * Gets the quantity based on any quantities set in cost option groups
	 * @return number
	 */
	private function _checkQuantity ($session_variables)
	{
		$cost_options = $session_variables['cost_options'];
		$cost_options_quantity = $session_variables['cost_options_quantity'];
		$maxQuantity = null;
		$noAdd = false;
		foreach ($cost_options as $group) {
			$quantity = 0;
			if ($group['quantity_type']==='individual') {
				//count this one...
				foreach ($group['options'] as $cost_option) {
					$quantity += (int)$cost_option['ind_quantity_remaining'];
				}
				if ($maxQuantity===null) {
					//not set yet, use this as the starting one
					$maxQuantity = $quantity;
				}
				if ($maxQuantity !== $quantity) {
					//there are differences...
					$noAdd=true;
				}
				$maxQuantity = max($quantity, $maxQuantity);
			}
		}
		if ($cost_options_quantity) {
			$quantity = array_sum($cost_options_quantity);
			if ($maxQuantity===null) {
				//not set yet, use this as the starting one
				$maxQuantity = $quantity;
			}
			if ($maxQuantity !== $quantity) {
				//there are differences...
				$noAdd=true;
			}
			$maxQuantity = max($quantity, $maxQuantity);
		}
		return array ('quantity' => $maxQuantity, 'noAdd' => $noAdd);
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
		
		//use text off of the details page
		$cart->site->messages = $this->messages = $cart->db->get_text(true, 9);
		
		$step = $cart->cart_variables['step'];
		$userId = (int)$_GET['userId'];
		$adminId = (int)$_GET['adminId'];
		
		$session = geoSession::getInstance();
		
		$sessionUser = $session->getUserId();
		
		$checkUser = ($adminId)? $adminId : $userId;
		
		if ($checkUser && !$sessionUser) {
			//user was logged in, now logged out
			return $this->_error($this->messages[502227]);
		}
		if ($sessionUser != $checkUser) {
			//user different than when started?
			return $this->_error($this->messages[502228]);
		}
		
		//check to make sure there is an item in there
		if (!$cart->item) {
			//oops, no item in cart, can't go forward.  Not on details step error msg
			return $this->_error($this->messages[502229]);
		}
		
		//make sure the step is not one of the built in ones
		if ($step !== 'combined' && strpos($step, ':') === false) {
			//They are on a built-in step, not details.  Not on details step error msg
			return $this->_error($this->messages[502229]);
		}
		
		//make sure the order items that are OK to be attached to
		$validItems = geoOrderItem::getParentTypesFor('images');
		
		if (!in_array($cart->item->getType(), $validItems)) {
			//oops! this isn't a valid order item...  Not on details step error msg
			return $this->_error($this->messages[502229]);
		}
		
		//Go ahead and get max images from the cart
		require_once CLASSES_DIR.'order_items/images.php';
		$this->maxImages = (int)imagesOrderItem::getMaxImages();
		
		//got this far, they should be on images step...
		return true;
	}
	
	/**
	 * Internal method to return error for PLUpload format
	 * @param int $code
	 * @param string $message
	 * @return boolean Always returns false
	 */
	private function _error ($message)
	{
		echo $this->encodeJSON(array ('error' => $message));
		return false;
	}
}