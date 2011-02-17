<?php

class Controller_OAuth_LinkedIn extends Controller_OAuth {
    protected $request_token_baseurl = "https://api.linkedin.com/uas/oauth/requestToken";
    protected $access_token_baseurl = "https://api.linkedin.com/uas/oauth/accessToken";
    protected $authorize_token_baseurl = "https://www.linkedin.com/uas/oauth/authorize";

  function performLinkedInRequest($url, $post_data = null, $get = false){
        $options = array(
            "oauth_token" => urlencode($this->getAuthToken(false)),
        );
        if ($get){
            return $this->performRequest($url, $options);
        } else {
            return $this->performPostRequest($url, array("Expect:"), $options, $post_data);
        }
    }

    function obtainRequestToken($extra = array()){
        $extra["oauth_callback"] = urlencode($this->callback_url_protocol . "://" . $this->callback_url);
        return parent::obtainRequestToken($extra);
    }
}
