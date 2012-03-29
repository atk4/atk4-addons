<?php
namespace formandsave;
class FormAndSave extends \Form {
    function init(){
        parent::init();
        $this->addSubmit('Save');

        $f=$this;
        $this->api->addHook('post-init',function() use($f){
            if($f->isSubmitted()){
                $f->update();
                $f->js()->univ()->successMessage('Saved')->execute();
            };
        });
    }
}
