<?php
namespace misc;
/*

    Remember all these times, when you had to create new page just to show contents of a pop-up?

    Not anymore. With this simple add-on you can seamlessly create a PHP call-back to construct
    your pop-up content. This method is already utilized by many views (such as CRUD), but it is
    much simpler and nicer with the syntax-sugar of this addon. Here is how it work:

    $b=$this->add('Button');
    $b->add('misc/PageInFrame')->set('Title Here',function($page){
        $page->add('LoremIpsum');
    });

    That's all.

 */
class PageInFrame extends \AbstractController {
    public $type='dialogURL';


    function getURL(){
        $this->api->url(null,array($this->name=>'click'));
    }

    function bindEvent($event='click',$title){
        $this->owner->js($event)->univ()->$t($title,$this->getURL());
    }

    function set($method){
        $t=$this->type;
        $self=$this;
        if($_GET[$this->name]=='click')$this->api->addHook('post-init',function()use($method,$self){
            $page=$self->api->add('Page');
            $self->api->cut($page);
            call_user_func($method,$page);
        });
        return $this;
    }

    /* Use this method if you are calling this from a Lister (which 
        * needs to be an owner) */
    function listerMode(){

    }

    /* Use this method if you are calling this from a Lister (which 
        * needs to be an owner) */
    function gridMode($name,$title=null,$buttontext=null,$grid=null){
        if(!$grid)$grid=$this->owner;

        $grid->addColumn('template',$name,$title)->setTemplate('<button type="button" class="pb_'.$name.'">'.
            htmlspecialchars($buttontext?:$title?:ucwords(str_replace('_',' ',$name))).'</button>');

        $grid->columns[$name]['thparam'].=' style="width: 40px; text-align: center"';

        $grid->js(true)->_selector('#'.$grid->name.' .pb_'.$name)->button();
        $t=$this->type;
        $grid->js('click',$this->bindEvent)->_selector('#'.$grid->name.' .pb_'.$name)
            ->univ()->$t($title,array($this->getURL(),
                'id'=>$this->js()->_selectorThis()->closest('tr')->attr('data-id')
            ));
    }

    function tabsMode($tab_name){
    }
}
