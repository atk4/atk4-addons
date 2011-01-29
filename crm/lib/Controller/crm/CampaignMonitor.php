<?php
class Controller_crm_CampaignMonitor extends AbstractController {
	public $soap;
	public $soap_params;

	// todo - move those to config
	public $url='http://api.createsend.com/api/api.asmx?wsdl';
	public $key;//='69f4ca7e2884fab4fdaa882061209df7';
	public $client;//='30de44c33258ababd46e8066dcd87736';
	
	function init(){
		parent::init();
		$this->key=$this->api->getConfig('crm/cm/key');
		$this->client=$this->api->getConfig('crm/cm/client');
		/*
		$this->soap=new SoapClient($this->url);
		$this->soap->ApiKey = $this->key;
		$this->soap->ApiKey = $this->key;
		*/
	}
	function addRequest($type){
		return $this
			->add('Model_crm_CampaignMonitor_Request_generic')
			->setFunction($type)
			->addHook('request-complete',array($this,'requestComplete'))
			;
	}
    function requestComplete($transaction,$response){
        // This function is executed after successful request
        $this->hook('request-complete',array($transaction,$response));
    }
}
