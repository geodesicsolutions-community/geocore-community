<?php

//Invoice.php
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
## ##    6.0.7-2-gc953682
##
##################################

// DON'T FORGET THIS
if (class_exists('admin_AJAX') or die()) {
}

class ADMIN_AJAXController_Invoice extends admin_AJAX
{
    /**
     * Gets the user data and stores it in this->user_data in array format.
     *
     * @return unknown
     */
    private function _getUserData($user_id)
    {
        $user_id = intval($user_id);

        if (!$user_id) {
            return false;
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT * FROM " . geoTables::userdata_table . " as ud, " . geoTables::user_groups_price_plans_table . " as ug WHERE 
					ud.id = ug.id AND ud.id = ? LIMIT 1";
        $result = $db->Execute($sql, array($user_id));
        if (!$result) {
            trigger_error('ERROR SQL: Retrieving user data, sql: ' . $sql . ' error: ' . $this->db->ErrorMsg());
            return false;
        }

        return $result->FetchRow();
    }

    public function getInvoice()
    {
        $admin = geoAdmin::getInstance();

        $invoice_id = intval($_GET['invoice_id']);
        if ($invoice_id) {
            $invoice = geoInvoice::getInvoice($invoice_id);
        }
        if (!$invoice_id || !is_object($invoice) || $invoice->getId() != $invoice_id) {
            echo 'Invalid Invoice ID, or no invoice specified, unable to retrieve invoice.';
            return;
        }

        $print = (isset($_GET['print'])) ? true : false;

        //let invoice object render itself.
        $renderResult = $invoice->render(true, $print);
        if (!$renderResult) {
            echo 'Failed to render the invoice.';
            return;
        }

        header('Content-Type: text/html');
        echo $renderResult;
    }
}
