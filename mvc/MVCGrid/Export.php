<?php

class MVCGrid_Export extends MVCGrid {
    public $export_csv = true;
    public $export_xls = true;
    public $export_pdf = true;
    public $can_add = true;
    function init(){
        parent::init();
        $this->api->addHook("pre-render", array($this, "add_buttons"));
    }
    function add_buttons(){
        if ($this->can_add){
            $this->add_button = $this->addButton("Add");
            $this->add_button->js('click')->univ()->frameURL("Add new", $this->api->url("./add"));
        }
        if ($this->export_csv){
            $this->addButton("Export CSV")->js("click")->univ()->redirect($this->api->url(null,
                        array("export_csv" => $this->name)));
        }
        if ($this->export_pdf){
            $this->addButton("Export PDF")->js("click")->univ()->redirect($this->api->url(null,
                        array("export_pdf" => $this->name)));
        }
        if ($this->export_xls){
            $this->addButton("Export XLS")->js("click")->univ()->redirect($this->api->url(null,
                        array("export_xls" => $this->name)));
        }
        //$this->js(true)->univ()->ajaxifyLinks();
    }
    function setModel($a,$b=null){
        $m = parent::setModel($a,$b);
        if ($_GET["export_csv"] == $this->name){
            /* format csv, export */
            $export = $this->add("Export");
            $data = $export->getDataFromMVCGrid($this);
            $export->setHeaderModel($a);
            $export->convertToCSV($data);
            $export->exportCSV("export.csv");
        }
        if ($_GET["export_pdf"] == $this->name){
            /* format csv, export */
            $export = $this->add("Export");
            $data = $export->getDataFromMVCGrid($this);
            $export->setHeaderModel($a);
            $export->convertToPDF($data);
            $export->exportPDF("export.pdf");
        }
        if ($_GET["export_xls"] == $this->name){
            /* format csv, export */
            $export = $this->add("Export");
            $data = $export->getDataFromMVCGrid($this);
            $export->setHeaderModel($a);
            $export->setConverterTemplate("export/xls");
            $export->convertToXLS($data);
            $export->exportXLS("export.xls");
        }
		$this->addColumn('delete','delete');
        $this->owner->js('reload',$this->js()->reload());
        return $m;
    }
}
