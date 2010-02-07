<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The UpdateOptionVisitor:: is responsible for updating visited options. It 
 * typically visits objects after form submission.
 *
 * @author  M. Flury
 * @package ReusableOptions
 */
class UpdateOptionVisitor extends OptionVisitor
{
	
	/**
	 * Abstract implementation of the visitTextField() method called whenever a
	 * TextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextFieldOption &$textField	Reference to visited option.
	 */
	 function visitTextFieldOptionBefore(&$textField)
	 {
	 	if(isset($_POST[$textField->getPOSTName()]))
	 		$textField->setValue(esc_attr($_POST[$textField->getPOSTName()]));
	 }
	 
	 
	 function visitStrictValidationTextFieldOptionBefore(&$textField)
	 {
	 	$oldValue = $textField->getValue();
	 	$this->visitTextFieldOptionBefore($textField);
	 	//check whether we pass validation if not put back the old value
	 	if(!$textField->validate())
	 		$textField->setValue($oldValue);	
	 }
	
	/**
	 * Abstract implementation of the visitTextField() method called whenever a
	 * TextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextFieldOption &$textField	Reference to visited option.
	 */
	 function visitPasswordTextFieldOptionBefore(&$textField)
	 {
	 	$this->visitTextFieldOptionBefore($textField);
	 }
	 
	 /**
	  * Abstract implementation of the visitTextField() method called whenever a
	  * TextFieldSiteOption is visited. Contrary to standard text field, for WPMU
	  * we only allow site_admins to make changes.
	  *
	  * @param object TextFieldOption &$textField	Reference to visited option.
	  */
	 function visitTextFieldSiteOptionBefore(&$textField)
	 {
	 	if(function_exists( 'is_site_admin' ) && is_site_admin())
			$this->visitTextFieldOptionBefore($textField);
	 }

	 
	  
	 /**
	 * Abstract implementation of the visitTextArea() method called whenever a
	 * TextAreaOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextAreaOption &$textArea	Reference to visited option.
	 */
	 function visitTextAreaOptionBefore(&$textArea)
	 {
	 	
	 	if(isset($_POST[$textArea->getPOSTName()]))
	 		$textArea->setValue(str_replace(array("\r\n", "\r", "\n"),PHP_EOL,esc_attr($_POST[$textArea->getPOSTName()])));
	 }
	 
	 /**
	 * Abstract implementation of the visitHiddenInputField() method called whenever a
	 * HiddenInputField is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object HiddenInputField &$hiddenInputField	Reference to visited option.
	 */
	 function visitHiddenInputFieldOptionBefore(&$hiddenInputField)
	 {
	 	if(isset($_POST[$hiddenInputField->getPOSTName()]))
	 		$hiddenInputField->setValue(esc_attr($_POST[$hiddenInputField->getPOSTName()]));
	 }
	
	/**
	 * Abstract implementation of the visitCheckBox() method called whenever a
	 * CheckBoxOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object CheckBoxOption &$checkBox	Reference to visited option.
	 */
	 function visitCheckBoxOptionBefore(&$checkBox)
	 {
	 	if (!isset($_GET['action']))
	 		$checkBox->setValue(isset($_POST[$checkBox->getPOSTName()]) ? '1' : '0');
	 }

	/**
	 * Abstract implementation of the visitCheckBox() method called whenever a
	 * CheckBoxOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object CheckBoxOption &$checkBox	Reference to visited option.
	 */
	 function visitRadioButtonListBefore(&$radioButtonList)
	 {	
	 	if(isset($_POST[$radioButtonList->getPOSTName()]))
	 		$radioButtonList->setValue($_POST[$radioButtonList->getPOSTName()]);
	 }
	 
	 function visitRO_CheckBoxListBefore(&$checkBoxList)
	 {	
	 	if (!isset($_GET['action']))
	 		$checkBoxList->setValue(isset($_POST[$checkBoxList->getPOSTName()]) ? $_POST[$checkBoxList->getPOSTName()] : NULL);
	 }
	 
	 
	 /**
	 * Abstract implementation of the visitDropDownListBefore() method called whenever a
	 * CheckBoxOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownList &$dropDownList	Reference to visited option.
	 */
	 function visitDropDownListBefore(&$dropDownList)
	 {	
	 	if(isset($_POST[$dropDownList->getPOSTName()]))
	 		$dropDownList->setValue($_POST[$dropDownList->getPOSTName()]);
	 }
	 

	 function visitRO_ReorderableListBefore(&$reorderableList){
	 	if(isset($_POST[$reorderableList->getFieldName()]))
	 		$reorderableList->setValue($_POST[$reorderableList->getFieldName()]);
	 }

	 //check whether a new option is being added
	 function visitRO_ExpandableCompositeOptionBefore(&$option){
	 	if (isset($_POST['addExpComp-'.$option->getName()])) {
	 		//name has to be save to create directories and not empty.
	 		$name = preg_replace('/[^a-zA-Z0-9_\-]/','_',$_POST['newExpComp-'.$option->getName()]);
	 		if(!empty($name)){
	 			$addOk = true;
	 			//callback to be executed if we add a child
				if(method_exists($option->_onAddCallback[0], $option->_onAddCallback[1]))
					$addOk = call_user_func_array(array($option->_onAddCallback[0], $option->_onAddCallback[1]), array($name));
				
				//only add if the onAddCallback returned true
				if($addOk){
	 				$className = $option->_childClassName;
	 				$option->addChild(new $className($name),1);
				}else
					echo "could not add, callback returned false";
	 		}else
	 			echo "ERROR: invalid name";
	 	}
	 	if (isset($_GET['action']) && $_GET['action'] == 'delExpComp-'.$option->getName()) {
	 		$name = esc_attr($_GET['entry']);
	 		//check for correct nonce first
	 		check_admin_referer('delExpComp'.$name. '-nonce');
	 		$delOk = true;
	 		//callback to be executed if we remove a child
	 		if(method_exists($option->_onDelCallback[0], $option->_onDelCallback[1]))
	 			$delOk = call_user_func_array(array($option->_onDelCallback[0], $option->_onDelCallback[1]), array($name));

			//only delete if the onDelCallback returned true
	 		if($delOk){
	 			$option->removeChild($name);
	 		}else
	 			echo "could not add, callback returned false";
			
	 	}
	 }

	 
	 
	 /**
	  * Method called whenever any option is visited.
	  *
	  * @param object ReusableOption &$option	Reference to visited option.
	  */
	 function visitDefaultBefore(&$option)
	 {
	 	$option->storeOldValues();
	 }

	 /**
	  * Method called whenever any option is visited.
	  *
	  * @param object ReusableOption &$option	Reference to visited option.
	  */
	 function visitDefaultAfter(&$option)
	 {
	 	$option->updateChangedStatus();
	 }


}

?>