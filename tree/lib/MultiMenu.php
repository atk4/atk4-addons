<?php
namespace tree;
class MultiMenu extends TreeView {
    public $use_template = "treemenu";
    function addMenuItem($page, $title, $ref = null, $aux=array()){
        $current = false;
        $p = (string)$this->api->url($page);
        $current = $this->isCurrent($page);
        return $this->addItem(array("page" => $p, "title" => $title, "current" => $current)+$aux, $ref);
    }
    function defaultTemplate(){
        return array("multimenu");
    }
}
