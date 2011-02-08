<?php
class View_Filestore_ImageList extends MVCLister {
	function init(){
		parent::init();
		$this->setController('Controller_Filestore_Image');
	}
	function defaultTemplate(){
		return array('view/filestore/imagelist');
	}
}
