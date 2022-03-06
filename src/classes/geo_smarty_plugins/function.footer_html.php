<?php

//function.footer_html.php


//This fella takes care of {footer_html}
function smarty_function_footer_html($params, Smarty_Internal_Template $smarty)
{
    if ($smarty->getTemplateVars('_inside_footer_html')) {
        //already in body var, prevent infinite recursion
        return '{footer_html}';
    }

    //figure out the file to use
    $file = '';
    $geo_inc_files = $smarty->getTemplateVars('geo_inc_files');

    if (isset($params['file'])) {
        //use file
        $file = $params['file'];
    } elseif (isset($geo_inc_files['footer_html'])) {
        $file = $geo_inc_files['footer_html'];
    }
    //first get any contents that may have been added "in line" inside of any
    //templates using the {add_footer_html} block
    $add_footer_html = geoView::getInstance()->_add_footer_html;
    //now generate the contents
    if (!$file) {
        //no main page file to use for template...
        return '' . $smarty->getTemplateVars('_footer_html') . $add_footer_html;
    }
    $tpl_vars = (array)$smarty->getTemplateVars('footer_vars');

    $tpl_vars['_inside_footer_html'] = 1;

    $g_type = $g_resource = null;
    if (isset($geo_inc_files['footer_html_addon'])) {
        $g_type = geoTemplate::ADDON;
        $g_resource = $geo_inc_files['footer_html_addon'];
    } elseif (isset($geo_inc_files['footer_html_system'])) {
        $g_type = geoTemplate::SYSTEM;
        $g_resource = $geo_inc_files['footer_html_system'];
    }
    //anything in footer html is displayed first so can be over-written by
    //stuff in template..  Then template contents..  then anything added using {add_footer_html}
    $pre = '' . $smarty->getTemplateVars('_footer_html');

    return geoTemplate::loadInternalTemplate($params, $smarty, $file, $g_type, $g_resource, $tpl_vars, $pre, $add_footer_html);
}
