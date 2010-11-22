<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of CRM integration framework implementing XXX
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

class Model_crm_CampaignMonitor_Request_generic extends AbstractModel {
    public $soap;
    public $function;
    public $area;
    public $result;

	function init(){
		parent::init();
		$this->soap=new SoapClient($this->owner->url,array('trace'=>true,'exceptions'=>true));
        $this->set('ApiKey',$this->owner->key);
        $this->set('ClientID',$this->owner->client);
	}
	function set($key,$val=null){
		if(is_array($key)){
			foreach($key as $a=>$b){
				$this->set($a,$b);
			}
			return;
		}

		if(is_null($val))unset($this->arguments[$key]);
		$this->arguments[$key]=$val;
        return $this;
	}
	function setFunction($function){
		$this->function=$function;
        return $this;
	}
	function process(){
		if($this->api->getConfig('crm/campaignmonitor/demo_mode',false)){
			return $this;
		}

        $fn=$this->function;

		// handle return values and throw exceptions!
		$this->resp=$this->soap->$fn($this->arguments);

        if(isset($this->resp)){
            $fn=$this->area.'.'.$this->function;
            foreach($this->resp as $key=>$val){
                if(substr($key,-6)=='Result')$this->result=$val;
            }
            if($this->result){
                if(isset($this->result->enc_value))$this->result=$this->result->enc_value;

                if(isset($this->result->Code) && $this->result->Code>100){
                    // Problem 
                    throw new BaseException("Received error (".$this->result->Code."): ".$this->result->Message." from
                            <pre>".htmlentities($this->soap->__getLastRequest()).'</pre>');
                }
            }else var_dump('No Result: ',$this->resp);
        }

		$this->hook('request-complete',array($this,$this->resp));
		return $this;
	}
}
