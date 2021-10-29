<?php

// DON'T FORGET THIS
if(class_exists( 'admin_AJAX' ) or die());

class ADMIN_AJAXController_DataImport extends admin_AJAX {
	
	public function text() {
		if(!$this->_verify()) {
			return $this->_failure('No Access');
		}
		$lang = (int)$_POST['lang'];
		if(!$lang) {
			return $this->_failure('Invalid Language');
		}
		$data = $_POST['data'];
		$parts = str_getcsv($data);
		$id = $parts[0];
		$page = $parts[1];
		$text = $parts[5];
				
		$db = DataAccess::getInstance();
		if(is_numeric($id)) {
			//normal text
			//table doesn't have a PK, so can't REPLACE...gotta figure out whether to INSERT or UPDATE
			$exists = $db->GetOne("SELECT `text_id` FROM ".geoTables::pages_text_languages_table." WHERE `text_id` = ? AND `language_id` = ?", array($id, $lang));
			if($exists) {
				$query = "UPDATE ".geoTables::pages_text_languages_table." SET `text` = ? WHERE `page_id` = ? AND `text_id` = ? AND `language_id` = ?";
			} else {
				$query = "INSERT INTO ".geoTables::pages_text_languages_table." (`text`, `page_id`, `text_id`, `language_id`) VALUES (?,?,?,?)";
			}
			$result = $db->Execute($query, array(geoString::toDB($text), $page, $id, $lang));
			if(!$result) {
				if(stripos($db->ErrorMsg(),'Deadlock') === 0) {
					//db freaking out from getting too much info at once. just wait a second and try again
					sleep(1);
					$result = $db->Execute($query, array(geoString::toDB($text), $page, $id, $lang));
				}
				if(!$result) {
					return $this->_failure('Database error inserting Text ID '.$id.' || '.$db->ErrorMsg());
				}
			}
			return $this->_success('Completed text ID '.$id);
		} elseif (stripos($id, 'addon.') === 0) {
			//addon text does things almost the same, but different enough...
			$info = explode('.',$id);
			$addon = $info[1];
			$auth_tag = $info[2];
			$text_id = $page;
			if(!geoAddon::getInstance()->isInstalled($addon)) {
				return $this->_failure('Addon '.$addon.' not installed');
			}
			$exists = $db->GetOne("SELECT `text_id` FROM ".geoTables::addon_text_table." WHERE `auth_tag` = ? AND `addon` = ? AND `text_id` = ? AND `language_id` = ?", array($auth_tag, $addon, $text_id, $lang));
			if($exists) {
				$query = "UPDATE ".geoTables::addon_text_table." SET `text` = ? WHERE `auth_tag` = ? AND `addon` = ? AND `text_id` = ? AND `language_id` = ?";
			} else {
				$query = "INSERT INTO ".geoTables::addon_text_table." (`text`, `auth_tag`, `addon`, `text_id`, `language_id`) VALUES (?,?,?,?,?)";
			}
			$result = $db->Execute($query, array(geoString::toDB($text), $auth_tag, $addon, $text_id, $lang));
			if(!$result) {
				if(stripos($db->ErrorMsg(),'Deadlock') === 0) {
					//db freaking out from getting too much info at once. just wait a second and try again
					sleep(1);
					$result = $db->Execute($query, array(geoString::toDB($text), $auth_tag, $addon, $text_id, $lang));
				}
				if(!$result) {
					return $this->_failure('Database error inserting Text ID '.$info.'.'.$text_id);
				}
			}
			return $this->_success('Completed text ID '.$id.'.'.$text_id);
		} else {
			//not a piece of text we're interested in (perhaps the CSV header line, though)
			return $this->_failure('NOLINE');
		}
	}
	
	public function clear_structure()
	{
		if(!$this->_verify()) {
			return $this->_failure('No Access');
		}
		$db = DataAccess::getInstance();
		if($_POST['clear_type'] === 'region_structure') {
			$db->Execute("DELETE FROM ".geoTables::region);
			$db->Execute("DELETE FROM ".geoTables::region_languages);
		} elseif($_POST['clear_type'] === 'category_structure') {
			$db->Execute("DELETE FROM ".geoTables::categories_table);
			$db->Execute("DELETE FROM ".geoTables::categories_languages_table);
		} else {
			return $this->_failure('Bad Type');
		}
		return $this->_success('cleared');
	}
	
	public function region_structure() {
		if(!$this->_verify()) {
			return $this->_failure('No Access');
		}
		$data = $_POST['data'];
		$parts = str_getcsv($data);
		if(!(int)$parts[0] || count($parts) != 9) {
			//not a piece of text we're interested in (perhaps the CSV header line, though)
			return $this->_failure('NOLINE');
		}
		$db = DataAccess::getInstance();
		$sql = "REPLACE INTO ".geoTables::region." (`id`,`parent`,`level`,`enabled`,`billing_abbreviation`,`unique_name`,`tax_percent`,`tax_flat`,`display_order`) VALUES (?,?,?,?,?,?,?,?,?)";
		$result = $db->Execute($sql, array($parts[0],$parts[1],$parts[2],$parts[3],$parts[4],$parts[5],$parts[6],$parts[7],$parts[8]));
		if(!$result) {
			if(stripos($db->ErrorMsg(),'Deadlock') === 0) {
				//db freaking out from getting too much info at once. just wait a second and try again
				sleep(1);
				$result = $db->Execute($sql, array($parts[0],$parts[1],$parts[2],$parts[3],$parts[4],$parts[5],$parts[6],$parts[7],$parts[8]));
			}
			if(!$result) {
				return $this->_failure('db error');
			}
		}
		return $this->_success('ok');
		
	}
	
