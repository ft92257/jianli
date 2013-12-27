var x=function(){
    var an=0,am=0;
    if(typeof(window.innerWidth)=="number"){an=window.innerWidth;am=window.innerHeight}else{if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight)){an=document.documentElement.clientWidth;am=document.documentElement.clientHeight}else{if(document.body&&(document.body.clientWidth||document.body.clientHeight)){an=document.body.clientWidth;am=document.body.clientHeight}}}return am};

var w=function(){
   var an=document;
   var aq=Math.max(an.body.scrollHeight,an.documentElement.scrollHeight);
   var ap=Math.max(an.body.offsetHeight,an.documentElement.offsetHeight);
   var am=Math.max(an.body.clientHeight,an.documentElement.clientHeight);
   var ao=Math.max(aq,ap,am);return ao};

function aq(){
   I=210;liveViewMaxTop=440;minHeight=700;s=0;
   var ar={duration:500,queue:false};
   var ax=$(document).scrollTop();
   var av=w();
   var aw=x();
   if(aw>minHeight){   if(ax<I){s=0}else{   if(ax>av-900){s=(av-850)-I}else{s=ax-I}  } }else{s=0}
   offset=(s+0)+"px";

  $("#Device").animate({top:offset},ar);
   
 }
   
$(window).scroll( function(){ aq(); });


 
 
 
var maxElmToSlide=6;
var pagesScrollerWrapper;
var numberOfSlides=0;
var currentPagesSlide=1;
function UpdateScroller(a){
    pagesScrollerWrapper=$("#your_pages_container_inner");
    var d=pagesScrollerWrapper.find("ul.your_pages");
    if(a=="remove"){
	    var c=$("ul.your_pages li").length;	
	}else{
		var c=$("ul.your_pages li").length+1;
	}
    var e=parseFloat(pagesScrollerWrapper.width())-6;
    numberOfSlides=Math.floor(c/maxElmToSlide)==0?1:(c%maxElmToSlide==0?c/maxElmToSlide:Math.floor(c/maxElmToSlide)+1);
	//var mypageCount = parseInt($("#your_pages_container_inner ul li").size()) ;
   // numberOfSlides = Math.ceil(mypageCount/6); 
    d.css("width",e*(Math.floor(c/maxElmToSlide)+1)+"px");
	
$("#scrollPagesRight").unbind("click").bind("click",function(){if($(this).hasClass("enabled")){$(".editZone").slideUp("fast");$(".edit_container").hide();$(".your_pages li").removeClass("selected");d.animate({left:"-="+e});currentPagesSlide++;b()}});

$("#scrollPagesLeft").unbind("click").bind("click",function(){if($(this).hasClass("enabled")){$(".editZone").slideUp("fast");$(".edit_container").hide();
$(".your_pages li").removeClass("selected");
d.animate({left:"+="+e});
currentPagesSlide--;b()}});
pagesScrollerWrapper.mousemove(function(f){if($(".ui-sortable-helper").length>=1){
var g=$("#Section-Pages .hidScrollHoverLeft");
var h=$("#Section-Pages .hidScrollHoverRight");
if(f.pageX<=g.offset().left){$("#scrollPagesLeft").trigger("click")}else{if(f.pageX>=h.offset().left){$("#scrollPagesRight").trigger("click")}}}});if(a=="add"){
	d.animate({left:-(e*(numberOfSlides-1))});
    currentPagesSlide=numberOfSlides;b()}else{
	if(a=="remove"){ 
	
	if(currentPagesSlide>numberOfSlides){d.animate({left:"+="+e});currentPagesSlide=numberOfSlides;b();}}}

function b(){if(numberOfSlides>1&&currentPagesSlide<numberOfSlides){$("#scrollPagesRight").addClass("enabled")}else{$("#scrollPagesRight").removeClass("enabled")}if(numberOfSlides>1&&currentPagesSlide>1){$("#scrollPagesLeft").addClass("enabled")}else{$("#scrollPagesLeft").removeClass("enabled")}}b()
};

var ax_apps_id;
$(function(){
	if( parseInt($("#your_pages_container_inner ul li").size()) <=0 ){
   	   show_get_start();
	}
});

