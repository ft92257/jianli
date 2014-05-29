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
	var STATICS='__STATICS__';
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

    <section class="w-96 mgl-a mgr-a content clearfix">
    	<div class="left w737 clearfix mt-5">
        	<ul class="tab-cut">
            	<li><a href="__GROUP__/Time/index">进&nbsp;&nbsp;&nbsp;度</a></li>
                <li><a href="__URL__/fee" class="mr-ck">费&nbsp;&nbsp;&nbsp;用</a></li>
            </ul>
        	<div class="w-53 ml-15">
            	<div class="clearfix show-box1" style="display:none">
                	<div class="left">
                    	<ul class="pie-chart">
                    		<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?><li><div class="icon" style="background:<?php echo ($vo[1]); ?>;">&nbsp;</div> <?php echo ($vo[0]); ?> <span id="scale_<?php echo ($field); ?>"></span>%</li><?php endforeach; endif; ?>
                        </ul>
                    </div>
                    <div class="left ml-3-5">
                    	<p class="clearfix cl-p ml-1">
                        	<a id="tab_soft" href="#" class="left show1-box addbg">软装</a>
                            <a id="tab_hard" href="#" class="left show2-box">硬装</a>
                        </p>
                        <p class="mt-2-5">
                        	<img id="soft_pie_image" src="__STATICS__/images/aad1.jpg" width="135" height="135" />
                        </p>
                    </div>
                    <div class="right">
                    	<div class="n-compile clearfix pt-0-5 pl-2 pb-0-5">
                        	<p class="left"><span style="font-size:18px">桑小姐</span>的家</p>
                            <a href="#" onclick="layerIfram('.soft-cost');" class="right mr-1-5 mt-0-5">编辑</a>
                        </div>
                        <div class="bg-color-h">
                        	<ul>
                            	<li>面积：<?php echo ($info["area"]); ?>㎡</li>
                                <li>户型：<?php echo ($info["apartment"]); ?></li>
                                <li>软装预算：<?php echo ($sb_total); ?>&nbsp;<a href="#" onclick="layerIfram('.soft-budget');">预算计算器</a></li>
                                <li>实际花费：<?php echo ($soft_total); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="clearfix show-box2" >
                	<div class="left">
                    	<ul class="pie-chart">
                    		<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?><li><div class="icon" style="background:<?php echo ($vo[1]); ?>;">&nbsp;</div> <?php echo ($vo[0]); ?> <span id="scale_<?php echo ($field); ?>"></span>%</li><?php endforeach; endif; ?>
                        </ul>
                    </div>
                    <div class="left ml-3-5">
                    	<p class="clearfix cl-p ml-1">
                        	<a href="#" class="left show1-box">软装</a>
                            <a href="#" class="left show2-box addbg">硬装</a>
                        </p>
                        <p class="mt-2-5">
                        	<img id="hard_pie_image" src="__STATICS__/images/aad2.jpg" width="135" height="135" />
                        </p>
                    </div>
                    <div class="right">
                    	<div class="n-compile clearfix pt-0-5 pl-2 pb-0-5">
                        	<p class="left"><span style="font-size:18px"><?php echo ($user["nickname"]); ?></span>的家</p>
                            <a href="#" onclick="layerIfram('.hard-cost');" class="right mr-1-5 mt-0-5">编辑</a>
                        </div>
                        <div class="bg-color-h">
                        	<ul>
                            	<li>面积：<?php echo ($info["area"]); ?>㎡</li>
                                <li>户型：<?php echo ($info["apartment"]); ?></li>
                                <li>硬装预算：<?php echo ($hb_total); ?>&nbsp;<a href="#" onclick="layerIfram('.hard-budget');">预算计算器</a></li>
                                <li>实际花费：<?php echo ($hard_total); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="bo-t">
                	<span>费用拆分</span>
                </div>
                <table width="530px" class="tables mt-1">
                	<thead>
                    	<tr>
                        	<th width="70">类别</th>
                            <th>收费项目</th>
                            <th>预估费用</th>
                            <th>支出费用</th>
                        </tr>
                    </thead>
                    <tbody>
                    	<tr istitle="1">
                        	<td style="border-left:0"></td>
                            <td></td>
                            <td></td>
                            <td style="border-right:0"></td>
                        </tr>
						<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?><tr>
                        	<td class="f-weight" style="border-left:0">硬装</td>
                            <td><a onclick="layerIfram('.hard-budget-<?php echo ($field); ?>');" href="#"><?php echo ($vo[0]); ?></a></td>
                            <td>￥<span id="fee_<?php echo ($field); ?>"><?php echo ($hard_budget[$field]); ?></span></td>
                            <td style="border-right:0">￥<span id="<?php echo ($field); ?>"><?php echo ($hard_realfee[$field]); ?></span></td>
                        </tr>
						<iframe id="iframe-hard-<?php echo ($field); ?>" onLoad="iFrameHeight('iframe-hard-<?php echo ($field); ?>')" class="hard-budget-<?php echo ($field); ?>" src="__URL__/child/type/<?php echo ($field); ?>/realfee/<?php echo ($hard_realfee[$field]); ?>" style="display:none;width:600px;background:#fff;"></iframe><?php endforeach; endif; ?>
                        
                        <tr istitle="1">
                        	<td style="border-left:0"></td>
                            <td></td>
                            <td></td>
                            <td style="border-right:0"></td>
                        </tr>
						<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?><tr>
                        	<td class="f-weight" style="border-left:0">软装</td>
                            <td><a onclick="layerIfram('.soft-budget-<?php echo ($field); ?>');" href="#"><?php echo ($vo[0]); ?></a></td>
                            <td>￥<span id="fee_<?php echo ($field); ?>"><?php echo ($soft_budget[$field]); ?></span></td>
                            <td style="border-right:0">￥<span id="<?php echo ($field); ?>"><?php echo ($soft_realfee[$field]); ?></span></td>
                        </tr>
						<iframe id="iframe-soft-<?php echo ($field); ?>" onLoad="iFrameHeight('iframe-soft-<?php echo ($field); ?>')" class="soft-budget-<?php echo ($field); ?>" src="__URL__/child/type/<?php echo ($field); ?>/realfee/<?php echo ($soft_realfee[$field]); ?>" style="display:none;width:600px;background:#fff;"></iframe><?php endforeach; endif; ?>
                        <tr istitle="1">
                        	<td style="border-left:0"></td>
                            <td></td>
                            <td></td>
                            <td style="border-right:0"></td>
                        </tr>
                        <tr>
                        	<td class="f-weight" style="border-left:0">总计</td>
                            <td></td>
                            <td>￥<?php echo ($info["budget_amount"]); ?></td>
                            <td style="border-right:0">￥<?php echo ($total); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
	
	<div class="hard-cost cost-compile-pop w-52-5" style="display:none;">
    	<div class="w-44-5 center-box">
        	<h1 class="stamp"><?php echo ($user["nickname"]); ?>的家<span>&nbsp;软装费用</span></h1>
            <form action="__URL__/hard_edit" method="post">
		    <table width="100%">
            	<thead>
                	<tr>
                    	<th>收费项目</th>
                        <th>预估费用</th>
                        <th>支出费用</th>
                    </tr>
                </thead>
                <tbody>
					<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?><tr>
						<th style="border-left:0 none; border-top:0 none"><?php echo ($vo[0]); ?></th>
						<td style=" border-top:0 none">￥<?php echo ($hard_budget[$field]); ?></td>
						<td style="border-right:0 none; border-top:0 none">￥<input type="text" name="<?php echo ($field); ?>" value="<?php echo ($hard_realfee[$field]); ?>" /></td>
					</tr><?php endforeach; endif; ?>
                </tbody>
            </table>
            <div style="width:58px; margin:30px auto 20px auto">
            	<input type="image" src="__STATICS__/images/bc-sub.jpg" />
            </div>
			</form>
        </div>
    </div> 
	
	<div class="soft-cost cost-compile-pop w-52-5" style="display:none;">
    	<div class="w-44-5 center-box">
        	<h1 class="stamp"><?php echo ($user["nickname"]); ?>的家<span>&nbsp;软装费用</span></h1>
            <form action="__URL__/soft_edit" method="post">
		    <table width="100%">
            	<thead>
                	<tr>
                    	<th>收费项目</th>
                        <th>预估费用</th>
                        <th>支出费用</th>
                    </tr>
                </thead>
                <tbody>
					<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?><tr>
						<th style="border-left:0 none; border-top:0 none"><?php echo ($vo[0]); ?></th>
						<td style=" border-top:0 none">￥<?php echo ($soft_budget[$field]); ?></td>
						<td style="border-right:0 none; border-top:0 none">￥<input type="text" name="<?php echo ($field); ?>" value="<?php echo ($soft_realfee[$field]); ?>" /></td>
					</tr><?php endforeach; endif; ?>
                </tbody>
            </table>
            <div style="width:58px; margin:30px auto 20px auto">
            	<input type="image" src="__STATICS__/images/bc-sub.jpg" />
            </div>
			</form>
        </div>
    </div> 

	<iframe id="iframe-hard" onLoad="iFrameHeight('iframe-hard')" class="hard-budget" src="__URL__/hard" style="display:none;width:600px;background:#fff;"></iframe>
	<iframe id="iframe-soft" onLoad="iFrameHeight('iframe-soft')" class="soft-budget" src="__URL__/soft" style="display:none;width:600px;background:#fff;"></iframe>

<script type="text/javascript" src="__STATICS__/js/main.js"></script>


<script>
	
$(function(){
	_updatePie('hard', getHtmlData('hard'));
	_updatePie('soft', getHtmlData('soft'));
	
	$("#tab_hard").click(function(){
		$("#hard_div").show();
		$("#soft_div").hide();
	});
	
	$("#tab_soft").click(function(){
		$("#hard_div").hide();
		$("#soft_div").show();
	});
	
	$(".f-weight").each(function(){
		if ($(this).parent().prev().attr("istitle") != "1") {
			$(this).html('');
		}
	});
});

function getHtmlData(type) {
	var data = {};
	if (type == 'hard') {
		<?php if(is_array($hard_fields)): foreach($hard_fields as $field=>$vo): ?>data.<?php echo ($field); ?> = $("#<?php echo ($field); ?>").html();<?php endforeach; endif; ?>
	} else {
		<?php if(is_array($soft_fields)): foreach($soft_fields as $field=>$vo): ?>data.<?php echo ($field); ?> = $("#<?php echo ($field); ?>").html();<?php endforeach; endif; ?>
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