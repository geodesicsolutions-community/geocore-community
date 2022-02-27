<?php
# Author :  Carlos Galindo
# Website: ComponentAjax.com
# Date :  2008 1:56:13 AM
# Copyright 2008

class extension  extends CoreEvents  {

	/**
	 * Makes sure the extension are registered
	 *
	 * @var boolean function
	 */
	private static $shutdown_register_extensions;
	/**
	 * Holds an array of loaded extensions
	 *
	 * @var array $extensions
	 */
	private static $extensions = array();

	/**
	 * Obtain the loaded extensions
	 *
	 * @return array $extensions
	 */
	public function get($value = null)
	{
		return self::$extensions;
	}

	function init($echo = false)
	{
		if(!self::$shutdown_register_extensions) {
			register_shutdown_function(array(__CLASS__,'register'));
			self::$shutdown_register_extensions = true;
		}
	}

	/**
	 * Add an extension to the extensions stack
	 *
	 * @param string $ext_name
	 * @param string $ext_name
	 * @param string $ext_path
	 * @return boolean
	 */
	public function add($f, $ext_path, $ext_name = 'plugins')
	{
		if(in_array($ext_name,self::$extensions)) return true;
		$extension = array('name' =>$f, 'base' =>$ext_path);
		self::$extensions[$ext_name] =  $extension;
	}

	/**
	 * Register extensions
	 *
	 */
	public static function register()
	{
		if(empty(self::$extensions)) return true;
		foreach (self::$extensions as $extension) {
			$xml[] = ("<extension_list>$extension</extension_list><base>{$extension['base']}</base>");
		}
		parent::xml(implode($xml));
	}
}
