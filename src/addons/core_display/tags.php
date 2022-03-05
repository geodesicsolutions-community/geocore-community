<?php

class addon_core_display_tags extends addon_core_display_info
{

    public function browsing_before_listings_column($params, Smarty_Internal_Template $smarty)
    {
        //check to see if the master display setting is on
        $reg = geoAddon::getRegistry($this->name);
        if (!$reg->browsing_filters_enabled) {
            return '';
        }

        return $this->display_browsing_filters($params, $smarty);
    }

    public function browsing_before_listings($params, Smarty_Internal_Template $smarty)
    {
        if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
            //browsing disabled, do not show this contents
            return '';
        }
        $reg = geoAddon::getRegistry($this->name);
        if (!$reg->featured_show_automatically) {
            //do not show automatically, only show with direct tag
            return '';
        }
        //show the browsing_featured_gallery
        return $this->browsing_featured_gallery($params, $smarty);
    }

    public function browsing_featured_gallery($params, Smarty_Internal_Template $smarty)
    {
        $db = DataAccess::getInstance();
        //This is a common file, since all featured modules work basically the same.
        if (geoPC::is_print() && $db->get_site_setting('disableAllBrowsing')) {
            //browsing disabled, do not show module contents
            return '';
        }

        $reg = geoAddon::getRegistry($this->name);

        //Check if this is on 2nd page or higher...
        if (isset($_GET['page']) && (int)$_GET['page'] > 1 && !$reg->featured_2nd_page) {
            //on 2nd or higher page, and set to not show on that page
            return '';
        }

        //featured only
        $levels = $reg->get('featured_levels', array(1 => 1));
        if (!count($levels)) {
            //no levels selected, don't show anything
            return '';
        }

        $tpl_vars = array();

        $tpl_vars['cat_id'] = (int)geoView::getInstance()->getCategory();

        if (isset($params['cat_id'])) {
            //Since cat_id affects how things are loaded, go ahead and check params
            //at this stage to allow cat_id to be changed.
            $tpl_vars['cat_id'] = (int)$params['cat_id'];
        } elseif (isset($params['category_id'])) {
            //if cat_id not set, check for category_id
            $tpl_vars['cat_id'] = (int)$params['category_id'];
        }

        $msgs = geoAddon::getText($this->auth_tag, $this->name);
        //Create a NEW browse class, so it isn't re-used by other places and end
        //up re-using same settings as we are using by accident
        $browsing = new geoBrowse($tpl_vars['cat_id']);

        //Allow browsing vars to get over-ridden by parameters passed in
        $settings = array(
            'photo_or_icon' => 1,
            'dynamic_image_dims' => $reg->get('dynamic_image_dims'),
            'featured_thumb_width' => $reg->get('featured_thumb_width', 150),
            'featured_thumb_height' => $reg->get('featured_thumb_height', 150),
            'featured_title_length' => $reg->get('featured_title_length', 0),
            'featured_desc_length' => $reg->get('featured_desc_length', 20),
            'display_all_of_description' => ($browsing->configuration_data['length_of_description'] == 0)
            );
        //now merge params on top to overwrite the settings
        $settings = array_merge($params, $settings);

        //Set up the browsing vars
        $browsing->messages = $db->get_text(true);
        $browsing->configuration_data['photo_or_icon'] = $settings['photo_or_icon'];
        $browsing->configuration_data['featured_thumbnail_max_width'] = $settings['featured_thumb_width'];
        $browsing->configuration_data['featured_thumbnail_max_height'] = $settings['featured_thumb_height'];
        $browsing->configuration_data['module_title_and_optional_length'] = $settings['featured_title_length'];
        $browsing->configuration_data['length_of_description'] = $settings['featured_desc_length'];
        $browsing->configuration_data['display_all_of_description'] = $settings['length_of_description'];
        $browsing->configuration_data['dynamic_image_dims'] = $settings['dynamic_image_dims'];

        $query = (isset($query)) ? $query : $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
        $classTable = geoTables::classifieds_table;

        $tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
        $tpl_vars['featured_carousel'] = $reg->featured_carousel;
        //make it use 3_featured_gallery attachment
        $tpl_vars['main_page_gallery_sub_template'] = geoView::getInstance()->getTemplateAttachment('3_featured_gallery', $browsing->language_id, $browsing->site_category, false);

        //strip out by item type
        if (!geoMaster::is('classifieds', 'auctions')) {
            //not showing classifieds or auctions...
            if (geoMaster::is('classifieds')) {
                $query->where("$classTable.`item_type`=1");
            } elseif (geoMaster::is('auctions')) {
                $query->where("$classTable.`item_type`=2");
            }
        }

        //limit by level(s)
        if (isset($levels[1]) || (isset($params['featured_level_1']) && $params['featured_level_1'])) {
            $query->orWhere("$classTable.featured_ad = 1", 'featured_ad');
        }
        //2 and up all use same name
        for ($i = 2; $i <= 5; $i++) {
            if (isset($levels[$i]) || (isset($params['featured_level_' . $i]) && $params['featured_level_' . $i])) {
                $query->orWhere("$classTable.featured_ad_{$i} = 1", 'featured_ad');
            }
        }
        //order randomly
        if (!isset($params['not_random'])) {
            //NOTE: can make it not random by passing not_random=1 as tag parameter...
            $query->order("RAND()");
        }

        //must be live
        $query->where("$classTable.`live`=1", 'live');
        //narrow by category
        if ($tpl_vars['cat_id']) {
            $browsing->whereCategory($query, $tpl_vars['cat_id']);
        }
        if ($params['only_image_listings']) {
            //must have at least one image, of course
            $query->where("$classTable.`image`>0");
            $imgTable = geoTables::images_urls_table;

            $subQuery = new geoTableSelect($imgTable);
            $subQuery->where("$imgTable.`classified_id`=$classTable.`id` AND $imgTable.`display_order`=1");

            $query->where("EXISTS ({$subQuery})");
            unset($subQuery, $imgTable);
        }
        //set limit
        $query->limit($reg->get('featured_max_count', 20));

        $browse_result = $db->Execute('' . $query);

        //unset query, we are done with it, don't want it accidentally persisting for another module.
        unset($query, $classTable);

        if ($browse_result->RecordCount() < 1) {
            //no listings in this tag
            $tpl_vars['no_listings'] = $msgs['featured_no_listings_message'];
        } else {
            $cfg = $listings = $headers = array();

            $fields = $browsing->fields->getDisplayLocationFields('core_featured_gallery', $this->name);

            //whether to show auctions or not...
            $show_auctions = (geoMaster::is('auctions'));

            $show_classifieds = (geoMaster::is('classifieds'));

            //set up header view vars
            $headers['css'] = 'browsing_featured_gallery';

            $cfg['sort_links'] = false;
            $cfg['listing_url'] = $db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";

            //NOTE:  headers are really "labels", just using same var names as main browsing
            // for consistency and partial cross-template compatibility...

            //NOTE2: SEtting both text and label, even though gallery view only uses "label",
            //so that someone could easily switch to use grid view instead and the labels
            //would be used for column headers.
            $cfg['cols']['type'] = (geoMaster::is('classifieds') && geoMaster::is('auctions') && $reg->featured_show_listing_type) ? true : false;
            $headers['type'] = array(
                'css' => 'item_type_pic_info',
                'label' => $msgs['featured_label_listing_type'],
                'text' => $msgs['featured_label_listing_type']
            );

            $cfg['cols']['business_type'] = ($fields['business_type']) ? true : false;
            $headers['business_type'] = array(
                'css' => 'business_type_pic_info',
                'label' => $msgs['featured_label_business_type'],
                'text' => $msgs['featured_label_business_type'],
                'reorder' => 43,
            );
            //always show image
            $cfg['cols']['image'] = true;
            $headers['image'] = array(
                'css' => 'photo_column_header',
                'label' => '',
                'text' => ''
            );

            $cfg['cols']['title'] = ($fields['title']) ? true : false;
            $headers['title'] = array(
                'css' => 'title_pic_info',
                'label' => $msgs['featured_label_title'],
                'text' => $msgs['featured_label_title'],
                'reorder' => 5,
            );
            if (!$fields['title']) {
                $cfg['cols']['icons'] = (bool)$fields['icons'];
            }
            //gallery view, no setting for description always under title, it's always that way
            $cfg['description_under_title'] = false;

            $cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']) ? true : false;
            $headers['description'] = array(
                'css' => 'description_pic_info',
                'label' => $msgs['featured_label_description'],
                'text' =>  $msgs['featured_label_description']
            );

            //Listing tags column
            $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
            $headers['tags'] = array(
                'css' => 'tags_pic_info',
                'label' => $msgs['featured_label_tags'],
                'text' =>  $msgs['featured_label_tags']
            );

            for ($i = 1; $i <= 20; $i++) {
                if ($fields['optional_field_' . $i]) {
                    $cfg['cols']['optionals'][$i] = true;
                    $headers['optionals'][$i] = array(
                        'css' => 'optional_field_' . $i . '_pic_info',
                        'label' => $msgs['featured_label_opt_' . $i],
                        'text' => $msgs['featured_label_opt_' . $i],
                    );
                } else {
                    $cfg['cols']['optionals'][$i] = false;
                }
            }

            $cfg['cols']['address'] = ($fields['address']) ? true : false;
            $headers['address'] = array(
                'css' => 'address_pic_info',
                'label' => $msgs['featured_label_address'],
                'text' => $msgs['featured_label_address']
            );

            $cfg['cols']['city'] = ($fields['city']) ? true : false;
            $headers['city'] = array(
                'css' => 'city_pic_info',
                'label' => $msgs['featured_label_city'],
                'text' => $msgs['featured_label_city'],
                'reorder' => 35,
            );


            $cfg['cols']['location_breadcrumb'] = ($fields['location_breadcrumb']) ? true : false;
            $headers['location_breadcrumb'] = array(
                'css' => 'location_breadcrumb_column_header',
                'label' => $msgs['featured_label_location_breadcrumb'],
                'text' => $msgs['featured_label_location_breadcrumb']
            );
            $enabledRegions = array();
            $maxLocationDepth = 0;
            $maxEnabledLevel = geoRegion::getLowestLevel();
            for ($r = 1; $r <= $maxEnabledLevel; $r++) {
                if ($fields['region_level_' . $r]) {
                    $enabledRegions[] = $r;
                    $maxLocationDepth = $r;
                }
            }
            $cfg['maxLocationDepth'] = $maxLocationDepth;
            foreach ($enabledRegions as $level) {
                $cfg['cols']['region_level_' . $level] = true;
                $headers['region_level_' . $level] = array(
                    'css' => 'region_level_' . $level . '_column_header',
                    'label' => $label = geoRegion::getLabelForLevel($level),
                    'text' => $label
                );
            }

            $cfg['cols']['zip'] = ($fields['zip']) ? true : false;
            $headers['zip'] = array(
                'css' => 'zip_pic_info',
                'label' => $msgs['featured_label_zip'],
                'text' => $msgs['featured_label_zip'],
                'reorder' => 41,
            );

            $cfg['cols']['price'] = ($fields['price']) ? true : false;
            $headers['price'] = array(
                'css' => 'price_pic_info',
                'label' => $msgs['featured_label_price'],
                'text' => $msgs['featured_label_price'],
                'reorder' => 1,
            );

            $cfg['cols']['num_bids'] = ($show_auctions && $fields['num_bids']) ? true : false;
            $headers['num_bids'] = array(
                'css' => 'number_bids_header',
                'label' => $msgs['featured_label_num_bids'],
                'text' => $msgs['featured_label_num_bids']
            );


            $cfg['cols']['entry_date'] = (($show_classifieds && $fields['classified_start']) || ($show_auctions && $fields['auction_start'])) ? true : false;
            $headers['entry_date'] = array(
                'css' => 'price_pic_info',
                'label' => $msgs['featured_label_start_date'],
                'text' => $msgs['featured_label_start_date'],
                'reorder' => 68,
            );

            $cfg['cols']['time_left'] = (($show_classifieds && $fields['classified_time_left']) || ($show_auctions && $fields['auction_time_left'])) ? true : false;
            $headers['time_left'] = array(
                'css' => 'price_pic_info',
                'label' => $msgs['featured_label_time_left'],
                'text' => $msgs['featured_label_time_left'],
                'reorder' => 70,
            );

            $cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL)) ? true : false;
            $headers['edit'] = array(
                'css' => 'price_pic_info',
                'label' => 'edit',
                'text' => 'edit'
            );

            $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
            $headers['delete'] = array(
                'css' => 'price_pic_info',
                'label' => 'delete',
                'text' => 'delete'
            );

            /**
             * Addon core event:
             * name: Browse_tag_display_browse_result_addHeader
             * vars: array (this => Object) (this is the instance of $this.
             * return: array (css => string (CSS Class), text => string (what should be displayed)
            */
            $tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addHeader', array('this' => $browsing, 'browse_fields' => $fields, 'featured_gallery' => true), geoAddon::ARRAY_ARRAY);

            if ($browsing->configuration_data['popup_while_browsing']) {
                $cfg['popup'] = true;
                $cfg['popup_width'] = $browsing->configuration_data['popup_while_browsing_width'];
                $cfg['popup_height'] = $browsing->configuration_data['popup_while_browsing_height'];
            } else {
                $cfg['popup'] = false;
            }
            $cfg['icons'] = array(
                'sold' => (($browsing->messages[500798]) ? geoTemplate::getUrl('', $browsing->messages[500798]) : ''),
                'buy_now' => (($browsing->messages[500799]) ? geoTemplate::getUrl('', $browsing->messages[500799]) : ''),
                'reserve_met' => (($browsing->messages[500800]) ? geoTemplate::getUrl('', $browsing->messages[500800]) : ''),
                'reserve_not_met' => (($browsing->messages[501665]) ? geoTemplate::getUrl('', $browsing->messages[501665]) : ''),
                'no_reserve' => (($browsing->messages[500802]) ? geoTemplate::getUrl('', $browsing->messages[500802]) : ''),
                'verified' => (($browsing->messages[500952]) ? geoTemplate::getUrl('', $browsing->messages[500952]) : ''),
            );

            $cfg['empty'] = $msgs['featured_no_listings_message'];

            $tpl_vars['cfg'] = $cfg;
            $tpl_vars['headers'] = $headers;

            //now set up all the listing data

            //common text
            $text = array(
                'item_type' => array(
                    'classified' => $msgs['featured_listing_type_classifieds'],
                    'auction' => $msgs['featured_listing_type_auctions'],
                ),
                'business_type' => array(
                    1 => $msgs['featured_listing_type_individual'],
                    2 => $msgs['featured_listing_type_business'],
                ),
                'time_left' => array(
                    'weeks' => $msgs['featured_time_left_weeks'],
                    'days' => $msgs['featured_time_left_days'],
                    'hours' => $msgs['featured_time_left_hours'],
                    'minutes' => $msgs['featured_time_left_minutes'],
                    'seconds' => $msgs['featured_time_left_seconds'],
                    'closed' => $msgs['featured_time_left_closed']
                )
            );

            while ($row = $browse_result->FetchRow()) {
                $id = $row['id']; //template expects $listings to be keyed by classified id

                $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

                //use the common geoBrowse class to do all the common heavy lifting
                //always pass in 1 for featured, to make it use module width/height settings
                $listings[$id] = $browsing->commonBrowseData($row, $text, true);

                //css is different enough to not include in the common file
                $listings[$id]['css'] = '';//'browsing_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');

                //also do addons separately
                $listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addRow', array('this' => $browsing,'show_classifieds' => $row, 'browse_fields' => $fields, 'featured_gallery' => true), geoAddon::ARRAY_ARRAY);
            }
            $tpl_vars['listings'] = $listings;
        }
        //done with browsing class
        unset($browsing);

        $tpl_vars['resultset_empty_message'] = $msgs['featured_no_listings_message'];
        $tpl_vars['gallery_columns'] = $reg->get('featured_column_count', 4);
        //for backwards compatibility in templates
        $tpl_vars['gallery_percent'] = round((100 / max(1, $tpl_vars['gallery_columns'])), 2);
        $tpl_vars['browse_tpl'] = 'system/browsing/common/gallery_view.tpl';

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'browsing_featured_gallery/index.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    public function display_browsing_filters($params, Smarty_Internal_Template $smarty)
    {
        $db = DataAccess::getInstance();
        $reg = geoAddon::getRegistry($this->name);
        $lField = geoLeveledField::getInstance();
        //first, assemble a list of all the valid filters

        $filtersToShow = array();

        $category = geoBrowsingFilter::getActiveCategory();
        $browsingCategory = geoBrowsingFilter::getBrowsingCategory();
        if (!$browsingCategory && $smarty->getTemplateVars('category_id')) {
            //called this from the addon tag before the core event could set the category. grab it out of the template vars and try again
            geoBrowsingFilter::setBrowsingCategory($smarty->getTemplateVars('category_id'));
            $category = geoBrowsingFilter::getActiveCategory(true); //be sure to pass in true to bust the cache
            $browsingCategory = geoBrowsingFilter::getBrowsingCategory();
        }

        $sql = "SELECT * FROM " . geoTables::browsing_filters_settings . " WHERE `category` = ? AND `enabled` = 1 ORDER BY `display_order` ASC";
        $settings = $db->Execute($sql, array($category));

        foreach ($settings as $field) {
            $target = $field['field'];

            $filter = geoBrowsingFilter::getFilter($target);

            if (!$filter) {
                //there's not a filter for this field that is set to be filtered by.
                //admin has probably disabled a field without turning off its filter first, which is a little unorthodox, but not wrong.
                //skip this and move along.
                continue;
            }
            if ($filter->isActive()) {
                //there's already an active filter for this field.
                //show it "breadcrumb-style" with just the active filter and a remove link
                $activeFilters[$filter->getTarget()] = $filter->getBreadcrumb();
            } else {
                //filter not active. show its selections
                $filters[] = $filter;
            }
        }

        $filtersToShow = array();
        $calendarAdded = false;

        //set up a tableselect so that we can consider any extant filters
        //but use a clone, because we don't want to screw with actual browsing here
        $query = $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
        $query->where('`live` = 1', 'live');
        if ($browsingCategory) {
            $classTable = geoTables::classifieds_table;
            $listCatTable = geoTables::listing_categories;

            $cat_subquery = "SELECT * FROM $listCatTable WHERE $listCatTable.`listing`=$classTable.`id`
				AND $listCatTable.`category`=$browsingCategory";

            $query->where("EXISTS ($cat_subquery)", 'category');
        }

        $currentCount = (int)$db->GetOne('' . $query->getCountQuery());
        if (!$currentCount) {
            //no listings remain. do not show any filters that have not yet been used
            //but DO show active ones, so they may be un-set, and so it's clear that they're active
            $filters = array();
        }

        //get the fields to use so we know what to get for the optional fields
        $fields = geoFields::getInstance(0, $browsingCategory);

        foreach ($filters as $filter) {
            //get the filterable values for this target

            $target = $filter->getTarget();
            $type = $filter->getType();

            $dependency = $filter->getDependency();
            if ($dependency) {
                //this filter can only be shown if another is already active
                //for instance, only show the selections of car Models after the Make filter has been set
                $dFilter = geoBrowsingFilter::getFilter($dependency);
                if (!$dFilter || !$dFilter->isActive()) {
                    //filter this depends on isn't active -- skip showing this
                    continue;
                }
            }

            if (in_array($type, array(geoBrowsingFilter::SCALAR, geoBrowsingFilter::PICKABLE))) {
                //get all live values from field into a list
                $values = array();
                if ($filter->isLeveled()) {
                    //get multi-level values
                    $parts = explode('_', $target);
                    $leveled_field = $parts[1];
                    $level = $parts[2];
                    //get value parent
                    $parent = 0;
                    if ($level > 1) {
                        $dFilter = geoBrowsingFilter::getFilter('leveled_' . $leveled_field . '_' . ($level - 1));
                        if (!$dFilter || !$dFilter->isActive()) {
                            //no active filter!  So no parent
                            continue;
                        }
                        $val = $dFilter->getValue();
                        if (!$val) {
                            //no value?
                            continue;
                        }
                        $parent = $val['id'];
                        unset($dFilter, $val);
                    }
                    //TODO: optimize filters to allow pagination of values
                    $values = $lField->getValues($leveled_field, $parent, 0, 'all');
                    foreach ($values['values'] as $value) {
                        if (!$reg->no_filter_counts) {
                            $count = $filter->listingCount($value, $browsingCategory);
                            if ($count > 0) {
                                $filtersToShow[$target]['value'][$value['id']] = $count;
                                $filtersToShow[$target]['leveled'][$value['id']] = $value;
                            }
                        } else {
                            $filtersToShow[$target]['value'][$value['id']] = null;
                            $filtersToShow[$target]['leveled'][$value['id']] = $value;
                        }
                    }
                } elseif (!$filter->isCatSpec()) {
                    //must be optional fields
                    if ($type == geoBrowsingFilter::SCALAR || $reg->use_listing_values) {
                        //get actual, in-use values for this optional field
                        $myQuery = clone $query;

                        $myQuery->group("$target")->order("$target");
                        $result = $db->Execute($myQuery . '');
                        while ($line = $result->FetchRow()) {
                            if (!$line[$target]) {
                                //skip over blank entries
                                continue;
                            }
                            $count = ($reg->no_filter_counts ? null : $filter->listingCount($line[$target], $browsingCategory));

                            unset($myQuery);
                            $filtersToShow[$target]['value'][$line[$target]] = $count;
                        }
                    } else {
                        //get the values from pre-valued dropdown
                        $sql = "SELECT * FROM " . geoTables::sell_choices_table . " WHERE `type_id` = " . intval($fields->$target->type_data) . " ORDER BY `display_order`,`value`";
                        $type_result = $db->Execute($sql);

                        if ($type_result && $type_result->RecordCount() > 0) {
                            foreach ($type_result as $type_row) {
                                $count = ($reg->no_filter_counts ? null : $filter->listingCount(geoString::toDB(geoString::specialChars($type_row['value'])), $browsingCategory));
                                $filtersToShow[$target]['value'][geoString::toDB($type_row['value'])] = $count;
                            }
                        }
                    }
                } else {
                    //category questions!
                    $qid = (int)substr($target, 3);
                    if ($reg->use_listing_values) {
                        //get actual, in-use values for this category-specific question

                        $myQuery = clone $query;

                        //have to go "backwards" here to find "values" based on
                        //finding listings that match the query thingy
                        $myQuery->where(geoTables::classified_extra_table . ".classified_id=" . geoTables::classifieds_table . ".id");
                        $sql = "SELECT `value`, COUNT(`value`) as count FROM " . geoTables::classified_extra_table . " WHERE `question_id` = ? AND EXISTS($myQuery) GROUP BY `value`";
                        $sql .= " ORDER BY `value` ASC"; //ORDER BY COUNT(`value`) DESC to put most common on top

                        $result = $db->Execute($sql, array($qid));
                        while ($line = $result->FetchRow()) {
                            if (!$line['value']) {
                                //skip over blank entries
                                continue;
                            }
                            $count = intval($line['count']);
                            $filtersToShow[$target]['value'][$line['value']] = ($reg->no_filter_counts ? null : $count);
                        }
                    } else {
                        //get the values from pre-valued dropdown
                        $sql = "SELECT c.value FROM " . geoTables::sell_choices_table . " c, " . geoTables::questions_languages . " l
								WHERE l.question_id=? AND l.language_id = ? AND l.choices=c.`type_id` ORDER BY c.`display_order`,c.`value`";
                        $type_result = $db->Execute($sql, array($qid, $db->getLanguage()));

                        if ($type_result && $type_result->RecordCount() > 0) {
                            foreach ($type_result as $type_row) {
                                if (!$reg->no_filter_counts) {
                                    $count = $filter->listingCount(geoString::toDB(geoString::specialChars($type_row['value'])), $browsingCategory);
                                    if ($count > 0) {
                                        $filtersToShow[$target]['value'][geoString::toDB($type_row['value'])] = $count;
                                    }
                                } else {
                                    $filtersToShow[$target]['value'][geoString::toDB($type_row['value'])] = null;
                                }
                            }
                        }
                    }
                }
                //could use this to sort the most common values to the top, if desired
                //arsort($filtersToShow[$target]['value'], SORT_NUMERIC);
            } elseif ($type == geoBrowsingFilter::RANGE) {
                $filtersToShow[$target]['value'] = 'RANGE';
            } elseif ($type == geoBrowsingFilter::DATE_RANGE) {
                $filtersToShow[$target]['value'] = 'DATE_RANGE';
            } elseif ($type == geoBrowsingFilter::BOOL) {
                if ($filter->isCatSpec()) {
                    //category-specific checkbox
                    $filtersToShow[$target]['value'] = 'BOOL';

                    //get number of listings for each option
                    $filtersToShow[$target]['yes'] = ($reg->no_filter_counts ? null : $filter->listingCount(1, $browsingCategory));
                    //fancy trick, we already have the overall listing count, the
                    //number of "no's" has to be the inverse
                    $filtersToShow[$target]['no'] = ($reg->no_filter_counts ? null : ($currentCount - $filtersToShow[$target]['yes']));
                } else {
                    //WHAT?! These don't exist! (yet?)
                }
            }
        }
        if (empty($filtersToShow) && empty($activeFilters)) {
            //no filters to show -- return empty
            return '';
        }

        //get friendly names
        $friendlyNames = array();
        foreach ($filtersToShow as $target => $type) {
            $friendlyNames[$target] = geoBrowsingFilter::getFriendlyName($target);
        }

        $tpl_vars = array(
            'msgs' => geoAddon::getText('geo_addons', $this->name),
            'activeFilters' => $activeFilters,
            'filters' => $filtersToShow,
            'friendlyNames' => $friendlyNames,
            'self' => geoBrowsingFilter::getPageUrl(),
            'numFilters' => geoBrowsingFilter::countActiveFilters(),
            'expandable_threshold' => $reg->expandable_threshold,
            'show_counts' => ($reg->no_filter_counts ? false : true)
        );

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'browsing_filter/sidebar.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
