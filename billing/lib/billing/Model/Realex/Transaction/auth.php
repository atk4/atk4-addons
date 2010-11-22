<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing Realex "auth" transaction
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


class billing_Model_Realex_Transaction_auth extends billing_Model_Realex_Transaction_generic {
	protected $url="https://epage.payandshop.com/epage-remote.cgi";
		// AUTH transaction is sent to a different URL

	protected $basicXML='<amount currency="<?currency?>EUR<?/?>"><?amount?>123<?/?></amount>
<card>
  <number><?$cc_number?></number>
  <expdate><?$exp_month?><?$exp_year?></expdate>
  <type><?cc_type?>visa<?/?></type>
  <chname><?$cc_name?></chname>
  <cvn>
   <number><?$cc_cvn?></number>
   <presind><?cvn_presind?>1<?/?></presind>
  </cvn>
</card>
<autosettle flag="1"/>';

	protected $mandatory=array(
					'cc_number',
					'exp_month',
					'exp_year',
					'cc_name',
					'cc_cvn'
					);

	protected $hash_order2=".amount.currency.cc_number";
}
