<?php

function smarty_function_language_select($params, Smarty_Internal_Template $smarty)
{
    $db = DataAccess::getInstance();

    $result = $db->Execute("SELECT * FROM " . geoTables::pages_languages_table . " WHERE `active` = 1");
    if ($result->RecordCount() < 2) {
        //no need to show a selector without multiple languages...
        trigger_error('DEBUG DESIGN: skipping language selector because there are not enough languages to pick from');
        return '';
    }
    $params = $_GET;
    foreach ($result as $l) {
        $tpl_vars['languages'][$l['language_id']]['name'] = $l['language'];
        $params['set_language_cookie'] = $l['language_id'];
        $str = array();
        foreach ($params as $key => $value) {
            $str[] = "$key=$value";
        }
        $tpl_vars['languages'][$l['language_id']]['link'] = $_SERVER['SCRIPT_NAME'] . '?' . implode('&amp;', $str);
    }
    $tpl_vars['current_language'] = $db->getLanguage();
    return geoTemplate::loadInternalTemplate(
        $params,
        $smarty,
        'helpers/language_select.tpl',
        geoTemplate::MAIN_PAGE,
        '',
        $tpl_vars
    );
    ;
}
