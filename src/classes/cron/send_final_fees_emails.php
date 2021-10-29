<?php
//send_final_fees_emails.php
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
##    7.5.3-36-gea36ae7
## 
##################################

//This one sends e-mails to anyone with a negative account balance.

if (!defined('GEO_CRON_RUN')) {
	die('NO ACCESS');
}
if (!geoMaster::is('auctions')) {
	//disabled
	$this->log('Auctions disabled, nothing to do.');
	return true;
}
$pretty_log = (isset($_GET['running_now']) && $_GET['running_now']);

if ($pretty_log) echo 'Starting the send e-main notice to users with pending final fees.<br /><br />......<br />';

//check for users with pending final fees
$sql = "SELECT DISTINCT o.`buyer` FROM ".geoTables::order." as o, ".geoTables::order_item." as oi WHERE oi.order=o.id AND oi.type='auction_final_fees' AND o.status != 'active'";
$this->log('Running: '.$sql,__line__);
$rows = $this->db->GetAll($sql);
if ($rows === false) {
	$this->log('DB Error, sql: '.$sql." Error: ".$this->db->ErrorMsg(),__line__);
	if ($pretty_log) echo 'DB Error!  Cannot complete process.<br />';
	return false;
}

//$this->log('Users: <pre>'.print_r($rows,1).'</pre>',__line__);
if (count($rows) == 0) {
	//no negative users, our work here is done.
	$this->log('No users found with pending final fees.');
	if ($pretty_log) echo 'No users with pending final fees, so no e-mails sent.';
	
	return true;
}
$msgs = $this->db->get_text(true,10213);
$email = geoEmail::getInstance();

//go ahead and use the same template over and over, since can re-render with
//different template vars each time
$tpl = new geoTemplate(geoTemplate::SYSTEM, 'emails');

//subject is same for all
//Outstanding Auction Final Fees - Friendly Reminder
$subject = $msgs[502167];

if ($pretty_log) echo '<strong>Sending e-mail to users:</strong><br />';
foreach ($rows as $row) {
	$tpl_vars = array();
	$user_id = $tpl_vars['user_id'] = (int)$row['buyer'];
	$user = geoUser::getUser($user_id);
	if (!$user) {
		$this->log('Getting user info for ID '.$user_id.' failed, moving on to next person.',__line__);
		continue;
	}
	$to = geoString::fromDB($user->email);
	
	$tpl_vars['salutation'] = $user->getSalutation();
	//OK now get all the info...
	$sql = "SELECT DISTINCT o.`id` FROM ".geoTables::order." as o, ".geoTables::order_item." as oi WHERE oi.order=o.id AND oi.type='auction_final_fees' AND o.`buyer`=? AND o.status != 'active'";
	$fees_rows = $this->db->GetAll($sql, $user_id);
	
	$final_fees = array();
	$tpl_vars['fixed'] = $tpl_vars['percent'] = false;
	foreach ($fees_rows as $fee_row) {
		$order = geoOrder::getOrder($fee_row['id']);
		$items = $order->getItem('auction_final_fees');
		$allItems = $order->getItem();
		$moreInCart = (count($allItems) > count($items));
		foreach ($items as $item) {
			$fee = array();
			if (!$tpl_vars['fixed'] && $item->get('final_fee_fixed') > 0) {
				$tpl_vars['fixed'] = true;
			}
			if (!$tpl_vars['percent'] && $item->get('final_fee_percentage') > 0) {
				$tpl_vars['percent'] = true;
			}
			$listing = $item->get('listing');
			$listing = geoListing::getListing($listing);
			$pre = $post = '';
			if ($listing) {
				$fee['listing_url'] = $listing->getFullUrl();
				$pre = $listing->precurrency;
				$post = $listing->postcurrency;
			}
			
			$fee['total'] = geoString::displayPrice($item->getCost());
			$fee['fixed'] = geoString::displayPrice($item->get('final_fee_fixed'));
			$fee['percent'] = $item->get('final_fee_percentage').'%';
			$fee['final_bid'] = geoString::displayPrice($item->get('final_bid'), $pre, $post);
			$fee['conversion_rate'] = $item->get('conversion_rate');
			$fee['adjusted_bid'] = geoString::displayPrice($item->get('converted_final_bid'));
			$fee['bid_quantity'] = 1;
			if ($item->get('price_applies')=='item') {
				$fee['bid_quantity'] = $item->get('bid_quantity');
			}
			//echo "debug: <pre>".print_r($item,1);
			$final_fees[] = $fee;
		}
	}
	$tpl_vars['final_fees'] = $final_fees;
	$tpl->assign($tpl_vars);
	$content = $tpl->fetch('final_fees_due_reminder.tpl');
	
	//if ($pretty_log) echo '<br /><strong>E-mail body:</strong><div>'.$content.'</div><br />';
	
	$email->addQueue($to,$subject,$content,0,0,0,'text/html');
	if ($pretty_log) echo $user->username.'<br />';
}
$email->saveQueue();
if ($pretty_log) echo '<br /><strong>Finished!  Sent notices to '.count($rows).' users.';
$this->log('Finished sending notices, send to '.count($rows).' users.',__line__);
return true;
