<?php
/**
 * Generic table model class.
 * Handles DB operations and formats data for views
 *
 * @author Camper (camper@agiletech.ie) on 26.03.2009
 */

abstract class Model_MVCTable extends Model {
    public $entity_code = null;
    protected $id = null;           // set with loadData(), identifies the singly entity
    protected $table_alias = null;  // we need alias always for more simple way in setQueryFields method
    protected $dsql=array();
    protected $fields_set=false;    // turns to true in setQueryFields(). prevents call to setQueryFields()
                                    // during execQuery() event
    protected $joins_set=false;     // turns to true when joins were added

    public $fields;

    protected $default_fields;      // fields and values for default field (will be used in inserts)

    protected $actual_fields=null;  // actual field list. defaults to all fields

    protected $init_where;

    private $range_split_pattern='..';

    protected $order=array();       // fields for ordering

    /**
     * data from some record (should be initialised in loadData method)
     */
    public $data=array();
    public $original_data=array();  // this array initialized ONLY on data load and
                                        // may be used to compare data before modifications

    /**
     * list of related entities, format: assoc_array with keys - alias string and items with props:
     *    'alias' => array('name'=>name of entity, 'on'=>completed condition, 'join'=>'inner'|'left outer')
     */
    protected $join_entities=array();

    /**
     * mode of check permisssions:
     *  'restricted'  - need check permission for user
     *  'allowed'  - modification allow for all users
     *  'admin_only' - editable only in admin part
     */
    protected $permission_check_mode = 'restricted';

    protected $calculated_fields=array();   // array containing queries for calculated fields

    protected $changed=false;   // set to true when loaded data was updated with set()

    protected $debug=false; // call debug to enable debugging

    public function init() {
        parent::init();
        if(!$this->table_alias)$this->table_alias=$this->entity_code;
        if(is_null($this->entity_code))throw new Exception_InitError('You should define entity code for '.get_class($this));
        $this->addField('id')
            ->datatype('int')
            ->system(true)
        ;

        if(method_exists($this,'defineFields')){
            //throw new Exception_Obsolete('defineFields method is obsolete');

            // obsolete method
            $this->defineFields();
        }

        // trying to set required fields
        //$this->api->addHook('post-init',array($this,'setMandatoryConditions'));
    }
    function debug($what=true){
        $this->debug=$what;
        return $this;
    }
    function defineFields(){
        // obsolete method
    }
    /**
     * Sets the mandatory conditions such as system reference
     */
    protected $is_mandatory_conditions_set=false;
    protected function setMandatoryConditions(){
        // showing only actual records
        if($this->is_mandatory_conditions_set)return;
        $this->is_mandatory_conditions_set=true;
        if (isset($this->fields['deleted']))
            $this->addCondition('deleted','N');
    }

    /**
    * return ALL fields definitions (array of objects FieldDefinition class)
    * To get visible fields, use getActualFields()
    */
    public function getFields($field_name=null) {
        if(!$field_name)return $this->fields;
        if(!isset($this->fields[$field_name]))
            throw new Exception_InitError('Field '.$field_name.' is not defined in '.$this->name);
        return $this->fields[$field_name];
    }
    public function getAllFields(){
        return $this->getFields();
    }
    /**
     * Returns fields that will be in actual query
     * Fields included are:
     * - those set with setActualFields()
     * - marked with system(true)
     * Fields are reordered as they set with setActualFields()
     * System fields are in the end of array
     */
    public function getActualFields(){
        $fields=$this->getFields();
        $new_fields=array();
        $actual_fields=$this->actual_fields;
        if(!is_null($actual_fields)){
            foreach($actual_fields as $field)if(isset($fields[$field])){
                $new_fields[$field]=$fields[$field];
            }
        }else{
            foreach($fields as $field=>$def){
                if($def->visible()===true)$new_fields[$field]=$def;
            }
        }
        return $new_fields;
    }
    public function getSearchableFields(){
        $fields=$this->getFields();
        $new_fields=array();
        foreach($fields as $field=>$def){
            if($def->searchable())$new_fields[$field]=$def;
        }
        return $new_fields;
    }
    public function getQuickSearchableFields(){
        $fields=$this->getFields();
        $new_fields=array();
        foreach($fields as $field=>$def){
            if($def->searchable() && $def->searchable()!=='fullonly')$new_fields[$field]=$def;
        }
        return $new_fields;
    }
    public function getSystemFields(){
        $fields=array();
        // adding system fields
        foreach($this->getAllFields() as $field=>$def){
            if($def->system()===true){
                $fields[$field]=$def;
            }
        }
        return $fields;
    }
    public function getMandatoryFields(){
        $fields=array();
        // adding system fields
        foreach($this->getAllFields() as $field=>$def){
            if($def->required()!==false){
                $fields[$field]=$def;
            }
        }
        return $fields;
    }
    public function setActualFields($actual_fields){
        $this->actual_fields=$actual_fields;
        return $this;
    }
    /**
     * Returns fields that are:
     * - belong to this entity only
     * - not calculated
     *
     */
    public function getOwnFields(){
        $r=array();
        foreach($this->getFields() as $field=>$def){
            if(!$def->calculated()&&!$def->isExternal())
                $r[$field]=$def;
        }
        return $r;
    }
    /**
    * Wrapper for getFields() to return only one field
    */
    public function getField($field_name){
        $r=$this->getFields($field_name);
        if(!$r)throw new Exception_InitError("Field $field_name is not defined for $this->name");
        return $r;
    }
    /**
    * Returns the single entity ID
    * It is presumed that when getID() is used, ID should be set first
    * null cannot be returned by this method, so it throws exception in this case
    */
    function getID(){
        if(is_null($this->id)||($this->id=='new'))throw new Exception_InitError("Entity ID is not set for ".get_class($this));
        return $this->id;
    }
    /**
     * Returns true if entity was loaded from DB with loadData()
     * Required for checks as getID() throws exception if entity was not loaded
     */
    function isInstanceLoaded(){
        // original_data is ONLY set in loadData() and unloadData(), so it can be used to
        // define if we loaded this entity
        return !empty($this->original_data);
    }
    /**
     * Returns an array with fields that will be used to initialize
     * dropdowns
     * Usually those are id and name
     * Some entities, such as contractors, have other field as name
     */
    public function getListFields(){
        return array('id'=>'id','name'=>'name');
    }
    /**
     * Create and return DSQL object
     * @param string $instance key of dsql object in internal array of objects
     * @param boolean $select_mode (optional) if FALSE will not add alias and initial where conditions
     * @param string $entity_code (optional) can used for make dsql for external entities (support only update dsql type)
     * @return dblite dsql object
     */
    function dsql($instance=null,$select_mode=true,$entity_code=null){
        $this->setMandatoryConditions();
        if (is_null($entity_code))
            $entity_code = $this->entity_code;

        if (is_null($instance)){
            $q=$this->new_dsql($select_mode,$entity_code);
        } else {
            $q=$this->get_dsql($instance,$select_mode,$entity_code);
        }
        if($this->debug===true || (is_string($this->debug) && $this->debug==substr($instance,0,strlen($this->debug))))$q->debug();
        return $q;
    }
    function selectQuery(){
        return $this->dsql();
    }
    /**
     * Used in dsql() method for the case of new query (instance=null)
     */
    protected function new_dsql($select_mode,$entity_code){
        $e=$select_mode?((!is_null($this->table_alias)?' '.$this->table_alias:'')):'';

        $q=$this->api->db->dsql()
                ->table($entity_code.$e);
        $q->select_mode=$select_mode;
        if ($select_mode) {
            $this->applyConditions($q);
        }
        return $q;
    }
    /**
     * Used in dsql() method for the case we get it by instance name
     */
    protected function get_dsql($instance,$select_mode,$entity_code){
        $e=$select_mode?((!is_null($this->table_alias)?' '.$this->table_alias:'')):'';

        if(!isset($this->dsql[$instance])){
            $this->dsql[$instance]=$this->api->db->dsql()
                ->table($entity_code.$e);
            $this->dsql[$instance]->select_mode=$select_mode;

            if ($select_mode) {
                $this->addJoins($instance);
                $this->applyConditions($this->dsql[$instance]);
            }
        }
        return $this->dsql[$instance];
    }
    public function getRef($field){
        $f=$this->getField($field);
        if(!$f || !is_object($f))throw $this->exception('getRef called on non-existant field')
            ->addMoreInfo('field',$field);
        return $f->refModel();
    }
    private function addJoins($instance,$join_list=array()){
        foreach($this->join_entities as $alias=>$entity){
            if(!empty($join_list) && !in_array($alias,$join_list))continue;
            $this->dsql[$instance]->join($entity['entity_name'].' '.$alias,
                                     $entity['on'],
                                     $entity['join']);
        }
        $this->joins_set=true;
    }
    /**
     * Applies conditions and ordering to a provided dsql object
     */
    private function applyConditions(&$dsql){
        if(!empty($this->init_where)){
            foreach($this->init_where as $fieldname=>$value){
                $c=substr($value,0,1);
                $complex=$c=='>' || $c=='<' || count(explode($this->range_split_pattern,$value))>1;
                $this->setCondition($dsql,$fieldname,$value,$complex);
            }
        }
        // applying ordering
        if(!empty($this->order)){
            foreach($this->order as $field=>$desc){
                if(isset($this->fields[$field]) && !$this->fields[$field]->calculated() && 
                        !$this->fields[$field]->datatype()=='recurring' && !$this->fields[$field]->sortable())
                    $dsql->order($this->fieldWithAlias($field),$desc);
                else $dsql->order($field,$desc);
            }
        }
        return $this;
    }

