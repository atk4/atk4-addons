<?php
namespace filestore;
class Field_File extends \Field_Reference {
    public $use_model = 'filestore/File';
    function init(){
        parent::init();
        $this->setModel($this->use_model,'url');
        $this->display(array('form'=>'upload'));

    }
    /*
    function updateSelectQuery($select){
        parent::updateSelectQuery($select);

        $m=$this->getModel();


        $q=$this->owner->_dsql()->dsql();
        $q->table('filestore_file','ffs');
        $vol=$q->join('filestore_volume',null,null,'ffv');
        $q->field($q->expr('concat_ws("/","'.$this->api->pm->base_path.'",ffv.dirname,ffs.filename)'));
        $q->where('ffs.id',$this);

        $select->field($q,$this->short_name.'_url');
    }
    */
    function displaytype($x){return $this;}
    function getModel(){
        if(!$this->model)$this->model=$this->add($this->model_name);
        return $this->model;
    }
}
