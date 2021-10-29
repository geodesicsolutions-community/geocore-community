<?php

//remove_old_invoices.php
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

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$this->log('Top of remove_old_invoices!', __line__);

//figure out how old are we talkin
$age = $this->db->get_site_setting('invoice_remove_age');

if (!$age) {
    $this->log('Removing old invoices is disabled (time is set to 0), not removing any old invoices.', __line__);
    return true;
}
//now find orders that are older than that
$age = geoUtil::time() - $age;

$allInvoices = $this->db->GetAll("SELECT `id` FROM " . geoTables::invoice . " WHERE (`due` > 0 AND `due` < $age) OR (`due` = 0 AND `created` != 0 AND `created` < $age)");
if ($allInvoices === false) {
    $this->log("Error!  Stopping rest of cron job.  DB error: " . $this->db->ErrorMsg());
    return true;
}
if (is_array($allInvoices) && count($allInvoices)) {
    //theres work to be done
    $this->log('Found ' . count($allInvoices) . ' old invoices to be removed.  Working on it.', __line__);
    foreach ($allInvoices as $row) {
        geoInvoice::remove($row['id']);
    }
    $this->log('Finished removing all invoices.', __line__);
} else {
    $this->log('No old invoices found.', __line__);
}

//now remove old transactions (to get rid of any transactions that may have been stranded some how)
$allTransactions = $this->db->GetAll("SELECT `id` FROM " . geoTables::transaction . " WHERE `date` < $age AND `invoice`='0'");
if (count($allTransactions)) {
    //theres work to be done
    $this->log('Found ' . count($allTransactions) . ' old ghost transactions to be removed.  Working on it.', __line__);
    foreach ($allTransactions as $row) {
        //transactions with invoice ID set to 0 are fair game to be removed automatically.
        geoTransaction::remove($row['id']);
    }
    $this->log('Finished removing all old ghost transactions.', __line__);
} else {
    $this->log('No old ghost transactions found to remove.', __line__);
}

//Do "orphaned transaction cleanup" - look for any old transactions where the invoice ID is set, but invoice is not valid, and set invoice to 0 so it will be removed next round.
$allTransactions = $this->db->GetAll("SELECT `id`, `invoice` FROM " . geoTables::transaction . " WHERE `date` < $age AND `invoice`!='0'");
if (count($allTransactions)) {
    //theres work to be done
    $this->log('Found ' . count($allTransactions) . ' old transactions that think they have invoices, going to check each one.  Working on it.', __line__);
    foreach ($allTransactions as $row) {
        $transaction = geoTransaction::getTransaction($row['id']);
        if (is_object($transaction) && !$transaction->getInvoice()) {
            //this item has no real order, so fix it
            $this->log('Transaction ID#' . $row['id'] . ' has invoice set to ' . $row['invoice'] . ' but that invoice could not be retrieved, so setting invoice to 0.', __line__);
            $transaction->setInvoice(0);
            $transaction->save();
            //now, the item will be removed next time this is called since the order is set to 0.
        } elseif (!is_object($transaction)) {
            //not a valid transaction some how??!?
        }
        unset($transaction);
    }
    $this->log('Finished checking for transactions with dead invoices.', __line__);
} else {
    $this->log('No old transactions with invoices found to check.', __line__);
}

return true;
