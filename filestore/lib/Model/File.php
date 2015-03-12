<?php
namespace filestore;
class Model_File extends \SQL_Model
{
    public $table = 'filestore_file';
    public $title_field = 'original_filename';

    // used Model classes
    public $type_model_class = 'filestore/Model_Type';
    public $volume_model_class = 'filestore/Model_Volume';

    public $magic_file = null; // path to magic database file used in finfo-open(), null = default
    public $import_mode = null;
    public $import_source = null;

    // set this to true, will allow to upload all file types
    // and will automatically create the type record for it
    public $policy_add_new_type = false;
    
    // set this to true, if you want to enable "soft delete", then only field
    // filestore_file.deleted will be set to true and files will not be
    // physically deleted
    public $policy_soft_delete = false;
    
    // Initially we store 4000 files per node until we reach 256 nodes.
    // After that we will determine node to use by modding filecounter.
    // @see generateFilename()
    protected $min_files_per_node = 4000;

    function init()
    {
        parent::init();
        
        // add fields
        $this->hasOne($this->type_model_class, 'filestore_type_id')
                ->caption('File Type')
                ->mandatory(true)
                ->sortable(true)
                ;
        $this->hasOne($this->volume_model_class, 'filestore_volume_id', false)
                ->caption('Volume')
                ->mandatory(true)
                ->sortable(true)
                ;
        $this->addField('original_filename')
                ->caption('Original Name')
                ->type('text')
                ->mandatory(true)
                ->sortable(true)
                ;
        $this->addField('filename')
                ->caption('Internal Name')
                ->mandatory(true)
                ->system(true)
                ->sortable(true)
                ;
        $this->addField('filesize')
                ->caption('File size')
                ->type('int')
                ->mandatory(true)
                ->defaultValue(0)
                ->sortable(true)
                ;
        $this->addField('deleted')
                ->caption('Deleted')
                ->type('boolean')
                ->mandatory(true)
                ->defaultValue(false)
                ->sortable(true)
                ;

        // join volume table and add fields from it
        $this->vol = $this->leftJoin('filestore_volume');
        $this->vol->addField('dirname')
                ->caption('Folder')
                ->mandatory(true)
                ->sortable(true)
                ->editable(false)
                ->display(array('form'=>'Readonly'))
                ;

        // calculated fields
        $this->addExpression('url')
                ->set(array($this,'getURLExpr'))
                ->caption('URL')
                ->sortable(true)
                ->editable(false)
                ->display(array('form'=>'Readonly'))
                ;

        // soft delete
        if ($this->policy_soft_delete) {
            $this->addCondition('deleted', '<>', 1);
        }

        // hooks
        $this->addHook('beforeSave', $this);
        $this->addHook('beforeDelete', $this);
    }
    
    /**
     * Produces expression which calculates full URL of image
     * 
     * @param Model $m
     * @param DSQL $q
     *
     * @return DSQL
     */
    function getURLExpr($m,$q)
    {
        return $q->concat(
            @$m->api->pm->base_path,
            $m->getElement('dirname'),
            "/",
            $m->getElement('filename')
        );
    }
    
    /**
     * Before save hook
     *
     * @param Model $m
     * 
     * @return void
     */
    function beforeSave($m)
    {
        // if new record, then choose volume and generate name
        if (!$m->loaded()) {
            // volume
            $m->set('filestore_volume_id', $m->getAvailableVolumeID());

            // generate random original_filename in case you import file contents as string
            if (! $m['original_filename']) {
                $m->set('original_filename', mt_rand());
            }
            // generate filename (with relative path)
            $m->set('filename', $m->generateFilename());
        }

        // perform import itself
        if ($m->import_mode) {
            $m->performImport();
        }
    }
    
    /**
     * Return available volume ID
     *
     * @return int
     */
    function getAvailableVolumeID()
    {
        // Determine best suited volume and returns it's ID
        $c = $this->ref("filestore_volume_id")
            ->addCondition('enabled', true)
            ->addCondition('stored_files_cnt', '<', 4096*256*256)
            ;
        $id = $c->dsql('select')
            ->field($this->id_field)
            ->order($this->id_field, 'asc') // to properly fill volumes, if multiple
            ->limit(1)
            ->getOne();
        if ($id !== null) {
            $c->tryLoad($id);
        }
        
        if (!$c->loaded()) {
            throw $this->exception('No volumes available. All of them are full or not enabled.');
        }

        /*
        if(disk_free_space($c->get('dirname') < $filesize)){
            throw new Exception_ForUser('Out of disk space on volume '.$id);
        }
        */

        return $id;
    }
    
    /**
     * Return file type ID
     *
     * @param string $mime_type
     * @param bool $add
     *
     * @return int
     */
    function getFiletypeID($mime_type = null, $add = false)
    {
        if ($mime_type === null) {
            $path = $this->get('filename') ? $this->getPath() : $this->import_source;
            if (!$path) {
                throw $this->exception('Load file entry from filestore or import');
            }

            if (!function_exists('finfo_open')) {
                throw $this->exception('You have to enable php_fileinfo extension of PHP.');
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE, $this->magic_file);
            if ($finfo === false) {
                throw $this->exception("Can't find magic_file with finfo_open().")
                    ->addMoreInfo('Magic_file: ',isnull($this->magic_file) ? 'default' : $this->magic_file);
            }
            $mime_type = finfo_file($finfo, $path);
            finfo_close($finfo);
        }
        
        $c = $this->ref("filestore_type_id");
        $data = $c->getBy('mime_type', $mime_type);
        if (!$data['id'] && $add) {
            // automatically add new MIME type
            $c->set(array("mime_type" => $mime_type, "name" => $mime_type, "allow" => true));
            $c->save();
            $data = $c->get();
        } elseif (!$data['id'] || !$data['allow']) {
            // not allowed MIME type
            throw $this->exception(
                sprintf(
                    $this->api->_('This file type is not allowed for upload (%s) or you are exceeding maximum file size'),
                    $mime_type
                ), 'Exception_ForUser')
                ->addMoreInfo('type',$mime_type);
        }
        
        return $data['id'];
    }
    
