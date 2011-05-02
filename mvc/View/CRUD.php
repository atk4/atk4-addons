<?php
class View_CRUD extends View {
    public $form=null;
    public $grid=null;

    public $grid_class='MVCGrid';
    public $form_class='MVCForm';

	public $allow_add=true;
	public $allow_edit=true;
	public $allow_del=true;
    
    public $frame_options=null;
    function init(){
        parent::init();

        if(isset($_GET[$this->name]) && ($this->allow_edit||$this->allow_add)){
            $this->api->stickyGET($this->name);

            $this->form=$this->add($this->form_class);
            $_GET['cut_object']=$this->name;

            return;
        }

        $this->grid=$this->add($this->grid_class);
        $this->js('reload',$this->grid->js()->reload());
		if($this->allow_add){
			$this->add_button = $this->grid->addButton('Add');
			$this->add_button->js('click')->univ()
				->frameURL('New',$this->api->getDestinationURL(null,array($this->name=>'new')),$this->frame_options);
		}
    }
    function setModel($a,$b=null){
        if($this->form){
            $m=$this->form->setModel($a,$b);

            if(($id=$_GET[$this->name])!='new' && $this->allow_edit){
				if(!$this->allow_edit)throw $this->exception('Editing not allowed');
                $m->loadData($id);
            }
			if(!$m->isInstanceLoaded() && !$this->allow_add)throw $this->exception('Adding not allowed');

            if($this->form->isSubmitted()){
                $this->form->update();
                $this->form->js(null,$this->js()->trigger('reload'))->univ()->closeDialog()->execute();
            }

            return $m;
        }
        $m=$this->grid->setModel($a,$b);
        $this->grid->addColumn('button','edit');
        $this->grid->addColumn('delete','delete');
        if($id=@$_GET[$this->grid->name.'_edit']){
            $this->js()->univ()->frameURL('New',$this->api->getDestinationURL(null,array($this->name=>$id)))->execute();
        }
        return $m;

    }
}
