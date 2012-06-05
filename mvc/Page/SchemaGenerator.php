<?php

class Page_SchemaGenerator extends Page {
    private $paths;
    function findModels($dir, &$models, $prefix = null){
        $d=dir($dir);
        $fetch = array();
        while(false !== ($entry=$d->read())){
            if (in_array($entry, array(".", ".."))){
                continue;
            }
            if (is_dir($dir . DIRECTORY_SEPARATOR . $entry)){
                $fetch[] = $entry;
                continue;
            }
            $m=str_replace('.php','',$entry);
            if($m[0]=='.')continue;
            $models[]=$prefix . $m;
        }
        $d->close();
        if ($fetch){
            foreach ($fetch as $entry){
                $this->findModels($dir . DIRECTORY_SEPARATOR .  $entry, $models, $entry . "_");
            }
        }
    }
    function init(){
        parent::init();
        /* dirty. will clean up later, but working well */
        parent::init();
        $c=$this->add('Columns');
        $f=$c->addColumn('50%')->add('Form');
        $l=$this->api->locatePath('php','Model');
        $this->addPath($l);
        $models = array();
        foreach ($this->paths as $path){
            $this->findModels($path, $models);
            $models=array_combine($models,$models);
        }
        $f->addField('dropdown','model')->setValueList($models);
        $f->addField('checkbox','drop');
        $f->addField('checkbox','execute');
        $f->addSubmit('Generate SQL');

        $r=$c->addColumn('50%');
        $output_object=$r->add('HtmlElement');

        $create_object=$r->add('HtmlElement')->setElement('pre');



        if(!$f->isSubmitted()){
            return;
        }
        $model=$f->get('model');
        $drop=$f->get('drop');

        $ptr = $this->add("Model_".$model);
        $fields = $ptr->elements;
        $fieldtypes = array();
        foreach ($fields as $field){
            if ($field instanceof Field){
                if ($field->calculated()){
                    continue;
                }
                list($field_type, $full_field_type) = $this->resolveFieldType($field);
                $field_name = $this->resolveFieldName($field);
                $dbfields[$field_name] = "\t " . $field_name . " " . $full_field_type;
                $fieldtypes[$field_name] = $field_type;
            }
        }
        $output='';
        $create='';
        $table = $ptr->table?$ptr->table:$ptr->entity_code;
        $q = array();
        try {
            $res = $this->api->db->getAllHash("desc $table");
            /* alter table */
            foreach ($res as $field){
                if (isset($dbfields[$field["Field"]])){
                    if ($fieldtypes[$field["Field"]] == $field["Type"]){
                        $output.= "Field " . $field["Field"] . " already in db<br/>\n";
                        unset($dbfields[$field["Field"]]);
                    } else {
                        unset($dbfields[$field["Field"]]);
                        $output.= "<span style=\"color:red\">" . $field["Field"] . " type <b>(" .
                            $field["Type"] . ")</b> differ from model spec: <b>" . $fieldtypes[$field["Field"]] . "</b></span>\n";
                        $q[] = "alter table " . $table . " change " . $field["Field"] . " " . $field["Field"] ." " . $fieldtypes[$field["Field"]] . "\n";
                           
                    }
                } else {
                    $output .= "<span style=\"color:red\"><b>Field " . $field["Field"] . " is in db BUT NOT IN MODEL</b></span>\n";
                        $q[] = "alter table " . $table . " drop " . $field["Field"] . "\n";
                }
            }
            if ($dbfields){
                $fields = implode(",\n", $dbfields);
                $create.="alter table $table add (\n$fields\n);\n";
            }
        } catch (Exception $e){
            $output.= "table does not exist\n";
            /* create table */
            $fields = implode(",\n", $dbfields);
            if ($drop){
                $create.= "drop table if exists $table;\n";
            }
            $create.= "create table $table ($fields);";
        }
        if ($q){
            foreach ($q as $qe){
                $create.= $qe .";\n";
            }
        }

        if($f->get('execute')){
            try {
                $this->api->db->query($create);
                $output.="<br/><font color=green>Executed successfully</font>";
            }catch (SQLException $e){
                $output.="<font color=red>".$e->getMessage()."</font>";
            }
        }

        $this->js(null,array(
                    $create_object->js()->text($create),
                    $output_object->js()->html($output),
                    ))->execute();


    }
    function resolveFieldType($field){
        $cast = array(
            "int" => "int(11)",
            "money" => "decimal(10,2)",
            "reference" => "int(11)",
            "datetime" => "datetime",
            "date" => "date",
            "password" => "varchar(255)",
            "text" => "text",
            "boolean" => "tinyint(1)",
            "default" => "varchar(255)"
        );
        $special = array(
            "id" => "int auto_increment not null primary key"
        );
        $datatype = $field->datatype();
        if (isset($cast[$datatype])){
            $type = $cast[$datatype];
        } else {
            $type = $cast["default"];
        }
        $full_type = $type;
        if (isset($special[$field->short_name])){
            $full_type = $special[$field->short_name];
        }
        return array($type, $full_type);
    }
    function resolveFieldName($field){
        return $field->short_name;
    }
    function addPath($path){
        $this->paths[] = $path;
    }
}
