<?php
namespace filestore;
class Model_Type extends \Model_Table {
	public $table='filestore_type';
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
