<?php
namespace translation;
class Model_Translation extends \SQL_Model {
    public $table="translation";
    function init(){
        parent::init();

        $this->addField('key')->system(true)->visible(true)->editable(true)->readonly(true)->sortable(true);
        $this->addField('tr_en')->sortable(true);
        $this->addField('tr_de')->sortable(true);
        $this->addField('tr_ru')->sortable(true);
        $this->addField('tr_lv')->sortable(true);	// extend and add your language
    }
}
