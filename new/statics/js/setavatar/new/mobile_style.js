$(function(){
	 //header image dialog delete event
	 $("#style_header_image_dialog .btn_remove_bgImg").live('click',function(){
		   $.post("/index.php/App/delete_style_header_bg",{'ax_apps_id':$("#ax_apps_id").val()},function(data){
			     if(data.code==1){
					$("#style_header_image_dialog .platform_img").attr('src','').hide();
					$("#style_header_image_dialog .btn_remove_bg Img").hide();
					$("#style_header_image_dialog .text-container span").show();
					$("#headerLogoImg").attr('src','').hide();
					$("#frmLiveView").contents().find("#app .app_info_bar").css({"background-image":""});
					$("#frmLiveView").contents().find("#app .app_info_bar .app_header_text").html(data.header_text).show();
				 }else{
				    alert( data.message );	 
				 }   
		    },'json');
	       
			 
	 });
	
     //header text image
	 //Image click event
	 $("#headerImg").click(function(){
		  if(!$(this).hasClass("checked")){
			  $("#headerText").removeClass("checked");
			  $(this).addClass("checked");
			  $(".headerContent_container .appHeadertxtWrapper").hide();
			  $(".headerContent_container .headerLogoContainer").fadeOut('slow').show();
			  $(".headerControl_Btns").fadeOut('slow').show();
			  
			  //device bg 
			  $.post("/index.php/App/get_style_header_bg",{'ax_apps_id':$("#ax_apps_id").val()},function(data){
				   if(data.code==1){
					    $("#frmLiveView").contents().find("#app .app_info_bar").css({"background-image":"url("+data.header_img+")"});
						$("#headerLogoImg").show();
						$("#frmLiveView").contents().find("#app .app_info_bar .app_header_text").html("");
				   }else{
				        alert( data.message );
				   }  
			  },'json');
			  
		  }
	 });
	 //Text click event
	 $("#headerText").click(function(){
		 if(!$(this).hasClass("checked")){
			  $("#headerImg").removeClass("checked");
			  $(this).addClass("checked");
			  $(".headerContent_container .headerLogoContainer").hide();
			  $(".headerControl_Btns").hide();
			  $(".headerContent_container .appHeadertxtWrapper").fadeOut('slow').show();
			  
			  //device text
			  $.post("/index.php/App/get_style_header_text",{'ax_apps_id':$("#ax_apps_id").val()},function(data){
				   if(data.code==1){
					    $("#frmLiveView").contents().find("#app .app_info_bar").css({"background-image":""});
						$("#frmLiveView").contents().find("#app .app_info_bar .app_header_text").html(data.header_text).show();
						$("#headerLogoImg").hide();
				   }else{
				        alert( data.message );
				   }  
			  },'json');
		 }
     });
	 //header text blur
	 $("#appHeadertxt").blur(function(){
	      var ax_apps_id = $("#ax_apps_id").val();
		  var app_header_txt = $(this).val();
		  $.post("/index.php/App/style_text",{
			       'ax_apps_id':ax_apps_id,
				   'app_header_txt':app_header_txt
			   },function(data){
				    if(data.code==1){
				      $("#frmLiveView").contents().find("#app .app_info_bar").css({"background-image":""});
					  $("#frmLiveView").contents().find("#app .app_info_bar .info_bar_right").prev().remove();   
					  $("#frmLiveView").contents().find("#app .app_info_bar .info_bar_right").before(data.content); 
					}
		  },'json');	 
	 });
	 //header upload image dialog event
	 $("#uploadHeaderLogoButton,#headerImgContainer").click(function(){
	         var ax_apps_id = $("#ax_apps_id").val();
			 var app_header_text = $("#appHeadertxt").val();
			 $.post("/index.php/App/style_header_image_dialog",{'ax_apps_id':ax_apps_id,'app_header_text':app_header_text},function(data){
				   $(".ui-widget-overlay").show();  
				   $("body").append( data );
			 });	 
	 });
	 //close 
	 $("#style_header_image_dialog .ui-icon-closethick,#style_header_image_dialog .upload_cancel_button").live('click',function(){
	        $("#style_header_image_dialog").remove();
		    $(".ui-widget-overlay").hide();
			return false;   
	 });
	 
	 $("#style_header_crop_dialog .ui-dialog-titlebar-close").live('click',function(){
	         $("#style_header_crop_dialog").remove();
			 $("#style_header_image_dialog").show();
			 $("#style_header_image_dialog .img_upload_loading_area img").hide();
			 $("#style_header_image_dialog .header-upload-link .qq-upload-button").removeClass("upload_btn_disabled");
	 });
	 	
});

