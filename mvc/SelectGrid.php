<?php
/**
 * SelectGrid
 * AJAX based class
 * Created on 09.10.2006 by *Camper* (camper@adevel.com)
 */
class SelectGrid extends Grid{
	protected $selected=array();

	function init(){
		parent::init();
		//restoring selected
		//$this->addHook('post-submit', array($this, 'getSelected'), 1);
		$this->getSelected();
		if($_GET['save_selected']){
			$r=explode(',',$_GET['selected']);
			$sel=array();
			foreach($r as $i=>$v){
				list($id,$selected)=explode(':',$v);
				$sel[$id]=$selected;
			}
			$this->processSelection($sel);
		}
	}
	function getSelected($id=null){
		if(!$this->selected)$this->selected = $this->recall('selected', array());
		return (is_null($id)?$this->selected:$this->selected[$id]);
	}
	/**
	 * changes selected status of the row
	 */
	function select($id){
		$this->selected[$id]=($this->selected[$id]=='Y'?'N':'Y');
		$this->memorize('selected', $this->selected);
	}
	function setSelected($selected){
		$this->selected=array_merge($this->selected,$selected);
		$this->memorize('selected', $this->selected);
	}
	function format_checkbox($field){
		$this->current_row[$field] = '<input type="checkbox" id="cb_'.
			$this->current_row['id'].'" name="cb_'.$this->current_row['id'].
			'" value="'.$this->current_row['id'].'"'.
			($this->selected[$this->current_row['id']]=='Y'?" checked ":" ").'" onclick="'.
			$this->onClick($field).'" />';
		$this->setTDParam($field,'width','10');
		$this->setTDParam($field,'align','center');
	}
	function onClick($field){
//    	return $this->add('Ajax')->loadRegionURL('cb_'.$this->current_row['id'],
		
		//return "alert('".$this->api->url(null,array('cb'=>$this->current_row['id']))."')";
		return str_replace('"',"'",$this->ajax()->executeUrl(
				$this->api->url(null,array('cb'=>$this->current_row['id']))
			)->getString());
	}
	function format_assigned($field){
		$this->current_row[$field] = ($this->assignmentExists($this->current_row['id']))?"Yes":"No";
		if($this->current_row[$field] == 'Yes')$this->current_row[$field] = "<b>".$this->current_row[$field]."</b>";
	}
	function assignmentExists($id){
		return false;
	}
	/**
	 * Override this method to perform any updates to DB, etc
	 *
	 * @param $selected array of IDs selected
	 */
	function processSelection($selected){
		$this->memorize('selected',$selected);
	}
}
