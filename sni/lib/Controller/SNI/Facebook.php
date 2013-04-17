<?php
namespace sni;
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
    function customRequest($url){
        return $this->oauth->performFBRequest($this->baseurl . $url);
    }
    function customPostRequest($url){
        return $this->oauth->performFBPostRequest($this->baseurl . $url);
    }
    function statusUpdate($message){
        $profile = $this->getUserProfile();
        $this->oauth->performFBPostRequest($this->baseurl . "/". $profile->id . "/feed", array("message" => $message));
    }
    function getFriends(){
        $url = $this->baseurl . "/me/friends";
        return $this->oauth->performFBRequest($url);
    }
}
