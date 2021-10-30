<?php

//addons/SEO/util.php

# SEO Addon (Search Engine Optimization)
# -- AKA Mod Rewrite

require_once ADDON_DIR . "SEO/info.php";

class addon_SEO_util extends addon_SEO_info
{

    public $registry_id;
    private static $_regUrl; //so we don't have to keep getting this from db every time
    private static $_file; //so we don't have to keep getting classifieds_file_name
    private static $_url; //so we don't have to keep getting classifieds_url
    private static $_secUrl; //so we don't have to keep getting classifieds_url
    private static $_baseHref; //so we don't have to keep getting classifieds_url
    private static $_useUnderscore; //whether we are still using old style underscores

    public $in_addon; //so that CJAX can access this property
    public $reset_one_setting;
    public $temp_registry_id, $replaceAnd;
    private static $arrow_id = 0;


    const REGEX_TITLE = '([^./\\\\"\'?#]+)';//'([-a-zA-Z0-9_]+)';
    const REGEX_NUMBER = '([0-9]+)';

    public function getItemsOrder()
    {
        $CJAX = geoCJAX::getInstance();
        $this->registry_id = $CJAX->get('r_id');

        if (!$this->registry_id) {
            return false;
        }
        $order = $this->get('order');
        $title = $this->get('title');
        $item_name = $this->get('name');
        $status = $this->get('status');
        $texts = $this->get('custom_text');
        $type = $this->get('type');
        $ext = $this->get('extension');
        $info = array();

        $info['status'] = $status;
        //echo ('<pre>'.print_r($item_name,1).'</pre>');

        foreach ($item_name as $name) {
            $i = $order[$name];
            $SEO[$i]['order']   =   $i;
            $SEO[$i]['title']   =   $title[$name];
            $SEO[$i]['name']    =   $item_name[$name];
            $SEO[$i]['status']  =   $status[$name];
            $SEO[$i]['type']    =   $type[$name];
            $SEO[$i]['text']    =   $texts[$name];
            $SEO[$i]['regex']   =   $regex[$name];
            $SEO[$i]['regexhandler']    =   $regex_handler[$name];
        }

        array_multisort($SEO, SORT_ASC);

        $i = 0;
        $egg = $CJAX->get('egg');
        if ($egg) {
            $egg = '&egg=reset';
        }
        //print_r ('<pre>'.print_r($SEO,1).'</pre>');
        $html = "
		<div class='table-responsive'>
<table class='table table-striped table-bordered table-hover'>
	<thead>
		<tr>
			<td class='col_hdr'>Used?</td>
			<td class='col_hdr'>Move</td>
			<td class='col_hdr'>Part</td>
			<td class='col_hdr'>Type</td>
		</tr>
	</thead>
	<tbody>";
        $row = 'row_color1';
        $parts = array();
        foreach ($SEO as $key => $item) {
            if (!$item['name'] && !$egg) {
                continue;
            }
            $i++;
            $pensil = $flag = '';
            $checkbox = '';
            $changeflag = '';

            if ($i == 1) {
                //It can be lonely at the top.
                $CJAX->link = true;
                $down = $CJAX->call("AJAX.php?controller=addon_SEO&action=order&item_name={$item['name']}&position=down{$egg}&r_id=$this->registry_id&qid=11", "div_order");
                $order = "<a href=$down class='btn btn-xs btn-info'><i class='fa fa-chevron-down'></i></a>";
            } elseif ($i == count($SEO)) {
                //Or at the bottom
                $CJAX->link = true;
                $up = $CJAX->call("AJAX.php?controller=addon_SEO&action=order&item_name={$item['name']}&position=up&r_id=$this->registry_id&qid=12", "div_order");
                $order = "<a href=$up class='btn btn-xs btn-info'><i class='fa fa-chevron-up'></i></a>";
            } else {
                //Somewhere in the middle
                //$CJAX->link = true;
                $down = $CJAX->call("AJAX.php?controller=addon_SEO&action=order&item_name={$item['name']}&position=down&r_id=$this->registry_id&qid=13", "div_order");
                //$CJAX->link = true;
                $up = $CJAX->call("AJAX.php?controller=addon_SEO&action=order&item_name={$item['name']}&position=up&r_id=$this->registry_id&qid=14", "div_order");
                $order = "
				<a href='#' $up class='btn btn-xs btn-info'><i class='fa fa-chevron-up'></i></a>
				<a href='#' $down class='btn btn-xs btn-info'><i class='fa fa-chevron-down'></i></a>
				";
            }

            $info[$i]['order'] = $order;
            $tag_type = 'Optional';
            $title = ucwords(str_replace('_', ' ', $item['name']));

            if (strpos($item['type'], 'custom_text') !== false) {
                //it's text
                if ($item['status'] == 1) {
                    $parts [] = $item['text'];
                }
                $current_tpl .= '/' . $item['text'];
                $tag_type = 'Custom';
                $CJAX->link = true;
                $checkbox = $CJAX->value('checkbox_' . $item['name']);
                $item_number = substr($item['name'], strrpos($item['name'], '_') + 1);
                $val = $CJAX->value($item['name']);
                $edit_call = $CJAX->call("AJAX.php?controller=addon_SEO&action=set_flag&cmd=edit&editing_text=1&item_name={$item['name']}&item_number=$item_number&ivalue=$checkbox&r_id=$this->registry_id&custom_value=$val");

                $title = "
				<span style='border: 1px solid black; padding: 2px; background-color: white;' id='span_{$item['name']}_text' $edit_call>
					{$item['text']}
				</span>
				<span id='span_{$item['name']}' style='display: none;'>
					<input type='text' id='{$item['name']}' value='~~edit_me~~' size='6' />
					" . geoHTML::addButton('Save', $edit_call, 1) . "
				</span>";
            } elseif ($item['status'] == 2) {
                //it's required text
                $tag_type = 'Required';
                $parts [] = '[' . strtoupper($item['name']) . ']';
            } elseif ($item['status'] == 1) {
                //it's optional
                $parts [] = '[' . strtoupper($item['name']) . ']';
            }
            if ($item['status'] != 2) {
                $changeflag = '';
                $checkbox = $CJAX->value('checkbox_' . $item['name']);
                $item_number = substr($item['name'], strrpos($item['name'], '_') + 1);
                $changeflag = $CJAX->call("AJAX.php?controller=addon_SEO&action=set_flag&cmd=edit&item_name={$item['name']}&item_number=$item_number&ivalue=$checkbox{$egg}&r_id=$this->registry_id");
            }

            if ($item['status'] === 2) {
                $flag = " disabled='disabled' checked='checked'";
                $tag_type = 'Required';
            } elseif ($item['status'] === 1) {
                $flag = " checked='checked'";
            }

            $row = ($row == 'row_color1') ? 'row_color2' : 'row_color1';
            $name = str_replace('_', ' ', $item['name']);
            $name = ucwords($name);


            $html .= "
		<tr>
			<td class='medium_font $row' style='width: 10px; text-align: center;'><input type='checkbox' id='checkbox_{$item['name']}'{$flag} value='1' {$changeflag} /></td>
			<td class='medium_font $row' style='width: 48px; text-align: center;'>$order</td>
			<td class='medium_font $row' style='width: 90%;'>{$title}</td>
			<td class='medium_font $row' style='white-space: nowrap;'>$tag_type</td>
		</tr>";
        }
        //Add extention
        $row = ($row == 'row_color1') ? 'row_color2' : 'row_color1';
        $ext_get = $CJAX->value('ext');
        $ext_onclick = $CJAX->call("AJAX.php?controller=addon_SEO&action=extension&r_id=$this->registry_id&ext=$ext_get");
        if (strlen($ext) == 0) {
            $ext = 'N/A';
        }
        $html .= "
		<tr>
			<td class='medium_font $row' style='text-align: center;'><input type='checkbox' name='na' disabled='disabled' checked='checked' /></td>
			<td class='medium_font $row' style='text-align: center;'>-</td>
			<td class='medium_font $row'>
				<span id='span_ext' style='display: none;'>
					<label>.<input type='text' id='ext' value='~~edit_me~~' size='5' /></label>
					" . geoHTML::addButton('Save', $ext_onclick, 1) . "
				</span>
				<span id='span_ext_text' style='border: 1px solid black; padding: 2px; background-color: white;' $ext_onclick>
					{$ext}
				</span>
			</td>
			<td class='medium_font $row' style='white-space: nowrap;'>File Extension</td>
		</tr>";


        $html .= "
	</tbody>
</table></div>";
        $info['html'] = $html;
        $info['parts'] = $parts;
        $info['ext'] = $ext;
        return $info;
    }


