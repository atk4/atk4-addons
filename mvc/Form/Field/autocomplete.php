<?php
/*
 * Text input with autocomplete
 */


class Form_Field_autocomplete extends Form_Field_reference {
	function render(){
		parent::render();

		$js=$this->js(true)
			->_load('ui.atk4_reference')
			->_load('ui.combobox')
			->atk4_reference()
			->atk4_reference('initAutocomplete',$this->getAutocompleteOptions())
			;

		//if($this->allowAdd())$this->addPluss($js);
	}
	/* 
	//
	//Broken as of 4.2 with multiple compile errors (e.g. getController() undefined, allowAdd() undefined...)
	function addPluss($js){
		$forms_name=$this->owner->getController();
		$title=null;
		$my_name=$this->short_name;
		if(isset($forms_name)){
			$title=$this->caption;
				//$forms_name->getField($my_name)->caption();
			$forms_name=$forms_name->short_name;

		}


	 	if(($this->urlForAdding() || method_exists($this->dictionary(),'addDefaultEntity')) && $this->allowAdd()){
			$js->atk4_reference('setPlusUrl',
					$this->api->getDestinationURL($this->urlForAdding(),
						array(
							'cut_region'=>'Content',
							'form_ctl'=>$forms_name,
							'my_name'=>$my_name,
							),false),array('height'=>'500'),$title);
		}
	}
	*/
	function getAutocompleteOptions(){
		if($this->short_name=='vat_rate_id')return array();
		return array('width'=>'200px');
	}
}
