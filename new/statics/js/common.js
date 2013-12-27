/**
 * 全选checkbox,注意：标识checkbox id固定为为check_box
 * @param string name 列表check名称,如 uid[]
 */
function selectall(name) {
	if ($("#check_box").attr("checked")==false) {
		$("input[name='"+name+"']").each(function() {
			this.checked=false;
		});
	} else {
		$("input[name='"+name+"']").each(function() {
			this.checked=true;
		});
	}
}

//检测邮箱格式
function is_email(str){
	var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	return reg.test(str);
}
//设为首页
function SetHomePage(obj,url){
	try{
		obj.style.behavior='url(#default#homepage)';obj.setHomePage(url);
	}catch(e){
		if(window.netscape){
			try{
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");  
			}catch (e){ 
				return false;  
			}
			var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
			prefs.setCharPref('browser.startup.homepage',url);
		}
	}
}
//加入收藏
function AddFavorite(url, title){
   try{
       window.external.addFavorite(url, title);
   }catch (e){
       try{
           window.sidebar.addPanel(title, url, "");
       }catch (e){
           return false;
       }
   }
}
/* 火狐下取本地全路径 */
function getFullPath(obj)
{
    if(obj)
    {
        //ie
        if (window.navigator.userAgent.indexOf("MSIE")>=1)
        {
            obj.select();
            return document.selection.createRange().text;
        }
        //firefox
        else if(window.navigator.userAgent.indexOf("Firefox")>=1)
        {
            if(obj.files)
            {
                return obj.files.item(0).getAsDataURL();
            }
            return obj.value;
        }
        return obj.value;
    }
}

function redirect(url) {
	location.href = url;
}

function openimg(u, w, h){
	if($("#lightbox_bg").length==0){
		$("body").append('<div id="lightbox_img" style="width: '+w+'px;height: '+h+'px;"><img src="'+u+'" id="lightbox_i" width="'+w+'" height="'+h+'" alt="" title="点击关闭"/></div><div id="lightbox_bg"></div><div id="lightbox_tool" style="display: none;background: #fff;"><a href="#" onclick="roateimg(1);" title="向左转"><img src="images/blank.gif" width="19" height="16" style="background-position: 0 0;"/></a><a href="#" onclick="roateimg();" title="向右转"><img src="images/blank.gif" width="19" height="16" style="background-position: -18px 0;"/></a><a href="#" onclick="window.open($(\'#lightbox_i_u\').val());return false;" title="新窗口打开"><img src="images/blank.gif" width="16" height="16" style="background-position: -37px 0;"/></a><input type="hidden" id="lightbox_i_r" value="0"/><input type="hidden" id="lightbox_i_o" value="0"/><input type="hidden" id="lightbox_i_u" value="'+u+'"/><input type="hidden" id="lightbox_i_w" value="'+w+'"/><input type="hidden" id="lightbox_i_h" value="'+h+'"/></div>');
	}else{
		if($('#lightbox_i_u').val()!=u){
			$('#lightbox_img').html('<img src="'+u+'" id="lightbox_i" width="'+w+'" height="'+h+'" alt="" title="点击关闭"/>');
			$('#lightbox_i').css({'width':w+'px', 'height':h+'px'});
			$('#lightbox_img').css({'width':w+'px', 'height':h+'px'});
			$('#lightbox_i_r').val('0');
			$('#lightbox_i_u').val(u);
			$('#lightbox_i_w').val(w);
			$('#lightbox_i_h').val(h);
		}
	}
	$('#lightbox_i_o').val('1');
	var ri=parseInt($('#lightbox_i_r').val());
	var nw=parseInt($('#lightbox_i_w').val());
	var nh=parseInt($('#lightbox_i_h').val());
	if((ri%2)>0){
		nw=parseInt($('#lightbox_i_h').val());
		nh=parseInt($('#lightbox_i_w').val());
	}
	nw+=10;
	nh+=10;
	var dl=$(document).scrollLeft();
	var dt=$(document).scrollTop();
	var ww=$(window).width();
	var wh=$(window).height();
	var vw=$(document).width();
	var vh=$(document).height();
	var l=dl+(ww-nw)/2;
	if(l<0)l=0;
	var t=dt+(wh-nh)/2;
	if(t<0)t=0;
	if(nw>vw){
		vw=nw;
		l=0;
	}
	if(nh>vh){
		vh=nh;
		t=0;
	}
	if($.browser.msie){
		$("#lightbox_img").show();
		$("#lightbox_bg").show();
		$("#lightbox_bg").fadeTo(50, 0.5);
	}else{
		$("#lightbox_img").fadeIn(500);
		$("#lightbox_bg").fadeIn(500);
	}
	$("#lightbox_img").css({'top':t, 'left':l});
	$("#lightbox_bg").css({'width':vw+'px', 'height':vh+'px'});
	$("#lightbox_img").click(function(){
		$('#lightbox_tool').hide();
		$('#lightbox_i_o').val('0');
		$("#lightbox_bg").fadeOut(500);
		$("#lightbox_img").fadeOut(500);
	}).mouseover(function(){
		var p=$(this).offset();
		$('#lightbox_tool').css({'left':(p.left+5)+'px', 'top':(p.top+5)+'px'});
		$('#lightbox_tool').show();
	}).mouseout(function(){
		$('#lightbox_tool').hide();
	});
	$('#lightbox_tool').mouseover(function(){
		if($('#lightbox_i_o').val()=='1')$(this).show();
	}).mouseout(function(){
		$(this).hide();
	});
}

