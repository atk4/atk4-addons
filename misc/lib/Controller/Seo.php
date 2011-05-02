<?php
/*
   Add this to your API to enabe SEO.

   http://agiletoolkit.org/addons/seotags
   */
class Controller_Seo extends AbstractController {
	public $move_tags=array('seo_keywords','seo_description','seo_title'=>'page_title');
	public $copy_tags=array('page_title');

    public $cur=array();
	function init(){
		parent::init();

		$this->api->versionRequirement('4.0.2');
        $this->cur['page_title']=$this->api->template->get('page_title');

        foreach(array_merge($this->move_tags,$this->copy_tags) as $key=>$val){
            if(is_int($key))$key=$val;
            if($this->api->template->is_set($val));
            $this->cur[$val]=$this->api->template->get($val);
        }

		$this->api->addHook('post-init',array($this,'SeoTags'));
	}
	function SeoTags(){
		$this->api->template->trySet('_page',str_replace('/','_',$this->api->page));
		if($this->api->page_object){


			foreach($this->move_tags as $key=>$val){
				if(is_int($key))$key=$val;
                // If it's changed already, skip it
                if($this->api->template->get($val)
                        !=$this->cur[$val])continue;

				// Move seo_keywords from page to shared.html
				if($this->api->page_object->template->is_set($key)){
					$this->api->template->trySet($val,
							$this->api->page_object->template->get($key));
					$this->api->page_object->template->del($key);
				}
			}

			foreach($this->copy_tags as $key=>$val){
				if(is_int($key))$key=$val;

				// Move seo_keywords from page to shared.html
				if($this->api->page_object->template->is_set($key)){
					$this->api->template->trySet($val,
							$this->api->page_object->template->get($key));
				}
			}
		}
	}

	function outputSitemap(){
		// TODO:
	}

}
