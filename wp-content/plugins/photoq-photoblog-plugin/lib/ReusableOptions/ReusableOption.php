<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The ReusableOption:: is the parent class of all options. Options are implemented
 * with a Composite pattern. ReusableOption:: is the Component object of the pattern. 
 *
 * @author  M. Flury
 * @package ReusableOptions
 */
class ReusableOption extends ReusableOptionObject
{
	
	/**
	 * Name of the option.
	 * 
	 * @var string
	 * @access private
	 */
	var $_name;
	
	/**
	 * Value of the option.
	 * 
	 * @var string
	 * @access private
	 */
	var $_value;
	
	/**
	 * Old values are stored before updating so we can check whether any of them changed
	 *
	 * @access private
	 * @var array
	 */
	var $_oldValues;

	/**
	 * Indicates whether the option has changed in the last update.
	 *
	 * @access private
	 * @var boolean
	 */
	var $_hasChanged;
	
	/**
	 * Default value of the option.
	 * 
	 * @var string
	 * @access private
	 */
	var $_default;
	
	/**
	 * Label for the option.
	 * 
	 * @var string
	 * @access private
	 */
	var $_label;
	
	/**
	 * Text to display before the option.
	 * 
	 * @var string
	 * @access private
	 */
	var $_textBefore;
	
	/**
	 * Text to display after the option.
	 * 
	 * @var string
	 * @access private
	 */
	var $_textAfter;
	
