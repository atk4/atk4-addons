<?php
class CRUD_Export extends CRUD {
    public $export_fields;
	protected function _getExportFilename(){
		return 'export.csv';
	}
    function setExportFields($f, $type="all"){
        $this->export_fields[$type] = $f;
    }
    function setModel($a, $b =null,$c=null){
        $r = parent::setModel($a,$b,$c);
        if ($this->grid){
            $this->grid->addButton("Export CSV")->js("click")->redirect($this->api->url(null,
                            array("export_csv" => $this->grid->name)));
            $this->grid->addButton("Export PDF")->js("click")->redirect($this->api->url(null,
                            array("export_pdf" => $this->grid->name)));
            $this->grid->addButton("Export XLS")->js("click")->redirect($this->api->url(null,
                            array("export_xls" => $this->grid->name)));

            if ($_GET["export_csv"] == $this->grid->name){
                $this->preFetchData("csv");
                /* format csv, export */
                $export = $this->add("Export");
                $data = $export->getDataFromMVCGrid($this->grid);
                $export->setHeaderModel($this->grid->getController()->getModel());
                $export->convertToCSV($data);
                $export->exportCSV("export.csv");
            }
            if ($_GET["export_pdf"] == $this->grid->name){
                /* format csv, export */
                $this->preFetchData("pdf");
                $export = $this->add("Export");
                $data = $export->getDataFromMVCGrid($this->grid);
                $export->setHeaderModel($this->grid->getController()->getModel());
                $export->convertToPDF($data);
                $export->exportPDF("export.pdf");
            }
            if ($_GET["export_xls"] == $this->grid->name){
                $this->preFetchData("xls");
                /* format csv, export */
                $export = $this->add("Export");
                $data = $export->getDataFromMVCGrid($this->grid);
                $export->setHeaderModel($this->grid->getController()->getModel());
                $export->setConverterTemplate("export/xls");
                $export->convertToXLS($data);
                $export->exportXLS("export.xls");
            }
        }
        return $r;
    }
    function preFetchData($type){
        if (isset($this->export_fields[$type])){
            $f = $this->export_fields[$type];
        } else if (isset($this->export_fields["all"])){
            $f = $this->export_fields["all"];
        }else $f=null;
        if ($f){
            $this->grid->getController()->getModel()->setActualFields($f);
        }
    }
}   
