<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing Realex "payer-new" transaction
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


class billing_Model_Realex_Transaction_payernew extends billing_Model_Realex_Transaction_generic {
	protected $basicXML='<payer type="<?payer_type?>Business<?/?>" ref="<?$payer_ref?>">
 <firstname><?$first_name?></firstname>
 <surname><?$last_name?></surname>
</payer>';
	protected $mandatory=array(
					'payer_ref',
					'first_name','last_name'
					);

	protected $hash_order2=".amount.currency.payer_ref";
    // this hash order is weird but it works
}
