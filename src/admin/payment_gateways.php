<?php

//payment_gateways.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

class paymentGatewayManage
{
    private $group_name;
    private $group = 0;

    public function __construct()
    {
        if (isset($_GET['group']) && intval($_GET['group']) > 0) {
            $group = (int)$_GET['group'];
            $sql = "SELECT `name`, `group_id` FROM " . geoTables::groups_table . " WHERE `group_id` = ? LIMIT 1";
            $db = DataAccess::getInstance();
            $row = $db->GetRow($sql, array($group));
            if (is_array($row) && isset($row['group_id']) && $row['group_id'] == $group) {
                geoPaymentGateway::setGroup($group);
                $this->group = geoPaymentGateway::getGroup();
                if ($this->group > 0) {
                    $this->group_name = $row['name'];
                }
            }
        }
    }

    public function display_payment_gateways()
    {
        $tpl_vars = array();

        $tpl_vars['gateways'] = $this->_getGateways();

        $tpl_vars['group'] = $this->group;
        $tpl_vars['group_name'] = $this->group_name;

        if (!$this->group) {
            //not in group-specific, but check if they exist, and show a warning if so
            //because otherwise it's easy for users to forget they have those and get confused when the "main" settings don't take effect
            $groupSettings = DataAccess::getInstance()->GetOne('SELECT COUNT(`name`) WHERE `group` > 0');
            if ($groupSettings > 0) {
                geoAdmin::m('At least one User Group has group-specific Payment gateway settings that may override these.', geoAdmin::NOTICE);
            }
        }

        $tpl_vars['admin_msgs'] = geoAdmin::m();

        geoView::getInstance()->addCssFile('css/payment_gateways.css')
            ->setBodyTpl('payment_gateways/index.tpl')
            ->setBodyVar($tpl_vars)
            ->page_title = (strlen($this->group_name) > 0) ? ' (' . $this->group_name . ')' : ' (Site-Wide)';
    }

    public function update_payment_gateways()
    {
        return true;
    }

    private function _getGateways()
    {
        $responses = geoPaymentGateway::callDisplay('admin_display_payment_gateways', null, 'array', '', false);

        foreach ($responses as $key => $row) {
            $gateway = geoPaymentGateway::getPaymentGateway($row['name']);
            if (!is_object($gateway)) {
                continue;
            }
            $row['enabled'] = $gateway->getEnabled();
            $row['default'] = $gateway->getDefault();
            $row['display_order'] = $gateway->getDisplayOrder();
            $row['show_config'] = is_callable(array($gateway, 'admin_custom_config'));
            $row['group'] = $this->group;
            $responses[$key] = $row;
        }
        return $responses;
    }
}
