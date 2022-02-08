<?php

include_once('../ini_tools.php');
//make sure it is at least 32 megs.
//geoRaiseMemoryLimit('64M');

include_once('../config.default.php');
require_once GEO_BASE_DIR . 'vendor/adodb/adodb-php/adodb.inc.php';

// phpcs:disable

class upgrade
{

    /**
     * Holds the ADODB db object
     *
     * @var object
     */
    var $db;

    /**
     * var $tablesToCopy
     *
     * Contains a lists of tables from the new install that should have data copied to it
     */
    var $tablesToCopy;

    /**
     * var $updateOnly
     *
     * contains column names that should only be updated
     * these are the configuration tables
     */
    var $updateOnly;

    /**
     * var $ignoreColumn
     *
     * contains column names that should only be ommited
     * these are the configuration tables
     */
    var $ignoreColumn;

    /**
     * var $columnDefaults
     *
     * contains default values for columns that weren't in the older versions
     * the key contains the new table name
     * the value contains another array with the key being the column name and the value being the value
     */
    var $columnDefaults;

    /**
     * var $renamedTables
     *
     * tables that have changed names in the CA DB
     */
    var $renamedTables = null;

    /**
     * var $renamedTables
     *
     * tables that have changed names in the CA DB
     */
    var $upgradeType;

     /**
      * Map of old price plans to new ones
      * @var array
      */
     var $pricePlanMap = array ();

    public function __construct()
    {
        $this->_connectDB();
    }

