<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="__STATICS__/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="__STATICS__/js/common.js"></script>

<title><?php echo ($Title); ?> | 易监理 - 装修行业中代表良心的力量，家装监理，连锁店装修监理，别墅装修监理，别墅监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
<meta name="keywords" content="易监理,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
<meta name="description" content="易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
<meta charset="utf-8">
<!–[if IE7 and IE10]>
<style>
 .topnav li{ float:left; cursor:pointer}
 .topnav li a{ display:block; cursor:pointer}
</style>
<![endif]–>
</head>
<body>
<iframe src="__HOST__/yjlnew.php" width="100%" height="135" frameborder=0 scrolling="no" target="_top" ></iframe>
<script>
	var URL = '__URL__';
	var GROUP = '__GROUP__';
</script>

<div style="width:980px;margin:auto;">
<h2><?php echo ($title); ?></h2>
<style>
	.pieTabs{
		line-height:26px;
	}
	.pieTabs a {
		color:#0000FF;
	}
</style>
<div style="width:330px;float:left;">
	<span class="pieTabs">
		<a href="#" id="tab_forecast" style="color:red;">预计费用</a> | <a href="#" id="tab_my">我的预算</a> | <a href="#" id="tab_real">实际费用</a>
	</span>
	<img style="float:left" id="pie_image" src="<?php echo U('softpie');?>" />
	<div style="float:left;margin-left:10px;line-height:28px;">
	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?><span style="color:#ffffff;background:<?php echo ($vo[1]); ?>;"><?php echo ($vo[0]); ?></span> <span id="scale_<?php echo ($field); ?>"></span>% <br><?php endforeach; endif; ?>
	</div>
</div>
<div style="650px;float:left;">
建筑面积：<input type="text" id="acreage" name="acreage" value="<?php echo ($acreage); ?>" />㎡<br>
设置预算：<input type="text" onblur="setBudget(this)" id="budget" name="budget" value="<?php echo ($hard_budget["total"]); ?>" />元<br>
选择档次：<select name="grade" id="grade" onchange="selectGrade(this)"><option value="0">请选择档次</option><option value="3">高档</option><option value="2">中档</option><option value="1">低档</option></select><br>
<button type="button" onclick="calculate()">预估费用</button><br><br>
</div>

<div style="float:left;width:980px;border-top:1px dashed #999;padding-top:10px;margin-top:5px;">
<form method="post" action="">
<table>
	<tr><td width="100">名称</td><td width="100">预计费用</td><td width="200">我的预算</td><td width="100">档次</td><td width="100">实际已花费</td></tr>
	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?><tr><td><?php echo ($vo[0]); ?></td><td><span id="fee_<?php echo ($field); ?>">0.00</span>元</td><td><input type="text" id="<?php echo ($field); ?>" name="<?php echo ($field); ?>" value="<?php echo ($hard_budget[$field]); ?>" />元</td><td id="grade_<?php echo ($field); ?>"><span id="grade_<?php echo ($field); ?>_3">高</span> <span id="grade_<?php echo ($field); ?>_2">中</span> <span id="grade_<?php echo ($field); ?>_1">低</span></td><td><span id="real_<?php echo ($field); ?>">0.00</span>元</td></tr><?php endforeach; endif; ?>
	<tr><td colspan="5" style="border-top:1px solid #ccc;"></td></tr>
	<tr><td>总费用</td><td><span id="fee_total">0.00</span>元</td><td><input type="checkbox" id="synBudget" onclick="synCheck(this)" />同步我的预算</td><td></td><td>0.00元</td></tr>
</table>
<br>
<input type="submit" value="保 存" />
</form>
</div>

</div>
<script>

function calculate(){
	$.post(URL+'/softCalculate', {acreage:$('#acreage').val(), budget:$('#budget').val(), grade:$('#grade').val()}, function(json){
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

	$("#pie_image").attr("src", "<?php echo U('softpie');?>/data/" + strData);
		
}
</script>


</body>
</html>