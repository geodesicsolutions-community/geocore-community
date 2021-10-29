<?php

//user_management_balance_transactions.php
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
## ##    7.6.3-116-gfc55207
##
##################################

class User_management_balance extends geoSite
{
    var $debug_balance = 0;
    var $transactionsPerPage = 10;
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function showInvoice($invoiceId, $printFriendly = false)
    {
        $invoiceId = intval($invoiceId);

        if (!$invoiceId) {
            //invalid invoice ID
            trigger_error('ERROR STATS INVOICE: Invalid invoice ID number.');
            return false;
        }


        $invoice = geoInvoice::getInvoice($invoiceId);
        if (!$invoice) {
            //either invoice is bad, and this is NOT an admin adjustment, or this is an admin
            //account balance adjustment but for another user.
            trigger_error('ERROR STATS INVOICE: Error getting invoice info for Invoice # ' . $invoiceId);
            return false;
        }
        //let invoice object render itself.
        $renderResult = $invoice->render($printFriendly ? false : true, $printFriendly);
        if (!$renderResult) {
            //something went wrong with displaying invoice.
            trigger_error('DEBUG INVOICE: render failed, not able to display invoice');
            return false;
        }

        if ($printFriendly) {
            //$renderResult contains the HTML to output as the entire print-friendly page
            header('Content-Type: text/html');
            echo $renderResult;
        } else {
            //show the invoice as a page in the main body
            $this->page_id = 183;
            $this->get_text();
            $this->display_page();
        }
        return true;
    }

    function show_past_balance_transactions($page = 0)
    {
        $page = intval($page);
        $userId = geoSession::getInstance()->getUserId();
        $user = geoUser::getUser($userId);
        if (!$user) {
            //problem getting user object
            return false;
        }
        geoPaymentGateway::setGroup($user->group_id);
        $gateway = geoPaymentGateway::getPaymentGateway('account_balance');
        if (!$gateway) {
            //problem getting plan item
            return false;
        }

        //make sure account balance is turned on
        if (!$gateway->getEnabled()) {
            //account balances turned off!
            return false;
        }

        $this->page_id = 184;
        $this->get_text();

        //get transactions that subtract from account balance for this user
        $sql = "SELECT count(`id`) as count FROM " . geoTables::transaction . " WHERE `gateway` = 'account_balance' AND `user`=$userId";
        //small change to query, and it will show for ALL recent transactions:
        //$sql = "SELECT count(`id`) as count FROM ".geoTables::transaction." WHERE `status`=1 AND `gateway`!='site_fee' AND `user`=$userId";
        $row = $this->db->GetRow($sql);
        $balance_count = $row['count'];

        $tpl_vars = array();
        $tpl_vars['balance_count'] = $balance_count;
        $tpl_vars['invoice_url'] = $this->db->get_site_setting('classifieds_url') . '?a=4&amp;b=18&amp;invoiceId=';
        if ($balance_count > 0) {
            $tpl_vars['account_balance'] = $user->account_balance;

            $sql = "SELECT `id` FROM " . geoTables::transaction . " WHERE `gateway` = 'account_balance' AND `user`=$userId ORDER BY `date` DESC";
            //small change to query, and it will show for ALL recent transactions:
            //$sql = "SELECT `id` FROM ".geoTables::transaction." WHERE `status`=1 AND `gateway`!='site_fee' AND `user`=$userId ORDER BY `date` DESC";

            //get which page of transactions to display
            if ($balance_count > $this->transactionsPerPage) {
                if ($page) {
                    //get this page (20) of balance transactions
                    $starting_point = (($page - 1) * $this->transactionsPerPage);
                    $sql .= " LIMIT $starting_point, $this->transactionsPerPage";
                } else {
                    $sql .= " LIMIT 0,$this->transactionsPerPage";
                }
            }

            $tIds = $this->db->GetAll($sql);
            $transactions = array();
            foreach ($tIds as $row) {
                $trans = geoTransaction::getTransaction($row['id']);
                if (!$trans) {
                    continue;
                }
                //amount is how much is coming FROM user TO site,
                //but needs to be other way, so make it - original
                $amount = ($trans->getAmount() * -1);
                $invoice = $trans->getInvoice();
                if ($invoice) {
                    $invoice = $invoice->getId();
                }
                $transactions[$trans->getId()] = array (
                    'id' => $trans->getId(),
                    'date' => date($this->configuration_data['entry_date_configuration'], $trans->getDate()), //pre-format it
                    'status' => $trans->getStatus(),
                    'amount' => $amount,
                    'adjustment' => $trans->get('adjustment'),
                    'invoice' => $invoice,
                    'orderId' => $trans->get('orderId'),
                    'adminNote' => $trans->get('admin_note')
                );

                //TODO: Add in some basic info about stuff in the order..
            }
            $tpl_vars['transactions'] = $transactions;
            $page = ($page) ? $page : 1;
            $tpl_vars['pagination'] = geoPagination::getHTML(ceil($balance_count / $this->transactionsPerPage), $page, $this->db->get_site_setting('classifieds_url') . '?a=4&amp;b=18&amp;c=', 'page_link');
        }
        geoView::getInstance()->setBodyVar($tpl_vars)
            ->setBodyTpl('balance_transactions/list_transactions.tpl', '', 'user_management');
        $this->display_page();
        return true;
    } //end of function show_past_balance_transactions
} // end of class User_management_balance
