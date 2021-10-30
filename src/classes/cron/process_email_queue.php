<?php

//sends e-mails that haven't been sent yet in the e-mail queue

$emailObj = geoEmail::getInstance();


return $emailObj->cron($this);
