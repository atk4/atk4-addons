<?php

namespace misc;
class Export_Advanced extends Export_Basic{
    function init(){
        parent::init();
        $this->add("misc/Export_Parser_CSV");
        $this->add("misc/Export_Parser_XLS");
    }
}
