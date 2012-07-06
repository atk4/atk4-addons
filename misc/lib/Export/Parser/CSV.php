<?php

namespace misc;

class Export_Parser_CSV extends Export_Parser_Generic {
    public $column_separator = ",";
    public $row_separator = "\n";
    public $button_label = "Export CSV";
    function parse($captions, $data){
        $data = array_merge(array($captions), $data);
        foreach ($data as $row){
            $cols = array();
            foreach ($row as $col){
                $cols[] = "\"" . preg_replace("/\"/", "\"\"", $col) . "\"";
            }
            if ($this->output){
                $this->output .= $this->row_separator;
            }
            $this->output .= implode($this->column_separator, $cols);
        }
        $this->hook("output");
    }
}
