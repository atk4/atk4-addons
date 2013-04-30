<?php
namespace dynamic_model;
/**
 * Author: Romans Malinovskis (c) Elexu Technologies www.elexutech.com
 * Distributed under MIT and AGPL Licenses
 *
 * Add this controller inside your model and it will make sure than all the 
 * fields defined in your model are also present in your SQL. If any fields
 * are missing, the ALTER table will create them
 *
 * DANGER: Using this controller on production system is VERY discouraged,
 * as it slows down database performance by doing constant "describe's".
 * Also you can loose or damage data in case of improper use.
 */
class Controller_AutoCreator_MySQL extends Controller_AutoCreator_Abstract
{
    // mapping array of field types ATK4 => DB (should define in extended class)
    public $mapping = array(
            "int"       => "integer",
            "money"     => "decimal(10,2)",
            "datetime"  => "datetime",
            "date"      => "date",
            "string"    => "varchar(255)",
            "text"      => "text",
            "boolean"   => "bool",
        );

    // default DB field type (should define in extended class)
    public $default_type = 'varchar(255)';

    // MySQL engine
    public $engine = 'MyISAM'; // MyISAM | INNODB | etc.
    


    // Initialization
    function init()
    {
        parent::init();
    }

    function createTable()
    {
        $t = 'create table if not exists [cr_table] ([field_name] [type_expr] not null PRIMARY KEY [auto_increment]) engine=[engine]';
        $q = $this->db->dsql()->expr($t);
        $q->setCustom('cr_table', $this->table);
        $q->setCustom('field_name', $this->owner->id_field);
        $q->setCustom('engine', $this->engine);
        
        if ($this->is_default_id_field) {
            // default ID field
            $q->setCustom('type_expr', 'integer');
            $q->setCustom('auto_increment', 'auto_increment');
        } else {
            // custom ID field
            $field = $this->owner->getElement($this->owner->id_field);
            $q->setCustom('type_expr', $this->mapFieldType($field->type()));
            $q->setCustom('auto_increment', '');
        }
        
        if ($this->debug) $q->debug();
        $q->execute();
    }

    function dropField($fieldname)
    {
        $t = 'alter table [al_table] drop [field_name]';
        $q = $this->db->dsql()->expr($t);
        $q->setCustom('al_table', $this->table);
        $q->setCustom('field_name', $fieldname);
        if ($this->debug) $q->debug();
        $q->execute();
    }

    function alterField(\Field $field, $add = false)
    {
        // never alter ID field or it can break auto increment
        if ($field->actual_field?:$field->short_name == $this->owner->id_field) {
            return;
        }

        $t = 'alter table [al_table] [method] [field_name] [type_expr]';
        $q = $this->db->dsql()->expr($t);
        $q->setCustom('al_table', $this->table);
        $q->setCustom('method', $add ? 'add' : 'modify');
        $q->setCustom('field_name', $field->actual_field ?: $field->short_name);
        $q->setCustom('type_expr', $this->db->dsql()->expr($this->mapFieldType($field->type())));
        if ($this->debug) $q->debug();
        $q->execute();
    }

}
