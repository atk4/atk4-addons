<?php
namespace filestore;
class Field_File extends \Field {
    public $use_model = 'filestore/File';
    function init(){
        parent::init();
        $this->setModel($this->use_model);
        $this->display(array('form'=>'upload'));
    }
    function displaytype($x){return $this;}
    function ref(){
        if(!$this->model){
            $this->model=preg_replace('|^(.*/)?(.*)$|','\1Model_\2',$this->use_model);
            $this->model=$this->add($this->model);
        }
        if(!$this->get())throw $this->exception('Reference field has no value')
            ->addMoreInfo('model',$this->owner)
            ->addMoreInfo('field',$this->short_name)
            ->addMoreInfo('id',$this->owner->id)
            ;
        return $this->model->load($this->get());
    }
}
