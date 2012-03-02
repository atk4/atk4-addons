<?php

Class Controller_OAuth_Facebook extends Controller_OAuth {
    protected $type = "facebook";
    protected $app_id;
    protected $scope;
    function check($scope="email"){
        $this->setScope($scope);
        return parent::check();
    }
    function setSignatureInfo(){
        parent::setSignatureInfo();
        $this->app_id = $this->api->getConfig("oauth/" . $this->type . "/app_id");
    }
    function setScope($scope){
        $this->scope = $scope;
    }
    function getAuthToken($full=null){
        if ($code = $_GET["code"]){
            try {
                $this->obtainAccessToken($code);
                $this->api->redirect($this->callback_url);
                exit;
            } catch (Exception $e){
                $this->callback_error_url->setArguments(array("error_msg" => $e->getMessage()));
                $this->api->redirect($this->callback_error_url);
            }
            exit;
        }
        if ($token = $this->recall("oauth-access-token")){
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
            . $this->app_id . "&redirect_uri=" . urlencode($this->callback_url->getURL()) . "&client_secret=" . $this->consumer_secret
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
            $this->memorize("oauth-access-token", $data);
        } else {
            throw new Exception("Could not fetch access token");
        }
        return $data;
    }
    function getAccessToken(){
        header("Location: https://www.facebook.com/dialog/oauth?client_id=".
            $this->app_id ."&redirect_uri=".(urlencode($this->callback_url->getURL()))."&scope=".$this->scope);
        exit;
    }
    function performFBRequest($url){
        $this->curlInit($url = $url . "?access_token=" . $this->getAuthToken(false));
        return json_decode($this->executeCurl());
    }
}
