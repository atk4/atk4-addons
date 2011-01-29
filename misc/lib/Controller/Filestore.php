<?php
class Controller_Filestore extends Controller {
	var $_id;
	var $_filenum;
	
	var $filename;
	var $filesize;
	
	var $mime_type;
	
	var $_filespace_id;

	var $_dirname;

	var $_backup_file_id;  // source file for converted audio	
		
	var $mError;

	var $error_message;

	/**
	 * for check prohibition action (try to get audio without advertise as picture)
	 */
	var $allowed_mime_type;

    function mFile() {
    	global $mError;
    	$this->mError = $mError;
    }
    
    function _error($mes) {
    	global $db;
    	
    	if (!($db->getOne("/* hack */select IS_FREE_LOCK('fs_fnum_lock')")))
			$db->getOne("/* hack */select release_lock('fs_fnum_lock')");

    	$this->error_message = $mes;
    	return false;
    }
    
    function getRec($id, $ignore_errors = false) {
    	global $db;
    	
    	if (($rec = $db->getRow('select f.id, f.filename, f.filesize, f.filenum, ft.mime_type, ' .
    			                '  f.filespace_id, fs.dirname, f.backup_file_id '.
                                '  from '.tbn('file').' f '.
    	                        ' inner join '.tbn('filetype').' ft on ft.id = f.filetype_id '.
    	                        ' inner join '.tbn('filespace').' fs on fs.id = f.filespace_id '.
    	                        ' where f.id = '.$id,
    	                        DBL_FETCHMODE_ASSOC
    	                        ))===false) return $this->_error($this->mError->db(__FILE__,__LINE__)); 
    	
    	if (empty($rec['id'])) {
    		$msg = 'Error get file properties for file '.$id;
    		if ($ignore_errors)
    			return $this->_error($msg);
    		else	
    			return $this->_error($this->mError->internal($msg,__FILE__,__LINE__));
    	}	
    	                        
    	$this->_id = $id;
    	$this->_filenum = $rec['filenum'];
    	$this->filename = $rec['filename'];
    	$this->filesize = $rec['filesize'];
    	$this->mime_type = $rec['mime_type'];
    	
    	$this->_filespace_id = $rec['filespace_id'];
    	$this->_dirname = $rec['dirname'];
    	
    	$this->_backup_file_id = $rec['backup_file_id'];
    	
    	if ((!empty($this->allowed_mime_type)) and (strpos($this->allowed_mime_type,$this->mime_type)===false)) {
    		$this->error_message = 'Unallowed file mime type!';
    		return false;	
    	}
    	else
    		return true;
    }
    
    function directory_exists() {
    	return file_exists($this->_dirname.'/000'); 
    }
    
    function _get_filespace($filesize) {
    	global $db;
    	
    	if (($res = $db->getRow('select id, dirname '.
    	                        '  from '.tbn('filespace').
                                ' where enabled = \'1\' and (used_space + :filesize) <= total_space ' .
                                '   and stored_files_cnt < 4096*256*256 '.
                                ' limit 1',
    	                        array('int filesize'=>$filesize),
    	                        DBL_FETCHMODE_ASSOC))===false) {
    		return $this->_error($this->mError->db(__FILE__,__LINE__));}
		
		if (empty($res) or (disk_free_space($res['dirname'])<$filesize)) {
			return $this->_error($this->mError->internal('Unavailable filespace for save file!'));
		}
		
		$this->_filespace_id = $res['id'];
		$this->_dirname = $res['dirname'];
		return true;
    }
    
    function _get_filetype_id($mime_type) {
    	global $db;
    	
    	if (($res = $db->getOne('select id '.
    	                        '  from '.tbn('filetype').
    	                        ' where mime_type = :mime_type ',
    	                        array('mime_type'=>$mime_type)
    	                        ))===false) return $this->_error($this->mError->db(__FILE__,__LINE__));
    	                        
		if (empty($res)) {
			if ($db->query('insert into '.tbn('filetype').' (mime_type) values (:mime_type)',
			               array('mime_type'=>$mime_type)
			               )===false) return $this->_error($this->mError->db(__FILE__,__LINE__)); 
			
			$res = $db->lastId();
		}
		
		return $res;    	                         
    }
    