    /**
     * Return dsql object for view operations (set reference type field like readonly)
     */
    public function view_dsql($instance=null) {
        foreach ($this->fields as $fieldname=>$field_definition) {
            if (($field_definition->datatype() == 'reference') and (!$field_definition->readonly())) {
                $this->fields[$fieldname]->readonly(true);
            }
        }
        // only for certain instance, otherwise there will be dead loop
        if(!is_null($instance) && !empty($this->order)){
            foreach($this->order as $field=>$desc)$this->setOrder($instance,$field,$desc);
        }
        return $this->dsql($instance);
    }

    /**
     * return dsql object for forms
     */
    public function edit_dsql($instance=null) {
        if(is_null($instance))$instance='edit_dsql';
        return $this->dsql($instance); // no ideas at current time, about special things for forms
    }
    /**
     * Removes the specified query from the list (if exists)
     */
    public function resetQuery($instance){
        if(isset($this->dsql[$instance]))unset($this->dsql[$instance]);
        return $this;
    }
    /**
    * Adds fields defined in this model to a <b>select</b> query specified
    * @param string $instance key of instance dsql object
    * @param mixed $get_fields see loadData() description
    * @return $this
    */
    public function setQueryFields($instance,$get_fields=false){
        $this->setMandatoryConditions();
        $a=array();

        if (!is_array($get_fields) && !is_bool($get_fields))throw new Exception_InitError('Field list must be array');

        if ($get_fields===false) $fields=array_merge(array_keys($this->getActualFields()),array_keys($this->getSystemFields()));
        elseif ($get_fields===true) $fields=array_keys($this->getAllFields());
        elseif (is_numeric(key($get_fields))) $fields=array_merge($get_fields,array_keys($this->getSystemFields()));
        else $fields=array_merge(array_keys($get_fields),array_keys($this->getSystemFields()));

        if (is_null($fields))
            $fields = array_keys($this->fields);
        else {
            // filtering fields
            $tmp = array();
            foreach ($fields as $fieldname)
                if (isset($this->fields[$fieldname]))
                    $tmp[] = $fieldname;

            $fields = $tmp;
        }

        $joined_entities = array();
        foreach($fields as $field_name){
            $f='';
            if (isset($this->fields[$field_name])) {
                $definition = $this->fields[$field_name];

                // select reference entities if readonly
                if ($definition->datatype()=='reference') {
                    $withid=$field_name.'_id';
                    if(!isset($this->fields[$withid])){
                        $withid=$field_name;
                    }
                    $definition_withid = $this->fields[$withid];

                    $f = $definition_withid->refModel(null,false)->toStringSQL(
                            (($definition->alias())?$definition->alias():$this->table_alias).
                                '.'.$definition_withid->dbname(), $field_name, $definition->displayField());
                    if ($definition->isExternal())
                        $joined_entities[$definition->alias()] = $definition->alias();
                    // possible alias
                    if(is_array($get_fields) && isset($get_fields[$field_name]))$f.=" as ".$get_fields[$field_name];
                }
                else
                if ($definition->calculated()){
                    // while on signup we don't need those fields, except one of them
                    // FIXME: review this condition
                    if(!method_exists($this->api,'isInSystemWizard') || !$this->api->isInSystemWizard() || $field_name=='exp_date'){
                        // calculated field can be as well aggregated!
                        $f = $definition->aggregate()?"sum(".$this->calculate($definition->name(),false).")":
                            $this->calculate($definition->name());
                        // ... and external
                        if($definition->isExternal())
                            $joined_entities[$definition->alias()] = $definition->alias();
                        if ($definition->aggregate()){
                            // non-agreated fields should be added to GROUP clause
                            $this->setGroupBy($instance);
                        }
                        // possible alias
                        if(is_array($get_fields) && isset($get_fields[$field_name]))$f.=" as ".$get_fields[$field_name];
                        else $f.=" as $field_name";
                    }
                }
                // aggregates
                elseif ($definition->aggregate()){
                    $f = "sum(".$definition->getDBfield($this->table_alias).")";
                    // non-agreated fields should be added to GROUP clause
                    $this->setGroupBy($instance);
                    // possible alias
                    if(is_array($get_fields) && isset($get_fields[$field_name]))$f.=" as ".$get_fields[$field_name];
                    else $f.=" as $field_name";
                }
                // other fields
                else {
                    $f = $definition->getDBfield($this->table_alias);
                    if ($definition->isExternal()){
                        $joined_entities[$definition->alias()] = $definition->alias();
                    }
                    // possible alias
                    if(is_array($get_fields) && isset($get_fields[$field_name]))$f.=" as ".$get_fields[$field_name];
                }
            }
            else {
                $f = $this->fieldWithAlias($field_name);  // allow using fields what not defined in fields prop
                // possible alias
                if(is_array($get_fields) && isset($get_fields[$field_name]))$f.=" as ".$get_fields[$field_name];
            }
            if($f)$a[]=$f;
        }
        $this->dsql($instance)->field($a);

        // add required joins
        if(!$this->joins_set)$this->addJoins($instance,$joined_entities);
        $this->fields_set=true;
        return $this;
    }
    /**
     * Adds non-aggregated fields into GROUP BY clause of the $instance
     * It is called within setQueryFields() whenever aggregated field met
     */
    private function setGroupBy($instance){
        foreach($this->getFields() as $name=>$def){
            // system fields must be skipped
            if($def->system()===true)continue;
            if($def->calculated()){
                // calculated fields should be in group too, but not a name only
                $f='calculate_'.$name;
                $gf=$this->$f();
            }else{
                if($def->isExternal())$gf=$def->alias().'.'.$def->dbname();
                else $gf=$name;
            }
            if(!$def->aggregate() && $def->visible()===true && !$this->dsql($instance)->paramExists('group',$gf))
                $this->dsql($instance)->group($gf);
        }
    }
    /**
     * Returns true if the join already exist
     * @param strin $instance instance of the query to check
     * @param string $alias alias of the join from the $this->join_entities array
     */
    private function joinExists($instance,$alias){
        if($this->dsql($instance)->args['join'])
        $this->join_entities[$alias]['entity_name'];
    }

