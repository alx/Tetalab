<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The CompositeOption:: is the parent class of all options that can contain. 
 * other options. Composite object of the composite pattern.
 *
 * @author  M. Flury
 * @package ReusableOptions
 */
class CompositeOption extends ReusableOption
{
	
	/**
	 * Any nested suboptions an option might have if it is composed of several
	 * primitive options.
	 *
	 * @var array object ReusableOption
	 * @access private
	 */
	var $_children;
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue = 1, $label = '', 
					$textBefore = '', $textAfter = '')
	{	
		parent::__construct($name, $defaultValue, $label, $textBefore, $textAfter);
		$this->_children = array();
	}
	
	/**
	 * First call visitBefore, then visit each of the children
	 * and finally visitAfter
	 *
	 * @param object OptionVisitor &$visitor	Reference to visiting visitor.
	 */
	function accept(&$visitor)
	{

		//default method that may be defined by visitor to be executed independent of object type
		if(method_exists($visitor, 'visitDefaultBefore'))
			call_user_func_array(array(&$visitor, 'visitDefaultBefore'), array(&$this));
		
		//call the before method
		if( $methodBefore = $this->findVisitorMethodToCall('visit', 'Before', $visitor) )
			call_user_func_array(array(&$visitor, $methodBefore), array(&$this));
			
			
		//pass the visitor to all children
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			//call the before child method
			if( $methodBeforeChild = $this->findVisitorMethodToCall('visit', 'BeforeChild', $visitor) )
				call_user_func_array(array(&$visitor, $methodBeforeChild), array(&$child, &$this) );
			
			$child->accept($visitor);
			
			//call the after child method
			if( $methodAfterChild = $this->findVisitorMethodToCall('visit', 'AfterChild', $visitor) )
				call_user_func_array(array(&$visitor, $methodAfterChild), array(&$child, &$this) );
			
		}

		//call the 'after' method on the visitor
		if( $methodAfter = $this->findVisitorMethodToCall('visit', 'After', $visitor) )
			call_user_func_array(array(&$visitor, $methodAfter), array(&$this));
		
		//default method that may be defined by visitor to be executed independent of object type
		if(method_exists($visitor, 'visitDefaultAfter'))
			call_user_func_array(array(&$visitor, 'visitDefaultAfter'), array(&$this));
		
	}
	
	
	/**
	 * Check whether we find a value for this option in the array pulled from 
	 * the database. If so adopt this value. Pass the array on to all the children
	 * such that they can do the same.
	 * 
	 * @param array $storedOptions		Array pulled from database.
	 * @access public
	 */
	function load($storedOptions)
	{
		
		if(is_array($storedOptions)){
			//pass it on to all the children to give them a chance to load
			foreach ( array_keys($this->_children) as $index ) {
				$child =& $this->_children[$index];
				if($child->getName() == $this->_name) 
					$child->load($storedOptions);			
				elseif(array_key_exists($child->getName(), $storedOptions))
					$child->load($storedOptions[$child->getName()]);
			}
		}	
		
	}
	
	/**
	 * Gets an array of options to be stored in the database. Recursively obtains
	 * options from children.
	 * 
	 * @return array $result		Array of options to store in database.
	 * @access public
	 */
	function store()
	{
		$result = array();
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			$result[$child->getName()] = $child->store();
		}
		return $result;
	}
	
	function getReferenceArray()
	{
		$result = array($this->_name => &$this);
		
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			$result = array_merge($child->getReferenceArray(),$result);
		}
		return $result;
	}
	
	/**
	 * Registers form submit buttons if any with the OptionController specified.
	 * @param $oc
	 * @return unknown_type
	 */
	function registerSubmitButtons(&$oc){
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			$child->registerSubmitButtons($oc);
		}
	}
	
	
	/**
	 * Assess whether this option changed in the last update. Called from UpdateVisitor.
	 *
	 */
	function updateChangedStatus()
	{
		parent::updateChangedStatus();
		if(!$this->_hasChanged){
			//it has changed if any of its children have changed
			foreach ( array_keys($this->_children) as $index ) {
				$child =& $this->_children[$index];
				if($child->hasChanged()){
					$this->_hasChanged = true;
					break;
				}
			}
		}
	}
	
	
	/**
	 * Add an option to the composite.	
	 * 
	 * @param object ReusableOption &$option  The option to be added to the composite.
	 * @return boolean	True if options could be added (composite), false otherwise.
	 * @access public
	 */
	function addChild(&$option)
	{	
		$this->_children[] = $option;
		return true;
	}
	
	/**
	 * Remove an option from the composite.	
	 * 
	 * @param string $name  The option to be removed from the composite.
	 * @return boolean 		True if existed and removed, False otherwise.
	 * @access public
	 */
	function removeChild($name)
	{	
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			if($child->getName() == $name){
				unset($this->_children[$index]);
				//reindex
				$this->_children = array_values($this->_children);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Remove all options from the composite.	
	 * 
	 * @access public
	 */
	function removeChildren()
	{	
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			unset($this->_children[$index]);
		}
		//re-index the array
		$this->_children = array_values($this->_children);
	}
	
	/**
	 * Recursively traverses this option and all its descendants to find an 
	 * option with a given name
	 *
	 * @param string $name				The name of the option we are looking for.
	 * @return object ReusableOption	The option if found, null otherwise.
	 * @access public
	 *
	 */
	function &getOptionByName($name){
		$option = null;
		if($this->_name == $name)
			$option =& $this;
		else
			foreach ( array_keys($this->_children) as $index ) {
				$child =& $this->_children[$index];
				$option =& $child->getOptionByName($name);
				if($option){
					break;
				}
			}
		
		return $option;
	}
	
	function countChildren()
	{
		return count($this->_children);
	}
	
	function getChildrenNames()
	{	
		$result = array();
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			$result[] = $child->getName();
		}
		return $result;
	}
	
	function &getChild($int)
	{
		$option = null;
		if($int >= 0 && $int < $this->countChildren())
			$option =& $this->_children[$int];
		return $option;
	}
	
	/**
	 * Low level function that allows to query children of composite through a callback function.
	 * Names of children whose callback returns true are returned in an array.
	 * @param $hasAttributeCallback the callback function to be called.
	 * @return array children for which the callback returned true
	 */
	function &getChildrenWithAttribute($hasAttributeCallback = 'hasChanged'){
		$with = array();
		$numChildren = $this->countChildren();
		for ($i = 0; $i < $numChildren; $i++){
			$current =& $this->getChild($i);
			if(method_exists($current, $hasAttributeCallback)){
				if($current->$hasAttributeCallback())
					$with[] =& $current;
			}else
				die('PhotoQOptionController: method callback with name ' . $hasAttributeCallback . 'does not exist');
		}
		return $with;
	}
	
	/**
	 * Low level function that allows to query children of composite through a callback function.
	 * Names of children whose callback returns true are returned in an array.
	 * @param $hasAttributeCallback the callback function to be called.
	 * @return array names of image sizes for which the callback returned true
	 */
	function getChildrenNamesWithAttribute($hasAttributeCallback = 'hasChanged'){
		$with = array();
		foreach($this->getChildrenWithAttribute($hasAttributeCallback) as $current)
			$with[] = $current->getName();
			
		return $with;
	}
	
	/**
	 * Returns an array containing names of children that changed during
	 * last update.
	 * @return array
	 */
	function getChangedChildrenNames(){
		return $this->getChildrenNamesWithAttribute();
	}
	
	/**
	 * Validate the child options.
	 *
	 * @return array
	 */
	function validate()
	{
		$result = true;

		//foreach ($this->_children as $child){
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			if(!$child->validate())
			$result = false;
		}
		return $result;	
	}
	
		/*function validate2Change()
	{
		return $this->_validateInputs('validate2Change');
	}
	
	function _validateInputs($validationCallback = 'validate'){
		$result = true;
		
		//foreach ($this->_children as $child){
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			if(!$child->$validationCallback())
				$result = false;
		}
		return $result;
	}*/


}

?>