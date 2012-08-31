
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
  start: function(lat,lng,options){
  	def={
  		zoom: 8, 
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
  }
},$.gm._import);

})(jQuery);