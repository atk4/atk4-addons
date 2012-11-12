<?php
namespace misc;
class Controller_AutoPaginator extends \AbstractController {
    
    function init(){
        parent::init();
    }
    function setLimit($limit){
        $this->limit = $limit;
        $this->owner->dq->limit($limit);
        $g = $this->owner;
        if (isset($_REQUEST[$g->name . "_offset"])){
            $offset = $_REQUEST[$g->name . "_offset"];
            $g->dq->limit($limit + $offset);
            $g->show_header = false;
            $g->js(true)->attr("offset", $limit + $offset);
        } else {
            $g->js(true)->attr("offset", 0);
        }
        //$g->js(true, "$(window).scroll(function(){if ($(window).scrollTop() + $(window).height() == $(document).height()){" . $g->js()->univ()->ajaxec($this->api->url(null, array($g->name . "_more" => true)), array("offset" => $g->js()->attr("offset"))) . "}});");
        $g->js(true, "$(window).scroll(function(){if ($(window).scrollTop() + $(window).height() == $(document).height()){" . $g->js()->reload(array($g->name . "_offset" => $g->js()->attr("offset"))) . "}});");
    }
    function trap($o, $what){
        $this->out .= $what;
    }
}
