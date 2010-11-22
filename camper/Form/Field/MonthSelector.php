<?php
/**
 * Created on 31.12.2007 by *Camper* (camper@adevel.com)
 */
class Form_Field_MonthSelector extends Form_Field{

	protected $required = false;

	protected $year_from;
	protected $year_to;
	protected $years = array();
	protected $months = array('1'=>'Jan', '2'=>'Feb', '3'=>'Mar', '4'=>'Apr',
			'5'=>'May', '6'=>'Jun', '7'=>'Jul', '8'=>'Aug',
			'9'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dec');

	protected $c_year;
	protected $c_month;
	protected $date_order=array('m','y');

	function init(){
		parent::init();

		$cur_year = date('Y');
		$this->setYearRange($cur_year-1, $cur_year+5);
		$this->c_year = $cur_year;
		$this->c_month= date('m');
	}
	function clearFieldValue(){
		$this->set(null);
	}
	function set($value){
		// value can be passed as YYYY-MM or as full date (YYYY-MM-DD)
		if(strtotime($value)===false)$value.='-01';
		$tm = strtotime($value);
		$yr = date('Y', $tm);
		if($yr > $this->year_to)$yr = $this->year_to;
		elseif($yr < $this->year_from)$yr = $this->year_from;

		$this->c_year = $yr;
		$this->c_month= date('m', $tm);

		return parent::set($value);
	}

	function setRequired($is_required){
		$this->required = $is_required === true;
	}

	function setYearRange($from=null, $to=null){
		if(!is_numeric($from))
			$from = null;
		if(!is_numeric($to))
			$to = null;

		$cur_year = date('Y');
		if(($from === null) && ($to === null))
			return array($cur_year => $cur_year);

		if(($from === null) && ($to !== null)){
			$from = ($to < $cur_year)?$to:$cur_year;
		}
		elseif(($from !== null) && ($to === null)){
			$to = ($from > $cur_year)?$from:$cur_year;
		}
		elseif($from > $to ){
			$temp = $to;
			$to = $from;
			$from=$temp;
		}

		$res = array();
		for($i=$from; $i<=$to; $i++)
			$res[$i] = $i;

		// correct the c_year value upon range change
		if($this->c_year > $to)
			$this->c_year = $to;
		if($this->c_year < $from)
			$this->c_year = $from;

		$this->year_from = $from;
		$this->year_to = $to;
		$this->years = $res;
		return $this;
	}

	function loadPOST(){
		if(empty($_POST))
			return;

		if(isset($_POST[$this->name.'_year']))
			$this->c_year = $_POST[$this->name.'_year'];
		if(isset($_POST[$this->name.'_month']))
			$this->c_month = $_POST[$this->name.'_month'];

		$this->set($this->c_year.'-'.sprintf("%02s",$this->c_month).'-01');
	}

	function validate(){
		if(false === strtotime($this->value.'-01'))
			$this->owner->errors[$this->short_name]="Invalid date specified!";

		return parent::validate();
	}

	function setOrder($order){
		//pass an array with 'd','m','y' as members to set an order
		$this->date_order=$order;
		return $this;
	}

	function getInput($attr=array()){
		$output=$this->getTag('span', array('style'=>'white-space: nowrap;'));
		$onChange=($this->onchange)?$this->onchange->getString():'';

		$xtraattrs = array();

		// month control
		$m=$this->getTag('select',array_merge(array(
						'id'=>$this->name.'_month',
						'name'=>$this->name.'_month',
						'onchange'=>$onChange
						), $attr, $this->attr, $xtraattrs)
				);
		foreach($this->months as $value=>$descr){
			$m.=
				$this->getTag('option',array(
						'value'=>$value,
						'selected'=>$value == $this->c_month
					))
				.htmlspecialchars($descr)
				.$this->getTag('/option');
		}
		$m.=$this->getTag('/select').'&nbsp;';

		// year control
		$y=$this->getTag('select',array_merge(array(
						'id'=>$this->name.'_year',
						'name'=>$this->name.'_year',
						'onchange'=>$onChange
						), $attr, $this->attr, $xtraattrs)
				);
		foreach($this->years as $value=>$descr){
			$y.=
				$this->getTag('option',array(
						'value'=>$value,
						'selected'=>$value == $this->c_year
					))
				.htmlspecialchars($descr)
				.$this->getTag('/option');
		}
		$y.=$this->getTag('/select');

		$o1=$this->date_order[0];$o2=$this->date_order[1];
		$output.=$$o1.$$o2;
		$output.=$this->getTag('/span');
		$output.='<!-- '.(is_null($this->value)?'null':$this->value).' -->';

		return $output;
	}

	function get(){
		if(parent::get()=='0000-00')return null;
		return parent::get();
	}
}
