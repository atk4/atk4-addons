<?php
namespace dynamic_model;
/*
 * This class will read your model structure one model at a time and will store the data inside a PHP file.
 * Unlike database dump, this model relies on on models only and therefore would work across any  Data source.
 */
class Controller_ModelDumper extends \AbstractController {

    public $models_backed_up=array();

    public $file=null;

    function _dump($model){

        if($this->models_backed_up[get_class($model)]++)return true;

        $cnt=$model->count()->getOne();
        $this->ln('// Dumping '.$cnt.' items from model '.get_class($model));

        // Next, check the references
        $this->ln('$m=$a->add(\''.$this->classof($model).'\');');

        foreach($model as $row){
            $this->ln('$m->set('.var_export($row,true).')->saveAndUnload();');
        }
        $this->ln();

    }

    function classof($m){
        return get_class($m);
    }

    function ln($line=''){
        if($this->file)return fputs($this->file,"        ".$line."\n");
        echo("        ".$line."\n");
    }

    function dump($file=null){
        if($file)$this->file=fopen($file,'w');
        $this->_dump($this->model);
        if($file)fclose($this->file);
    }


}
