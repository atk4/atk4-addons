<?php

class Controller_OAuth extends AbstractController {
    protected $ch; //curl handler
    protected $sign_method = "RSA-SHA1"; // also supported are: PLAINTEXT and HMAC-SHA1
    protected $certfile; // required for RSA-SHA1
    protected $consumer_key; // required for all types
    protected $request_token_baseurl; // url for retreiving request token
    protected $access_token_baseurl; // url for exchanging tokens
    protected $authorize_token_baseurl; // url for token authorization
    protected $callback_url; // callback url
    protected $callback_url_protocol = "http";
    protected $error_callback_url; // error calback url
    protected $error_callback_url_protocol = "http";
    protected $realm; // this is set automatically, to separate tokens from different providers
    function init(){
        parent::init();
        $this->realm = md5(get_class($this));
    }
    function resetAuthToken(){
        $this->api->forget("oauth-access-token");
        $this->api->forget("oauth-request-token");
    }
    function getAuthToken($full = true){
        if ($oauth_token = $_GET["oauth_token"]){
            $oauth_verifier = $_GET["oauth_verifier"];
            try {
                $this->obtainAccessToken($oauth_token, $oauth_verifier);
                header("Location: " . $this->callback_url_protocol . "://" . $this->callback_url);
            } catch (Exception $e){
                $appex = "&error=".base64_encode($e->getMessage());
                header("Location: " . $this->error_callback_url_protocol . "://" .$this->error_callback_url . $appex);
            }
            exit;
        }
        if ($token = $this->api->recall($this->realm . "oauth-access-token")){
            if ($full){
                return $token;
            } else {
                return $token["oauth_token"];
            }
        } else {
            $this->obtainRequestToken();
            $this->authorizeToken();
        }
    }
    function getAuthTokenSecret(){
        if (isset($this->use_request_token)){
            $oauth = $this->api->recall($this->realm . "oauth-request-token");
        } else {
            $oauth = $this->api->recall($this->realm . "oauth-access-token");
        }
        return $oauth["oauth_token_secret"];
    }
    function getRequestTokenSecret(){
        $oauth = $this->api->recall($this->realm . "oauth-request-token");
        return $oauth["oauth_token_secret"];
    }
    function setAuthToken($oauth_token, $oauth_token_secret = null){
        $this->api->memorize($this->realm . "oauth-access-token",
            array(
                "oauth_token" => $auth_token,
                "auth_token_secret" => $oauth_token_secret
            )
        );
    }
    function setSignMethod($method){
        /* supported: RSA-SHA1, PLAINTEXT */
        $this->sign_method = $method;
    }
    function signRequest($method, $baseurl, array $parameters){
        /*
         * $method = GET | POST
         * $baseurl = "https://www.google.com/accounts/OAuth...";
         * $parameters = "extra parameters"
         */
        if (method_exists($this, $sign_method = "signRequest" . preg_replace("/[^a-zA-Z0-9]+/", "", $this->sign_method))){
            return $this->$sign_method($method, $baseurl, $parameters);
        }
    }
    function signRequestPLAINTEXT($method, $baseurl, array $parameters){
        return $this->consumer_secret . "&" . $this->getAuthTokenSecret();
    }
    function signRequestHMACSHA1($method, $baseurl, array $parameters){
        $data = $this->createSignatureBase($method, $baseurl, $parameters);
        $sign = hash_hmac("sha1", $data, $raw=$this->consumer_secret . "&" . $this->getAuthTokenSecret(), true);
        return base64_encode($sign);
    }

