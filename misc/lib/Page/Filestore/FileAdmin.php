<?php
class Page_Filestore_FileAdmin extends Page {
	public $controller='Controller_Filestore_File';
	function init(){
		parent::init();
		$this->api->stickyGET('tab');
		switch($_GET['tab']){
			case null:
			case 'file':
				$this->controller='Controller_Filestore_File';
				break;
			case 'volume':
				$this->controller='Controller_Filestore_Volume';
				break;
			case 'type':
				$this->controller='Controller_Filestore_Type';
				break;
		}
	}
	function initMainPage(){
		/*
		$g=$this->add('MVCGrid');
		$c=$g->add('Controller_Filestore_File');
		$g->setController($c);

		*/

		$f=$this->add('Form');
		$f->addField('upload','Upload_test','Upload new file')->setController($this->controller)->debug();

		$v=$this->add('View_Columns');
		$g=$v->addColumn(5);

		$g->add('H3')->set('Storage Location');
        $g->add('CRUD')->setModel('Filestore_Volume',null,array('name','dirname','stored_files_cnt','enabled'));

		$g=$v->addColumn(5);

		$g->add('H3')->set('Allowed Filetypes');
        $g->add('CRUD')->setModel('Filestore_Type',null,array('name','mime_type'));
        if($g->grid)$g->grid->addPaginator(100);

		$g=$this->add('CRUD');$g->setModel('Filestore_File');
        if($g->grid)$g->grid->addPaginator(50);
		if($g->grid)$g->grid->dq->order('id desc');


	}
}
