<?php
namespace filestore;
class Page_FileAdmin extends \Page {
	function init(){
		parent::init();
		$this->api->stickyGET('tab');
		switch($_GET['tab']){
			case null:
			case 'file':
				$this->model='filestore/File';
				break;
			case 'volume':
				$this->model='filestore/Volume';
				break;
			case 'type':
				$this->model='filestore/Type';
				break;
		}
	}
	function initMainPage(){
		/*
		$g=$this->add('MVCGrid');
		$c=$g->add('Controller_filestore/Model_File');
		$g->setController($c);

		*/

		$f=$this->add('Form');
		$f->addField('upload','Upload_test','Upload Test')->setModel($this->model)->debug();

		$v=$this->add('View_Columns');
		$g=$v->addColumn(6);

		$g->add('H3')->set('Storage Location');
        $g->add('CRUD')->setModel('filestore/Volume',null,array('name','dirname','stored_files_cnt','enabled'));

		$g=$v->addColumn(6);

		$g->add('H3')->set('Allowed Filetypes');
        $g->add('CRUD')->setModel('filestore/Type',null,array('name','mime_type'));
        if(isset($g->grid))$g->grid->addPaginator(100);

		$g=$this->add('CRUD');$g->setModel('filestore/File')->setOrder('id',true);
        if($g->grid)$g->grid->addPaginator(50);
		//if($g->grid)$g->grid->dq->order('id desc');


	}
}
