<?php
namespace filestore;
class Field_Image extends Field_File {
    public $use_model = 'filestore/Image';


    /* Adds a calculated field for displaying a thubnail of this image */
    function addThumb($name=null,$thumb='thumb_url'){

        if(!$name)$name=$this->getDereferenced().'_thumb';

        $self=$this;
        $this->owner->addExpression($name)->set(function($m)use($self,$thumb){
            return $m->refSQL($self->short_name)->fieldQuery($thumb);
        });
        return $this;
    }
}