function show_get_start(){
	$("#Section-Pages .section_body .no_content_note").show();
	$("#Device .no_pages_liveview").show();
}

function hide_get_start(){
	$("#Section-Pages .section_body .no_content_note").hide();
	$("#Device .container .content .no_pages_liveview").hide();
}

function get_rand_num(n){
	var chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	var res = "";
	for(var i=0;i<n;i++){
		var id = Math.ceil(Math.random()*35);
		res+=chars[id];
	}
	return res;
}

$(function(){
	//tab切换
	$("#editMenu > a").click(function(){
			var currentHref=$(this).attr("name");
			$(this).addClass("activeTab");
			$(currentHref).addClass("activeTab");
		   
		    $("#editMenu a").each(function(n,e){
			      if($(e).attr('name')!=currentHref){ $(e).removeClass("activeTab"); $($(e).attr('name')).removeClass("activeTab"); }
			});
					
	 });
	
	
	 
	 $("#your_pages_container_inner ul li").click(function(){
	       var app_name = $(this).attr("data-pageid");
		   if( $("#page_id_myapps").hasClass("selected") ){
		      $("#edit_container_"+app_name).slideUp('slow');
			   setTimeout(function(){
			   $("#page_id_"+app_name+" .selected_page").hide();
			   $(".editZone").hide();
			},400);
			   $("#page_id_myapps").removeClass("selected");
		   }else{
			   $("#edit_container_"+app_name).slideDown('slow');
			   setTimeout(function(){
			   $("#page_id_"+app_name+" .selected_page").show();
			   $(".editZone").show();
				},400);
				$("#page_id_myapps").addClass("selected");
			   }
		});	
	 
});


function add_app(o){
	 ax_apps_id = $("#ax_apps_id").val();
	 hide_get_start();
	 $(".your_pages li").each(function(n,e){
		  if( $(e).hasClass("selected") ){
			   var app_name =  $(this).attr('id'); 
			   $("#"+app_name).removeClass('selected');
			   $("#edit_"+app_name).slideUp('fast').hide();  
		  }
	 });
	  
	  var app_name = $(o).attr("name"); 
	  var page_id = $(o).attr("id");
	  var app_id = get_rand_num(10);
	  $.post("/index.php/App/get_app", { "apps": app_name,"id":app_id,ax_apps_id:ax_apps_id,page_id:page_id },
				 function(data){
					  $("#your_pages_container_inner ul").append(data);   
				  });
	  $.post("/index.php/App/get_app_content", { "apps": app_name,"id":app_id,ax_apps_id:ax_apps_id,page_id:page_id },
				 function(data){
					  $(".editZone").append(data).slideDown('fast').show();   
	   });
	  UpdateScroller("add");
	  change_phone_content(app_id,app_name,page_id,ax_apps_id);
}

