<?php
namespace filestore;
class Field_Image extends \Field {
    function init(){
        parent::init();
        $this->setModel('filestore/Image');
        $this->display(array('form'=>'upload'));
    }
}
