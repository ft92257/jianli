<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>费用</title>
<link type="text/css" rel="stylesheet" href="__STATICS__/css/screen.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/layer/skin/layer.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/css/main.css" />
<script type="text/javascript" src="__STATICS__/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="__STATICS__/js/common.js"></script>
<script src="__STATICS__/layer/layer.min.js"></script>
<!--[if lt ie 9]>
<script type="text/javascript" src="__STATICS__/js/html5shiv.js"></script>
<![endif]-->
</head>
<body>
<script>
	var URL = '__URL__';
	var GROUP = '__GROUP__';
</script>


<section style="width:560px;padding:10px 20px;">
<h2><?php echo ($title); ?></h2>
    <div class="show1">
        <div class="clearfix">
            <div class="left">
                <div class="pieTabs g-ys-tit mt-1">
                    <a href="#" id="tab_forecast">预计费用</a>
                    <a href="#" id="tab_my">我的预算</a>
                    <!--<a href="#" id="tab_real">实际费用</a>-->
                </div>
                <div class="clearfix mt-1-5">
                    <div class="left">
                        <ul class="pie-chart">
                        	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?><li><div class="icon" style="background:<?php echo ($vo[1]); ?>;">&nbsp;</div> <?php echo ($vo[0]); ?> <span id="scale_<?php echo ($field); ?>"></span>%</li><?php endforeach; endif; ?>
                        </ul>
                    </div>
                    <div class="left" style="margin-left:20px">
                        <p>
                            <img id="pie_image" src="__URL__/childpie/type/<?php echo ($type); ?>" width="135" height="135" />
                        </p>
                    </div>
                </div>
            </div>
			<div class="right calculate h-19-5">
                <div class="from-div ml-1-5 w-19-5 mt-1-5">
                    <div class="clearfix"></div>
                    <div class="clearfix mt-0-5"><label>设置预算：</label><input type="text" onblur="setBudget(this)" id="budget" name="budget" value="<?php echo ($budget["total"]); ?>" /></div>
                    <div class="clearfix mt-0-5">
                        <label>设置预算：</label>
                        <select name="grade" id="grade" onchange="selectGrade(this)">
                            <option value="0">请选择档次</option>
							<option value="3">高档</option>
							<option value="2">中档</option>
							<option value="1">低档</option>
                        </select>
                    </div>
                    <div class="mt-1"><input type="submit" onclick="calculate()" value="预估费用" style="cursor:pointer" /></div>
                </div>
            </div>
        </div>
		<form method="post" action="">
        <table class="ys-tab-b mt-6">
            <thead>
                <tr>
                    <th width="65px">名称</th>
                    <th width="80px">预计费用</th>
                    <th width="115px">我的预算</th>
                    <th width="32px">档次</th>
                    <th width="250px">备注</th>
                </tr>
            </thead>
            <tbody>
            	<?php if(is_array($fields)): foreach($fields as $field=>$vo): ?><tr>
					<th style="border-left:0"><a href="__URL__/child/type/<?php echo ($field); ?>"><?php echo ($vo[0]); ?></a></td>
					<td><span id="fee_<?php echo ($field); ?>">0.00</span>元</td>
					<td><input style="width:80px;" type="text" id="<?php echo ($field); ?>" name="<?php echo ($field); ?>" value="<?php echo ($budget[$field]); ?>" /> 元</td>
					<td id="grade_<?php echo ($field); ?>"><span id="grade_<?php echo ($field); ?>_3">高</span> <span id="grade_<?php echo ($field); ?>_2">中</span> <span id="grade_<?php echo ($field); ?>_1">低</span></td>
					<td style="border-right:0"><?php echo ($vo[3]); ?></td>
				</tr><?php endforeach; endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>总费用</th>
                    <td><span id="fee_total">0.00</span>元</td>
                    <td><input id="synBudget" onclick="synCheck(this)" type="checkbox" />同步我的预算</td>
                    <td></td>
                    <td>实际费用：<?php echo ($_GET['realfee']); ?> 元</td>
                </tr>
            </tfoot>
        </table>
        <div class="sub-btn mt-5"><input type="submit" value="保&nbsp;&nbsp;&nbsp;存" style="cursor:pointer" /></div>
    	</form>
	</div>

</section> 
	
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