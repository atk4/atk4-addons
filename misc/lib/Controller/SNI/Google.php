<?php

class Controller_SNI_Google extends Controller_SNI {
    protected $oauth;
    protected $developer_key;
    function setOAuth($oauth){
        $this->oauth = $oauth;
    }
    function setDeveloperKey($key){
        $this->developer_key = $key;
    }
    function setAuthToken($oauth){
        if (is_array($oauth)){
            $this->oauth_token = $oauth["oauth_token"];
            $this->oauth_token_secret = $oauth["oauth_token_secret"];
        } else if (is_object($oauth) && method_exists($oauth, "getAuthToken")){
            $this->setAuthToken($oauth->getAuthToken());
        }
    }
    function request($url, $params){
        
    }
}
