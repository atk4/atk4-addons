<?php
class Controller_SNI_LinkedIn extends Controller_SNI {
    protected $baseurl = "http://api.linkedin.com/v1/";
    protected $oauth; //oauth object
    function setOAuth($oauth){
        $this->oauth = $oauth;
    }
    function getUserProfile(){
        $url = $this->baseurl . "people/~";
        return $this->oauth->performLinkedInRequest($url, null, true);
    }
}