    public function isFieldsSet($instance){
        return (!empty($this->dsql($instance)->args['fields'])&&$this->fields_set);
    }
    /**
     * returns true if field is defined for the model, false otherwise
     */

    function fieldExists($fieldname){
        return isset($this->fields[$fieldname]);
    }
    function hasField($fieldname){
        return $this->fieldExists($fieldname);
    }

    public function addField($name) {
        $this->fields[$name] = new FieldDefinition($this);
        return $this->fields[$name]->name($name);//->readonly($this->isReadOnly());
    }
    function newField($name){
        // OBSOLETE: to be removed in 4.1. use addField();
        return $this->addField($name);
    }
    /**
     * Sets the SQL for calculated field
     * This SQL will be passed to resulting dsql
     * Actually this method calls calculate_$field_name() which must exist for model to work
     */
    protected function calculate($field_name,$add_alias=true){
        $calc=$this->getField($field_name)->calculated();
        if($calc===true){
            $method='calculate_'.$field_name;
            if(!method_exists($this,$method))throw new Exception_InitError("No calculation algorythm for $field_name");
            return "(".$this->$method().")";//.($add_alias?" as $field_name":"");
        }elseif(is_string($calc)){
            $method='calculate__'.$calc;
            if(!method_exists($this,$method))throw new Exception_InitError("No calculation algorythm for $field_name"
                   ." (calculate__$calc)");
            return "(".$this->$method($field_name).")";//.($add_alias?" as $field_name":"");
        }elseif(is_callable($calc)){
            call_user_func($calc,$this,$field_name);
        }
    }
    function calculate__ref($name){

        $definition=$this->getField($name.'_id');
        $f = $definition->refModel()->toStringSQL(
                (($definition->alias())?$definition->alias():$this->table_alias).
                '.'.$definition->dbname(), $name, $definition->displayField());
        if ($definition->isExternal())
            $joined_entities[$definition->alias()] = $definition->alias();
        // possible alias
        if(is_array($get_fields) && isset($get_fields[$field_name]))$f.=" as ".$get_fields[$field_name];



        ts("ref $name<br/>");
        $r=$this->getField($name);

        $r_ref=$this->getField($name.'_id');
        $m_ref=$r_ref->refModel(null,false);
        ts("out $name");


        if($m_ref->fieldExists('name') && !$m_ref->getField('name')->calculated()){
            $f=$m_ref->table_alias.'.name';
        }else{
            $f = $m_ref->toStringSQL(
                    (($r_ref->alias())?$r_ref->alias():$this->table_alias).
                    '.'.$r_ref->dbname(), $name, $r_ref->displayField());
        }



        $q=$r_ref->refModel()->dsql();
        $q->field($f);
        $q->args['having']=array();
        $q->table($m_ref->entity_code.' '.$m_ref->table_alias);
        $q->where($m_ref->table_alias.'.id='.$this->table_alias.'.'.$name.'_id');
        return $q->select();
    }

    public function set($field_name,$value=null){
        if(is_null($value) && is_array($field_name)){
            foreach($field_name as $k=>$v)if($this->fieldExists($k))$this->set($k,$v);
            return $this;
        }
        $this->data[$field_name]=$value;
        $this->changed=true;
        return $this->setFieldVal($field_name,$value);
    }

    private function booleanToDb($value){
        if($value===true||$value=='Y')$value='Y';
        elseif($value===false||$value=='N')$value='N';
        // sometimes we get garbage from the form
        elseif($value==='')$value='N';
        // sometimes there are tricky field values, like 'past' for recurring
        else $value=$value;
        return $value;
    }

    public function setFieldVal($field_name, $value) {
        if(!isset($this->fields[$field_name]))throw new Exception_InitError('No such field '.$field_name.' in '.$this->name);
        $field=$this->fields[$field_name];
        if(!$field)return $this;
        // readonly fields are not processed ever!
        if ($field->readonly())
            return $this;

        if(is_null($value))$value=$field->defaultValue();
        // fixing up the data. some field values may not be in proper format
        if (isset($this->fields[$field_name])) {
            $def=$this->fields[$field_name];
            // boolean fields
            if ($def->datatype()=='boolean'){
                $value=$this->booleanToDb($value);
            }

            // integer and numeric fields, MUST be processed last
            if($def->datatype()=='reference_id' || $def->datatype()=='reference' || $def->datatype()=='int' ||
            $def->datatype()=='real' || $def->datatype()=='money' || $def->datatype()=='date' ||
            $def->datatype()=='datetime' || $def->datatype()=='list'){
                if($value===''){
                    if($def->datatype()=='reference')$value=null;
                    elseif($def->required())$value=0;
                    else $value=null;
                    
                }
            }else{
                // HTML code
                if($def->datatype()!='image' && !$def->allow_html())
                    $value=strip_tags($value);
            }
        }

        // joined fields may not be defined in fields definition
        if ((!isset($this->fields[$field_name])) or (!$this->fields[$field_name]->isExternal())){
            $this->dsql('modify',false)->set($this->fields[$field_name]->dbname(),$value);
        }else {
            $entity=&$this->join_entities[$this->fields[$field_name]->alias()];
            $this->dsql('modify_'.$this->fields[$field_name]->alias(),false,$entity['entity_name'])
                ->set($this->fields[$field_name]->dbname(),$value);
            $entity['updated'] = true;
        }
        $this->data[$field_name]=$value;

        return $this;
    }

    public function setDefaultField($name, $value) {
        $this->default_fields[$name]=$value;
        return $this;
    }

