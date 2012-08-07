<?php
namespace tree;
class TreeView extends \AbstractView {
    public $use_template = "tree";
    public $current_class = "current";
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
    function preRender($obj = null, $enclosure = null, $parent_ref = null){
        if (!$enclosure){
            $enclosure = $this;
        }
        $out = $enclosure->add("View", null, null, array($this->use_template, "_top"));
        $item_t = $out->template->cloneRegion("item");
        $out->template->set("item", "");
        $is_branch_current = false;
        foreach ($this->tree[$parent_ref] as $root => $node){
            $item = $out->add("View", null, "item");
            $item->template = clone $item_t;
            $item->template->trySet($this->items[$node]);
            $is_item_current = false;
            if (isset($this->items[$node]["current"])){
                if ($this->items[$node]["current"]){
                    $is_item_current = true;
                }
            }
            if (isset($this->tree[$node])){
                $is_sub_current = $this->preRender(null, $item, $node);
                if ($is_sub_current){
                    $is_item_current = true;
                }
            }
            if ($is_item_current){
                $item->template->set("class", $this->current_class);
                $is_branch_current = true;
            }
        }
        return $is_branch_current;
    }
    function defaultTemplate(){
        return array($this->use_template);
    }
}
