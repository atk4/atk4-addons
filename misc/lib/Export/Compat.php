<?php

class Export_Compat extends AbstractObject {

	public $csv_column_separator = ",";
	public $csv_line_separator = "\n";
    public $csv_show_header = true;
    private $__headerModel;
	public function convertArrToCSVLine($arr) {
		$line = array();
		foreach ($arr as $v) {
			$line[] = is_array($v) ? $this->convertArrToCSVLine($v) : '"' . str_replace(Array('"', "\r\n", "\n",'&euro;'),
                    Array('""', "", "",''), $v) . '"';
		}
		return implode($this->csv_column_separator, $line);
	}

	public function convertArrToCSV($arr) {
		$lines = array();
        $is_firstRow=false;
		foreach ($arr as $v) {
			if (!$is_firstRow && $this->csvShowHeader()){
				$lines[] = $this->convertArrToCSVLine($this->csvGetHeader($v));
			}
			$lines[] = $this->convertArrToCSVLine($v);
			$is_firstRow = true;
		}
		return implode($this->csv_line_separator, $lines);
	}
    public function convertToCSV($data){
        $this->csv = $this->convertArrToCSV($data);
    }
    public function exportCSV($filename){
        $this->_export("text/csv", $this->csv, $filename);
    }
    public function csvShowHeader(){
		return $this->csv_show_header;
	}
	public function csvGetHeader($arr){
		$a = array_keys($arr);
		foreach ($a as $_a){
			$b[] = $this->__getHeaderModel()->getField($_a)->caption();
		}
		return $b;
	}
	public function pdfGetHeader($arr){
        if (!$arr){
            return;
        }
		$a = array_keys($arr);
		foreach ($a as $_a){
            if (!is_object($this->__getHeaderModel())){
                $b[$_a] = $_a;
            } else {
                $b[$_a] = $this->__getHeaderModel()->getField($_a)->caption();
            }
		}
		return $b;
	}
    protected function _export($type, $data, $filename){
        header('Content-type: ' . $type);
        header('Content-disposition: attachment;filename="' . $filename  . '"');
        header('Content-length: ' . strlen($data));
        print $data;
        exit;
    }
    /* magic */
    public function setConverterTemplate($template, $tag = null){
        $this->converter_template_name = $template;
        $this->converter_template_tag = $tag;
        $this->converter_template = $this->add("SMLite")->loadTemplate($template);
    }
    public function convertViaLister($data){
        $l = $this->add("Export_Lister_XLS", null, null, array($this->converter_template_name?$this->converter_template_name:"export/xls",
                    $this->converter_template_tag?$this->converter_template_tag:"rows"));
        $l->setStaticSource($data)->render();
        $this->converter_template->trySet("q", "?");
        $this->converter_template->trySet("date", date("d/m/Y"));
        return $this->converter_template->set("rows", $l->get())->render();
    }
    private function convertParam($arr){
        $out = "";
        $o = array();
        foreach ($arr as $k => $v){
            $out = $k . "=\"";
            if (is_array($v)){
                foreach ($v as $ck => $cv){
                    $out .= $ck . ":$cv;";
                }
            } else {
                $out .= $v;
            }
            $out .= "\"";
            $o[] = $out;
        }
        return " " . implode(" ", $o);
    }
    public function convertArrToHTML($data){
        $out = "";
        foreach ($data as $k=>$row){
            if (is_array($row)){
                $out .= "<tr>";
                foreach ($row as $ck=>$cell){
                    if ($this->data_params[$k][$ck]){
                        $tdparams = $this->convertParam($this->data_params[$k][$ck]);
                    } else {
                        $tdparams = "";
                    }
                    $out .= "<td$tdparams>$cell</td>";
                }
                $out .= "</tr>";
            }
        }
        return $out;
    }
    public function convertToXLS($data){
        if (is_object($data)){
            $data = $this->modelToArr($data);
        } else {
            $k = array_keys($data);
            $h = $this->pdfGetHeader($data[$k[0]]);
            $data = array_merge(array($h), $data);
        }
        $this->xls = $this->convertViaLister($data);
    }
    public function modelToArr($model){
        $d = $model->get();
        $t = array();
        foreach ($d as $k => $v){
            $c = $model->getField($k)->caption();
            $t[] = array("k" => $c?$c:$k, "v" => "$v");
        }
        return $t;
    }
    public function exportXLS($filename){
        $this->_export("application/vnd.ms-excel", $this->xls, $filename);
    }
    public function convertToPDF($data){
        if (is_object($data)){
            $data = $this->convertViaLister($this->modelToArr($data));
        } else {
            $k = array_keys($data);
            $h = $this->pdfGetHeader($data[$k[0]]);
            $data = array($h)+ $data;
            $data = $this->convertArrToHTML($data);
            if (!$this->converter_template){
                $this->setConverterTemplate("export/pdf");
            }
            $this->converter_template->trySet("q", "?");
            $this->converter_template->trySet("date", date("m/d/Y"));
            $this->converter_template->set("rows", $data);
            $data = $this->converter_template->render();
        }
        $tmp = $this->api->getConfig("tmp_dir", "/tmp/");
        $in = $tmp . "/i" . session_id() . ".html";
        $fd = fopen($in, "w");
        fputs($fd, $data);
        fclose($fd);

        $out = $tmp . "/o" . session_id() . ".pdf";
        exec($e=$this->api->getConfig("pdf_converter") . " " . $in . " " . $out);
        $this->pdf = file_get_contents($out);
        unlink($in);
        unlink($out);
    }
    public function exportPDF($filename){
        $this->_export("application/pdf", $this->pdf, $filename);
    }
	public function setHeaderModel($m){
		$this->__headerModel = $m;
	}
	private function __getHeaderModel(){
		return $this->__headerModel;
	}
    public function getDataFromMVCGrid($grid,$remove_system_fields=true, $format=true){
        $data = $grid->getController()->getRows();
        if (!$format){
            return $data;
        }
        foreach ($data as $k => $r){
            $grid->current_row = $r;
            $grid->formatRow();
            foreach ($r as $rk => $rv){
                /* we should leave boolean as is */
                if (!in_array($rv, array("Y","N"))){
                    $new[$r["id"]][$rk] = $grid->current_row[$rk];
                } else {
                    $new[$r["id"]][$rk] = $rv;

                }
            }
        }
        $data = $new;
        if ($remove_system_fields){
            $sf = $grid->getController()->getSystemFields();
            foreach ($data as $k => $row){
                foreach ($sf as $sk=>$f){
                    unset($data[$k][$sk]);
                }
            }
        }
        $this->data = $data;
        $this->data_params = $grid->getAllTDParams();
        return $data;
    }
}

class Export_Lister_XLS extends Lister {
    public $v;
	public $safe_html_output=false;  // do htmlspecialchars by default when formatting rows
    function output($v){
        $this->v .= $v;
    }
    function get(){
        return $this->v;
    }
    function init(){
        parent::init();
        $this->l = $this->add("Export_Lister", null, "aux", array("export/xls_cell"));
        unset($this->elements[$this->l->name]);
        $this->template->set("aux", "");
    }
    function formatRow(){
        $r = array();
        foreach ($this->current_row as $k => $v){
            $r[$k] = array("k" => $v);
        }
        $this->l->v = "";
        $this->l->setStaticSource($r)->render();
        $this->current_row["cells"] = $this->l->get();
        parent::formatRow();
    }
}

class Export_Lister extends Lister {
    public $v;
	public $safe_html_output=false;  // do htmlspecialchars by default when formatting rows
    function output($v){
        $this->v .= $v;
    }
    function get(){
        return $this->v;
    }
}
