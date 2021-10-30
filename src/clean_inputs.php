<?php

//clean_inputs.php


//list of keys to NOT filter.
#READ BELOW FOR NOTE ON 3RD PARTY FIELDS
$no_filter_list = array (
    'description',
    'affiliate_html',
    'pageBody',
    'messageBody',
    'storefrontNote',
    'imageSlots',
    'videoSlots',
    'contact',
    'rawChars',
    'tags', 'tag',
    );

//If there are any 3rd party custom fields, you can specify them to not be
//filtered by creating an array named $no_filter_list_extra before
//calling this file in config.php, with structure similar to the array above.
//This way, no editing of this file is needed
//to stop the filtering of custom fields.
if (isset($no_filter_list_extra) && is_array($no_filter_list_extra) && count($no_filter_list_extra)) {
    //add any custom (3rd party) fields to the list of fields not to filter.
    $no_filter_list = array_merge($no_filter_list, $no_filter_list_extra);
}
if (!defined('CLEAN_INPUTS')) {
    if (!function_exists('recursive_clean')) {
        function recursive_clean($input_vars, $no_filter_var, $only_fix_magic_quotes = false)
        {
            if (!class_exists('geoString')) {
                //cannot recursive clean until the geoString class is included.
                return $input_vars;
            }
            //geoString class exists, so can clean inputs now.
            if (!defined('CLEAN_INPUTS')) {
                define('CLEAN_INPUTS', 1);
            }
            if (is_array($input_vars)) {
                foreach ($input_vars as $key => $value) {
                    if (!in_array($key, $no_filter_var, true)) {
                        $input_vars[$key] = recursive_clean($value, $no_filter_var, $only_fix_magic_quotes);
                    } else {
                        //if we do not clean, the least we can do is fix the magic quotes.
                        $input_vars[$key] = recursive_clean($value, $no_filter_var, true);
                    }
                }
            } else {
                //make it so that we don't have to worry about magic quotes anywhere in the software.
                $input_vars = ((get_magic_quotes_gpc() == 1) ? stripslashes($input_vars) : $input_vars);

                if (!$only_fix_magic_quotes) {
                    //only htmlentity it if we are not just fixing magic quotes.
                    $input_vars = geoString::specialChars($input_vars);
                }
            }
            return $input_vars;
        }
    }
    if (defined('IN_ADMIN') || defined('CLEAN_INPUTS_MAGIC_ONLY')) {
        //if we are in the admin, or somewhere else that should not filter inputs,
        //so do not filter all inputs.

        //Just fix the magic quotes.
        $only_magic = true;
    } else {
        //clean all inputs recursevly, since sometimes we have arrays inside arrays.
        //echo 'in main.';
        $only_magic = false;
    }
    $_REQUEST = recursive_clean($_REQUEST, $no_filter_list, $only_magic);
    $_GET = recursive_clean($_GET, $no_filter_list, $only_magic);
    $_POST = recursive_clean($_POST, $no_filter_list, $only_magic);
}
