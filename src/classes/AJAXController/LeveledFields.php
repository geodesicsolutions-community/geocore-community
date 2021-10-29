<?php

/**
* Creates the next-level region for geographic selectors.
* Call with parent=0 for level 1
* prints nothing if parent has no children
*/

// DON'T FORGET THIS
if( class_exists( 'classes_AJAX' ) or die());

class CLASSES_AJAXController_LeveledFields extends classes_AJAX
{
	public function getLevel ()
	{
		$leveled_field = $_GET['leveled_field'];
		if ($leveled_field !== 'cat') {
			//if it is not "special case" of being a category selection...  it needs to be an int
			$leveled_field = (int)$leveled_field;
		}
		$parent = (int)$_GET['parent'];
		$page = (int)max(1, $_GET['page']);
		$selected = (int)$_GET['selected'];
		$showClearSelection = (bool)$_GET['showClearSelection'];
		//just so it's a number in URL
		$showClearSelection = (int)$showClearSelection;
		$isCat = (isset($_GET['cat']) && $_GET['cat']);
		
		$inAdmin = (isset($_GET['inAdmin']) && $_GET['inAdmin']);
		
		$tpl_vars = array();
		
		$leveled = geoLeveledField::getInstance();
		
		if (!$isCat && $parent && !$leveled_field) {
			//get rest of info based on parent
			$pInfo = $leveled->getValueInfo($parent);
			if (!$pInfo) {
				//failsafe...
				echo 'Err: Invalid Value';
				return;
			}
			
			$page = 1;
			$leveled_field = (int)$pInfo['leveled_field'];
		} else if ($isCat && !$leveled_field) {
			$leveled_field = 'cat';
		}
		
		if($isCat && $_GET['searchcat'] == 1) {
			$tpl_vars['leveledCatSearch'] = true;
		}
		
		$info = $lev_field = array();
		if ($isCat) {
			$listing_types_allowed = (isset($_GET['listing_types_allowed']))? (int)$_GET['listing_types_allowed'] : 0;
			$recurringClassPricePlan = (isset($_GET['recurringpp']))? (int)$_GET['recurringpp'] : false;
			$info['value_info'] = geoCategory::getCategoryLeveledValues($parent, $listing_types_allowed, $selected, $page, null, null, $recurringClassPricePlan);
		} else {
			$info['value_info'] = $leveled->getValues($leveled_field, $parent, $selected, $page);
		}
		if (!count($info['value_info']['values'])) {
			//no values...
			return;
		}
		if ($info['value_info']['maxPages']>1) {
			//pagination
			$pagination_url = (($inAdmin)? '../':'')."AJAX.php?controller=LeveledFields&amp;action=getLevel&amp;leveled_field=$leveled_field&amp;parent=$parent&amp;selected=$selected&amp;showClearSelection=$showClearSelection";
			if ($isCat) {
				$pagination_url .= "&amp;cat=1&amp;listing_types_allowed=$listing_types_allowed";
				if($recurringClassPricePlan) {
					$pagination_url .= "&amp;recurringpp=$recurringClassPricePlan";
				}
			}
			if ($inAdmin) {
				$pagination_url .= '&amp;inAdmin=1';
			}
			$pagination_url .= "&amp;page=";
			$info['value_info']['pagination'] = geoPagination::getHTML($info['value_info']['maxPages'], $page, $pagination_url,'leveled_pagination','',false,false);
		}
		
		$info['page'] = $page;
		
		if ($isCat) {
			$info['level'] = array('level'=>$info['value_info']['level']);
		} else {
			$info['level'] = $leveled->getLevel($leveled_field,$info['value_info']['level'],DataAccess::getInstance()->getLanguage());
		}
		$lev_field['leveled_field'] = $leveled_field;
		//this is ajax call, it only happens when it can be edited...
		$lev_field['can_edit'] = true;
		if ($showClearSelection) {
			//populate the clear selection text
			$msgs = DataAccess::getInstance()->get_text(true, 44);
			$tpl_vars['leveled_clear_selection_text'] = $msgs[502065];
		} else {
			$tpl_vars['leveled_clear_selection_text'] = '';
		}
		$tpl_vars['info'] = $info;
		$tpl_vars['lev_field'] = $lev_field;
		$tpl_vars['is_ajax'] = true;
		
		$tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
		$tpl->assign($tpl_vars);
		$tpl->display('shared/leveled_fields/level.tpl');
	}
}
