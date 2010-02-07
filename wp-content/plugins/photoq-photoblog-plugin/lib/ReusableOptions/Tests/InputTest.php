<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The InputTest:: is the parent class of all classes that can be used to validate the input of TextFields
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class InputTest extends ReusableOptionObject
{
	/**
	 * The function to call when a validation error is raised.
	 * @var unknown_type
	 */
	var $_errMsgCallback;
	
	/**
	 * A custom error message to be shown instead of the default one.
	 * @var String
	 * @access private
	 */
	var $_customErrMsg;
	
	/**
	 * This string is shown before the error message is.
	 * @var String
	 * @access private
	 */
	var $_errMsgPrefix;
		
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($errMsgCallback, $errMsgPrefix = '', $customErrMsg = '')
	{
		$this->_errMsgCallback = $errMsgCallback;
		$this->_errMsgPrefix = $errMsgPrefix;
		$this->_customErrMsg = $customErrMsg;
	}
	
	/**
	 * Abstract implementation of the validate() method. This methods determines 
	 * whether input validation passes or not.
	 * @param object ReusableOption &$target 	The option to validate.
	 * @return String 	The error message created by this test.
	 * @access public
	 */
	function validate(&$target)
	{	
		return true;//''; //no error -> test passed
	}
	
	/**
	 * Replace default error message with custom one, and append prefix if any
	 *
	 * @param string $errMsg	The unformated error message.
	 * @return string			The formated error message.
	 */
	function formatErrMsg($errMsg)
	{
		if($errMsg){
			//custom message of default one?
			$errMsg = $this->_customErrMsg ? $this->_customErrMsg : $errMsg;
			//prefix?
			$errMsg = $this->_errMsgPrefix . ' ' . $errMsg;
		}
		return $errMsg;
	}
	
	function raiseErrorMessage($errMsg){
		call_user_func_array($this->_errMsgCallback, array($this->formatErrMsg($errMsg)));
	}
	
	
}

?>
