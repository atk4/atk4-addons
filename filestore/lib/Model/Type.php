<?php
namespace filestore;
class Model_Type extends \SQL_Model
{
	public $table = 'filestore_type';
	
	function init()
	{
		parent::init();
		
		$this->addField('name')
                ->caption('Name')
                ->mandatory(true)
                ;
		$this->addField('mime_type')
                ->caption('MIME type')
                ->mandatory(true)
                ;
		$this->addField('extension')
                ->caption('Filename extension')
                ->mandatory(true)
                ;
        $this->addField('allow')
                ->caption('Allow')
                ->type('boolean')
                ->defaultValue(true)
                ->mandatory(true)
                ;
	}
}
