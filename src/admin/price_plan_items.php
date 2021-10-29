<?php
// price_plan_items.php
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
##    16.09.0-79-gb63e5d8
## 
##################################

class PricePlanItemManage {
	public function getConfig ($item, $price_plan, $category ) {
		return "<a id='configure' href='javascript:void(0);' onclick=\"configureItem('$item','$price_plan', '$category')\" class=\"mini_button\">Configure</a>";
	}
	
	public function display_pricing_items ($price_plan = null, $category = null, $preSelected = null)
	{
		//get the price plan
		if ($price_plan === null) {
			$price_plan = $_GET['price_plan'];
		}
		
		//what category this is for
		if ($category === null) {
			$category = $_GET['category'];
		}
		
		if ($preSelected === null) {
			$preSelected = trim($_GET['planItem']);
		}
		
		//get item type, to hide irrelevant items
		$db = DataAccess::getInstance();
		$applies_to = $db->GetOne("SELECT `applies_to` FROM ".geoTables::price_plans_table." WHERE `price_plan_id` = ?", array($price_plan));
		
		$price_plan = intval($price_plan);
		$category = intval($category);
		$cjax = geoCJAX::getInstance();
		$admin = geoAdmin::getInstance();
		if (!$price_plan) {
			$admin->userError('No price plan specified!');
			$admin->v()->addBody($admin->getUserMessages());
			return;
		}
		$items = array();
		if ($category && !geoPlanItem::useCatSpecificPlan($price_plan, $category)) {
			//TODO:  Make it so if this is first time and no settings are set yet,
			//it gets some default settings.
			$saveMe = true;
		} else {
			$saveMe = false;
			
			$this->_initPlanItems($price_plan, $category);
			
			$plan_items = geoPlanItem::getPlanItems($price_plan,$category);
			
			foreach ($plan_items as $item) {
				$itemName = $item->getOrderItem();
				if($applies_to == 1) {
					//this is a classified price plan -- skip auction items
					if(in_array($itemName, array('auction','auction_final_fees','auction_final_fees_tableDisplay'))) {
						continue;
					}
				} elseif($applies_to == 2) {
					//this is an auction price plan -- skip classified items
					if(in_array($itemName, array('classified','classified_recurring'))) {
						continue;
					}
				}
				$class_name = $itemName.'OrderItem';
				if (!class_exists($class_name,0)){
					continue;
				}
				
				$order_item = Singleton::getInstance($class_name);
				if (!$order_item->displayInAdmin()) {
					continue;
				}
				if (method_exists($order_item,'getTypeTitle')){
					$title = $order_item->getTypeTitle();
				} else {
					$title = ucwords(str_replace('_',' ',$item->getOrderItem()));
				}
				if (method_exists($order_item,'adminPlanItemConfigDisplay')) {
					$id = $item->getOrderItem();
					$config_button = "<div id='update_config_$id'>".$this->getConfig($id, $item->getPricePlan(), $item->getCategory())."</div>";
				} else {
					$config_button = '';
				}
				if (!$config_button && count(geoOrderItem::getParentTypesFor($order_item->getType())) > 0) {
					//no configure button, no require admin approve box, no need to display
					continue;
				}
				$url_requireToggle = 'AJAX.php?controller=price_plan_items&action=updateRequireAdmin&item='.$item->getOrderItem().'&price_plan_id='.$item->getPricePlan().'&category_id='.$item->getCategory();
				$items[$item->getOrderItem()] = array (
					'title' => $title,
					'parents' => geoOrderItem::getParentTypesFor($order_item->getType()),
					'admin_approve' => $item->needAdminApproval(),
					'config_button' => $config_button,
					'url_requireToggle' => $url_requireToggle
				);
			}
		}
		$tpl = new geoTemplate('admin');
		$tpl->assign('plan_items', $items);
		$tpl->assign('saveMe',$saveMe);
		$admin->v()->addBody($tpl->fetch('price_plans/items'));
		
		if ($preSelected) {
			$admin->v()->addTop("
			<script type='text/javascript'>
				Event.observe(window,'load',function () {
					//auto click on certain configuration for certain item
					configureItem('$preSelected','$price_plan', '$category');
				});
			</script>");
		}
		
		//Once all settings are moved over to use plan item settings, use below instead to display
		//at top of page.
		//$admin->v()->plan_items = $items;
		//$admin->setBodyTpl('price_plans/items');
		
		$admin->v()->addTop($cjax->init())
			->addJScript('js/price_plan_items.js')
			->addCssFile('css/order_items.css');
	}
	/**
	 * Ensures that settings for the specified price plan and category are set, and if they are not,
	 * it adds new settings for them.  If no price plan is specified, it ensures that the settings for
	 * all price plans are set.
	 *
	 * @param int $price_plan
	 * @param int $category_id
	 */
	private function _initPlanItems($price_plan = 0, $category_id = 0)
	{
		$db = DataAccess::getInstance();
		$v = geoView::getInstance();
		if (!$price_plan) {
			//first, get all the price plans
			$sql = "SELECT `price_plan_id` FROM ".geoTables::price_plans_table;
			$price_plans = $db->GetAll($sql);
			if ($price_plans === false) {
				trigger_error('ERROR SQL: query: '.$sql.' Error msg: '.$db->ErrorMsg());
				return;
			}
		} else {
			$price_plans = array (array('price_plan_id' => $price_plan));
		}
		//now, get all the order items
		$order_items = geoOrderItem::getOrderItemTypes();
		foreach ($price_plans as $row) {
			$pp_id = $row['price_plan_id'];
			foreach ($order_items as $item_name => $item_settings) {
				
				$planItem = geoPlanItem::getPlanItem($item_name,$pp_id,$category_id, true);
				//force it!
				if ($planItem->getCategory() != $category_id) {
					$planItem->setCategory($category_id);
				}
				$planItem->save();
			}
		}
	}
}