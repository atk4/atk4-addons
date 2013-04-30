<?php
class billing_Model_PaymentMethod_CreditCard extends billing_Model_PaymentMethod_generic {
	function init(){
		parent::init();

		$this->addField('user_id')
			->type('int')
			->system(true);

		$this->addField('card_ref')
			->type('string')
			->caption('Credit Card Ref')
			->mandatory("Must Be Filled")
			->length(20)
			->system(true)
		;
		$this->addField('cc_type')
			->type('list')
			->caption('Type')
			->listData(array(
				'VISA'=>'Visa',
				'LASER'=>'Laser',
				'MC'=>'Master Card',
				'AMEX'=>'Amex',
				'SWITCH'=>'Switch',
				'DINERS'=>'Diners',
			))
			->defaultValue('VISA')
		;
		$this->addField('cc_number')
			->type('string')
			->caption('Card Number')
			->length(20)
			->mandatory("Must be filled")
		;
		$this->addField('cc_name')
			->type('string')
			->caption('Name on card')
			->mandatory("Must be filled")
		;
		// this field contains cc_expiry reversed: YYMM instead of MMYY
		// this is required for proper fitlering by date
		$this->addField('exp_date')
			->type('string')
			->system(true)
			->calculated(true)
		;
		$this->addField('exp_month')
			->caption('Expiry month')
			->type('list')
			->listData(array(
						'01'=>'01-January',
						'02'=>'02-February',
						'03'=>'03-March',
						'04'=>'04-April',
						'05'=>'05-May',
						'06'=>'06-June',
						'07'=>'07-July',
						'08'=>'08-August',
						'09'=>'09-September',
						'10'=>'10-October',
						'11'=>'11-November',
						'12'=>'12-December',
						));
		;

		$ey=array();
		for($y=date('y');$y<date('y')+10;$y++)$ey[$y]="20$y";

		$y=$this->addField('exp_year')
			->type('list')
			->caption('Expiry year')
			->listData($ey)
		;
		$this->addField('cc_cvn')
			->type('string')
			->caption('CVN')
		;
			
	}
	private $cc_number=null;
	private $cc_cvn=null;
	function beforeInsert(&$data){
		$this->cc_number=$this->get('cc_number'); //$data['cc_number'];
		$this->cc_cvn=$this->get('cc_cvn'); //$data['cc_cvn'];
		/*
		if(!$this->get('cc_expiry',false)){
			$this->set('cc_expiry',$this->get('exp_month').$this->get('exp_year'));
		}
		*/
		$this->set('cc_number',$data['cc_number']='**** **** **** '.substr($this->get('cc_number'),-4));
		$this->set('cc_cvn',$data['cc_cvn']='***');
		return parent::beforeInsert($data);
	}

	function charge($amount,$currency='EUR',$descr=null){
		if(!$amount)return null;	// zero payment

		if(!$this->get('card_ref',false)){
			if(!$this->cc_number){
				$this->cc_number=$this->get('cc_number');
				$this->cc_cvn=$this->get('cc_cvn');
			}
			if(!$this->cc_number) throw new Exception_InitError('Credit card number was lost');
			$r=$this->getMerchant()->addRequest('auth');
			$r->set($this->data);
			$r->set('cc_number',$this->cc_number);
			$r->set('cc_cvn',$this->cc_cvn);
			$r->set('amount',round($amount*100));
			$r->set('currency',$currency);
			return $r->process();
		}else{
			$r=$this->getMerchant()->addRequest('receipt-in');

			$user_ref=$this->getUserRef();
			$card_ref=$this->getCardRef();

			$r->set('payer_ref',$user_ref);
			$r->set('card_ref',$card_ref);
			$r->set('amount',round($amount*100));
			return $r->process();
		}
	}

	function getUser(){
		if($this->api->isAdminPart()){
			$u=$this->get('user_id');
			$u=$this->add('Controller_User')->loadData($u);
		}else{
			$u=$this->api->getUser();
		}
		return $u;
	}
	function getUserRef(){
		/* returns existing ref or proceedes with registration and creates new */
		$u=$this->getUser();

		if(!$u->get('payer_ref',false)){
			$payer_ref=$this->api->getConfig('realex/userprefix','U').$u->get('id');

			$r=$this->getMerchant()->addRequest('payer-new');

			$u->set('payer_ref',$payer_ref);	// for user, db
			$r->set('payer_ref',$payer_ref);	// for request

			
			$r->set('first_name',preg_replace('/[^a-zA-Z0-9 ]/','',$u->get('first_name')));
			$r->set('last_name',preg_replace('/[^a-zA-Z0-9 ]/','',$u->get('last_name')));
			$r->process();

			$u->update();
		}
		return $u->get('payer_ref');
	}
	function getCardRef(){
		/* returns existing ref or proceedes with registration and creates new */
		if(!$this->get('card_ref',false)){
			$this->update();
			if(!$this->cc_number)throw new Exception_InitError('Credit card number was lost');
			$card_ref=$this->api->getConfig('realex/cardprefix','C').$this->get('id');

			$r=$this->getMerchant()->addRequest('card-new');
			$this->set('card_ref',$card_ref);

			$r->set($this->data);
			$r->set('cc_number',$this->cc_number);
			$r->set('cc_cvn',$this->cc_cvn);
			
			$r->set('payer_ref',$this->getUserRef());

			$r->process();

			$this->update();
		}
		return $this->get('card_ref');
	}

	function saveCard($default=false){
		$this->getCardRef();
		if($default){
			// saving card as default
			$u=$this->getUser();
			if($u->get('current_card_id')!=$this->get('id')){
				$u->set('current_card_id',$this->get('id'));
				$u->update();
			}
		}
		return $this;
	}

	function void($resp){
		$reg2 = $this->getMerchant()->addRequest('void');
		$reg2->set('pasref',$resp->pasref);
		$reg2->set('authcode',$resp->authcode);
		$reg2->set('orderid',$resp->orderid);
		return $reg2->process();
	}
	function verify(){
		$resp=$this->charge(1);
		$this->void($resp);

		// Functions below will execute necessary API calls if 
		// ref is missing
		$user_ref=$this->getUserRef();
		$card_ref=$this->getCardRef();

	}
	protected function calculate_exp_date(){
		return 'concat(exp_year,exp_month)';
	}
	public function toString() {
		return $this->get('cc_name').' ('.$this->get('cc_number').')';
	}
	public function toStringSQL($source_field, $dest_fieldname, $expr = 'card_ref') {
		return parent::toStringSQL($source_field, $dest_fieldname, 'card_ref');
	}

	/*
	public function afterDelete($old_id){
		$this->api->db->dsql()->table('sys_user')
			->set('current_card_id',null)
			->where('current_card_id',$old_id)
			->do_update();
		return parent::afterDelete($old_id);
	}
	*/
	
	function getMerchant(){
		return $this->api->add('billing_Controller_Merchant_Realex');
	}
}