    /**
     * Add item into $join_entities property
     * Calling this method causes join by the following rules:
     * 1) related entity is always joined by ID
     * 2) two types of join can be created: related entity references to master and master entity references to related
     * 3) if MASTER references to related (as contractor references to address):
     *      $join_type JOIN $entity_name $entity_alias ON $master_alias.$join_field = $entity_alias.id
     * 4) if RELATED references to master (as invoice references dochead):
     *      $join_type JOIN $entity_name $entity_alias ON $entity_alias.$join_field = $master_alias.id
     *
     * @param string $entity_alias related table alias
     * @param string $entity_name related table name
     * @param string $join_field field used in join condition
     * @param string $reference_type either 'master' or 'related', defines WHICH $join_field to use in join, default 'master'
     * @param string $join_type 'inner' or 'left outer' type of join (optional, 'inner' by default)
     * @param bool $required if true - related entity MUST be inserted with insertRecord(), even if no values were set
     *  for corresponding entity
     * @param string $master_alias alias of master entity (optional, $this->table_alias by default)
     * @return $this
     */
    public function addRelatedEntity($entity_alias, $entity_name, $join_field, $join_type = 'inner',
                                        $reference_type='master', $required=false, $master_alias = null) {
        if (is_null($master_alias))
            $master_alias = $this->table_alias;

        //while(isset($this->join_entities[$alias])){$alias.='1';}
        $join_alias=$reference_type=='master'?$master_alias:$entity_alias;
        $other_alias=$reference_type=='master'?$entity_alias:$master_alias;

        $on_condition = $join_alias.'.'.$join_field.' = '.$other_alias.'.id';
        $this->join_entities[$entity_alias] = array('readonly'=>false,'entity_name'=>$entity_name,'join_field'=>$join_field,
            'reference_type'=>$reference_type,'on'=>$on_condition,'join'=>$join_type,'required'=>$required,
            'table'=>($master_alias==$this->table_alias?$this->entity_code:$this->join_entities[$master_alias]['entity_name']));

        // Define ID file (used when inserting)
        $f=$this->addField($join_field)->system(true);
        if($reference_type!='master')$f->relEntity($entity_alias);

        return $this;
    }

    protected function addDefaultFields($dq) {
        if (!empty($this->default_fields)) {
            $fields_in_update = $dq->getArgsList('set');
            foreach ($this->default_fields as $fieldname=>$value){
                // default fields may belong to external entity
                if (!$fields_in_update||!in_array($fieldname,$fields_in_update))// && !$this->getField($fieldname)->isExternal())
                    $this->setFieldVal($fieldname,$value);
            }
        }
        return $this;
    }
    /**
     * Used to implement master-detail relationship
     * Master-details views might be different, like:
     * - invoice-invoice_specs
     * - VATperiod-invoices
     * - Contractor-invoices
     * - etc.
     * This method sets the field name which will be used as a reference to master and a value
     * of the master record
     * Once called, this method assures that:
     * 1) model shows records of the specified master_id
     * 2) model adds records with predefined master_id
     * This method can be called several times for different fields
     * @param $field field name of this model which contains reference to master dataset
     * @param $value master ID value
     */
    function setMasterField($field,$value){
        $this->addCondition($field,$value);
        // some conditions cannot be used in set clause
        if(count(explode(' ',$field))==1){
            // FIXME: maybe change to getField()->defaultValue() call right here
            $this->setDefaultField($field,$value);
        }
        $this->getField($field)->system(true);
        return $this;
    }
    /**
     * Applies filter of the quicksearch to model query
     * Conditions added to all SEARCHABLE fields of the model and are joined
     * by OR
     *
     * @param string $q string to search
     * @param string $instance optional - query to apply filter on
     */
    function applyQuicksearch($q,$instance=null){
        $c=array();
        foreach($this->getSearchableFields() as $field=>$def){
            $c[]="$field like '%".$this->api->db->escape($q)."%'";
        }
        if(empty($c))return $this;
        if(is_null($instance)){
            throw new Exception_NotImplemented("Quicksearch is not available for multiple queries");
        }
        $this->dsql($instance)->having(join(' OR ',$c));
        return $this;
    }
    private function setOrCondition($instance=null,$cond){

    }
    /**
     * Adds a condition to init array.
     * All queries created after call to this method will have this condition
     * All queries created so far are also will be appended with this condition
     * @param string $field field name
     * @param $value
     * @param boolean $complex optional. See setCondition() description
     */
    function addCondition($field,$value=null,$complex=false){
        // complex conditions cannot be added to init array
        $this->init_where[$field]=$value;
        $this->setCondition(null,$field,$value,$complex);
        return $this;
    }
    /**
     * Sets conditions on all existing select queries created so far or to specific instance
     * It is useful for setController() method creates query _before_ setMasterField() is called
     * @param mixed $instance optional, if null - all queries processed, if object - dsql instance expected which will be processed
     * @param string $field field name
     * @param $value
     * @param boolean $complex optional. If true, $value treated as complex condition, i.e. it can be:
     * - '>1000' - numeric field greater than 1000 (or any other figure)
     * - '<2000' - numeric field less than 2000
     * - '1000:2000' - numeric field greater tahn 1000 and less than 2000
     * - '>2010-01-01', '<2010-01-01', '2010-01-01:2010-01-31' - date field, similar to numeric
     * Complex conditions applied only to select queries
     */
    function setCondition($instance=null,$field,$value=null,$complex=false){
        if(is_null($value)&&is_array($field)){
            foreach($field as $f=>$v)$this->setCondition($instance,$f,$v,$complex);
            return $this;
        }
        // in case instance is null - setting all existing instances
        if(is_null($instance)){
            foreach($this->dsql as $instance=>$dsql)$this->setCondition($instance,$field,$value,$complex);
            return $this;
        }
        $_field=$this->parseFieldName($field);
        if($this->fieldExists($_field)){
            if($this->getField($_field)->datatype()=='boolean')$value=$this->booleanToDb($value);
        }
        // strpos('.',$field) shows that field was passed w/o prefix. If there is prefix, we certainly need where condition
        // FIXME: may be there is better implementation of this
        $cond=$this->getField($_field)->calculated() && strpos($field,'.')===false?'having':'where';
        // if $field contains field name only - we add an alias (only for where condition)
        $where_field=(count(explode(' ',$_field))==1 and strpos($_field,'.')===false and $cond=='where')?$this->fieldWithAlias($field):$field;

        if(is_object($instance))$dsql=$instance;
        else $dsql=$this->dsql($instance);
        if($dsql->select_mode){
            // existing condition must be overwritten
            if(isset($dsql->args[$cond]))foreach($dsql->args[$cond] as $i=>$where){
                if(stripos($where,$where_field)!==false)unset($dsql->args[$cond][$i]);
            }
            // some conditions may be on external fields
            if($this->getField($_field)->isExternal()){
                $alias=$this->getField($_field)->alias();
                $dsql->join(
                    $this->join_entities[$alias]['entity_name'].' '.$alias,
                    $this->join_entities[$alias]['on'],
                    $this->join_entities[$alias]['join']);
            }
            // if $value is null, required condition may be fully in $field
            if(is_null($value) && $_field!=$field){
                $dsql->$cond($where_field);
            }else{
                // processing complex conditions
                if($complex){
                    // signs at the beginning
                    $c=substr($value,0,1);
                    $r=explode($this->range_split_pattern,$value);
                    if($c=='>' || $c=='<'){
                        $where_field.=$c;
                        $value=substr($value,1);
                    }
                    // ranges
                    elseif(count($r)>1){
                        // must be between condition
                        $dsql->$cond("$where_field between '{$r[0]}' and '{$r[1]}'");
                        return $this;
                    }
                }
                $dsql->$cond($where_field,$value,false);
                if(!$complex)$dsql->set($field,$value);
            }
        }else if(!$complex)$this->setFieldVal($field,$value);
        return $this;
    }
    /**
     * Sets default ordering for all new instances
     */
    function setOrder($instance=null,$field,$desc=false,$sticky=false){
        // null means set to all queries, including those not yet initialized.
        // removing following line breaks reportss' ordering, so you will need to manually set $sticky
        // parameter in those calls
        if(is_null($instance))$sticky=true;
        if($sticky)$this->order[$field]=$desc;

        if(is_array($field)){
            throw new Exception_InitError("setOrder() does not allow array as a parameter");
        }
        // in case instance is null - setting all existing instances
        if(is_null($instance)){
            foreach($this->dsql as $instance=>$dsql)$this->setOrder($instance,$field,$desc,$sticky);
            return $this;
        }

        $dsql=$this->dsql($instance);
        if(isset($this->fields[$field]) && !$this->fields[$field]->calculated() && 
                !$this->fields[$field]->datatype()=='recurring' && !$this->fields[$field]->sortable())
            $dsql->order($this->fieldWithAlias($field),$desc);
        else $dsql->order($field,$desc);

        return $this;
    }
    /**
     * This defines which column data shouldbe sorted (by default). If first character is minus,
     * then sort order is reversed. Actual sorting is performed by Views where aplicable
     */
    public function getSortColumn(){
        //Example: return '-created_dts';
        return null;
    }

