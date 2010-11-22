// This extends univ() chain to support Google Map functions 

(function($){

$.each({

showMapForEdit: function(){
	if (!$.univ.f_lat || !$.univ.f_lng){
		alert('Please define Lat, Lng form foeld');
		return;
	}

	var map = new GMap2(this.jquery[0]);

	if($($.univ.f_lat).val().trim() == '' || $($.univ.f_lng).val().trim() == ''){
		var latlng=new GLatLng(53.27958469355977, -6.1818695068359375);
	}
	else {
		var latlng=new GLatLng($($.univ.f_lat).val(), $($.univ.f_lng).val());
	}

	if ($.univ.f_zoom && parseInt($($.univ.f_zoom).val()) > 0) {
		zoom =  parseInt($($.univ.f_zoom).val());
	}
	else {
		zoom = 10;
		$($.univ.f_zoom).val(zoom);
	}
	map.setCenter(latlng, parseInt(zoom));
	map.addControl(new GSmallMapControl());
    //_m = new GMarker(latlng, {draggable: true});
    //_ov = map.addOverlay(_m);
	$.univ.addMarker(latlng, map);

	var myEventListener = GEvent.bind(map, "click", this, function(overlay, latlng) {
		map.clearOverlays();
		$.univ.addMarker(latlng, map);
	});

	$.univ.addMapZoom(map);
},

addMapZoom : function(map){
	GEvent.bind(map, "zoomend", this, function() {
		zoom = map.getZoom();
		if($.univ.f_zoom) $($.univ.f_zoom).val(zoom);
	}); 
},

renderMap: function(f_lat,f_lng, f_zoom){
	if (!f_lat || !f_lng){
		alert('Please define Lat, Lng');
		return;
	}
	var map = new GMap2(this.jquery[0]);

	var latlng=new GLatLng(f_lat, f_lng);
   	map.setCenter(latlng, f_zoom ? parseInt(f_zoom) : 10);
	map.addControl(new GSmallMapControl());
    _m = new GMarker(latlng);
    _ov = map.addOverlay(_m);
},

getMarkerForLocation: function(){
	if (!$.univ.f_country || !$.univ.f_city || !$.univ.f_address){
		alert('Please define Coutry, City, Address');
		return;
	}

	_o_country = $($.univ.f_country + ' option:selected');
	_o_city = $($.univ.f_city);
	_o_address = $($.univ.f_address);

	_a = '';
	if(_o_address.val()){
		_a += _o_address.val();
	}
	if(_o_city.val()){
		_a += ', ' + _o_city.val();
	}
	if(_o_country.val()){
		_a += ', ' + _o_country.text();
	}

	var map = new GMap2(this.jquery[0]);
	map.addControl(new GSmallMapControl());

	geocoder = new GClientGeocoder();
	if (geocoder) {
		geocoder.getLatLng(
		_a,
			function(point) {
			if (!point) {
				alert(_a + " not found");
			} else {
				z = $($.univ.f_zoom).val();
				map.setCenter(point, parseInt(z));
				map.clearOverlays();
				$.univ.addMarker(point, map).openInfoWindowHtml(_a);
			}
		});
	}
	$.univ.addMapZoom(map);
},
addMarker : function (latlng, map){
	_m = new GMarker(latlng, {draggable: true});
	_ov = map.addOverlay(_m);

	var marker_moved=function() {
		$.univ.updateBindFieldValues(_m.getPoint().lat(), _m.getPoint().lng());
	};

	GEvent.addListener(_m, "dragend", marker_moved);

	lat = latlng.lat();
	lng = latlng.lng();
	$.univ.updateBindFieldValues(lat, lng);

	return(_m);
},
updateBindFieldValues : function(lat, lng){ 
	$($.univ.f_lat).val(lat);
	$($.univ.f_lng).val(lng);
},
bindLatLngZoom : function (f_lat, f_lng, f_zoom){
	$.univ.f_lat = f_lat;
	$.univ.f_lng = f_lng;
	$.univ.f_zoom = f_zoom;
},
bindLocationFields : function (country, city, address){
	$.univ.f_country = country;
	$.univ.f_city = city;
	$.univ.f_address = address;
},
bindRefreshAfterChange : function (_name){
	$(_name).change(function(){
		$.univ().getMarkerForLocation();
	});
},

},$.univ._import);

})($);
