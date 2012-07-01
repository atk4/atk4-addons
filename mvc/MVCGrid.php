<?php
/**
 *
 * @author Camper (camper@agiletech.ie) on 29.01.2010
 */
class MVCGrid extends Grid{

	// THIS IS CORRECT BEHAVOR. ESCAPE TEXT, BUT LEAVE HTML ALONE
	function format_text($field){
		$this->current_row[$field] = htmlentities_utf8($this->current_row[$field]);
	}
	function format_real($field){
		$this->current_row[$field]=(float)$this->current_row[$field];
		$this->setTDParam($field,'align','right');
	}
	function setController($name){
		parent::setController($name);
		$this->dq=$this->controller->view_dsql($this->name);
		$this->api->addHook('pre-render',array($this->controller,'execQuery'));
		$this->processSorting();
		$this->controller->initLister();
		//$this->dq->debug();
		return $this;
	}
	function processSorting(){
		if($this->sortby){
			$desc=false;
			$order=$this->sortby;
			if(substr($this->sortby,0,1)=='-'){
				$desc=true;
				$order=substr($order,1);
			}
            if(!$this->sortby_db){
                $this->getController()->setOrder($this->name,$order,$desc);
            }
		}
		//we always need to calc rows
		$this->dq->calc_found_rows();
		return $this;
	}
	function addColumnMVC($field_name,$type=null){
		$field=$this->getController()->getModel()->getField($field_name);
		if(is_null($field))throw new Exception_InitError("Field '$field_name' is not defined in the ".
			get_class($this->getController()->getModel())." model");
		if(is_null($type))$type=$this->getController()->formatType($field->datatype(),'grid',$field_name);
		if($field_name=='locked')return
			parent::addColumn('locked','locked','');
        if($field_name=='id')$type='text';
		$r=parent::addColumn($type,$field_name,$field->caption());
		//echo get_class($field) . '<br />';
		if($field->sortable())
			$r->makeSortable();
		return $r;
	}
	function addColumnPlain($type,$name=null,$descr=null,$color=null){
		return $this->addColumn($type,$name,$descr,$color);
	}
	function addSelectable($field){
		$this->js_widget=null;
		$this->js(true)
			->_load('ui.atk4_checkboxes')
			->atk4_checkboxes(array('dst_field'=>$field));
		$this->addColumnPlain('checkbox','selected');

		$this->addOrder()
			->useArray($this->columns)
			->move('selected','first')
			->now();
	}
	function hasColumn($name){
		return isset($this->columns[$name])?$this->columns[$name]:false;
	}
	function _performDelete($id){
		$this->getController()->loadData($id)->delete();
	}
}