function change_phone_content(app_id,app_name,page_id,ax_apps_id){
      	$("#Device .container .no_pages_liveview").hide();
		if( parseInt($("#your_pages_container_inner ul li").size()) <=0 ){
   	         $("#frmLiveView").attr("src","/index.php/App/layout/id/"+app_id+"/name/"+app_name+"/page_id/"+page_id+"/ax_apps_id/"+ax_apps_id);
	    }else if( parseInt($("#your_pages_container_inner ul li").size()) ==5 ){
			 $("#frmLiveView").contents().find("#app #app_navigation_bar td").each(function(n,e){
				   var app_id_device = $(e).attr("data");
				   $(e).find("a").removeClass("selected");
				   $("#frmLiveView").contents().find("#app_pages_container #device_view_content_"+app_id_device).hide();
			 });
			 $("#frmLiveView").contents().find("#app_navigation_bar tr td:last").remove();
			 $.post("/index.php/App/get_device_more", { "app_name": app_name,"app_id":app_id },
						 function(data){
								$("#frmLiveView").contents().find("#app_navigation_bar tr").append(data);
						  });
			 $.post("/index.php/App/add_app_content", { "app_name": app_name,"app_id":app_id },
						 function(data){
								$("#frmLiveView").contents().find("#app_pages_container").append(data);
						  });	
			 
		}else if(parseInt($("#your_pages_container_inner ul li").size()) > 5 ){
			
			 $("#frmLiveView").contents().find("#app #app_navigation_bar td").each(function(n,e){
				   var app_id_device = $(e).attr("data");
				   $(e).find("a").removeClass("selected");
				   $("#frmLiveView").contents().find("#app_pages_container #device_view_content_"+app_id_device).hide();
			 });
			  var sel_app_id = $("#frmLiveView").contents().find("#app_pages_container div:visible").attr("data");	 
	         $("#frmLiveView").contents().find("#device_view_content_"+sel_app_id).hide(); 
			 $.post("/index.php/App/add_app_content", { "app_name": app_name,"app_id":app_id },
						 function(data){
								$("#frmLiveView").contents().find("#app_pages_container").append(data);
						  });	
		
		}else{
		     $("#frmLiveView").contents().find("#app #app_navigation_bar td").each(function(n,e){
				   var app_id_device = $(e).attr("data");
				   $(e).find("a").removeClass("selected");
				   $("#frmLiveView").contents().find("#app_pages_container #device_view_content_"+app_id_device).hide();
			 });
			
			 
			 
			  $.post("/index.php/App/add_app_nav", { "app_name": app_name,"app_id":app_id },
						 function(data){
								$("#frmLiveView").contents().find("#app_navigation_bar tr").append(data);
						  });
			  $.post("/index.php/App/add_app_content", { "app_name": app_name,"app_id":app_id },
						 function(data){
								$("#frmLiveView").contents().find("#app_pages_container").append(data);
						  });	
			 
			 var app_count =   100 /  parseInt( $("#frmLiveView").contents().find("#app #app_navigation_bar td").size()+1);
		     $("#frmLiveView").contents().find("#app #app_navigation_bar td").each(function(n,e){
				   $(e).css("width",app_count+"%");
			 });		  
			 
		}
}

function close_content_app(app_id){
		$("#edit_"+app_id).slideUp('fast',function(){ $(".editZone").hide(); });
		setTimeout(function(){
		   $("#"+app_id+" .selected_page").hide();
		   
		},400);
		$("#"+app_id).removeClass("selected");
}

function co_content_app(app_id){
	if($("#"+app_id).hasClass('selected')){
		close_content_app(app_id);
	}else{
		$("#your_pages_container_inner ul li").each(function(n,e){
		     if( $(e).hasClass('selected') ){
				$("#edit_"+ $(e).attr('id')).slideUp('fast');
				setTimeout(function(){
				   $("#"+ $(e).attr('id')+" .selected_page").hide();
				   $(".editZone").hide();
				},400);
				$("#"+ $(e).attr('id') ).removeClass("selected");
			 }
		});
		
		$("#edit_"+app_id).slideDown('slow');
		setTimeout(function(){
		     $("#"+app_id+" .selected_page").show();
		     $(".editZone").show();
		   },400);
		$("#"+app_id).addClass("selected");
			   
	}
	c_device_view(app_id);
}

function c_device_view(app_id){
	
	
	$("#frmLiveView").contents().find("#template_view_more").hide();
	$("#frmLiveView").contents().find("#app_navigation_bar td").each(function(n,e){
		 if($(e).find("a").hasClass("selected")){
			   $(e).find("a").removeClass("selected");
		 }
		 
	});
	 var sel_app_id = $("#frmLiveView").contents().find("#app_pages_container div:visible").attr("data");	 
	 $("#frmLiveView").contents().find("#device_view_content_"+sel_app_id).hide(); 
	 $("#frmLiveView").contents().find("#device_view_nav_"+app_id+" a").addClass("selected");
	 $("#frmLiveView").contents().find("#device_view_content_"+app_id).show();
}

function remove_content_app(app_id){
	co_content_app(app_id);
	
	
	var app_name = $("#"+app_id).attr("rel");
	$.post("/index.php/App/get_app_dialog", { "apps": app_name,"app_id":app_id },
				   function(data){
						$("body").append(data);
						$(".ui-widget-overlay").show();   
		 });	
}

