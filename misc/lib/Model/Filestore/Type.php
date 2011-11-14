<?php
class Model_Filestore_Type extends Model_Table {
	public $entity_code='filestore_type';
	function init(){
		parent::init();
		$this->newField('name')
			;
		$this->newField('mime_type')
			;
		$this->newField('extension')
			;
		// TODO: extension should be substituted when recording filename
	}
}
