<?php
namespace auth;
/** Dummy sample addon which shows you popup with list of all users. Clicking on a user will
 * resume login process */
class Controller_DummyPopup extends \AbstractController{
    function init(){
        parent::init();
        $this->api->requires('atk','4.2');

        if(!$this->owner instanceof \Auth){
            throw $this->exception('Must be added into $api->auth');
        }

        $this->owner->addHook(array('updateForm'),$this);

        if($_GET[$this->name]=='popup'){

            if($_GET['user']){
                $this->owner->loginByID($_GET['user']);
                echo "<script>window.opener.document.location=window.opener.document.location;window.close()</script>";
            }

            $this->api->stickyGET($this->name);
            $l=$this->api->add('auth/UserLister');
            $l->setModel($this->owner->model);
            echo '<p>Pick a user from a list:</p>'.$l->getHTML();exit;
        }

    }
    function updateForm($auth){
        $b=$auth->form->addButton('Pick a User');
        $b->js('click')->univ()->newWindow($this->api->url(null,array($this->name=>'popup')),'auth','height=500,width=500');
    }
}

class UserLister extends \Lister {
    function formatRow(){
        $this->current_row_html['name']='<a href="'.$this->api->url(null,array('user'=>$this->current_id)).'">'.$this->current_row['email'].'</a>';
    }
}
