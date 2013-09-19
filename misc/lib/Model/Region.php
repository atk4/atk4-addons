<?php
class Model_Region extends SQL_Model {
	protected $entity_code = 'region';
	protected $table_alias='reg';

	function init() {
        parent::init();
		$this->addField('id')
				->type('int')
				->system(true);

		$this->addField('country_code')
				->type('string')
				->caption('Country Code')
				->length(8);

		$this->addField('name')
				->type('string')
				->length(255);

		$this->addField('local_name');
		$this->addField('seat_city');
		$this->addField('province');
		$this->addField('juristidction');
	}
	public function getIdByName($name){
		$q=$this->dsql()->where($this->fieldWithAlias('name'),$name)
			->field($this->fieldWithAlias('id'))
			->limit(1)
		;
		return $q->do_getOne();
	}
}
