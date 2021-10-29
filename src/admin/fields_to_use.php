<?php

//fields_to_use.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.09.0-79-gb63e5d8
##
##################################

class FieldsManage
{
    public function display_fields_to_use()
    {
        $admin = geoAdmin::getInstance();
        $view = $admin->v();
        $db = DataAccess::getInstance();

        $categoryId = (geoPC::is_ent() && isset($_GET['categoryId'])) ? (int)$_GET['categoryId'] : 0;
        $groupId = (geoPC::is_ent() && isset($_GET['groupId'])) ? (int)$_GET['groupId'] : 0;

        if (geoAjax::isAjax() && geoPC::is_ent() && isset($_GET['change'])) {
            //show the change box
            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign('categoryId', $categoryId);
            $tpl->assign('groupId', $groupId);

            if ($categoryId) {
                $tpl->assign('categoryName', geoCategory::getName($categoryId, true));
            }

            $tpl->assign('groups', $db->GetAssoc("SELECT `group_id`, `name` FROM " . geoTables::groups_table));

            $tpl->display('fields_to_use_change.tpl');
            $view->setRendered(true);
            return;
        }
        $tpl_vars = array ();
        //double check group/category ID, if appear to be valid, let template know
        //their names and settings.
        if ($categoryId) {
            $tpl_vars['categoryName'] = geoCategory::getName($categoryId, true);
            $tpl_vars['categoryWhat'] = $db->GetOne("SELECT `what_fields_to_use` FROM " . geoTables::categories_table . " WHERE `category_id`=$categoryId");
            if (!$tpl_vars['categoryWhat']) {
                //what fields to use not showing results, most likely an invalid category
                $admin->userNotice('Invalid category ID specified (' . $categoryId . '), showing site-wide default settings.');
                $categoryId = $groupId = 0;
            }
        }
        if ($groupId) {
            $row = $db->GetRow("SELECT `name`, `what_fields_to_use` FROM " . geoTables::groups_table . " WHERE `group_id`=$groupId");
            if (!$row) {
                $admin->userNotice('Invalid group ID specified (' . $groupId . '), showing site-wide default settings.');
                $groupId = $categoryId = 0;
            } else {
                $tpl_vars['groupName'] = $row['name'];
                $tpl_vars['groupWhat'] = $row['what_fields_to_use'];
            }
        }

        $fields = geoFields::getInstance($groupId, $categoryId);

        $tpl_vars['is_ent'] = geoPC::is_ent();
        $tpl_vars['categoryId'] = $categoryId;
        $tpl_vars['groupId'] = $groupId;

        $tpl_vars['default_fields'] = geoFields::getDefaultFields($groupId, $categoryId);
        $locations = array();
        $locations['pages'] = geoFields::getDefaultLocations($groupId, $categoryId, 'page');
        $locations['modules'] = geoFields::getDefaultLocations($groupId, $categoryId, 'module');
        $locations['pic_modules'] = geoFields::getDefaultLocations($groupId, $categoryId, 'pic_module');
        $locations['addons'] = geoFields::getDefaultLocations($groupId, $categoryId, 'addon');
        if (!count($locations['addons'])) {
            //no addon locations, don't show the tab
            unset($locations['addons']);
        }

        $tpl_vars['default_locations'] = $locations;
        $activeTab = 'main';
        if (isset($_GET['activeTab']) && in_array($_GET['activeTab'], array_keys($locations))) {
            $activeTab = $_GET['activeTab'];
            $tpl_vars['forceTab'] = true;
        }
        $tpl_vars['activeTab'] = $activeTab;

        if (geoPC::is_ent()) {
            //figure out type selections
            $sql = "SELECT * FROM " . geoTables::sell_choices_types_table;
            $tpl_vars['sell_question_types'] = $db->GetAssoc($sql);
        }
        $fieldsArray = $fields->toArray();
        foreach ($fieldsArray as $key => $field) {
            if (strpos($field['type_data'], ':use_other') !== false) {
                //the type data will have both the question ID AND :use_other if
                //use other box is enabled for dropdown
                $fieldsArray[$key]['type_data'] = (int)$field['type_data'];
                $fieldsArray[$key]['use_other'] = 1;
            }

            if ($field['field_name'] == 'cost_options' && $field['type_data']) {
                //special case for cost_options...  type data is | delimited
                $data = explode('|', $field['type_data']);

                if (count($data) == 2) {
                    $fieldsArray[$key]['field_max_groups'] = (int)$data[0];
                    $fieldsArray[$key]['field_max_options'] = (int)$data[1];
                }
                unset($data);
            }
        }
        $tpl_vars['fields'] = $fieldsArray;

        if (!$categoryId && !$groupId) {
            //get other misc. settings saved at bottom of page
            $miscSettings = $db->GetRow("SELECT `textarea_wrap`, `editable_category_specific` FROM " . geoTables::ad_configuration_table);

            $siteSettings = array (
                'display_ad_description_where',
                'display_all_of_description',
                'length_of_description',
                'entry_date_configuration',
                'member_since_date_configuration',
                'date_field_format',
                'date_field_format_short',
                'add_cost_at_top',
                'use_sitewide_auto_title',
                'allow_html_description_browsing',);
            foreach ($siteSettings as $name) {
                $miscSettings[$name] = $db->get_site_setting($name);
            }
            $tpl_vars['misc'] = $miscSettings;
            $tpl_vars['misc']['sitewide_auto_titles'] = explode('|', $db->get_site_setting('sitewide_auto_title'));

            while (count($tpl_vars['misc']['sitewide_auto_titles']) < 5) {
                $tpl_vars['misc']['sitewide_auto_titles'][] = 0;
            }
        } elseif ($categoryId && !$groupId) {
            $tpl_vars['misc'] = $db->GetRow("SELECT `display_ad_description_where`, `display_all_of_description`, `length_of_description`,
				`default_display_order_while_browsing_category` FROM " . geoTables::categories_table . " WHERE `category_id`=$categoryId");

            $order_by_array = array();
            $order_by_array[0] = "default site wide setting or no setting";
            $order_by_array[1] = "price ascending";
            $order_by_array[2] = "price descending";
            $order_by_array[3] = "placement date ascending";
            $order_by_array[4] = "placement date descending";
            $order_by_array[5] = "title ascending (alphabetical)";
            $order_by_array[6] = "title descending";
            $order_by_array[7] = "city ascending (alphabetical)";
            $order_by_array[8] = "city descending";
            $order_by_array[9] = "state ascending";
            $order_by_array[10] = "state descending";
            $order_by_array[11] = "country ascending";
            $order_by_array[12] = "country descending";
            $order_by_array[13] = "zip ascending";
            $order_by_array[14] = "zip descending";

            $order_by_array[15] = $db->get_site_setting('optional_field_1_name') . " ascending";
            $order_by_array[16] = $db->get_site_setting('optional_field_1_name') . " descending";
            $order_by_array[17] = $db->get_site_setting('optional_field_2_name') . " ascending";
            $order_by_array[18] = $db->get_site_setting('optional_field_2_name') . " descending";
            $order_by_array[19] = $db->get_site_setting('optional_field_3_name') . " ascending";
            $order_by_array[20] = $db->get_site_setting('optional_field_3_name') . " descending";
            $order_by_array[21] = $db->get_site_setting('optional_field_4_name') . " ascending";
            $order_by_array[22] = $db->get_site_setting('optional_field_4_name') . " descending";
            $order_by_array[23] = $db->get_site_setting('optional_field_5_name') . " ascending";
            $order_by_array[24] = $db->get_site_setting('optional_field_5_name') . " descending";
            $order_by_array[25] = $db->get_site_setting('optional_field_6_name') . " ascending";
            $order_by_array[26] = $db->get_site_setting('optional_field_6_name') . " descending";
            $order_by_array[27] = $db->get_site_setting('optional_field_7_name') . " ascending";
            $order_by_array[28] = $db->get_site_setting('optional_field_7_name') . " descending";
            $order_by_array[29] = $db->get_site_setting('optional_field_8_name') . " ascending";
            $order_by_array[30] = $db->get_site_setting('optional_field_8_name') . " descending";
            $order_by_array[31] = $db->get_site_setting('optional_field_9_name') . " ascending";
            $order_by_array[32] = $db->get_site_setting('optional_field_9_name') . " descending";
            $order_by_array[33] = $db->get_site_setting('optional_field_10_name') . " ascending";
            $order_by_array[34] = $db->get_site_setting('optional_field_10_name') . " descending";

            $order_by_array[45] = $db->get_site_setting('optional_field_11_name') . " ascending";
            $order_by_array[46] = $db->get_site_setting('optional_field_11_name') . " descending";
            $order_by_array[47] = $db->get_site_setting('optional_field_12_name') . " ascending";
            $order_by_array[48] = $db->get_site_setting('optional_field_12_name') . " descending";
            $order_by_array[49] = $db->get_site_setting('optional_field_13_name') . " ascending";
            $order_by_array[50] = $db->get_site_setting('optional_field_13_name') . " descending";
            $order_by_array[51] = $db->get_site_setting('optional_field_14_name') . " ascending";
            $order_by_array[52] = $db->get_site_setting('optional_field_14_name') . " descending";
            $order_by_array[53] = $db->get_site_setting('optional_field_15_name') . " ascending";
            $order_by_array[54] = $db->get_site_setting('optional_field_15_name') . " descending";
            $order_by_array[55] = $db->get_site_setting('optional_field_16_name') . " ascending";
            $order_by_array[56] = $db->get_site_setting('optional_field_16_name') . " descending";
            $order_by_array[57] = $db->get_site_setting('optional_field_17_name') . " ascending";
            $order_by_array[58] = $db->get_site_setting('optional_field_17_name') . " descending";
            $order_by_array[59] = $db->get_site_setting('optional_field_18_name') . " ascending";
            $order_by_array[60] = $db->get_site_setting('optional_field_18_name') . " descending";
            $order_by_array[61] = $db->get_site_setting('optional_field_19_name') . " ascending";
            $order_by_array[62] = $db->get_site_setting('optional_field_19_name') . " descending";
            $order_by_array[63] = $db->get_site_setting('optional_field_20_name') . " ascending";
            $order_by_array[64] = $db->get_site_setting('optional_field_20_name') . " descending";

            $order_by_array[43] = "business type ascending";
            $order_by_array[44] = "business type descending";
            $order_by_array[69] = "ending soonest";
            $order_by_array[70] = "reverse ending (farthest ending first)";
            $order_by_array[71] = "listings with no images first";
            $order_by_array[72] = "listings with at least one image first";

            $tpl_vars['order_by_array'] = $order_by_array;
        }

        if (isset($_POST['settings_posted']) && !isset($_POST['auto_save'])) {
            geoAdmin::m("Settings NOT Saved!  We did not receive the entire submitted form, some data
				was cut off, so none of the settings were saved.<br /><br />
				Contact your host to have this resolved, most likely the Suhosin Patch's
				settings need to be adjusted to not restrict the size of superglobals (your host should know what
				this means).", geoAdmin::ERROR);
        }

        $tpl_vars['admin_msgs'] = $admin->message();

        $view->setBodyTpl('fields_to_use.tpl')
            ->setBodyVar($tpl_vars);
    }

    public function update_fields_to_use()
    {
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $inputFields = $_POST['fields'];
        //die ('fields: <pre>'.print_r($inputFields,1));
        $categoryId = (geoPC::is_ent() && isset($_GET['categoryId'])) ? (int)$_GET['categoryId'] : 0;
        $groupId = (geoPC::is_ent() && isset($_GET['groupId'])) ? (int)$_GET['groupId'] : 0;


        if ($categoryId || $groupId) {
            $what_fields_to_use = (isset($_POST['what_fields_to_use'])) ? $_POST['what_fields_to_use'] : array();

            if (!$what_fields_to_use) {
                //failsafe sanity, this shouldn't happen normally
                $admin->userError('Which Group or Category fields to use not set properly!');
                return false;
            }

            $groupValid = $catValid = array ('own','site');
            $catValid[] = 'parent';//category can also be set to parent

            $useThese = true;

            if ($categoryId) {
                //lets update the category with fields to use setting
                $what = (in_array($what_fields_to_use['category'], $catValid)) ? $what_fields_to_use['category'] : 'parent';
                $sql = "UPDATE " . geoTables::categories_table . " SET `what_fields_to_use`=? WHERE `category_id`=? LIMIT 1";

                $result = $db->Execute($sql, array($what, $categoryId));
                if (!$result) {
                    $admin->userError('Internal DB error, please try again.  Debug: ' . $db->ErrorMsg());
                    return false;
                }
                if ($what != 'own') {
                    $useThese = false;
                    $sql = "DELETE FROM " . geoTables::fields . " WHERE `category_id`=?";
                    $result = $db->Execute($sql, array($categoryId));
                    if (!$result) {
                        $admin->userError('Internal DB error, please try again.  Debug: ' . $db->ErrorMsg());
                        return false;
                    }
                }
            }
            if ($groupId) {
                //lets update the group with fields to use setting
                $what = (in_array($what_fields_to_use['group'], $groupValid)) ? $what_fields_to_use['group'] : 'site';
                $sql = "UPDATE " . geoTables::groups_table . " SET `what_fields_to_use`=? WHERE `group_id`=? LIMIT 1";

                $result = $db->Execute($sql, array($what, $groupId));
                if (!$result) {
                    $admin->userError('Internal DB error, please try again.  Debug: ' . $db->ErrorMsg());
                    return false;
                }
                if ($what != 'own') {
                    $useThese = false;
                    $sql = "DELETE FROM " . geoTables::fields . " WHERE `group_id`=?";
                    $result = $db->Execute($sql, array($groupId));
                    if (!$result) {
                        $admin->userError('Internal DB error, please try again.  Debug: ' . $db->ErrorMsg());
                        return false;
                    }
                }
            }

            if (!$useThese) {
                //nothing more to do here, we are not using cat/group specific
                //settings so do not attempt to save.
                return true;
            }
        }
        if (!$categoryId && !$groupId) {
            //site wide misc. settings
            $misc = (isset($_POST['misc'])) ? $_POST['misc'] : array();

            $adSql = "UPDATE " . geoTables::ad_configuration_table . " SET `textarea_wrap`=?, `editable_category_specific`=?";
            $queryVars = array (
                ((isset($misc['textarea_wrap']) && $misc['textarea_wrap']) ? 1 : 0),
                ((isset($misc['editable_category_specific']) && $misc['editable_category_specific']) ? 1 : 0),
            );
            $db->Execute($adSql, $queryVars);

            //update site settings
            $db->set_site_setting('display_ad_description_where', ((isset($misc['display_ad_description_where']) && $misc['display_ad_description_where']) ? 1 : false));
            $db->set_site_setting('allow_html_description_browsing', ((isset($misc['allow_html_description_browsing']) && $misc['allow_html_description_browsing']) ? 1 : false));
            $db->set_site_setting('display_all_of_description', ((isset($misc['display_all_of_description']) && $misc['display_all_of_description']) ? 1 : false));
            $db->set_site_setting('length_of_description', (int)$misc['length_of_description']);
            $db->set_site_setting('entry_date_configuration', $misc['entry_date_configuration']);
            $db->set_site_setting('member_since_date_configuration', $misc['member_since_date_configuration']);
            $db->set_site_setting('date_field_format', $misc['date_field_format']);
            $db->set_site_setting('date_field_format_short', $misc['date_field_format_short']);

            if (geoPC::is_ent()) {
                $db->set_site_setting('add_cost_at_top', ((isset($misc['add_cost_at_top']) && $misc['add_cost_at_top']) ? 1 : false));
                $use_sitewide_auto_title = ((isset($misc['use_sitewide_auto_title']) && $misc['use_sitewide_auto_title']) ? 1 : false);
                $db->set_site_setting('use_sitewide_auto_title', $use_sitewide_auto_title);
                if ($use_sitewide_auto_title) {
                    $db->set_site_setting('sitewide_auto_title', implode('|', $misc['sitewide_auto_titles']));
                }
            }
        }

        if ($categoryId && !$groupId) {
            //category specific misc. settings
            $misc = (isset($_POST['misc'])) ? $_POST['misc'] : array();

            $display_ad_description_where = ((isset($misc['display_ad_description_where']) && $misc['display_ad_description_where']) ? 1 : 0);
            $display_all_of_description = ((isset($misc['display_all_of_description']) && $misc['display_all_of_description']) ? 1 : 0);
            $length_of_description = (int)$misc['length_of_description'];
            $default_display_order_while_browsing_category = (int)$misc['default_display_order_while_browsing_category'];

            $sql = "UPDATE " . geoTables::categories_table . " SET `display_ad_description_where`=$display_ad_description_where, 
			`display_all_of_description`=$display_all_of_description,
			`length_of_description`=$length_of_description,
			`default_display_order_while_browsing_category`=$default_display_order_while_browsing_category WHERE `category_id`=$categoryId";

            $db->Execute($sql);
        }

        $fields = geoFields::getInstance($groupId, $categoryId);

        if ($fields->getCategoryId() != $categoryId) {
            //should not get here with the checks done above, this just a failsafe
            $admin->userError('Not able to set the fields for this category! requested: ' . $categoryId . ' actual: ' . $fields->getCategoryId());
            return false;
        }

        if ($fields->getGroupId() != $groupId) {
            //should not get here with the checks done above, this just a failsafe
            $admin->userError('Not able to set the fields for this group!');
            return false;
        }

        $defaultFields = geoFields::getDefaultFields($groupId, $categoryId);
        $defaultLocations = array_keys(geoFields::getDefaultLocations($groupId, $categoryId));

        //so that tricky admin user doesn't try to trick us with fancy input
        //altering, only save stuff we already know are the fields

        foreach ($defaultFields as $sectionName => $sectionFields) {
            $fieldNames = array_keys($sectionFields['fields']);

            foreach ($fieldNames as $fieldName) {
                //OK Then, we can assume that $fields->$fieldName is set
                //because that also checks the default fields and fills in blank
                //ones for ones not set yet.
                $field = $fields->$fieldName;


                $inputField = (isset($inputFields[$sectionName][$fieldName])) ? $inputFields[$sectionName][$fieldName] : array();

                $switches = array ('is_enabled', 'is_required','can_edit');
                if (isset($defaultFields[$sectionName]['fields'][$fieldName]['type_extra']) && $defaultFields[$sectionName]['fields'][$fieldName]['type_extra'] == 'on_off') {
                    //type_data is being used as an on/off switch
                    $switches[] = 'type_data';
                }
                foreach ($switches as $switch) {
                    //simple 1/0 switches (which are checkboxes)
                    $field->$switch = (isset($inputField[$switch]) && $inputField[$switch]) ? 1 : 0;
                }

                if (isset($sectionFields['fields'][$fieldName]['type_select']) && $sectionFields['fields'][$fieldName]['type_select']) {
                    //handle field types

                    $fieldType = $inputField['field_type'];
                    if (in_array($fieldType, array ('text','textarea','url','email','number','cost','date','dropdown','other'))) {
                        if ($field->field_type !== $fieldType) {
                            //field type is changing, so clear the field type data
                            $field->type_data = '';
                        }

                        $field->field_type = $inputField['field_type'];
                    } elseif (is_numeric($fieldType) && $fieldType > 0) {
                        //assume dropdown

                        //TODO: Make this work better for custom save types that might use this differently
                        $field->field_type = 'dropdown';

                        $typeData = '' . (int)$fieldType;
                        if ($inputField['use_other']) {
                            $typeData .= ':use_other';
                        }

                        $field->type_data = $typeData;
                    }
                }

                if ($fieldName == 'email') {
                    //email field -- save "reveal on listing display page" setting to type_data
                    $field->type_data = $inputField['type_data'];
                }

                if (isset($inputField['field_max_tags'])) {
                    $field->type_data = (int)$inputField['field_max_tags'];
                }

                if (isset($inputField['field_max_groups'], $inputField['field_max_options'])) {
                    //Expects max field group and max options to be set in type_data with | delimiter
                    $field_max_groups = (int)max(0, $inputField['field_max_groups']);
                    $field_max_options = (int)max(0, $inputField['field_max_options']);
                    $field->type_data = "$field_max_groups|$field_max_options";
                }

                $field->text_length = (isset($inputField['text_length']) && $inputField['text_length']) ? (int)$inputField['text_length'] : 0;
                $locations = array ();
                foreach ($defaultLocations as $location) {
                    if (isset($inputField['display_locations'][$location]) && $inputField['display_locations'][$location]) {
                        $locations[] = $location;
                    }
                }
                $field->display_locations = $locations;
                if (
                    geoPC::is_ent() && $sectionFields['fields'][$fieldName]['opt_name_set']
                    && !($categoryId || $groupId) && $inputField['label']
                ) {
                    //set optional field name
                    $opt_num = (int)$sectionFields['fields'][$fieldName]['opt_num'];
                    $db->set_site_setting('optional_field_' . $opt_num . '_name', $inputField['label']);
                }
            }
        }
        //TODO: Give addons a chance to make changes to fields before they are serialized

        return $fields->serialize();
    }
}
