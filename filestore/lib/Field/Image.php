<?php
namespace filestore;
class Field_Image extends Field_File
{
    public $use_model = 'filestore/Model_Image';

    /**
     * Adds a calculated field for displaying a thubnail of this image
     *
     * @param string $name
     * @param string $thumb
     * 
     * @return this
     */
    function addThumb($name = null, $thumb = 'thumb_url')
    {
        if (!$name) {
            $name = $this->getDereferenced() . '_thumb';
        }

        $self = $this;
        $this->owner->addExpression($name)->set(function($m) use ($self, $thumb){
            return $m->refSQL($self->short_name)->fieldQuery($thumb);
        });
        
        return $this;
    }
}
