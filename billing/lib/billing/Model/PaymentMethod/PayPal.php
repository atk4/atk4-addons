<?php
class billing_Model_PaymentMethod_PayPal extends billing_Model_PaymentMethod_generic {
    var $sandbox_mode = false;
	function charge($amount,$currency='EUR',$descr=false){
		// returns URL to redirect to
		return $this->api->url('/ppproxy',array_merge(
					is_array($descr)?$descr:array('descr'=>$descr),
					array('amount'=>$amount,'currency'=>$currency)));
	}
	function ppproxy($id=null){
		// returns true if paymentwas successful

		// return false if used as thank you page

		if($_POST){
			// might be getting adta from paypal! better log!
			foreach ($_POST as $key=>$value) $postdata.=$key."=".urlencode($value)."&";  
			$postdata.="cmd=_notify-validate"; 
			$curl = curl_init("https://www.".$this->isSandbox()."paypal.com/cgi-bin/webscr"); 
			curl_setopt ($curl, CURLOPT_HEADER, 0); 
			curl_setopt ($curl, CURLOPT_POST, 1); 
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata); 
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 1); 
			$response = curl_exec ($curl); 
			curl_close ($curl);  
            $this->api->logger->logLine($response);
			if ($response != "VERIFIED"){
				$this->api->logger->logLine('FAILED: post='.print_r($_POST,true));
				exit;
			}else{
				$this->api->logger->logLine('VERIFIED: post='.print_r($_POST,true));
			}
			if($_POST['payment_status']=='Completed' and $_POST['txn_type']!='reversal')return true;
			
			exit;
		}
		if($_GET['amount']){
$r= '
<html><head></head><body onload="document.forms[0].submit()">
<form action="https://www.'.$this->isSandbox().'paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="business" value="'.$this->api->getConfig('billing/paypal/merchant').'">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="rm" value="2">
<input type="hidden" name="item_name" value="'.addslashes($_GET['descr']).'"> 
<input type="hidden" name="item_number" value="'.addslashes($_GET['id']).'"> 
<input type="hidden" name="amount" value="'.addslashes($_GET['amount']).'">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="notify_url" value="http://' . $_SERVER['HTTP_HOST'].$this->api->url('/ppproxy',array('ipn'=>$_GET['id'])).'">
<input type="hidden" name="cancel_return" value="http://' . $_SERVER['HTTP_HOST'].$this->api->url('/ppproxy',array('cancel'=>$_GET['id'])).'">
<input type="hidden" name="return" value="http://' . $_SERVER['HTTP_HOST'].$this->api->url('/ppproxy',array('success'=>$_GET['id'])).'">
<input type="hidden" name="currency_code" value="'.addslashes($_GET['currency']).'"> 
</form>
</body></html>
';
if($_GET['debug'])echo '<pre>'.htmlspecialchars($r);else echo $r;
exit;

		}
	}
    function isSandbox(){
        if ($this->api->getConfig("billing/paypal/sandbox", $this->sandbox_mode)){
            return "sandbox.";
        } else {
            return "";
        }
    }
}
