<?php
//addons/price_drop_auctions/admin.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    16.09.0-6-g63299ef
## 
##################################


require_once ADDON_DIR.'price_drop_auctions/info.php';

class addon_price_drop_auctions_admin extends addon_price_drop_auctions_info
{
	
	public function init_pages ()
	{
		menu_page::addonAddPage('price_drop_config', '', 'Configuration', $this->name);
	}
	
	public function init_text ($languageId)
	{
		$return = array
		(
			'listing_placement_section_header' => array (
				'name' => 'Listing Placement Section Header',
				'desc' => '',
				'type' => 'input',
				'default' => 'Automatic Price Dropping'
			),
			'section_header_tooltip' => array (
					'name' => 'Section Header Tooltip',
					'desc' => 'Add text here to create a popup help link in the Price Drop section header',
					'type' => 'textarea',
					'default' => ''
			),
			'listing_placement_activate_label' => array (
					'name' => 'Listing Placement Activate Label',
					'desc' => '',
					'type' => 'input',
					'default' => 'Activate:'
			),
			'listing_placement_activate_desc' => array (
					'name' => 'Listing Placement Activate Description',
					'desc' => '',
					'type' => 'input',
					'default' => 'Automatically lower Buy Now price over time, if auction is not purchased'
			),
			'listing_placement_minimum_price_label' => array (
					'name' => 'Listing Placement Minimum Price Label',
					'desc' => '',
					'type' => 'input',
					'default' => 'Minimum Price: '
			),
			
		);
		
		return $return;
	}

	public function display_price_drop_config()
	{		
		$tpl_vars = array();
		$tpl_vars['admin_msgs'] = geoAdmin::m();
		
		$reg = geoAddon::getRegistry($this->name);
		$tpl_vars['delay_low'] = $reg->delay_low;
		$tpl_vars['delay_high'] = $reg->delay_high;
		$tpl_vars['drop_amount_low'] = $reg->drop_amount_low;
		$tpl_vars['drop_amount_high'] = $reg->drop_amount_high;
		$tpl_vars['drop_amount_static'] = $reg->drop_amount_static;
		
		 
		geoView::getInstance()->setBodyTpl('admin/config.tpl',$this->name)->setBodyVar($tpl_vars);
	}
	
	public function update_price_drop_config()
	{
		$reg = geoAddon::getRegistry($this->name);
		$delLow = (float)$_POST['delay_low'];
		$delHigh = (float)$_POST['delay_high'];
		$amtLow = (float)$_POST['drop_amount_low'];
		$amtHigh = (float)$_POST['drop_amount_high'];
		$amtStatic = $_POST['drop_amount_static'] ? 1 : 0;
		if($delLow > $delHigh) {
			geoAdmin::m('Delay upper bound must be greater than or equal to lower bound',geoAdmin::ERROR);
			return false;
		}
		if(!$amtStatic && $amtLow > $amtHigh) {
			geoAdmin::m('Drop amount upper bound must be greater than or equal to lower bound',geoAdmin::ERROR);
			return false;
		}
		if(!$amtStatic && $amtLow < 0 || $amtHigh < 0 || $amtLow > 100 || $amtHigh > 100) {
			geoAdmin::m('Drop amount out of bounds. Must be 0-100',geoAdmin::ERROR);
			return false;
		}
		$reg->delay_low = $delLow;
		$reg->delay_high = $delHigh;
		$reg->drop_amount_low = $amtLow;
		$reg->drop_amount_high = $amtHigh;
		$reg->drop_amount_static = $amtStatic;
		$reg->save();
		return true;
	}
	
	
}