function content_cancel(app_id){
		$(".ui-widget-overlay").hide();   
		$("#dia_"+app_id).remove();
}

function content_remove(app_id){
	if( parseInt($("#your_pages_container_inner ul li").size()) ==6 ){
	     	//处理
			alert("动态调取iphone容器列表");
			$("#frmLiveView").contents().find("#app_navigation_bar td:last").remove();
	}
	$(".ui-widget-overlay").hide(); 
	$("#"+app_id).remove();
	$("#edit_"+app_id).remove();
	$("#dia_"+app_id).remove();
	$(".editZone").hide();
	UpdateScroller("remove");
	
	if( parseInt($("#your_pages_container_inner ul li").size()) <=0 ){
   	   show_get_start();
	}
		
	$("#frmLiveView").contents().find("#device_view_nav_"+app_id).remove();
    $("#frmLiveView").contents().find("#device_view_content_"+app_id).remove();
	var set_appid=$("#frmLiveView").contents().find("#app_navigation_bar td").first().attr("data");
	$("#frmLiveView").contents().find("#device_view_nav_"+set_appid).find("a").addClass("selected");
	$("#frmLiveView").contents().find("#device_view_content_"+set_appid).show();
}

function change_page_icon(app_id){
	if( $("#edit_"+app_id+" .editor_link_change_icon").hasClass("page_icon_expanded") ){
		 $("#edit_"+app_id+" .editor_link_change_icon").removeClass("page_icon_expanded");
		 $(".icon_select").slideUp('fast',function(){ setTimeout(function(){ $(this).hide(); },400); });
		 $(".upload_custom_icon_wrapper").hide();
		 $(".icon_select .icon_select_tabs_buttons a").each(function(n,e){
			  if( $(e).attr("data") ){
				  $(e).removeClass("active");  
			  } 
		 });				 
	 }else{
		 $("#edit_"+app_id+" .editor_link_change_icon").addClass("page_icon_expanded"); 
		 $("#edit_"+app_id+" .icon_select").slideDown('fast',function(){ set_page_icon_out(true); }).show();
		 
		 $("#edit_"+app_id+" .icon_select_tabs_buttons a").first().addClass("active");
		 $("#edit_"+app_id+" .icon_select_inner").addClass("active").show();
		 
	 }	 
	 $("#custom_sel_"+app_id).hide();
}

function page_icon_tab(tab_name,app_id,obj){
	       if(!$(obj).hasClass("active")){
			 $("#custom_sel_"+app_id).hide();
			 $("#edit_"+app_id+" .icon_select_tabs_buttons a").each(function(n,e){
				      if(  $(e).hasClass('active')  ){  $(e).removeClass("active"); }
			  });  
			  $(obj).addClass("active");
			  var txt_data = $(obj).attr("data");
			  $(".icon_select_inner").each(function(i,e){
				    if($(e).attr("data")==txt_data){
					    $(e).addClass("active").show();	
					}else{
						$(e).removeClass("active").hide();
					}
					if(txt_data=="custom"){
						$("#edit_"+app_id+" .upload_custom_icon_wrapper").show();
					}else{
						 
					    $("#edit_"+app_id+" .upload_custom_icon_wrapper").hide();	
					}
			  });
			  
		   }
}

function hide_page_icon(app_id){
	if( $("#edit_"+app_id+" .editor_link_change_icon").hasClass("page_icon_expanded") ){
		 $("#edit_"+app_id+" .upload_custom_icon_wrapper").hide();
		 $("#edit_"+app_id+" .icon_select .icon_select_tabs_buttons a").each(function(n,e){
			  if( $(e).attr("data") ){
				  $(e).removeClass("active");  
			  } 
		 });
		 
		 $("#edit_"+app_id+" .editor_link_change_icon").removeClass("page_icon_expanded");
		 $("#edit_"+app_id+" .icon_select").slideUp('fast',function(){ setTimeout(function(){ $(this).hide(); },400); }); 
	 }
}

