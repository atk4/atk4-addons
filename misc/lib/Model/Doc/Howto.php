<?php
class Model_Doc_Howto extends SQL_Model {
	public $entity_code='doc_howto';

	function defineFields(){
		parent::defineFields();

		$this->addField('title')
			->mandatory(true)
			;

		$this->addField('keywords')
			;

		$this->addField('inherit')
			;

		$this->addField('descr')
			->type('text')
			;

		$this->addField('example')
			->type('text')
			;

		$this->addField('approved')
			->type('boolean')
			;
	}
}
