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

class billing_Controller_Merchant_Logging extends AbstractController {
	function init(){
		parent::init();
		$this->owner->addHook('request-complete',array($this,'requestComplete'));
	}
	function requestComplete($transaction,$response){
		$this->api->logger->logLine('Transaction '.get_class($transaction).' completed'."\n");
		$this->api->logger->logLine('SENT: '."\n".$transaction->sentXML."\n");
		$this->api->logger->logLine('RECV: '."\n".$transaction->responseXML."\n");
	}
}