    public function core_filter_display_page($full_text)
    {
        trigger_error('DEBUG STATS URL_REWRITE: Doing url rewrite stuff.');

        if (!$this->isON()) {
            return $full_text;
        }
        if (defined('IN_GEO_RSS_FEED')) {
            //Used with RSS
            //once for <link>URL</link> - matches:
            //<link>any_text</link>
            return preg_replace('#\<link\>(.+)\</link\>#ie', '"<link>".\$this->rewriteUrl(stripslashes(\'$1\'))."</link>"', $full_text);
        }

        //NOTE:  base tag now added by main system, since we set addBaseTag to view vars

        //modify quotes and no-quotes differently, to allow spaces and > to be in
        //urls that do properly have quotes surrounding them.

        //once for single quotes - matches:
        //href='any url in quotes, even with newlines'
        $full_text = preg_replace_callback('/href\s?=(\')([^\']+)(\')\n?./i', function ($matches) {
            return $this->formatUrls($matches[2], $matches[0]);
        }, $full_text);

        //once for double quotes - matches:
        //href="any url in quotes, even with newlines"
        $full_text = preg_replace_callback('/href\s?=(")([^"]+)(")\n?./i', function ($matches) {
            return $this->formatUrls($matches[2], $matches[0]);
        }, $full_text);

        //once for no quotes - matches:
        //href=http://some_url_with_no_spaces> or href=some_url (space afterwords)
        $full_text = preg_replace_callback('/href=([^\'"\s>]+)(\s|>)/i', function ($matches) {
            return $this->formatUrls($matches[1], $matches[0]);
        }, $full_text);

        return $full_text;
    }

    public $isON;

    /**
     * checks to make sure SEO is turned on, and if set, also; checks the setting name to make
     * sure it is good too.
     *
     * @param string $setting_name
     * @return bool
     */
    public function isON($setting_name = null)
    {
        if (!$this->isON) {
            $current_reg = $this->registry_id;
            $this->registry_id = 'install';
            $this->isON = $this->get('settings');
            $this->registry_id = $current_reg;
        }
        if (!($this->isON['continue'] || $this->isON['type'] === 2)) {
            //turned off
            return false;
        }
        if ($setting_name !== null && (!isset($this->isON[$setting_name]) || !$this->isON[$setting_name])) {
            return false;
        }
        return true;
    }

    /**
     * Returns a "template" for a certain type of URL.
     *
     * @param string $template_type
     * @return string A "template" url, looks something like /(!CATEGORY_ID!)/cat/(!CATEGORY_TEXT!).html
     */
    public function getUrlTemplate($template_type = 'url_template')
    {
        if (!$template_type) {
            return false;
        }
        $template = $this->get($template_type);
        if ($this->_templatePrefix && $template) {
            $template = $this->_templatePrefix . $template;
        }

        return $template;
    }
    private $reg;
    /**
     * Works like revise, except it skips anything not really necessary like trimming,
     * and doesn't do fancy stuff like & to -and- (& would just be removed)
     *
     * @var string $string string to clean
     */
    public function cleanish($string)
    {
        $reg = geoAddon::getRegistry($this->name);

        if ($reg->replaceAccents) {
            //Convert accented chars to non-accented equivelents.
            $string = geoString::removeAccents($string);
        }

        $string = geoFilter::cleanUrlTitle($string, array(), false);

        if (!isset(self::$_useUnderscore)) {
            self::$_useUnderscore = $reg->useUnderscore;
        }

        if (self::$_useUnderscore) {
            //we're still using underscores, change - to underscores so it doesn't break, until
            //they get a chance to update their htaccess.
            $string = str_replace('-', '_', $string);
        }

        return $string;
    }

    public function revise($string, $allow = array(), $ignore_empty_string = false, $url_encode_titles = false)
    {
        $reg = geoAddon::getRegistry($this->name);

        $string = geoString::specialCharsDecode($string);
        if ($reg->replaceAccents) {
            //Convert accented chars to non-accented equivelents.
            $string = geoString::removeAccents($string);
        }

        //replace & with -and-
        //NOTE:  Not doing replacement using an array for search
        //and replace, because things need to be replaced in a
        //certain order.
        $search = '/[\s]*\&(amp\;)?[\s]*/';

        if (!isset($this->replaceAnd)) {
            $this->replaceAnd = $reg->get('replaceAnd', '-and-');
        }

        $replace = $this->replaceAnd;
        $string = preg_replace($search, $replace, $string);

        //do majority of cleaning here
        $string = geoFilter::cleanUrlTitle($string, $allow);

        if (strlen($string) == 0 && !$ignore_empty_string) {
            //make sure it is at least 1 char big
            $string = '-';
        }

        if (self::$_useUnderscore) {
            //we're still using underscores, change - to underscores so it doesn't break, until
            //they get a chance to update their htaccess.
            $string = str_replace('-', '_', $string);
        }
        if ($url_encode_titles) {
            $string = urlencode($string);
        }
        return $string;
    }

