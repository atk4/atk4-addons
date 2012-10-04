<?php
namespace translation;
class Model_Translation extends \Model_Table {
	public $table="translation";
	function init(){
		parent::init();
		
		$this->addField('key')->system(true)->visible(true)->editable(true)->readonly(true);
		$this->addField('tr_en');
		$this->addField('tr_de');
		$this->addField('tr_ru');
		$this->addField('tr_lv');	// extend and add your language
	}
}