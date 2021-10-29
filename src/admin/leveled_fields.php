<?php
//admin/leveled_fields.php
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
##    7.2.5-4-g851c0a0
## 
##################################

class LeveledFieldsManage
{
	public function display_leveled_fields ()
	{
		$view = geoView::getInstance();
		$db = DataAccess::getInstance();
		
		$tpl_vars = array();
		
		$rows = $db->Execute("SELECT * FROM ".geoTables::leveled_fields);
		
		$fields = array();
		foreach ($rows as $row) {
			$row['label'] = geoString::fromDB($row['label']);
			//get number of levels
			$row['max_level'] = (int)$db->GetOne("SELECT `level` FROM ".geoTables::leveled_field_value."
					WHERE `leveled_field`=? ORDER BY `level` DESC", array($row['id']));
			//make sure it is at least 1
			$row['max_level'] = max(1,$row['max_level']);
			
			//find number of values
			$row['value_count'] = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value."
					WHERE `leveled_field`=?", array($row['id']));
			
			$fields[] = $row;
		}
		$tpl_vars['fields'] = $fields;
		$tpl_vars['adminMsgs'] = geoAdmin::m();
		
		$this->_addCssJs();
		$view->setBodyTpl('leveled_fields/list_fields.tpl')
			->setBodyVar($tpl_vars);
	}
	
	public function display_leveled_fields_add ()
	{
		return $this->display_leveled_fields();
	}
	
	public function update_leveled_fields_add ()
	{
		$db = DataAccess::getInstance();
		
		$label = trim($_POST['new_label']);
		if (!$label) {
			geoAdmin::m('Field label is required to add a new leveled field.',geoAdmin::ERROR);
			return false;
		}
		$label = geoString::toDB($label);
		//make sure not already a matching label
		$count = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_fields." WHERE `label`=?",array($label));
		if ($count>0) {
			geoAdmin::m('That field label already exists!',geoAdmin::ERROR);
			return false;
		}
		
		//insert it
		$result = $db->Execute("INSERT INTO ".geoTables::leveled_fields." SET `label`=?",array($label));
		if (!$result) {
			geoAdmin::m('DB error attempting to set label.  DB error message: '.$db->ErrorMsg(),geoAdmin::ERROR);
			return false;
		}
		return true;
	}
	
