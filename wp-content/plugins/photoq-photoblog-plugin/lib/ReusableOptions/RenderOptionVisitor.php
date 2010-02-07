<?php
/**
 * @package ReusableOptions
 */


/**
 * The RenderOptionVisitor:: is responsible for rendering of the options. It
 * renders every visited option in HTML.
 *
 * @author  M. Flury
 * @package ReusableOptions
 */
class RenderOptionVisitor extends OptionVisitor
{

	/**
	 * Concrete implementation of the visitTextField() method called whenever a
	 * TextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextFieldOption &$textField	Reference to visited option.
	 */
	function visitCompositeOptionBefore(&$option)
	{
		print $option->getTextBefore();
	}
	function visitCompositeOptionAfter(&$option)
	{
		print $option->getTextAfter();
	}
	
	/**
	 * Concrete implementation of the visitTextField() method called whenever a
	 * TextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextFieldOption &$textField	Reference to visited option.
	 */
	function visitTextFieldOptionBefore(&$textField)
	{
		$text = $textField->getTextBefore();
		if($textField->getLabel()){
			$text .= '<label for="'.str_replace(' ','_',$textField->getName()).'">';
			$text .= $textField->getLabel();
			$text .= '</label>';
		}
		$text .= ' <input type="text" name="'.$textField->getName().'" id="'.str_replace(' ','_',$textField->getName()).'" ';
		$text .= 'size="'.$textField->getSize().'" maxlength="'.$textField->getMaxLength().'" ';
		$text .= 'value="'.stripslashes($textField->getValue()).'" /> ';
		$text .= $textField->getTextAfter();
		print $text . PHP_EOL;
	}

	function visitStrictValidationTextFieldOptionBefore(&$textField)
	{
		$this->visitTextFieldOptionBefore($textField);
	}

	/**
	 * Concrete implementation of the visitTextField() method called whenever a
	 * TextFieldOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextFieldOption &$textField	Reference to visited option.
	 */
	function visitPasswordTextFieldOptionBefore(&$textField)
	{
		$text = $textField->getTextBefore();
		if($textField->getLabel()){
			$text .= '<label for="'.str_replace(' ','_',$textField->getName()).'">';
			$text .= $textField->getLabel();
			$text .= '</label>';
		}
		$text .= ' <input type="password" name="'.$textField->getName().'" id="'.str_replace(' ','_',$textField->getName()).'" ';
		$text .= 'size="'.$textField->getSize().'" maxlength="'.$textField->getMaxLength().'" ';
		$text .= 'value="'.stripslashes($textField->getValue()).'" /> ';
		$text .= $textField->getTextAfter();
		print $text . PHP_EOL;
	}

	/**
	 * Concrete implementation of the visitTextArea() method called whenever a
	 * TextAreaOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object TextAreaOption &$textArea	Reference to visited option.
	 */
	function visitTextAreaOptionBefore(&$textArea)
	{
		$text = $textArea->getTextBefore();
		if($textArea->getLabel()){
			$text .= '<label for="'.str_replace(' ','_',$textArea->getName()).'">';
			$text .= $textArea->getLabel();
			$text .= '</label>';
		}
		$text .= ' <textarea name="'.$textArea->getName().'" id="'.str_replace(' ','_',$textArea->getName()).'" ';
		$text .= 'rows="'.$textArea->getRows().'" cols="'.$textArea->getCols().'">';
		$text .= stripslashes($textArea->getValue()).'</textarea> ';
		$text .= $textArea->getTextAfter();
		print $text . PHP_EOL;
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
		$text = '<input type="hidden" name="'.$hiddenInputField->getName().'" id="'.str_replace(' ','_',$hiddenInputField->getName()).'" ';
		$text .= 'value="'.stripslashes($hiddenInputField->getValue()).'" /> ';
		print $text . PHP_EOL;
	}


	/**
	 * Concrete implementation of the visitCheckBox() method called whenever a
	 * CheckBoxOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object CheckBoxOption &$checkBox	Reference to visited option.
	 */
	function visitCheckBoxOptionBefore(&$checkBox)
	{
		$text = $checkBox->getTextBefore();
		if($checkBox->getLabel()){
			$text .= '<label for="'.str_replace(' ','_',$checkBox->getName()).'">';
		}
		$text .= ' <input type="checkbox" name="'.$checkBox->getName().'" id="'.str_replace(' ','_',$checkBox->getName()).'" ';
		if($checkBox->getValue())
		$text .= 'checked="checked"';
		$text .= ' /> ';
		if($checkBox->getLabel()){
			$text .= $checkBox->getLabel();
			$text .= '</label>';
		}

		$text .= $checkBox->getTextAfter();
		
		print $text . PHP_EOL;
	}
	
