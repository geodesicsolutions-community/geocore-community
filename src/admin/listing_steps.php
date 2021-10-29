<?php
//admin/listing_steps.php
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
##    7.2.1-6-g63ad625
## 
##################################

class listingStepsManage
{
	public function display_listing_placement_steps ()
	{
		$view = geoView::getInstance();
		
		$tpl_vars = array();
		
		$tpl_vars['admin_msgs'] = geoAdmin::m();
		
		//let app bottom know to NOT save cart
		define ('geoCart_skipSave',true);
		
		//Lets get all the order item steps...
		$cart = geoCart::getInstance();
		
		//don't initialize normally, we only need site to be done
		$cart->init(true,1,false);
		
		$parents = $this->_getParentTypes();
		
		$tpl_vars['types'] = array();
		
		//prefix for all plan item settings
		$pre = geoCart::COMBINED_PREFIX;
		
		//loop through all parent types, get the ones that want to combine steps
		foreach ($parents as $type => $details) {
			$entry = array();
			//create an empty item so we can get the type title which is not
			//a static var...
			$blankItem = new $details['class_name'];
			$entry['title'] = $blankItem->getTypeTitle();
			unset($blankItem);
			
			geoOrderItem::callUpdate('geoCart_initSteps',true,$type);
			
			if (geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails',null,'bool_true',$type)) {
				$cart->addStep('other_details');
			}
			
			$entry['steps'] = $cart->initStepsView(true, false);
			$cart->clearAllSteps();
			
			//get settings...  NOTE: Use price plan ID of 0 and cat 0 to signify
			//"site-wide" settings, as there is no price plan or cat specific settings
			//for combining steps.
			
			$planItem = geoPlanItem::getPlanItem($type, 0, 0);
			$entry['combine'] = $planItem->get($pre.'combine','none');
			if ($entry['combine'] == 'selected') {
				$entry['combined'] = $planItem->get($pre.'combined',array());
			}
			$entry['skip_cart'] = $planItem->get($pre.'skip_cart');
			$entry['always_preview'] = $planItem->get($pre.'always_preview');
			$entry['force_preview'] = $planItem->get($pre.'force_preview');
			
			$tpl_vars['types'][$type] = $entry;
		}
		
		$view->setBodyTpl('listing_steps.tpl')
			->setBodyVar($tpl_vars)
			->addJScript('js/listing_placement_steps.js');
	}
	
	public function update_listing_placement_steps ()
	{
		$parents = $this->_getParentTypes();
		//prefix for all plan item settings
		$pre = geoCart::COMBINED_PREFIX;
		
		foreach ($parents as $type => $details) {
			$planItem = geoPlanItem::getPlanItem($type, 0, 0);
			
			$validCombines = array('none','all','selected');
			
			$combine = (isset($_POST['combine'][$type]) && in_array($_POST['combine'][$type],$validCombines))? $_POST['combine'][$type] : 'none';
			
			if ($combine=='selected' && (!$_POST['combined'][$type] || count($_POST['combined'][$type])<2)) {
				//none selected, or 2 or more not selected, change it to none
				$combine='none';
			}
			$planItem->set($pre.'combine', $combine);
			
			if ($combine=='selected') {
				//save selected step values
				$combined = (array)$_POST['combined'][$type];
				$planItem->set($pre.'combined', $combined);
			}
			
			$skip_cart = (isset($_POST['skip_cart'][$type]) && $_POST['skip_cart'][$type])?1:false;
			$always_preview = (isset($_POST['always_preview'][$type]) && $_POST['always_preview'][$type])?1:false;
			//only force preview is "skip cart" or "always preview" is on
			$force_preview = (($skip_cart || $always_preview) && isset($_POST['force_preview'][$type]) && $_POST['force_preview'][$type])?1:false;
			
			$planItem->set($pre.'skip_cart',$skip_cart);
			$planItem->set($pre.'always_preview',$always_preview);
			$planItem->set($pre.'force_preview',$force_preview);
			
			$planItem->save();
		}
		return true;
	}
	
	private function _getParentTypes ()
	{
		$parents = geoOrderItem::getOrderItemTypes(true);
		
		$types = array();
		foreach ($parents as $type => $details) {
			if (geoOrderItem::callDisplay('geoCart_canCombineSteps',null,'bool_true', $type)) {
				$types[$type] = $details;
			}
		}
		return $types;
	}
}