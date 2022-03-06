<?php

/**
 * This is a custom smarty function used to display the categories on the left hand side
 *
 * @param unknown $params
 * @param Smarty_Internal_Template $smarty
 * @return string|Ambigous <string, boolean, Mixed, mixed, NULL, unknown, multitype:unknown >
 */
function smarty_function_category_list($params, Smarty_Internal_Template $smarty)
{
    $db = DataAccess::getInstance();
    if ($db->get_site_setting('disableAllBrowsing')) {
        //browsing disabled, do not show module contents
        return;
    }

    $parent_cat = $current_cat = (int)$smarty->getTemplateVars('category_id');
    $parent_cats = array();
    if ($parent_cat) {
        while ($parent_cat > 0) {
            $parent_cats[$parent_cat] = $parent_cat;

            $parent_cat = (int)$db->GetOne("SELECT parent_id FROM " . geoTables::categories_table . " WHERE
					category_id=?", array($parent_cat));
        }
    }

    $tpl_vars['categories'] = cz_cats(0, $current_cat, $parent_cats);

    $tpl_vars['link'] = $db->get_site_setting('classifieds_file_name') . '?a=5&amp;b=';

    return geoTemplate::loadInternalTemplate(
        $params,
        $smarty,
        'helpers/categories.tpl',
        geoTemplate::MAIN_PAGE,
        '',
        $tpl_vars
    );
    ;
}

function cz_cats($parent_id = 0, $current_cat = 0, $selected = array())
{
    $db = DataAccess::getInstance();
    $language_id = $db->getLanguage();

    $sql = "SELECT lang.category_id, lang.category_name, lang.description, lang.language_id,
						lang.category_image, cat.auction_category_count, cat.category_count
						FROM " . geoTables::categories_table . " as cat, " . geoTables::categories_languages_table . " as lang where
						cat.parent_id=? and cat.category_id = lang.category_id and lang.language_id = {$language_id}
						and cat.enabled='yes' order by cat.display_order, lang.category_name";

    $rows = $db->GetAll($sql, array($parent_id));

    $categories = array();

    foreach ($rows as $row) {
        $row ['category_name'] = geoString::fromDB($row['category_name']);
        $row ['category_description'] = geoString::fromDB($row['description']);
        $cat_id = (int)$row['category_id'];
        if ($cat_id === $current_cat) {
            $row['current'] = true;
        }
        if (isset($selected[$cat_id])) {
            $row['selected'] = true;
            $row['sub_categories'] = cz_cats($cat_id, $current_cat, $selected);
        }
        $categories[] = $row;
    }
    return $categories;
}
