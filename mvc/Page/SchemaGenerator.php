<?php

class Page_SchemaGenerator extends Page {
    function init(){
        /* dirty. will clean up later, but working well */
        $model = $_GET["model"];
        $drop = $_GET["drop"];
        $ptr = $this->add("Model_".$model);
        $fields = $ptr->getAllFields();
        $fieldtypes = array();
        foreach ($fields as $field){
            list($field_type, $full_field_type) = $this->resolveFieldType($field);
            $field_name = $this->resolveFieldName($field);
            $dbfields[$field_name] = "\t " . $field_name . " " . $full_field_type;
            $fieldtypes[$field_name] = $field_type;
        }
        echo "<pre>";
        $table = $ptr->entity_code;
        $q = array();
        try {
            $res = $this->api->db->getAllHash("desc $table");
            /* alter table */
            foreach ($res as $field){
                if (isset($dbfields[$field["Field"]])){
                    if ($fieldtypes[$field["Field"]] == $field["Type"]){
                        echo "Field " . $field["Field"] . " already in db\n";
                        unset($dbfields[$field["Field"]]);
                    } else {
                        unset($dbfields[$field["Field"]]);
                        echo "<span style=\"color:red\">" . $field["Field"] . " type <b>(" .
                            $field["Type"] . ")</b> differ from model spec: <b>" . $fieldtypes[$field["Field"]] . "</b></span>\n";
                        $q[] = "alter table " . $table . " change " . $field["Field"] . " " . $field["Field"] ." " . $fieldtypes[$field["Field"]] . "\n";
                           
                    }
                } else {
                    echo "<span style=\"color:red\"><b>Field " . $field["Field"] . " is in db BUT NOT IN MODEL</b></span>\n";
                }
            }
            echo "\nResulting query:\n";
            if ($dbfields){
                $fields = implode(",\n", $dbfields);
                echo "alter table $table add (\n$fields\n);";
            } else {
                echo "no new fields\n";
            }
        } catch (Exception $e){
            echo "table does not exist";
            /* create table */
            $fields = implode(",\n", $dbfields);
            if ($drop){
                echo "drop table if exists $table;\n";
            }
            echo "create table $table ($fields);";
        }
        if ($q){
            echo "field type change queries:\n";
            foreach ($q as $qe){
                echo $qe ."\n";
            }
        }
        exit;
    }
    function resolveFieldType($field){
        $cast = array(
            "int" => "int(11)",
            "reference" => "int(11)",
            "datetime" => "datetime",
            "date" => "date",
            "password" => "varchar(255)",
            "text" => "text",
            "boolean" => "enum('Y','N')",
            "default" => "varchar(255)"
        );
        $special = array(
            "id" => " auto_increment not null primary key"
        );
        $datatype = $field->datatype();
        if (isset($cast[$datatype])){
            $type = $cast[$datatype];
        } else {
            $type = $cast["default"];
        }
        $full_type = $type;
        if (isset($special[$field->name()])){
            $full_type = $special[$field->name()];
        }
        return array($type, $full_type);
    }
    function resolveFieldName($field){
        return $field->name();
    }
}
