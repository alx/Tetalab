<?php 
/**
 * Here we group everything related to PhotoQ error handling
 */


//define PhotoQ error codes
define('PHOTOQ_INFO_MSG', 					1); //general information/feedback to user, not really an error
define('PHOTOQ_PHOTO_NOT_FOUND', 			10);
define('PHOTOQ_QUEUED_PHOTO_NOT_FOUND', 	11);
define('PHOTOQ_FILE_UPLOAD_FAILED', 		12);
define('PHOTOQ_SIZE_NOT_DEFINED',			13); //image size with given name is not defined (freeform mode)
define('PHOTOQ_COULD_NOT_MOVE',				14);
define('PHOTOQ_POST_NOT_FOUND',				20);//post with id 'id' not found
define('PHOTOQ_BATCH_REGISTER_FAILED', 		30);
define('PHOTOQ_FIELD_EXISTS',				40); //custom field with same name as the one to be added already exists
define('PHOTOQ_VIEW_EXISTS',				50); //custom field with same name as the one to be added already exists
define('PHOTOQ_ERROR_VALIDATION', 			100); //options did not validate properly
define('PHOTOQ_EXPCOMP_ENTRY_EXISTS',		110); //element of an expandable composite option already exists
define('PHOTOQ_IMGSIZE_DEL_FAILED',			112); //could not remove directory of image size
define('PHOTOQ_XML_IMPORT_FAILED',			200); //problem importing xml file
define('PHOTOQ_XML_IMPORT_MISSING_ELEMENT',	201); //required exp comp element that was not there
define('PHOTOQ_XML_DENIED_OPTION',			202); //option was not in list of allowed options

/**
 * This class helps ErrorStack in handling errors
 * @author manu
 *
 */
class PhotoQErrorHandler extends PhotoQObject
{
	
	var $_defaultMsgCallback;
	
	function __construct($defaultMsgCallback){
		$this->_defaultMsgCallback = $defaultMsgCallback;
	}
	
    function errorMessageCallback(&$stack, $err)
    {
 		$message = '<li>';
 		$message .= call_user_func_array($this->_defaultMsgCallback, array(&$stack, $err));
		$message .= '</li>';
		return $message;
    }
    
    /**
     * Called statically. Shows all errors that accumulated on the stack
     * @return unknown_type
     */
    function showAllErrors(&$stack, $print = true){
    	$msg = PhotoQErrorHandler::showAllErrorsExcept($stack, array(), false);
    	if($print) echo $msg;
  		return $msg;
    }
    
    /**
     * Similar to showAllErrors but let's us exclude the error codes given
     * in the array exclude
     * @param $stack
     * @param $exclude array	Error codes to exclude
     * @param $print
     * @return unknown_type
     */
	function showAllErrorsExcept(&$stack, $exclude = array(), $print = true){
    	$msg = PhotoQErrorHandler::showErrorsByLevel($stack, 'info', 'updated fade', $exclude, false);
    	$msg .= PhotoQErrorHandler::showErrorsByLevel($stack, 'error', 'error', $exclude, false);
    	
    	if($print) echo $msg;
  		
  		return $msg;
    }
    
    /**
     * Show only errors of a given level, excluding error codes given in other parameter
     * @param $stack
     * @param $level
     * @param $cssClass
     * @param $exclude
     * @param $print
     * @return unknown_type
     */
	function showErrorsByLevel(&$stack, $level = 'error', $cssClass = 'error', $exclude = array(), $print = true){
    	//show errors if any
    	$msg = '';
    	if ($stack->hasErrors($level)) {
    		$errMsgs = '';
    		//with purging it doesn't work -> bug in errorStack under PHP5
    		foreach ($stack->getErrors(false, $level) as $err){
    			if(!in_array($err['code'],$exclude))
    				$errMsgs .= $err['message'];
    		}
    		if($errMsgs)
    			$msg .= '<div class="'.$cssClass.'"><ul>'.$errMsgs.'</ul></div>';
    	}
		    	
  		if($print) echo $msg;
  		
  		return $msg;
    }
    
    /**
     * Push callback used to disable PHOTOQ_PHOTO_NOT_FOUND error types.
     * @param $err
     * @return unknown_type
     */
    function mutePHOTOQ_PHOTO_NOT_FOUND($err){
    	return PhotoQErrorHandler::muteError($err, PHOTOQ_PHOTO_NOT_FOUND);
    }
    
	/**
     * Push callback used to disable PHOTOQ_VIEW_EXISTS error types.
     * @param $err
     * @return unknown_type
     */
    function mutePHOTOQ_VIEW_EXISTS($err){ 	
    	return PhotoQErrorHandler::muteError($err, PHOTOQ_VIEW_EXISTS);
    }
    
