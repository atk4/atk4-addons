<?php
/*
 * Created on 06.04.2009
 *
 * Responsibilities:
 *  - gateway beetween model and view objects
 */

class Controller extends AbstractController{
	protected $type_correspondence=array(
		'grid'=>array(
			'string'=>'text',
			'int'=>'number',
			'numeric'=>'number',
			'real'=>'real',
			'money'=>'money',
			'text'=>'shorttext',
			'reference'=>'text',
			'password'=>'text',
			'datetime'=>'timestamp',
			'date'=>'date',
			'daytime'=>'daytime',
			'daytime_total'=>'daytime_total',
			'boolean'=>'boolean',
			'list'=>'text',
			'readonly'=>'text',
			'image'=>'text',
			'file'=>'referenece',
		),
		'tree'=>array(
			'string'=>'text',
			'int'=>'text',
			'numeric'=>'text',
			'money'=>'text',
			'real'=>'text',
			'text'=>'shorttext',
			'reference'=>'text',
			'datetime'=>'timestamp',
			'date'=>'date',
			'boolean'=>'text',
			'list'=>'text',
			'readonly'=>'text',
		),
		'form'=>array(
			'string'=>'line',
			'text'=>'text',
			'int'=>'line',
			'numeric'=>'line',
			'money'=>'line',
			'real'=>'line',
			'date'=>'DatePicker',
			'datetime'=>'DatePicker',
			'daytime'=>'timepickr',
			'boolean'=>'checkbox',
			'reference'=>'readonly',
			'reference_id'=>'reference',
			'password'=>'password',
			'list'=>'reference',
			'radio'=>'Radio',
			'readonly'=>'readonly',
			'image'=>'image',
			'file'=>'upload',
		),
		'filter'=>array(
			'string'=>'line',
			'text'=>'text',
			'int'=>'line',
			'numeric'=>'line',
			'real'=>'line',
			'money'=>'line',
			'date'=>'daterange',
			'datetime'=>'daterange',
			'daytime'=>'timepickr',
			'boolean'=>'AScheckbox',
			'reference'=>'autocomplete',
			'password'=>'password',
			'list'=>'reference',
			'readonly'=>'readonly',
			'image'=>'upload',
		),
	);
	protected $model_name;
	protected $active_fields=null;				// If set this defines which fields to display in order

	function __call($name,$args){
		// some of the methods may be in related Model
		if(method_exists($this->getModel(),$name)){
			$r=call_user_func_array(array($this->getModel(),$name),$args);
			//if($r instanceof Model)return $this;
			return $r;
		}
		if(!method_exists($this,$name))throw new Exception_InitError("Method $name is not defined neither in $this->short_name, " .
				"nor in its Model");
	}

	function init(){
		parent::init();
		if($this->model_name)//throw new Exception_InitError("Model name is not defined for controller $this->name");
			$this->setModel($this->model_name);
	}
    function debug(){
        $this->getModel()->debug();
        return $this;
    }

