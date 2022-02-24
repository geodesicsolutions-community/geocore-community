<?php


function AJAXErrorHandler($errno, $errstr, $errfile, $errline)
{
	/*
    The first parameter, errno, contains the level of the error raised, as an integer.
    The second parameter, errstr, contains the error message, as a string.
    The third parameter is optional, errfile, which contains the filename that the error was raised in, as a string.
    The fourth parameter is optional, errline, which contains the line number the error was raised at, as an integer.
    */
	ob_start();
	if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);//constant added on PHP 5.3
	if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);//constant added on PHP 5.3

    if( $errno & ( E_NOTICE | E_WARNING | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED ) ) {
    	ob_end_clean();
    	return true;
    }
    ob_end_clean();
    if( strpos( $errstr, 'DEBUG') === 0 ) {
		return true;
	}
	if( strpos( $errstr, 'ERROR') === 0 ) {
		return true;
	}
	if (!defined('IAMDEVELOPER') || defined('DEMO_MODE')) {
		//only show filename, prevent full path from showing as that is not good for security
		$start = (int)strrpos($errfile,'/');
		if (!$start) {
			$start = (int)strrpos($errfile,'\\');
		}
		if (!$start) {
			//failsafe, if no / or \ only show 10 chars
			$start = -10;
		}
		$errfile = '*****'.substr($errfile, $start);
	}
	if( $errno & ( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR ) ) {
		die( "<error>{$errstr} on line {$errline} of {$errfile}</error>" );
	} else if( $errno & E_USER_NOTICE) {
		echo "<user_error>{$errstr} on line {$errline} of {$errfile}</user_error>";
	} else {
		echo "<error>{$errno} {$errstr} on line {$errline} of {$errfile}</error>";
	}
}

class geoAjax
{
	public $directory = '';

	public function __construct ()
	{
		//currently, nothing to do in constructor...
	}

	/**
	 * Gets an instance of the geoAjax class.
	 * @return geoAjax
	 */
	public static function getInstance ()
	{
		return Singleton::getInstance('geoAjax');
	}

	public static function isAjax ()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}

	public function notAuthorized ()
	{
		trigger_error( 'Not authorized' );
		exit;
	}

	public function dispatch ($controller, $action, $data = '')
	{
		//clean input, remove any invalid chars for class name
		$controller = preg_replace('/[^_a-zA-Z0-9]*/','',$controller);

		if(strstr($controller,"addon_")){
 			// check for addons
 			$addon = true;
 			include(GEO_BASE_DIR.'get_common_vars.php');
 			$addonName = str_replace("addon_","",$controller);

 			if(!$addon->isEnabled($addonName)) {
 				trigger_error( $addonName . ' is not enabled or installed');
 				exit;
 			}

 			$filename = ADDON_DIR . $addonName . '/'.$this->directory.'.ajax.php';
 			$class = 'addon_' . $addonName . '_'.$this->directory.'_ajax';
 		} else {
			//account for changed admin, addon, or classes dir
			if ($this->directory == 'ADMIN'){
				//allows admin directory name to change without need of changing PHP files.
				$dir_start = ADMIN_DIR;
			} else if (strpos($this->directory,'ADDON_') === 0){
				//if the directory starts with ADDON_
				$dir_start = str_replace('ADDON_',ADDON_DIR,$this->directory) . '/';
			} else if ($this->directory == 'CLASSES') {
				//allows classes directory name to change without need of changing PHP files.
				$dir_start = CLASSES_DIR;
			} else {
				//not a special case, this is located in a directory that cannot have
				//a changed name.
				$dir_start = GEO_BASE_DIR.$this->directory.'/';
			}

			$filename = $dir_start.'AJAXController/' . $controller . '.php';


			$class = $this->directory.'_AJAXController_' . $controller;
 		}

		if( !$controller || !$action ) {
			trigger_error( 'Not enough data to perform request' );
			exit;
		}

		if( !file_exists($filename) ) {
			trigger_error( 'Cannot find ' . $filename );
			exit;
		}

		require_once $filename;

		if( !class_exists($class) ) {
			trigger_error( 'Class ' . $class . ' does not exist' );
			exit;
		}
		$methods = get_class_methods($class);
		if( !in_array( strtolower($action), $methods) && !in_array( $action, $methods ) ) {
			trigger_error( $action . ' does not exist in ' . $class );
			exit;
		}

		$ajax = new $class();
		echo $ajax->$action( $data );
	}

	/**
	 * Use this to encode anything you need to JSON.  It will handle converting
	 * to UTF-8 if that is necessary, depending on if config is set to use UTF-8
	 * or not.
	 *
	 * @param mixed $data
	 */
	public function encodeJSON ($data)
	{
		$charset = geoString::getCharsetTo();
		if (!$charset) {
			$charset = geoString::getCharset();
		}
		if ($charset && $charset != 'UTF-8') {
			//we need to convert this thing to UTF-8 or it won't work!
			$data = geoArrayTools::convertCharset($data, $charset, 'UTF-8');
		}

		return json_encode($data);
	}

	/**
	 * Mostly here for completeness sake, this would decode the data from JSON
	 * to PHP.
	 *
	 * @param string $data
	 */
	public function decodeJSON ($data)
	{
		return json_decode($data);
	}

	public function jsonHeader ()
	{
		//Charset for JSON must always be UTF-8
		$charset = 'UTF-8';

		//set header for json content, usually used by prototype.
		header('Content-Type: application/json; charset='.$charset);
	}
}
//For backwards compatibility, remove once all locations have been updated to use Ajax.
class Ajax extends geoAjax {}

