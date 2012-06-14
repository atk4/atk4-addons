<?php
namespace jqgrid;
class jqGrid extends \View {
	public $default_controller='jqgrid/jqGrid';
	public $colNames=array();
	public $colModel=array();
	public $curColumnn=0;
	public $options=array(
		'viewrecords'=>true,
		'prmNames'=>array('page'=>'grid_page')
		);
	public $source=null;

	function init(){
		parent::init();

		$this->loadIncludes();
	}
	function loadIncludes(){
		$l=$this->api->locate('addons','jqgrid','location');
        $this->api->pathfinder->addLocation($this->api->locate('addons','jqgrid'),array(
            'js'=>'js'
        ))->setParent($l);

		$this->api->jui->addStaticStylesheet('jqgrid/ui.jqgrid','.css','js');
		$this->api->jui->addStaticInclude('jqgrid/grid.locale-en');
		$this->api->jui->addStaticInclude('jqgrid/jquery.jqGrid.min');

		$this->setElement('table');
	}

	function render(){

		$this->setOptions(array(
			'colNames'=>$this->colNames,
			'colModel'=>$this->colModel
			));

		if($_GET[$this->name]=='json'){

			$data=array();
			foreach($this->source as $row){
				$cell=array();
				foreach($this->colModel as $m){
					$cell[]=$row[$m['name']];
				}
				$data[]=array('id'=>$row['id'],'cell'=>$cell);
			}

			$data=array(
				'page'=>1,
				'total'=>1,
				'rows'=>$data
				);
			echo json_encode($data);
			exit;
		}

		$this->js(true)->jqGrid($this->options);
		parent::render();
	}

	function setSource($source){
		$this->setOptions(array(
			'datatype'=>'json',
			'url'=>$this->api->url(null,array($this->name=>'json'))
			));

		$this->source=$source;

	}

	function setOptions(array $options){
		$this->options=array_merge($this->options,$options);
	}

	/**
	 * same as grid->addColumn for compatibility
	 */
	function addColumn($type,$name,$label=null,$options=array()){
		if(is_array($label)){ $options=$label; $label=null; }
		$this->colNames[]=$label?:ucfirst(str_replace('_',' ',$name));
		$this->colModel[]=array_merge(array(
			'name'=>$name,
			'index'=>$name,
			'sort'=>false
			),$options);

		$this->curColumn=count($this->colModel)-1;
		return $this;
	}
	function makeSortable(){
		$this->colModel[$this->curColumn]['sortable']=true;
		return $this;
	}
}
