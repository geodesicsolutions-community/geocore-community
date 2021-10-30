<?php

//addons/charity_tools/util.php

# Charity Tools

require_once ADDON_DIR . 'charity_tools/info.php';

class addon_charity_tools_util extends addon_charity_tools_info
{
    public function core_Admin_site_display_user_data($user_id)
    {
        $reg = geoAddon::getRegistry($this->name);
        $db = DataAccess::getInstance();
        if (!$reg->use_neighborly) {
            //not using neighborly badge. nothing to do here
            return;
        }
        $html = '';

        $sql = "SELECT `active_until` FROM `geodesic_addon_charity_tools_neighborly` WHERE `user` = ?";
        $activeUntil = $db->GetOne($sql, array($user_id));

        if ($activeUntil) {
            //badge is active. show expiration date and deactivation button
            $goodNeighborStatus = "Active until " . date('F d, Y', $activeUntil) . " <a href='index.php?page=users_view&amp;auto_save=1&amp;neighborly=no&amp;b={$user_id}' class='mini_button lightUpLink'>Deactivate</a>";
        } else {
            //badge not active. show activation button
            $goodNeighborStatus = "<a href='index.php?page=users_view&amp;auto_save=2&amp;neighborly=yes&amp;b={$user_id}' class='mini_button lightUpLink'>Activate</a>";
        }


        $html .= geoHTML::addOption('Good Neighbor Status:', $goodNeighborStatus);


        return $html;
    }

    public function core_Admin_user_management_update_users_view($user_id)
    {
        $reg = geoAddon::getRegistry($this->name);
        $db = DataAccess::getInstance();
        $neighborly_duration = $reg->neighborly_duration;
        $user_id = $_GET['b'];
        if ($_GET['neighborly'] === 'yes') {
            $end = strtotime("+$neighborly_duration months", geoUtil::time());
            $sql = "REPLACE INTO `geodesic_addon_charity_tools_neighborly` (`user`, `active_until`) VALUES (?, ?)";
            $r = $db->Execute($sql, array($user_id, $end));
        } elseif ($_GET['neighborly'] === 'no') {
            $sql = "DELETE FROM `geodesic_addon_charity_tools_neighborly` WHERE `user` = ?";
            $db->Execute($sql, array($user_id));
        }

        return true;
    }

    public function core_use_listing_icons()
    {
        //see if at least one live listing has a charitable badge
        $db = DataAccess::getInstance();
        $sql = "SELECT `id` FROM " . geoTables::classifieds_table . " as c, `geodesic_addon_charity_tools_charitable_purchases` as p WHERE c.live = 1 AND p.listing=c.id";
        $result = $db->Execute($sql);
        $hasC = (bool) $result && $result->RecordCount > 0;

        //see if at least one user has an active good neighbor badge
        $reg = geoAddon::getRegistry($this->name);
        if ($reg->use_neighborly && $reg->neighborly_image) {
            $sql = "SELECT `user` FROM `geodesic_addon_charity_tools_neighborly` WHERE `active_until` > ?";
            $result = $db->Execute($sql, array(geoUtil::time()));
            $hasN = (bool) $result && $result->RecordCount > 0;
        } else {
            $hasN = false;
        }

        return (bool) ($hasN || $hasC);
    }

    public function core_add_listing_icons($data)
    {
        $db = DataAccess::getInstance();
        //does the seller of this listing have a Good Neighbor badge?
        $reg = geoAddon::getRegistry($this->name);
        if ($reg->use_neighborly && $reg->neighborly_image) {
            $seller = $data['seller'];
            $sql = "SELECT `user` FROM `geodesic_addon_charity_tools_neighborly` WHERE `active_until` > ? AND `user` = ?";
            $neighborly = (bool)$db->GetOne($sql, array(geoUtil::time(), $seller));
        }

        //does this listing have a Charitable badge? If so, which?
        $sql = "SELECT `purchased_badge` FROM `geodesic_addon_charity_tools_charitable_purchases` WHERE `listing` = ?";
        $badgeId = $db->GetOne($sql, array($data['id']));
        if ($badgeId) {
            if ($reg->charitable_override) {
                //using an override image, so show it instead of the specific badge image
                $image = $reg->charitable_override;
            } else {
                $sql = "SELECT `image` FROM `geodesic_addon_charity_tools_charitable` WHERE `id` = ?";
                $image = $db->GetOne($sql, array($badgeId));
            }
        }

        $tpl = new geoTemplate('addon', 'charity_tools');

        $iconText = $db->get_text(true, 59);
        $tpl->assign('helpIcon', $iconText[500797]);
        $addonText = geoAddon::getText('geo_addons', 'charity_tools');


        if ($neighborly) {
            $tpl->assign('neighborly', geoTemplate::getUrl('images', 'addon/charity_tools/' . $reg->neighborly_image));
            if ($addonText['tooltip_badge_display_neighborly']) {
                $tpl->assign('neighborly_tooltip', true);
            }
        }
        if ($image) {
            $tpl->assign('charitable', geoTemplate::getUrl('images', 'addon/charity_tools/' . $image));
            if ($addonText['tooltip_badge_display_charitable']) {
                $tpl->assign('charitable_tooltip', true);
            }
        }
        return $tpl->fetch('listing_icons.tpl');
        ;
    }
}
