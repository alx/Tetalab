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
class HiddenInputFieldOption extends ReusableOption
{
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue)
	{
		parent::__construct($name, $defaultValue, '','','');
	}
	
	
	
}

?>