    public function formatUrls($string, $entireString)
    {
        trigger_error('DEBUG MOD_REWRITE: Formating url.  $string:' . $string . ' $entireString:' . $entireString);

        //strip slashes, since preg_replace adds slashes to " and ' there is no way to have both
        //not slashed.
        $string = stripslashes($string);
        $entireString = stripslashes($entireString);

        if (!isset(self::$_regUrl)) {
            //Since this is called over and over, cache the reg url, file name, and classified url so we
            //don't have to keep getting the db object over and over
            //get db
            $db = true;
            include GEO_BASE_DIR . 'get_common_vars.php';
            self::$_regUrl = $db->get_site_setting('registration_url');
            self::$_file = $db->get_site_setting('classifieds_file_name');
            self::$_url = $db->get_site_setting('classifieds_url');
            self::$_secUrl = $db->get_site_setting('classifieds_ssl_url');

            //also cache whether or not we use dashes or underscores
            $reg = geoAddon::getRegistry('SEO');
            self::$_useUnderscore = $reg->useUnderscore;
        }

        //do not modify if it matches...
        $ignore_if = array (
            'mailto:',
             //currently, no registration url's are SEO
            self::$_regUrl, //since you typically don't need search engine's to index the registration page.
            '&quot;', //if the link begins with an encoded quote, it's probably in a textarea
            '&apos;' //e.g. part of a listing description -- don't need to rewrite it
        );

        foreach ($ignore_if as $rule) {
            if (strpos($entireString, $rule) !== false) {
                //it matches something in the ignore array
                return $entireString;
            }
        }

        //Remove quotes from around the URL, (we will add them back)
        $string = trim($string, "\"");
        $string = trim($string, "'");

        $startUrl = 'href="';

        //Figure out what goes on the end
        $end = geoString::substr($entireString, -1);
        if ($end === ' ' || $end === '>') {
            //End has a space or > at the end of it
            $endUrl = '"' . $end;
        } else {
            $endUrl = '"';
        }

        if (strpos($string, '#') === 0) {
            //The first char in the url is #, don't do any re-writing
            return $startUrl . $string . $endUrl;
        }

        if (stristr($string, "javascript") !== false && stristr($string, "http") === false) {
            //javascript URL, treat differently
            if (!isset(self::$_baseHref)) {
                self::$_baseHref = str_replace(self::$_file, "", self::$_url);
            }

            if (stristr($string, "win('")) {
                //Window popup
                $string = str_replace("win('", "win('" . self::$_baseHref, $string);
            }
            if (stristr($string, "winimage('")) {
                //window image popup
                $string = str_replace("winimage('", "winimage('" . self::$_baseHref, $string);
            }

            return $startUrl . $string . $endUrl;
        }

        if (!stristr($string, self::$_file . "?")) {
            //there is no parameters in the URL, so don't do anything with it
            return $startUrl . $string . $endUrl;
        }

        //turn on debugging, un-comment line below, then say debug=whatever in url
        //It makes each URL produce an alert with a bunch of debug info about it
        //if (isset($_GET['debug'])) $debug = true;

        if (isset($debug) && $debug) {
            //if debug turned on, make it do an alert about debug info about that URL
            $startUrl .= "javascript:alert('SEO Debug Turned On, Debug Info: \n";
            $endUrl = "')" . $endUrl;
        } else {
            $debug = false;
        }
        $tpl = $this->rewriteUrl($string);
        if ($debug) {
            $tpl = __line__ . "registry($this->registry_id)----original($string)  new($tpl)";
        }
        //none of the templates applied, so don't modify it
        return $startUrl . $tpl . $endUrl;
    }
    private $_templatePrefix = '';