    /**
     * Save filename in available filespace and return id from "file" table
     */ 
    function save($filename, $orig_filename, $mime_type, $adv_spots = null) {
    	global $db;
    	$this->error_message = '';
    	$this->filesize = filesize($filename);
    	
    	if ($this->_get_filespace($this->filesize)==false) return false;
/*    	
    	$fext  = array_pop(explode('.', $filename));
		$fname = basename($filename, '.'.$fext);
*/    	
		$this->mime_type = $mime_type;
		$this->filename = $orig_filename;

		// get lock for calc next filenum (timeout 5 seconds)
		if (($res_op = $db->getOne("/* hack */select get_lock('fs_fnum_lock',5)"
		                                      ))===false) 
		           return $this->_error($this->mError->db(__FILE__,__LINE__));

		if (empty($res_op)) return $this->_error('Error get lock for calculate next filenumber.');

		if (($filenum = $db->getOne(
								'select filenum from '.tbn('filedelnum').
				                ' where filespace_id = '.$this->_filespace_id.' limit 1'
		                                      ))===false) 
		           return $this->_error($this->mError->db(__FILE__,__LINE__));

		if (!empty($filenum)) {
			if ($db->query('delete from '.tbn('filedelnum').
                                      ' where filespace_id = '.$this->_filespace_id.
                                      ' and filenum = '.$filenum)===false) 
                              return $this->_error($this->mError->db(__FILE__,__LINE__));
		}
		else {
			if (($filenum = $db->getOne(
									'select max(filenum)+1 from '.tbn('file').
					                ' where filespace_id = '.$this->_filespace_id
			                                      ))===false) 
			           return $this->_error($this->mError->db(__FILE__,__LINE__));
	
			$filenum = (empty($filenum))?1:$filenum;
		}
			
    	if ($db->query('insert into '.tbn('file').' (filespace_id, filetype_id, filenum, filename, filesize, adv_spots, ins_dts) '.
    	               ' values (:filespace_id, :filetype_id, :filenum, :filename, :filesize, :adv_spots, :ins_dts)',
    	               array(
    	                       'int filespace_id'=>$this->_filespace_id,
    	                       'int filetype_id'=>$this->_get_filetype_id($mime_type),
    	                       'int filenum'=>$filenum,
    	                       'filename'=>$this->filename,
    	                       'int filesize'=>$this->filesize,
    	                       'adv_spots'=>$adv_spots,
    	                       'date ins_dts'=>time() 
    	                     )
    	               )===false) return $this->_error($this->mError->db(__FILE__,__LINE__)); 
    	               
    	$this->_filenum = $filenum;               
		$this->_id = $db->lastId();  

		// release lock
		if (($res_op = $db->getOne("/* hack */select release_lock('fs_fnum_lock')"
		                                      ))===false) 
		           return $this->_error($this->mError->db(__FILE__,__LINE__));
		
		// move file
		if (!@rename($filename,$this->_real_filename())) {
			$db->query('delete from '.tbn('file').' where id = '.$this->_id);
			$this->_id = null;
			return $this->_error($this->mError->internal('Error move file '.$filename.' into filespace ('.$this->_real_filename().')!',__FILE__,__LINE__)); 
		} 
		else {
			// increase used space
	    	if ($db->query('update '.tbn('filespace').
	    	               '   set used_space = used_space + :filesize, stored_files_cnt = stored_files_cnt + 1 '.
	                       ' where id = :id',
	                       array('int filesize'=>$this->filesize,
	                             'int id'=>$this->_filespace_id))===false) 
	                             return $this->_error($this->mError->db(__FILE__,__LINE__));
/*	
			// disabled filespace if used space more than total space
	    	if ($db->query('update '.tbn('filespace').
	    	               '   set enabled = \'0\' '.
	                       ' where enabled = \'1\' and used_space >= total_space and id = :id',
	                       array('int id'=>$this->_filespace_id))===false) db_error(__FILE__,__LINE__);
*/			
		}
		
		return $this->_id;  	               
    }
    
