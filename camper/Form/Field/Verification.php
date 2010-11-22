<?php
/**
 * This field type is used for user verification.
 * It produces random image with the text user must enter
 * to submit form.
 *
 * To make it work:
 * - add this field to form
 * - specify the URL of the image to display (actually it will be the url of the image generator):
 *   you need to provide the URL which will return the correct image, URL contains string to encode
 *
 * Created on 15.10.2007 by *Camper* (camper@adevel.com)
 */
class Form_Field_Verification extends Form_Field_Line{
	protected $image_url=null;

	function init(){
		parent::init();
		$this->generateCode();
	}
	function generateCode(){
		if(!isset($_SESSION['S_IMAGE_DECODED']))
			$_SESSION['S_IMAGE_DECODED']=substr(sha1(time().$this->api->getConfig('login/secret_word','amodules3')),1,5);
	}
	function setImageURL($url){
		$this->image_url=$url;
		return $this;
	}
	function displayFieldError($msg=null){
		if(!isset($msg))$msg='Incorrect input for "'.$this->caption.'"';
		if($this->api->isAjaxOutput()){
			echo $this->ajax()->displayAlert($msg)->setInnerHTML($this->name.'_img',
				'<img src="'.$this->image_url.'" border="0">')->execute();
		}
		$this->owner->errors[$this->short_name]=$msg;
	}
	function get(){
		return strtoupper($_SESSION['S_IMAGE_DECODED'])==strtoupper($this->value);
	}
	function getInput($attr=array()){
		return parent::getInput($attr).'<div id="'.$this->name.'_img">' .
				'<img src="'.$this->image_url.'" border="0"></div>';
	}
	function validate(){
		// we must regenerate code if validation fails
		$result=parent::validate();
		if(!$result){
			unset($_SESSION['S_IMAGE_DECODED']);
			$this->generateCode();
		}
		return $result;
	}
}
