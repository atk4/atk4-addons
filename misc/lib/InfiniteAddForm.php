<?php
class InfiniteAddForm extends View {
    /*
       Infinite add form for specified model
       */
    public $c;
    public $form;
    function setController($c){

        $this->c=$c;
        
        $this->addForm($_GET[$this->name]?$_GET[$this->name]:1);
    }
    function addForm($u){
        $u=basename($u);

        // This argument is passed when 2nd, 3rd etc forms are loaded. We should preserve it to submit proprely
        $this->api->stickyGET($this->name);

        // Horizontal form is good for 4 fields. Most of the behaviour can be changed through Controller
        $this->form=$f=$this->add('MVCForm',$u,null,array('form_horizontal'));
        $f->setController($this->c);

        // No buttons
        if($f->hasElement('Save'))$f->elements['Save']->destroy();

        // Determine first and last field in form
        $first_field=null;
        foreach($f->elements as $element){
            if(!($element instanceof Form_Field))continue;
            $element->js(true)->univ()->disableEnter();
            if(!$first_field)$first_field=$element;
            $last_field=$element;
        }
        $first_field->setAttr('class','nofocus');

        // Calculate identifier for the next form in line. Those should be unique
        $un=$this->name.'_'.($u+1);

        // Focusing first field triggers loading of additional form. This way it has plenty of time to load
        // by the time this form is filled out. Also drop binding to avoid double-loading
        $first_field->js('focus',array(
                $this->js()->append('<div id="'.$un.'"/>'),
                $this->js()->_selector('#'.$un)->atk4_load($this->api->getDestinationURL(null,
                        array($this->name=>$u+1,'cut_object'=>$un))),
                $first_field->js()->unbind('focus'),
             ));

        // Bluring of last field will submit theform
        $last_field->js('blur',$f->js()->submit());
        if($f->isSubmitted()){
            $id=$f->update();
            $this->jsSuccess($f->js()->fadeOut(),$id)->execute();
        }
    }
    function jsSuccess($js,$id){
        return $js->univ()->successMessage('Record Saved ('.$id.')');
    }
}