	function visitCheckBoxOptionAfter(&$checkBox)
	{}
	
	/**
	 * Concrete implementation of the visitRadioButton() method called whenever a
	 * RadioButtonOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RadioButtonOption &$radioButton	Reference to visited option.
	 */
	function visitRadioButtonOptionBefore(&$radioButton)
	{
		$text = $radioButton->getTextBefore();
		if($radioButton->getLabel()){
			$text .= '<label>';
		}
		$text .= ' <input type="radio" name="'.$radioButton->getName().'" ';
		$text .= 'value="'.$radioButton->getValue().'" ';
		if($radioButton->isSelected()){
			$text .= 'checked="checked"';
		}
		$text .= ' /> ';
		if($radioButton->getLabel()){
			$text .= $radioButton->getLabel();
			$text .= '</label>';
		}
		$text .= $radioButton->getTextAfter();	 
		
		print $text . PHP_EOL;
	}
	
	function visitRadioButtonOptionAfter(&$radioButton)
	{}

	/**
	 * Concrete implementation of the visitCheckBoxListOption() method called whenever a
	 * CheckBoxListOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object CheckBoxListOption &$checkBox	Reference to visited option.
	 */
	function visitRO_CheckBoxListOptionBefore(&$checkBox)
	{
		$text = $checkBox->getTextBefore();
		if($checkBox->getLabel()){
			$text .= '<label>';
		}
		$text .= ' <input type="checkbox" name="'.$checkBox->getName().'[]" ';
		$text .= 'value="'.$checkBox->getValue().'" ';
		if($checkBox->isSelected()){
			$text .= 'checked="checked"';
		}
		$text .= ' /> ';
		if($checkBox->getLabel()){
			$text .= $checkBox->getLabel();
			$text .= '</label>';
		}
		$text .= $checkBox->getTextAfter();
		 
		print $text . PHP_EOL;
	}

	/**
	 * Concrete implementation of the visitDropDownListBefore() method called whenever a
	 * DropDownList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownList &$dropDownList	Reference to visited option.
	 */
	function visitDropDownListBefore(&$dropDownList)
	{
		print $dropDownList->getTextBefore() . '<select name="'.$dropDownList->getName().'" id="'.$dropDownList->getName().'">';
	}

	/**
	 * Concrete implementation of the visitDropDownListAfter() method called whenever a
	 * DropDownList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownList &$dropDownList	Reference to visited option.
	 */
	function visitDropDownListAfter(&$dropDownList)
	{
		print "</select>";
		if($dropDownList->getLabel()){
			$text .= ' <label for="'.str_replace(' ','_',$dropDownList->getName()).'">';
			$text .= $dropDownList->getLabel();
			$text .= '</label>';
			print $text . PHP_EOL;
		}
		print $dropDownList->getTextAfter();
	}


	/**
	 * Concrete implementation of the visitRadioButtonListBefore() method called whenever a
	 * RadioButtonList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RadioButtonList &$radioButtonList	Reference to visited option.
	 */
	function visitRadioButtonListBefore(&$radioButtonList)
	{
		print $radioButtonList->getTextBefore();
	}

	/**
	 * Concrete implementation of the visitRadioButtonListAfter() method called whenever a
	 * RadioButtonList is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object RadioButtonList &$radioButtonList	Reference to visited option.
	 */
	function visitRadioButtonListAfter(&$radioButtonList)
	{
		if($radioButtonList->getLabel()){
			$text .= ' <label for="'.str_replace(' ','_',$radioButtonList->getName()).'">';
			$text .= $radioButtonList->getLabel();
			$text .= '</label>';
			print $text . PHP_EOL;
		}
		print $radioButtonList->getTextAfter();
	}

	/**
	 * Concrete implementation of the visitDropDownOption() method called whenever a
	 * DropDownOption is visited. Subclasses should override this and and
	 * define the operation to be performed.
	 *
	 * @param object DropDownOption &$dropDownOption	Reference to visited option.
	 */
	function visitDropDownOptionBefore(&$dropDownOption)
	{
		$text = '';
		$text .= '<option value="'.$dropDownOption->getValue().'"';
		if($dropDownOption->isSelected()){
			$text .= ' selected="selected"';
		}
		$text .= '>';
		$text .= $dropDownOption->getName().'</option>';
		 
		print $text . PHP_EOL;
	}

	
	