	/**
     * Push callback used to disable PHOTOQ_FIELD_EXISTS error types.
     * @param $err
     * @return unknown_type
     */
    function mutePHOTOQ_FIELD_EXISTS($err){ 	
    	return PhotoQErrorHandler::muteError($err, PHOTOQ_FIELD_EXISTS);
    }
    
    function muteError($err, $errCode){
    	if ($err['code'] == $errCode){
    		return PEAR_ERRORSTACK_IGNORE;
    	}
    }

}



//setup the error stack that is used for error handling in PhotoQ
$photoqErrStack = &PEAR_ErrorStack::singleton('PhotoQ');
$photoqErrHandler = new PhotoQErrorHandler($photoqErrStack->getMessageCallback('PhotoQ'));
$photoqErrStack->setMessageCallback(array(&$photoqErrHandler, 'errorMessageCallback'));
//these are the default messages for above defined errors
$photoQMsgs = array(
    PHOTOQ_PHOTO_NOT_FOUND 				=> __('Post "%title%": The photo "%imgname%" could not be found at "%path%".', 'PhotoQ'),
    PHOTOQ_QUEUED_PHOTO_NOT_FOUND 		=> __('Queued post "%title%": The photo "%imgname%" could not be found at "%path%".', 'PhotoQ'),
    PHOTOQ_POST_NOT_FOUND 				=> __('The post with ID "%id%" does not seem to exist.', 'PhotoQ'),
    PHOTOQ_FILE_UPLOAD_FAILED			=> __('The file upload failed with the following error: %errMsg%.', 'PhotoQ'),
    PHOTOQ_SIZE_NOT_DEFINED				=> __('The image size "%sizename%" is not defined.', 'PhotoQ'),
    PHOTOQ_COULD_NOT_MOVE				=> __('Unable to move "%source%" to "%dest%".', 'PhotoQ'),
    PHOTOQ_BATCH_REGISTER_FAILED 		=> __('Error when registering batch process: No photos updated.', 'PhotoQ'),
    PHOTOQ_EXPCOMP_ENTRY_EXISTS			=> __('Please choose another name, an entry with this name already exists.', 'PhotoQ'),
    PHOTOQ_FIELD_EXISTS					=> __('Please choose another name, a meta field with name "%fieldname%" already exists.', 'PhotoQ'),
    PHOTOQ_VIEW_EXISTS					=> __('Please choose another name, a view with name "%viewname%" already exists.', 'PhotoQ'),
    PHOTOQ_IMGSIZE_DEL_FAILED 			=> __('Could not remove image size. The required directories in %imgDir% could not be removed. Please check your settings.','PhotoQ'),
    PHOTOQ_XML_IMPORT_FAILED			=> __('The XML file "%filename%" could not be imported', 'PhotoQ'),
    PHOTOQ_XML_IMPORT_MISSING_ELEMENT	=> __('XML import error: Element %element% was missing in %compName% option.','PhotoQ'),
    PHOTOQ_XML_DENIED_OPTION			=> __('The option %optionname% that you tried to import is not allowed.', 'PhotoQ'),
    
);
$photoqErrStack->setErrorMessageTemplate($photoQMsgs);









// Here come the classes for the older error handling. They are still needed
// until everything is completely refactored		
		
/**
 * No exceptions in PHP4, so let's try to have at least 
 * some level of error handling through this class
 *
 */
class PhotoQStatusMessage extends PhotoQObject
{

	/**
	 * The string message.
	 *
	 * @var string
	 * @access private
	 */
	var $_msg;
	
	/**
	 * PHP4 type constructor
	 */
	/*function PhotoQStatusMessage($msg = '')
	{
		$this->__construct($msg);
	}*/


	/**
	 * PHP5 type constructor
	 */
	function __construct($msg = '')
	{
		$this->_msg = $msg;	
	}
	
	/**
	 * Whether the message denotes an error or not.
	 *
	 * @access public
	 * @return boolean
	 */
	function isError()
	{
		return false;		
	}
	
	/**
	 * Print the message to screen.
	 *
	 * @access public
	 */
	function show()
	{
 		echo '<div class="updated fade">';
		echo "<p>$this->_msg</p>";
		echo '</div>';
	}
	
	/**
	 * Getter for the message string.
	 *
	 * @access public
	 * @return string
	 */
	function getMsg()
	{
		return $this->_msg;
	}
	
}

/**
 * This message can be returned if there was an error.
 *
 */
class PhotoQErrorMessage extends PhotoQStatusMessage
{
	function isError()
	{
		return true;		
	}

	function show()
	{
		echo '<div class="error">';
		echo $this->getMsg();
		echo '</div>';
	}
}

?>