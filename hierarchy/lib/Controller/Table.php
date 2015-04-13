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

        // travel to parent
        if (!$this->owner->hasElement($this->parent_ref)) {
            $r = $this->owner->hasOne($this->class_name,$field)
                        ->display(array('form'=>'hierarchy/drilldown'));
            // set different table alias for parent table
            $r->table_alias = $this->getAlias($this->owner);
        }

        // travel to children
        if (!$this->owner->hasElement($this->class_name)) {
            $r = $this->owner->hasMany($this->class_name,$field,null,$this->child_ref);
            // set different table alias for child table
            $r->table_alias = $this->getAlias($this->owner);
        }

        // add column of children count
        $this->addCountColumn(strtolower($this->child_ref).'_cnt');
    }
    function addCountColumn($f){
        $self=$this;
        if($this->owner->hasElement($f))return;
        $this->owner->addExpression($f)->set(function($m)use($self){
            return $m->refSQL($self->child_ref)->count();
        });
    }
    // Generate custom table alias for sub-selects.
    // Otherwise tables get mixed together in SELECTs like this:
    // SELECT `name`,`parent_id`,(select `name` from `foo` where `foo`.`parent_id` = `foo`.`id`) `parent_cnt`,`id` FROM `foo`
    protected function getAlias($m) {
        return ($m->table_alias?:$m->table) . '_sub';
    }
}
