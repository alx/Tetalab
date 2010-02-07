<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The RO_WordPressVersionInputTest:: checks whether the WordPress version number is ok.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RO_WordPressVersionInputTest extends InputTest
{
	
	/**
	 * Lower bound on WordPress version.
	 *
	 * @var string
	 * @access private
	 */
	var $_lower;
	
	/**
	 * Upper bound on WordPress version.
	 *
	 * @var string
	 * @access private
	 */
	var $_upper;
	
	
	/**
	 * PHP4 type constructor
	 */
	/*function RO_WordPressVersionInputTest($lowerBound, $upperBound = '', $errMsgPrefix = '', $customErrMsg = '')
	{
		$this->__construct($lowerBound, $upperBound, $errMsgPrefix, $customErrMsg);
	}*/
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($errMsgCallback, $lowerBound, $upperBound = '', $errMsgPrefix = '', $customErrMsg = '')
	{
		parent::__construct($errMsgCallback, $errMsgPrefix, $customErrMsg);
		$this->_lower = $lowerBound;	
		$this->_upper = $upperBound;
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
		if ($this->_lower && $this->_compareVersions(get_bloginfo('version'), $this->_lower) == -1) {
    		$errMsg .=  "Warning: The minimum WordPress version required for this plugin is $this->_lower. You are running WordPress ".get_bloginfo('version').".";
			$this->raiseErrorMessage($errMsg);
			return false;
		}
		if ($this->_upper && $this->_compareVersions(get_bloginfo('version'), $this->_upper) == 1) {
    		$errMsg .=  "Warning: This plugin has been tested with Wordpress versions up to $this->_upper. You are running WordPress ".get_bloginfo('version').".";
			$this->raiseErrorMessage($errMsg);
			return false;
		}
		return true;
	}
	
	/**
	 * Compares two version strings.
	 *
	 * @param string $va	stringA
	 * @param string $vb	stringB
	 * @return integer		-1 if stringA < stringB, 1 if stringA > stringB, 0 if the same
	 */
	function _compareVersions($va, $vb)
	{
		//convert to arrays for easier handling
		$vaArr = $this->_convertVersionString2VersionArray($va);
		$vbArr = $this->_convertVersionString2VersionArray($vb);
		//make both same length by paddin zeros to shorter one	
		$vaArr = array_pad($vaArr, max(count($vaArr),count($vbArr)), 0);
		$vbArr = array_pad($vbArr, max(count($vaArr),count($vbArr)), 0);
		//compare
		for ($i = 0; $i<count($vaArr); $i++){
			if($vaArr[$i]<$vbArr[$i])
				return -1;
			elseif($vaArr[$i]>$vbArr[$i])
				return 1;
		}
		return 0;
	}
	
	/**
	 * Converts a version string like 2.4.5 to an array (2,4,5). Chops of
	 * any non digit characters at the end: 2.4.5-bleeding becomes (2,4,5) as well.
	 *
	 * @param string $vs	The version number as a string.
	 * @return array		The version number as an array.
	 */
	function _convertVersionString2VersionArray($vs)
	{
		$va = explode('.', $vs);
		$lastIndex = count($va)-1;
		//remove any non digits from last element of array
		$va[$lastIndex] = preg_replace('/\D/'  ,''  ,$va[$lastIndex]);
		return $va;
	}

	
}

?>
