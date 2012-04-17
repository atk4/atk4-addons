<?php
/**
 * Validates the stuff
 *
 * Created on 16.11.2007 by *Camper* (camper@adevel.com)
 */ 
class Validator extends AbstractController{
	static function validateEMail($email){
		return preg_match('/^[0-9a-z]+[_\.\'0-9a-z-]*[0-9a-z]*[\@]{1}[0-9a-z]*[\.0-9a-z-]*[0-9a-z]+[\.]{1}[a-z]{2,4}$/i',
			$email);
	}

	static function validateURL($url){
		if (preg_match("/^http:\/\/+[\.0-9a-zA-Z\/-]+[\.]{1}[a-z]{2,4}$/i", $url)) {
			return true;
		}
		return false;
	}

	static function validateHttp($url){
		return preg_match('^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|localhost|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$^',
			$url);
	}

	static function validatePhoneUK($value){
		return preg_match('/^0[1-9]{2,3}[\s]{0,1}[\-]{0,1}[\s]{0,1}[0-9]{7}$/i',$value);
	}
	static function validatePhoneInt($value){
		return preg_match('/^\+[1-9]{1,2}[\.]{1}[0-9]{9,10}$/i',$value);
	}
	static function validatePhone($value){
		return
			Validator::validatePhoneInt($value)||
			Validator::validatePhoneUK($value)||
			Validator::validatePhonePlain($value)
		;
	}
	static function validatePhonePlain($value){
		return preg_match('/^[\d]{11,12}$/i',$value);
	}
	/**
	 * Checks if there are only numbers, spaces and + in value
	 */
	static function validatePhoneA($value){
		return preg_match('/^\+[\d]{11,12}$/i',str_ireplace(' ','',$value));
	}
	static function validateName($value){
		return preg_match('/^[a-zA-Z\'\(\)\s-]+$/',$value);
		//return preg_match('/^[a-zA-Z\u0410-\u042F\u0430-\u044F\u0401\u0451\u0101\u0100\u010c\u010d\u0112\u0113\u011E\u011F\u012A\u012B\u0136\u0137\u013b\u013C\u0145\u0146\u0160\u0161\u016A\u016B\u017D\u017E\'\(\)\s-]+$/',$value);
	}
	static function validateLogin($value){
		return preg_match('/^[\w]+$/',$value);
	}
	static function validateTime($value){
		return strtotime($value)!==false;
	}
}
