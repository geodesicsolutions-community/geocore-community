<?php

//addons/charity_tools/admin.php

# Charity Tools

require_once ADDON_DIR . 'charity_tools/info.php';

class addon_charity_tools_admin extends addon_charity_tools_info
{
    public function init_pages()
    {
        menu_page::addonAddPage('addon_charity_tools_settings', '', 'Settings', $this->name);
        menu_page::addonAddPage('addon_charity_tools_charitable_report', '', 'Charitable Badge Reports', $this->name);
    }

    public function display_addon_charity_tools_settings()
    {
        $db = DataAccess::getInstance();

        if (isset($_GET['deleteCharitable']) && $_GET['deleteCharitable']) {
            $sql = "DELETE FROM `geodesic_addon_charity_tools_charitable` WHERE `id` = ?";
            $db->Execute($sql, array($_GET['deleteCharitable']));

            //remove description from addon text, if applicable
            $sql = "DELETE FROM `geodesic_addon_text` WHERE `auth_tag` = ? AND `addon` = ? AND `text_id` = ?";
            $db->Execute($sql, array('geo_addons','charity_tools','tooltip_charitable_description_' . $_GET['deleteCharitable']));
        }

        $reg = geoAddon::getRegistry($this->name);
        $tpl_vars['adminMessages'] = geoAdmin::m();

        $tpl_vars['use_neighborly'] = $reg->use_neighborly;
        $tpl_vars['neighborly_duration'] = $reg->get('neighborly_duration', 12);
        $tpl_vars['neighborly_image'] = $reg->get('neighborly_image');
        $tpl_vars['neighborly_preview'] = geoTemplate::getUrl('images', 'addon/charity_tools/' . $tpl_vars['neighborly_image']);


        $sql = "SELECT * FROM `geodesic_addon_charity_tools_charitable`";
        $result = $db->Execute($sql);
        foreach ($result as $c) {
            $tpl_vars['charitables'][$c['id']] = array(
                'name' => geoString::fromDB($c['name']),
                'image' => geoTemplate::getUrl('images', 'addon/charity_tools/' . $c['image']),
                'region' => $c['region'] ? geoRegion::getNameForRegion($c['region']) : false,
                'zipcode' => $c['zipcode'],
                'description' => geoString::fromDB($c['description']),
                'deleteLink' => 'index.php?page=addon_charity_tools_settings&deleteCharitable=' . $c['id']
            );
        }

        $tpl_vars['charitable_override'] = $reg->charitable_override;
        if ($tpl_vars['charitable_override']) {
            $tpl_vars['override_image'] = geoTemplate::getUrl('images', 'addon/charity_tools/' . $tpl_vars['charitable_override']);
        }

        $tpl_vars['newRegion'] = geoRegion::regionSelector('nc[region]');

        geoView::getInstance()->setBodyTpl('admin/settings.tpl', $this->name)
            ->setBodyVar($tpl_vars);
    }

    public function update_addon_charity_tools_settings()
    {
        $reg = geoAddon::getRegistry($this->name);
        $settings = $_POST['settings'];

        if ($settings) {
            $reg->use_neighborly = (isset($settings['use_neighborly']) && $settings['use_neighborly'] == 1) ? 1 : false;
            $reg->neighborly_image = $settings['neighborly_image'];
            $reg->neighborly_duration = ($settings['neighborly_duration']) ? $settings['neighborly_duration'] : 12;
            $reg->charitable_override = ($settings['charitable_override']) ? $settings['charitable_override'] : '';
        }

        $newCharitable = $_POST['nc'];
        $name = geoString::toDB($newCharitable['name']);
        $image = $newCharitable['image'];

        $zipcode = $newCharitable['zipcode'] ? $newCharitable['zipcode'] : '';
        $region = 0;
        if (!$zipcode) {
            //no zipcode entered, so check for a Region
            //we only look for regions if no zipcode in order to handle input on sites with a single top-level region, where level 1 would always be populated
            while (($r = array_pop($newCharitable['region'])) !== null) {
                if ($r) {
                    $region = $r;
                    break;
                }
            }
        }




        if ($name && $image) {
            $db = DataAccess::getInstance();

            $description = geoString::toDB($newCharitable['description']);

            $sql = "INSERT INTO `geodesic_addon_charity_tools_charitable` (`name`,`image`,`region`,`zipcode`,`description`) VALUES (?,?,?,?,?)";
            $db->Execute($sql, array($name,$image,$region, $zipcode, $description));
            $insert_id = $db->Insert_Id();

            if ($insert_id && $description) {
                //also gimp the description into addon-text
                $sql = "INSERT INTO `geodesic_addon_text` (`auth_tag`,`addon`,`text_id`,`language_id`,`text`) VALUES(?,?,?,?,?)";
                $db->Execute($sql, array('geo_addons','charity_tools','tooltip_charitable_description_' . $insert_id,'1',$description));
            }
        }

        $reg->save();
        return true;
    }

