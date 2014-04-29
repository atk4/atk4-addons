<?php
namespace filestore;
class Model_Volume extends \SQL_Model
{
	public $table = 'filestore_volume';
	
	function init()
	{
		parent::init();
		
		$this->addField('name')
                ->caption('Volume Name')
                ->mandatory(true)
                ->sortable(true)
                ;
		$this->addField('dirname')
                ->caption('Folder')
                ->hint('You can use absolute path too')
                ->mandatory(true)
                ->sortable(true)
                ;
		/*
		// @todo there is no implementation of total_space and used_space
		$this->addField('total_space')
                ->caption('Total space')
                ->hint('Volume size limit (bytes)')
                ->type('int')
                ->mandatory(true)
                ->defaultValue('1000000000')
                ->sortable(true)
                ;
		$this->addField('used_space')
                ->caption('Used space')
                ->hint('Space used by files (bytes)')
                ->type('int')
                ->mandatory(true)
                ->defaultValue(0)
                ->display(array('form'=>'Readonly'))
                ->sortable(true)
                ;
        */
		$this->addField('stored_files_cnt')
                ->caption('Files')
                ->hint('Count of files in volume')
                ->type('int')
                ->mandatory(true)
                ->defaultValue(0)
                ->display(array('form'=>'Readonly'))
                ->sortable(true)
                ;
		$this->addField('enabled')
                ->caption('Writable')
                ->hint('Be sure to check this one!')
                ->type('boolean')
                ->mandatory(true)
                ->defaultValue(false)
                ->sortable(true)
                ;
	}
	
	/**
	 * Returns sequential file number. Each time this is called - number is increased.
	 * 
	 * Note that this is only approximate number and will not be decreased upon file delete.
	 *
	 * @return integer
	 */
	function getFileNumber()
	{
		//$this->api->db->query('lock tables '.$this->table.' write');

		$f = $this->get('stored_files_cnt');
		$this->set('stored_files_cnt', $f+1);
		$this->api->db->dsql()
			->table($this->table)
			->set('stored_files_cnt', $f+1)
			->where('id', $this->get('id'))
			->update();

		//$this->api->db->query('unlock tables '.$this->table);

		return $f;
	}
}