    public function rewriteUrl($url, $url_encode_titles = false, $forceNoSSL = false)
    {
        $reg = geoAddon::getRegistry('SEO');
        if (!isset(self::$_regUrl)) {
            //Since this is called over and over, cache the reg url, file name, and classified url so we
            //don't have to keep getting the db object over and over
            //get db
            $db = DataAccess::getInstance();
            self::$_regUrl = $db->get_site_setting('registration_url');
            self::$_file = $db->get_site_setting('classifieds_file_name');
            self::$_url = $db->get_site_setting('classifieds_url');
            self::$_secUrl = $db->get_site_setting('classifieds_ssl_url');

            //also cache whether or not we use dashes or underscores
            self::$_useUnderscore = $reg->useUnderscore;
        }


        if (strpos($url, '?') === false) {
            //no ? in URL so nothing to redirect to
            return $url;
        }
        //Now split up the URL into the different GET parameters
        $urlParts = explode(".php?", html_entity_decode($url));//be sure to un-do any w3c entity encoding in the urls.

        //Account for # references in the URL
        $anchor = '';
        if (isset($urlParts[1]) && strpos($urlParts[1], '#') !== false) {
            $aParts = explode('#', $urlParts[1]);
            if (count($aParts) == 2) {
                $anchor = '#' . $aParts[1];
                $urlParts[1] = $aParts[0];
            }
        }

        $urlGetVariables = explode('&', $urlParts[1]);
        $get = array();

        foreach ($urlGetVariables as $keyValuePair) {
            //Now each $keyValuePair = "key=value"
            $parts = explode('=', $keyValuePair);
            if ($parts[0] == 'c' && $parts[1] == 0) {
                //special case, ignore when c=0, treat it like c is not set
                continue;
            }

            //set $get["key"]="value"
            $get[$parts[0]] = $parts[1];
        }

        $this->_templatePrefix = '';
        //figure out if we need to set template prefix or not
        if (defined('IN_GEO_RSS_FEED')) {
            //If in RSS feed, ALWAYS use base href for template prefix
            $this->_templatePrefix = geoFilter::getBaseHref();
            //if this is a listing URL, over-write it with subdomain
            if (isset($get['a']) && $get['a'] == 2 && isset($get['b']) && $get['b'] !== 0 && count($get) == 2) {
                //This is a listing URL...  with no domain part to it...
                $this->setSubdomain($get['b']);
            }
        } elseif (strpos($url, 'http:') === 0 || strpos($url, 'https:') === 0) {
            //has full URL, see if current URL matches the base URL or not
            if (strpos($url, $this->getDomain(true)) !== false && isset($get['a']) && $get['a'] == 2 && isset($get['b']) && $get['b'] !== 0 && count($get) == 2) {
                //This is a listing URL...  make sure the subdomain is set correctly
                $this->setSubdomain($get['b']);
            }
            if (!$this->_templatePrefix && (strpos($url, self::$_url) === 0) || (strpos($url, self::$_secUrl) === 0)) {
                //for some reason, the "full URL" is included in the link.
                //Go ahead and preserve it, since we don't know if this is
                //in e-mail or somewhere like that, it may need to keep it
                if (strpos($url, 'https:') === 0) {
                    $this->_templatePrefix = dirname(self::$_secUrl) . '/';
                } else {
                    $this->_templatePrefix = dirname(self::$_url) . '/';
                }
            } elseif (!$this->_templatePrefix && strpos($url, geoFilter::getBaseHref()) === false) {
                if (strpos($url, $this->getDomain(true)) === false) {
                    //it seems this one should not be re-written
                    //echo "no rewrite: $url<br />";
                    return $url;
                }

                //OK replace the domain name part of base URL with one in URL
                $replaceDomain = preg_replace('|^https?://([^/]+)/.*$|', '$1', $url);

                $domain = preg_replace('|://([^/]+)|', '://' . $replaceDomain, geoFilter::getBaseHref());
                //echo "domain: $domain<br />";
                $this->_templatePrefix = $domain;
            }

            if ($forceNoSSL && strpos($this->_templatePrefix, 'https') === 0) {
                $this->_templatePrefix = 'http' . substr($this->_templatePrefix, 5);
            }
        } elseif (isset($get['a']) && $get['a'] == 2 && isset($get['b']) && $get['b'] !== 0 && count($get) == 2) {
            //This is a listing URL...  with no domain part to it...
            $this->setSubdomain($get['b']);
        }

        //trigger_error('DEBUG STATS: Re-writting URL '.$url);
        //Now re-write each URL according to criteria.

        if (isset($get['a']) && $get['a'] == 5 && isset($get['b']) && (count($get) == 2 || (isset($get['page']) && count($get) == 3))) {
            //a is 5, b is set and page may or may not be set.
            //?a=5&b=##

            //Only use page if it's more than 1
            $page = intval((isset($get['page']) && $get['page'] > 1) ? $get['page'] : 0);
            $p_reg = ($page) ? ' pages' : '';
            $this->registry_id = "category$p_reg";
            $tpl = $this->getUrlTemplate();
            if ($tpl) {
                $b = intval($get['b']);

                if (strpos($tpl, '(!CATEGORY_TITLE!)') !== false) {
                    $category_properties = geoCategory::getBasicInfo($b);
                    $current_category_name_to_use = ((strlen(trim($category_properties['seo_url_contents'])) > 0) ? $category_properties['seo_url_contents'] : $category_properties['category_name']);
                    if (($reg->includeParentCategoryName) && ($category_properties['parent_id'] != 0)) {
                        $parent_category_properties = geoCategory::getBasicInfo($category_properties['parent_id']);
                        $parent_category_name_to_use = ((strlen(trim($parent_category_properties['seo_url_contents'])) > 0) ? $parent_category_properties['seo_url_contents'] : $parent_category_properties['category_name']);

                        $category_name_to_use = $parent_category_name_to_use . "-" . $current_category_name_to_use;
                    } else {
                        $category_name_to_use = $current_category_name_to_use;
                    }
                    $tpl = str_replace('(!CATEGORY_TITLE!)', $this->revise($category_name_to_use, array(), false, $url_encode_titles), $tpl);
                }
                $tpl = str_replace('(!CATEGORY_ID!)', $b, $tpl);
                if ($page) {
                    $tpl = str_replace('(!PAGE_ID!)', $page, $tpl);
                }
                return $tpl . $anchor;
            }
        }

        if (isset($get['a']) && $get['a'] == 8 && isset($get['b']) && (count($get) == 2 || (isset($get['page']) && count($get) == 3))) {
            //Category featured ad pics / browse by pic 1st page
            //?a=8&b=##

            //only set page if greater than 1
            $page = intval((isset($get['page']) && $get['page'] > 1) ? $get['page'] : 0);
            $p_reg = ($page) ? ' pages' : '';
            $this->registry_id = "category featured listing pics$p_reg";
            $tpl = $this->getUrlTemplate();

            if ($tpl) {
                $b = intval($get['b']);

                if (strpos($tpl, '(!CATEGORY_TITLE!)') !== false) {
                    $category_properties = geoCategory::getBasicInfo($b);
                    $current_category_name_to_use = ((strlen(trim($category_properties['seo_url_contents'])) > 0) ? $category_properties['seo_url_contents'] : $category_properties['category_name']);
                    if (($reg->includeParentCategoryName) && ($category_properties['parent_id'] != 0)) {
                        $parent_category_properties = geoCategory::getBasicInfo($category_properties['parent_id']);
                        $parent_category_name_to_use = ((strlen(trim($parent_category_properties['seo_url_contents'])) > 0) ? $parent_category_properties['seo_url_contents'] : $parent_category_properties['category_name']);
                        $category_name_to_use = $parent_category_name_to_use . "-" . $current_category_name_to_use;
                    } else {
                        $category_name_to_use = $current_category_name_to_use;
                    }
                    $tpl = str_replace('(!CATEGORY_TITLE!)', $this->revise($category_name_to_use, array(), false, $url_encode_titles), $tpl);
                }

                $tpl = str_replace('(!CATEGORY_ID!)', $b, $tpl);

                if ($page) {
                    $tpl = str_replace('(!PAGE_ID!)', $page, $tpl);
                }
                return $tpl . $anchor;
            }
        }

        if (isset($get['a']) && $get['a'] == 2 && isset($get['b']) && $get['b'] !== 0 && count($get) == 2) {
            //Display listing URL
            //?a=2&b=##
            $this->registry_id = 'listings';
            $tpl = $this->getUrlTemplate();

            if ($tpl) {
                //get the listing object, so we can get the title for the listing
                $b = intval($get['b']);
                $listing = geoListing::getListing($b, false);
                if (is_object($listing)) {
                    $listing_title = geoString::fromDB($listing->title);
                    $category_id = $listing->category;
                    if (strpos($tpl, '(!CATEGORY_TITLE!)') !== false) {
                        $category_properties = geoCategory::getBasicInfo($category_id);
                        $current_category_name_to_use = ((strlen(trim($category_properties['seo_url_contents'])) > 0) ? $category_properties['seo_url_contents'] : $category_properties['category_name']);
                        if (($reg->includeParentCategoryName) && ($category_properties['parent_id'] != 0)) {
                            $parent_category_properties = geoCategory::getBasicInfo($category_properties['parent_id']);
                            $parent_category_name_to_use = ((strlen(trim($parent_category_properties['seo_url_contents'])) > 0) ? $parent_category_properties['seo_url_contents'] : $parent_category_properties['category_name']);
                            $category_name_to_use = $parent_category_name_to_use . "-" . $current_category_name_to_use;
                        } else {
                            $category_name_to_use = $current_category_name_to_use;
                        }
                        $tpl = str_replace('(!CATEGORY_TITLE!)', $this->revise($category_name_to_use, array(), false, $url_encode_titles), $tpl);
                    }

                    //replace listing id, category id, listing title, and category title
                    $search = array('(!LISTING_ID!)','(!LISTING_TITLE!)','(!CATEGORY_ID!)');
                    $replace = array ($b, $this->revise($listing_title, array(), false, $url_encode_titles),$category_id);
                    $tpl = str_replace($search, $replace, $tpl);

                    return $tpl . $anchor;
                }
            }
        }

        if (isset($get['a']) && $get['a'] == 8 && isset($get['page']) && count($get) == 2) {
            //Featured listings URL nth page
            //?a=8&page=n
            $this->registry_id = 'featured listings page';
            $tpl = $this->getUrlTemplate();

            if ($tpl) {
                $tpl = str_replace('(!PAGE_ID!)', $get['page'], $tpl);
                return $tpl . $anchor;
            }
        }

        if (
            isset($get['a']) && $get['a'] == 11 && isset($get['d']) && intval($get['d']) > 0 && intval($get['d']) < 5
            && (!isset($get['c']) || $get['c'] == 65) && ((isset($get['page']) && count($get) <= 5) || count($get) <= 4)
        ) {
            //if a=11 AND d is 1-4 AND (c=65 or c not set) AND (page is set and get count <=5) or get count <= 4
            //Newest listings last n amount of time, default sort order
            //?a=11&b=##&c=65&d=## (d is 1-4)

            /*
             * D values:
             * d=1 : 1 week
             * d=2 : 2 weeks
             * d=3 : 3 weeks
             * d=4 : 1 day
             */

            $b = intval($get['b']);
            $d = intval($get['d']);

            $Ds = array (1 => '1week',2 => '2weeks', 3 => '3weeks', 4 => '1day');

            //Only use page # if page is more than 1
            $page = intval((isset($get['page']) && $get['page'] > 1) ? $get['page'] : 0);

            //first figure out which registry to use
            $page_reg = ($page) ? ' pages' : '';
            $this->registry_id = "category newest {$Ds[$d]}{$page_reg}";
            //get the template for that URL
            $tpl = $this->getUrlTemplate();

            if ($tpl) {
                $category_properties = geoCategory::getBasicInfo($b);
                $current_category_name_to_use = ((strlen(trim($category_properties['seo_url_contents'])) > 0) ? $category_properties['seo_url_contents'] : $category_properties['category_name']);
                if ($category_properties !== false) {
                    if (($reg->includeParentCategoryName) && ($category_properties['parent_id'] != 0)) {
                        $parent_category_properties = geoCategory::getBasicInfo($category_properties['parent_id']);
                        $parent_category_name_to_use = ((strlen(trim($parent_category_properties['seo_url_contents'])) > 0) ? $parent_category_properties['seo_url_contents'] : $parent_category_properties['category_name']);
                        $category_name_to_use = $parent_category_name_to_use . "-" . $current_category_name_to_use;
                    } else {
                        $category_name_to_use = $current_category_name_to_use;
                    }
                    $tpl = str_replace('(!CATEGORY_ID!)', $b, $tpl);
                    $tpl = str_replace('(!CATEGORY_TITLE!)', $this->revise($category_name_to_use, array(), false, $url_encode_titles), $tpl);
                    if ($page) {
                        $tpl = str_replace('(!PAGE_ID!)', $page, $tpl);
                    }
                    return $tpl . $anchor;
                }
            }
        }


        if (isset($get['a']) && $get['a'] == 14 && isset($get['b']) && count($get) == 2) {
            //Print View Listing URL
            //?a=14&b=##
            $this->registry_id = 'print item';
            $tpl = $this->getUrlTemplate('url_template');

            if ($tpl) {
                $tpl = str_replace('(!ITEM_ID!)', $get['b'], $tpl);
                return $tpl . $anchor;
            }
        }

        if (isset($get['a']) && $get['a'] == 15 && isset($get['b'])  && count($get) == 2) {
            //Image browsing URL
            //?a=15&b=##
            $this->registry_id = 'images browsing';
            $tpl = $this->getUrlTemplate('url_template');

            if ($tpl) {
                $tpl = str_replace('(!IMAGE_ID!)', $get['b'], $tpl);
                return $tpl . $anchor;
            }
        }

        if (isset($get['a']) && $get['a'] == 6 && isset($get['b']) && (count($get) == 2 || (isset($get['page']) && count($get) == 3))) {
            //Seller's other listings, 1st page
            //?a=6&b=##
            //Seller's other listings, nth page
            //?a=6&b=##&page=n

            //Only use page # if page is more than 1
            $page = intval((isset($get['page']) && $get['page'] > 1) ? $get['page'] : 0);

            $p_reg = ($page) ? ' page' : '';
            $this->registry_id = "other seller$p_reg";

            $tpl = $this->getUrlTemplate('url_template');

            if ($tpl) {
                $tpl = str_replace('(!SELLER_ID!)', $get['b'], $tpl);
                $tpl = str_replace('(!PAGE_ID!)', $page, $tpl);

                return $tpl . $anchor;
            }
        }

        if (isset($get['a']) && $get['a'] == 'tag' && isset($get['tag']) && (count($get) == 2 || (isset($get['page']) && count($get) == 3))) {
            //tag browsing
            //?a=tag&tag=VAL

            //Only use page if it's more than 1
            $page = intval((isset($get['page']) && $get['page'] > 1) ? $get['page'] : 0);
            $p_reg = ($page) ? ' pages' : '';
            $this->registry_id = "browse tag$p_reg";
            $tpl = $this->getUrlTemplate();
            if ($tpl) {
                $tag = geoFilter::cleanListingTag($get['tag']);
                //prevent 404
                if (!strlen($tag)) {
                    $tag = '-';
                }
                $tpl = str_replace('(!TAG_NAME!)', $tag, $tpl);
                if ($page) {
                    $tpl = str_replace('(!PAGE_ID!)', $page, $tpl);
                }
                return $tpl . $anchor;
            }
        }

        /**
         * Allow integration with other addons, to do URL re-writting, so the
         * other addons don't have to re-invent the wheel.
         *
         * @param array $get An array of "URL Parameters", for example an array (a=>2, b=>4)
         *  for the url index.php?a=2&b=4
         * @param string $url The url string as it was passed into the calling method.
         * @return mixed geoAddon::NOT_NULL - Return null if addon does not care
         *  to re-write the given URL.  Or return the re-written URL if the addon
         *  DOES want to re-write the URL.
         */
        $addonCall = geoAddon::triggerDisplay('addon_SEO_rewriteUrl', array('get' => $get, 'url' => $url, 'anchor' => $anchor, 'url_encode_titles' => $url_encode_titles), geoAddon::NOT_NULL);
        if ($addonCall !== null && strlen(trim($addonCall)) > 0) {
            //addon decided it wanted to re-write the URL.
            return $addonCall;
        }

        //not found, return original url
        return $url;
    }

