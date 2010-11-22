<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing merchant interface
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

interface billing_Controller_Merchant_Interface_Basic {
	function charge($card,$amount,$currency=null,$description=null); // deducts specified amount from the credit card
	function verify($card);        // verifies credit card by chargin 1.00 then voiding transaction
}
