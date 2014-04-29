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
                ->sortable(true)
                ;
		$this->addField('mime_type')
                ->caption('MIME type')
                ->mandatory(true)
                ->sortable(true)
                ;
		$this->addField('extension')
                ->caption('Filename extension')
                ->mandatory(true)
                ->sortable(true)
                ;
        $this->addField('allow')
                ->caption('Allow')
                ->hint('Be sure to check this one!')
                ->type('boolean')
                ->defaultValue(true)
                ->mandatory(true)
                ->sortable(true)
                ;
	}
}
