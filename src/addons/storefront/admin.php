<?php

//addons/storefront/admin.php

# storefront Addon

class addon_storefront_admin extends addon_storefront_info
{
    var $db;
    var $site_configuration_table = "geodesic_classifieds_configuration";
    var $classified_categories_languages_table = "geodesic_categories_languages";
    var $categories_table = "geodesic_categories";
    var $classified_categories_table = "geodesic_categories";
    var $color;
    var $sitedefault;
    var $has_option;
    var $tables;
    public function init_pages()
    {
        menu_page::addonAddPage('addon_storefront_main', '', 'Settings', 'storefront', 'fa-shopping-cart');
        menu_page::addonAddPage('display_storefront_link', '', 'Display Storefront Link', 'storefront', 'fa-shopping-cart');
        menu_page::addonAddPage('store_front_fields_to_use', '', 'Fields to Use', 'storefront', 'fa-shopping-cart');
        menu_page::addonAddPage('storefront_subscription_choices', '', 'Subscription Choices', 'storefront', 'fa-shopping-cart');
        menu_page::addonAddPage('storefront_subscription_choices_add', 'storefront_subscription_choices', 'Add Choice', 'storefront', 'fa-shopping-cart', 'sub_page');
        menu_page::addonAddPage('storefront_subscription_choices_edit', 'storefront_subscription_choices', 'Edit Choice', 'storefront', 'fa-shopping-cart', 'sub_page');
        menu_page::addonAddPage('storefront_subscription_choices_delete', 'storefront_subscription_choices', 'Delete Choice', 'storefront', 'fa-shopping-cart', 'sub_page');
        menu_page::addonAddPage('storefront_list_stores_options', '', 'List Stores Settings', 'storefront', 'fa-shopping-cart');
    }

    function display_addon_storefront_main()
    {
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();
        $reg = geoAddon::getRegistry('storefront');
        $addon = geoAddon::getInstance();

        $tpl_vars = array();

        $tpl_vars['admin_msgs'] = $admin->getUserMessages();

        //check to see if SEO is installed/enabled
        $tpl_vars['seo'] = $addon->isEnabled('SEO');
        $tpl_vars['sef'] = $reg->sef;

        $tpl_vars['geographic_navigation'] = $addon->isEnabled('geographic_navigation');
        $tpl_vars['geonav_filter_storefronts'] = $reg->geonav_filter_storefronts;

        $tpl_vars['show_traffic'] = $reg->get('show_traffic', 1);
        $tpl_vars['allow_newsletter'] = $reg->get('allow_newsletter', 1);
        $tpl_vars['default_storename_to_company'] = $reg->get('default_storename_to_company', 1);

        $tpl_vars['use_logo_for_store_links'] = $reg->get('use_logo_for_store_links', 1);

        $tpl_vars['max_logo_width_in_store'] = $reg->get('max_logo_width_in_store', 450);
        $tpl_vars['max_logo_height_in_store'] = $reg->get('max_logo_height_in_store', 110);
        $tpl_vars['max_logo_width_in_browsing'] = $reg->get('max_logo_width_in_browsing', 70);
        $tpl_vars['max_logo_height_in_browsing'] = $reg->get('max_logo_height_in_browsing', 100);

        $tpl_vars['size_tooltip'] = geoHTML::showTooltip('Max logo width/height', 'These settings control the maximum length and width of logos <strong>inside</strong> the actual storefronts. For the logos shown on the "list all stores" page, see the "List Stores Settings" page');

        geoView::getInstance()->setBodyTpl('admin/settings.tpl', $this->name)
            ->setBodyVar($tpl_vars);
    }

