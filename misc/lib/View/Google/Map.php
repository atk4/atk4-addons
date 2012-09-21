<?php
namespace google;

class View_Map extends HtmlElement {
	public $width=640;
	public $height=480;
	function init(){
		parent::init();

		$this->set('Problem Loading Google Map');

		$url='http://maps.googleapis.com/maps/api/js?key='.
			$this->api->getConfig('map/google/key','')
		.'&sensor=true';

		$this->api->template->appendHTML('js_include',
			'<script type="text/javascript" src="'.$url.'"></script>'."\n");
		$this->js(true,' var map = new google.maps.Map(document.getElementById("'.$this->name.
			'"),{zoom: 8, center: new google.maps.LatLng(-34.397, 150.644), mapTypeId: google.maps.MapTypeId.ROADMAP});');
	}
	function setWidthHeight(){
		$this->addStyle(array('width'=>$this->width.'px','height'=>$this->height.'px'));
	}
	function render(){
		$this->setWidthHeight();
		parent::render();
	}
	function showMapForEdit(){
		$this->js(true)->univ()->showMapForEdit();
	}
	function renderMap($latitude,$longitude,$zoom=null){
		$this->js(true)->univ()->renderMap($latitude,$longitude,$zoom);
	}
	function getMarkerForLocation($country, $city, $addess){
		$this->js(true)->univ()->getMarkerForLocation($country,$city,$addess);
	}
	function bindLatLngZoom($lat, $lng,$zoom=null){
		$this->js(true)->univ()->bindLatLngZoom($lat, $lng, $zoom);
	}
	function bindLocationFields($country, $city, $addess){
		$this->js(true)->univ()->bindLocationFields($country, $city, $addess);
	}
	function bindRefreshAfterChange($name){
		if (is_array($name)){
			foreach ($name as $_name){
				$this->js(true)->univ()->bindRefreshAfterChange($_name);
			}
		}
		else {
			$this->js(true)->univ()->bindRefreshAfterChange($name);
		}
	}
}
