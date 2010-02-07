<?php

/**
 * Here we have a series of objects allowing to help us with cleaner
 * OOP especially in PHP4.
 */

/**
 * Base class for all PhotoQ objects. Allows us to get PHP5-like 
 * constructors in PHP4.
 * @author flury
 *
 */
class PhotoQObject {
	
	
	/**
	 * PHP4-type constructor
	 * @return unknown_type
	 */
	function PhotoQObject() {
		$args = func_get_args();
		if (method_exists($this, '__destruct')) {
			register_shutdown_function(array(&$this, '__destruct'));
		}
		call_user_func_array(array(&$this, '__construct'), $args);
	}

	function __construct() {
		
	}
	
	
}



/**
 * Helper class to implement Singleton pattern. Instantiate objects like
 * $object =& PhotoQSingleton::getInstance('ClassName');
 *
 */
class PhotoQSingleton extends PhotoQObject
{
	/**
	 * implements the 'singleton' design pattern.
	 */
	function &getInstance ($class)
	{
		static $instances = array();  // array of instance names

		if (!array_key_exists($class, $instances)) {
			// instance does not exist, so create it
			$instances[$class] = new $class;
		}
		return $instances[$class];;
	}
} // singleton

?>