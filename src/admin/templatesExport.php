<?php

//templatesExport.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.09.0-79-gb63e5d8
##
##################################

//this is used within update as well
require_once CLASSES_DIR . 'php5_classes/File.class.php';
require_once CLASSES_DIR . 'php5_classes/String.class.php';
require_once CLASSES_DIR . 'php5_classes/Template.class.php';
require_once CLASSES_DIR . 'php5_classes/smarty/Smarty.class.php';


class geoTemplatesExport
{
    private $db, $file;
    private $error = '';
    protected static $_pagesTemplates;
    protected static $_module_attachments;
    protected static $_modules;
    protected static $_instance;

    public static function getInstance($db = null)
    {
        if (!isset(self::$_instance) || !is_object(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c($db);
        }
        return self::$_instance;
    }

    private function __construct($db = null)
    {
        if ($db === null) {
            $db = DataAccess::getInstance();
        }
        $this->db = $db;
        $this->file = geoFile::getInstance(geoFile::TEMPLATES);
        $this->file->jailTo(GEO_TEMPLATE_DIR);
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
            $custom_tpl = new miniTemplate();
            $custom_section = $custom_tpl->fetch(ADMIN_DIR . 'templates/design/parts/t_sets_custom_section.tpl');
        }
        return $custom_section;
    }

    public function useTSets($t_sets)
    {
        //use the tsets
        $tpl = new miniTemplate();
        $tpl->assign('t_sets', $t_sets);
        $tpl->assign('custom_section', $this->get_custom_tset_section());
        $t_contents = $tpl->fetch(ADMIN_DIR . 'templates/design/files/t_sets.php.tpl');

        $result = $this->file->fwrite('t_sets.php', $t_contents);
        if (!$result) {
            $this->error = $this->file->errorMsg();
            return false;
        }
        return true;
    }

