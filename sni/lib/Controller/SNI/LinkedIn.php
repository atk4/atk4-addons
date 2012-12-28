<?php
namespace sni;
class Controller_SNI_LinkedIn extends Controller_SNI {
    protected $baseurl = "http://api.linkedin.com/v1/";
    protected $oauth; //oauth object
    function setOAuth($oauth){
        $this->oauth = $oauth;
    }
    function getUserProfile(){
        $url = $this->baseurl . "people/~";
        return $this->oauth->performLinkedInRequest($url, null, true);
    }
    function getUserFullProfile(){
        $fields = array(
            "id",
            "email-address",
            "first-name",
            "last-name",
            "headline",
            "location",
            "industry",
            "associations",
            "honors",
            "interests",
            "publications",
            "patents",
            "languages",
            "skills",
            "certifications",
            "educations",
            "courses",
            "volunteer",
            "positions",
            "public-profile-url"
        );
        $url = $this->baseurl . "people/~:(" . implode(",", $fields) .")";
        return $this->oauth->performLinkedInRequest($url, null, true);
    }

}
