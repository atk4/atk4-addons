<?php
/**
 * Generic model class.
 * Handles data operations and formats data for views
 *
 * @author Camper (camper@agiletech.ie) on 26.03.2009
 */
abstract class Model extends AbstractModel{
//	protected $owner=null;	// object that uses this model
	protected $dq; // dymanic query object
	protected $entity_code;
	protected $entity_reference_url=null;	// the URL from where this model will retrieve records
											// when used in flexbox form field
											// see also Form_Field_flexbox::setDictionary() and
											// ASForm::addField()
											// normally this field should be set during Model init

	// here we can init the object
	public function init(){
		parent::init();
	}

	public function dsql() {
		return $this->api->db->dsql();
	}
	/**
	 * Sets the URL for reference dictionary
	 */
	protected function setReferenceURL($url){
		$this->rentity_reference_url=$url;
		return $this;
	}
	function getReferenceURL(){
		return $this->entity_reference_url;
	}

}
