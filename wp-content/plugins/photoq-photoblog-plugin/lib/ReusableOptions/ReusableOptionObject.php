<?php

/**
 * Here we have a series of objects allowing helping us with cleaner
 * OOP especially in PHP4.
 */

/**
 * Base class for all ReusableOptions. Allows us to get PHP5-like 
 * constructors in PHP4.
 * @author flury
 *
 */
class ReusableOptionObject {
	/**
	 * PHP4-type constructor
	 * @return unknown_type
	 */
	function ReusableOptionObject() {
		$args = func_get_args();
		if (method_exists($this, '__destruct')) {
			register_shutdown_function(array(&$this, '__destruct'));
		}
		call_user_func_array(array(&$this, '__construct'), $args);
	}

	function __construct() {
	}
}

?>