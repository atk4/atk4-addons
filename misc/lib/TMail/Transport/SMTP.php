<?php
class TMail_Transport_SMTP extends TMail_Transport {
    private $errorNr;
    private $errorStr;
    private $errorTimeout = 5;
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
    function send($ptr, $to,$from,$subject,$body,$headers){
        if (!in_array(ord($headers[strlen($headers)]), array(0x0A, 0x0D))){
            $headers .= "\n";
        }
        $headers.='From: '.$this->owner->args['from_formatted']."\n";
        $headers.='To: '.$this->owner->args['to_formatted']."\n";
        $headers.='Subject: '.$subject."\n";

        $this->connect();
        $out = "";
        $task = "";
        $out .= $task . fgets($this->fid, 4096);
        fputs($this->fid, $task = "HELO localhost\n");
        $out .= $task . fgets($this->fid, 4096); 
        $out .= null;
        fputs($this->fid, $task = "MAIL FROM:<".$this->api->getConfig("tmail/from").">\n");
        $out .= $task . fgets($this->fid, 4096);
        fputs($this->fid, $task = "RCPT TO:<$to>\n");
        $out .= $task . fgets($this->fid, 4096);
        fputs($this->fid, $task = "DATA\n");
        $out .= $task . fgets($this->fid, 4096);
        fputs($this->fid, $task = "SUBJECT: $subject\n");
        $out .= $task;
        fputs($this->fid, $task = ($headers?$headers:"") ."\n");
        $out .= $task;
        fputs($this->fid, $body);
        $out .= $body;
        fputs($this->fid, "\n.\n");
        $out .= fgets($this->fid, 4096);
        fputs($this->fid, "QUIT\n");
        $out .= fgets($this->fid, 4096);
        fclose($this->fid);
        return true;
    }
}
