<?php
namespace performance;

class Controller_Profiler extends \AbstractController {

    public $timetable=array();
    public $curfunc=array();
    public $offset=0;

    function init(){
        parent::init();
        $this->api->pr=$this;
    }

    /** Returns microseconds since last call */
    function getOffset(){
        $t=time()+microtime()-$this->api->start_time;
        $res=$t-$this->offset;
        $this->offset=$t;
        return $res;
    }
    function logTime($activity,$limit=true){
        $this->timetable[$activity]['total']+=$this->getOffset();
        if(isset($limit) && $this->timetable[$activity]['total']>$limit)throw $this->exception('time limit');
    }

    function next($location=null,$ignore_fx=false){
        $this->stop(null,$ignore_fx);
        $this->start($location);
    }
    function getFlatStack(){
        return '';
        $bt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $fx=array();
        foreach($bt as $row){
            if($row['class']=='performance\Controller_Profiler')continue;
            $fx[]=$row['class'].'::'.$row['function'].'()';
        }
        return join('  »  ',array_reverse($fx));
    }
    function start($location=null){
        if($this->curfunc)$this->logTime($this->curfunc[0][0]);
        array_unshift($this->curfunc,array($location,$this->getFlatStack()));
        $this->timetable[$location]['calls']++;
    }
    function stop($limit=null,$ignore_fx=false){
        if(!$this->curfunc){
            throw $this->exception('Stopping but wasn\'t started');
        }
        list($location,$stack)=array_shift($this->curfunc);
        $newstack=$this->getFlatStack();
        if($stack!=$newstack && !$ignore_fx)throw $this->exception('Trace start and stop are in different functions. use stop(null,true); if that is desired')
            ->addMoreInfo('started_at',$stack)
            ->addMoreInfo('stopped_at',$newstack)
            ->addMoreInfo('location',$location)
            ;
        $this->logTime($location,$limit);
    }
    function dump(){
        if($this->curfunc)var_Dump($this->curfunc);

        
        echo "Total: ".(time()+microtime()-$this->api->start_time).'<br/>';

        uasort($this->timetable,function($l,$r){
            $l=$l['total'];///$l['calls'];
            $r=$r['total'];///$r['calls'];
            if($l>$r)return -1;
            if($l<$r)return 1;
        });

        foreach($this->timetable as $activity=>$detail){
            echo '+'.number_format($detail['total']/*/$detail['calls']*/*1000,2).' '.$activity.' x '.$detail['calls'].'<br/>';
        }
    }
    function __destruct(){
        $this->dump();
    }
}