    // delete current file
    function delete() {
    	global $db;
    	
    	if (empty($this->_id)) return false;
    	
    	if ($res = @unlink($this->_real_filename())) {
	    	// update filespace record (decrease used space)
	    	if ($db->query('update '.tbn('filespace').
	    	               '   set used_space = used_space - :filesize, stored_files_cnt = stored_files_cnt - 1 '.
	                       ' where id = :id',
	                       array('int filesize'=>$this->filesize,
	                             'int id'=>$this->_filespace_id))===false) 
	             return $this->_error($this->mError->db(__FILE__,__LINE__));
/*	
			// enabled filespace if used space less than total space
	    	if ($db->query('update '.tbn('filespace').
	    	               '   set enabled = \'1\' '.
	                       ' where enabled = \'0\' and used_space < total_space and id = :id',
	                       array('int id'=>$this->_filespace_id))===false) db_error(__FILE__,__LINE__);
*/	
			// delete record desctibe currect file
	    	if ($db->query('delete from '.tbn('file').
	                       ' where id = :id',
	                       array('int id'=>$this->_id))===false) 
				return $this->_error($this->mError->db(__FILE__,__LINE__));

	    	if ($db->query('insert into '.tbn('filedelnum').' (filespace_id, filenum) '.
	                       ' values (:filespace_id, :filenum)',
	                       array('int filespace_id'=>$this->_filespace_id,
	                             'int filenum'=>$this->_filenum))===false) 
				return $this->_error($this->mError->db(__FILE__,__LINE__));

    		
    		if (!empty($this->_backup_file_id)) {
    			$backup_file_id = $this->_backup_file_id; $this->_backup_file_id = null;
    			if ($this->getRec($backup_file_id)) 
    				$res = $this->delete();
    		}
    	}
    	    	
    	return $res; 
    }
    
    
    // return filename in filespace, create directories if necessary
    function _real_filename() {
	$path = str_pad(strtoupper(dechex($this->_filenum)), 7, '0', STR_PAD_LEFT);
    	$res = $this->_dirname.DIRECTORY_SEPARATOR.substr($path,0,3);
    	if (!file_exists($res)) 
    		if (!@mkdir($res)) 
    			return $this->_error($this->mError->internal('Error create directory '.$res,__FILE__,__LINE__));
    		
		$res .= DIRECTORY_SEPARATOR.substr($path,3,2);
    	if (!file_exists($res)) 
    		if (!@mkdir($res)) 
    			return $this->_error($this->mError->internal('Error create directory '.$res,__FILE__,__LINE__));
		
		$res .= DIRECTORY_SEPARATOR.substr($path,5,2);
    	
    	return $res; 
    }
    