    public function exportTo($t_set, $exportAll = false)
    {
        $t_set = geoFile::cleanPath($t_set);

        if (!is_writable(GEO_TEMPLATE_DIR)) {
            $this->error = 'The templates dir <em>' . GEO_TEMPLATE_DIR . '</em> Is NOT Writable, you will need to CHMOD 777 that directory and all contents
			in order to convert your current design to use Smarty templates.';
            return false;
        }

        if (!file_exists($this->file->absolutize($t_set))) {
            //attempt to create dir
            mkdir($this->file->absolutize($t_set));
        }


        if (is_dir(GEO_TEMPLATE_DIR . "$t_set/main_page") && !is_writable(GEO_TEMPLATE_DIR . "$t_set/main_page")) {
            $this->error = 'The dir <em>' . GEO_TEMPLATE_DIR . $t_set . 'main_page/</em> Is NOT Writable, you will need to CHMOD 777 that directory and all contents
			in order to convert your current design to use Smarty templates.';
            return false;
        }
        if (!isset($this->db)) {
            $this->error = 'Error: DB connection could not be established.';
            return false;
        }

        //Check to see if they even have any db-based templates
        if (!$this->_tableExists('geodesic_templates')) {
            $this->error = 'Cannot export DB-based design since no DB templates were found!';
            return false;
        }


        /**
         * Convert all the templates in the database to use smarty templates
         */
        $sql = "SELECT `template_id`,`name`,`language_id`,`template_code`,`applies_to`,`storefront_template`,`storefront_template_default` FROM `geodesic_templates` ORDER BY language_id";

        $templates = $this->db->GetAll($sql);
        if ($templates === false) {
            $this->error = 'An error occurred when attempting to get data from db!  error msg: ' . $this->db->ErrorMsg();
            return false;
        }

        //Attempt to copy files from default template set
        //first, do theme styles
        $pres = array ('', 'primary_','secondary_');
        foreach ($pres as $pre) {
            $cssContents = file_get_contents($this->file->absolutize('default/external/css/' . $pre . 'theme_styles.css'));

            if ($cssContents) {
                //got contents, replace beginning part
                $search = '/^\/\* GIT: ([^\s]+) \*\//';
                $replace = '@import url(\'../../../default/external/css/' . $pre . 'theme_styles.css\');

/*
 * Leave first line of this file intact to make software updates easier!
 * 
 * File Created from Geo pre-5.0 design exporter (GIT: $1)
 */
';
                $cssContents = preg_replace($search, $replace, $cssContents);

                $this->file->fwrite($t_set . '/external/css/' . $pre . 'theme_styles.css', $cssContents);
            }
        }
        //now copy over images
        $this->file->copy('default/external/images/', "$t_set/external/images/");
        //and finally, the text changes
        $this->file->copy('default/text.csv', "$t_set/text.csv");

        $tpls = array();

        foreach ($templates as $template) {
            //just a generic template
            $filename = $this->_cleanFilename($template['name']) . "_lang{$template['language_id']}_{$template['template_id']}.tpl";

            self::$_pagesTemplates['t'][$template['template_id']] = $filename;//remember for page to template assignment later on
            if ($template['storefront_template'] || $template['storefront_template_default'] || $template['applies_to'] == 3) {
                //storefront template
                $cat = ($template['storefront_template_default']) ? 0 : $template['template_id'];

                self::$_pagesTemplates['addons/storefront/home'][1][$cat] = $filename;
            }
            $full_file = $this->file->absolutize("{$t_set}/main_page/{$filename}");

            $tpls[$template['template_id']] = $filename;

            $tpl_code = geoString::fromDB($template['template_code']);
            if ((strpos($tpl_code, '\\"') !== false || strpos($tpl_code, '\\\'') !== false) && !preg_match("/[^\\\]+('|\")/", $tpl_code)) {
                //this template was never strip slashed back in the days
                $tpl_code = stripslashes($tpl_code);
            }

            //find all attached modules & convert template to use smarty
            $tpl_code = self::convertTemplate($tpl_code, $filename);

            //write the file
            if (!$this->file->fwrite($full_file, $tpl_code)) {
                $this->error = $this->file->errorMsg();
                return false;
            }
        }

        /**
         * Convert all logged_in_out_html_modules to smarty templates
         */

        $sql = "SELECT `page_id`, `module_replace_tag`, `module_logged_in_html`,`module_logged_out_html`, `php_code` FROM `geodesic_pages` WHERE `module_replace_tag` like '(!LOGGED%' OR `module_replace_tag` like '(!MODULE_PHP_INSERT%'
			OR `module_replace_tag` like 'logged%' OR `module_replace_tag` like 'module_php_insert%'";

        $modules_results = $this->db->GetAll($sql);
        //die ('results: <pre>'.print_r($modules_results,1));
        if ($modules_results === false) {
            $this->error = 'An error occurred when attempting to data from db, error msg: ' . $this->db->ErrorMsg();
            return false;
        }
        foreach ($modules_results as $module) {
            $tpl_code = false;
            //clean up the tag, just in case the update script that does this
            //has not already been run, to convert (!TAG!) style to just tag
            $tag = strtolower(str_replace(array('(!','!)'), '', $module['module_replace_tag']));
            $num = intval(preg_replace('/[^0-9]/', '', $tag));
            if (!$num) {
                $num = 1;
            }

            if (strpos($tag, 'html') !== false) {
                //logged in/out module
                $filename = 'attached/html' . $num . '.tpl';

                $full_file = "$t_set/main_page/$filename";

                $logged_in = trim(geoString::fromDB($module['module_logged_in_html']));
                $logged_out = trim(geoString::fromDB($module['module_logged_out_html']));

                self::$_module_attachments[$filename] = array(); //make sure the file is written, even if there are no module
                //attachments

                //convert tags
                $logged_in = self::convertTemplate($logged_in, $filename);
                $logged_out = self::convertTemplate($logged_out, $filename);

                //create full text
                $tpl_code = "{if not \$logged_in}
{*Logged out code*}
$logged_out
{else}
{*Logged in code*}
$logged_in
{/if}";
            } elseif (strpos($tag, 'php') !== false) {
                $filename = 'php' . $num . '.tpl';

                $full_file = "$t_set/main_page/attached/$filename";

                $tpl_code = trim(geoString::fromDB($module['php_code']));
                if ($tpl_code) {
                    $tpl_code = "{* PHP no longer supported (as of Smarty 3.1)! *} {* {php}
$tpl_code
{/php}*}";
                }
            }
            if (!$tpl_code) {
                continue;
            }

            //write file
            if (!$this->file->fwrite($full_file, $tpl_code)) {
                $this->error = $this->file->errorMsg();
                return false;
            }
        }

