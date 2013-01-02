<?php
class Page_Doc_Howto extends Page {
	public $c;
	public $src;
	function initMainPage(){
		$c=$this->add('Controller_Doc_Howto');
		$c->addCondition('approved',true);
		$s=$this->add('View_Doc_Sidebar',null,'Sidebar',array('view/sidebar','_top'));
		$s->setController($c->setActualFields(array('title')));

		if(!$_GET['t']){
			$f=$this->frame('Welcome');
			$f->add('Text')
				->set('Welcome to How-to');
			return;
		}
		$this->api->stickyGET('t');

		$this->c=$c2=$this->add('Controller_Doc_Howto','cc');
		$c2->tryLoad($_GET['t']);

		$t=$this->add('Tabs');
		$dem=$t->addTab('Demo');
		$src=$t->addTab('Source');

		$dem->add('Text','sometext')->set(nl2br($this->c->get('descr')).'<hr/>');
		$this->executeDemo($dem);

		$src->add('Text')
			->set(highlight_string("<?php\n // You might need to use \$p=\$this; if you are inserting this code into page/*.php\n\n".$this->src,true))
			;
	}
	function executeDemo($p){
		$_inherit=$this->c->get('inherit');
		if($_inherit)foreach(explode(',',$_inherit) as $_i){
			$_c=$this->add('Controller_Doc_Howto','c'.$_i)->tryLoad($_i);
			eval($_e=$_c->get('example'));
			$this->src.=" // From example ".$_c->get('title')." (#".$_c->get('id').")\n";
			$this->src.=$_e."\n\n";
		}


		eval($_e=$this->c->get('example'));
		$this->src.=$_e."\n\n";
	}
	function defaultTemplate(){
		return array('page/doc/howto','_top');
	}
	function page_surname(){
		$this->add('Text')->set('Sub-page "surname" is not defined in this demonstration');
	}
}
class View_Doc_Sidebar extends MVCLister {
	public $current_class=null;
	function init(){
		$this->current_class=$this->template->get('current');
		parent::init();
	}
	function formatRow(){
		parent::formatRow();
		$this->current_row['href']=$this->api->url(null,array('t'=>$this->current_row['id']));
		$this->current_row['current']=$this->current_row['id']==$_GET['t']?$this->current_class:'';
	}
}
