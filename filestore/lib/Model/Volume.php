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
                ;
		$this->addField('dirname')
                ->caption('Folder')
                ->mandatory(true)
                ;
		/*
		// @todo there is no implementation of total_space and used_space
		$this->addField('total_space')
                ->caption('Total space')
                ->type('int')
                ->mandatory(true)
                ->defaultValue('1000000000')
                ;
		$this->addField('used_space')
                ->caption('Used space')
                ->type('int')
                ->mandatory(true)
                ->defaultValue(0)
                ;
        */
		$this->addField('stored_files_cnt')
                ->caption('Files')
                ->type('int')
                ->mandatory(true)
                ->defaultValue(0)
                ;
		$this->addField('enabled')
                ->caption('Enabled')
                ->type('boolean')
                ->mandatory(true)
                ->defaultValue(false)
                ->caption('Writable')
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
