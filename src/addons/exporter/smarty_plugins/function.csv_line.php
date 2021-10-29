<?php
//function.csv_line.php
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
## ##    7.4.4-10-g8576128
## 
##################################

//This fella takes care of {csv_line} to output a CSV line inside a template file

function smarty_function_csv_line ($params, $smarty)
{
	static $firstRow = true;
	
	$handle = $smarty->getVariable('csvHandle')->value;
	
	if (!$handle) {
		//can't do much without a handle...
		return '';
	}
	
	$row = $smarty->getVariable('listing')->value;
	
	if ($firstRow) {
		$firstRow = $row;
		unset($firstRow['category_name']);
		
		fputcsv($handle, array_keys($firstRow));
		$firstRow = false;
	}
	//Certain columns need a bit of pre-processing...
	if (isset($row['images'])) {
		$imgs = array ();
		foreach ($row['images'] as $data) {
			$imgs[] = $data['url'];
		}
		$row['images'] = implode("\n",$imgs);
	}
	if (isset($row['questions'])) {
		$questions = array();
		foreach ($row['questions'] as $question) {
			if ($question['checkbox']) {
				$questions[] = $question['value'];
			} else {
				$questions[] = $question['name'].'  '.$question['value'];
			}
		}
		$row['questions'] = implode("\n",$questions);
	}
	
	if (isset($row['category']) && $smarty->getVariable('catFormat')->value!='id') {
		$cat = $row['category_name'];
		if ($smarty->getVariable('catFormat')->value=='name_id') {
			$cat .= '('.$row['category'].')';
		}
		$row['category'] = $cat;
		unset($row['category_name']);
	}
	
	fputcsv($handle, $row);
	return '';
}
