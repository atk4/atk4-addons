<?php
namespace translation;
/* Implementation of basic translation controller using models 

  $api->add('translation/Controller_Basic')
    ->setLocale('de')
    ->setModel('translation/Translation');

    // to be abel to access admin page=translation/admin
  $api->routePages('translation');

 */
class Controller_Basic extends \AbstractController {
    public $lang='en';
    public $cache=null;
    public $debug=false;
    function setModel($m){
        parent::setModel($m);

        $this->api->addHook('localizeString',$this);

        $this->api->translation=$this;
    }
    function setLocale($l){
        $this->lang=$l;
        return $this;
    }
    function localizeString($f,$s){
        if(is_object($s))return $s;
        if(!$s)return $s;
        //$this->model->setActualFields(array('tr_'.$this->lang));
        if(isset($this->cache[$s]))return $this->cache[$s];
        $this->cache[$s]='LOCALE ERROR: '.$s;
        $this->model->tryLoadBy('key',$s);
        if(!$this->model->loaded()){
            $this->model['key']=$s;
            $this->model->save();
            return $this->cache[$s]=($this->debug?'☺':'').$s;
        }

        return $this->cache[$s]=$this->model['tr_'.$this->lang]?:($this->debug?'☺':''.$s);
    }
}
