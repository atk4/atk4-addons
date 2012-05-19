<?php
namespace hierarchy;
class Controller_Table extends AbstractController {
    public $class_name;
    public $child_ref;
    public $parent_ref;
    function init(){
        parent::init();
        $this->owner->hierarchy_controller=$this;
    }
    function useField($field){
        if(!$this->class_name)$this->class_name=preg_replace('/^Model_/', '', get_class($this->model)); // remove "Model_" from class
        if(!$this->child_ref)$this->child_ref=$this->class_name;
        $this->parent_ref=$field;

        if(!$this->owner->hasElement($parent_ref)$this->owner->hasOne($this->class_name,$field);
        if(!$this->owner->hasElement($child_ref)$this->owner->hasMany($this->child_ref,$field)
            ->display(array('form'=>'hierarchy/drilldown'));

        $this->addCountColumn($this->child_ref.'_cnt');
    }
    function addCountColumn($f){
        $this->owner->addExpression($f)->set($this->refSQL($this->child_ref)->count());
    }
}
