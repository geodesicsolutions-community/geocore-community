<?php
//FILE_LOCATION/FILE_NAME.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    67d0e9c
## 
##################################

# social connect

require_once ADDON_DIR.'social_connect/info.php';

class addon_social_connect_pages extends addon_social_connect_info
{
	public function merge_accounts ()
	{
		//this is used for internal user only
		return '<h1>Page used internally only.</h1><!-- Nice try though!  -->';
	}
}