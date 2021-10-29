<?php
//adplotter/CLASSES.ajax.php
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
## ##    7.6.2-7-g452b0b7
## 
##################################

//this is a remnant of what seemed like a good idea at the time, but turned out to not be so much so
//making it return nothing for now; may remove entirely later on

// DON'T FORGET THIS
if( class_exists( 'classes_AJAX' ) or die());

class addon_adplotter_CLASSES_ajax extends classes_AJAX {	
	
	public function processImageDispatch()
	{
		return '';
	}
	 
}