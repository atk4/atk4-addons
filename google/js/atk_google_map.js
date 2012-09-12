
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

              $('#'+$.gm.f_location).val( title );
              $('#'+$.gm.f_lat).val( lat );
              $('#'+$.gm.f_lnt).val( lng );
          }
      } else if (lat != null && lng != null) {
          $.gm.markerNew.lat = lat;
          $.gm.markerNew.lng = lng;
          $.gm.markerNew.marker = $.gm.marker(lat,lng,title);
          $.gm.map.panTo(new google.maps.LatLng(lat,lng));

          $('#'+$.gm.f_location).val( title );
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
      // TODO simetimes it sends same request any way. Why?
      // TODO It is not crytical but interesting :)
//      if ($.gm.getCoordinatesByAddr.lastRequest == addr) {
//          console.log('last request match. return  = '+$.gm.getCoordinatesByAddr.lastRequest);
//          return;
//      }
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
                  //console.log("BINGO " +$.gm.getCoordinatesByAddr.lineCounter);
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
    bindLocationFields : function (f_location, f_lat, f_lnt){
    	$.gm.f_location = f_location;
    	$.gm.f_lat = f_lat;
    	$.gm.f_lnt = f_lnt;
    },
    renderMapWithTimeout: function(map,time){
        if ( typeof time == 'undefined' ) time = 5000;
        setTimeout(
                function () {$(map).trigger('render_map');}
                ,time
        );
    }
},$.gm._import);

})(jQuery);