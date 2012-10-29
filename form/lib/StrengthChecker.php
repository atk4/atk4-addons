<?php
// Add into a field to perform password strength checking
namespace form;
class StrengthChecker extends \HtmlElement {
    public $default_text='use secure password';
    function init(){
        parent::init();
        $this->set($this->default_text);
        $this->addClass('password-checker');

        $field=$this->owner;
        if(!($field instanceof \Form_Field)){
            throw $this->exception('You should use StrengthChecker by inserting it into password field');
        }


		$field->js('change')->univ()->ajaxec(
				array($this->api->url(),
					$this->name=>$field->js()->val()
					));
		$field->js(true)->univ()->autoChange(1000);

		if(isset($_GET[$this->name]) || isset($_POST[$this->name])){

            $p=$_POST[$this->name]?$_POST[$this->name]:$_GET[$this->name];

            // Check 
            if($p)$out = $this->checkByCrackLib($_GET[$this->name]);
            else $out='';

            $this->setResponse($p, $out);

		}
    }
    function setResponse($p, $out){
        // redefine this in your own checker
        // Set color code
        $j=$this->showStrengLevel($out);

        if(!$p){
            $this->js(null,$j)->text($this->default_text)->execute();
        }elseif($out != "OK"){
            $this->js(null,$j)->text($out)->execute();
        }else{
            $this->js(null,$j)->text('Password is OK')->execute();
        }
    }
    /* Set text which appears when password field is empty */
    function setDefaultText($t){
        return $this->set($this->default_text=$t);
    }
    function showStrengLevel($response){
        $j=$this->js()->removeClass('low')->removeClass('high')->removeClass('medium');
        if(!$response)return $j;
        if($response=='it is WAY too short')return $this->js(null,$j)->addClass('low');
        if($response=='OK')return $this->js(null,$j)->addClass('high');
        return $this->js(null,$j)->addClass('medium');
    }
	function checkByCrackLib($password){
		$cl=$this->api->getConfig('cracklib','/usr/sbin/cracklib-check');

		if(file_exists($cl) && is_executable($cl)){
			$cl=$this->add('System_ProcessIO')
				->exec('/usr/sbin/cracklib-check')
				->write_all($password)
				;

			$out=trim($cl->read_all());
			$out=str_replace($password,'',$out);
			$out=preg_replace('/^:\s*/','',$out);
			return $out;
		} else {
			if(strlen($password)<4)return "it is WAY too short";
			if(strlen($password)<6)return "it is too short";
			return "OK";
		}
	}
}
