<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>费用</title>
<link type="text/css" rel="stylesheet" href="__STATICS__/css/screen.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/layer/skin/layer.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/css/main.css" />
<script type="text/javascript" src="__STATICS__/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="__STATICS__/js/common.js"></script>
<!--[if lt ie 9]>
<script type="text/javascript" src="__STATICS__/js/html5shiv.js"></script>
<![endif]-->
</head>
<body>
<script>
	var URL = '__URL__';
	var GROUP = '__GROUP__';
</script>

<div class="log-in w-41 log-in-show">
    	<p class="ts-tit clearfix ml-5 mr-6-5 mt-2"><span class="left f-s">登录</span><span class="left mt-1-5 ml-2">没有账号？<a href="#" class="register-to">请注册</a></span></p>
        <div class="from-box ml-5 mb-3-5">
        	<label class="l-bg mt-2"><input type="text" value="用户名" /></label>
            <label class="l-bg mt-1"><input type="text" value="密码" /></label>
            <div class="clearfix mt-1">
            	<label class="l-bg-yz left"><input type="text" value="验证码" /></label>
                <img class="left ml-1" src="__STATICS__/images/yz-img.jpg" width="68" height="34" />
                <a href="#" class="left ml-1 mt-0-5"><img src="__STATICS__/images/hyzimg.jpg" width="108" height="27" /></a>
            </div>
            <input type="submit" class="sub-mit" value="登&nbsp;&nbsp;录" />
        </div>
    </div>
    <div class="log-in w-41 register-show">
    	<p class="ts-tit clearfix ml-5 mr-6-5 mt-2"><span class="left f-s">注册</span><span class="left mt-1-5 ml-2">已注册？<a href="#" class="log-in-to">请登录</a></span></p>
        <div class="from-box ml-5 mb-3-5">
        	<label class="l-bg mt-2"><input type="text" value="用户名" /></label>
            <label class="l-bg mt-1"><input type="text" value="密码" /></label>
            <label class="l-bg mt-1"><input type="text" value="确认密码" /></label>
            <div class="clearfix mt-1">
            	<label class="l-bg-yz-zc left"><input type="text" value="验证码" /></label>
                <!--<a href="#" class="left ml-1 mt-0-5"><img src="images/hq-yz.jpg" width="87" height="27" /></a>-->
                <input type="button" id="btn" value="获取验证码" style="border:#E4E4E3 1px solid; background-color:#EFEFEF; height:25px; line-height:25px; width:90px; color:#434446; margin-top:5px; margin-left:15px" />
            </div>
            <input type="submit" class="sub-mit" value="注&nbsp;&nbsp;册" />
        </div>
    </div>
	<header>
    	<nav class="w-96 mga">
        	<ul class="clearfix ml-7-5">
            	<li>
                	<a href="#" class="bg-col-sty"><span class="nav-ico ico1"></span><span>档案</span></a>
                </li>
                <li>
                	<a href="#"><span class="nav-ico ico2"></span><span>知识库</span></a>
                </li>
                <li>
                	<a href="#"><span class="nav-ico ico3"></span><span>帮助</span></a>
                </li>
                <li>
                	<a href="#"><span class="nav-ico ico4"></span><span>备忘录</span></a>
                </li>
                <li>
                	<a href="javascript:;" class="logIn"><span class="nav-ico ico5"></span><span>登录</span></a>
                </li>
            </ul>
        </nav>
    </header>
