<?php
namespace formandsave;
class FormAndSave extends \Form {
    function init(){
        parent::init();
        $this->addSubmit('Save');

        $this->onSubmit(function($f){
            $f->update();
            $f->js()->univ()->successMessage('Saved')->execute();
        });
    }
}
