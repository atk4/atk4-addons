<?php
/**
 * HOW TO:
 *
 *  1. add to form new pass field                   $f->addField('password','new','New Password');
 *  2. add to form new pass repeate field           $f->addField('password','rep_new','Repeat New Password');
 *  3. Add this checker                             $rpc = $f->getElement('rep_new')->add('RepeatPassChecker', "rpc", "after_field");
 *  4. Pass to checker new pass field               $rpc->setNewPassFieldName($f->getElement('new'));
 *  5. Check after submit                           if($f->isSubmitted()){
 *                                                      $rep_pass_checker->checkOnSubmit();
 *                                                  }
 *  6. Done!
 */

class RepeatPassChecker extends HtmlElement {
    public $default_text='new pass and new pass repeat doesn\'t match';
    public $new_pass_field_name = false;
    function init(){
        parent::init();
        $this->addClass('password-repeat');
        if(!($this->owner instanceof Form_Field)){
            throw $this->exception('You should use RepeatPassChecker by inserting it into password field');
        }
    }
    function setNewPassFieldName(Form_Field $pass_field) {
        $this->new_pass_field_name = $pass_field->name; //var_dump($this->new_pass_field_name);
        return $this;
    }
    /* Set text which appears when password field is empty */
    function setDefaultText($t){
        return $this->set($this->default_text=$t);
    }
    function checkOnSubmit() {
        $np = isset($_POST[$this->new_pass_field_name])? $_POST[$this->new_pass_field_name]: false;
        $npr = isset($_POST[$this->owner->name])? $_POST[$this->owner->name]: false;
        if( $np || $npr ){
            // Check
            $out = $this->checkIfMatch($np, $npr);
            if ($out === false) {
                $this->has_error = true;
                $this->owner->owner->displayError($this->owner->short_name,$this->default_text);
            }
      	}
    }
	function checkIfMatch($np, $npr){
        return ($np==$npr)? true : false;
	}
}
