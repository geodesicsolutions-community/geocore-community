<?php

//shared/category_navigation.tpl


//Common code used for most of the category navigation modules

//needs to have certain things set prior to including this shared file

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

$sub_categories = array();
$catCount = 0;

//NOTE: This is NOT making use of "in_statement" that was removed in 7.4..  This is specifically
//for showing "one level down" categories as special case
$parentIn = " = " . (int)$tpl_vars['parent_category'];

if ($show_module['module_category_level_to_display']) {
    //show one level down from top one...

    //so get all cat IDs for top level categories
    $sql = "SELECT category_id
			FROM geodesic_categories
			WHERE parent_id {$parentIn} AND `enabled`='yes'
			ORDER BY display_order";
    $level2 = $this->GetCol($sql);
    $parentIn = " IN (" . implode(', ', $level2) . ")";
    unset($level2);
}

if ($show_module['module_display_sub_category_nav_links']) {
    //use a single query to get all sub-categories, this has proven on average
    //about 2x faster than getting sub-categories for each individual category.

    $sql = "SELECT child_cat.parent_id,
			child_cat.category_id,
			child_lang.category_name
			FROM geodesic_categories_languages AS parent_lang
			INNER JOIN geodesic_categories AS parent_cat 
			ON parent_lang.category_id = parent_cat.category_id
			LEFT JOIN geodesic_categories AS child_cat 
			ON child_cat.parent_id = parent_cat.category_id
			INNER JOIN geodesic_categories_languages AS child_lang 
			ON child_lang.category_id = child_cat.category_id
			WHERE parent_cat.parent_id $parentIn AND child_cat.category_id IS NOT NULL
			AND parent_lang.language_id = " . $page->language_id . "
			AND child_lang.language_id = " . $page->language_id . "
			AND parent_cat.enabled='yes' AND child_cat.enabled='yes'
			ORDER BY parent_cat.parent_id, parent_cat.display_order, parent_lang.category_name, child_cat.display_order, child_lang.category_name";
    $rows = $this->GetAll($sql);
    foreach ($rows as $row) {
        $sub_categories[$row['parent_id']][] = $row;
        $catCount++;
    }
    if ($catCount) {
        //each subcategory displays with about half the height of a full category
        $catCount = ceil($catCount / 2);
    }
    unset($rows);
    trigger_error('DEBUG STATS: - sub_cat initialized');
}


$sql = "SELECT lang.category_id, lang.category_name, lang.description, lang.language_id,
	lang.category_image, lang.category_image_alt, cat.auction_category_count, cat.category_count
	FROM " . geoTables::categories_table . " as cat, " . geoTables::categories_languages_table . " as lang where
	cat.parent_id {$parentIn} and cat.category_id = lang.category_id and lang.language_id = {$page->language_id} 
	and cat.enabled='yes' order by cat.display_order, lang.category_name";

$rows = $this->GetAll($sql);

$catCount += count($rows);

$categories = array();
$classified_file_name = $this->get_site_setting('classified_file_name');

foreach ($rows as $row) {
    if ($show_module['display_category_count']) {
        $category_count = array (
            'listing_count' => $row['auction_category_count'] + $row['category_count'],
            'ad_count' => $row['category_count'],
            'auction_count' => $row['auction_category_count'],
        );

        $row ['category_counts'] = $page->display_category_count($db, $row['category_id'], $show_module['browsing_count_format'], '', '', $category_count);
    }
    $row['category_name'] = geoString::fromDB($row['category_name']);
    $row['category_description'] = geoString::fromDB($row['description']);
    if ($show_module['module_display_new_ad_icon']) {
        $row['new_ad_icon'] = geoCategory::new_ad_icon_use($row['category_id']);
    }

    if (isset($sub_categories[$row['category_id']])) {
        $row['sub_categories'] = $sub_categories[$row['category_id']];
    }

    $row['category_image'] = geoString::fromDB($row['category_image']);
    $row['category_image_alt'] = geoString::fromDB($row['category_image_alt']);

    $categories[] = $row;
}
unset($rows, $sub_categories);

$columns = ($show_module['number_of_browsing_columns']) ? $show_module['number_of_browsing_columns'] : 1;
$maxColumnCount = ceil($catCount / $columns);
$tpl_vars['categories'] = geoBrowse::categoryColumnSort($categories, $columns, $show_module['alpha_across_columns'], $maxColumnCount);


$tpl_vars['error_message'] = $page->error_message;
$tpl_vars['module'] = $show_module;

if (!isset($tpl_vars['link'])) {
    $tpl_vars['link'] = $this->get_site_setting('classifieds_file_name') . '?a=5&amp;b=';
}
$tpl_vars['col_count'] = $columns;
$tpl_vars['col_width'] = floor(100 / $columns) . '%';

//free up memory
unset($categories);

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
