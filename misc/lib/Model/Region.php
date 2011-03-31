<?php
class Model_Region extends Model_Table {
	protected $entity_code = 'region';
	protected $table_alias='reg';

	function init() {
        parent::init();
		$this->newField('id')
				->datatype('int')
				->system(true);

		$this->newField('country_code')
				->datatype('string')
				->caption('Country Code')
				->length(8);

		$this->newField('name')
				->datatype('string')
				->length(255);

		$this->newField('local_name');
		$this->newField('seat_city');
		$this->newField('province');
		$this->newField('juristidction');
	}
	public function getIdByName($name){
		$q=$this->dsql()->where($this->fieldWithAlias('name'),$name)
			->field($this->fieldWithAlias('id'))
			->limit(1)
		;
		return $q->do_getOne();
	}
}
