<?php

class MVCForm_Export extends MVCForm {
    function init(){
        parent::init();
        $this->api->addHook("pre-render", array($this, "addExportButtons"));
    }
    function addExportButtons(){
        if ($_GET["export_xls"] == $this->name){
            $this->exportToXLS();   
        }
        if ($_GET["export_pdf"] == $this->name){
            $this->exportToPDF();   
        }
        $this->api->stickyGET("id");
        $this->addButton("Export (XLS)", "export_xls")->js("click")->redirect($this->api->url(null, array("export_xls" => $this->name)));
        $this->addButton("Export (PDF)", "export_pdf")->js("click")->redirect($this->api->url(null, array("export_pdf" => $this->name)));
    }
    function exportToXLS(){
        $export = $this->add("Export");
        /* magic with lister */
        $export->setConverterTemplate("export/xls");
        $export->convertToXLS($this->getController());
        $export->exportXLS("export_" . time() . ".xls");
    }
    function exportToPDF(){
        $export = $this->add("Export");
        $export->setConverterTemplate("export/pdf");
        $export->convertToPDF($this->getController());
        $export->exportPDF("export_" . time() . ".pdf");
    }
}
