<?php
/*
   Add this to your API to enabe SEO.

   http://agiletoolkit.org/addons/seotags
   */
class Controller_Seo extends AbstractController {
	public $move_tags=array('seo_keywords','seo_description','seo_title'=>'page_title');
	public $copy_tags=array('page_title');

	function init(){
		parent::init();

		$this->api->versionRequirement('4.0.2');
		$this->api->addHook('post-init',array($this,'SeoTags'));
	}
	function SeoTags(){
		$this->api->template->trySet('_page',str_replace('/','_',$this->api->page));
		if($this->api->page_object){


			foreach($this->move_tags as $key=>$val){
				if(is_int($key))$key=$val;

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
