<?php
if(!class_exists('singleton')){
	class singleton {
		static $instances = array();  // array of instance names
		
	    function getInstance ($class){
	    // implements the 'singleton' design pattern.	
	        if (!array_key_exists($class, self::$instances)) {
	            // instance does not exist, so create it
	            self::$instances[$class] = new $class;
	        }
	        $instance =& self::$instances[$class];
	        return $instance;   
	    }   
	}
}
