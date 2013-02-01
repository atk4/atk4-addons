<?php
namespace gridorder;
class Controller_GridOrder extends \AbstractController {
    public $model;

    function init(){
        parent::init();

        // requirements
        $this->api->requires('atk','4.2.3');

        // add locations
        $l=$this->api->locate('addons',__NAMESPACE__,'location');
        $addon = $this->api->locate('addons',__NAMESPACE__);
        $this->api->pathfinder->addLocation($addon,array(
            'template'=>'templates'
        ))->setParent($l);

        // add button
        $this->owner->addButton('Re-order records')
            ->js('click')->univ()->frameURL('Re-order records',
                    $this->api->url(null,
                        array($this->name=>'activate')),array('width'=>'500px'));

        // save model
        $this->model = $this->owner->model;
        if(!$this->model->hasElement('ord')){
            $this->model->addField('ord')->system(true);
        }
        $this->model->setOrder('ord');
        
        // open dialog on button click
        if($_GET[$this->name]=='activate'){
            $this->api->stickyGET($this->name);
            $this->initFrame();
        }
    }
    function initFrame(){
        // add view
        $v = $this->owner->owner->add('View',null,$this->owner->spot);
        $this->api->cut($v);
        
        // add lister
        $lister = $v->add('CompleteLister',null,null,array('view/gridorder'));
        $lister->setModel($this->model);
        $lister->js(true)->sortable();

        // add save button and action
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
        $this->model->dsql()
            ->set('ord',$this->model->id_field)
            ->where('ord',null)
            ->do_update();

        // get current order sequence
        $seq = $this->model->dsql()
            ->field($this->model->getElement($this->model->id_field)->getExpr())
            ->field($this->model->getElement('ord'))
            ->get();

        // extract ORDs and sort them
        $ord = array();
        foreach($seq as $key=>$val){
            $ord[] = $val['ord'];
        }
        sort($ord);
        
        // update database each record one by one
        foreach(explode(',',$id_order) as $id){
            $this->model->dsql()
                ->set('ord',array_shift($ord))
                ->where($this->model->id_field,$id)
                ->do_update();
        }
    }
}