    function read($inline = true) {
    	$real_filename = $this->_real_filename();
/*    	
global $trace;
$trace->p('$real_filename: '.$real_filename);
$trace->p('this: '.print_r($this,true));
*/    	
	   	header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($real_filename)) . ' GMT');
	
	   	header('Content-Transfer-Encoding: none');
	   	header('Content-Type: '.$this->mime_type.'; name="' . $this->filename . '"');
	   	header('Content-Disposition: '.($inline?'inline':'attachment').'; filename="' . $this->filename . '"');
	   	header('Content-length: '. $this->filesize);

    	@readfile($real_filename);
    }

	function _sendHeaders($datetime, $mime_type, $name) {
	    header('Last-Modified: '.date( 'r', $datetime));
   		header('Content-Transfer-Encoding: none');
   		header('Content-Type: '.$mime_type.'; name="' . $name . '"');
   		header('Content-Disposition: inline; filename="' . $name . '"');
		header('Expires: '.date( 'r', time()+3600*24*30));
		header('Cache-control: Public');
		header('Pragma: cache');
	}

	function _cached_filename($dirname, $hash_filename) {
		if (!file_exists($dirname)) 
			if (!mkdir($dirname)) error_log('Can\'t make directory '.$dirname);
		$dirname .= '/'.substr($hash_filename,0,3);
		if (!file_exists($dirname)) 
			if (!mkdir($dirname)) error_log('Can\'t make directory '.$dirname);
		$dirname .= '/'.substr($hash_filename,3,3);
		if (!file_exists($dirname)) 
			if (!mkdir($dirname)) error_log('Can\'t make directory '.$dirname);
		$dirname .= '/'.substr($hash_filename,6,3);
		if (!file_exists($dirname)) 
			if (!mkdir($dirname)) error_log('Can\'t make directory '.$dirname);

		return $dirname.'/'.substr($hash_filename,9,23);
	}

    /**
     * read image from filespace and output to browser
     * size_limit - on pixels, image crop for square with parts equal size  
     */
    function readImagePreview($dirname, $name, $mime_type, $file_num, $size_limit, $cache_dir = null) {
    	
    	if (!is_null($cache_dir)) {
    		$cache_filename = $this->_cached_filename($cache_dir,md5($dirname. $name. $mime_type. $file_num. $size_limit));
    		
    		if (file_exists($cache_filename)) {
    			$this->_sendHeaders(filemtime($cache_filename), $mime_type, $name);
    			readfile($cache_filename);
    			exit;
    		}
    			
    	}
    	else
    		$cache_filename = null;
    	
    	$this->filename = $name;
    	$this->_filenum = $file_num;
    	$this->_dirname = $dirname;
    	$this->mime_type = $mime_type;
    	if (($real_filename = $this->_real_filename())===false) {
    		error_log('Error: '.$this->error_message.' at file '.__FILE__.' line '.__LINE__);
    		exit;
    	}

		$size = getimagesize($real_filename);
		list($orig_width,$orig_height,$type) = $size;

		$this->_sendHeaders(filemtime($real_filename), $mime_type, $name);
		
		if (
		      ((($orig_width>$size_limit))) // or ($orig_height>$size_limit)))
		        and 
		      (($type==1) or ($type==2) or ($type==3)) // supported image types 
		    )  {
			// need resize
			
			if ($orig_width >= $orig_height) {
				$ratio = $orig_height / $orig_width;
				
				$new_width = $size_limit;
				$new_height = (int) $size_limit*$ratio;
			}
			else {
				$ratio = $orig_width / $orig_height;
				
				if ($orig_width >= $size_limit) {
					$new_width = $size_limit;
					$new_height = (int) $size_limit/$ratio;
				}
				else {
					$new_height = $size_limit;
					$new_width = (int) $size_limit*$ratio;
				}
				
			}
/*
			if ($orig_width >= $orig_height) {
				$ratio = $orig_height / $orig_width;
				$new_width = $size_limit;
				$new_height = (int) $size_limit*$ratio;
			}
			else {
				$ratio = $orig_width / $orig_height;
				
				$new_height = $size_limit;
				$new_width = (int) $size_limit*$ratio;
			}
*/
            switch ((int) $type) {
                case 1:
                    $imagecreate_func = 'imagecreatefromgif';
                    $imagesave_func = 'imagegif';
                    break;
                case 2:
                    $imagecreate_func = 'imagecreatefromjpeg';
                    $imagesave_func = 'imagejpeg';
                    break;
                case 3:
                    $imagecreate_func = 'imagecreatefrompng';
                    $imagesave_func = 'imagepng';
                    break;
            }

            $orig_img = $imagecreate_func($real_filename);

            $new_img = ImageCreateTrueColor($new_width, $new_height);

            ImageCopyResampled($new_img, $orig_img, 0, 0, 0, 0, $new_width, $new_height,
                                                    $orig_width, $orig_height);

		    if ((!$imagesave_func($new_img,$cache_filename)) and (!is_null($cache_filename))) {
		    	$imagesave_func($new_img); 
		    }
		    elseif (!is_null($cache_filename))
		    	readfile($cache_filename);
		    	
		    ImageDestroy($orig_img);
		    ImageDestroy($new_img);
		}
		else
	    	readfile($real_filename);	
    }


}
