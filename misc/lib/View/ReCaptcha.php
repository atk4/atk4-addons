<?php
require_once(dirname(__FILE__) . '/../recaptcha-php-1.11/recaptchalib.php');
class View_ReCaptcha extends View_HtmlElement{
	private $__publicKey;
	private $__privateKey;
	private $__fieldChallenge = 'recaptcha_challenge_field';
	private $__fieldResponse = 'recaptcha_response_field';
	private $__error = null;
	function init(){
		parent::init();
		$this->__publicKey  = $this->api->getConfig('recaptcha/publickey');
		$this->__privateKey = $this->api->getConfig('recaptcha/privatekey');
		$this->check();
		$this->set($this->getHtml());
	}
	function isValid(){
		return $this->__isValid;
	}
	function getError(){
		return $this->__error;
	}
	function check(){
		if ($_POST[$this->__fieldResponse]) {
			$resp = recaptcha_check_answer ($this->__privateKey,
					$_SERVER["REMOTE_ADDR"],
					$_POST[$this->__fieldChallenge],
					$_POST[$this->__fieldResponse]);

			if ($resp->is_valid) {
				$this->__isValid = true;
			} else {
				$this->__error = $resp->error;
				$this->__isValid = false;
			}
		}
		else {
			$this->__isValid = false;
			$this->__error = false;
		}
	}
	function getHtml(){
		return recaptcha_get_html($this->__publicKey, 'asdfsdfsdf');
		return recaptcha_get_html($this->__publicKey, $this->__error);
	}
	function display(){
		echo $this->getHtml();
	}
}