    function _connectDB()
    {
        if (!(isset($this->db) && is_object($this->db))) {
            include(GEO_BASE_DIR . 'config.default.php');
            $this->db =& ADONewConnection($db_type);

            if (isset($persistent_connections) && $persistent_connections) {
                if (!$this->db->PConnect($db_host, $db_username, $db_password, $database)) {
                    echo 'Could not connect to database (persistent connection).';
                    exit;
                }
            } else {
                if (!$this->db->Connect($db_host, $db_username, $db_password, $database)) {
                    echo "Could not connect to database.";
                    exit;
                }
            }
        }
        //fix SQL strict mode.
        $this->db->Execute('SET SESSION sql_mode=\'\'');
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC) ;
    }

    function getUpgradeType()
    {
        if ($this->upgradeType !== null) {
            return $this->upgradeType;
        }
        if ($this->db->Execute("select * from old_geodesic_auctions_logins limit 1")) {
            $this->upgradeType = "auctions";
            return $this->upgradeType;
        } elseif ($this->db->Execute("select * from old_geodesic_classifieds_logins limit 1")) {
            $this->upgradeType = "classifieds";
            return $this->upgradeType;
        } else {
            //probably need to run the
            debug(__line__ . ' not sure what type?<br />');
        }
    }

    function initiateClassifiedUpgrade()
    {
        $this->tablesToCopy = array(
            "geodesic_api_installation_info",
            "geodesic_ad_filter",
            "geodesic_ad_filter_categories",
            "geodesic_categories",
            "geodesic_cc_authorizenet",
            "geodesic_cc_authorizenet_transactions",
            "geodesic_cc_bitel",
            "geodesic_cc_bitel_transactions",
            "geodesic_cc_linkpoint",
            "geodesic_cc_linkpoint_transactions",
            "geodesic_cc_twocheckout",
            "geodesic_cc_twocheckout_transactions",
            "geodesic_choices",
            "geodesic_classifieds",
            "geodesic_classifieds_ad_configuration",
            "geodesic_classifieds_ads_extra",
            "geodesic_classifieds_categories_languages",
            "geodesic_classifieds_configuration",
            "geodesic_classifieds_expirations",
            "geodesic_classifieds_expired",
            "geodesic_classifieds_images",
            "geodesic_classifieds_images_urls",
            "geodesic_classifieds_messages_form",
            "geodesic_classifieds_messages_past",
            "geodesic_classifieds_messages_past_recipients",
            "geodesic_classifieds_price_increments",
            "geodesic_classifieds_price_plans",
            "geodesic_classifieds_price_plans_categories",
            "geodesic_classifieds_sell_questions",
            "geodesic_classifieds_sell_question_choices",
            "geodesic_classifieds_sell_question_types",
            "geodesic_classifieds_transactions",
            "geodesic_classifieds_user_credits",
            "geodesic_classifieds_user_subscriptions",
            "geodesic_confirm",
            "geodesic_confirm_email",
            "geodesic_countries",
            "geodesic_credit_card_choices",
            "geodesic_currency_types",
            "geodesic_favorites",
            "geodesic_file_types",
            "geodesic_groups",
            "geodesic_html_allowed",
            "geodesic_logins",
            "geodesic_paypal_transactions",
            "geodesic_price_plan_ad_lengths",
            "geodesic_states",
            "geodesic_text_badwords",
            "geodesic_user_communications",
            "geodesic_user_groups_price_plans",
            "geodesic_userdata",
            "geodesic_userdata_history",
            "geodesic_worldpay_settings",
            "geodesic_worldpay_transactions"
            );

        $this->updateOnly = array(
            "geodesic_api_installation_info",
            "geodesic_classifieds_configuration",
            "geodesic_classifieds_ad_configuration",
            "geodesic_worldpay_settings",
            "geodesic_cc_authorizenet",
            "geodesic_cc_bitel",
            "geodesic_cc_linkpoint",
            "geodesic_cc_twocheckout"
        );

        $this->ignoreColumn = array(
            "geodesic_classifieds_ad_configuration" => array("user_ad_template","user_extra_template",
                "user_checkbox_template","auctions_user_ad_template","auctions_user_extra_template",
                "auctions_user_checkbox_template","full_size_image_template","ad_detail_print_friendly_template",
                "auction_detail_print_friendly_template"),
            "geodesic_classifieds_configuration" => array("buy_now_image","reserve_met_image")
        );

        $this->columnDefaults = array(
            "geodesic_categories" => array("what_fields_to_use" => "parent"),
            "geodesic_classifieds" => array("item_type" => "1"),
            "geodesic_classifieds_price_plans" => array("applies_to" => "1")
        );

        $this->renamedTables = array(
            "geodesic_ad_filter" => "old_geodesic_classifieds_ad_filter",
            "geodesic_ad_filter_categories" => "old_geodesic_classifieds_ad_filter_categories",
            "geodesic_categories" => "old_geodesic_classifieds_categories",
            "geodesic_choices" => "old_geodesic_classifieds_choices",
            "geodesic_countries" => "old_geodesic_classifieds_countries",
            "geodesic_favorites" => "old_geodesic_classifieds_favorites",
            "geodesic_file_types" => "old_geodesic_classifieds_file_types",
            "geodesic_groups" => "old_geodesic_classifieds_groups",
            "geodesic_html_allowed" => "old_geodesic_classifieds_html_allowed",
            "geodesic_logins" => "old_geodesic_classifieds_logins",
            "geodesic_payment_choices" => "old_geodesic_classifieds_payment_choices",
            "geodesic_pages_languages" => "old_geodesic_text_languages",
            "geodesic_registration_session" => "old_geodesic_classifieds_registration_session",
            "geodesic_states" => "old_geodesic_classifieds_states",
            "geodesic_user_communications" => "old_geodesic_classifieds_user_communications",
            "geodesic_user_groups_price_plans" => "old_geodesic_classifieds_user_groups_price_plans",
            "geodesic_userdata" => "old_geodesic_classifieds_userdata",
            "geodesic_userdata_history" => "old_geodesic_classifieds_userdata_history"
        );
    }

    function initiateAuctionUpgrade()
    {
        $this->tablesToCopy = array(
           "geodesic_api_installation_info",
           "geodesic_ad_filter",
           "geodesic_ad_filter_categories",
           "geodesic_auctions_autobids",
           "geodesic_auctions_bids",
           "geodesic_auctions_feedbacks",
           "geodesic_auctions_final_fee_price_increments",
           "geodesic_auctions_increments",
           "geodesic_categories",
           "geodesic_cc_authorizenet",
           "geodesic_cc_authorizenet_transactions",
           "geodesic_cc_twocheckout",
           "geodesic_cc_twocheckout_transactions",
           "geodesic_choices",
           "geodesic_classifieds",
           "geodesic_classifieds_ad_configuration",
           "geodesic_classifieds_ads_extra",
           "geodesic_classifieds_categories_languages",
           "geodesic_classifieds_configuration",
           "geodesic_classifieds_expirations",
           "geodesic_classifieds_expired",
           "geodesic_classifieds_images",
           "geodesic_classifieds_images_urls",
           "geodesic_classifieds_messages_form",
           "geodesic_classifieds_messages_past",
           "geodesic_classifieds_messages_past_recipients",
           "geodesic_classifieds_price_plans",
           "geodesic_classifieds_price_plans_extras",
           "geodesic_classifieds_sell_questions",
           "geodesic_classifieds_sell_question_choices",
           "geodesic_classifieds_sell_question_types",
           "geodesic_classifieds_transactions",
           "geodesic_classifieds_user_configuration",
           "geodesic_classifieds_user_credits",
           "geodesic_classifieds_user_subscriptions",
           "geodesic_confirm",
           "geodesic_confirm_email",
           "geodesic_countries",
           "geodesic_currency_types",
           "geodesic_favorites",
           "geodesic_file_types",
           "geodesic_groups",
           "geodesic_html_allowed",
           "geodesic_logins",
           "geodesic_paypal_transactions",
           "geodesic_states",
           "geodesic_text_badwords",
           "geodesic_user_communications",
           "geodesic_user_groups_price_plans",
           "geodesic_userdata",
           "geodesic_userdata_history",
           "geodesic_worldpay_settings",
           "geodesic_worldpay_transactions"
           );

        $this->updateOnly = array(
           "geodesic_api_installation_info",
           "geodesic_classifieds_configuration",
           "geodesic_classifieds_ad_configuration",
           "geodesic_worldpay_settings",
           "geodesic_cc_authorizenet",
           "geodesic_cc_bitel",
           "geodesic_cc_linkpoint",
           "geodesic_cc_twocheckout"
        );

        $this->ignoreColumn = array(
           "geodesic_classifieds_ad_configuration" => array("user_ad_template","user_extra_template","user_checkbox_template","auctions_user_ad_template","auctions_user_extra_template","auctions_user_checkbox_template","full_size_image_template","ad_detail_print_friendly_template","auction_detail_print_friendly_template")
        );

        $this->columnDefaults = array(
           "geodesic_categories" => array("what_fields_to_use" => "parent"),
           "geodesic_classifieds" => array("item_type" => "2"),
           "geodesic_classifieds_price_plans" => array("applies_to" => "2")
        );

        $this->renamedTables = array(
           "geodesic_classifieds" => "old_geodesic_auctions",
           "geodesic_classifieds_ad_configuration" => "old_geodesic_auctions_ad_configuration",
           "geodesic_ad_filter" => "old_geodesic_auctions_ad_filter",
           "geodesic_ad_filter_categories" => "old_geodesic_auctions_ad_filter_categories",
           "geodesic_classifieds_ads_extra" => "old_geodesic_auctions_ads_extra",
           "geodesic_categories" => "old_geodesic_auctions_categories",
           "geodesic_classifieds_categories_languages" => "old_geodesic_auctions_categories_languages",
           "geodesic_choices" => "old_geodesic_auctions_choices",
           "geodesic_classifieds_configuration" => "old_geodesic_auctions_configuration",
           "geodesic_countries" => "old_geodesic_auctions_countries",
           "geodesic_classifieds_expirations" => "old_geodesic_auctions_expirations",
           "geodesic_classifieds_expired" => "old_geodesic_auctions_expired",
           "geodesic_favorites" => "old_geodesic_auctions_favorites",
           "geodesic_file_types" => "old_geodesic_auctions_file_types",
           "geodesic_groups" => "old_geodesic_auctions_groups",
           "geodesic_html_allowed" => "old_geodesic_auctions_html_allowed",
           "geodesic_classifieds_images" => "old_geodesic_auctions_images",
           "geodesic_classifieds_images_urls" => "old_geodesic_auctions_images_urls",
           "geodesic_logins" => "old_geodesic_auctions_logins",
           "geodesic_classifieds_messages_form" => "old_geodesic_auctions_messages_form",
           "geodesic_classifieds_messages_past" => "old_geodesic_auctions_messages_past",
           "geodesic_classifieds_messages_past_recipients" => "old_geodesic_auctions_messages_past_recipients",
           "geodesic_classifieds_price_plans" => "old_geodesic_auctions_price_plans",
           "geodesic_classifieds_price_plans_extras" => "old_geodesic_auctions_price_plans_extras",
           "geodesic_classifieds_sell_question_choices" => "old_geodesic_auctions_sell_question_choices",
           "geodesic_classifieds_sell_question_types" => "old_geodesic_auctions_sell_question_types",
           "geodesic_classifieds_sell_questions" => "old_geodesic_auctions_sell_questions",
           "geodesic_states" => "old_geodesic_auctions_states",
           "geodesic_classifieds_transactions" => "old_geodesic_auctions_transactions",
           "geodesic_user_communications" => "old_geodesic_auctions_user_communications",
           "geodesic_classifieds_user_configuration" => "old_geodesic_auctions_user_configuration",
           "geodesic_classifieds_user_credits" => "old_geodesic_auctions_user_credits",
           "geodesic_user_groups_price_plans" => "old_geodesic_auctions_user_groups_price_plans",
           "geodesic_classifieds_user_subscriptions" => "old_geodesic_auctions_user_subscriptions",
           "geodesic_userdata" => "old_geodesic_auctions_userdata",
           "geodesic_userdata_history" => "old_geodesic_auctions_userdata_history"
           );
    }

    function getColumnNames($table)
    {
        $all = $this->db->GetAll("SHOW COLUMNS FROM " . $table);
        if (!$all) {
            debug(__line__ . ' DB Error (this one is probably not a problem): ' . $this->db->ErrorMsg());
            return array();
        }
        $names = array();
        //die ('row: <pre>'.print_r($row,1));
        foreach ($all as $row) {
            $names[] = $row['Field'];
        }

        return $names;
    }

    function getColumnsToCopy($newTableNames, $oldTableNames)
    {
        debug(__line__ . " getting copies<br />");
        if (!is_array($oldTableNames) || !is_array($newTableNames)) {
            throw new Exception("geo columns, should be 2 arrays, but instead: new:<pre>" . print_r($newTableNames, 1) . "<br />old:<br />" . print_r($oldTableNames, 1) . "</pre>");
            return array();
        }
        return array_intersect($newTableNames, $oldTableNames);
    }

    function getSelectSQL($oldTableName, $columnNames)
    {
        $string = "SELECT ";
        foreach ($columnNames as $name) {
            $string .= $name . ", ";
        }
        $string = substr_replace($string, '', -2, 2);
        $string .= " FROM " . $oldTableName;
        return $string;
    }

    function getInsertSQL($newTableName, $columnNames, $values)
    {
        //check for default values
        $defaultIfNull = array();
        $defaultOnly = array();
        debug(__line__ . " new table name: $newTableName ");
        if (isset($this->columnDefaults[$newTableName])) {
            $defaultIfNull = array_intersect($columnNames, array_keys($this->columnDefaults[$newTableName]));
            $defaultOnly = array_diff(array_keys($this->columnDefaults[$newTableName]), $columnNames);
        }

        foreach ($columnNames as $columnName) {
            if (!isset($this->ignoreColumn[$newTableName]) || !in_array($columnName, $this->ignoreColumn[$newTableName])) {
                $columnString .= $columnName . ", ";
                if (in_array($columnName, $defaultIfNull) && !isset($values[$columnName])) {
                    $valueString .= "\"" . addslashes($this->columnDefaults[$newTableName][$columnName]) . "\", ";
                } else {
                    $valueString .= "\"" . addslashes($values[$columnName]) . "\", ";
                }
            }
        }

        foreach ($defaultOnly as $columnName) {
            $columnString .= $columnName . ", ";
            $valueString .= "\"" . addslashes($this->columnDefaults[$newTableName][$columnName]) . "\", ";
        }

        $columnString = substr_replace($columnString, '', -2, 2);
        $valueString = substr_replace($valueString, '', -2, 2);

        $string = "INSERT INTO " . $newTableName . " ( ";
        $string .= $columnString;
        $string .= " ) VALUES ( ";
        $string .= $valueString;
        $string .= " )";
        debug(__line__ . ' SQL: ' . $string);
        return $string;
    }

    function getUpdateSQL($newTableName, $columnNames, $values, $where = null)
    {
        //check for default values
        $defaultIfNull = array();
        $defaultOnly = array();
        if (isset($this->columnDefaults[$newTableName]) && $where == null) {
            $defaultIfNull = array_intersect($columnNames, array_keys($this->columnDefaults[$newTableName]));
            $defaultOnly = array_diff(array_keys($this->columnDefaults[$newTableName]), $columnNames);
        }

        $string = "UPDATE " . $newTableName . " SET ";
        foreach ($columnNames as $columnName) {
            debug(__line__);
            if (!isset($this->ignoreColumn[$newTableName]) || !in_array($columnName, $this->ignoreColumn[$newTableName])) {
                $string .= $columnName;
                $string .= " = ";
                if (in_array($columnName, $defaultIfNull) && !isset($values[$columnName])) {
                    $string .= "\"" . $this->columnDefaults[$newTableName][$columnName] . "\", ";
                } else {
                    $string .= "\"" . $values[$columnName] . "\", ";
                }
            }
        }
        debug(__line__);
        if (!is_array($defaultOnly)) {
            throw new Exception("default only is not array, it is: <pre>" . print_r($defaultOnly, 1) . "</pre>");
            $defaultOnly = array();
        }
        foreach ($defaultOnly as $columnName) {
            $string .= $columnName;
            $string .= " = ";
            $string .= "\"" . $this->columnDefaults[$newTableName][$columnName] . "\", ";
        }

        $string = substr_replace($string, '', -2, 2);

        if ($where !== null) {
            $string .= " WHERE " . $where[0] . " = " . $where[1];
        }
        debug(__line__ . ' update sql: ' . $string);
        return $string;
    }

    function getTruncateSQL($oldTableName)
    {
        $string = "TRUNCATE TABLE `$oldTableName`";
        return $string;
    }

    function getAssoc($values)
    {
        //fix adodb's dumb result set
        return ($values);
        debug(__line__);
        if (!is_array($values)) {
            return $values;
        }
        $valuesFixed = array();
        foreach ($values as $key => $value) {
            if (!is_numeric($key)) {
                $valuesFixed[$key] = $value;
            }
        }
        return $valuesFixed;
    }

    function get_sql_in_statement($category_id)
    {
        $this->subcategory_array = array();
        $this->get_sql_in_array($category_id);
        if (count($this->subcategory_array) > 0) {
            $this->in_statement = "";
            $this->in_statement .= " in (";
            while (list($key,$value) = each($this->subcategory_array)) {
                if ($key == 0) {
                    $this->in_statement .= $value;
                } else {
                    $this->in_statement .= "," . $value;
                }
            }
            $this->in_statement .= ")";
            return $this->in_statement;
        } else {
            return false;
        }
    }

    function get_sql_in_array($category_id)
    {
        $count = 0;

        $sql_query = "select category_id from geodesic_categories where parent_id = " . $category_id;
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            while ($show_category = $result->FetchRow()) {
                $this->get_sql_in_array($show_category["category_id"]);
            }
        }
        array_push($this->subcategory_array, $category_id);

        return true;
    }

    function renameTable($table, $newName)
    {
        return "ALTER TABLE `" . $table . "` RENAME `" . $newName . "`; <br>";
    }

    function fixAttentionGetters()
    {
        $sql_query = "
			INSERT INTO `geodesic_choices` (`type_of_choice`, `display_value`, `value`, `numeric_value`, `display_order`, `language_id`) VALUES (559, 10, 'must sell', 'images/attn_gtrs/must_sell.gif', 0, 0, 1),
			(10, 'make offer', 'images/attn_gtrs/make_offer.gif', 0, 0, 1),
			(10, 'hot rod', 'images/attn_gtrs/hot_rod.gif', 0, 0, 1),
			(10, 'hot deal', 'images/attn_gtrs/hot_deal.gif', 0, 0, 1),
			(10, 'great deal', 'images/attn_gtrs/great_deal.gif', 0, 0, 1),
			(10, 'sweetheart', 'images/attn_gtrs/sweetheart.gif', 0, 0, 1),
			(10, 'dont miss this', 'images/attn_gtrs/dont_miss.gif', 0, 0, 1),
			(10, 'heart throb', 'images/attn_gtrs/heart_throb.gif', 0, 0, 1),
			(10, 'sweet ride', 'images/attn_gtrs/sweet_ride.gif', 0, 0, 1),
			(10, 'too hot', 'images/attn_gtrs/too_hot.gif', 0, 0, 1),
			(10, 'wow', 'images/attn_gtrs/wow.gif', 0, 0, 1),
			(10, 'price lowered', 'images/attn_gtrs/price_lowered.gif', 0, 0, 1);";
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            return false;
        }
        return true;
    }

    function fixQuestionLanguages($oldValues)
    {
        $sql = "INSERT INTO `geodesic_classifieds_sell_questions_languages` SET
			`question_id`=?, `language_id`=1, `name`=?, `explanation`=?, `choices`=?";
        $result = $this->db->Execute($sql, array($oldValues['question_id'], $oldValues['name'] . '',$oldValues['explanation'] . '', $oldValues['choices'] . ''));
        if (!$result) {
            debug(__line__ . " DB Error: " . $this->db->ErrorMsg());
        }
    }
}