        /**
         * Convert all extra pages to templates
         */
        //NOTE: Called from update process as well, so don't have access to geoTables::
        $sql = "SELECT `extra_pages`, `val_string`, `val_text` FROM `geodesic_extra_pages_registry` WHERE `index_key` = 'body_code'";
        $extras = $this->db->GetAll($sql);

        if ($extras === false) {
            $this->error = 'An error occurred when attempting to data from db, error msg: ' . $this->db->ErrorMsg();
            return false;
        }
        foreach ($extras as $extra) {
            $tpl_code = geoString::fromDB($extra['val_string']);
            if (!$tpl_code) {
                $tpl_code = geoString::fromDB($extra['val_text']);
            }

            $parts = explode(':', $extra['extra_pages']);
            $page_id = $parts[0];
            $language_id = $parts[1];

            $page_name = 'page_' . ($page_id - 134) . '_l' . $language_id;

            $filename = 'extra_pages/' . $page_name . '.tpl';

            self::$_pagesTemplates[$page_id]["'extra_page_main_body'"][$language_id][0] = $filename;

            $full_file = "$t_set/main_page/$filename";

            self::$_module_attachments[$filename] = array(); //make sure the file is written, even if there are no module
            //attachments

            //convert tags
            if ($tpl_code) {
                $tpl_code = self::convertTemplate($tpl_code, $filename);
            }

            //write file
            if (!$this->file->fwrite($full_file, $tpl_code)) {
                $this->error = $this->file->errorMsg();
                return false;
            }
        }

        /*
         * Write the module to template attachments php files
         */
        foreach (self::$_module_attachments as $filename => $attachments) {
            $tpl = new miniTemplate();
            $tpl->assign($attachments);
            $tpl->assign('filename', $filename);
            $tpl_code = $tpl->fetch(ADMIN_DIR . 'templates/design/files/modules_to_template.tpl');
            unset($tpl);
            $full_file = "$t_set/main_page/attachments/modules_to_template/$filename.php";

            //write the file
            if (!$this->file->fwrite($full_file, $tpl_code)) {
                $this->error = $this->file->errorMsg();
                return false;
            }
        }

        /**
         * Create and write the template to page php files
         */

        //get all the main category ones
        //NOTE: Called from update process as well, so don't have access to geoTables::
        $sql = "SELECT t.`page_id`, t.language_id, t.template_id FROM `geodesic_pages` as p, `geodesic_pages_templates` as t WHERE p.page_id = t.page_id AND p.`module`=0";
        $tplResults = $this->db->GetAll($sql);

        foreach ($tplResults as $row) {
            self::$_pagesTemplates[$row['page_id']][$row['language_id']][0] = self::$_pagesTemplates['t'][$row['template_id']];
        }

        //Get all the classified/auction sub-pages assignments non-cat specific
        //user_ad_template = default classified details sub-page
        $sql = "SELECT `user_ad_template`, `auctions_user_ad_template`,"
             . "`full_size_image_template`, `ad_detail_print_friendly_template`,"
             . "`auction_detail_print_friendly_template`, `popup_image_template_id`"
             . " FROM `geodesic_classifieds_ad_configuration`";
        $row = $this->db->GetRow($sql);
        if ($row === false) {
            $this->error = 'A DB Error occured, conversion of templates cannot continue.  Debug info: : ' . $this->db->ErrorMsg();
            return false;
        }

