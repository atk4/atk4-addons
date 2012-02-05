<?php

class Page_ModelGenerator Extends Page {
    private $capitalize = true;
    private $postfix = "Core";
    protected $skip_pages = false;

    function init(){
        parent::init();
        /* dirty. will clean up later, but working well */
        $this->add("Text")->set("Welcome. This is Model Creator Kit. It will use mysql database to create models for you");
        $c=$this->add('Columns');
        $f=$c->addColumn('50%')->add('Form');
        $l=$this->api->locatePath('php','Model');
        list($tables, $fields) = $this->findModels();
        $tables = array("-1" => "All") + $tables;
        $f->addField('dropdown','table')->setValueList($tables);
        $f->addButton('create')->js("click", $f->js()->submit());
        $r=$c->addColumn('50%');
        $output_object=$r->add('HtmlElement');
        $create_object=$r->add('HtmlElement')->setElement('pre');
        if($f->isSubmitted()){
            $code = "";
            if ($f->get("table") == -1){
                foreach ($tables as $k => $table){
                    if ($k > -1){
                        $code .= htmlspecialchars($this->generateModel($table, $fields[$table], $tables));

                    }
                }
            } else {
                $code = $this->generateModel($tables[$f->get("table")], $fields[$tables[$f->get("table")]], $tables);
            }
            $create_object->js()->html($code)->execute();
            return;
        }
    
    }
    function resolveFieldType($type){
        $cast = array(
            "int" => "int(11)",
            "money" => "decimal(10,2)",
            "datetime" => "datetime",
            "date" => "date",
            "string" => "varchar(255)",
            "text" => "text",
            "boolean" => "enum('Y','N')",
        );
        $ret = array_search($type, $cast);
        return $ret?$ret:"string";
    }
    function resolveFieldName($field){
        return $field->name();
    }
    function findModels($dir, &$models, $prefix = null){
        $r = $this->api->db->getAll("show tables");
        $tables = array();
        foreach ($r as $row){
            $tables[] = $row[0];
        }
        if ($tables){
            foreach ($tables as $table){
                $fields[$table] = $this->api->db->getAllHash("desc `$table`");
            }
        }
        return array($tables, $fields);
    }
    function generateModel($table, $fields, $tables){
        $ignore = $this->api->getConfig("mg/ignore", array());
        if ($ignore){
            foreach ($ignore as $pattern){
                if (preg_match("/$pattern/", $table)){
                    return "Ignoring $table ($pattern)\n";
                }
            }
        }
        $v = $this->add("View", null, null, array("view/model"));
        $v->template->set("php", "<?php");
        $v->template->set("class_name", "Model_" . $this->getModelByTable($table) ."_" . $this->postfi . $this->postfix);
        $v->template->set("entity_code", $table);
        $v->template->set("extends", "Model_Table");
        $v->template->set("table_alias", "al_" . substr($table, 0, 2));
        $l = $v->add("Lister", null, "field_lister", array("view/model", "field_lister"));
        $l->safe_html_output = false;
        foreach ($fields as $k => $field){
            if ($field["Field"] == "id"){
                unset($fields[$k]);
                continue;
            }
            $fields[$k]["datatype"] = $this->resolveFieldType($field["Type"]);
            if ((array_search(substr($field["Field"], 0, -3), $tables) !== false) && (substr($field["Field"], -2) == "id")){
                $fields[$k]["aux"] .= "->refModel(\"Model_" . $this->getModelByTable(substr($field["Field"], 0, -3)) ."\")";
            } else {
                $fields[$k]["aux"] .= "";
            }
            if ($field["Field"] == "deleted"){
                $fields[$k]["aux"] .= "->system(true)->visible(false)";
            }
        }
        $l->setStaticSource($fields);
        $m = (string)$v;
        $lbase = "lib/Model";
        $pbase = "page";
        $chunks = explode("_", $table);
        $model_name = $this->uc($chunks[count($chunks)-1]);
        $page_name = strtolower($this->uc($chunks[count($chunks)-1]));
        $auto_model_name = $this->uc($model_name) . "_" . $this->postfix;
        foreach ($chunks as $chunk){
            $chunk = $this->uc($chunk);
            /* create model dir */
            $dir=$lbase ."/". $chunk;
            if (!file_exists($dir)){
                $out .= "Created dir $dir\n";
                mkdir($dir);
            }
            $lbase = $lbase ."/" . $chunk;
            /* create page dir */
            if (!$this->skip_pages){
                if ($model_name != $chunk){
                    $dir = $pbase ."/". strtolower($chunk);
                    if (!file_exists($dir)){
                        $out .= "Created dir $dir\n";
                        mkdir($dir);
                    }
                    $pbase = $pbase ."/" . strtolower($chunk);
                }
            }
        }
        $fid = fopen($file=$lbase . "/" . $this->postfix . ".php", "w");
        $out .= "Created $file\n";
        fputs($fid, $m);
        fclose($fid);
        if (!file_exists($file=$lbase . ".php")){
            $out .= "Created $file\n";
            $v = $this->add("View", null, null, array("view/model_core"));
            $v->template->set("php", "<?php");
            $v->template->set("class_name", "Model_" . $this->getModelByTable($table));
            $v->template->set("extends", "Model_" . $this->getModelByTable($table) ."_" . $this->postfix);
            $fid = fopen($lbase . ".php", "w");
            fputs($fid, (string)$v);
            fclose($fid);
        }
        if (!$this->skip_pages){
            if (!file_exists($file=$pbase."/".$page_name. ".php")){
                $out .= "Created $file\n";
                $v = $this->add("View", null, null, array("view/page"));
                $v->template->set("model", $this->getModelByTable($table));
                $v->template->set("pmodel", strtolower($table));
                $v->template->set("php", "<?php");

                $fid = fopen($pbase."/" . $page_name . ".php", "w");
                fputs($fid, (string)$v);
                fclose($fid);
            }
        }
        return $out;
    }
    function getModelByTable($table){
        if (!$this->capitalize){
            return $table;
        }
        $table=str_replace('_',' ',$table);
        $table=ucwords($table);
        $table=str_replace(' ','_',$table);
        return $table;
    }
    function uc($p){
        if (!$this->capitalize){
            return $p;
        }
        return ucfirst($p);
    }
}
