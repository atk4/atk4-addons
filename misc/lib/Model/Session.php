<?php

class Model_Session extends SQL_Model {
    public $entity_code='session';
    public $table_alias='se';
    public $debug=false;
    private $session_name;
    private $session_path;
    function init(){
        parent::init();
        $this->addField("session_id");
        $this->addField("data")->type("text");
        $this->addField("timestamp")->type("text");
    }
    function open($path, $session_name){
        $this->session_name = $session_name;
        $this->session_path = $path;
        return true;
    }
    function close(){
        $this->update(array("timestamp" => time()));
        return true;
    }
    function read($sid){
        $this->getBySid($sid);
        return $this->get("data");
    }
    function write($sid, $data){
        $this->getBySid($sid);
        $this->update(array("data" => $data, "timestamp" => time()));
        return true;
    }
    function destroy($sid){
        $this->getBySid($sid);
        $this->delete();
    }
    function gc($maxlifetime){
        $maxlifetime = (int) $maxlifetime;
        $this->dsql("gc")->where("timestamp + $maxlifetime < " . time())->do_delete();
    }
    //
    function getBySid($sid){
        if (!($d=$this->getBy("session_id", $this->session_name . ":" . $sid))){
            $this->update(array("session_id" => $this->session_name . ":" . $sid, "timestamp" => time()));
        } else {
            $this->tryLoad($d["id"]);
        }
        $this->sid = $sid;
    }
}
