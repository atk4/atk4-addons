<?php
/**
 * This class contains field definition
 *
 * Most important and interesting feature of the definition is reference to other entities
 * Reference could be of two types:
 * 1) Reference Model - created by refModel($model_name) method
 * 		this type useful for cases when you need to show/edit entity defined by ID in current model
 * 		i.e. we define Invoice model which has contractor_id field, we define this field as:
 * 		$model->newField('contractor_id')->refModel('Model_Contractor')->displayField('legal_name'),
 * 		and this makes model display contractor_id field as string selected from Model_Contractor's table,
 * 		as well as edit this field in Forms using referenced model
 * 		Field is being selected in resulting query as "(select legal_name from ...) contractor_id", i.e.
 * 		no joins used
 * 2) Related Entity - created by relEntity() and addRelatedEntity() methods
 * 		this type can be used together with type (1)
 * 		This method results in joins in Model's select
 * 		i.e. we define Contractor Model and we need to show/edit address fields, which are in separate table
 * 		We join related entity by calling:
 * 		$model->addRelatedEntity('a','address','reg_address_id','left outer');
 * 			this will allow to join table 'address' with alias 'a' by 'a.id=contractor.reg_address_id'
 * 		Then we add fields from the joined table to Contractor Model as this:
 * 		$model->newField('country_id')
 *   			->caption('Region')
 *   			->refModel('Model_Country')
 *   			->relEntity('a','country_id');
 * 			this will add field 'country_id' from table with alias 'a' (which is 'address', as defined before), this
 * 			field will be named 'country_id' in our model (and referenced like this in grids/forms), and will
 * 			have reference model Model_Country for edit purposes
 * 		$model->newField('state')
 *   			->datatype('string')
 *   			->relEntity('a','state');
 * 			this will ass field 'state' from table with alias 'a', this field will be referenced as 'state' in
 * 			Model_Contractor
 * Setting related entity with addRelatedEntity() will not cause join explicitely, joins are made on the basis of
 * fields defined in model, i.e. only if we add field with relEntity() defined.
 * This concept is complicated, so here is another example of related entities join.
 * We create a model with subsequent joins: Model_Company, which is based on 'system_sys_user table and needs
 * 'system' table to have access to system data and 'contractor' table, which is joined to 'system'. We do like this:
 * $model->addRelatedEntity('s','system','system_id'); // joined 'system' by 'system_sys_user.system_id'
 * $model->newField('system_id')
 *   			->relEntity('s','id')	// field 's.id' will appear in our model as 'system_id'
 *   			->displayField('id')	// data to display will be taken from field 'system.id'
 *
 * Created on 07.04.2009
 */

class MVCFieldDefinition {

	protected $owner;
	protected $api;
	protected $instance_name;
	protected $name;
	protected $caption;
	protected $datatype;
	protected $length;
	protected $list_data;
	protected $ref_model;
	protected $default_value=null;
	protected $entity_alias=null;
	protected $dbname=null;
	protected $display_field='name';	// field name which will be used to select entity name from DB for reference fields

	protected $readonly=false;	// prevents edit
	protected $required=false;
	protected $visible=true;
	protected $editable=true;		// to show or not on forms. allows edit in distinction with readonly
	protected $searchable=false;
	protected $sortable=false;
	protected $system=false;		// if true, this field never shown to user, but is editable
	protected $calculated=false;
	protected $aggregate=false;	// aggregated status. if true, field is wrapped in sum() in selects
								// it is also excluded from edits
	protected $allow_html=false;	// allow or not HTML code in field value
								// fields with allowed HTML should be processed separately to avoid injections, etc.

	protected $validation=null;
	protected $params=array();	// additional parameters required on, say, field calculation

	function __construct($owner){
		$this->owner=$owner;
		$this->api=$owner->api;
		$this->instance_name=$this->owner->name.'_undefined';
	}

	public function name($new_value=null) {
		if (is_null($new_value))
			return $this->name;
		else {
			$this->name = $new_value;
			$this->instance_name=$this->owner->name.'_'.$new_value;
			return $this;
		}
	}

