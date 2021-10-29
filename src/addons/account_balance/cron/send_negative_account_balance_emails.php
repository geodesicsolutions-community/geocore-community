<?php

//send_negative_account_balance_emails.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.4.0-34-gae530f5
##
##################################

//This one sends e-mails to anyone with a negative account balance.

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$pretty_log = (isset($_GET['running_now']) && $_GET['running_now']);

if ($pretty_log) {
    echo 'Starting the send e-main notice to users with negative account balance.<br /><br />......<br />';
}

//check for users with negative balance
$sql = "SELECT `id` FROM " . geoTables::userdata_table . " WHERE `account_balance` < 0 AND `id` > 1";
$this->log('Running: ' . $sql, __line__);
$rows = $this->db->GetAll($sql);
if ($rows === false) {
    $this->log('DB Error, sql: ' . $sql . " Error: " . $this->db->ErrorMsg(), __line__);
    if ($pretty_log) {
        echo 'DB Error!  Cannot complete process.<br />';
    }
    return false;
}

//$this->log('Users: <pre>'.print_r($rows,1).'</pre>',__line__);
if (count($rows) == 0) {
    //no negative users, our work here is done.
    $this->log('No users found with a negative account balance.');
    if ($pretty_log) {
        echo 'No users with negative balance found, so no e-mails sent.';
    }

    return true;
}
$msgs = $this->db->get_text(true, 177);
$email = geoEmail::getInstance();

if ($pretty_log) {
    echo '<strong>Sending e-mail to users:</strong><br />';
}

foreach ($rows as $user_id) {
    $user = geoUser::getUser($user_id['id']);
    if (!$user) {
        $this->log('Getting user info for ID ' . $user_id['id'] . ' failed, moving on to next person.', __line__);
        continue;
    }
    $to = geoString::fromDB($user->email);
    //Outstanding account balance - friendly reminder
    $subject = $msgs[500447];
    $tpl = new geoTemplate('system', 'emails');
    $tpl->assign('salutation', $user->getSalutation());
    $tpl->assign('add_balance_url', $this->db->get_site_setting('classifieds_url') . '?a=cart&amp;action=new&amp;main_type=account_balance');
    $tpl->assign('balance', $user->account_balance);
    $tpl->assign('balance_history_url', $this->db->get_site_setting('classifieds_url') . '?a=4&amp;b=18');
    $tpl->assign('balance_negative_date', $user->date_balance_negative);
    $tpl->assign('short_date_format', $this->db->get_site_setting('date_field_format_short'));
    $content = $tpl->fetch('negative_balance_reminder.tpl');
    //$this->log('E-mail sent:</pre><div style="border: 1px dashed black; ">'.$content.'</div><pre>', __line__);
    $email->addQueue($to, $subject, $content, 0, 0, 0, 'text/html');
    if ($pretty_log) {
        echo $user->username . '<br />';
    }
}
$email->saveQueue();
if ($pretty_log) {
    echo '<br /><strong>Finished!  Sent notices to ' . count($rows) . ' users.';
}
$this->log('Finished sending notices, send to ' . count($rows) . ' users.', __line__);
return true;
