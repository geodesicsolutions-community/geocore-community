<?php

/**
 * This file shows how to write server-side code for AJAX requests
 * Use this file as a template for new 
 * 
 * AJAX requests should be sent to admin/AJAX.php in the following form:
 *   admin/AJAX.php?controller=Example&action=foo&data=oof
 * 
 * This request will do the following:
 *  1. Include AJAXController/Example.php which defines the 
 * 		CLASSES_AJAXController_Example class.
 *  2. Creates an object from CLASSES_AJAXController_Example.
 *  3. Passes $GET, minus controller and action, to the 'foo' method
 * 		of CLASSES_AJAXController_Example.
 * 
 * Note on class names and directory locations:
 * The class name usually goes with the directory location, with / replaced with _, but
 * there are 2 special cases:  starting class name with CLASSES or ADMIN:
 * ADMIN: means it is a sub-directory of whatever the admin directory is.  Allows site owner
 *  to change admin directory name.
 * CLASSES: means it is a sub-directory of whatever the classes dir is.  Also allows site owner
 *  to change the classes directory name.
 * 
 * See the admin_AJAX::dispatch() function in AJAX.php for more details.
 * 
 */

// DON'T FORGET THIS
if( class_exists( 'classes_AJAX' ) or die());

class CLASSES_AJAXController_AdvancedSearch extends classes_AJAX {	
	public function getCatFields ()
	{
		$categoryID = intval($_GET['catId']);
		
		$to_display = 0;
		
		$db = DataAccess::getInstance();
		$msgs = $db->get_text(true, 44);
		
		$tpl_vars = array();
		
		$tpl_vars['category'] = $categoryID;
		
		$session = geoSession::getInstance();
		$session->initSession();
		$groupId = (int)$session->getUserId();
		
		$category_fields = geoFields::getInstance($groupId, $categoryID);
		
		if ($category_fields->getCategoryId()) {
			//if fields->getCategoryId returns other than 0, there are category-specific settings for this category (or one of its parents)
			
			$showCat = $category_fields->getDisplayLocationFields('search_fields');
			$site_fields = geoFields::getInstance($groupId, 0);
			$showSite = $site_fields->getDisplayLocationFields('search_fields');
			//if there are any site-wide fields in use here but not in default
			//we need to show them
			
			//first, figure out what leveled fields to show
			//Leveled fields
			$leveled = geoLeveledField::getInstance();
			
			$leveled_ids = $leveled->getLeveledFieldIds();
			if ($leveled_ids) {
				$tpl_vars['leveled_fields'] = array();
				$tpl_vars['leveled_clear_selection_text'] = $msgs[502065];
				foreach ($leveled_ids as $lev_id) {
					$level_1 = "leveled_{$lev_id}_1";
					if (!$showSite[$level_1] && $showCat[$level_1]) {
						$entry = array();
						//put together each of the indexes, it's easier to do in PHP
						//than in smarty
						$entry['level_1'] = $level_1;
							
						//let it know what it is
						$entry['leveled_field'] = $lev_id;
							
						$maxLevelEver = $leveled->getMaxLevel($lev_id, true);
						$maxLevel = 1;
						//can edit just applies to listing placement/editing
						$canEditLeveled = $entry['can_edit'] = true;
						$prevParent = 0;
						
						for ($i = 1; $i<=$maxLevelEver; $i++) {
							$level_i = "leveled_{$lev_id}_{$i}";
							if (!$showSite[$level_i] && $showCat[$level_i]) {
								$maxLevel = $i;
							} else {
								//we reached limit to enabled ones
								break;
							}
							//(currently) no pre-selected values
							$selected = 0;
							//populate the first level
							
							//page is always 1 starting out
							$page = 1;
							if ($i>1) {
								//Nothing past first level is going to be populated yet...
								$value_info = array(
									'values' =>array(),
									'maxPages' => 1);
							} else {
								$value_info = $leveled->getValues($lev_id,$prevParent,$selected, $page);
							}
							if ($value_info['maxPages']>1) {
								//pagination
								$pagination_url = "AJAX.php?controller=LeveledFields&amp;action=getLevel&amp;leveled_field=$lev_id&amp;parent={$prevParent}&amp;selected=0&amp;page=";
								$value_info['pagination'] = geoPagination::getHTML($value_info['maxPages'], $value_info['page'], $pagination_url,'leveled_pagination','',false,false);
							}
							$entry['levels'][$i]['can_edit']=true;
							$entry['levels'][$i]['leveled_field'] = $lev_id;
							$entry['levels'][$i]['value_info'] = $value_info;
							$prevParent=$selected;
							
							$entry['levels'][$i]['level'] = $leveled->getLevel($lev_id, $i, $db->getLanguage());
							$to_display++;
						}
						$entry['maxLevel'] = $maxLevel;
						$tpl_vars['leveled_fields'][$lev_id] = $entry;
					}
				}
			}
			$optionals = array();
							
			for ($i = 1; $i <= 20; $i++) {
				$fieldName = 'optional_field_'.$i;
				if ($showSite[$fieldName] || !$showCat[$fieldName]) {
					//do not show this one, either it is already turned on site-wide
					//or it is not turned on category specific
					continue;
				}
				
				if($i == 1) {
					$optionals[$i]['label'] = $msgs[1457];
				} else if($i <= 10) {
					$optionals[$i]['label'] = $msgs[(1458+($i-1))];
				} else if($i <= 20) {
					$optionals[$i]['label'] = $msgs[(1933+($i-11))];
				} else if($i <= 35) {
					//what the...
					$optionals[$i]['label'] = $msgs[(2778+($i-21))];
				}
				$field_type = $category_fields->$fieldName->field_type;
				if ($field_type == 'number' || $field_type == 'cost') {
					//if numbers only - produce a upper and lower limit
					$optionals[$i]['type'] = 'numbers';
				} else if ($field_type == 'date') {
					$optionals[$i]['type'] = 'date';
				} elseif ($category_fields->$fieldName->field_type != 'dropdown') {
					$optionals[$i]['type'] = 'text';
				} else {
					$optionals[$i]['type'] = 'select';
					$query = "select * from geodesic_classifieds_sell_question_choices where type_id = ".((int)$category_fields->$fieldName->type_data)." order by display_order,value";
					$type_result = $db->Execute($query);
					if (!$type_result) {
						trigger_error("ERROR SQL: query: $query error: ".$db->ErrorMsg());
						return false;
					}
					if ($type_result->RecordCount() > 0) {
						$dropdownOptions = array();
						$dropdownOptions[0]['value'] = "";
						$matched = 0;
						for ($o = 1; $show_dropdown = $type_result->FetchRow(); $o++) {
							$dropdownOptions[$o]['value'] = $show_dropdown['value'];
							if ($this->classified_variables["optional_field_".$i] == $show_dropdown['value']) {
								$dropdownOptions[$o]['selected'] = true;
								$matched = 1;
							}
						}
						$optionals[$i]['dropdown'] = $dropdownOptions;
					} else {
						//blank text box
						$optionals[$i]['type'] = 'text';
					}
				}
				if (strpos($category_fields->$fieldName->type_data, ':use_other')!== false && intval($category_fields->$fieldName->type_data)) {
					$optionals[$i]['other_box'] = true;
				}
				$to_display++;
			}
			$tpl_vars['optionals'] = $optionals;			
		}
		//get the category question details...
		$catQuestions = $this->getCatQuestions($categoryID);
		
		//spit out the questions
		if ($catQuestions) {
			$to_display += count($catQuestions);
			$tpl_vars['questions'] = $catQuestions;
		}
		
		$tpl_vars['addonCriteria'] = geoAddon::triggerDisplay('Search_classifieds_search_form', array ('category_id'=> $categoryID, 'search_fields'=>$category_fields), geoAddon::ARRAY_ARRAY);
		
		if (!$to_display && !$tpl_vars['addonCriteria']) {
			//nothing to display
			return;
		}
		$tpl = new geoTemplate('system','other');
		$tpl->assign($tpl_vars);
		echo $tpl->fetch('ajax_searchQuestions.tpl');
	}
	