    /**
     * Should return either null or ID of the entity which is referenced
     * by this entity as a parent (e.g. for docspec entity this method should return dochead_id)
     */
    protected function getOwnerId(){
        return null;
    }
    /**
     * Calls required method of Model to update data
     * Depends on the model status:
     * - data updated if model has instance loaded
     * - data inserted if no instance loaded into model
     * @param array $data
     * @param boolean $force_new_record if true, forces model to unload its data so new record is inserted surely
     */
    public function update($data=array(),$force_new_record=false){
        if($force_new_record)$this->unloadData();//$this->original_data=array();
        foreach($data as $key=>$val){
            if(!$this->hasField($key))unset($data[$key]);
        }

        // any action required before update is processed
        $this->beforeModify($data);

        // with rules of array_merge
        if($this->isInstanceLoaded()){
            $id=$this->getID();
            $r=$this->updateRecord($id,$data);
        }else{
            $r=$this->insertRecord($data);
            $id=$r;
        }

        // any action after update is processed
        $this->afterModify($id);    // data is already saved and loaded

        if(is_null($r=$this->api->hook('compat-update-returns-id',array($r)))){
            $r=$this;
        }

        return $r;
    }
    public function insert($data=array()){
        $r=$this->update($data,true);
        return $r;
    }
    protected function insertRecord($data=array()) {
        // table data often with relations, so we always must have a transaction
        $this->api->db->beginTransaction();
        try{
            $this->beforeInsert($data);
            if(!$this->isInstanceLoaded())$data=array_merge($this->data,$data);
            if (!empty($data)) {
                foreach ($data as $field=>$value){
                    $this->setFieldVal($field,$value);
                }
            }

            $this->addDefaultFields($this->dsql('modify',false));
            $this->validateData($this->data);
            // this check is required as some entities may not allow edits for some reason, e.g.:
            // - invoice is read-only if its VAT period is closed
            if($this->isReadonly())throw new Exception_AccessDenied('Operation not allowed!');

            // create records in related entities if need
            if (!empty($this->join_entities)){
                foreach ($this->join_entities as $alias=>&$entity_item) {
                    // reference fields should be skipped as we don't update dictionaries automatically
                    if($this->fieldExists($entity_item['join_field'])&&
                    $this->getField($entity_item['join_field'])->datatype()==='reference')continue;
                    // joined entities fields are appended to query only if reference_type was 'master'
                    // because with reference_type='related' this entity ID is used for join
                    // for master references we add to THIS entity table
                    if(!$entity_item['readonly'] 
                    && $entity_item['reference_type']=='master' && isset($entity_item['updated']) && 
                    ($entity_item['updated']==true)) {
                        $this->setFieldVal($entity_item['join_field'],$entity_id=$this->dsql('modify_'.$alias)->do_insert());
                        unset($this->dsql['modify_'.$alias]);
                        $entity_item['updated']=false;
                    }
                }
            }

            // setting required fields
            // tricky thing with system ID
            if (isset($this->fields['created_dts']))
                $this->dsql('modify',false)->setDate('created_dts');

            //$this->logVar($this->dsql('modify',false)->insert());

            $res = $this->dsql('modify',false)->do_insert();
            unset($this->dsql['modify']); // clear object

            // now we should insert mandatory related entities
            if(!empty($this->join_entities)){
                foreach($this->join_entities as $alias=>&$entity_item){
                    // reference fields should be skipped as we don't update dictionaries automatically
                    if($this->fieldExists($entity_item['join_field'])&&
                    $this->getField($entity_item['join_field'])->datatype()==='reference')continue;
                    if($entity_item['reference_type']=='related' and $entity_item['required']){
                        // this item should reference just added entity
                        $this->setFieldVal($entity_item['join_field'],
                            $id=$this->dsql('modify_'.$alias,false,$entity_item['entity_name'])
                                ->set($entity_item['join_field'],$res)->do_insert());
                        // remembering new record ID
                        $entity_item['id']=$id;
                        unset($this->dsql['modify_'.$alias]);
                    }
                }
            }
            // loading data inserted as it will be used then
            // Romans Commit this!!!
            if($res === false)throw new Exception_InitError("Insert failed");
            $this->afterInsert($res);
            $this->api->db->commit();
        }catch(Exception $e){
            $this->api->db->rollback();
            throw $e;
        }
        $this->loadData($res);
        //if(!$this->isInstanceLoaded())throw new Exception_InitError("Insert but failed to load back, id=".$res);

        return $res;
    }
    /**
     * This method executes before update() method is processed. Override this method if you want
     * something to be recalculated/validated before any update
     */
    function beforeModify(&$data){
        $this->hook('beforeModify',array($this,&$data));
        return $this;
    }
    /* Redefine or use beforeLoad hook */
    function beforeLoad($id){
        $this->hook('beforeLoad',array($this));
        return $this;
    }
    /* Redefine or use afterLoad hook */
    function afterLoad(){
        $this->hook('afterLoad',array($this));
        return $this;
    }
    /**
     * This method executes last after all modifications were made in update()
     */
    function afterModify($id){
        $this->hook('afterModify',array($this));
        return $this;
    }
    /**
     * This method executes before insertRecord() method is processed, and AFTER beforeModify()
     */
    function beforeInsert(&$data){
        $this->hook('beforeInsert',array($this,$data));
        return $this;
    }
    /**
     * This method executes right after insertRecord() was processed, and BEFORE afterModify()
     */
    function afterInsert($new_id){
        $this->hook('afterInsert',array($this,$new_id));
        return $this;
    }
    /**
     * This method executes before updateRecord() method is processed, and AFTER beforeModify()
     */
    function beforeUpdate(&$data){
        $this->hook('beforeUpdate',array($this,$data));
        return $this;
    }
    /**
     * This method executes right after updateRecord() was processed, and BEFORE afterModify()
     */
    function afterUpdate($id){
        $this->hook('afterUpdate',array($this));
        return $this;
    }
    function beforeDelete(&$data){
        $this->hook('beforeDelete',array($this));
        return $this;
    }
    function afterDelete($old_id){
        $this->hook('afterDelete',array($this));
        return $this;
    }
    function updateRecord($id=null, $data=array()) {
        if(is_null($id))$id=$this->get('id');

        $this->api->db->beginTransaction();
        try{
            $this->beforeUpdate($data);
            // we'll need this in audit function:
            $this->data=$data=array_merge($this->data,$data);
            $changed=0;
            if (!empty($data)) {
                foreach ($data as $fieldname=>$value){
                    if($this->isChanged($fieldname,$value)){
                        $this->setFieldVal($fieldname,$value);

                        $field=$this->fields[$fieldname];
                        if($field && !$field->readonly())
                            $changed++;
                    }
                }
            }
            if(!$changed){
                $this->afterUpdate($this->getID());
                $this->api->db->commit();
                return $this;
            }
            // when setting field values some of them may change:
            // e.g. boolean fields change value from true/false to 'Y'/'N'
            $this->validateData($data);
            // see insertRecord() for explanations on this check
            if($this->isReadonly())throw new Exception_AccessDenied('Operation not allowed!');

            // create or update records in related entities if need
            if (!empty($this->join_entities)){
                foreach ($this->join_entities as $alias=>&$entity_item) {
                    // reference fields should be skipped as we don't update dictionaries automatically
                    if($this->fieldExists($entity_item['join_field'])&&
                    $this->getField($entity_item['join_field'])->datatype()=='reference')continue;
                    // readonly entities should not be update
                    if($entity_item['readonly']===true)continue;
                    if($entity_item['reference_type']=='master'&&(isset($entity_item['updated']))&&($entity_item['updated']==true)) {
                        // get value from DB because controller can not define this field in actual field list
                        // also, this field can not defined in field list
                        // field may be in another entity
                        $joined_field_value = $this->api->db->dsql()->table($entity_item['table'])//($this->entity_code)
                            ->field($entity_item['join_field'])
                            ->where('id',$this->id);
                        $joined_field_value = $joined_field_value->do_getOne();
                        if (empty($joined_field_value))
                            $this->setFieldVal($entity_item['join_field'],
                                                $this->dsql('modify_'.$alias,false)->do_insert());
                        else{
                            $this->dsql('modify_'.$alias,false)
                                ->where('id',$joined_field_value)
                            ;
                            //$this->logVar($this->dsql('modify_'.$alias)->update(), $this->short_name);
                            $this->dsql('modify_'.$alias,false)->do_update();
                        }
                        $this->dsql['modify_'.$alias]=null;
                    }
                    // related join type has fields in reverse, so we process it differently
                    elseif($entity_item['reference_type']=='related' and (isset($entity_item['updated'])) and ($entity_item['updated']==true)){
                        $joined_field_value = $this->api->db->dsql()->table($entity_item['entity_name'])
                            ->field('id')
                            ->where($entity_item['join_field'],$this->id);
                        $joined_field_value = $joined_field_value->do_getOne();
                        if (empty($joined_field_value)&&$entity_item['required'])
                            $this->setFieldVal($entity_item['join_field'],
                                                $this->dsql('modify_'.$alias)->do_insert());
                        else{
                            $this->dsql('modify_'.$alias,false,$entity_item['entity_name'])
                                ->where('id',$joined_field_value)
                                // FIXME: tricky set to avoid exception, http://adevel.com/fuse/mantis/view.php?id=2698
                                ->set($entity_item['join_field'],$this->id)
                            ;
                            $this->dsql('modify_'.$alias,false)->do_update();
                        }
                        $this->dsql['modify_'.$alias]=null;
                    }
                    $entity_item['updated']=false;
                }
            }

            if (isset($this->fields['updated_dts']))
                $this->dsql('modify',false)->setDate('updated_dts');

            $this->dsql('modify',false)->where('id',$id);
            //$this->logVar($this->dsql('modify',false)->update());

            $this->dsql('modify',false)->do_update();
            unset($this->dsql['modify']); // clear object

            $this->afterUpdate($id);
            $this->api->db->commit();
        }catch(Exception $e){
            $this->api->db->rollback();
            throw $e;
        }

        // reloading data as it will be required later
        $this->loadData($id);

        return $this;
    }

