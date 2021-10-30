<?php

//This is where conditional queries go.
//For cases where an sql query might not be run, in the
//case that it is not run, add an empty string
//for the query.

//There needs to be the same number of sql queries generated, no
//matter what, otherwise the sql index will be off from the database.
//That is the reason to use an empty string in cases where an "optional" query
//is not run.


//conditional sql queries.
$sql_strict = array (
//array of sql queries, if one of these fail, it
//does not continue!

);

$sql_not_strict = array (
//array of sql queries, if one of these fail, it
//just ignores it and keeps chugin along.

);

//remove non-used column
# remove old column that is not used any more
$sql_not_strict[] = "ALTER TABLE `geodesic_pages` DROP `module_display_auction_id`";

$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD COLUMN `use_auto_title` TINYINT(1) NOT NULL DEFAULT 0";
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD COLUMN `auto_title` VARCHAR(255) NOT NULL DEFAULT 0";

$sql_not_strict[] = "ALTER TABLE `geodesic_auctions_final_fee_price_increments` ADD COLUMN `charge_fixed` DOUBLE(16,2) NOT NULL DEFAULT '0.00'";
$sql_not_strict[] = "ALTER TABLE `geodesic_auctions_final_fee_price_increments` CHANGE `charge` `charge` DOUBLE( 16, 2 ) NOT NULL DEFAULT '0.00'";

//Add charset column to language table
$sql_not_strict [] = " ALTER TABLE `geodesic_pages_languages` ADD `charset` ENUM( 'ISO-8859-1', 'ISO-8859-15', 'UTF-8', 'cp866', 'cp1251', 'cp1252', 'KOI8-R', 'BIG5', 'GB2312', 'BIG5-HKSCS', 'Shift_JIS', 'EUC-JP' ) NOT NULL DEFAULT 'ISO-8859-1'";

//field for entire_word check box in admin under Badwords
$sql_not_strict [] = "ALTER TABLE `geodesic_text_badwords` ADD `entire_word` TINYINT(1) NOT NULL DEFAULT '0'";

$sql_not_strict[] = "UPDATE `geodesic_credit_card_choices` SET `name` = 'AIM Method', `explanation` = 'This allows the use of AIM Method processors such as Authorize.net and PayTrace' WHERE `cc_id` = 1";

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_sell_session` ADD COLUMN `end_mode` TINYINT(4) NOT NULL";
$sql_not_strict[] = "ALTER TABLE `geodesic_pages_fonts` ADD COLUMN `custom_css` TEXT NOT NULL";

$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `classauctions` = '0' WHERE `message_id` = '500074' LIMIT 1";

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_sell_session` ADD COLUMN `address` VARCHAR(255)";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD COLUMN `location_address` VARCHAR(255)";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_ad_configuration` ADD COLUMN `use_address_field` TINYINT(4) NOT NULL DEFAULT '0'";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_ad_configuration` ADD COLUMN `editable_address_field` TINYINT(4) NOT NULL DEFAULT '0'";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_ad_configuration` ADD COLUMN `address_length` TINYINT(4) NOT NULL DEFAULT '50'";
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD COLUMN `use_address_field` TINYINT(4) NOT NULL DEFAULT '0'";
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD COLUMN `display_browsing_address_field` TINYINT(4) NOT NULL DEFAULT '0'";


//move credit card up in diplay order of transacion detail form
$query = "SELECT `name` FROM `geodesic_payment_choices` WHERE `payment_choice_id` = 5";
$result = $this->_db->Execute($query);
$result = $result->FetchRow();

if ($result['name'] == "Credit Card") {
    $sql_not_strict[] = "UPDATE `geodesic_payment_choices` SET `payment_choice_id` = '99' WHERE `payment_choice_id` =5 LIMIT 1";
    $sql_not_strict[] = "UPDATE `geodesic_payment_choices` SET `payment_choice_id` = '5' WHERE `payment_choice_id` =4 LIMIT 1";
    $sql_not_strict[] = "UPDATE `geodesic_payment_choices` SET `payment_choice_id` = '4' WHERE `payment_choice_id` =3 LIMIT 1";
    $sql_not_strict[] = "UPDATE `geodesic_payment_choices` SET `payment_choice_id` = '3' WHERE `payment_choice_id` =2 LIMIT 1";
    $sql_not_strict[] = "UPDATE `geodesic_payment_choices` SET `payment_choice_id` = '2' WHERE `payment_choice_id` =99 LIMIT 1";
} else {
    $sql_not_strict[] = "";
    $sql_not_strict[] = "";
    $sql_not_strict[] = "";
    $sql_not_strict[] = "";
    $sql_not_strict[] = "";
}

// Fix it to allow multiple classified sessions with same session ID
$result = $this->_db->Execute('SHOW INDEX FROM `geodesic_classifieds_sell_session`');
$remove_primary = false;
$add_index = true;
if (!$result) {
    //this table is changed in 4.0 so in testing and development this will throw an error
    //just skip if this is the case.
} else {
    while ($row = $result->FetchRow()) {
        if ($row['Column_name'] == 'session') {
            //this is an index from the session column.
            if ($row['Non_unique'] == 0) {
                //it is set to unique, so need to remove primary.
                $remove_primary = true;
            } else {
                //this is a normal index, so do not add another index for this column.
                $add_index = false;
            }
        }
        //echo '<pre>'.print_r($row,1).'</pre><br />';
    }
    if ($remove_primary) {
        $sql_strict[] = 'ALTER TABLE `geodesic_classifieds_sell_session` DROP PRIMARY KEY';
    } else {
        $sql_strict[] = '';
    }
    if ($add_index) {
        $sql_strict[] = 'ALTER TABLE `geodesic_classifieds_sell_session` ADD INDEX ( `session` )';
    } else {
        $sql_strict[] = '';
    }
}


//use this file for things like, checking if some file exists to determine what the query will be, ect.
