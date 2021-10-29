<?php

//module_display_search_box_1.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

if ($page->page_id == 72) {
    //output of this module can be cached, when on main page of site.
    $cacheMe = true;
}
$tpl_vars = array();
$tpl_vars['form_target'] = $page->configuration_data['classifieds_file_name'] . "?a=19";

//placeholder text for input box, escaped in the template
$tpl_vars['placeholder'] = $page->messages[501681];

$message = '';
if (isset($page->messages[500107])) {
    $message = $page->messages[500107];
}
$tpl_vars['category_dropdown'] = $page->get_category_dropdown("c", $page->site_category, 0, "search_box_1_input", $message, 2);

if ($db->get_site_setting('zipsearch_by_location_name') == 1) {
    $tpl_vars['zipsearchByLocation_html'] = geoSearchUtils::zipsearchByLocation(true);
} else {
    $tpl_vars['zipsearchByLocation_html'] = '';
}

$hidden_fields = "";
if ($show_module['display_category_description']) {
    $page->get_ad_configuration(0, 1);
    $page->field_configuration_data = $page->ad_configuration_data;

    $catCfg = geoCategory::getCategoryConfig($page->site_category, true);
    if ($catCfg['what_fields_to_use'] != 'site') {
        $page->field_configuration_data = array_merge($page->field_configuration_data, $catCfg);
    }

    if (geoPC::is_ent()) {
        $fields = $page->fields->getDisplayLocationFields('search_fields');
        if ($fields['optional_field_1']) {
            $tpl_vars['opt1'] = true;
        }
        if ($fields['optional_field_2']) {
            $tpl_vars['opt2'] = true;
        }
        if ($fields['optional_field_3']) {
            $tpl_vars['opt3'] = true;
        }
        if ($fields['optional_field_4']) {
            $tpl_vars['opt4'] = true;
        }
        if ($fields['optional_field_5']) {
            $tpl_vars['opt5'] = true;
        }
        if ($fields['optional_field_6']) {
            $tpl_vars['opt6'] = true;
        }
        if ($fields['optional_field_7']) {
            $tpl_vars['opt7'] = true;
        }
        if ($fields['optional_field_8']) {
            $tpl_vars['opt8'] = true;
        }
        if ($fields['optional_field_9']) {
            $tpl_vars['opt9'] = true;
        }
        if ($fields['optional_field_10']) {
            $tpl_vars['opt10'] = true;
        }
        if ($fields['optional_field_11']) {
            $tpl_vars['opt11'] = true;
        }
        if ($fields['optional_field_12']) {
            $tpl_vars['opt12'] = true;
        }
        if ($fields['optional_field_13']) {
            $tpl_vars['opt13'] = true;
        }
        if ($fields['optional_field_14']) {
            $tpl_vars['opt14'] = true;
        }
        if ($fields['optional_field_15']) {
            $tpl_vars['opt15'] = true;
        }
        if ($fields['optional_field_16']) {
            $tpl_vars['opt16'] = true;
        }
        if ($fields['optional_field_17']) {
            $tpl_vars['opt17'] = true;
        }
        if ($fields['optional_field_18']) {
            $tpl_vars['opt18'] = true;
        }
        if ($fields['optional_field_19']) {
            $tpl_vars['opt19'] = true;
        }
        if ($fields['optional_field_20']) {
            $tpl_vars['opt20'] = true;
        }
    }
} else {
    $tpl_vars['hidden_fields'] = true;
}

$tpl_vars['addonExtra'] = geoAddon::triggerDisplay(
    'module_search_box_add_search_fields',
    array ('page' => $page, 'show_module' => $show_module),
    geoAddon::ARRAY_STRING
);

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
        ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
