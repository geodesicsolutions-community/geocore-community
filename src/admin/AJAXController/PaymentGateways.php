<?php

//PaymentGateways.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.2.1-8-ge4be188
##
##################################

if (class_exists('admin_AJAX') or die()) {
}

class ADMIN_AJAXController_PaymentGateways extends admin_AJAX
{
    public function __construct()
    {
        if (!$this->isAllowed('payment_gateways', 'update')) {
            die('NO_INCOMING_CALLS');
        }

        $group = intval($_GET['group']);

        geoPaymentGateway::setGroup($group);
    }

    public function position()
    {
        $this->_ajax_movePosition();
        //re-load the entire table
        $return = array();
        $return['table_settings'] = $this->getTable();
        $return['admin_messages'] = geoAdmin::getInstance()->getMessagesArray();
        $this->jsonHeader();
        echo $this->encodeJSON($return);
    }

    private function getTable()
    {
        //re-load the entire table
        $tpl = new geoTemplate('admin');
        $tpl->assign('gateways', $this->_getGateways());
        $tpl->assign('group', (int)$_GET['group']);
        return $tpl->fetch('payment_gateways/gateway_table.tpl');
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

    public function configure()
    {
        $item = trim($_GET['item']);
        $group = (int)$_GET['group'];

        //this is an ajax call, not a normal page load.  Process ajax call.
        $gateway = geoPaymentGateway::getPaymentGateway($item);
        if (!is_object($gateway)) {
            return $this->_error('Error: Gateway not found!');
        }
        $return = array ();

        $return['settings'] = $gateway->admin_custom_config();

        $this->jsonHeader();
        echo $this->encodeJSON($return);
    }

    public function save()
    {
        $item = $_GET['item'];
        $group = (int)$_GET['group'];
        $admin = geoAdmin::getInstance();

        $gateway = geoPaymentGateway::getPaymentGateway($item);
        if (method_exists($gateway, 'admin_update_payment_gateways')) {
            if (!$gateway->admin_update_payment_gateways()) {
                if ($admin->getMessageCount() == 0) {
                    geoAdmin::m('An error occurred, please try again.', geoAdmin::ERROR, 1);
                    return;
                }
            } else {
                $gateway->serialize();
            }
        }
        if ($admin->getMessageCount() == 0) {
            //display message
            geoAdmin::m('Settings saved.', geoAdmin::SUCCESS);
        }
        $this->jsonHeader();
        echo $this->encodeJSON(array('admin_messages' => $admin->getMessagesArray()));
    }

    public function update_payment_gateways()
    {
        $group = (int)$_GET['group'];
        geoPaymentGateway::setGroup($group);

        geoPaymentGateway::callUpdate('admin_update_payment_gateways_custom');//allow gateways to save any custom stuff

        if ((isset($_POST['enabled_gateways']) && is_array($_POST['enabled_gateways']))) {
            //save enabled and default settings.

            $types = array();
            $typeAlert = $default = false;
            $default_gateway = $_POST['default_gateway'];
            $all_gateways = geoPaymentGateway::getPaymentGatewayOfType('really_all');
            foreach ($all_gateways as $name => $gateway) {
                $enabled = (isset($_POST['enabled_gateways'][$name]) && $_POST['enabled_gateways'][$name]) ? 1 : 0;

                if (!is_object($gateway)) {
                    continue;
                }

                if (method_exists($gateway, 'admin_update_payment_gateways')) {
                    $result = $gateway->admin_update_payment_gateways();
                    if (!$result) {
                        continue;
                    }
                }

                //check for multiples of this gateway type
                $type = $gateway->getType();
                if ($enabled) {
                    if (in_array($type, $types) && !$typeAlert) {
                        //we've already read an enabled gateway of this type
                        //inform user, but do not block

                        geoAdmin::m('Warning: You have two or more gateways of type "' . $type . '" enabled. This may cause undesired and/or anomalous behavior. We recommend enabling only a single "' . $type . '" gateway.');
                        //only want to show one alert...
                        $typeAlert = true;
                    }
                    //add to array of enabled types
                    $types[] = $type;
                }

                //set enabled
                $gateway->setEnabled($enabled);

                if ($enabled && $default_gateway == $name) {
                    $gateway->setDefault(1);
                    $default = 1;
                } else {
                    $gateway->setDefault(0);
                }
                $gateway->serialize();
            }
            if (!$default && count($types)) {
                //auto select a default, use first one
                $gateway = geoPaymentGateway::getPaymentGateway($types[0]);
                if (is_object($gateway) && $gateway->getType() == $types[0]) {
                    //set this to default
                    $gateway->setDefault(1);
                    $gateway->serialize();
                }
            }
        }

        $admin = geoAdmin::getInstance();
        $admin->message('Setting Saved.');
        $this->jsonHeader();
        echo $this->encodeJSON(array('admin_messages' => $admin->getMessagesArray()));
    }

    private function _ajax_movePosition()
    {
        $position = $_GET['move'];
        $item = $_GET['item'];

        $gateway = geoPaymentGateway::getPaymentGateway($item);
        if (!is_object($gateway)) {
            geoAdmin::m('Error: Invalid gateway for ' . $item, geoAdmin::ERROR);
            return false;
        }
        $current_display_order = $gateway->getDisplayOrder();

        //go through existing gateways, and figure out what gateway is one up or
        //down from current one.
        $sorted_gateways =& geoPaymentGateway::getPaymentGateway('sorted');

        $new_order = $current_display_order + (($position == 'up') ? -1 : 1);
        $flop = true;
        $keys = array_keys($sorted_gateways[$new_order]);
        $max = geoPaymentGateway::getMaxDisplayOrder();
        while (
            $new_order > 1 && $new_order < $max &&
            !(isset($sorted_gateways[$new_order]) && is_object($sorted_gateways[$new_order][$keys[0]]) && method_exists($sorted_gateways[$new_order][$keys[0]], 'admin_display_payment_gateways'))
        ) {
            //keep incrementing until we get to a display order that:
            //1.  has a gateway associated with that display order.
            //2.  The gateway has the function defined so we know it will be showing up in the list.

            $new_order = $new_order + (($position == 'up') ? -1 : 1);
            //get keys for new order...
            $keys = array_keys($sorted_gateways[$new_order]);
        }
        if (!isset($sorted_gateways[$new_order])) {
            //could not find one to flop with, so just increment or decrement by one,
            //and set it to not flop, only flip...
            $new_order = $current_display_order + (($position == 'up') ? -1 : 1);
            $flop = false;
        }

        $gateway->setDisplayOrder($new_order);
        $gateway->save();
        if ($flop) {
            $keys = array_keys($sorted_gateways[$new_order]);
            $gateway2 =& $sorted_gateways[$new_order][$keys[0]];

            if (!is_object($gateway2)) {
                //oops!
                geoAdmin::m('Error, replacement gateway is not valid!  name: ' . geoString::specialChars(print_r($sorted_gateways[$new_order], 1)), geoAdmin::ERROR);
                return false;
            }
            //now set the order for both
            $gateway2->setDisplayOrder($current_display_order);
            $gateway2->save();
        } else {
            geoAdmin::m('no flop!', geoAdmin::ERROR);
        }

        return true;
    }
    private function _error($message)
    {
        $this->jsonHeader();
        echo $ajax->encodeJSON(array('error' => $message));
        return;
    }
}
