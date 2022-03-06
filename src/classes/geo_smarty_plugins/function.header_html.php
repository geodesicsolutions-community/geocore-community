<?php

//function.header_html.php


//This fella takes care of {header_html} - for backwards compatibility
function smarty_function_header_html($params, Smarty_Internal_Template $smarty)
{
    require_once CLASSES_DIR . 'geo_smarty_plugins/function.head_html.php';
    return smarty_function_head_html($params, $smarty);
}
