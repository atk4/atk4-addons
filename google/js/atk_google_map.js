
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
  fitZoom: function(points){
      /*
      http://loco.ru/materials/137-google-maps-masshtabiruem-kartu-delaem-po-centru
      http://stackoverflow.com/questions/2437683/google-maps-api-v3-can-i-setzoom-after-fitbounds
              point = [
                [title,lat,lng]
              ];
              this.fitZoom([point]);
       */


    // set zoom to show all points
    var latlngbounds = new google.maps.LatLngBounds();
    for ( var i=0; i<points.length; i++ ){
        var myLatLng = new google.maps.LatLng(locations[i][1], locations[i][2]);
         latlngbounds.extend(myLatLng);
    }
    this.map.setCenter( latlngbounds.getCenter(), this.map.fitBounds(latlngbounds));
//      var bounds = new google.maps.LatLngBounds();
//      this.map.fitBounds(bounds);
//      var listener = google.maps.event.addListener(this.map, "idle", function() {
//        if (this.map.getZoom() > 16) this.map.setZoom(16);
//        google.maps.event.removeListener(listener);
//      });
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
//      console.log('marker new = ' + $.gm.markerNew.marker);
//      console.log('lat = '+ lat);
//      console.log('lng = ' + lng);
      if( typeof $.gm.markerNew.marker != 'undefined' ) {
          if ( $.gm.markerNew.lat != lat && $.gm.markerNew.lng != lng && lat != null && lng != null ) {
              if ( typeof $.gm.markerNew.lat != 'undefined' && typeof $.gm.markerNew.lng != 'undefined' ) {
                      $.gm.markerNew.marker.setMap(null);
              }
              $.gm.markerNew.lat = lat;
              $.gm.markerNew.lng = lng;
              $.gm.markerNew.marker = $.gm.marker(lat,lng,title);
              $.gm.map.panTo(new google.maps.LatLng(lat,lng));

              $('#'+$.gm.f_location).val( title );
              $('#'+$.gm.f_lat).val( lat );
              $('#'+$.gm.f_lng).val( lng );
          }
      } else if (lat != null && lng != null) {
//          console.log('========>>>>> undefined <<<<<<=======');
          $.gm.markerNew.lat = lat;
          $.gm.markerNew.lng = lng;
          $.gm.markerNew.marker = $.gm.marker(lat,lng,title);
          $.gm.map.panTo(new google.maps.LatLng(lat,lng));

          $('#'+$.gm.f_location).val( title );
          $('#'+$.gm.f_lat).val( lat );
          $('#'+$.gm.f_lng).val( lng );
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
      // TODO simetimes it sends same request any way. Why?
      // TODO It is not crytical but interesting :)
      if ($.gm.getCoordinatesByAddr.lastRequest == addr) {
          //console.log('last request match. return  = '+$.gm.getCoordinatesByAddr.lastRequest);
          return;
      }
      if (addr.length >= 3 && $.gm.getCoordinatesByAddr.lastRequest != addr) {
          if( typeof $.gm.getCoordinatesByAddr.lineCounter == 'undefined' ) {
              $.gm.getCoordinatesByAddr.lineCounter = 0;
          } else {
              $.gm.getCoordinatesByAddr.lineCounter++;
              //console.log('up   = '+$.gm.getCoordinatesByAddr.lineCounter);
          }
          setTimeout(
              function () {
                  if ( $.gm.getCoordinatesByAddr.lineCounter > 0) {
                      $.gm.getCoordinatesByAddr.lineCounter--;
                      //console.log('down = '+$.gm.getCoordinatesByAddr.lineCounter);
                      return;
                  }

                  $.gm.getCoordinatesByAddr.lineCounter--;
                  console.log("BINGO " +$.gm.getCoordinatesByAddr.lineCounter);
                  $.getJSON(url+'&addr='+addr,
                      function(data) {
                        $('.res').html('<b>'+data.name+'.</b> <i>lng '+data.lon+' lat '+data.lat+'</i>');
                        $('#'+map_id).gm().markerNew(data.lat,data.lon,data.name);
                        //alert('Load was performed.');
                  });
                  $.gm.getCoordinatesByAddr.lastRequest = addr;
                  //console.log('last request   = '+$.gm.getCoordinatesByAddr.lastRequest);
              }
              ,1000
          );
      }
  },
    bindLocationFields : function (f_location, f_lat, f_lng){
    	$.gm.f_location = f_location;
    	$.gm.f_lat = f_lat;
    	$.gm.f_lng = f_lng;
    },
    renderMapWithTimeout: function(map,time){
        $.gm.getCoordinatesByAddr.lastRequest = '';
        $.gm.markerNew.marker = undefined;
        //console.log('marker must be undefined - '+$.gm.markerNew.marker );
        if ( typeof time == 'undefined' ) time = 5000;
        setTimeout(
            function () {
                $(map).trigger('render_map');

                setTimeout(
                    function () {
                        //console.log('$.gm.f_location value = ' + $('#'+$.gm.f_location).val());
                        //console.log('$.gm.f_lat value = ' + $('#'+$.gm.f_lat).val());
                        //console.log('$.gm.f_lng value = ' + $('#'+$.gm.f_lng).val());
                        if (
                          //$('#'+$.gm.f_location).val() != null && $('#'+$.gm.f_location).val() != '' &&
                          $('#'+$.gm.f_lat).val() != null && $('#'+$.gm.f_lat).val() != '' &&
                          $('#'+$.gm.f_lng).val() != null &&$('#'+$.gm.f_lng).val() != ''
                        ) {
                            $.gm.markerNew($('#'+$.gm.f_lat).val(),$('#'+$.gm.f_lng).val(),$('#'+$.gm.f_location).val());
                        }
                    },500)
                }
                ,time
        );
    }
},$.gm._import);

})(jQuery);