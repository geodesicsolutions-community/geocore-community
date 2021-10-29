<?php

//module_total_live_users.php
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
## ##    7.5.3-36-gea36ae7
##
##################################


$sql = "SELECT count(*) counter FROM geodesic_sessions";
$logged_result = $this->GetRow($sql);
$count = (isset($logged_result['counter'])) ? $logged_result['counter'] : 0;

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], 'live_users', $count);
