<?php 
//browse_affiliate_ads.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

class Browse_ads extends geoBrowse {
	var $subcategory_array = array();
	var $notify_data = array();
	var $debug_affiliate_browse = 0;

//########################################################################

	public function __construct ($affiliate_id,$language_id,$category_id=0,$page=0,$classified_id=0,$affiliate_group_id=0)
	{
		$db = $this->db = DataAccess::getInstance();
		if ($category_id) {
			$this->site_category = (int)$category_id;
		} else if ($classified_id) {
			$show = $this->get_classified_data($classified_id);
			$this->site_category = $show->CATEGORY;
		} else {
			$this->site_category = 0;
		}
		if (isset($limit) && $limit) {
			$this->browse_limit = (int)$limit;
		}
		
		if ($page) {
			$this->page_result = (int)$page;
			$this->affiliate_page_type = (int)$page;
		} else {
			$this->affiliate_page_type = 1;
			$this->page_result = 1;
		}
		$this->affiliate_group_id = (int)$affiliate_group_id;
		$this->affiliate_id = (int)$affiliate_id;
		
		parent::__construct();
	} 

//###########################################################

	function browse($db,$category=0,$browse_type=0)
	{
		$db = DataAccess::getInstance();
		$view = geoView::getInstance();
		$this->page_id = 3;
		$this->get_text();
		
		$this->browse_type = $browse_type;
		$order_by = $this->getOrderByString($browse_type, $this->site_category);
			

		$sql_count = "select count(id) as total from ".geoTables::classifieds_table." where seller = ? and live = 1";
		
		$sql = "select * from ".geoTables::classifieds_table." where
			live = 1 and seller = ? ".$order_by." limit ".(($this->page_result - 1) * $this->configuration_data['number_of_ads_to_display']).",".$this->configuration_data['number_of_ads_to_display'];
		

		$result = $db->Execute($sql, array($this->affiliate_id));
		if (!$result) {
			$this->browse_error();
			return false;
		}
		$total_returned = $db->GetOne($sql_count, array($this->affiliate_id));

		$this->display_browse_result($result);

		if ($this->configuration_data['number_of_ads_to_display'] < $total_returned) {
			$numPages = ceil($total_returned / $this->configuration_data['number_of_ads_to_display']);

			$cStr = $browse_type ? "&amp;c=".$browse_type : '';
			$url = $this->configuration_data['affiliate_url']."?aff=".$this->affiliate_id."&amp;a=5&amp;b=".$category.$cStr."&amp;page=".$i;
			$css = "browsing_result_page_links";
			$view->pagination = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
			$view->page_number = $this->page_result;
			$view->total_pages = $numPages;
		}

		$view->setBodyTpl('affiliate.tpl', '', 'browsing');
		$this->display_page($db);
		return true;
	}

//####################################################################################

