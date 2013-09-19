<?php
class Model_Feedback extends SQL_Model {
	public $entity_code='feedback';
	public $table_alias='fb';

	function init(){
		parent::init();
		$this->addField('name')->caption('Your Name');
		$this->addField('ref')->system(true);
		$this->addField('descr')->caption('Suggestion or Feedback')->type('text');
		$this->addField('date')->system(true)->defaultValue(date('Y-m-d'));
	}
}
