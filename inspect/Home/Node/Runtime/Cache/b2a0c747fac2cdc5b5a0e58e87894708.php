<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>阶段简介</title>
<link type="text/css" rel="stylesheet" href="__STATICS__/css/screen.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/layer/skin/layer.css" />
<link type="text/css" rel="stylesheet" href="__STATICS__/css/main.css" />

<script type="text/javascript" src="__STATICS__/js/jquery-1.10.2.min.js"></script>
<script language="javascript" type="text/javascript" src="__STATICS__/My97DatePicker/WdatePicker.js"></script>
<!-- JQ formVerification类库 -->
<link rel="stylesheet" href="__STATICS__/jquery-form/css/validationEngine.jquery.css" type="text/css"/>
<link rel="stylesheet" href="__STATICS__/jquery-form/css/template.css" type="text/css"/>
<script src="__STATICS__/jquery-form/js/languages/jquery.validationEngine-zh_CN.js" type="text/javascript" charset="utf-8"></script>
<script src="__STATICS__/jquery-form/js/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>

<script src="__STATICS__/layer/layer.min.js"></script>
<script type="text/javascript" src="__STATICS__/js/common.js"></script>
<!--[if lt ie 9]>
<script type="text/javascript" src="js/html5shiv.js"></script>
<![endif]-->
</head>
<body>
	<div class="page-bg">
    	<h1 class="h-title-s pt-9 ml-2 mr-5-5 align-c"><?php echo ($node['name']); ?>阶段，简介</h1>
        <div class="ml-3-5">
        	<div class="clearfix div_time"><strong class="gq-time left">工期：<?php echo ($user_step['begin_time']); ?> ~ <?php echo ($user_step['end_time']); ?></strong><a href="javascript:;" id="a_edit" class="left mr-1-5 mt-2 ml-1-5"><img src="__STATICS__/images/xg-sub.jpg" width="58" height="22" /></a></div>
            <p class="ja-con mt-2" style="word-break:break-all"><?php echo ($node['info']); ?></p>
            <p class="ja-con mt-2">知识点（<a href="javascript:;>" id="btn_question" data-id="<?php echo ($user_step[step]); ?>"><?php echo ($user_step['question_c']); ?></a>）&nbsp;&nbsp; 验收点（<a href="javascript:;>" id="btn_acceptance" data-id="<?php echo ($user_step[step]); ?>"><?php echo ($user_step['aceptance_no_c']+$user_step['aceptance_pass_c']+$user_step['aceptance_nopass_c']); ?></a>）</p>
            <!--  
            <div class="mt-5-5 clearfix">
            	<a href="#" id="a_edit" class="left mr-1-5"><img src="__STATICS__/images/xg-sub.jpg" width="58" height="22" /></a>
                <a href="#" class="left"><img src="__STATICS__/images/bc-sub.jpg" width="58" height="22" /></a>
            </div>
            -->
        </div>
    </div>
    <input id="hid_begin_time" type="hidden" value="<?php echo ($user_step['begin_time']); ?>"/>
    <input id="hid_end_time" type="hidden" value="<?php echo ($user_step['end_time']); ?>"/>
    <input type="hidden" value="<?php echo ($user_step[no]); ?>" id="hid_no"/>
    <input type="hidden" value="<?php echo ($user_step[step]); ?>" id="hid_step"/>
</body>

<script>
 var GROUP = "__GROUP__";
 var STATICS ='__STATICS__';
$(function() {
	$("#btn_question").bind('click', function(){
		 parent.$.layer({
			    type: 2,
			    maxmin: true,
			    shadeClose: true,
			    title: false,
			    shade: [0.1,'#fff'],
			    offset: ['20px',''],
			    area: ['565px', '702px'],
			    iframe: {src: GROUP+'/Step/indexQuestion/step/'+$(this).attr('data-id')}
			}); 
		});
	
	$("#btn_acceptance").bind('click', function(){
		parent.$.layer({
		    type: 2,
		    maxmin: true,
		    shadeClose: true,
		    title: false,
		    shade: [0.1,'#fff'],
		    area: ['565px', '702px'],
		    iframe: {src: GROUP+'/Step/indexAcceptance/no/'+$('#hid_no').val()+'/step/'+$(this).attr('data-id')}
		}); 
	});
	
	$("#a_edit").bind("click", function(){
		createForm();
	});
	
	function createForm(){
		var html = [];
		html.push('<form action="'+GROUP+'/Step/edit" method="post" class="clearfix">');
		html.push('<strong class="gq-time left">工期：<input type="text" name="begin_date" onClick="WdatePicker()" value="'+$("#hid_begin_time").val()+'"/> ~ <input type="text" name="end_date" onClick="WdatePicker()" value="'+$("#hid_end_time").val()+'"/>');
		html.push('<input type="hidden" name="no" value="'+$("#hid_no").val()+'"/>');
		html.push('<input type="hidden" name="step" value="'+$("#hid_step").val()+'"/></strong>');
		html.push('<input type="hidden" name="act" value="updatetime"/>');
		html.push('<input type="submit" id="sub_save" style="display:none" />');
		html.push('<a href="javascript:;" class="btn_save left mr-0-5 mt-2 ml-1-5" id="a_save"><img src="'+STATICS+'/images/bc-sub.jpg"/></a><a href="javascript:;" id="a_esc" class="btn_esc left mt-2"><img src="'+STATICS+'/images/qx-sub.jpg"/></a>');
		html.push('</form>');
		$(".div_time").html(html.join(""));
		$("#a_save").bind("click", function(){
			$("#sub_save").click();
		});
		$("#a_esc").bind('click',function(){
			reHtml();
		});
	}
	
	function reHtml(){
		var html = [];
		html.push('<strong class="gq-time left">工期：'+$("#hid_begin_time").val()+' ~ '+$("#hid_end_time").val()+'</strong>');
		html.push('<a href="javascript:;" id="a_edit" class="left mr-1-5 mt-2 ml-1-5"><img src="'+STATICS+'/images/xg-sub.jpg" width="58" height="22" /></a>');
		$(".div_time").html(html.join(""));
		$("#a_edit").bind("click", function(){
			createForm();
		});
	}
});
</script>

</html>