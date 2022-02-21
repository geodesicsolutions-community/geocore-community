<?php

class DesignManage
{

    private $_invalidTSetNames = array();

    private $_initRun = false;
    private $_workWith, $_advMode;
    /**
     * File object
     * @var geoFile
     */
    private $_file;

    private $_canEditDefault = false;
    private $_canEditSystemTemplates = false;

    private $_validTypes = array ('main_page','external','system','module','addon','smarty');

    public function init($minimal = false)
    {
        if ($this->_initRun) {
            //already run the init
            return;
        }
        $db = DataAccess::getInstance();

        //set the jail dir to the templates dir
        $this->_file = geoFile::getInstance(geoFile::TEMPLATES);
        $this->_file->jailTo(GEO_TEMPLATE_DIR);

        //get invalid tset names from template class
        $this->_invalidTSetNames = geoTemplate::getInvalidSetNames();

        $this->_advMode = (geoPC::is_trial()) ? false : $db->get_site_setting('advDesignMode');

        if ($this->_advMode) {
            $this->_canEditSystemTemplates = $db->get_site_setting('canEditSystemTemplates');
            if (defined('IAMDEVELOPER')) {
                $this->_canEditDefault = $db->get_site_setting('allowDefaultTsetEdit');
            }
        }

        if ($minimal) {
            //don't do anything past this point
            return;
        }
        $this->_initRun = true;

        //send this non-standard header as it will keep browsers from stopping
        //scripts from loading when the template contents are posted...
        //Without this, in Chrome and other webkit browsers with XSS detection
        //built in, depending on the template contents, it may think it is an XSS
        //attack just because it is saving contents of a file with JS in it.
        header('X-XSS-Protection: 0');

        //set the selected working set(s)
        if (isset($_GET['forceEditTset'])) {
            $add = trim($_GET['forceEditTset']);
            if ($this->_advMode && isset($_GET['forceChange'])) {
                //combine with what is in it already
                //note, if "forceChange" is set, then this is result of clicking
                //on "edit templates" so it should "add" rather than "replace" admin
                //working with list
                $list = explode(',', $db->get_site_setting('designManageWorkWith'));
                if (!in_array($add, $list)) {
                    $list[] = $add;
                }
                $workWith = implode(',', $list);
            } else {
                //standard mode where only one thing can be active at once, so just
                //set workwith to what it is trying to force it to be
                $workWith = $add;
            }
        } else {
            $workWith = '' . $db->get_site_setting('designManageWorkWith');
        }
        $list = explode(',', $workWith);

        //clean the list
        $allowed = $this->getAllTemplateSets();
        if (!in_array('default', $allowed) && $this->_advMode) {
            //allow to "work on" but it won't actually allow editing anything...
            $allowed[] = 'default';
        }

        $list = array_intersect($list, $allowed);

        if (!count($list)) {
            $active = geoTemplate::getTemplateSets();
            $tset = array_shift($active);
            if ($tset && ($tset !== 'default' || $this->_canEditDefault)) {
                $list = array ($tset);
            } elseif (count($this->_tSets)) {
                //pop one off the front of tsets
                $tsets = $this->_tSets;
                $tset = array_shift($tsets);
                if ($tset) {
                    $list = array($tset);
                }
            }
            if (!count($list)) {
                //No template set found?!?
                $list = array('None Found!');
            }
        }
        //reset the keys in the array so that $list[0] is always first one in list
        $list = array_values($list);

        $workWith = implode(',', $list);
        $this->_workWith = $list;

        if (isset($_GET['forceChange']) && $_GET['forceChange']) {
            $db->set_site_setting('designManageWorkWith', $workWith);
        }

        //Checks for mis-configured stuff...

        //t-sets using "reserved" names
        $invalidTSets = array_intersect($this->getAllTemplateSets(true), $this->_invalidTSetNames);
        if (count($invalidTSets)) {
            //invalid template set:
            geoAdmin::m('Invalid Template Set name(s) used: ' . implode(', ', $invalidTSets) . ' -- these names are reserved by the system, so will not be used.', geoAdmin::NOTICE);
        }



        //TODO: check for pages without template assigned (using current "working with")

        //TODO: Check for template assignments for templates not found (using current "active sets")

        //add common tpl vars, JS and CSS used on every design manage page
        $tpl_vars = array (
            'workWith' => $workWith,
            'workWithList' => $this->_workWith,
            'allTSets' => $this->_tSets,
            'canEditDefault' => $this->_canEditDefault,
            'canEditSystemTemplates' => (int)$this->_canEditSystemTemplates,
            'advMode' => $this->_advMode,
            'needsDefaultCopy' => (count($this->_tSets) == 0),
            'geo_templatesDir' => geoAdmin::getInstance()->geo_templatesDir(),
        );

        geoView::getInstance()->addCssFile('css/designManage.css')
            ->addJScript('js/designManage.js')
            ->setBodyVar($tpl_vars);
    }

    public function initUpdate($minimal = false)
    {
        $this->init($minimal);

        //clear the cache
        $cacheLocations = array (
            'smarty_template_paths',
            'smarty_template_file_sets',
            //since text is cached with {external ...} pre-parsed, do addon text...
            'addon_text'
        );
        foreach ($cacheLocations as $location) {
            geoCacheSetting::expire($location);
        }
        //expire all text since text is cached with {external ...} pre-parsed
        geoCache::clearCache('text');
    }

    public function display_design_settings()
    {
        $this->init();
        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $this->_activeTemplateSetWarning();

        $tpl_vars = array();

        $tpl_vars['external_url_base'] = $db->get_site_setting('external_url_base');
        $tpl_vars['GEO_TEMPLATE_LOCAL_DIR'] = GEO_TEMPLATE_LOCAL_DIR;
        $tpl_vars['GEO_JS_LIB_LOCAL_DIR'] = GEO_JS_LIB_LOCAL_DIR;

        $tpl_vars['minifyEnabled'] = $db->get_site_setting('minifyEnabled');
        $tpl_vars['minifyLibs'] = $db->get_site_setting('minifyLibs');

        $tpl_vars['noMinifyJs'] = $db->get_site_setting('noMinifyJs');
        $tpl_vars['noMinifyCss'] = $db->get_site_setting('noMinifyCss');

        $tpl_vars['useFooterJs'] = $db->get_site_setting('useFooterJs');

        $tpl_vars['tplHtaccess'] = $db->get_site_setting('tplHtaccess');
        $tpl_vars['tplHtaccess_protect'] = $db->get_site_setting('tplHtaccess_protect');
        $tpl_vars['tplHtaccess_compress'] = $db->get_site_setting('tplHtaccess_compress');
        $tpl_vars['tplHtaccess_expires'] = $db->get_site_setting('tplHtaccess_expires');
        $tpl_vars['tplHtaccess_rewrite'] = $db->get_site_setting('tplHtaccess_rewrite');

        $tpl_vars['noDefaultCss'] = $db->get_site_setting('noDefaultCss');
        $tpl_vars['useCHMOD'] = $db->get_site_setting('useCHMOD');
        $tpl_vars['geo_template_dir'] = GEO_TEMPLATE_DIR;
        $tpl_vars['adminMsgs'] = geoAdmin::m();

        $tpl_vars['useGoogleLibApi'] = $db->get_site_setting('useGoogleLibApi');

        $tpl_vars['filter_trimwhitespace'] = $db->get_site_setting('filter_trimwhitespace');


        $view->setBodyTpl('design/settings.tpl')
            ->setBodyVar($tpl_vars);
    }

    private function _looksLikeFullUrl($url)
    {
        $info = parse_url($url);
        return (isset($info['host']) && isset($info['scheme']));
    }

    public function update_design_settings()
    {
        $this->initUpdate(true);
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $external_url_base = trim($_POST['external_url_base']);
        if (strlen($external_url_base)) {
            //make sure it is URL
            if (!$this->_looksLikeFullUrl($external_url_base)) {
                geoAdmin::m('The external media base URL entered (' . $external_url_base . ') is not a valid URL.  It needs to either be blank, or an alternate fully qualified URL to use to access the external media files.', geoAdmin::ERROR);
                $external_url_base = '';
            } else {
                //make sure trailing slash is included
                $external_url_base = rtrim($external_url_base, '/') . '/';
            }
        }
        $db->set_site_setting('external_url_base', $external_url_base);

        $minifyEnabled = (isset($_POST['minifyEnabled']) && $_POST['minifyEnabled']) ? 1 : false;
        $db->set_site_setting('minifyEnabled', $minifyEnabled);
        $onoff = array();
        $minifyLibs = false;
        if ($minifyEnabled) {
            //save rest of settings
            $onoff[] = 'minifyLibs';
            $minifyLibs = (isset($_POST['minifyLibs']) && $_POST['minifyLibs']);
        }
        $onoff[] = 'useFooterJs';

        $tplHtaccess = (isset($_POST['tplHtaccess']) && $_POST['tplHtaccess']) ? 1 : false;
        $protect = $compress = $rewrite = false;
        if ($tplHtaccess) {
            $protect = ((isset($_POST['tplHtaccess_protect']) && $_POST['tplHtaccess_protect']) ? 1 : false);
            $compress = ((isset($_POST['tplHtaccess_compress']) && $_POST['tplHtaccess_compress']) ? 1 : false);
            $expires = ((isset($_POST['tplHtaccess_expires']) && $_POST['tplHtaccess_expires']) ? 1 : false);
            $rewrite = (($minifyEnabled && isset($_POST['tplHtaccess_rewrite']) && $_POST['tplHtaccess_rewrite']) ? 1 : false);
            if (!$protect && !$compress && !$rewrite && !$expires) {
                geoAdmin::m('Must enable at least one of the htaccess options!', geoAdmin::NOTICE);
                $tplHtaccess = false;
            }
        }
        //generate the htaccess contents
        $tplHtaccess = $this->_htaccessGenerate($tplHtaccess, $protect, $compress, $expires, $rewrite);

        //also save the settings...
        $db->set_site_setting('tplHtaccess', $tplHtaccess);
        if ($tplHtaccess) {
            $onoff[] = 'tplHtaccess_protect';
            $onoff[] = 'tplHtaccess_compress';
            $onoff[] = 'tplHtaccess_expires';
            if ($minifyEnabled) {
                $onoff[] = 'tplHtaccess_rewrite';
            }
        }
        $onoff[] = 'useGoogleLibApi';
        if ($minifyLibs) {
            //force use google library API to be OFF if minifying libraries
            unset($_POST['useGoogleLibApi']);
        }
        if ($this->_advMode) {
            $onoff[] = 'canEditSystemTemplates';
        }
        $onoff[] = 'noDefaultCss';
        $onoff[] = 'noMinifyJs';
        $onoff[] = 'noMinifyCss';
        $onoff[] = 'filter_trimwhitespace';
        foreach ($onoff as $setting) {
            $db->set_site_setting($setting, ((isset($_POST[$setting]) && $_POST[$setting]) ? 1 : false));
        }

        //useCHMOD
        $useCHMOD = (isset($_POST['useCHMOD']) && $_POST['useCHMOD']) ? 1 : false;
        if (geoPC::is_trial() && !$useCHMOD) {
            //so that trials can be removed properly, they must have CHMOD 777
            $useCHMOD = 1;
            $admin->userNotice('For the trial to perform correctly, the CHMOD 777 setting must be kept on in trial demos.');
        }
        $db->set_site_setting('useCHMOD', $useCHMOD);

        //since htaccess adds admin messages, go ahead and add a settings saved ourselves
        geoAdmin::m('Settings Saved.', geoAdmin::SUCCESS);
        return true;
    }

    private function _htaccessGenerate($tplHtaccess = false, $protect = true, $compress = true, $expires = true, $rewrite = true)
    {
        $db = DataAccess::getInstance();
        $mainContents = '';
        $filename = $this->_file->absolutize('.htaccess');
        if (!$this->_file->inJail($filename)) {
            //just a failsave, this shouldn't happen unless they have settings messed up or something...
            return false;
        }
        if (!is_writable(dirname($filename))) {
            geoAdmin::m('Could not write the .htaccess file to (' . dirname($filename) . '/), make sure folder is writable.', geoAdmin::ERROR);
            //cannot write the file, so return false here without trying to clear it
            return false;
        } elseif (file_exists($filename) && !is_writable($filename)) {
            geoAdmin::m('Could not update the .htaccess file, file (' . $filename . ') is not writable (CHMOD 777).', geoAdmin::ERROR);
            //cannot write the file, so return false here without trying to clear it
            return false;
        }

        $tokens = array (
            'start' => '##GEO START##',
            'end' => '##GEO END##',
            'content_placeholder' => '### ~~~ GEO CONTENTS - DO NOT ADD THIS TO THE FILE, USED INTERNALLY ONLY ~~~ ###',
            'no_edit' => '##NO EDIT##',
            );

        //get the "existing" contents
        $existing = $mainContents = '';
        if (file_exists($filename)) {
            //if the htaccess file already exists...  We are either going to update it,
            //or we're going to remove it.  Either way first make sure it's "safe" to do so

            $existing = $this->_file->file_get_contents($filename);
            if (!strlen(trim($existing))) {
                //don't preserve white space in empty file
                $existing = '';
            }
            if (strpos($existing, $tokens['no_edit']) !== false) {
                //Special case: found "no edit" text in the contents, so do not
                //automatically generate the contents.
                if ($tplHtaccess) {
                    //if they are trying to turn it on, show an error...
                    geoAdmin::m("The .htaccess file already exists and contains the
							\"NO EDIT\" text ({$tokens['no_edit']}), so cannot turn
							on using the .htaccess contents.", geoAdmin::ERROR);
                }
                return false;
            }

            $start = strpos($existing, $tokens['start']);
            $end = strpos($existing, $tokens['end']);

            $content_not_safe = "Error: The .htaccess file exists but the contents
					could not be safely updated by the software.  Delete the file
					using FTP, then re-save this admin page settings to have the .htaccess file
					contents automatically re-generated.";

            if (strpos($existing, $tokens['content_placeholder']) !== false) {
                //just a failsafe...
                geoAdmin::m($content_not_safe . ' (DEBUG INFO: Internal token ' . $tokens['content_placeholder'] . ' found in contents)', geoAdmin::ERROR);
                return false;
            }

            if ($start !== false && $end !== false) {
                if ($start >= $end) {
                    geoAdmin::m($content_not_safe . " (DEBUG INFO: {$tokens['start']} found AFTER {$tokens['end']} in contents)", geoAdmin::ERROR);
                    return false;
                }
                //strip out the old contents, replace with placeholder...
                $existing = substr($existing, 0, $start) . $tokens['content_placeholder'] . substr($existing, ($end + strlen($tokens['end'])));
                if (trim($existing) === $tokens['content_placeholder']) {
                    //nothing else in file
                    $existing = '';
                }
            } elseif ($start !== false || $end !== false) {
                //found one token bot not both, oops...
                $missing = ($start === false) ? $tokens['start'] : $tokens['end'];
                geoAdmin::m($content_not_safe . " (DEBUG INFO: $missing not found in contents)", geoAdmin::ERROR);
                return false;
            } elseif (!$tplHtaccess) {
                //NOT adding the htaccess file contents, there is an existing .htaccess
                //file and it doesn't have start/end tokens..  don't mess with it
                return $tplHtaccess;
            }
        } elseif (!$tplHtaccess) {
            //no .htaccess file exists, and not set to add/generate one... don't bother
            //doing anything else...
            return $tplHtaccess;
        }

        if ($tplHtaccess) {
            //Generate the contents
            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign('protect', $protect);
            $tpl->assign('compress', $compress);
            $tpl->assign('rewrite', $rewrite);
            $tpl->assign('expires', $expires);

            //Figure out the path
            $base = $db->get_site_setting('external_url_base');
            if (!$base) {
                //use classifieds url
                $base = dirname($db->get_site_setting('classifieds_url')) . '/';
            }
            $base .= GEO_TEMPLATE_LOCAL_DIR;
            $info = parse_url($base);
            $base = (isset($info['path'])) ? '/' . trim($info['path'], '/') : '/';
            $tpl->assign('rewrite_base', $base);
            $mainContents = $tpl->fetch('design/files/geotemplates_htaccess.tpl');
        }
        if (!strlen(trim($mainContents)) && !strlen(trim($existing))) {
            //nothing to write, and no "existing" parts to preserve, so remove
            //the file if it exists.
            if (file_exists($filename)) {
                //just remove the file
                if ($this->_file->unlink($filename)) {
                    geoAdmin::m('Successfully removed the .htaccess file.', geoAdmin::SUCCESS);
                }
            }
            return false;
        }
        if (strpos($existing, $tokens['content_placeholder']) === false) {
            //We are adding text, but no placeholder to know where to stick it...

            //just add the placeholder to the existing text so it has somewhere
            //to stick the new code.
            if (!strlen(trim($existing))) {
                $existing = $tokens['content_placeholder'];
            } else {
                //already exists, add it to the end
                $existing .= "\n" . $tokens['content_placeholder'];
            }
        }

        //insert the main contents
        $replace = $tokens['start'] . "\n" . trim($mainContents) . "\n\n" . $tokens['end'];
        $contents = str_replace($tokens['content_placeholder'], $replace, $existing);

        if ($contents) {
            if (!$this->_file->fwrite($filename, $contents)) {
                geoAdmin::m('Error reported when attempting to write the .htaccess file (' . $filename . ').', geoAdmin::ERROR);
                //cannot write the file, so return false here without trying to clear it
                return false;
            }
            geoAdmin::m('Updated the (' . GEO_TEMPLATE_LOCAL_DIR . '.htaccess) file contents successfully.', geoAdmin::SUCCESS);
        } else {
            //failsafe
            geoAdmin::m('Internal Error generating .htaccess contents.', geoAdmin::ERROR);
            return false;
        }

        return $tplHtaccess;
    }

    public function display_design_clear_combined()
    {
        $this->init();
        if (!geoAjax::isAjax()) {
            $this->display_design_settings();
            return;
        }
        //display the thing
        $view = geoView::getInstance();
        $view->setRendered(true);

        if (!isset($_POST['auto_save'])) {
            $tpl_vars = $view->getAssignedBodyVars();

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);

            echo $tpl->fetch('design/clearCombined.tpl');
        }
    }

    public function update_design_clear_combined()
    {
        $this->initUpdate();
        if ($this->clearCombined()) {
            geoAdmin::m('Combined output was cleared successfully!', geoAdmin::SUCCESS);
            return true;
        }
        geoAdmin::m('There was an error attempting to remove the generated minified files.', geoAdmin::ERROR);
        return false;
    }

    public function clearCombined()
    {
        $db = DataAccess::getInstance();

        //first, clear entries from the database
        $db->Execute("DELETE FROM " . geoTables::combined_css_list);
        $db->Execute("DELETE FROM " . geoTables::combined_js_list);

        //now delete the files...
        $folder = $this->_file->absolutize('.min/');
        if (file_exists($folder)) {
            if (!$this->_file->unlink($folder)) {
                return false;
            }
        }
        return true;
    }

    public function display_design_sets()
    {
        $this->init();
        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $this->_activeTemplateSetWarning();

        $tpl_vars = array();
        if (geoAjax::isAjax() && isset($_GET['changeEditing'])) {
            $tpl_vars = $view->getAssignedBodyVars();
        }
        $tpl_vars['t_sets'] = $this->getAllTemplateSets();
        /**
         * Note: geoTemplate::getTemplateSets() doesn't bother with the check
         * for if a loaded template set exists, but we do for the $this->_tSets
         * list. So by intersecting the former with the latter, we get rid of
         * activated tempalte sets that no longer exist in file system.
         */
        $tpl_vars['t_sets_used'] = array_intersect(geoTemplate::getTemplateSets(), $this->_tSets);

        $tpl_vars['t_sets_meta'] = geoTemplate::getTemplateSetsMeta();

        $languages = $db->GetAll("SELECT * FROM " . geoTables::pages_languages_table);
        foreach ($languages as $language) {
            $tpl_vars['languages'][$language['language_id']] = $language['language'];
        }

        $importTSets = array ();
        foreach ($this->_tSets as $tset) {
            if (file_exists($this->_file->absolutize("$tset/text.csv"))) {
                $importTSets[$tset] = $tset;
            }
        }
        $tpl_vars['importTextTsets'] = $importTSets;

        $tpl_vars['geo_template_dir'] = GEO_TEMPLATE_DIR;
        $tpl_vars['adminMsgs'] = geoAdmin::m();
        $tpl_vars['canZip'] = true;
        $tpl_vars['showExport'] = $db->tableExists('geodesic_templates');
        $tpl_vars['iamdeveloper'] = defined('IAMDEVELOPER');

        $view->setBodyTpl('design/templateSets.tpl')
            ->setBodyVar($tpl_vars);
    }

    public function get_custom_tset_section()
    {
        //get the custom code
        $full = $this->_file->file_get_contents('t_sets.php');
        $start = strpos($full, '# [CUSTOM SECTION] #');
        $custom_section = '';
        if ($start) {
            //trim it down to be just what is inside the custom section
            $start += 20;
            $custom_section = substr($full, $start);
            $custom_section = substr($custom_section, 0, strpos($custom_section, '# [/CUSTOM SECTION] #'));
        }

        if (strlen(trim($custom_section)) == 0) {
            //nothing in custom section, add some default stuff
            $custom_tpl = new geoTemplate(geoTemplate::ADMIN);
            $custom_section = $custom_tpl->fetch('design/parts/t_sets_custom_section.tpl');
        }
        return $custom_section;
    }

