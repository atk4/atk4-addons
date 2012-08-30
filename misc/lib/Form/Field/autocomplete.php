<?php
/*
 * Text input with autocomplete
 */
namespace misc;

class Form_Field_autocomplete extends \Form_Field_dropdown {
	function init(){
        parent::init();
		$js=$this->js(true)
            ->_load('ui.atk4_reference')
			->_load('ui.combobox')
			->atk4_reference('initAutocomplete',$this->getAutocompleteOptions())
			;

	}
	function getAutocompleteOptions(){
		return array('width'=>'200px');
	}
}
