<?php
namespace helloworld;
class Model_Test extends \Model {
    function init(){
        parent::init();
        $this->setSource('ArrayAssoc',array('test model from add-on'));
    }
}
