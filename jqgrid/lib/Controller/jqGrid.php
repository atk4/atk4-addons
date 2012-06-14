<?php
namespace jqgrid;
class Controller_jqGrid extends \AbstractController {

	function setActualFields($fields){
		if($fields===false)return;
		$this->importFields($this->owner->model,$fields);

		$this->owner->setSource($this->owner->model);
    }
    function importFields($model,$fields){
		$this->model=$this->owner->model;

		if(!$fields || $fields===undefined)$fields='visible';
        if(!is_array($fields))$fields=$model->getActualFields($fields);
        foreach($fields as $field){
            $this->importField($field);
        }
        $model->setActualFields($fields);

        return $this;
    }
    function importField($field){

    	$f=$this->model->getElement($field);

    	$this->owner->addColumn('text',$f->short_name,$f->caption());
	}
}