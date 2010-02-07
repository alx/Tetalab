<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The SelectableOption:: class represents a single selectable option.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class SelectableCompositeOption extends CompositeOption
{
	/**
	 * State of the radiobutton
	 * @var boolean
	 * @access private
	 */
	var $_selected;
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue, $label = '', 
					$textBefore = '', $textAfter = '')
	{
		parent::__construct($name, $defaultValue, $label, $textBefore, $textAfter);
		$this->deselect();
	}
	
	
	/**
	 * Select this option.
	 *
	 * @access public
	 */
	function select()
	{
		$this->_selected = true;
	}
	
	/**
	 * Deselect this option.
	 *
	 * @access public
	 */
	
	function deselect()
	{
		$this->_selected = false;
	}
	
	/**
	 * Check whether this option is selected.
	 *
	 * @return boolean	True if selected, False otherwise.
	 * @access public
	 */
	function isSelected()
	{		
		return $this->_selected;
	}
	
	
	/**
	 * Assess whether this option changed in the last update. Called from UpdateVisitor.
	 * A Selectable Composite changed if it changed itself or if it is selected and a child changed.
	 */
	function updateChangedStatus()
	{
		//needed because stuff from database can be differently attribute escaped than input data
		$a = (is_string($this->_value)) ? stripslashes($this->_value) : $this->_value;
		$b = (is_string($this->_oldValues)) ? stripslashes($this->_oldValues) : $this->_oldValues;
		
		$this->_hasChanged = ($a != $b);
		
		if(!$this->_hasChanged && $this->isSelected()){
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
	

}

?>