	public function region_data() {
		if(!$this->_verify()) {
			return $this->_failure('No Access');
		}
		$data = $_POST['data'];
		$parts = str_getcsv($data);
		if(!(int)$parts[0] || count($parts) != 3) {
			//not a piece of text we're interested in (perhaps the CSV header line, though)
			return $this->_failure('NOLINE');
		}
		$db = DataAccess::getInstance();
		$id = $parts[0];
		$name = $parts[2];
		
		$lang = (int)$_POST['lang'];
		if(!$lang) {
			return $this->_failure('Invalid Language');
		}
		
		$exists = $db->GetOne("SELECT `id` FROM ".geoTables::region_languages." WHERE `id` = ? AND `language_id` = ?", array($id, $lang));
		if($exists) {
			$query = "UPDATE ".geoTables::region_languages." SET `name` = ? WHERE `id` = ? AND `language_id` = ?";
		} else {
			$query = "INSERT INTO ".geoTables::region_languages." (`name`, `id`, `language_id`) VALUES (?,?,?)";
		}
		$result = $db->Execute($query, array(geoString::toDB($name), $id, $lang));
		if(!$result) {
			if(stripos($db->ErrorMsg(),'Deadlock') === 0) {
				//db freaking out from getting too much info at once. just wait a second and try again
				sleep(1);
				$result = $db->Execute($query, array(geoString::toDB($name), $id, $lang));
			}
			if(!$result) {
				return $this->_failure('Database error inserting Region ID '.$id);
			}
		}
		return $this->_success('Completed Region ID '.$id);
	}
	
	public function category_structure() {
		if(!$this->_verify()) {
			return $this->_failure('No Access');
		}
		$data = $_POST['data'];
		$parts = str_getcsv($data);
		if(!(int)$parts[0] || count($parts) != 17) {
			//not a piece of text we're interested in (perhaps the CSV header line, though)
			return $this->_failure('NOLINE');
		}
		$db = DataAccess::getInstance();
		$sql = "REPLACE INTO `geodesic_categories` (`category_id`,`parent_id`,`level`,`enabled`,`display_order`,`category_count`,`auction_category_count`,`what_fields_to_use`,`display_ad_description_where`,`display_all_of_description`,`length_of_description`,`default_display_order_while_browsing_category`,`listing_types_allowed`,`use_auto_title`,`auto_title`,`which_head_html`,`front_page_display`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$result = $db->Execute($sql, array($parts[0],$parts[1],$parts[2],$parts[3],$parts[4],$parts[5],$parts[6],$parts[7],$parts[8],$parts[9],$parts[10],$parts[11],$parts[12],$parts[13],$parts[14],$parts[15],$parts[16]));
		if(!$result) {
			if(stripos($db->ErrorMsg(),'Deadlock') === 0) {
				//db freaking out from getting too much info at once. just wait a second and try again
				sleep(1);
				$result = $db->Execute($sql, array($parts[0],$parts[1],$parts[2],$parts[3],$parts[4],$parts[5],$parts[6],$parts[7],$parts[8],$parts[9],$parts[10],$parts[11],$parts[12],$parts[13],$parts[14],$parts[15],$parts[16]));
			}
			if(!$result) {
				return $this->_failure('db error');
			}
		}
		return $this->_success('ok');
	}
	
	public function category_data() {
		if(!$this->_verify()) {
			return $this->_failure('No Access');
		}
		$data = $_POST['data'];
		$parts = str_getcsv($data);
		if(!(int)$parts[0] || count($parts) != 19) {
			//not a piece of text we're interested in (perhaps the CSV header line, though)
			return $this->_failure('NOLINE: '.print_r($parts,1));
		}
		$db = DataAccess::getInstance();
		$id = $parts[0];
		$name = $parts[1];
		
		$lang = (int)$_POST['lang'];
		if(!$lang) {
			return $this->_failure('Invalid Language');
		}
		
		$exists = $db->GetOne("SELECT `category_id` FROM ".geoTables::categories_languages_table." WHERE `category_id` = ? AND `language_id` = ?", array($id, $lang));
		if($exists) {
			$query = "UPDATE ".geoTables::categories_languages_table." SET `category_name` = ?, `description` = ?, `category_image` = ?, `head_html` = ?, `title_module` = ?, `seo_url_contents` = ?, `category_image_alt` = ? WHERE `category_id` = ? AND `language_id` = ?";
		} else {
			$query = "INSERT INTO ".geoTables::categories_languages_table." (`category_name`, `description`, `category_image`, `head_html`, `title_module`, `seo_url_contents`,`category_image_alt`, `category_id`, `language_id`) VALUES (?,?,?,?,?,?,?,?,?)";
		}
		$result = $db->Execute($query, array(geoString::toDB($name), $parts[2], $parts[3], $parts[4], $parts[5], $parts[6], $parts[7], $id, $lang));
		if(!$result) {
			if(stripos($db->ErrorMsg(),'Deadlock') === 0) {
				//db freaking out from getting too much info at once. just wait a second and try again
				sleep(1);
				$result = $db->Execute($query, array(geoString::toDB($name), $parts[2], $parts[3], $parts[4], $parts[5], $parts[6], $parts[7], $id, $lang));
			}
			if(!$result) {
				return $this->_failure('Database error inserting Category ID '.$id);
			}
		}
		return $this->_success('Completed Category ID '.$id);
	}
	
	private function _failure($msg) {
		return $this->encodeJSON(array('status'=>'error','message'=>$msg));
	}
	private function _success($msg) {
		return $this->encodeJSON(array('status'=>'ok','message'=>$msg));
	}
	private function _verify() {
		return $this->canUpdate('languages_import');
	}
}