<?php
namespace filestore;
class Model_Type extends \SQL_Model {
	public $table='filestore_type';
	function init(){
		parent::init();
		
		$this->addField('name')
            ;
		$this->addField('mime_type')
            ;
		$this->addField('extension')
            ;
		// TODO: extension should be substituted when recording filename
	}
}