    public function archive(){
        if(!$this->fieldExists('archive'))throw new Exception_InitError($this->short_name.
            " does not have archive ability");
        $this->set('archive',true)->updateRecord($this->get('id'));
        return $this;
    }
    public function delete(){
        $r=$this->deleteRecord($this->get('id'));
        return $r;
    }
    /**
     * Restores deleted record if possible
     * Only records marked as deleted (deleted='Y') can be restored, until they are deleted permanently
     * This method must be overridden as different entities require different processing
     * for undelete
     */
    function restore($id){
        // TODO: implement restore for some controllers
        throw new Exception_NotImplemented("Restore is not implemented for ".get_class($this));
    }
    protected function deleteRecord($id=null) {
        if(is_null($id))$id=$this->get('id');
        else $this->loadData($id);
        // see insertRecord() for explanations on this check
        if($this->isReadonly())throw new Exception_AccessDenied('Operation not allowed!');

        // some mega-clever developers allow to delete records that are not allowed to delete,
        // so we check again and give exception
        if($this->getRelatedEntities())throw new Exception_InitError("Entity $this->short_name:$id is not allowed to delete");
        $this->api->db->beginTransaction();
        try{
            $this->beforeDelete($this->data);
            if (isset($this->fields['deleted'])) {
                $dq = $this->dsql(null,false)->where('id',$id)
                        ->set('deleted','Y');

                if (isset($this->fields['deleted_dts']))
                    $dq->setDate('deleted_dts');

                $dq->do_update();

            }
            else
                $this->dsql(null,false)->where('id',$id)->do_delete();

            $this->afterDelete($id);
            $this->api->db->commit();
        }catch(Exception $e){
            $this->api->db->rollback();
            throw $e;
        }
        // if data was loaded into model - we should change state to not loaded
        $this->unloadData();

        return $this;
    }