    function signRequestRSASHA1($method, $baseurl, array $parameters){
        $fp = @fopen($this->certfile, "r");
        if (!$fp){
            throw new Exception("Could not read certificate file");
        }
        $private = fread($fp, 8192);
        fclose($fp);
        $data = $this->createSignatureBase($method, $baseurl, $parameters);
        $keyid = openssl_get_privatekey($private);
        openssl_sign($data, $signature, $keyid);
        openssl_free_key($keyid);
        return base64_encode($signature);
    }
    function encodeStr($str){
        return preg_replace("/\%7E/", "~", urlencode($str));
    }
    function createSignatureBase($method, $baseurl, array $parameters){
        $data = $method.'&';
        $data .= $this->encodeStr($baseurl).'&';
        $oauth = '';
        ksort($parameters);
        foreach($parameters as $key => $value){
            $oauth .= "&{$key}={$value}";
        }
        $data .= urlencode(substr($oauth, 1));
        return $data;
    }
    function buildAuthArray($baseurl, $extra = array(), $method = 'GET'){
        /*
         * All $extra params should be urlencoded!
         */
        $auth = array();
        $baseurl = preg_replace("/\?.*$/", "", $baseurl);
        $auth['oauth_consumer_key'] = $this->consumer_key;
        $auth['oauth_signature_method'] = $this->sign_method;
        $auth['oauth_timestamp'] = time();
        $auth['oauth_nonce'] = md5(uniqid(rand(), true));
        $auth['oauth_version'] = '1.0';

        $auth = array_merge($auth, is_array($extra)?$extra:array());
        $auth['oauth_signature'] = $this->signRequest($method, $baseurl, $auth);
        $auth['oauth_signature'] = urlencode($auth['oauth_signature']);
        return $auth;
    }
    function setCallbackUrl($callback_url, $protocol = null){
        $this->callback_url = $callback_url;
        if ($protocol){
            $this->callback_url_protocol = $protocol;
        }
    }
    function setErrorCallbackUrl($error_callback_url, $protocol = null){
        $this->error_callback_url = $error_callback_url;
        if ($protocol){
            $this->error_callback_url_protocol = $protocol;
        }
    }
    function setConsumerKey($consumer_key){
        $this->consumer_key = $consumer_key;
    }
    function setConsumerSecret($consumer_secret){
        $this->consumer_secret = $consumer_secret;
    }
    function setCertFile($certfile){
        $this->certfile = $certfile;
    }
    function useRequestToken(){
        /* this is needed when exchanging tokens and using HMAC-RSA signature method */
        $this->use_request_token = true;
    }
    function obtainAccessToken($oauth_token, $oauth_verifier = null){
        $params = array("oauth_token" => urlencode($oauth_token));
        if ($oauth_verifier){
            $params["oauth_verifier"] = urlencode($oauth_verifier);
        }
        $this->useRequestToken();
        $response = $this->performRequest($this->access_token_baseurl, $params);
        $response = explode("&", $response);
        $data = array();
        foreach ($response as $row){
            $row = explode("=", $row);
            $data[$row[0]] = $row[1];
        }
        if (isset($data["oauth_token_secret"])){
            $this->api->memorize($this->realm . "oauth-access-token", $data);
        } else {
            throw new Exception("Could not fetch access token");
        }
        return $data;
    }
    function authorizeToken(){
        $token_data = $this->api->recall($this->realm . "oauth-request-token", array());
        if ($token_data){
            header("Location: " . $this->authorize_token_baseurl . "?oauth_token=" . $token_data["oauth_token"] .
"&oauth_callback=http://" . $this->callback_url);
            exit;
        }
    }
    function obtainRequestToken($extra = array()){
        $response = $this->performRequest($this->request_token_baseurl, $extra);
        $response = explode("&", $response);
        $data = array();
        foreach ($response as $row){
            $row = explode("=", $row);
            $data[$row[0]] = $row[1];
        }
        $this->api->memorize($this->realm . "oauth-request-token", $data);
        return $data;
    }
    function performRequest($url, $extra = array()){
        $this->curlInit($url);
        $auth = $this->buildAuthArray($url, $extra);
        $this->setCurlAuthHeader($auth);
        $response = $this->executeCurl();
        return $response;
    }
    function performPostRequest($url, $extra_header = array(), $extra_auth = array(), $post = array()){
        $this->curlInit($url, "POST");
        $auth = $this->buildAuthArray($url, $extra_auth, "POST");
        $this->setCurlAuthHeader($auth, $extra_header);
        $this->curlSetPost($post);
        $response = $this->executeCurl();
        return $response;
    }
    function createAuthHeader($auth){
        $str = array();
        foreach($auth as $k => $v){
            $str[] = "{$k}=\"{$v}\"";
        }
        $str = implode(", ", $str);
        return $str;
    }
    /* curl methods */
    function curlInit($url, $method = "GET"){
        $this->ch=curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        curl_setopt($this->ch, CURLOPT_STDERR, fopen("curlerr.log", "a"));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
        curl_setopt($this->ch, CURLOPT_SSLVERSION,3);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == "POST"){
            curl_setopt($this->ch, CURLOPT_POST, 1);
        }
        return $this;
    }
    function curlSetPost($data){
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
    }
    function setCurlAuthHeader($auth, $extra = null){
        $auth_header = $this->createAuthHeader($auth);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array_merge(array("Authorization: OAuth {$auth_header}"),
                    is_array($extra)?$extra:array()));
        return $this;
    }
    function executeCurl(){
        $response = curl_exec($this->ch);
        $st = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if ($st < 200 || $st >= 300){
            $this->last_error = $response;
            throw new Exception("Could not process request ($st)" . $response, $st); 
        }
        return $response; 
    }
    function render(){
        
    }
}