	private function getCatQuestions($category_id=0)
	{
		//sanitize input.
		$category_id = intval($category_id);
		
		$db = DataAccess::getInstance();
		
		$category = array();
		
		$typeMap = array (
			'none'=>'text',
			'number'=>'numbers',
			'url'=>'text',
			'textarea'=>'text',
			);
		
		//here, we get the categories to check from the bottom-up, but ultimately want to display them from the top-down
		//so put the cat IDs into a reversable array, then reverse it and use it to pull the actual data
		while ($category_id != 0) {
			//figure out parent categories
			$categories[] = $category_id;
			$query = "SELECT parent_id FROM ".geoTables::categories_table." WHERE category_id = $category_id";
			$category_id = (int)$db->GetOne($query);
		}
		$categories = array_reverse($categories);
		
		foreach($categories as $c) {
			//get the questions for this category
			$query = "SELECT lang.name,lang.choices,lang.question_id, cat.other_input, lang.search_as_numbers FROM ".geoTables::questions_table." as cat,
				".geoTables::questions_languages." as lang
				WHERE cat.category_id = '$c' AND cat.question_id=lang.question_id
					AND lang.language_id = ".$db->getLanguage()."
				ORDER BY display_order";
			
			$result = $db->Execute($query);
			if (!$result) {
				trigger_error("ERROR SQL: $query ERror: ".$db->ErrorMsg());
				return false;
			}
			foreach ($result as $row) {
				if (isset($questions[$row['question_id']])) {
					//we already retrieved this one!
					continue;
				}
				$row['key'] = $row['question_id'];
				$row['label'] = $row['name'];
				if (isset($typeMap[$row['choices']])) {
					$type = $typeMap[$row['choices']];
				} else {
					$type = $row['choices'];
				}
				if (is_numeric($type)) {					
					//get the choices!
					$type = (int)$type;
					$query = "SELECT `value` FROM ".geoTables::sell_choices_table." WHERE type_id = '{$type}' order by display_order,value";
					$select_resuts = $db->Execute($query);
					if (!$select_resuts) {
						trigger_error("ERROR SQL: $query ERror: ".$db->ErrorMsg());
						return false;
					}
					if ($select_resuts->RecordCount() > 0) {
						$type = 'select';
						
						$opts = array();
						//no leading, blank option here
						//we will only add if there is not already one.
						$blankInserted = false;
						foreach ($select_resuts as $options) {
							if (strlen(trim($options['value'])) == 0) {
								$blankInserted = true;
							} else if (!$blankInserted) {
								//automatically add blank option as first option, but ONLY if there
								//is not already another blank option at the top of the list.
								//If there is NO blank option, then user is forced to always search
								//for a specific thing.
								$blankInserted = true;
								$opts[] = "";
							}
							$opts[] = $options['value'];
						}
						$row['options'] = $opts;
						if ($row['other_input'] == 1) {
							$row['other'] = true;
						}
					}
				}
				$row['type'] = $type;
				$category[$row['question_id']] = $row;
			}
		}
		return $category;
	}
}