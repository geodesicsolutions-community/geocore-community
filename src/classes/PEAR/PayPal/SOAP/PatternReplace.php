<?php
function underscore_replace($soap_data){
	$replacement = array('cpp_header_image' => 'cpp-header-image');
	foreach ($replacement as $key => $value) {
		$soap_data = str_replace($key,$value,$soap_data);
	}
	return $soap_data;
   }
   ?>
