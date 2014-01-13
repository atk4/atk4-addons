<?php

class Page_ModelGenerator Extends Page {
    
    private $capitalize = true;
    private $postfix = "Core";
    protected $skip_pages = true;
    protected $db_name = 'your_database_name';

    function initMainPage(){
        // parent::init();
        /* dirty. will clean up later, but working well */

        // $this->add('View_Error')->set('Disabled');
        // throw $this->exception('','StopInit');

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
            "boolean" => "tinyint(1)",
        );
        $ret = array_search($type, $cast);
        return $ret?$ret:"string";
    }
    function resolveFieldName($field){
        return $field->name();
    }
    function findModels($dir=null, &$models=null, $prefix = null){
        $r = $this->api->db->dsql()->expr('show tables')->get();
        foreach ($r as $row){
            $tables[] = $row['Tables_in_'.$this->db_name];
        }

        // $i=1;
        $fields=array();
        foreach ($tables as $table){
            $fields[$table] = array_merge($this->api->db->dsql()->expr("desc `$table`")->get(),(isset($fields[$table]))?$fields[$table]:array());
            // if($i==1) print_r($fields);
            // $i++;
            foreach($fields[$table] as $field){
                if ((array_search(substr(strtolower($field["Field"]), 0, -3), $tables) !== false) && (substr($field["Field"], -3) == "_id")){
                    
                    $fields[substr(strtolower($field["Field"]), 0, -3)][]=array(
                            'Field'=>$this->getModelByTable($table)."__",
                            'Related_to' => substr(strtolower($field["Field"]), 0, -3)."_id"
                        );
                }
            }
            // echo "<pre>";
            // print_r($fields);
            // echo "</pre>";
            // throw new Exception("Error Processing Request", 1);
            
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
        $v->template->setHTML("php", "<?php");
        $v->template->set("class_name", "Model_" . $this->getModelByTable($table) ."_" . $this->postfix);
        $v->template->set("entity_code", $table);
        $v->template->set("extends", "SQL_Model");
        $v->template->set("table_alias", "al_" . substr($table, 0, 2));
        
        $hol = $v->add("Lister", null, "hasone_lister", array("view/model", "hasone_lister"));
        $one_relation_fields=array();
        
        $l = $v->add("Lister", null, "field_lister", array("view/model", "field_lister"));
        $l->safe_html_output = false;
        
        $hml = $v->add("Lister", null, "hasmany_lister", array("view/model", "hasmany_lister"));
        $many_relation_fields=array();

        foreach ($fields as $k => $field){
            if ($field["Field"] == "id"){
                unset($fields[$k]);
                continue;
            }

            if(substr($field['Field'],-2)=="__"){
                $many_relation_fields[$k]['Field'] = $field['Related_to'];
                $many_relation_fields[$k]['Model'] = $this->getModelByTable(substr($field["Field"], 0, -2)). "_Core";
                $many_relation_fields[$k]['RelationName'] = $this->getModelByTable(substr($field["Field"], 0, -2));
                unset($fields[$k]);
                continue;
            }

            $fields[$k]["type"] = $this->resolveFieldType($field["Type"]);
            if ((array_search(substr(strtolower($field["Field"]), 0, -3), $tables) !== false) && (substr($field["Field"], -3) == "_id")){
                // echo "array_search(".substr(strtolower($field["Field"]), 0, -3).", \$tables) = " . array_search(substr(strtolower($field["Field"]), 0, -3), $tables) . " && substr(".$field["Field"].", -3) = ". substr($field["Field"], -3) . "<br/>";
                $one_relation_fields[$k]['Field'] = $field['Field'];
                $one_relation_fields[$k]['Model'] = $this->getModelByTable(substr($field["Field"], 0, -3))."_Core";
                $one_relation_fields[$k]['RelationName'] = $this->getModelByTable(substr($field["Field"], 0, -3));
                unset($fields[$k]);
                continue;
            } else {
                $fields[$k]["aux"] .= "";
            }
            if ($field["Field"] == "deleted"){
                $fields[$k]["aux"] .= "->system(true)->visible(false)";
            }
        }
        $hml->setStaticSource($many_relation_fields);
        $hol->setStaticSource($one_relation_fields);
        $l->setStaticSource($fields);
        $m = (string)$v->getHTML(true);
        $lbase = "lib/Model";
        $pbase = "page";
        $chunks = explode("_", $table);
        $model_name = $this->uc($chunks[count($chunks)-1]);
        $page_name = strtolower($this->uc($chunks[count($chunks)-1]));
        $auto_model_name = $this->uc($model_name) . "_" . $this->postfix;
        $out="";
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
            $v->template->setHTML("php", "<?php");
            $v->template->set("class_name", "Model_" . $this->getModelByTable($table));
            $v->template->set("extends", "Model_" . $this->getModelByTable($table) ."_" . $this->postfix);
            $fid = fopen($lbase . ".php", "w");
            fputs($fid, (string)$v->getHTML(true));
            fclose($fid);
        }
        if (!$this->skip_pages){
            if (!file_exists($file=$pbase."/".$page_name. ".php")){
                $out .= "Created $file\n";
                $v = $this->add("View", null, null, array("view/page"));
                $v->template->set("model", $this->getModelByTable($table));
                $v->template->set("pmodel", strtolower($table));
                $v->template->setHTML("php", "<?php");

                $fid = fopen($pbase."/" . $page_name . ".php", "w");
                fputs($fid, (string)$v->getHTML(true));
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
