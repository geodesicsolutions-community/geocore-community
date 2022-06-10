<?php

//addons/SEO/admin.php

# SEO Addon
#for documentation on how addons work, see the example addon.

class addon_SEO_admin extends addon_SEO_info
{
    var $body;
    public $registry_id;

    function init_pages()
    {
        menu_page::addonAddPage('addon_SEO_main_config', '', 'General Settings', 'SEO', 'fa-sliders');
            menu_page::addonAddPage('addon_SEO_url_config', 'addon_SEO_main_config', 'URL Config', 'SEO', 'fa-sliders', 'sub_page');
    }

    function nextStep()
    {
        $CJAX = geoCJAX::getInstance();
        $next = intval($CJAX->get('step')) + 1;
        return geoHTML::addButton("Continue", "?mc=addon_cat_SEO&page=addon_SEO_main_config&step=$next");
    }

    function replaceCustomText($text_num)
    {
    }
    public function customizeUrls($minimal = 1)
    {
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = 'install';
        $s = $this->get('settings');

        $reg = geoAddon::getRegistry('SEO');

        $generate_all_confirm = $CJAX->call("AJAX.php?controller=addon_SEO&action=generateAllConfirm", 'confirm_generate_all');
        $applyAllTooltips = geoHTML::showTooltip('Apply All Settings', 'Changing the URL settings will not have an effect on your site,
		 until you click Apply all Settings button.
		 <br /><br />
		 This is to give you time to set up your URLs how you want them, and in the mean time the links won\'t be broken on your site.');
        $HTML = '';
        if (!$minimal) {
            $checked = ($s['continue'] ? " checked='checked'" : "");
            $check_val = $CJAX->value('rewrite_urls');
            $call  = $CJAX->call('AJAX.php?controller=addon_SEO&action=onOff&rewrite_urls=' . $check_val);

            $warning = '';
            //un-comment to test using underscores
            /*
            $reg->useUnderscore = 1;
            $reg->save();
            */

            if ($reg->useUnderscore) {
                //Upgraded from before version 2.1.0, and still using underscores!
                $warning = geoHTML::addOption('Changes to .htaccess Needed', 'You are still using underscores "_" in titles!  Re-generate the .htaccess file and copy the changes to your .htaccess file to be able to start using dashes "-" instead.');
            }

            $HTML = "
			<fieldset>
				<legend>SEO Configuration</legend>
				<div class='form-horizontal'>
				$warning
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Re-Write URLS</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='checkbox' id='rewrite_urls' value='1'$checked $call />
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Apply All Settings $applyAllTooltips</label>
					<div class='col-xs-12 col-sm-6'>
						" . geoHTML::addButton('Apply All Settings', $generate_all_confirm, true, 'generate') . "
					</div>
				</div>
				<div id='confirm_generate_all'></div>
				<span id='updates_your_htaccess' style='color:red'></span><br />
				<button id='htaccessSelectButton'>Select All</button>
				<div id='htaccess' class='medium_font' style='overflow: auto; height: auto; min-height: 30px; border:dashed black 1px;text-align:left; margin:5px; padding:5px;'>Click Apply-All to re-generate .htaccess</div>
				</div>
			</fieldset>
			";
        }
        $HTML .= "<fieldset>
			<legend>Customize Re-Written URLs</legend>
			<div class='form-horizontal'>
			";

        $items = array_keys($this->get('items', 'settings'));

        if (count($items) == 0) {
            //TODO: maybe re-generate or something?
        }
        foreach ($items as $s_name) {
            $settings[$s_name]['title'] = $this->get('title', $s_name);
            $settings[$s_name]['text'] = $this->get('custom_text', $s_name);
            $settings[$s_name]['order'] = $this->get('order', $s_name);
            $settings[$s_name]['title'] = $this->get('title', $s_name);
            $settings[$s_name]['name'] = $this->get('name', $s_name);
            $settings[$s_name]['status'] = $this->get('status', $s_name);
            $settings[$s_name]['custom_text'] = $this->get('custom_text', $s_name);
            $settings[$s_name]['type'] = $this->get('type', $s_name);
            $settings[$s_name]['regex'] = $this->get('regex', $s_name);
            $settings[$s_name]['regexhandler'] = $this->get('regexhandler', $s_name);
            $settings[$s_name]['url_template'] = $this->get('url_template', $s_name);
        }

        $db = DataAccess::getInstance();
        $indexfile = $db->get_site_setting('classifieds_file_name');
        $site =  str_replace($indexfile, '', $db->get_site_setting('classifieds_url'));

        foreach ($settings as $setting_name => $setting) {
            $util = geoAddon::getUtil('SEO');
            $util->registry_id = $setting_name;
            $template_url = $util->getUrlTemplate();

            if (!$template_url) {
                $template_url = "&nbsp;&nbsp;&nbsp;&nbsp; - Edit before display - ";
                $path = '';
            } else {
                $path = $site;
            }
            $search = array ('/\(![^!]+\_PAGE_ID!\)/','/\(![^!]+\_ID!\)/','/\(![^!]+\_TITLE!\)/');
            $title_replace = ($reg->useUnderscore) ? 'Title_abc_123' : 'Title-abc-123';
            $replace = array('3','456',$title_replace);
            $template_url = preg_replace($search, $replace, $template_url);
            $dash_name = str_replace(' ', '_', $setting_name);

            $newWin = (isset($_GET['step']) && $_GET['step'] == 1) ? 'onclick="window.open(this.href); return false;"' : '';
            $edit_button = "<a href='index.php?page=addon_SEO_url_config&amp;r_id=$setting_name&bypass=' $newWin class='btn btn-xs btn-info'><i class='fa fa-pencil'></i> Edit</a>";
            $HTML .= '<div class="form-group">
						<label class="control-label col-xs-12 col-sm-5">' . ucwords($setting_name) . ' URL:</label>
						<div class="col-xs-12 col-sm-6 vertical-form-fix">' .
                        "<span class='small_font' id='{$dash_name}_path'>{$path}</span><span class='small_font' id='$dash_name'>{$template_url}</span> {$edit_button}" .
                        '</div>
					</div>';
        }
        $HTML .= "
		</div>
		</fieldset>";

        if (!$minimal) {
            //convert accents?
            $accentsSelect = ($reg->get('replaceAccents', false)) ? 'checked="checked"' : '';
            $accents_val = $CJAX->value('replaceAccents');
            $CJAX->JSevent('onchange');
            $accents_call = $CJAX->call('AJAX.php?controller=addon_SEO&action=replaceAccents&replaceAccents=' . $accents_val);

            //get setting for & replacement
            $replaceAnd = $reg->get('replaceAnd', '-and-');
            $and_val = $CJAX->value('replaceAnd');
            $CJAX->JSevent('onclick');
            $and_call = $CJAX->call('AJAX.php?controller=addon_SEO&action=replaceAnd&replaceAnd=' . $and_val);
            $and_reset_call = $CJAX->call('AJAX.php?controller=addon_SEO&action=replaceAnd&replaceAnd=-and-');
            $and_button = geoHTML::addButton('apply', $and_call, true);
            $and_reset = geoHTML::addButton('reset', $and_reset_call, true);

            //add parent category name before category name?
            $includeParentCategoryNameSelect = ($reg->get('includeParentCategoryName', false)) ? 'checked="checked"' : '';
            $includeParentCategoryName_val = $CJAX->value('includeParentCategoryName');
            $CJAX->JSevent('onchange');
            $includeParentCategoryName_call = $CJAX->call('AJAX.php?controller=addon_SEO&action=includeParentCategoryName&includeParentCategoryName=' . $includeParentCategoryName_val);


            $HTML .= "
		<fieldset>
			<legend>Titles in Re-Written URLs</legend>
			<div class='form-horizontal'>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Convert accents</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='checkbox' name='replaceAccents' id='replaceAccents' value='1' $accentsSelect $accents_call />
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Replace &amp; with:</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='text' id='replaceAnd' size='4' value='$replaceAnd' /> {$and_button} {$and_reset}
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Include parent category name in front of category name where it appears</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='checkbox' name='includeParentCategoryName' id='includeParentCategoryName' value='1' $includeParentCategoryNameSelect $includeParentCategoryName_call />
					</div>
				</div>
			</div>
		</fieldset>";

            //add fieldset for advanced settings
            $checked_seo = ($s['use_old_redirects']) ? "checked='checked'" : '';
            $check_val = $CJAX->value('use_old_redirects');
            $call  = $CJAX->call('AJAX.php?controller=addon_SEO&action=useOldRedirects&use_old_redirects=' . $check_val);
            $old_tooltip = geoHTML::showTooltip('Include SEO 1.0 URLs', "Turn this on if you previously used the SEO 1.0 version for any length of time,
			and search engines have the URLs used by that version still indexed.
			<br /><br />
			This will add additional lines to the generated .htaccess file contents necessary so that &quot;old&quot; links will re-direct (301) to the
			new URLs.
			<br /><br />
			Once search engines have had time to update their indexes to the new URLs for your site, you can turn this back off and re-generate the .htaccess file contents.");

            $checked_force = ($s['force_seo_urls']) ? "checked='checked'" : '';
            $check_val_force = $CJAX->value('force_seo_urls');
            $call_force  = $CJAX->call('AJAX.php?controller=addon_SEO&action=forceSeoUrls&force_seo_urls=' . $check_val_force);
            $force_tooltip = geoHTML::showTooltip('Force SEO URLs', "If a page that is supposed to use SEO re-written URL is not, this will force it to re-direct to the re-written SEO URL.
			<br /><br />
			Use this to help prevent duplicate pages with the same content, which can have a negative effect on search engine rankings.");

            $symlink = ($s['omit_symlink']) ? "checked='checked'" : '';
            $check_val_symlink = $CJAX->value('omit_symlink');
            $call_symlink  = $CJAX->call('AJAX.php?controller=addon_SEO&action=omitSymlink&omit_symlink=' . $check_val_symlink);
            $symlink_tooltip = geoHTML::showTooltip('Omit FollowSymLinks line in .htaccess', "If checked, this will OMIT the following line when generating the
			contents of the .htaccess file for you to copy/paste:
			<br />
			<strong style='border: 1px dashed black;'>Options +FollowSymlinks</strong>
			<br /><br />
			Some servers produce a 500 internal server error, when using the line noted above.  Other servers will not work without the line
			noted above.  If SEO re-written URLs are not working, or if you are getting a 500 server error on re-written URLs, check or un-check
			the setting.  Then Apply All and re-copy the contents for the .htaccess file.");


            $HTML .= "
		<fieldset>
			<legend id='advanced_settings'>Advanced Settings</legend>
			<div class='form-horizontal'>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Omit FollowSymlinks line: $symlink_tooltip</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='checkbox' id='omit_symlink' value='1' $symlink $call_symlink />
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Include SEO 1.0 URLs: $old_tooltip</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='checkbox' id='use_old_redirects' value='1' $checked_seo $call />
					</div>
				</div>
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Force SEO URLs: $force_tooltip</label>
					<div class='col-xs-12 col-sm-6'>
						<input type='checkbox' id='force_seo_urls' value='1' $checked_force $call_force />
					</div>
				</div>
			</div>
		</fieldset>
			";
        }

        return $HTML;
    }
    function display_addon_SEO_main_config()
    {
        //responsible for creating & diplaying the entire page, including the header and footer.
        $CJAX = geoCJAX::getInstance();
        $view = geoView::getInstance();

        $this->registry_id = 'install';
        $s = $this->get('settings');

        $view->addTop($CJAX->init())
            ->addJScript('../addons/SEO/seo.js');

        if (!$s['continue'] && !$s['skip']) {
            $tpl = new geoTemplate('addon', 'SEO');
            $call = $CJAX->call("AJAX.php?controller=addon_SEO&action=firstTimeUsing&type={$s['type']}");
            $tpl->assign('settings', $s);
            $current_step = $CJAX->get('step');
             //really 4 but since starts from 1 and not from 0 then its going to be 5
            if (!$current_step) {
                $current_step = 0;
            }

            switch ($current_step) {
                default:
                case 0:
                    $tip = "";
                    $modules = [];
                    if (function_exists('apache_get_modules')) {
                        $modules = apache_get_modules();
                    }
                    if (!is_array($modules) xor !in_array('mod_rewrite', $modules)) {
                        $tpl->assign('checks_pass', 'no');
                    }
                    $no_step = true;
                    $content = $tpl->fetch('introduction.tpl');

                    break;
                case 1:
                    $tip = "URL Settings";
                    $content = $this->customizeUrls();
                    //$content = "";
                    break;
                case 2:
                    $tip = ".htaccess Contents";
                    //$call = $CJAX->call("AJAX.php?controller=addon_SEO&action=WizardGenerateAll");
                    $content = '';//geoHTML::addButton('Generate',$call,true,'generate');
                    break;
                case 3:
                    $tip = "Test Re-Written URLs.";
                    $no_step = true;
                    //$this->registry_id = 'category';
                    $category_template = $this->get('url_template', 'category');
                    $_extension = $this->get('extension', 'category');
                    $cat = geoCategory::getRandomBasicInfo();
                    $db = DataAccess::getInstance();
                    $seo = geoAddon::getUtil('SEO');
                    $url = $db->get_site_setting('classifieds_url');

                    $category_template = $seo->formatUrls($db->get_site_setting('classifieds_file_name') . '?a=5&b=' . $cat['category_id'], '');
                    $category_template = str_replace(array('href="','"'), '', $category_template);

                    $url = str_replace($db->get_site_setting('classifieds_file_name'), '', $url) . $category_template;
                    $url = "<a href='{$url}' target='_blank'>{$url}</a>";
                    $tpl->assign('url_info', $url);
                    break;
                case 4:
                    $no_step = true;
                    $tip = "Final Step";
                    $call = $CJAX->call("AJAX.php?controller=addon_SEO&action=goLive");

                    $content = "
					" . geoHTML::addButton('Finish: Start using SEO Urls', $call, true) . "
					";
                    break;
            }
            $tpl->assign('tip', $tip);
            $tpl->assign('go_step', $current_step);
            $number_of_steps = 4;
            if (isset($current_step) && $current_step && !$no_step && $current_step != ($number_of_steps - 1) && $current_step != 1) {
                $content .= "<br /><br />" . $this->nextStep();
            }

            $tpl->assign('steps', array(
                1 => 'URL Settings',
                2 => 'Configure .htaccess',
                3 => 'Test',
                4 => 'Finish'));
            $tpl->assign('content', $content);
            geoAdmin::display_page($tpl->fetch('steps.tpl'));
            return;
        }
        $view->addBody($this->customizeUrls(0));
    }

    public function display_addon_SEO_url_config()
    {
        geoView::getInstance()->addJScript('../addons/SEO/seo.js');
        $title = $this->get('title');
        $HTML = '';

        $CJAX = geoCJAX::getInstance();
        $db = DataAccess::getInstance();

        if (!$this->registry_id) {
            $this->registry_id = $CJAX->get('r_id');
        }
        $CJAX->link = true;

        $infos = $this->getItemsOrder();

        //Generate the old and the new URL
        $util = geoAddon::getUtil('SEO');
        $util->registry_id = $this->registry_id;

        $temps = $util->getUrlTemplate();
        $temps = str_replace(array('(!','!)','/'), array('[',']',' / '), $temps);
        $live_url = $temps;
        $site = '[' . dirname($db->get_site_setting('classifieds_url')) . '] / ';
        if ($live_url) {
            $live_url = $site . $live_url;
        } else {
            $live_url = 'Not Live Yet';
        }
        //generate current URL
        $ext = ($infos['ext'] != 'N/A') ? $infos['ext'] : '';
        $current_url = $site . implode(' / ', $infos['parts']) . $ext;

        $new = $CJAX->call("AJAX.php?controller=addon_SEO&action=addCustomText&r_id=$this->registry_id");//,'div_seo');
        $liveUrlTooltip = geoHTML::showTooltip('Live URL', 'This is the URL that is currently being used to
		re-write URLs on your site, if re-write URLs is enabled.
		Changing the URL settings below will not have an effect on your site,
		 until you click Apply all Settings button from the main configuration page.
		 <br /><br />
		 This is to give you time to set up your URLs how you want them, and in the mean time the links won\'t be broken on your site.');
        $currentUrlTooltip = geoHTML::showTooltip('URL using Settings Below', 'This is the URL according to settings set below.

		Changing the URL settings below will effect this URL, but will not effect the Live URL
		 until you click Apply all Settings button from the main configuration page.
		 <br /><br />
		 This is to give you time to set up your URLs how you want them, and in the mean time
		 the links won\'t be broken on your site.
		 <br /><br />
		 <strong>Note:</strong> If you see that one of the URL parts is not being used here when you
		 want it to be, ensure that the checkbox for <strong>Used?</strong> is checked in the settings
		 below.');

        $HTML .= "
		<fieldset>
		<legend>URL Parts</legend>
		<div class='form-horizontal'>
			<div class='form-group'>
				<label class='control-label col-xs-12 col-sm-5'>Live URL $liveUrlTooltip</label>
				<div class='col-xs-12 col-sm-6 vertical-form-fix'>
					<span id='span_live_url' style='white-space: nowrap;'>$live_url</span>
				</div>
			</div>
			<div class='form-group'>
				<label class='control-label col-xs-12 col-sm-5'>URL using Settings Below $currentUrlTooltip</label>
				<div class='col-xs-12 col-sm-6 vertical-form-fix'>
					<span id='span_current_url' style='white-space: nowrap;'>$current_url</span>
				</div>
			</div>

			<div id='div_order'>
				{$infos['html']}
			</div>
			<br />
			<div class='center'><a href='#' $new class='btn btn-success'><i class='fa fa-plus-circle'></i> Add new Custom URL Part</a></div>
		</div>
		</fieldset>
		";

        $resetbutton = $CJAX->call("AJAX.php?controller=addon_SEO&action=resetSetting&amp;r_id=$this->registry_id");
        $HTML .= "
		<fieldset>
		<legend>Reset to default</legend>
		<div>
			<div class='page_note'>Warning: by clicking the \"Reset\" button, you will be deleting any changes that you have applied to this URL</div>
			<div class='center'><a href='#' $resetbutton class='btn btn-danger'><i class='fa fa-trash-o'></i> Reset</a></div>
		</div>
		</fieldset>
		";

        $send = $CJAX->call('?mc=addon_cat_SEO&auto_save_cjax=1&debug=', 'all_settings_saved');
        $HTML  .= "</form>";
        //$HTML = "<pre style='border: 2px solid red;'>".geoString::specialChars($HTML).'</pre>';
        geoAdmin::display_page($HTML, '', '', $CJAX->init());
    }

    function getItemsOrder()
    {
        $seo = geoAddon::getUtil('SEO');
        if ($seo) {
            return $seo->getItemsOrder();
        }
    }


    function update_addon_SEO_main_config()
    {
        $admin = geoAdmin::getInstance();
        $CJAX = geoCJAX::getInstance();

        $CJAX->message($admin->getUserMessages(), 5);
        require GEO_BASE_DIR . 'app_bottom.php';
        exit;
    }

    public static $registry = array();
    private static $_pending_changes = array();
    function initRegistry($optional_id = '')
    {
        if (!$this->registry_id && !$optional_id) {
            return false;
        }
        if (!$optional_id) {
            $optional_id = $this->registry_id;
        }
        if (isset(self::$registry[$optional_id]) && is_object(self::$registry[$optional_id])) {
            return;
        }
        self::$registry[$optional_id] = new geoRegistry();
        self::$registry[$optional_id]->setName('addon_seo');
        self::$registry[$optional_id]->setId($optional_id);
        self::$registry[$optional_id]->unSerialize();
    }
    function save()
    {
        foreach (self::$registry as $id => $reg) {
            if (is_object($reg) && self::$_pending_changes[$id]) {
                $reg->save();
                self::$_pending_changes[$id] = 0;
            }
        }
    }

    function get($setting, $optional_id = '')
    {
        if (!$this->registry_id && !$optional_id) {
            return false;
        }
        if (!$optional_id) {
            $optional_id = $this->registry_id;
        }
        $this->initRegistry($optional_id);
        return self::$registry[$optional_id]->get($setting);
    }
    function set($setting, $value, $optional_id = '')
    {
        if (!$this->registry_id && !$optional_id) {
            return false;
        }
        if (!$optional_id) {
            $optional_id = $this->registry_id;
        }
        $this->initRegistry($optional_id);
        self::$registry[$optional_id]->set($setting, $value);
        self::$_pending_changes[$optional_id] = 1;
    }
}
