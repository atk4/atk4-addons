<?php
namespace hierarchy;

// This form field is similar to drop-down but will work with models which are referencing themselves through
// parent_id or similar field. You need to define both hasOne and hasMany references in your field, such as this:
//
// $this->hasOne('Category','parent_id')->display(array('form'=>'misc/drilldown'));   
//                                          // link from child to parent
//
// $this->hasMany('Category','parent_id');  // link from parent to children
//
// The "display" is not mandatory, but it will display the drilldown whenever Category is edited.
//
// The drop-down will be decorated with $indent_phrase which will be added for each sub-level. To change
// decoration you can either extend drilldown class or change the property by adressing field after form
// has been populated $form->getField('parent_id')->indent_phrase='  ';
//
// Authors: Bob Siefkes, Romans Malinovskis

class Form_Field_drilldown extends \Form_Field_Dropdown {
    public $child_ref;
    public $parent_ref;
    public $indent_phrase='---';
    public $empty_text='..';

    function getValueList(){

        if(!$this->model)throw $this->exception('Drilldown can only be used with specified moedl. Consult documentation and examples.');
        if ($this->empty_text){
            $res=array(''=>$this->empty_text);
        } else {
            $res = array();
        }

        // Determine the parent_id field

        $this->child_ref=preg_replace('/^Model_/', '', get_class($this->model)); // remove "Model_" from class

        if(!$this->model->hasElement($this->child_ref))throw $this->exception("Unable to determine how to reference child elements of a model. Did you declare hasMany() ?")
            ->addMoreInfo('model',get_class($this->model))
            ->addMoreInfo('attempted_child_ref',$this->child_ref)
            ;

        $this->parent_ref=$this->model->getElement($this->child_ref)->their_field;
        if(!$this->parent_ref)throw $this->exception("Unable to determine how to reference parent elements of a model. Did you declare hasOne() ?")
            ->addMoreInfo('model',get_class($this->model))
            ->addMoreInfo('attempted_parent_ref',$this->parent_ref)
            ;

        $m=$this->model->newInstance()->addCondition($this->parent_ref,'is',null);

        return $res+$this->drill($m);
    }

    /** Recursively return array of sub-elements. Will produce as many queries as there are nodes */
    function drill($m,$prefix='') {
        $r=array();

        $m->setActualFields(array($this->model->title_field));    // only query title field

        foreach($m as $row) {
            $r[$m->id]=$prefix.$row[$this->model->getTitleField()];
            $r=$r+$this->drill($m->ref($this->child_ref),$prefix.$this->indent_phrase);
        }

        return $r;
    }
}