function roateimg(t){
	var w=parseInt($('#lightbox_i_w').val());
	var h=parseInt($('#lightbox_i_h').val());
	var ri=parseInt($('#lightbox_i_r').val());
	ri++;
	if(t){
		$("#lightbox_i").rotateLeft();
	}else{
		$("#lightbox_i").rotateRight();
	}
	var nw=w;
	var nh=h;
	if((ri%2)>0){
		nw=h;
		nh=w;
	}
	$('#lightbox_img').css({'width':nw+'px', 'height':nh+'px'});
	nw+=10;
	nh+=10;
	var dl=$(document).scrollLeft();
	var dt=$(document).scrollTop();
	var ww=$(window).width();
	var wh=$(window).height();
	var vw=$(document).width();
	var vh=$(document).height();
	var l=dl+(ww-nw)/2;
	if(l<0)l=0;
	var t=dt+(wh-nh)/2;
	if(t<0)t=0;
	if(nw>vw){
		vw=nw;
		l=0;
	}
	if(nh>vh){
		vh=nh;
		t=0;
	}
	$("#lightbox_img").css({'top':t, 'left':l});
	$("#lightbox_bg").css({'width':vw+'px', 'height':vh+'px'});
	$("#lightbox_img").mouseover();
	$('#lightbox_i_r').val(ri);
}

//验证手机号码
function checkTelephone(val)         
{        
   if (val.search(/^(1[358]\d{9})$/)!=-1) {  
	   return true;    
   } else {    
	   alert('手机号码格式不正确，请输入十一位数字！');
	   return false;
   }
} 

//姓名验证
function checkName(val)
{    
   if (val.search(/^[\u4e00-\u9fa5]{2,4}$/) !== -1) {
	   return true;    
   } else {    
	   alert('姓名格式不正确，请输入中文名字！');
	   return false;
   }
}

function checkForm() {
	var inputs = $("input[check]");
	var func;
	var val;
	var result;
	
	for (var i=0;i < inputs.length;i++) {
		func = $(inputs[i]).attr('check');
		val = $(inputs[i]).val();
		eval("result = " + func + "('"+ val +"')");
		if (!result) {
			inputs[i].focus();
			return false;
		}
	}
		
	return true;
}
