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

/**
 * TODO list
 * ---------
 * 1. All fields of the table (at least in MySQL) can be altered in one SQL
 *    request like this.
 *    ALTER TABLE foo MODIFY bar integer, DROP baz, ADD qwerty
 *    If we implement this, then that should make our code much faster.
 *
 * 2. Quite often you add same model mutiple times in your views/controllers/
 *    whatever. So, we could register AutoCreator in API for a first time it's
 *    called for particular model and later just check aren't we already
 *    registered and if so, then do nothing, because tables are altered on
 *    first call already.
 *
 * 3. Create more extended controllers. For example, for SQLite.
 * 
 * 4. Implement feature to also add foreign keys. We can do that by creating
 *    related model and then add/alter foreign key in DB.
 */

abstract class Controller_AutoCreator_Abstract extends \AbstractController
{
    // debug mode
    public $debug = false;

    // mapping array of field types ATK4 => DB (should define in extended class)
    public $mapping = array();

    // default DB field type (should define in extended class)
    public $default_type = '';

    // shortcut to owners database object
    protected $db;
    
    // shortcut to owners table name
    protected $table;
    
    // default ID field
    protected $is_default_id_field; // true|false, for internal use only
    


    // Initialization
    function init()
    {
        parent::init();

        // check owner object
        if (! $this->owner instanceof \Model_Table) {
            throw $this->exception('Must be used only with Model_Table', 'ValidityCheck');
        }

        // create shortcuts
        $this->db = $this->owner->db;
        $this->table = $this->owner->table;
        
        // default_id_field ?
        $this->is_default_id_field = strtolower($this->owner->id_field)=='id';

        // get current description of a table from DB
        $db_fields = $this->getDBFields();

        // get fields from model
        $m_fields = $this->getModelFields();

        // create new table if it's not in DB
        if (empty($db_fields)) {
            if ($this->debug) var_dump("CREATE TABLE");
            $this->createTable();
            $db_fields = $this->getDBFields();
        }

        // add/modify fields in DB table
        foreach ($m_fields as $m) {
            if ($m instanceof \Field) {
                $f = $m->actual_field ?: $m->short_name;

                // expression field and hasMany relation are not stored in table
                if ($m instanceof \Field_Expression || $m->relation) {
                    if ($this->debug) var_dump("EXPRESSION or HASMANY FIELD - no changes: ".$f." (type=".$m->type.")");
                    unset($db_fields[$f]);
                    continue;
                }

                // create or modify DB table field
                if (! isset($db_fields[$f]) ) {
                    if ($this->debug) var_dump("ADD FIELD: ".$f);
                    $this->alterField($m, true); // create
                } else {
                    if ($this->debug) var_dump("MODIFY FIELD: ".$f." (type=".$m->type.")");
                    $this->alterField($m, false); // modify
                }

                // hasOne reference field
                if ($m instanceof \Field_Reference) {
                    if ($this->debug) var_dump("REFERENCE FIELD: ".$f." (type=".$m->type.")");
                    
                    // TODO: create related model and add foreign key

                }
                
                unset($db_fields[$f]);
            }
        }

        // drop all DB table fields left in array
        foreach ($db_fields as $name=>$d) {
            if ($this->debug) var_dump("DROP FIELD: ".$name);
            $this->dropField($name);
        }
    }

    protected function getDBFields(){
        $q = $this->db->dsql()->describe($this->table);
        $fields = array();
        try {
            foreach ($q as $field) {
                $key = isset($field['name']) ? $field['name'] : $field['Field'];
                $fields[$key] = $field;
            }
        } catch (\Exception $e) {
            // no table;
        }
        return  $fields;
    }
    protected function getModelFields(){
        return $this->owner->elements;
    }

    protected function mapFieldType($type)
    {
        if (isset($this->mapping[$type])) {
            return $this->mapping[$type];
        }
        return $this->default_type;
    }

    // Abstract methods (should be implemented in extended classes for each DB type)
    abstract function createTable();
    abstract function dropField($fieldname);
    abstract function alterField(\Field $field, $add = false);
    
}
