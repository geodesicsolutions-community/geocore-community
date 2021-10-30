<?php
//CJAX.class.php
/**
 * Holds the geoCJAX class which is a wrapper for the CJAX_FRAMEWORK, a little
 * 3rd party library we started using to make ajaxy type stuff a little easier.
 * 
 * @package System
 * @since Version 4.0.0
 */


# This is a wrapper class for the CJAX stuff, to make our lives easy
if (defined('IN_ADMIN') && !defined('JSDIR')) {
define('JSDIR','../classes/cjax/core/js/');
}
require_once CLASSES_DIR . 'cjax/cjax.php';

/**
 * Class that wraps the CJAX_FRAMEWORK, used to get the CJAX class.
 * 
 * @package System
 * @since Version 4.0.0
 */
abstract class geoCJAX extends CJAX_FRAMEWORK {
	/**
	 * Gets an instance of geoCJAX
	 *
	 * @return geoCJAX
	 */
	public static function getInstance()
	{
		return CJAX::initciate();
	}
}