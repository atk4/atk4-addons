<?php
class Model_Filestore_Image extends Model_Table {
	protected $entity_code='filestore_image';

	// Temporarily, to be replaced in 4.1 to use Model_File
	public $entity_file='Filestore_File';

	function defineFields(){
		parent::defineFields();
		$this->newField('name')
			;
		$this->newField('original_file_id')
			->datatype('reference')
			->refModel('Model_'.$this->entity_file)
			->caption('Original File')
			;

		$this->newField('thumb_file_id')
			->datatype('reference')
			->refModel('Model_'.$this->entity_file)
			->caption('Thumbnail')
			;
	}
	function toStringSQL($source_field, $dest_fieldname){
		return $source_field.' '.$dest_fieldname;
	}
	function import($source,$mode='upload'){
		/*
		   Import image from different location. See Filestore_File->import() for information about upload modes.

		   */

		$this->refModel('original_file_id')->import($source,$mode);

		// Now that the origninal is imported, lets generate thumbnails
		$this->afterImport();
	}
	function afterImport(){
		// Called after original is imported. You can do your resizes here

		$this->resizeImage();
	}
	function setMaxResize(){
	}
}
