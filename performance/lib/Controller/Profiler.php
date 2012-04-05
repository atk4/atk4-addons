<?php
namespace performance;

class Controller_Profiler extends \AbstractController {

    public $timetable=array();
    public $curfunc=array();
    public $offset=0;
    public $profstart=null;
    public $calls=0;

    function init(){
        parent::init();
        $this->api->pr=$this;

        $this->profstart=time()+microtime();
        $this->start('everything else');
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
        else $this->getOffset();
        array_unshift($this->curfunc,array($location,$this->getFlatStack()));
        $this->timetable[$location]['calls']++;
        $this->logTime('Profiling');
    }
    function stop($limit=null,$ignore_fx=false){
        $this->calls++;
        if(!$this->curfunc){
            throw $this->exception('Stopping but wasn\'t started');
        }
        list($location,$stack)=array_shift($this->curfunc);
        $this->logTime($location,$limit);

        $newstack=$this->getFlatStack();
        if($stack!=$newstack && !$ignore_fx)throw $this->exception('Trace start and stop are in different functions. use stop(null,true); if that is desired')
            ->addMoreInfo('started_at',$stack)
            ->addMoreInfo('stopped_at',$newstack)
            ->addMoreInfo('location',$location)
            ;
        $this->logTime('Profiling');
    }
    function destroy(){
        $this->api->pr=new \Dummy();
        return parent::destroy();
    }
    function dump(){
        if($this->curfunc)var_Dump($this->curfunc);

        
        echo "Profiling period: ".($this->profstart-$this->api->start_time).' .. '.
            (time()+microtime()-$this->api->start_time).'<br/>';
        echo "Profiled (ms): <b>".round(1000*(time()+microtime()-$this->profstart
            -$this->timetable['Profiling']['total']
        ),2).'</b><br/>';

        uasort($this->timetable,function($l,$r){
            $l=$l['total'];///$l['calls'];
            $r=$r['total'];///$r['calls'];
            if($l>$r)return -1;
            if($l<$r)return 1;
        });

        $x=0;
        foreach($this->timetable as $activity=>$detail){
            if($activity=='Profiling'){
                $activity='<font color="gray">'.$activity.'</font>';
                $detail['calls']=$this->calls*2;
            }
            $x+=$detail['total'];
            echo '+'.number_format($detail['total']/*/$detail['calls']*/*1000,2).' '.$activity.' x '.$detail['calls'].'<br/>';
        }
    }
    function __destruct(){
        $this->stop(null,true);
        $this->dump();
    }
}
