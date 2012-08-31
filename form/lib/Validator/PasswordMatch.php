<?php
/**
 * HOW TO:
 *
 *  1. add to form new pass field                   $f->addField('password','new','New Password');
 *  2. add to form new pass repeate field           $f->addField('password','rep_new','Repeat New Password');
 *  3. Add this checker                             $rpc = $f->getElement('rep_new')->add('RepeatPassChecker');
 *  4. Pass to checker new pass field               $rpc->setNewPassFieldName($f->getElement('new'));
 *
 */

namespace form;

class Validator_PasswordMatch extends \AbstractController {
    public $default_text='Your passwords do not match';
    public $new_pass_rep_field = false;
    public $new_pass_field = false;
    public $form = false;
    function init(){
        parent::init();
        if(!($this->owner instanceof \Form_Field))
            throw $this->exception('You should use RepeatPassChecker by inserting it into password field');
        $this->new_pass_rep_field = $this->owner;
        if(!($this->owner->owner instanceof \Form))
            throw $this->exception('Field must be part of Form');
        $this->form = $this->owner->owner;
        $this->form->addHook('validate',array($this,'checkOnSubmit'));
    }
    function setNewPassField(\Form_Field $pass_field) {
        $this->new_pass_field = $pass_field;//->name; //var_dump($this->new_pass_field_name);
        return $this;
    }
    /* Set text which appears when password field is empty */
    function setDefaultText($t){
        return $this->set($this->default_text=$t);
    }
    function checkOnSubmit() {
        $np = $this->form->get(
            $this->new_pass_field->short_name)? $this->form->get($this->new_pass_field->short_name): false;
        $npr = $this->form->get($this->new_pass_rep_field->short_name)? $this->form->get($this->new_pass_rep_field->short_name): false;
        if( $np || $npr ){
            // Check
            $out = $this->checkIfMatch($np, $npr);
            if ($out === false) {
                $this->has_error = true;
                $this->form->displayError($this->new_pass_rep_field->short_name,$this->default_text);
            }
      	}
    }
	function checkIfMatch($np, $npr){
        return ($np==$npr)? true : false;
	}
}
