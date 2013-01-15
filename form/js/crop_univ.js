$.each({
  cropSelectSetTarget: function(field){
  	this.cropSelectTarget=field;
  },
  cropSelectTarget: null,
  cropSelect: function(a){
    $(this.cropSelectTarget).val(this.toJSON(a));
  },
  uploadHandler: function(crop_url,reload_url){
  	var uploader=this.jquery;
  	$('.spinner').hide();

  	reload_url = $.atk4.addArgument(reload_url);
    crop_url = $.atk4.addArgument(crop_url);

  	this.dialogURL('Crop and Save',crop_url,{close: function(){
  		document.location=reload_url;
  	}});

  }
},$.univ._import);
