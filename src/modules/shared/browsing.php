<?php

//modules/shared/browsing.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.10.0-11-g76731fa
##
##################################

//This is a common file, since all featured modules work basically the same.
if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

if ($show_module['is_ajax']) {
    //add in the common text (page 59) when reloading this via ajax
    // (the + operator preserves numerical array keys, unlike array_merge())
    $page->messages = $page->messages + $this->get_text(true, 59);
}

//IMPORTANT: due to new ajax pagination, $show_module can be affected by user input and should NOT be assumed to be clean
//module_replace_tag, in particular, is dumped straight into a couple of templates, so be extra sure there's no funny business going on in it
$show_module['module_replace_tag'] = geoString::specialChars($show_module['module_replace_tag']);

$browsing = new geoBrowse();
$browsing->site_category = $page->site_category;
$browsing->get_configuration_data();
//let browsing class be aquanted with the messages we already have
$browsing->messages =& $page->messages;
$query = (isset($query)) ? $query : $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
$classTable = geoTables::classifieds_table;

//strip out by item type
//$show_module['module_display_type_listing'] WILL ALWAYS BE ZERO IF NOT CLASSAUCTIONS. THE SWITCH IN ADMIN IS NOT AVAILABLE TO CHOOSE.
if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
    if ($show_module['module_display_type_listing'] == 1) {
        //only show classifieds.
        $query->where("$classTable.`item_type`=1");
    } elseif ($show_module['module_display_type_listing'] == 2) {
        $query->where("$classTable.`item_type`=2");
    } elseif ($show_module['module_display_type_listing'] == 4) {
        $query->where("$classTable.`item_type`=2 AND $classTable.`auction_type`=3");
    }
} elseif (geoMaster::is('classifieds')) {
    $query->where("$classTable.`item_type`=1");
} elseif (geoMaster::is('auctions')) {
    $query->where("$classTable.`item_type`=2");
}

//featured
if ($col_name) {
    $seed = rand();
    $query->where("$classTable.{$col_name} = 1")
        ->order("RAND($seed)");
    unset($col_name);
}
//must be live
$query->where("$classTable.`live`=1", 'live');

//narrow by category
$tpl_vars['cat_id'] = $page->site_category;

if (isset($show_module['cat_id'])) {
    //special case, cat_id was passed in by module parameter
    $parts = explode(',', $show_module['cat_id']);
    if (count($parts) > 1) {
        //passed in an array; clean it up
        $tpl_vars['cat_id'] = array();
        foreach ($parts as $p) {
            $p = (int)trim($p);
            if ($p) {
                //if this doesn't cast to a non-zero int, don't add it to the array
                $tpl_vars['cat_id'][] = $p;
            }
        }
    } else {
        //passed in a scalar value; clean it as an int
        $tpl_vars['cat_id'] = (int)$show_module['cat_id'];
    }
} elseif (isset($tpl_vars['is_featured_category']) && $tpl_vars['is_featured_category'] && $show_module['module_category']) {
    $tpl_vars['cat_id'] = (int)$show_module['module_category'];
}
if (isset($show_module['not_cat_id'])) {
    //allow saying "don't include these categories"

    $parts = explode(',', $show_module['not_cat_id']);
    if (count($parts) > 1) {
        //passed in an array; clean it up
        $not_cat_id = array();
        foreach ($parts as $p) {
            $p = (int)trim($p);
            if ($p) {
                //if this doesn't cast to a non-zero int, don't add it to the array
                $not_cat_id[] = $p;
            }
        }
    } else {
        //passed in a scalar value; clean it as an int
        $not_cat_id = (int)$show_module['not_cat_id'];
    }
    //generate the query like normal..
    $browsing->whereCategory($query, $not_cat_id);
    //But then add NOT in front so it ends up being "NOT EXISTS (...)"
    $where = 'NOT ' . $query->getWhere('category');
    $query->where($where, 'category');
} elseif ($tpl_vars['cat_id'] && !$tpl_vars['ignoreCategory']) {
    //$tpl_vars['ignoreCategory'] is a special case for FEATURED_ADS_2, which is never restricted by current category
    $browsing->whereCategory($query, $tpl_vars['cat_id']);
}

//allow addons to add to or modify the query
geoAddon::triggerUpdate('Browse_module_generate_query', array('this' => $this, 'query' => $query, 'show_module' => $show_module));

