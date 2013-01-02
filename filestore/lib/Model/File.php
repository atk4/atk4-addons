<?php
namespace filestore;
class Model_File extends \Model_Table {
    public $table='filestore_file';

	public $entity_filestore_type='filestore/Type';
	public $entity_filestore_volume='filestore/Volume';

    public $magic_file=null; // path to magic database file used in finfo-open(), null = default
    public $import_mode=null;
    public $import_source=null;

    public $policy_add_new_type=false; // set this to true, will allow to upload all file types
        // and will automatically create the type record for it

	function init(){
		parent::init();
		$this->hasOne($this->entity_filestore_type,'filestore_type_id',false)
			->caption('File Type')
			->mandatory(true)
			;
		$this->hasOne($this->entity_filestore_volume,'filestore_volume_id',false)
			->caption('Volume')
			->mandatory(true)
			;
		$this->addField('original_filename')
			->datatype('text')
			->caption('Original Name')
			;
		$this->addField('filename')
			->datatype('string')
			->caption('Internal Name')
			;
		$this->addField('filesize')
			->datatype('int')
            ->defaultValue(0)
			;
		$this->addField('deleted')
			->datatype('boolean')
            ->defaultValue(false)
			;

		$this->vol=$this->leftJoin('filestore_volume');
		$this->vol->addField('dirname');

		$this->addExpression('url')->set(array($this,'getURLExpr'));

        $this->addHook('beforeSave',$this);
	}
	/* Produces expression which calculates full URL of image */
	function getURLExpr($m,$q){
		return $q->concat(
			$m->api->pm->base_path,
			$m->getElement('dirname'),
			"/",
			$m->getElement('filename')
			);
	}
	function beforeSave($m){
        if(!$this->loaded()){
            // New record, generate the name
            $this->set('filestore_volume_id',$x=$this->getAvailableVolumeID());
            $this->set('filename',$this->generateFilename());
        }
		if($this->import_mode){
			$this->performImport();
		}
	}
	function getAvailableVolumeID(){
		// Determine best suited volume and returns it's ID
        $c=$this->ref("filestore_volume_id")
			->addCondition('enabled',true)
			->addCondition('stored_files_cnt','<',4096*256*256)
			;
		$id=$c->dsql('select')
            ->order('id', 'asc') // to properly fill volumes, if multiple
			->limit(1)
			->field('id')
			->do_getOne();
		$c->tryLoad($id);

        /*
		if(disk_free_space($c->get('dirname')<$filesize)){
			throw new Exception_Filestore_Physical('Out of disks space on volume '.$c);
		}
         */

		return $id;
	}
    function getFiletypeID($mime_type = null, $add = false){
        if($mime_type == null){
            $path = $this->get('filename')?$this->getPath():$this->import_source;
            if(!$path)throw $this->exception('Load file entry from filestore or import');

            if(!function_exists('finfo_open'))throw $this->exception('You have to enable php_fileinfo extension of PHP.');
            $finfo = finfo_open(FILEINFO_MIME_TYPE, $this->magic_file);	
            if($finfo===false)throw $this->exception("Can't find magic_file in finfo_open().")
                ->addMoreInfo('Magic_file: ',isnull($this->magic_file)?'default':$this->magic_file);
            $mime_type = finfo_file($finfo, $path);
            finfo_close($finfo);
        }
        $c=$this->ref("filestore_type_id");
        $data = $c->getBy('mime_type',$mime_type);
        if(!$data['id']){
            if ($add){
                $c->update(array("mime_type" => $mime_type, "name" => $mime_type));
                $data = $c->get();
            } else { 
                throw $this->exception('This file type is not allowed for upload')
                    ->addMoreInfo('type',$mime_type);
            }
        }
        return $data['id'];
    }
	function generateFilename(){
        $this->hook("beforeGenerateFilename");
        if ($filename = $this->get("filename")){
            return $filename;
        }
		$v=$this->ref('filestore_volume_id'); //won't work because of MVCFieldDefinition, line 304, loaded() check
		$dirname=$v->get('dirname');
		$seq=$v->getFileNumber();

		// Initially we store 4000 files per node until we reach 256 nodes. After that we will
		// determine node to use by modding filecounter. This method ensures we don't create too
		// many directories initially and will grow files in directories indefenetely

		$limit=4000*256;

		if($seq<$limit){
			$node=floor($seq / 4000);
		}else{
			$node=$seq % 256;
		}

        $d=$dirname.'/'.dechex($node);
		if(!is_dir($d))mkdir($d);

		// Generate temporary file
		$file=basename(tempnam($d,'fs'));

		// Verify that file was created
		if(!file_exists($d.'/'.$file)){
			throw $this->exception('Could not create file')->addMoreInfo('inside',$d)->addMoreInfo('file',$file);
		}

		return dechex($node).'/'.$file;
	}

	function import($source,$mode='upload'){
		/*
		   Import file from different location. 

		   $mode can be
		    - upload - for moving uploaded files. (additional validations apply)
			- move - original file will be removed
			- copy - original file will be kept
			- string - data is passed inside $source and is not an existant file
		   */
		$this->import_source=$source;
		$this->import_mode=$mode;

        if($this->loaded() && $this->id){// -- if we have this, then we 
            // can import right now

			// If file is already in database - put it into store
			$this->performImport();
			$this->save();
        }
        return $this;
	}

	function getPath(){
        $path = 
            $this->ref("filestore_volume_id")->get("dirname") . "/" .
            $this['filename'];
        return $path;
	}
    function getMimeType(){
        return $this->ref('filestore_type_id')
            ->get('mime_type');
    }
	function performImport(){
		/*
		   After our filename is determined - performs the operation
		   */
		$destination=$this->getPath();
		switch($this->import_mode){
        case'upload':
				move_uploaded_file($this->import_source,$destination);
				break;
			case'move':
				rename($this->import_source,$destination);
				break;
			case'copy':
				copy($this->import_source,$destination);
				break;
			case'string':
				$fd=fopen($destination,'w');
				fwrite($fd,$this->import_source);
				fclose($fd);
				break;
            case'none': // file is already in place
                break;
			default:
				throw new Exception_Filestore('Incorrect import mode specified: '.$this->import_mode);
		}
        chmod($destination, $this->api->getConfig('filestore/chmod',0660));
		clearstatcache();
		$this->set('filesize',$f=filesize($destination));
		$this->set('deleted',false);
        $this->set('filestore_type_id',$this->getFiletypeID(null,$this->policy_add_new_type));
		$this->import_source=null;
		$this->import_mode=null;
        return $this;
	}
	/*
	function beforeDelete(&$data){
		// Truncate but do not delete file completely
		parent::beforeDelete($data);
		$fd=fopen($this->getPath(),'w');
		fclose($fd);
	}
	*/
}
