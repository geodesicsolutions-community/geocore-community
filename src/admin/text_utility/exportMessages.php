<?php

$db = DataAccess :: getInstance();



$lang = (int)$_REQUEST['lang'];
$type = $_REQUEST['type'];

if($type === 'text') {
	$query = "select text_id,pages.page_id,language_id,name,description,langs.text from geodesic_pages_messages as pages, geodesic_pages_messages_languages as langs where pages.message_id = langs.text_id and language_id = '{$lang}' order by text_id asc";	
	$result = $db->Execute($query) or die($db->ErrorMsg());
	echo "Text ID or addon info, Page ID or Addon Text ID, Language ID, Name, Description, Text\n";

	$rowsRemain = true;
	while($row = $result->FetchRow()) {
		echo geoArrayTools::toCSV($row, true)."\n";
	}

	//addon text
	$addon = geoAddon::getInstance();
	$addonsText = $addon->getTextAddons();

	foreach ($addonsText as $info) {
		$text = $addon->getText($info->auth_tag, $info->name, $lang);
		$addonAdmin = $addon->getTextAddons($info->name);
		if (!$text || !is_object($addonAdmin)) {
			//something wrong with this one
			continue;
		}
		$textInfo = $addonAdmin->init_text();
		foreach ($text as $textI => $val) {
			$line = array (
				'addon.'.$info->name.'.'.$info->auth_tag,
				$textI,
				$lang,
				$textInfo[$textI]['name'],
				$textInfo[$textI]['desc'],
				$val
			);
			echo geoArrayTools::toCSV($line)."\n";
		}
	}
} else {

	if ($type === 'region_structure') {
		$sql = "SELECT * FROM `geodesic_region`";
	} elseif ($type === 'region_data') {
		$sql = "SELECT * FROM `geodesic_region_languages` WHERE `language_id` = {$lang}";
	} elseif ($type === 'category_structure') {
		$sql = "SELECT * FROM `geodesic_categories`";
	} elseif ($type === 'category_data') {
		$sql = "SELECT * FROM `geodesic_categories_languages` WHERE `language_id` = {$lang}";
	} else {
		geoAdmin::m('Unknown type');
		return;
	}
	$result = $db->Execute($sql);
	$i = 0 ;
	foreach($result as $row) {
		if($i++ == 0) {
			//write header row
			foreach($row as $key => $value) {
				$headerCols[] = $key;
			}
			echo geoArrayTools::toCSV($headerCols)."\n";
		}
		//need to fromDB the names of things
		if(isset($row['category_name'])) $row['category_name'] = geoString::fromDB($row['category_name']);
		if(isset($row['name'])) $row['name'] = geoString::fromDB($row['name']);
		echo geoArrayTools::toCSV($row)."\n";
	}
}


