<?php
namespace google;

class View_Map extends \View {
	public $height=400;
    public $center = array('lat'=>-34.397, 'lon'=>150.644);
    public $zoom=10;
    public $api_js_url = null;
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
    function fitZoom($bound_coord){
        $this->js(true)->gm()->fitZoom($bound_coord);
        return $this;
    }
    function setMarker($args=null,$trigger=true){
        $this->js($trigger)->gm()->marker($args);
        return $this;
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
    function _renderMapJs($trigger=true){
               $this->js($trigger)->_load('atk_google_map')->gm()
                       ->start($this->center['lat'],$this->center['lon'],$this->zoom);
   	}

    //
    function renderMap($trigger=true, $for_form=false){
        $points = $this->calculatePoints();
        $center = $this->findCenter($points);
        $bound_coord = $this->findBounds($points);
        if (count($center))$this->setCenter($center['lat'],$center['lon']);
        $this->_renderMapJs($trigger);
        $this->setMarkers($points);
        $this->fitZoom($bound_coord);
    }
    function setMarkers($points){
        foreach($points as $point) {//var_dump($point);echo '<hr>';
            $this->setMarker(
                $point['args']
            );
        }
    }
    function calculatePoints(){
        $points = array();
        if ($this->model) {
            foreach($this->model as $point) {
                $points[] = array(
                    'lat'  =>$point['f_lat'],
                    'lon'  =>$point['f_lon'],
                    'name' =>$point['name'],
                    'args' =>$point
                );
            }
    }
        return $points;
    }
    function findCenter($points){
        $count = 0;
        foreach($points as $point) {
            $lat[] = $point['lat'];
            $lon[] = $point['lon'];
            $count++;
        }
        if ($count) {
            return array(
                'lat' => array_sum($lat) / $count,
                'lon' => array_sum($lon) / $count,
            );
        }
        return false;
    }
    function findBounds($points){
        $count = 0;
        foreach($points as $point) {
            $lat[] = $point['lat'];
            $lon[] = $point['lon'];
            $count++;
        }
        if ($count >= 2) {
            return array(
                'NorthEastLat' => min($lat),
                'NorthEastLng' => min($lon),
                'SouthWestLat' => max($lat),
                'SouthWestLng' => max($lon),
            );
        }
        return false;
    }
}



      /*
      http://loco.ru/materials/137-google-maps-masshtabiruem-kartu-delaem-po-centru
      http://stackoverflow.com/questions/2437683/google-maps-api-v3-can-i-setzoom-after-fitbounds
              point = [
                [title,lat,lng]
              ];
              this.fitZoom([point]);
       */


    // set zoom to show all points
//    var latlngbounds = new google.maps.LatLngBounds();
//    for ( var i=0; i<points.length; i++ ){
//        var myLatLng = new google.maps.LatLng(locations[i][1], locations[i][2]);
//         latlngbounds.extend(myLatLng);
//    }
//    this.map.setCenter( latlngbounds.getCenter(), this.map.fitBounds(latlngbounds));
//      var bounds = new google.maps.LatLngBounds();
//      this.map.fitBounds(bounds);
//      var listener = google.maps.event.addListener(this.map, "idle", function() {
//        if (this.map.getZoom() > 16) this.map.setZoom(16);
//        google.maps.event.removeListener(listener);
//      });
