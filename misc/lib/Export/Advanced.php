<?php

class Export_Advanced extends Export_Basic{
    function init(){
        parent::init();
        $this->add("Export_Parser_CSV");
        $this->add("Export_Parser_XLS");
    }
}
