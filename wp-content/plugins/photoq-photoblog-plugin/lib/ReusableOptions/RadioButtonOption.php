<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The RadioButtonOption:: class represents a single radio button.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RadioButtonOption extends SelectableCompositeOption
{
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($defaultValue, $label = '', 
					$textBefore = '', $textAfter = '')
	{
		parent::__construct('', $defaultValue, $label, $textBefore, $textAfter);
	}
	
	
	
	
}

?>
