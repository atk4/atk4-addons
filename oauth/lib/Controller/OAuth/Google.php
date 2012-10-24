<?php
namespace oauth;
class Controller_OAuth_Google extends Controller_OAuth {
    protected $type = "google";
    protected $request_token_baseurl = "https://www.google.com/accounts/OAuthGetRequestToken";
    protected $access_token_baseurl = "https://www.google.com/accounts/OAuthGetAccessToken";
    protected $authorize_token_baseurl = "https://www.google.com/accounts/OAuthAuthorizeToken";
    protected $scope = "https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/userinfo.profile"; //defines to which google service to get access
    function obtainRequestToken($extra = array()){
        return parent::obtainRequestToken(array_merge($extra, array("scope" => urlencode($this->scope))));
    }
    function performGoogleRequest($url, $extra = array(), $token, $extra_headers = null, $method = null, $post_data = null){
        $options = array(
            "oauth_token" => $token,
        );
        $options = array_merge($options, is_array($extra)?$extra:array());
        $this->curlInit($url, $method);
        $auth = $this->buildAuthArray($url,
            $options,
            $method
        );
        $this->setCurlAuthHeader($auth, $extra_headers);
        if ($post_data){
            $this->curlSetPost($post_data);
        }
        $response = $this->executeCurl();
        return $response;
    }
    function setSignatureInfo(){
        $this->setConsumerKey($this->api->getConfig('oauth/'.$this->type.'/consumer/key'));
        $this->setCertFile($this->api->getConfig('oauth/'.$this->type.'/cert_file'));
        $this->setSignMethod("RSA-SHA1");
    }
}
