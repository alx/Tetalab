<?php
/**
 * @package ReusableOptions
 */
 

/**
 * A RadioButtonList:: is a container for RadioButtonOptions. Only one of which
 * can be selected at a time.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RadioButtonList extends SelectionList
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
		if(is_a($option, 'RadioButtonOption')){
			//all radiobuttons in a group must have the name of the group
			$option->setOptionName($this->getName());		
			return parent::addChild($option);
		}
		
		return false;
	}
	
	
	
	/**
	 * Populate List with children given by value-label array.
	 *
	 * @access public
	 * @param array $nameValueArray value-label pairs with which to populate the list.
	 */
	function populate($valueLabelArray, $textBefore = '', $textAfter = '')
	{
		//populate the list with all ImageSizes
		foreach ($valueLabelArray as $value => $label){
			$this->addChild(
			new RadioButtonOption(
			$value, $label, $textBefore, $textAfter
			)
			);
		}
	}
	
	

}

?>
