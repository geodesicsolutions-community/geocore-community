<?php
//outputfilter.add_basetag.php
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

function smarty_outputfilter_add_basetag ($output, Smarty_Internal_Template $smarty)
{
	if ($smarty->source->type == 'geo_tset' && $smarty->gType() == 'main_page') {
		//this is a geo tset, so do the filter
		return geoFilter::baseHref($output);
	}
	//not a main_page template, don't bother filtering
	return $output;
}
