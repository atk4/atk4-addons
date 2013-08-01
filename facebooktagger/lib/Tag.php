<?php
# *****************************************************
# Author    : Qambar Raza
# Add-on    : Facebook Wall V1.0
# Desc      : This plugin sends a message on a user's wall 
#             interacting with facebook API 
# *******************************************************

namespace facebooktagger; 

class MyPhotoAlbumFacebook extends FacebookTagger
{
  //  var $APP_ID = '000000';
 //   var $SECRET = 'xxxxxx';

    //var $facebook;

    function init() {
        parent::init();
    }

    public function login()
    {
        //var_dump($this->facebook);
        if (!$this->facebook) {
            $loginParameters = array(
                    'canvas'    => 1,
                    'fbconnect' => 0,
                    'req_perms' => 'status_update, publish_stream, user_photo_video_tags, friends_photo_video_tags, user_photos, friends_photos'
                    );
            $url = $this->facebook->getLoginUrl($loginParameters);
            echo "<script type='text/javascript'>top.location.href = '$url';</script>"; 
            exit;
        }
        return true;
    }

    public function getFacebook()
    {
        return $this->facebook;
    }

    public function getMe()
    {
        return $this->facebook->api('/me');
    }

    public function postPhoto($source, $message = null, $tags = array())
    {
        $source = '@' . realpath($source);
        try {
            $requestParameters = array(
                    'access_token' => $this->facebook->getAccessToken(),
                    'message'      => $message,
                    'source'       => $source,
                    );
            if ($tags) {
                $requestParameters['tags'] = $tags;
            }
           // var_dump($requestParameters);
            //die();

            $this->facebook->setFileUploadSupport(true);
            $response = $this->facebook->api('me/photos', 'POST', $requestParameters);
        } catch (FacebookApiException $e) {
            throw $e;
        }
        return $response;
    }

    public function getProfilePhoto($uid)
    {
        // use FQL. (http://developers.facebook.com/docs/reference/fql/)
        $fql = 'SELECT pic_big FROM profile WHERE id = ' . $uid;
        $url = 'https://api.facebook.com/method/fql.query?query=' . urlencode($fql);
        $xml = simplexml_load_file($url);
        if (!$xml || !$xml->profile) {
            return false;
        }
        $source = (string)$xml->profile->pic_big;

        // create local file.
        $file = file_get_contents($source);
        $tmpfname = tempnam('/tmp', 'source');
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $file);
        fclose($handle);
        return $tmpfname;
    }
    public function getCustomPhoto($source)
    {
        // use FQL. (http://developers.facebook.com/docs/reference/fql/)
       // $fql = 'SELECT pic_big FROM profile WHERE id = ' . $uid;
        //$url = 'https://api.facebook.com/method/fql.query?query=' . urlencode($fql);
  /*
        $xml = simplexml_load_file($url);
        if (!$xml || !$xml->profile) {
            return false;
        }
        $source = (string)$xml->profile->pic_big;
*/
        // create local file.
        $file = file_get_contents($source);
        $tmpfname = tempnam('/tmp', 'source');
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $file);
        fclose($handle);
        return $tmpfname;
    }
}