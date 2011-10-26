<?php
class FormAndSave extends MVCForm {
    function init(){
        parent::init();
        $this->addSubmit('Save');

        $this->onSubmit(function($f){
            $f->update();

            $f->js()->univ()->successMessage('Saved')->execute();
        });
    }
}
