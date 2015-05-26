<?php
class modelbuilder_Page extends Page {
	function init(){
	}
	function initMainPage(){
		$c=$this->frame('Model Builder')->add('View_Columns');

		$f=$c->addColumn()->add('Form');
		//$f->js()->_load('ui.atk4_form')->atk4_form();
		$c2=$c->addColumn();


		$f->addComment('Choose database table below to generate basic model fields<br/><br/>');

		$dd=array();
		$data=$this->api->db->getAll("show tables");
		foreach($data as $row){
			$dd[$row[0]]=$row[0];
		}

		$f->addField('dropdown','table','Pick table')->setValueList($dd);
		$f->addSubmit();

		if($f->isSubmitted()){
			$f->api->redirect($this->api->url(null,$f->getAllData()));
		}




		if($f->get('table')){
			$c2->add('Text')->set($this->buildModel($f->get('table')));
		}
		//$c2->add('Text')->set('Table: '.$f->get('table'));
	}
	function buildModel($t){
		if(!ctype_alnum($t))throw BaseException('invalid table name');
		$o='';
		$data=$this->api->db->getAll("describe $t");
		var_dump($data);
		foreach($data as $row){
			$dd[$row[0]]=$row[0];
		}

	}
}
