<?php
/* {{{ vim:ts=4:sw=4:et

   About: This file is part of billing framework implementing basic Realex transaction
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

class billing_Model_Realex_Transaction_generic extends AbstractModel {
	protected $template;                      // SMLite object containing XML template

	protected $basicXML=null;                 // common part of XML message
	protected $additionalXML=null;            // additional part of XML message

	protected $mandatory=array();                      // list of template fields which are
    protected $hash_order1 = "timestamp.merchantid.orderid";
    protected $hash_order2 = "";
	protected $url="https://epage.payandshop.com/epage-remote-plugins.cgi"; // URL where CURL requests will be sent to



	public $sentXML=null,$responseXML=null;        // data sent / received
    public $sentSimpleXML=null;                    // Parsed sent
	public $responseSimpleXML=null;                // Parsed received

	public $code, $message;


	function init(){
        parent::init();
        $this->template = $this->add('SMlite');
		$this->template->loadTemplateFromString($this->getXML());

		$timestamp = strftime("%Y%m%d%H%M%S");
		$this->set('orderid',$timestamp."-".mt_rand(1, 999));
		$this->set('merchantid',$this->api->getConfig('billing/realex/merchantid'));
		$this->set('timestamp',$timestamp);
		$this->set('account',$this->api->getConfig('billing/realex/account'));
	}

	// Data manipulation
	function set($a,$b=null,$escape=true){
		if(is_array($a)){
			foreach($a as $key=>$val){
				$this->set($key,($escape)?htmlspecialchars($val):$val); // escaping for correct XML value
			}
		}else{
			$this->template->trySet($a,($escape)?htmlspecialchars($b):$b);  // escaping for correct XML value
			$key = array_search($a,$this->mandatory);
			if($key!==false){
				unset($this->mandatory[$key]);
			}
		}
		return $this;
	}
	function get($a){
		return $this->template->get($a);
	}
	function getXML(){
		return
'<request type="<?$type?>" timestamp="<?$timestamp?>">'."\n".
'<merchantid><?$merchantid?></merchantid>'."\n".
'<account><?$account?></account>'."\n".
'<orderid><?$orderid?></orderid>'."\n".
$this->basicXML."\n".
'<?$hash?>'."\n".
$this->additionalXML."\n".
'</request>';
	}
	function getHash(){
		// Construct hash string of fields listed in hash_order
		$fields=explode('.',$this->hash_order1.$this->hash_order2);
		$data=array();
		foreach($fields as $field){
			$data[]=$this->get($field);
		}
		$data=join('.',$data);
		$this->tmp_hash=$data;
		$data=$this->hashFunc($data);
		$data=$data.'.'.$this->api->getConfig('billing/realex/secret');
		$data=$this->hashFunc($data,true);
		return $data;
	}
	function hashFunc($data,$addTag=false){
		$hash_func=$this->owner->hash_func;
		$h= $hash_func($data);
		if($addTag)return '<'.$hash_func.'hash>'.$h.'</'.$hash_func.'hash>';
		return $h;
	}

	// Request processing
	function getRequest(){
		if($this->mandatory){
			throw new billing_Exception_PaymentFailed("Mandatory fields are not specified: ".join(', ',$this->mandatory)." in ".get_class($this));
		}

		$this->set('hash',$this->getHash(),false);
		return $this->template->render();
	}
    // Sent request to Realex and parse result. Throws billing_Exception_PaymentFailed if credit card data was not valid or
    // billing_Exception on other errors
	function process(){
		$this->sentXML=$this->getRequest();

        try{
            if($this->api->getConfig('billing/realex/demo_mode_always_pay',false)){
                // Running in demo mode, so all payments will be successful.
                $this->responseXML='<'.'?xml version=\'1.0\' encoding = \'ISO-8859-1\'?'.'>
<response timestamp="20060416201509">
<fake>System is in auto-pay mode, all transactions are automatically approved</fake>
<merchantid>'.$this->api->getConfig('billing/realex/merchantid').'</merchantid>
<account>'.$this->api->getConfig('billing/realex/account').'</account>
<orderid>19911818-928</orderid>
<result>00</result>
<pasref>pr123</pasref>
<message>Successful</message>
<authcode>ac123</authcode>
<batchid></batchid>
<timetaken>0</timetaken>
</response>';
            }else{
                if(!function_exists('curl_init')){
                    // we lack curl support, running in test mode

                    throw new billing_Exception("Missing CURL, unable to send request");// \n".htmlspecialchars($this->getRequest()));
                    // TODO - add support for atk4
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, $this->owner->version);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->sentXML);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->api->getConfig('billing/realex/strict_ssl',false)); // this line makes it work under https
                $this->responseXML = curl_exec ($ch);
                if (empty($this->responseXML))
                    throw billing_Exception::c("Received empty respons from CURL on Realex request")
                        ->addVariable('CURL error',curl_error());

                curl_close ($ch);
            }



            if (($resp = $this->responseSimpleXML = @simplexml_load_string($this->responseXML))===false)
                throw billing_Exception::c("Error during responseXML parsing")
                    ->addVariable('responseXML',$this->responseXML);

            if ((empty($resp)) or (!is_object($resp)))
                throw billing_Exception::c("Parse was successful but result is not object!");

            if(!isset($resp->result))
                throw billing_Exception::c("Result code was not specified in XML received from Realex")
                    ->addSimpleXML($resp);

            $this->code = (int) $resp->result;
            $this->message = $resp->message;
            $this->pasref = $resp->pasref;
            $this->orderid = $resp->orderid;
            $this->authcode = $resp->authcode;


            if($this->code)
                throw billing_Exception_PaymentFailed::c("Payment failed: ".$this->message)
                    ->setVariable('message',$this->message)
                    ->setVariable('code',$this->code)
                    ;

        }catch(billing_Exception $be){
            $this->hook('failed-transaction',array($this,$resp));
            $this->hook('request-complete',array($this,$resp));
            throw $be;
        }
        $this->hook('successful-transaction',array($this,$resp));
        $this->hook('request-complete',array($this,$resp));

		return $this;
	}
}
