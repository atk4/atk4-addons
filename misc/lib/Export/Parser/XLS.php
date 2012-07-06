<?php

namespace misc;
class Export_Parser_XLS extends Export_Parser_Generic {
    public $button_label = "Export XLS";
    function init(){
        parent::init();
        $this->setOutput("application/vnd.ms-excel", "attachment", "export.xls");
    }
    function parse($captions, $data){
        $data = array_merge(array($captions), $data);
        $this->output = "<table>";
        foreach ($data as $row){
            $cols = array();
            foreach ($row as $col){
                $cols[] = "<td>" . strtr($col, array("<" => "&lt;", ">" => "&gt;")) . "</td>";
            }
            $this->output .= "<tr>" . implode($this->column_separator, $cols) . "</tr>";
        }
        $this->output .= "</table>";
        $this->hook("output");
    }
}
