<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of Billing framework implementing XXX
   Documentation: http://atk4.info/doc/billing/

   ---------------------------------------------------------------------

   Agile Toolkit 4

   (c) 1999-2010 Agile Technologies Limited

   See COPYRIGHT for details

   ---------------------------------------------------------------------

   Authors:

    Romans

   ---------------------------------------------------------------------

	}}} */


class billing_page_UserPayments extends ATK3_Page {
	function init(){
		parent::init();
        if($_GET['id'])$_GET['user_id']=$_GET['id'];
        $this->api->stickyGET('user_id');

        // ID = user_id
	}
	function initMainPage(){
		// List cards user have

		$g=$this->add('ATK3_Grid');
		$g->setController('billing_Controller_PaymentMethod_CreditCard');

		$g->addButton('Add Credit Card')
			->js('click')->univ()->dialogURL('Validate and add new credit card',
					$this->api->url('./addcreditcard'));

		$g->addButton('Add PayPal')
			->js('click')->univ()->dialogURL('Charge PayPal account',
					$this->api->url('./addpaypal'));

		$g->addColumnPlain('button','validate','Validate');
		$g->addColumnPlain('prompt','charge','Charge');
		$g->addColumnPlain('confirm','delete','Delete');

		$c=$g->getController();
		if($id=$_GET['validate']){
			$c->loadData($id);
			$c->verify();
			$g->js()->univ()->alert('validated')->execute();
		}
		if($id=$_GET['charge']){
			$c->loadData($id);
			$c->charge($_GET['value']);
			$g->js()->univ()->alert('amount deducted')->execute();
		}
		if($id=$_GET['delete']){
			$c->loadData($id);
			$c->delete();
			$g->js()->reload()->execute();
		}

		// Now show previous transactions
		$g=$this->add('ATK3_Grid','t');
		$g->setSource('billing_transaction');
		$g->addColumnPlain('text','ts');
		$g->addColumnPlain('text','op');
		$g->addColumnPlain('text','result');
		$g->addColumnPlain('text','amount');
		$g->addColumnPlain('text','orderid');
		$g->addColumnPlain('text','card_ref');
		$g->addColumnPlain('text','payer_ref');
		$g->dq->order('id desc');
		

		$g->addColumnPlain('button','void','Void');
		//$g->addColumnPlain('button','rebate','Charge');
		//$g->addColumnPlain('confirm','delete','Delete');

		$c=$g->getController();
		if($id=$_GET['void']){
			$c->loadData($id);
			$c->void();
			$g->js()->refresh()->execute();
		}
	}
	function page_addcreditcard(){
        //throw new BaseException('oau');
		$c=$this->add('billing_Controller_PaymentMethod_CreditCard');
		$f=$this->add('ATK3_Form')->setController($c);
		if($f->isSubmitted()){
			$c->set($f->getAllData());
            $c->set('user_id',$_GET['user_id']);
			$c->verify();// charge(49);
			$c->update();
            $f->js()->univ()->closeFrame()->execute();
		}
	}
	function page_addpaypal(){
        //throw new BaseException('oau');
		$c=$this->add('billing_Controller_PaymentMethod_PayPal');
		$f=$this->add('ATK3_Form')->setController($c);
        $f->addComment('');
        $f->addFieldPlain('line','amount');
		if($f->isSubmitted()){
            $r=$c->charge($f->get('amount'));
            if(is_object($r)){
                // URL where we should redirect user
                // first - save purchase as half-paid
                $this->js()->univ()->location($r)->execute();
            }
        }
    }
}
