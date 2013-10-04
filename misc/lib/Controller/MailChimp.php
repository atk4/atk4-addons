<?php

class Controller_MailChimp extends AbstractController {

    public $data_center = 'us4';
    public $api_key = null;
    public $list_id = null;

    /**
     * Initialize MailChimp Server Config
     * 
     * @param type $api_key
     * @param type $list_id
     */
    function initConfig($api_key = null, $list_id = null, $data_center = null) {
        if (!empty($api_key)) {
            $this->api_key = $api_key;
        }
        if (!empty($list_id)) {
            $this->list_id = $list_id;
        }
        if (!empty($data_center)) {
            $this->data_center = $data_center;
        }

        return $this;
    }

    /**
     * Send subscribe request to MailChimp API
     * 
     * @param type $email
     * @param type $name
     * @param type $return
     * @return type
     */
    function subscribe($email, $name = null, $return = false) {
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
        $submit_url = "http://" . $this->data_center . ".api.mailchimp.com/1.3/?method=listSubscribe";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $submit_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));

        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result);

        if ($return) {
            $status = array('status' => 'success');
            if ($data->error) {
                $status['status'] = 'failure';
                $status['code'] = $data->code;
                $status['error'] = $data->error;
            }

            return $status;
        }
    }

}