<?php
//adplotter/CLASSES.ajax.php

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