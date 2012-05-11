<?php

// use with setModel and make sure that the model does have a hasMany() reference to iself with the parent_id 
// in order to recursively indent.
// E.g. in model: $this->hasMany('Category','id_parent');
// on page: $f->addField('drilldown','category')->setModel('Model_Category');

class Form_Field_Drilldown extends Form_Field_Dropdown {
  public $drill_ref;
  public $indent_phrase='---';
    
  function getValueList(){
 
    if($this->model){
        $title=$this->model->getTitleField();
        $id=$this->model->id_field;
        if ($this->empty_text){
            $res=array(''=>$this->empty_text);
        } else {
            $res = array();
        }
        
      $this->drill_ref=preg_replace('/((^(model_)?)|(_))([a-z])/e', '\'$4\'.strtoupper(\'$5\')', $this->model->short_name); // model_some_thing -> Some_Thing
      
      if (!isset ($this->model->elements[$this->drill_ref]))
        throw $this->exception("No ref found, make sure to use hasMany() in model to itself for parent id")
            ->addMoreInfo('ref',$this->drill_ref);
      
      if(!$this->model->loaded()) $this->model->tryLoad(1);
      $res=$this->drill();
      return $this->value_list=$res;
		}

    if($this->empty_text && isset($this->value_list[''])){
        $this->value_list['']=$this->empty_text;
    }
		return $this->value_list;
	}
    

  function drill($prefix='') {
    $r=array();
    
    $r[$this->model->id]=$prefix.$this->model->get($this->model->getTitleField());
    $childs=$this->model->ref($this->drill_ref);
    foreach($childs as $child) {
      $this->model=$childs;
      foreach($this->drill($prefix.$this->indent_phrase) as $key=>$value) { // cannot do array_merge as merge will renumber
        $r[$key]=$value;
      }
    }
    
    return $r;
  }
 
}

