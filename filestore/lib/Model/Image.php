<?php
namespace filestore;
class Model_Image extends Model_File {
	//protected $entity_code='filestore_image';
	public $default_thumb_width=140;
	public $default_thumb_height=140;

	// Temporarily, to be replaced in 4.1 to use Model_File
    // TODO: replace with file_model_name and no auto-prefixing with filestore/
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

		$this->addExpression('thumb_url')->set(array($this,'getThumbURLExpr'));
	}
	/* Produces expression which calculates full URL of image */
	function getThumbURLExpr($m,$q){
        $m=$this->add('filestore/Model_'.$this->entity_file);
        $m->addCondition('id',$this->i->fieldExpr('thumb_file_id'));
        return $m->fieldQuery('url');
	}
	function toStringSQL($source_field, $dest_fieldname){
		return $source_field.' '.$dest_fieldname;
	}
	function performImport(){
		parent::performImport();

        $this->createThumbnails();

		// Now that the origninal is imported, lets generate thumbnails
        /*
		$this->performImport();
		$this->update();
		$this->afterImport();
        */
        return $this;
	}
    function createThumbnails(){
        if($this->id)$this->load($this->id);// temporary
        $this->createThumbnail('thumb_file_id',$this->default_thumb_height,$this->default_thumb_width);
    }
	function createThumbnail($field,$x,$y){
        // Create entry for thumbnail.
        $thumb=$this->ref($field,'link');
        if(!$thumb->loaded()){
            $thumb->set('filestore_volume_id',$this->get('filestore_volume_id'));
            $thumb->set('original_filename','thumb_'.$this->get('original_filename'));
            $thumb->set('filestore_type_id',$this->get('filestore_type_id'));
            $thumb['filename']=$thumb->generateFilename();
        }

        if(class_exists('\Imagick',false)){
            $image=new \Imagick($this->getPath());
            //$image->resizeImage($x,$y,\Imagick::FILTER_LANCZOS,1,true);
            $image->cropThumbnailImage($x,$y);
            $this->hook("beforeThumbSave", array($thumb));
            $image->writeImage($thumb->getPath());
            $thumb["filesize"] = filesize($thumb->getPath());
        }elseif(function_exists('imagecreatefromjpeg')){
            list($width, $height, $type) = getimagesize($this->getPath());
            ini_set("memory_limit","1000M");
            

            $a=array(null,'gif','jpeg','png');
            $type=@$a[$type];
            if(!$type)throw $this->exception('This file type is not supported');

            //saving the image into memory (for manipulation with GD Library)
            $fx="imagecreatefrom".$type;
            $myImage = $fx($this->getPath());

            $thumbSize = $x;    // only supports rectangles
            if($x!=$y && 0)throw $this->exception('Model_Image currently does not support non-rectangle thumbnails with GD extension')
                ->addMoreInfo('x',$x)
                ->addMoreInfo('y',$y);

            // calculating the part of the image to use for thumbnail
            if ($width > $height) {
                $y = 0;
                $x = ($width - $height) / 2;
                $smallestSide = $height;
            } else {
                $x = 0;
                $y = ($height - $width) / 2;
                $smallestSide = $width;
            }

            // copying the part into thumbnail
            $myThumb = imagecreatetruecolor($thumbSize, $thumbSize);
            imagecopyresampled($myThumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);

            //final output
            imagejpeg($myThumb, $thumb->getPath());
            imageDestroy($myThumb);
            imageDestroy($myImage);
        }else{
            // No Imagemagick support. Ignore resize
            $thumb->import($this->getPath(),'copy');
        }
        $thumb->save();  // update size and chmod
    }
    function afterImport(){
        // Called after original is imported. You can do your resizes here

        $f=$this->getPath();

        $gd_info=getimagesize($f);
    }
    function setMaxResize(){
    }
}
