<?php
namespace dynamic_model;
/**
 * Authors:
 *      Romans Malinovskis (c) Elexu Technologies www.elexutech.com
 *      Imants Horsts      (c) DSD, SIA           www.dsd.lv
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

/**
 * TODO list
 * ---------
 * 1. Check out this proposal made by Bob:
 *    https://groups.google.com/d/msg/agile-toolkit-devel/scoRCKlcEXg/kYgNOqXIrBgJ
 * 2. Create more extended controllers for different database engines.
 *    For example, for SQLite (should be easy), Oracle, Mongo (not instanceof
 *    SQL_Model) etc.
 */

abstract class Controller_AutoCreator_Abstract extends \AbstractController
{
    // debug mode
    public $debug = false;

    // mapping array of field types ATK4 => DB (should define in extended class)
    // You can use templates like {length|255} which means $field->length()
    // or $field->length or 255.
    // Also template like decimal({size|length|10},{precision|2}) should work.
    public $mapping = array();

    // default DB field type (should define in extended class)
    public $default_type;

    // default ID field
    protected $is_default_id_field; // true|false, for internal use only

    // array of SQL templates used
    protected $templates = array();

    // array of actions to perform on synchronization phase
    protected $actions = array();



    /**
     * Initialization
     * 
     * @return void
     */
    function init() {
        parent::init();

        // check owner object
        $model = $this->owner;
        if (! $model instanceof \SQL_Model) {
            throw $this->exception('Must be used only with SQL_Model', 'ValidityCheck');
        }

        // execute
        $this->execute($model);
    }

    /**
     * 
     * @param SQL_Model $model
     *
     * @return void
     */
    function execute(\SQL_Model $model) {
        $class = get_class($model);

        // if model class already processed, then step out
        if (isset($this->api->dynamicModel[$class])
            && $this->api->dynamicModel[$class] === true
        ) {
            return;
        }

        // debug
        $this->dbg('MODEL: ' . $class. "(" . $model->name. ")");

        // default_id_field ?
        $this->is_default_id_field = strtolower($model->id_field)=='id';

        // get fields from model
        $m_fields = $this->getModelFields($model);

        // get current description of a table from DB
        $db_fields = $this->getDBFields($model);

        // create new table if it's not in DB
        if (! $db_fields) {
            $this->dbg("CREATE TABLE");
            $this->createTable($model);
            $db_fields = $this->getDBFields($model);
        }

        // add/modify fields in DB table
        foreach ($m_fields as $field) {
            if ($field instanceof \Field) {
                
                // actual name of field
                $f = $this->getFieldName($field);

                // expression and hasMany reference fields are not stored in DB
                if ($field instanceof \Field_Expression || $field->relation) {
                    $this->dbg("EXPRESSION or HASMANY FIELD (no changes): ".$f." (type=".$field->type.")");
                    unset($db_fields[$f]);
                    continue;
                }

                // hasOne reference field
                // can be be implemented in extended class alterField method
                // see Controller_AutoCreator_MySQL as example
                if ($field instanceof \Field_Reference) {
                    $this->dbg("REFERENCE (HasOne) FIELD: ".$f." (see below)");
                }

                // create or modify DB table field
                if (! isset($db_fields[$f]) ) {
                    $this->dbg("ADD FIELD: ".$f);
                    $this->alterField($model, $field, true); // create
                } else {
                    $this->dbg("MODIFY FIELD: ".$f." (type=".$field->type.")");
                    $this->alterField($model, $field, false); // modify
                }

                unset($db_fields[$f]);
            }
        }

        // drop all DB table fields not used by model (left in array)
        foreach ($db_fields as $name=>$d) {
            $this->dbg("DROP FIELD: ".$name);
            $this->dropField($model, $name);
        }
        
        // actually process all DB operations
        // execute synchronization and register synchronized class name in API
        $this->hook('beforeSynchronize');
        
        $this->dbg('SYNC: ' . $class . ' --> DB table ' . $model->table);
        $this->synchronize($model);
        $this->api->dynamicModel[$class] = true;
        
        $this->hook('afterSynchronize');
    }
    
    /**
     * Show debug info
     *
     * @param mixed $s
     * 
     * @return void
     */
    protected function dbg($s) {
        if ($this->debug) {
            var_dump($s);
        }
    }

