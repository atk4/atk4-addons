
(function($){

$.gm=function(){
	return $.gm;
}

$.fn.extend({gm:function(){
	var u=new $.gm;
	u.jquery=this;
	return u;
}});


$.gm._import=function(name,fn){
	$.gm[name]=function(){
		var ret=fn.apply($.gm,arguments);
		return ret?ret:$.gm;
	}
}

$.each({

  latlng: function(lat, lng){
  	return new google.maps.LatLng(lat,lng);
  },
  start: function(lat,lng,zoom,options){
  	def={
  		zoom: zoom,
  		center: new google.maps.LatLng(lat,lng),
  		mapTypeId: google.maps.MapTypeId.ROADMAP
  	};

  	this.map = new google.maps.Map(this.jquery[0],$.extend(def,options));
  },
  marker: function(lat,lng,title){
  	var marker = new google.maps.Marker({
      position: new google.maps.LatLng(lat,lng),
      animation: google.maps.Animation.DROP,
      map: this.map,
      title:title
  	});
      return marker;
  },
  markerNew: function(lat,lng,title){
      if( typeof $.gm.markerNew.marker != 'undefined' ) {
          if ( $.gm.markerNew.lat != lat && $.gm.markerNew.lng != lng && lat != null && lng != null ) {
              if ( typeof $.gm.markerNew.lat != 'undefined' && typeof $.gm.markerNew.lng != 'undefined' ) {
                      $.gm.markerNew.marker.setMap(null);
              }
              $.gm.markerNew.lat = lat;
              $.gm.markerNew.lng = lng;
              $.gm.markerNew.marker = $.gm.marker(lat,lng,title);
              $.gm.map.panTo(new google.maps.LatLng(lat,lng));

              $('#'+$.gm.f_name).val( title );
              $('#'+$.gm.f_lat).val( lat );
              $('#'+$.gm.f_lnt).val( lng );
          }
      } else {
          $.gm.markerNew.lat = lat;
          $.gm.markerNew.lng = lng;
          $.gm.markerNew.marker = $.gm.marker(lat,lng,title);
          $.gm.map.panTo(new google.maps.LatLng(lat,lng));

          $('#'+$.gm.f_name).val( title );
          $('#'+$.gm.f_lat).val( lat );
          $('#'+$.gm.f_lnt).val( lng );
      }

  },
  markerCounter: function(marker){
      if( typeof $.gm.markerCounter.markers == 'undefined' ) { $.gm.markerCounter.markers = []; }
//      console.log($.gm.markerCounter.markers.length);
      $.each($.gm.markerCounter.markers, function(index, value) {
          if (value.title == marker.title) {
              console.log('===> ' +value.title + ': ' + marker.title);
          }
      });

      $.gm.markerCounter.markers[$.gm.markerCounter.markers.length] = marker;
  },
  getCoordinatesByAddr: function(url,addr,map_id){
      // TODO add timer before sending data to server. Wait about 2 seconds
      if (addr.length >= 5) {
          $.getJSON(url+'&addr='+addr,
              function(data) {
                $('.res').html('lon: '+data.lon+' lat: '+data.lat+' name: '+data.name);
                $('#'+map_id).gm().markerNew(data.lat,data.lon,data.name);
                //alert('Load was performed.');
          });
      }
  },
    bindLocationFields : function (f_name, f_lat, f_lnt){
    	$.gm.f_name = f_name;
    	$.gm.f_lat = f_lat;
    	$.gm.f_lnt = f_lnt;
    }
},$.gm._import);

})(jQuery);