    public function update_design_sets()
    {
        //do not run "full" init yet or changes here won't take effect for "working with"
        $this->initUpdate(true);
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        //needs to be comma sep. string:
        $workWith = (is_array($_POST['workWith'])) ? implode(',', $_POST['workWith']) : trim($_POST['workWith']);

        //now let init do the work of figuring out if it is valid or not
        $_GET['forceEditTset'] = $workWith;
        $this->init();
        //die ('workWith: '.$workWith.' after: '.print_r($this->_workWith,1));
        $db->set_site_setting('designManageWorkWith', implode(',', $this->_workWith));

        //active working sets
        $activeSets = (isset($_POST['activeSets']) && $_POST['activeSets']) ? $_POST['activeSets'] : array();
        $activeSets = array_keys($activeSets);

        $languageSets = (isset($_POST['language']) && $_POST['language']) ? $_POST['language'] : array();
        $deviceSetsRaw = (isset($_POST['device']) && $_POST['device']) ? $_POST['device'] : array();

        //validate each of the device sets, plus set the value to teh "constant" for saving
        $deviceMap = array (
            geoTemplate::DEVICE_ANY => 'geoTemplate::DEVICE_ANY',
            geoSession::DEVICE_MOBILE => 'geoSession::DEVICE_MOBILE',
            geoSession::DEVICE_DESKTOP => 'geoSession::DEVICE_DESKTOP',
        );
        $deviceSets = array();
        foreach ($deviceSetsRaw as $key => $value) {
            if (isset($deviceMap[$value])) {
                $deviceSets[$key] = $deviceMap[$value];
            }
        }

        $newSets = array();

        //now add on any new sets checked that are valid
        foreach ($activeSets as $t_set) {
            if (!in_array($t_set, $newSets) && in_array($t_set, $this->_tSets)) {
                $orig = $t_set;
                $t_set = strtolower(trim($t_set));

                //make sure it's safe for a file name
                $t_set = geoTemplate::cleanTemplateSetName($t_set);

                $block = array('.','..','t_sets.php','default');
                if (in_array($t_set, $block)) {
                    //shouldn't happen normally, this would only happen if they are trying to do input manipulation.
                    continue;
                }

                if (!file_exists($this->_file->absolutize($t_set))) {
                    //could not add
                    geoAdmin::m('Could not add template set (' . $orig . '), as it does not meet the proper naming policies.  Was this template set re-named using FTP? The folder name must use all lower case alpha-numeric characters.', geoAdmin::NOTICE);
                    continue;
                }
                $lang = 0;
                if (isset($languageSets[$t_set]) && $languageSets[$t_set] > 0) {
                    $lang = (int)$languageSets[$t_set];
                }
                $device = $deviceMap[geoTemplate::DEVICE_ANY];
                if (isset($deviceSets[$t_set])) {
                    //is a valid device set, so set it
                    $device = $deviceSets[$t_set];
                }
                //NOTE: While it would be more "elegant" to have the line specify
                //the constant for the device like:
                //geoTemplate::addTemplateSet('{$t_set.name}', {$t_set.language_id}, geoSession::DEVICE_MOBILE);
                //Do NOT do it that way, instead use the string for the device,
                //for backwards compatibility
                $newSets[] = array('name' => $t_set, 'language_id' => $lang, 'device' => $device);
            }
        }
        if (isset($_POST['move']) && $_POST['move']) {
            //going to change the order
            $move = $_POST['move'];

            $moveTset = key($move);
            $direction = array_pop($move);

            $moveSets = array ();
            foreach ($newSets as $i => $set) {
                $moveSets[$i] = $set['name'];
                if ($set['name'] == $moveTset) {
                    $swapA = $i;
                    $swapB = ($direction == 'up') ? ($swapA - 1) : ($swapA + 1);
                    break;
                }
            }
            $origSets = $newSets;
            foreach ($origSets as $i => $set) {
                if ($i == $swapA) {
                    $newSets[$i] = $origSets[$swapB];
                } elseif ($i == $swapB) {
                    $newSets[$i] = $origSets[$swapA];
                } else {
                    $newSets[$i] = $set;
                }
            }
        }

        //now update template sets

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign('t_sets', $newSets);

        $tpl->assign('custom_section', $this->get_custom_tset_section());

        $tpl_code = $tpl->fetch('design/files/t_sets.php.tpl');

        //write the file
        if (!$this->_file->fwrite('t_sets.php', $tpl_code)) {
            $admin->userError('Error writing file (t_sets.php), not able to update active template sets.');
        } else {
            $admin->userSuccess('Active template sets changed.');
            $admin->userSuccess('Settings Saved.');
        }
        geoTemplate::loadTemplateSets(true);

        return true;
    }

