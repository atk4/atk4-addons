<?php
class Form_UploadTest extends Form {
	function init(){
		parent::init();
		//$this->addField('line','test');
		$u=$this->addField('upload','file');
		$u->setController('Controller_Filestore_File');
		//$this->addSubmit('Upload');
	}
}
