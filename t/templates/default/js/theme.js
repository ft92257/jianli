$(document).ready(function(){
    $('#colorpicker').farbtastic('#color-background');
    $("#color-background").click(function() {
        $('#colorpicker').farbtastic('#color-background');
        closealert();
    });
    $("#color-text").click(function() {
        $('#colorpicker').farbtastic('#color-text');
        closealert();
    });
    $("#color-links").click(function() {
        $('#colorpicker').farbtastic('#color-links');
        closealert();
    });
	
    $("#color-background").val(theme_bg_color);
    $("#color-background").css("background-color",theme_bg_color);
    $("#color-text").val(theme_text_color);
    $("#color-text").css("background-color",theme_text_color);
    $("#color-links").val(theme_link_color);
    $("#color-links").css("background-color",theme_link_color);
	

    $("#cencel").click(function() {
        window.location.href=thisSiteURL;
    });
    $("#save").click(function() {
        top.window.onbeforeunload="";
    });
    setpic();
});

function setpic() {
//    $("#setbgyes").click(function() {
//        var t=$("#color-background").val();
//        var u=this.style.backgroundImage.replace("_preview","");
//        $("body").css("background",t+" "+ u);
//		$("#user-background-repeat").attr("checked", true); 
//		
//		$("#user-background-fixed").attr("checked", true);
//		
//		$("#theme_bg_image").val(u);
//		
//        closealert();
//    });
    $("#setbgno").click(function() {
        $("body").css("background",$("#color-background").val());
        $("#theme_bg_image").val("");
        closealert();
    });
    $("#setmybgimage").click(function() {
    	var t=$("#color-background").val();
    	var u=$("#mybgimage").attr("src");
    	$("body").css("background",t+" ur" + "l("+ u +")");
    	$("#theme_bg_image").val(u);
    	closealert();
    });
}

function usertheme(obj) {
    var ele= obj.split(",");
    $("#color-background").val(ele[0]);
    $("#color-background").css("background-color",ele[0]);
    $("#color-text").val(ele[1]);
    $("#color-text").css("background-color",ele[1]);
    $("#color-links").val(ele[2]);
    $("#color-links").css("background-color",ele[2]);

    $("body").css("backgroundColor",ele[0]);
    $("body").css("color",ele[1]);
    $("a").css("color",ele[2]);
	
     var mybgSET=$("#mybgimage").attr("src"); 

        $("#themeimages").html('<p><a href="javascript:void(0);" id="setbgyes" class="yesbg" style="" title="已使用的背景图片"></a><a href="javascript:void(0);" class="nobg" id="setbgno" title="不使用背景图片"><img src="templates/default/images/theme_nobg.gif"></a></p><p><dl class="setii"><dt>设置背景图</dt><dd><label for="user-background-left"><input id="user-background-left" onclick="leftclick()" name="theme_bg_image_type" type="radio" value="left"> 左对齐</label>&nbsp;&nbsp;<label for="user-background-center"><input id="user-background-center" onclick="centerclick()" name="theme_bg_image_type" type="radio" value="center"> 居中</label>&nbsp;&nbsp;<label for="user-background-right"><input id="user-background-right" onclick="rightclick()" name="theme_bg_image_type" type="radio" value="right"> 右对齐</label></dd></dd><label for="user-background-repeat"><input id="user-background-repeat" onclick="repeatclick()" name="theme_bg_image_type" type="checkbox" value="repeat"> 背景平铺</label>&nbsp;&nbsp;<label for="user-background-fixed"><input id="user-background-fixed" onclick="fixedclick()" name="theme_bg_image_type" type="checkbox" value="fixed"> 背景固定</label>&nbsp;&nbsp;</dd></dl></p>');
        setpic();
        $("#setbgyes").css("backgroundImage","url("+thisSiteURL+"theme/"+ele[3]+"/themebg_preview.jpg)");
        $("#user-background-"+ele[4]).attr("checked",true);
        if (ele[4]=="repeat") {
            $("body").css("background",ele[0]+" url("+thisSiteURL+"theme/"+ele[3]+"/images/themebg.jpg) repeat scroll left top");
        } else if (ele[4]=="center"){
            $("body").css("background",ele[0]+" url("+thisSiteURL+"theme/"+ele[3]+"/images/themebg.jpg) no-repeat scroll center top");
        } else if (ele[4]=="left"){
            $("body").css("background",ele[0]+" url("+thisSiteURL+"theme/"+ele[3]+"/images/themebg.jpg) no-repeat scroll left top");
        } else if (ele[4]=="bottom"){
        	$("body").css("background",ele[0]+" url("+thisSiteURL+"theme/"+ele[3]+"/images/themebg.jpg) no-repeat scroll bottom top");
        }
    if (mybgSET==""){ $("#setmybgimage").remove();};
    $("#theme_id").val(ele[3]);
    $("#theme_bg_image").val("");
    closealert();
}


function repeatclick() {
    $("body").css("background-repeat","repeat"); 
    closealert();
}

function fixedclick() {
    $("body").css("background-attachment","fixed"); 
    closealert();
}

function leftclick() {
    var t=$("#color-background").val();
    var u=document.getElementById("setbgyes").style.backgroundImage.replace("_preview","");
    $("body").css("background",t+" "+ u +" no-repeat left top");
    closealert();
}

function centerclick() {
    var t=$("#color-background").val();
    var u=document.getElementById("setbgyes").style.backgroundImage.replace("_preview","");
    $("body").css("background",t+" "+ u +" no-repeat center top");
    closealert();
}

function rightclick() {
    var t=$("#color-background").val();
    var u=document.getElementById("setbgyes").style.backgroundImage.replace("_preview","");
    $("body").css("background",t+" "+ u +" no-repeat right top");
    closealert();
}

function closealert() {
    top.window.onbeforeunload = function(){return "如果确定要离开本页，那么你将丢失当前的操作！";}
}

function alert_check(){
var r=confirm("此操作将使没有自定义界面的用户默认使用此界面样式!");
if (r==true)
  {
  alert("确定");
  }
else
  {
  alert("取消");
  }
}