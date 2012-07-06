<?php

namespace misc;
abstract class Export_Parser_Generic extends \AbstractController {
    public $output = "";
    public $output_type = "text/csv";
    public $output_disposition = "attachment";
    public $output_filename = "export.csv";
    public $debug = false;
    public $button_label = "Export Type";
    public $limit = false;
    function init(){
        parent::init();
        $this->api->addHook("pre-render-output", array($this, "export"));
        $this->addHook("output", array($this, "output"));
        $o = $this->owner->owner;
        if ($o instanceof \Grid){
            $this->addGridButton($o);
        } else if ($o instanceof \Crud){
            if ($o->grid){
                $this->addGridButton($o->grid);
            }
        }
    }
    function addGridButton($o){
        $this->button = $o->addButton($this->button_label);
        $this->button->js("click")->univ()->location($this->api->url(null, array($this->button->name => "1")));
    }
    abstract function parse($captions, $data);
    function export($api){
        if (isset($_GET[$this->button->name])){
            if (!$this->limit){
                $this->owner->owner->dq->del("limit");
            }
            $this->owner->export();
            $this->parse($this->owner->captions, $this->owner->data);
        }
    }
    function setOutput($type=null, $disposition=null, $filename=null){
        if ($type){
            $this->output_type = $type;
        }
        if ($disposition){
            $this->output_disposition = $disposition; // inline or attachment
        }
        if ($filename){
            $this->output_filename = $filename;
        }
    }
    function output(){
        if ($this->debug){
            echo "Type: " . $this->output_type . "<br />";
            echo "Disposition: " . $this->output_disposition . "<br />";
            echo "Filename: " . $this->output_filename . "<br />";
            echo "Len: " . strlen($this->output). "<br />";
            print $this->output;
            exit;
        }
        header("Content-type: " . $this->output_type);
        header("Content-disposition: " . $this->output_disposition . "; filename=\"" . $this->output_filename . "\"");
        header("Content-Length: " . strlen($this->output));
        print $this->output;
        exit;
    }
}
