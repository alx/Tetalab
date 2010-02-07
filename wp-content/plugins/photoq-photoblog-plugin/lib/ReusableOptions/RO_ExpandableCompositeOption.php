<?php 

class RO_ExpandableCompositeOption extends CompositeOption
{
	
	/**
	 * This composite can hold only children of this type
	 * @var string
	 */
	var $_childClassName;
	var $_excludedNames;
	var $_onAddCallback;
	var $_onDelCallback;

	function __construct($name, $childClassName, $onAddCallback = NULL, $onDelCallback = NULL, $excludedNames = array(), $defaultValue = array(), $label = '',
		$textBefore = '', $textAfter = '')
	{
		parent::__construct($name, $defaultValue, $label, $textBefore, $textAfter);
		$this->_childClassName = $childClassName;
		$this->_excludedNames = $excludedNames;
		$this->_onAddCallback = $onAddCallback;
		$this->_onDelCallback = $onDelCallback;
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
		//print_r($storedOptions);
		if(is_array($storedOptions)){
			if(array_key_exists($this->getName(), $storedOptions)){
				$this->setValue($storedOptions[$this->getName()]);
			}
			
			//register all ImageSizes that can be added/removed on runtime
			foreach ($this->getValue() as $key => $removable){
				//only add if not yet there
				if(!in_array($key,$this->getChildrenNames())){ 
					$this->addChild(new $this->_childClassName($key), $removable);
				}
			}
			
			
			
			parent::load($storedOptions);
			
			
		}
		
		

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
		$result[$this->_name] = $this->getValue();
		$result = array_merge($result, parent::store());
		return $result;
	}
	
	/**
	 * Registers form submit buttons if any with the OptionController specified.
	 * @param $oc
	 * @return unknown_type
	 */
	function registerSubmitButtons(&$oc){
		$oc->registerPostButton('addExpComp-'.$this->getName());
		$oc->registerGetButton('delExpComp-'.$this->getName());
		parent::registerSubmitButtons($oc);
	}
	
	/**
	 * Add an option to the composite. And add its name to the list of names (= value of ImageSizeContainer)
	 * 
	 * @param object ReusableOption &$option  The option to be added to the composite.
	 * @return boolean	True if options could be added (composite), false otherwise.
	 * @access public
	 */
	function addChild(&$option, $removable = 1)
	{	
		if(is_a($option, $this->_childClassName) && !in_array($option->getName(),$this->_excludedNames)){
			$newValue = $this->getValue();
			$newValue[$option->getName()] = $removable;
			$this->setValue($newValue);
			return parent::addChild($option);
		}
		return false;
	}
	
	/**
	 * Remove an option from the composite.	
	 * 
	 * @param string $name  The option to be removed from the composite.
	 * @return boolean 		True if existed and removed, False otherwise.
	 * @access public
	 */
	function removeChild($name)
	{	
		$newValue = $this->getValue();
		if($newValue[$name]){ //only remove images sizes that are allowed to be removed
			unset($newValue[$name]);
			$this->setValue($newValue);
			return parent::removeChild($name);
		}
		return false;
	}
	
	/**
	 * Checks whether the child with given name is removable
	 * @param $childName
	 * @return unknown_type
	 */
	function isChildRemovable($childName){
		$val = $this->getValue();
		if($val[$childName])
			return true;
		return false;
	}
	
	
	/**
	 * Returns an array containing names of imagesizes that changed during
	 * last update.
	 * @return array
	 */
	/*function getChangedImageSizeNames(){
		return $this->_getImageSizeNamesWithAttribute();
	}*/
	
	/**
	 * Returns an array containing names of imagesizes that have a watermark.
	 * @return array
	 */
	/*function getImageSizeNamesWithWatermark(){
		return $this->_getImageSizeNamesWithAttribute('hasWatermark');
	}*/
	
	/**
	 * Low level function that allows to query image sizes through a callback function.
	 * Names of image sizes whose callback return true are returned in an array.
	 * @param $hasAttributeCallback the callback function to be called.
	 * @return array names of image sizes for which the callback returned true
	 */
	/*function _getImageSizeNamesWithAttribute($hasAttributeCallback = 'hasChanged'){
		$with = array();
		foreach($this->getChildrenWithAttribute($hasAttributeCallback) as $current)
			$with[] = $current->getName();
			
		return $with;
		
		
	}*/

	
}


?>