	function display_browse_result($browse_result)
	{
		$db = DataAccess::getInstance();
		$tpl_vars = array();
		
		if ($browse_result->RecordCount() < 1) {
			$tpl_vars['no_listings'] = $this->messages[17];
		} else {
			$cfg = $listings = $headers = array();
			//use main browsing display settings for now
			$fields = $this->fields->getDisplayLocationFields('browsing');
			
			$headers['css'] = 'browsing_result_table_header';
			
			$cfg['sort_links'] = true;
			$cfg['browse_url'] = $this->configuration_data['affiliate_url']."?aff=".$this->affiliate_id."&amp;a=5&amp;b=".$this->site_category."&amp;c=";
			$cfg['listing_url'] = $this->configuration_data['affiliate_url']."?aff=".$this->affiliate_id."&amp;a=2&amp;b=";
			$tpl_vars['affiliate_id'] = $this->affiliate_id;
			
			$cfg['cols']['business_type'] = ($fields['business_type']) ? true : false;
			$headers['business_type'] = array(
				'css' => 'business_type_column_header',
				'text' => $this->messages[1262], 'label' => $this->messages[1262],		
			);
			if($this->browse_type == 43) $headers['business_type']['reorder'] = 44;
			elseif($this->browse_type == 44) $headers['business_type']['reorder'] = 0;
			else $headers['business_type']['reorder'] = 43;
			
			$cfg['cols']['image'] = ($fields['photo']) ? true : false;
			$headers['image'] = array(
				'css' => 'photo_column_header',
				'text' => $this->messages[23]
				//NO LABEL
			);
				
			$cfg['cols']['title'] = ($fields['title']) ? true : false;
			$headers['title'] = array(
				'css' => 'title_column_header',
				'text' => $this->messages[19], 'label' => $this->messages[19],
			);
			if (!$fields['title']) {
				$cfg['cols']['icons'] = (bool)$fields['icons'];
			}
			if($this->browse_type == 5) $headers['title']['reorder'] = 6;
			elseif($this->browse_type == 6) $headers['title']['reorder'] = 0;
			else $headers['title']['reorder'] = 5;
			$cfg['description_under_title'] = ($fields['description'] && $this->configuration_data['display_ad_description_where']) ? true : false;

			$cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']) ? true : false;
			$headers['description'] = array(
				'css' => 'description_column_header',
				'text' => $this->messages[21], 'label' => $this->messages[21],
			);
			
			//Listing tags column
			$cfg['cols']['tags'] = ($fields['tags'])? true : false;
			$headers['tags'] = array(
				'css' => 'tags_column_header',
				'text' => $this->messages[500875], 'label' => $this->messages[500875],
			);
								
			for ($i = 1; $i <= 20; $i++) {
				if (geoPC::is_ent() && $fields['optional_field_'.$i]) {
					$cfg['cols']['optionals'][$i] = true;
					$headers['optionals'][$i] = array(
						'css' => 'optional_field_header_'.$i,
						'text' => (($i <= 10) ? $this->messages[921+$i] : $this->messages[1685+$i]),
						'label' => (($i <= 10) ? $this->messages[921+$i] : $this->messages[1685+$i])
					);
					$browse1 = ($i <= 10) ? ( 2 * ($i-1) + 15 ) : ( 2 * ($i-11) + 45 ) ; //15, 17, 19, ... : 45, 47, 49, ...
					$browse2 = $browse1 + 1;
					if($this->browse_type == $browse1) $headers['optionals'][$i]['reorder'] = $browse2;
					elseif($this->browse_type == $browse2) $headers['optionals'][$i]['reorder'] = 0;
					else $headers['optionals'][$i]['reorder'] = $browse1;
				} else {
					$cfg['cols']['optionals'][$i] = false;
				}
			}
	
			$cfg['cols']['address'] = ($fields['address']) ? true : false;
			$headers['address'] = array(
				'css' => 'address_column_header',
				'text' => $this->messages[500167], 'label' => $this->messages[500167],
			);
			
			$cfg['cols']['city'] = ($fields['city']) ? true : false;
			$headers['city'] = array(
				'css' => 'city_column_header',
				'text' => $this->messages[1199], 'label' => $this->messages[1199],
			);
			if($this->browse_type == 35) $headers['city']['reorder'] = 36;
			elseif($this->browse_type == 36) $headers['city']['reorder'] = 0;
			else $headers['city']['reorder'] = 35;
			
			
			$cfg['cols']['location_breadcrumb'] = ($fields['location_breadcrumb']) ? true : false;
			$headers['location_breadcrumb'] = array(
				'css' => 'location_breadcrumb_column_header',
				'text' => $this->messages[501623], 'label' => $this->messages[501623],
			);
			$enabledRegions = array();
			$maxLocationDepth = 0;
			for($r = 1; $r <= geoRegion::getLowestLevel(); $r++) {
				if($fields['region_level_'.$r]) {
					$enabledRegions[] = $r;
					$maxLocationDepth = $r;
				}
			}
			$cfg['maxLocationDepth'] = $maxLocationDepth;
			foreach($enabledRegions as $level) {
				$cfg['cols']['region_level_'.$level] = true;
				$headers['region_level_'.$level] = array(
					'css' => 'region_level_'.$level.'_column_header',
					'text' => $label=geoRegion::getLabelForLevel($level),
					'label' => $label,
				);
			}
			
			$cfg['cols']['zip'] = ($fields['zip']) ? true : false;
			$headers['zip'] = array(
				'css' => 'zip_column_header',
				'text' => $this->messages[1202], 'label' => $this->messages[1202],
			);
			if($this->browse_type == 41) $headers['zip']['reorder'] = 42;
			elseif($this->browse_type == 42) $headers['zip']['reorder'] = 0;
			else $headers['zip']['reorder'] = 41;
					
			$cfg['cols']['price'] = ($fields['price']) ? true : false;
			$headers['price'] = array(
				'css' => 'price_column_header',
				'text' => $this->messages[27], 'label' => $this->messages[27],
			);
			if($this->browse_type == 1) $headers['price']['reorder'] = 2;
			elseif($this->browse_type == 2) $headers['price']['reorder'] = 0;
			else $headers['price']['reorder'] = 1;
							

			$cfg['cols']['num_bids'] = ($auction && $fields['num_bids']) ? true : false;
			$headers['num_bids'] = array(
				'css' => 'number_bids_header',
				'text' => $this->messages[103041], 'label' => $this->messages[103041],
			);
			
		
			$cfg['cols']['entry_date'] = ((!$auction && $fields['classified_start']) || ($auction && $fields['auction_start'])) ? true : false;
			$headers['entry_date'] = array(
				'css' => 'price_column_header',
				'text' => $this->messages[22], 'label' => $this->messages[22],
			);
			if($this->browse_type == 68) $headers['entry_date']['reorder'] = 67;
			elseif($this->browse_type == 67) $headers['entry_date']['reorder'] = 0;
			else $headers['entry_date']['reorder'] = 68;
			
			$cfg['cols']['time_left'] = ((!$auction && $fields['classified_time_left']) || ($auction && $fields['auction_time_left'])) ? true : false;
			$headers['time_left'] = array(
				'css' => 'price_column_header',
				'text' => $this->messages[103008], 'label' => $this->messages[103008],
			);
			if($this->browse_type == 70) $headers['time_left']['reorder'] = 69;
			elseif($this->browse_type == 69) $headers['time_left']['reorder'] = 0;
			else $headers['time_left']['reorder'] = 70;
			
			$cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL)) ? true : false;
			$headers['edit'] = array(
				'css' => 'price_column_header',
				'text' => 'edit',
				//NO LABEL
			);
			
