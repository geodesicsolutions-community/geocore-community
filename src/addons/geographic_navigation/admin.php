<?php

class addon_geographic_navigation_admin extends addon_geographic_navigation_info
{
    var $body;
    private $self_path;
    public $url;

    public function init_pages()
    {
        menu_page::addonAddPage('addon_geographic_navigation_settings', '', 'Settings', 'geographic_navigation', 'fa-globe');
        menu_page::addonAddPage('addon_geographic_navigation_subdomains', '', 'Sub Domains', 'geographic_navigation', 'fa-globe');

        $this->self_path = "?page={$_GET['page']}&mc=addon_geographic_navigation_admin";
    }



    public function display_addon_geographic_navigation_subdomains()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $reg = geoAddon::getRegistry($this->name);
        $util = geoAddon::getUtil($this->name);

        $tpl_vars = array();
        $tpl_vars['adminMessages'] = geoAdmin::m();

        $tpl_vars['subdomains'] = $reg->subdomains;
        $tpl_vars['forceSubdomainListing'] = $reg->forceSubdomainListing;
        $tpl_vars['domain'] = $util->getDomain();


        geoView::getInstance()->setBodyVar($tpl_vars)
            ->setBodyTpl('admin/subdomains.tpl', $this->name);
    }

    public function update_addon_geographic_navigation_subdomains()
    {
        $validSettings = array ('configure','on');

        $subdomains = $_POST['subdomains'];
        //it's either configure, on, or false for off.
        $subdomains = (in_array($subdomains, $validSettings)) ? $subdomains : false;

        //save settings
        $reg = geoAddon::getRegistry($this->name);
        $reg->subdomains = $subdomains;
        if ($subdomains == 'on') {
            $forceSubdomainListing = (isset($_POST['forceSubdomainListing']) && $_POST['forceSubdomainListing']) ? 1 : false;
            $reg->forceSubdomainListing = $forceSubdomainListing;
        } else {
            $reg->forceSubdomainListing = false;
        }
        $reg->save();

        if ($subdomains && isset($_POST['autoAdd']) && $_POST['autoAdd'] == 'add') {
            //auto-add sub-domain for every region found
            $db = DataAccess::getInstance();
            $util = geoAddon::getUtil($this->name);
            //auto-set for countries

            $skipped = $set = 0;
            $skipped_text = '';

            //auto-set for regions
            $regions = $db->GetAll("SELECT * FROM " . geoTables::region . " as r, " . geoTables::region_languages . " as l WHERE r.id=l.id AND l.language_id = 1 AND r.`unique_name`='' ORDER BY r.`parent`, r.`id`");

            foreach ($regions as $region) {
                $regionId = (int)$region['id'];

                //begin with the name of this region in the default language
                $subdomain = geoString::fromDB($region['name']);

                //prepend with parent subdomain if there is one
                if ($region['parent']) {
                    $parentSub = $db->GetOne("SELECT `unique_name` FROM " . geoTables::region . " WHERE `id`=?", array((int)$region['parent']));
                    if ($parentSub) {
                        $subdomain = "$subdomain.$parentSub";
                    }
                }

                //make it pretty
                $subdomain = $util->subdomainClean($subdomain);


                if (!$regionId || !$subdomain || $util->subdomainUsed($subdomain)) {
                    $skipped++;
                    $skipped_text .= "Region: " . geoString::fromDB($region['label']) . "($regionId) - Would-be Subdomain: $subdomain<br />";
                    continue;
                }
                $db->Execute(
                    "UPDATE " . geoTables::region . " SET `unique_name`=? WHERE `id`=?",
                    array ($subdomain, $regionId)
                );
                $set++;
            }
            $message = "Settings saved, and subdomains automatically set for $set locations.";
            if ($skipped) {
                $message .= "($skipped possible duplicates skipped, see details listed above)";
            }
            geoAdmin::m($message, geoAdmin::SUCCESS);
            if ($skipped_text) {
                geoAdmin::m("The following locations were skipped, most likely because of a duplicate sub-domain already in use:<br /><br />" . $skipped_text, geoAdmin::NOTICE);
            }
        } elseif ($subdomains && isset($_POST['autoAdd']) && $_POST['autoAdd'] == 'clear') {
            //going to clear all sub-domains
            $db = DataAccess::getInstance();
            $db->Execute("UPDATE " . geoTables::region . " SET `unique_name`=''");
            geoAdmin::m("Settings saved, and all subdomains for all locations have been cleared.", geoAdmin::SUCCESS);
        }


        return true;
    }

    public function display_addon_geographic_navigation_settings()
    {
        $reg = geoAddon::getRegistry($this->name);

        $tpl_vars = array();
        $tpl_vars['adminMessages'] = geoAdmin::m();

        $tpl_vars['columns'] = (int)$reg->get("columns");
        $tpl_vars['tree'] = $reg->tree;
        $tpl_vars['showInSearchBox'] = $reg->showInSearchBox;
        $tpl_vars['showInTitleListing'] = $reg->showInTitleListing;
        $tpl_vars['showInTitle'] = $reg->showInTitle;
        $tpl_vars['terminalSiblings'] = $reg->terminalSiblings;

        $tpl_vars['showLegacyUrlSetting'] = DataAccess::getInstance()->tableExists("geodesic_countries") ? true : false;
        $tpl_vars['useLegacyUrls'] = $reg->useLegacyUrls;

        $tpl_vars['geo_ip'] = $reg->geo_ip;
        $tpl_vars['geo_ip_apikey'] = $reg->geo_ip_apikey;
        $tpl_vars['geo_ip_redirect_ssl'] = $reg->geo_ip_redirect_ssl;

        $tpl_vars['countFormat'] = $reg->countFormat;
        $tpl_vars['showSubs'] = $reg->showSubs;
        $tpl_vars['combineTree'] = $reg->combineTree;
        $tpl_vars['hideEmpty'] = $reg->hideEmpty;

        $tpl_vars['countOptions'] = array (0 => 'No Counts');

        if (geoMaster::is('classifieds')) {
            $tpl_vars['countOptions']['c'] = 'Classified Count';
        }
        if (geoMaster::is('auctions')) {
            $tpl_vars['countOptions']['a'] = 'Auction Count';
        }
        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            $tpl_vars['countOptions']['ca'] = 'Classified then Auction Count';
            $tpl_vars['countOptions']['ac'] = 'Auction then Classified Count';
            $tpl_vars['countOptions']['all'] = 'Combined Count';
        }

        $view = geoView::getInstance();
        $view->setBodyVar($tpl_vars)
            ->setBodyTpl('admin/settings.tpl', $this->name);
    }

    public function update_addon_geographic_navigation_settings()
    {
        $settings = $_POST['settings'];

        if (isset($settings['columns'])) {
            $reg = geoAddon::getRegistry('geographic_navigation');

            $reg->columns = (intval($settings['columns']) > 0) ? intval($settings['columns']) : 1;

            $validTree = array ('compact', 'full');

            $reg->tree = (in_array($settings['tree'], $validTree)) ? $settings['tree'] : false;
            $reg->showSubs = (isset($settings['showSubs']) && $settings['showSubs']) ? 1 : false;
            $reg->combineTree = (isset($settings['combineTree']) && $settings['combineTree']) ? 1 : false;
            $reg->hideEmpty = (isset($settings['hideEmpty']) && $settings['hideEmpty']) ? 1 : false;
            $reg->showInSearchBox = (isset($settings['showInSearchBox']) && $settings['showInSearchBox']) ? 1 : false;
            $reg->showInTitleListing = (isset($settings['showInTitleListing']) && $settings['showInTitleListing']) ? 1 : false;
            $reg->showInTitle = (isset($settings['showInTitle']) && $settings['showInTitle']) ? 1 : false;
            $reg->terminalSiblings = (isset($settings['terminalSiblings']) && $settings['terminalSiblings']) ? 1 : false;
            $reg->useLegacyUrls = (isset($settings['useLegacyUrls']) && $settings['useLegacyUrls']) ? 1 : false;
            $reg->geo_ip = (isset($settings['geo_ip']) && $settings['geo_ip']) ? 1 : false;
            $reg->geo_ip_apikey = trim($settings['geo_ip_apikey']);
            $reg->geo_ip_redirect_ssl = (isset($settings['geo_ip_redirect_ssl']) && $settings['geo_ip_redirect_ssl']) ? 1 : false;

            if ($settings['countFormat'] == '0') {
                $settings['countFormat'] = false;
            }
            $reg->countFormat = $settings['countFormat'];

            $reg->save();
        }

        return true;
    }

    public function init_text($languageId)
    {
        $text = array
        (
            'currentRegion' => array (
                'name' => 'Current Region',
                'desc' => 'Used when displaying the Addon tag.',
                'type' => 'input',
                'default' => 'Region:'
            ),
            'allRegions' => array (
                'name' => 'All Regions link',
                'desc' => 'Used when displaying the Addon tag.',
                'type' => 'input',
                'default' => 'All Regions'
            ),
            'selectRegions' => array (
                'name' => 'Select Region text',
                'desc' => 'Used in select drop-down.',
                'type' => 'input',
                'default' => 'Select'
            ),
            'allRegionsSelect' => array (
                'name' => 'All Regions select text',
                'desc' => 'Used in select drop-down specifically in search box.',
                'type' => 'input',
                'default' => 'All Regions'
            ),
            'noRegions' => array (
                'name' => 'No Regions text',
                'desc' => 'Used if there are no regions at current level.  Make blank to not display any message.',
                'type' => 'input',
                'default' => 'There are no sub-regions at this level.'
            ),
            'userInfoLocationLabel' => array (
                'name' => 'Location Label in User Info',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location'
            ),
            'userInfoEditLocationLabel' => array (
                'name' => 'Location Label in Edit User Info',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location'
            ),
            'detailsLocationLabel' => array (
                'name' => 'Location Label in Listing Detail Collection',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location'
            ),
            'registerLocationLabel' => array (
                'name' => 'Location Label in Registration',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location'
            ),
            'searchLocationLabel' => array (
                'name' => 'Location Label in Advanced Search',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location'
            ),
            'browsingHeader' => array (
                'name' => 'Browsing Listings Header Text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Geographic Location'
            ),
            'clearRegions' => array (
                'name' => '[clear] text used on compact breadcrumb',
                'desc' => '',
                'type' => 'input',
                'default' => '[Clear]',
            ),
            'changeLinkText' => array (
                'name' => 'Link text used on change location link',
                'desc' => '',
                'type' => 'input',
                'default' => 'change location',
            ),
            'selectLocationTitle' => array (
                'name' => 'Select location title text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Select Location',
            ),
            'selectButton' => array (
                'name' => 'Select button link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Select',
            ),
            'clearSelectionButton' => array (
                'name' => 'Clear Selection button link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Clear Selection',
            ),
            'cancelButton' => array (
                'name' => 'Cancel button text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Cancel',
            ),
            'view_listing_filters_column_header' => array (
                'name' => 'Listing Filters column header',
                'desc' => '',
                'type' => 'input',
                'default' => 'Region',
            ),


            //ERRORS ::

            'errorListingRequired' => array (
                'name' => 'Location Required error message (Listing)',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location is Required.'
            ),
            'errorUserRequired' => array (
                'name' => 'Location Required error message (User)',
                'desc' => '',
                'type' => 'input',
                'default' => 'Location is Required.'
            ),
            'errorListingEndRequired' => array (
                'name' => 'Location terminal-level Required error message (Listing)',
                'desc' => '',
                'type' => 'input',
                'default' => 'Full Location Depth is Required.'
            ),
            'errorUserEndRequired' => array (
                'name' => 'Location terminal-level Required error message (user)',
                'desc' => '',
                'type' => 'input',
                'default' => 'Full Location Depth is Required.'
            ),
        );
        return $text;
    }
}
