<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing Realex "card-new" transaction
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


class billing_Model_Realex_Transaction_cardnew extends billing_Model_Realex_Transaction_generic {
	protected $basicXML='<card>
 <ref><?$card_ref?></ref>
 <payerref><?$payer_ref?></payerref>
 <number><?$cc_number?></number>
 <expdate><?$exp_month?><?$exp_year?></expdate>
 <type><?cc_type?>visa<?/?></type>
 <chname><?$cc_name?></chname>
 <issueno><?$issueno?></issueno>
</card>';
	protected $mandatory=array(
            'card_ref','payer_ref',
            'cc_number','exp_month',
            'exp_year','cc_name'
					);

	protected $hash_order2=".amount.currency.payer_ref.cc_name.cc_number";
    // this hash order is weird but it works
}