	function visitRO_ReorderableListBefore(&$reorderableList, $selected){
		switch($selected){
			case true:
				print '<input name="'.$reorderableList->getFieldName().'" type="hidden" id="'.$reorderableList->getFieldName().'" size="100" />';
				print '<div class="reorderableList"><h4 class="reorderableListTitle">'.$reorderableList->getL10nString('selectedListLabel').'</h4><ul id="'.$reorderableList->getName().'" class="reorderable selectedOptions">';
				break;
			case false:
				print '<div class="reorderableList"><h4 class="reorderableListTitle">'.$reorderableList->getL10nString('deselectedListLabel').'</h4><ul id="'.$reorderableList->getName().'All" class="reorderable deselectedOptions">';
				break;
		}
	}

	/**
	 * After the reorderable list we add a hidden field that will contain the ordering
	 * and that can be stored to the database. this hidden field is kept up-to-date via
	 * the reorder-option.js script.
	 * @param $reorderableList
	 * @return unknown_type
	 */
	function visitRO_ReorderableListAfter(&$reorderableList){
		print '</ul></div>';		
	}
	
	/**
	 * Called before a (sortable) child of a reorderable list is rendered. We wrap
	 * each such child with a li element that can be dragged around. 
	 * We add the suffix xLIx here to distinguish the id of the wrapping li element
	 * from the id of the contained ReusableObject that can be sorted.
	 * @param $reorderableList
	 * @param $child
	 * @return unknown_type
	 */
	function visitRO_ReorderableListBeforeChild(&$child, &$parent){
		print '<li id="'.str_replace(' ','_',$child->getName()).'">';
	}
	function visitRO_ReorderableListAfterChild(&$child, &$parent){
		print '</li>';
	}
	
	
	
	function visitRO_ExpandableCompositeOptionBefore(&$option){
		//print '<input name="'.$reorderableList->getFieldName().'" type="hidden" id="'.$reorderableList->getFieldName().'" size="100" />';
		//print '<div class="expandableComposite"><h4 class="expandableCompositeTitle">'.$option->getL10nString('selectedListLabel').'</h4>
		print '<div class="expCompOption">';
		print '<ul id="'.$option->getName().'" class="expCompOptionList">';
	}

	/**
	 * After the reorderable list we add a hidden field that will contain the ordering
	 * and that can be stored to the database. this hidden field is kept up-to-date via
	 * the reorder-option.js script.
	 * @param $reorderableList
	 * @return unknown_type
	 */
	function visitRO_ExpandableCompositeOptionAfter(&$option){
		print '</ul>';//</div>';
		print $option->getTextBefore().'<input type="text" name="newExpComp-'.$option->getName().'" id="newExpComp-'.$option->getName().'"
					size="20" maxlength="20" value="" />
			<input type="submit" class="button-secondary"
					name="addExpComp-'.$option->getName().'"
					value="Add new &raquo;" />';
		print $option->getTextAfter().'</div>';
	}
	
	/**
	 * Called before a (sortable) child of a reorderable list is rendered. We wrap
	 * each such child with a li element that can be dragged around. 
	 * We add the suffix xLIx here to distinguish the id of the wrapping li element
	 * from the id of the contained ReusableObject that can be sorted.
	 * @param $reorderableList
	 * @param $child
	 * @return unknown_type
	 */
	function visitRO_ExpandableCompositeOptionBeforeChild(&$child, &$parent){
		
		print '<li class="expCompEl" id="expCompEl_'.str_replace(' ','_',$child->getName()).'">';
		if($parent->isChildRemovable($child->getName())){
			//add delete link
			$deleteLink = basename($_SERVER['REQUEST_URI']).'&amp;action=delExpComp-'.$parent->getName().'&amp;entry='.$child->getName();
	 		$deleteLink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($deleteLink, 'delExpComp' . $child->getName() . '-nonce') : $deleteLink;
	 		$deleteLink = '<a href="'.$deleteLink.'" class="deleteLink" onclick="return confirm(\'Are you sure?\');">Delete "'.$child->getName().'" entry</a>';
	 		print $deleteLink;
	 	}
	}
	
	function visitRO_ExpandableCompositeOptionAfterChild(&$child, &$parent){
		print '</li>';
	}



}

?>