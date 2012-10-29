<?php
/* 
Copyright (C) <2012+> Romans Malinovskis <romans@agiletoolkit.org>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace misc;
class InfiniteAddForm extends \View {
    /*
       Infinite add form for specified model
       */
    public $form;
    function setModel($m){
        parent::setModel($m);
        $this->addForm($_GET[$this->name]?$_GET[$this->name]:1);
    }
    function addForm($u){
        $u=basename($u);

        // This argument is passed when 2nd, 3rd etc forms are loaded. We should preserve it to submit proprely
        $this->api->stickyGET($this->name);

        // Horizontal form is good for 4 fields. Most of the behaviour can be changed through Controller
        $this->form=$f=$this->add('Form',$u,null,array('form_horizontal'));
        $f->setModel($this->model);

        // Determine first and last field in form
        $first_field=null;
        foreach($f->elements as $element){
            if(!($element instanceof \Form_Field))continue;
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
                $f->js()->_selector('#'.$un)->atk4_load($this->api->url(null,
                        array($this->name=>$u+1,'cut_object'=>$un))),
                $first_field->js()->unbind('focus'),
             ));

        // Bluring of last field will submit theform
        $last_field->js('blur',$f->js()->submit());
        if($f->isSubmitted()){
            $m=$f->update()->model;
            $this->jsSuccess($f->js()->fadeOut(),$m)->execute();
        }
    }
    function jsSuccess($js,$m){
        return $js->univ()->successMessage('Record Saved (id='.$m->id.')');
    }
}
