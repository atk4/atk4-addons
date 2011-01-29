<?php
/**
 * $this->get() method returns field addslashes'ed value 
 * Created on 28.01.2008 by *Camper* (camper@adevel.com)
 */
class SafeFilter extends Filter{
	function get($field){
		return addslashes(parent::get($field));
	}
	function set($field_or_array,$value=undefined){
		// value should be cleared from added slashes
		if($value!==undefined)$value=(!is_numeric($value)?stripslashes($value):$value);
		return parent::set($field_or_array,$value);
	}
}
