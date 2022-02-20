<?php
//

/**
 * This fella takes care of {csv_line} to output a CSV line inside a template file
 *
 * @param array $params
 * @param Smarty $smarty
 * @return void
 */
function smarty_function_csv_line ($params, $smarty)
{
	static $firstRow = true;

	$handle = $smarty->_getVariable('csvHandle')->value;

	if (!$handle) {
		//can't do much without a handle...
		return '';
	}

	$row = $smarty->_getVariable('listing')->value;

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
		$row['images'] = implode(",",$imgs);
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

	if (isset($row['category']) && $smarty->_getVariable('catFormat')->value!='id') {
		$cat = $row['category_name'];
		if ($smarty->_getVariable('catFormat')->value=='name_id') {
			$cat .= '('.$row['category'].')';
		}
		$row['category'] = $cat;
		unset($row['category_name']);
	}

	fputcsv($handle, $row);
	return '';
}
