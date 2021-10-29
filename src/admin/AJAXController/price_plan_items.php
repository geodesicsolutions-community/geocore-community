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
## ##    7.4.6-11-g316131f
## 
##################################

// DON'T FORGET THIS
if(class_exists( 'admin_AJAX' ) or die());

class ADMIN_AJAXController_price_plan_items extends admin_AJAX {
	public function display_config()
	{
		$admin = geoAdmin::getInstance();
		
		if (!$admin->isAllowed('pricing_edit_plans')){
			return 'ERROR: no access';
		}
		
		$all_items = geoOrderItem::getOrderItemTypes();
		
		$item_name = $_GET['item'];
		if (!isset($all_items[$item_name]) || !class_exists($all_items[$item_name]['class_name'])) {
			//could not find that order item!
			return 'ERROR: could not retrieve data';
		}
		$order_item = Singleton::getInstance($all_items[$item_name]['class_name']);
		$price_plan_id = intval($_GET['price_plan_id']);
		$category_id = intval($_GET['category_id']);
		$plan_item = geoPlanItem::getPlanItem($item_name, $price_plan_id,$category_id);
		$config = $order_item->adminPlanItemConfigDisplay($plan_item);
			
		$html = "
		<div style='position:relative;'>
			<form id='form_$item_name' onsubmit='return false;' action='' method='post'>
				<div class='configBox'>$config</div>
			</form>
		</div>";
		
		return $html;
	}

	public function save ()
	{
		$admin = geoAdmin::getInstance();
		
		if (!$admin->isAllowed('pricing_edit_plans','update')){
			return 'ERROR: no access';
		}
		
		$all_items = geoOrderItem::getOrderItemTypes();
		
		$item_name = $_GET['item'];
		
		if (!isset($all_items[$item_name]) || !class_exists($all_items[$item_name]['class_name'])) {
			//could not find that order item!
			return 'Settings NOT saved. Check for data errors.';
		}
		$price_plan_id = intval($_GET['price_plan_id']);
		$category_id = intval($_GET['category_id']);
		
		$order_item = Singleton::getInstance($all_items[$item_name]['class_name']);
		$plan_item = geoPlanItem::getPlanItem($item_name, $price_plan_id,$category_id);
		$status = $order_item->adminPlanItemConfigUpdate($plan_item);
		
		$plan_item->save();
		
		return ($status) ? 'Settings Saved' : 'Settings NOT saved. Check for data errors.';
	}
	
	public function updateRequireAdmin ()
	{
		$admin = geoAdmin::getInstance();
		
		if (!$admin->isAllowed('pricing_items','update')){
			//return pre-toggle value
			return (int)$_POST['newState'] == 1 ? 0 : 1;
		}
		
		$all_items = geoOrderItem::getOrderItemTypes();
		
		$item_name = $_GET['item'];
		if (!isset($all_items[$item_name]) || !class_exists($all_items[$item_name]['class_name'])) {
			//could not find that order item!
			//return pre-toggle value
			return (int)$_POST['newState'] == 1 ? 0 : 1;
		}
		
		$plan_item = geoPlanItem::getPlanItem($item_name, (int)$_GET['price_plan_id'],(int)$_GET['category_id']);
		$newValue = (int)$_POST['newState'] == 1 ? 1 : 0;
		$plan_item->setNeedAdminApproval($newValue);
		$plan_item->save();
		return ($newValue == 1) ? 1 : 0;
	}
}