        $homeTpl = $this->db->GetRow("SELECT `value` FROM `geodesic_site_settings` WHERE `setting`='home_template'");
        $homeTpl = (isset(self::$_pagesTemplates['t'][$homeTpl['value']])) ? self::$_pagesTemplates['t'][$homeTpl['value']] : 'user_management.tpl';

        self::$_pagesTemplates['1_classified'][1][0] = self::$_pagesTemplates['t'][$row['user_ad_template']];
        self::$_pagesTemplates['1_auction'][1][0] = self::$_pagesTemplates['t'][$row['auctions_user_ad_template']];
        self::$_pagesTemplates['84_detail'][1][0] = self::$_pagesTemplates['t'][$row['full_size_image_template']];
        self::$_pagesTemplates['69_classified'][1][0] = self::$_pagesTemplates['t'][$row['ad_detail_print_friendly_template']];
        self::$_pagesTemplates['69_auction'][1][0] = self::$_pagesTemplates['t'][$row['auction_detail_print_friendly_template']];
        //self::$_pagesTemplates['157_popup'][1][0] = self::$_pagesTemplates['t'][$row['popup_image_template_id']];
        self::$_pagesTemplates['43_home'][1][0] = $homeTpl;


        //get all the cat specific ones too
        //TODO Paginate this!
        $sql = "SELECT category_id, template_id, secondary_template_id,"
             . "ad_display_template_id, ad_detail_display_template_id,"
             . "auction_detail_display_template_id, ad_detail_full_image_display_template_id,"
             . "ad_detail_print_friendly_template, auction_detail_print_friendly_template,"
             . "search_template_id, language_id FROM `geodesic_categories_languages`";
             //. "ORDER BY category_id, language_id LIMIT $start, $at_once";//pagination start

        //Do NOT use GetAll - it causes problems on sites with a TON of cat-specific
        //templates.  Just go result row by result row, the old-school (and
        //performance-better) way.
        $catTplResults = $this->db->Execute($sql);
        while ($catTplResults && $row = $catTplResults->FetchRow()) {
            if ($row['template_id']) {
                //cat browsing (id 3)
                self::$_pagesTemplates[3][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['template_id']];
                if ($row['template_id'] != $row['secondary_template_id']) {
                    //do 2 different template depending on if this is page x vs first page.
                    self::$_pagesTemplates['3_secondary'][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['secondary_template_id']];
                }
            }

            if ($row['ad_display_template_id']) {
                //1: display listing details overall page
                self::$_pagesTemplates[1][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['ad_display_template_id']];
            }

            if ($row['ad_detail_display_template_id']) {
                //1: display classifieds details sub-template
                self::$_pagesTemplates['1_classified'][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['ad_detail_display_template_id']];
            }
            if ($row['auction_detail_display_template_id']) {
                //1: display auction details sub-template
                self::$_pagesTemplates['1_auction'][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['auction_detail_display_template_id']];
            }

            if ($row['ad_detail_full_image_display_template_id']) {
                //84: display full image template
                self::$_pagesTemplates['84_detail'][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['ad_detail_full_image_display_template_id']];
            }

            if ($row['ad_detail_print_friendly_template']) {
                //69: display print friendly classified
                self::$_pagesTemplates['69_classified'][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['ad_detail_print_friendly_template']];
            }
            if ($row['auction_detail_print_friendly_template']) {
                //69: display print friendly auction
                self::$_pagesTemplates['69_auction'][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['auction_detail_print_friendly_template']];
            }
            if ($row['search_template_id']) {
                //44: search page
                self::$_pagesTemplates[44][$row['language_id']][$row['category_id']] = self::$_pagesTemplates['t'][$row['search_template_id']];
            }
        }

