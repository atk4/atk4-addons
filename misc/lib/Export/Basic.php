<?php

namespace misc;
class Export_Basic extends \AbstractController {
    public $fields = array();
    function init(){
        parent::init();
        $this->api->addHook("pre-render-output", array($this, "export"));
    }
    function setActualFields($fields){
        $this->fields = $fields;
    }
    function export(){
        /* so export will work in the following way:
         * 1) take dq from parent
         * 2) take model from parent 
         * 3) fetch data
         * 4) use export_parser to prepare output required 
         */
        $data = array();
        $keys = null;
        if ($this->fields){
            foreach ($this->owner->dq as $k => $row){
                if (!$keys){
                    $r2 = array();
                    foreach (array_keys($row) as $kk=>$vv){
                        if (in_array($vv, $this->fields)){
                            $r2[] = $vv;
                        }
                    }
                    $keys = $r2;
                }
                $r2 = array();
                foreach ($row as $kk=>$vv){
                    if (in_array($kk, $this->fields)){
                        $r2[$kk] = $vv;
                    }
                }
                $data[] = $r2;
            }
        } else {
            foreach ($this->owner->dq as $k => $row){
                if (!$keys){
                    $keys = array_keys($row);
                }
                $data[] = $row;
            }
        }

        $captions = array();
        if ($keys){
            if ($m=$this->owner->getModel()){
                foreach ($keys as $key){
                    try {
                        if ($o=$m->getField($key)){
                            $captions[$key] = (method_exists($o, "caption") && $o->caption())?$o->caption():$key;
                        } else {
                            $captions[$key] = $key;
                        }
                    } catch (\Exception $e){
                        $captions[$key] = $key;
                    }
                }
            } else {
                foreach ($keys as $key){
                    $captions[$key] = $key;
                } 
            }
        }
        $this->captions = $captions;
        $this->data = $data;
    }
}
