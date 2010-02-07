<?php
/**
 * @package ReusableOptions
 */
 

/**
 * A CategoryDropDownList:: is a container for DropDownOptions. Only one of which
 * can be selected at a time.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class CategoryDropDownList extends DropDownList
{
	
	
	/**
	 * PHP5 type constructor
	 */
	function __construct($name, $defaultValue = '', $label = '', 
					$textBefore = '', $textAfter = '')
	{
		$args = array(
			'orderby' => 'ID', 'order' => 'ASC',
			'hide_empty' => 0, 'hierarchical' => 1,
		);
		$categories = get_categories($args);
		parent::__construct($name, $defaultValue, $label, $textBefore, $textAfter);
		
		//populate the list with all available categories
		foreach($categories as $cat){
			$this->addChild(
				new DropDownOption(
		 			$cat->name,
		 			$cat->cat_ID
				)
			);
		}
		
	}
	
	

}

?>
