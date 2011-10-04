<?php

class MultiController extends Controller {
    function getActualFields(){
        $ret = array();
        foreach ($this->models as $model){
            $ret = array_merge($ret, $model->getActualFields());
        }
        $this->fields = $ret;
        return $ret;
    }
    function getModel(){
        return $this;
    }
    function getField($field_name){
        foreach ($this->fields as $name => $field){
            if ($name == $field_name){
                return $field;
            }
        }
    }
	function setQueryFields($instance,$get_fields=false){
        foreach ($this->models as $model){
            $model->setQueryFields($instance,$get_fields);
        }
    }
	function setCondition($instance=null,$field,$value=null,$complex=false){
        foreach ($this->models as $model){
            $model->setCondition($instance=null,$field,$value=null,$complex=false);
        }
    }
    function updateAll($data){
        foreach ($this->models as $model){
            $model->update($data);
        }
    }
    function get($field = null){
        $data = array();
        foreach ($this->models as $model){
            $data = array_merge($data, $model->get());
        }
        if ($field === null){
            return $data;
        } else {
            if (isset($data[$field])){
                return $data[$field];
            }
        }
        return null;
    }
    function addModel($name, $actual_fields = array()){
        $this->models[] = $m = $this->add("Model_" . $name);
        if ($actual_fields){
            $m->setActualFields($actual_fields);
        }
        return $m;
    }
}