        if ($exportAll) {
            //do all the user-group specific ones
            $sql = "SELECT * FROM `geodesic_pages_templates_affiliates`";

            $affTplResults = $this->db->GetAll($sql);
            foreach ($affTplResults as $row) {
                if ($row['template_id'] && isset(self::$_pagesTemplates['t'][$row['template_id']])) {
                    //cat browsing (id 3)
                    self::$_pagesTemplates[3] ['\'affiliate_group\''] [$row['language_id']] [$row['group_id']] = self::$_pagesTemplates['t'][$row['template_id']];
                    if ($row['template_id'] != $row['secondary_template_id'] && isset(self::$_pagesTemplates['t'][$row['secondary_template_id']])) {
                        //do 2 different template depending on if this is page x vs first page.
                        self::$_pagesTemplates['3_secondary'] ['\'affiliate_group\''] [$row['language_id']] [$row['group_id']] = self::$_pagesTemplates['t'][$row['secondary_template_id']];
                    }
                }

                if ($row['ad_display_template_id'] && isset(self::$_pagesTemplates['t'][$row['ad_display_template_id']])) {
                    //1: display classifieds details sub-template
                    self::$_pagesTemplates['1_classified'] ['\'affiliate_group\''] [$row['language_id']] [$row['group_id']] = self::$_pagesTemplates['t'][$row['ad_display_template_id']];
                }
                if ($row['auctions_display_template_id'] && isset(self::$_pagesTemplates['t'][$row['auctions_display_template_id']])) {
                    //1: display auction details sub-template
                    self::$_pagesTemplates['1_auction'] ['\'affiliate_group\''] [$row['language_id']] [$row['group_id']] = self::$_pagesTemplates['t'][$row['auctions_display_template_id']];
                }
            }
        }

        //do all the templates to addon pages
        $sql = "SELECT * FROM `geodesic_addon_pages`";
        $rows = $this->db->GetAll($sql);
        $addon_tpls = array();
        foreach ($rows as $row) {
            if ($row['template'] && isset(self::$_pagesTemplates['t'][$row['template']])) {
                //ignore storefront home attachments, if they are set
                if ($row['addon'] == 'storefront' && $row['name'] == 'home') {
                    //skip this one
                    continue;
                }
                self::$_pagesTemplates['addons/' . $row['addon'] . '/' . $row['name']][$row['language']][0] = self::$_pagesTemplates['t'][$row['template']];
                $addon_tpls[$row['addon']] = $row['addon'];
            }
        }

        $page_ids = array_keys(self::$_pagesTemplates);
        foreach ($page_ids as $page_id) {
            if ($page_id == 't') {
                continue;
            }
            $tpl = new miniTemplate();
            //echo "\$attachments = <pre>".print_r($attachments,1)."</pre>";
            $tpl->assign('page_attachments', self::$_pagesTemplates[$page_id]);

            $tpl_code = $tpl->fetch(ADMIN_DIR . 'templates/design/files/templates_to_page.tpl');

            $full_file = "$t_set/main_page/attachments/templates_to_page/$page_id.php";
            //write the file
            if (!$this->file->fwrite($full_file, $tpl_code)) {
                $this->error = $this->file->errorMsg();
                return false;
            }
        }

        $useCss = $this->db->GetRow("SELECT `use_css` FROM `geodesic_classifieds_configuration`");

