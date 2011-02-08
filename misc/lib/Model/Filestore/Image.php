<?php
class Model_Filestore_Image extends Model_Filestore_File {
	//protected $entity_code='filestore_image';

	// Temporarily, to be replaced in 4.1 to use Model_File
	public $entity_file='Filestore_File';

	function defineFields(){
		parent::defineFields();

		$this->addRelatedEntity('i','filestore_image','original_file_id','inner','related',true);

		/*
		$this->newField('original_file_id')
			->datatype('reference')
			->refModel('Model_'.$this->entity_file)
			->caption('Original File')
			;
			*/

		$this->newField('thumb_file_id')
			->datatype('reference')
			->relEntity('i')
			->refModel('Model_'.$this->entity_file)
			->caption('Thumbnail')
			;
	}
	function toStringSQL($source_field, $dest_fieldname){
		return $source_field.' '.$dest_fieldname;
	}
	function import($source,$mode='upload'){
		parent::import($source,$mode);

		// Now that the origninal is imported, lets generate thumbnails
		$this->performImport();
		$this->update();
		$this->afterImport();
	}
	function afterImport(){
		// Called after original is imported. You can do your resizes here

		$f=$this->getPath();
		
		$gd_info=getimagesize($f);
		var_Dump($f);

	}
	function setMaxResize(){
	}
}
