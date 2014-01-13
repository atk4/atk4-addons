<?php

namespace sni;
class Controller_SNI_Twitter extends Controller_SNI {
    protected $baseurl = "http://api.twitter.com";
    protected $oauth; //oauth object
    function setOAuth($oauth){
        $this->oauth = $oauth;
    }
    function statusUpdate($msg){
        $url = $this->baseurl . "/1.1/statuses/update.json";
        return $this->oauth->performTwitterRequest($url,array("status" => $msg));
    }
    function getUserProfile($screen_name){
        $url = $this->baseurl . "/1.1/users/show.json?screen_name=" . $screen_name;
        return $this->oauth->performTwitterRequest($url, null, true);
    }
}
