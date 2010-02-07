<?php
/**
 * @package ReusableOptions
 */
 

/**
 * A SelectionList:: is a container for SelectableOptions. Several of which
 * can be selected at a time, depending on whether we pass in one or more
 * values when updating. 
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class SelectionList extends CompositeOption
{
	
	/**
	 * Add an option to the composite.	
	 * 
	 * @param object ReusableOption &$option  The option to be added to the composite.
	 * @return boolean	True if options could be added (composite), false otherwise.
	 * @access public
	 */
	function addChild(&$option)
	{
		if(is_a($option, 'SelectableOption') || is_a($option, 'SelectableCompositeOption')){
			if( (is_array($this->getValue()) && in_array($option->getValue(), $this->getValue())) ||
			$this->getValue() == $option->getValue()){
				$option->select();
			}
				
			return parent::addChild($option);
		}
		
		return false;
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
		$result = array();
		if($selectedChildren =& $this->_getSelectedChildren()){
			$result[$this->_name] = $this->getValue();
			foreach ( array_keys($selectedChildren) as $index ) {
				$selected =& $selectedChildren[$index];
				$result = array_merge($selected->store(), $result);
			}
		}elseif(!$this->countChildren()){ //no children yet, keep the preassigned value
			$result[$this->_name] = $this->getValue();
		}else{
			$this->setValue('');//nothing selected
			$result[$this->_name] = $this->getValue();
		}
		return $result;
	}
	
	/**
	 * Returns the Selected Option. 
	 *
	 * @return object SelectableOption		The selected SelectableOption
	 * @access private
	 */
	function &_getSelectedChildren($flag = true){
		$selected = array();
		$numChildren = $this->countChildren();
		for ($i = 0; $i < $numChildren; $i++){
			$current =& $this->getChild($i);
			if((is_a($current, 'SelectableOption')
				|| is_a($current, 'SelectableCompositeOption')) && $current->isSelected() == $flag){
				$selected[] =& $current;
				
			}
		}
		return $selected; 
	}
	
	/**
	 * Deselects the currently selected child options.
	 *
	 * @access private
	 */
	function _deselectSelected()
	{
		if($selectedChildren =& $this->_getSelectedChildren())
			foreach ( array_keys($selectedChildren) as $index ) {
				$selected =& $selectedChildren[$index];
				$selected->deselect();
		}
	}
	
	/**
	 * Selects the child option with the corresponding values.
	 *
	 * @param string $value		The value of the option to be selected.
	 * @access private
	 */
	function _selectByValue($values)
	{
		$numChildren = $this->countChildren();
		for ($i = 0; $i < $numChildren; $i++){
			$current =& $this->getChild($i);
			if(($current->getValue() && is_a($current, 'SelectableOption') 
				|| is_a($current, 'SelectableCompositeOption')) && 
				((is_array($values) && in_array($current->getValue(),$values)) ||
				$current->getValue() == $values )
			){
				$current->select();
			}
		}
	}
	
	/**
	 * Setter for value field.
	 * @param string		The new value of the option.
	 * @access public
	 */
	function setValue($value)
	{
		$this->_value = $value;
		$this->_deselectSelected();
		if($value)
			$this->_selectByValue($value);
	}


	
	
	
	

}

?>