    public function update_addon_storefront_main()
    {
        $reg = geoAddon::getRegistry('storefront');
        $settings = $_POST['storefront'];

        $reg->show_traffic = ($settings['show_traffic'] == 1) ? 1 : 0;
        $reg->allow_newsletter = ($settings['allow_newsletter'] == 1) ? 1 : 0;
        $reg->default_storename_to_company = ($settings['default_storename_to_company'] == 1) ? 1 : 0;

        $reg->use_logo_for_store_links = ($settings['use_logo_for_store_links'] == 1) ? 1 : 0;

        $reg->max_logo_width_in_store = intval($settings['max_logo_width_in_store']);
        $reg->max_logo_height_in_store = intval($settings['max_logo_height_in_store']);
        $reg->max_logo_width_in_browsing = intval($settings['max_logo_width_in_browsing']);
        $reg->max_logo_height_in_browsing = intval($settings['max_logo_height_in_browsing']);
        //this one defaults to off so no need to preserve number 0 for off
        $reg->geonav_filter_storefronts = (isset($settings['geonav_filter_storefronts']) && $settings['geonav_filter_storefronts']) ? 1 : false;

        $sef = (isset($settings['sef']) && $settings['sef']) ? 1 : false;

        if ($sef) {
            //add SEF URL's to SEO settings

            $seoUtil = geoAddon::getUtil('SEO');
            if ($seoUtil && version_compare($seoUtil->version, '2.0.5', '>=')) {
                $regex_title = addon_SEO_util::REGEX_TITLE;
                $regex_number = addon_SEO_util::REGEX_NUMBER;
                $settings = array (
                'list all stores'
                    => array(
                        'items' =>
                            array(
                                'custom_text_1'
                            ),
                        'title' =>
                            array(
                                'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                            ),
                        'status' => array('custom_text_1' => 2),
                        'order'  => array('custom_text_1' => 1),
                        'text'  => array('custom_text_1' => 'list-stores'),
                        'type' => array('custom_text_1' => 'custom_text'),
                        'regex' => array(),
                        'regexhandler' => 'a=ap&addon=storefront&page=list_stores',
                    ),
                'list all stores pages'
                    => array(
                        'items' =>
                            array(
                                'custom_text_1',
                                'page_id'
                            ),
                        'title' =>
                            array(
                                'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                                'page_id' => '(!PAGE_ID!)'
                            ),
                        'status' => array('custom_text_1' => 2, 'page_id' => 2),
                        'order'  => array('custom_text_1' => 1, 'page_id' => 2),
                        'text'  => array('custom_text_1' => 'list-stores'),
                        'type' => array('custom_text_1' => 'custom_text', 'page_id' => 'required'),
                        'regex' => array('page_id' => $regex_number),
                        'regexhandler' => 'a=ap&addon=storefront&page=list_stores&p=(!page_id!)',
                    ),
                    //Category
                ###/listings/category([0-9]*)\.htm$ $1.php?a=5&b=$2 [L]
                'storefront store'
                    => array(
                        'items' =>
                            array(
                                'store_name',
                                'custom_text_1'
                            ),
                        'title' => array(
                        'store_name' => '(!STORE_NAME!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        ),
                        'status'
                            => array('store_name' => 2,'custom_text_1' => 1),
                        'order'
                            => array('custom_text_1' => 1,'store_name' => 2),
                        'text'
                            => array('custom_text_1' => 'store'),
                        'type'
                             => array('custom_text_1' => 'custom_text','store_name' => 'required'),
                        'regex'
                            => array('store_name' => $regex_title),
                        'regexhandler' => 'a=ap&addon=storefront&page=home&store=(!store_name!)', //this is a set up for the htacccess , a=5 means its a category page in the url
                                             // (!REGEX_GROUP!) will be the group order
                    ),
                'storefront store page'
                    => array(
                        'items' =>
                            array(
                                'store_name',
                                'page_title',
                                'page_id',
                                'custom_text_1',
                                'custom_text_2'
                            ),
                        'title' => array(
                        'store_name' => '(!STORE_NAME!)',
                        'page_title' => '(!PAGE_TITLE!)',
                        'page_id' => '(!PAGE_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)',
                        ),
                        'status'
                            => array('store_name' => 2,'page_id' => 2, 'page_title' => 1, 'custom_text_1' => 1, 'custom_text_2' => 1),
                        'order'
                            => array('custom_text_1' => 1,'store_name' => 2,'custom_text_2' => 3, 'page_id' => 4, 'page_title' => 5),
                        'text'
                            => array('custom_text_1' => 'store', 'custom_text_2' => 'page'),
                        'type'
                             => array('custom_text_1' => 'custom_text','custom_text_2' => 'custom_text','store_name' => 'required','page_id' => 'required'),
                        'regex'
                            => array('store_name' => $regex_title, 'page_id' => $regex_number, 'page_title' => $regex_title),
                        'regexhandler' => 'a=ap&addon=storefront&page=home&store=(!store_name!)&p=(!page_id!)', //this is a set up for the htacccess , a=5 means its a category page in the url
                                             // (!REGEX_GROUP!) will be the group order
                    ),
                'storefront store category'
                    => array(
                        'items' =>
                            array(
                                'store_name',
                                'category_title',
                                'category_id',
                                'custom_text_1',
                                'custom_text_2'
                            ),
                        'title' => array(
                        'store_name' => '(!STORE_NAME!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'category_id' => '(!CATEGORY_ID!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)',
                        ),
                        'status'
                            => array('store_name' => 2,'category_id' => 2, 'category_title' => 1, 'custom_text_1' => 1, 'custom_text_2' => 1),
                        'order'
                            => array('custom_text_1' => 1,'store_name' => 2,'custom_text_2' => 3, 'category_id' => 4, 'category_title' => 5),
                        'text'
                            => array('custom_text_1' => 'store', 'custom_text_2' => 'category'),
                        'type'
                             => array('custom_text_1' => 'custom_text','custom_text_2' => 'custom_text','store_name' => 'required','category_id' => 'required'),
                        'regex'
                            => array('store_name' => $regex_title, 'category_id' => $regex_number, 'category_title' => $regex_title),
                        'regexhandler' => 'a=ap&addon=storefront&page=home&store=(!store_name!)&category=(!category_id!)', //this is a set up for the htacccess , a=5 means its a category page in the url
                                             // (!REGEX_GROUP!) will be the group order
                    ),
                'storefront store category pages'
                    => array(
                        'items' =>
                            array(
                                'store_name',
                                'category_title',
                                'category_id',
                                'custom_text_1',
                                'custom_text_2',
                                'page_id'
                            ),
                        'title' => array(
                            'store_name' => '(!STORE_NAME!)',
                            'category_title' => '(!CATEGORY_TITLE!)',
                            'category_id' => '(!CATEGORY_ID!)',
                            'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                            'custom_text_2' => '(!CUSTOM_TEXT_2!)',
                            'page_id' => '(!PAGE_ID!)'
                        ),
                        'status'
                            => array('store_name' => 2,'category_id' => 2, 'category_title' => 1, 'custom_text_1' => 1, 'custom_text_2' => 1, 'page_id' => 2),
                        'order'
                            => array('custom_text_1' => 1,'store_name' => 2,'custom_text_2' => 3, 'category_id' => 4, 'category_title' => 5, 'page_id' => 6),
                        'text'
                            => array('custom_text_1' => 'store', 'custom_text_2' => 'category'),
                        'type'
                             => array('custom_text_1' => 'custom_text','custom_text_2' => 'custom_text','store_name' => 'required','category_id' => 'required', 'page_id' => 'required'),
                        'regex'
                            => array('store_name' => $regex_title, 'category_id' => $regex_number, 'category_title' => $regex_title, 'page_id' => $regex_number),
                        'regexhandler' => 'a=ap&addon=storefront&page=home&store=(!store_name!)&category=(!category_id!)&page_result=(!page_id!)', //this is a set up for the htacccess , a=5 means its a category page in the url
                                             // (!REGEX_GROUP!) will be the group order
                    ),
                'storefront store listing' => array(
                    'items' =>
                        array(
                            'store_name',
                            'listing_id',
                            'listing_title',
                            'category_id',
                            'category_title',
                            'custom_text_1',
                            'custom_text_2',
                            'custom_text_3'
                        ),
                    'title' => array(
                        'store_name' => '(!STORE_NAME!)',
                        'listing_id' => '(!LISTING_ID!)',
                        'listing_title' => '(!LISTING_TITLE!)',
                        'category_id' => '(!CATEGORY_ID!)',
                        'category_title' => '(!CATEGORY_TITLE!)',
                        'custom_text_1' => '(!CUSTOM_TEXT_1!)',
                        'custom_text_2' => '(!CUSTOM_TEXT_2!)',
                        'custom_text_3' => '(!CUSTOM_TEXT_3!)'),
                    'status'
                        => array('store_name' => 2, 'listing_id' => 2,'custom_text_1' => 1,'custom_text_2' => 1, 'custom_text_3' => 1, 'listing_title' => 1, 'category_id' => 1, 'category_title' => 1)
                    ,'order'
                        => array('custom_text_1' => 1,'store_name' => 2, 'custom_text_2' => 3, 'category_id' => 4, 'category_title' => 5,'custom_text_3' => 6, 'listing_id' => 7,'listing_title' => 8)
                    ,'text'
                        => array('custom_text_1' => 'store', 'custom_text_2' => 'category', 'custom_text_3' => 'listings')
                    ,'type'
                        => array('custom_text_1' => 'custom_text','listing_id' => 'required', 'store_name' => 'required')
                    ,'regex'
                        => array('store_name' => $regex_title, 'listing_id' => $regex_number,'listing_title' => $regex_title,'category_id' => $regex_number, 'category_title' => $regex_title)
                    ,'regexhandler' => 'a=ap&addon=storefront&page=home&store=(!store_name!)&listing=(!listing_id!)'
                    ),
                );


                $count = $seoUtil->addSeoUrls($settings, true);
                if ($count) {
                    geoAdmin::m('To start using Search Engine Friendly URLs you will need to re-generate
					the .htaccess file in the SEO admin, and copy the changes to your .htaccess file.', geoAdmin::NOTICE);
                }
            } else {
                $sef = false;
            }
        }
        $reg->sef = $sef;

        $reg->save();
        return true;
    }

    function admin_get_row_color($default = 1, $first_option = false)
    {
        if (!$this->color) {
            $this->color = $default;
        }
        $this->color = ($this->color == 1) ? 2 : 1;
        return 'row_color' . $this->color;
    }

    function label($text, $for = '')
    {
        return "<label" . (($for) ? " for='" . $for . "'>" : ">") . $text . "</label>";
    }

    public function display_display_storefront_link()
    {
        geoAdmin::m('This setting has moved to <em>Fields to Use</em>, accessible through <em>Listing Setup</em> (for the site-wide defaults) or <em>Categories &gt; Category Setup</em> (for the category-specific version).', geoAdmin::NOTICE);
        geoAdmin::getInstance()->v()->addBody(geoAdmin::m());
    }

    function display_storefront_subscription_choices()
    {
        $admin = geoAdmin::getInstance();
        $v = $admin->v();
        $db = DataAccess::getInstance();

        $admin->v()->messages = $admin->getUserMessages();

        $table = geoAddon::getUtil('storefront')->tables();
        $sql = "SELECT * FROM $table->subscriptions_choices ORDER BY value";
        $r = $db->getall($sql);

        if ($r === false) {
            die('yes it is it!');
            $this->error_message = $this->internal_error_message;
            return false;
        }

        foreach ($r as $subscription_details) {
            $count++;
            $reset_count++;

            $count_display[$count] = $count;
            $display_values[$count] = $subscription_details[display_value];
            $numberofdays[$count] = $subscription_details[value];
            $value_plural[$count] = ($subscription_details[value] == 1) ?  "" : "s";
            $amount[$count] = sprintf("%0.2f", $subscription_details[amount]);
            $trial[$count] = (($subscription_details['trial'] == 1) ? true : false);
            $period_ids[$count] = $subscription_details[period_id];
            $color_class[$count] =  geoHTML::adminGetRowColor();

            if ($reset_count == 6) {
                $add_header[$count] = 1;
                $reset_count = 0;
            }
        }

        $tpl = new geoTemplate('addon', 'storefront');
        $v->count_display = $count_display;
        $v->display_values = $display_values;
        $v->numberofdays = $numberofdays;
        $v->value_plural = $value_plural;
        $v->amount = $amount;
        $v->trial = $trial;
        $v->period_ids = $period_ids;
        $v->color_class = $color_class;
        $v->add_header = $add_header;
        $v->last_color = geoHTML::adminGetRowColor();
        $v->precurrency = $db->get_site_setting('precurrency');
        $v->postcurrency = $db->get_site_setting('postcurrency');
        $v->plans = geoAddon::adminDisplayPlanItemLinks('storefront_subscription', true);

        $v->setBodyTpl('admin_subscription_choices.tpl', 'storefront');
    }

    function update_storefront_subscription_choices()
    {
        //nothing to update
        return true;
    }

    function display_storefront_subscription_choices_add()
    {
        $this->display_storefront_subscription_choices();
    }

    function update_storefront_subscription_choices_add()
    {
        return $this->insert_subscription_period($_POST["d"]);
    }

    function display_storefront_subscription_choices_edit()
    {
        $db = $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $periodId = $_REQUEST["period_id"];

        if (!$periodId) {
            $admin->userError('No id specified!');
            $this->display_storefront_subscription_choices();
            //stop from running any more
            return ;
        }
        $table = geoAddon::getUtil("storefront")->tables();
        $sql = "SELECT * FROM {$table->subscriptions_choices} WHERE period_id = $periodId";
        $result = $db->Execute($sql);
        if (!$result) {
            $admin->userError('DB Error.');
            $this->display_storefront_subscription_choices();
            //stop from running any more
            return ;
        }
        $show_subscriptions = $result->FetchRow();
        $html = $admin->getUserMessages();
        $html .= "
	<fieldset>
		<legend>Edit Subscription Choice</legend>
		<form action='' method=post>";

        $html .= geoHTML::addOption('Display Value', "<input type='text' name='d[display_value]' value='{$show_subscriptions["display_value"]}' />");
        $html .= geoHTML::addOption('Period', "<input type='text' name='d[value]' value='{$show_subscriptions["value"]}' /> Days");
        $html .= geoHTML::addOption('Cost', "<input type='text' name='d[cost]' value='{$show_subscriptions["amount"]}' />");

        $isTrial = (($show_subscriptions['trial'] == 1) ? 'Yes' : 'No');
        $html .= geoHTML::addOption('Trial Period', "<strong>$isTrial</strong> (The trial status of a subscription choice may not be edited.) <input type='hidden' name='d[trial]' value='" . (($show_subscriptions['trial'] == 1) ? '1' : '0') . "' />");

        $html .= "<div class='center'>
		<input type='submit' name='auto_save' value='Save'/></div>
		</form>
	</fieldset>
";

        $html .= "<div style='padding: 5px;'><a href='index.php?page=storefront_subscription_choices&mc=addon_cat_storefront' class='back_to'>
				<img src='admin_images/design/icon_back.gif' alt='' class='back_to'>Back to Storefront Subscription Choices</a></div>";

        $admin->v()->addBody($html);
    }

    function update_storefront_subscription_choices_edit()
    {
        return $this->insert_subscription_period($_POST["d"], $_REQUEST["period_id"]);
    }

    function display_storefront_subscription_choices_delete($s_id = null)
    {
        $this->display_storefront_subscription_choices();
    }

    function update_storefront_subscription_choices_delete($period_id = 0)
    {
        $admin = geoAdmin::getInstance();
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        if (isset($period_id) && intval($period_id) > 0) {
            //Allow for easy deleting using API once we are ready to add this
            //as an API call.  (if we ever do)
            $subscription_period_id = intval($period_id);
        } else {
            $subscription_period_id = intval($_REQUEST["period_id"]);
        }

        if (!$subscription_period_id) {
            $admin->userError('Error attempting to delete period, invalid subscription period specified.');
            return false;
        }
        $table = geoAddon::getUtil("storefront")->tables();
        $sql = "DELETE FROM {$table->subscriptions_choices} WHERE `period_id` = $subscription_period_id LIMIT 1";
        $delete_result = $db->Execute($sql);
        //echo $sql."<br>\n";
        if (!$delete_result) {
            $admin->userError('Subscription choice NOT deleted, there was a query error.  Please try again.  Debug info: SQL: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            return false;
        }
        $admin->userSuccess('Subscription choice has been deleted.');
        return true;
    }

    function display_subscription_period_form()
    {
        $html .= "
		<form action='index.php?page=storefront_subscription_choices_add' method='post'>
			<tr class='" . $this->get_row_color() . "'>
				<td class=medium_font>
					<input type=text name=d[display_value] value='30 Days' />
				</td>
				<td class=medium_font align=center>
					<input type='text' name='d[value]' value='30' /> days
				</td>
				<td class=medium_font>
					<input type='text' name='d[cost]' value='5.00' />
				</td>
				<td>
					<input type='submit' class='submit' name='auto_save' value='Add Choice' />
				</td>
				<td>&nbsp;</td>
			</tr>
		</form>";

        return $html;
    }

    function insert_subscription_period($subscription_info = 0, $periodId = 0)
    {
        if (PHP5_DIR) {
            $admin = geoAdmin::getInstance();
        } else {
            $admin = & geoAdmin::getInstance();
        }
        //validate input
        if (intval($subscription_info["value"]) . '' !== $subscription_info["value"]) {
            $admin->userNotice('Period must be the number in days.');
        }
        $periodId = intval($periodId); //force period id to be intval
        if (!isset($subscription_info) || !is_array($subscription_info)) {
            //invalid input!
            return false;
        }

        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        //generate the price
        $amount = '';
        if (isset($subscription_info['period_dollars'])) {
            $amount = $subscription_info["period_dollars"] . "." . $subscription_info["period_cents"];
        } else {
            //NEW way: let them enter in the number!
            $amount = floatval($subscription_info['cost']);
        }

        $trial = ($subscription_info['trial'] == 1) ? 1 : 0;

        $table = geoAddon::getUtil("storefront")->tables();
        if ($periodId) {
            $sql = "UPDATE " . $table->subscriptions_choices . " SET
			`display_value` = ?,
			`value` = ?,
			`amount` = ?,
			`trial` = ?
			WHERE `period_id` = ?";

            $query_data = array($subscription_info["display_value"], $subscription_info["value"], $amount, $trial, $periodId);
        } else {
            $sql = "INSERT INTO " . $table->subscriptions_choices . "
			(`display_value`,`value`,`amount`, `trial`)
			VALUES (?, ?, ?, ?)";
            $query_data = array ($subscription_info["display_value"],$subscription_info["value"],$amount, $trial);
        }
        $insert_result = $db->Execute($sql, $query_data);
        //echo $sql."<br>\n";
        if (!$insert_result) {
            $admin->userError('DB Query error, please try again.  Debug info: ' . $db->ErrorMsg());
            return false;
        }
        return true;
    } //end of function insert_subscription_period


    function getcolor($color = 1)
    {
        (!$this->color) ? $this->color = 1 : 2;
        $this->color = ($this->color == 1) ? 2 : 1;
        return 'row_color' . $this->color;
    }

    function display_store_front_fields_to_use()
    {
        $db = $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $reg = geoAddon::getRegistry('storefront');
        $this->configuration_data = Admin_site::getConfigurationData();
        if (geoMaster::is('auctions') && geoMaster::is('classifieds')) {
            $config_title = "Listing Configuration";
            $item_name = "listing";
        } elseif (geoMaster::is('auctions')) {
            $config_title = "Auction Configuration";
            $item_name = "Auction";
        } else {
            $config_title = "Listing Setup";
            $item_name = "Ad";
        }

        $html = $admin->getUserMessages();
        // Listings header
        // Set title and text for tooltip

        if ($this->ad_configuration_message) {
            $html .= "<div class='page_note'>" . $this->ad_configuration_message . '</div>';
        }
        $html .= "
				<script>
					function validate(field,max)
					{
						max=(max)?max:256;
						if (!(field.value>=0 && field.value<=max))
						{
							alert('Must be between 0 and '+max+'. Values outside this range as well as invalid characters will not be submitted.');
							field.value=\"\";
							field.focus();
						}
					}
					function check_all(elements,col)
					{
						for(x = 0; x < elements.length; x++)
						{
							if(elements[x].id == col && !elements[x].disabled)
								elements[x].checked=elements[col+'_all'].checked;
							if(elements[x].id == col+'_section' && !elements[x].disabled)
								elements[x].checked=elements[col+'_all'].checked;
						}
					}
				</script>
				<fieldset>
				<legend>Fields to Display</legend>
				<form name='fields_to_use' action='' method='post'>
				<div class='table-responsive'>
				<table class='table table-striped table-hover'>";


        // Block of checkboxes for major settings
        $html .= "<thead>
					<tr>
						<th>Field</td>
						<th style='text-align: center;'>Display</td>
					</tr>
					</thead><tbody>";
        $this->row_count = 0;
        //display photo column
        $html .= "
							<tr>
								<td align=left valign=top class=medium_font>
									<b>Photo Icon/Thumbnail</b>
								</td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[display_photo_icon] value=1 "
                                    . (($reg->display_photo_icon == 1) ? "checked" : "") . ">
								</td>
							</tr>";
        // Title Field
        $html .= "
									<tr>
								<td align=left valign=top class=medium_font><b>Title</b></td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[display_ad_title] value=1 "
                                    . (($reg->display_ad_title == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    // Description Field
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>Description</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_ad_description] value=1 "
                                    . (($reg->display_ad_description == 1) ? "checked" : "") . ">
								</td>
							</tr>";
                                    // Display Description Field under title
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>Description under Title</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_ad_description_where] value=1 "
                                    . (($reg->display_ad_description_where == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    // Price Field
                                    $html .= "
							<tr>
								<td align=left valign=top class=medium_font><b>Price</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_price] value=1 "
                                    . (($reg->display_price == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    // Country Field
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>Country</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_browsing_country_field] value=1 "
                                    . (($reg->display_browsing_country_field == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    // State Field
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>State</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_browsing_state_field] value=1 "
                                    . (($reg->display_browsing_state_field == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    // City Field
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>City</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_browsing_city_field] value=1 "
                                    . (($reg->display_browsing_city_field == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    // Zip Field
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>Zip</b></td>
								<td valign=top align=center class=medium_font>
									<input id=display type=checkbox name=c[display_browsing_zip_field] value=1 "
                                    . (($reg->display_browsing_zip_field == 1) ? "checked" : "") . ">
								</td>
							</tr>";

                                    //display business type column
                                    $html .= "<tr>
								<td align=left valign=top class=medium_font><b>Business Type</b></td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[display_business_type] value=1 "
                                    . (($reg->display_business_type == 1) ? "checked" : "") . ">
								</td>
							</tr>";

        if (geoMaster::is('classifieds')) {
            //display classified entry date
            $html .= "
							<tr>
								<td align=left valign=top class=medium_font><b>Classified Entry Date</b>&nbsp;</td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[display_entry_date] value=1 "
            . (($reg->display_entry_date == 1) ? "checked" : "") . ">
								</td>
							</tr>";
        }
        if (geoMaster::is('auctions')) {
            //display auction entry date
            $html .= "
							<tr>
								<td align=left valign=top class=medium_font><b>Auction Entry Date</b>&nbsp;</td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[auction_entry_date] value=1 "
            . (($reg->auction_entry_date == 1) ? "checked" : "") . ">
								</td>
							</tr>";
        }
        if (geoMaster::is('classifieds')) {
            //display classified time left
            $html .= "
							<tr>
								<td align=left valign=top class=medium_font>
									<b>Time Left Before Classified Expires</b>&nbsp;
								</td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[classified_time_left] value=1 "
            . (($reg->classified_time_left == 1) ? "checked" : "") . ">
								</td>
							</tr>";
        }
        if (geoMaster::is('auctions')) {
            //display auction time left
            $html .= "
							<tr>
								<td align=left valign=top class=medium_font>
									<b>Time Left Before Auction Closes</b>&nbsp;
								</td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[display_time_left] value=1 "
            . (($reg->display_time_left == 1) ? "checked" : "") . ">
								</td>
							</tr>";
        }
        if (geoMaster::is('auctions')) {
            //display number of bids column
            $html .= "
							<tr>
								<td align=left valign=top class=medium_font>
									<b>Number of Bids</b>&nbsp;
								</td>
								<td align=center valign=top class=medium_font>
									<input id=display type=checkbox name=c[display_number_bids] value=1 "
            . (($reg->display_number_bids == 1) ? "checked" : "") . ">
								</td>
							</tr>";
        }

                                    $this->row_count = 0;
                                    //Optional Fields
        for ($i = 1; $i < 21; $i++) {
            $optional_field_name = $this->configuration_data['optional_field_' . $i . '_name'];
            $html .= "	<tr>
										<td align=left valign=top class=medium_font><b>$optional_field_name</b></td>
										<td align=center valign=top class=medium_font>
											<input id=display type=checkbox name=c[display_optional_field_" . $i . "] value=1 " .
            (($reg->get('display_optional_field_' . $i) == 1) ? "checked" : "") . ">
										</td>
									</tr>";
        }



                                    $html .= "<tr class=row_color_black>
								<td class='col_hdr'>&nbsp;</td>
								<td align=center class='col_hdr'><input id=display_all onclick=\"javascript:check_all(document.fields_to_use,'display');\" type=checkbox></td>
								</tr>";


                $html .= "
							</tr>
							</tbody>
						</table>
						</div>
						<div class='center'><input type='submit' value=\"Save\" name=\"auto_save\"></div>
					</form>
				</fieldset>
				";
        $admin->v()->addBody($html);
        return true;
    }

    function update_store_front_fields_to_use()
    {
        $admin = geoAdmin::getInstance();

        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $reg = geoAddon::getRegistry('storefront');
        $site_config_info =& $_POST['c'];
        //TODO: Make sure all these settings are actually changeable/displayed in admin
        $site_fields = array(
        "display_ad_description_where",
        "display_photo_icon",
        "display_ad_title",
        "display_ad_description",
        "display_price",
        "display_browsing_country_field",
        "display_browsing_state_field",
        "display_browsing_city_field",
        "display_browsing_zip_field",
        "display_entry_date",
        "display_business_type",
        "display_time_left",
        "display_number_bids",
        "auction_entry_date",
        "classified_time_left"
        );
        for ($i = 1; $i < 21; $i++) {
            $site_fields[] = "display_optional_field_{$i}";
        }
        foreach ($site_fields as $name) {
            $val = (isset($site_config_info[$name]) && $site_config_info[$name]) ? 1 : false;
            $reg->set($name, $val);
        }
        $reg->save();
        return true;
    }


    function get_row_color($row_count = 0)
    {
        if (!$row_count) {
            $row_count = $this->row_count;
        }
        if (($row_count % 2) == 0) {
            $row_color = "row_color1";
        } else {
            $row_color = "row_color2";
        }
        return $row_color;
    } //end of function get_row_color

    function site_error($db_error = 0, $file = 0, $line = 0)
    {
        $db = DataAccess::getInstance();
        //check to see if debugging
        if ($this->debug) {
            highlight_string(print_r(debug_backtrace(), 1));
            echo "<table cellpadding=3 cellspacing=1 border=0>
					<tr>
						<td class=very_large_font>
							There has been a database error
						</td>
					</tr>
					<tr>
						<td class=medium_error_font>";
            if ($db_error) {
                echo "		With the following sql error:" . $db_error . "<br>";
            }
            if ($file || $line) {
                echo "		This <b>site_error()</b> was called from . . .
							" . (($file) ? "<br>FILE = <b>$file</b>" : "") . "
							" . (($line) ? "<br>LINE = <b>$line</b>" : "");
            }
            echo "		</td>
					</tr>
				</table>";
        } else {
            echo "<table cellpadding=3 cellspacing=1 border=0>\n";
            echo "<tr>\n\t<td class=very_large_font>There has been a error.<br>
				Please try again. \n\t</td>\n</tr>\n";
            echo "</table>\n";
        }
    } //end of function site_error

    function _get_row_color($row_count = 0)
    {
        if (!$row_count) {
            $row_count = $this->row_count;
        }
        if (($row_count % 2) == 0) {
            $row_color = "row_color1";
        } else {
            $row_color = "row_color2";
        }
        return $row_color;
    } //end of function get_row_color

    function _get_category_name($category_id = 0)
    {
        if ($category_id) {
            $db = true;
            include GEO_BASE_DIR . 'get_common_vars.php';

            $sql = "SELECT category_name from " . $this->classified_categories_languages_table . " WHERE language_id = 1 and category_id = " . $category_id;
            $mycat = $db->getrow($sql);
            if (!$mycat) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($db->ErrorMsg());
                return false;
            } else {
                $cat =  urldecode(stripslashes($mycat['category_name']));
                return $cat;
            }
            return '';
        }
        return "Main";
    }

    public function display_storefront_list_stores_options()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $reg = geoAddon::getRegistry('storefront');

        $html = $admin->getUserMessages();

        $html .= '<form action="" method="post" class="form-horizontal">';

        $html .= '<div style="text-align: center;"><a href="' . DataAccess::getInstance()->get_site_setting('classifieds_url') . '?a=ap&amp;addon=storefront&amp;page=list_stores" class="mini_button" onclick="window.open(this.href); return false;">Preview List All Stores Page</a></div>';

        $html .= '<fieldset><legend>Settings</legend><div>';

        $html .= '
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">
					Maximum number of stores per page
				</label>
				<div class="col-xs-12 col-sm-6">
					<input type="text" name="data[list_max_stores]" value="' . ($reg->get('list_max_stores', 25)) . '" class="form-control" />
				</div>
			</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Maximum length of welcome note / description
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="text" name="data[list_description_length]" value="' . ($reg->get('list_description_length', 30)) . '" class="form-control" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Max logo width
			</label>
			<div class="col-xs-12 col-sm-6">
				<div class="input-group">
					<input type="text" name="data[max_logo_width]" value="' . $reg->get('max_logo_width', '450') . '" class="form-control" />
					<div class="input-group-addon">pixels</div>
				</div>
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Max logo height
			</label>
			<div class="col-xs-12 col-sm-6">
				<div class="input-group">
					<input type="text" name="data[max_logo_height]" value="' . $reg->get('max_logo_height', '110') . '" class="form-control" />
					<div class="input-group-addon">pixels</div>
				</div>
			</div>
		</div>';

        $html .= '</div></fieldset>';

        $html .= '<fieldset><legend>Column Switches</legend><div>';

        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show logo column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_logo]" value="1" ' . ($reg->get('list_show_logo') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show title column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_title]" value="1" ' . ($reg->get('list_show_title') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show number of items column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_num_items]" value="1" ' . ($reg->get('list_show_num_items') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show description / welcome note column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_description]" value="1" ' . ($reg->get('list_show_description') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show city column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_city]" value="1" ' . ($reg->get('list_show_city') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show state column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_state]" value="1" ' . ($reg->get('list_show_state') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';
        $html .= '
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">
				Show zip column
			</label>
			<div class="col-xs-12 col-sm-6">
				<input type="checkbox" name="data[list_show_zip]" value="1" ' . ($reg->get('list_show_zip') ? 'checked="checked"' : '') . '" />
			</div>
		</div>';


        $html .= '</div></fieldset>';
        $html .= '<div class="center"><input type="submit" value="Save" name="auto_save" /></div></form>';

        $admin->v()->addBody($html);
    }

    public function update_storefront_list_stores_options()
    {
        $reg = geoAddon::getRegistry('storefront');
        $data = $_POST['data'];
        if (!$data) {
            return false;
        }
        $reg->set('list_max_stores', (int)$data['list_max_stores']);
        $reg->set('list_description_length', (int)$data['list_description_length']);
        $reg->set('max_logo_width', (int)$data['max_logo_width']);
        $reg->set('max_logo_height', (int)$data['max_logo_height']);

        $reg->set('list_show_logo', ($data['list_show_logo'] ? 1 : 0));
        $reg->set('list_show_title', ($data['list_show_title'] ? 1 : 0));
        $reg->set('list_show_num_items', ($data['list_show_num_items'] ? 1 : 0));
        $reg->set('list_show_description', ($data['list_show_description'] ? 1 : 0));
        $reg->set('list_show_city', ($data['list_show_city'] ? 1 : 0));
        $reg->set('list_show_state', ($data['list_show_state'] ? 1 : 0));
        $reg->set('list_show_zip', ($data['list_show_zip'] ? 1 : 0));
        $reg->save();
        return true;
    }


    /**
     * This internal variable is NOT used directly by the addon system.
     * It is the array returned by {@link addon_storefront_admin::init_text()}
     *
     * @var array Used as return value for function {@link addon_storefront_admin::init_text()}
     * @see addon_storefront_admin::init_text()
     */
    var $default_addon_text = array (
    // TEXT FOR STORE LISTING PAGE
                //************************** General ******************************
        'page_title' => array (
            'name' => 'Page title',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefronts',
            'section' => 'General'
        ),
        'listing_storefront_link' => array (
            'name' => 'Storefront Link Text for Listing',
            'desc' => '',
            'type' => 'input',
            'default' => "Seller's Storefront",
            'section' => 'General'
        ),
        'storefront_header_from_browsing' => array (
            'name' => 'Storefront Link Column Header (from browsing pages/modules)',
            'desc' => '',
            'type' => 'input',
            'default' => "Storefront",
            'section' => 'General'
        ),
        'storefront_link_from_browsing' => array (
            'name' => 'Storefront Link Text (from browsing pages/modules)',
            'desc' => '',
            'type' => 'input',
            'default' => "<img alt=\"Storefront\" src=\"{external file='images/icon_storefront.png'}\" />",
            'section' => 'General'
        ),

        //************************** Column Headers ******************************
        'title_column' => array ( //text_index1 is the text_id
            'name' => 'Title column header', //name is used in the admin section for editing text messages
            'desc' => '', //desc is used in the admin section for editing text messages
            'type' => 'input', //type is either textarea, or input, and designates what form will be used to edit the text in the admin.
            'default' => 'Title', //default is used when installing the addon, to set the default value for the text.
            'section' => 'Column Headers'
        ),
        'photo_column' => array (
            'name' => 'Logo column header',
            'desc' => '',
            'type' => 'input',
            'default' => '',
            'section' => 'Column Headers'
        ),
        'items_column' => array (
            'name' => 'Item total column header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Items',
            'section' => 'Column Headers'
        ),
        'description_column' => array (
            'name' => 'Description column header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Description',
            'section' => 'Column Headers'
        ),
        'city_column' => array (
            'name' => 'City column header',
            'desc' => '',
            'type' => 'input',
            'default' => 'City',
            'section' => 'Column Headers'
        ),
        'state_column' => array (
            'name' => 'State column header',
            'desc' => '',
            'type' => 'input',
            'default' => 'State',
            'section' => 'Column Headers'
        ),
        'zip_column' => array (
            'name' => 'Zip column header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Zip',
            'section' => 'Column Headers'
        ),

        //************************** Search Results ******************************
        'search_results_storefront_header' => array (
            'name' => 'Storefront Header on Search results',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront',
            'section' => 'Search Results'
        ),
        'search_results_storefront_link' => array (
            'name' => 'Storefront link on Search results',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront',
            'section' => 'Search Results'
        ),

        //************************** Subscriptions ******************************
        'extend_subscription_button' => array (
            'name' => 'Add Storefront Subscription button text in cart',
            'desc' => 'Used as button text, when viewing the cart, when the user does not currently have any current subscription.',
            'type' => 'input',
            'default' => 'Add Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'add_subscription_button' => array (
            'name' => 'Extend Storefront Subscription button text in cart',
            'desc' => 'Used as button text, when viewing the cart, when the user has a current subscription so they would be extending current.',
            'type' => 'input',
            'default' => 'Extend Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'renew_purchase_title' => array (
            'name' => 'Title of storefront subscription renewal page',
            'desc' => 'Used when user is viewing page to choose the subscription period to purchase.',
            'type' => 'input',
            'default' => 'Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'renew_purchase_sub_title' => array (
            'name' => 'Sub-Title of storefront subscription renewal page',
            'desc' => 'Used when user is viewing page to choose the subscription period to purchase.',
            'type' => 'input',
            'default' => 'Add or Extend Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'renew_purchase_desc' => array (
            'name' => 'Description on subscription renewal page',
            'desc' => 'Used when user is viewing page to choose the subscription period to purchase.',
            'type' => 'input',
            'default' => 'Choose the subscription period you wish to extend your Storefront Subscription by.',
            'section' => 'Subscriptions'
        ),
        'renew_purchase_submit_button_text' => array (
            'name' => 'Submit button text on subscription renewal page',
            'desc' => 'Used as button text, when user is viewing page to choose the subscription period to purchase.',
            'type' => 'input',
            'default' => 'Next &rsaquo;&rsaquo;',
            'section' => 'Subscriptions'
        ),
        'renew_purchase_cancel_text' => array (
            'name' => 'Cancel text on subscription renewal page',
            'desc' => 'Used as link text (can be an image tag) when user is viewing page to choose the subscription period to purchase.',
            'type' => 'input',
            'default' => 'Cancel &amp; Remove',
            'section' => 'Subscriptions'
        ),
        'storefront_subscription_step' => array (
            'name' => 'CART STEP: Storefront Subscription Period',
            'desc' => 'Label used for storefront subscription period step, when displaying steps in cart.',
            'type' => 'input',
            'default' => 'Storefront Subscription Period',
            'section' => 'Subscriptions'
        ),
        'error_sub_one_sub_at_time' => array (
            'name' => 'Subscription Error - only 1 subscription at a time',
            'desc' => 'Informs a user that only 1 subscription can be purchased at a time, if attempting to purchase a second subscription.',
            'type' => 'input',
            'default' => 'Cannot purchase more than one Storefront subscription at a time.  Either remove or edit the existing subscription in your cart.',
            'section' => 'Subscriptions'
        ),
        'error_sub_pending' => array (
            'name' => 'Subscription Error - pending subscription',
            'desc' => 'Informs a user that they cannot purchase a subscription, as there is a subscription that is still pending in the system.',
            'type' => 'input',
            'default' => 'You cannot add a Storefront subscription period at this time, because you have a pending subscription order in the system.',
            'section' => 'Subscriptions'
        ),
        'error_existing_sub' => array (
            'name' => 'Subscription Error - existing subscription',
            'desc' => 'Informs a user that they cannot purchase a subscription because of an existing subscription.',
            'type' => 'input',
            'default' => 'You already have an active, recurring Storefront Subscription, and cannot create a new one.',
            'section' => 'Subscriptions'
        ),
        'sub_title_in_cart' => array (
            'name' => 'Title for Storefront Subscription displayed in Cart',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'invalid_subscription_choice' => array (
            'name' => 'Error - invalid subscription choice',
            'desc' => 'Error displayed when, during purchase of storefront subscription, the user does not select any period choice.',
            'type' => 'input',
            'default' => 'Valid subscription choice required, please choose one of the subscription periods.',
            'section' => 'Subscriptions'
        ),
        'make_subscription_choice' => array (
            'name' => 'Make a subscription choice',
            'desc' => '',
            'type' => 'input',
            'default' => 'Subscription Choices',
            'section' => 'Subscriptions'
        ),
        'subscription_expired_explain' => array (
            'name' => 'Subscription has Expired',
            'desc' => 'Displayed when user is routed to the purchase storefront subscription process, if attempting to access storefront without current subscription.',
            'type' => 'textarea',
            'default' => 'Your subscription has expired or is not active. To add more time to your storefront subscription please select a subscription plan below.',
            'section' => 'Subscriptions'
        ),
        'extend_storefront_action' => array (
            'name' => 'Extend Storefront action text',
            'desc' => '',
            'type' => 'input',
            'default' => 'Renewing Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'extend_storefront_action_short' => array (
            'name' => 'Extend Storefront Short action text',
            'desc' => 'Used for "In Progress" text',
            'type' => 'input',
            'default' => 'Storefront',
            'section' => 'Subscriptions'
        ),
        'extend_storefront_icon' => array (
            'name' => 'Extend Storefront Icon',
            'desc' => 'Full image tag',
            'type' => 'textarea',
            'default' => '',
            'section' => 'Subscriptions'
        ),
        'extend_storefront_label' => array (
            'name' => 'Extend Storefront Link Text',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront Subscription',
            'section' => 'Subscriptions'
        ),
        'recurring_desc' => array (
            'name' => 'Recurring Subscription Description',
            'desc' => 'Used for describing the storefront subscription in the payment gateway, and
				on transaction details displayed to user.',
            'type' => 'input',
            'default' => 'Storefront Subscription for',
            'section' => 'Subscriptions'
        ),

        //************************** Category Selection ******************************
        'category_sub_title' => array (
            'name' => 'Sub-title on storefront category ',
            'desc' => 'Used when user is selecting what storefront category to use for their listing, when placing new listing or editing existing listing.',
            'type' => 'input',
            'default' => 'Storefront Category',
            'section' => 'Category Selection'
        ),
        'category_desc' => array (
            'name' => 'Description on storefront category page',
            'desc' => 'Used when user is viewing page to choose the storefront category for a listing.',
            'type' => 'input',
            'default' => 'Choose which of your storefront categories you wish to place the listing in.',
            'section' => 'Category Selection'
        ),
        'category_submit_button_text' => array (
            'name' => 'Submit button text on storefront category',
            'desc' => 'Used as button text, when user is viewing page to choose the storefront category, when placing a new listing or editing an existing listing.',
            'type' => 'input',
            'default' => 'Next &rsaquo;&rsaquo;',
            'section' => 'Category Selection'
        ),
        'category_cancel_text' => array (
            'name' => 'Cancel text on storefront category page',
            'desc' => 'Used as link text (can be an image tag) when user is viewing page to choose the storefront category for a listing.',
            'type' => 'input',
            'default' => 'Cancel Listing Edit',
            'section' => 'Category Selection'
        ),
        'edit_category_txt' => array (
            'name' => 'Edit category text',
            'desc' => 'Used as link text (can be an image tag) when user is viewing page to edit a listing, to choose what to edit.',
            'type' => 'input',
            'default' => 'Edit Storefront Category',
            'section' => 'Category Selection'
        ),
        'edit_category_step' => array (
            'name' => 'CART STEP: Edit Storefront Category',
            'desc' => 'Label used for edit storefront category step, when displaying steps in cart.',
            'type' => 'input',
            'default' => 'Storefront Category',
            'section' => 'Category Selection'
        ),
        'storefront_category_cart_title' => array (
            'name' => 'Storefront Category title',
            'desc' => 'Title for storefront category item, displayed in cart and order views.',
            'type' => 'input',
            'default' => 'Storefront Category',
            'section' => 'Category Selection'
        ),
        'storefront_category_choose_title' => array (
            'name' => 'Choose Storefront Category title',
            'desc' => 'Title displayed when placing listing, for section to choose the storefront category.',
            'type' => 'input',
            'default' => 'Storefront Category',
            'section' => 'Category Selection'
        ),

        //************************** My Account Links ******************************
        'cp_link_text' => array (
            'name' => 'Storefront Control Panel Link Text',
            'desc' => 'shown in My Account Links module',
            'type' => 'input',
            'default' => 'Storefront Control Panel',
            'section' => 'My Account Links'
        ),
        'my_account_cp_link_text' => array (
            'name' => 'Storefront Control Panel Link Text (My Account)',
            'desc' => 'shown on My Account Home Page',
            'type' => 'input',
            'default' => 'Storefront Control Panel',
            'section' => 'My Account Links'
        ),
        'my_account_links_icon'  => array (
            'name' => 'Link Icon (My Account)',
            'desc' => 'displays in My Account Links module, but only if enabled in Site Setup > User Account Settings (insert the full <img> tag here)',
            'type' => 'input',
            'default' => '',
            'section' => 'My Account Links'
        ),
        'mal_section_title' => array (
            'name' => 'My Account Home - Section Title',
            'desc' => 'Title of the Storefront section on the My Account Home Page',
            'type' => 'input',
            'default' => 'Storefront',
            'section' => 'My Account Links'
        ),
        'mal_expdate_label' => array (
            'name' => 'My Account Home - Expiration Label',
            'desc' => 'Labels the expiration date of a user\'s current subscription',
            'type' => 'input',
            'default' => 'Storefront subscription expiration date: ',
            'section' => 'My Account Links'
        ),
        'mal_renew_link' => array (
            'name' => 'My Account Home - Renewal Link',
            'desc' => 'text of link used to take the user to the subscription renewal page',
            'type' => 'input',
            'default' => 'Renew Storefront Subscription',
            'section' => 'My Account Links'
        ),
        'mal_new_sub_link' => array (
            'name' => 'My Account Home - Purchase Link',
            'desc' => 'text of link used to take the user to the purchase new subscription page',
            'type' => 'input',
            'default' => 'Purchase Storefront Subscription',
            'section' => 'My Account Links'
        ),
        'mal_storefront_link' => array (
            'name' => 'My Account Home - View Your Storefront Link',
            'desc' => 'text of link from My Account Home to the user\'s store page',
            'type' => 'input',
            'default' => 'View Your Storefront',
            'section' => 'My Account Links'
        ),
        'mal_no_sub' => array (
            'name' => 'My Account Home - No Subscription',
            'desc' => 'Informs a user he does not have an active storefront subscription',
            'type' => 'input',
            'default' => 'You do not have a current Storefront Subscription',
            'section' => 'My Account Links'
        ),
        'my_storefront_label' => array (
            'name' => 'My Storefront link text',
            'desc' => 'Used for link to my storefront from the my account links page.',
            'type' => 'input',
            'default' => 'My Storefront',
            'section' => 'My Account Links'
        ),

        //************************** My Account Info ******************************
        'account_info_section_title' => array (
            'name' => 'Account Info Storefront Subscription Section Title',
            'desc' => 'Section title used on user account info page, for section relating to storefront subscription.',
            'type' => 'input',
            'default' => 'Storefront Subscription Information',
            'section' => 'My Account Info'
        ),
        'account_info_section_desc' => array (
            'name' => 'Account Info Storefront Subscription Section Description',
            'desc' => 'Section description used on user account info page, for section relating to storefront subscription.',
            'type' => 'textarea',
            'default' => 'Open or Renew your Storefront Subscription using the information below:',
            'section' => 'My Account Info'
        ),
        'sub_expires_label' => array (
            'name' => 'Subscription Expires label',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Subscription Expires',
            'section' => 'My Account Info'
        ),
        'sub_renew_label' => array (
            'name' => 'Renew Subscription label',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Renew your Subscription:',
            'section' => 'My Account Info'
        ),
        'sub_renew_link_txt' => array (
            'name' => 'Renew Subscription link text',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Renew your Subscription',
            'section' => 'My Account Info'
        ),
        'recurring_sub_price_label' => array (
            'name' => 'Recurring Subscription price label',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Recurring Subscription Price',
            'section' => 'My Account Info'
        ),
        'recurring_sub_price_every' => array (
            'name' => 'Recurring Subscription price &quot;every&quot;',
            'desc' => 'Used on user account info page. $5.00 <strong>every</strong> 30 days',
            'type' => 'input',
            'default' => 'every',
            'section' => 'My Account Info'
        ),
        'recurring_sub_price_days' => array (
            'name' => 'Recurring Subscription price &quot;days&quot;',
            'desc' => 'Used on user account info page. $5.00 every 30 <strong>days</strong>',
            'type' => 'input',
            'default' => 'days',
            'section' => 'My Account Info'
        ),
        'recurring_sub_next_payment_label' => array (
            'name' => 'Recurring Subscription next payment date label',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Next Recurring Subscription Payment Scheduled for',
            'section' => 'My Account Info'
        ),
        'recurring_sub_cancel_label' => array (
            'name' => 'Recurring Subscription cancel label',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Cancel Recurring Subscription Payments',
            'section' => 'My Account Info'
        ),
        'recurring_sub_cancel_link' => array (
            'name' => 'Recurring Subscription cancel link',
            'desc' => 'Used on user account info page.',
            'type' => 'input',
            'default' => 'Cancel Payments',
            'section' => 'My Account Info'
        ),

        //************************** User CP ******************************
        'add_remove_wysiwyg' => array (
            'name' => 'Add Remove WYSIWYG link text',
            'desc' => '',
            'type' => 'input',
            'default' => '[Add/Remove Editor]',
            'section' => 'User Control Panel'
        ),
        'default_page_name_home' => array (
            'name' => 'name of default Home page',
            'desc' => '',
            'type' => 'input',
            'default' => 'Home',
            'section' => 'User Control Panel'
        ),
        'default_page_name_about' => array (
            'name' => 'name of default About page',
            'desc' => '',
            'type' => 'input',
            'default' => 'About',
            'section' => 'User Control Panel'
        ),
        'default_page_name_contact' => array (
            'name' => 'name of default Contact Us page',
            'desc' => '',
            'type' => 'input',
            'default' => 'Contact Us',
            'section' => 'User Control Panel'
        ),
        'default_category_name' => array (
            'name' => 'name of default category',
            'desc' => '',
            'type' => 'input',
            'default' => 'Inventory',
            'section' => 'User Control Panel'
        ),
        'default_home_category_name' => array (
            'name' => 'default value for Home Category Name',
            'desc' => '',
            'type' => 'input',
            'default' => 'Store',
            'section' => 'User Control Panel'
        ),
        'usercp_title' => array (
            'name' => 'User Control Panel - Title',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront Control Panel',
            'section' => 'User Control Panel'
        ),
        'usercp_toggle_header' => array (
            'name' => 'User Control Panel - Toggle Switch - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Toggle Storefront',
            'section' => 'User Control Panel'
        ),
        'usercp_toggle_on' => array (
            'name' => 'User Control Panel - Toggle Switch - ON',
            'desc' => '',
            'type' => 'input',
            'default' => 'Turn Store On',
            'section' => 'User Control Panel'
        ),
        'usercp_toggle_off' => array (
            'name' => 'User Control Panel - Toggle Switch - OFF',
            'desc' => '',
            'type' => 'input',
            'default' => 'Turn Store Off',
            'section' => 'User Control Panel'
        ),
        'usercp_links_header' => array (
            'name' => 'User Control Panel - Links - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Control Menu',
            'section' => 'User Control Panel'
        ),
        'usercp_links_stats' => array (
            'name' => 'User Control Panel - Links - Stats',
            'desc' => '',
            'type' => 'input',
            'default' => 'Statistics',
            'section' => 'User Control Panel'
        ),
        'usercp_links_customize' => array (
            'name' => 'User Control Panel - Links - Customize',
            'desc' => '',
            'type' => 'input',
            'default' => 'Customize',
            'section' => 'User Control Panel'
        ),
        'usercp_links_pages' => array (
            'name' => 'User Control Panel - Links - Pages',
            'desc' => '',
            'type' => 'input',
            'default' => 'Categories &amp; Pages',
            'section' => 'User Control Panel'
        ),
        'usercp_links_newsletter' => array (
            'name' => 'User Control Panel - Links - Newsletter',
            'desc' => '',
            'type' => 'input',
            'default' => 'Newsletter',
            'section' => 'User Control Panel'
        ),
        'usercp_links_help' => array (
            'name' => 'User Control Panel - Links - Help',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront Help',
            'section' => 'User Control Panel'
        ),
        'usercp_preview' => array (
            'name' => 'User Control Panel - Preview Button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Preview Storefront',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_header' => array (
            'name' => 'User Control Panel - Stats - Legend - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Reports Legend',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_uniquelabel' => array (
            'name' => 'User Control Panel - Stats - Legend - Unique Visits Label',
            'desc' => '',
            'type' => 'input',
            'default' => '',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_uniquekey' => array (
            'name' => 'User Control Panel - Stats - Legend - Unique Visits Key',
            'desc' => '',
            'type' => 'input',
            'default' => '## of Unique Visits',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_totallabel' => array (
            'name' => 'User Control Panel - Stats - Legend - Total Visits Label',
            'desc' => '',
            'type' => 'input',
            'default' => '',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_totalkey' => array (
            'name' => 'User Control Panel - Stats - Legend - Total Visits Key',
            'desc' => '',
            'type' => 'input',
            'default' => '## of Total Visits',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_lastmonth' => array (
            'name' => 'User Control Panel - Stats - Legend - Last Month button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Show Last Month',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_lastyear' => array (
            'name' => 'User Control Panel - Stats - Legend - Last Yonth button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Show Last Year',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_legend_lastthree' => array (
            'name' => 'User Control Panel - Stats - Legend - Last Three Years button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Show Last Three Years',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_label_month' => array (
            'name' => 'User Control Panel - Stats - Last Month label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Last Month',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_label_year' => array (
            'name' => 'User Control Panel - Stats - Last Year label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Last Year',
            'section' => 'User Control Panel'
        ),
        'usercp_stats_label_three' => array (
            'name' => 'User Control Panel - Stats - Last Three Years label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Last Three Years',
            'section' => 'User Control Panel'
        ),
        'usercp_common_savesuccess' => array (
            'name' => 'User Control Panel - Common - Save Success',
            'desc' => '',
            'type' => 'input',
            'default' => 'Settings Saved!',
            'section' => 'User Control Panel'
        ),
        'usercp_common_savefailure' => array (
            'name' => 'User Control Panel - Common - Save Failure',
            'desc' => '',
            'type' => 'input',
            'default' => 'An error was detected. Your settings were NOT saved.',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_header' => array (
            'name' => 'User Control Panel - Customize - Logo - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Logo',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_upload' => array (
            'name' => 'User Control Panel - Customize - Logo - File Upload Label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Upload a logo:',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_currentheader' => array (
            'name' => 'User Control Panel - Customize - Logo - Current Logo Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Current Logo',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_size_header' => array (
            'name' => 'User Control Panel - Customize - Logo - Size Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Logo Sizing',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_size_mainlabel' => array (
            'name' => 'User Control Panel - Customize - Logo - Size Main Label',
            'desc' => '',
            'type' => 'input',
            'default' => 'In Your Storefront:',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_size_listlabel' => array (
            'name' => 'User Control Panel - Customize - Logo - Size List Stores Label',
            'desc' => '',
            'type' => 'input',
            'default' => 'In "All Stores" List:',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_size_px' => array (
            'name' => 'User Control Panel - Customize - Logo - Pixels label',
            'desc' => '',
            'type' => 'input',
            'default' => 'pixels',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_size_width' => array (
            'name' => 'User Control Panel - Customize - Logo - Width label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Width:',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_logo_size_height' => array (
            'name' => 'User Control Panel - Customize - Logo - Height label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Height:',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_header' => array (
            'name' => 'User Control Panel - Customize - Settings - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront Settings',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_name_label' => array (
            'name' => 'User Control Panel - Customize - Settings - Name - Label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Storefront Name',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_name_check' => array (
            'name' => 'User Control Panel - Customize - Settings - Name - Availability button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Check Availability',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_name_pending' => array (
            'name' => 'User Control Panel - Customize - Settings - Name - Pending',
            'desc' => 'Text shown while the server processes the name request',
            'type' => 'input',
            'default' => 'Validating store name...',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_name_good' => array (
            'name' => 'User Control Panel - Customize - Settings - Name - Available',
            'desc' => '',
            'type' => 'input',
            'default' => 'That storefront name is available!',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_name_invalid' => array (
            'name' => 'User Control Panel - Customize - Settings - Name - Invalid',
            'desc' => '',
            'type' => 'input',
            'default' => 'That storefront name is invalid. Please try a different name.',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_name_taken' => array (
            'name' => 'User Control Panel - Customize - Settings - Name - Taken',
            'desc' => '',
            'type' => 'input',
            'default' => 'That storefront name is already in use. Please try a different name.',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_welcomenoteheader' => array (
            'name' => 'User Control Panel - Customize - Settings - Welcome Note Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Welcome Note',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_tpl_header' => array (
            'name' => 'User Control Panel - Customize - Settings - Template Select - Header',
            'desc' => 'only shown if multiple templates are available for selection',
            'type' => 'input',
            'default' => 'Storefront Template Selection',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_tpl_label' => array (
            'name' => 'User Control Panel - Customize - Settings - Template Select - Label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Select Template To Use:',
            'section' => 'User Control Panel'
        ),
        'usercp_custom_settings_save' => array (
            'name' => 'User Control Panel - Customize - Settings - Save button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Save',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_header' => array (
            'name' => 'User Control Panel - Pages - Settings - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Categories &amp; Pages',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_homecatlabel' => array (
            'name' => 'User Control Panel - Pages - Settings - Home Category label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Home Category Name:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_restoredefaults' => array (
            'name' => 'User Control Panel - Pages - Settings - Restore Defaults link',
            'desc' => 'appears if there are no pages for this user',
            'type' => 'input',
            'default' => 'Restore Default Pages',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_restoredefaults_confirm' => array (
            'name' => 'User Control Panel - Pages - Settings - Restore Defaults Confirmation Text',
            'desc' => 'warning shown in a popup when the Restore Default Pages link is clicked',
            'type' => 'textarea',
            'default' => 'This will erase all of your Content Pages and restore the defaults. This action cannot be undone. Are you sure you want to do this?',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_defaultlabel' => array (
            'name' => 'User Control Panel - Pages - Settings - Default Page label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Default Page:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_defaultpagenull' => array (
            'name' => 'User Control Panel - Pages - Settings - Default Page null choice',
            'desc' => 'first item of the default page dropdown, select to turn off default page functionality',
            'type' => 'input',
            'default' => '--',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_addnewheader' => array (
            'name' => 'User Control Panel - Pages - Settings - Add New header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Add New',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_addnewcategory' => array (
            'name' => 'User Control Panel - Pages - Settings - Add New Category label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Add New Category:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_settings_addnewpage' => array (
            'name' => 'User Control Panel - Pages - Settings - Add New Page label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Add New Content Page:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_btn_save' => array (
            'name' => 'User Control Panel - Pages - Save button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Save',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_btn_edit' => array (
            'name' => 'User Control Panel - Pages - Edit button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Edit',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_btn_delete' => array (
            'name' => 'User Control Panel - Pages - Delete button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Delete',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_btn_cancel' => array (
            'name' => 'User Control Panel - Pages - Cancel button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Cancel',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_btn_addsub' => array (
            'name' => 'User Control Panel - Pages - Add Subcategory button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Add Subcategory',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_plh_newsub' => array (
            'name' => 'User Control Panel - Pages - Subcategory Name placeholder',
            'desc' => '',
            'type' => 'input',
            'default' => 'Subcategory Name',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_cats_header' => array (
            'name' => 'User Control Panel - Pages - Edit Categories - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Manage Categories <span style="font-size: 0.8em;">(<span class="glyphicon glyphicon-move"></span>&nbsp;reorder)</span>',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_cats_saved' => array (
            'name' => 'User Control Panel - Pages - Edit Categories - Updated',
            'desc' => 'shown when successfully saving a change to a category name or the category order',
            'type' => 'input',
            'default' => 'Categories Updated',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_page_header' => array (
            'name' => 'User Control Panel - Pages - Edit Pages - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Manage Pages <span style="font-size: 0.8em;">(<span class="glyphicon glyphicon-move"></span>&nbsp;reorder)</span>',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_page_name' => array (
            'name' => 'User Control Panel - Pages - Edit Pages - Name label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Page Name:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_page_link' => array (
            'name' => 'User Control Panel - Pages - Edit Pages - Link Text label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Page Link Text:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_page_body' => array (
            'name' => 'User Control Panel - Pages - Edit Pages - Body label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Page Body:',
            'section' => 'User Control Panel'
        ),
        'usercp_pages_page_saved' => array (
            'name' => 'User Control Panel - Pages - Edit Pages - Updated',
            'desc' => 'shown when successfully saving a change to a page or the page order',
            'type' => 'input',
            'default' => 'Content Pages Updated',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_header' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Newsletter Options',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_allownewsubs' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Allow New Subscriptions label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Allow New Subscriptions:',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_allownewsubs_yes' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Allow New Subscriptions - Yes',
            'desc' => '',
            'type' => 'input',
            'default' => 'yes',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_allownewsubs_no' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Allow New Subscriptions - No',
            'desc' => '',
            'type' => 'input',
            'default' => 'no',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_currentsubs' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Current Subscriptions label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Current Subscriptions:',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_remove' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Remove Subscribers label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Remove Subscribers:',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_remselect' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Remove Select Box label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Select subscribers to remove:',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_save' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Save button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Save',
            'section' => 'User Control Panel'
        ),
        'usercp_news_settings_saveremove' => array (
            'name' => 'User Control Panel - Newsletter - Settings - Save and Remove button',
            'desc' => 'only shown when there are active subscribers',
            'type' => 'input',
            'default' => 'Save (and remove highlighted subscribers)',
            'section' => 'User Control Panel'
        ),
        'usercp_news_send_header' => array (
            'name' => 'User Control Panel - Newsletter - Send - Header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Send a Newsletter',
            'section' => 'User Control Panel'
        ),
        'usercp_news_send_subject' => array (
            'name' => 'User Control Panel - Newsletter - Send - Subject',
            'desc' => '',
            'type' => 'input',
            'default' => 'Subject',
            'section' => 'User Control Panel'
        ),
        'usercp_news_send_bodyheader' => array (
            'name' => 'User Control Panel - Newsletter - Send - Body header',
            'desc' => '',
            'type' => 'input',
            'default' => 'Newsletter Body',
            'section' => 'User Control Panel'
        ),
        'usercp_news_send_button' => array (
            'name' => 'User Control Panel - Newsletter - Send - Send button',
            'desc' => '',
            'type' => 'input',
            'default' => 'Send',
            'section' => 'User Control Panel'
        ),
        'usercp_back_to_my_account' => array (
            'name' => 'User Control Panel - Back to My Account button',
            'desc' => '',
            'type' => 'input',
            'default' => 'My Account',
            'section' => 'User Control Panel'
        ),

        //************************** Newsletter ******************************
        'newsletter_subscribe_good' => array (
            'name' => 'Subscription to Newsletter succeeded',
            'desc' => '',
            'type' => 'input',
            'default' => 'Thanks! We have added you to our newsletter!',
            'section' => 'Newsletter'
        ),
        'newsletter_subscribe_bad' => array (
            'name' => 'Subscription to Newsletter failed',
            'desc' => '',
            'type' => 'input',
            'default' => 'That address is already subscribed to our newsletter.',
            'section' => 'Newsletter'
        ),

        //************************** List Stores Page ******************************
        'list_stores_state_filter_label' => array (
            'name' => 'List Stores page - State Filter label',
            'desc' => '',
            'type' => 'input',
            'default' => 'Show only stores located in:',
            'section' => 'List Stores Page'
        ),
        'list_stores_state_filter_default' => array (
            'name' => 'List Stores page - State Filter default text',
            'desc' => '',
            'type' => 'input',
            'default' => 'All States',
            'section' => 'List Stores Page'
        ),
        'store_tab_name' => array(
            'name' => 'Navigation Tab',
            'desc' => 'Text of the tab/link to the List Stores page, shown at the top of the default design',
            'type' => 'input',
            'default' => 'Stores',
            'section' => 'List Stores Page'
        ),
        'no_storefronts' => array (
            'name' => 'No Active Storefronts text',
            'desc' => 'Text used on the storefront list page when there are no storefronts.',
            'type' => 'input',
            'default' => 'No active stores were found.',
            'section' => 'List Stores Page'
        ),

        //************************** Contact Email ******************************
        'contact_email_subject' => array(
            'name' => 'Contact Email Subject',
            'desc' => 'Subject of the email sent by a storefront\'s contact form',
            'type' => 'input',
            'default' => 'Message from storefront visitor | ',
            'section' => 'Contact Email'
        ),
        'contact_email_greeting' => array(
            'name' => 'Contact Email Greeting',
            'desc' => 'Used in the email sent by a storefront\'s contact form (appears before receiver\'s name)',
            'type' => 'input',
            'default' => 'Hello ',
            'section' => 'Contact Email'
        ),
        'contact_email_text1' => array(
            'name' => 'Contact Email Text1',
            'desc' => 'Used in the email sent by a storefront\'s contact form',
            'type' => 'input',
            'default' => " sent you a message, and had this to say:\n\n",
            'section' => 'Contact Email'
        ),
        'contact_email_result_good' => array(
            'name' => 'Contact Email Success',
            'desc' => 'Shown to user when a storefront\'s contact form sends successfully',
            'type' => 'input',
            'default' => "Your message has been sent.",
            'section' => 'Contact Email'
        ),
        'contact_email_result_bad' => array(
            'name' => 'Contact Email Failure',
            'desc' => 'Error message for a storefront\'s contact form',
            'type' => 'input',
            'default' => "There was an internal error. Message not sent.",
            'section' => 'Contact Email'
        ),
        'contact_email_bad_email' => array(
                'name' => 'Contact Email Bad From Address',
                'desc' => 'Error message for a storefront\'s contact form',
                'type' => 'input',
                'default' => "You must enter a valid return email address. Message not sent.",
                'section' => 'Contact Email'
        ),
        'contact_email_missing_info' => array(
                'name' => 'Contact Email Missing Info',
                'desc' => 'Error message for a storefront\'s contact form',
                'type' => 'input',
                'default' => "All fields are required. Message not sent.",
                'section' => 'Contact Email'
        ),

        //************************** Template Text ******************************
        'tpl_manage_link' => array(
                'name' => 'Manage My Storefront link',
                'desc' => '',
                'type' => 'input',
                'default' => 'Manage My Storefront',
                'section' => 'Text Used In Templates'
        ),
        'tpl_cat_header' => array(
                'name' => 'Category Navigation Header',
                'desc' => '',
                'type' => 'input',
                'default' => 'Store Categories',
                'section' => 'Text Used In Templates'
        ),
        'tpl_page_header' => array(
                'name' => 'Page Navigation Header',
                'desc' => '',
                'type' => 'input',
                'default' => 'Store Pages',
                'section' => 'Text Used In Templates'
        ),
        'tpl_newsletter_header' => array(
                'name' => 'Newsletter Header',
                'desc' => '',
                'type' => 'input',
                'default' => 'Newsletter',
                'section' => 'Text Used In Templates'
        ),
        'tpl_newsletter_thanks' => array(
                'name' => 'Newsletter Confirmation',
                'desc' => '',
                'type' => 'input',
                'default' => 'Thank You!',
                'section' => 'Text Used In Templates'
        ),
    );

    /**
     * Optional function that should return details about the text
     * that this addon will be using on the client side.
     * @param Int $language_id
     * @return Array Associative array as documented by {@link addon_storefront_admin::$default_addon_text}
     */
    function init_text($language_id)
    {
        //Rename the function to remove _no_use if we need to start using addon text.
        //TODO: Need to make all "built in" text use addon text instead.
        if (geoPC::is_ent() && !isset($this->default_addon_text['opt_1_column'])) {
            //dynamically set up all the optional field header text
            for ($i = 1; $i < 21; $i++) {
                $this->default_addon_text['listings_opt_' . $i . '_column'] = array (
                    'name' => 'Optional Field ' . $i . ' column header',
                    'desc' => '',
                    'type' => 'input',
                    'default' => 'Optional Field ' . $i
                );
            }
        }
        return $this->default_addon_text;
    }
}
