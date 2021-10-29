<?php
//modifier.phoneFormat.php
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
## ##    7.4beta3-22-g5653c10
## 
##################################

//this smarty plugin is for phoneFormat modifier

function smarty_modifier_phoneFormat ($value)
{
	return geoNumber::phoneFormat($value);
}
