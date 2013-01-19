<?php
class FormAndSave extends MVCForm {
    function init(){
        parent::init();
        $this->addSubmit('Save');
        $this->api->addHook("pre-render", array($this, "preRender"));
    }
    function preRender(){
        $this->onSubmit(function($f){
            $f->update();
            $f->js()->univ()->successMessage('Saved')->execute();
        });
    }
}
