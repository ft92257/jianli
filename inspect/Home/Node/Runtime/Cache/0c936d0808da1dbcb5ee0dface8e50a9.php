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
 body{
 	margin:0px;
 }
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
		line-height:26px;width:330px;float:left;
	}
	.pieTabs a {
		color:#0000FF;
	}
</style>
<div style="width:330px;float:left;">
	<span class="pieTabs">
		<a href="javascript:void(0)" id="tab_hard" style="color:red;">硬装费用</a> | <a href="javascript:void(0)" id="tab_soft">软装费用</a>
	</span>
	
	<div id="hard_div">
	<img style="float:left" id="hard_pie_image" />
	<div style="float:left;margin-left:10px;line-height:28px;">
	<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?><span style="color:#ffffff;background:<?php echo ($vo[1]); ?>;"><?php echo ($vo[0]); ?></span> <span id="scale_<?php echo ($field); ?>"></span>% <br><?php endforeach; endif; ?>
	</div>
	</div>
	
	<div id="soft_div" style="display:none;">
	<img style="float:left" id="soft_pie_image" />
	<div style="float:left;margin-left:10px;line-height:28px;">
	<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?><span style="color:#ffffff;background:<?php echo ($vo[1]); ?>;"><?php echo ($vo[0]); ?></span> <span id="scale_<?php echo ($field); ?>"></span>% <br><?php endforeach; endif; ?>
	</div>
	</div>
</div>
<div style="650px;float:left;line-height:26px;">
建筑面积：<?php echo ($info["acreage"]); ?>㎡<br>
总预算：<?php echo ($info["budget"]); ?>元 <br>
<a href="__URL__/hard" target="_blank">硬装预算</a> <a href="__URL__/soft" target="_blank">软装预算</a><br>
实际费用：<?php echo ($total); ?>元
</div>

<div style="float:left;width:980px;border-top:1px dashed #999;padding-top:10px;margin-top:5px;">
<form method="post" action="">
<table>
	<tr><td width="200">名称</td><td width="200">预计费用</td><td width="300">实际费用</td></tr>
	<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?><tr><td><a href="__URL__/child/type/<?php echo ($field); ?>"><?php echo ($vo[0]); ?></a></td><td><span id="fee_<?php echo ($field); ?>"><?php echo ($hard_budget[$field]); ?></span>元</td><td><input type="text" id="<?php echo ($field); ?>" name="<?php echo ($field); ?>" value="<?php echo ($hard_realfee[$field]); ?>" />元</td></tr><?php endforeach; endif; ?>
	<tr><td colspan="3" style="border-top:1px solid #ccc;"></td></tr>
	<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?><tr><td><a href="__URL__/child/type/<?php echo ($field); ?>/realfee/<?php echo ($soft_realfee[$field]); ?>"><?php echo ($vo[0]); ?></a></td><td><span id="fee_<?php echo ($field); ?>"><?php echo ($soft_budget[$field]); ?></span>元</td><td><input type="text" id="<?php echo ($field); ?>" name="<?php echo ($field); ?>" value="<?php echo ($soft_realfee[$field]); ?>" />元</td></tr><?php endforeach; endif; ?>
</table>
<br>
<input type="submit" value="保 存" />
</form>
</div>

</div>
<script>
	
$(function(){
	_updatePie('hard', getValData('hard'));
	_updatePie('soft', getValData('soft'));
	
	$("#tab_hard").click(function(){
		$("#hard_div").show();
		$("#soft_div").hide();
	});
	
	$("#tab_soft").click(function(){
		$("#hard_div").hide();
		$("#soft_div").show();
	});
});

function getValData(type) {
	var data = {};
	if (type == 'hard') {
		<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?>data.<?php echo ($field); ?> = $("#<?php echo ($field); ?>").val();<?php endforeach; endif; ?>
	} else {
		<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?>data.<?php echo ($field); ?> = $("#<?php echo ($field); ?>").val();<?php endforeach; endif; ?>
	}
	
	return data;
}

function _updatePie(type, data) {
	var total = 0;
	var strData = '';
	if (type == 'hard') {
		<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?>strData += data.<?php echo ($field); ?> + ",";
			total += parseInt(data.<?php echo ($field); ?>);<?php endforeach; endif; ?> 
	} else {
		<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?>strData += data.<?php echo ($field); ?> + ",";
			total += parseInt(data.<?php echo ($field); ?>);<?php endforeach; endif; ?> 
	}


	total = total / 100;
	
	var sum = 0;var last_field = '';
	if (type == 'hard') {
		<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?>var scale_<?php echo ($field); ?> = Math.round(data.<?php echo ($field); ?>/total);
			sum += scale_<?php echo ($field); ?>;
			last_field = '<?php echo ($field); ?>';
			
			$("#scale_<?php echo ($field); ?>").html(scale_<?php echo ($field); ?>);<?php endforeach; endif; ?> 
	} else {
		<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?>var scale_<?php echo ($field); ?> = Math.round(data.<?php echo ($field); ?>/total);
			sum += scale_<?php echo ($field); ?>;
			last_field = '<?php echo ($field); ?>';
			
			$("#scale_<?php echo ($field); ?>").html(scale_<?php echo ($field); ?>);<?php endforeach; endif; ?> 
	}

	var last = parseInt($("#scale_" + last_field).html()) + 100 - sum;
	$("#scale_" + last_field).html(last);

	strData = strData.substr(0, strData.length - 1);

	if (type == 'hard') {
		$("#hard_pie_image").attr("src", "__URL__/pie/data/" + strData);
	} else {
		$("#soft_pie_image").attr("src", "__URL__/softpie/data/" + strData);
	}
}
</script>

</body>
</html>