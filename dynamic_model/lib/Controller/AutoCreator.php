<?php
namespace dynamic_model;
/*
   Author: Romans Malinovskis (c) Elexu Technologies www.elexutech.com
   Distributed under MIT and AGPL Licenses

   Add this controller inside your model and it will make sure than all the 
   fields defined in your model are also present in your SQL. If any fields
   are missing, the ALTER table will create them

   DANGER: Using this controller on production system is VERY discouraged,
   as it slows down database performance by doing constant "describe's"
    */
class Controller_AutoCreator extends \AbstractController {
	function init(){
		parent::init();

        if(!$this->owner instanceof \Model_Table){
            throw $this->exception('Must be used only with Model_Table','ValidityCheck');
        }
		$this->db=$this->owner->db;
		$this->table=$this->owner->table;

		// try describe table

		$q=$this->db->dsql()->describe($this->owner->table);

		$missing_fields=$this->owner->elements;

		try{
			foreach($q as $line){
				// TODO: match type of field, and perform alter if not matches

				unset($missing_fields[$line['name'] ?: $line['Field']]);

			}
		}catch(\Exception $e){
			// no table;
		}


		if($missing_fields[$this->owner->id_field]){
			// ouch, id field is missing too
			$this->createTable();
			unset($missing_fields[$this->owner->id_field]);
		}

		foreach($missing_fields as $field)if($field instanceof \Field){

			if($field instanceof \Field_Expression)continue;
			if($field->relation)continue;

			$this->alterField($field, true);

			if($field instanceof \Field_Reference){
				// TODO: create related model and add foreign key
			}
		}
	}
	// TODO: move into separate controller extended for each database type
	function createTable(){
		$q=$this->db->dsql()->expr('create table [cr_table] ([idfield] INTEGER not null PRIMARY KEY [auto_increment] )');
		$q->setCustom('cr_table',$this->table);
		$q->setCustom('auto_increment',$this->db->dsql()->expr($q instanceof \DB_dsql_mysql?
			'auto_increment':''));
		$q->setCustom('idfield',$this->owner->id_field);
		$q->execute();	// executes query
	}

	function alterField(\Field $field, $add=false){
		$q=$this->db->dsql()->expr('alter table [al_table] add [field_name] [type_expr]');
		$q->setCustom('al_table',$this->table);
		$q->setCustom('field_name',$x=$field->actual_field?:$field->short_name);
		$q->setCustom('type_expr',$this->db->dsql()->expr($this->resolveFieldType($field->type())));
		$q->execute();
	}

	// TODO: move this to a setparate controller
    function resolveFieldType($type){
        $cast = array(
            "int" => "integer",
            "money" => "decimal(10,2)",
            "datetime" => "datetime",
            "date" => "date",
            "string" => "varchar(255)",
            "text" => "text",
            "boolean" => "bool",
        );
        if(isset($cast[$type]))return $cast[$type];
        return 'varchar(255)';
    }
}
