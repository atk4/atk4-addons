<?php
namespace tree;
class TreeView extends \AbstractView {
    public $use_template = "tree";
    public $current_class = "current";
    public $default_class = "";
    public $is_current_checker = false;
    function init(){
        parent::init();

        $this->api->addHook("pre-render", array($this, "preRender"));
    }
    function addItem($item_data, $ref = null){
        $this->items[] = $item_data;
        $new_ref = count($this->items) - 1;
        $this->tree[$ref][] = $new_ref;
        return $new_ref;
    }
    function preRender($obj = null, $enclosure = null, $parent_ref = null, $depth = 0){
        if (!$enclosure){
            $enclosure = $this;
        }
        if (isset($this->items[$parent_ref]["template"])){
            $t = $this->items[$parent_ref]["template"];
        } else if (is_array($this->use_template)){
            $t = $this->use_template[$depth];
        } else {
            $t = $this->use_template;
        }
        $out = $enclosure->add("View", null, null, array($t, "_top"));
        $out->template->trySet($this->items[$parent_ref]);
        $item_t = $out->template->cloneRegion("item");
        $out->template->del("item");
        $is_branch_current = false;
        foreach ($this->tree[$parent_ref] as $root => $node){
            $item = $out->add("View", null, "item");
            $item->template = clone $item_t;
            $item->template->trySet($this->items[$node]);
            $is_item_current = false;
            $force_not_current = false;
            if (isset($this->items[$node]["current"])){
                if ($this->items[$node]["current"]){
                    $is_item_current = true;
                }
            }
            if (isset($this->items[$node]["recursive_current"])){
                $force_not_current = !$this->items[$node]["recursive_current"];
            }
            if (isset($this->tree[$node])){
                $is_sub_current = $this->preRender(null, $item, $node, $depth + 1);
                if ($is_sub_current){
                    $is_item_current = true;
                }
            }
            if ($is_item_current){
                if (!$force_not_current){
                    $item->template->trySet("class", $item->template->get("class"). " ".$this->current_class);
                }
                $is_branch_current = true;
            }
        }
        return $is_branch_current;
    }
    function defaultTemplate(){
        return array($this->use_template);
    }
    function initializeTemplate(){
        $l=$this->api->locate('addons',__NAMESPACE__,'location');
        $lp=$this->api->locate('addons',__NAMESPACE__);

        $this->api->addLocation($lp,array(
            'template'=>'templates/default',
            )
        )
        ->setParent($l);
        return parent::initializeTemplate();
    }
    function setCurrentChecker($callable){
        $this->is_current_checker = $callable;
    }
    function isCurrent($what){
        if ($callable = $this->is_current_checker){
            if (is_array($callable)){
                list($o,$m) = $callable;
                return $o->$m($what);
            } else {
                return $callable($what);
            }
        }
        $p = (string)$this->api->url($page);
        if ((string)$this->api->url(null) == $p){
            $current = true;
        } else {
            return false;
        }
    }
}
