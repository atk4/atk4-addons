<?php
namespace filestore;
class Field_Image extends Field_File {
    public $use_model = 'filestore/Image';


    /* Adds a calculated field for displaying a thubnail of this image */
    function addThumb($name=null){
    	if(!$name)$name=$this->getDereferenced().'_thumb';

    	var_Dump($name);

    }
}
