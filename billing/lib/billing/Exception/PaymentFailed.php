<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of XXX framework implementing XXX
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

class billing_Exception_PaymentFailed extends billing_Exception {
	static function c($msg){
		$x=new billing_Exception_PaymentFailed($msg);
		return $x;
	}
    function shortenVariable($v){
        if(is_array($v))return "Array(..)";
        if(is_object($v)){
            $t=get_class($v);
            if(method_exists($v,'__toString'))return $v->__toString();
            return "($t)[object]";
        }
        if(strlen($v)>1000)return '"'.htmlspecialchars(substr($v,0,600).'..'.substr($v,-300)).'"';
        return "'".htmlspecialchars($v)."'";
    }
    function setVariable($name,$value,$description=null){
        $this->additional_info[]=array($name,$this->shortenVariable($value),$description);
        return $this;
    }
}
