<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The ReusableOption:: is the parent class of all options. Component class of 
 * the options Composite pattern.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class TextAreaOption extends ReusableOption
{

	

	/**
	 * Size of the textarea.
	 *
	 * @var integer
	 * @access private
	 */
	var $_rows;
	
	/**
	 * Maximum length of textarea content
	 *
	 * @var integer
	 * @access private
	 */
	var $_cols;
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue, $label = '', 
				$textBefore = '', $textAfter = '', $rows = 10, $cols = 50)
	{
		parent::__construct($name, $defaultValue, $label, $textBefore, $textAfter);
		$this->_rows = $rows;
		$this->_cols = $cols;
	}
	
	
	/**
	 * Getter for rows field.
	 * @return integer		The number of rows of the textarea.
	 * @access public
	 */
	function getRows()
	{
		return $this->_rows;
	}
	
	/**
	 * Setter for rows field.
	 * @param integer $rows		The new number of rows of the textarea.
	 * @access public
	 */
	function setRows($rows)
	{
		$this->_rows = $rows;
	}
	
	/**
	 * Getter for cols field.
	 * @return integer		The number of cols of the textarea.
	 * @access public
	 */
	function getCols()
	{
		return $this->_cols;
	}
	
	/**
	 * Setter for cols field.
	 * @param integer $length	The new number of cols of the textarea.
	 * @access public
	 */
	function setCols($cols)
	{
		$this->_cols = $cols;
	}

}

?>
