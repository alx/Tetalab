<?php
/**
 * @package ReusableOptions
 */


/**
 * A RO_CapabilityCheckBoxList:: represents a list of capabilities that can be dynamically
 * added (removed) to (from) a role.
 *
 * @author  M.Flury
 * @package ReusableOptions
 */
class RO_CapabilityCheckBoxList extends RO_CheckBoxList
{
	/**
	 * The role to which these capabilities belong to
	 * @var string
	 */
	var $_role;

	function __construct($name, $role = 'administrator', $defaultValue = '', $label = '',
				$textBefore = '', $textAfter = '')
	{
		parent::__construct($name, $defaultValue, $label, $textBefore, $textAfter);
		$this->_role = $role;
	}
	
	
	/**
	 * Assess whether this option changed in the last update. Called from UpdateVisitor.
	 * On change update corresponding role in wordpress database
	 */
	function updateChangedStatus()
	{
		parent::updateChangedStatus();
		if($this->_hasChanged){
			$old = empty($this->_oldValues) ? array() : $this->_oldValues;
			$new = empty($this->_value) ? array() : $this->_value;
			
			//update roles and capabilities in the wordpress database
			$remove = array_diff($old, $new);
			$add = array_diff($new, $old);

			$currentRole = get_role($this->_role);
			if(!empty($currentRole)){
				foreach ($remove as $capName)
					$currentRole->remove_cap($capName);
				
				foreach ($add as $capName)
					$currentRole->add_cap($capName);
				
			}
			
		}
	}

	function getRole(){
		return $this->_role;
	}
}


?>
