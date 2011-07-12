<?php

class Export_EMT extends Export {
    function setEMTData($data){
        $emt = $this->api->getConfig("emt");
        $out = array();
        $sum = 0;
        $count = 0;
        foreach ($data as $row){
            $count++;
            $out[] = $row["sort"] . $row["acc"] . $emt["prefix"] . sprintf("%015d", $row["amount"] * 100) . $row["reference"];
            $sum += $row["amount"] * 100;
        }
        $out[] = $emt["final_prefix_p1"] . sprintf("%03d", $count) .$emt["final_prefix_p2"]. sprintf("%015d", $sum) . $emt["ref"];
        $this->emt = implode("\n", $out);
    }
    function exportEMT($filename){
        $this->_export("plain/text", $this->emt, $filename);
    }
}
