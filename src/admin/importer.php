<?php

//admin/importer.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

class admin_importer
{
    public function display_importer()
    {
        //see if there is a pre-existing Import on this session
        $import = geoImport::getInstance();
        if ($import) {
            //kill this import item so that nothing bleeds over unintentionally
            //TODO: make a way to carry over settings from previous imports
            $import->purge();
        }
        unset($import);

        $sourceFolder = ADMIN_DIR . "importer_source_files/";
        if (!is_writable($sourceFolder)) {
            //try to chmod the folder on the fly. if that doesn't work, show an error
            if (!chmod($sourceFolder, 0777)) {
                geoAdmin::m("The folder $sourceFolder does not appear to be writable by the server. Please CHMOD 777 that folder before continuing.", geoAdmin::ERROR);
            }
        }

        $tpl_vars['base_image_tooltip'] = geoHTML::showTooltip('Base Image Path', "This value, if specified, will be prepended to all image fields.");

        $tpl_vars['adminMsgs'] = geoAdmin::m();
        geoView::getInstance()->setBodyTpl('importer/start.tpl')->setBodyVar($tpl_vars);
    }

    public function update_importer()
    {
        $sourceFolder = ADMIN_DIR . "importer_source_files/";
        $time = geoUtil::time();
        $instance = 0;
        $ext = pathinfo($_FILES['source']['name'], PATHINFO_EXTENSION);
        do {
            $filename = $time . '-' . $instance++;
        } while (file_exists($sourceFolder . $filename . '.' . $ext));
        $savedFile = $sourceFolder . $filename . '.' . $ext;

        if (!move_uploaded_file($_FILES['source']['tmp_name'], $savedFile)) {
            geoAdmin::m('Could not save file. Check that destination folder is writable.', geoAdmin::ERROR);
            return false;
        }

        $import = geoImport::getInstance($savedFile, $_POST['filetype']);

        if ($_POST['filetype'] === 'csv') {
            $import->settings['csv_skipfirst'] = $_POST['csv_skipfirst'];
            $import->settings['csv_delimiter'] = $_POST['csv_delimiter'];
            $import->settings['csv_encapsulation'] = $_POST['csv_encapsulation'];
        }
        if ($_POST['base_image_path']) {
            $import->settings['base_image_path'] = $_POST['base_image_path'];
        }

        //store import object according to session ID
        $import->store();

        //trigger next step
        header('Location: index.php?page=map_import&mc=admin_tools_settings');
        exit();
        return true;
    }


    public function display_map_import()
    {

        $import = geoImport::getInstance();
        if (!$import) {
            geoAdmin::m('Missing settings. Be sure you have selected a file to upload from before proceeding', geoAdmin::ERROR);
            header('Location: index.php?page=importer&mc=admin_tools_settings');
            return;
        }

        $items = $import->getAllImportItems();

        foreach ($items as $item) {
            //group items according to FieldGroup
            $tpl_vars['fieldgroups'] = array(
                geoImportItem::NOT_USED_FIELDGROUP => 'Field Not Used',
                geoImportItem::USER_GENERAL_FIELDGROUP => 'General Information',
                geoImportItem::USER_OPTIONAL_FIELDGROUP => 'Registration Optional Fields',
                geoImportItem::USER_LOCATION_FIELDGROUP => 'Location Fields',
                geoImportItem::USER_LOGIN_FIELDGROUP => 'Login Data',
                geoImportItem::USER_UGPP_FIELDGROUP => 'User Groups / Price Plans',
                geoImportItem::USER_ADDON_FIELDGROUP => 'Addon Fields',

            );
            $tpl_vars['fields'][$item->getFieldGroup()][$item->getSaveName()] = array( 'name' => $item->getName(),
                                                                                        'description' => $item->getDescription(),
                                                                                        'requires' => ($item->requires) ? $item->requires : false);
        }
        ksort($tpl_vars['fields']); //make sure all the fieldgroups are in order
        $tpl_vars['demoTokens'] = $import->getDemoTokens();
        $tpl_vars['csvHeaders'] = $import->csvHeaders; //class var loaded as part of getDemoTokens()

        $tpl_vars['adminMsgs'] = geoAdmin::m();
        geoView::getInstance()->setBodyTpl('importer/map.tpl')->setBodyVar($tpl_vars)->addCssFile('css/importer.css');
    }

    public function update_map_import()
    {
        $import = geoImport::getInstance();
        $allItems = $import->getAllImportItems();
        foreach ($_POST['fieldselect'] as $colNum => $field) {
            $default = ($_POST['defaultval'][$colNum]) ? $_POST['defaultval'][$colNum] : '';
            $import->addImportItem($allItems[$field], $default, $colNum);
        }

        $import->store();

        //trigger next step
        header('Location: index.php?page=do_import&mc=admin_tools_settings');
        exit();
        return true;
    }

    public function display_do_import()
    {
        $import = geoImport::getInstance();
        //display final confirmation and any last-minute settings before beginning upload
        $tpl_vars['adminMsgs'] = geoAdmin::m();
        geoView::getInstance()->setBodyTpl('importer/confirm.tpl')->setBodyVar($tpl_vars);
    }

    public function update_do_import()
    {
        $import = geoImport::getInstance();

        //set new error handler to capture any problems with the import
        set_error_handler(array($this,'error_handler'));

        $completed = $import->processFile();

        geoAdmin::m('Import complete! ' . $completed . ' users imported. If there were any errors, they will be shown here. If needed, you can click Submit to run the import again');

        $import->store(); //close out file and save to db for possible retreival/reference later

        return true;
    }

    /**
     * Used to shunt trigger_error() calls in ImportItems into geoAdmin::m() for easy admin display.
     * When this importer gets added to the front side, it will have its own method for dealing with those errors there.
     */
    public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (strpos($errstr, 'DEBUG IMPORT') === false && strpos($errstr, 'ERROR IMPORT') === false) {
            //not an importer error -- give it back to the normal error handler
            return geo_default_debug_error_handler($errno, $errstr, $errfile, $errline, $errcontext);
        } else {
            $type = strpos($errstr, 'ERROR') === 0 ? geoAdmin::ERROR : geoAdmin::NOTICE;
            geoAdmin::m('Import Message: ' . $errstr, $type);
            return true;
        }
    }
}
