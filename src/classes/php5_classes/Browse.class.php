<?php

/**
 * Holds the geoBrowse class.
 *
 * @package System
 * @since Version 4.0.0
 */

require_once CLASSES_DIR . 'site_class.php';

/**
 * Contains functions common to browsing listings
 * @package System
 * @since Version 4.0.0
 */
class geoBrowse extends geoSite
{
    /**
     * category configuration settings.
     * @var Array
     */
    public $configuration_data;

    /**
     * Creates a geoBrowse object and merges category-specific settings together
     *
     * @param int $category_id If set, will be used to set site_category.  {@since Version 7.2.2}
     */
    public function __construct($category_id = 0)
    {
        if ($category_id) {
            //go ahead and set category ID
            $this->site_category = (int)$category_id;
        }
        parent::__construct();

        $this->configuration_data = DataAccess::getInstance()->get_site_settings(true);
        if (geoPC::is_ent()) {
            $catCfg = geoCategory::getCategoryConfig($this->site_category, true);

            if ($catCfg && $catCfg['what_fields_to_use'] != 'site') {
                //there are category-specific settings for this category
                //merge them into the config array as 2nd parameter, so category settings take precedence if they exist
                $this->configuration_data = array_merge($this->configuration_data, $catCfg);
            }
        }

        if ($this->site_category) {
            $category = geoCategory::getBasicInfo($this->site_category);
            if ($category) {
                $view = geoView::getInstance();
                $view->currentCategoryName = $category['category_name'];
                $view->currentCategoryDescription = $category['description'];
            }
        }
        geoAddon::triggerUpdate('process_browsing_filters', $this->site_category);
    }

