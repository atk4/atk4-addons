<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing Realex "receipt-in" transaction
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


class billing_Model_Realex_Transaction_receiptin extends billing_Model_Realex_Transaction_generic {
	protected $basicXML='<amount currency="<?currency?>EUR<?/?>"><?amount?>123<?/?></amount>
<payerref><?$payer_ref?></payerref>
<paymentmethod><?$card_ref?></paymentmethod>';
	protected $mandatory=array(
            'card_ref','payer_ref','amount'
					);

	protected $hash_order2=".amount.currency.payer_ref";
    // this hash order is weird but it works
}