	public function caption($new_value=null) {
		if (is_null($new_value)) {
			return (empty($this->caption))?ucwords(str_replace('_',' ',$this->name)):$this->caption;
		}
		else {
			$this->caption = $new_value;
			return $this;
		}
	}
	/**
	 * Sets field readonly
	 * Field is NOT displayed on a form
	 * Field can NOT be saved in generic way (Model::setFieldValue() does not process it)
	 */
	public function readonly($new_value=null) {
		if (is_null($new_value))
			return $this->readonly;
		else {
			$this->readonly = $new_value;
			return $this;
		}
	}
	/**
	 * Allows/prevents field to be shown on a form
	 * Field is NOT displayed on a form if false
	 * Field can be saved
	 */
	public function editable($new_value=null){
		if (is_null($new_value))
			return $this->editable;
		else {
			$this->editable = $new_value;
			return $this;
		}
	}
	public function allow_html($new_value=null){
		if (is_null($new_value))
			return $this->allow_html;
		else {
			$this->allow_html = $new_value;
			return $this;
		}
	}
	public function searchable($new_value=null){
		if(is_null($new_value)){
			return $this->searchable;
		}else{
			if($new_value===true)$this->sortable(true);	// sort searchable fields by default
			$this->searchable=$new_value;
			return $this;
		}
	}
	public function sortable($new_value=null){
		if(is_null($new_value)){
			return $this->sortable;
		}else{
			$this->sortable=$new_value;
			return $this;
		}
	}
	/**
	 * Alias for required()
	 */
	public function mandatory($new_value=null){
		return $this->required($new_value);
	}
	/**
	 * Set/get datatype of the field. Possible types are:
	 * 'string','date','datetime','text','readonly','int','real','boolean','reference','password','list'
	 */
	public function datatype($new_value = null) {
		if (is_null($new_value))
			return (empty($this->datatype))?'string':$this->datatype;
		else {
			if (!in_array($new_value,array(
					'string','date','datetime','text','readonly',
					'int','real','money','boolean','reference','reference_id','password','list',
					'daytime','daytime_total','image','radio','file'
				)))
				throw new Exception_InitError('Unknown datatype: '.$new_value);

			$this->datatype = $new_value;
			return $this;
		}
	}
	public function system($new_value=null){
		if (is_null($new_value))
			return $this->system;
		else {
			$this->system = $new_value;
			if($new_value===true)$this->editable(false);	// hide system fields by default
			return $this;
		}
	}
	/**
	 * Sets/returns calculated status
	 * Calculated fields are retrieved in a special way and are not set in update queries
	 */
	public function calculated($new_value=null){
		if (is_null($new_value))
			return $this->calculated;
		else {
			if($new_value===true){
				// check some stuff
				if($this->datatype()=='reference'||$this->datatype()=='password'||$this->datatype()=='list')
					throw new Exception_InitError("Datatype '".$this->datatype()."' cannot be calculated");
				$this->readonly(true);
			}
			$this->calculated = $new_value;
			return $this;
		}
	}
	public function aggregate($new_value = null){
		if (is_null($new_value))
			return $this->aggregate;
		else {
			$this->aggregate = $new_value;
			if($new_value===true)$this->editable(false);
			return $this;
		}
	}

	public function length($new_value = null) {
		if (is_null($new_value))
			return (empty($this->length))?255:$this->length;
		else {
			$this->length = $new_value;
			return $this;
		}
	}

	/**
	 * Sets default value for a field
	 */
	public function defaultValue($value='**not_set**'){
		if($value==='**not_set**'){
			return $this->default_value;
		}
		$this->default_value=$value;
		$this->owner->setDefaultField($this->name,$value);
		return $this;
	}

