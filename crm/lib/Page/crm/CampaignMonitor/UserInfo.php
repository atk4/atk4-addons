<?php
class Page_crm_CampaignMonitor_UserInfo extends ATK3_Page {
	public $user;
	function cmRequest($r){
		return $this->add('Controller_crm_CampaignMonitor')->addRequest($r);
	}
	function initMainPage(){
		// Drawing main page. Let's see if user is subscribed fist

		//$f=$this->add('Form');
		$g=$this->add('Grid');

		if($_GET['Subscribe']){
			$result=$this->cmRequest('AddSubscriber')
				->set('ListID',$_GET['Subscribe'])
				->set('Email',$this->user->get('email'))
				->set('Name',$this->user->get('first_name').' '.$this->user->get('last_name'))
				->process()->result;

			$g->js()->reload()->execute();
		}
		if($_GET['Unsubscribe']){
			$result=$this->cmRequest('Unsubscribe')
				->set('ListID',$_GET['Unsubscribe'])
				->set('Email',$this->user->get('email'))
				->process()->result;

			$g->js()->reload()->execute();
		}

		$r=$this->cmRequest('GetClientLists')->process();
		$data=array();
		foreach($r->result->List as $list){

			$r=$this->cmRequest('GetIsSubscribed')
				->set('ListID',$list->ListID)
				->set('Email',$this->user->get('email'))
				->process()->result;


			//$f->addField('checkbox','subscribed_'.$list->ListID,$list->Name)
			//	->set($r=='True');

			$data[]=array(
					'Name'=>$list->Name,
					'id'=>$list->ListID,
					'Subscribed'=>$r=='True'?'Y':''
					);
		}

		$g->addColumn('text','Name');
		$g->addColumn('text','id','ListID');
		$g->addColumn('text','Subscribed');

		$g->addColumn('button','Subscribe');
		$g->addColumn('button','Unsubscribe');


		$g->setStaticSource($data);


		/*

		$r=$this->add('Controller_crm_CampaignMonitor')->addRequest('GetIsSubscribed');
		$r->set('Email',$this->email);
		$is_subscribed=$r->process()->result=='True';


		*/


		//echo $u->get('email');
		//var_dump($_GET['id']);
	}

}
