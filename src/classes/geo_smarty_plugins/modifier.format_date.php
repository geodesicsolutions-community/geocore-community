<?php
//modifier.format_date.php
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

//this smarty plugin is for displayPrice modifier

function smarty_modifier_format_date ($date, $format = null)
{
	if ($format === null) {
		$format = DataAccess::getInstance()->get_site_setting('entry_date_configuration');
	}
	return date($format,$date);
}
