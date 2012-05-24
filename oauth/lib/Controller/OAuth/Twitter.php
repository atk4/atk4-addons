<?php
namespace oauth;
class Controller_OAuth_Twitter extends Controller_OAuth {
    protected $type = "twitter";
    protected $request_token_baseurl = "https://api.twitter.com/oauth/request_token";
    protected $access_token_baseurl = "https://api.twitter.com/oauth/access_token";
    protected $authorize_token_baseurl = "https://api.twitter.com/oauth/authorize";

  function performTwitterRequest($url, $post_data = null, $get = false){
        $options = array(
            "oauth_token" => urlencode($this->getAuthToken(false)),
        );
        if ($get){
            return $this->performRequest($url, $options);
        } else {
            return $this->performPostRequest($url, array("Expect:"), $options, $post_data);
        }
    }

}
