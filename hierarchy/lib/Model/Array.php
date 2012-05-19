<?php
/**
 * A handy model for your static hierarchy structure. Extend and define your fileds as well as use setSource
 */
namespace hierarchy;
class Model_Array extends \Model {
    function init(){
        parent::init();

        $this->controller=$this->setController('hierarchy/Array');
        $this->controller->useField('children');
    }

    function setSource(array $array){
        $this->controller->setSource($this,$array);
        return $this;
    }
    function ref($field){
        return $this->controller->ref($this,$field);
    }

}
