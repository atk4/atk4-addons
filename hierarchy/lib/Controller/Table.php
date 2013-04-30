<?php
namespace hierarchy;
class Controller_Table extends \AbstractController {
    public $class_name;
    public $child_ref;
    public $parent_ref;
    function init(){
        parent::init();
        $this->owner->hierarchy_controller=$this;
    }
    function useField($field){
        if(!$this->class_name)$this->class_name=preg_replace('/^Model_/', '', get_class($this->owner)); // remove "Model_" from class
        if(!$this->child_ref)$this->child_ref=$this->class_name;
        $this->parent_ref=$field;

        if(!$this->owner->hasElement($this->parent_ref))$this->owner->hasOne($this->class_name,$field)
            ->display(array('form'=>'hierarchy/drilldown'));
        if(!$this->owner->hasElement($this->child_ref))$this->owner->hasMany($this->child_ref,$field);

        $this->addCountColumn(strtolower($this->child_ref).'_cnt');
    }
    function addCountColumn($f){
        $self=$this;
        if($this->owner->hasElement($f))return;
        $this->owner->addExpression($f)->set(function($m)use($self,$f){
            return $m->refSQL($self->child_ref)->count();
        });
    }
}
