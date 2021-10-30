<?php

##
## This file contains the nuts and bolts of the LEGACY text importer.
##
## It's kept around because the new one doesn't always play nice with all servers
##


function getMaxLineLength($fileName)
{

    $length = 512;
    $array = file($fileName);
    foreach ($array as $value) {
        if (strlen($value) > $length) {
            $length = strlen($value) + 1;
        }
    }
    return $length;
}

function doImport($language, $filename = null)
{

    $menu_loader = geoAdmin::getInstance();
    $db = DataAccess :: getInstance();
    $addon = geoAddon::getInstance();

    if ($filename === null) {
        $filename = TRANSLATED_FILE;
    }

    $rFile = fopen($filename, "r");
    $stmt = $db->Prepare("UPDATE " . geoTables::pages_text_languages_table . " SET
		`text` = ? WHERE `text_id` = ? AND `page_id` = ? AND `language_id` = ?");

    $length = getMaxLineLength(TRANSLATED_FILE);

    $pre = 'import_text_';

    $offset = (int)$db->get_site_setting($pre . 'offset');
    $lastImport = $db->get_site_setting($pre . 'lastImport');
    if ($lastImport != $filename) {
        $offset = 0;
    }
    if (!$offset) {
//reset everything just to be sure
        $db->set_site_setting($pre . 'offset', 0);
        $db->set_site_setting($pre . 'successes', false);
        $db->set_site_setting($pre . 'errors', false);
        $offset = $successes = $errors = 0;
    } else {
        fseek($rFile, $offset);
        $errors = (int)$db->get_site_setting($pre . 'errors');
        $successes = (int)$db->get_site_setting($pre . 'successes');
    }
    $db->set_site_setting($pre . 'lastImport', $filename);

    @set_time_limit(360);
    while (false !== (!feof($rFile) && $row = fgetcsv($rFile, $length))) {
        if (count($row) == 6) {
            if (is_numeric($row[0])) {
                //this is normal page text
                if (false === $db->Execute($stmt, array(geoString::toDB(($row[5])), $row[0], $row[1], $language))) {
                    trigger_error("ERROR SQL: Error inserting language.<br />" . $db->ErrorMsg());
                    $errors++;
                    $db->set_site_setting($pre . 'errors', $errors);
                } else {
                    $successes++;
                    $db->set_site_setting($pre . 'successes', $successes);
                }
            } elseif (strpos($row[0], 'addon.') === 0) {
            //addon!
                $parts = explode('.', $row[0]);
                if (count($parts) == 3) {
                    //should be in 3 parts, starts with addon, then addon name, then auth tag.
                    $addonName = $parts[1];
                    $auth_tag = $parts[2];
                    $text = $row[5];
                    $text_id = $row[1];
                    if ($addon->setText($auth_tag, $addonName, $text_id, $text, $language)) {
                        $successes++;
                        $db->set_site_setting($pre . 'successes', $successes);
                    } else {
                        $errors++;
                        $db->set_site_setting($pre . 'errors', $errors);
                    }
                }
            }
        }
        $offset = ftell($rFile);
        $db->set_site_setting($pre . 'offset', $offset);
    }

    if ($errors) {
        $menu_loader->userError("Some messages may not have been imported. {$successes} message(s) succeeded, {$errors} failed. Please verify that <em>{$filename}</em> is formatted correctly.");
    } else {
        $menu_loader->userSuccess("Imported {$successes} messages for your language!");
    }
    $db->set_site_setting($pre . 'offset', false);
    $db->set_site_setting($pre . 'successes', false);
    $db->set_site_setting($pre . 'errors', false);
    $db->set_site_setting($pre . 'lastImport', false);
    return (bool)$errors;
}
