<?php
class UpgradeChecker extends HtmlElement {
    function init(){
        parent::init();
        $this->set($this->api->getVersion());

        if(isset($_COOKIE[$this->name]))return;

        //setcookie($this->name,1,time()+3600*24);
        
		$this->api->template->append('js_include',
		'<script aync="true" onload="atk4_version_check(\''.$this->name.
            '\')" type="text/javascript" src="http://agiletoolkit.org/upgrade_check/'.
        $this->api->getVersion().'.js"></script>'."\n");


    }
}
