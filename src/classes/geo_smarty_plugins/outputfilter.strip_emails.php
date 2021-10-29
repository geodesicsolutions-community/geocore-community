<?php
//outputfilter.strip_emails.php
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

//this smarty plugin is for stripping e-mails from output

function smarty_outputfilter_strip_emails ($output, Smarty_Internal_Template $smarty)
{
	$replace_with_email = 'HIDDEN FOR DEMO';
	return preg_replace('/(^|[^a-zA-Z0-9]+)'.geoString::EMAIL_PREG_EXPR.'($|[^a-zA-Z])/','$1'.$replace_with_email.'$2',$output);
}
