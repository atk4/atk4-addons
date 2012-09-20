<?php
namespace filestore;
class Field_Thumb extends \Field_Expression {
	public $image_field;
	public $thumb_field;

	/* Associates thumbnail with the image. Note that this
	field is only used for display purpose */
	function setImage($image_field, $thumb='thumb_file_id'){

		if(is_string($image_field)){
			$image_field=$this->owner
				->getElement($image_field);
		}

		$this->image_field=$image_field;
		$this->thumb_field=$thumb;

        $this->set(array($this,'getThumbURL'));


		return $this;
	}
    function getThumbURL($m,$q){

        //  Profile  / thumb32 = expr
        //    Profile->image_id->getModel()  <--  Model_Profile_Image (Model_Image)
        //      Image->thumb_64  <--- Model_File
        //

    	$m=$this->image_field->getModel();
        return $m->refSQL('picture_id/thumb_64')->debug()->fieldQuery('url');


        // Profile->refSQL('picture_id')->refSQL('thumb_64');  <-- Model_File
        // 
        // selcet * from image where id=profile.picture_id

        // select * from file join image on image.thumb_64=file.id where image.id=<profile.picture_id>


        exit;
    	$m->debug();
    	$m->setActualFields(array('id'))->load(1);
    	exit;

    	// Construct a good expression


		$p=$this->newInstance();

		// Picture needs to be joined with filestore
		$pic=$p
           ->join('filestore_file','filestore_file_id')
           ;

        // If we need thumbnail, that's few more joins
        if($is_thumb){
        	$pic=$pic
	        	->join('filestore_image.original_file_id')
	        	->join('filestore_file','thumb_file_id')
	        	;
        }

        // Finally we need volume
        $v=$pic->join('filestore_volume');

        // Construct the field
        $p->addExpression($field,function($m,$s)use($v,$p,$pic){
            return $s->expr(
                'COALESCE(
                        concat("'.$p->api->pm->base_path.'",'.
                            $v->fieldExpr('dirname').
                            ',"/",'.
                            $pic->fieldExpr('filename').
                        ')
                , "'.$p->api->locateURL('template','images/portrait.jpg').'") ');
        });
        return $p;







        return $this->owner->_dsql()->expr(

        	);

        if(!is_string($this->expr) && is_callable($this->expr))
            return '('.call_user_func($this->expr,$this->owner,$this->owner->dsql(),$this).')';
        
        if($this->expr instanceof DB_dsql)return $this->expr;

    }

} 