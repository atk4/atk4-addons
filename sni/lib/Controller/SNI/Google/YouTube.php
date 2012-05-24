<?php

namespace sni;
Class Controller_SNI_Google_YouTube extends Controller_SNI_Google {
    private $baseurl = "http://gdata.youtube.com";
    function init(){
        parent::init();
    }
    function getUserProfile($username = null){
        $url = $this->baseurl . "/feeds/api/users/default";
        return $this->oauth->performGoogleRequest($url, null, $this->oauth_token, null, "GET");
    }
    function uploadVideo($file_name, $meta_data = null){
        $url = "http://uploads.gdata.youtube.com/feeds/api/users/default/uploads";
        $atom_xml = $this->createAtomRequest($meta_data);
        $params = array(
        );
        $extra_headers = array(
            "Slug: " . $file_name,
            "Content-Type: multipart/related",
            "X-GData-Key: key=" . $this->developer_key
        );
        $post_data = array(
            "atom" => $atom_xml,
            "file" => "@" . $file_name
        );
        return $this->oauth->performGoogleRequest($url, $params, $this->oauth_token, $extra_headers, "POST", $post_data);
    }
    function createAtomRequest($meta_data){
        if (!isset($meta_data["title"])){
            throw new Exception("meta_data:title not set");
        }
        if (!isset($meta_data["description"])){
            throw new Exception("meta_data:description not set");
        }
        if (!isset($meta_data["category"])){
            throw new Exception("meta_data:category not set");
        }
        if (!isset($meta_data["keywords"])){
            throw new Exception("meta_data:keywords not set");
        }
        $atom_xml = '<'.'?xml version="1.0"?>
        <entry xmlns="http://www.w3.org/2005/Atom"
          xmlns:media="http://search.yahoo.com/mrss/"
          xmlns:yt="http://gdata.youtube.com/schemas/2007">
          <media:group>
            <media:title type="plain">'.$meta_data["title"].'</media:title>
            <media:description type="plain">'.$meta_data["description"].'
            </media:description>
            <media:category scheme="http://gdata.youtube.com/schemas/2007/categories.cat">'.$meta_data["category"].'</media:category>
            <media:keywords>'.$meta_data["keywords"].'</media:keywords>
          </media:group>
        </entry>';
        return $atom_xml;
    }
}