var isOut = true;
function set_page_icon_out(flag){
	isOut = flag;
}
$(document).click(function(){
  if(isOut){
	   $(".editor_link_change_icon").removeClass("page_icon_expanded");
	   $(".icon_select").slideUp('fast',function(){ setTimeout(function(){ $(this).hide(); },400); });
	   $(".upload_custom_icon_wrapper").hide();
	   $(".icon_select .icon_select_tabs_buttons a").each(function(n,e){
			if( $(e).attr("data") ){
				$(e).removeClass("active");  
			} 
	   });
	   //$("#custom_sel").hide();		 
	   isOut = false;
  }  
  
});

//tab content default icon selected.
//app_id -- my_page_numbers
function check_page_icon(obj,app_id){
	$("#frmLiveView").contents().find("#template_view_more").hide();
	ax_apps_id = $("#ax_apps_id").val();
	var my_page_icon = $(obj).attr("sel");
	var icon_white_url = $(obj).attr("rel");
	$.post("/index.php/App/change_page_icon",{'ax_apps_id':ax_apps_id,'my_page_numbers':app_id,'icon':my_page_icon,'icon_white_url':icon_white_url},function(data){         $("#edit_"+app_id+" .iconImage img").attr("src",$(obj).attr("sel"));
		 $("#edit_"+app_id+" .icon_select").slideUp('fast',function(){ set_page_icon_out(false);setTimeout(function(){ $("#edit_"+app_id+" .editor_link_change_icon").removeClass("page_icon_expanded"); },400); }); 
		 $("#frmLiveView").contents().find("#device_view_nav_"+app_id+" .navigation_bar_item_bubble img").attr("src",$(obj).attr("rel"));
    },'json');
}

function content_add_feed(app_id){
				var feed_num=0;
				$("#edit_"+app_id+" .pageContent .items .edit_zone_input_row").each(function(n,e){
					 if( parseInt(feed_num) < parseInt( $(e).attr("name") ) ){
						feed_num = parseInt( $(e).attr("name") );	 
					 }
				 });
				 feed_num=feed_num+1;
				 if( parseInt( $("#edit_"+app_id+" .pageContent .items .edit_zone_input_row").size())==3 ){
					$("#edit_"+app_id+" .pageContent .editor_add_row div").addClass("add_disabled"); 
				 }
				//限制添加个数
				var app_name = $("#edit_"+app_id+" .iconText input").val();
				if( parseInt( $("#edit_"+app_id+" .pageContent .items .edit_zone_input_row").size() ) <=3 ){
					 $.post("/index.php/App/get_feed", { "app_id": app_id,"app_name":app_name,"feed_num":feed_num },
							   function(data){
									$("#edit_"+app_id+" .pageContent .editor_frame .items").append(data);   
								});			 	
				} 	 
}

function delete_feed(app_id,app_name,feed_num){
	var ax_apps_id = $("#ax_apps_id").val();
	app_name = $("#your_pages_container_inner #"+app_id+" .page_title").html();
	$.post("/index.php/App/get_feed_dialog", { "app_id": app_id,"app_name":app_name,feed_num:feed_num },
				   function(data){
						$("body").append(data);
						$(".ui-widget-overlay").show();
					});					
}

function content_cancel_feed(app_id){
		$(".ui-widget-overlay").hide();   
		$("#dia_feed_"+app_id).remove();
}

function content_remove_feed(app_id,feed_num){
	var ax_apps_id = $("#ax_apps_id").val();
	$(".ui-widget-overlay").hide(); 
	$("#dia_feed_"+app_id).remove();
	$("#edit_"+app_id+" #feed_row_"+feed_num).remove();
	$("#edit_"+app_id+" .pageContent .editor_add_row div").removeClass("add_disabled"); 
	//改变iframe中的feed信息
	$("#frmLiveView").contents().find("#device_view_content_"+app_id+" .scroll_wrapper .page-tabs-view td").each(function(n,e){
		   if(feed_num==$(e).attr("name")) $(e).remove();
		   $(e).find("span").removeClass("selected");
	});
	$("#frmLiveView").contents().find("#device_view_content_"+app_id+" .scroll_wrapper .page-tabs-view td").first().find("span").addClass("selected");
	$.post("/index.php/App/delete_account",{'ax_apps_id':ax_apps_id,'my_pages_numbers':app_id,'number_id':feed_num},function(data){
	     	
	},'json');
}

