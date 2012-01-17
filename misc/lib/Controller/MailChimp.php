<?php
class Controller_MailChimp extends AbstractController {
    function subscribe($email,$name){
        $merges=array(
                'FNAME'=>$name,
                );
        $data=array(
                'email_address'=>$email,
                'apikey'=>$this->api->getConfig('mailchimp/apikey'),
                'id'=>$this->api->getConfig('mailchimp/list'),
                'merge_vars' => $merges
                );
        $payload=json_encode($data);
 
        //replace us2 with your actual datacenter
        $submit_url = "http://us4.api.mailchimp.com/1.3/?method=listSubscribe";
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $submit_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));

        $result = curl_exec($ch);
        curl_close ($ch);
        $data = json_decode($result);
    }
}
/*
        if ($data->error){
            echo $data->code .' : '.$data->error."\n";
        } else {
            echo "success, look for the confirmation message\n";
        }
        */
