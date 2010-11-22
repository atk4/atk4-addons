<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing Realex "void" transaction
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


class billing_Model_Realex_Transaction_void extends billing_Model_Realex_Transaction_generic {
	protected $url="https://epage.payandshop.com/epage-remote.cgi";
	protected $basicXML='<pasref><?$pasref?></pasref>
<authcode><?$authcode?></authcode>';
	protected $mandatory=array(
            'authcode','orderid'    // you should set original orderid too
					);

	protected $hash_order2=".amount.currency.payer_ref";
    // this hash order is weird but it works
}