    private function setSubdomain($listingId)
    {
        $newUrl = 'http://';
        $listingId = (int)$listingId;
        if (!$listingId) {
            return;
        }
        $geoReg = geoAddon::getRegistry('geographic_navigation');
        if ($geoReg && $geoReg->forceSubdomainListing) {
            //make sure subdomain matches the listing...
            $db = DataAccess::getInstance();
            $regions = geoRegion::getRegionsForListing($listingId);
            $getSub = $db->Prepare("SELECT `unique_name` FROM " . geoTables::region . " WHERE `id` = ?");
            $sub = '';
            for ($i = geoRegion::getLowestLevel(); $i >= 1; $i--) {
                $sub = $db->GetOne($getSub, array($regions[$i]));
                if (strlen($sub) > 0) {
                    $newUrl .= $sub . '.';
                    break;
                }
            }
            //now figure out the actual domain part
            $domain = preg_replace("|^https?://(www\.)?|", '', self::$_url);
            //kill the index.php part
            $domain = dirname($domain) . '/';
            $newUrl .= $domain;
            $this->_templatePrefix = $newUrl;
        }
    }

    public function getDomain($includeFolder = false)
    {
        //gets the domain
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $site = $db->get_site_setting('classifieds_url');
        $site = preg_replace("|^https?://(www\.)?|", '', $site);
        //clear off the end part
        if (!$includeFolder) {
            $site = preg_replace("/\/.*$/", '', $site);
        } else {
            $site = dirname($site);
        }

        return $site;
    }

    public function RegisterSettings($specify = false, $onlyAdd = false)
    {

        $settings = array();

        if (!$onlyAdd && $specify) {
            $this->registry_id = 'settings';
            $setting = $this->get('items');
            $settings[$this->temp_registry_id] = $setting[$this->temp_registry_id];
        } else {
            /**
             * status tips;
             * meanings
             * 0 = disabled
             * 1 = enabled
             * 2 = required
             */
            $regex_title = self::REGEX_TITLE;
            $regex_number = self::REGEX_NUMBER;
            //setting up the properties for each setting

            //NOTE: do not rename the key of these or will brake if any current urls are currently working!!
            $settings = array(

            //TODO: Move this to the actual regions filter addon,
            //so that its only added if they have both the regions filter
            //and the SEO addon.

                //Regions filter
                /*
            'regions_filter'
            => array (
                'items' =>
                    array (
                    'region_id',
                    'sub_region_id',
                    'custom_text_1'
                ),
                'title' => array (
                    'region_id' => '(!REGION_ID!)',
                    'sub_region_id' => '(!SUB_REGION_ID!)',
                    'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                ),
                'status'
                    =>array('region_id' => 2,'sub_region_id' => 2,'custom_text_1' => 1),
                'order'
                    =>array('region_id' => 2,'sub_region_id' =>3,'custom_text_1'=>1),
                'text'
                    =>array('custom_text_1'=>'regions'),
                'type'
                     =>array('region_id'=>'required','sub_region_id' => 'required'),
                'regex'
                    =>array('region_id'=>$regex_title,'sub_region_id' => $regex_title),
                'regexhandler' => 'region=(!region_id!)&sub_region=(!sub_region_id!)', //this is a set up for the htacccess , a=5 means its a category page in the url
                                         // (!REGEX_GROUP!) will be the group order
            ),
            */

            //Category
            ###/listings/category([0-9]*)\.htm$ $1.php?a=5&b=$2 [L]
                'category'
                => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'custom_text_1'
                    ),
                        'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        ),
                        'status'
                            => array('category_id' => 2,'custom_text_1' => 1,'category_title' => 1),
                        'order'
                            => array('custom_text_1' => 1,'category_id' => 2,'category_title' => 3),
                        'text'
                            => array('custom_text_1' => 'category'),
                        'type'
                             => array('custom_text_1' => 'custom_text','category_id' => 'required'),
                        'regex'
                            => array('category_id' => $regex_number,'category_title' => $regex_title),
                        'regexhandler' => 'a=5&b=(!category_id!)', //this is a set up for the htacccess , a=5 means its a category page in the url
                                             // (!REGEX_GROUP!) will be the group order
                ),

                #listings/category([0-9]*)/page([0-9]*)\.htm$ $1.php?a=5&b=$2&page=$3 [L]
                //category pages
                'category pages' //
                    => array(
                        'items' =>
                        array(
                        'category_id',
                        'category_title',
                        'page_id',
                        'custom_text_1',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)'
                    ),
                    'status'
                        => array('category_id' => 2,'category_title' => 1, 'page_id' => 2,'custom_text_1' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'category_id' => 2,'category_title' => 3, 'page_id' => 4)
                    ,'text'
                        => array('custom_text_1' => 'category')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','page_id' => 'required','category_id' => 'required')
                    ,'regex'
                        => array('category_id' => $regex_number,'category_title' => $regex_title, 'page_id' => $regex_number)
                    ,'regexhandler' => 'a=5&b=(!category_id!)&page=(!page_id!)'
                ),