	function initForm(){
		$this->addFormFields();
		$this->owner->setTitle($this->getTitle());
		// adding DB fields
		////$this->getModel()->dsql($this->owner->name,false)->field(array_keys($this->owner->getAllData()));

		$this->getModel()
			->setQueryFields('edit_dsql')  // get external fields feature, with needed joins and filtering fields
			//->dsql($this->owner->name,false);
		;
		//
        $this->api->hook('compat-addModelSave',array($this->owner));
		//$this->owner->addSubmit('Save');
		return $this;
	}
	function initFilter(){
		$this->addFilterFields();
		$this->getModel()
			->setQueryFields('edit_dsql')  // get external fields feature, with needed joins and filtering fields
		;
		return $this;
	}
	/**
	 * Adds fields to a form on the basis of model definition
	 */
	function addFormFields(){
		foreach($this->getActualFields() as $field_name=>$def){
			if(!is_object($def))continue;
			//if(!$def->visible())continue;
			if(!$def->editable())continue;
			//if($def->system()===true)continue;
			$this->owner->addFieldMVC($field_name);
			// all field's parameters and manipulations are made in MVCForm::addField()
		}
	}
	/**
	 * Adds columns to a grid on the basis of model definition
	 */
	function addGridFields(){
		foreach($this->getActualFields() as $field_name=>$def){
			// some field types are not required ???
			if(!is_object($def))continue;
			// we don't chck system status here, as actual fields contain fields that must be shown
			if($def->visible()===true && $def->type()!='reference_id')$this->owner->addColumnMVC($field_name);
		}
	}
	/**
	  * Initialise search filter for this entity list
	  */
	function addFilterFields(){
		foreach($this->getActualFields() as $field_name=>$def){
			if(!is_object($def))continue;
			$this->owner->addFieldMVC($field_name);
		}
	}
	/**
	 * Returns Model field set
	 */
	function getAllFields(){
		return $this->getModel()->getFields();
	}
	function getTitle(){
		$r=explode('_',$this->short_name);
		array_shift($r);
		return join(' ',$r);
	}
	function initLister(){
		$this->addGridFields();
	}
	/**
	 * Returns the string representing the type of the field
	 */
	function formatType($type,$object,$field=null){
        if($field){
			//$arr=$this->model->getField($field)->display();
			$arr=$this->getModel()->getField($field)->display();
            if(is_array($arr) && $arr[$object]){
                return $arr[$object];
            }
        }
		$r=$this->type_correspondence[$object][$type];
		if(!$r)throw new Exception_InitError("Type '$type' is not defined for $object");//$r=$type;
		return $r;
	}
	/**
	 * Previously introduced in Lister, this method executes select query before
	 * the associated View object will be rendered
	 */
	function execQuery(){
		// default fields must be added (if not yet)
		if(!$this->getModel()->isFieldsSet($this->owner->name))
			$this->getModel()->setQueryFields($this->owner->name);
		$this->getModel()->dsql($this->owner->name)->do_select();
	}
	function update($data=array()){
		// if no data passed trying to get it from the Form that owns controller
		// $data will be appended to values already set with Controller::set() or Model::set()
		if(empty($data)&&($this->owner instanceof Form))$data=$this->owner->getAllData();
		$this->getModel()->update($data);
		return $this;
	}
    function _bindView(){
        // data was loaded previously
        // problem here, isInstanceLoaded is not defined in Controller - it's in Model.. - causing crashing
        if (method_exists($this, "isInstanceLoaded")){
            if($this->isInstanceLoaded()){
                /*

                   // TODO: test and uncomment this!

                if($this->owner instanceof Form){
                    if(isset($_GET['id']))$this->owner->addConditionFromGET('id');
                    else $this->owner->addCondition('id',$id);
                }
                */
                if($this->owner instanceof View){
                    $this->owner->template->set($this->get());
                }
            }
        }
    }
	/**
	 * Calls assigned Model loadData() and in addition adds condition to form,
	 * if form is the owner of this controller
	 */
	function loadData($id=null,$all_fields=false){
		$this->getModel()->loadData($id,$all_fields);
		if($this->owner instanceof Form){
			if(isset($_GET['id']))$this->owner->addConditionFromGET('id');
			else $this->owner->addCondition('id',$id);
		}
		if($this->owner instanceof View){
			$this->owner->template->set($this->get());
		}
		return $this;
	}

	/**
	  * Set list of fields which should be displayed by grid or quickedit
	  */
	public function setActualFields($actual_fields){
		$this->getModel()->setActualFields($actual_fields);
		return $this;
	}
	/**
	 * This method used by admin system
	 * Returns the assoc array of extra action to be added to entity lister near Add button,
	 * so the core could render entity lister automatically w/o additional page class creation
	 * No implementation here for the actions listed, all functionality is implemented in
	 * corresponding page class
	 * @return false or array($href=>$title), where $href is valid URL in AModules3 style
	 */
	public function getExtraActions(){
		return false;
	}
	/**
	 * Generates user-friendly string of related entities
	 * @param array $data entity hash as it returned from Model_Table::getRelatedEntities()
	 * @return string
	 */
	public function formatRelatedEntities($data){
		$result=array();
		foreach($data as $entity=>$count){
			switch($entity){
				case 'docspec':
					$name='document line';
					break;

				case 'timesheet':
					$name='timesheet entry';
					break;

				case 'fixed_asset':
					$name='fixed asset';
					break;

				default:
					$name=$entity;
			}
			$result[]=$count." $name".($count==1?'':'s');
		}
		return join(', ',$result);
	}
	public function countRelatedEntities($data){
		$result=0;
		foreach($data as $count)$result+=$count;
		return $result;
	}
	function __toString(){
		return $this->getModel()->__toString();
	}
}
