<?php
namespace 'helloworld';
class Core extends \AbstractView {
    function init(){
        $this->setModel('Test');
    }
    function render(){
        $x=$this->model->load()->get('name');
        $this->output('Hello world from add-on. Model used is '.$x);
    }
}
