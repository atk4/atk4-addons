<?php
namespace filestore;
class Model_Image extends Model_File
{
    // File model classname
    public $file_model_class = 'filestore/Model_File';
    
    // thumbnail max width/height in pixels
    public $default_thumb_width  = 140;
    public $default_thumb_height = 140;

    function init()
    {
        parent::init();

        $this->i = $this->join('filestore_image.original_file_id');

        $this->i->hasOne($this->file_model_class, 'thumb_file_id')
                ->caption('Thumbnail')
                ->sortable(true)
                ;

        $this->addExpression('thumb_url')
                ->set(array($this, 'getThumbURLExpr'))
                ->caption('Thumb URL')
                ->sortable(true)
                ;
    }
    
    /**
     * Produces DSQL expression which calculates full URL of image
     * 
     * @return DSQL
     */
    function getThumbURLExpr()
    {
        $m = $this->add($this->file_model_class);
        $m->addCondition($m->id_field, $this->i->fieldExpr('thumb_file_id'));
        return $m->fieldQuery('url');
    }
    
    /**
     * Perform file import
     *
     * @return this
     */
    function performImport()
    {
        parent::performImport();

        // Now that the original is imported, lets generate thumbnails
        $this->createThumbnails();
        
        return $this;
    }
    
    /**
     * Generate thumbnails
     *
     * @return void
     */
    function createThumbnails()
    {
        if ($this->id) {
            $this->load($this->id); // temporary
        }
        $this->createThumbnail('thumb_file_id', $this->default_thumb_width, $this->default_thumb_height);
    }
    
    /**
     * Imagick crop
     * 
     * @param Image $i
     * @param int $width
     & @param int $height
     *
     * @return void
     */
    function imagickCrop($i,$width,$height)
    {
        $geo = $i->getImageGeometry();

        if ($geo['width']<$width && $geo['height']<$height) {
            return; // don't crop, image is too small
        }

        // crop the image
        $w = $geo['width']/$width;
        $h = $geo['height']/$height;
        if ($w < $h) {
            $i->cropImage($geo['width'], floor($height*$w), 0, ($geo['height']-$height*$w)/2);
        } else {
            $i->cropImage(ceil($width*$h), $geo['height'], ($geo['width']-$width*$h)/2, 0);
        }
        
        // thumbnail the image
        $i->ThumbnailImage($width, $height, true);
    }

    /**
     * Generate thumbnail
     * 
     * @param string $field Field name
     * @param int $x
     * @param int $y
     *
     * @return void
     */
    function createThumbnail($field, $x, $y)
    {
        // Create entry for thumbnail.
        $thumb = $this->ref($field, 'link');
        if (!$thumb->loaded()) {
            $thumb->set('filestore_volume_id', $this->get('filestore_volume_id'));
            $thumb->set('original_filename', 'thumb_'.$this->get('original_filename'));
            $thumb->set('filestore_type_id', $this->get('filestore_type_id'));
            $thumb['filename'] = $thumb->generateFilename();
        }

        if (class_exists('\Imagick', false)) {
            $image = new \Imagick($this->getPath());
            //$image->resizeImage($x, $y, \Imagick::FILTER_LANCZOS, 1, true);
            //$image->cropThumbnailImage($x, $y);
            $this->imagickCrop($image, $x, $y);
            $this->hook("beforeThumbSave", array($thumb));
            $image->writeImage($thumb->getPath());
            $thumb["filesize"] = filesize($thumb->getPath());
        } elseif (function_exists('imagecreatefromjpeg')) {
            list($width, $height, $type) = getimagesize($this->getPath());
            ini_set("memory_limit", "1000M");

            $a = array(null, 'gif', 'jpeg', 'png');
            $type = @$a[$type];
            if (!$type) {
                array_shift($a); // shift null
                throw $this->exception('This file type is not supported')
                        ->addMoreInfo('Supported file types', join(', ', array_values($a)));
            }

            // saving the image into memory (for manipulation with GD Library)
            $fx = "imagecreatefrom" . $type;
            $myImage = $fx($this->getPath());

            $geo = $this->getGeo($x, $y, $width, $height);

            $myThumb = imagecreatetruecolor($geo['width'], $geo['height']);
            imagecopyresampled($myThumb, $myImage, 0, 0, 0, 0, $geo['width'], $geo['height'], $width, $height);

            // final output
            imagejpeg($myThumb, $thumb->getPath());
            imageDestroy($myThumb);
            imageDestroy($myImage);
            $thumb["filesize"] = filesize($thumb->getPath());
        } else {
            // No Imagemagick support. Ignore resize.
            $thumb->import($this->getPath(), 'copy');
        }
        $thumb->save(); // update size and chmod
    }

    /**
     * Return new dimensions
     *
     * @param int $width
     * @param int $height
     * @param int $orig_width
     * @param int $orig_height
     *
     * @return array
     */
    function getGeo($width, $height, $orig_width, $orig_height)
    {
        $new_geo = array('width' => $width, 'height' => $height);

        $geo = array(
            'height' => $orig_height,
            'width'  => $orig_width,
        );

        if ($geo['width'] < $width && $geo['height'] < $height) {
            return $new_geo; // image is too small
        }

        $w = $geo['width']/$width;
        $h = $geo['height']/$height;
        if ($w > $h) {
            $new_geo = array(
                'width'  => $width,
                'height' => ceil($geo['height']/$w)
            );
        } else {
            $new_geo = array(
                'width'  => ceil($geo['width']/$h),
                'height' => $height
            );
        }
        
        return $new_geo;
    }
    
    /**
     * Called after original is imported. You can do your resizes here
     *
     * @return void
     */
    function afterImport()
    {
        $f = $this->getPath();
        $gd_info = getimagesize($f);
    }

    /**
     * Try delete related thumb file before deleting file entity itself
     *
     * @return void
     */
    function beforeDelete()
    {
        parent::beforeDelete();
        
        $this->ref('thumb_file_id')->tryDelete();
    }
}
