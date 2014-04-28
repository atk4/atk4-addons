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
			;
		$this->addField('dirname')
			;
		/*
		// @todo there is no implementation of used_space or total_space
		$this->addField('total_space')
			->type('int')
			->defaultValue('1000000000')
			;
		$this->addField('used_space')
			->type('int')
			;
        */
		$this->addField('stored_files_cnt')
			->type('int')
			->defaultValue(0)
			->caption('Files')
			;
		$this->addField('enabled')
			->type('boolean')
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
		$this->set('stored_files_cnt',$f+1);
		$this->api->db->dsql()
			->table($this->table)
			->set('stored_files_cnt',$f+1)
			->where('id',$this->get('id'))
			->update();

		//$this->api->db->query('unlock tables '.$this->table);

		return $f;
	}
}
