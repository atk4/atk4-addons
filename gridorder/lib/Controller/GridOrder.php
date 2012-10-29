<?php
namespace gridorder;
class Controller_GridOrder extends \AbstractController {
    public $model;

    function init(){
        parent::init();

        $this->api->requires('atk','4.2');

        $l=$this->api->locate('addons','gridorder','location');
        $this->api->pathfinder->addLocation($this->api->locate('addons','gridorder'),array(
            'template'=>'templates'
        ))->setParent($l);

        $this->owner->addButton('Re-order records')
            ->js('click')->univ()->frameURL('Re-order records',
                    $this->api->url(null,
                        array($this->name=>'activate')),array('width'=>'500px'));

        $this->owner->dq->order('ord');

        if($_GET[$this->name]=='activate'){
            $this->api->stickyGET($this->name);
            $this->initFrame();
        }
    }
    function initFrame(){
        $v=$this->owner->owner->add('View',null,$this->owner->spot);
        $_GET['cut_object']=$v->name;

        $lister=$v->add('CompleteLister',null,null,array('view/gridorder'));
        $this->model=$m=$this->owner->getModel();

        if(!$m->hasElement('ord')){
            $m->addField('ord')->system(true);
        }

        $lister->setModel($m);
        $lister->dq->order('ord');

        $lister->js(true)->sortable();

        $v->add('Button')->set('Save')->js('click')->univ()->ajaxec(
                array($this->api->url(),
                $this->name.'_order'=>$v->js(null,"\$('#{$lister->name}').children().map(function(){ return $(this).attr('data-id'); }).get().join(',')")
                )
            );
        if(isset($_GET[$this->name.'_order'])){
            $this->processReorder($_GET[$this->name.'_order']);
            $v->js(null,$this->owner->js()->reload(array($this->name=>false)))->univ()->closeDialog()->successMessage('New order saved')->execute();
        }
    }
    function processReorder($id_order){
        // add missing "ord" fields
        $q=$this->model->dsql();
        $q->set('ord',$q->expr('id'));
        $q->where($q->expr('ord is null'));
        $q->do_update();

        $q=$this->model->dsql()->field('id')->field('ord');
        $seq=$q->do_getAllHash();

        // extract ORDs
        $ord=array();
        foreach($seq as $key=>$val){
            $ord[]=$val['ord'];
        }

        sort($ord);
        
        foreach(explode(',',$id_order) as $id){
            $q=$this->model->dsql();
            $q->set('ord',array_shift($ord));
            $q->where('id',$id);
            $q->do_update();
        }
    }
}

