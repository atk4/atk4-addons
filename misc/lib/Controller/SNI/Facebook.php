<?php

class Controller_SNI_Facebook extends Controller_SNI {
    protected $baseurl = "https://graph.facebook.com";
    protected $oauth; //oauth object
    function setOAuth($oauth){
        $this->oauth = $oauth;
    }
    function getUserProfile(){
        $url = $this->baseurl . "/me";
        return $this->oauth->performFBRequest($url);
    }
}
