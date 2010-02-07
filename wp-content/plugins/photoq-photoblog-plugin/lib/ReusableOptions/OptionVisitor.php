<?php
/**
 * @package ReusableOptions
 */
 

/**
 * The OptionVisitor:: is the abstract parent class of a Visitor pattern allowing
 * to perform operations on a hierarchy of options. 
 *
 * @author  M. Flury
 * @package ReusableOptions
 */
class OptionVisitor extends ReusableOptionObject
{
	
	/**
	 * Abstract implementation of the visitTextField() method called whenever a
	 * TextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextFieldOption &$textField	Reference to visited option.
	 */
	 function visitTextField(&$textField)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitPasswordTextField() method called whenever a
	 * PasswordTextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object PasswordTextFieldOption &$textField	Reference to visited option.
	 */
	 function visitPasswordTextField(&$textField)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitTextArea() method called whenever a
	 * TextAreaOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextAreaOption &$textArea	Reference to visited option.
	 */
	 function visitTextArea(&$textArea)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitHiddenInputField() method called whenever a
	 * HiddenInputField is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object HiddenInputField &$hiddenInputField	Reference to visited option.
	 */
	 function visitHiddenInputField(&$hiddenInputField)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitCheckBox() method called whenever a
	 * CheckBoxOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object CheckBoxOption &$checkBox	Reference to visited option.
	 */
	 function visitCheckBox(&$checkBox)
	 {
	 	return false;
	 }
	 
	/**
	 * Abstract implementation of the visitCheckBoxList() method called whenever a
	 * RO_CheckBoxList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RO_CheckBoxList &$checkBoxList	Reference to visited option.
	 */
	 function visitCheckBoxList(&$checkBoxList)
	 {
	 	return false;
	 }
	 
	/**
	 * Abstract implementation of the visitCheckBoxListOption() method called whenever a
	 * RO_CheckBoxListOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RO_CheckBoxListOption &$checkBox	Reference to visited option.
	 */
	 function visitCheckBoxListOption(&$checkBox)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitRadioButtonList() method called whenever a
	 * RadioButtonList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RadioButtonList &$radioButtonList	Reference to visited option.
	 */
	 function visitRadioButtonListBefore(&$radioButtonList)
	 {
	 	return false;
	 }
	 
	/**
	 * Abstract implementation of the visitRadioButtonList() method called whenever a
	 * RadioButtonList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RadioButtonList &$radioButtonList	Reference to visited option.
	 */
	 function visitRadioButtonListAfter(&$radioButtonList)
	 {
	 	return false;
	 }
	 
	 
	 /**
	 * Abstract implementation of the visitRadioButton() method called whenever a
	 * RadioButtonOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RadioButtonOption &$radioButton	Reference to visited option.
	 */
	 function visitRadioButton(&$radioButton)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitDropDownListBefore() method called whenever a
	 * DropDownList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownList &$dropDownList	Reference to visited option.
	 */
	 function visitDropDownListBefore(&$dropDownList)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitDropDownListAfter() method called whenever a
	 * DropDownList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownList &$dropDownList	Reference to visited option.
	 */
	 function visitDropDownListAfter(&$dropDownList)
	 {
	 	return false;
	 }
	 
	 /**
	 * Abstract implementation of the visitDropDownOption() method called whenever a
	 * DropDownOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownOption &$dropDownOption	Reference to visited option.
	 */
	 function visitDropDownOption(&$dropDownOption)
	 {
	 	return false;
	 }
	 
	 

}

?>