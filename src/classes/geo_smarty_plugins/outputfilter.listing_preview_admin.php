<?php
//outputfilter.strip_forms.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin is nice

function smarty_outputfilter_listing_preview_admin ($output, Smarty_Internal_Template $smarty)
{
	return geoFilter::baseHref($output, true);
}