    /**
     * Returns array of actual field names from models database table
     *
     * @param SQL_Model $model
     *
     * @return array
     */
    protected function getDBFields(\SQL_Model $model = null) {
        if (! $model === null) {
            $model = $this->owner;
        }

        // get DB field descriptions
        $q = $model->db->dsql();
        if ($this->debug) $q->debug();
        $db_fields = $q->describe($model->table);

        // extract DB field names from descriptions
        $fields = array();
        try {
            foreach ($db_fields as $field) {
                $key = isset($field['name']) ? $field['name'] : $field['Field'];
                $fields[$key] = $field;
            }
        } catch (\Exception $e) {
            // no table;
        }
        
        return  $fields;
    }
    
    /**
     * Returns array of actual fieldnames from model
     *
     * @param SQL_Model $model
     *
     * @return array
     */
    protected function getModelFields(\SQL_Model $model = null) {
        if (! $model === null) {
            $model = $this->owner;
        }
        
        return $model->elements;
    }

    /**
     * Returns model fields actual name
     * 
     * @param Field $field
     *
     * @return string
     */
    protected function getFieldName(\Field $field) {
        return $field->actual_field ?: $field->short_name;
    }

    /**
     * Map field types Model Field --> DB field
     *
     * @param Field $field
     *
     * @return string
     */
    protected function mapFieldType(\Field $field) {
        $type = $field->type();
        
        // try to find mapping
        // if not found, then fall back to default type or model field type
        if (isset($this->mapping[$type])) {
            $db_type = $this->mapping[$type];
        } else {
            $db_type = $this->default_type ?: $type;
        }
        
        // if no DB type found, then throw exception
        if (!$db_type) {
            throw $this->exception('No field type mapping found')
                        ->addMoreInfo('Model field type', $type);
        }
        
        // replace by template if any
        // template can be like varchar({length|255}) - $field->length() or $field->length or 255
        preg_replace_callback(
            '/{([^{]*?)}/i',
            function ($matches) use ($field, &$db_type) {
                $vars = explode('|', $matches[1]);
                $vars = array_map('trim', $vars);
                
                for ($i=0; $i<count($vars), $v=$vars[$i]; $i++) {
                    if (method_exists($field, $v) && @$field->$v()) { // try to get from field method (surpress warnings because of setterGetter methods)
                        $db_type = str_replace($matches[0], $field->$v(), $db_type);
                        break;
                    } elseif (property_exists($field, $v) && $field->$v) { // try to get from field property
                        $db_type = str_replace($matches[0], $field->$v, $db_type);
                        break;
                    } elseif (is_numeric($v)) { // simply numeric constant
                        $db_type = str_replace($matches[0], $v, $db_type);
                        break;
                    } elseif ($i == count($vars)-1) { // if last variant, then simply use that as constant
                        $db_type = str_replace($matches[0], $v, $db_type);
                        break;
                    }
                }
            },
            $db_type
        );
        
        return $db_type;
    }

    /**
     * Execute model and DB synchronization
     *
     * Can and probably should be overwritten in extended classes
     *
     * @param SQL_Model $model
     *
     * @return void
     */
    function synchronize(\SQL_Model $model)
    {
        $this->executeAction($model, $this->actions);
    }

    /**
     * Execute one action
     *
     * Supports one level deep nested action templates
     * TODO: maybe all of this can be rewritten to use DSQL->consume for recursion?
     *
     * @param SQL_Model $model
     * @param array $action
     *
     * @return void
     */
    function executeAction(\SQL_Model $model, $action)
    {
        // prepare
        $q = $model->db->dsql()->expr($action['template']);
        if (isset($action['tags']) && $action['tags']) {
            // replace tags
            foreach ($action['tags'] as $k=>$v) {
                if (is_array($v)) {
                    // sub-template
                    $expr = array();
                    foreach($v as $k2=>$v2) {
                        $q2 = $model->db->dsql()->expr($v2['template']);
                        $q2->setCustom($v2['tags']);
                        $expr[] = $q2->render();
                    }
                    $expr = implode(', ', $expr);

                    $q->setCustom($k, $expr);
                } else {
                    // simple tag replacement
                    $q->setCustom($k, $v);
                }
            }
        }

        // execute
        if ($this->debug) $q->debug();
        $q->execute();
    }

    // Abstract methods
    // Should be implemented in extended classes for each DB type
    // See Controller_AutoCreator_MySQL as example.
    abstract function createTable(\SQL_Model $model);
    abstract function alterField (\SQL_Model $model, \Field $field, $add = false);
    abstract function dropField  (\SQL_Model $model, $fieldname);
}
