<?php

//FILE_LOCATION/FILE_NAME.php

# tokens

require_once ADDON_DIR . 'tokens/info.php';

class addon_tokens_util extends addon_tokens_info
{
    public function core_Admin_site_display_user_data($user_id)
    {
        //add the calendar JS, for use on the add token ajax page
        geoView::getInstance()->addJScript('../js/calendarview.js')
            ->addCssFile('css/calendarview.css');

        $tpl_vars = array();

        $tpl_vars['allTokens'] = $this->getTokensFor($user_id);
        $tpl_vars['user_id'] = $user_id;

        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign($tpl_vars);

        return $tpl->fetch('addon/tokens/admin/show_tokens_user.tpl');
    }

    public function core_Admin_user_management_update_users_view($user_id)
    {
        $data = $_POST['tokens'];
        $user_id = (int)$user_id;
        if (!$user_id || !$data) {
            return;
        }
        if (isset($data['count']) && $data['count']) {
            //add new tokens
            $count = (int)$data['count'];
            $expire_parts = explode('-', $data['expire']);

            $date_error_msg = "Invalid expiration date format, expecting 'YYYY-MM-DD'";

            if (count($expire_parts) !== 3) {
                geoAdmin::m($date_error_msg, geoAdmin::ERROR);
                return;
            }
            $year = $expire_parts[0];
            $month = $expire_parts[1];
            $day = $expire_parts[2];
            if (strlen($year) != 4 || strlen($month) != 2 || strlen($day) != 2) {
                geoAdmin::m($date_error_msg, geoAdmin::ERROR);
                return;
            }
            $expire = mktime(23, 59, 0, $month, $day, $year);
            if ($expire < geoUtil::time()) {
                geoAdmin::m("Cannot add tokens that expire in the past.", geoAdmin::ERROR);
                return;
            }
            $this->addTokensFor($user_id, $count, $expire);
            geoAdmin::m("Tokens added.");
            return;
        }
        if (isset($data['delete']) && $data['delete']) {
            //token ID to remove
            $id = (int)$data['delete'];
            DataAccess::getInstance()->Execute("DELETE FROM " . geoTables::user_tokens . " WHERE `id`=$id AND `user_id`=$user_id");
            geoAdmin::m("Tokens deleted.");
        }
    }

    public function core_notify_user_remove($user_id)
    {
        $db = DataAccess::getInstance();
        $user_id = (int)$user_id;
        $db->Execute("DELETE FROM " . geoTables::user_tokens . " WHERE `user_id`=$user_id");
    }

    public function core_User_management_information_display_user_data()
    {
        $user_id = (int)geoSession::getInstance()->getUserId();
        if (!$user_id) {
            return;
        }
        $allTokens = $this->getTokensFor($user_id);
        if (!$allTokens) {
            //no tokens to show!
            return;
        }
        $tpl_vars = array();

        $msgs = $tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);

        $db = DataAccess::getInstance();
        $tpl_vars['entry_date_configuration'] = $db->get_site_setting('entry_date_configuration');
        $tpl_vars['allTokens'] = $allTokens;
        $tpl_vars['token_entries'] = count($allTokens);
        $tpl_vars['user_id'] = $user_id;

        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign($tpl_vars);

