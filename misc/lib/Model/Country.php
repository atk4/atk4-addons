<?php
class Model_Country extends Model_Table {
	protected $entity_code = 'country';
	protected $table_alias='cn';

	protected function defineFields() {
		$this->newField('id')
				->datatype('int')
				->system(true);

		$this->newField('code')
				->datatype('string')
				->caption('Code')
				->length(8);

		$this->newField('name')
				->datatype('string')
				->caption('Name')
				->length(128);

		$this->newField('eu_member')
			->datatype('boolean')
			->caption('EU member')
		;
	}
	public function getIdByName($name){
		$q=$this->dsql()->where($this->fieldWithAlias('name'),$name)
			->field($this->fieldWithAlias('id'))
			->limit(1)
		;
		return $q->do_getOne();
	}
}
