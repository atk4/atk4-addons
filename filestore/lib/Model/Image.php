<?php
namespace filestore;
class Model_Image extends Model_File {
	//protected $entity_code='filestore_image';
	public $default_thumb_width=140;
	public $default_thumb_height=140;

	// Temporarily, to be replaced in 4.1 to use Model_File
	public $entity_file='File';

	function init(){
		parent::init();

        $this->i=$this->join('filestore_image.original_file_id');

        /*
        $this->hasOne('filestore/'.$this->entity_file,'original_file_id')
            ->caption('Original File');
         */

        $this->i->hasOne('filestore/'.$this->entity_file,'thumb_file_id')
            ->caption('Thumbnail');
	}
	function toStringSQL($source_field, $dest_fieldname){
		return $source_field.' '.$dest_fieldname;
	}
	function performImport(){
		parent::performImport();

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
        $thumb=$this->ref($field,false);
        if(!$thumb->loaded()){
            $thumb->set('filestore_volume_id',$this->get('filestore_volume_id'));
            $thumb->set('original_filename','thumb_'.$this->get('original_filename'));
            $thumb->set('filestore_type_id',$this->get('filestore_type_id'));
        }

        if(class_exists('\Imagick',false)){
            $image=new \Imagick($this->getPath());
            $image->resizeImage($x,$y,\Imagick::FILTER_LANCZOS,1,true);
            $this->hook("beforeThumbSave", array($thumb));
            $thumb->save(); // generates filename 
            $image->writeImage($thumb->getPath());
            $thumb->import(null,'none');
        }else{
            // No Imagemagick support. Ignore resize
            $thumb->import($this->getPath(),'copy');
        }
		$thumb->save();  // update size and chmod

        $this->set($field,$thumb->get('id'));
	}
	function afterImport(){
		// Called after original is imported. You can do your resizes here

		$f=$this->getPath();
		
		$gd_info=getimagesize($f);
	}
	function setMaxResize(){
	}
}
