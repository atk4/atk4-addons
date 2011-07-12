<?php
class Controller_Encrypt extends AbstractController {
    /* Model Controller to Encrypt certain fields of your model, when it's saved into database. Well suitable
       for securing your database contents.

       http://agiletolokit.org/a/encrypt

       Logic explanation:

       Encryption plugin handles loading and saving of encrypted fields. If decryption is not possible, then
       plugin will attempt to preserve original value without changes. However if field has been changed,
       then the new value will be stored.

       This scenario allows you to have ability to use encryption only of sensetive data on your interface.

       Note: empty string always encrypts and decrypts to empty string.
    */

    /* List of fields to be encrypted */
    public $fields=array();
    /* Original, encrypted value of field */
    public $encrypted_fields=array();
    /* Hash with fields which failed to be decrypted on load. */
    public $undecryptable_fields=array();
    /* Encryption cipher to be used */
    public $cipher=null;
    /* If exception is rasied in encryption/decryption, it's stored here and not thrown */
    public $last_exception;

    /* Init: Binds with it's owner, setting up callbacks. */
    function init(){
        parent::init();
        $this->owner->addHook('afterLoad',array($this,'afterLoad'));
        $this->owner->addHook('beforeModify',array($this,'beforeModify'));
    }
    /* Specify array with fields, which will be encrypted by this controller */
    function useFields($fields=array()){
        $this->fields=$fields;
        return $this;
    }
    /* Such as rot13, rsa. Extend this class and define encrypt_xx, decrypt_xx for new cipher xx */
    function useEncryption($cipher){
        $this->cipher=$cipher;
    }
    /* Internal Callback: Decrypts fields after model is loaded */
    function afterLoad($o){
        foreach($this->fields as $f) if(isset($o->data[$f])){
            $enc=$o->get($f);
            $dec=$this->decrypt($enc);
            $this->encrypted_fields[$f]=$enc;


            if($enc && !$dec){
                // Decryption failed
                $this->undecryptable_fields[$f]=true;
                $o->set($f,'#ENCRYPTED#');
                $o->original_data[$f]='#ENCRYPTED#';
                continue;
            }

            // Make sure that field is not marked as "Changed"
            $o->set($f,$dec);
            $o->original_data[$f]=$dec;
        }
    }
    /* Internal Callback: Encrypts fields before model is added or updated */
    function beforeModify($o,&$data){
        foreach($this->fields as $f) if(isset($o->data[$f])){
            $o->original_data[$f]=$this->encrypted_fields[$f];

            // Restore original encrypted data for fields which haven't been changed
            if(!$o->isChanged($f) && $o->get($f)===$data[$f]){
                // Restore original crypted field
                $o->set($f,$enc=$this->encrypted_fields[$f]);
                $data[$f]=$enc;
                continue;
            }

            $enc=$this->encrypt($data[$f]);

            // Handle undecryptable fields
            if($this->undecryptable_fields[$f]){

                // Do not update if it wasn't changed
                if(!$o->isChanged($f) || $data[$f]=='#ENCRYPTED#'){
                    $o->set($f,$enc=$this->encrypted_fields[$f]);
                    $data[$f]=$enc;
                    continue;
                }

            }

            if($enc===false){
                $e=$o->exception('Unable to store field data securely')
                    ->setField($f)
                    ;
                if($this->last_exception)
                    $e->addMoreInfo('original_exception',$this->last_exception->getMessage());
                throw $e;
            }

            $data[$f]=$enc;
        }
    }
    /* Internal. Add encrypt_{cipher} to extend this */
    function encrypt($text){
        if(!$text)return '';
        $f='encrypt_'.$this->cipher;
        $res=$this->$f($text);
        return $res;
    }
    /* Internal. Add decrypt_{cipher} to extend this */
    function decrypt($text){
        if(!$text)return '';
        $f='decrypt_'.$this->cipher;
        $res=$this->$f($text);
        return $res;
    }
    /* Implements ROT 13 Encryption, http://en.wikipedia.org/wiki/ROT13 */
    function encrypt_rot13($text){
        return str_rot13($text);
    }
    /* Implements ROT 13 Decryption, http://en.wikipedia.org/wiki/ROT13 */
    function decrypt_rot13($text){
        return str_rot13($text);
    }
    /* Implements RSA encryption. Uses config. See http://agiletoolkit.org/a/encrypt/rsa */
    function encrypt_rsa($text){
        try{
            $public=openssl_get_publickey(file_get_contents($this->api->getConfig('rsa/public/path')));
            openssl_public_encrypt($text,&$out,$public);
            return base64_encode($out);
        }catch (Exception $e){
            return false;
        }
    }
    /* Implements RSA decryption. Uses config. See http://agiletoolkit.org/a/encrypt/rsa */
    function decrypt_rsa($text){
        try{
            $private=openssl_get_privatekey(array(file_get_contents($this->api->getConfig('rsa/private/path')),
                        $this->api->getConfig('rsa/private/passphrase')));
            openssl_private_decrypt(base64_decode($text),&$out,$private);
            return $out;
        }catch (Exception $e){
            return false;
        }
    }
}
