<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing basic exception
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

class billing_Exception extends BaseException {
	static function c($msg){
		$x=new billing_Exception($msg);
		return $x;
	}
	function addVariable($v){
		// get rid of this
	}
	function addSimpleXML($xml){
		// get rid of this
	}
}
