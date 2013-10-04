<?php

class Controller_MailChimp extends AbstractController {

    public $api_key = null;
    public $list_id = null;

    /**
     * Initialize MailChimp Server Config
     * 
     * @param type $api_key
     * @param type $list_id
     */
    function initConfig($api_key = null, $list_id = null) {
        if (!empty($api_key)) {
            $this->api_key = $api_key;
        }
        if (!empty($list_id)) {
            $this->list_id = $list_id;
        }
    }

    /**
     * Send subscribe request to MailChimp API
     * 
     * @param type $email
     * @param type $name
     * @param type $api_key
     * @param type $list_id
     */
    function subscribe($email, $name = null) {
        $merges = array(
            'FNAME' => $name,
        );
        $data = array(
            'email_address' => $email,
            'apikey' => (!empty($this->api_key)) ? $this->api_key : $this->api->getConfig('mailchimp/apikey'),
            'id' => (!empty($this->list_id)) ? $this->list_id : $this->api->getConfig('mailchimp/list'),
            'merge_vars' => $merges
        );
        $payload = json_encode($data);

        //replace us2 with your actual datacenter
        $submit_url = "http://us4.api.mailchimp.com/1.3/?method=listSubscribe";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $submit_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));

        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result);

//        if ($data->error) {
//            echo $data->code . ' : ' . $data->error . "\n";
//        } else {
//            echo "success, look for the confirmation message\n";
//        }
    }

}