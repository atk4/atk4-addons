<?php
namespace dynamic_model;
/**
 * Authors:
 *      Romans Malinovskis (c) Elexu Technologies www.elexutech.com
 *      Imants Horsts      (c) DS Development     www.dsd.lv
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

class Controller_AutoCreator_SQLite extends Controller_AutoCreator_Abstract
{
    // Mapping of field types Model => DB (should define in extended class).
    // You can use templates like {length|255} which means $field->length()
    // or $field->length or 255.
    // Also template like decimal({size|length|10},{precision|2}) should work.
    public $mapping = array(
            "int"      => "integer",
            "real"     => "decimal(18,6)", // don't use MySQLs approximate value types like float or double
            "money"    => "decimal(14,2)",
            "datetime" => "datetime",
            "date"     => "date",
            "string"   => "varchar({length|255})",
            "text"     => "text",
            "boolean"  => "bool",
        );

    // default DB field type
    public $default_type = 'varchar({length|255})';

    // MySQL engine
    public $engine = 'MyISAM'; // MyISAM | INNODB | etc.

    // array of SQL templates used
    // supports one level deep nested action templates
    protected $templates = array(
            // create table
            'create-table' => 'CREATE TABLE IF NOT EXISTS `[table]` (`[field]` [type] NOT NULL PRIMARY KEY [auto_incr])',

            // modify table fields
            'modify-table' => 'ALTER TABLE `[table]` [content]',
            'add-field'    => 'ADD `[field]` [type]',
            'modify-field' => 'MODIFY `[field]` [type]',
            'drop-field'   => 'DROP field `[field]`',

            // add foreign key
            'add-f-key'    => 'ALTER TABLE `[table]` ADD FOREIGN KEY `[idx_name]` (`[idx_col]`) REFERENCES `[ref_table]` (`[ref_col]`)',
        );



    /**
     * Prepare create-table action
     *
     * @param Model $model
     *
     * @return void
     */
    function createTable(\Model $model)
    {
        if ($this->is_default_id_field) {
            // default ID field
            $type = 'integer';
            $auto = '';
        } else {
            // custom ID field
            $field = $model->getElement($model->id_field);
            $type = $this->mapFieldType($field);
            $auto = '';
        }

        // register action
        $this->actions['create-table'] = array(
                'template' => $this->templates['create-table'],
                'tags'     => array(
                        'table'     => $model->table,
                        'field'     => $model->id_field,
                        'type'      => $type,
                        'auto_incr' => $auto,
                        'engine'    => $this->engine,
                    ),
            );
    }

    /**
     * Prepare modify-table action
     *
     * @param Model $model
     *
     * @return void
     */
    function modifyTable(\Model $model)
    {
        if (! isset($this->actions['modify-table'])) {
            $this->actions['modify-table'] = array(
                    'template' => $this->templates['modify-table'],
                    'tags'     => array('table' => $model->table),
                );
        }
    }

    /**
     * Extend modify-table action with add-field, modify-field
     *
     * @param Model $mode
     * @param Field $field
     * @param boolean $add
     *
     * @return void
     */
    function alterField(\Model $model, $field, $add = false)
    {
        if(!$add)return; // does not suppor field alter


        // initialize modify-table action
        $this->modifyTable($model);

        // actual name of field
        $f = $this->getFieldName($field);

        // never alter ID field, that can break auto increment
        if ($f == $model->id_field) {
            return;
        }

        // calculate field type
        if ($field instanceof \Field_Reference) {

            // initialize referenced model, get description of its ID field and
            // use type of its ID field as type for this field
            $ref_model = $field->ref('model');
            $ref_fields = $this->getDBFields($ref_model);
            $ref_id = $ref_fields[$ref_model->id_field];
            $type = $ref_id['Type'];

            // add foreign key to referenced model
            // it looks that it's impossible to modify keys, so we only do this
            // when creating new field
            if ($add) {
                $this->addForeignKey($model, $field, $ref_model);
            }

        } else {

            // if ordinary field, then get type from type mapping
            $type = $this->mapFieldType($field);
        }

        // register action
        $this->actions['modify-table']['tags']['content'][] = array(
                'template' => $add
                        ? $this->templates['add-field']
                        : $this->templates['modify-field'],
                'tags'     => array(
                        'field'  => $f,
                        'type'   => $type,
                    ),
            );
    }

    /**
     * Extend modify-table action with drop-field
     *
     * @param Model $model
     * @param string $fieldname
     *
     * @return void
     */
    function dropField(\Model $model, $fieldname)
    {
        return;
        // initialize modify-table action
        $this->modifyTable($model);

        // register action
        $this->actions['modify-table']['tags']['content'][] = array(
                'template' => $this->templates['drop-field'],
                'tags'     => array(
                        'field'  => $fieldname,
                    ),
            );
    }

    /**
     * Prepare add-f-keys action
     *
     * @param Model $model
     * @param Field $field
     * @param Model $ref_model
     *
     * @return void
     */
    function addForeignKey(\Model $model, \Field $field, \Model $ref_model)
    {
        // initialize modify-table action
        $this->modifyTable($model);

        // actual name of field
        $f = $this->getFieldName($field);

        // debug
        $this->dbg("ADD FOREIGN KEY: ".get_class($model)."->".$f." --> ".get_class($ref_model));

        // check if it's reference field
        if (! $field instanceof \Field_Reference) {
            throw $this->exception('Field must be of class Field_Reference', 'ValidityCheck')
                        ->addMoreInfo('Field', $f);
        }

        // register actions
        $this->actions['add-f-keys'][] = array(
                'template' => $this->templates['add-f-key'],
                'tags'     => array(
                        'table'     => $model->table,
                        'idx_name'  => 'fk_'.$f,
                        'idx_col'   => $f,
                        'ref_table' => $ref_model->table,
                        'ref_col'   => $ref_model->id_field,
                    ),
            );
    }

    /**
     * Execute model and DB synchronization
     *
     * @param Model $model
     *
     * @return void
     */
    function synchronize(\Model $model)
    {
        // Create table
        if (isset($this->actions['create-table'])) {
            $this->executeAction($model, $this->actions['create-table']);
        }

        // Alter table (add, modify, drop fields)
        if (isset($this->actions['modify-table'])) {
            $this->executeAction($model, $this->actions['modify-table']);
        }

        // Alter table (foreign keys)
        if (isset($this->actions['add-f-keys'])) {
            foreach ($this->actions['add-f-keys'] as $action) {
                $this->executeAction($model, $action);
            }
        }
    }


    /**
     * Execute one action
     *
     * Supports one level deep nested action templates
     * TODO: maybe all of this can be rewritten to use DSQL->consume for recursion?
     *
     * @param Model $model
     * @param array $action
     *
     * @return void
     */
    function executeAction(\Model $model, $action)
    {
        // prepare
        $commited=true;
        $q = $model->dsql->dsql()->expr($action['template']);
        if (isset($action['tags']) && $action['tags']) {
            // replace tags

            foreach ($action['tags'] as $k=>$v) {
                if (is_array($v)) {
                    // sub-template
                    $expr = array();
                    foreach($v as $k2=>$v2) {
                        $mq=clone $q;
                        $q2 = $model->dsql->dsql()->expr($v2['template']);
                        $q2->setCustom($v2['tags']);
                        $mq->setCustom($k, $q2->render());
                        if ($this->debug) $mq->debug();
                        $mq->execute();
                        $commited=true;
                    }
                } else {
                    // simple tag replacement
                    $q->setCustom($k, $v);
                    $commited=false;
                }
                // execute
            }
        }
        if (!$commited) {
            if ($this->debug) $q->debug();
            $q->execute();
        }

    }
}
