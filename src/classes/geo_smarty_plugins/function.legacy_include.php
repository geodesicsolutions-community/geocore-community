<?php

/**
 * Can be used for websites updating so they need minimal changes in custom system or module templates.
 *
 * @param array $params
 * @param Smarty_Internal_Template $smarty
 * @return void
 */
function smarty_function_legacy_include($params, Smarty_Internal_Template $smarty)
{
    $path = '';

    if (!empty($params['g_type'])) {
        $path .= $params['g_type'] . '/';
    } elseif (!empty($smarty->getTemplateVars('g_type'))) {
        $path .= $smarty->getTemplateVars('g_type') . '/';
    } elseif ($smarty instanceof geoTemplate && !empty($smarty->gType())) {
        /** @var geoTemplate $smarty */
        $path .= $smarty->gType() . '/';
    } elseif ($smarty->smarty instanceof geoTemplate && !empty($smarty->smarty->gType())) {
        /** @var geoTemplate $smarty->smarty */
        $path .= $smarty->smarty->gType() . '/';
    }

    if (!empty($params['g_resource'])) {
        $path .= $params['g_resource'] . '/';
    } elseif (!empty($smarty->getTemplateVars('g_resource'))) {
        $path .= $smarty->getTemplateVars('g_resource') . '/';
    } elseif ($smarty instanceof geoTemplate && !empty($smarty->gResource())) {
        /** @var geoTemplate $smarty */
        $path .= $smarty->gResource() . '/';
    } elseif ($smarty->smarty instanceof geoTemplate && !empty($smarty->smarty->gResource())) {
        /** @var geoTemplate $smarty->smarty */
        $path .= $smarty->smarty->gResource() . '/';
    }

    $path .= $params['file'];
    $copy = clone $smarty;
    $copy->assign($params);
    if ($params['assign']) {
        $copy->assign($params['assign'], $copy->fetch($path, null, null, $smarty));
        return '';
    }
    $copy->display($path, null, null, $smarty);
}
