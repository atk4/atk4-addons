<?php
namespace google;

class View_Map extends \View {
	public $height=400;
    public $center = array('lat'=>-34.397, 'lon'=>150.644);
    public $zoom=5;
    private $api_js_url = null;
	function init(){
		parent::init();
        $this->api_js_url =  'http://maps.googleapis.com/maps/api/js?key='.$this->api->getConfig('map/google/key','').'&sensor=true';
        $l=$this->api->locate('addons',__NAMESPACE__,'location');
        $this->api->pathfinder->addLocation($this->api->locate('addons',__NAMESPACE__),array(
            'template'=>'templates',
            'js'=>'js'
        ))->setParent($l);
		$this->set('Loading Google Map...');
        $this->addApiJs();
	}
    function addApiJs() {
      	$this->api->jui->addStaticInclude($this->api_js_url);
        return $this;
    }
    function setCenter($latitude,$longitude){
        $this->center = array('lat'=>$latitude,'lon'=>$longitude);
        return $this;
    }
    function setZoom($zoom){
        $this->zoom = $zoom;
        return $this;
    }
    function setMarker($latitude,$longitude,$title){
        $this->js(true)->gm()->marker($latitude,$longitude,$title);
    }
	function setWidthHeight(){
		$this->addStyle(array('height'=>$this->height.'px'));
	}
	function render(){
		$this->setWidthHeight();
		parent::render();
	}
	function showMapForEdit(){
		$this->js(true)->univ()->showMapForEdit();
	}
	function renderMap($trigger=true){
            $this->js($trigger)->_load('atk_google_map')->gm()
                    ->start($this->center['lat'],$this->center['lon'],$this->zoom);
	}
	function getMarkerForLocation($country, $city, $addess){
		$this->js(true)->univ()->getMarkerForLocation($country,$city,$addess);
	}
	function bindLatLngZoom($lat, $lng,$zoom=null){
		$this->js(true)->univ()->bindLatLngZoom($lat, $lng, $zoom);
	}
	function bindLocationFields($name, $lat, $lng){
		$this->js(true)->gm()->bindLocationFields($name, $lat, $lng);
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
