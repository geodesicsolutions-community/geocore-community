<?php

//addons/core_display/util.php

# storefront Addon

require_once ADDON_DIR . 'core_display/info.php';

class addon_core_display_util extends addon_core_display_info
{

    public function core_process_browsing_filters($category)
    {

        //let filter class know if we're in a certain category
        geoBrowsingFilter::setBrowsingCategory($category);

        //grab all previously-existing filters out of the database and load them to be active
        geoBrowsingFilter::retrieveAll();

        if (isset($_GET['setFilter']) && isset($_GET['filterValue'])) {
            //setting a new filter
            $target = $_GET['setFilter'];
            $value = geoString::toDB($_GET['filterValue']);
            $newFilter = geoBrowsingFilter::getFilter($target);
            if ($newFilter) {
                $newFilter->activate($value);
            } else {
                //not a valid filter target
            }
        }

        if (isset($_POST['filterRange'])) {
            foreach ($_POST['filterRange'] as $target => $values) {
                $newFilter = geoBrowsingFilter::getFilter($target);
                if ($newFilter && $newFilter->getType() == geoBrowsingFilter::RANGE) {
                    //clean inputs
                    $values['low'] = geoNumber::deformat($values['low']);
                    $values['high'] = geoNumber::deformat($values['high']);

                    $value = array();
                    $value['low'] = max(0, $values['low']);
                    $value['high'] = ($values['high']) ? min(100000000, $values['high']) : 100000000;
                    if ($value['low'] == 0 && $value['high'] == 100000000) {
                        //not actually filtering by anything (both high and low left blank or invalid)
                        continue;
                    }

                    $newFilter->activate($value);
                } else {
                    //not a valid filter target
                }
            }
        }

        if (isset($_POST['filterDate'])) {
            foreach ($_POST['filterDate'] as $target => $values) {
                $newFilter = geoBrowsingFilter::getFilter($target);
                if ($newFilter && $newFilter->getType() == geoBrowsingFilter::DATE_RANGE) {
                    //clean inputs
                    $values['start'] = geoCalendar::fromInput($values['start']);
                    $values['end'] = geoCalendar::fromInput($values['end']);

                    $value = array();
                    $value['low'] = $values['start'] ? $values['start'] : 0;
                    $value['high'] = $values['end'] ? $values['end'] : 100000000;
                    if ($value['low'] == 0 && $value['high'] == 100000000) {
                        //not actually filtering by anything (both high and low left blank or invalid)
                        continue;
                    }
                    $newFilter->activate($value);
                } else {
                    //not a valid filter target
                }
            }
        }

        if (isset($_GET['resetFilter'])) {
            //removing an existing filter
            $deactivate = $_GET['resetFilter'];
            $filter = geoBrowsingFilter::getFilter($deactivate);
            if ($filter) {
                $filter->deactivate();
            } else {
                //sanity failure -- deactivating a filter that doesn't exist!
            }
        }

        if (isset($_GET['resetAllFilters']) && $_GET['resetAllFilters'] == 1) {
            geoBrowsingFilter::deactivateAll();
        }
    }

    public function core_geoFields_getDefaultLocations($vars)
    {
        //expected to return using following format:
        return array (
            'core_featured_gallery' => 'Browsing Featured Gallery',
            //you can add as many locations as you want.
        );
    }

    public function core_admin_category_manage_add_links($category_id)
    {
        $links = array();
        $links[] = array (
            'href' => 'index.php?page=browsing_filter_settings&mc=addon_cat_core_display&category=' . $category_id,
            'label' => 'Browse Filters'
            );
        return $links;
    }

    public function core_admin_category_list_specific_icons($row)
    {
        $category_id = (int)$row['category_id'];
        $icons = array();
        //expects and array of arrays..

        //note that it expects same icons to show even if it is not active for this
        //category
        $icons[] = array(
            'title' => 'Browsing Filters',
            'src' => 'admin_images/icons/filter.png',
            'active' => (int)DataAccess::getInstance()->GetOne("SELECT COUNT(*) FROM " . geoTables::browsing_filters_settings . " WHERE `category`=$category_id")
            );
        return $icons;
    }
}
