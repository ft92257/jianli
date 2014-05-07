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
<h2>硬装费用</h2>

<div style="width:200px;float:left;">
	<img id="pie_image" src="<?php echo U('pie');?>" />
	<div style="float:left;">
	<span style="color:#ffffff;background:#ff0000;">设计费</span> <span id="scale_design"></span>% <br>
	<span style="color:#ffffff;background:#00ff00;">人工费</span> <span id="scale_artificial"></span>% <br>
	<span style="color:#ffffff;background:#0000ff;">材料费</span> <span id="scale_material"></span>% <br>
	<!--<span style="color:#ffffff;background:#ff00ff;">其他费</span> <span id="scale_other"></span>% <br>-->
	</div>
</div>
<div style="780px;float:left;">
建筑面积：<input type="text" id="acreage" name="acreage" value="100" />㎡<br>
设置预算：<input type="text" id="budget" name="budget" />元<br>
选择档次：<select name="grade" id="grade"><option value="0">请选择档次</option><option value="3">高档</option><option value="2">中档</option><option value="1">低档</option></select><br>
<button type="button" onclick="calculate()">预估费用</button><br><br>
</div>

<div style="float:left;width:980px;border-top:1px dashed #999;padding-top:10px;">
<form method="post" action="">
<table>
	<tr><td width="100">名称</td><td width="100">预计费用</td><td width="200">我的预算</td><td width="100">档次</td><td width="100">实际已花费</td></tr>
	<tr><td>设计费</td><td><span id="fee_design">0.00</span>元</td><td><input type="text" id="design" name="design" />元</td><td id="grade_design"><span id="grade_design_3">高</span> <span id="grade_design_2">中</span> <span id="grade_design_1">低</span></td><td>0.00元</td></tr>
	<tr><td>人工费</td><td><span id="fee_artificial">0.00</span>元</td><td><input type="text" id="artificial" name="artificial" />元</td><td id="grade_artificial"><span id="grade_artificial_3">高</span> <span id="grade_artificial_2">中</span> <span id="grade_artificial_1">低</span></td><td>0.00元</td></tr>
	<tr><td>材料费</td><td><span id="fee_material">0.00</span>元</td><td><input type="text" id="material" name="material" />元</td><td id="grade_material"><span id="grade_material_3">高</span> <span id="grade_material_2">中</span> <span id="grade_material_1">低</span></td><td>0.00元</td></tr>
	<tr><td colspan="5" style="border-top:1px solid #ccc;"></td></tr>
	<tr><td>总费用</td><td><span id="fee_total">0.00</span>元</td><td></td><td></td><td>0.00元</td></tr>
	<!--<tr><td>其他费</td><td><span id="fee_other">0.00</span>元</td><td><input type="text" id="other" name="other" />元</td><td><span id="grade_other_3">高</span> <span id="grade_other_2">中</span> <span id="grade_other_1">低</span></td><td>0.00元</td></tr>-->
</table>
<input type="submit" value="保 存" />
</form>
</div>

</div>
<script>
function calculate(){
	$.post(URL+'/calculate', {acreage:$('#acreage').val(), budget:$('#budget').val(), grade:$('#grade').val()}, function(json){
		if (json.status == 0) {
			var data = json.data;
			$("#fee_design").html(data.design);
			$("#fee_artificial").html(data.artificial);
			$("#fee_material").html(data.material);
			//$("#fee_other").html(data.other);
			
			$("#design").val(data.design);
			$("#artificial").val(data.artificial);
			$("#material").val(data.material);
			//$("#other").val(data.other);
			
			var oGrade = {"1":"低", "2":"中", "3":"高"};
			$("#grade_design").html(oGrade[data.grade.design]);
			$("#grade_artificial").html(oGrade[data.grade.artificial]);
			$("#grade_material").html(oGrade[data.grade.material]);
			
			//$("#grade_design_" + data.grade.design).css("color", "red");
			//$("#grade_artificial_" + data.grade.artificial).css("color", "red");
			//$("#grade_material_" + data.grade.material).css("color", "red");
			//$("#grade_other_" + data.grade.other).css("color", "red");
			
			var total = (data.design + data.artificial + data.material) / 100;
			var scale_design = Math.round(data.design/total);
			var scale_artificial = Math.round(data.artificial/total);
			//var scale_material = Math.round(data.material/total);
			var scale_material =  100 - scale_design - scale_artificial;
			$("#scale_design").html(scale_design);
			$("#scale_artificial").html(scale_artificial);
			$("#scale_material").html(scale_material);
			//$("#scale_other").html(scale_other);
			
			$("#fee_total").html(total * 100);
			
			$("#pie_image").attr("src", "<?php echo U('pie');?>/data/" + data.design + "," + data.artificial + "," + data.material);
		}
		alert(json.msg);
	}, 'json');
}
</script>


</body>
</html>