    public function display_addon_charity_tools_charitable_report()
    {
        $db = DataAccess::getInstance();
        if ($_POST['d']) {
            $startDate = strtotime($_POST['d']['start_date']);
            $endDate = strtotime($_POST['d']['end_date']);

            $sql = "SELECT * FROM `geodesic_addon_charity_tools_charitable_purchases` as p, `geodesic_addon_charity_tools_charitable` as c WHERE p.purchased_badge = c.id AND `time` BETWEEN ? AND ? ORDER BY `region`";
            $result = $db->Execute($sql, array($startDate, $endDate));
            if (!$result || $result->RecordCount() == 0) {
                geoAdmin::m('Found no charitable badge purchases for this timeframe');
            } else {
                foreach ($result as $purchase) {
                    $tpl_vars['badgeData'][$purchase['purchased_badge']]['total'] += $purchase['price'];
                    $tpl_vars['purchases'][$purchase['purchased_badge']][] = array(
                        'listing' => $purchase['listing'],
                        'time' => date('M d Y', $purchase['time']),
                        'price' => geoString::displayPrice($purchase['price'])
                    );

                    if (!$tpl_vars['badgeData'][$purchase['purchased_badge']]['name']) {
                        $tpl_vars['badgeData'][$purchase['purchased_badge']]['name'] = geoString::fromDB($purchase['name']);
                    }
                    if (!$tpl_vars['badgeData'][$purchase['purchased_badge']]['region']) {
                        $tpl_vars['badgeData'][$purchase['purchased_badge']]['region'] = geoRegion::getNameForRegion($purchase['region']);
                    }
                }
            }
        }


        $tpl_vars['adminMsgs'] = geoAdmin::m();
        geoView::getInstance()->setBodyTpl('admin/charitable_report.tpl', $this->name)
            ->setBodyVar($tpl_vars)
            ->addCssFile('css/calendarview.css')
            ->addJScript('../js/calendarview.js');
    }

    public function init_text($languageId)
    {
        $return = array
        (
            'charitable_badge_label' => array (
                'name' => 'Charitable Badge Label',
                'desc' => 'Labels the charitable badge selection box, on the "other details" step',
                'type' => 'input',
                'default' => 'Charitable Badge',
                'section' => 'Charitable Badge'
            ),
            'charitable_badge_selection_error' => array (
                'name' => 'Charitable Badge Selection Error',
                'desc' => 'Shown when a valid Charitable Badge is not selected',
                'type' => 'input',
                'default' => 'You must select a specific Charitable Badge',
                'section' => 'Charitable Badge'
            ),
            'charitable_badge_cart_title' => array (
                'name' => 'Charitable Badge Cart Title',
                'desc' => 'Shown as the name of the Charitable Badge item in the cart',
                'type' => 'input',
                'default' => 'Charitable Badge',
                'section' => 'Charitable Badge'
            ),
            'tooltip_listing_placement' => array (
                'name' => 'Charitable Badge Listing Placement Tooltip',
                'desc' => 'Text of the tooltip that appears with the Charitable Badge during listing placement',
                'type' => 'input',
                'default' => 'For a donation to charity, the selected badge will appear on your listing',
                'section' => 'Charitable Badge'
            ),
            'tooltip_badge_display_neighborly' => array (
                'name' => 'Good Neighbor Badge Explanation',
                'desc' => 'Text of the tooltip that appears alongside the Good Neighbor badge',
                'type' => 'input',
                'default' => 'The seller of this listing has been recognized by the site admin as exceptionally charitable',
                'section' => 'Charitable Badge'
            ),
            'tooltip_badge_display_charitable' => array (
                'name' => 'Good Neighbor Badge Explanation',
                'desc' => 'Text of the tooltip that appears alongside the Good Neighbor badge',
                'type' => 'input',
                'default' => 'The seller of this listing has made a donation to charity',
                'section' => 'Charitable Badge'
            ),
        );

        //add in admin descriptions for each field so that they may be accessed by the tooltip system (yay for dirty, dirty hacks!)
        $db = DataAccess::getInstance();
        $sql = "SELECT * FROM `geodesic_addon_charity_tools_charitable` WHERE `description` <> ''";
        $result = $db->Execute($sql);
        foreach ($result as $d) {
            $return['tooltip_charitable_description_' . $d['id']] = array(
                'name' => 'INTERNAL TOOLTIP Relay #' . $d['id'],
                'desc' => 'DO NOT EDIT THIS. CHANGE THIS TEXT ONLY ON THE Settings PAGE',
                'type' => 'input',
                'default' => geoString::fromDB($d['description']),
                'section' => 'Internal Use Only'
            );
        }

        return $return;
    }
}