//tab content page Background upload dialog ui
function upload_page_background(my_page_numbers){
	var ax_apps_id = $("#ax_apps_id").val();
	$.post("/index.php/App/get_upload_bg", { "my_page_numbers": my_page_numbers,"ax_apps_id":ax_apps_id },
				   function(data){
						$("body").append(data);
						$(".ui-widget-overlay").show();
					});
	return false;				
}

function close_upload_bg(app_id){
	$(".ui-widget-overlay").hide();
	$("#upload_bg_"+app_id).remove();
	$("#crop_upload_bg_"+app_id).remove();
	return false;
}

$(function(){
    
   
      $("#allDevices div").click(function(){
	     if( $(this).hasClass('android') ){
		       $("#allDevices div").each(function(n,e){ if(!$(e).hasClass('android')){ $(e).removeClass('device_selected'); } });
			   $(this).addClass("device_selected");	
			  
			   $("#Device").removeClass().addClass("Android").hide().fadeOut('fast').fadeIn('fast').show();
			   $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/NexusGalaxy.png");
			  
		 }else if($(this).hasClass('iphone')){			 
			   $("#allDevices div").each(function(n,e){ if(!$(e).hasClass('iphone')){ $(e).removeClass('device_selected'); } });
			   $(this).addClass("device_selected");	
			   
			   var witchDevice = "";
			   $("#allDevices .iphone  span").each(function(n,e){
				    if($(e).attr("class")=="active"){
						witchDevice = $(e).attr("target");
						return false;
					} 
			   });
			   if(witchDevice=="" || witchDevice=="iPhone"){
				    $("#Device").removeClass().addClass("iPhone").hide().fadeOut('fast').fadeIn('fast').show();
			        $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/iPhone.png");
			   }else if(witchDevice=="iPhone5"){
				    $("#Device").removeClass().addClass("iPhone").hide().fadeOut('fast').fadeIn('fast').show();
			        $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/iPhone5.png");
			  }
			   
			   
			   
		 }else if($(this).hasClass('html5')){
			   $("#allDevices div").each(function(n,e){ if(!$(e).hasClass('html5')){ $(e).removeClass('device_selected'); } });
			   $(this).addClass("device_selected");	
			   
			    var witchDevice = "";
			   $("#allDevices .html5 span").each(function(n,e){
				    if($(e).attr("class")=="active"){
						witchDevice = $(e).attr("target");
						return false;
					} 
			   });
			   if(witchDevice=="" || witchDevice=="iPhone"){
				    $("#Device").removeClass().addClass("iPhone HTML5").hide().fadeOut('fast').fadeIn('fast').show();
			        $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/iPhone.png");
			   }else if(witchDevice=="Android"){
				    $("#Device").removeClass().addClass("Android HTML5").hide().fadeOut('fast').fadeIn('fast').show();
			        $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/NexusGalaxy.png");
			  }
 
			  
		 }
   });	
   
   
   $("#allDevices .iphone span").click(function(){
	     var txt_target = $(this).attr("target");
		 $("#allDevices .iphone  span").each(function(n,e){
				    if($(e).attr("target")==txt_target){
						$(e).addClass("active");
					}else{
					    $(e).removeClass("active");	
					}
					
		 });
		 
		 $("#allDevices div").each(function(n,e){ if(!$(e).hasClass('iphone')){ $(e).removeClass('device_selected'); } });
		 $(this).addClass("device_selected");
		 
		 if(txt_target=="iPhone"){
			  $("#Device").removeClass().addClass("iPhone").hide().fadeOut('fast').fadeIn('fast').show();
			  $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/iPhone.png");
		 }else if(txt_target=="iPhone5"){
			  $("#Device").removeClass().addClass("iPhone5").hide().fadeOut('fast').fadeIn('fast').show();
			  $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/iPhone5.png");
		 }else if(txt_target=="iPad"){
		      alert('dialog');	 
		 }	    
   });
   
   $("#allDevices .android div").click(function(){
			 $("#Device").removeClass().addClass("Android").hide().fadeOut('fast').fadeIn('fast').show();
			 $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/NexusGalaxy.png");   
   });
   
   
   $("#allDevices .html5 span").click(function(){
	     var txt_target = $(this).attr("target");
		 $("#allDevices .html5  span").each(function(n,e){
				    if($(e).attr("target")==txt_target){
						$(e).addClass("active");
					}else{
					    $(e).removeClass("active");	
					}
					
		 });
		 
		  
		 if(txt_target=="iPhone"){
			  $("#Device").removeClass().addClass("iPhone HTML5").hide().fadeOut('fast').fadeIn('fast').show();
			  $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/iPhone.png");
		 }else if(txt_target=="Android"){
			  $("#Device").removeClass().addClass("Android HTML5").hide().fadeOut('fast').fadeIn('fast').show();
		      $("#Device .container").css("background-image","/storage.conduit.com/images/mobile/controlpanel/sprites/previewarea/NexusGalaxy.png");
		 }    
   });
   
});