    /**
     * Get fieldname with associated table alias
     * @param string $fieldname name of field (key from fields prop)
     * @return string
     */
    public function fieldWithAlias($fieldname) {
        // dot in fieldname means there is alias already
        if(strpos($fieldname,'.')!==false)return $fieldname;
        // parenthesis in fieldname means it is a function, no alias required
        if(strpos($fieldname,'(')!==false)return $fieldname;
        // now we need to remove any < > like in etc. from field name
        $field=$this->parseFieldName($fieldname);
        $sign=$this->parseFieldName($fieldname,'sign');
        if (!isset($this->fields[$field])) {
            $res = (is_null($this->table_alias)?'':"$this->table_alias.").$fieldname;
        } else {
            if ($this->fields[$field]->isExternal())
                $alias = $this->fields[$field]->alias();
            else
                $alias = $this->table_alias;

            $res = (empty($alias)?$this->fields[$field]->dbname():$alias.'.'.$this->fields[$field]->dbname()).$sign;
        }

        return $res;
    }
    /**
     * Parses a string to get field name from it
     * Sometimes conditions passed as 'fieldname>' or 'fieldname like', and therefore
     * we can't use this string as a field name
     * This function parses such string to get actual field name from it
     */
    public function parseFieldName($fieldname,$return='field'){
        $c=substr($fieldname,-1,1);
        if(substr($fieldname,-2,2)=='<>'){
            $field=trim(substr($fieldname,0,-2));
            $sign='<>';
        }
        elseif(substr($fieldname,-2,2)=='<='||substr($fieldname,-2,2)=='>='){
            $field=trim(substr($fieldname,0,-2));
            $sign=substr($fieldname,-2,2);
        }
        elseif($c=='<' || $c=='>' || $c=='='){
            $field=trim(substr($fieldname,0,-1));
            $sign=$c;
        }
        elseif(substr($fieldname,-5,5)==' like'){
            $field=trim(substr($fieldname,0,-5));
            $sign=' like';
        }
        elseif(substr($fieldname,-7,7)==' not in'){
            $field=trim(substr($fieldname,0,-7));
            $sign=' not in';
        }
        elseif(substr($fieldname,-3,3)==' in'){
            $field=trim(substr($fieldname,0,-3));
            $sign=' in';
        }
        elseif(substr($fieldname,-3,3)==' is'){
            $field=trim(substr($fieldname,0,-3));
            $sign='';
        }
        elseif(substr($fieldname,-12,12)==' is not null'){
            $field=trim(substr($fieldname,0,-12));
            $sign=' is not null';
        }
        else{
            $field=$fieldname;
            $sign='';
        }
        // cutting off table aliases
        if(strpos($field,'.')!==false)$field=substr($field,strpos($field,'.')+1);
        // either field or sign:
        return $$return;
    }

    /**
    * load data into model for ID
    * Sets the ID for the model so it can be used later
    * @param int $id if null, tries to load data from the previous ID (refresh)
    * @param string $get_fields may have values:
    *   - bool true: loads all fields, not only visible
    *   - bool false: default, loads only getActualFields() fields
    *   - array(): loads fields set in array
    *   arrays here are arrays returned by getActualFields(), getOwnFields(), etc.
    */
    public function loadData($id=null,$get_fields=false) {
        //echo "loading {$this->name} $id<br/>";
        $this->beforeLoad($id);
        if(is_null($id))$id=$this->id;
        else $this->id=$id;
        $this->resetQuery('loadData_'.$id)->setQueryFields('loadData_'.$id,$get_fields);
        $q=$this
            ->dsql('loadData_'.$id)
            ->where($this->fieldWithAlias('id'),$this->id)
        ;
        //if($this instanceof Model_Invoice)
        //  $this->logVar($q->select(),"$this->short_name:");
        //if($this instanceof Model_Invoice)
        //  $this->logVar($q->do_getHash(),"$this->short_name:");
        $this->data=$this->original_data=$q->do_getHash();
        if(!$this->data){
            $this->api->getLogger()->logLine("No data with id: ".$id." for: ".get_class($this).
                    " but got no data. Query: ".$q->select()."\n");
        }
        $this->changed=false;
        $this->afterLoad();
        return $this;
    }

    /**
     * Unloads all data loaded into model, sets its state as empty
     * Controller::update() method will insert new record after calling this method
     */
    public function unloadData(){
        $this->id=null;
        $this->data=array();
        $this->original_data=array();
        return $this;
    }

    function __toString() {
        try{
            $r=$this->toString();
        }catch(Exception_InitError $e){
            return "Failed to load entity: no ID";
        }
        return (string)$r;
    }

    /**
    * return array with field values or one value for some field
    */
    public function get($field=null,$mandatory=true){
        if($field && !$this->fields[$field]){
            throw new Exception_InstanceNotLoaded('Field '.$field.' is not defined in '.$this->name);
        }
        if (empty($this->data) && $mandatory===true)
            throw new Exception_InstanceNotLoaded('Data was not loaded for '.$this->name);

        $res=null;
        if (is_null($field))
            $res = $this->data;
        elseif (!is_array($field)) {
            //if(!isset($this->fields[$field]))throw new Exception_InitError('Field `'.$field.'` is not defined in '.$this);
            if(!array_key_exists($field,$this->data)&&method_exists($this->api,'getSysConfig') && $this->api->getSysConfig('debug_global')&&$this->api->getSysConfig('debug_warn_get'))
                $this->api->getLogger()->logVar("Field $field does not exist in $this->short_name");
            elseif ($this->fields[$field]->datatype()=='boolean'){
                $res=$this->data[$field]=='Y'?true:false;
            }
            else $res = $this->data[$field];
        }
        else {
            $res = array();
            foreach ($field as $fieldname){
                if(!array_key_exists($fieldname,$this->data)&&$this->api->getSysConfig('debug_global')&&$this->api->getSysConfig('debug_warn_get'))
                    $this->api->getLogger()->logVar("Field $fieldname does not exist in $this->short_name");
                elseif ($this->fields[$field]->datatype()=='boolean'){
                    $res[$fieldname]=$this->data[$field]=='Y'?true:false;
                }
                else $res[$fieldname] = $this->data[$fieldname];
            }
        }

        return $res;
    }
    public function getOriginal($field=null){
        if(empty($this->original_data))return null;
        return is_null($field)?$this->original_data:$this->original_data[$field];
    }
    /**
     * Checks changes made in entity since last load and returns hash of changed fields in form of
     * array($field_name=>array('old'=>$old_value,'new'=>$new_value))
     * Data provided is checked against $this->original_data array
     *
     * @param array $data array of data to check, if empty - equals to $this->data
     */
    public function whatChanged($data=array()){
        if(is_null($data))$data=$this->data;
        $result=array();
        // we will go through all entity fields
        foreach($this->getAllFields() as $name=>$def){
            // if no such field in provided data - it was not changed
            if(!array_key_exists($name,$data))continue;
            if($data[$name]!=$this->original_data[$name])$result[$name]=array('old'=>$this->original_data[$name],'new'=>$data[$name]);
        }
        return $result;
    }
    /**
     * Checks if specidied field value was changed since last load
     * If $value provided, check against this value, in other way field
     * is checked against its current value
     */
    public function isChanged($field,$value='** not set **'){
        if($value==='** not set **')$value=$this->data[$field];
        return (isset($this->original_data[$field])?
            $this->original_data[$field]:null)!==$value;
    }
    /**
     * Returns all rows from the Model
     * where conditions are applied
     * @param array $fields assoc array with fields to retrieve in format 'field'=>'alias'
     *      if null - return all fields
     */
    public function getRows($fields=array()) {
        $q=$this->resetQuery('get_rows')->dsql('get_rows');
        //$q=$this->resetQuery('get_rows')->view_dsql('get_rows');
        $this->setQueryFields('get_rows',empty($fields)?false:$fields);
        //$this->logVar($q->select(),$this->short_name);
        return $q->do_getAllHash();
    }
    /**
     * Returns totals for specified rows
     */
    function getRowTotals($fields){
        $sum=array();
        foreach($fields as $field){
            $sum[$field]=0;
        }
        $rows=$this->getRows($fields);
        foreach($rows as $row){
            foreach($fields as $field){
                $sum[$field]+=$row[$field];
            }
        }
        return $sum;
    }
    /**
      * Load data by other field than ID
      */
    public function loadBy($field,$value=null,$case_insensitive=false){
        $id=$this->getBy($field,$value,$case_insensitive);
        if($id)$this->loadData($id['id']);
        return $this;
    }
    /**
     * Returns the hash by any field of the entity's table
     * TODO: if required - returns data by several fields similar to methods like dsql::set()
     * @param string $field
     * @param mixed $value
     * @param boolean $case_insensitive if true - for string fields makes condition case insensitive,
     * for other fields makes no sense
     */
    public function getBy($field,$value=null,$case_insensitive=false){
        if(is_array($field))$instance=join(array_keys($field));
        else $instance=$field;
        $data = $this->setQueryFields("getby_$instance")
            ->dsql("getby_$instance");
        if(is_null($value)&&is_array($field)){
            foreach($field as $key=>$val){
                if($case_insensitive&&$this->getField($field)->datatype()=='string')
                    $data->where('lcase('.$this->fieldWithAlias($key).')',strtolower($val));
                else $data->where($this->fieldWithAlias($key),$val);
            }
        }
        else{
            if($case_insensitive&&$this->getField($field)->datatype()=='string')
                $data->where('lcase('.$this->fieldWithAlias($field).')',strtolower($value));
            else $data->where($this->fieldWithAlias($field),$value);
        }
        //$this->logVar($data->select());
        $data=$data->do_getHash();
        $this->resetQuery("getby_$instance");
        return $data;
    }

