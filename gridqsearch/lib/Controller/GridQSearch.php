<?php
namespace gridqsearch;
class Controller_GridQSearch extends \AbstractController {
    function init(){
        parent::init();

        $this->api->requires('atk','4.2');

        $symbols=$this->owner->model->_dsql()->field($this->owner->model->_dsql()->expr("DISTINCT LEFT($this->field,1) as symbol"))->order($this->field);
        
        $ul=$this->api->add('View',null,'Content')->setElement('ul')->addClass('gridqsearch');
        
        $li = $ul->add('View','c')->setElement('li')->addClass('refresh')->addStyle('cursor','pointer');
        $li->add('View')->setElement('span')->addClass('qsearch-refresh')->set("clear");
        $li->js('click',array(
                $this->owner->js()->reload(array(
                    'filter'=>'clear',
                )),
        ));

        foreach ($symbols as $symbol) {
            $li = $ul->add('View','c'.$this->count++)->setElement('li')->addClass('ui-corner-all')->addStyle('cursor','pointer');
            $li->add('View')->setElement('span')->addClass('qsearch-value')->set($symbol['symbol']);
            $li->js('click',array(
                    $this->owner->js()->reload(array(
                        'filter'=>$li->js()->text(),
                    )),
            ));
        }
        
        if ($_GET['filter']!=''){
            if ($_GET['filter']=='clear'){
                $this->api->forget('filter');
            }else{
                $this->api->memorize('filter',$_GET['filter']);
            }
        }
        
        if ($this->api->recall('filter')!=''){
            $this->owner->model->addCondition($this->field,'like',trim($this->api->recall('filter')).'%');
        }
    }

}

