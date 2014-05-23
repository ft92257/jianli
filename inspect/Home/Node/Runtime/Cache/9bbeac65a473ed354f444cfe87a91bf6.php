<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>知识点</title>
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
    	<h1 class="h-title-s pt-9 ml-2 mr-5-5 align-c"><?php echo ($node[name]); ?>阶段，知识点</h1>
        <div class="bd" id="scroll_bd">
            <div class="top" id="bd">
                <ul id="ul" class="list-li">
                <?php if(is_array($question)): foreach($question as $key=>$vo): ?><li><a href="javascript:;" data-id="<?php echo ($vo['id']); ?>" class="question_info"><?php echo ($vo[title]); ?></a></li><?php endforeach; endif; ?>
                </ul>
            </div>
		</div>
    </div>
</body>
<script>
var GROUP= "__GROUP__"
$(function(){
	$(".question_info").bind('click', function(){
		 parent.$.layer({
			    type: 2,
			    maxmin: true,
			    shadeClose: true,
			    title: false,
			    shade: [0.1,'#fff'],
			    offset: ['20px',''],
			    area: ['565px', '702px'],
			    iframe: {src: GROUP+'/Step/questionInfo/id/'+$(this).attr('data-id')}
			}); 
		});
});
</script>
</html>