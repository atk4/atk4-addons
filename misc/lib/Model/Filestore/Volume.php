<?php
class Model_Filestore_Volume extends Model_Table {
	protected $entity_code='filestore_volume';
	function defineFields(){
		parent::defineFields();
		$this->newField('name')
			->caption('Volume Name')
			;
		$this->newField('dirname')
			;
		$this->newField('total_space')
			->datatype('int')
			->defaultValue('1000000000')
			;
		/*
		$this->newField('used_space')
			->datatype('int')

			;
			*/
		$this->newField('stored_files_cnt')
			->datatype('int')
			->defaultValue(0)
			->caption('Files')
			;
		$this->newField('enabled')
			->datatype('boolean')
			->caption('Writable')
			;
	}
	function getFileNumber(){
		/*
		   Returns sequnetal file number. Each time this is called - number is increased.

		   Note that this is only approximate number and will not be decreased upon file delete.
		   */
		//$this->api->db->query('lock tables '.$this->entity_code.' write');

		$this->setActualFields(array('stored_files_cnt'));
		$f=$this->get('stored_files_cnt');
		$this->set('stored_files_cnt',$f+1);
		$this->api->db->dsql()
			->table($this->entity_code)
			->set('stored_files_cnt',$f+1)
			->where('id',$this->get('id'))
			->do_update();

		//$this->api->db->query('unlock tables '.$this->entity_code.'');

		return $f;
	}
}
