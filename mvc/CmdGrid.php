<?php
/*
 * Created on 20.05.2007 by *Camper* (camper@adevel.com)
 */
class CmdGrid extends SelectGrid{

	private $client=null;
	private $subtotals=null;
	protected $current_row_index=-1;

	protected $thparam=array();
	public function setTHParam($field_name,$value){
		if(!isset($this->thparam[$field_name])){
			$this->thparam[$field_name]=$value;
		}else{
			$this->thparam[$field_name].=' '.$value;
		}
		return $this;
	}

	function init(){
		parent::init();
		// calculating current_row_index
		$this->current_row_index=0;
	}
	/**
	 * Same as in Grid, but sets a color for the column title
	 */
	function addColumn($type,$name=null,$descr=null,$color=null){
		parent::addColumn($type,$name,$descr);
		$this->columns[$this->last_column]['color']=$color;
		return $this;
	}
	/**
	 * Replicates Grid::precacheTemplate() as title coloring must take effect
	 * Changed lines are commented with <C>
	 */
	function precacheTemplate($full=true){
		// pre-cache our template for row
		// $full=false used for certain row init
		$row = $this->row_t;
		$col = $row->cloneRegion('col');

		$row->set('row_id','<?$id?>');
		$row->trySet('odd_even','<?$odd_even?>');
		$row->del('cols');

		if($full){
			$header = $this->template->cloneRegion('header');
			$header_col = $header->cloneRegion('col');
			$header_sort = $header_col->cloneRegion('sort');

			if($t_row = $this->totals_t){
				$t_col = $t_row->cloneRegion('col');
				$t_row->del('cols');
			}

			$header->del('cols');
		}

		if(count($this->columns)>0){
			foreach($this->columns as $name=>$column){
				$col->del('content');
				$col->set('content','<?$'.$name.'?>');

				if(isset($t_row)){
					$t_col->del('content');
					$t_col->set('content','<?$'.$name.'?>');
					$t_col->trySet('tdparam','<?tdparam_'.$name.'?>nowrap<?/?>');
					$t_row->append('cols',$t_col->render());
				}

				// some types needs control over the td

				$col->set('tdparam','<?tdparam_'.$name.'?>nowrap<?/?>');

				$row->append('cols',$col->render());

				if($full){
					$header_col->trySet('descr',$column['descr']);
					$header_col->trySet('color',$column['color']);
					if(isset($column['sortable'])){
						$s=$column['sortable'];
						// calculate sortlink
						$l = $this->api->getDestinationURL(null,array($this->name.'_sort'=>$s[1]));

						$header_sort->trySet('order',$column['sortable'][0]);
						$sicons=array('vertical','top','bottom');
						$header_sort->trySet('sorticon',$sicons[$column['sortable'][0]]);
						$header_sort->set('sortlink',$l);
						$header_col->set('sort',$header_sort->render());
					}else{
						$header_col->del('sort');
						$header_col->tryDel('sort_del');
					}
					if($this->thparam[$name]){
						$header_col->trySet('thparam',$this->thparam[$name]);
					}else{
						$header_col->tryDel('thparam');
					}
					$header->append('cols',$header_col->render());
				}
			}
		}
		$this->row_t = $this->api->add('SMlite');
		$this->row_t->loadTemplateFromString($row->render());

		if(isset($t_row)){
			$this->totals_t = $this->api->add('SMlite');
			$this->totals_t->loadTemplateFromString($t_row->render());
		}

		if($full)$this->template->set('header',$header->render());
		// for certain row: required data is in $this->row_t
		//var_dump(htmlspecialchars($this->row_t->tmp_template));

	}
	/**
	 * Adds paginator to the grid
	 * @param $ipp row count per page
	 * @param $name if set, paginator will get the name specified. Useful for saving
	 * 		different page numbers for different filtering conditions
	 */
	function addPaginator($ipp=25,$name=null){
		// adding ajax paginator
		$this->paginator=$this->add('Paginator', $name, 'paginator', array('paginator', 'ajax_paginator'));
		$this->paginator->region($this->name);
		$this->paginator->cutObject($this->name);
		$this->paginator->ipp($ipp);
		$this->current_row_index=$this->paginator->skip-1;
		return $this;
	}

