<?php
class billing_Model_PaymentMethod_generic extends SQL_Model
{
	public $entity_code = 'billing_paymentmethod';

	public $table_alias = 'b_pm';

	function init(){
        parent::init();

		$this->addField('merchant_type')
			->type('list')
			->caption('Merchant')
			->system(true)
			->listData(array(
						'creditcard' => 'CreditCard',
						'paypal'     => 'PayPal'
						));
	}
}
