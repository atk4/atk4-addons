<?php

class MVCGrid_Export extends MVCGrid {
    function init(){
        parent::init();
        $this->add_button = $this->addButton("Add");
        $this->add_button->js('click')->univ()->frameURL("Add new", $this->api->getDestinationURL("./add"));
        $this->addButton("Export CSV")->js("click")->univ()->redirect($this->api->getDestinationURL(null,
                        array("export_csv" => $this->name)));
        $this->addButton("Export PDF")->js("click")->univ()->redirect($this->api->getDestinationURL(null,
                        array("export_pdf" => $this->name)));

        $this->addButton("Export XLS")->js("click")->univ()->redirect($this->api->getDestinationURL(null,
                        array("export_xls" => $this->name)));
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