var icon_crop_numbers;
//tab content custom icon upload file
function content_upload_icon(my_page_numbers){
	 icon_crop_numbers = my_page_numbers;
	 $("#edit_"+my_page_numbers+" .editor_icon_left .iconImage img").hide();
	 $("#edit_"+my_page_numbers+" .editor_icon_left .loading").show();
	  var options = {
		  success: function(txt) {
					txt = eval('('+txt+')');
					if(txt.code==1){
						   $(".ui-widget-overlay").show();
						   var ax_apps_id = $("#ax_apps_id").val();
						   $.post("/index.php/App/get_content_crop_icon",{'page_numbers':my_page_numbers,'ax_apps_id':ax_apps_id},function(data){
							     $("body").append( data );  
								 $("#icon_crop_edit_"+my_page_numbers).css("top",30);
	                       $("#icon_crop_edit_"+my_page_numbers).css("left",($(window).width() - $('#icon_crop_edit_'+my_page_numbers).outerWidth())/2 );	
						   $("#icon_crop_edit_"+my_page_numbers).show();
						    var tmpDate = new Date(); 
							set_UP_W_H(txt.data['picwidth'],txt.data['picheight']);
							var defautlv = ( txt.data['picwidth'] > txt.data['picheight']) ?txt.data['picheight']:txt.data['picwidth'];
							$("#icon_crop_edit_"+my_page_numbers+" #postImageUploadCropArea img").attr("src",txt.data['picurl']+'?t='+ tmpDate.getTime());
							$("#icon_crop_edit_"+my_page_numbers+" input[name=picurl]").attr('value',txt.data['picurl']);
							$("#icon_crop_edit_"+my_page_numbers+" #photo_big").attr('src',txt.data['picurl']+'?t='+ tmpDate.getTime());
							content_icon_imgrs = $('#icon_crop_edit_'+my_page_numbers+' #postImageUploadCropArea img').imgAreaSelect({ 
										x1: 245, 
										y1: 180,
										x2: 359, 
										y2: 294, 
										handles: true,
										onInit:content_icon_preview,
										onSelectChange:content_icon_preview,
										onSelectEnd:content_icon_onSelectEnd,
										aspectRatio: '1:1',
										instance: true,
										resizable:false,
										maxWidth:114,
										maxHeight:114,
										minWidth:114,
										minHeight:114,
										parent:$('#icon_crop_edit_'+my_page_numbers+' #postImageUploadCropArea')
										}); 
						   });
						   
						   
					}else{
					   alert(txt.message);
					}
			    }
	 };
	 $("#form_uploadpic_"+my_page_numbers).ajaxSubmit(options);
	 return false;	
}