	public function required($new_value=null) {
		if (is_null($new_value))
			return $this->required;
		else {
			$this->required = $new_value;
			return $this;
		}
	}
	/**
	 * Sets the validation method
	 * Validation applied before data is saved
	 * @param array $new_value object and method to call for validation, similar to AModules hook definition
	 */
	public function validate($new_value=null){
		if(is_null($new_value)){
			return $this->validation;
		}else{
			$this->validation=$new_value;
			return $this;
		}
	}
	/**
	 * Get/set visible attribute
	 * If field::visible==false, field is not displayed on form/grid
	 */
	public function visible($new_value=null){
		if(is_null($new_value)){
			return $this->visible;
		}
		$this->visible=$new_value;
		return $this;
	}

	public function refModel($model=null,$loadref=true) {
		if (!is_null($model)) {

            $noid=str_replace('_id','',$this->name);

            if($noid==$this->name || $this->owner->fieldExists($noid)){
				$this->datatype('reference');
				$this->ref_model = $model;
				return $this;

            }

            $this->owner->addField($noid)
                ->visible(true)
                ->editable(false)
                ->readonly(true)
                ->datatype('reference');


			$this->system(true);
			$this->editable(true);
			$this->datatype('reference_id');
			$this->ref_model = $model;
			return $this;
		}
		else{
			if(!$this->ref_model)throw new Exception_InitError("No reference model set for ".$this->owner->name."::$this->name");
			if(!is_object($this->ref_model))$this->ref_model=$this->owner->add($this->ref_model);
            if(!$loadref)return $this->ref_model;
			// trying to load data depending on owner field value
            if($this->datatype=='reference_id' && $this->owner->isInstanceLoaded() && $id=$this->owner->get($this->name))$this->ref_model->loadData($id,true);
			return $this->ref_model;
		}
	}

	public function listData($new_value=null) {
		if (is_null($new_value)) {
			return $this->list_data;
		}
		else {
			$this->list_data = $new_value;
			return $this;
		}
	}

	/**
	 * @return boolean TRUE if field used in external entity
	 */
	public function isExternal() {
		return (empty($this->entity_alias)||$this->entity_alias=='')?false:true; // aliases defined only for external fields
	}

	public function alias($new_value=null) {
		if (is_null($new_value)) {
			return $this->entity_alias;
		}
		else {
			$this->entity_alias = $new_value;
			return $this;
		}
	}

	public function dbname($new_value=null) {
		if (is_null($new_value)) {
			return (empty($this->dbname))?$this->name:$this->dbname;
		}
		else {
			$this->dbname = $new_value;
			return $this;
		}
	}

	public function displayField($new_value=null){
		if(is_null($new_value))return $this->display_field;
		else{
			$this->display_field=$new_value;
			return $this;
		}
	}

	/**
	 * Define properties for external fields (from related entities)
	 * @param string $alias alias for related entity in query
	 * @param string $dbname name of field in db (equal of name if not set)
	 * @return $this
	 */
	public function relEntity($alias, $dbname=null) {
		$this->entity_alias = $alias;
		if (is_null($dbname))
			$dbname = $this->name;

		$this->dbname = $dbname;

		return $this;
	}

	/**
	 * Goal: construct correct field definition in select sql
	 * @param string $alias main entity alias in query
	 * @return string sometring like 'a.dbname as fieldname'
	 */
	public function getDBfield($alias = null) {
		if (!empty($this->entity_alias)) {
			$res = $this->entity_alias.'.'.$this->dbname;
			if ($this->dbname!=$this->name)
				$res .= ' as '.$this->name;
		}else{
			$res = ((is_null($alias))?'':$alias.'.').$this->dbname();
			if ($this->dbname()!=$this->name)
				$res .= ' as '.$this->name;
		}
		return $res;
	}
	/**
	 * Adds a parameter to a field
	 * Parameters may be required on some custom calculations
	 * You can specify either single value (->addParam("my string")) or an array of values
	 * (->addParam(array('p1'=>1,'p2'=>'my string')))
	 *
	 * In second case all array values will be appended to existing values
	 */
	public function parameter($param = null){
		if(!is_null($param)){
			if(is_array($param))$this->params=array_merge($this->params,$param);
			else $this->params=$param;
			return $this;
		}
		return $this->params;
	}
	public function getOwner(){
		return $this->owner;
	}
}
?>