	public function display_leveled_field_edit ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_fields();
		}
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		$tpl_vars = array();
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		$tpl->assign($tpl_vars);
		$tpl->display('leveled_fields/edit_field.tpl');
		geoView::getInstance()->setRendered(true);
	}
	
	public function update_leveled_field_edit ()
	{
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		$label = trim($_POST['label']);
		
		if (!$label) {
			geoAdmin::m('Error: label cannot be blank.',geoAdmin::ERROR);
			return false;
		}
		$label = geoString::toDB($label);
		$db->Execute("UPDATE ".geoTables::leveled_fields." SET `label`=? WHERE `id`=?",
				array($label, $leveled_field));
		return true;
	}
	
	public function display_leveled_fields_delete ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_fields();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		if (!$leveled_field) {
			geoAdmin::m('Invalid selection to delete.',geoAdmin::ERROR);
			return $this->ajaxError();
		}
		//count how many values there are...
		$tpl_vars['value_count'] = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value." WHERE
				`leveled_field`=?",array($leveled_field));
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		//ajax call, just display template
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/deleteGroup.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_fields_delete ()
	{
		if (!isset($_POST['really']) || $_POST['really']!=='yes') {
			geoAdmin::m("OK, nothing done since you may not be looking at what you are clicking on.  (You almost just deleted an entire group of multi-level fields and all values in them without realizing it!)",geoAdmin::NOTICE);
			return false;
		}
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		geoLeveledField::remove($leveled_field);
		geoAdmin::m('Leveled field removed.');
		return true;
	}
	
	public function display_leveled_field_values ()
	{
		$view = geoView::getInstance();
		$db = DataAccess::getInstance();
		
		$lField = geoLeveledField::getInstance();
		
		$tpl_vars = array();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		if (!$leveled_field) {
			geoAdmin::m('Invalid field specified.',geoAdmin::ERROR);
			return $this->display_leveled_fields();
		}
		
		//the parent leveled field to show..
		$parent = (int)$_GET['parent'];
		
		//verify it is valid...
		$parent = $this->validValueId($parent);
		
		$tpl_vars = array();
		
		$perPage = $db->get_site_setting('leveled_max_vals_per_page');
		if (!$perPage) {
			//just a failsafe... default to 100
			$perPage = 100;
		}
		
		$allPages = (isset($_GET['p']) && $_GET['p']==='all');
		$page = (isset($_GET['p']))? $_GET['p'] : 1;
		
		$total_count = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value." WHERE parent=? AND `leveled_field`=?",array($parent, $leveled_field));
		
		$maxPages = ceil($total_count/$perPage);
		
		if ($page > $maxPages||$page<1) {
			$page = 1;
		}
		if ($maxPages > 1 && $allPages) {
			//use all for current page
			$page = 'all';
		}
		$tpl_vars['page'] = $page;
		
		if ($maxPages > 1) {
			$tpl_vars['pagination'] = geoPagination::getHTML($maxPages, $page, 'index.php?page=leveled_field_values&amp;parent='.$parent.'&amp;leveled_field='.$leveled_field.'&amp;p=','','',true,false);
		}
		
		$limit = ($page==='all')? '' : 'LIMIT '.(($page-1)*$perPage).', '.$perPage;
		
		$values = $db->Execute("SELECT * FROM ".geoTables::leveled_field_value." v, ".geoTables::leveled_field_value_languages." l WHERE l.id=v.id AND l.language_id=1 AND v.parent=? AND v.leveled_field=? ORDER BY v.display_order, l.name $limit",array($parent, $leveled_field));
		
		foreach ($values as $row) {
			//get listing count
			$row['listing_count'] = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::listing_leveled_fields." WHERE `leveled_field`=? AND `field_value`=?", array($leveled_field, $row['id']));
			$tpl_vars['values'][] = $row;
		}
		
		$tpl_vars['parent'] = $parent;
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent,true);
		}
		if ($parent) {
			$info = $lField->getValueInfo($parent);
			$tpl_vars['level'] = $info['level']+1;
			$tpl_vars['parent_name'] = $info['name'];
		} else {
			$tpl_vars['level'] = 1;
		}
		
		$tpl_vars['levels'] = $lField->getLevels($leveled_field);
		$tpl_vars['levelInfo'] = (isset($tpl_vars['levels'][$tpl_vars['level']]))? $tpl_vars['levels'][$tpl_vars['level']] : array();
		
		
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		$tpl_vars['adminMsgs'] = geoAdmin::m();
		$this->_addCssJs();
		$view->setBodyTpl('leveled_fields/list_values.tpl')
			->setBodyVar($tpl_vars);
	}
	
	public function display_leveled_field_value_create ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		//the parent value to show..
		$parent = (int)$_GET['parent'];
		$leveled_field = (int)$_GET['leveled_field'];
		
		//verify it is valid...
		$parent = $this->validValueId($parent);
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		$tpl_vars = array();
		
		//get all the languages:
		$tpl_vars['languages'] = $this->getLanguages();
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		
		//ajax call, just display template
		$tpl_vars['is_ajax'] = true;
		$tpl_vars['parent'] = $parent;
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent, true);
		}
		$tpl_vars['value'] = array(
				'name'=>'',
				'display_order'=>'1',
				'level' => $level, 'parent' => $parent
				);
		$tpl_vars['new'] = true;
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/edit_value.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_create ()
	{
		$parent = (int)$_GET['parent'];
		$leveled_field = (int)$_GET['leveled_field'];
		$parent = $this->validValueId($parent);
		
		if (!$leveled_field) {
			geoAdmin::m('Invalid leveled field specified!',geoAdmin::ERROR);
			return false;
		}
		
		$lField = geoLeveledField::getInstance();
	
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
	
		$db = DataAccess::getInstance();
		$langs = $this->getLanguages();
		
		//find out if this level already exists. If not, create it.
		$levelExists = $db->GetOne("SELECT `level` FROM ".geoTables::leveled_field_level." WHERE `level` = ? AND `leveled_field`=?", array($level, $leveled_field));
		if (!$levelExists) {
			$insert = $db->Execute("INSERT INTO ".geoTables::leveled_field_level." (level, leveled_field, always_show) VALUES (?, ?, ?)", array($level, $leveled_field, 'yes'));
			if (!$insert) {
				geoAdmin::m('Failed to insert level due to database error: '.$db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
			foreach ($langs as $lang) {
				$insert = $db->Execute("INSERT INTO ".geoTables::leveled_field_level_labels." (level, leveled_field, language_id) VALUES (?, ?, ?)", array($level, $leveled_field, $lang['language_id']));
				if (!$insert) {
					geoAdmin::m('Failed to insert level due to database error on language'.$lang['language_id'].': '.$db->ErrorMsg(), geoAdmin::ERROR);
					return false;
				}
			}
		}
		
		$display_order = (int)$_POST['display_order'];
		
		$enabled = (isset($_POST['enabled']) && $_POST['enabled'])? 'yes' : 'no';
		
		//insert it in there
		$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_value." SET `leveled_field`=?, `parent`=?, `level`=?, `enabled`=?, `display_order`=?",
				array($leveled_field, $parent, $level, $enabled, $display_order));
	
		if (!$result) {
			geoAdmin::m("DB error when attempting to add new value: ".$db->ErrorMsg(),geoAdmin::ERROR);
			return false;
		}
	
		$value_id = $db->Insert_Id();
	
		foreach ($langs as $lang) {
			$name = trim($_POST['name'][$lang['language_id']]);
			$name = geoString::toDB($name);
			$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_value_languages." SET `id`=?, `language_id`=?, `name`=?", array($value_id, $lang['language_id'], $name));
			if (!$result) {
				geoAdmin::m("DB error (2) when attempting to add new value: ".$db->ErrorMsg(),geoAdmin::ERROR);
				return false;
			}
		}
		return true;
	}
	
	public function display_leveled_field_value_edit ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
	
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
	
		//the parent value to show..
		$value_id = (int)$_GET['value'];
		$leveled_field = (int)$_GET['leveled_field'];
		
		if (!$value_id) {
			$admin->message("Error: Invalid value specified.", geoAdmin::ERROR);
			return $this->ajaxError();
		}
		
		$valueInfo = $lField->getValueInfo($value_id);
		if (!$valueInfo) {
			$admin->message("Error: Value not found.",geoAdmin::ERROR);
			return $this->ajaxError();
		}
		
		$tpl_vars = array();
		
		//get all the languages:
		$tpl_vars['languages'] = $this->getLanguages();
		
		$tpl_vars['value'] = $valueInfo;
		$tpl_vars['level'] = $lField->getLevel($leveled_field, $valueInfo['level']);
		$tpl_vars['parent'] = $valueInfo['parent'];
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		$page = 1;
		if (isset($_GET['p'])) {
			$page = ($_GET['p']==='all')? 'all' : (int)$_GET['p'];
		}
		$tpl_vars['page'] = $page;
		
		$names = array ();
		$rows = $db->Execute("SELECT * FROM ".geoTables::leveled_field_value_languages." WHERE `id`=? ORDER BY `language_id`",array($value_id));
		
		foreach ($rows as $row) {
			$names[$row['language_id']] = geoString::fromDB($row['name']);
		}
		$tpl_vars['names'] = $names;
		$tpl_vars['parents'] = $lField->getParents($value_id);
		
		$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
		$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
		
		//ajax call, just display template
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/edit_value.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_edit ()
	{
		$value_id = (int)$_GET['value'];
		if (!$value_id) {
			geoAdmin::m('Invalid value to edit!',geoAdmin::ERROR);
			return false;
		}
		
		$lField = geoLeveledField::getInstance();
		
		$valueInfo = $lField->getValueInfo($value_id);
		$parent = $valueInfo['parent'];
		$leveled_field = (int)$_GET['leveled_field'];
		
		//always re-calculate level...  at some point we might add "skiping levels" but not yet.
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		
		$db = DataAccess::getInstance();
		
		$names = $_POST['name'];
		foreach ($names as $name) {
			if (!trim($name)) {
				geoAdmin::m('Error:  Value name is required for all languages.',geoAdmin::ERROR);
				return false;
			}
		}
		
		$display_order = (int)$_POST['display_order'];
		
		$enabled = (isset($_POST['enabled']) && $_POST['enabled'])? 'yes' : 'no';
		
		//insert it in there
		$result = $db->Execute("UPDATE ".geoTables::leveled_field_value." SET `level`=?, `enabled`=?, `display_order`=?
				WHERE `id`=?",
				array($level, $enabled, $display_order, $value_id));
		
		if (!$result) {
			geoAdmin::m("DB error when attempting to edit value: ".$db->ErrorMsg(),geoAdmin::ERROR);
			return false;
		}
		
		$langs = $this->getLanguages();
		//Clear current ones...
		$db->Execute("DELETE FROM ".geoTables::leveled_field_value_languages." WHERE `id`=?",array($value_id));
		//now re-add them all
		foreach ($langs as $lang) {
			$name = trim($names[$lang['language_id']]);
			$name = geoString::toDB($name);
			$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_value_languages." SET `id`=?, `language_id`=?, `name`=?", array($value_id, $lang['language_id'], $name));
			if (!$result) {
				geoAdmin::m("DB error when attempting to add value name for language {$lang['language_id']}, db error: ".$db->ErrorMsg(),geoAdmin::ERROR);
				return false;
			}
		}
		return true;
	}
	
	public function display_leveled_field_value_create_bulk ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		//the parent value to show..
		$parent = (int)$_GET['parent'];
		$leveled_field = (int)$_GET['leveled_field'];
		
		//verify it is valid...
		$parent = $this->validValueId($parent);
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		$tpl_vars = array();
		
		//ajax call, just display template
		$tpl_vars['parent'] = $parent;
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent, true);
		}
		$tpl_vars['level'] = $level;
		$tpl_vars['parent_info'] = $parent_info;
		$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
		$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		//figure out what to use as the display order...
		$display_order = (int)$db->GetOne("SELECT MAX(`display_order`) FROM ".geoTables::leveled_field_value." WHERE `leveled_field`=? AND `parent`=?", array($leveled_field, $parent));
		if ($display_order > 0) {
			//add one to the order
			$display_order++;
		} else {
			//no display orders yet, or the max is 1...  in which case they may wish
			//to make all of them 1
			$display_order = 1;
		}
		$tpl_vars['display_order'] = $display_order;
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/bulk.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_create_bulk ()
	{
		$parent = (int)$_GET['parent'];
		$parent = $this->validValueId($parent);
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		if (!$leveled_field) {
			//just a failsafe
			geoAdmin::m('Error: invalid leveled field group specified, cannot add.',geoAdmin::ERROR);
			return false;
		}
		
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		
		$db = DataAccess::getInstance();
		$langs = $this->getLanguages();
		
		//find out if this level already exists. If not, create it.
		$levelExists = $db->GetOne("SELECT `level` FROM ".geoTables::leveled_field_level." WHERE `level` = ? AND `leveled_field`=?", array($level, $leveled_field));
		if(!$levelExists) {
			$insert = $db->Execute("INSERT INTO ".geoTables::leveled_field_level." (level, leveled_field, always_show) VALUES (?, ?, ?)", array($level, $leveled_field, 'yes'));
			if(!$insert) {
				geoAdmin::m('Failed to insert level due to database error: '.$db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
			foreach($langs as $lang) {
				$insert = $db->Execute("INSERT INTO ".geoTables::leveled_field_level_labels." (level, leveled_field, language_id) VALUES (?, ?, ?)", array($level, $leveled_field, $lang['language_id']));
				if(!$insert) {
					geoAdmin::m('Failed to insert level due to database error on language'.$lang['language_id'].': '.$db->ErrorMsg(), geoAdmin::ERROR);
					return false;
				}
			}
		}
		
		
		if (isset($_POST['undo']) && $_POST['undo']) {
			$min_value_id = (int)$_POST['min_value_id'];
			$max_value_id = (int)$_POST['max_value_id'];
			
			if (!$min_value_id || !$max_value_id) {
				geoAdmin::m("Error, invalid min/max value when attempting to undo bulk add.",geoAdmin::ERROR);
				return false;
			}
			//undo (remove) values in range
			$result = $db->Execute("DELETE FROM ".geoTables::leveled_field_value." WHERE `id`>=? AND `id`<=?", array($min_value_id, $max_value_id));
			if (!$result) {
				geoAdmin::m("Error attempting to remove values, DB error: ".$db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
			$result = $db->Execute("DELETE FROM ".geoTables::leveled_field_value_languages." WHERE `id`>=? AND `id`<=?", array($min_value_id, $max_value_id));
			if (!$result) {
				geoAdmin::m("Error attempting to remove value languages, DB error: ".$db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
			//reset increment so that if someone adds thousands of values and un-does several times,
			//it won't end up taking all the available slots...
			$result = $db->Execute("ALTER TABLE ".geoTables::leveled_field_value." AUTO_INCREMENT=1");
			if (!$result) {
				geoAdmin::m("DB Error attempting to reset AUTO_INCREMENT, DB error: ".$db->ErrorMsg(), geoAdmin::ERROR);
			}
			geoAdmin::m('Undo successful, removed the values that were just added.');
			return true;
		}
		
		$display_order = (int)$_POST['display_order'];
		$inc_order = ($_POST['display_order_type']==='inc');
		
		$enabled = (isset($_POST['enabled']) && $_POST['enabled'])? 'yes' : 'no';
		
		$names = $_POST['names'];
		//we accept either comma, newline, or tab for delimiters...  so convert all to commas
		$names = preg_replace('/[\n\r\t]+/',', ',$names);
		
		//now split it up
		$names = explode(',', $names);
		if (!count($names)) {
			geoAdmin::m('No names entered to bulk-add.',geoAdmin::ERROR);
			return false;
		}
		$count = 0;
		
		//keep track of names added to prevent adding multiples...
		$duplicates = 0;
		$max_value_id = 0;
		//make sure it doesn't time out
		set_time_limit(0);
		foreach ($names as $name) {
			//clean up name, remove extra space, along with quotes if they surrounded each with quote...
			$name = trim($name, ' "');
			
			if (strlen($name)<=1) {
				//nothing to this name...  Either blank or just a single character
				//NOTE: we skip single characters to make easier to copy/paste from
				//lists that might contain alphabetic headers to seperate by letter.
				continue;
			}
			//check for duplicates
			$dup_count = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value_languages." l,
					".geoTables::leveled_field_value." r
					WHERE l.id=r.id AND r.parent=? AND r.`leveled_field`=? AND l.`name`=?",array($parent, $leveled_field, urlencode($name)));
			if ($dup_count > 0) {
				$duplicates++;
				continue;
			}
			
			
			//insert it in there
			$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_value." SET `leveled_field`=?, `parent`=?, `level`=?, `enabled`=?, `display_order`=?",
					array($leveled_field, $parent, $level, $enabled, $display_order));
			
			if (!$result) {
				geoAdmin::m("DB error when attempting to bulk add new value: ".$db->ErrorMsg(),geoAdmin::ERROR);
				return false;
			}
			
			$value_id = $db->Insert_Id();
			//clean name for DB
			$name = geoString::toDB($name);
			
			foreach ($langs as $lang) {
				$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_value_languages." SET `id`=?, `language_id`=?, `name`=?", array($value_id, $lang['language_id'], $name));
				if (!$result) {
					geoAdmin::m("DB error (2) when attempting to add new value: ".$db->ErrorMsg(),geoAdmin::ERROR);
					return false;
				}
			}
			if ($inc_order) {
				$display_order++;
			}
			$count++;
			if (!isset($min_value_id)) {
				$min_value_id = $value_id;
			}
			$max_value_id = $value_id;
		}
		$undo = '';
		if (($max_value_id - $min_value_id + 1) === $count) {
			//value id's are continuous, so let them undo if they want...
			$undo = '<a href="index.php?page=leveled_field_value_create_bulk&amp;undo=1&amp;leveled_field='.$leveled_field.'&amp;parent='.$parent.'&amp;min_value_id='.$min_value_id.'&amp;max_value_id='.$max_value_id.'&amp;auto_save=1" class="mini_cancel lightUpLink">Undo Bulk Add!</a>';
		}
		
		geoAdmin::m("Successfully added $count values!  $undo");
		if ($duplicates > 0) {
			geoAdmin::m("Note: there were $duplicates duplicate entries that were skipped.");
		}
		
		return true;
	}
	
	public function display_leveled_field_value_delete ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		//the values...
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		if (!count($values)) {
			geoAdmin::m("Error: No values selected!", geoAdmin::ERROR);
			return $this->ajaxError();
		}
		
		//what we gonna do:  show all the values and sub-values that would be affected...
		$parent = (int)$_GET['parent'];
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		$tpl_vars = array();
		
		$tpl_vars['levels_removed'] = geoLeveledField::removeValues($values, true);
		
		$tpl_vars['values'] = $values;
		$tpl_vars['value_count'] = count($values);
		$tpl_vars['parent'] = $parent;
		$tpl_vars['level'] = $level;
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent, true);
			$tpl_vars['parent_info'] = $lField->getValueInfo($parent);
		}
		
		$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
		$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
		
		$page = 1;
		if (isset($_GET['p'])) {
			$page = ($_GET['p']==='all')? 'all' : (int)$_GET['p'];
		}
		$tpl_vars['page'] = $page;
		
		//ajax call, just display template
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/delete.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_delete ()
	{
		$db = DataAccess::getInstance();
		
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		
		if (!count($values)) {
			geoAdmin::m("Error: No values selected to delete!",geoAdmin::ERROR);
			return false;
		}
		
		if (!isset($_POST['really']) || $_POST['really']!=='yes') {
			geoAdmin::m("OK, nothing done since you may not be looking at what you are clicking on.  (You almost just deleted a bunch of leveled field values without realizing it!)",geoAdmin::NOTICE);
			return false;
		}
		//ok, delete them!
		geoLeveledField::removeValues($values, false);
		geoAdmin::m("Deleted the ".count($values)." selected values(s) and all sub-values.");
		return true;
	}
	
	public function display_leveled_field_value_edit_bulk ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		//the values...
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		if (!count($values)) {
			geoAdmin::m("Error: No values selected!", geoAdmin::ERROR);
			return $this->ajaxError();
		}
		
		$parent = (int)$_GET['parent'];
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		$tpl_vars = array();
		
		//get all the languages:
		$tpl_vars['languages'] = $this->getLanguages();
		
		$tpl_vars['values'] = $values;
		$tpl_vars['value_count'] = count($values);
		$tpl_vars['parent'] = $parent;
		$tpl_vars['level'] = $lField->getLevel($leveled_field, $level);
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		$page = 1;
		if (isset($_GET['p'])) {
			$page = ($_GET['p']==='all')? 'all' : (int)$_GET['p'];
		}
		$tpl_vars['page'] = $page;
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent, true);
			$tpl_vars['parent_info'] = $lField->getValueInfo($parent);
		}
		
		//ajax call, just display template
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/bulkEdit.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_edit_bulk ()
	{
		$parent = (int)$_GET['parent'];
		$parent = $this->validValueId($parent);
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		
		$db = DataAccess::getInstance();
		
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		
		if (!count($values)) {
			geoAdmin::m("Error: No values selected!",geoAdmin::ERROR);
			return false;
		}
		
		$set = array();
		$query_vars = array();
		
		if (isset($_POST['enabled']) && in_array($_POST['enabled'], array('yes','no'))) {
			//turning enabled on/off
			$set[] = "`enabled`=?";
			$query_vars[] = ''.$_POST['enabled'];
		}
		
		$inc_display_order = false;
		if (isset($_POST['display_order_change']) && $_POST['display_order_change']) {
			if ($_POST['display_order_change'] === 'same') {
				$set[] = "`display_order`=?";
				$query_vars[] = (int)$_POST['display_order_same'];
			} else if ($_POST['display_order_change'] === 'inc') {
				//will be looping through in the end...
				$inc_display_order=true;
				$display_order = (int)$_POST['display_order_inc_start'];
			}
		}
		
		//now do the mass update which should be easy
		if (!count($set) && !$inc_display_order && !$unique_use) {
			//nothing to do...
			geoAdmin::m("Error: No changes specified, nothing to bulk-change!",geoAdmin::ERROR);
			return false;
		}
		
		if (count($set)) {
			$sql = "UPDATE ".geoTables::leveled_field_value." SET ".implode(', ',$set)." WHERE `id` IN (".implode(', ',$values).")";
			$result = $db->Execute($sql, $query_vars);
			if (!$result) {
				geoAdmin::m("DB error attempting to apply bulk changes, error reported: ".$db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
		}
		
		if ($inc_display_order) {
			//must also go through every single one...
			//first clear time limit
			set_time_limit(0);
			
			foreach ($values as $value_id) {
				$sql = "UPDATE ".geoTables::leveled_field_value." SET `display_order`=? WHERE `id`={$value_id}";
				$result = $db->Execute($sql, array($display_order));
				if (!$result) {
					geoAdmin::m("Error attempting to update value! DB error reported: ".$db->ErrorMsg(),geoAdmin::ERROR);
					return false;
				}
				$display_order++;
			}
		}
		$count = count($values);
		geoAdmin::m("Successfully updated $count values!");
		
		return true;
	}
	
	public function display_leveled_field_value_enabled ()
	{
		if (!geoAjax::isAjax()) {
			//this shouldn't really happen but whatevs
			return $this->display_leveled_field_values();
		}
		geoAjax::getInstance()->jsonHeader();
		$lField = geoLeveledField::getInstance();
		
		$value_id = (int)$_POST['value'];
		$valueInfo = $lField->getValueInfo($value_id);
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign('value', $valueInfo)
			->assign('is_ajax',true);
		
		$tpl->display('leveled_fields/enabled.tpl');
		
		geoView::getInstance()->setRendered(true);
	}
	
	public function update_leveled_field_value_enabled ()
	{
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$value_id = (int)$_POST['value'];
		
		$valueInfo = $lField->getValueInfo($value_id);
		
		if ($valueInfo) {
			$enabled = ($valueInfo['enabled']==='yes')? 'no' : 'yes';
			
			$db->Execute("UPDATE ".geoTables::leveled_field_value." SET `enabled`=? WHERE `id`=?", array($enabled, $value_id));
		}
		return true;
	}
	
	public function display_leveled_field_value_move ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		$new_leveled_field = (isset($_GET['new_leveled_field']))? (int)$_GET['new_leveled_field'] : $leveled_field;
		
		//the values...
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		if (!count($values)) {
			geoAdmin::m("Error: No values selected!", geoAdmin::ERROR);
			return $this->ajaxError();
		}
		
		if (isset($_GET['browseGroup']) && $_GET['browseGroup']) {
			//browse the groups...
			$tpl_vars = array();
			
			//leave out the selected values...
			$tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM ".geoTables::leveled_fields." ORDER BY label");
				
			$tpl_vars['browse_link'] = 'index.php?page=leveled_field_value_move&amp;browse=1&amp;leveled_field='.$leveled_field.'&amp;parent=0&amp;new_leveled_field=';
			
			$tpl = new geoTemplate(geoTemplate::ADMIN);
			$tpl->assign($tpl_vars);
			$tpl->display('leveled_fields/moveBrowseGroups.tpl');
			$admin->v()->setRendered(true);
			return;
		}
		if (isset($_GET['browse']) && $_GET['browse']) {
			$tpl_vars = array();
			$new_parent = (int)$_GET['parent'];
			$new_parent = $this->validValueId($new_parent);
			$tpl_vars['new_parent'] = $new_parent;
			if ($new_parent) {
				$tpl_vars['new_parents'] = $lField->getParents($new_parent, true);
			}
			//leave out the selected values...
			$tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM ".geoTables::leveled_field_value." r, ".geoTables::leveled_field_value_languages." l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? AND r.leveled_field=? AND r.id NOT IN (".implode(',',$values).") ORDER BY r.display_order, l.name",array($new_parent, $new_leveled_field));
			
			$tpl_vars['browse_link'] = 'index.php?page=leveled_field_value_move&amp;browse=1&amp;leveled_field='.$new_leveled_field.'&amp;new_leveled_field='.$new_leveled_field.'&amp;parent=';
			$tpl_vars['browse_groups_link'] = 'index.php?page=leveled_field_value_move&amp;browseGroup=1&amp;leveled_field='.$leveled_field.'&amp;new_leveled_field=';
			$tpl_vars['new_leveled_field'] = $new_leveled_field;
			$tpl_vars['new_leveled_field_label'] = $lField->getLeveledFieldLabel($new_leveled_field);
			
			$tpl = new geoTemplate(geoTemplate::ADMIN);
			$tpl->assign($tpl_vars);
			$tpl->display('leveled_fields/moveBrowse.tpl');
			$admin->v()->setRendered(true);
			return;
		}
		
		//what we gonna do:  show all the values and sub-values that would be affected...
		$parent = (int)$_GET['parent'];
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		$tpl_vars = array();
		
		$tpl_vars['values'] = $values;
		$tpl_vars['value_count'] = count($values);
		$tpl_vars['parent'] = $parent;
		$tpl_vars['level'] = $level;
		//starting out, leveled field and "new" leveled field are going to be same...
		$tpl_vars['leveled_field'] = $tpl_vars['new_leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $tpl_vars['new_leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent, true);
			$tpl_vars['parent_info'] = $lField->getValueInfo($parent);
		}
		$new_parent = (isset($_GET['new_parent']))? $_GET['new_parent'] : $parent;
		
		if ($new_parent) {
			$tpl_vars['new_parents'] = $lField->getParents($new_parent, true);
			$tpl_vars['new_parent_info'] = $lField->getValueInfo($new_parent);
		}
		//leave out the selected values...
		$tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM ".geoTables::leveled_field_value." r, ".geoTables::leveled_field_value_languages." l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? AND r.leveled_field=? AND r.id NOT IN (".implode(',',$values).") ORDER BY r.display_order, l.name",array($new_parent, $new_leveled_field));
		
		$tpl_vars['browse_link'] = 'index.php?page=leveled_field_value_move&amp;browse=1&amp;leveled_field='.$leveled_field.'&amp;new_leveled_field='.$new_leveled_field.'&amp;parent=';
		$tpl_vars['browse_groups_link'] = 'index.php?page=leveled_field_value_move&amp;browseGroup=1&amp;leveled_field='.$leveled_field.'&amp;new_leveled_field=';
		
		$tpl_vars['group_browse_link'] = '';
		
		$page = 1;
		if (isset($_GET['p'])) {
			$page = ($_GET['p']==='all')? 'all' : (int)$_GET['p'];
		}
		$tpl_vars['page'] = $page;
		
		//ajax call, just display template
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/move.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_move ()
	{
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		$new_leveled_field = $leveled_field;
		
		if ((isset($_GET['browse']) && $_GET['browse']) || (isset($_GET['growseGroups']) && $_GET['growseGroups'])) {
			//nothing to do, just browsing...
			return true;
		}
		
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		
		if (!count($values)) {
			geoAdmin::m("Error: No values selected to move!",geoAdmin::ERROR);
			return false;
		}
		
		$parent = (int)$_GET['parent'];
		//FAILSAFE:  Make sure the parent specified matches the selected values...
		//because if it does not that might indicate hitting refresh or something
		$wrong_parent_count = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value." WHERE
				`id` IN (".implode(',',$values).") AND `parent`!=?",array($parent));
		if ($wrong_parent_count > 0) {
			//oops!  Must have hit refresh or something...
			geoAdmin::m("Error: some of the selected values are no longer in the same place,
					aborting the move proceedure.  This typically happens when you refresh the page
					directly after moving values(s), or if more than one admin is editing values at once.",geoAdmin::ERROR);
			return false;
		}
		
		$new_type = $_POST['to_type'];
		
		if ($new_type === 'top') {
			//easy, new parent is 0
			$new_parent = 0;
		} else if ($new_type === 'id') {
			//use either ID or unique_name
			if (!strlen(trim($_POST['new_parent']))) {
				geoAdmin::m("Error: No parent value ID specified!",geoAdmin::ERROR);
				return false;
			}
			$new_parent = (int)$_POST['new_parent'];
			
			$new_parent = $this->validValueId($new_parent);
			if (!$new_parent) {
				geoAdmin::m("Error: Parent Value ID specified is not valid or could not be found!",geoAdmin::ERROR);
				return false;
			}
			
			//now figure out if we really should be moving here, make sure not
			//trying to move value into itself.
			
			if (in_array($new_parent, $values)) {
				geoAdmin::m("ERROR:  Can't move a value into itself!");
				return false;
			}
			
			//get the new parent's info
			$new_parent_info = $lField->getValueInfo($new_parent);
			$new_leveled_field = (int)$new_parent_info['leveled_field'];
			//now go through each level...
			
			$next_level = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent` IN (".implode(',',$values).")");
			$next_count = $next_level->RecordCount();
			while ($next_level && $next_count) {
				$this_level = array();
				foreach ($next_level as $row) {
					if ($row['id'] == $new_parent) {
						//oops found a child of the values being moved that matches the new location,
						//so can't do this!
						geoAdmin::m("ERROR: Cannot move into a child of a selected values(s)!",geoAdmin::ERROR);
						return false;
					}
					$this_level[] = $row['id'];
				}
				$next_level = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent` IN (".implode(',',$this_level).")");
				$next_count = $next_level->RecordCount();
			}
			unset($next_level,$next_count,$this_level);
			//gets this far, it "should" be ok to move into this value... maybe...
		} else if ($new_type === 'browse') {
			$new_parent = (int)$_POST['browse_value'];
			$new_parent = $this->validValueId($new_parent);
			if ($new_parent !== (int)$_POST['browse_value']) {
				//while can browse to top, if the number specified was not 0 but
				//valid value ID returned 0 then it isn't the one they asked for...
				geoAdmin::m("Error: Parent Value ID specified is not valid or could not be found!",geoAdmin::ERROR);
				return false;
			}
			$new_leveled_field = (int)$_POST['new_leveled_field'];
			if (!$new_leveled_field) {
				geoAdmin::m("Error: Invalid selection, must select the leveled field group to change to.",geoAdmin::ERROR);
				return false;
			}
		}
		
		
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$old_level = (int)$parent_info['level']+1;
		} else {
			$old_level = 1;
		}
		
		if ($new_parent) {
			$new_parent_info = $lField->getValueInfo($new_parent);
			$new_level = (int)$new_parent_info['level']+1;
		} else {
			$new_level = 1;
		}
		
		if ($parent === $new_parent && $leveled_field === $new_leveled_field) {
			geoAdmin::m("That is the same place it started from!",geoAdmin::NOTICE);
			return false;
		}
		
		//first part is easy, just update the parents...
		$result = $db->Execute("UPDATE ".geoTables::leveled_field_value." SET `parent`=? WHERE `id` IN (".implode(',',$values).")", array($new_parent));
		
		if ($new_level !== $level || $new_leveled_field !== $leveled_field) {
			//oops, have to fix the levels on all...
			foreach ($values as $value_id) {
				$this->fixValueLevel($value_id, $new_level, $new_leveled_field);
			}
		}
		geoAdmin::m("Values moved successfully!");
		return true;
	}
	
	public function display_leveled_field_value_copy ()
	{
		if (!geoAjax::isAjax()) {
			return $this->display_leveled_field_values();
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		$new_leveled_field = (isset($_GET['new_leveled_field']))? (int)$_GET['new_leveled_field'] : $leveled_field;
		
		//the values...
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		if (!count($values)) {
			geoAdmin::m("Error: No values selected!", geoAdmin::ERROR);
			return $this->ajaxError();
		}
		
		if (isset($_GET['browseGroup']) && $_GET['browseGroup']) {
			//browse the groups...
			$tpl_vars = array();
			
			//leave out the selected values...
			$tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM ".geoTables::leveled_fields." ORDER BY label");
				
			$tpl_vars['browse_link'] = 'index.php?page=leveled_field_value_copy&amp;browse=1&amp;leveled_field='.$leveled_field.'&amp;parent=0&amp;new_leveled_field=';
			
			$tpl = new geoTemplate(geoTemplate::ADMIN);
			$tpl->assign($tpl_vars);
			$tpl->display('leveled_fields/copyBrowseGroups.tpl');
			$admin->v()->setRendered(true);
			return;
		}
		if (isset($_GET['browse']) && $_GET['browse']) {
			$tpl_vars = array();
			$new_parent = (int)$_GET['parent'];
			$new_parent = $this->validValueId($new_parent);
			$tpl_vars['new_parent'] = $new_parent;
			if ($new_parent) {
				$tpl_vars['new_parents'] = $lField->getParents($new_parent, true);
			}
			//leave out the selected values...
			$tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM ".geoTables::leveled_field_value." r, ".geoTables::leveled_field_value_languages." l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? AND r.leveled_field=? AND r.id NOT IN (".implode(',',$values).") ORDER BY r.display_order, l.name",array($new_parent, $new_leveled_field));
			
			$tpl_vars['browse_link'] = 'index.php?page=leveled_field_value_copy&amp;browse=1&amp;leveled_field='.$new_leveled_field.'&amp;new_leveled_field='.$new_leveled_field.'&amp;parent=';
			$tpl_vars['browse_groups_link'] = 'index.php?page=leveled_field_value_copy&amp;browseGroup=1&amp;leveled_field='.$leveled_field.'&amp;new_leveled_field=';
			$tpl_vars['new_leveled_field'] = $new_leveled_field;
			$tpl_vars['new_leveled_field_label'] = $lField->getLeveledFieldLabel($new_leveled_field);
			
			$tpl = new geoTemplate(geoTemplate::ADMIN);
			$tpl->assign($tpl_vars);
			$tpl->display('leveled_fields/copyBrowse.tpl');
			$admin->v()->setRendered(true);
			return;
		}
		
		//what we gonna do:  show all the values and sub-values that would be affected...
		$parent = (int)$_GET['parent'];
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$level = (int)$parent_info['level']+1;
		} else {
			$level = 1;
		}
		$tpl_vars = array();
		
		$tpl_vars['values'] = $values;
		$tpl_vars['value_count'] = count($values);
		$tpl_vars['parent'] = $parent;
		$tpl_vars['level'] = $level;
		//starting out, leveled field and "new" leveled field are going to be same...
		$tpl_vars['leveled_field'] = $tpl_vars['new_leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $tpl_vars['new_leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		if ($parent) {
			$tpl_vars['parents'] = $lField->getParents($parent, true);
			$tpl_vars['parent_info'] = $lField->getValueInfo($parent);
		}
		$new_parent = (isset($_GET['new_parent']))? $_GET['new_parent'] : $parent;
		
		if ($new_parent) {
			$tpl_vars['new_parents'] = $lField->getParents($new_parent, true);
			$tpl_vars['new_parent_info'] = $lField->getValueInfo($new_parent);
		}
		//leave out the selected values...
		$tpl_vars['browse_values'] = $db->GetAll("SELECT * FROM ".geoTables::leveled_field_value." r, ".geoTables::leveled_field_value_languages." l WHERE l.id=r.id AND l.language_id=1 AND r.parent=? AND r.leveled_field=? AND r.id NOT IN (".implode(',',$values).") ORDER BY r.display_order, l.name",array($new_parent, $new_leveled_field));
		
		$tpl_vars['browse_link'] = 'index.php?page=leveled_field_value_copy&amp;browse=1&amp;leveled_field='.$leveled_field.'&amp;new_leveled_field='.$new_leveled_field.'&amp;parent=';
		$tpl_vars['browse_groups_link'] = 'index.php?page=leveled_field_value_copy&amp;browseGroup=1&amp;leveled_field='.$leveled_field.'&amp;new_leveled_field=';
		
		$tpl_vars['group_browse_link'] = '';
		
		$page = 1;
		if (isset($_GET['p'])) {
			$page = ($_GET['p']==='all')? 'all' : (int)$_GET['p'];
		}
		$tpl_vars['page'] = $page;
		
		//ajax call, just display template
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign($tpl_vars);
		
		$tpl->display('leveled_fields/copy.tpl');
		
		$admin->v()->setRendered(true);
	}
	
	public function update_leveled_field_value_copy ()
	{
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		$new_leveled_field = $leveled_field;
		
		if ((isset($_GET['browse']) && $_GET['browse']) || (isset($_GET['growseGroups']) && $_GET['growseGroups'])) {
			//nothing to do, just browsing...
			return true;
		}
		
		$values = array();
		foreach ($_POST['values'] as $value_id) {
			$value_id = (int)$value_id;
			if ($value_id && !in_array($value_id, $values)) {
				$values[] = $value_id;
			}
		}
		
		if (!count($values)) {
			geoAdmin::m("Error: No values selected to copy!",geoAdmin::ERROR);
			return false;
		}
		
		$parent = (int)$_GET['parent'];
		//FAILSAFE:  Make sure the parent specified matches the selected values...
		//because if it does not that might indicate hitting refresh or something
		$wrong_parent_count = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value." WHERE
				`id` IN (".implode(',',$values).") AND `parent`!=?",array($parent));
		if ($wrong_parent_count > 0) {
			//oops!  Must have hit refresh or something...
			geoAdmin::m("Error: some of the selected values are no longer in the same place,
					aborting the copy proceedure.  This typically happens when you refresh the page
					directly after copying values(s), or if more than one admin is editing values at once.",geoAdmin::ERROR);
			return false;
		}
		
		$new_type = $_POST['to_type'];
		
		if ($new_type === 'top') {
			//easy, new parent is 0
			$new_parent = 0;
		} else if ($new_type === 'id') {
			//use either ID or unique_name
			if (!strlen(trim($_POST['new_parent']))) {
				geoAdmin::m("Error: No parent value ID specified!",geoAdmin::ERROR);
				return false;
			}
			$new_parent = (int)$_POST['new_parent'];
			
			$new_parent = $this->validValueId($new_parent);
			if (!$new_parent) {
				geoAdmin::m("Error: Parent Value ID specified is not valid or could not be found!",geoAdmin::ERROR);
				return false;
			}
			
			//now figure out if we really should be moving here, make sure not
			//trying to move value into itself.
			
			if (in_array($new_parent, $values)) {
				geoAdmin::m("ERROR:  Can't move a value into itself!");
				return false;
			}
			
			//get the new parent's info
			$new_parent_info = $lField->getValueInfo($new_parent);
			$new_leveled_field = (int)$new_parent_info['leveled_field'];
			//now go through each level...
			
			$next_level = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent` IN (".implode(',',$values).")");
			$next_count = $next_level->RecordCount();
			while ($next_level && $next_count) {
				$this_level = array();
				foreach ($next_level as $row) {
					if ($row['id'] == $new_parent) {
						//oops found a child of the values being moved that matches the new location,
						//so can't do this!
						geoAdmin::m("ERROR: Cannot move into a child of a selected values(s)!",geoAdmin::ERROR);
						return false;
					}
					$this_level[] = $row['id'];
				}
				$next_level = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent` IN (".implode(',',$this_level).")");
				$next_count = $next_level->RecordCount();
			}
			unset($next_level,$next_count,$this_level);
			//gets this far, it "should" be ok to move into this value... maybe...
		} else if ($new_type === 'browse') {
			$new_parent = (int)$_POST['browse_value'];
			$new_parent = $this->validValueId($new_parent);
			if ($new_parent !== (int)$_POST['browse_value']) {
				//while can browse to top, if the number specified was not 0 but
				//valid value ID returned 0 then it isn't the one they asked for...
				geoAdmin::m("Error: Parent Value ID specified is not valid or could not be found!",geoAdmin::ERROR);
				return false;
			}
			$new_leveled_field = (int)$_POST['new_leveled_field'];
			if (!$new_leveled_field) {
				geoAdmin::m("Error: Invalid selection, must select the leveled field group to change to.",geoAdmin::ERROR);
				return false;
			}
			if ($new_parent) {
				$new_parent_info = $lField->getValueInfo($new_parent);
			}
		}
		
		
		if ($parent) {
			$parent_info = $lField->getValueInfo($parent);
			$old_level = (int)$parent_info['level']+1;
		} else {
			$old_level = 1;
		}
		
		if ($new_parent) {
			$new_parent_info = $lField->getValueInfo($new_parent);
			$new_level = (int)$new_parent_info['level']+1;
		} else {
			$new_level = 1;
		}
		
		if ($parent === $new_parent && $leveled_field === $new_leveled_field) {
			geoAdmin::m("That is the same place it started from!",geoAdmin::NOTICE);
			return false;
		}
		
		foreach ($values as $value_id) {
			if (!$this->copyValue($value_id, $new_leveled_field, $new_parent_info)) {
				//problem with copy, don't keep going
				return false;
			}
		}
		geoAdmin::m("Values copied successfully!");
		return true;
	}
	
	public function display_leveled_field_levels ()
	{
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		if (!$leveled_field) {
			geoAdmin::m('Error: invalid leveled field group specified.',geoAdmin::ERROR);
			return $this->display_leveled_fields();
		}
		
		$tpl_vars = array();
		
		$tpl_vars['admin_msgs'] = $admin->message();
		
		$tpl_vars['leveled_field'] = $leveled_field;
		$tpl_vars['leveled_field_label'] = $lField->getLeveledFieldLabel($leveled_field);
		
		$tpl_vars['levels'] = $lField->getLevels($leveled_field);
		$tpl_vars['languages'] = $this->getLanguages();
		
		$tpl_vars['tooltips'] = array(
			'level' => geoHTML::showTooltip('Level', 'Levels are automatically created as you add new values to a leveled field group.'),
			'sample' => geoHTML::showTooltip('Sample', 'The sample values below were pulled from the leveled field group based on the value with the deepest number of levels for that field group.'),
			'always_show' => geoHTML::showTooltip('Always Show', 'If checked, this level will appear as a disabled dropdown in front-side field selectors (such as in Listing Placement) before its parent values are selected'),
		);
	
		$this->_addCssJs();
		
		$admin->setBodyTpl('leveled_fields/levels.tpl')
			->v()->setBodyVar($tpl_vars);
	}
	
	public function update_leveled_field_levels ()
	{
		$db = DataAccess::getInstance();
		$lField = geoLeveledField::getInstance();
		
		$leveled_field = (int)$_GET['leveled_field'];
		
		//We get max level by looking at the levels for the values in the system, we don't
		//care about levels beyond that since they would not effect anything
		$maxLevels = $lField->getMaxLevel($leveled_field, false);
		
		$always_show = (array)$_POST['always_show'];
		
		$labels = (array)$_POST['label'];
		$languages = $this->getLanguages();
		
		//go level by level saving the details for each level
		for ($i = 1; $i<=$maxLevels; $i++) {
			//see if label already set
			$label = $db->GetRow("SELECT * FROM ".geoTables::leveled_field_level." WHERE `level`=$i AND `leveled_field`=$leveled_field");
			
			$setting = (isset($always_show[$i]) && $always_show[$i]==='yes')? 'yes' : 'no';
			
			if ($label) {
				//update
				$result = $db->Execute("UPDATE ".geoTables::leveled_field_level." SET `always_show`=? WHERE `level`=? AND `leveled_field`=?", array($setting, $i, $leveled_field));
				if (!$result) {
					geoAdmin::m("DB Error when attempting to save field level settings, error reported: ".$db->ErrorMsg(), geoAdmin::ERROR);
					return false;
				}
			} else {
				//this label not yet saved, so save it!
				$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_level." SET `level`=?, `leveled_field`=?, `always_show`=?",array($i, $leveled_field, $setting));
				if (!$result) {
					geoAdmin::m("DB Error when attempting to add new field level settings, error reported: ".$db->ErrorMsg(), geoAdmin::ERROR);
					return false;
				}
			}
			
			//update label(s)!
			foreach ($languages as $lang) {
				$label = trim($labels[$i][$lang['language_id']]);
				$label = geoString::toDB($label);
				
				//see if update or insert
				if ((int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_level_labels." WHERE `level`=? AND `leveled_field`=? AND `language_id`=?", array($i, $leveled_field, $lang['language_id']))) {
					//already exists, update
					$result = $db->Execute("UPDATE ".geoTables::leveled_field_level_labels." SET `label`=? WHERE `level`=? AND `leveled_field`=? AND `language_id`=?", array($label, $i, $leveled_field, $lang['language_id']));
					if (!$result) {
						geoAdmin::m("DB Error when attempting update field level labels, error reported: ".$db->ErrorMsg(), geoAdmin::ERROR);
						return false;
					}
				} else {
					//not exist yet, insert
					$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_level_labels." SET `level`=?, `leveled_field`=?, `language_id`=?, `label`=?", array($i, $leveled_field, $lang['language_id'], $label));
					if (!$result) {
						geoAdmin::m("DB Error when attempting to add new field level label, error reported: ".$db->ErrorMsg(), geoAdmin::ERROR);
						return false;
					}
				}
				
			}
		}
		return true;
	}
	
	private function validValueId ($value_id)
	{
		$value_id = (int)$value_id;
		if (!$value_id) {
			//value ID 0, don't verify
			return $value_id;
		}
		
		$db = DataAccess::getInstance();
		
		$count = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value." WHERE `id`=?",array($value_id));
		if ($count) {
			return $value_id;
		}
		//not found!
		geoAdmin::m("Could not find the parent value specified!  Showing the top level 1 values.", geoAdmin::NOTICE);
		return 0;
	}
	
	private $_languages;
	
	/**
	 * Get an array of languages from the DB
	 * 
	 * @return array
	 */
	private function getLanguages ()
	{
		if (!isset($this->_languages)) {
			$db = DataAccess::getInstance();
			$this->_languages = $db->GetAll("SELECT `language_id`, `language` FROM ".geoTables::pages_languages_table." ORDER BY `language_id`");
		}
		return $this->_languages;
	}
	
	private function ajaxError ()
	{
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl->assign('admin_msgs', geoAdmin::m());
		$tpl->display('leveled_fields/ajaxError.tpl');
		
		geoView::getInstance()->setRendered(true);
	}
	
	/**
	 * Recursively "fixes" the levels of the specified value ID and all "child"
	 * values, according to the new value specified.
	 *
	 * @param int $value_id
	 * @param int $level
	 * @param int $leveled_field
	 */
	private function fixValueLevel ($value_id, $level, $leveled_field)
	{
		$db = DataAccess::getInstance();
		
		//first off, go through any children and update their levels...
		$values = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent`=?",array($value_id));
		foreach ($values as $valueInfo) {
			$this->fixValueLevel($valueInfo['id'], ($level+1), $leveled_field);
		}
	
		//after taken care of all children, fix level for this one
		$db->Execute("UPDATE ".geoTables::leveled_field_value." SET `leveled_field`=?, `level`=? WHERE `id`=?", array((int)$leveled_field, (int)$level, (int)$value_id));
	}
	
	private function copyValue ($value_id, $leveled_field, $parent)
	{
		$db = DataAccess::getInstance();
		$langs = $this->getLanguages();
		
		$value_id = (int)$value_id;
		$leveled_field = (int)$leveled_field;
		
		$info = $db->GetRow("SELECT * FROM ".geoTables::leveled_field_value." WHERE `id`=?",array($value_id));
		if (!$info) {
			geoAdmin::m("Error: copy from value could not be found!",geoAdmin::ERROR);
			return false;
		}
		$parent_id = ($parent)? (int)$parent['id'] : 0;
		$level = ($parent)? $parent['level']+1 : 1;
		$enabled = $info['enabled'];
		$display_order = (int)$info['display_order'];
		
		//need to insert new one in there...
		$result = $db->Execute("INSERT INTO ".geoTables::leveled_field_value." SET `leveled_field`=?, `parent`=?, `level`=?, `enabled`=?, `display_order`=?",
				array($leveled_field, $parent_id, $level, $enabled, $display_order,));
		
		if (!$result) {
			geoAdmin::m('Error inserting copy into database, DB error reported: '.$db->ErrorMsg());
			return false;
		}
		$new_id = $db->Insert_Id();
		if (!$new_id) {
			geoAdmin::m('Error getting new id for new value inserted into database.');
			return false;
		}
		
		//set the ID in the existing info so we can pass it as the parent
		$info['id'] = $new_id;
		$info['level'] = $level;
		
		//Ok now copy over the languages
		foreach ($langs as $lang) {
			$row = $db->GetRow("SELECT * FROM ".geoTables::leveled_field_value_languages." WHERE `id`=? AND `language_id`=?",
					array($value_id, $lang['language_id']));
			//insert it for the new one
			if (!$row) {
				geoAdmin::m("Error retrieving language value! DB error reported: ".$db->ErrorMsg());
				return false;
			}
			$db->Execute("INSERT INTO ".geoTables::leveled_field_value_languages." SET `id`=?, `language_id`=?, `name`=?",
					array($new_id, $lang['language_id'], $row['name'].''));
		}
		
		//now, go through any children and copy those...
		$values = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent`=?",array($value_id));
		foreach ($values as $valueInfo) {
			if (!$this->copyValue($valueInfo['id'], $leveled_field, $info)) {
				//something went wrong copying a child, stop here
				return false;
			}
		}
		return true;
	}
	
	private function _addCssJs ()
	{
		geoView::getInstance()
			->addCssFile('css/leveled_fields.css')
			->addJScript('js/leveled_fields_admin.js');
	}
}