//set limit
$pageNumber = (int)$show_module['results_page'];
$pageNumber = $pageNumber > 1 ? $pageNumber : 1;
$firstListing = ($pageNumber - 1) * (int)$show_module['module_number_of_ads_to_display'];
$query->limit($firstListing, (int)$show_module['module_number_of_ads_to_display']);

if ($order_by) {
    $query->order($order_by);
    unset($order_by);
}

$browse_result = $db->Execute('' . $query);

$numListings = $db->GetOne('' . $query->getCountQuery());
$numPages = ceil($numListings / $show_module['module_number_of_ads_to_display']);
if ($numPages > 1 && $show_module['use_pagination'] == 1) {
    $tpl_vars['module_pagination'] = geoPagination::getHTML($numPages, $pageNumber, '', '', '', false, false, $show_module['module_replace_tag']);
}

//unset query, we are done with it, don't want it accidentally persisting for another module.
unset($query, $classTable);

if (!$browse_result) {
    //error running query, this shouldn't normally happen!
    trigger_error("ERROR SQL: Error running query $query - Error msg: " . $db->ErrorMsg());
    return '';
}
if ($browse_result->RecordCount() < 1) {
    //no listings in this tag
    $tpl_vars['no_listings'] = $txt_vars['empty_category'];
    //make sure there are no listings left over from a previous module...
    $tpl_vars['listings'] = false;
} else {
    $cfg = $listings = $headers = array();

    $fields = $browsing->fields->getModuleFields($show_module['module_replace_tag']);

    //set this explicitly to prevent module bleed
    $tpl_vars['no_listings'] = false;

    //whether to show auctions or not...
    $show_auctions = (geoMaster::is('auctions') && in_array($show_module['module_display_type_listing'], array(0,2,4)));

    $show_classifieds = (geoMaster::is('classifieds') && in_array($show_module['module_display_type_listing'], array(0,1)));

    //set up header view vars
    $headers['css'] = 'module_' . $show_module['module_replace_tag'];

    $cfg['sort_links'] = false;
    $cfg['listing_url'] = $db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";

    //NOTE: We set the "label" to same as the "text", since modules do not have
    //controls to switch between views, so they don't have dedicated text.. But
    //we do want to allow easily making it use gallery or list view templates, which
    //do need to have label set to show that.

    //one of the few columns not set in fields to use...
    $cfg['cols']['type'] = (geoMaster::is('classifieds') && geoMaster::is('auctions') && $txt_vars['module_display_listing_column']) ? true : false;
    $headers['type'] = array(
        'css' => 'item_type_column_header',
        'text' => $txt_vars['module_display_listing_column'],
        'label' => $txt_vars['module_display_listing_column'],
    );

    $cfg['cols']['business_type'] = ($fields['business_type']) ? true : false;
    $headers['business_type'] = array(
        'css' => 'business_type_column_header',
        'text' => $txt_vars['module_display_business'],
        'label' => $txt_vars['module_display_business'],
        'reorder' => 43,
    );

    $cfg['cols']['image'] = ($fields['photo']) ? true : false;
    $headers['image'] = array(
        'css' => 'photo_column_header',
        'text' => $txt_vars['module_display_photo_icon'],
        'label' => $txt_vars['module_display_photo_icon'],
    );

    $cfg['cols']['title'] = ($fields['title']) ? true : false;
    $headers['title'] = array(
        'css' => 'title_column_header',
        'text' => $txt_vars['module_display_title'],
        'label' => $txt_vars['module_display_title'],
        'reorder' => 5,
    );
    if (!$fields['title']) {
        $cfg['cols']['icons'] = (bool)$fields['icons'];
    }

    $cfg['description_under_title'] = ($fields['description'] && $show_module['module_display_ad_description_where']) ? true : false;

    $cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']) ? true : false;
    $headers['description'] = array(
        'css' => 'description_column_header',
        'text' =>  $txt_vars['module_display_ad_description'],
        'label' => $txt_vars['module_display_ad_description'],
    );

    //Listing tags column
    $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
    $headers['tags'] = array(
        'css' => 'tags_column_header',
        'text' =>  $txt_vars['module_display_tags'],
        'label' => $txt_vars['module_display_tags'],
    );

    //Leveled fields
    $lField = geoLeveledField::getInstance();
    $leveled_field_ids = $lField->getLeveledFieldIds();
    foreach ($leveled_field_ids as $lev_id) {
        //go through each level, see if that level should be displayed
        $maxLevels = $lField->getMaxLevel($lev_id, true);
        for ($i = 1; $i <= $maxLevels; $i++) {
            if (!$fields['leveled_' . $lev_id . '_' . $i]) {
                //this level not set to show...
                continue;
            }

            //show this region
            $levelInfo = $lField->getLevel($lev_id, $i);
            $headers['leveled'][$lev_id][$i] = array (
                'css' => 'leveled_' . $lev_id . '_' . $i,
                'text' => $levelInfo['label'],
                'label' => $levelInfo['label'],
                );
            $cfg['cols']['leveled'][$lev_id][$i] = true;
        }
    }

    for ($i = 1; $i <= 20; $i++) {
        if (geoPC::is_ent() && $fields['optional_field_' . $i]) {
            $cfg['cols']['optionals'][$i] = true;
            $reorder = ($i <= 10) ? ( 2 * ($i - 1) + 15 ) : ( 2 * ($i - 11) + 45 ) ;
            $headers['optionals'][$i] = array(
                'css' => 'optional_field_header_' . $i,
                'text' => $txt_vars['module_display_optional_field_' . $i],
                'label' => $txt_vars['module_display_optional_field_' . $i],
                'reorder' => $reorder,
            );
        } else {
            $cfg['cols']['optionals'][$i] = false;
        }
    }

    $cfg['cols']['address'] = ($fields['address']) ? true : false;
    $headers['address'] = array(
        'css' => 'address_column_header',
        'text' => $txt_vars['module_display_address'],
        'label' => $txt_vars['module_display_address'],
    );

    $cfg['cols']['city'] = ($fields['city']) ? true : false;
    $headers['city'] = array(
        'css' => 'city_column_header',
        'text' => $txt_vars['module_display_city'],
        'label' => $txt_vars['module_display_city'],
        'reorder' => 35,
    );


    $cfg['cols']['location_breadcrumb'] = ($fields['location_breadcrumb']) ? true : false;
    $headers['location_breadcrumb'] = array(
        'css' => 'location_breadcrumb_column_header',
        'text' => $txt_vars['module_display_location'],
        'label' => $txt_vars['module_display_location'],
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
            'text' => $label = geoRegion::getLabelForLevel($level),
            'label' => $label,
        );
    }

    $cfg['cols']['zip'] = ($fields['zip']) ? true : false;
    $headers['zip'] = array(
        'css' => 'zip_column_header',
        'text' => $txt_vars['module_display_zip'],
        'label' => $txt_vars['module_display_zip'],
        'reorder' => 41,
    );

    $cfg['cols']['price'] = ($fields['price']) ? true : false;
    $headers['price'] = array(
        'css' => 'price_column_header',
        'text' => $txt_vars['module_display_price'],
        'label' => $txt_vars['module_display_price'],
        'reorder' => 1,
    );

    $cfg['cols']['num_bids'] = ($show_auctions && $fields['num_bids']) ? true : false;
    $headers['num_bids'] = array(
        'css' => 'number_bids_header',
        'text' => $txt_vars['module_display_number_bids'],
        'label' => $txt_vars['module_display_number_bids'],
    );


    $cfg['cols']['entry_date'] = (($show_classifieds && $fields['classified_start']) || ($show_auctions && $fields['auction_start'])) ? true : false;
    $headers['entry_date'] = array(
        'css' => 'price_column_header',
        'text' => $txt_vars['module_display_entry_date'],
        'label' => $txt_vars['module_display_entry_date'],
        'reorder' => 68,
    );

    $cfg['cols']['time_left'] = (($show_classifieds && $fields['classified_time_left']) || ($show_auctions && $fields['auction_time_left'])) ? true : false;
    $headers['time_left'] = array(
        'css' => 'price_column_header',
        'text' => $txt_vars['module_display_time_left'],
        'label' => $txt_vars['module_display_time_left'],
        'reorder' => 70,
    );

    $cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL)) ? true : false;
    $headers['edit'] = array(
        'css' => 'price_column_header',
        'text' => 'edit',
        'label' => 'edit',
    );

    $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
    $headers['delete'] = array(
        'css' => 'price_column_header',
        'text' => 'delete',
        'label' => 'delete',
    );

    /**
     * Addon core event:
     * name: Browse_tag_display_browse_result_addHeader
     * vars: array (this => Object) (this is the instance of $this.
     * return: array (css => string (CSS Class), text => string (what should be displayed)
     */
    $tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_module_display_browse_result_addHeader', array('this' => $page, 'fields' => $fields, 'show_module' => $show_module), geoAddon::ARRAY_ARRAY);

    if ($browsing->configuration_data['popup_while_browsing']) {
        $cfg['popup'] = true;
        $cfg['popup_width'] = $browsing->configuration_data['popup_while_browsing_width'];
        $cfg['popup_height'] = $browsing->configuration_data['popup_while_browsing_height'];
    } else {
        $cfg['popup'] = false;
    }
    $cfg['icons'] = array(
        'sold' => (($this->messages[500798]) ? geoTemplate::getUrl('', $this->messages[500798]) : ''),
        'buy_now' => (($this->messages[500799]) ? geoTemplate::getUrl('', $this->messages[500799]) : ''),
        'reserve_met' => (($this->messages[500800]) ? geoTemplate::getUrl('', $this->messages[500800]) : ''),
        'reserve_not_met' => (($this->messages[501665]) ? geoTemplate::getUrl('', $this->messages[501665]) : ''),
        'no_reserve' => (($this->messages[500802]) ? geoTemplate::getUrl('', $this->messages[500802]) : ''),
        'verified' => (($this->messages[500952]) ? geoTemplate::getUrl('', $this->messages[500952]) : ''),
    );

    $cfg['empty'] = $this->messages[501619];

    $tpl_vars['cfg'] = $cfg;
    $tpl_vars['headers'] = $headers;

    //now set up all the listing data

    //common text
    $text = array(
        'item_type' => array(
            'classified' => $txt_vars['item_type_1'],
            'auction' => $txt_vars['item_type_2']
        ),
        'business_type' => array(
            1 => $txt_vars['business_type_1'],
            2 => $txt_vars['business_type_2'],
        ),
        'time_left' => array(
            'weeks' => $txt_vars['weeks'],
            'days' => $txt_vars['days'],
            'hours' => $txt_vars['hours'],
            'minutes' => $txt_vars['minutes'],
            'seconds' => $txt_vars['seconds'],
            'closed' => $txt_vars['closed']
        )
    );
    //overwrite settings with module settings
    if ($show_module['photo_or_icon'] != 2) {
        $browsing->configuration_data['photo_or_icon'] = (int)$show_module['photo_or_icon'];
    }
    $browsing->configuration_data['dynamic_image_dims'] = (int)$show_module['dynamic_image_dims'];
    $browsing->configuration_data['featured_thumbnail_max_width'] = (int)$show_module['module_thumb_width'];
    $browsing->configuration_data['featured_thumbnail_max_height'] = (int)$show_module['module_thumb_height'];
    $browsing->configuration_data['display_all_of_description'] = ((int)$show_module['length_of_description'] == 0);
    $browsing->configuration_data['length_of_description'] = (int)$show_module['length_of_description'];

    while ($row = $browse_result->FetchRow()) {
        $id = $row['id']; //template expects $listings to be keyed by classified id

        $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

        //use the common geoBrowse class to do all the common heavy lifting
        //always pass in 1 for featured, to make it use module width/height settings
        $listings[$id] = $browsing->commonBrowseData($row, $text, true);

        //css is different enough to not include in the common file
        $listings[$id]['css'] = 'browsing_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');

        //also do addons separately
        $listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_module_display_browse_result_addRow', array('this' => $page,'show_classifieds' => $row, 'fields' => $fields, 'show_module' => $show_module), geoAddon::ARRAY_ARRAY);
    }
    $tpl_vars['listings'] = $listings;
}
//done with browsing class
unset($browsing);

$tpl_vars['hide_headers'] = !$show_module['module_display_header_row'];
$tpl_vars['module'] = $show_module;

$browse_view = 'grid';
if (isset($show_module['browse_view']) && in_array($show_module['browse_view'], array ('grid','list','gallery'))) {
    //there is no browse_view setting for modules, if set that means it was smarty param
    $browse_view = $show_module['browse_view'];
}
$tpl_vars['browse_tpl'] = 'common/' . $browse_view . '_view.tpl';

foreach ($show_module as $key => $value) {
    //encode only the active parameters as json (to keep js/post size down)
    //use this to persist smarty setting overrides through ajax pagination
    if ($value) {
        $json[$key] = $value;
    }
}
$tpl_vars['params_json'] = json_encode($json);

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
