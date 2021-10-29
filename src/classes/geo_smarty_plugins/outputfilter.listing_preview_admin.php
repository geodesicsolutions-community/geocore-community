<?php
//outputfilter.strip_forms.php
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

//this smarty plugin is nice

function smarty_outputfilter_listing_preview_admin ($output, Smarty_Internal_Template $smarty)
{
	return geoFilter::baseHref($output, true);
}
