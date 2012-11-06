<?php
namespace dynamic_model;
/* Creates hierarchy of models */
class Page_DynamicAdmin extends \Page {
    function setModel($m){
        $m=parent::setModel($m);
        $this->api->stickyGET($this->name);
        $this->api->stickyGET('start_id');

        if($_GET['start_id'])$m->load($_GET['start_id']);

        if($_GET[$this->name]){
            $m=$m->ref($_GET[$this->name]);
        }

        $m->add('dynamic_model/Controller_AutoCreator');

        $cr=$this->_addViews($this,$m);
        if($cr->grid){
            if($cr->grid->addButton('Dump all data')->isClicked()){
            }
        }
    }
    function _addViews($v,$m){
        $this->crud=$cr=$v->add('CRUD','c'.str_replace('/','_',$_GET[$this->name]));

        $m=$cr->setModel($m);

        foreach($m->elements as $key=>$el)if($el instanceof \SQL_Many){
            if($_GET['expander']=='x_'.$key){
                $this->api->redirect($x=$this->drillURL($key)->set('start_id',$_GET['id']));
            }
        }



        // traverse hasMany relations
        if($cr->grid){
            if($m->_references)foreach($m->_references as $key=>$misc){
                $cr->grid->addColumn('button',$key);
            }

            foreach($m->elements as $key=>$el)if($el instanceof \SQL_Many){
                $cr->grid->addColumn('expander','x_'.$key,array('descr'=>$key.'s','page'=>$this->api->url()));
            }

        }elseif($cr->form){
            // add buttons for traversing inside
            foreach($m->elements as $name=>$field)if($field instanceof \Field_Reference){
                $n=$cr->form->getElement($name);
                $b=$n->addButton('Edit');
                if($b->isClicked()){
                    $this->js()->univ()->frameURL('Edit '.$name,$this->drillURL($name),array(
                        //'close'=> $cr->form->js()->_enclose()->atk4_form('reloadField',$name))
                        'beforeClose'=> $cr->form->js()->_enclose()->reload())
                    )->execute();
                }
                if($_GET[$b->name.'_'=='cc']){

                    // edit recursively
                    $this->api->stickyGET($b->name.'_');
                    $this->api->cut($this->_addViews($cr->form));
                }
            }
        }
        return $cr;
    }
    function drillURL($ref){
        $x=$_GET[$this->name];
        if($x)$x.='/';$x.=$ref;
        return $this->api->url(null,array($this->name=>$ref,$this->crud->name=>false));
    }
}
