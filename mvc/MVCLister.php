<?php
class MVCLister extends CompleteLister{
	function setController($name){
		parent::setController($name);
		$this->dq=$this->controller->getModel()->view_dsql($this->name);
		$this->api->addHook('pre-render',array($this->controller,'execQuery'));
		return $this;
	}
}
