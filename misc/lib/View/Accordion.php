<?php
/*
   max@agiletech.ie
  */
class View_Accordion extends View{
	private $options=NULL;

	function addSection($title){
		$t=$this->defaultTemplate();$t[1]='Sections';
		// FIXME: this does not work with custom templates

		$c=$this->add('View_HtmlElement',null, 'Sections', $t);
		$c->template->trySet('section_title', $title);
		return $c;
	}
	function setOptions(array $options){
		$this->options = $options;
	}
	function render(){
		if($this->options) {
			$this->js(true)->accordion($this->options);
		} else {
			$this->js(true)->accordion();
		}
		parent::render();
	}
	function defaultTemplate(){
		return array('view/accordion');
	}
}
