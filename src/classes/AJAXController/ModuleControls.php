<?php

/**
* Handles "pagination" for browsing modules
*/

// DON'T FORGET THIS
if( class_exists( 'classes_AJAX' ) or die());

class CLASSES_AJAXController_ModuleControls extends classes_AJAX
{
	public function GetPage()
	{
		$tag = $_POST['tag'];
		$params = $_POST['params'];
		$params['results_page'] = (int)$_POST['results_page'];
		$params['is_ajax'] = 1;
		
		//hack to get a Smarty_Internal_Template, as expected by the moduleTag function
		require_once(CLASSES_DIR.'php5_classes/smarty/Smarty.class.php');
		$smarty = new Smarty_Internal_Template(geoTemplate::MODULE, new geoTemplate());
		
		echo DataAccess::getInstance()->moduleTag($tag, $params, $smarty);
	}
}
