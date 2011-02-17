<?php

Class Controller_OAuth_Facebook extends Controller_OAuth {
    protected $app_id;
    protected $scope;
    function setAppId($app_id){
        $this->app_id = $app_id;
    }
    function setScope($scope){
        $this->scope = $scope;
    }
    function getAuthToken(){
        if ($code = $_GET["code"]){
            try {
                $this->obtainAccessToken($code);
                header("Location: " . $this->callback_url_protocol . "://" . $this->callback_url);
                exit;
            } catch (Exception $e){
                $appex = "&oauth_error=".base64_encode($e->getMessage());
                header("Location: " . $this->error_callback_url_protocol . "://" .$this->error_callback_url . $appex);
            }
            exit;
        }
        if ($token = $this->api->recall($this->realm . "oauth-access-token")){
            $expires = $token["expires"];
            if ($expires < time()){
                $this->obtainRequestToken();
                $this->authorizeToken();
            } else {
                if ($full){
                    return $token;
                } else {
                    return $token["access_token"];
                }
            }
        } else {
            $this->getAccessToken();
        }
    }
    function obtainAccessToken($code){
        $url = "https://graph.facebook.com/oauth/access_token?client_id="
            . $this->app_id . "&redirect_uri=" . urlencode($this->callback_url_protocol . "://" . $this->callback_url) . "&client_secret=" . $this->consumer_secret
            . "&code=" . $code;
        $this->curlInit($url);
        $response = $this->executeCurl();
        $response = explode("&", $response);
        $data = array();
        foreach ($response as $row){
            $row = explode("=", $row);
            $data[$row[0]] = $row[1];
        }
        if (isset($data["access_token"])){
            $data["expires"] = time() + $data["expires"]; // validitiy of token
            $this->api->memorize($this->realm . "oauth-access-token", $data);
        } else {
            throw new Exception("Could not fetch access token");
        }
        return $data;
    }
    function getAccessToken(){
        header("Location: https://www.facebook.com/dialog/oauth?client_id=".
            $this->app_id ."&redirect_uri=".$this->callback_url_protocol ."://".$this->callback_url."&scope=".$this->scope);
        exit;
    }
    function performFBRequest($url){
        $this->curlInit($url = $url . "?access_token=" . $this->getAuthToken(false));
        return json_decode($this->executeCurl());
    }
}
