<?php
/**
 * @package ReusableOptions
 */
 

/**
 * A RO_ReorderableList:: is a container for ReusableOptions that can be
 * sorted by drag'n drop. The ordered list of option names inside the container
 * is stored in a hidden field.
 * Note: for this to work both scripts and styles of the option controller need to
 * be enqueued by setting the proper callbacks somewhere, e.g. through sth like this:
 *     add_action("admin_print_styles-$options", array(&$this->_oc, 'enqueueStyles'), 1);
 *     add_action("admin_print_scripts-$options", array(&$this->_oc, 'enqueueScripts'), 1);
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RO_ReorderableList extends CompositeOption
{
	
	/**
	 * Suffix of the field ids that are used to store ids of sorted elements.
	 * Note this one has to match the ones from reorder-option.js
	 * @var constant string
	 */
	var $FIELD_SUFFIX = 'Ordering';
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $label = '', 
					$textBefore = '', $textAfter = '')
	{	
		parent::__construct($name, '', $label, $textBefore, $textAfter);
		//set the default (English) values for strings that can be localized
		$this->_L10nStrings = array(
			"selectedListLabel" => "selected",
			"deselectedListLabel" => "deselected"
		);
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
			if(array_key_exists($this->_name, $storedOptions)){
				$this->setValue($storedOptions[$this->_name]);
			}
			parent::load($storedOptions);
		}elseif($storedOptions) //option was not stored in an associative array
			$this->setValue($storedOptions);
	}
	
	
	
	/**
	 * Stores own values in addition to selected childrens values in associative 
	 * array that can be stored in Wordpress database.
	 * 
	 * @return array $result		Array of options to store in database.
	 * @access public
	 */
	function store()
	{
		$result[$this->_name] = $this->getValue();
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			$result[$child->getName()] = $child->store();
		}
		return $result;
	}

	/**
	 * Returns the name of the hidden input field that contains the elements in
	 * their correct order.
	 * @return string
	 */
	function getFieldName(){
		return $this->_name.$this->FIELD_SUFFIX;
	}
	
	
	/**
	 * Setter for value field. Make sure children array is sorted accordingly.
	 * @param string		The new value of the option.
	 * @access public
	 */
	function setValue($value)
	{
		$this->_value = $value;
		if(!empty($value)){
			//reorder children based on ordering given by value
			$orderArray = array_unique(explode(',',$value));
			$orderedChildren = array();
			foreach($orderArray as $childName) {
				$temp  =& $this->getOptionByName($childName);
				if(!empty($temp))
				$orderedChildren[] =& $temp;
			}
			foreach ( array_keys($this->_children) as $index ) {
				$child =& $this->_children[$index];
				if(!in_array($child->getName(),$orderArray))
				$orderedChildren[] =& $child;
			}
			$this->_children =& $orderedChildren;
		}
	}
	
	
	/*function setValue($value)
	{
		if(!empty($value)){
			//remove the placeholder that distinguishes the <li> element id from 
			//the form id of the option
			//$val = preg_replace('/xLIx/','',$value);
			//array of ordered element names
			$orderArray = array_unique(explode(',',$value));
			//only go on if the sizes match
			$selectedChildren =& $this->_getSelectedChildren();
			//if(count($orderArray) == count($selectedChildren)){
				//create the new array of ordered children
				$orderedChildren = array();
				foreach($orderArray as $childName) {
					$temp  =& $this->getOptionByName($childName);
					if(!empty($temp))
						$orderedChildren[] =& $temp;
				}
				$deselectedChildren =& $this->_getSelectedChildren(false);
				
				//only if we have one-to-one relationship do we go on
				//if(count($orderedChildren) == $this->countChildren()){
//					$this->_children =& array_merge($orderedChildren,$deselectedChildren);
//					$this->_children = array();
//					 foreach ( array_keys($orderedChildren) as $index ) {
//					 $child =& $orderedChildren[$index];
//					 $this->_children[] = $child;
//					 }
					//var_dump($orderedChildren);
					$this->_value = $value;
				//}
			//}
		}//!empty
	}*/
	
	function accept(&$visitor)
	{
		//render selected first, deselected after 
		if(is_a($visitor, 'RenderOptionVisitor')){
			$this->_acceptSelected($visitor, true);
			$this->_acceptSelected($visitor, false);
		}else
			parent::accept($visitor);
	}
	
	/**
	 * First call visitBefore, then visit each of the children
	 * and finally visitAfter
	 *
	 * @param object OptionVisitor &$visitor	Reference to visiting visitor.
	 */
	function _acceptSelected(&$visitor, $selected)
	{
		
		//default method that may be defined by visitor to be executed independent of object type
		if(method_exists($visitor, 'visitDefaultBefore'))
			call_user_func_array(array(&$visitor, 'visitDefaultBefore'), array(&$this));
		
		//call the before method
		if( $methodBefore = $this->findVisitorMethodToCall('visit', 'Before', $visitor) )
			call_user_func_array(array(&$visitor, $methodBefore), array(&$this,$selected));
			
			
		//pass the visitor to all children
		foreach ( array_keys($this->_children) as $index ) {
			$child =& $this->_children[$index];
			if(in_array($child->getName(),array_unique(explode(',',$this->getValue()))) == $selected){
				//call the before child method
				if( $methodBeforeChild = $this->findVisitorMethodToCall('visit', 'BeforeChild', $visitor) )
					call_user_func_array(array(&$visitor, $methodBeforeChild), array(&$child, &$parent) );
					
				$child->accept($visitor);
					
				//call the after child method
				if( $methodAfterChild = $this->findVisitorMethodToCall('visit', 'AfterChild', $visitor) )
					call_user_func_array(array(&$visitor, $methodAfterChild), array(&$child, &$parent) );
					
			}
		}

		//call the 'after' method on the visitor
		if( $methodAfter = $this->findVisitorMethodToCall('visit', 'After', $visitor) )
			call_user_func_array(array(&$visitor, $methodAfter), array(&$this));
		
		//default method that may be defined by visitor to be executed independent of object type
		if(method_exists($visitor, 'visitDefaultAfter'))
			call_user_func_array(array(&$visitor, 'visitDefaultAfter'), array(&$this));
	}
	

	
	
	

}



?>
