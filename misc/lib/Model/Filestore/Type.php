<?php
class Model_Filestore_Type extends Model_Table {
	protected $entity_code='filestore_type';
	function defineFields(){
		parent::defineFields();
		$this->newField('name')
			;
		$this->newField('mime_type')
			;
		$this->newField('extension')
			;
		// TODO: extension should be substituted when recording filename
	}
}
