<?php
class Model_Country extends SQL_Model {
	protected $entity_code = 'country';
	protected $table_alias='cn';

	protected function defineFields() {
		$this->addField('id')
				->type('int')
				->system(true);

		$this->addField('code')
				->type('string')
				->caption('Code')
				->length(8);

		$this->addField('name')
				->type('string')
				->caption('Name')
				->length(128);

		$this->addField('eu_member')
			->type('boolean')
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
