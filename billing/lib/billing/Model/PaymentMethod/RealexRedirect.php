<?php
class billing_Model_PaymentMethod_RealexRedirect extends billing_Model_PaymentMethod_generic {
    var $sandbox_mode = false;
	function charge($order_id,$amount,$currency='EUR',$extras=array()){
		// returns URL to redirect to
		return $this->api->url('/realexproxy',array_merge(
					$extras,
					//is_array($descr)?$descr:array('descr'=>$descr),
					array(
						'amount'=>round($amount*100),
						'order_id'=>$order_id,
						'comment'=>$comment,
						'currency'=>$currency)));
	}
	function ppproxy($id=null){
		// returns true if paymentwas successful

		// return false if used as thank you page

		if($_POST){
			if($_POST['RESULT']==='00'){
                //success case
				return $_POST['ORDER_ID'];

			}
			echo '<a href="'. $this->api->getConfig('billing/realex/error_url', 'https://linkedfinance.com/') . '">Problem with payment</a>';
		}
		if($_GET['amount']){
			$ts=date('YmdHis');
			$merchantid=$this->api->getConfig('billing/realex/merchantid');
			$tmp = "$ts.$merchantid.".$_GET['order_id'].".".$_GET['amount'].".".$_GET['currency'];
			$tmp = md5($tmp);
			$tmp = $tmp.'.'.$this->api->getConfig('billing/realex/secret');
			$hash = md5($tmp);

$r= '
<html><head></head><body onload="document.forms[0].submit()">
<form action="https://epage.payandshop.com/epage.cgi" method="post" style="visibility: hidden">
<input type="text" name="MERCHANT_ID" value="'.$merchantid.'">
<input type="text" name="ORDER_ID" value="'.addslashes($_GET['order_id']).'">
<input type="text" name="ACCOUNT" value="'.$this->api->getConfig('billing/realex/account').'">
<input type="text" name="AMOUNT" value="'.addslashes($_GET['amount']).'">
<input type="text" name="CURRENCY" value="'.addslashes($_GET['currency']).'">
<input type="text" name="TIMESTAMP" value="'.$ts.'">
<input type="text" name="MD5HASH" value="'.$hash.'">
<input type="text" name="AUTO_SETTLE_FLAG" value="1">
<input type="submit"/>
</form>
</body></html>
';
if($_GET['debug'])echo '<pre>'.htmlspecialchars($r);else echo $r;
exit;

		}
	}
}
