<?php
namespace hierarchy;
class Controller_Array extends \Controller_Data_Array {
    public $field;
    public $child_ref;
    public $class_name;
    function init(){
        parent::init();
        $this->owner->hierarchy_controller=$this;
    }
    function useField($field){
        if(!$this->class_name)$this->class_name=preg_replace('/^Model_/', '', get_class($this->owner)); // remove "Model_" from class
        $this->field=$field;
        $this->child_ref=$field;
    }
    function setSource(\Model $m, $data){

        // Calculate sub-child count
        foreach($data as &$row){
            $row[$this->field.'_cnt']=count($row[$this->field]);
        }

        parent::setSource($m, $data);
    }
    function ref(\Model $m,$field){
        return $this->owner->newInstance()
            ->setSource($this->owner[$this->field]);
    }
}
