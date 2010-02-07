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
class SelectableOption extends ReusableOption
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
	 * A selectable option isn't stored itself by default. it is the parent
	 * list that stores the appropriate value
	 *
	 * @return array $result		Array of options to store in database.
	 * @access public
	 */
	function store()
	{
		$result = array();
		return $result;
	}
	
	/**
	 * Similarly, not loaded either.
	 * 
	 * @param array $storedOptions		Array pulled from database.
	 * @access public
	 */
	function load($storedOptions)
	{
		return false;
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

}

?>
