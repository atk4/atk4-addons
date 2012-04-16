<?php
class TMail_Transport_PHPMailer extends TMail_Transport {
    function connect(){
        $this->fid = fsockopen(
            $this->api->getConfig("tmail/smtp/host"),
            $this->api->getConfig("tmail/smtp/port"),
            $this->errorNr,
            $this->errorStr,
            $this->errorTimeout
        );     
        if (!$this->fid){
            throw $this->exception("Could not connect to mail server: " . $this->errorStr);
        }   
    } 
    function send($to,$from,$subject,$body,$headers){
        require_once("PHPMailer/class.phpmailer.php");
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
        $mail->SMTPAuth   = $this->api->getConfig("tmail/phpmailer/username", null)?true:false;                  // enable SMTP authentication
        $mail->Host       = $this->api->getConfig("tmail/smtp/host");
        $mail->Port       = $this->api->getConfig("tmail/smtp/port");
        $mail->Username   = $this->api->getConfig("tmail/phpmailer/username", null);
        $mail->Password   = $this->api->getConfig("tmail/phpmailer/password", null);
        $mail->AddReplyTo($this->api->getConfig("tmail/phpmailer/reply_to"), $this->api->getConfig("tmail/phpmailer/reply_to_name"));
        $mail->AddAddress($to);
        $mail->SetFrom($this->api->getConfig("tmail/phpmailer/from"), $this->api->getConfig("tmail/phpmailer/from_name"));
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AltBody = null;
        $mail->IsHTML(true);
        foreach (explode("\n", $headers) as $h){
            $mail->AddCustomHeader($h);
        }
        $mail->Send();
    }
}
 