        if ($exportAll && $useCss && $useCss['use_css']) {
            /**
             * Export the CSS
             */

            $sql = "SELECT `geodesic_pages_fonts`.*, `geodesic_pages`.`module`, `geodesic_pages`.`name` as page_name, `geodesic_pages`.`module_replace_tag` FROM `geodesic_pages_fonts`, `geodesic_pages` WHERE `geodesic_pages_fonts`.page_id=`geodesic_pages`.page_id ORDER BY `page_id`, `element`";
            $this_css_info = array();
            $rows = $this->db->GetAll($sql);

            foreach ($rows as $row) {
                $row['module'] = (int)$row['module'];
                //if using module tag, "clean it up" just in case the update script has not already done that.
                $row['page_id'] = ($row['module']) ? str_replace(array('(!','!)'), '', strtolower($row['module_replace_tag'])) : $row['page_id'];
                if (!isset($this_css_info[$row['module']][$row['page_id']])) {
                    $pText = ($row['module']) ? 'module' : 'page';
                    $this_css_info[$row['module']][$row['page_id']] = "\n/* CSS for $pText {$row['page_name']} (#{$row['page_id']}) */\n\n";
                }
                $this_css_info[$row['module']][$row['page_id']] .= "/* {$row['name']} */\n.{$row['element']} {\n";
                if (strlen(trim($row['font_family'])) > 1) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\tfont-family: {$row['font_family']};\n";
                }
                if ($row['font_size'] > 0) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\tfont-size: {$row['font_size']}px;\n";
                }
                if (strlen(trim($row['font_style'])) > 1) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\tfont-style: {$row['font_style']};\n";
                }
                if (strlen(trim($row['font_weight'])) > 1) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\tfont-weight: {$row['font_weight']};\n";
                }
                if (strlen(trim($row['color'])) > 0) {
                    if (strpos($row['color'], '#') === false) {
                        //Add the # to the beginning
                        $row['color'] = '#' . trim($row['color']);
                    }
                    while (strlen($row['color']) < 7) {
                        //add 0's to the end, until it is a hex 6 long
                        //(7 including # at beginning)
                        $row['color'] .= '0';
                    }
                    $this_css_info[$row['module']][$row['page_id']] .= "\tcolor: {$row['color']};\n";
                }
                if (strlen(trim($row['text_decoration'])) > 0) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\ttext-decoration: {$row['text_decoration']};\n";
                }
                if (strlen(trim($row['background_color'])) > 0) {
                    if (strpos($row['background_color'], '#') === false) {
                        //Add the # to the beginning
                        $row['background_color'] = '#' . trim($row['background_color']);
                    }
                    while (strlen($row['background_color']) < 7) {
                        //add 0's to the end, until it is a hex 6 long
                        //(7 including # at beginning)
                        $row['background_color'] .= '0';
                    }
                    $this_css_info[$row['module']][$row['page_id']] .= "\tbackground-color: " . $row['background_color'] . ";\n";
                }
                if (strlen(trim($row['background_image'])) > 0) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\tbackground-image: url('{$row['background_image']}');\n";
                }
                if (strlen(trim($row['text_align'])) > 0) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\ttext-align: {$row['text_align']};\n";
                }
                if (strlen(trim($row['text_vertical_align'])) > 0) {
                    $this_css_info[$row['module']][$row['page_id']] .= "\tvertical-align: {$row['text_vertical_align']};\n";
                }
                if (strlen(trim($row['custom_css'])) > 1) {
                    $this_css_info[$row['module']][$row['page_id']] .= $row['custom_css'] . "\n";
                }

                $this_css_info[$row['module']][$row['page_id']] .= "}\n";
            }
            unset($rows);
            foreach ($this_css_info as $isModule => $pages) {
                foreach ($pages as $pageId => $contents) {
                    $fileName = "$t_set/external/css/" . (($isModule) ? 'module' : 'page') . "/$pageId.css";
                    if (!$this->file->fwrite($fileName, $contents)) {
                        $this->error = $this->file->errorMsg();
                        return false;
                    }
                }
            }
            unset($this_css_info);
        }

        return true;
    }


    public static function convertTemplate($tpl_code, $filename)
    {
        //replace { with {ldelim} first
        $tpl_code = str_replace('{', '{ldelim}', $tpl_code);

        $tag_search = array ('(!MAINBODY!)','(!CSSSTYLESHEET!)', '(!SELLER_USERNAME!)' );
        $tag_replace = array ('{body_html}','{head_html}', '{$seller_username}');

        //convert common tags
        $tpl_code = str_replace($tag_search, $tag_replace, $tpl_code);
        //echo 'code:<pre>'.htmlspecialchars($tpl_code).'</pre><br /><br />';

        $storefrontChecks = array ('(!STOREFRONT_MANAGER!)', '(!STOREFRONT_CATEGORIES',
            '(!STOREFRONT_PAGES', '(!STOREFRONT_SUBSCRIBE');
        $processStorefront = false;
        foreach ($storefrontChecks as $check) {
            if (strpos($tpl_code, $check) !== false) {
                $processStorefront = true;
                break;
            }
        }
        if ($processStorefront) {
            //Convert Storefront template...
            /**
             * Convert storefront tags, which are "special"
             */
            //convert storefront categories
            $cat_tag_replace = "{if \$storefront_categories}
<ul id='categories'>
	<li class='category_menu_title menu_title'>\\2</li>
	<li id='home' class='menu_item'>{\$storefront_homelink}</li>
	{foreach from=\$storefront_categories item='cat'}
	<li class='menu_item'>&nbsp;&nbsp;<a href='{\$cat.url}'>{\$cat.category_name}</a></li>
	{/foreach}
</ul>
{/if}";
            $tpl_code = preg_replace('@\(!STOREFRONT_CATEGORIES[\s]*?(MENU_TITLE="([^"]*)")?[\s]*?!\)@', $cat_tag_replace, $tpl_code);

            //convert storefront pages
            $page_tag_replace = "{if \$storefront_pages}
<ul id='pages'>
	<li class='page_menu_title menu_title'>\\2</li>
	{foreach from=\$storefront_pages item='page'}
	<li class='menu_item'><a href='{\$page.url}'>{\$page.link_text}</a></li>
	{/foreach}
</ul>
{/if}";
            $tpl_code = preg_replace('/\(!STOREFRONT_PAGES[\s]*?(MENU_TITLE="([^"]*)")?[\s]*?!\)/', $page_tag_replace, $tpl_code);

            //convert newsletter
            $subscriptionBlock .= "
<form action='' id='newSubscriber' method='post'>
	<ul id='subscription'>
		<li class='page_menu_title menu_title'>\\2</li>
{if \$storefront_email_added}
		<li class='menu_item'>\\8</li>
{else}
		<li class='menu_item'>
			<input type='hidden' name='newSubscriber' value='1' />
			<input type='text' name='emailAddress' id='emailAddress' value='\\6' onfocus='javascript: document.getElementById(\"subscribeSubmit\").disabled = false;' />
		</li>
		<li class='menu_item'>
			<input type='submit' name='subscribeSubmit' id='subscribeSubmit' value='\\4' disabled='disabled' />
		</li>
{/if}
	</ul>
</form>";

            $tpl_code = preg_replace('/\(!STOREFRONT_SUBSCRIBE[\s]*?(MENU_TITLE="([^"]*)")?[\s]*?(BUTTON_TEXT="([^"]*)")?[\s]*?(DEFAULT_VALUE="([^"]*)")?[\s]*?(AFTER_TEXT="([^"]*)")?[\s]*?!\)/', $subscriptionBlock, $tpl_code);



            $tpl_code = preg_replace('/\(!STOREFRONT_MANAGER!\)([a-zA-Z0-9\w\W\d\D]*)\(!END_STOREFRONT_MANAGER!\)/', '{storefront_manager}', $tpl_code);
            $tpl_code = str_replace('(!STOREFRONT_MANAGER!)', '{storefront_manager file="test.tpl"}', $tpl_code);
            $tpl_code = preg_replace('/\(!STOREFRONT_MANAGER_EXTRA_PAGE!\)([a-zA-Z0-9\w\W\d\D]*)\(!END_STOREFRONT_MANAGER_EXTRA_PAGE!\)/', '', $tpl_code);

            //remove the storefront css, it is added dynamically
            $csses = array (
            '<link href="storefront.css" rel="stylesheet" type="text/css">',
            '<link href="storefront_manager.css" rel="stylesheet" type="text/css">'
            );

            $tpl_code = str_replace($csses, '', $tpl_code);
        }

        //convert rest
        $tpl_code = preg_replace('/\(\!([^\!\s]+)\!\)/e', "geoTemplatesExport::replace('\$1',\"$filename\")", $tpl_code);

        if (!isset(self::$_module_attachments[$filename])) {
            //make sure even a blank attachment file is used
            self::$_module_attachments[$filename] = array();
        }

        return $tpl_code;
    }

    public static function replace($tag, $filename)
    {
        if (!isset(self::$_module_attachments)) {
            self::$_module_attachments = array();
        }

        $origTag = trim($tag);
        $tag = trim(strtolower(stripslashes($tag)));
        if (strpos($tag, 'addon') === 0) {
            //Convert syntax:
            //old: (!addon.[auth_tag].[addon_name].[tag]!)
            //new: {addon author='[auth_tag]' addon='[addon_name]' tag='[tag]'}

            //Use "orig" tag, not the lowercase tag, in case addon name has caps in it.
            $parts = explode('.', $origTag);
            if (count($parts) != 4) {
                //invalid addon tag?
                return '(!' . $tag . '!)';
            }
            self::$_module_attachments[$filename]['addons'][$parts[1]][$parts[2]][$parts[3]] = $parts[3];
            return "{addon author='{$parts[1]}' addon='{$parts[2]}' tag='{$parts[3]}'}";
        } elseif (strpos($tag, 'logged_in_out_html') === 0) {
            //special case, logged in/out module
            $num = intval(preg_replace('/[^0-9]/', '', $tag));
            if (!$num) {
                $num = 1;
            }
            self::$_module_attachments[$filename]['sub_pages']["attached/html{$num}.tpl"] = "attached/html{$num}.tpl";
            return "{include file=\"attached/html{$num}.tpl\"}";
        } elseif (strpos($tag, 'module_php_insert') === 0) {
            //special case, php module
            $num = intval(preg_replace('/[^0-9]/', '', $tag));
            return "{include file=\"attached/php{$num}.tpl\"}";
        } else {
            $id = self::getModuleId($tag);
            if (!$id) {
                //Not a module, just translate it to have the same var name as the tag..
                //TODO: Special case: payment_options is array
                $pre = "{\$";
            } else {
                //convert syntax:
                //old: (![tag]!)
                //new: {module tag='[tag]'}

                //remember attachment so we know to add it to attachments page
                self::$_module_attachments[$filename]['modules'][$id] = $tag;

                return "{module tag='{$tag}'}";
            }
        }
        return $pre . $tag . "}";
    }


    private $_genericFileNames = 0;
    private function _cleanFilename($name)
    {
        //clean out all the stuff that's not healthy for filenames.
        $name = strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', trim($name)));

        if ($name == '_') {
            //hmm there was no "healthy" parts to that name?  Give it a generic name then
            $this->_genericFileNames ++;
            $name = 'template_' . $this->_genericFileNames;
        }
        return $name;
    }

    /**
     * Checks to see if the given table exists.
     * @param String $tableName
     * @return Boolean true if table exists, false otherwise.
     */
    private function _tableExists($tableName)
    {
        $result = $this->db->Execute("show tables");
        while ($row = $result->FetchRow()) {
            if (in_array($tableName, $row)) {
                return true;
            }
        }
        return false;
    }

    public static function getModuleId($tag)
    {
        if (isset(self::$_modules)) {
            return self::$_modules[$tag];
        }
        $te = self::getInstance();

        $sql = 'SELECT `page_id`, `module_replace_tag` FROM `geodesic_pages` WHERE `module` = 1';
        $tagResult = $te->db->GetAll($sql);
        self::$_modules = array();
        if (!$tagResult) {
            return $tag;
        }
        foreach ($tagResult as $row) {
            //if using module tag, "clean it up" just in case the update script has not already done that.
            $thisTag = strtolower(str_replace(array('(!','!)'), '', $row['module_replace_tag']));
            self::$_modules[$thisTag] = $row['page_id'];
        }
        return self::$_modules[$tag];
    }

    public function errorMsg()
    {
        return $this->error;
    }
}

class miniTemplate extends Smarty
{
    public function __construct()
    {
        parent::__construct();
        $this->setCompileDir(GEO_TEMPLATE_COMPILE_DIR);
    }
}
