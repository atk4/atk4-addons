<?php
/*
 * Base class for reference-showing fields
 */

class Form_Field_reference extends Form_Field_ValueList {
	/*
	   Reference field enhances ValueList with support for references. In other words - list of values could rely on
	   other model to get itself from.

	   This class will also provide API interface to set up behavor for adding new values, however in order to
	   see them - you would have to use one of field types which supports it, such as "autocomplete" or "reference_pluss"

	   */

	function setController($c){
		parent::setController($c);
		$this->dictionary($this->getController()->getModel());
		return $this;
	}
	function dictionary($d=null){
		if(!is_null($d))$this->dictionary=$d;
		return $this->dictionary;
	}private $dictionary=null;

	function setValueList($model_or_list, $field_definition=null){
		if($model_or_list instanceof FieldDefinition){
			// ommit first argument
			$field_definition=$model_or_list;
			$model_or_list=$field->refModel();
		}

		if($field_definition){
			// find out if this field can be empty
			$this->required=$field_definition->required();
		}

		if(!is_object($model_or_list)){
			return parent::setValueList($model_or_list);
		}else{
			// we have been passed a Model, we can do more with this
			if(!($model_or_list instanceof AbstractModel))
				throw new BaseException('Only Model or Array can be specified to this function');
			$this->dictionary=$model_or_list;
			$this->allowAdd(method_exists($this->dictionary,'addDefaultEntity'));
		}
		return $this;
	}

	function allowAdd($b=null){
		if(!is_null($b))$this->allow_add=$b;
		return $this->allow_add;
	}private $allow_add=false;

	function emptyValue($val){
		$this->empty_value=$val;
		return $this;
	}




	// This field enhances value list by allowing to store "Model" object inside
	// $this->value_list. You can access the field with getValueList and setValueList functions
	// and also you can still use arays.

	// Should we allow user to add new field? This will be automatically initialised if you
	// initialise this field with controller.

	public $required=false;
	// If field can be empty then use this. You can set this argument to string, which will be
	// displayed instead of empty field. $this->allow_empty="Select category";

	public $empty_value='';
	// For dropdown - display empty value




	// ====== Redefine the following functions if necessary ========
	function getShowAddDialogJS($js=null){
		// This function have to use provided JS which will be executed when client desires
		// to add new element

		if(!$js)$js=$this->js();
		return $js->atk4_reference('showAddDialog',$this->getAddURL());
	}
	function addEntry($value){
		// This function is called when new entry needs to be added. Value can be either
		// empty or it can contain array with key=>value.
		return $this->getDictionary()->addDefaultEntity($value);
	}



	// ===== Call the following functions ========
	function includeDictionary(array $fields=array()){
		/*
		 * We are already including providing form with id=>value from the model. However sometimes
		 * it makes sense to provide additional values from the referred model.
		 *
		 * You can then use univ().bindFillInFields() to automatically fill-in the fields whenever current
		 * field is changed.
		 */
		$fields[]='id';
		foreach($fields as $key=>$row){
			if(is_numeric($key))$key=$row;
			$res[$key]=$row;
		}
		$o=$this->dictionary()->getRows($res);
		$res=array();
		foreach($o as $row){
			$id=$row['id'];
			unset($row['id']);
			$res[$id]=$row;
		}
		$this->setProperty('rel',json_encode($res));
		return $this;
	}


	function validate(){
		$list=$this->getValueList();
		if(!$list || !$this->value)return parent::validate();
		if(!isset($list[$this->value])){
			if($this->api->isAjaxOutput()){
				/*
				$this->ajax()->displayAlert($this->short_name.": Please select to continue")
					->execute();
					*/
				$this->owner->showAjaxError($this,'Please select value from the list');
			}
			$this->owner->errors[$this->short_name]="Please value from the list";
		}
		return parent::validate();
	}
	function getInput($attr=array()){
		$list=$this->getValueList();
		$output=$this->getTag('select',array_merge(array(
						'name'=>$this->name,
						'id'=>$this->name,
						),
					$attr,
					$this->attr)
				);

		foreach($list as $value=>$descr){
			// Check if a separator is not needed identified with _separator<
			$descr = trim($descr);
			if ($value === '_separator') {
				$output.=
					$this->getTag('option',array(
							'disabled'=>'disabled',
						))
					.htmlspecialchars($descr)
					.$this->getTag('/option');
			} else {
				$output.=
					$this->getOption($value)
					.htmlspecialchars($descr)
					.$this->getTag('/option');
			}
		}
		$output.=$this->getTag('/select');
		return $output;
	}
	function getOption($value){
		return $this->getTag('option',array(
					'value'=>$value,
					'selected'=>$value == $this->value
					));
	}



	function getValueList(){
		if(!$this->dictionary()){
			return parent::getValueList();
		}
		$res=array();
		$o=array();
		if(!is_null($this->empty_value)){
			$o=$o+array(''=>array('name'=>$this->empty_value));
		}

		/*
		   // REVERT back sorting (from #1345) becasue it breaks vat rates
		$q=$this->dictionary->resetQuery('get_rows')->dsql('get_rows');
		$this->dictionary->setQueryFields('get_rows',$this->dictionary()->getListFields());

		if(method_exists($this->dictionary(),'getSortColumn') && $sc=$this->dictionary()->getSortColumn()){
			$desc=false;
			if($sc[0]=='-'){
				$desc=true;$sc=substr($sc,1);
			}
			if($this->dictionary()->fieldExists($sc)){
				$this->dictionary()->setOrder('get_rows',$sc,$desc);
			}
		}

		$o=$o+$q->do_getAllHash();
		*/

		// getRows do not support sorting, so we do ourselves.
		// This is to avoid changing getRows in the core, which would have much more severe
		// impact on the system!!
		// TODO: refactor during next major release
		$data=$this->dictionary()->getRows($this->dictionary()->getListFields());

		// fucking good to sort in some certainly the-only-really-ever-needed order. yeah, in the core library.
		//usort($data,'usort_cmp');
		$o=$o+$data;
		foreach($o as $row){
			@$res[$row['id']]=isset($row['name'])?$row['name']:$row['id'];
		}
		return $res;
	}
	private function getShortName(){
		//return $this->short_name;
		$r=explode('_',$this->short_name);
		if(is_numeric($r[count($r)-1]))array_pop($r);
		return join('_',$r);
	}
	function set($value){
		if($value==='')$value=null;
		parent::set($value);
	}
	function setDictionary($model){
		/*
		 * Please refer to a model descendand of Model_dictionary
		 */
		throw new BaseException('please use standard method setValueList and pass array, controller or fieldDefinition');
		$this->dictionary=$model;
		return $this;
	}
	function render(){
		$this->js(true)
			->_load('ui.atk4_reference')
			->atk4_reference();

		parent::render();
	}
}
function usort_cmp($a,$b){
	return strcasecmp($a['name'],$b['name']);
}
