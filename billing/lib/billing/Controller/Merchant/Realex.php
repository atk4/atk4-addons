<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing abstract payment merchant
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

class billing_Controller_Merchant_Realex
extends billing_Controller_Merchant_generic
implements billing_Controller_Merchant_Interface_CreditCard, 
           billing_Controller_Merchant_Interface_Basic, 
           billing_Controller_Merchant_Interface_Recurring 
{

    public $hash_func='sha1';        // which encryption method to use
    public $version='Agile Toolkit / Billing module 3.92';

    function init(){
        parent::init();
        $this->hash_func=$this->api->getConfig('billing/realex/hash_func',$this->hash_func);
        $this->add('billing_Controller_Merchant_Logging');
    }

    function getSupportedCardTypes(){
        return array(
                'VISA'=>'Visa',
                'LASER'=>'Laser',
                'MC'=>'Master Card',
                'AMEX'=>'Amex',
                'SWITCH'=>'Switch',
                'DINERS'=>'Diners',
                );
    }

    function addRequest($type){
        return $this
            ->add('billing_Model_Realex_Transaction_'.str_replace('-','',$type))
            ->set('type',$type)
            ->addHook('request-complete',array($this,'requestComplete'))
            ;
    }
    function requestComplete($transaction,$response){
        // This function is executed after successful request
        $this->hook('request-complete',array($transaction,$response));
    }


    // Interface Functions
    function charge($card, $amount, $currency=null, $description=null){
        // Perform charge of amount from $card

        if($card->isSaved()){
            $r=$this->addRequest('receipt-in')
                ->set('payer_ref',$card->get('payer_ref'))
                ->set('card_ref',$card->get('card_ref'))
                ->set('amount',round($amount*100))
                ;
        }else{
            $r=$this->addRequest('auth')
                ->set($card->data)
                ->set('cc_number',$card->cc_number)
                ->set('cc_cvn',$card->cc_cvn)
                ->set('amount',round($amount*100))
                ;
        }
        return $r->process();
    }

    function verify($card){
        $r=$this->charge($card,$amount);
        return $this->addRequest('void')
            ->set('pasref',$r->pasref)
            ->set('authcode',$r->authcode)
            ->set('orderid',$r->orderid)
            ->process();
    }

    function obfuscate($card){
		$card->cc_number=$card->get('cc_number'); //$data['cc_number'];
		$card->cc_cvn=$card->get('cc_cvn'); //$data['cc_cvn'];
		$card->set('cc_number','**** **** **** '.substr($card->get('cc_number'),-4));
		$card->set('cc_cvn','***');
    }


}
