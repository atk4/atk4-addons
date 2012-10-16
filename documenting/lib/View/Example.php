<?php
namespace documenting;
class View_Example extends \View {
    function init(){
        parent::init();
        $this->api->requires('atk','4.2');
    }
    function setTitle($title){
        $this->template->trySet('title',$title);
        return $this;
    }
    function set($code,$silent=false){

        $short=$this->short_name;

        if($_GET['cut']==$short)$this->api->example_cut=$this;

        $this->template->trySet('short',$short);

        $brush='Php';
        if($this->template->is_set('brush')){
            $brush=$this->template->get('brush');
            $this->template->del('brush');
        }


        $this->template->set('Code',$code);

        if(!@$this->api->highlighter_initialized){

            $this->api->jui->addStaticInclude('syntaxhighlighter/scripts/shCore');
            $this->api->jui->addStaticInclude('syntaxhighlighter/scripts/shBrush'.$brush);
            $this->api->jui->addStaticInclude('shJQuery');
            $this->api->jui->addStaticStylesheet('shCoreDefault','.css','js');
            $this->api->jui->addStaticStylesheet('documenting');
            $this->api->highlighter_initialized=true;
        }

        $this->js(true)->_selector('#'.$this->name.'_ex')->syntaxhighlighter(array('lang'=>$brush));

        if($silent=='noexec'){
            $this->template->del('has_demo');
            return;
        }


        if($_GET['cut'])return;

        $res=$this->executeDemo($code);

        if($silent===true){
            if($res)$res->destroy();
            $this->template->del('has_demo');
        }
        return $this;
    }
    function executeDemo($code){
        $page=$this->add('View',null,'Demo');
        $page->template->setHTML('Content','<b>The example was executed successfully, but did not produce any output</b>');
        eval($code);
        return $page;
    }
    function defaultTemplate(){
        $l=$this->api->locate('addons','documenting','location');

        $this->api->pathfinder->addLocation($this->api->locate('addons','documenting'),array(
            'template'=>'templates',
            'css'=>'templates/css',
            'js'=>'js'
        ))->setParent($l);



        if($_GET['cut'])return array('view/example_cut');
        return array('view/example');
    }
}
