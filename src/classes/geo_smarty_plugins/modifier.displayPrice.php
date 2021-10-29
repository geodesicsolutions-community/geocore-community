<?php
//modifier.displayPrice.php
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

function smarty_modifier_displayPrice ($value, $pre = false, $post = false, $type = null)
{
	return geoString::displayPrice($value, $pre, $post, $type);
}