//header image upload 
function upload_style_header_image(){
	$("#style_header_image_dialog .img_upload_loading_area img").show();
	$("#style_header_image_dialog .header-upload-link .qq-upload-button").addClass("upload_btn_disabled");
	var options = {
		  success: function(txt) {
					txt = eval('('+txt+')');
					if(txt.code==1){
						   $("#style_header_image_dialog").hide();
						   var ax_apps_id = $("#ax_apps_id").val();
						   $.post("/index.php/App/style_header_crop",{'ax_apps_id':ax_apps_id},function(data){
							     $("body").append( data );  
								 $("#style_header_crop_dialog").css("top",30);
	                             $("#style_header_crop_dialog").css("left",($(window).width() - $('#style_header_crop_dialog').outerWidth())/2 );	
						         $("#style_header_crop_dialog").show();
						    var tmpDate = new Date(); 
							set_UP_W_H(txt.data['picwidth'],txt.data['picheight']);
							var defautlv = ( txt.data['picwidth'] > txt.data['picheight']) ?txt.data['picheight']:txt.data['picwidth'];
							$("#style_header_crop_dialog #postImageUploadCropArea img").attr("src",txt.data['picurl']+'?t='+ tmpDate.getTime());
							$("#style_header_crop_dialog input[name=picurl]").attr('value',txt.data['picurl']);
							$("#style_header_crop_dialog #photo_big").attr('src',txt.data['picurl']+'?t='+ tmpDate.getTime());
							style_header_imgrs = $('#style_header_crop_dialog #postImageUploadCropArea img').imgAreaSelect({ 
										x1: 50, 
										y1: 100,
										x2: 370, 
										y2: 144, 
										handles: true,
										onInit:style_header_preview,
										onSelectChange:style_header_preview,
										onSelectEnd:style_header_onSelectEnd,
										aspectRatio: '1:1',
										instance: true,
										resizable:false,
										maxWidth:320,
										maxHeight:44,
										minWidth:320,
										minHeight:44,
										parent:$('#style_header_crop_dialog #postImageUploadCropArea')
										}); 
						   });
						   
						   
					}else{
					   alert(txt.message);
					}
			    }
	 };
	 $("#form_style_header_image_dialog").ajaxSubmit(options);
	 return false;		
}

//style header image crop save
function style_header_crop_save(){
     $("#style_header_crop_dialog #postImageUplaodLoading img").show();
	 $("#style_header_crop_dialog #btnSaveImagePostUpload").addClass("upload_btn_disabled").attr("disabled","disabled");
	 var options = {
			    success: function(txt) {
					txt = eval('('+txt+')');
					if(txt.code==1){
						  $("#style_header_crop_dialog").remove();
			              $("#style_header_image_dialog").show();
			              $("#style_header_image_dialog .img_upload_loading_area img").hide();
			              $("#style_header_image_dialog .header-upload-link .qq-upload-button").removeClass("upload_btn_disabled");
						  var pic_url = txt.pic_url;
						  $("#style_header_image_dialog .img-container .platform_img").attr('src',pic_url).show();
						  $("#style_header_image_dialog .img-container .text-container span").html("");
						  $("#style_header_image_dialog .img-container .btn_remove_bgImg").show();
						  $("#headerImgContainer img").attr('src',pic_url).show();
						  $("#frmLiveView").contents().find("#app .app_info_bar").css({"background-image":"url('"+pic_url+"')"});
						  $("#frmLiveView").contents().find("#app .app_info_bar h1").hide();						  	  
					}else if(txt.code==0){
						  alert(txt.message);						  
					}
					
			    }
		   };		
	  $('#style_header_crop_dialog #style_header_image_crop').ajaxSubmit(options);
	  return false;	   	
}

//上传 tab info icon
var style_header_imgrs;
function style_header_onSelectEnd(img,selection){
	$('#style_header_crop_dialog input[name=x1]').val(selection.x1);
	$('#style_header_crop_dialog input[name=y1]').val(selection.y1);
	$('#style_header_crop_dialog input[name=x2]').val(selection.x2);
	$('#style_header_crop_dialog input[name=y2]').val(selection.y2); 
	$('#style_header_crop_dialog input[name=w]').val(selection.width); 
	$('#style_header_crop_dialog input[name=h]').val(selection.height); 
}	
function style_header_preview(img, selection) {
	style_header_onSelectEnd(img,selection);
	var big_scaleX = 320 / (selection.width || 1);
	var big_scaleY = 44 / (selection.height || 1);
	$('#style_header_crop_dialog #photo_big').css({
		width: Math.round(big_scaleX * up_pic_width) + 'px',
	    height: Math.round(big_scaleY * up_pic_height) + 'px',
		marginLeft: '-' + Math.round(big_scaleX * selection.x1) + 'px',
		marginTop: '-' + Math.round(big_scaleY * selection.y1) + 'px'
	});
}		

