<?php

//send_negative_account_balance_emails.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
##
##################################

//sends e-mails that haven't been sent yet in the e-mail queue

$emailObj = geoEmail::getInstance();


return $emailObj->cron($this);
