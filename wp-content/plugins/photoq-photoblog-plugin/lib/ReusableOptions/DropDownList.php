<?php
/**
 * @package ReusableOptions
 */


/**
 * A DropDownList:: is a container for DropDownOptions. Only one of which
 * can be selected at a time.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class DropDownList extends SelectionList
{

	
	
	/**
	 * Populate List with children given by name-value array.
	 *
	 * @access public
	 * @param array $nameValueArray Name-value pairs with which to populate the list.
	 */
	function populate($nameValueArray)
	{
		//populate the list with all child options
		foreach ($nameValueArray as $name => $value){
			$this->addChild(
			new DropDownOption(
			$name, $value
			)
			);
		}
	}



}

?>