function change_device_color(color){
   var main_color =color;
   var main_color_rgba = colorRgba(main_color,1.00);
   $("#frmLiveView").contents().find(".app_header").removeClass("clr_appHeader_bg").css({"background-color":main_color});
   
   
}

//Look & feel Change the general appearance of your app and mobile site start
$(function(){
	//主题
	$("#matterialBoxChangeWrapper").click(function(){
		    if($(this).parent().parent().hasClass("active")){
			    look_feed_div_hide();
			}else{
			  look_feed_div_hide();
	      	  $(this).parent().parent().addClass("active");
			  $(this).parent().parent().find(".themeItem_reveal_box").slideDown("fast").show();
			}
	});
	//选中主题
	$("#matterialBox #matterialItemsContainer img").click(function(){
		 var theme_url = $(this).attr('src');
	     $.post("/index.php/App/style_set_theme",{'ax_apps_id':$('#ax_apps_id').val(),'theme_type':$(this).attr('data-rowid')},function(data){
			  if(data.code==1){
				   $("#matterialImg").attr('src',theme_url);
		           look_feed_div_hide();
				   //更改颜色方案
				   $("#style_theme .themeList li").remove();
				   $("#style_theme .themeList").append(data.theme_color_list);
				   //更改device中颜色
				   if( $("#frmLiveView").contents().find("#app div").hasClass("app_header")){
					     change_device_color( data.blue[4]);					 
				   } 
				    
			  }else if(data.code==0){
				  alert(data.message);
			  }
		 },'json');
		 
	});
	
	
	//背景
	$("#bgImgContainer").click(function(){
		   if($(this).parent().parent().hasClass("active")){
			    look_feed_div_hide();
			}else{
			  look_feed_div_hide();
	      	  $(this).parent().parent().addClass("active");
			  $(this).parent().parent().find(".themeItem_reveal_box").slideDown("fast").show();
			}
	});
	//布局
	$("#navItemIcon").click(function(){
		    if($(this).parent().parent().hasClass("active")){
			    look_feed_div_hide();
			}else{
			  look_feed_div_hide();
	      	  $(this).parent().parent().addClass("active");
			  $(this).parent().parent().find(".themeItem_reveal_box").slideDown("fast").show();
			}
	});
	
	//页面点击隐藏Theme Background Navigation 下拉框
	$(document).click(function(evt){
		   if( $(evt.target).parents("#matterialBoxChangeWrapper").length==0 &&  $(evt.target).parents("#bgImgContainer").length==0 && $(evt.target).parents("#navItemIcon").length==0 && $(evt.target).parents("#matterialBox").length==0 && $(evt.target).parents("#backGroundBox").length==0 && $(evt.target).parents("#navigationBox").length==0){
			  look_feed_div_hide();
		   }
	});
	
});
function look_feed_div_hide(){
	 $(".themeBox").removeClass("active");
	 $(".themeBox .themeItem_reveal_box").hide();
	 $(".bgBox").removeClass("active");
	 $(".bgBox .themeItem_reveal_box").hide();
	 $(".navigationWrapper").removeClass("active");
	 $(".navigationWrapper .themeItem_reveal_box").hide();
}

//Look & feel Change the general appearance of your app and mobile site end


