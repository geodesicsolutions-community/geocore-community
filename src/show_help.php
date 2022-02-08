<?php

include_once('app_top.common.php');
// Formatting for help topics
$tpl = new geoTemplate('system', 'other');

if ($_REQUEST["a"]) {
    if ($_REQUEST["b"]) {
        if ($b == "1") {
            $sql = "select explanation from " . $site->sell_choices_types_table . " where type_id = ?";
            $explanation_result = $db->Execute($sql, array($_REQUEST['a']));
            if (!$explanation_result) {
                return false;
            } elseif ($explanation_result->RecordCount() == 1) {
                $show_explanation = $explanation_result->FetchRow();
                $tpl->assign('explanation', geoString::fromDB($show_explanation["explanation"]));
            }
        }
    } elseif ($_REQUEST["c"]) {
        $language_id = $db->getLanguage();
        $sql_query = "SELECT * FROM `geodesic_classifieds_sell_questions_languages` WHERE question_id = ?
            and language_id = ?";
        $language_specific_result = $db->Execute($sql_query, array($_REQUEST["c"],$language_id));
        if ((!$language_specific_result) || ($language_specific_result->RecordCount() != 1)) {
            //set the default language text from the classified_sell_questions_table
            //as the upgrade may have failed or not been run
            $sql = "select explanation from geodesic_classifieds_sell_questions where question_id = ?";
            $result = $db->Execute($sql, array($_REQUEST["c"]));
            if (!$result) {
                return false;
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
                $tpl->assign('explanation', stripslashes($show["explanation"]));
            }
        } else {
            $question_name_and_explanation = $language_specific_result->FetchRow();
            $tpl->assign('explanation', $question_name_and_explanation["explanation"]);
        }
    } else {
        $languageId = geoSession::getInstance()->getLanguage();
        $textId = (int)$_REQUEST['a'];

        if (!$textId) {
            return false;
        }
        //get the page ID
        $sql = "SELECT `page_id` FROM " . geoTables::pages_text_languages_table . " WHERE `text_id`=?
            AND `language_id`=?";
        $result = $db->GetRow($sql, array($textId, $languageId));
        if (!$result || !$result['page_id']) {
            return false;
        }
        $pageId = (int)$result['page_id'];
        $msgs = $db->get_text(true, $pageId);
        $tpl->assign('explanation', $msgs[$textId]);
    }
} elseif ($_REQUEST['addon'] && $_REQUEST['auth'] && $_REQUEST['textName']) {
    //enable use of help links from addons using addon text
    $text =& geoAddon::getText($_REQUEST['auth'], $_REQUEST['addon']);
    $tpl->assign('explanation', $text[$_REQUEST['textName']]);
}
echo $tpl->fetch('help_popup.tpl');
require_once GEO_BASE_DIR . 'app_bottom.php';