    /**
     * converts raw listing data that is common to all browsing pages into a format the templates can use.
     * This sets up ALL possible data -- it's up to the caller to configure the template to only show the appropriate columns
     *
     * @param Array $data usually a row out of the classifieds table
     * @param Array $text array of page-specific database text
     * @param bool $featured is this being displayed as part of a set of featured listings?
     * @param bool $tabular If set to false, will set country/state to blank
     *   instead of "-" when not set.  Param added in version 6.0.4
     * @return Array the formatted array
     */
    public function commonBrowseData($data, $text, $featured = false, $tabular = true)
    {
        if (!is_array($data)) {
            //no data given...can't do anything
            trigger_error('DEBUG BROWSE: no data');
            return array();
        }

        $formatted = $data;

        /**
         * This is all the simple fields, here for consistency sake with the
         * browsing display page fields
         */
        $formatted['classified_id'] = $data['id'];
        $formatted['viewed_count'] = $data['viewed'];
        //seller info
        $formatted['seller_id'] = (int)$data['seller'];
        if ($data['seller'] && $seller = geoUser::getUser($data['seller'])) {
            $formatted['seller_username'] = $seller->username;
            $formatted['seller_first_name'] = $seller->firstname;
            $formatted['seller_last_name'] = $seller->lastname;
            if ($seller->url) {
                $nofollow = DataAccess::getInstance()->get_site_setting('add_nofollow_user_links') ? ' rel="nofollow"' : '';
                $newTarget = DataAccess::getInstance()->get_site_setting('open_window_user_links') ? ' target="_blank"' : '';
                if (stristr(stripslashes($seller->url), urldecode("http://"))) {
                    $url_current_line = "<a href=\"" . $seller->url . "\"{$newTarget}$nofollow>" . $seller->url . "</a>";
                } else {
                    $url_current_line = "<a href=\"http://" . $seller->url . "\"{$newTarget}$nofollow>" . $seller->url . "</a>";
                }
                $formatted['seller_url'] = $url_current_line;
            }
            $formatted['seller_address'] = $seller->address . " " . $seller->address_2;
            $formatted['seller_city'] = $seller->city;
            $formatted['seller_state'] = geoRegion::getStateNameForUser($seller->id);
            $formatted['seller_country'] = geoRegion::getCountryNameForUser($seller->id);
            $formatted['seller_zip'] = $seller->zip;
            $formatted['seller_phone'] = geoNumber::phoneFormat($seller->phone);
            $formatted['seller_phone2'] = geoNumber::phoneFormat($seller->phone2);
            $formatted['seller_fax'] = geoNumber::phoneFormat($seller->fax);
            $formatted['seller_company_name'] = $seller->company_name;
            $formatted['seller_optional_1'] = $seller->optional_field_1;
            $formatted['seller_optional_2'] = $seller->optional_field_2;
            $formatted['seller_optional_3'] = $seller->optional_field_3;
            $formatted['seller_optional_4'] = $seller->optional_field_4;
            $formatted['seller_optional_5'] = $seller->optional_field_5;
            $formatted['seller_optional_6'] = $seller->optional_field_6;
            $formatted['seller_optional_7'] = $seller->optional_field_7;
            $formatted['seller_optional_8'] = $seller->optional_field_8;
            $formatted['seller_optional_9'] = $seller->optional_field_9;
            $formatted['seller_optional_10'] = $seller->optional_field_10;

            $formatted['member_since'] = date(trim($this->configuration_data['member_since_date_configuration']), $seller->date_joined);
        }
        if ($data["delayed_start"] == 0) {
            $formatted['date_started'] = date(trim($this->configuration_data['entry_date_configuration']), $data["date"]);
        } else {
            //TODO: This text may be on display ad page
            $formatted['date_started'] = $this->messages[500225];
        }
        $formatted['address_data'] = ucwords(geoString::fromDB($data["location_address"]));

        //Get city data
        $overrides = geoRegion::getLevelsForOverrides();
        if ($overrides['city']) {
            $data['location_city'] = geoRegion::getNameForListingLevel($data['id'], $overrides['city']);
        }
        $formatted['city_data'] = ucwords(geoString::fromDB($data['location_city']));
        $formatted['state_data'] = geoRegion::getStateNameForListing($data['id']);
        $formatted['country_data'] = geoRegion::getCountryNameForListing($data['id']);
        $formatted['zip_data'] = geoString::fromDB($data['location_zip']);

        if ($this->fields->payment_types->is_enabled) {
            //payment_types - both on and off-site combined (for now)
            //in future may seperate them if there is a demand, for now they
            //are displayed in the same list.
            $payment_options = array();
            if ($data["item_type"] == 2) {
                //on-site payment options
                $vars = array (
                    'listing_id' => $data['id'],
                );
                $this_payment_options = geoSellerBuyer::callDisplay('displayPaymentTypesListing', $vars, ', ');
                if (strlen($this_payment_options) > 0) {
                    $payment_options[0] = $this_payment_options;
                }
            }

            //off-site payment options
            $data["payment_options"] = geoString::fromDB($data["payment_options"]);
            $this_payment_options = str_replace("||", ", ", $data["payment_options"]);
            if (strlen($this_payment_options) > 0) {
                $payment_options[1] = $this_payment_options;
            }
            if (trim(implode(' ', $payment_options))) {
                $formatted['payment_options'] = implode(', ', $payment_options);
            }
            unset($payment_options);
        }
        if ($this->fields->email->type_data == 'reveal' && ($data["expose_email"]) && $data["email"]) {
            $formatted['public_email'] = geoString::fromDB($data["email"]);
        }
        if ($this->fields->phone_1->is_enabled && $data["phone"]) {
            $formatted['phone_data'] = geoNumber::phoneFormat(geoString::fromDB($data["phone"]));
        }
        if ($this->fields->phone_2->is_enabled && $data["phone2"]) {
            $formatted['phone2_data'] = geoNumber::phoneFormat(geoString::fromDB($data["phone2"]));
        }
        if ($this->fields->fax->is_enabled && $data["fax"]) {
            $formatted['fax_data'] = geoNumber::phoneFormat(geoString::fromDB($data["fax"]));
        }
        if ($this->fields->url_link_1->is_enabled) {
            if (strlen(trim($data["url_link_1"])) > 0) {
                $url = trim(geoString::fromDB($data['url_link_1']));

                if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
                    $url = 'http://' . $url;
                }
                $formatted['url_link_1_href'] = $url;
            }
        }
        if ($this->fields->url_link_2->is_enabled) {
            if (strlen(trim($data["url_link_2"])) > 0) {
                $url = trim(geoString::fromDB($data['url_link_2']));

                if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
                    $url = 'http://' . $url;
                }
                $formatted['url_link_2_href'] = $url;
            }
        }
        if ($this->fields->url_link_3->is_enabled) {
            if (strlen(trim($data["url_link_3"])) > 0) {
                $url = trim(geoString::fromDB($data['url_link_3']));

                if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
                    $url = 'http://' . $url;
                }
                $formatted['url_link_3_href'] = $url;
            }
        }
        if ($this->fields->mapping_location->is_enabled) {
            $formatted['mapping_location'] = geoString::fromDB($data['mapping_location']);
        }
        if ($data['item_type'] == 2) {
            if ($data["auction_type"] == 1) {
                $formatted['auction_type_data'] = $this->messages[102707];
            } elseif ($data['auction_type'] == 2) {
                $formatted['auction_type_data'] = $this->messages[102708];
            } elseif ($data['auction_type'] == 3) {
                $formatted['auction_type_data'] = $this->messages[500981];
            }
            //buy now price
            if ($data["auction_type"] != 2 && $data['buy_now'] > 0) {
                //NOT dutch auction, see if should show buy now
                $show_buy_now = false;
                $reserveMet = $data['reserve_price'] > 0
                    && $data['current_bid'] > 0
                    && (
                        (
                            $data['auction_type'] != 3
                            && $data['current_bid'] >= $data['reserve_price']
                        ) || (
                            $data['auction_type'] == 3
                            && $data['current_bid'] <= $data['reserve_price']
                        )
                    );
                if ($data['buy_now_only']) {
                    //it's buy now only, of course show it...
                    $show_buy_now = true;
                } elseif ($data['current_bid'] == 0) {
                    //there are no bids yet, so show buy now option
                    $show_buy_now = true;
                } elseif ($data['current_bid'] != 0 && $this->configuration_data['buy_now_reserve'] == 1 && !$reserveMet) {
                    //there is a bid, but it is set to allow buy now until reserve is met
                    //and reserve is not met yet
                    $show_buy_now = true;
                }
                if ($show_buy_now) {
                    $formatted['buy_now_data'] = geoString::displayPrice($data["buy_now"], $data['precurrency'], $data['postcurrency'], 'listing');
                }
            }
            if ($data['start_time']) {
                $data['bid_start_date'] = date(trim($this->configuration_data['entry_date_configuration']), $data['start_time']);
            } else {
                $data['bid_start_date'] = date(trim($this->configuration_data['entry_date_configuration']), $data['date']);
            }
        }

        /**
         * END of the "consistency sake" added vars
         */

        if ($data['item_type'] == 1) {
            $formatted['type'] = $text['item_type']['classified'];
        } elseif ($data['item_type'] == 2) {
            $formatted['type'] = $text['item_type']['auction'];
        }

        if ($data['business_type']) {
            $formatted['business_type'] = ($data['business_type'] == 1) ? $text['business_type'][1] : $text['business_type'][2];
        } else {
            $formatted['business_type'] = '';
        }

        $no_image_url = ($this->messages[500795]) ? geoTemplate::getURL('', $this->messages[500795]) : '';
        $photo_icon_url = ($this->messages[500796]) ? geoTemplate::getURL('', $this->messages[500796]) : '';
        if ($data['image'] > 0) {
            if ($this->configuration_data['photo_or_icon'] == 1) {
                $formatted['full_image_tag'] = true;
                if ($featured) {
                    //featured listings have separate size settings for thumbnails
                    //NOTE: browsing modules do some fancy trickery and always use the "featured" sizes
                    $width = $this->configuration_data['featured_thumbnail_max_width'];
                    $height = $this->configuration_data['featured_thumbnail_max_height'];
                } else {
                    //let it use defaults built into display_thumbnail()
                    $width = $height = 0;
                }
                $formatted['image'] = geoImage::display_thumbnail($data['id'], $width, $height, 1, ($data['is_storefront'] ? $data['seller'] : 0), ($data['is_storefront'] ? 'store' : 'aff'), 0, $this->configuration_data['dynamic_image_dims']);
            } else {
                $formatted['full_image_tag'] = false;
                $formatted['image'] = $photo_icon_url;
            }
        } elseif ($no_image_url && $this->configuration_data['photo_or_icon'] == 1) {
            $formatted['full_image_tag'] = true;
            if ($featured) {
                //featured listings have separate size settings for thumbnails
                //NOTE: browsing modules do some fancy trickery and always use the "featured" sizes
                $width = $this->configuration_data['featured_thumbnail_max_width'];
                $height = $this->configuration_data['featured_thumbnail_max_height'];
            } else {
                //use normal thumbnail display dimensions
                $width = DataAccess::getInstance()->get_site_setting('thumbnail_max_width');
                $height = DataAccess::getInstance()->get_site_setting('thumbnail_max_height');
            }
            $formatted['image'] = geoImage::display_image($no_image_url, $width, $height, 0, 0, '', $this->configuration_data['dynamic_image_dims']);
        } else {
            $formatted['full_image_tag'] = true;
            $formatted['image'] = '';
        }

        $formatted['icons'] = array(
                    'sold' => (( $data['sold_displayed']) ? true : false),
                    'buy_now' => (($data['buy_now'] != 0 && ($data['current_bid'] == 0 || ($this->configuration_data['buy_now_reserve'] && $data['current_bid'] < $data['reserve_price']))) ? true : false),
                    'reserve_met' => (($data['reserve_price'] > 0 && $data['current_bid'] > 0 && (($data['auction_type'] != 3 && $data['current_bid'] >= $data['reserve_price']) || ($data['auction_type'] == 3 && $data['current_bid'] <= $data['reserve_price']))) ? true : false),
                    'reserve_not_met' => (($data['reserve_price'] > 0 && ($data['current_bid'] == 0 || ($data['auction_type'] != 3 && $data['current_bid'] < $data['reserve_price']) || ($data['auction_type'] == 3 && $data['current_bid'] > $data['reserve_price']))) ? true : false),
                    'no_reserve' => (($data['item_type'] == 2 && $data['reserve_price'] == 0 && $data['buy_now_only'] == 0) ? true : false),
                    'attention_getter' => (($data['attention_getter']) ? true : false),
                    'verified' => geoUser::isVerified($data['seller']),//we set this in a second
                    'addon_icons' => geoAddon::triggerDisplay('add_listing_icons', $data, geoAddon::ARRAY_STRING),
        );
        $formatted['attention_getter_url'] = geoString::fromDB($data['attention_getter_url']);

        $formatted['title'] = geoString::fromDB($data['title']);
        if ($this->configuration_data['module_title_and_optional_length'] && strlen($formatted['title']) > $this->configuration_data['module_title_and_optional_length']) {
            //browsing pic modules use the "length of description" setting to also apply to titles and optional fields
            $formatted['title'] = geoString::substr($formatted['title'], 0, $this->configuration_data['module_title_and_optional_length']) . '...';
        }

        $description = $data['description'];

        if ($this->configuration_data['display_all_of_description'] != 1) {
            $description = geoFilter::listingDescription($description, true);//force always strip tags
            $description = geoFilter::listingShortenDescription($description, $this->configuration_data['length_of_description']); //shorten
        } else {
            $description = geoFilter::listingDescription($description);
        }
        $formatted['description'] = $description;

        $formatted['tags'] = geoListing::getTags($data['id']);


        $formatted['location_breadcrumb'] = geoRegion::displayRegionsForListing($data['id']);

        foreach ($data['regionInfo']['enabledLevels'] as $level) {
            $formatted['region_level_' . $level] = geoRegion::getNameForListingLevel($data['id'], $level);
        }

        $formatted['address'] = geoString::fromDB($data['location_address']);
        $formatted['city'] = geoString::fromDB($data['location_city']);
        $formatted['state'] = geoRegion::getStateNameForListing($data['id']);
        $formatted['country'] = geoRegion::getCountryNameForListing($data['id']);
        $formatted['zip'] = geoString::fromDB($data['location_zip']);

        if ($formatted['country'] === 'none') {
            //country not selected, replace with - since none is not something that
            //can be changed for language-specific
            $formatted['country'] = ($tabular) ? '-' : '';
        }

        if ($formatted['state'] === 'none') {
            //state not selected, replace with - since none is not something that
            //can be changed for language-specific
            $formatted['state'] = ($tabular) ? '-' : '';
        }

        if ($data['item_type'] == 1) {
            //this is a classified -- show the price
            $price = $data['price'];
        } elseif ($data['item_type'] == 2 && ($data['auction_type'] == 1 || $data['auction_type'] == 2)) {
            //this is an auction -- figure out which price to show
            if ($data['buy_now_only'] == 1) {
                //buy now only -- show buy now price
                $price = $data['buy_now'];
            } elseif ($data['minimum_bid'] != 0) {
                //minimum bid exists -- show it if it is at least the starting bid
                if ($data['minimum_bid'] < $data['starting_bid']) {
                    $data['minimum_bid'] = $data['starting_bid'];
                }
                $price = $data['minimum_bid'];
            } else {
                //show starting bid
                $price = $data['minimum_bid'] = $data['starting_bid'];
            }
        } elseif ($data['item_type'] == 2 && $data['auction_type'] == 3) {
            //for reverse auctions, the price is always the "minimum_bid" field (which is actually the maximum bid)

            $price = $data['minimum_bid'];
        }
        $formatted['precurrency'] = geoString::fromDB($data['precurrency']);
        $formatted['postcurrency'] = geoString::fromDB($data['postcurrency']);
        $formatted['price'] = geoString::displayPrice($price, $data['precurrency'], $data['postcurrency'], 'listing');
        $formatted['minimum_bid'] = geoString::displayPrice($data['minimum_bid'], $data['precurrency'], $data['postcurrency'], 'listing');
        $formatted['starting_bid'] = geoString::displayPrice($data['starting_bid'], $data['precurrency'], $data['postcurrency'], 'listing');
        $formatted['current_bid'] = geoString::displayPrice($data['current_bid'], $data['precurrency'], $data['postcurrency'], 'listing');

        for ($i = 1; $i <= 20; $i++) {
            //Set both optionals[i] and optional_field_i because latter is used
            //as field names for {listing}
            $formatted['optionals'][$i] = $formatted['optional_field_' . $i] = geoString::fromDB($data['optional_field_' . $i]);
            $field = 'optional_field_' . $i;
            $field_type = $this->fields->$field->field_type;
            if ($field_type == 'cost') {
                //display price for any optional fields that "adds cost"
                $formatted['optionals'][$i] = $formatted['optional_field_' . $i] = geoString::displayPrice($formatted['optionals'][$i], $data['precurrency'], $data['postcurrency'], 'listing');
            } elseif ($field_type == 'date') {
                //use short field type format
                $formatted['optionals'][$i] = $formatted['optional_field_' . $i] = geoCalendar::display($formatted['optionals'][$i], true);
            } elseif ($this->configuration_data['module_title_and_optional_length'] && strlen($formatted['optionals'][$i]) > $this->configuration_data['module_title_and_optional_length']) {
                //browsing pic modules use the "length of description" setting to also apply to titles and optional fields
                $formatted['optionals'][$i] = geoString::substr($formatted['optionals'][$i], 0, $this->configuration_data['module_title_and_optional_length']) . '...';
            }
        }

        $formatted['num_bids'] = geoListing::bidCount($data['id']);

        if ($data['item_type'] == 1 && (!$this->fields->classified_start->is_enabled  || $text['mixed_settings']['hide_entry_date_classified'])) {
            //this is a classified, and we're not showing classified entry date
            $formatted['entry_date'] = false;
        } elseif ($data['item_type'] == 2 && (!$this->fields->auction_start->is_enabled  || $text['mixed_settings']['hide_entry_date_auction'])) {
            //this is an auction, and we're not showing auction entry date
            $formatted['entry_date'] = false;
        } else {
            $formatted['entry_date'] = date($this->configuration_data['entry_date_configuration'], $data['date']);
        }


        if ($data['item_type'] == 1 && (!$this->fields->classified_time_left->is_enabled || $text['mixed_settings']['hide_time_left_classified'])) {
            //this is a classified, and we're not showing classified time left
            $formatted['time_left'] = false;
        } elseif ($data['item_type'] == 2 && (!$this->fields->auction_time_left->is_enabled || $text['mixed_settings']['hide_time_left_auction'])) {
            //this is an auction, and we're not showing auction time left
            $formatted['time_left'] = false;
        } elseif ($data['ends'] == 0) {
            //this is an "unlimited duration" listing
            $formatted['time_left'] = '-';
        } else {
            $weeks = $this->DateDifference('w', geoUtil::time(), $data['ends']);
            $remaining_weeks = ($weeks * 604800);
            $days = $this->DateDifference('d', (geoUtil::time() + $remaining_weeks), $data['ends']);
            $remaining_days = ($days * 86400);
            $hours = $this->DateDifference('h', (geoUtil::time() + $remaining_days), $data['ends']);
            $remaining_hours = ($hours * 3600);
            $minutes = $this->DateDifference('m', (geoUtil::time() + $remaining_hours), $data['ends']);
            $remaining_minutes = ($minutes * 60);
            $seconds = $this->DateDifference('s', (geoUtil::time() + $remaining_minutes), $data['ends']);

            if ($weeks > 0) {
                $formatted['time_left'] = $weeks . ' ' . $text['time_left']['weeks'] . ', ' . $days . ' ' . $text['time_left']['days'];
            } elseif ($days > 0) {
                $formatted['time_left'] = $days . ' ' . $text['time_left']['days'] . ', ' . $hours . ' ' . $text['time_left']['hours'];
            } elseif ($hours > 0) {
                $formatted['time_left'] = $hours . ' ' . $text['time_left']['hours'] . ', ' . $minutes . ' ' . $text['time_left']['minutes'];
            } elseif ($minutes > 0) {
                $formatted['time_left'] = $minutes . ' ' . $text['time_left']['minutes'] . ', ' . $seconds . ' ' . $text['time_left']['seconds'];
            } elseif ($seconds > 0) {
                $formatted['time_left'] = $seconds . ' ' . $text['time_left']['seconds'];
            } else {
                //listing closed
                $formatted['time_left'] = $text['time_left']['closed'];
            }
        }

        //Leveled fields
        $formatted['leveled'] = geoListing::getLeveledValues($data['id']);

        $formatted['categories'] = geoListing::getCategories($data['id']);

        $formatted['edit'] = 'edit';
        $formatted['delete'] = 'delete';

        if ($data['featured_ad'] || $data['featured_ad_2'] || $data['featured_ad_3'] || $data['featured_ad_4'] || $data['featured_ad_5']) {
            $formatted['is_featured'] = 1;
        }

        return $formatted;
    }


    /**
     * Displays an error message, then exits the script.
     * If no message is provided, attempts to find one in the site class
     * Failing that, displays a generic error message
     *
     * @param $error String An error message to show
     */
    public function browse_error($error = '')
    {
        if (!$error && $this->error_message) {
            $error = $this->error_message;
        }

        $this->page_id = 1;
        $this->get_text();
        $tpl = new geoTemplate('system', 'browsing');
        $tpl->assign('error', $error);
        $this->body = $tpl->fetch('error.tpl');

        //make it 404 if that setting turned on
        self::pageNotFound();

        $this->display_page();
        include_once GEO_BASE_DIR . 'app_bottom.php';
        exit;
    }

    /**
     * If setting to use 404 header is turned on, this method will send a 404
     * not found header.  Otherwise nothing will happen when this is called.
     *
     * @since Version 5.1.0
     */
    public static function pageNotFound()
    {
        if (DataAccess::getInstance()->get_site_setting('use_404')) {
            //use 404 status code, so search engines don't index this page any more.
            header('HTTP/1.0 404 Not Found', 404);
        }
    }

    /**
     * Returns the order by part of a SQL statement depending on the specified
     * browse type number, specify false to use site-default.
     *
     * @param int $browse_type The number to browse by.
     * @param int $category the current browsing category
     * @return string
     */
    public function getOrderByString($browse_type = false, $category = 0)
    {
        if ($browse_type === false) {
            //nothing passed in -- check class var
            $browse_type = $this->browse_type;
        }

        //grab field settings, so that we can make it do numerical sort where applicable
        $fields = geoFields::getInstance(0, $category);

        //sort numbers "deprecated"
        //9 11 35 37 39 41 67

        $sort_types = array (
            1 => array('minimum_bid','price','buy_now'),
            3 => 'date',
            5 => 'title',
            7 => 'location_city',
            //9 => 'location_state',
            //11 => 'location_country',
            13 => 'location_zip',
            //15-33 - optional fields 1-10 set in loop below
            43 => 'business_type',
            //45-63 - optional fields 10-20 set in loop below
            //65 => '',  ////***65/66 - reserved cases, default for some SEO pages***
            69 => 'ends',
            71 => 'image > 0', //this is valid mysql: "ORDER BY image > 0 DESC" means "show listings with at least one image first"
        );

        //Use loop for optional fields, to shorten even more
        for ($i = 1; $i <= 20; $i++) {
            $from = ($i <= 10) ? ( 2 * ($i - 1) + 15 ) : ( 2 * ($i - 11) + 45 ) ;
            $field = 'optional_field_' . $i;
            $sort_types[$from] = (in_array($fields->$field->field_type, array('number','cost'))) ? "($field * 1)" : $field;
        }

        //ODD IS ASC, EVEN THAT FOLLOWS IS DESC
        //e.g. 61 => optional 19 ASC ~~ 62 => optional 19 DESC

        //Sort order by ASC or DESC depend on if number is even or odd.
        //even numbers are DESC, odd numbers are ASC (a%2=0 means even, a%2=1 means odd)
        $asc_desc = ($browse_type % 2 == 0) ? 'DESC' : 'ASC';

        //fix ones where odd version is desc, and even version is asc (backwards of normal)
        $asc_backwards = array (
            //if there are ever any where odd num is DESC and even is ASC, add that number to
            //this array, for instance if 1 and 2 were backwards it would look like:
            //1,2
        );
        if (in_array($browse_type, $asc_backwards)) {
            //backwards ones, even # is DESC, odd # is ASC (a%2=0 means even, a%2=1 means odd)
            $asc_desc = ($browse_type % 2 == 0) ? 'ASC' : 'DESC';
        }

        //Goal: if it's an even number, get it to be 1 less.  (a%2=0 means even, a%2=1 means odd)
        $browse_type = (($browse_type % 2 == 0) ? $browse_type - 1 : $browse_type);


        if ($browse_type <= 0 || !isset($sort_types[$browse_type])) {
            //default case, this is a special case where better placement comes first!
            $order_by = "`better_placement` DESC, `date` DESC";
        } else {
            //use some fanciness with arrays to avoid a huge gigantic long switch.
            $sort_fields = (is_array($sort_types[$browse_type])) ? $sort_types[$browse_type] : array($sort_types[$browse_type]);
            $sort = array();
            foreach ($sort_fields as $field) {
                $sort [] .= "$field $asc_desc"; //don't use `backticks` on $field, or inequality conditions (like 71) will break
            }
            $sort = implode(', ', $sort);
            $order_by = "$sort, `better_placement` DESC";
        }
        //die($order_by);
        return $order_by;
    }

    /**
     * Finds out if a given id number has a listing associated with it.
     * Included here mainly for legacy purposes, this may be removed in the
     * future.  Instead, you would call geoListing::getListing($classified_id,false)
     * and if the result produced an object, then the listing exists.  If it
     * returned false, you know the listing does not exist.
     *
     * @param int $classified_id the id to check
     * @return bool true if listing exists, false otherwise
     */
    public function classified_exists($classified_id = 0)
    {
        if (!is_numeric($classified_id) || $classified_id <= 0) {
            return false;
        }
        $listing = geoListing::getListing($classified_id, false);
        return is_object($listing);
    }

    /**
     * Add the specified category to the WHERE clause for the $query
     *
     * @param geoTableSelect $query
     * @param int|array $category_id a single category ID, or an array of category IDs
     * @return boolean True if where subquery added successfully, false otherwise
     * @since 7.4.0
     */
    public function whereCategory($query, $categories)
    {
        if (!$categories) {
            return false;
        }

        if (!is_array($categories)) {
            //several places pass in only a single, scalar value instead of an array
            //make it be an array so we can treat everything the same below
            $categories = array((int)$categories);
        }

        $db = DataAccess::getInstance();
        $classTable = geoTables::classifieds_table;
        $listCatTable = geoTables::listing_categories;

        $cat_subquery = "SELECT * FROM $listCatTable WHERE $listCatTable.`listing`=$classTable.`id` AND
						 $listCatTable.`category` IN (" . implode(',', $categories) . ")";


        if ($this->configuration_data && isset($this->configuration_data['display_sub_category_ads'])) {
            $display_sub_category_ads = $this->configuration_data['display_sub_category_ads'];
        } else {
            $display_sub_category_ads = $db->get_site_setting('display_sub_category_ads');
        }

        if (!$display_sub_category_ads) {
            $cat_subquery .= " AND $listCatTable.`is_terminal`='yes'";
        }

        $query->where("EXISTS ($cat_subquery)", 'category');
        return true;
    }

    /**
     * Populates category browsing information, according to $this->site_category,
     * using category cache if available.  Currently it returns the navigation
     * contents, but that could change once we ge around to re-doing it so that
     * the category data is cached using the geoCache system instead of just
     * saving category browsing to DB.
     *
     * @param array $text Associative array of text to use in template, with following
     *   indexes: back_to_normal_link, tree_label, main_category, no_subcats
     * @param string $cacheNamePrefix The DB column prefix for the category cache
     * @param bool $isHomePage If true, will bypass normal check of whether displaying
     *   category navigation is on, since home page has it's own setting for that.  Setting
     *   added in version 7.3.2
     * @return string The category browsing HTML to use on the page.
     * @since Version 5.1.0
     */
    public function categoryBrowsing($text = array(), $cacheNamePrefix = '', $isHomePage = false)
    {
        if (geoPC::is_print() && $this->db->get_site_setting('disableAllBrowsing')) {
            return;
        }
        $category_cache = false;
        if ($this->site_category && $this->db->get_site_setting('use_category_cache') && !$this->db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere()) {
            //see if we have a valid cache saved
            $sql = "select `{$cacheNamePrefix}category_cache` from " . geoTables::categories_languages_table . " where {$cacheNamePrefix}cache_expire > ? and category_id = ? and language_id = ?";
            $category_cache = geoString::fromDB($this->db->GetOne($sql, array(geoUtil::time(), $this->site_category, $this->language_id)));
        }

        if (!$category_cache) {
            //no cache saved -- make a new one

            //get the categories inside of this category

            $sql = "SELECT lang.category_id, lang.category_name, lang.description, lang.language_id, lang.`category_cache`, lang.`cache_expire`,
				lang.category_image, lang.category_image_alt, lang.seo_url_contents, cat.auction_category_count, cat.category_count, cat.parent_id
				FROM " . geoTables::categories_table . " as cat, " . geoTables::categories_languages_table . " as lang where
				cat.parent_id = " . $this->site_category . " and cat.category_id = lang.category_id and lang.language_id = " . $this->language_id . "
				and cat.enabled='yes' order by cat.display_order, lang.category_name";


            $category_result = $this->db->Execute($sql);

            if (!$category_result) {
                $this->error_message = "<span class=\"error_message\">" . urldecode($this->messages[65]) . "</span>";
                return false;
            }

            //get category list, then optionally save it to cache for next time
            $cacheTpl = new geoTemplate('system', 'browsing');
            $cacheTpl->assign(geoView::getInstance()->getAllAssignedVars());
            $tpl_vars = array();
            $tpl_vars['category'] = $this->site_category;
            if (!$text) {
                //set default text
                $text = array(
                    'back_to_normal_link' => $this->messages[876],
                    'tree_label' => $this->messages[680],
                    'main_category' => $this->messages[18],
                    'no_subcats' => $this->messages[20]
                );
            }
            $tpl_vars['text'] = $text;

            if (!$this->browse_type) {
                $this->browse_type = 0;
            }
            $a_var = (isset($_GET['a']) && (int)$_GET['a']) ? (int)$_GET['a'] : 5;
            $c_str = (isset($_GET['c']) && (int)$_GET['c']) ? '&amp;c=' . (int)$_GET['c'] : ''; //preserve sort order, but default to nothing if not present
            $d_str = (isset($_GET['d']) && (int)$_GET['d']) ? '&amp;d=' . (int)$_GET['d'] : ''; //preserve lookback (for "newest listings" pages), default to nothing if not present
            $tpl_vars['link'] = $this->db->get_site_setting('classifieds_file_name') . "?a=$a_var" . $c_str . $d_str . $this->browsing_options['query_string'] . "&amp;b=";
            //Make sure the link to the top just links to "home" page for normal browsing
            $tpl_vars['link_top'] = ($a_var == 5) ? $this->db->get_site_setting('classifieds_file_name') : $tpl_vars['link'] . '0';

            $tpl_vars['tree_display_mode'] = $this->configuration_data['category_tree_display'];
            if ($this->configuration_data['category_tree_display'] != 3) {
                $category_tree = geoCategory::getTree($this->site_category);
                if (is_array($category_tree)) {
                    $tpl_vars['array_tree'] = $category_tree;
                } else {
                    $tpl_vars['string_tree'] = $category_tree;
                }

                $tpl_vars['browse_type'] = $this->browse_type;
            }

            $current_category_name = geoCategory::getName($this->site_category);
            $tpl_vars['current_category_name'] = $current_category_name->CATEGORY_NAME;
            if ($this->configuration_data['display_category_navigation'] || $isHomePage) {
                if (!$category_result->RecordCount()) {
                    if ($this->configuration_data['display_no_subcategory_message']) {
                        $tpl_vars['show_no_subcats'] = true;
                    } else {
                        //no subcats to show, but option to show 'no subcats' message is off
                    }
                } else {
                    $tpl_vars['show_subcats'] = true;
                    $tpl_vars['category_columns'] = $columns = ($this->site_category) ? $this->configuration_data['number_of_browsing_subcategory_columns'] : $this->configuration_data['number_of_browsing_columns'];
                    $tpl_vars['column_width'] = floor(100 / $columns) . '%';
                    $tpl_vars['column_count'] = $columns;

                    $categories = array();

                    $category_new_ad_limit = $this->db->get_site_setting('category_new_ad_limit');

                    $catCount = 0;
                    //simple hack for now, don't show categories unless there is no
                    //main category and there is no "back to normal" link.
                    $showSubcategories = !($this->site_category) && !$text['back_to_normal_link'];

                    while ($row = $category_result->FetchRow()) {
                        //let category class have data so it doesn't need to look it up again
                        //in the same page load
                        geoCategory::addCategoryResult($row);
                        if ($this->configuration_data['display_category_count']) {
                            $row ['category_count'] = $this->display_category_count(0, $row['category_id']);
                        } else {
                            //admin has showing counts turned off, so remove the count data
                            $row['category_count'] = false;
                        }
                        $row ['category_name'] = geoString::fromDB($row['category_name']);
                        $row ['category_description'] = geoString::fromDB($row['description']);
                        $row ['category_image'] = geoString::fromDB($row['category_image']);
                        $row ['category_image_alt'] = geoString::fromDB($row['category_image_alt']);

                        if ($category_new_ad_limit) {
                            $row ['new_ad_icon'] = geoCategory::new_ad_icon_use($row['category_id']);
                        }
                        $row['count_add'] = 1;
                        if ($showSubcategories) {
                            $sql = "SELECT lang.category_id, lang.category_name, lang.description, lang.language_id, lang.`category_cache`, lang.`cache_expire`,
								lang.category_image, lang.category_image_alt, lang.seo_url_contents, cat.auction_category_count, cat.category_count, cat.parent_id FROM " . geoTables::categories_table . " as cat, " . geoTables::categories_languages_table . " as lang where
								cat.parent_id = " . (int)$row['category_id'] . " and cat.category_id = lang.category_id and lang.language_id = " . $this->language_id . "
								and cat.enabled='yes' " . (($isHomePage) ? " and cat.front_page_display='yes' " : "") . " order by cat.display_order, lang.category_name";

                            $row['sub_categories'] = $this->db->GetAll($sql);
                            //let the category class have the subcategory data, so it doesn't have
                            //to look it up again during this page load
                            geoCategory::addCategoryResults($row['sub_categories']);

                            //2 "sub category" is roughly the height as single main category
                            if ($row['sub_categories']) {
                                $row['count_add'] = ceil(count($row['sub_categories']) / 2) + 1;
                            }
                        }
                        $catCount += $row['count_add'];
                        $categories [$row['category_id']] = $row;
                    }

                    $cat_alpha = $this->db->get_site_setting('cat_alpha_across_columns');

                    $maxColumnCount = ceil($catCount / $columns);

                    $tpl_vars['categories'] = self::categoryColumnSort($categories, $columns, $cat_alpha, $maxColumnCount);

                    $tpl_vars['show_descriptions'] = $this->db->get_site_setting('display_category_description');
                }
            }
            if (!$isHomePage) {
                $tpl_vars['streamlined'] = !empty($tpl_vars['categories']);
                $tpl_vars['in_terminal_category'] = (bool)($category_result->RecordCount() == 0);
            }
            $cacheTpl->assign($tpl_vars);
            $category_cache = $cacheTpl->fetch('common/category_block.tpl');

            if ($this->site_category && $this->db->get_site_setting('use_category_cache') && !$this->db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere()) {
                $recache_time = geoUtil::time() + (3600 * $this->db->get_site_setting('use_category_cache'));
                $sql = "update " . $this->db->geoTables->categories_languages_table . " set
					{$cacheNamePrefix}category_cache = ?,
					{$cacheNamePrefix}cache_expire = ?
					where category_id = ? and language_id = ?";
                $cache_result = $this->db->Execute($sql, array(geoString::toDB($category_cache), $recache_time, $this->site_category, $this->language_id));
                if (!$cache_result) {
                    trigger_error('DEBUG CACHE: failed to save cache');
                }
            }
        }
        geoView::getInstance()->addJScript(geoTemplate::getUrl('js', 'system/browsing/category_popup.js'));
        return $category_cache;
    }

    /**
     * Sorts an array of categories into a number of columns with a max length
     * @param Array $categories pre-constructed array of categories. several assumed values. @see geoBrowse::categoryBrowsing()
     * @param int $columns number of columns to sort into
     * @param bool $cat_alpha true if sorting across columns instead of down them
     * @param int $maxColumnCount maximum length of each column (will increase recursively if needed)
     * @return Array $categories_sorted Like $categories, but sorted.
     * @since 7.0.0
     */
    public static function categoryColumnSort($categories, $columns, $cat_alpha, $maxColumnCount)
    {
        if (!$categories) {
            //no categories to sort!
            return array();
        }
        $currentColumn = 1; //IMPORTANT: column numbers are NOT zero-indexed!
        $currentCount = 0;

        $categories_sorted = array();

        if ($cat_alpha) {
            //sorting across columns -- this is the easy way to do things
            foreach ($categories as $row) {
                $categories_sorted [$currentColumn++][] = $row;
                //reset back to 1 if needed.
                if ($currentColumn > $columns) {
                    $currentColumn = 1;
                }
            }
            return $categories_sorted;
        }

        //a little more complicated when sorting down
        foreach ($categories as $row) {
            $add = ceil(count(!empty($row['sub_categories']) ? $row['sub_categories'] : []) / 2) + 1;

            if ($currentCount > 0 && ($currentCount + $add) > $maxColumnCount && $currentColumn < $columns) {
                //adding this category would take up more than the alloted vertical space. go to the next column.
                //notes: - if $currentCount = 0, this is the first category in this column. leave it even if it's "too long."
                //       - can't go to the next column if already in the last column (so in that case, leave it here)

                $currentColumn++;
                $currentCount = 0; //starting a new column resets the count of items in the column to 0
            }
            //add this category to the currentColumn, and track the number of categories added to that column so far
            $currentCount += $add;
            $categories_sorted[$currentColumn][] = $row;
        }

        //if, at the end, $currentCount is substantively larger than $maxColumnCount, we're wasting space elsewhere in the table
        //add a padding to $maxColumnCount and try again
        if ($currentCount >= ceil($maxColumnCount * 1.33)) {
            return self::categoryColumnSort($categories, $columns, $cat_alpha, ceil($maxColumnCount * 1.2));
        }

        return $categories_sorted;
    }

    /**
     * Get what the current browsing view should be, based on the default or if
     * set, the last requested view as saved in the session.  It will be either
     * grid, list, or gallery
     *
     * @return string
     * @since Version 7.1.0
     */
    public function getCurrentBrowseView()
    {
        $session = geoSession::getInstance();

        $browse_view = $this->db->get_site_setting('default_browse_view');
        $browse_view = ($browse_view) ? $browse_view : 'grid';

        $valid_views = array ('grid','list','gallery');

        $browse_view = $session->get('browse_view', $browse_view);

        if (isset($_GET['browse_view']) && in_array($_GET['browse_view'], $valid_views)) {
            $browse_view = $_GET['browse_view'];
            //remember it for this session...
            $session->set('browse_view', $browse_view);
        }

        //one final check, make sure browse view is one of the enabled types
        $browse_view = (in_array($browse_view, $valid_views)) ? $browse_view : 'grid';
        return $browse_view;
    }

    /**
     * Get array of sort options to show in sort drop-down.
     *
     * @param array $fields Array of fields as returned by geoFields::getDisplayLocationFields()
     * @param array $txt Array of labels, each array entry is sort_id => label
     * @return array An array in same format as the text array, but with entries
     *   that should not show filtered out.
     * @since Version 7.1.0
     */
    public function getSortOptions($fields, $txt)
    {
        $sortOptions = array();

        if (!$this->db->get_site_setting('browse_sort_dropdown_display')) {
            //sorting dropdown turned off anyways
            return $sortOptions;
        }

        $field_map = array (
            1 => 'price',
            3 => array('c' => 'classified_start','a' => 'auction_start'),
            5 => 'title',
            7 => 'city',
            13 => 'zip',
            43 => 'business_type',
            69 => array('c' => 'classified_time_left','a' => 'auction_time_left'),
            71 => 'photo',
            );
        //set optionals with loop
        for ($i = 1; $i <= 20; $i++) {
            $from = ($i <= 10) ? ( 2 * ($i - 1) + 15 ) : ( 2 * ($i - 11) + 45 ) ;
            $field_map[$from] = 'optional_field_' . $i;
        }

        foreach ($txt as $sort_id => $label) {
            if ($sort_id > 0 && !strlen($label)) {
                //label is empty, don't show option
                continue;
            }
            //Note: we always keep sort ID of 0
            if ($sort_id > 0) {
                $field_check = $field_map[(($sort_id % 2 == 0) ? $sort_id - 1 : $sort_id)];
                if (is_array($field_check)) {
                    //map is an array, one entry 'c' for field if classifieds turned on,
                    //one entry for 'a' if auctions turned on
                    $class = (geoMaster::is('classifieds') && isset($fields[$field_check['c']]) && $fields[$field_check['c']]);
                    $auc = (geoMaster::is('auctions') && isset($fields[$field_check['a']]) && $fields[$field_check['a']]);
                    if (!$class && !$auc) {
                        //neither option turned on/valid!
                        continue;
                    }
                } elseif (!isset($fields[$field_check]) || !$fields[$field_check]) {
                    //field not turned on!
                    continue;
                }
            }
            //if it gets this far, then we should show it!
            $sortOptions[$sort_id] = $label;
        }
        return $sortOptions;
    }
}
