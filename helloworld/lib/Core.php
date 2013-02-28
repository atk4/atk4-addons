<?php
namespace helloworld;
class Core extends \AbstractView {
    function init(){
        parent::init();
        $this->setModel('helloworld/Test');
    }
    function render(){
        $this->model->load();
        $this->output('Hello world from add-on. Name from model: '.$this->model['name']);
    }
}
