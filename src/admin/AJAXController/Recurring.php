<?php

// DON'T FORGET THIS
if (class_exists('admin_AJAX') or die()) {
}

class ADMIN_AJAXController_Recurring extends admin_AJAX
{
    private function _returnError($msg, $data)
    {
        $data['error'] = $msg;
        echo $this->encodeJSON($data);
        return false;
    }

    /**
     * Return common stuff that might change when making changes to recurring billing.
     * @param geoRecurringBilling $recurring
     */
    private function _returnResults($recurring, $data, $return = false)
    {
        //populate the stuff that was likely to have changed
        $data['status'] = ucwords($recurring->getStatus());
        $data['paidUntil'] = geoDate::toString($recurring->getPaidUntil());
        $data['id'] = $recurring->getId();

        $extra = $recurring->get('refreshExtraInfo');
        if ($extra) {
            $data['extraInfo'] = '<br />' . $extra;
        }

        if ($return) {
            return $data;
        }
        echo $this->encodeJSON($data);
    }

    public function refresh()
    {
        $this->jsonHeader();
        $data = array ('action' => __function__);
        if (!$this->canUpdate('recurring_billing_details')) {
            return $this->_returnError('Not authorized.', $data);
        }

        $id = (int)$_GET['id'];
        if (!$id) {
            return $this->_returnError('Invalid ID specified.', $data);
        }
        $recurring = geoRecurringBilling::getRecurringBilling($id);
        if (!$recurring) {
            return $this->_returnError('Error retrieving recurring billing to refresh.', $data);
        }
        $recurring->updateStatus();
        $this->_returnResults($recurring, $data);
    }

    public function cancel()
    {
        $this->jsonHeader();
        $data = array ('action' => __function__);
        if (!$this->canUpdate('recurring_billing_details')) {
            return $this->_returnError('Not authorized.', $data);
        }

        $id = (int)$_GET['id'];
        if (!$id) {
            return $this->_returnError('Invalid ID specified.', $data);
        }
        $recurring = geoRecurringBilling::getRecurringBilling($id);
        if (!$recurring) {
            return $this->_returnError('Error retrieving recurring billing to cancel.', $data);
        }
        //TODO: Somehow let admin specify reason?
        if (!$recurring->cancel('Canceled by Admin')) {
            $message = $recurring->getUserMessage();
            if (!$message) {
                $message = 'Cancel payment gateway failed! Try refreshing the status
				to see if it is because the gateway is already canceled.';
            }
            return $this->_returnError($message, $data);
        }

        $this->_returnResults($recurring, $data);
    }

    public function batch()
    {
        $this->jsonHeader();
        $data = array ('action' => __function__);
        if (!$this->canUpdate('recurring_billing_details')) {
            return $this->_returnError('Not authorized.', $data);
        }

        $allowedActions = array ('updateStatus','cancel');
        $action = trim($_POST['batch_status']);

        if (!in_array($action, $allowedActions)) {
            return $this->_returnError('Unknown action, could not apply.', $data);
        }
        $var = ($action == 'cancel') ? 'Canceled by Admin' : true;
        $batch = (isset($_POST['batch_recurring'])) ? $_POST['batch_recurring'] : false;

        if (!$batch || !is_array($batch) || !count($batch)) {
            return $this->_returnError('Nothing was selected, please select at least one recurring billing to apply the selected action.', $data);
        }

        foreach ($batch as $id) {
            $id = (int)$id;
            if (!$id) {
                continue;
            }

            $recurring = geoRecurringBilling::getRecurringBilling($id);
            if (!$recurring) {
                //return $this->_returnError('Error retrieving recurring billing.', $data);
                continue;
            }

            $result = $recurring->$action($var);
            $data['recurrings'][] = $this->_returnResults($recurring, array('action' => 'batch', 'result' => $result), true);
        }
        echo $this->encodeJSON($data);
    }
}