    /**
     * return TRUE if current entity is readonly
     */
    protected function isReadonly() {
        return false;
    }

    protected function defineAuditFields() {
        // some audit fields might be defined in the model explicitely
        // as they play important role in system logics
        if(!isset($this->fields['created_dts']))
            $this->newField('created_dts')
                    ->datatype('datetime')
                    ->caption('Created')
                    ->readonly(true)
                    ->visible(false)
                    ->editable(false)
                    ->system(true)
            ;
        if(!isset($this->fields['upadted_dts']))
            $this->newField('updated_dts')
                    ->datatype('datetime')
                    ->caption('Updated')
                    ->readonly(true)
                    ->visible(false)
                        ->editable(false)
            ;
        if(!isset($this->fields['deleted']))
            $this->newField('deleted')
                    ->datatype('boolean')
                    ->caption('Deleted')
                    ->readonly(true)
                    ->visible(false)
                    ->editable(false)
            ;

        if(!isset($this->fields['deleted_dts']))
            $this->newField('deleted_dts')
                    ->datatype('datetime')
                    ->caption('Deleted')
                    ->readonly(true)
                    ->visible(false)
                    ->editable(false)
            ;
    }

    /**
    * return string representation of record
    */
    public function toString() {
        return $this->entity_code.' #'.$this->getId();
    }

    function getEntityCode(){
        return $this->entity_code;
    }

    function getFriendlyName(){
        return $this->entity_code;
    }

    /**
     * Returns entity code (table name) which contains image data for the entity
     * It is entity_code by default, but, say, for contractor_self it is other table (company)
     */
    function getImageEntity(){
        return $this->entity_code;
    }

    /**
     * Return expression for get string representation of record in SQL
     * @param string $source_field field in source table (with alias)
     * @param string $dest_fieldname name of field in query result
     * @param string $expr expression for get data from entity (this param should be only in Model_Table!)
     * @return string
     */
    public function toStringSQL($source_field, $dest_fieldname, $expr = 'name') {
        if($this->fieldExists($expr))
            return '(select '.$expr.' from '.$this->entity_code.
                    '  where id = '.$source_field.') as '.$dest_fieldname;

        return 'concat("'.$this->entity_code.' #",'.$source_field.') '.$dest_fieldname;

    }
    /**
     * This function returns false if there are no entities in DB which reference this entity
     * Otherwise it returns hash in form $name=>$count,
     *      where $name is the name of the related entity (any name you want to see)
     *          and $count is count of related entities for this record
     * Model uses field definition to get info about related tables and then checks every related entity
     * for data with this model ID.
     * TODO: Some entities may have cascade relations, like address for contractor. Such entities should be marked with
     * TODO: FieldDefinition::relation() method call
     * FIXME: for the time being this method introduced in certain Models:
     * - Contractor
     */
    public function getRelatedEntities(){
        return false;
    }
    /**
     * Prepares SQL query to get this entity related records
     * Used primarily in getRelatedEntities() method, and also
     * in some other places like Document's locked field calculation
     *
     * This method should return prepared dsql instance or false.
     * No fields should be specified as they are parent method specific
     *
     * @param int $id optional. If specified, condition created by this ID, else it is by a host query ID
     */
    protected function getRelatedEntitiesSQL($id=null){
        return false;
    }
    /**
     * Performs data validation before it is updated
     * It calls all the validations set by FieldDefinition::validate() if any
     */
    protected function validateData($data){
        foreach($this->getActualFields() as $field=>$def){
            // some fields somehow do not have definition...
            try {
                if(!$this->isChanged($field,isset($data[$field])?$data[$field]:null))continue;
                if(is_object($def) && $def->validate()){
                    $res=call_user_func_array($def->validate(),array($data[$field],$this->owner));
                    if($res===false)throw new Exception_ValidityCheck('Incorrect format');
                    if(is_string($res))throw new Exception_ValidityCheck($res);
				}
			}catch(Exception_ValidityCheck $v){
				$v->setField($field);
				throw $v;
			}
		}
		return $this;
	}
	/**
	 * This method is quite similar to all entities with 'name' field
	 */
	public function validateName($data){
		if($this->isInstanceLoaded() && !$this->isChanged('name',$data['name']))return true;
		// should not be duplicate names
		if($this->dsql()->where('name',$data['name'])->field('count(*)')->do_getOne()>0)
			throw new Exception_ValidityCheck('Duplicate '.$this->getFriendlyName().' name');
		return true;
    }
    function destroy(){
        foreach ($this->dsql as $k=>$q){
            unset($q);
        }
        foreach ($this->fields as $k=>$f){
            unset($this->fields[$k]);
        }
        return parent::destroy();
    }
}
