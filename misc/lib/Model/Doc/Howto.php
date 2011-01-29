<?php
class Model_Doc_Howto extends Model_Table {
	public $entity_code='doc_howto';

	function defineFields(){
		parent::defineFields();

		$this->newField('title')
			->mandatory(true)
			;

		$this->newField('keywords')
			;

		$this->newField('inherit')
			;

		$this->newField('descr')
			->datatype('text')
			;

		$this->newField('example')
			->datatype('text')
			;

		$this->newField('approved')
			->datatype('boolean')
			;
	}
}
