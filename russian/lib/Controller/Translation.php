<?php
namespace russian;
class Controller_Translation extends \AbstractController {
    function init(){
        parent::init();
        $this->api->addHook('localizeString',$this);
    }
    function getFlatStack(){
        $bt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $fx=array();
        foreach($bt as $row){
            if($row['class']=='performance\Controller_Profiler')continue;
            $fx[]=$row['class'].'::'.$row['function'].'()';
        }
        return join('  »  ',array_reverse($fx));
    }
    function localizeString($f,$s){
        switch($s){
            // translations
            case'':return '';
            case'test':return 'тест';
            case'params':return 'парамтры';
            case'Database Connection Failed':return 'Oshibka podkluchenija k baze dannih';
            case'Configuration parameter is missing in config.php':return 'Отсутствует строка конфигурации в config.php';
            default:
                $this->api->logger->logLine($s."\n",null,'notrans');
                return $s;
        }
    }
}
