<?php
//addons/adplotter/tags.php
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
## ##    7.5.2-18-ga8e9355
## 
##################################

# Adplotter Link addon

class addon_adplotter_tags extends addon_adplotter_info {
	
	public function aff_id()
	{
		$reg = geoAddon::getRegistry($this->name);
		return $reg->affiliate_code;		
	}
	
}