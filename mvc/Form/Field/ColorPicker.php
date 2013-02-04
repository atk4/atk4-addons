<?php
/*
 * Created on 14.12.2007 by *Camper* (camper@adevel.com)
 */
class Form_Field_ColorPicker extends Form_Field_Line{
	function init(){
		parent::init();
		// on entering this field we show the picker
		$this->onFocus()->ajaxFunc('showColorPicker(this,document.'.
			$this->owner->name.'.'.$this->name.')');
		// on changing a value we set the div color
		//$this->onChange()
		
		$this->setAttr('size',6);
	}
	function getInput(){
		return parent::getInput().$this->getButton();
	}
	/**
	 * Draws a button of fields's value color
	 */
	function getButton(){
		return '';
		return '<div style="width: 45px; height:18px; border:1px ridge #000000; margin:0; ' .
				'background-color:#' .$this->get(). '; '.
				'padding:0; float:left;" ' .
				'onClick="showColorPicker(this,document.forms[0].rgb2)"' .
				'>Change</div>';
	}
}
