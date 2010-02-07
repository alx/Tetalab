<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The FileWritableInputTest:: checks whether input file/dir is writable (for php user).
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class FileWritableInputTest extends InputTest
{
	
	/**
	 * The file to be tested. If empty, target->getValue() is tested.
	 *
	 * @var string
	 */
	var $_file;
	
	
	/**
	 * PHP4 type constructor
	 */
	/*function FileWritableInputTest($file = '', $errMsgPrefix = '', $customErrMsg = '')
	{
		$this->__construct($file, $errMsgPrefix, $customErrMsg);
	}*/
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($errMsgCallback, $file = '', $errMsgPrefix = '', $customErrMsg = '')
	{
		parent::__construct($errMsgCallback, $errMsgPrefix, $customErrMsg);
		$this->_file = $file;
	}
	
	/**
	 * Concrete implementation of the validate() method. This methods determines 
	 * whether input validation passes or not.
	 * @param object ReusableOption &$target 	The option to validate.
	 * @return String 	The error message created by this test.
	 * @access public
	 */
	function validate(&$target)
	{	
		$filename = $this->_file ? $this->_file : $target->getValue();
		$filename = path_join( ABSPATH, $filename );
		//$filename = ABSPATH . $filename;
		if (!is_writable($filename)) {
    		$errMsg =  "The file ". $filename . " is not writable, check permissions.";
			$this->raiseErrorMessage($errMsg);
			return false;
		}
		return true;
	}
	
	
}

?>
