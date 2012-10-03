<?php
# *****************************************************
# Author 	: Qambar Raza
# Add-on 	: Facebook Wall V1.0
# Desc 		: This plugin sends a message on a user's wall 
#			  interacting with facebook API 
# *******************************************************

namespace facebookwall; 

class FacebookWall extends \AbstractView {
	
	protected $loginUrl;
	protected $logoutUrl;
	
	protected $user;	 //Logged in user (/me)
	protected $facebook; //object to facebook sdk
	
	function render(){
    }

	function init() {
		parent::init();
		$this->facebook = new Facebook(array(
		  'appId'  => $this->api->getConfig('appId'),
		  'secret' => $this->api->getConfig('secret'),
		));
		
		// Get User ID
		$this->user = $this->facebook->getUser();

		// We may or may not have this data based on whether the user is logged in.
		//
		// If we have a $this->user id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.

		if ($this->user) {
		  try {
			// Proceed knowing you have a logged in user who's authenticated.
			$user_profile = $this->facebook->api('/me');
		  } catch (FacebookApiException $e) {
			error_log($e);
			$this->user = null;
		  }
		}

		
		$this->loginUrl = $this->facebook->getLoginUrl(	array(
			   'scope' => 'publish_stream'
			  ));
		$this->logoutUrl = $this->facebook->getLogoutUrl();
		
		
	}
	/** Access tokens are used to interact with facebook, following function returns the token**/
	
	function getAccessToken() {
		return $this->facebook->getAccessToken();
	}
	
	function isLoggedIn() {
		return $this->user;
	}
	
	/**
	* Function that gets your friend's list
	* returns : friendsList as a sorted array
	**/
	function getFriends() {
	
		$friendsList = array();
		
		if ($this->user){
			$jasonData = file_get_contents('https://graph.facebook.com/me/friends?access_token='.$this->getAccessToken());
			$friends = json_decode($jasonData);
			$friendsList = array();
			foreach ($friends->data as $f) {
				$friendsList[(string) $f->id] = $f->name;
				
			}
			asort($friendsList);
		}
		
		return  $friendsList;
	}
	
	
	/**************************************************************************************
	postMessageOnWall() 
	$touid: Facebook Unique ID of an User. Set the variable to "me" if posting on logged in user's wall
	$msg: The Message to be posted above the actual link post
	$link: Direct (Full) URL of the Link to be posted
	***************************************************************************************/
	function postMessageOnWall($touid, $msg, $uri = ""){ 
	
		$url = "https://graph.facebook.com/".$touid."/feed";
		$data = array(
						 'access_token' => $this->getAccessToken()
						,'message' => $msg
						,'link' => $uri
						);
	 
		// use key 'http' even if you send the request to https://...
		$options = array('http' => array(
			'method'  => 'POST',
			'content' => http_build_query($data)
		));
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		
		return $result;
		
	}

	function getLoginUrl() {
		return $this->loginUrl;
	}
	function getLogoutUrl() {
		return $this->logoutUrl;
	}
}