			$cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
			$headers['delete'] = array(
				'css' => 'price_column_header',
				'text' => 'delete',
				//NO LABEL
			);

			$tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addHeader', array('this'=>$this, 'browse_fields'=>$fields), geoAddon::ARRAY_ARRAY);
			
			if ($this->configuration_data['popup_while_browsing']) {
				$cfg['popup'] = true;
				$cfg['popup_width'] = $this->configuration_data['popup_while_browsing_width'];
				$cfg['popup_height'] = $this->configuration_data['popup_while_browsing_height'];
			} else {
				$cfg['popup'] = false;
			}
			$cfg['icons'] = array(
				'sold' => (($this->messages[500798])? geoTemplate::getUrl('',$this->messages[500798]):''),
				'buy_now' => (($this->messages[500799])? geoTemplate::getUrl('',$this->messages[500799]):''),
				'reserve_met' => (($this->messages[500800])? geoTemplate::getUrl('',$this->messages[500800]):''),
				'reserve_not_met' => (($this->messages[501665])? geoTemplate::getUrl('',$this->messages[501665]):''),
				'no_reserve' => (($this->messages[500802])? geoTemplate::getUrl('',$this->messages[500802]):''),
				'verified' => (($this->messages[500952])? geoTemplate::getUrl('',$this->messages[500952]):''),
				'addon_icons' => geoAddon::triggerDisplay('use_listing_icons',null,geoAddon::BOOL_TRUE),
			);
			
			$cfg['empty'] = $this->messages[501619];
			
			$tpl_vars['cfg'] = $cfg;
			$tpl_vars['headers'] = $headers;
			
			//now set up all the listing data
			
			//common text
			$text = array(
				'business_type' => array(
					1 => $this->messages[1263],
					2 => $this->messages[1264],
				),
				'time_left' => array(
					'weeks' => $this->messages[103003],
					'days' => $this->messages[103004],
					'hours' => $this->messages[103005],
					'minutes' => $this->messages[103006],
					'seconds' => $this->messages[103007],
					'closed' => $this->messages[100051]
				)
			);
			
			while($row = $browse_result->FetchRow()) {
				$id = $row['id']; //template expects $listings to be keyed by classified id
				
				$row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);
								
				//use the common geoBrowse class to do all the common heavy lifting
				$listings[$id] = $this->commonBrowseData($row, $text, $featured);
				
				//css is different enough to not include in the common file
				$listings[$id]['css'] = 'browsing_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');
				
				//uncomment if base browse_ads addons are wanted here
				$listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addRow', array('this'=>$this,'show_classifieds' => $row), geoAddon::ARRAY_ARRAY);
			}
			$tpl_vars['listings'] = $listings;
		}
		$tpl_vars['aff'] = 1;
		geoView::getInstance()->setBodyVar($tpl_vars);
	}
	
}