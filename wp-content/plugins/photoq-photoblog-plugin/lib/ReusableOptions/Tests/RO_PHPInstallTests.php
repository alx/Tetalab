<?php
/**
 * A suite of classes that allows to test the PHP configuration of the server.
 * @package ReusableOptions
 */
 

/**
 * The RO_SafeModeOffInputTest:: checks whether php safe mode is off.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RO_SafeModeOffInputTest extends InputTest
{
	
	/**
	 * Concrete implementation of the validate() method. This methods determines 
	 * whether input validation passes or not.
	 * @param object ReusableOption &$target 	The option to validate.
	 * @return String 	The error message created by this test.
	 * @access public
	 */
	function validate(&$target)
	{	
		if (ini_get('safe_mode')) {
    		$errMsg =  "Warning: You are running PHP with safe_mode on. This plugin requires safe_mode off for correct functioning.";
			$this->raiseErrorMessage($errMsg);
			return false;
		}
		return true;
	}
	
	
}


/**
 * The RO_GDAvailableInputTest:: checks whether gd image library is available.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RO_GDAvailableInputTest extends InputTest
{
	
	
	/**
	 * Concrete implementation of the validate() method. This methods determines 
	 * whether input validation passes or not.
	 * @param object ReusableOption &$target 	The option to validate.
	 * @return String 	The error message created by this test.
	 * @access public
	 */
	function validate(&$target)
	{	
		if (!function_exists("gd_info")) {
    		$errMsg =  "Warning: PHP GD library does not seem to be activated/installed on your server. GD is however required for this plugin to work properly.";
			$this->raiseErrorMessage($errMsg);
			return false;
		}
		return true;
	}
	
	
}


?>