        $value = $tpl->fetch('user_info_tokens.tpl');
        /*
        $total = 0;

        foreach ($allTokens as $token) {
            $value .= "{$token['token_count']} (expires ".date($entry_date_configuration, $token['expire']).')<br />';
            $total += $token['token_count'];
        }
        if (count($allTokens > 1)) {
            $value .= "<br /><strong>Total Tokens:</strong> $total";
        }
        */
        return array ('label' => $msgs['user_info_label'], 'value' => $value);
    }

    public function core_Admin_Group_Management_edit_group_display($vars)
    {
        $group_id = (int)$vars['group_id'];

        $reg = geoAddon::getRegistry($this->name);

        $tpl_vars = array();
        $tpl_vars['group_starting_tokens_count'] = $reg->get($group_id . 'group_starting_tokens_count', 0);

        $day = $tpl_vars['day'] = 86400;
        $year = $tpl_vars['year'] = 31536000;

        $group_starting_tokens_expire_period = $reg->get($group_id . 'group_starting_tokens_expire_period', $year);

        if ($group_starting_tokens_expire_period >= $year) {
            $tpl_vars['group_starting_tokens_expire_period'] = (int)($group_starting_tokens_expire_period / $year);
            $tpl_vars['group_starting_tokens_expire_period_units'] = $year;
        } else {
            $tpl_vars['group_starting_tokens_expire_period'] = (int)($group_starting_tokens_expire_period / $day);
            $tpl_vars['group_starting_tokens_expire_period_units'] = $day;
        }

        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign($tpl_vars);

        return $tpl->fetch('addon/tokens/admin/group_settings.tpl');
    }

    public function core_Admin_Group_Management_edit_group_update($vars)
    {
        $group_id = (int)$vars['group_id'];

        $settings = $_POST['tokens'];

        if (!$settings) {
            //settings not sent?
            return;
        }
        if (isset($settings['group_starting_tokens_count'])) {
            $reg = geoAddon::getRegistry($this->name);

            $reg->set($group_id . 'group_starting_tokens_count', (int)$settings['group_starting_tokens_count']);

            $day = 86400;
            $year = 31536000;

            if ($settings['group_starting_tokens_expire_period_units'] == $year) {
                $expire = (int)$settings['group_starting_tokens_expire_period'] * $year;
            } else {
                $expire = (int)$settings['group_starting_tokens_expire_period'] * $day;
            }
            $reg->set($group_id . 'group_starting_tokens_expire_period', $expire);

            $reg->save();
        }
    }

    public function core_registration_add_field_update($vars)
    {
        $user_id = (int)$vars['user_id'];

        if (!$user_id) {
            //don't care about it
            return;
        }
        $user = geoUser::getUser($user_id);

        $group_id = $user->group_id;
        if (!$group_id) {
            //not sure what group ID is
            return;
        }

        $reg = geoAddon::getRegistry($this->name);
        $starting_tokens = (int)$reg->get($group_id . 'group_starting_tokens_count', 0);
        if ($starting_tokens > 0) {
            $expire = geoUtil::time() + $reg->get($group_id . 'group_starting_tokens_expire_period');
            if ($expire > geoUtil::time()) {
                //add tokens!

                $this->addTokensFor($user_id, $starting_tokens, $expire);
            }
        }
    }

    public function getPriceOptions($price_plan_id, $convertUnits = false)
    {
        $db = DataAccess::getInstance();
        $price_plan_id = (int)$price_plan_id;

        $rows = $db->GetAll("SELECT * FROM " . self::TOKENS_PRICE_TABLE . " WHERE `price_plan_id` = $price_plan_id ORDER BY `tokens`");

        $day = 86400;
        $year = 31536000;

        $options = array();
        foreach ($rows as $row) {
            if ($convertUnits) {
                if ($row['expire_period'] >= $year) {
                    $row['expire_period_units'] = $year;
                    $row['expire_period'] = (int)($row['expire_period'] / $year);
                } else {
                    //use days
                    $row['expire_period_units'] = $day;
                    $row['expire_period'] = (int)($row['expire_period'] / $day);
                }
            }

            $options[$row['tokens']] = $row;
        }

        return $options;
    }

    public function getTokenInfo($price_plan_id, $number_tokens)
    {
        $db = DataAccess::getInstance();
        $price_plan_id = (int)$price_plan_id;
        $number_tokens = (int)$number_tokens;

        if (!$price_plan_id || !$number_tokens) {
            //oops!
            return false;
        }

        return $db->GetRow("SELECT * FROM " . self::TOKENS_PRICE_TABLE . " WHERE `price_plan_id` = $price_plan_id AND `tokens` = $number_tokens");
    }

    public function getTokensFor($user_id)
    {
        $user_id = (int)$user_id;
        $db = DataAccess::getInstance();

        return $db->GetAll("SELECT * FROM " . geoTables::user_tokens . " WHERE `user_id`=? ORDER BY `expire`", array ($user_id));
    }

    public function addTokensFor($user_id, $token_count, $expiration)
    {
        $db = DataAccess::getInstance();

        $user_id = (int)$user_id;
        $expiration = (int)$expiration;
        $token_count = (int)$token_count;

        if ($token_count < 1 || $user_id <= 1) {
            //no good
            return;
        }

        $db->Execute(
            "INSERT INTO `geodesic_user_tokens` SET `user_id`=?, `token_count`=?, `expire`=?",
            array($user_id, $token_count, $expiration)
        );
    }

    public function expireTokens()
    {
        //expire all out-of-date tokens for all users
        $sql = "DELETE FROM " . geoTables::user_tokens . " WHERE `expire` < " . geoUtil::time() . " OR `token_count` = 0";
        DataAccess::getInstance()->Execute($sql);
    }
}
