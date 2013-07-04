<?php
namespace dynamic_model;
/**
 * Author: Romans Malinovskis (c) Elexu Technologies www.elexutech.com
 * Distributed under MIT and AGPL Licenses
 *
 * Add this controller inside your model and it will make sure than all the 
 * fields defined in your model are also present in your SQL. If any fields
 * are missing, then ALTER table will create them. It'll also keep track of
 * types of your model fields and ALTER table repectively.
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
            "real"      => "float",
            "money"     => "decimal(10,2)",
            "datetime"  => "datetime",
            "date"      => "date",
            "string"    => "varchar({length|255})", // {length|255} - $field->length or 255
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
            $q->setCustom('type_expr', $this->mapFieldType($field));
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
        // actual name of field
        $f = $field->actual_field ?: $field->short_name;

        // never alter ID field or it can break auto increment
        if ($f == $this->owner->id_field) {
            return;
        }

        // calculate field type
        if ($field instanceof \Field_Reference) {
            // Initialize referenced model, get description of its ID field and
            // use type of its ID field as type for this field
            $ref_model = $field->ref('model');
            $ref_fields = $this->getDBFields($ref_model->table);
            $ref_id = $ref_fields[$ref_model->id_field];
            $type = $ref_id['Type'];
        } else {
            // if ordinary field, then get type from type mapping
            $type = $this->mapFieldType($field);
        }
        
        // do ALTER
        $t = 'alter table [al_table] [method] [field_name] [type_expr]';
        $q = $this->db->dsql()->expr($t);
        $q->setCustom('al_table', $this->table);
        $q->setCustom('method', $add ? 'add' : 'modify');
        $q->setCustom('field_name', $f);
        $q->setCustom('type_expr', $this->db->dsql()->expr($type));
        if ($this->debug) $q->debug();
        $q->execute();
        
        // add Foreign key reference if this is reference field and it's added
        if ($add && $field instanceof \Field_Reference) {
            $t = 'alter table [al_table] add foreign key [idx_name] ([idx_col]) REFERENCES [ref_table] ([ref_col])';
            $q = $this->db->dsql()->expr($t);
            $q->setCustom('al_table', $this->table);
            $q->setCustom('idx_name', 'fk_'.$f);
            $q->setCustom('idx_col', $f);
            $q->setCustom('ref_table', $ref_model->table);
            $q->setCustom('ref_col', $ref_model->id_field);
            if ($this->debug) $q->debug();
            $q->execute();
        }
    }

}