	/**
	 * Strings that can be localized.
	 * @var unknown_type
	 */
	var $_L10nStrings;
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue, $label = '', $textBefore = '', $textAfter = '')
	{	
		$this->_name = $name; 
		$this->setDefaultValue($defaultValue);
		$this->setValue($this->_default);
		$this->setLabel($label);
		$this->setTextBefore($textBefore);
		$this->setTextAfter($textAfter);
		$this->_hasChanged = false;
	}
	
	/**
	 * Default implementation of the accept() method allowing traversal of 
	 * options by a visitor object. Calls the appropriate visit method on 
	 * the visitor object. We have two visit methods one that is called before
	 * the children are visited (so a non-composite option only defines this one)
	 * and one that is called after the childs are visited.
	 *
	 * @param object OptionVisitor &$visitor	Reference to visiting visitor.
	 */
	function accept(&$visitor)
	{
	
		//default method that may be defined by visitor to be executed independent of object type
		if(method_exists($visitor, 'visitDefaultBefore'))
			call_user_func_array(array(&$visitor, 'visitDefaultBefore'), array(&$this));
		
		//here we only call the "before" method as we don't have children that can be visited in between
		if( $methodBefore = $this->findVisitorMethodToCall('visit', 'Before', $visitor) )
			call_user_func_array(array(&$visitor, $methodBefore), array(&$this));
		
		//default method that may be defined by visitor to be executed independent of object type
		if(method_exists($visitor, 'visitDefaultAfter'))
			call_user_func_array(array(&$visitor, 'visitDefaultAfter'), array(&$this));
			
	}
	
	/**
	 * Check whether we find a value for this option in the array pulled from 
	 * the database. If so adopt this value.
	 * 
	 * @param array $storedOptions		Array pulled from database.
	 * @access public
	 */
	function load($storedOptions)
	{
		if(is_array($storedOptions)){
			if(array_key_exists($this->_name, $storedOptions))
				$this->setValue($storedOptions[$this->_name]);
		}	
		elseif($storedOptions) //option was not stored in an associative array
			$this->setValue($storedOptions);
	}
	
	/**
	 * Gets an array of options to be stored in the database. Recursively obtains
	 * options from children.
	 * 
	 * @return array string		Array of options to store in database.
	 * @access public
	 */
	function store()
	{
		$result = array();
		$result[$this->_name] = $this->_value;
		
		return $result;
	}
	
	/**
	 * Just returns an array with one entry containg a self reference.
	 * @return unknown_type
	 */
	function getReferenceArray()
	{
		return array($this->_name => &$this);
	}
	
	/**
	 * Store old values before updating such that we can later check whether any of them changed.
	 *
	 */
	function storeOldValues()
	{		
		$this->_oldValues = $this->_value;
	}
	
	/**
	 * Assess whether this option changed in the last update. Called from UpdateVisitor.
	 *
	 */
	function updateChangedStatus()
	{
				
		//needed because stuff from database can be differently attribute escaped than input data
		$a = (is_string($this->_value)) ? stripslashes($this->_value) : $this->_value;
		$b = (is_string($this->_oldValues)) ? stripslashes($this->_oldValues) : $this->_oldValues;
		
		$this->_hasChanged = ($a != $b);
		
		
	}
	
	/**
	 * Check whether this option changed in the last update.
	 *
	 * @return boolean
	 */
	function hasChanged()
	{
		return $this->_hasChanged;
	}
	
	/**
	 * Composite options should overwrite this to allow adding options to them.
	 * 
	 * @param object ReusableOption &$option  The option to be added to the composite.
	 * @return boolean	True if options could be added (composite), false otherwise.
	 * @access public
	 */
	function addChild(&$option)
	{
		return false;
	}
	
	function removeChild($name)
	{
		return false;
	}
	
	
	function countChildren(){
		return 0;
	}

	function &getChild($int)
	{
		$option = null;
		return $option;
	}

	/**
	 * Add an input valdiation test.	
	 * 
	 * @param object InputValidationTest &$test  The test to be added.
	 * @return boolean	True if test could be added, false otherwise.
	 * @access public
	 */
	function addTest(&$test)
	{	
		return false;
	}
	
	/**
	 * Default implementation of the validate() method allowing input validation of 
	 * options.
	 * 
	 * @return array string			The error messages created by the validation procedure.
	 * @access public
	 */
	function validate()
	{	
		return true;
	}
	
	
	
	
	/**
	 * Looks for an option with a given name.
	 *
	 * @param string $name				The name of the option we are looking for.
	 * @return object ReusableOption	The option if found, null otherwise.
	 * @access public
	 *
	 */
	function &getOptionByName($name){
		$option = null;
		if($this->_name == $name)
			$option = $this;
		
		return $option;
	}
	
	/**
	 * Getter for name field.
	 * @return string		The name of the option.
	 * @access public
	 */
	function getName()
	{
		return $this->_name;
	}
	
	/**
	 * Getter for name field.
	 * @return string		The name of the option.
	 * @access public
	 */
	function getPOSTName()
	{
		//when POSTing whitespace becomes _
		return preg_replace('/\s/', '_', $this->_name); 
	}
	
	/**
	 * Setter for name field.
	 * @param string $value		The name of the option.
	 * @access public
	 */
	function setOptionName($value)
	{
		$this->_name = $value;
	}
	
	/**
	 * Getter for value field.
	 * @return string		The value of the option.
	 * @access public
	 */
	function getValue()
	{
		return $this->_value;
	}
	
	/**
	 * Getter for the old values field
	 * @return mixed	The value of the option before the last update
	 */
	function getOldValue(){
		return $this->_oldValues;
	}
	
	
	/**
	 * Setter for value field.
	 * @param mixed		The new value of the option.
	 * @access public
	 */
	function setValue($value)
	{
		$this->_value = $value;
	}
	
	/**
	 * Setter for default field.
	 * @param mixed		The new default value of the option.
	 * @access public
	 */
	function setDefaultValue($value)
	{
		$this->_default = $value;
	}
	
	/**
	 * Getter for textBefore field.
	 * @return string		Text to show before the option.
	 * @access public
	 */
	function getTextBefore()
	{
		return $this->_textBefore;
	}
	
	
	/**
	 * Setter for textBefore field.
	 * @param string $value	Text to show before the option.
	 * @access public
	 */
	function setTextBefore($value)
	{
		$this->_textBefore = $value;
	}
	
	/**
	 * Getter for textAfter field.
	 * @return string		Text to show after the option.
	 * @access public
	 */
	function getTextAfter()
	{
		return $this->_textAfter;
	}
	
	/**
	 * Setter for textAfter field.
	 * @param string $value	Text to show after the option.
	 * @access public
	 */
	function setTextAfter($value)
	{
		$this->_textAfter = $value;
	}
	
	/**
	 * Getter for label field.
	 * @return string		Option label.
	 * @access public
	 */
	function getLabel()
	{
		return $this->_label;
	}
	
	/**
	 * Setter for label field.
	 * @param string $value		Option label.
	 * @access public
	 */
	function setLabel($value)
	{
		$this->_label = $value;
	}
	
	/**
	 * Works its way up the class hierarchy unless it find 
	 * a method on the supplied visitor that matches.
	 * @return String method to call or false if none found
	 */
	function findVisitorMethodToCall($prefix, $postfix, &$visitor)
	{
		//check whether we have a method for this class
		$currentClass = get_class($this);
		$methodName = $prefix . $currentClass . $postfix;
		if(method_exists($visitor, $methodName))
			return $methodName;
		
		//we don't so we work up our way through the hierarchy
		while( $currentClass = get_parent_class($currentClass) ){
			$methodName = $prefix . $currentClass . $postfix;
			if(method_exists($visitor, $methodName))
				return $methodName;
		}
		
		//there is no method to be called
		return false;
		
	}
	
	/**
	 * Translate strings that are given in a key value array
	 * @param $localizedStrings
	 * @return unknown_type
	 */
	function localizeStrings($localizedStrings)
	{
		foreach ($localizedStrings as $key => $value)
			$this->_L10nStrings[$key] = $value;	
	}
	
	/**
	 * Getter for localizeable strings
	 * @param $key
	 * @return unknown_type
	 */
	function getL10nString($key)
	{
		return $this->_L10nStrings[$key];
	}
	
	/**
	 * Registers form submit buttons if any with the OptionController specified.
	 * @param $oc
	 * @return unknown_type
	 */
	function registerSubmitButtons(&$oc){
		return true;
	}
	


}

?>