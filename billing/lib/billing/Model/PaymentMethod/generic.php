<?php
class billing_Model_PaymentMethod_generic extends Model_Table {
	protected $entity_code='billing_paymentmethod';
	protected $table_alias='b_pm';
	function defineFields(){
		parent::defineFields();
		$this->newField('merchant_type')
			->datatype('list')
			->caption('Merchant')
			->system(true)
			->listData(array(
						'creditcard'=>'CreditCard',
						'paypal'=>'PayPal'
						));
	}
}
