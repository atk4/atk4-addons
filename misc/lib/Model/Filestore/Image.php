<?php
class Model_Filestore_Image extends Model_Filestore_File {
	//protected $entity_code='filestore_image';
	public $default_thumb_height=140;
	public $default_thumb_width=140;

	// Temporarily, to be replaced in 4.1 to use Model_File
	public $entity_file='Filestore_File';

	function defineFields(){
		parent::defineFields();

		$this->addRelatedEntity('i','filestore_image','original_file_id','inner','related',true);

        /*
		$this->newField('original_file_id')
			->relEntity('i')
			->datatype('int')->system(true);
        /*
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
	function performImport(){
		parent::performImport();

        $original=$this->getPath();

		$this->createThumbnail('thumb_file_id',$this->default_thumb_height,$this->default_thumb_width);

		// Now that the origninal is imported, lets generate thumbnails
        /*
		$this->performImport();
		$this->update();
		$this->afterImport();
        */
        return $this;
	}
	function createThumbnail($field,$x,$y){
        // Create entry for thumbnail.
        $thumb=$this->getRef($field);
        if(!$thumb->isInstanceLoaded()){
            $thumb->set('filestore_volume_id',$this->get('filestore_volume_id'));
            $thumb->set('original_filename','thumb_'.$this->get('original_filename'));
            $thumb->set('filestore_type_id',$this->get('filestore_type_id'));
            $thumb->update();
            $this->set($field,$thumb->get('id'));
        }

        $image=new Imagick($this->getPath());
        $image->resizeImage($x,$y,Imagick::FILTER_LANCZOS,1,true);
        $image->writeImage($thumb->getPath());

        $thumb->import(null,'none');
		$thumb->update();  // update size and chmod
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
