<?php
namespace filestore;
class Model_Image extends Model_File {
	//protected $entity_code='filestore_image';
	public $default_thumb_height=140;
	public $default_thumb_width=140;

	// Temporarily, to be replaced in 4.1 to use Model_File
	public $entity_file='File';

	function init(){
		parent::init();

		//$this->addRelatedEntity('i','filestore_image','original_file_id','inner','related',true);
        $this->i=$this->join('filestore_image.original_file_id');

        /*
		$this->newField('original_file_id')
			->relEntity('i')
			->datatype('int')->system(true);
        /*
			->refModel('Model_'.$this->entity_file)
			->caption('Original File')
			;
			*/

        $this->i->hasOne('filestore/'.$this->entity_file,'thumb_file_id')
            ->caption('Thumbnail');
        /*
            addField('thumb_file_id')
			->datatype('reference')
			->refModel('Model_'.$this->entity_file)
			->caption('Thumbnail')
			;
         */
	}
	function toStringSQL($source_field, $dest_fieldname){
		return $source_field.' '.$dest_fieldname;
	}
	function performImport(){
        echo "i";
		parent::performImport();

        echo "g";
        $original=$this->getPath();
        echo "o";

		$this->createThumbnail('thumb_file_id',$this->default_thumb_height,$this->default_thumb_width);

		// Now that the origninal is imported, lets generate thumbnails
        /*
		$this->performImport();
		$this->update();
		$this->afterImport();
        */
        echo "I";
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
