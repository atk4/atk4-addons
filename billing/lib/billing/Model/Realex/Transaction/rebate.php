<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing Realex "rebate" transaction
   Documentation: http://atk4.info/doc/

   ---------------------------------------------------------------------

   Agile Toolkit 4

   (c) 1999-2010 Agile Technologies Limited

   See COPYRIGHT for details

   ---------------------------------------------------------------------

   Authors:

    Romans

   ---------------------------------------------------------------------

	}}} */


class billing_Model_Realex_Transaction_rebate extends billing_Model_Realex_Transaction_generic {
	protected $url="https://epage.payandshop.com/epage-remote.cgi";
	protected $basicXML=<<<EOF
<pasref><?$pasref?></pasref>
<authcode><?$authcode?></authcode>
<amount currency="<?currency?>EUR<?/?>"><?amount?>123<?/?></amount>
<refundhash><?refundhash?>738e83....3434ddae662a<?/?></refundhash>
<autosettle flag="<?autosettle?>1<?/?>" />
EOF
;
	protected $mandatory=array(
				'pasref','authcode','amount','refundhash','autosettle'
					);

	protected $hash_order2=".amount.currency.payer_ref";
    // this hash order is weird but it works


    function init(){
        parent::init();
		$this->set('autosettle',1);
		$this->set('refundhash',sha1($this->api->getConfig('billing/realex/refund_password')));
        // TODO: is it always sha1 or can it be md5?
    }
}