//color select start
$(function(){
	//页面点击隐藏 advanced 下拉框
	$(document).click(function(evt){
		   if( $(evt.target).parents("#style_theme").length==0 ){
			     $("#style_theme .customize_link").removeClass("customize_expanded").addClass("customize_collapsed");
			     $("#style_theme .themeColors").slideUp("fast").hide("slow");
		   }
	});
	
	
	//点击advanced 
	$("#style_theme .customize_link").click(function(){
      if($(this).hasClass("customize_collapsed")){
		    $(this).removeClass("customize_collapsed").addClass("customize_expanded");
			$("#style_theme .themeColors").slideDown("fast").show();
	  }else{
		    $(this).removeClass("customize_expanded").addClass("customize_collapsed");
			$("#style_theme .themeColors").slideUp("fast").hide("slow");
	  } 	
    });
	
	//Crimson Mono Earth Blues  style click event
	$("#style_theme .themeList .theme_container").live('click',function(){
		   
		   if($(this).hasClass("theme_selected")){			     
			     if($("#style_theme .customize_link").hasClass("customize_collapsed")){
					  $("#style_theme .customize_link").removeClass("customize_collapsed").addClass("customize_expanded");
					  $("#style_theme .themeColors").slideDown("fast").show();
				  }else{ 
					  $("#style_theme .customize_link").removeClass("customize_expanded").addClass("customize_collapsed");
					  $("#style_theme .themeColors").slideUp("fast").hide("slow");
				  } 	  
		   }else{
			   	 $("#style_theme .themeList").find(".theme_selected").removeClass("theme_selected");
		         $(this).addClass("theme_selected");
				 
				 if($("#style_theme .customize_link").hasClass("customize_collapsed")){
					  $("#style_theme .customize_link").removeClass("customize_collapsed").addClass("customize_expanded");
					  $("#style_theme .themeColors").slideDown("fast").show();
				  }    
		   }
		   
		   //更改颜色信息
		   var theme_type_color = $(this).attr("name");
		   $.post("/index.php/App/theme_type_color",{'theme_type_color':theme_type_color,'ax_apps_id':$("#ax_apps_id").val()},function(data){
			     $("#style_theme .themeColors div ul li").remove();
				 $("#style_theme .themeColors div ul").append( data.type_color );
				 change_device_color(data.theme_type_color[4]);
		   },'json');
		   
		 
	});
	
	//color click
	$("#style_theme .style_inner .color_display_container").live('click',function(){
		  hide_theme_color_picker();		
	      $(this).removeClass("collapsed").addClass("color_display_container_expanded");
		  $(this).parent().find(".theme_color_picker").slideDown("fast").show();
		  
		  var rel = $(this).parent().attr('rel');
		  var sel_color = "";
		  $.ajax({  
					  type:"POST",  
					  async:false,  
					  url:"/index.php/App/get_picker_color",  
					  data:{'ax_apps_id':$('#ax_apps_id').val(),'rel':rel},  
					  dataType: "json",
					  success:function(data){  
						 sel_color = data.color;
					 }  
				  });  
		  
 		  if($(this).parent().parent().find(".theme_color_picker div div").hasClass("colorpicker")){
			  $(this).parent().parent().find(".theme_color_picker div div").show();
		  }else{
 
				 $(this).parent().parent().find(".theme_color_picker div").ColorPicker({
					flat:true,
					color: sel_color,
					onShow: function (colpkr) {
						$(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						$(colpkr).fadeOut(500);
						return false;
					},
					onChange: function (hsb, hex, rgb) {
						 change_device_color("#"+hex);
						 $(this).parent().parent().find("div div").first().css('background','#'+hex);
					}
				});
		  }
		  
	});
	
	//点击页面 隐藏颜色拾取器容器
	$(document).click(function(evt){
		   if( $(evt.target).parents(".theme_color_container").length==0 && $(evt.target).parents(".color_display_container").length==0 ){
			     hide_theme_color_picker();
		   }
	});
	
});
function hide_theme_color_picker(){
	$("#style_theme .style_inner .color_display_container").each(function(n,e){
		  if($(e).hasClass("color_display_container_expanded")){
			    $(e).removeClass("color_display_container_expanded").addClass("collapsed");
				$(e).parent().find(".theme_color_picker").hide();
				$(this).parent().parent().find(".theme_color_picker div div").hide();
		  }
	});
}
//color select end
function colorRgba(color,a){
	if(a==""){
	    a=1.00;	
	}
	var reg = /^#([0-9a-fA-f]{3}|[0-9a-fA-f]{6})$/;
	var sColor = color.toLowerCase();
	if(sColor && reg.test(sColor)){
		if(sColor.length === 4){
			var sColorNew = "#";
			for(var i=1; i<4; i+=1){
				sColorNew += sColor.slice(i,i+1).concat(sColor.slice(i,i+1));	
			}
			sColor = sColorNew;
		}
		var sColorChange = [];
		for(var i=1; i<7; i+=2){
			sColorChange.push(parseInt("0x"+sColor.slice(i,i+2)));	
		}
		return "rgba(" + sColorChange.join(",") + ","+a+")";
	}else{
		return sColor;	
	}
		
}