// tab content icon crop save event
function content_icon_crop_save(numbers){
	 $("#icon_crop_edit_"+numbers+" #postImageUplaodLoading img").show();
	 $("#icon_crop_edit_"+numbers+" #btnSaveImagePostUpload").addClass("upload_btn_disabled").attr("disabled","disabled");
	 var options = {
			    success: function(txt) {
					txt = eval('('+txt+')');
					if(txt.code==1){
						  $("#icon_crop_edit_"+numbers).remove();
						  var pic_url = txt.pic_url;
						  $("#edit_"+numbers+" .editor_icon_left .iconImage img").attr('src',pic_url).show();
						  $("#frmLiveView").contents().find("#device_view_nav_"+numbers+" img").attr('src',pic_url);
						  $("#edit_"+numbers+" .editor_icon_left .loading").hide();
						  $(".ui-widget-overlay").hide();
						  $.post("/index.php/App/get_custom_content_icon",{'my_page_numbers':numbers,'pic_url':pic_url},function(data){
						        $("#edit_"+numbers+" .editor_icon_left .custom  .icon_select_list").append(data);	
						  });	
						  				  
					}else if(txt.code==0){
						  alert(txt.message);						  
					}
					$("#icon_crop_edit_"+numbers+" #postImageUplaodLoading img").hide();
			    }
		   };		
		   $('#icon_crop_edit_'+numbers+' #infouploadpic_crop').ajaxSubmit(options);
	       return false;	    
}

//tab content custom icon select.
function select_custom_icon_content(numbers,pic_url){
	 var ax_apps_id = $("#ax_apps_id").val();
	 $.post("/index.php/App/change_content_icon_custom",{'my_page_numbers':numbers,'pic_url':pic_url,'ax_apps_id':ax_apps_id},function(data){
		   if(data.code==1){
			     $("#frmLiveView").contents().find("#template_view_more").hide();
				 var ax_apps_id = $("#ax_apps_id").val();
				 $("#edit_"+numbers+" .editor_icon_left .iconImage img").attr('src',pic_url);
				 $("#frmLiveView").contents().find("#device_view_nav_"+numbers+" img").attr('src',pic_url); 
				 $("#edit_"+numbers+" .icon_select").slideUp('fast',function(){ set_page_icon_out(false);setTimeout(function(){ $("#edit_"+numbers+" .editor_link_change_icon").removeClass("page_icon_expanded"); },400); }); 
				 $("#edit_"+numbers+" .upload_custom_icon_wrapper").hide();	
				 $("#edit_"+numbers+" .icon_select .icon_select_tabs_buttons a").each(function(n,e){
						  if( $(e).attr("data")=='custom' ){
							  $(e).removeClass("active");  
						  } 
					 });   
		   }else{
			      alert(data.message);   
		   }
	 },'json');
}

//close content tab crop dialog
function close_content_crop_dialog(numbers){
	 $("#icon_crop_edit_"+numbers).remove();
	 $("#edit_"+numbers+" .editor_icon_left .iconImage img").show();
	 $("#edit_"+numbers+" .editor_icon_left .loading").hide();
	 $(".ui-widget-overlay").hide();
}


//上传 tab info icon
var content_icon_imgrs;
function content_icon_onSelectEnd(img,selection){
	$('#icon_crop_edit_'+icon_crop_numbers+' input[name=x1]').val(selection.x1);
	$('#icon_crop_edit_'+icon_crop_numbers+' input[name=y1]').val(selection.y1);
	$('#icon_crop_edit_'+icon_crop_numbers+' input[name=x2]').val(selection.x2);
	$('#icon_crop_edit_'+icon_crop_numbers+' input[name=y2]').val(selection.y2); 
	$('#icon_crop_edit_'+icon_crop_numbers+' input[name=w]').val(selection.width); 
	$('#icon_crop_edit_'+icon_crop_numbers+' input[name=h]').val(selection.height); 
}	
function content_icon_preview(img, selection) {
	content_icon_onSelectEnd(img,selection);
	var big_scaleX = 50 / (selection.width || 1);
	var big_scaleY = 50 / (selection.height || 1);
	$('#icon_crop_edit_'+icon_crop_numbers+' #photo_big').css({
		width: Math.round(big_scaleX * up_pic_width) + 'px',
	    height: Math.round(big_scaleY * up_pic_height) + 'px',
		marginLeft: '-' + Math.round(big_scaleX * selection.x1) + 'px',
		marginTop: '-' + Math.round(big_scaleY * selection.y1) + 'px'
	});
}		