	function format_htmlspecialchars($field){
		$value=$this->current_row[$field];
		$value=htmlspecialchars($value,ENT_QUOTES);
		$this->current_row[$field]=$value;
	}
	function format_mailto($field){
		$this->current_row[$field]='<a href="mailto:'.$this->current_row[$field].'">'.
			$this->current_row[$field].'</a>';
	}
	function format_nullmoney($field){
		$value=$this->current_row[$field];
		$this->format_money($field);
		$this->format_right($field);
		if(!$value)$this->current_row[$field]='';
	}
	function format_totals_nullmoney($field){
		$value=$this->current_row[$field];
		$this->format_totals_money($field);
		$this->format_right($field);
		if(!$value)$this->current_row[$field]='-';
	}
	/**
	 * Highlights column if $is_current==true
	 */
	function format_current($field,$is_current=false){
		if($is_current){
			$this->tdparam[$this->getCurrentIndex()][$field]['style']['background-color']='#ffefd3';
		}
	}
	function format_subtotal($field){
		$this->current_row[$field]='<b>'.$this->current_row[$field].'</b>';
	}
	/**
	 * Specifies rows which should be formatted as subtotals.
	 *
	 * @param $subtotals - array with IDs of rows
	 *
	 * See also formatSubTotals()
	 */
	function setSubTotals($subtotals){
		$this->subtotals=$subtotals;
		return $this;
	}
	/**
	 * Formats current row as subtotals. Here we just setting the text bold
	 */
	function formatSubTotals(){
		foreach($this->current_row as $key=>$val){
			$this->current_row[$key]='<b>'.$val.'</b>';
		}
	}
	function formatRow($row=null){
		parent::formatRow($row);
		if(!$this->subtotals)return;
		// formatting subtotals
		$is_subtotals=in_array($this->current_row['id'],$this->subtotals);
		if($is_subtotals)$this->formatSubTotals();
	}
	function format_left($field){
		$this->tdparam[$this->getCurrentIndex()][$field]['align']='left';
	}
	function format_right($field){
		$this->tdparam[$this->getCurrentIndex()][$field]['align']='right';
	}
	function format_center($field){
		$this->tdparam[$this->getCurrentIndex()][$field]['align']='center';
	}
	function format_selectbox($field,$onclick=''){
		/*
		 * Formats the field as checkbox. Field should be 'Y'/'N' or 1/0 to properly reflect its state
		 * Ajax script, if needed, could be set in $onclick
		 */
		$value=$this->current_row[$field];
		$value=($value=='Y'||$value==1)?'checked ':'';
		$this->current_row[$field]='<input type="checkbox" '.$value.' onclick="'.$onclick.'" />';
		$this->tdparam[$this->getCurrentIndex()][$field]['id']=$this->name.'_'.$field.'_'.$this->current_row['id'];
	}
	function format_bold($field){
		$this->tdparam[$this->getCurrentIndex()][$field]['style']['font-weight']='bold';
	}
	function format_timeago($field){
		/*
		 * This format is used in many grids throughout the project.
		 * Check before you change this format
		 */
		if(is_null($this->current_row[$field]))$this->current_row[$field]='never';
		else{
			$value=time()-strtotime($this->current_row[$field]);
			// outputs only the greatest measure unit
			$value=format_time_str($value);
			list($v,$m)=explode(' ',$value);
			$this->current_row[$field]="$v $m";
		}
	}
	function format_timeleft($field){
		$value=strtotime($this->current_row[$field])-time();
		$this->current_row[$field]=format_time_str($value);
	}
	function format_enum($field){
		$this->current_row[$field]=$this->current_row_index+1;
		$this->format_right($field);
	}
	/**
	 * Only one expander allowed, others are closed prior to expand
	 */
	function format_expandersingle($field, $idfield='id'){
		$this->format_expander($field,$idfield);
		$this->setTDParam($field,'onclick','expander_flip_single(\''.$this->name.'\','.
					$this->current_row[$idfield].',\''.
					$field.'\',\''.
					$this->api->getDestinationURL($this->api->page.'_'.$field,array('expander'=>$field,
						'cut_object'=>$this->api->page.'_'.$field, 'expanded'=>$this->name)).'&id=\')'
		);
	}
	#######################################################################
	###### 					Overridden methods 						 ######
	#######################################################################
	function fetchRow(){
		$this->current_row_index++;
		return parent::fetchRow();
		/*
		if(is_array($this->data)){
			$result=(bool)($this->current_row=array_shift($this->data));
			//$this->current_row_index=array_search($this->current_row,$this->data);
			return $result;
		}
		if(!isset($this->dq))throw new BaseException($this->name.": dq must be set here");
		return (bool)($this->current_row=$this->dq->do_fetchHash());
		*/
	}

}
