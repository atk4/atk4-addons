<?php
class TMail_Transport_SES extends TMail_Transport {
    public $output; // response from SAS server

    function getSignature($string){
		return base64_encode(hash_hmac('sha256', $string, $this->api->getConfig('tmail/AWSSecretKey'), true));
    }



    function send($obj,$to,$from,$subject,$body,$headers){
        $headers='To: '.$this->owner->args['to_formatted']."\n".$headers;
        $headers='Subject: '.$subject."\n".$headers;
        $headers='From: '.$this->owner->args['from_formatted']."\n".$headers;

        $query=array(
                'Action=SendRawEmail',
                'Source='.urlencode($from),
                'Destinations.member.1='.urlencode($to),
                'RawMessage.Data='.$u=urlencode(base64_encode(utf8_encode($x=$headers."\n\n".$body)))
                );
        $query=implode('&',$query);

		$date=gmdate('D, d M Y H:i:s e');
		$auth='AWS3-HTTPS AWSAccessKeyId='.$this->api->getConfig('tmail/AWSAccessKeyId').
            ',Algorithm=HmacSHA256,Signature='.$this->getSignature($date);
        $host=explode('/',$this->api->getConfig('tmail/ses_url'));
        $host=$host[2]; // https://host/...
        
        $headers=array(
                'Date: '.$date,
                'Host: '.$host,
                'X-Amzn-Authorization: '.$auth,
                'Content-Type: application/x-www-form-urlencoded'
                );
                
        
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, $this->api->getConfig('tmail/agent','AgileToolkit.org/TMail/SES'));

        /*
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, ($this->ses->verifyHost() ? 1 : 0));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, ($this->ses->verifyPeer() ? 1 : 0));
        */
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        /*
        $output='';
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, function(&$curl,&$data) use ($output) {
            $output.=$data;
            return strlen($data);
        });
        
        */

		curl_setopt($curl, CURLOPT_URL, $this->api->getConfig('tmail/ses_url'));
        
		if ($x=curl_exec($curl)) {
            // TODO: validate output
			curl_getinfo($curl, CURLINFO_HTTP_CODE);
		} else {
            throw $this->exception('SES/Curl problem')
                ->addMoreInfo('curl_errno',curl_errno($curl))
                ->addMoreInfo('curl_error',curl_error($curl))
                ;
		}
        $this->output=$output;

		@curl_close($curl);

        return $this;
    }
}