<script language="javascript" type="text/javascript" src="__STATICS__/My97DatePicker/WdatePicker.js"></script>
<!-- JQ formVerification类库 -->
<link rel="stylesheet" href="__STATICS__/jquery-form/css/validationEngine.jquery.css" type="text/css"/>
<link rel="stylesheet" href="__STATICS__/jquery-form/css/template.css" type="text/css"/>
<script src="__STATICS__/jquery-form/js/languages/jquery.validationEngine-zh_CN.js" type="text/javascript" charset="utf-8"></script>
<script src="__STATICS__/jquery-form/js/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>
    <section class="w-96 mgl-a mgr-a content clearfix">
    	<form action="" method="post">
    	<div class="left w737 clearfix mt-5">
        	<div class="ml-10">
            	<h1 class="inform-t ml-1-5">业主</h1>
                <div class="fromdiv">
                	<div class="clearfix mt-1-5">
                    	<label>真实姓名：</label>
                        <input type="text" class="text-bg1 validate[required]" name="realname" />
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>手机号：</label>
                        <input type="text" class="text-bg1 validate[required]" name="mobile" />
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>小区名称：</label>
                        <input type="text" class="text-bg1 validate[required]" name="community" />
                    </div>
                    <div class="clearfix mt-1-5" >
                    	<label>地址：</label>
                       <div id="div_city"></div>
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label></label>
                        <input type="text" class="text-bg1 validate[required]" value="输入详细地址"  name="address"/>
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>房屋类型：</label>
                        <div id="div_house_type"></div>
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>装修风格：</label>
                        <select class="select2 validate[required]" name="style">
                        	<option value="">请选择</option>
                        	<?php if(is_array($style)): foreach($style as $i=>$vo): ?><option value="<?php echo ($i); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                <h1 class="inform-t mt-4 ml-1-5">施工</h1>
                <div class="fromdiv">
                	<div class="clearfix mt-1-5">
                    	<label>预算：</label>
                        <select name="budget" class="select1 validate[required]">
                        	<option value="">请选择</option>
					    	<?php if(is_array($budget)): foreach($budget as $i=>$vo): ?><option value="<?php echo ($i); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; ?>
                        </select>
                        <input type="text" name="budget_amount" class="text-bg2 validate[required]" value=""/>
                    </div>
                	<div class="clearfix mt-1-5">
                    	<label>施工面积：</label>
                        <input type="text" name="area" class="text-bg1 validate[required]" value=""/>平方米
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>预计开工时间：</label>
                        <input id="begin_date" class="text-bg1 validate[required]" name="begin_date" type="text" onClick="WdatePicker()">
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>工期：</label>
                        <input type="text" name="cycle" class="text-bg1 validate[required]" value="" />天
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>施工队长：</label>
                       <input type="text" name="contractor" class="text-bg1 validate[required]" value=""/>
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>项目经理：</label>
                        <input type="text" name="PM" class="text-bg1 validate[required]" value=""/>
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>房屋状态：</label>
                        <select name="house_status" class="select2 validate[required]">
                        	<option value="">请选择</option>
					    	<?php if(is_array($house_status)): foreach($house_status as $i=>$vo): ?><option value="<?php echo ($i); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="clearfix mt-1-5">
                    	<label>装修种类：</label>
                        <select name="renovation_type" class="select2 validate[required]">
                        	<option value="">请选择</option>
					    	<?php if(is_array($renovation_type)): foreach($renovation_type as $i=>$vo): ?><option value="<?php echo ($i); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; ?>
                        </select>
                    </div>
                    <ul class="jd-list w-28 mt-4 ml-3 clearfix">
				    <?php if(is_array($node)): foreach($node as $i=>$vo): ?><li><input type="checkbox" checked="checked" name="node[]" value="<?php echo ($i); ?>"/><span><?php echo ($vo); ?></span></li><?php endforeach; endif; ?>
				    </ul>
                    <div class="ml-2 mt-2 pb-2">
                    	<input type="submit" class="btn-tj" value="提&nbsp;&nbsp;&nbsp;交" />
                        <input type="reset" class="btn-tj" value="重&nbsp;&nbsp;&nbsp;置" />
                    </div>
                </div>
            </div>
        </div>
        </form>
        <div class="right w223">
        	<ul class="r-nav ml-3 mt-4-5">
            	<li><a href="#" class="ico1">通讯录</a></li>
                <li><a href="#" class="ico2">专家在线</a></li>
                <li><a href="#" class="ico3">设计在线</a></li>
                <li><a href="#" class="ico4">监理在线</a></li>
                <li><a href="#" class="ico5">商城</a></li>
            </ul>
        </div>
    </section>
</body>
</html>

<script>
$(function(){
/*
	$("form").validationEngine('attach', {
		onValidationComplete: function(form, status){
			if (status){
				form.submit();
			}
		}  
	});*/
	cityAjax('city');
	house_typeAjax('house_type');
});
/*省市区列表联动*/
function cityAjax(act,json){
	$.ajax({
		type:'post',
		url:'__GROUP__/City/citySelect',
		async:false,
		data:{'data':json,'act':act},
		success:function(r){
			$("#div_"+act).append(r);
		}
	});
}
function cityRemove(obj){
	if(obj.attr('id') == 'province'){
		$("#city").remove();
		$("#county").remove();
	}else if(obj.attr('id') == 'city'){
		$("#county").remove();	
	}
}
/*房型户型列表联动*/
function house_typeAjax(act,json){
	$.ajax({
		type:'post',
		url:'__GROUP__/House_type/house_typeSelect',
		async:false,
		data:{'data':json,'act':act},
		success:function(r){
			$("#div_"+act).append(r);
		}
	});
}
function house_typeRemove(obj){
	if(obj.attr('id') == 'room'){
		$("#apartment").remove();
	}
}

</script>