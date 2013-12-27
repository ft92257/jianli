//close upload content tab background dialog 
function close_content_bg_dialog(numbers){
     $("#background_crop_edit_"+numbers).remove();
	 $("#upload_bg_"+numbers).show();
	 $("#upload_bg_"+numbers+" .img_upload_loading_area img").hide();
	 $("#upload_bg_"+numbers+" #Filedata").removeClass("upload_btn_disabled");
}
//upload content tab background event
function content_upload_background(my_page_numbers){
     icon_crop_numbers = my_page_numbers;
	 $("#upload_bg_"+my_page_numbers+" .img_upload_loading_area img").show();
	 $("#upload_bg_"+my_page_numbers+" #Filedata").addClass("upload_btn_disabled");
	  var options = {
		  success: function(txt) {
					txt = eval('('+txt+')');
					if(txt.code==1){
						   $("#upload_bg_"+my_page_numbers).hide();
						   var ax_apps_id = $("#ax_apps_id").val();
						   $.post("/index.php/App/get_content_crop_bg",{'page_numbers':my_page_numbers,'ax_apps_id':ax_apps_id},function(data){
							     $("body").append( data );  
								 $("#background_crop_edit_"+my_page_numbers).css("top",30);
	                             $("#background_crop_edit_"+my_page_numbers).css("left",($(window).width() - $('#background_crop_edit_'+my_page_numbers).outerWidth())/2 );	
						         $("#background_crop_edit_"+my_page_numbers).show();
						         var tmpDate = new Date(); 
							     set_UP_W_H(txt.data['picwidth'],txt.data['picheight']);
							     var defautlv = ( txt.data['picwidth'] > txt.data['picheight']) ?txt.data['picheight']:txt.data['picwidth'];
							     $("#background_crop_edit_"+my_page_numbers+" #postImageUploadCropArea img").attr("src",txt.data['picurl']+'?t='+ tmpDate.getTime());
							     $("#background_crop_edit_"+my_page_numbers+" input[name=picurl]").attr('value',txt.data['picurl']);
							     $("#background_crop_edit_"+my_page_numbers+" #photo_big").attr('src',txt.data['picurl']+'?t='+ tmpDate.getTime());
							     content_background_imgrs = $('#background_crop_edit_'+my_page_numbers+' #postImageUploadCropArea img').imgAreaSelect({ 
										x1: 50, 
										y1: 50,
										x2: 250, 
										y2: 350, 
										handles: true,
										onInit:content_bg_preview,
										onSelectChange:content_bg_preview,
										onSelectEnd:content_bg_onSelectEnd,
										aspectRatio: '1:1.5',
										instance: true,
										resizable:false,
										maxWidth:320,
										maxHeight:480,
										minWidth:320,
										minHeight:480,
										parent:$('#background_crop_edit_'+my_page_numbers+' #postImageUploadCropArea')
										}); 
						    });
						      
					}else{
					   alert(txt.message);
					}
			    }
	 };
	 $("#upload_bg_"+my_page_numbers+" #form_uploadpic_"+my_page_numbers).ajaxSubmit(options);
	 return false;	
}

//tab content background crop save
function content_background_crop_save(numbers){
	 $("#background_crop_edit_"+numbers+" #postImageUplaodLoading img").show();
	 $("#background_crop_edit_"+numbers+" #btnSaveImagePostUpload").addClass("upload_btn_disabled").attr("disabled","disabled");
	 var options = {
			    success: function(txt) {
					txt = eval('('+txt+')');
					if(txt.code==1){
						  $("#background_crop_edit_"+numbers).remove();
						  var pic_url = txt.pic_url;
						  $("#upload_bg_"+numbers).show();
						  $("#upload_bg_"+numbers+" .img_upload_loading_area img").hide();
	                      $("#upload_bg_"+numbers+" #Filedata").removeClass("upload_btn_disabled");
						  $("#upload_bg_"+numbers+" .img-container .platform_img").attr('src',txt.pic_url).show();
			   			  $("#upload_bg_"+numbers+" .img-container .btn_remove_bgImg").show();
						  $("#edit_"+numbers+" .uploadPageBg div img").attr('src',txt.pic_url).show();
						  $("#frmLiveView").contents().find("#device_view_content_"+numbers).css({"background-image":"url("+txt.pic_url+")"});			  
					}else if(txt.code==0){
						  alert(txt.message);						  
					}
					$("#background_crop_edit_"+numbers+" #postImageUplaodLoading img").hide();
			    }
		   };		
		   $('#background_crop_edit_'+numbers+' #infouploadpic_crop').ajaxSubmit(options);
	       return false;	   
}

//删除tab content background 
function delete_page_background(my_page_numbers){
    var ax_apps_id = $("#ax_apps_id").val();
	$.post("/index.php/App/delete_page_background",{'ax_apps_id':ax_apps_id,'my_page_numbers':my_page_numbers},function(data){
		  if(data.code==1){
			  $("#frmLiveView").contents().find("#device_view_content_"+my_page_numbers).css({"background-image":""});
			  $("#upload_bg_"+my_page_numbers+" .img-container .platform_img").attr('src','').hide();
			  $("#upload_bg_"+my_page_numbers+" .img-container .btn_remove_bgImg").hide();
			  $("#edit_"+my_page_numbers+" .uploadPageBg div img").attr('src','').hide();
		  }else if(data.code==0){
			  
		  }else if(data.code==2){
			  
		  }
	},'json');
}

//上传 tab info icon
var content_background_imgrs;
function content_bg_onSelectEnd(img,selection){
	$('#background_crop_edit_'+icon_crop_numbers+' input[name=x1]').val(selection.x1);
	$('#background_crop_edit_'+icon_crop_numbers+' input[name=y1]').val(selection.y1);
	$('#background_crop_edit_'+icon_crop_numbers+' input[name=x2]').val(selection.x2);
	$('#background_crop_edit_'+icon_crop_numbers+' input[name=y2]').val(selection.y2); 
	$('#background_crop_edit_'+icon_crop_numbers+' input[name=w]').val(selection.width); 
	$('#background_crop_edit_'+icon_crop_numbers+' input[name=h]').val(selection.height); 
}	
function content_bg_preview(img, selection) {
	content_bg_onSelectEnd(img,selection);
	var big_scaleX = 100 / (selection.width || 1);
	var big_scaleY = 100 / (selection.height || 1);
	$('#background_crop_edit_'+icon_crop_numbers+' #photo_big').css({
		width: Math.round(big_scaleX * up_pic_width) + 'px',
	    height: Math.round(big_scaleY * up_pic_height) + 'px',
		marginLeft: '-' + Math.round(big_scaleX * selection.x1) + 'px',
		marginTop: '-' + Math.round(big_scaleY * selection.y1) + 'px'
	});
}		

