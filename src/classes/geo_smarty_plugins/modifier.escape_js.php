<?php
//modifier.escape_js.php
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

//this is custom smarty plugin modifier

function smarty_modifier_escape_js ($string)
{
	$string = preg_replace('/[\r\n\t]+/', ' ', $string);
	$string = addslashes($string);
	return $string;
}