    public function display_design_change_editing()
    {
        $this->init();
        if (!geoAjax::isAjax()) {
            $this->display_design_sets();
            return;
        }
        //display the thing
        $view = geoView::getInstance();
        $view->setRendered(true);

        if (!isset($_POST['auto_save'])) {
            $tpl_vars = $view->getAssignedBodyVars();
            $tpl_vars['t_sets'] = $this->getAllTemplateSets();

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);

            echo $tpl->fetch('design/changeWorkingWith.tpl');
        }
    }

    public function update_design_change_editing()
    {
        //do not run "full" init yet or changes here won't take effect for "working with"
        $this->initUpdate(true);

        $db = DataAccess::getInstance();
        //needs to be comma sep. string:
        $workWith = (is_array($_POST['workWith'])) ? implode(',', $_POST['workWith']) : trim($_POST['workWith']);

        //now let init do the work of figuring out if it is valid or not
        $_GET['forceEditTset'] = $workWith;
        $this->init();
        //die ('workWith: '.$workWith.' after: '.print_r($this->_workWith,1));
        $db->set_site_setting('designManageWorkWith', implode(',', $this->_workWith));

        $data = array (
            'message' => 'Template Set(s) being edited in admin panel changed.'
        );
        geoAjax::getInstance()->jsonHeader();
        echo json_encode($data);

        return true;
    }

    public function display_design_sets_scan()
    {
        if (geoAjax::isAjax()) {
            $admin = geoAdmin::getInstance();
            $this->init();
            $errors = '';
            $t_set = (isset($_GET['t_set']) && $_GET['t_set']) ? $_GET['t_set'] : false;

            if ($t_set == 'default' && !$this->_canEditDefault) {
                //Just here for acedemic reasons...  The default template set can be
                //re-scanned, what it will do is make sure all the addon templates
                //are copied over.
            }

            if (!in_array($t_set, $this->_tSets)) {
                $errors .= 'Could not find template set specified!';
            }

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign('t_set', $t_set);
            $tpl->assign('errorMsgs', $errors);

            echo $tpl->fetch('design/tsetScan.tpl');
            geoView::getInstance()->setRendered(true);
        } else {
            //let main thing display stuff
            $this->display_design_sets();
        }
    }

    public function update_design_sets_scan()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();

        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        if ($t_set == 'default') {
            //re-scan addon stuff and re-copy if needed
            $addon = geoAddon::getInstance();

            $addons = $addon->getEnabledList();

            foreach ($addons as $addonData) {
                if (!$addon->updateTemplates($addonData['name'])) {
                    //copy failed...
                    $admin->userError('Re-apply of templates for addon ' . $addonData['title'] . ' failed.');
                    return false;
                }
            }

            if (!$this->_canEditDefault) {
                //that's all folks, don't do normal re-scanning stuff.
                return true;
            }
        }

        if (!in_array($t_set, $this->_tSets)) {
            $admin->userError('Could not find template set specified (' . $t_set . ')!');
            return false;
        }

        //delete the _temp/ directory if it exists
        $this->_file->unlink("_temp/attachScan/$t_set/");

        //make sure there are templates in t_set/main_page/ to process
        $fileList = array_diff($this->_file->scandir("$t_set/main_page/", false, false), array ('attachments'));

        if (!$fileList) {
            $admin->userError('No main-page templates to scan were found in the template set (' . $t_set . ').');
            return false;
        }

        //work in the _temp/ directory, then once we are done, copy over everything
        $this->_file->mkdir("_temp/attachScan/$t_set/");

        if (!is_writable($this->_file->absolutize("_temp/attachScan/$t_set/"))) {
            $admin->userError("Could not write to temporary directory (_temp/attachScan/$t_set/), check permissions and try again.");
            return false;
        }
        $affectedTpls = $this->_reScan($t_set);

        if ($affectedTpls !== false) {
            //delete t_set/main_page/attachments/ then copy over _temp...
            if (file_exists($this->_file->absolutize("$t_set/main_page/attachments/modules_to_template/"))) {
                //delete the directory to start with so we start out fresh
                $this->_file->unlink("$t_set/main_page/attachments/modules_to_template/");
            }
            //move over the files
            $result = $this->_file->rename("_temp/attachScan/$t_set/", "$t_set/main_page/attachments/modules_to_template/");

            if ($result) {
                $admin->userSuccess('Successfully re-scanned ' . $affectedTpls . ' templates in the ' . $t_set . ' template set, and updated the attachments.');
                return true;
            }
        }
        //delete the _temp/... dir
        $this->_file->unlink("_temp/attachScan/$t_set/");
        $admin->userError('Error during scanning for template attachments, note that the template set files were not affected.');

        return false;
    }

    private function _reScan($t_set, $subDir = '', $affectedCount = 0)
    {
        //for each template file, scan it and save attachments to _temp/
        $skipList = array ();
        if (!$subDir) {
            $skipList[] = 'attachments';
        }

        $absDir = $this->_file->absolutize("$t_set/main_page/$subDir");
        if (!$absDir) {
            //error getting it, it would have thrown admin error already
            return false;
        }

        $fileList = array_diff($this->_file->scandir($absDir, false, false), $skipList);
        if (!$fileList) {
            //empty directory, nothing to do
            return true;
        }
        foreach ($fileList as $entry) {
            if (!$this->_checkFile("$t_set/main_page/$subDir/$entry")) {
                //most likely an invalid filename like one with single quotes or something...
                //checkFile adds it's own error.
                return false;
            }
            if (is_dir($absDir . $entry)) {
                $affectedCount = $this->_reScan($t_set, $subDir . $entry . '/', $affectedCount);
                if ($affectedCount === false) {
                    //something went wrong with scanning sub-dir
                    return false;
                }
                continue;
            }

            $tplContents = file_get_contents($absDir . $entry);
            $tpl = new geoTemplate(geoTemplate::ADMIN);

            $tpl_vars = self::scanForAttachments($tplContents);
            $tpl_vars['filename'] = $subDir . $entry;

            $tpl->assign($tpl_vars);

            $contents = $tpl->fetch('design/files/modules_to_template.tpl');
            $result = $this->_file->fwrite("_temp/attachScan/$t_set/$subDir{$entry}.php", $contents);
            if (!$result) {
                $admin->userError('Error writing temporary attachments file, cannot continue.');
                return false;
            }
            $affectedCount++;
            unset($tpl, $contents, $tplContents, $result);
        }
        return $affectedCount;
    }

    public function display_design_sets_copy()
    {
        if (geoAjax::isAjax()) {
            $this->init();

            $t_set = (isset($_GET['t_set']) && $_GET['t_set']) ? $_GET['t_set'] : false;

            if ($t_set != 'merged' && !in_array($t_set, $this->_tSets)) {
                geoAdmin::m('Could not find template set specified!', geoAdmin::ERROR);
                echo geoAdmin::m();
                geoView::getInstance()->setRendered(true);
                return;
            }

            //figure out what this template set has to offer to copy...
            //We would just use $this->_validTypes but we need it to be in specific order
            $t_types = array('main_page','external','system','module','addon','smarty');
            if ($t_set != 'merged') {
                $t_types = array_intersect($t_types, scandir($this->_file->absolutize($t_set . '/')));
                if (!$t_types) {
                    geoAdmin::m('Could not find anything to copy in that template set!', geoAdmin::ERROR);
                    echo geoAdmin::m();
                    geoView::getInstance()->setRendered(true);
                    return;
                }
            }

            $tpl = new geoTemplate(geoTemplate::ADMIN);

            $tpl->assign('t_set', $t_set);
            if ($t_set == 'merged') {
                $tpl->assign('t_sets', $this->getAllTemplateSets());
            }

            $tpl->assign('t_types', $t_types);

            echo $tpl->fetch('design/tsetCopy.tpl');
            geoView::getInstance()->setRendered(true);
        } else {
            //let main thing display stuff
            $this->display_design_sets();
        }
    }

    public function update_design_sets_copy()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();

        $new_t_set = $this->_cleanNewTSetName($_POST['new_t_set']);
        if (!$new_t_set) {
            //problem with t-set name
            return false;
        }
        //check the from
        $t_sets = $this->getAllTemplateSets();
        $t_set = $_POST['t_set'];
        if ($t_set == 'merged') {
            //copy over "merged" template sets in backwards order so that which files
            //are used is the same as what would be used to display templates
            $t_setsInput = $_POST['t_sets'];

            $t_setsFrom = array_reverse(array_intersect($t_sets, $t_setsInput));
            if (!count($t_setsFrom)) {
                //error, no tsets to merge (this really shouldn't happen
                $admin->userError('Error: No valid template sets were specified to merge.');
                return false;
            }
        } else {
            $t_setsFrom = array_intersect($t_sets, array($t_set));
        }

        $copyTypes = $this->_getTemplateTypes();

        $to = $this->_file->absolutize($new_t_set . '/');
        if (!$to) {
            $admin->userError('Invalid to copy template set name!');
            return false;
        }

        $copycount = 0;
        foreach ($t_setsFrom as $t_set) {
            if (!in_array($t_set, $t_sets)) {
                $admin->userError('Specified template set to copy from is invalid.');
                return false;
            }
            //copy it over!
            $from = $this->_file->absolutize($t_set . '/');
            foreach ($copyTypes as $copyType) {
                if (is_dir($from . $copyType) || ($copyType == 'text.csv' && file_exists($from . $copyType))) {
                    //source dir exists and is dir, so copy it
                    if (!$this->_file->copy($from . $copyType, $to . $copyType)) {
                        //problem with copy
                        if ($copyCount) {
                            $admin->userNotice('There may be a partial copy of some of the template set\'s files.  The failure occured when attempting to copy the ' . $copyType . ' files.');
                        }
                        return false;
                    }
                    $copyCount++;
                }
            }
        }
        if (!$copyCount) {
            //they most likely un-checked all the different types to copy, or
            //there were no files found within selected types to copy. just create the dir.
            if (!$this->_file->mkdir($to)) {
                return false;
            }
            $admin->userSuccess('Created template set (' . $new_t_set . '), no template types selected so no files copys.  New template set\'s absolute location is (' . $to . ')');
            return true;
        }
        $admin->userSuccess('Created new template set copy (' . $new_t_set . ').');
        //make sure it re-gets the template sets
        $this->_tSets = null;
        $this->getAllTemplateSets();
        return true;
    }
    private $_downloadSuccess = false;
    public function display_design_sets_download()
    {
        //should not really be displaying anything, update does all the work
        if (!$this->_downloadSuccess && geoAjax::isAjax()) {
            //show the download box thingy
            $this->init();

            $t_set = (isset($_GET['t_set']) && $_GET['t_set']) ? $_GET['t_set'] : false;

            if (!in_array($t_set, $this->_tSets)) {
                geoAdmin::m('Could not find template set specified!', geoAdmin::ERROR);
                echo geoAdmin::m();
                geoView::getInstance()->setRendered(true);
                return;
            }

            //figure out what this template set has to offer to copy...
            //We would just use $this->_validTypes but we need it to be in specific order
            $t_types = array('main_page','external','system','module','addon', 'smarty');

            $t_types = array_intersect($t_types, scandir($this->_file->absolutize($t_set . '/')));
            if (!$t_types) {
                geoAdmin::m('Could not find anything to download in that template set!', geoAdmin::ERROR);
                echo geoAdmin::m();
                geoView::getInstance()->setRendered(true);
                return;
            }

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign('t_set', $t_set);
            $tpl->assign('canZip', true);
            $tpl->assign('t_types', $t_types);

            echo $tpl->fetch('design/tsetDownload.tpl');
            geoView::getInstance()->setRendered(true);
        } elseif (!$this->_downloadSuccess) {
            //oops, did not download, display page
            $this->display_design_sets();
        } else {
            geoView::getInstance()->setRendered(true);
        }
    }

    public function update_design_sets_download()
    {
        $this->initUpdate();

        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        if (!$t_set || !in_array($t_set, $this->getAllTemplateSets())) {
            geoAdmin::m('Invalid template set specified, cannot download.', geoAdmin::ERROR);
            return false;
        }
        $copyTypes = $this->_getTemplateTypes();
        if (!$copyTypes) {
            geoAdmin::m('No template types specified, nothing to download in template set.', geoAdmin::ERROR);
            return false;
        }

        $localFile = $t_set . '.zip';
        $absFile = $this->_file->absolutize("_temp/$localFile");

        if (file_exists($absFile)) {
            //delete existing file to re-generate it.
            unlink($absFile);
        }
        $this->_file->mkdir(dirname($absFile));

        $sources = array();
        foreach ($copyTypes as $type) {
            if ($type != 'text.csv') {
                $localname = "$t_set/$type/";
                $sources[] = $localname;
            } else {
                $localname = "$t_set/$type";
                $sources[] = $localname;
            }
        }
        if (!$sources) {
            geoAdmin::m('Nothing selected to download for template set, cannot download!', geoAdmin::ERROR);
            return false;
        }
        $result = $this->_file->zip($sources, $absFile);
        $this->_downloadSuccess = $this->_file->download($absFile);

        //delete the file
        unlink($absFile);
        return $this->_downloadSuccess;
    }

    public function display_design_sets_upload()
    {
        if (geoAjax::isAjax()) {
            $this->init();

            $tpl = new geoTemplate(geoTemplate::ADMIN);

            $tpl->assign('canZip', true);

            echo $tpl->fetch('design/tsetUpload.tpl');
            geoView::getInstance()->setRendered(true);
        } else {
            //let main part do it all
            $this->display_design_sets();
        }
    }

    public function update_design_sets_upload()
    {
        if (geoPC::is_trial()) {
            //disable template set upload in trials
            geoAdmin::m(geoPC::adminTrialMessage(), geoAdmin::NOTICE);
            return true;
        }

        if (!isset($_FILES['zipfile']) || $_FILES['zipfile']['error'] == 4) {
            geoAdmin::m('No uploaded file!');
            return false;
        }

        $this->_file = geoFile::getInstance(geoFile::TEMPLATES);

        //not calling init, so set jailed location here
        $this->_file->jailTo(GEO_TEMPLATE_DIR);

        $filedata = $_FILES['zipfile'];
        $zipName = $filedata['name'];
        //overwrite is either rename or overwrite
        $overwrite = (isset($_POST['overwrite']) && in_array($_POST['overwrite'], array('overwrite','rename'))) ? $_POST['overwrite'] : 'rename';
        //use the new set?
        $useSet = (isset($_POST['useIt']) && $_POST['useIt']) ? true : false;

        if (!$zipName || !$filedata['tmp_name']) {
            $msg = ' ';
            if ($filedata['error']) {
                //file upload error
                switch ($filedata['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $msg .= 'The file exceeds the <em>upload_max_filesize</em> (' . ini_get('upload_max_filesize') . ') directive in php.ini.';
                        break;

                    case UPLOAD_ERR_PARTIAL:
                        $msg .= 'The uploaded file was only partially uploaded.';
                        break;

                    case UPLOAD_ERR_NO_TMP_DIR:
                        $msg .= 'Missing a temporary folder, such as /tmp/.  Contact your host to resolve.';
                        break;

                    case UPLOAD_ERR_CANT_WRITE:
                        $msg .= 'Failed to write file to disk, contact your host to resolve.';
                        break;

                    case UPLOAD_ERR_EXTENSION:
                        $msg .= 'A PHP extension stopped the file upload, contact your host to resolve.';
                        break;
                }
                $msg .= " [Err code {$filedata['error']}]";
            }

            geoAdmin::m('Error: upload failed, please try again.' . $msg);
            return false;
        }
        //don't bother checking extension, if they upload something funky we catch
        //it when the zip throws an error.

        //extract to temporary directory
        $tmpDir = GEO_TEMPLATE_DIR . '_temp/upload';
        if (file_exists($tmpDir)) {
            //delete it
            $this->_file->unlink($tmpDir);
        }
        $this->_file->mkdir($tmpDir);

        if (!$this->_file->unzip($filedata['tmp_name'], '_temp/upload')) {
            $this->_file->unlink($tmpDir);
            geoAdmin::m('Error extracting uploaded file, unable to process.', geoAdmin::ERROR);
            return false;
        }

        //now parse the extracted files, make sure it "appears" to be a template set.
        $tmpDir .= '/';
        //first level, should have 1 dir in it, named same as template set
        $dirs = $this->_file->scandir($tmpDir, false, false);
        if (count($dirs) != 1) {
            $this->_file->unlink($tmpDir);
            geoAdmin::m('Uploaded file does not appear to be for a template set!  (base should have a single directory)', geoAdmin::ERROR);
            return false;
        }
        $copyCount = 0;
        foreach ($dirs as $baseDir) {
            if (!is_dir($tmpDir . $baseDir)) {
                $this->_file->unlink($tmpDir);
                geoAdmin::m('Uploaded file does not appear to be for a template set!  (main directory not found)', geoAdmin::ERROR);
                return false;
            }
            $fromBase = $tmpDir . $baseDir . '/';
            $t_set = geoTemplate::cleanTemplateSetName($baseDir);
            $toBase = GEO_TEMPLATE_DIR . $t_set . '/';
            //make sure this does not already exist
            if ($t_set && file_exists($toBase)) {
                if ($overwrite == 'rename') {
                    //Come up with alternate name
                    $t_set = $this->_file->generateRename($t_set, GEO_TEMPLATE_DIR);
                    $toBase = GEO_TEMPLATE_DIR . $t_set . '/';
                } else {
                    //unlink it
                    if (!$this->_file->unlink($toBase)) {
                        $this->_file->unlink($tmpDir);
                        geoAdmin::m('Failed removing existing template set (' . $t_set . ') to replace it!  Try again.', geoAdmin::ERROR);
                        return false;
                    }
                }
            }

            //make sure it is not reserved name
            if (!$t_set) {
                $this->_file->unlink($tmpDir);
                geoAdmin::m('Template set name (' . $baseDir . ') is reserved or not allowed, cannot proccess.', geoAdmin::ERROR);
                return false;
            }

            $tTypes = $this->_file->scandir($fromBase, false, false);
            //get rid of anything that isn't standard template type
            $validTypes = $this->_validTypes;
            $validTypes[] = 'text.csv';
            $tTypes = array_intersect($tTypes, $validTypes);
            if (count($tTypes) == 0) {
                $this->_file->unlink($tmpDir);
                geoAdmin::m('Uploaded file does not appear to be for a template set!  (contains no valid template types, like main_body, system, ect.)', geoAdmin::ERROR);
                return false;
            }

            foreach ($tTypes as $type) {
                //second level, should have dirs main_body, system, external, ... - copy them
                if (!is_dir($fromBase . $type) && $type != 'text.csv') {
                    //not a directory
                    continue;
                }
                if ($type != 'text.csv') {
                    $type .= '/';
                }
                //copy the files over
                if (!$this->_file->copy($fromBase . $type, $toBase . $type)) {
                    //some error copying files
                    $this->_file->unlink($tmpDir);
                    return false;
                }
                $copyCount++;
            }
        }
        if (!$copyCount) {
            $this->_file->unlink($tmpDir);
            geoAdmin::m('Did not find any valid template set files in upload!', geoAdmin::ERROR);
            return false;
        }
        $this->_file->unlink($tmpDir);

        if ($useSet) {
            //auto-add the set
            $this->_addSingleTset($t_set);
        }

        geoAdmin::m('Successfully added new template set ' . $t_set . '!', geoAdmin::SUCCESS);
        return true;
    }

    public function display_design_sets_export()
    {
        if (!geoAjax::isAjax()) {
            //just let the main page display
            return $this->display_design_sets();
        }
        $db = DataAccess::getInstance();
        $admin = geoAdmin::getInstance();
        $errorMsgs = '';
        if (!$db->tableExists('geodesic_templates')) {
            $errorMsgs .= 'There are no DB-based templates to export!';
        }
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('errorMsgs', $errorMsgs);
        echo $tpl->fetch('design/tsetExport.tpl');
        $admin->v()->setRendered(true);
    }

    public function update_design_sets_export()
    {
        $db = DataAccess::getInstance();
        $admin = geoAdmin::getInstance();

        if (!$db->tableExists('geodesic_templates')) {
            $admin->userError('There are no DB-based templates to export!');
            return false;
        }

        require_once ADMIN_DIR . 'templatesExport.php';

        $exporter = geoTemplatesExport::getInstance();

        $result = $exporter->exportTo('geo_exported_pre_5.0');
        if ($result) {
            $admin->userSuccess('Successfully exported the design to the template set (geo_exported_pre_5.0)!  Be sure to activate the new template set to start using it.');
            return true;
        }
        $errors = $exporter->errorMsg();
        if ($errors) {
            $admin->userError($errors);
        }
        return false;
    }

    public function display_design_sets_create_main()
    {
        if (!geoAjax::isAjax()) {
            //display main template sets
            return $this->display_design_sets();
        }
        $this->init();

        $tpl = new geoTemplate(geoTemplate::ADMIN);


        echo $tpl->fetch('design/tsetCreateMain.tpl');
        geoView::getInstance()->setRendered(true);
    }

    public function update_design_sets_create_main()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();

        $new_t_set = $this->_cleanNewTSetName($_POST['new_t_set']);
        if (!$new_t_set) {
            //problem with t-set name
            return false;
        }
        //The copy from is always "default" on create main set.
        $t_set = 'default';

        $to = $this->_file->absolutize($new_t_set . '/');
        if (!$to) {
            $admin->userError('Invalid template set name!');
            return false;
        }

        //copy it over!
        $from = $this->_file->absolutize($t_set . '/');
        $copyTypes = array(
            'main_page',
            //manually specify images, so that JS and fancy CSS does NOT get copied over.
            'external/images'
        );
        if (DataAccess::getInstance()->tableExists('geodesic_templates')) {
            //also copy over suggested text changes
            $copyTypes[] = 'text.csv';
        }
        foreach ($copyTypes as $copyType) {
            if (is_dir($from . $copyType) || ($copyType == 'text.csv' && file_exists($from . $copyType))) {
                //source dir exists and is dir, so copy it
                if (!$this->_file->copy($from . $copyType, $to . $copyType)) {
                    //problem with copy
                    if ($copyCount) {
                        $admin->userNotice('There may be a partial creation of some of the template set\'s files.  The failure occured when attempting to copy the ' . $copyType . ' files.');
                    }
                    return false;
                }
                $copyCount++;
            }
        }
        if (!$copyCount) {
            //just a failsafe, catch any time nothing was copied, which would
            //only happen if no files were found in default template set.
            $admin->userError('No files found in default templates to copy over, check to make sure all software files are uploaded and that the template dir setting is set correctly in the config.php file (if set at all).');
            return false;
        }

        //create a custom.css file
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $custom = $tpl->fetch('design/files/customCss.tpl');
        if (strlen($custom)) {
            $this->_file->mkdir($to . 'external/css/');
            $this->_file->fwrite($to . 'external/css/custom.css', $custom);
        }

        //start using it automatically
        $this->_addSingleTset($new_t_set);

        //also start working on it
        DataAccess::getInstance()->set_site_setting('designManageWorkWith', $new_t_set);

        $admin->userSuccess('Created the main template set (' . $new_t_set . ').');
        //make sure it re-gets the template sets
        $this->_tSets = null;
        $this->getAllTemplateSets();

        //allow init to run again to reset stuff
        $this->_initRun = false;

        return true;
    }

    public function display_design_sets_delete()
    {
        if (geoAjax::isAjax()) {
            $this->init();

            $t_set = (isset($_GET['t_set']) && $_GET['t_set']) ? $_GET['t_set'] : false;

            if (!in_array($t_set, $this->_tSets)) {
                geoAdmin::m('Could not find template set specified!', geoAdmin::ERROR);
                echo geoAdmin::m();
                geoView::getInstance()->setRendered(true);
                return;
            }

            if ($t_set == 'default') {
                //just fail-safe to stop it from being able to delete default templates
                geoAdmin::m('Cannot delete default template set, that will break your site!', geoAdmin::ERROR);
                echo geoAdmin::m();
                geoView::getInstance()->setRendered(true);
                return;
            }

            $tpl = new geoTemplate(geoTemplate::ADMIN);

            $tpl->assign('t_set', $t_set);

            echo $tpl->fetch('design/tsetDelete.tpl');
            geoView::getInstance()->setRendered(true);
        } else {
            //let main thing display stuff
            $this->display_design_sets();
        }
    }

    public function update_design_sets_delete()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();

        $verify = (int)$_POST['verify'];

        if (!$verify) {
            $admin->message('Selected to NOT delete template set, so the template set was not removed.', geoAdmin::NOTICE);
            return true;
        }

        //must be very careful here to prevent any hack attempts (or bugs) from
        //deleting something outside template set.

        $t_set = geoTemplate::cleanTemplateSetName($_POST['t_set']);
        if (!$t_set) {
            $admin->message('Invalid template set specified, if you created this template set using FTP it may have characters not allowed in template sets.  You will need to delete this template set using FTP.', geoAdmin::ERROR);
            return false;
        }

        if ($t_set == 'default') {
            //make sure they aren't trying to remove default
            $admin->message('Cannot delete default template set!  That will break your site!', geoAdmin::ERROR);
            return false;
        }

        $folder = $this->_file->absolutize($t_set . '/');

        if (!file_exists($folder)) {
            $admin->message('Could not find template set (' . $t_set . '), cannot delete.', geoAdmin::ERROR);
            return false;
        }
        if (!is_dir($folder)) {
            $admin->message('The template set requested (' . $t_set . ') is not a folder, cannot delete!', geoAdmin::ERROR);
            return false;
        }

        if (!is_writable($folder)) {
            $admin->message('The template set (' . $t_set . ') folder is not writable (CHMOD 777), cannot delete.', geoAdmin::ERROR);
            return false;
        }

        if ($folder == GEO_TEMPLATE_DIR) {
            //just sanity check, in case something weird went on, just making sure we don't delete entire template folder
            $admin->message('Internal error, cannot remove template set.', geoAdmin::ERROR);
            return false;
        }

        //delete template set
        $result = $this->_file->unlink($t_set . '/');

        //make sure it re-gets the template sets
        $this->_tSets = null;
        $this->getAllTemplateSets();

        //allow init to run again to reset stuff
        $this->_initRun = false;

        if (!$result) {
            $admin->message('Error when attempting to delete template set, regular checks passed but deletion seems to have failed.', geoAdmin::ERROR);
            return false;
        }
        $admin->message("Template set ($t_set) deleted successfully!");
        return true;
    }

    public function display_design_sets_import_text()
    {
        $this->init();
        $db = DataAccess::getInstance();
        if (!geoAjax::isAjax()) {
            return $this->display_design_sets();
        }
        $t_set = $_GET['t_set'];
        $errors = '';
        if (!in_array($t_set, $this->_tSets)) {
            $errors .= 'Could not find template set specified!<br />';
        } else {
            if (!file_exists($this->_file->absolutize($t_set . '/text.csv'))) {
                $errors .= 'Could not find text import file (text.csv) in specified template set!<br />';
            }
        }


        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('errorMsgs', $errors);
        $tpl->assign('t_set', $t_set);

        $tpl->assign('languages', $db->GetAssoc("SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table));

        echo $tpl->fetch('design/importText.tpl');
        geoView::getInstance()->setRendered(true);
    }

    public function update_design_sets_import_text()
    {
        $this->initUpdate();

        $db = DataAccess::getInstance();

        $t_set = $_GET['t_set'];
        $languageId = (int)$_POST['languageId'];

        $importFile = $this->_file->absolutize($t_set . '/text.csv');

        if (!in_array($t_set, $this->_tSets)) {
            geoAdmin::m('Invalid template set specified, cannot import text.', geoAdmin::ERROR);
            return false;
        }

        if (!file_exists($importFile)) {
            geoAdmin::m('Could not find text import file (text.csv) in specified template set.', geoAdmin::ERROR);
            return false;
        }

        $languages = $db->GetAssoc("SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table);
        if (!$languageId || !isset($languages[$languageId])) {
            geoAdmin::m('Invalid language specified, cannot import text.', geoAdmin::ERROR);
            return false;
        }

        require_once ADMIN_DIR . 'text_utility/importMessages.php';
        return doImport($languageId, $importFile);
    }

    public function display_design_change_mode()
    {
        $this->init();
        if (!geoAjax::isAjax()) {
            $this->display_design_sets();
            return;
        }
        //display the thing
        $view = geoView::getInstance();
        $view->setRendered(true);

        if (!isset($_POST['auto_save'])) {
            $tpl_vars = $view->getAssignedBodyVars();

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);

            echo $tpl->fetch('design/changeMode.tpl');
        }
    }

    public function update_design_change_mode()
    {
        $this->initUpdate();
        $db = DataAccess::getInstance();

        if (geoPC::is_trial()) {
            //not allowed to change mode in trial demo

            $data = array (
                'message' => geoPC::adminTrialMessage(),
            );
        } else {
            $newMode = ($this->_advMode) ? false : 1;
            $db->set_site_setting('advDesignMode', $newMode);

            $data = array (
                'message' => 'Design mode successfully changed to <em>' . (($newMode) ? 'Advanced' : 'Standard') . ' Mode</em>.'
            );
        }
        $this->echoJson($data);
    }

    public function display_page_attachments()
    {
        $this->init();
        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $this->_activeTemplateSetWarning();

        $tpl_vars = array();

        $forceAddon = (isset($_GET['addon']) && $_GET['addon']) ? $_GET['addon'] : false;

        //$tpl_vars['t_sets_used'] = geoTemplate::getTemplateSets();
        $tpl_vars['adminMsgs'] = geoAdmin::m();
        $tpl_vars['templates'] = $this->getAllTemplates();

        $tpl_vars['default_templates'] = $this->_file->scandir('default/main_page/');

        //die ("tpls: <pre>".print_r($this->_file->scandir('default/main_page/'),1));

        //addon attachments
        $addonPages = geoAddon::getInstance()->getPageList();
        if ($forceAddon && !isset($addonPages[$forceAddon])) {
            //not addon
            $forceAddon = false;
        }
        foreach ($addonPages as $addon => $info) {
            $pages = array();
            if ($forceAddon && $addon != $forceAddon) {
                continue;
            }
            foreach ($info['pages'] as $pageKey => $page) {
                $pages[$page] = array();
                foreach ($this->_workWith as $t_set) {
                    $attachFile = "$t_set/main_page/attachments/templates_to_page/addons/$addon/{$page}.php";
                    if (file_exists($this->_file->absolutize($attachFile))) {
                        $pages[$page]['t_set'] = $t_set;
                        $pages[$page]['templates'] = include $this->_file->absolutize($attachFile);
                        unset($return);
                        //only do the first tset found
                        break;
                    }
                }
                if (!isset($pages[$page]['t_set'])) {
                    //get from defaults
                    $attachedFile = "default/main_page/attachments/templates_to_page/addons/$addon/{$page}.php";
                    if (file_exists($this->_file->absolutize($attachedFile))) {
                        $pages[$page]['defaults'] = include $this->_file->absolutize($attachedFile);

                        unset($return);
                    }
                }
            }
            $addonPages[$addon]['pages'] = $pages;
        }
        //die ('addon pages: <pre>'.print_r($addonPages,1));
        $tpl_vars['addonPages'] = $addonPages;
        $tpl_vars['pages'] = $this->_getPageInfos();

        $tpl_vars['forceAddon'] = $forceAddon;

        $view->setBodyTpl('design/templatesToPage.tpl')
            ->setBodyVar($tpl_vars);
    }
    public function update_page_attachments()
    {
        $this->initUpdate();
        geoAdmin::m('This should not be run?', geoAdmin::NOTICE);

        return true;
    }

    public function display_page_attachments_edit()
    {
        $this->init();
        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $pageId = (isset($_GET['pageId']) && $_GET['pageId']) ? $_GET['pageId'] : false;
        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        //set from GET value if set
        $t_set = (!$t_set && isset($_GET['t_set'])) ? $_GET['t_set'] : $t_set;

        if (!$pageId) {
            //invalid page id?
            $admin->userError('Page ID not specified, cannot edit.');
            return $this->display_page_attachments();
        }
        if (substr($pageId, 0, 7) == 'addons/') {
            //addon page, make sure it is valid
            $parts = explode('/', $pageId);
            if (count($parts) != 3) {
                $admin->userError('Invalid page ID format specified, cannot edit.');
                return $this->display_page_attachments();
            }
            $addonName = $parts[1];
            $addonPage = $parts[2];
            $addonPages = geoAddon::getInstance()->getPageList();
            if (!isset($addonPages[$addonName])) {
                $admin->userError('Addon (' . $addonName . ') is not currently enabled, cannot edit addon page.');
                return $this->display_page_attachments();
            }

            if (!in_array($addonPage, $addonPages[$addonName]['pages'])) {
                $admin->userError('Invalid page (' . $addonPage . ') specified for addon (' . $addonPages[$addonName]['title'] . '), cannot edit.');
                return $this->display_page_attachments();
            }
            $tpl_vars['addon'] = $addonName;
            $tpl_vars['addonTitle'] = $addonPages[$addonName]['title'];
            $tpl_vars['pageName'] = (isset($addonPages[$addonName]['pages_info'][$addonPage]['title'])) ? $addonPages[$addonName]['pages_info'][$addonPage]['title'] : $addonPage;
            $tpl_vars['addonPage'] = $addonPage;
        } else {
            //make sure it is "valid"
            $pageName = $this->_getPageName($pageId);
            if (!$pageName) {
                //page name not valid
                $admin->userError('Invalid page ID specified, cannot edit.');
                return $this->display_page_attachments();
            }
            $tpl_vars['pageName'] = $pageName;
            $tpl_vars['pageInfo'] = $this->_getPageInfo($pageId);
            if ($tpl_vars['pageInfo']['affiliatePage'] && geoPC::is_ent()) {
                //affiliate page, get groups
                $sql = "SELECT `group_id`, `name` FROM " . geoTables::groups_table . " ORDER BY `group_id`";

                $tpl_vars['groupNames'] = $db->GetAssoc($sql);
            }
        }
        $tpl_vars['is_ent'] = geoPC::is_ent();
        $filename = "main_page/attachments/templates_to_page/$pageId.php";

        if (!$t_set) {
            //get t_set from first template set we are currently working with...
            $firstSet = '';
            foreach ($this->_workWith as $set) {
                if (!$firstSet) {
                    $firstSet = $set;
                }

                if (file_exists($this->_file->absolutize("$set/$filename"))) {
                    $t_set = $set;
                    break;
                }
            }
            if (!$t_set) {
                //tset not there? work with first one on list
                $t_set = $firstSet;
            }
        }

        if (!$t_set || !in_array($t_set, $this->_workWith)) {
            $admin->userError('Template set specified (' . $t_set . ') is not currently being worked on or is not valid, cannot edit.');
            return $this->display_page_attachments();
        }

        $tpl_vars['templates'] = $templates = $this->getAllTemplates();
        $tpl_vars['t_set'] = $t_set;
        $tpl_vars['pageId'] = $pageId;

        //get the current attachments
        $attachments = array ();
        if (file_exists($this->_file->absolutize("$t_set/$filename"))) {
            $attachments = include $this->_file->absolutize("$t_set/$filename");
            unset($return);
        } elseif ($t_set !== 'default' && file_exists($this->_file->absolutize("default/$filename"))) {
            $attachments = include $this->_file->absolutize("default/$filename");
            $tpl_vars['from_defaults'] = 1;
            unset($return);
        }

        $tpl_vars['attachments'] = $attachments;

        $languagesR = $db->GetAll("SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table);
        $languages = array(1 => 'Base/Fallback Language');
        foreach ($languagesR as $row) {
            if ($row['language_id'] != 1) {
                $languages[$row['language_id']] = $row['language'];
            } else {
                $languages[$row['language_id']] .= " ({$row['language']})";
            }
        }
        reset($languages);
        $tpl_vars['languages'] = $languages;

        //get categories - re-use really old code
        require_once ADMIN_DIR . 'admin_site_class.php';
        $oldClass = Singleton::getInstance('Admin_site');
        $oldClass->get_category_dropdown("new[cat][category]", 0, 0, $db->get_site_setting("levels_of_categories_displayed_admin"));
        $tpl_vars['catDropdown'] = $oldClass->dropdown_body;

        //get the category name for each category already being used
        $cats = array(0 => 'All Categories / Fallback Category');
        foreach ($attachments as $langId => $sub) {
            foreach ($sub as $catId => $tpl) {
                if (!isset($cats[$catId])) {
                    $cats[$catId] = geoCategory::getName($catId, true);
                    if ($catId && $tpl_vars['pageInfo']['categoryPage'] && !geoPC::is_ent()) {
                        //let it know that even though it is basic it somehow got a cat specific template set
                        $tpl_vars['has_cat_tpls'] = 1;
                    }
                }
            }
        }

        $tpl_vars['catNames'] = $cats;
        $tpl_vars['adminMsgs'] = geoAdmin::m();

        if (count($this->_workWith) == 1 && $this->_workWith[0] == 'default' && !$this->_canEditDefault) {
            $tpl_vars['read_only'] = 1;
        }

        $view->setBodyTpl('design/templatesToPageEdit.tpl')
            ->setBodyVar($tpl_vars);


        //allow the addon to specify an alternate template to render the page
        //if it really wants to.
        geoAddon::triggerUpdate('admin_display_page_attachments_edit_end', $tpl_vars);
    }

    public function update_page_attachments_edit()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;
        $attachmentsEntered = (isset($_POST['attachments']) && $_POST['attachments']) ? $_POST['attachments'] : false;

        $pageId = (isset($_GET['pageId']) && $_GET['pageId']) ? $_GET['pageId'] : false;

        if (!$pageId) {
            $admin->userError('Page ID not specified, cannot process.');
            return false;
        }

        //Addon pages
        if (substr($pageId, 0, 7) == 'addons/') {
            //addon page, make sure it is valid
            $parts = explode('/', $pageId);
            if (count($parts) != 3) {
                $admin->userError('Invalid addon page ID format specified, cannot process.');
                return false;
            }
            $addonName = $parts[1];
            $addonPage = $parts[2];
            $addonPages = geoAddon::getInstance()->getPageList();
            if (!isset($addonPages[$addonName])) {
                $admin->userError('Addon (' . $addonName . ') is not currently enabled, cannot process edit addon page.');
                return false;
            }

            if (!in_array($addonPage, $addonPages[$addonName]['pages'])) {
                $admin->userError('Invalid page (' . $addonPage . ') specified for addon (' . $addonPages[$addonName]['title'] . '), cannot edit.');
                return $false;
            }
        } else {
            //make sure it is "valid"
            $pageName = $this->_getPageName($pageId);
            if (!$pageName) {
                //page name not valid
                $admin->userError('Invalid page ID specified, cannot edit.');
                return false;
            }
        }

        if (!$t_set || !in_array($t_set, $this->_workWith)) {
            $admin->userError('Invalid template set specified (' . $t_set . '), cannot process.');
            return false;
        }

        $file = "$t_set/main_page/attachments/templates_to_page/$pageId.php";

        $languages = $db->GetAssoc("SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table);
        $templates = $this->getAllTemplates();
        $groups = $db->GetAssoc("SELECT `group_id`, `name` FROM " . geoTables::groups_table . " ORDER BY `group_id`");

        //check attachments
        $attachments = array ();
        //die ('data: <pre>'.print_r($_POST,1));

        //"normal" attachments
        foreach ($attachmentsEntered as $langId => $cats) {
            if (!is_numeric($langId)) {
                //this is group or extra page setting, we handle that in a bit...
                continue;
            }
            //validate the language ID
            $langId = (int)$langId;
            if (!$langId || !isset($languages[$langId])) {
                //not a valid language
                continue;
            }

            foreach ($cats as $catId => $template) {
                $catId = (int)$catId;

                if (!($langId == 1 && $catId == 0) && isset($_POST['delete'][$langId][$catId]) && $_POST['delete'][$langId][$catId]) {
                    //deleting this one, skip over it
                    continue;
                }

                //TODO: Check category ID, see if it's valid

                //check template
                if (!isset($templates[$template])) {
                    $admin->userError('Template (' . $template . ') not found in template sets currently working with, cannot process changes.');
                    return false;
                }

                $attachments[$langId][$catId] = $template;
            }
        }

        //affiliate group attachments
        if (geoPC::is_ent() && isset($attachmentsEntered['affiliate_group'])) {
            foreach ($attachmentsEntered['affiliate_group'] as $langId => $groups) {
                $langId = (int)$langId;
                if (!$langId || !isset($languages[$langId])) {
                    //not a valid language
                    continue;
                }

                foreach ($groups as $groupId => $template) {
                    $groupId = (int)$groupId;
                    if (isset($_POST['delete']['affiliate_group'][$langId][$groupId]) && $_POST['delete']['affiliate_group'][$langId][$groupId]) {
                        //deleting this one
                        continue;
                    }
                    if (!isset($groups[$groupId])) {
                        //not a valid group, don't do nothin
                        continue;
                    }

                    if (!isset($templates[$template])) {
                        $admin->userError('Template (' . $template . ') not found in template sets currently working with, cannot process changes.');
                        return false;
                    }

                    $attachments['\'affiliate_group\''][$langId][$groupId] = $template;
                }
            }
        }

        //extras attachments
        if (isset($attachmentsEntered['extra_page_main_body'])) {
            foreach ($attachmentsEntered['extra_page_main_body'] as $langId => $template) {
                $langId = (int)$langId;
                if (!$langId || !isset($languages[$langId])) {
                    //not a valid language
                    continue;
                }


                if (isset($_POST['delete']['extra_page_main_body'][$langId]) && $_POST['delete']['extra_page_main_body'][$langId]) {
                    //deleting this one
                    continue;
                }
                if (!isset($templates[$template])) {
                    $admin->userError('Template (' . $template . ') not found in template sets currently working with, cannot process changes.');
                    return false;
                }

                $attachments['\'extra_page_main_body\''][$langId][0] = $template;
            }
        }

        //Add new if applicable
        $new = (isset($_POST['new']['cat'])) ? $_POST['new']['cat'] : false;
        if ($new && $new['template'] != 'none' && (!($new['languageId'] == 1 && $new['category'] == 0 && (int)$new['catId'] == 0) || isset($new['catIdNocheck']))) {
            $addNew = true;
            if (!isset($templates[$new['template']])) {
                $admin->userError('Invalid template specified (' . $new['template'] . ') for new template assignment.');
                $addNew = false;
            }
            if (!isset($languages[$new['languageId']])) {
                $admin->userError('Invalid language ID specified (' . $new['languageId'] . ') for new template assignment.');
                $addNew = false;
            }
            if (isset($new['catIdNocheck']) && $addonName) {
                //allow addon pages to say to not check the category ID
                $catId = $new['catIdNocheck'];
            } else {
                $catId = (isset($new['catId']) && $new['catId']) ? $new['catId'] : $new['category'];
                $catId = (int)$catId;

                if ($catId > 0 && !geoCategory::getName($catId, true)) {
                    $admin->userError('Invalid category ID specified (' . $catId . ') for new template assignment.');
                    $addNew = false;
                }
            }

            if ($addNew) {
                $attachments[$new['languageId']][$catId] = $new['template'];
            }
        }
        //set new for groups, if applicable
        $new = (geoPC::is_ent() && isset($_POST['new']['aff'])) ? $_POST['new']['aff'] : false;
        if ($new && $new['template'] != 'none' && $new['groupId'] && isset($groups[$new['groupId']])) {
            $addNew = true;
            if (!isset($templates[$new['template']])) {
                $admin->userError('Invalid template specified (' . $new['template'] . ') for new template assignment.');
                $addNew = false;
            }
            if (!isset($languages[$new['languageId']])) {
                $admin->userError('Invalid language ID specified (' . $new['languageId'] . ') for new template assignment.');
                $addNew = false;
            }
            $groupId = $new['groupId'];

            if ($addNew) {
                $attachments['\'affiliate_group\''][$new['languageId']][$groupId] = $new['template'];
            }
        }

        //set new for extra pages, if applicable
        $new = (isset($_POST['new']['extra'])) ? $_POST['new']['extra'] : false;
        if ($new && $new['template'] != 'none') {
            $addNew = true;
            if (!isset($templates[$new['template']])) {
                $admin->userError('Invalid template specified (' . $new['template'] . ') for new template assignment.');
                $addNew = false;
            }
            if (!isset($languages[$new['languageId']])) {
                $admin->userError('Invalid language ID specified (' . $new['languageId'] . ') for new template assignment.');
                $addNew = false;
            }
            if ($addNew) {
                $attachments['\'extra_page_main_body\''][$new['languageId']][0] = $new['template'];
            }
        }

        if (!isset($attachments[1][0])) {
            $admin->userError('No default template specified!  Cannot process changes.');
            return false;
        }

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('page_attachments', $attachments);
        $result = $this->_file->fwrite($file, $tpl->fetch('design/files/templates_to_page.tpl'));
        if (!$result) {
            //error!
            $admin->userError('Error saving attachment file!');
            return false;
        }

        $admin->userSuccess('Attachments for page saved!');

        return true;
    }

    public function display_page_attachments_apply_defaults()
    {
        $this->init();

        if (!geoAjax::isAjax()) {
            //display list of thingies
            return $this->display_page_attachments();
        }

        $pageId = (isset($_GET['pageId']) && $_GET['pageId']) ? $_GET['pageId'] : 0;
        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $pageId = (isset($_GET['pageId']) && $_GET['pageId']) ? $_GET['pageId'] : false;
        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        //set from GET value if set
        $t_set = (!$t_set && isset($_GET['t_set'])) ? $_GET['t_set'] : $t_set;
        $errors = '';
        $fromFile = "default/main_page/attachments/templates_to_page/$pageId.php";
        if (!$t_set) {
            //get t_set from first template set we are currently working with...
            foreach ($this->_workWith as $set) {
                //set it to the first template set in the work with thingy
                $t_set = $set;
                break;
            }
        }
        if (!$pageId) {
            //invalid page id?
            $errors .= 'Page ID not specified, cannot edit.<br />';
        } elseif (!$this->_file->isChild("default/main_page/attachments/templates_to_page/", $fromFile)) {
            $errors .= 'Page ID specified is invalid!<br />';
        } elseif (!file_exists($this->_file->absolutize($fromFile))) {
            $errors .= 'Could not find default attachments for page id specified.<br />';
        }

        if (!$t_set || !in_array($t_set, $this->_workWith) || $t_set == 'default') {
            $errors .= 'Template set specified (' . $t_set . ') is not currently being worked on or is not valid, cannot apply default attachments.<br />';
        }
        $tpl_vars['t_set'] = $t_set;
        $tpl_vars['pageId'] = $pageId;

        if (substr($pageId, 0, 7) == 'addons/') {
            //addon page, make sure it is valid
            $parts = explode('/', $pageId);
            if (count($parts) != 3) {
                $admin->userError('Invalid page ID format specified, cannot edit.');
                return $this->display_page_attachments();
            }
            $addonName = $parts[1];
            $addonPage = $parts[2];
            $addonPages = geoAddon::getInstance()->getPageList();
            if (!isset($addonPages[$addonName])) {
                $errors .= 'Addon (' . $addonName . ') is not currently enabled, cannot apply defaults for page.<br />';
            }

            if (!in_array($addonPage, $addonPages[$addonName]['pages'])) {
                $errors .= 'Invalid page (' . $addonPage . ') specified for addon (' . $addonPages[$addonName]['title'] . '), cannot apply defaults.<br />';
            }
            $tpl_vars['addon'] = $addonName;
            $tpl_vars['addonTitle'] = $addonPages[$addonName]['title'];
            $tpl_vars['pageName'] = (isset($addonPages[$addonName]['pages_info'][$addonPage]['title'])) ? $addonPages[$addonName]['pages_info'][$addonPage]['title'] : $addonPage;
            $tpl_vars['addonPage'] = $addonPage;
            $attachedFile = "default/main_page/attachments/templates_to_page/{$pageId}.php";
            if (file_exists($this->_file->absolutize($attachedFile))) {
                $tpl_vars['info']['defaults'] = include $this->_file->absolutize($attachedFile);

                unset($return);
            }
        } else {
            $tpl_vars['info'] = $this->_getPageInfo($pageId);
        }

        $tpl_vars['errors'] = $errors;


        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);
        echo $tpl->fetch('design/applyDefaultAttachments.tpl');

        $view->setRendered(true);
    }

    public function update_page_attachments_apply_defaults()
    {
        $this->initUpdate();
        $pageId = (isset($_GET['pageId']) && $_GET['pageId']) ? $_GET['pageId'] : 0;

        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $pageId = (isset($_GET['pageId']) && $_GET['pageId']) ? $_GET['pageId'] : false;
        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        //set from GET value if set
        $t_set = (!$t_set && isset($_GET['t_set'])) ? $_GET['t_set'] : $t_set;
        $errors = '';

        if (!$t_set) {
            //get t_set from first template set we are currently working with...
            foreach ($this->_workWith as $set) {
                //set it to the first template set in the work with thingy
                $t_set = $set;
                break;
            }
        }
        if (!$t_set || !in_array($t_set, $this->_workWith) || $t_set == 'default') {
            $admin->userError('Template set specified (' . $t_set . ') is not currently being worked on or is not valid, cannot apply default attachments.');
            return false;
        }

        if (!$pageId) {
            //invalid page id?
            $admin->userError('Page ID not specified, cannot edit.');
            return false;
        }

        $fromFile = "default/main_page/attachments/templates_to_page/$pageId.php";
        $toFile = "$t_set/main_page/attachments/templates_to_page/$pageId.php";
        if (!file_exists($this->_file->absolutize($fromFile))) {
            $admin->userError('Could not find default attachments for page id specified.');
            return false;
        }
        if (!$this->_file->isChild("default/main_page/attachments/templates_to_page/", $fromFile)) {
            $admin->userError('Page ID specified is invalid!');
            return false;
        }

        $attachments = include $this->_file->absolutize($fromFile);
        //make sure each of the attachments exist in the template set
        foreach ($attachments as $langId => $langs) {
            foreach ($langs as $catId => $data) {
                if (is_array($data)) {
                    //there are some 3 dimensional arrays
                    foreach ($data as $val) {
                        if (!$this->_checkFileExistsFromDefault($val, $t_set)) {
                            return false;
                        }
                    }
                } else {
                    if (!$this->_checkFileExistsFromDefault($data, $t_set)) {
                        return false;
                    }
                }
            }
        }

        //copy over from default template set
        return $this->_file->copy($fromFile, $toFile);
    }

    public function display_page_attachments_restore_template()
    {
        $this->init();

        if (!geoAjax::isAjax()) {
            //display list of thingies
            return $this->display_page_attachments();
        }

        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $file = (isset($_GET['file']) && $_GET['file']) ? $_GET['file'] : false;
        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        //set from GET value if set
        $t_set = (!$t_set && isset($_GET['t_set'])) ? $_GET['t_set'] : $t_set;
        $errors = '';
        $fromFile = "default/main_page/$file";
        if (!$t_set) {
            //get t_set from first template set we are currently working with...
            foreach ($this->_workWith as $set) {
                //set it to the first template set in the work with thingy
                $t_set = $set;
                break;
            }
        }
        if (!$file) {
            //invalid page id?
            $errors .= 'Template to restore not specified, cannot proceed.<br />';
        } elseif (!$this->_file->isChild("default/main_page/", $fromFile)) {
            $errors .= 'Template specified is invalid!<br />';
        } elseif (!file_exists($this->_file->absolutize($fromFile))) {
            $errors .= 'Could not find default template for (' . $file . ') to restore.<br />';
        }

        if (!$t_set || !in_array($t_set, $this->_workWith) || $t_set == 'default') {
            $errors .= 'Template set specified (' . $t_set . ') is not currently being worked on or is not valid, cannot restore default template.<br />';
        }
        $tpl_vars['t_set'] = $t_set;
        $tpl_vars['file'] = $file;

        $tpl_vars['errors'] = $errors;


        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);
        echo $tpl->fetch('design/restoreDefaultTemplate.tpl');

        $view->setRendered(true);
    }

    public function update_page_attachments_restore_template()
    {
        $this->initUpdate();

        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $file = (isset($_GET['file']) && $_GET['file']) ? $_GET['file'] : false;
        $t_set = (isset($_POST['t_set']) && $_POST['t_set']) ? $_POST['t_set'] : false;

        //set from GET value if set
        $t_set = (!$t_set && isset($_GET['t_set'])) ? $_GET['t_set'] : $t_set;
        $errors = '';

        if (!$t_set || !in_array($t_set, $this->_workWith) || $t_set == 'default') {
            $admin->userError('Template set specified (' . $t_set . ') is not currently being worked on or is not valid, cannot restore default template.');
            return false;
        }

        if (!$file) {
            //invalid file?
            $admin->userError('Page ID not specified, cannot edit.');
            return false;
        }

        $fromFile = "default/main_page/$file";
        $toFile = "$t_set/main_page/$file";
        if (!file_exists($this->_file->absolutize($fromFile))) {
            $admin->userError('Could not find default template, cannot restore!');
            return false;
        }
        if (!$this->_file->isChild("default/main_page/", $fromFile)) {
            $admin->userError('Template specified is invalid!');
            return false;
        }
        return $this->_checkFileExistsFromDefault($file, $t_set);
    }

    private function _checkFileExistsFromDefault($filename, $t_set)
    {
        $tplFile = "$t_set/main_page/$filename";
        $defaultFile = "default/main_page/$filename";
        if (!file_exists($this->_file->absolutize($tplFile)) && file_exists($this->_file->absolutize($defaultFile))) {
            //copy file
            if (!$this->_file->copy($defaultFile, $tplFile)) {
                return false;
            }
            //check the attachment file as well
            $tplFile = "$t_set/main_page/attachments/modules_to_template/$filename.php";
            $defaultFile = "default/main_page/attachments/modules_to_template/$filename.php";
            if (!file_exists($this->_file->absolutize($tplFile)) && file_exists($this->_file->absolutize($defaultFile))) {
                //copy file
                if (!$this->_file->copy($defaultFile, $tplFile)) {
                    return false;
                }
            }
        }
        return true;
    }


    public function display_design_manage()
    {
        $this->init();
        $admin = geoAdmin::getInstance();
        $view = geoView::getInstance();

        $this->_activeTemplateSetWarning();

        $tpl_vars = array();
        if (geoAjax::isAjax()) {
            $tpl_vars = $view->getAssignedBodyVars();
        }
        $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
        $sortBy = 'filename';
        $sortOrder = 'up';
        if (isset($_GET['sortBy']) && $_GET['sortBy']) {
            $sortBy = $_GET['sortBy'];
        }
        if (isset($_GET['sortOrder']) && $_GET['sortOrder'] == 'down') {
            //sort order is specified, and it is specified as down.
            $sortOrder = 'down';
        }

        //check the location
        if (!is_dir($this->_file->absolutize($location)) || !$this->_file->inJail($location) || !geoString::isFilePath($location)) {
            $admin->userNotice('Current selected location (' . $location . '), is not found, is not a folder, or is not valid folder name.');
            //start off at template set(s)

            $location = '';
        }
        if ($location && substr($location, -1) != '/') {
            //add trailing slashy
            $location .= '/';
        }
        $location = geoFile::cleanPath($location);

        $locationInfo = $this->_fileInfo($location);
        if (!$this->_advMode && !$locationInfo['t_set'] && $this->_workWith[0]) {
            //we are at the tset level, force the location to go into the template set being worked on
            $newLs = array (
                $this->_workWith[0] . '/main_page/',
                $this->_workWith[0] . '/external/',
            );
            foreach ($newLs as $newL) {
                if (is_dir($this->_file->absolutize($newL))) {
                    $location = $newL;
                    $locationInfo = $this->_fileInfo($location);
                    break;
                }
            }
        }

        //figure out location info
        $tpl_vars['location'] = $location;
        $tpl_vars['locationInfo'] = $locationInfo;
        $t_set = false;
        //note: can create file logic is same as can upload file, so no need to have different vars.
        $tpl_vars['canCreateFolder'] = $tpl_vars['canCreateFile'] = 1;
        $tpl_vars['canUploadFile'] = 1;
        if ($locationInfo['t_set']) {
            $t_set = $locationInfo['t_set'];
            $tpl_vars['system_exists'] = ($this->_advMode && is_dir($this->_file->absolutize("$t_set/system/"))) ? 1 : 0;
            $tpl_vars['main_page_exists'] = (is_dir($this->_file->absolutize("$t_set/main_page/"))) ? 1 : 0;
            $tpl_vars['module_exists'] = ($this->_advMode && is_dir($this->_file->absolutize("$t_set/module/"))) ? 1 : 0;
            $tpl_vars['addon_exists'] = ($this->_advMode && is_dir($this->_file->absolutize("$t_set/addon/"))) ? 1 : 0;
            $tpl_vars['external_exists'] = (is_dir($this->_file->absolutize("$t_set/external/"))) ? 1 : 0;

            //figure out where we are, to see what main operations are permitted

            if ($t_set == 'default' && !$this->_canEditDefault) {
                //cannot create file or folder in default template set
                $tpl_vars['canCreateFolder'] = $tpl_vars['canCreateFile'] = $tpl_vars['canUploadFile'] = 0;
            } elseif (isset($locationInfo['type']) && !in_array($locationInfo['type'], array ('main_page','external'))) {
                //cannot create file or folder if not in main_page or external
                $tpl_vars['canCreateFolder'] = $tpl_vars['canCreateFile'] = 0;
                if (!$this->_canEditSystemTemplates) {
                    $tpl_vars['canUploadFile'] = 0;
                }
            } elseif (!$locationInfo['type']) {
                //cannot create file at base template set folder location
                $tpl_vars['canCreateFile'] = $tpl_vars['canUploadFile'] = 0;
            }
        } else {
            //cannot create file in base folder w/o template set
            $tpl_vars['canCreateFile'] = $tpl_vars['canUploadFile'] = 0;
        }

        //figure out each level and if it can be clicked
        $locationParts = array ();
        if ($location) {
            $parts = explode('/', trim($location, ' /'));
            $path = '';
            foreach ($parts as $level => $part) {
                $locationParts[$level]['location'] = $locationParts[$level]['title'] = $part;
                $locationParts[$level]['showLink'] = 1; //new design: always link
                if ($level == 0) {
                    //template set level
                    $locationParts[$level]['title'] = $part . ' - Template Set';
                    $path = $part;
                } else {
                    $path .= '/' . $part;
                }
                $locationParts[$level]['fullPath'] = $path;
                $locationParts[$level]['endPath'] = (int)(count($locationParts) == count($parts));
            }
        } else {
            $locationParts[] = array (
                'location' => 'All Template Sets',
                'title' => 'All Template Sets',
                'showLink' => (int)$this->_advMode,
                'fullPath' => '',
                'endPath' => 1
            );
        }
        $tpl_vars['locationParts'] = $locationParts;

        $tpl_vars['files'] = $files = $this->_getFiles($location, $sortBy, $sortOrder);

        //count up the sizes
        $size = $folderCount = $fileCount = 0;
        foreach ($files as $file) {
            $size += $file['size'];
            if ($file['is_dir']) {
                $folderCount++;
            } else {
                $fileCount++;
            }
        }
        $totalSize = geoNumber::filesizeFormat($size);

        $viewing = '-';
        //figure out what they are looking at
        if (!$locationInfo['t_set']) {
            //looking at template sets
            $viewing = 'All template sets';
        } elseif (!$locationInfo['type']) {
            //looking at different template location types
            $viewing = 'Template Location Types';
        } else {
            switch ($locationInfo['type']) {
                case 'addon':
                    $viewing = 'Addon Templates (over-ride an addon\'s own templates)';
                    break;

                case 'external':
                    $viewing = 'Media files (images, CSS, Javascript, etc.)';
                    break;

                case 'main_page':
                    $viewing = 'Main page templates (overall templates)';
                    break;

                case 'module':
                    $viewing = 'Module Templates (used for dymanic content produced by modules)';
                    break;

                case 'system':
                    $viewing = 'System templates (used for dynamic {main_body} contents)';
                    break;
            }
        }
        $tpl_vars['viewing'] = $viewing;

        //figure out access rights
        $tpl_vars['is_writable'] = 1;
        $tpl_vars['wReason'] = '';
        if (!is_writable($this->_file->absolutize($location))) {
            $tpl_vars['is_writable'] = 0;
            $tpl_vars['wReason'] = 'Folder Permissions (must CHMOD to 777)';
        }
        if (isset($locationInfo['t_set']) && $locationInfo['t_set'] == 'default' && !$this->_canEditDefault) {
            $tpl_vars['is_writable'] = 0;
            $tpl_vars['wReason'] = 'Cannot modify default templates.';
        }

        $tpl_vars['totalSize'] = $totalSize;
        $tpl_vars['fileCount'] = $fileCount;
        $tpl_vars['folderCount'] = $folderCount;
        $tpl_vars['sortBy'] = $sortBy;
        $tpl_vars['sortOrder'] = $sortOrder;
        $tpl_vars['location'] = $location;
        $tpl_vars['yesterday'] = strtotime('today midnight');
        $tpl_vars['isAjax'] = geoAjax::isAjax();

        $tpl_vars['adminMsgs'] = geoAdmin::m();

        if (geoAjax::isAjax()) {
            //only displaying partial page
            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            $tpl->display('design/parts/fileList.tpl');
            $view->setRendered(true);
            return;
        }
        $view->setBodyTpl('design/manage.tpl')
            ->setBodyVar($tpl_vars);
    }

    private function _getFiles($location, $sortBy = 'filename', $sortOrder = 'up')
    {
        $admin = geoAdmin::getInstance();

        //first get the file info
        $nameInfo = $this->_fileInfo($location);
        $inMainPage = (isset($nameInfo['type']) && $nameInfo['type'] == 'main_page');

        //since we primarily are not going through geoFile class, go ahead and absolutize it
        $location = $this->_file->absolutize($location);
        if ($location && substr($location, -1) != '/') {
            //add trailing slashy
            $location .= '/';
        }

        if (!$this->_file->inJail($location)) {
            $admin->userError('Error getting directory contents, directory outside of templates directory! (' . $location . ')');
            return false;
        }
        $validSortbys = array (
            'filename',
            'size',
            'type',
            'modified'
        );
        if (!in_array($sortBy, $validSortbys)) {
            //if sort by is not valid, default to filename
            $sortBy = 'filename';
        }
        if ($sortOrder == 'down') {
            $scanDir = $this->_file->scandir($location, false, false, false, 1);
        } else {
            $scanDir = $this->_file->scandir($location, false, false);
        }
        if (!$scanDir) {
            return array();
        }

        $filesUnsorted = array ();
        foreach ($scanDir as $entry) {
            if (!geoString::isFilePath($entry)) {
                //has some weird chars in it, skip it
                continue;
            }

            $fInfo = array ();
            $fInfo['filename'] = $entry;
            if ($nameInfo['t_set'] == 'default' && !$this->_canEditDefault) {
                //all files are readonly if in default template set
                $fInfo['readonly'] = 1;
            } elseif (
                !$this->_canEditSystemTemplates && $nameInfo['type'] &&
                $nameInfo['type'] != 'main_page' && $nameInfo['type'] != 'external'
            ) {
                $fInfo['readonly'] = 1;
            } else {
                $fInfo['readonly'] = (int)(!(is_writable($location . $entry)));
            }
            $stats = stat($location . $entry);
            $fInfo['modified'] = $stats['mtime'];
            $fInfo['cssClasses'] = '';
            $fInfo['title'] = $entry;
            $fInfo['is_t_set'] = $fInfo['hasMainpage'] = 0;
            if (is_dir($location . $entry)) {
                $fInfo['is_dir'] = 1;
                $fInfo['size'] = 0;
                $fInfo['type'] = 'folder';
                $fInfo['icon'] = 'folder-closed.png';
                if (!isset($nameInfo['t_set']) || !$nameInfo['t_set']) {
                    //we are at tset location level
                    $fInfo['is_t_set'] = 1;
                    $fInfo['type'] = 'template set';
                    $fInfo['hasMainpage'] = (int)is_dir($this->_file->absolutize($entry . '/main_page/'));
                    if ($entry == 'default' && !$this->_canEditDefault) {
                        //default is readonly
                        $fInfo['readonly'] = 1;
                        if (!$this->_advMode) {
                            //no show, not in adv mode
                            continue;
                        }
                    }
                    if (!in_array($entry, $this->_workWith)) {
                        if (!$this->_advMode) {
                            //no show, not in adv mode
                            continue;
                        }
                        $fInfo['hasMainpage'] = 0;
                        if ($entry == '.min') {
                            $fInfo['cssClasses'] .= ' minTset';
                            $fInfo['title'] .= ' - folder used for the combined / minified CSS and JS contents.';
                        } elseif ($entry == '_temp') {
                            $fInfo['cssClasses'] .= ' tempTset';
                            $fInfo['title'] .= ' - temporary folder, used by system.';
                        } elseif (substr($entry, 0, 1) == '_') {
                            $fInfo['cssClasses'] .= ' hiddenTset';
                            $fInfo['title'] .= ' - Ignored by system (starts with _ character)';
                        } else {
                            $fInfo['cssClasses'] .= ' restrictedTset';
                            $fInfo['title'] .= ' - Not within template sets working with.';
                        }
                    }
                } elseif ($nameInfo['type'] == 'main_page' && !$nameInfo['localFile'] && $entry == 'attachments') {
                    //this is an attachments directory, restrict it
                    if (!$this->_advMode) {
                        //no show, not in adv mode
                        continue;
                    }
                    $fInfo['is_attachments'] = 1;
                    $fInfo['cssClasses'] .= ' restrictedAttachments';
                    $fInfo['title'] .= ' - Restricted system attachments folder.';
                } elseif (!$this->_advMode && !$nameInfo['type'] && !in_array($entry, array('main_page','system'))) {
                    //no show, not in adv mode
                    continue;
                }
            } else {
                $fInfo['is_dir'] = 0;
                $fInfo['size'] = $bytes = $stats['size'];

                if ($bytes > 1024) {
                    $kbytes = ($bytes / 1024);
                    if ($kbytes > 1024) {
                        $mbytes = $kbytes / 1024;
                        $fInfo['size_mb'] = round($mbytes, 2);
                    } else {
                        $fInfo['size_kb'] = round($kbytes, 2);
                    }
                }

                $fInfo['type'] = $type = $this->_getFiletype($entry);
                switch ($type) {
                    case 'tpl':
                    case 'html':
                    case 'htm':
                        //break ommited on previous cases on purpose
                        //this is a template file
                        $fInfo['icon'] = 'file-tpl.png';
                        break;

                    case 'css':
                        $fInfo['icon'] = 'file-css.png';
                        break;

                    case 'js':
                        $fInfo['icon'] = 'file-js.png';
                        break;

                    case 'php':
                        $fInfo['icon'] = 'file-php.png';
                        break;

                    case 'jpg':
                    case 'gif':
                    case 'png':
                    case 'jpeg':
                        //break ommited on previous cases on purpose
                        //image file
                        $fInfo['icon'] = 'file-image.png';
                        break;

                    default:
                        $fInfo['icon'] = 'file.png';
                }

                if ($type == 'tpl' && $inMainPage) {
                    //grab the attachments
                    $localFile = ($nameInfo['localFile']) ? $nameInfo['localFile'] . '/' . $entry : $entry;
                    $attachFile = "{$nameInfo['t_set']}/main_page/attachments/modules_to_template/$localFile.php";
                    $attachments = $this->getModulesToTemplate($attachFile);

                    $fInfo['attachments']['modules'] = (isset($attachments['modules'])) ? count($attachments['modules']) : 0;
                    $fInfo['attachments']['addons'] = (isset($attachments['addons'])) ? count($attachments['addons']) : 0;
                    $fInfo['attachments']['sub_pages'] = (isset($attachments['sub_pages'])) ? count($attachments['sub_pages']) : 0;
                } elseif ($type == 'php') {
                    //see if it is the t_sets.php
                    if (!$this->_advMode) {
                        //no show, not in adv mode
                        continue;
                    }

                    if ((!isset($nameInfo['t_set']) || !$nameInfo['t_set'])) {
                        //PHP files in the base folder... a few have special roles,
                        //go ahead and let the user know what they do
                        if ($entry == 't_sets.php') {
                            //we are at tset location level
                            $fInfo['cssClasses'] .= ' restrictedTset_file';
                            $fInfo['type'] = 'system file';
                            $fInfo['title'] .= ' - system file used to save active template sets.';
                        } elseif ($entry == 'min.php') {
                            //min.php file
                            $fInfo['cssClasses'] .= ' restrictedMin_file';
                            $fInfo['type'] = 'system file';
                            $fInfo['title'] .= ' - system file used to generate combined CSS and JS contents.';
                        } else {
                            //generic PHP file - restrict access to php files
                            $fInfo['cssClasses'] .= ' restrictedPhpFile';
                        }
                    } else {
                        //restrict access to php files
                        $fInfo['cssClasses'] .= ' restrictedPhpFile';
                    }
                } elseif ($entry == '.htaccess' && (!isset($nameInfo['t_set']) || !$nameInfo['t_set'])) {
                    //htaccess file at the top level...
                    if (!$this->_advMode) {
                        //no show, not in adv mode
                        continue;
                    }
                    $fInfo['cssClasses'] .= ' htaccess_file';
                    $fInfo['type'] = 'system file';
                    $fInfo['title'] .= ' - Generated by the system to set various apache settings.';
                }
            }
            $topLevel = ($fInfo['is_dir']) ? 0 : 1;

            $filesUnsorted[$topLevel][$fInfo[$sortBy]][] = $fInfo;
        }
        //sort by key to get them sorted in direction of whatever
        $sortFlag = ($sortBy == 'size' || $sortBy == 'modified') ? SORT_NUMERIC : SORT_STRING;
        if ($sortOrder == 'down') {
            //backwards order
            krsort($filesUnsorted, SORT_NUMERIC);//$sortFlag);
        } else {
            //forwards order
            ksort($filesUnsorted, SORT_NUMERIC);//$sortFlag);
        }

        //now go through semi-unsorted and add to sorted array, so they end up all nice and in order
        $files = array ();
        $index = 0;
        foreach ($filesUnsorted as $subList) {
            if ($sortOrder == 'down') {
                //backwards order
                krsort($subList, $sortFlag);
            } else {
                //forwards order
                ksort($subList, $sortFlag);
            }
            foreach ($subList as $subSubList) {
                foreach ($subSubList as $thisEntry) {
                    $thisEntry['cssClasses'] .= ' fileListRow_' . $i;
                    $files[$index] = $thisEntry;
                    $index++;
                }
            }
        }
        return $files;
    }

    public function display_design_download_file()
    {
        $this->init();
        //should not really be displaying anything, update does all the work
        if (!$this->_downloadSuccess) {
            //oops, did not download, display page
            $this->display_design_manage();
        } else {
            geoView::getInstance()->setRendered(true);
        }
    }

    public function update_design_download_file()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();
        $file = (isset($_POST['file']) && $_POST['file']) ? $_POST['file'] : false;
        if (!$file) {
            $admin->userError('Invalid file download specified.');
            return false;
        }
        $this->_downloadSuccess = $this->_file->download($file);
        return $this->_downloadSuccess;
    }

    public function display_design_delete_files()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_design_manage();
        }
        $this->init();
        $view = geoView::getInstance();

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl_vars = $view->getAssignedBodyVars();

        $tpl_vars['location'] = (isset($_GET['location'])) ? $_GET['location'] : '';

        //ok list of files will be in GET
        $files = $tpl_vars['files'] = (isset($_GET['files']) && $_GET['files']) ? $_GET['files'] : false;

        foreach ($files as $file) {
            $fileList = $this->_deleteFiles($file);

            if (!count($fileList)) {
                //invalid file?
                continue;
            }

            $tpl_vars['fileList'][$file] = $fileList;
            $tpl_vars['deleteFile'][$file] = $file;
        }

        $tpl->assign($tpl_vars);
        echo $tpl->fetch('design/deleteConfirm.tpl');
        $view->setRendered(true);
        return;
    }

    public function update_design_delete_files()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();
        $deleteFiles = (isset($_POST['deleteFiles']) && $_POST['deleteFiles']) ? $_POST['deleteFiles'] : false;
        if (!$deleteFiles) {
            geoAdmin::m('Invalid input.', geoAdmin::ERROR);
            return false;
        }

        //first, get the fileList
        foreach ($deleteFiles as $deleteFile) {
            //ok now actually delete the files:
            if (!$this->_deleteFiles($deleteFile, false)) {
                $admin->userError('File(s) failed to delete: ' . $deleteFile);
                return false;
            }
        }
        $admin->userSuccess('File(s) Deleted successfully.');
        return true;
    }

    public function display_design_preview_file()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_design_manage();
        }
        $this->init();

        geoView::getInstance()->setRendered(true);

        $data = array ();

        $file = (isset($_GET['file']) && $_GET['file']) ? $_GET['file'] : false;

        $data['file'] = $file = geoFile::cleanPath($file);

        if (!$file) {
            $data['error'] = 'No preview file specified.';
            $this->echoJson($data);
            return;
        }

        if (!$this->_file->inJail($file)) {
            //not in jail!
            $data['error'] = 'File outside templates dir!';
            $this->echoJson($data);
            return;
        }
        $absFile = $this->_file->absolutize($file);

        if (!file_exists($absFile)) {
            $data['error'] = 'No such file found!';
            $this->echoJson($data);
            return;
        }

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $localFile = (strpos($file, '/') < strlen($file) - 1) ? substr($file, strrpos($file, '/', -2) + 1) : $file;
        $tpl->assign('localFile', $localFile);
        $tpl->assign('fInfo', $fInfo = $this->_fileInfo($file));
        $tpl->assign('file', $file);
        $tpl->assign('is_folder', $is_folder = is_dir($absFile));
        if ($is_folder) {
            $tpl->assign('fileType', $fileType = $data['fileType'] =  'folder');
        } else {
            $tpl->assign('fileType', $fileType = $data['fileType'] = $this->_getFileType($file));
        }

        $access = 'Read/Write';
        $canEdit = $canView = true;
        if ($fInfo['t_set'] == 'default' && !$this->_canEditDefault) {
            $access = 'Read Only (default template set)';
            $canEdit = false;
        } elseif (!is_writable($absFile)) {
            $access = 'Read Only (file permissions)';
            $canEdit = false;
        } elseif (!$fInfo['type'] || (!$this->_canEditSystemTemplates && !in_array($fInfo['type'], array('main_page','external')))) {
            $access = 'Read Only (restricted folder)';
            $canEdit = false;
        }

        if (!in_array($fileType, array ('tpl','css','js','html','htm','txt'))) {
            $canEdit = false;
            $canView = false;
        }

        $tpl->assign('access', $access);
        $tpl->assign('canEdit', $canEdit);
        $tpl->assign('canView', $canView);

        $tpl->assign('is_writable', is_writable($absFile));

        $tpl->assign('stats', $stats = stat($absFile));
        $tpl->assign('size', geoNumber::filesizeFormat($stats['size']));

        $tpl->assign('yesterday', strtotime('today midnight'));

        $tpl->assign('location', dirname($file) . '/');

        if ($fInfo['type'] == 'main_page' && $fInfo['localFile']) {
            $attachFile = "{$fInfo['t_set']}/main_page/attachments/modules_to_template/{$fInfo['localFile']}.php";
            if (file_exists($this->_file->absolutize($attachFile))) {
                $attachments = $this->getModulesToTemplate($attachFile);
                foreach ($attachments['sub_pages'] as $key => $subPageFile) {
                    $tsets = $this->_whatTSets("main_page/$subPageFile");
                    $attachments['sub_pages'][$key] = array('name' => $subPageFile, 'tsets' => $tsets);
                }

                $tpl->assign('attachments', $attachments);
            }
            $tpl->assign('pageNames', $this->_getPageNames());
        }

        if ($is_folder) {
            $fileList = $this->_getFiles($file);
            $contents = "";
            if (!count($fileList)) {
                $contents = 'Empty Folder';
            } else {
                foreach ($fileList as $entry) {
                    $contents .= $entry['filename'];
                    if ($entry['is_dir']) {
                        $contents .= '/';
                    }
                    $contents .= "\n";
                }
            }
            $data['previewFull'] = $data['preview'] = $contents;

            $tpl->assign('filePreviewType', 'Folder Contents');
        } else {
            $imageTypes = array (
                'jpg','bmp','jpeg','gif','png'
            );
            if (in_array($fileType, $imageTypes)) {
                $url = '../' . GEO_TEMPLATE_LOCAL_DIR . $file;
                $dims = getimagesize($absFile);
                if (!$dims) {
                    $style = "width: 100%; height: 100%;";
                } else {
                    $dims = geoImage::getScaledSize($dims[0], $dims[1], 150, 110);

                    $style = "width: {$dims['width']}px; height: {$dims['height']}px;";
                }
                $data['preview'] = '<img src="' . $url . '" alt="preview" style="' . $style . '" />';
                $data['previewFull'] = '<img src="' . $url . '" alt="preview" />';
                $tpl->assign('filePreviewType', 'Image Preview');
            } else {
                $contents = file_get_contents($absFile);

                $data['previewFull'] = $data['preview'] = htmlspecialchars($contents);
                if ($fileType == 'tpl') {
                    //surround with textarea for the WYSIWYG editor
                    $tpl->assign('filePreviewType', 'Template Source Code');
                } elseif ($fileType == 'html') {
                    $tpl->assign('filePreviewType', 'HTML Source Code');
                } else {
                    $tpl->assign('filePreviewType', 'File Contents');
                }
            }
        }

        $data['contents'] = $tpl->fetch('design/previewFile.tpl');
        $this->echoJson($data);
        return;
    }

    public function display_design_copy_files()
    {
        $this->init();

        if (geoAjax::isAjax()) {
            $view = geoView::getInstance();
            $admin = geoAdmin::getInstance();
            //this is an ajax call, just display the template

            //since displaying template directly, need to get tpl vars set in init()
            $tpl_vars = $view->getAssignedBodyVars();

            //Check out the file to make sure it's valid
            $files = $tpl_vars['files'] = (isset($_GET['files']) && $_GET['files']) ? $_GET['files'] : false;

            $fromFolder = $tpl_vars['fromFolder'] = (isset($_GET['fromFolder']) && $_GET['fromFolder']) ? geoFile::cleanPath($_GET['fromFolder']) : '';
            $toFolder = $tpl_vars['toFolder'] = (isset($_GET['toFolder']) && $_GET['toFolder']) ? geoFile::cleanPath($_GET['toFolder']) : '';
            $location = $tpl_vars['location'] = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';

            $actionType = $tpl_vars['actionType'] = (isset($_GET['actionType']) && in_array($_GET['actionType'], array('copy','cut'))) ? $_GET['actionType'] : 'copy';

            $errors = '';

            if (!$fromFolder) {
                $errors .= 'From folder not valid or not specified.<br />';
            }
            if (!$toFolder) {
                $errors .= 'To folder not valid or not specified.<br />';
            }
            if (!file_exists($this->_file->absolutize($toFolder))) {
                //attempt to create folder
                $this->_file->mkdir($toFolder);
            }

            if (($fromFolder && !is_dir($this->_file->absolutize($fromFolder))) || ($toFolder && !is_dir($this->_file->absolutize($toFolder)))) {
                $errors .= 'From or To folder is not valid (has it been removed/renamed?).<br />';
            }

            //Check from/to dirs: make sure they are same type, and that they have tset and type
            if (!$errors) {
                $fromInfo = $this->_fileInfo($fromFolder);
                $toInfo = $this->_fileInfo($toFolder);

                if (!$fromInfo['t_set']) {
                    $errors .= 'No from folder template set, invalid selection.<br />';
                } elseif (!$fromInfo['type']) {
                    $errors .= 'From template location type not known, invalid selection.<br />';
                }

                if (!$toInfo['t_set']) {
                    $errors .= 'No to folder template set, invalid selection.<br />';
                } elseif ($toInfo['t_set'] == 'default' && !$this->_canEditDefault) {
                    //block pasting to default template set
                    $errors .= 'Cannot modify default template set.<br />';
                }

                if (!$toInfo['type']) {
                    $errors .= 'To template location type not known, invalid selection.<br />';
                } elseif ($fromInfo['type'] !== $toInfo['type']) {
                    $errors .= 'From location type (' . $fromInfo['type'] . ') does not match to location type (' . $toInfo['type'] . '), invalid selection.<br />';
                } elseif ($fromInfo['type'] !== 'main_page' && $fromInfo['type'] !== 'external' && !$this->_canEditSystemTemplates) {
                    $errors .= 'Cannot make changes to locations outside of main_page or external.<br />';
                }

                if ($fromFolder == $toFolder) {
                    $errors .= 'From and To folder the same, invalid operation.<br />';
                }

                if ($actionType == 'cut' && $fromInfo['t_set'] == 'default' && !$this->_canEditDefault) {
                    $errors .= 'Cannot cut from default template set.';
                }
            }
            //we can check each individual file upon copying, don't need to check now...

            $tpl_vars['errorMsgs'] = $errors;

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            echo $tpl->fetch('design/copyFile.tpl');
            //make sure rest of page doesn't display
            $view->setRendered(true);
        } else {
            //display whole page
            $this->display_design_manage();
        }
    }

    public function update_design_copy_files()
    {
        $admin = geoAdmin::getInstance();
        $this->initUpdate();

        //Clean inputs
        $files = (isset($_POST['files']) && $_POST['files']) ? $_POST['files'] : false;
        if (!$files) {
            $admin->userError('Invalid files specified for copy!');
            return false;
        }

        $fromFolder = (isset($_POST['fromFolder']) && $_POST['fromFolder']) ? geoFile::cleanPath($_POST['fromFolder']) : '';
        $toFolder = (isset($_POST['toFolder']) && $_POST['toFolder']) ? geoFile::cleanPath($_POST['toFolder']) : '';
        $actionType = (isset($_POST['actionType']) && in_array($_POST['actionType'], array('copy','cut'))) ? $_POST['actionType'] : 'copy';

        if (!$fromFolder) {
            $admin->userError('From directory not valid or not specified.');
            return false;
        }
        if (!$toFolder) {
            $admin->userError('To directory not valid or not specified.');
            return false;
        }
        //make sure from and to dir has trailing slashy
        if (substr($fromFolder, -1) != '/') {
            $fromFolder .= '/';
        }
        if (substr($toFolder, -1) != '/') {
            $toFolder .= '/';
        }

        if (!is_dir($this->_file->absolutize($fromFolder)) || !is_dir($this->_file->absolutize($toFolder))) {
            $admin->userError('From folder (' . $fromFolder . ') or to folder (' . $toFolder . ') is not valid (has it been removed/renamed?).');
            return false;
        }

        //Check from/to dirs: make sure they are same type, and that they have tset and type
        $fromInfo = $this->_fileInfo($fromFolder);
        $toInfo = $this->_fileInfo($toFolder);

        if (!$fromInfo['t_set']) {
            $admin->userError('No from folder template set, invalid selection.');
            return false;
        } elseif (!$fromInfo['type']) {
            $admin->userError('From template location type not known, invalid selection.');
            return false;
        }

        if (!$toInfo['t_set']) {
            $admin->userError('No to folder template set, invalid selection.');
            return false;
        } elseif (!$toInfo['type']) {
            $admin->userError('To template location type not known, invalid selection.');
            return false;
        } elseif ($toInfo['t_set'] == 'default' && !$this->_canEditDefault) {
            //block pasting to default template set
            $admin->userError('Cannot modify default template set.');
            return false;
        }
        if ($actionType == 'cut' && $fromInfo['t_set'] == 'default' && !$this->_canEditDefault) {
            $admin->userError('Cannot cut from default template set.');
            return false;
        }
        if ($fromInfo['type'] != $toInfo['type']) {
            $admin->userError('From location type (' . $fromInfo['type'] . ') does not match to location type (' . $toInfo['type'] . '), invalid selection.');
            return false;
        }

        if ($fromFolder == $toFolder) {
            $admin->userError('From and To directory the same, invalid operation.');
            return false;
        }
        //either copy or rename, depending on if copy or cut
        $performAction = ($actionType == 'copy') ? 'copy' : 'rename';
        foreach ($files as $file) {
            $fromFile = $fromFolder . $file;
            $toFile = $toFolder . $file;
            if (!$this->_checkFile($fromFile) || !$this->_checkFile($toFile)) {
                return false;
            }

            //make sure both are "inside" the from/to location
            if (!$this->_file->isChild($fromFolder, $fromFile)) {
                $admin->userError('From file (' . $fromFile . ') is "above" the from location(' . $fromFolder . '), cannot copy!');
                return false;
            }
            if (!$this->_file->isChild($toFolder, $toFile)) {
                $admin->userError('To file (' . $toFile . ') is "above" the to location(' . $toFolder . '), cannot copy!');
                return false;
            }

            $fInfo = $this->_fileInfo($fromFile);

            $t_set = $fInfo['t_set'];
            $localFile = $fInfo['localFile'];
            $type = $fInfo['type'];

            //copy attachment files as well, if applicable...
            if ($type == 'main_page') {
                //make sure it is not a sub-directory of attachments
                if ($this->_file->isChild("$t_set/main_page/attachments/", $fromFile)) {
                    $admin->userError('File being copied from is in attachments sub-directory!  Cannot copy.');
                    return false;
                }
                if ($this->_file->isChild("{$toInfo['t_set']}/main_page/attachments/", $toFile)) {
                    $admin->userError('File being copied to (' . $toFile . ') is in attachments sub-directory!  Cannot copy.');
                    return false;
                }

                $extension = (is_dir($fromFile)) ? '' : '.php';

                if (file_exists(GEO_TEMPLATE_DIR . "$t_set/main_page/attachments/modules_to_template/$localFile{$extension}")) {
                    //The FROM is easy...
                    $fromAttached = "$t_set/main_page/attachments/modules_to_template/$localFile{$extension}";

                    //The TO is tricky...
                    $attachedInfo = $this->_fileInfo($toFile);
                    $toAttached = GEO_TEMPLATE_DIR . "{$attachedInfo['t_set']}/main_page/attachments/modules_to_template/{$attachedInfo['localFile']}$extension";
                    //either copy or rename, depending on if copy or cut
                    if (!$this->_file->$performAction($fromAttached, $toAttached)) {
                        return false;
                    }
                }
            }
            //either copy or rename, depending on if copy or cut
            if (!$this->_file->$performAction($fromFile, $toFile)) {
                return false;
            }
            //copy file(s)

            $admin->userNotice((($actionType == 'copy') ? 'Copied' : 'Moved') . ' file(s) from (' . $fromFile . ') to (' . $toFile . ') successfully.');
        }
        $admin->userSuccess('Finished ' . (($actionType == 'copy') ? 'copying' : 'moving') . ' files in file clipboard.');
        return true;
    }

    public function display_design_rename_file()
    {
        $this->init();

        if (!geoAjax::isAjax()) {
            //display the whole page
            return $this->display_design_manage();
        }

        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        //this is an ajax call, just display the template

        //since displaying template directly, need to get tpl vars set in init()
        $tpl_vars = $view->getAssignedBodyVars();

        //Check out the file to make sure it's valid
        $file = $tpl_vars['file'] = (isset($_GET['file']) && $_GET['file']) ? $_GET['file'] : false;

        $location = $tpl_vars['location'] = (isset($_GET['location']) && $_GET['location']) ? geoFile::cleanPath($_GET['location']) : '';

        if (!$this->_checkFile($file)) {
            //error when checking out the file being copied...
            $tpl_vars['adminMsgs'] = geoAdmin::m();
            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            echo $tpl->fetch('design/copyFile.tpl');

            //make sure rest of page doesn't display
            $view->setRendered(true);

            return;
        }

        $fInfo = $this->_fileInfo($file);

        $t_set = $tpl_vars['t_set'] = $fInfo['t_set'];
        $localFile = $tpl_vars['tLocalFile'] = $tpl_vars['localFile'] = $fInfo['localFile'];
        $type = $tpl_vars['tType'] = $fInfo['type'];

        $absFile = GEO_TEMPLATE_DIR . "$t_set/$type/$localFile";

        $tpl_vars['is_dir'] = is_dir($absFile);
        $tpl_vars['defaults'] = $_GET['defaults'];

        $toDirs = $this->_file->scandir(GEO_TEMPLATE_DIR . "$t_set/$type/", true, false, true);

        if ($type == 'main_page' && isset($toDirs['attachments'])) {
            //get rid of attachments/ and all sub-dirs
            unset($toDirs['attachments']);
            foreach ($toDirs as $key => $val) {
                if (strpos($val, 'attachments/') === 0) {
                    //is a sub-dir of attachments
                    unset($toDirs[$key]);
                }
            }
        }
        //remove directory it is in
        if (isset($toDirs[$localFile])) {
            unset($toDirs[$localFile]);
        }
        if (strpos($localFile, '/') !== false) {
            //in a sub-directory, kill that sub-directory
            $dir = substr($localFile, 0, strrpos($localFile, '/'));

            $tpl_vars['localFile'] = substr($localFile, (strrpos($localFile, '/') + 1));
            $tpl_vars['selectedDir'] = substr($localFile, 0, strrpos($localFile, '/'));
        }
        $tpl_vars['folderOption'] = ($tpl_vars['addBaseDir'] || count($toDirs) > 0);
        $tpl_vars['tsetOption'] = (count($this->_workWith) > 1);

        $tpl_vars['toDirs'] = $toDirs;

        $view->setRendered(true);

        //figure out what directory locations can be copied from


        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);
        echo $tpl->fetch('design/renameFile.tpl');
        //make sure rest of page doesn't display
        $view->setRendered(true);
    }

    public function update_design_rename_file()
    {
        $admin = geoAdmin::getInstance();
        $this->initUpdate();

        //Clean inputs
        $file = (isset($_POST['file']) && $_POST['file']) ? $_POST['file'] : false;

        if (!$this->_checkFile($file)) {
            return false;
        }
        $toFilename = (isset($_POST['localNewName']) && $_POST['localNewName']) ? $_POST['localNewName'] : false;

        if (!$toFilename) {
            $admin->userError('No filename specified!');
            return false;
        }

        $copyOnly = (isset($_POST['move_or_copy']) && $_POST['move_or_copy'] == 'make_copy');

        $copyToText = ($copyOnly) ? 'copy to' : 'rename or move to';

        //generate to file location

        $fInfo = $this->_fileInfo($file);

        $t_set = $fInfo['t_set'];
        $localFile = $fInfo['localFile'];
        $type = $fInfo['type'];

        $fromLocal = "$t_set/$type/$localFile";
        $from = $this->_file->absolutize($fromLocal);

        $toDir = (isset($_POST['toDir']) && $_POST['toDir']) ? $_POST['toDir'] : false;
        if (!$toDir) {
            $admin->userError('Invalid input!  (no to directory specified)');
            return false;
        }
        $localfileTo = ($toDir == '.') ? $toFilename : $toDir . '/' . $toFilename;

        $renamed = ($toDir == '.') ? $toFilename : $toDir . '/' . $toFilename;
        $toDir = ($toDir == '.') ? '/' : "/$toDir/";

        $toLocal = "$t_set/$type{$toDir}$toFilename";
        if (!$this->_checkFile($toLocal)) {
            $admin->userError('Error with directory/name specified, cannot complete copy.');
            return false;
        }

        $to = $this->_file->absolutize($toLocal);

        if ($from == $to) {
            //can't move to itself!
            $admin->userError('No change specified, nothing to ' . $copyToText . '!');
            return false;
        }

        //make sure destination does not exist
        if (file_exists($to)) {
            $admin->userError('Destination already exists, cannot ' . $copyToText . ' this location.');
            return false;
        }

        if (geoPC::is_trial()) {
            //check the to filename for weirdness, prevent renaming a file to end in something like .php.tpl
            if (!is_dir($from) && !$this->_checkNameTrials($to)) {
                //checks failed on new filename
                return false;
            }
        }

        $actionMethod = ($copyOnly) ? 'copy' : 'rename';

        //copy attachment files as well, if applicable...
        if ($type == 'main_page') {
            //For every file being renamed/moved, adjust attachments...
            //first, get the fileList (this won't actually delete anything...
            $fileList = $this->_deleteFiles($fromLocal);
            if (!$fileList) {
                $admin->userError('Could not find any applicable files to ' . (($copyOnly) ? 'copy' : 'rename') . '!');
                return false;
            }
            $attachmentsList = $this->_getPagesUsingTemplates($fileList);
            $attachMsg = '';
            $fileListSimple = array();
            if ($attachmentsList) {
                //generate a file list of easier to parse files
                foreach ($fileList as $t_set => $types) {
                    if (!$t_set || !in_array($t_set, $this->_workWith)) {
                        //can't do anything, invalid template set
                        continue;
                    }
                    foreach ($types as $type => $files) {
                        if (!$type || !in_array($type, $this->_validTypes)) {
                            //can't work with this type
                            continue;
                        }
                        foreach ($files as $f => $file) {
                            if ($f != 'attachments') {
                                //normal template file, check template to page attachments
                                $fileListSimple[$file] = $file;
                            }
                        }
                    }
                }
            }

            if (!$copyOnly && isset($attachmentsList['templates_to_page']) && $attachmentsList['templates_to_page']) {
                //Auto-adjust tempalte to page attachments!  Not if only doing copy though
                $attachMsg .= ', template to page attachments adjusted';

                //template set will be first index in array
                reset($fileList);
                $t_set = key($fileList);

                foreach ($attachmentsList['templates_to_page'] as $thisFile) {
                    $attachFileUpdated = GEO_TEMPLATE_DIR . "$t_set/main_page/attachments/templates_to_page/{$thisFile}.php";
                    $attachments = include $attachFileUpdated;

                    //get rid of any and all templates found in the array
                    foreach ($fileListSimple as $fromfile) {
                        $tof = $localfileTo . substr($fromfile, strlen($localFile));
                        $attachments = geoArrayTools::searchAndReplace($fromfile, $tof, $attachments);
                    }
                    //now apply changes
                    $tpl = new geoTemplate(geoTemplate::ADMIN);
                    $tpl->assign('page_attachments', $attachments);

                    //die ("New contents of $attachFileUpdated: <br /><pre>".htmlspecialchars($tpl->fetch('design/files/templates_to_page.tpl')));
                    $result = $this->_file->fwrite($attachFileUpdated, $tpl->fetch('design/files/templates_to_page.tpl'));
                    if (!$result) {
                        //error writing file
                        return false;
                    }
                    unset($attachments, $return, $tpl, $attachFileUpdated);
                }
            }

            if (!$copyOnly && isset($attachmentsList['modules_to_template']) && $attachmentsList['modules_to_template']) {
                //Auto-adjust module to template attachments (sub-templates)
                $attachMsg .= ', sub-template to template attachments adjusted';

                //template set will be first index in array
                reset($fileList);
                $t_set = key($fileList);

                foreach ($attachmentsList['modules_to_template'] as $thisFile) {
                    $attachFileUpdated = GEO_TEMPLATE_DIR . "$t_set/main_page/attachments/modules_to_template/{$thisFile}.php";

                    $tplFile = GEO_TEMPLATE_DIR . "$t_set/main_page/{$thisFile}";

                    $attachments = $this->getModulesToTemplate($attachFileUpdated);

                    $tplContents = file_get_contents($tplFile);

                    //get rid of any and all templates found in the array
                    foreach ($fileListSimple as $fromfile) {
                        $tof = $localfileTo . substr($fromfile, strlen($localFile));

                        $attachments = geoArrayTools::searchAndReplace($fromfile, $tof, $attachments);

                        //OK now update template itself

                        $search = array(
                            '{include file="' . $fromfile . '"}',
                            '{include file=\'' . $fromfile . '\'}',
                        );
                        $replace = '{include file="' . $tof . '"}';

                        $tplContents = str_replace($search, $replace, $tplContents);
                    }
                    //now apply changes
                    $tpl = new geoTemplate(geoTemplate::ADMIN);

                    $tpl->assign($attachments);
                    $tpl->assign('filename', $thisFile);

                    $result = $this->_file->fwrite($attachFileUpdated, $tpl->fetch('design/files/modules_to_template.tpl'));
                    if (!$result) {
                        //error writing file
                        return false;
                    }

                    //apply the changes to the template file
                    if ($tplContents) {
                        //echo "Writing: file: $thisFile<br />full: ".GEO_TEMPLATE_DIR."$t_set/main_page/{$thisFile} contents:<br /><pre style='border: black solid 1px;'>".htmlspecialchars($tplContents)."</pre><br /><br />";
                        if (!$this->_file->fwrite(GEO_TEMPLATE_DIR . "$t_set/main_page/{$thisFile}", $tplContents)) {
                            return false;
                        }
                    }
                    unset($attachments, $return, $tpl, $already_attached, $attachFileUpdated, $tplContents);
                }
            }

            //now do a simple re-name of this template(s)' module attachment file(s).
            $extension = (is_dir($from)) ? '' : '.php';

            if (file_exists(GEO_TEMPLATE_DIR . "$t_set/main_page/attachments/modules_to_template/$localFile{$extension}")) {
                //The FROM is easy...
                $fromAttached = GEO_TEMPLATE_DIR . "$t_set/main_page/attachments/modules_to_template/$localFile{$extension}";

                //The TO is tricky...
                $attachedInfo = $this->_fileInfo($toLocal);
                $toAttached = GEO_TEMPLATE_DIR . "{$attachedInfo['t_set']}/main_page/attachments/modules_to_template{$toDir}{$toFilename}$extension";

                if (!$this->_file->$actionMethod($fromAttached, $toAttached)) {
                    return false;
                }
            }
        }

        if (!$this->_file->$actionMethod($from, $to)) {
            return false;
        }
        //copy file(s)

        $admin->userSuccess((($copyOnly) ? 'Copied' : 'Renamed') . ' file(s) from (' . $fromLocal . ') to (' . $toLocal . ') successfully.');
        return true;
    }
    public function display_design_new_folder()
    {
        $this->init();

        if (geoAjax::isAjax()) {
            $view = geoView::getInstance();
            $admin = geoAdmin::getInstance();
            //this is an ajax call, just display the template
            $errors = '';

            $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
            if (!$this->_file->inJail($location)) {
                //invalid location
                $errors .= 'Invalid location specified.';
            }
            //since displaying template directly, need to get tpl vars set in init()
            $tpl_vars = $view->getAssignedBodyVars();
            if ($location && substr($location, -1) != '/') {
                //be sure it has trailing slashy
                $location .= '/';
            }
            $location = geoFile::cleanPath($location);
            $tpl_vars['location'] = $location;

            $locationInfo = $tpl_vars['locationInfo'] = $this->_fileInfo($location);

            if ($locationInfo['t_set'] == 'default' && !$this->_canEditDefault) {
                $errors .= 'Cannot modify default template set.';
            } elseif (isset($locationInfo['type']) && !in_array($locationInfo['type'], array ('main_page','external'))) {
                $errors .= 'Cannot create folder/file in system/module/addon locations.
				Instead, copy any template(s) you may wish to modify from the default template
				set, to your own template set, then you can modify it there.';
            }

            $tpl_vars['errorMsgs'] = $errors;

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            echo $tpl->fetch('design/newFolder.tpl');
            //make sure rest of page doesn't display
            $view->setRendered(true);
        } else {
            //display the whole page
            $this->display_design_manage();
        }
    }

    public function update_design_new_folder()
    {
        $admin = geoAdmin::getInstance();
        $this->initUpdate();

        $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
        if ($location && substr($location, -1) != '/') {
            //be sure it has trailing slashy
            $location .= '/';
        }
        $locationInfo = $this->_fileInfo($location);
        $t_set = $locationInfo['t_set'];
        $type = $locationInfo['type'];

        $name = (isset($_POST['name']) && $_POST['name']) ? $_POST['name'] : false;

        if ($t_set && !in_array($t_set, $this->_workWith)) {
            $admin->userError('Invalid template set!  You are not currently working with the template set.');
            return false;
        }
        if ($t_set == 'default' && !$this->_canEditDefault) {
            //they should have already been blocked, but just in case
            $admin->userError('Not able to modify the default template set.');
            return false;
        }

        if (!$t_set) {
            //act like creating a new template set
            $new_t_set = $this->_cleanNewTSetName($name);
            if (!$new_t_set) {
                //problem with t-set name
                return false;
            }
            if (!$this->_file->mkdir($new_t_set)) {
                //problem creating directory
                return false;
            }

            if (!$this->_file->mkdir("$new_t_set/main_page/")) {
                //problem creating main_page directory
                return false;
            }

            if (!$this->_file->mkdir("$new_t_set/external/")) {
                //problem creating main_page directory
                return false;
            }
            $admin->userSuccess("New template set ($new_t_set) created successfully.");
            return true;
        }

        if (!$type) {
            //creating a folder for type?
            if (!in_array($name, array('main_page','external','system','module','addon'))) {
                $admin->userError('Invalid folder name at this level (' . $name . '), only allowed
				folders at this level is: main_page, external, system, module, and addon.');
                return false;
            }

            if (!$this->_file->mkdir("$t_set/$name/")) {
                //problem creating dir
                return false;
            }
            $admin->userSuccess("New folder ($t_set/$name/) created successfully.");
            return true;
        }

        //get this far, creating a dir in a sub-directory somewhere
        if ($type != 'main_page' && $type != 'external') {
            $admin->userError('Invalid folder location!  You can only create a folder in main_page or external.');
            return false;
        }

        $fullFilename = geoFile::cleanPath("{$location}$name/");
        if ($fullFilename != "{$location}$name/") {
            //cleaning it changed it, so whatever they specified was "invalid"..
            $admin->userError('Invalid folder name specified (' . $name . '), cannot create.');
            return false;
        }

        //check the full file location

        if (!$this->_checkFile($fullFilename)) {
            //there was a problem with the location!
            return false;
        }

        if ($this->_file->isChild("$t_set/main_page/attachments/", $fullFilename)) {
            $admin->userError('Invalid location (' . $fullFilename . ') - cannot create in attachments sub-directory.');
            return false;
        }

        if (file_exists(GEO_TEMPLATE_DIR . $fullFilename)) {
            $admin->userError("Location ($fullFilename) already exists, cannot create!");
            return false;
        }
        //create a new directory
        if (!$this->_file->mkdir($fullFilename)) {
            //problem creating directory
            return false;
        }

        $admin->userSuccess("New directory ($fullFilename) created successfully.");
        return true;
    }

    public function display_design_new_file()
    {
        $this->init();

        if (geoAjax::isAjax()) {
            $view = geoView::getInstance();
            $admin = geoAdmin::getInstance();
            //this is an ajax call, just display the template
            $errors = '';
            $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
            if (!$this->_file->inJail($location)) {
                //invalid location
                $errors .= 'Invalid location specified.';
            }
            //since displaying template directly, need to get tpl vars set in init()
            $tpl_vars = $view->getAssignedBodyVars();
            if ($location && substr($location, -1) != '/') {
                //be sure it has trailing slashy
                $location .= '/';
            }
            $location = geoFile::cleanPath($location);
            $tpl_vars['location'] = $location;

            $locationInfo = $tpl_vars['locationInfo'] = $this->_fileInfo($location);

            if (!isset($locationInfo['t_set'])) {
                $errors .= 'Cannot create files in the base directory.  Select a template set to place the new file in.';
            } elseif ($locationInfo['t_set'] == 'default' && !$this->_canEditDefault) {
                $errors .= 'Cannot modify default template set.';
            } elseif (!isset($locationInfo['type'])) {
                $errors .= 'Cannot create a new file here!  If you want to make a new template, create one in the main_page sub-directory.';
            } elseif (!in_array($locationInfo['type'], array ('main_page','external'))) {
                $errors .= 'Cannot create folder/file in system/module/addon locations.
				Instead, copy any template(s) you may wish to modify from the default template
				set, to your own template set, then you can modify it there.';
            }

            $tpl_vars['errorMsgs'] = $errors;

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            echo $tpl->fetch('design/newFile.tpl');
            //make sure rest of page doesn't display
            $view->setRendered(true);
        } else {
            //display whole page
            $this->display_design_manage();
        }
    }

    public function update_design_new_file()
    {
        $admin = geoAdmin::getInstance();
        $this->initUpdate();

        $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
        if ($location && substr($location, -1) != '/') {
            //be sure it has trailing slashy
            $location .= '/';
        }
        $locationInfo = $this->_fileInfo($location);
        $t_set = $locationInfo['t_set'];
        $type = $locationInfo['type'];

        $name = (isset($_POST['name']) && $_POST['name']) ? $_POST['name'] : false;

        if ($t_set && !in_array($t_set, $this->_workWith)) {
            $admin->userError('Invalid template set!  You are not currently working with the template set.');
            return false;
        }
        if ($t_set == 'default' && !$this->_canEditDefault) {
            //this shouldn't be possible w/o input altering
            $admin->userError('Not able to modify the default template set.');
            return false;
        }

        if (!$t_set) {
            //at template sets level?  this shouldn't be possible w/o input altering
            $admin->userError('Not able to create a new file at the base directory.');
            return false;
        }

        if (!$type) {
            //at template types level?  this shouldn't be possible w/o input altering
            $admin->userError('Not able to create a file here.');
            return true;
        }

        //get this far, creating a file in a sub-directory somewhere
        if ($type != 'main_page' && $type != 'external') {
            //only in main_page or external, this shouldn't be possible w/o input altering
            $admin->userError('Invalid file location!  You can only create a file in main_page or external.');
            return false;
        }

        $extension = (isset($_POST['fileType']) && $_POST['fileType']) ? $_POST['fileType'] : false;

        if (!$name || !$extension) {
            $admin->userError('Invalid name or extension specified, please try again.');
            return false;
        }
        if (($type == 'main_page' && $extension != '.tpl') || ($type == 'external' && !in_array($extension, array('.js','.css')))) {
            //invalid extension, this shouldn't be possible w/o input altering
            $admin->userError('Invalid file extension type!');
            return false;
        }


        $fullFilename = geoFile::cleanPath("{$location}$name{$extension}");
        if ($fullFilename != "{$location}$name{$extension}") {
            //cleaning it changed it, so whatever they specified was "invalid"..
            $admin->userError('Invalid file name specified (' . $name . $extension . '), cannot create.');
            return false;
        }

        //check the full file location

        if (!$this->_checkFile($fullFilename)) {
            //there was a problem with the location!
            return false;
        }

        if ($this->_file->isChild("$t_set/main_page/attachments/", $fullFilename)) {
            $admin->userError('Invalid location (' . $fullFilename . ') - cannot create in attachments sub-directory.');
            return false;
        }

        if (file_exists(GEO_TEMPLATE_DIR . $fullFilename)) {
            $admin->userError("Location ($fullFilename) already exists, cannot create!");
            return false;
        }

        if (geoPC::is_trial() && !$this->_checkNameTrials($fullFilename)) {
            //invalid filename specified in trial demo
            return false;
        }

        //create a new file
        if ($type == 'main_page' && $extension == '.tpl') {
            //create module to template file
            $localFile = $locationInfo['localFile'];
            if ($localFile && substr($localFile, -1) != '/') {
                //be sure it has trailing slashy
                $localFile .= '/';
            }
            $newFile = geoFile::cleanPath("$t_set/main_page/attachments/modules_to_template/{$localFile}$name.tpl.php");

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign('filename', geoFile::cleanPath("{$localFile}$name.tpl"));
            if (!$this->_file->fwrite($newFile, $tpl->fetch('design/files/modules_to_template.tpl'))) {
                //problem writing attachment file
                return false;
            }
            unset($tpl);
        }

        $tplFile = 'design/files/newTpl.tpl';
        if ($extension == '.css') {
            $tplFile = 'design/files/newCss.tpl';
        } elseif ($extension == '.js') {
            $tplFile = 'design/files/newJs.tpl';
        }
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        if (!$this->_file->fwrite($fullFilename, $tpl->fetch($tplFile))) {
            //problem creating new file
            return false;
        }

        $admin->userSuccess("New file ($fullFilename) created successfully.");

        return true;
    }

    public function display_design_upload_file()
    {
        $this->init();

        if (geoAjax::isAjax()) {
            $view = geoView::getInstance();
            $admin = geoAdmin::getInstance();
            //this is an ajax call, just display the template
            $errors = '';
            $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
            if (!$this->_file->inJail($location)) {
                //invalid location
                $errors .= 'Invalid location specified.';
            }
            //since displaying template directly, need to get tpl vars set in init()
            $tpl_vars = $view->getAssignedBodyVars();
            if ($location && substr($location, -1) != '/') {
                //be sure it has trailing slashy
                $location .= '/';
            }
            $location = geoFile::cleanPath($location);
            $tpl_vars['location'] = $location;

            $locationInfo = $tpl_vars['locationInfo'] = $this->_fileInfo($location);

            if (!isset($locationInfo['t_set'])) {
                $errors .= 'Cannot upload files in the base directory.  Select a template set to upload the file to.';
            } elseif ($locationInfo['t_set'] == 'default' && !$this->_canEditDefault) {
                $errors .= 'Cannot modify default template set.';
            } elseif (!isset($locationInfo['type'])) {
                $errors .= 'Cannot upload a file here!  If you want to upload a template, upload one in the main_page sub-directory.';
            } elseif (!in_array($locationInfo['type'], array ('main_page','external'))) {
                //TODO: file list for user to select from for the name
            }

            $tpl_vars['errorMsgs'] = $errors;

            $tpl = new geoTemplate(geoTemplate::ADMIN);
            $tpl->assign($tpl_vars);
            echo $tpl->fetch('design/uploadFile.tpl');
            //make sure rest of page doesn't display
            $view->setRendered(true);
        } else {
            //display whole page
            $this->display_design_manage();
        }
    }

    public function update_design_upload_file()
    {
        $this->initUpdate();
        $admin = geoAdmin::getInstance();

        $location = (isset($_GET['location']) && $_GET['location']) ? $_GET['location'] : '';
        if (substr($location, -1) != '/') {
            //be sure it has trailing slashy
            $location .= '/';
        }

        $name = (isset($_POST['name']) && $_POST['name']) ? $_POST['name'] : '';

        if (!$name) {
            $admin->userError('File name required to upload file!');
            return false;
        }

        $file = $location . $name;

        //only upload files to specific locations
        if (!$this->_checkFile($file)) {
            //oops!  check file will throw it's own error

            return false;
        }

        $fInfo = $this->_fileInfo($file);
        $t_set = $fInfo['t_set'];
        $localFile = $fInfo['localFile'];
        $type = $fInfo['type'];

        if ($this->_file->isChild("$t_set/main_page/attachments/", $file)) {
            //sanity check...
            $admin->userError('Invalid file location (' . $file . '), cannot edit files in main_page/attachments.');
            return false;
        }

        if (!in_array($type, array('main_page','external'))) {
            //sanity check
            //TODO: Allow for system, module, and addon, if file already exists
            $admin->userError('Invalid file location (' . $file . '), cannot edit file.');
            return false;
        }
        $absFile = $this->_file->absolutize($file);

        $file = geoFile::cleanPath($file);

        if (!isset($_FILES['contents']['tmp_name']) || $_FILES['contents']['error']) {
            $errno = $_FILES['contents']['error'];
            $admin->userError('Error with uploaded file. (error ' . $errno . ')');
            return false;
        }
        $tmpFilename = $_FILES['contents']['tmp_name'];
        if (!file_exists($tmpFilename)) {
            $admin->userError('Error with uploaded file.  Could not access uploaded file temp location (' . $tmpFilename . ')');
            return false;
        }
        $contents = file_get_contents($tmpFilename);
        if (!strlen(trim($contents))) {
            $admin->userError('File uploaded was blank!  If you wish to clear the file, just clear the contents using the editor and save changes.');
            return false;
        }
        $extension = substr($file, strrpos($file, '.'));

        if ($extension == '.php') {
            $admin->userError('Uploading PHP files is not allowed for security reasons, if you need a PHP file you must manually upload one through FTP.');
            return false;
        }

        if (geoPC::is_trial() && !$this->_checkNameTrials($file)) {
            //invalid name for trial demo
            return false;
        }

        if ($type == 'main_page') {
            //scan contents for attachments

            $allowedTplExtensions = array (
                '.tpl','.htm','.html'
            );

            if (!in_array($extension, $allowedTplExtensions)) {
                $admin->userError('Invalid file extension (' . $extension . ') for main_page templates, must be .tpl.  If you want to upload a media file, upload it to the external/ folder in this same template set.');
                return false;
            }

            $attachments = self::scanForAttachments($contents);

            $tpl = new geoTemplate(geoTemplate::ADMIN);

            $tpl->assign($attachments);
            $tpl->assign('filename', $localFile);

            $attachFile = "$t_set/main_page/attachments/modules_to_template/$localFile.php";
            if (!$this->_file->fwrite($attachFile, $tpl->fetch('design/files/modules_to_template.tpl'))) {
                $admin->userError('Unable to update attachments file, not able to save changes.');
                return false;
            }
            $admin->userNotice('Template file attachments updated successfully. (' . $attachFile . ')');
        }

        //now write the actual file's contents
        if (!$this->_file->fwrite($file, $contents)) {
            return false;
        }

        $admin->userSuccess('File uploaded successfully. (' . $file . ')');
        return true;
    }

    public function display_design_edit_file()
    {
        $this->init();
        $view = geoView::getInstance();
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        if ($this->_downloadSuccess) {
            $view->setRendered(true);
            return;
        }

        $tpl_vars = array();
        $file = (isset($_GET['file']) && $_GET['file']) ? $_GET['file'] : false;
        $location = $tpl_vars['location'] = ($file) ? geoFile::cleanPath(dirname($file) . '/') : '';

        if (!$this->_checkFile($file)) {
            //oops!  check file will throw it's own error

            return $this->display_design_manage();
        }
        $fInfo = $this->_fileInfo($file);
        $t_set = $tpl_vars['t_set'] = $fInfo['t_set'];
        $localFile = $fInfo['localFile'];
        $type = $tpl_vars['fileType'] = $fInfo['type'];
        $extension = $this->_getFileType($file);
        if ($this->_file->isChild("$t_set/main_page/attachments/", $file)) {
            //sanity check...
            $admin->userError('Invalid file location (' . $file . '), cannot edit files in main_page/attachments.');
            return $this->display_design_manage();
        }

        if (!$this->_advMode && !in_array($type, array ('main_page','external'))) {
            //if not advanced mode, they can't even VIEW system, module, or addon templates.
            $admin->userError('Invalid file location (' . $file . '), cannot edit files unless they are in main_page or external.');
            return $this->display_design_manage();
        }

        $absFile = $this->_file->absolutize($file);
        if (!$absFile) {
            return $this->display_design_manage();
        }
        if (!file_exists($absFile)) {
            $admin->userError('File does not exist, cannot edit.');
            return $this->display_design_manage();
        }
        if (is_dir($absFile)) {
            $admin->userError('Cannot edit a directory!');
            return $this->display_design_manage();
        }

        $allowedExts = array('tpl','css','js','html','htm','txt');
        if (!in_array($extension, $allowedExts)) {
            $admin->userError('Invalid file type, can only edit tpl, txt, html, css, js, or txt files.');
            return $this->display_design_manage();
        }

        if ($t_set != 'default' && file_exists($this->_file->absolutize("default/$type/$localFile"))) {
            $tpl_vars['restoreDefault'] = true;
        } else {
            $tpl_vars['restoreDefault'] = false;
        }

        $tpl_vars['contents'] = file_get_contents($absFile);
        $tpl_vars['file'] = geoFile::cleanPath($file);
        $tpl_vars['showWysiwyg'] = false;

        $showDefFor = array ('css/custom.css','css/theme1.css','css/theme2.css');
        if (in_array($localFile, $showDefFor) && !$db->get_site_setting('noDefaultCss')) {
            $tpl_vars['default_contents'] = file_get_contents($this->_file->absolutize('default/external/css/default.css'));
            $tpl_vars['css_filename'] = substr($localFile, 4);
        }

        if ($type == 'main_page') {
            //the attachment file

            $attachmentFile = $tpl_vars['modules_to_template_filename'] = "$t_set/main_page/attachments/modules_to_template/$localFile.php";
            if (!file_exists(GEO_TEMPLATE_DIR . $attachmentFile)) {
                $tpl_vars['modules_to_template_filename'] .= " <strong style='color: red;'>Does Not Exist! (Yet)</strong>";
            } else {
                $attachments = $this->getModulesToTemplate($attachmentFile);

                //go through sub-pages and mark which ones are not found
                foreach ($attachments['sub_pages'] as $key => $subPageFile) {
                    $tsets = $this->_whatTSets("main_page/$subPageFile");
                    $attachments['sub_pages'][$key] = array('name' => $subPageFile, 'tsets' => $tsets);
                }

                $tpl_vars['attachments'] = $attachments;

                //get pages/addons attached to:
                $pAttach = $this->_getPagesUsingTemplate($localFile, $t_set, 'templates_to_page');
                $tpl_vars['attachedToPage'] = $pAttach['templates_to_page'];
                $pAttach = $this->_getPagesUsingTemplate($localFile, $t_set, 'modules_to_template');
                $tpl_vars['attachedToTpl'] = $pAttach['modules_to_template'];

                $tpl_vars['pageNames'] = $this->_getPageNames();
            }

            //come up with template list for drop-down insert thingy
            $templates = $this->getAllTemplates();
            $templates = array_merge(array ('Select a Sub-Template' => null), $templates);
            $tpl_vars['templates'] = $templates;

            //module list
            $tpl_vars['modules'] = $this->_getModules();

            //addon tags list
            $addon = geoAddon::getInstance();
            $tpl_vars['addonTags'] = $addon->getTagList();

            //listing details tags list
            $tags = geoFields::getListingTagsMeta();

            $tpl_vars['listingTags'] = $tags;

            $tpl_vars['listingAddonTags'] = $addon->getTagList('listing');

            if (geoAddon::getUtil('signs_flyers')) {
                require_once ADMIN_DIR . 'admin_pages_class.php';
                $pagesClass = Singleton::getInstance('Admin_pages');
                $allTags = $pagesClass->getSignFlyerTags();
                $tags = array ();
                foreach ($allTags as $tags_section) {
                    $tags = array_merge($tags, array_keys($tags_section));
                }
                $tpl_vars['signs_flyersTags'] = $tags;
            }

            //let the editor know that certain files don't represent an entire page (so it doesn't try to auto-add doctype and stuff)
            $partialPages = array('listing_classified.tpl','listing_auction.tpl','header.tpl','footer.tpl','head_common.tpl');
            $fullpage = ($type !== 'main_page' || in_array($localFile, $partialPages)) ? false : true;

            require_once ADMIN_DIR . 'admin_wysiwyg_config.php';
            $wysHeader = wysiwyg_configuration::getHeaderText('templateCode', $fullpage, true, $tpl_vars['restoreDefault']);
            if ($wysHeader) {
                $view->addBottom($wysHeader);
                $tpl_vars['showWysiwyg'] = true;
            }
        }

        $tpl_vars['externalFiles'] = array_keys($this->getAllTemplates('external'));

        //figure out if can edit, or just view
        $canEdit = $canView = true;
        $access = '<span style="color: green;">Read/Write</span> - Can view &amp; edit';
        if ($fInfo['t_set'] == 'default' && !$this->_canEditDefault) {
            $access = '<span style="color: red;">Read Only</span> - Cannot edit default template set';
            $canEdit = false;
        } elseif (!is_writable($absFile)) {
            $access = '<span style="color: red;">Read Only</span> - file permissions do not allow editing this file, need to use FTP to CHMOD the file to 777, to allow read/write access to all.';
            $canEdit = false;
        } elseif (!in_array($extension, array ('tpl','css','js','html','htm','txt'))) {
            //hmm, cannot edit or view?
            //This check is already done above, just here for acedemic reasons.

            $canEdit = false;
            $canView = false;
        } elseif (!$fInfo['type'] || (!in_array($fInfo['type'], array('main_page','external')) && !$this->_canEditSystemTemplates)) {
            $canEdit = false;
            $access = '<span style="color: red;">Read Only</span> (This is a system, module, or addon template)';
        }
        $tpl_vars['canEdit'] = $canEdit;
        $tpl_vars['canView'] = $canView;
        $tpl_vars['access'] = $access;

        //figure out what mode to use, false (off) for default
        $codeMirrorMode = false;
        if (in_array($extension, array('tpl','html','htm'))) {
            $codeMirrorMode = 'text/html';
        } elseif ($extension == 'js') {
            $codeMirrorMode = 'text/javascript';
        } elseif ($extension = 'css') {
            $codeMirrorMode = 'text/css';
        }
        $tpl_vars['codeMirrorMode'] = $codeMirrorMode;
        $tpl_vars['codemirrorTheme'] = $db->get_site_setting('codemirrorTheme');
        $tpl_vars['codemirrorAutotab'] = $db->get_site_setting('codemirrorAutotab');
        $tpl_vars['codemirrorSearch'] = $db->get_site_setting('codemirrorSearch');

        //figure out each level and if it can be clicked
        $locationParts = array ();

        $parts = explode('/', trim($file, ' /'));

        $path = '';
        foreach ($parts as $level => $part) {
            $locationParts[$level]['showLink'] = 0;
            $locationParts[$level]['endPath'] = (int)(count($locationParts) == count($parts));
            $locationParts[$level]['location'] = $locationParts[$level]['title'] = $part;
            $locationParts[$level]['showLink'] = 1;  //new design: always link
            if ($level == 0) {
                //template set level
                $locationParts[$level]['title'] = $part . ' - Template Set';

                $path = $part;
            } else {
                $path .= '/' . $part;
            }
            $locationParts[$level]['fullPath'] = $path;
        }

        $tpl_vars['locationParts'] = $locationParts;

        $tpl_vars['adminMsgs'] = geoAdmin::m();

        if (geoPC::is_trial()) {
            $tpl_vars['trial_msg'] = geoPC::adminTrialMessage('tpl_security');
        }

        $view->setBodyTpl('design/fileEdit.tpl')
            ->setBodyVar($tpl_vars)
            ->addJScript(array('../js/codemirror/lib/codemirror.js',
                '../js/codemirror/mode/xml/xml.js',
                '../js/codemirror/mode/javascript/javascript.js',
                '../js/codemirror/mode/css/css.js',
                '../js/codemirror/mode/htmlmixed/htmlmixed.js'
            ))
            ->addCssFile(array(
                '../js/codemirror/lib/codemirror.css',
                //'../js/codemirror/css/docs.css'
            ));

        if ($tpl_vars['codemirrorTheme']) {
            //set theme
            $view->addCssFile('../js/codemirror/theme/' . $tpl_vars['codemirrorTheme'] . '.css');
        }
        if ($tpl_vars['codemirrorSearch']) {
            //load up the search/replace functionality

            $view->addJScript(array(
                    '../js/codemirror/addon/search/search.js',
                    '../js/codemirror/addon/search/searchcursor.js',
                    '../js/codemirror/addon/dialog/dialog.js',
                ))
                ->addCssFile('../js/codemirror/addon/dialog/dialog.css');
        }
    }

    public function update_design_edit_file()
    {
        $this->initUpdate();

        if (isset($_POST['download']) && $_POST['download'] && (!isset($_POST['saveChanges']) || !$_POST['saveChanges'])) {
            //downloading file w/o saving changes...
            $file = (isset($_POST['file']) && $_POST['file']) ? $_POST['file'] : false;
            if ($this->_file->download($file)) {
                $this->_downloadSuccess = true;
                return true;
            } else {
                return false;
            }
        }

        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $file = (isset($_POST['file']) && $_POST['file']) ? $_POST['file'] : false;

        if (!$this->_checkFile($file)) {
            //oops!  check file will throw it's own error

            return false;
        }

        $fInfo = $this->_fileInfo($file);
        $t_set = $fInfo['t_set'];
        $localFile = $fInfo['localFile'];
        $type = $fInfo['type'];

        if ($this->_file->isChild("$t_set/main_page/attachments/", $file)) {
            //sanity check...
            $admin->userError('Invalid file location (' . $file . '), cannot edit files in main_page/attachments.');
            return false;
        }

        if (!in_array($type, array('main_page','external')) && !$this->_canEditSystemTemplates) {
            //sanity check
            $admin->userError('Invalid file location (' . $file . '), cannot edit file.');
            return false;
        }
        $absFile = $this->_file->absolutize($file);
        if (!file_exists($absFile)) {
            $admin->userError('File does not exist, cannot edit.');
            return $this->display_design_manage();
        }
        $file = geoFile::cleanPath($file);

        $uploadedFile = (isset($_POST['upload']) && $_POST['upload']);
        $restoreFile = (isset($_POST['restore']) && $_POST['restore']);

        if ($uploadedFile) {
            if (!isset($_FILES['contents']['tmp_name']) || $_FILES['contents']['error']) {
                $errno = $_FILES['contents']['error'];
                $admin->userError('Error with uploaded file. (error ' . $errno . ')');
                return false;
            }
            $tmpFilename = $_FILES['contents']['tmp_name'];
            if (!file_exists($tmpFilename)) {
                $admin->userError('Error with uploaded file.  Could not access uploaded file temp location (' . $tmpFilename . ')');
                return false;
            }
            $contents = file_get_contents($tmpFilename);
            if (!strlen(trim($contents))) {
                $admin->userError('File uploaded was blank!  If you wish to clear the file, just clear the contents using the editor and save changes.');
                return false;
            }
            $admin->userNotice('File contents uploaded.');
        } elseif ($restoreFile) {
            $fileLocation = "default/{$fInfo['type']}/{$fInfo['localFile']}";

            if (file_exists($this->_file->absolutize($fileLocation))) {
                //get contents of file from default template set
                $contents = file_get_contents($this->_file->absolutize($fileLocation));
            } else {
                $admin->userError('Could not find the default for this file!');
                //get contents from current file so it doesn't blank out the template on failure
                $contents = file_get_contents($absFile);
            }
        } else {
            $pre = (isset($_POST['contentsPre']) && $_POST['contentsPre']) ? $_POST['contentsPre'] : '';
            $post = (isset($_POST['contentsPost']) && $_POST['contentsPost']) ? $_POST['contentsPost'] : '';

            $contents = $pre . ((isset($_POST['contents']) && $_POST['contents']) ? $_POST['contents'] : '') . $post;
        }

        if ($type == 'main_page') {
            //scan contents for attachments
            $attachments = self::scanForAttachments($contents);

            $tpl = new geoTemplate(geoTemplate::ADMIN);

            $tpl->assign($attachments);
            $tpl->assign('filename', $localFile);

            $attachFile = "$t_set/main_page/attachments/modules_to_template/$localFile.php";
            if (!$this->_file->fwrite($attachFile, $tpl->fetch('design/files/modules_to_template.tpl'))) {
                $admin->userError('Unable to update attachments file, not able to save changes.');
                return false;
            }
            $admin->userNotice('Template file attachments updated successfully. (' . $attachFile . ')');
        } elseif ($type === 'external') {
            //see if it is a CSS file that is typically combined
            $combined = array ('css/custom.css','css/theme1.css','css/theme2.css');
            if (in_array($localFile, $combined) && $db->get_site_setting('minifyEnabled')) {
                //it is one of them, and site is set to combine the contents... go ahead and clear
                if ($this->clearCombined()) {
                    $admin->userNotice('Combined CSS output cleared successfully.');
                } else {
                    //throw an error but don't stop it from continuing with saving the file
                    $admin->userError('There was a problem attempting to clear the combined CSS.');
                }
            }
        }

        //now write the actual file's contents
        if (!$this->_file->fwrite($file, $contents)) {
            return false;
        }
        if (isset($_POST['download']) && $_POST['download']) {
            //downloading file and saving changes...

            if ($this->_file->download($file)) {
                $this->_downloadSuccess = true;
                return true;
            }
        }
        $admin->userSuccess('Template file contents updated successfully. (' . $file . ')');
        return true;
    }

    private $_tSets;

    public function getAllTemplateSets($evenInvalid = false)
    {
        if (!file_exists(GEO_TEMPLATE_DIR) || !is_dir(GEO_TEMPLATE_DIR)) {
            trigger_error('ERROR TEMPLATE STATS: Folder for template sets does not exist or is not a directory: "' . GEO_TEMPLATE_DIR . '" (most likely not set correctly in config.php)');
            return array();
        }
        if (!isset($this->_tSets)) {
            $sets = geoTemplate::getTemplateSets();
            foreach ($sets as $key => $set) {
                if ($set == 'default' && !$this->_advMode) {
                    unset($sets[$key]);
                } elseif (!is_dir($this->_file->absolutize($set . '/'))) {
                    //auto remove it if could not find it or it is not dir!
                    unset($sets[$key]);
                }
            }
            $skip = array('.','..','t_sets.php','_temp');//what to skip
            if (!$this->_advMode) {
                $skip [] = 'default';
                unset($sets['default']);
            }
            $allSets = array_diff(scandir(GEO_TEMPLATE_DIR), $skip);
            foreach ($allSets as $entry) {
                if (!in_array($entry, $sets) && is_dir(GEO_TEMPLATE_DIR . $entry) && !in_array(substr($entry, 0, 1), array ('_','.'))) {
                    //note: also skiping anything starting with _
                    //Which means, already skipping any frontpage extension folders, so no need to account for them...
                    $sets[] = $entry;
                }
            }
            //make sure the sets list starts off with index 0
            $this->_tSets = array_values($sets);
        }
        if (!$evenInvalid) {
            //skip over invalid entries
            return array_diff($this->_tSets, $this->_invalidTSetNames);
        }

        return $this->_tSets;
    }

    private $_allTemplates;

    public function getAllTemplates($tType = 'main_page', $startingTSet = '', $startingDir = '')
    {
        if (!file_exists(GEO_TEMPLATE_DIR) || !is_dir(GEO_TEMPLATE_DIR)) {
            trigger_error('ERROR TEMPLATE STATS: Folder for template sets does not exist or is not a directory: "' . GEO_TEMPLATE_DIR . '" (most likely not set correctly in config.php)');
            return array();
        }
        if (!$startingTSet && !$startingDir && isset($this->_allTemplates[$tType])) {
            //all templates already retrieved
            return $this->_allTemplates[$tType];
        }

        if (!$startingTSet && !$startingDir) {
            $t_sets = $this->_workWith;
            $this->_allTemplates[$tType] = array();
        } else {
            $t_sets = array ($startingTSet);
        }

        //get all templates found in all template sets
        $baseDir = ($startingDir) ? $startingDir . '/' : '';

        foreach ($t_sets as $t_set) {
            $entries = $this->_file->scandir("$t_set/$tType/$baseDir", false, false);
            $dirs = $files = array();
            foreach ($entries as $entry) {
                if (!geoString::isFilePath("$t_set/$tType/$baseDir{$entry}")) {
                    //this file has something in it that is not good, like single quote
                    continue;
                }
                if (is_dir(GEO_TEMPLATE_DIR . "$t_set/$tType/$baseDir{$entry}")) {
                    if ($tType != 'main_page' || ($tType == 'main_page' && $baseDir . $entry != 'attachments')) {
                        //do not parse attachments dir
                        $dirs[] = $entry;
                    }
                } else {
                    $files[] = $entry;
                }
            }
            //sort so they are alphabetical
            sort($dirs);
            sort($files);
            //go through files first
            foreach ($files as $f) {
                $this->_allTemplates[$tType][$baseDir . $f][$t_set] = $t_set;
            }
            //now add dirs
            foreach ($dirs as $d) {
                //it is not in the attachments directory
                $this->getAllTemplates($tType, $t_set, $baseDir . $d);
            }
        }
        return $this->_allTemplates[$tType];
    }

    private $_allTemplatesTree;

    private function _whatTSets($localFile)
    {
        $this->init();

        $tsets = array();
        foreach ($this->_workWith as $t_set) {
            if (file_exists($this->_file->absolutize("$t_set/$localFile"))) {
                $tsets[$t_set] = $t_set;
            }
        }
        return $tsets;
    }
    private $_dirsCreated = array();
    private $_umask = false;

    private function _getTemplateTypes()
    {
        $types = (isset($_POST['types'])) ? $_POST['types'] : array();

        $copyTypes = array();
        foreach ($types as $type => $use) {
            if ($use && in_array($type, $this->_validTypes)) {
                $copyTypes[] = $type;
            }
        }
        //add copy of text.csv
        $copyTypes[] = 'text.csv';
        return $copyTypes;
    }

    private function _deleteFiles($file, $dryRun = true, $startWith = array())
    {
        $admin = geoAdmin::getInstance();
        //figure out what files will be removed as a result of this file being removed...
        $errorReturn = ($dryRun) ? $startWith : false;
        if (!$file) {
            //not valid
            return $errorReturn;
        }
        $absFile = GEO_TEMPLATE_DIR . $file;
        if (!file_exists($absFile)) {
            //does not exist
            $admin->userError('File "' . $absFile . '" could not be found, so cannot delete file.');
            return $errorReturn;
        }
        //figure out the TSET
        $fInfo = $this->_fileInfo($file);
        $t_set = $fInfo['t_set'];
        $localFile = $fInfo['localFile'];
        $type = $fInfo['type'];

        if (!$t_set || !in_array($t_set, $this->_workWith)) {
            //tset outside of what we are working on
            $admin->userError('File being removed from (' . $t_set . ') which is not within the current template sets being worked with.');
            return $errorReturn;
        }
        if ($t_set == 'default' && $type == 'external' && !$this->_canEditDefault) {
            $admin->userError('Cannot delete external files from default template set!');
            return $errorReturn;
        }

        //make sure file is within allowed upload locations
        if (strpos(dirname($absFile, GEO_TEMPLATE_DIR . $t_set . '/abc') === false)) {
            //trying to escape the working set dir?
            //this should only happen upon a bug, or a hack attempt...
            $admin->userError('File outside of the working set!');
            return $errorReturn;
        }

        if (!$localFile || !$type) {
            //type or localfile not known, can't do much
            $admin->userError('Could not figure out what type of template or what the "local" template name was, aborting deletion.');
            return $errorReturn;
        }
        if (!in_array($type, $this->_validTypes)) {
            //not a valid type!
            $admin->userError('This file does not reside in any of the built-in template type locations, cannot delete this file.');
            return $errorReturn;
        }
        $fileList = $startWith;
        if (is_dir($absFile)) {
            //it is a directory, recursively process the contents
            $contents = $this->_file->scandir($absFile, false, false);
            foreach ($contents as $entry) {
                if (!$dryRun) {
                    //just run it strait up
                    if (!$this->_deleteFiles($file . '/' . $entry, $dryRun)) {
                        //some error, do not throw another error, one should have
                        //been displayed on prior error.
                        return false;
                    }
                } else {
                    //recursive call ourselves for dir contents
                    $fileList = $this->_deleteFiles($file . '/' . $entry, $dryRun, $fileList);
                }
            }
            //add the directory to be removed at the end (dirs end in /)
            $fileList[$t_set][$type][$localFile . '/'] = $localFile . '/';
            if (!$dryRun) {
                //remove the folder
                if (!is_writable($absFile)) {
                    $admin->userError('Could not remove the existing directory (' . $absFile . '), check file permissions (CHMOD 777) and try again.');
                    return false;
                }
                $result = rmdir($absFile);
                if (!$result) {
                    $admin->userError('Error while attempting to delete the directory (' . $absFile . ').');
                    return false;
                }
                //removal seems to be a success!
                return true;
            }
        } else {
            if ($type == 'main_page') {
                //also remove "modules attached"
                $attachFile = "$t_set/$type/attachments/modules_to_template/{$localFile}.php";
                if (file_exists(GEO_TEMPLATE_DIR . $attachFile)) {
                    //check to see if this page is attached as a sub-page anywhere

                    $fileList[$t_set][$type]['attachments']["modules_to_template/{$localFile}.php"] = "modules_to_template/{$localFile}.php";

                    if (!$dryRun) {
                        //remove the attached file
                        if (!is_writable(GEO_TEMPLATE_DIR . $attachFile)) {
                            $admin->userError('Could not remove the attachment file (' . GEO_TEMPLATE_DIR . $attachFile . '), check file permissions (CHMOD 777) and try again.');
                            return false;
                        }
                        if (!unlink(GEO_TEMPLATE_DIR . $attachFile)) {
                            //problem removing attachment file
                            $admin->userError('Error while attempting to delete the attachment file (' . GEO_TEMPLATE_DIR . $attachFile . ').');
                            return false;
                        }
                    }
                }
            }
            $fileList[$t_set][$type][$localFile] = $localFile;

            if (!$dryRun) {
                //remove the file
                if (!is_writable($absFile)) {
                    $admin->userError('Could not remove the file (' . $absFile . '), check file permissions (CHMOD 777) and try again.');
                    return false;
                }
                $result = unlink($absFile);
                if (!$result) {
                    $admin->userError('Error while attempting to delete the file (' . $absFile . ').');
                    return false;
                }
                return true;
            }
        }
        //die ("file: $file<br />localfile: $localFile<br />type: $type<br />absfile: $absFile<br />tset: $t_set");

        return $fileList;
    }

    private function _getPagesUsingTemplates($templateList)
    {
        if (!is_array($templateList)) {
            //just to check...
            return array();
        }
        $attachedTo = array();
        foreach ($templateList as $t_set => $types) {
            if (!$t_set || !in_array($t_set, $this->_workWith)) {
                //can't do anything, invalid template set
                continue;
            }
            foreach ($types as $type => $files) {
                if (!$type || !in_array($type, $this->_validTypes)) {
                    //can't work with this type
                    continue;
                }
                foreach ($files as $f => $file) {
                    if ($f == 'attachments') {
                        //this is an attachments file, it won't have any sub-attachments
                        continue;
                    }
                    //normal template file, check template to page attachments
                    $attachedTo = $this->_getPagesUsingTemplate($file, $t_set, 'templates_to_page', $attachedTo);
                }
            }
        }

        return $attachedTo;
    }
    //so we only have to get each file list once...
    private $_attachFileList;
    private function _getPagesUsingTemplate($templateFile, $t_set, $type = 'templates_to_page', $attachedTo = array())
    {
        if (!$t_set || !in_array($t_set, $this->_workWith)) {
            //can't do anything, invalid template set
            return $attachedTo;
        }
        if (!in_array($type, array('templates_to_page','modules_to_template'))) {
            //sane check: invalid "type" specified..
            return $attachedTo;
        }
        //go through all the attachment files...
        if (!isset($this->_attachFileList["$t_set/main_page/attachments/$type/"])) {
            $list = $this->_file->scandir(GEO_TEMPLATE_DIR . "$t_set/main_page/attachments/$type/");
            //Filter the list, we only care about PHP files in this folder
            foreach ($list as $k => $file) {
                if (substr($file, -4) !== '.php') {
                    continue;
                }
                $this->_attachFileList["$t_set/main_page/attachments/$type/"][$k] = $file;
            }
            //echo "filelist: <pre>".print_r($this->_attachFileList["$t_set/main_page/attachments/$type/"],1)."</pre><br /><br />";
        }
        $fileList = $this->_attachFileList["$t_set/main_page/attachments/$type/"];

        foreach ($fileList as $file) {
            $absFile = "$t_set/main_page/attachments/$type/$file";
            if (!file_exists($this->_file->absolutize($absFile))) {
                //umm file doesn't exist?
                continue;
            }
            if ($type == 'modules_to_template') {
                $attachments = $this->getModulesToTemplate($absFile);
            } else {
                $attachments = include $this->_file->absolutize($absFile);
            }
            if (!$attachments) {
                continue;
            }
            if (geoArrayTools::inArray($templateFile, $attachments)) {
                $file = preg_replace('/\.php$/', '', $file);
                $attachedTo[$type][$file] = $file;
            }
            unset($return, $attachments);
        }

        return $attachedTo;
    }

    public function getModulesToTemplate($attachmentFile)
    {
        if (!$this->_file->inJail($attachmentFile)) {
            geoAdmin::m('Attachment file not within working template directory!');
            return false;
        }
        $attachmentFile = $this->_file->absolutize($attachmentFile);
        $skip_sub_pages = true;
        $attachments = include $attachmentFile;
        if (isset($attachments['already_attached']) && count($attachments['already_attached']) > 1) {
            //this attachment file was created PRE-4.2
            //some tricky tricks to not include sub-page files when re-creating the file ;)
            $already_attached = $attachments['already_attached'];
            unset($return, $attachments);
            $return = array('already_attached' => $already_attached);
            $attachments = include $attachmentFile;
        }
        return $attachments;
    }

    private function _getPageInfo($pageId)
    {
        $pageInfos = $this->_getPageInfos();
        if (isset($pageInfos[$pageId])) {
            return $pageInfos[$pageId];
        }
        return false;
    }

    private function _getPageInfos()
    {
        if (!isset($this->_pageInfos)) {
            $this->_getPageNames(true);
        }
        return $this->_pageInfos;
    }

    private $_allPages = array ();

    public function getPagesData($specialTypes = array ('normal','special','category','affiliate','extra'), $filterAppliesTo = true)
    {
        if (!is_array($specialTypes)) {
            //allow just a single string to be passed in
            $specialTypes = ($specialTypes) ? array($specialTypes) : array();
        }
        $normalPages = (in_array('normal', $specialTypes));
        $specialPages = (in_array('special', $specialTypes));
        $categoryPages = (in_array('category', $specialTypes));
        $affiliatePages = (in_array('affiliate', $specialTypes));
        $extraPages = (in_array('extra', $specialTypes));

        $pages = array ();
        //get the HTML and PHP id's to skip them...
        require_once ADMIN_DIR . 'admin_pages_class.php';

        $tempClass = Singleton::getInstance('Admin_pages');

        $skipList = array_merge(
            $tempClass->logged_in_out_HTML_mods,
            $tempClass->PHP_mods,
            $tempClass->filter_dropdown_mods,
            $tempClass->email_pages
        );
        //add the print friendly page, it only has 69_classifieds and 69_auctions
        //the main 69 page is not actually used for template attachment.
        $skipList[] = 69;

        //page 10214 is just used for holding text, and has no template attachment of its own
        $skipList[] = 10214;
        /*
         * Category normal pages:
         * 1 - listing details overall template
         * 3 - browse category
         * 44 - Search page/search results
         * 84 - Full sized image display page
         * (plus ones that are "special" pages of course)
         */
        $categoryNormalPages = array (1,3,44,84);

        /**
         * Group affiliate normal pages:
         * 1 - listing details overall template
         * 84 - full sized image display page
         * (plus ones that are "special" pages of course)
         */
        $affiliateNormalPages = array (1, 84);

        /**
         * Extra pages:
         * 135 (extra page 1) - 154 (extra page 20)
         */
        $extraNormalPages = range(135, 154);

        if ($normalPages) {
            //get all normal pages, we will weed through them in a sec to remove
            //any PHP, HTML, e-mail, etc. pages.
            $db = DataAccess::getInstance();
            $sql = "SELECT `page_id`, `name`, `module_replace_tag`, `applies_to`, `admin_label` FROM " . geoTables::pages_table . " ORDER BY `page_id`";

            $pages = array_merge($pages, $db->GetAll($sql));
        } elseif ($categoryPages || $affiliatePages || $extraPages) {
            //get the normal pages that are only category-specific
            $list = array();
            if ($categoryPages) {
                $list += $categoryNormalPages;
            }
            if ($affiliatePages) {
                $list += $affiliateNormalPages;
            }
            if ($extraPages) {
                $list += $extraNormalPages;
            }
            $db = DataAccess::getInstance();
            $sql = "SELECT `page_id`, `name`, `module_replace_tag`, `applies_to` FROM " . geoTables::pages_table . " WHERE `page_id` IN (" . implode(', ', $list) . ") ORDER BY `page_id`";
            $pages = array_merge($pages, $db->GetAll($sql));
        }
        $special = array();
        if ($specialPages || $categoryPages || $affiliatePages) {
            //add all the "special" pages or special category pages


            $special[1][] = array (
                'page_id' => '1_classified',
                'name' => 'Listing Details Page, <strong>Classified</strong> {main_body} sub-template',
                'applies_to' => 1,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
            $special[1][] = array (
                'page_id' => '1_auction',
                'name' => 'Listing Details Page, <strong>Auction</strong> {main_body} sub-template',
                'applies_to' => 2,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
            $special[3][] = array (
                    'page_id' => '3_grid',
                    'name' => 'Browse Categories Grid Sub-Template',
                    'applies_to' => 0,
                    'specialPage' => 1,
                    'categoryPage' => 1,
                    'affiliatePage' => 1,
            );
            $special[3][] = array (
                'page_id' => '3_list',
                'name' => 'Browse Categories List Sub-Template',
                'applies_to' => 0,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
            $special[3][] = array (
                'page_id' => '3_gallery',
                'name' => 'Browse Categories Gallery Sub-Template',
                'applies_to' => 0,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
            $special[3][] = array (
                'page_id' => '3_featured_gallery',
                'name' => 'Browse Categories Featured Listing Gallery Sub-Template',
                'applies_to' => 0,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
            $special[3][] = array (
                'page_id' => '3_secondary',
                'name' => 'Browse Categories 2nd page and higher',
                'applies_to' => 0,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
            if ($specialPages) {
                //this one is special page only, no cat specific version
                $special[43][] = array (
                    'page_id' => '43_home',
                    'name' => '"Old" account home page {main_body} sub-template',
                    'applies_to' => 0,
                    'specialPage' => 1,
                    'categoryPage' => 0,
                    'affiliatePage' => 0,
                );
            }
            $special[69][] = array (
                'page_id' => '69_classified',
                'name' => 'Classified Details print-friendly full template',
                'applies_to' => 1,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );

            $special[69][] = array (
                'page_id' => '69_auction',
                'name' => 'Auction Details print-friendly full template',
                'applies_to' => 2,
                'specialPage' => 1,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );


            $special[84][] = array (
                'page_id' => '84_detail',
                'name' => 'Full-sized image display {main_body} sub-template',
                'applies_to' => 0,
                'categoryPage' => 1,
                'affiliatePage' => 1,
            );
        }
        $cleanPages = array();
        foreach ($pages as $row) {
            if ($filterAppliesTo) {
                if ($row['applies_to'] == 2 && !geoMaster::is('auctions')) {
                    continue;
                }
                if ($row['applies_to'] == 1 && !geoMaster::is('classifieds')) {
                    continue;
                }
            }
            if (in_array($row['page_id'], $skipList)) {
                //a PHP or HTML module, skip it
                continue;
            }
            if (!$tempClass->isPageEditable($row['page_id'])) {
                //not an editable page for this product
                continue;
            }
            if (in_array($row['page_id'], $categoryNormalPages)) {
                //category page
                $row['categoryPage'] = 1;
            }
            if (in_array($row['page_id'], $affiliateNormalPages)) {
                $row['affiliatePage'] = 1;
            }
            if (in_array($row['page_id'], $extraNormalPages)) {
                $row['extraPage'] = 1;
            }

            $cleanPages[$row['page_id']] = $row;
            if (isset($special[$row['page_id']])) {
                $pageId = $row['page_id'];
                $specials = $special[$pageId];

                foreach ($specials as $row) {
                    if ($filterAppliesTo) {
                        if ($row['applies_to'] == 2 && !geoMaster::is('auctions')) {
                            continue;
                        }
                        if ($row['applies_to'] == 1 && !geoMaster::is('classifieds')) {
                            continue;
                        }
                    }
                    $cleanPages[$row['page_id']] = $row;
                }
                unset($special[$pageId]);
            }
        }
        if (count($special)) {
            //there are still specials not "below" some other field
            foreach ($special as $pageId => $pages) {
                foreach ($pages as $row) {
                    if ($filterAppliesTo) {
                        if ($row['applies_to'] == 2 && !geoMaster::is('auctions')) {
                            continue;
                        }
                        if ($row['applies_to'] == 1 && !geoMaster::is('classifieds')) {
                            continue;
                        }
                    }
                    $cleanPages[$row['page_id']] = $row;
                }
            }
        }
        //I'd hate to leave a old class lying around in such a new fancy place like this class...
        //I wouldn't even use it except that it is good practice to only define
        //such a large array of junk in one "location"...
        unset($tempClass);
        return $cleanPages;
    }

    private $_pageNames, $_moduleTags, $_pageInfos;

    private function _getPageNames($parseInfos = false)
    {
        if (!isset($this->_pageNames) || ($parseInfos && !isset($this->_pageInfos))) {
            //generate the page names
            $pages = $this->getPagesData();
            $names = $modules = $infos = array();
            foreach ($pages as $row) {
                $names[$row['page_id']] = $row['name'];
                if ($row['module_replace_tag']) {
                    $tag = $row['module_replace_tag'];
                    $modules[$row['page_id']] = $tag;
                } elseif ($parseInfos) {
                    //only build page infos if we need it
                    $info = $row;
                    foreach ($this->_workWith as $t_set) {
                        $attachFile = "$t_set/main_page/attachments/templates_to_page/{$row['page_id']}.php";
                        if (file_exists($this->_file->absolutize($attachFile))) {
                            $info['t_set'] = $t_set;
                            $info['templates'] = include $this->_file->absolutize($attachFile);
                            unset($return);
                            //only do the first tset found
                            break;
                        }
                    }
                    if (!isset($info['t_set'])) {
                        //get from defaults
                        $attachedFile = "default/main_page/attachments/templates_to_page/{$row['page_id']}.php";
                        if (file_exists($this->_file->absolutize($attachedFile))) {
                            $info['defaults'] = include $this->_file->absolutize($attachedFile);

                            unset($return);
                        }
                    }
                    $infos[$row['page_id']] = $info;
                }
            }

            $this->_pageNames = $names;
            $this->_moduleTags = $modules;
            if ($parseInfos) {
                $this->_pageInfos = $infos;
            }
        }
        return $this->_pageNames;
    }

    private function _getPageName($page)
    {
        if (!isset($this->_pageNames)) {
            $this->_getPageNames();
        }
        return (isset($this->_pageNames[$page])) ? $this->_pageNames[$page] : '';
    }

    private function _getModules()
    {
        if (!isset($this->_pageNames)) {
            $this->_getPageNames();
        }
        return $this->_moduleTags;
    }

    private function _getModuleId($tag)
    {
        if (!isset($this->_moduleTags)) {
            $this->_getPageNames();
        }
        return array_search($tag, $this->_moduleTags);
    }

    private function _fileInfo($file)
    {
        $file = geoFile::cleanPath($file);

        $return = array();

        if (strpos($file, '/') !== false) {
            //able to figure out the t_set
            $return['t_set'] = substr($file, 0, strpos($file, '/'));

            $file = substr($file, (strpos($file, '/') + 1));
            if (strpos($file, '/') !== false) {
                //able to figure out the "type"
                $return['type'] = substr($file, 0, strpos($file, '/'));

                $file = substr($file, (strpos($file, '/') + 1));
            }
        }
        $return['localFile'] = $file;
        return $return;
    }

    private function _checkFile($file)
    {
        $admin = geoAdmin::getInstance();
        $fInfo = $this->_fileInfo($file);

        $t_set = $fInfo['t_set'];
        $localFile = $fInfo['localFile'];
        $type = $fInfo['type'];
        $absFile = $this->_file->absolutize("$t_set/$type/$localFile");

        //first, check for forbidden chars
        if (!geoString::isFilePath($absFile)) {
            $admin->userError("Filename or folder name specified ($type/$localFile) contains invalid characters for template set files.  The following characters cannot be used, please remove all such characters from the name and try again:<br />
			&lt; (less than), &gt; (greater than), : (colon), &quot; (double quote), ' (apostrophe or single quote), | (vertical bar or pipe), ? (question mark), * (asterisk)");
            return false;
        }

        if (!$file) {
            $admin->userError('Invalid file specified.');
            return false;
        }
        if (!$t_set || !in_array($t_set, $this->_workWith)) {
            //tset outside of what we are working on
            $admin->userError('File is from (' . $t_set . ') which is not within the current template sets being edited.');
            return false;
        }
        if (!$this->_file->isChild($t_set . '/' . $type . '/', $absFile)) {
            //trying to escape the working set dir?
            //this should only happen upon a bug, or a hack attempt...
            $admin->userError('File (' . $absFile . ') outside of the working set!');
            return false;
        }
        if (!$localFile || !$type) {
            //type or localfile not known, can't do much
            $admin->userError('Could not figure out what type of template or what the "local" template name was for file (' . $absFile . '), cannot proceed!');
            return false;
        }
        if (!in_array($type, $this->_validTypes)) {
            //not a valid type!
            $admin->userError('This file does not reside in any of the built-in template type locations, cannot copy this file.');
            return false;
        }
        return true;
    }

    public static function scanForAttachments($contents)
    {
        $attachments = array();
        //Scan for attached modules
        preg_match_all('/\{module[^}]*? tag=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?\}/', $contents, $matches);
        //die ("matches: <pre>".print_r($matches,1));
        $designObj = Singleton::getInstance(__class__);
        foreach ($matches[1] as $module) {
            $modId = $designObj->_getModuleId($module);
            if ($modId) {
                $attachments['modules'][$modId] = $module;
            }
        }
        unset($matches);
        //Scan for attached addons
        //{addon author="auth_tag" addon="addon_name" tag="tag_name"}
        preg_match_all('/\{addon'
            . '[^}]*? (author|addon|tag)=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?'
            . '[^}]*? (author|addon|tag)=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?'
            . '[^}]*? (author|addon|tag)=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?'
            . '\}/', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tagInfo = array();
            for ($i = 1; $i < count($match); $i += 2) {
                $label = $match[$i];
                $value = $match[$i + 1];
                if (in_array($label, array('author','addon','tag')) && $value) {
                    $tagInfo[$label] = $value;
                }
            }
            if ($tagInfo['author'] && $tagInfo['addon'] && $tagInfo['tag']) {
                $attachments['addons'][$tagInfo['author']][$tagInfo['addon']][$tagInfo['tag']] = $tagInfo['tag'];
            }
        }
        unset($matches);

        //Scan for attached sub-template attachments
        //{include file="template_file.tpl" g_type="g_type" g_resource="junk"}
        //g_type and g_resource are optional (and not typical)
        preg_match_all('/\{include'
            . '[^}]*? (file|g_type|g_resource)=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?'
            . '([^}]*? (file|g_type|g_resource)=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?)?'
            . '([^}]*? (file|g_type|g_resource)=[\'"]{1}([^\'"]+)[\'"]{1}[^}]*?)?'
            . '\}/', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tagInfo = array('g_type' => 'main_page', 'g_resource' => '');
            for ($i = 1; $i < count($match); $i += 2) {
                $label = $match[$i];
                $value = $match[$i + 1];
                if (in_array($label, array('file','g_type','g_resource')) && $value) {
                    $tagInfo[$label] = $value;
                }
            }
            if ($tagInfo['g_type'] == 'main_page' && $tagInfo['file']) {
                $baseDir = ($tagInfo['g_resource']) ? $tagInfo['g_resource'] . '/' : '';
                $tplFile = geoFile::cleanPath($baseDir . $tagInfo['file']);
                if (!$tplFile) {
                    //oops!  file not within our system, don't put it as an attachment
                    continue;
                }
                $attachments['sub_pages'][$tplFile] = $tplFile;
            }
        }
        return $attachments;
    }

    private function _addSingleTset($t_set = null, $language_id = 0)
    {
        $admin = geoAdmin::getInstance();

        $set_file = GEO_TEMPLATE_DIR . 't_sets.php';
        $exists = file_exists($set_file);
        if ($exists && !is_writable($set_file)) {
            $admin->userError('Error: Do not have permission (chmod 777) to edit the file (' . $set_file . '), cannot add the template set.');
            return false;
        }

        if (!$exists && !is_writable(GEO_TEMPLATE_DIR)) {
            $admin->userError('Error: Do not have permission (chmod 777) to edit the templates directory (' . GEO_TEMPLATE_DIR . '), cannot add the template set.');
            return false;
        }
        if ($t_set === null) {
            $t_set = strtolower(trim($_POST['t_set']));
        }
        //make sure it's safe for a file name
        $t_set = geoTemplate::cleanTemplateSetName($t_set);

        $block = array('.','..','t_sets.php','default');
        if (in_array($t_set, $block)) {
            //shouldn't happen normally, this would only happen if they are trying to do input manipulation.
            $admin->userError('Error: Specified template set not allowed.');
            return false;
        }

        if (!file_exists(GEO_TEMPLATE_DIR . $t_set)) {
            $admin->userError('Error: Could not find the specified template set.  Make sure you are using a valid template set directory name.');
            return false;
        }

        $full_file = $set_file;

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $t_sets = geoTemplate::getTemplateSets();
        unset($t_sets['default']);
        if (in_array($t_set, $t_sets)) {
            //template set already added!  nothing to do...
            return true;
        }
        $t_sets[] = array (
            'name' => $t_set,
            'language_id' => (int)$language_id,
        );

        $tpl->assign('t_sets', $t_sets);

        $tpl->assign('custom_section', $this->get_custom_tset_section());

        $tpl_code = $tpl->fetch('design/files/t_sets.php.tpl');

        //write the file
        if (!$this->_file->fwrite($full_file, $tpl_code)) {
            return false;
        }

        geoTemplate::loadTemplateSets(true);
    }

    private function _cleanNewTSetName($t_set)
    {
        $admin = geoAdmin::getInstance();
        $new_t_set = $orig = strtolower(trim($t_set));
        //make sure it's safe for a file name
        $new_t_set = geoTemplate::cleanTemplateSetName($new_t_set);

        if (strlen($new_t_set) == 0) {
            $admin->userError('Invalid template set name.  Make sure you use all lowercase alpha-numeric characters (underscores "_" and dashes "-" allowed as well)');
            return false;
        }
        $blocked = array ('t_set','default');
        if (in_array($new_t_set, $blocked) || in_array($new_t_set, $this->_invalidTSetNames)) {
            $admin->userError('New template set name specified (' . $new_t_set . ') is not allowed, as that is a reserved name.  Please use another name.');
            return false;
        }
        if (file_exists(GEO_TEMPLATE_DIR . $new_t_set)) {
            $admin->userError('New template set name specified (' . $new_t_set . ') already exists.  Please use another name.  If you wish to over-write this template set, you need to delete it first.');
            return false;
        }

        if (!is_writable(GEO_TEMPLATE_DIR)) {
            $admin->userError('Template Directory (' . GEO_TEMPLATE_DIR . ') is not writable (CHMOD 777).  To enable copying of a template set, you need to ensure that this directory is writable.');
            return false;
        }
        if ($orig != $new_t_set) {
            $admin->userNotice('Note: The original name you specified, <em>' . geoString::specialChars($orig) . '</em> had to be cleaned to be safe for use as a directory name.  The template set name we will use is <em>' . $new_t_set . '</em>.');
        }
        return $new_t_set;
    }

    private function _getFiletype($file)
    {
        $extension = substr($file, (strrpos($file, '.') + 1));
        //list of alternate file types that could be considered as a template file
        $tplTypes = array ('html','htm');
        if (in_array($extension, $tplTypes)) {
            return 'tpl';
        }
        if ($extension == 'folder') {
            //keep it from being mistaken as an actual folder
            return '.folder';
        }
        return $extension;
    }

    /**
     * Used to check the given filename to make sure it is "safe", as far as filename is concerned.
     *
     * @param $filename  The full filename
     * @return bool
     */
    private function _checkNameTrials($filename)
    {
        $filename = geoFile::cleanPath($filename);

        //only concerned really with the actual file
        $filename = substr($filename, (strrpos($filename, '/') + 1));

        //first, check the extension
        $allowedExts = array (
            'tpl',
            'html',
            'js',
            'css',
            'jpg',
            'png',
            'gif',
            'jpeg',
        );

        $extension = substr($filename, (strpos($filename, '.') + 1));

        //note that since we start from the "first" . found, this automatically will fail on
        //double extensions, like if they attempted to use .php.tpl

        if (!in_array($extension, $allowedExts)) {
            geoAdmin::m(geoPC::adminTrialMessage('invalid_ext', $extension), geoAdmin::ERROR);
            return false;
        }

        return true;
    }

    private function echoJson($data)
    {
        $ajax = geoAjax::getInstance();
        $ajax->jsonHeader();
        echo $ajax->encodeJSON($data);
    }

    public function __call($name, $arguments)
    {
        geoView::getInstance()->addBody("<fieldset><legend>Calling $name</legend><div>Calling: <strong>$name</strong></div></fieldset>");
    }

    private function _activeTemplateSetWarning()
    {
        $activeSets = geoTemplate::getTemplateSets(true);
        if (!$activeSets[0]) {
            //$activeSets[0] will always be the highest-priority, non-default, active template set
            //if it doesn't exist, then only Default is active -- show an admin message
            geoAdmin::m('No <em>Active</em> template set detected -- currently using the default design, which is uneditable.', geoAdmin::NOTICE);
            geoAdmin::m('To make design changes, first <a href="index.php?page=design_sets_create_main" class="lightUpLink">Create a Main Template Set</a> or <a href="index.php?page=design_sets&mc=design">Activate</a> an existing one', geoAdmin::NOTICE);
        }
    }
}
