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

<div style="width:980px;margin:auto;">
<h2><?php echo ($title); ?></h2>
<style>
	.pieTabs{
		line-height:26px;width:330px;float:left;
	}
	.pieTabs a {
		color:#0000FF;
	}
</style>
<div style="width:330px;float:left;">
	<span class="pieTabs">
		<a href="#" id="tab_forecast" style="color:red;">预计费用</a> | <a href="#" id="tab_my">我的预算</a> <!--| <a href="#" id="tab_real">实际费用</a>-->
	</span>
	<img style="float:left" id="pie_image" src="__URL__/childpie/type/<?php echo ($type); ?>" />
	<div style="float:left;margin-left:10px;line-height:28px;">
	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?><span style="color:#ffffff;background:<?php echo ($vo[1]); ?>;"><?php echo ($vo[0]); ?></span> <span id="scale_<?php echo ($field); ?>"></span>% <br><?php endforeach; endif; ?>
	</div>
</div>
<div style="650px;float:left;">
实际费用：<?php echo ($_GET['realfee']); ?> 元<br>
设置预算：<input type="text" onblur="setBudget(this)" id="budget" name="budget" value="<?php echo ($budget["total"]); ?>" />元<br>
选择档次：<select name="grade" id="grade" onchange="selectGrade(this)"><option value="0">请选择档次</option><option value="3">高档</option><option value="2">中档</option><option value="1">低档</option></select><br>
<button type="button" onclick="calculate()">预估费用</button><br><br>
</div>

<div style="float:left;width:980px;border-top:1px dashed #999;padding-top:10px;margin-top:5px;">
<form method="post" action="">
<table>
	<tr><td width="150">名称</td><td width="150">预计费用</td><td width="200">我的预算</td><td width="100">档次</td><!--<td width="100">实际已花费</td>--></tr>
	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?><tr><td> <!--<a href="__GROUP__/Realfee/index/type/<?php echo ($field); ?>"></a>--><?php echo ($vo[0]); ?></td><td><span id="fee_<?php echo ($field); ?>">0.00</span>元</td><td><input type="text" id="<?php echo ($field); ?>" name="<?php echo ($field); ?>" value="<?php echo ($budget[$field]); ?>" />元</td><td id="grade_<?php echo ($field); ?>"><span id="grade_<?php echo ($field); ?>_3">高</span> <span id="grade_<?php echo ($field); ?>_2">中</span> <span id="grade_<?php echo ($field); ?>_1">低</span></td><!--<td><span id="real_<?php echo ($field); ?>">0.00</span>元</td>--></tr><?php endforeach; endif; ?>
	<tr><td colspan="4" style="border-top:1px solid #ccc;"></td></tr>
	<tr><td>总费用</td><td><span id="fee_total">0.00</span>元</td><td><input type="checkbox" id="synBudget" onclick="synCheck(this)" />同步我的预算</td><td></td><!--<td>0.00元</td>--></tr>
</table>
<br>
<input type="submit" value="保 存" />
</form>
</div>

</div>
<script>

function calculate(){
	$.post(URL+'/childCalculate/type/<?php echo ($type); ?>', {budget:$('#budget').val(), grade:$('#grade').val()}, function(json){
		if (json.status == 0) {
			var data = json.data;
			var oGrade = {"1":"低", "2":"中", "3":"高"};
			var total = 0;
		<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?>$("#fee_<?php echo ($field); ?>").html(data.<?php echo ($field); ?>);
			if ($("#synBudget")[0].checked) {
				$("#<?php echo ($field); ?>").val(data.<?php echo ($field); ?>);
			}
			$("#grade_<?php echo ($field); ?>").html(oGrade[data.grade.<?php echo ($field); ?>]);

			total += parseInt(data.<?php echo ($field); ?>);<?php endforeach; endif; ?> 
			$("#fee_total").html(total);

			_updatePie(data);

			$(".pieTabs a").css("color", 'blue');
			$("#tab_forecast").css("color", 'red');
		} else {
			alert(json.msg);
		}
	}, 'json');
}

function selectGrade(obj) {
	if (obj.value) {
		$("#budget").val('');
		calculate();
	}
}

function setBudget(obj) {
	if (obj.value) {
		$("#grade").val(0);
	}
}

function synCheck(obj) {
	if (obj.checked) {
		<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?>$("#<?php echo ($field); ?>").val($("#fee_<?php echo ($field); ?>").html());<?php endforeach; endif; ?> 
	}
}

$(function(){
	calculate();
	$(".pieTabs a").click(function(){
		$(".pieTabs a").css("color", 'blue');
		$(this).css("color", 'red');
	});
	$("#tab_forecast").click(function(){
		_updatePie(getHtmlData('fee_'));
	});
	$("#tab_my").click(function(){
		_updatePie(getValData(''));
	});
	$("#tab_real").click(function(){
		_updatePie(getHtmlData('real_'));
	});
});

function getHtmlData(pre) {
	var data = {};
	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?>data.<?php echo ($field); ?> = $("#"+pre+"<?php echo ($field); ?>").html();<?php endforeach; endif; ?>
	
	return data;
}

function getValData(pre) {
	var data = {};
	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?>data.<?php echo ($field); ?> = $("#"+pre+"<?php echo ($field); ?>").val();<?php endforeach; endif; ?>
	
	return data;
}

function _updatePie(data) {
	var total = 0;
	var strData = '';
<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?>strData += data.<?php echo ($field); ?> + ",";
	total += parseInt(data.<?php echo ($field); ?>);<?php endforeach; endif; ?> 

	total = total / 100;
	
	var sum = 0;var last_field = '';
<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?>var scale_<?php echo ($field); ?> = Math.round(data.<?php echo ($field); ?>/total);
	sum += scale_<?php echo ($field); ?>;
	last_field = '<?php echo ($field); ?>';
	
	$("#scale_<?php echo ($field); ?>").html(scale_<?php echo ($field); ?>);<?php endforeach; endif; ?> 
	var last = parseInt($("#scale_" + last_field).html()) + 100 - sum;
	$("#scale_" + last_field).html(last);

	strData = strData.substr(0, strData.length - 1);

	$("#pie_image").attr("src", "<?php echo U('childpie');?>/type/<?php echo ($type); ?>/data/" + strData);
		
}
</script>


</body>
</html>