    /**
     * Generate filename
     *
     * @return string
     */
    function generateFilename()
    {
        $this->hook("beforeGenerateFilename");
        
        if ($filename = $this->get("filename")) {
            return $filename;
        }
        
        $v = $this->ref('filestore_volume_id'); //won't work because of MVCFieldDefinition, line 304, loaded() check
        $dirname = $v->get('dirname');
        $seq = $v->getFileNumber();

        // Initially we store $min_files_per_node files per node until we reach 256 nodes.
        // After that we will determine node to use by modding filecounter.
        // This method ensures we don't create too many directories initially
        // and will grow files in directories indefinitely.
        $limit = $this->min_files_per_node * 256;
        if ($seq < $limit){
            $node = floor($seq / $this->min_files_per_node);
        } else {
            $node = $seq % 256;
        }
        $d = $dirname . '/' . dechex($node);
        if (!is_dir($d)) {
            mkdir($d);
            chmod($d, $this->api->getConfig('filestore/chmod', 0660));
        }

        // Generate temporary file
        // $file = basename(tempnam($d, 'fs'));

        // File name generation for store in file system, example: 20130201110338_5-myfile.jpg
        $cnt = (int) @$this->api->_filestore_unique_file++;
        $file = date("YmdHis") . '_' . $cnt . '_' . $this->convertName($this['original_filename']);
        $fp = @fopen($d . '/' . $file, "w");
        @fclose($fp);

        // Verify that file was created
        if (!file_exists($d . '/' . $file)) {
            throw $this->exception('Could not create file')
                ->addMoreInfo('path', $d)
                ->addMoreInfo('file', $file);
        }

        return dechex($node) . '/' . $file;
    }

    /**
     * Remove special characters in filename, replace spaces with -, trim and
     * set all characters to lowercase
     * 
     * @param string $str
     *
     * @return string
     */
    function convertName($str)
    {
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9.\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
        return $clean;
    }

    /**
     * Import file
     *
     * @param string $source
     * @param string $mode
     *
     * @return this
     */
    function import($source, $mode = 'upload')
    {
        /*
           Import file from different location. 

           $mode can be
            - upload - for moving uploaded files. (additional validations apply)
            - move - original file will be removed
            - copy - original file will be kept
            - string - data is passed inside $source and is not an existant file
         */
        $this->import_source = $source;
        $this->import_mode = $mode;

        if ($this->loaded() && $this->id) {// -- if we have this, then
            // we can import right now

            // If file is already in database - put it into store
            $this->performImport();
            $this->save();
        }
        
        return $this;
    }

    /**
     * Return path
     * 
     * @return string
     */
    function getPath()
    {
        $path = 
            $this->ref("filestore_volume_id")->get("dirname") .
            "/" .
            $this['filename'];
        
        return $path;
    }
    
    /**
     * Return MIME type
     *
     * @return string
     */
    function getMimeType()
    {
        return $this->ref('filestore_type_id')
            ->get('mime_type');
    }
    
    /**
     * Perform import
     *
     * @return this
     */
    function performImport()
    {
        // After our filename is determined - performs the operation
        $destination = $this->getPath();
        
        switch ($this->import_mode) {
            case 'upload':
                move_uploaded_file($this->import_source, $destination);
                break;
            case 'move':
                rename($this->import_source, $destination);
                break;
            case 'copy':
                copy($this->import_source, $destination);
                break;
            case 'string':
                $fd = fopen($destination, 'w');
                fwrite($fd, $this->import_source);
                fclose($fd);
                break;
            case 'none': // file is already in place
                break;
            default:
                throw $this->exception('Incorrect import mode specified.')
                        ->addMoreInfo('specified mode', $this->import_mode);
        }
        
        chmod($destination, $this->api->getConfig('filestore/chmod', 0660));
        clearstatcache();
        
        $this->set('filesize', filesize($destination));
        $this->set('deleted', false);
        $this->set('filestore_type_id', $this->getFiletypeID(null, $this->policy_add_new_type));
        $this->import_source = null;
        $this->import_mode = null;
        
        return $this;
    }
    
    /**
     * Delete file from file system before deleting it from DB
     *
     * @return void
     */
    function beforeDelete()
    {
        if (!$this->policy_soft_delete) {
            $file = $this->getPath();
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Deletes record matching the ID (implementation of soft delete)
     * 
     * @param int $id
     * 
     * @return this
     */
    function delete($id=null)
    {
        if ($this->policy_soft_delete) {
            if(!is_null($id))$this->load($id);
            if(!$this->loaded())throw $this->exception('Unable to determine which record to delete');

            $this->set('deleted', true)->saveAndUnload();
            
            return $this;
        } else {
            return parent::delete($id);
        }
    }
}