                //display a featured ad pics in this category
                //b will contain the category id [ browse by pic ]
                ##/featured/category([0-9]*)\.htm$ $1.php?a=8&b=$2 [L]
                'category featured listing pics' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('category_id' => 2,'category_title' => 1,'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4)
                        ,'text'
                            => array('custom_text_1' => 'category','custom_text_2' => 'featured')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','category_id' => 'required')
                        ,'regex'
                            => array('category_id' => $regex_number, 'category_title' => $regex_title)
                        ,'regexhandler' => 'a=8&b=(!category_id!)'
                ),
                // setting up listing URL.
                // all fields are options with exception main_item_id and item_title


                #-/featured/category([0-9]*)/page([0-9]*)\.htm$ $1.php?a=8&b=$2&page=$3 [L]
                'category featured listing pics pages' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'page_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('category_id' => 2,'category_title' => 1,'page_id' => 2, 'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4, 'page_id' => 5)
                        ,'text'
                            => array('custom_text_1' => 'category','custom_text_2' => 'featured')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','custom_text_2' => 'custom_text', 'category_id' => 'required', 'page_id' => 'required')
                        ,'regex'
                            => array('category_id' => $regex_number, 'category_title' => $regex_title, 'page_id' => $regex_number)
                        ,'regexhandler' => 'a=8&b=(!category_id!)&page=(!page_id!)'
                ),
                #listings
                #/listings/page([0-9]*)\.htm$ $1.php?a=2&b=$2 [L]
                'listings' => array(
                    'items' =>
                    array(
                    'listing_id',
                    'listing_title',
                    'category_id',
                    'category_title',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'listing_id' => '(!LISTING_ID!)',
                        'listing_title' => '(!LISTING_TITLE!)',
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('listing_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1, 'listing_title' => 1, 'category_id' => 1, 'category_title' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'category_id' => 2, 'category_title' => 3,'custom_text_2' => 4, 'listing_id' => 5,'listing_title' => 6)
                        ,'text'
                            => array('custom_text_1' => 'category', 'custom_text_2' => 'listings')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','listing_id' => 'required')
                        ,'regex'
                            => array('listing_id' => $regex_number,'listing_title' => $regex_title,'category_id' => $regex_number, 'category_title' => $regex_title)
                        ,'regexhandler' => 'a=2&b=(!listing_id!)'
                ),

                #/featured/page([0-9]*)\.htm$ $1.php?a=8&page=$2 [L]
                'featured listings page' => array(
                    'items' =>
                    array(
                    'page_id',
                    'custom_text_1',
                    ),
                    'title' => array(
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)'
                    ),
                    'status'
                        => array('page_id' => 2,'custom_text_1' => 1,)
                    ,'order'
                        => array('custom_text_1' => 1,'page_id' => 2)
                    ,'text'
                        => array('custom_text_1' => 'Featured_Listings')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','page_id' => 'required')
                    ,'regex'
                        => array('page_id' => $regex_number)
                    ,'regexhandler' => 'a=8&page=(!page_id!)'
                ),
                //1 day
                'category newest 1day' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'
                    ),
                    'status'
                        => array('category_id' => 2, 'category_title' => 1, 'custom_text_1' => 1,'custom_text_2' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4)
                    ,'text'
                        => array('custom_text_1' => 'new','custom_text_2' => '1_day')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','custom_text_2' => 'custom_text', 'category_id' => 'required')
                    ,'regex'
                        => array('category_id' => $regex_number,'category_title' => $regex_title)
                    ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=4'
                ),

                //1 day pages
                'category newest 1day pages' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'page_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('category_id' => 2, 'category_title' => 1, 'page_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4, 'page_id' => 5)
                        ,'text'
                            => array('custom_text_1' => 'new','custom_text_2' => '1_day')
                        ,'type'
                            => array('category_id' => 'required','page_id' => 'required','custom_text_1' => 'custom_text','custom_text_2' => 'custom_text')
                        ,'regex'
                            => array('category_id' => $regex_number, 'category_title' => $regex_title, 'page_id' => $regex_number)
                        ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=4&page=(!page_id!)'
                ),
                //1 week
                'category newest 1week' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'
                    ),
                    'status'
                        => array('category_id' => 2, 'category_title' => 1, 'custom_text_1' => 1,'custom_text_2' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4)
                    ,'text'
                        => array('custom_text_1' => 'new','custom_text_2' => '1_week')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','category_id' => 'required')
                    ,'regex'
                        => array('category_id' => $regex_number,'category_title' => $regex_title)
                    ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=1'
                ),
                //1 week pages
                'category newest 1week pages' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'page_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('category_id' => 2, 'category_title' => 1, 'page_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4, 'page_id' => 5)
                        ,'text'
                            => array('custom_text_1' => 'new','custom_text_2' => '1_week')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','category_id' => 'required','page_id' => 'required','custom_text_3' => 'custom_text')
                        ,'regex'
                            => array('category_id' => $regex_number, 'category_title' => $regex_title, 'page_id' => $regex_number)
                        ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=1&page=(!page_id!)'
                ),
                //2 weeks
                'category newest 2weeks' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'
                    ),
                    'status'
                        => array('category_id' => 2, 'category_title' => 1, 'custom_text_1' => 1,'custom_text_2' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4)
                    ,'text'
                        => array('custom_text_1' => 'new','custom_text_2' => '2_weeks')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','category_id' => 'required')
                    ,'regex'
                        => array('category_id' => $regex_number,'category_title' => $regex_title)
                    ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=2'
                ),
                //2 weeks pages
                'category newest 2weeks pages' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'page_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('category_id' => 2, 'category_title' => 1, 'page_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4, 'page_id' => 5)
                        ,'text'
                            => array('custom_text_1' => 'new','custom_text_2' => '2_weeks')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','category_id' => 'required','page_id' => 'required')
                        ,'regex'
                            => array('category_id' => $regex_number, 'category_title' => $regex_title, 'page_id' => $regex_number)
                        ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=2&page=(!page_id!)'
                ),

                //3 weeks
                'category newest 3weeks' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'
                    ),
                    'status'
                        => array('category_id' => 2, 'category_title' => 1, 'custom_text_1' => 1,'custom_text_2' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4)
                    ,'text'
                        => array('custom_text_1' => 'new','custom_text_2' => '3_weeks')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','category_id' => 'required')
                    ,'regex'
                        => array('category_id' => $regex_number,'category_title' => $regex_title)
                    ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=3'
                ),
                //2 weeks pages
                'category newest 3weeks pages' => array(
                    'items' =>
                    array(
                    'category_id',
                    'category_title',
                    'page_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('category_id' => 2, 'category_title' => 1, 'page_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'custom_text_2' => 2,'category_id' => 3,'category_title' => 4, 'page_id' => 5)
                        ,'text'
                            => array('custom_text_1' => 'new','custom_text_2' => '3_weeks')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','category_id' => 'required','page_id' => 'required','custom_text_3' => 'custom_text')
                        ,'regex'
                            => array('category_id' => $regex_number, 'category_title' => $regex_title, 'page_id' => $regex_number)
                        ,'regexhandler' => 'a=11&b=(!category_id!)&c=65&d=3&page=(!page_id!)'
                ),

                #print item
                #/print/item([0-9]*)\.htm$ $1.php?a=14&b=$2 [L]
                'print item' => array(
                    'items' =>
                    array(
                    'item_id',
                    'custom_text_1',
                    'custom_text_2'
                    ),
                    'title' => array(
                        'item_id' => '(!ITEM_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'),
                        'status'
                            => array('item_id' => 2, 'custom_text_1' => 1,'custom_text_2' => 1)
                        ,'order'
                            => array('item_id' => 2,'custom_text_1' => 1,'custom_text_2' => 3)
                        ,'text'
                            => array('custom_text_1' => 'print','custom_text_2' => 'item')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','item_id' => 'required')
                        ,'regex'
                            => array('item_id' => $regex_number)
                        ,'regexhandler' => 'a=14&b=(!item_id!)'

                ),

                #image browsing
                #/images/item([0-9]*)\.htm$ $1.php?a=15&b=$2 [L]
                'images browsing' => array(
                    'items' =>
                    array(
                    'image_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'image_id' => '(!IMAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'
                    ),
                    'status'
                        => array('image_id' => 2, 'custom_text_1' => 1,'custom_text_2' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'image_id' => 2,'custom_text_2' => 3)
                    ,'text'
                        => array('custom_text_1' => 'image','custom_text_2' => 'item')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','image_id' => 'required')
                    ,'regex'
                        => array('image_id' => $regex_number)
                    ,'regexhandler' => 'a=15&b=(!image_id!)'
                ),

                #/other/seller([0-9]*)\.htm$ $1.php?a=6&b=$2 [L]
                'other seller' => array(
                    'items' =>
                    array(
                    'seller_id',
                    'custom_text_1',
                    'custom_text_2',
                    ),
                    'title' => array(
                        'seller_id' => '(!SELLER_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)'
                    ),
                    'status'
                        => array('seller_id' => 2, 'custom_text_1' => 1,'custom_text_2' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'seller_id' => 2,'custom_text_2' => 3)
                    ,'text'
                        => array('custom_text_1' => 'other','custom_text_2' => 'seller')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','seller_id' => 'required')
                    ,'regex'
                        => array('seller_id' => $regex_number)
                    ,'regexhandler' => 'a=6&b=(!seller_id!)'
                ),

                #other seller pages
                #/other/seller([0-9]*)/page([0-9]*)\.htm$ $1.php?a=6&b=$2&page=$3 [L]
                'other seller page' => array(
                    'items' =>
                    array(
                    'seller_id',
                    'page_id',
                    'custom_text_1',
                    'custom_text_2',
                    'custom_text_3',
                    ),
                    'title' => array(
                        'seller_id' => '(!SELLER_ID!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)',
                        'custom_text_3' => '(!CUSTOM_TEXT_3!)'),
                        'status'
                            => array('seller_id' => 2, 'page_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1, 'custom_text_3' => 1)
                        ,'order'
                            => array('custom_text_1' => 1,'seller_id' => 2,'custom_text_2' => 3,'custom_text_3' => 4,'page_id' => 5)
                        ,'text'
                            => array('custom_text_1' => 'other','custom_text_2' => 'seller','custom_text_3' => 'page')
                        ,'type'
                            => array('custom_text_1' => 'custom_text','seller_id' => 'required','page_id' => 'required','custom_text_3' => 'custom_text')
                        ,'regex'
                            => array('seller_id' => $regex_number,'page_id' => $regex_number)
                        ,'regexhandler' => 'a=6&b=(!seller_id!)&page=(!page_id!)'
                ),

                //browse Tag
                ### ?a=tag&tag=value
                'browse tag' => array(
                    'items' =>
                    array(
                    'tag_name',
                    'custom_text_1'
                    ),
                        'title' => array(
                        'tag_name' => '(!TAG_NAME!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        ),
                        'status'
                            => array('tag_name' => 2,'custom_text_1' => 1),
                        'order'
                            => array('custom_text_1' => 1,'tag_name' => 2),
                        'text'
                            => array('custom_text_1' => 'tag'),
                        'type'
                             => array('custom_text_1' => 'custom_text','tag_name' => 'required'),
                        'regex'
                            => array('tag_name' => $regex_title),
                        'regexhandler' => 'a=tag&tag=(!tag_name!)', //this is a set up for the htacccess
                ),

                //browse Tag pages
                ### ?a=tag&tag=value&page=##
                'browse tag pages' //
                    => array(
                        'items' =>
                        array(
                        'tag_name',
                        'page_id',
                        'custom_text_1',
                    ),
                    'title' => array(
                        'tag_name' => '(!TAG_NAME!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)'
                    ),
                    'status'
                        => array('tag_name' => 2,'page_id' => 2,'custom_text_1' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'tag_name' => 2,'page_id' => 3)
                    ,'text'
                        => array('custom_text_1' => 'tag')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','page_id' => 'required','tag_name' => 'required')
                    ,'regex'
                        => array('tag_name' => $regex_title, 'page_id' => $regex_number)
                    ,'regexhandler' => 'a=tag&tag=(!tag_name!)&page=(!page_id!)'
                ),
            );
        }
        //speciy above and it will dinacly create the items for you
        $item_order = $item_title = $item_name = $item_status = $item_type = $item_desc = array();
        $item_custom_text = $item_regex = $item_regexhandler = array();

        if (!$settings) {
            return false;
        }
        $this->addSeoUrls($settings, $onlyAdd);

        //reset setting
        if (!$onlyAdd && $specify) {
            geoAdmin::m('Settings have been reset to factory defaults.', geoAdmin::SUCCESS, true, 0);
            $CJAX = geoCJAX::getInstance();
            $CJAX->wait(4);
            $CJAX->location();
            include GEO_BASE_DIR . 'app_bottom.php';
            exit();
        } elseif (!$onlyAdd) {
            //fresh install
            $this->registry_id = 'settings';
            $this->set('items', $settings);
            $this->save();
        }
        return true;
    }

    /**
     * Adds URL's to the array of URL settings, see RegisterSettings() for format
     * of arrays.
     *
     * @param array $urlSettings
     * @param bool $onlyAdd If true, will only add new url settings, and not reset
     *  existing ones.
     * @return int The number of new URLs that were added.
     */
    public function addSeoUrls($urlSettings, $onlyAdd = false)
    {
        if (!$urlSettings || !is_array($urlSettings)) {
            return false;
        }

        $urlCount = 0;
        foreach ($urlSettings as $set_setting => $values) {
            $item_order = $item_title = $item_name = $item_status = $item_type = $item_desc = array();
            $item_custom_text = $item_regex = $item_regexhandler = array();

            $this->registry_id = $set_setting;
            if ($onlyAdd && $this->get('regex') !== false) {
                //only adding new stuff, and a regex was just found

                continue;
            }

            foreach ($values['items'] as $item) {
                //alert("ITEM: $item  type:".$values['type'][$item]);
                if (!$values['type'][$item] && strpos($item, 'custom_text') !== false ||  $values['text'][$item]) {
                    $item_custom_text[$item] = $values['text'][$item];
                }
                if ($values['order'][$item]) {
                    $item_order[$item] =  $values['order'][$item];
                } else {
                    $o++;
                    $item_order[$item] =  $o;
                }

                $item_title[$item] =  $values['title'][$item];

                if ($values['name'][$item]) {
                    $item_name[$item] = $values['name'][$item];
                } else {
                    $item_name[$item] = $item;
                }

                if ($values['status'][$item]) {
                    $item_status[$item] = $values['status'][$item];
                } else {
                    $item_status[$item] = 0;
                }

                if (!$values['type'][$item] && strpos($item, 'custom_text') !== false) {
                    $item_type[$item] = 'custom_text';
                } else {
                    $item_type[$item] = $values['type'][$item];
                }

                $item_desc[$item] = $values['desc'][$item];

                $item_regex[$item] = $values['regex'][$item];

                $item_regexhandler = $values['regexhandler'];


                $item_extension  = $values['extension'];
                if (!isset($values['extension'])) {
                    $item_extension  = '.html';
                }
            }
            $this->registry_id = $set_setting;

            $this->set('regex', $item_regex);
            $this->set('regexhandler', $item_regexhandler);

            $this->set('type', $item_type);
            $this->set('order', $item_order);
            $this->set('name', $item_name);
            $this->set('status', $item_status);
            $this->set('desc', $item_desc);
            $this->set('title', $item_title);
            $this->set('custom_text', $item_custom_text);
            $this->set('extension', $item_extension);
            $urlCount++;
        }
        if ($urlCount) {
            //make sure items is updated
            $items = $this->get('items', 'settings');
            $items = (is_array($items)) ? $items : array();
            $items = array_merge($items, $urlSettings);
            $this->set('items', $items, 'settings');
            $this->save();
        }
        return $urlCount;
    }

    function resetUpgradeSettings()
    {
        $db = DataAccess::getInstance();

        $filename = $db->get_site_setting('classifieds_file_name');
        $index = str_replace('.php', '', $filename);

        $setting = "$index/listings/category(!CATEGORY_ID!).htm";
        $this->registry_id = 'category';
        $this->set('url_template', $setting);
        $this->save();

        #listings/category([0-9]*)/page([0-9]*)\.htm$ $1.php?a=5&b=$2&page=$3 [L]
        $setting = "$index/listings/category(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'Category pages';
        $this->set('url_template', $setting);
        $this->save();

        ##/featured/category([0-9]*)\.htm$ $1.php?a=8&b=$2 [L]
        $setting = "$index/featured/category(!CATEGORY_ID!).htm";
        $this->registry_id = 'Category featured ad pics';
        $this->set('url_template', $setting);
        $this->save();

        #-/featured/category([0-9]*)/page([0-9]*)\.htm$ $1.php?a=8&b=$2&page=$3 [L]
        $setting = "$index/featured/category(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'Category featured ad pics pages';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/page([0-9]*)\.htm$ $1.php?a=2&b=$2 [L]
        $setting = "$index/listings/page(!LISTING_ID!).htm";
        $this->registry_id = 'listings';
        $this->set('url_template', $setting);
        $this->save();

        #/featured/page([0-9]*)\.htm$ $1.php?a=8&page=$2 [L]
        $setting = "$index/featured/page(!PAGE_ID!).htm";
        $this->registry_id = 'featured listings page';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/1day([0-9]*)\.htm$ $1.php?a=11&b=$2&c=4 [L]
        $setting = "$index/listings/1day(!CATEGORY_ID!).htm";
        $this->registry_id = 'category newest 1day';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/1day([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=4&page=$3 [L]
        $setting = "$index/listings/1day(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'category newest 1day pages';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/1week([0-9]*)\.htm$ $1.php?a=11&b=$2&c=1 [L]
        $setting = "$index/listings/1week(!CATEGORY_ID!).htm";
        $this->registry_id = 'category newest 1week';
        $this->set('url_template', $setting);
        $this->save();


        #listings/1week([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=1&page=$3 [L]
        $setting = "$index/listings/1week(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'category newest 1week pages';
        $this->set('url_template', $setting);
        $this->save();


        #\listings/2weeks([0-9]*)\.htm$ $1.php?a=11&b=$2&c=2 [L]
        $setting = "$index/listings/2weeks(!CATEGORY_ID!).htm";
        $this->registry_id = 'category newest 2weeks';
        $this->set('url_template', $setting);
        $this->save();


        #/listings/2weeks([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=2&page=$3 [L]
        $setting = "$index/listings/2weeks(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'category newest 2weeks pages';
        $this->set('url_template', $setting);
        $this->save();

        #/listings/3weeks([0-9]*)\.htm$ $1.php?a=11&b=$2&c=3 [L]
        $setting = "$index/listings/3weeks(!CATEGORY_ID!).htm";
        $this->registry_id = 'category newest 3weeks';
        $this->set('url_template', $setting);
        $this->save();

        ##/listings/3weeks([0-9]*)/page([0-9]*)\.htm$ $1.php?a=11&b=$2&c=3&page=$3 [L]
        $setting = "$index/listings/3weeks(!CATEGORY_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'category newest 3weeks pages';
        $this->set('url_template', $setting);
        $this->save();

        #/print/item([0-9]*)\.htm$ $1.php?a=14&b=$2 [L]
        $setting = "$index/print/item(!ITEM_ID!).htm";
        $this->registry_id = 'print item';
        $this->set('url_template', $setting);
        $this->save();


        #/images/item([0-9]*)\.htm$ $1.php?a=15&b=$2 [L]
        $setting = "$index/images/item(!IMAGE_ID!).htm";
        $this->registry_id = 'images browsing';
        $this->set('url_template', $setting);
        $this->save();


        #/other/seller([0-9]*)\.htm$ $1.php?a=6&b=$2 [L]
        $setting = "$index/other/seller(!SELLER_ID!).htm";
        $this->registry_id = 'other seller';
        $this->set('url_template', $setting);
        $this->save();


        #/other/seller([0-9]*)/page([0-9]*)\.htm$ $1.php?a=6&b=$2&page=$3 [L]
        $setting = "$index/other/seller(!SELLER_ID!)/page(!PAGE_ID!).htm";
        $this->registry_id = 'other seller page';
        $this->set('url_template', $setting);
        $this->save();
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

    public function core_rewrite_single_url($vars)
    {
        $url = $vars['url'];
        $forceNoSSL = $vars['forceNoSSL'];
        return $this->isON() ? $this->rewriteUrl($url, false, $forceNoSSL) : $url;
    }
}
