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
            	<li><a href="__GROUP__/Time/index" class="mr-ck">进&nbsp;&nbsp;&nbsp;度</a></li>
                <li><a href="__GROUP__/Budget/fee">费&nbsp;&nbsp;&nbsp;用</a></li>
            </ul>
<div class="clearfix mr-2 pb-2"><span class="right">当前状态：施工中<input id="btn_shutdown" type="button" class="stop" value="停&nbsp&nbsp&nbsp;工" /></span></div>
            <div class="clearfix">
<!--  <a href="__GROUP__/Memo/memoAdd">添加备忘录</a>-->
        	<div class="left ml-12">
        	<?php if(!empty($user_step[2])): ?><div class="w-24 h-20 bor-top-2 mt-10 mr--1 boxhover">
                	<div class="clearfix mr-1-5 mt-1">
                        <p class="right xg-mouse">
                            <a href="javascript:;" class="step_info" data-id="2"><span class="font-style"><?php echo ($user_step[2][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[2][end_time]); ?></span>&nbsp;<span class="font-size">土建</span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix right mr-1-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="2">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[2][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="2">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[2][aceptance_no_c]+$user_step[2][aceptance_pass_c]+$user_step[2][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="2">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[2][check_no_c]+$user_step[2][check_pass_c]+$user_step[2][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-right">
                    	<div class="h-9">
                        	<span class="bg-left1 s-style1" style="top:27%"><?php echo ($user_step[2][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
          <?php else: ?>
          		<div class="w-24 h-20 bor-top-2 mr--1 boxhover">
                	<div class="clearfix mr-1-5 mt-1">
                        <p class="right xg-mouse">
                            <span class="font-size">土建</span>
                        </p>
                    </div>
                </div><?php endif; ?>
                
          <?php if(!empty($user_step[4])): ?><div class="w-24 h-20 bor-top-4 mr--1 boxhover">
                	<div class="clearfix mr-1-5 mt-1">
                        <p class="right xg-mouse">
                            <a href="javascript:;" class="step_info" data-id="4"><span class="font-style"><?php echo ($user_step[4][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[4][end_time]); ?></span>&nbsp;<span class="font-size">泥木</span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix right mr-1-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="4">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[4][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="4">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[4][aceptance_no_c]+$user_step[4][aceptance_pass_c]+$user_step[4][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="4">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[4][check_no_c]+$user_step[4][check_pass_c]+$user_step[4][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-right">
                    	<div class="h-9">
                        	<span class="bg-left2 s-style2" style="top:27%"><?php echo ($user_step[4][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
          <?php else: ?>
          		<div class="w-24 h-20 bor-top-4 mr--1 boxhover">
                	<div class="clearfix mr-1-5 mt-1">
                        <p class="right xg-mouse">
                            <span class="font-size">泥木</span>
                        </p>
                    </div>
                </div><?php endif; ?>      
                
         <?php if(!empty($user_step[6])): ?><div class="w-24 h-20 bor-top-6 mr--1 boxhover">
                	<div class="clearfix mr-1-5 mt-1">
                        <p class="right xg-mouse">
                           <a href="javascript:;" class="step_info" data-id="6"><span class="font-style"><?php echo ($user_step[6][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[6][end_time]); ?></span>&nbsp;<span class="font-size">安装</span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix right mr-1-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="6">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[6][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="6">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[6][aceptance_no_c]+$user_step[6][aceptance_pass_c]+$user_step[6][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="6">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[6][check_no_c]+$user_step[6][check_pass_c]+$user_step[6][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-right">
                    	<div class="h-9">
                        	<span class="bg-left3 s-style3" style="top:27%"><?php echo ($user_step[6][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
          <?php else: ?>
          		<div class="w-24 h-20 bor-top-6 mr--1 boxhover">
                	<div class="clearfix mr-1-5 mt-1">
                        <p class="right xg-mouse">
                            <span class="font-size">安装</span>
                        </p>
                    </div>
                </div><?php endif; ?>      
                
            </div>
            
            <div class="left"><img src="__STATICS__/images/zhouxian.png" width="44" height="767" /></div>
            
            <div class="left" style="margin-top:2px">
            
           <?php if(!empty($user_step[1])): ?><div class="w-24 h-20 bor-top-1 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <a href="javascript:;" class="step_info" data-id="1"><span class="font-size">准备</span>&nbsp;<span class="font-style"><?php echo ($user_step[1][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[1][end_time]); ?></span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix left ml-2-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="1">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[1][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="1">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[1][aceptance_no_c]+$user_step[1][aceptance_pass_c]+$user_step[1][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="1">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[1][check_no_c]+$user_step[1][check_pass_c]+$user_step[1][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-left">
                    	<div class="h-9">
                        	<span class="bg-left1 s-style1" style="top:27%"><?php echo ($user_step[1][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
            	<div class="w-24 h-20 bor-top-1 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <span class="font-size">准备</span>
                        </p>
                    </div>
                </div><?php endif; ?>
           <?php if(!empty($user_step[3])): ?><div class="w-24 h-20 bor-top-3 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <a href="javascript:;" class="step_info" data-id="3"><span class="font-size">水电</span>&nbsp;<span class="font-style"><?php echo ($user_step[3][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[3][end_time]); ?></span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix left ml-2-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="3">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[3][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="3">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[3][aceptance_no_c]+$user_step[3][aceptance_pass_c]+$user_step[3][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="3">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[3][check_no_c]+$user_step[3][check_pass_c]+$user_step[3][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-left">
                    	<div class="h-9">
                        	<span class="bg-left2 s-style2" style="top:27%"><?php echo ($user_step[3][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
            	<div class="w-24 h-20 bor-top-3 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <span class="font-size">水电</span>
                        </p>
                    </div>
                </div><?php endif; ?>
            <?php if(!empty($user_step[5])): ?><div class="w-24 h-20 bor-top-5 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <a href="javascript:;" class="step_info" data-id="5"><span class="font-size">涂料</span>&nbsp;<span class="font-style"><?php echo ($user_step[5][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[5][end_time]); ?></span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix left ml-2-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="5">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[5][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="5">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[5][aceptance_no_c]+$user_step[5][aceptance_pass_c]+$user_step[5][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="5">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[5][check_no_c]+$user_step[5][check_pass_c]+$user_step[5][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-left">
                    	<div class="h-9">
                        	<span class="bg-left3 s-style3" style="top:27%"><?php echo ($user_step[5][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
            	<div class="w-24 h-20 bor-top-5 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <span class="font-size">涂料</span>
                        </p>
                    </div>
                </div><?php endif; ?>
               <?php if(!empty($user_step[7])): ?><div class="w-24 h-20 bor-top-7 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <a href="javascript:;" class="step_info" data-id="7"><span class="font-size">软装</span>&nbsp;<span class="font-style"><?php echo ($user_step[7][begin_time]); ?>&nbsp;--&nbsp;<?php echo ($user_step[7][end_time]); ?></span></a>
                        </p>
                    </div>
                    <ul class="point-accept clearfix left ml-2-5 mt-1">
                    	<li class="mr-1-5">
                            <a href="javascript:;" class="clearfix btn_question" data-id="7">
                                <span class="span-ico ico1 mr-0-5"></span>
                                <span class="mt-0-5">知识点&nbsp;<?php echo ($user_step[7][question_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_acceptance" data-id="7">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">验收点&nbsp;<?php echo ($user_step[7][aceptance_no_c]+$user_step[7][aceptance_pass_c]+$user_step[7][aceptance_nopass_c]); ?></span>
                            </a>
                        </li>
                        <li>
                        	<a href="javascript:;" class="clearfix btn_check" data-id="7">
                                <span class="span-ico ico2 mr-0-5"></span>
                                <span class="mt-0-5">巡查点&nbsp;<?php echo ($user_step[7][check_no_c]+$user_step[7][check_pass_c]+$user_step[7][check_nopass_c]); ?></span>
                            </a>
                        </li>
                    </ul>
                    <div class="h-9 plan-left">
                    	<div class="h-9">
                        	<span class="bg-left4 s-style4" style="top:33%"><?php echo ($user_step[7][schedule]); ?>%</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
            	<div class="w-24 h-20 bor-top-7 ml--1 boxhover">
                	<div class="clearfix ml-1-5 mt-1">
                        <p class="left xg-mouse">
                            <span class="font-size">软装</span>
                        </p>
                    </div>
                </div><?php endif; ?>
            </div>
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
</body>
</html>
 <input type="hidden" value="<?php echo ($user_info[no]); ?>" id="hid_no"/>
 <script>
 var GROUP = '__GROUP__';
 $(function(){
	 $(".step_info").bind('click', function(){
		 $.layer({
			    type: 2,
			    maxmin: true,
			    shadeClose: true,
			    title: false,
			    shade: [0.1,'#fff'],
			    offset: ['20px',''],
			    area: ['565px', ($(window).height() - 50) +'px'],
			    iframe: {src: GROUP+"/Step/index/no/"+$("#hid_no").val()+"/step/"+$(this).attr('data-id')}
			}); 
	 });
	 $("#btn_shutdown").bind('click', function(){
		 $.layer({
			    type: 2,
			    maxmin: true,
			    title: '停工',
			    offset: [($(window).height()/2-200)+'px',''],
			    area: ['350px', '240px'],
			    iframe: {src: GROUP+"/Form/shutdownForm/no/"+$("#hid_no").val()}
			});
	 });
	 $(".btn_question").bind('click', function(){
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
	
	$(".btn_acceptance").bind('click', function(){
		parent.$.layer({
		    type: 2,
		    maxmin: true,
		    shadeClose: true,
		    title: false,
		    shade: [0.1,'#fff'],
		    offset: ['20px',''],
		    area: ['565px', '702px'],
		    iframe: {src: GROUP+'/Step/indexAcceptance/no/'+$("#hid_no").val()+'/step/'+$(this).attr('data-id')}
		}); 
	});
	
	$(".btn_check").bind('click', function(){
		parent.$.layer({
		    type: 2,
		    maxmin: true,
		    shadeClose: true,
		    title: false,
		    shade: [0.1,'#fff'],
		    offset: ['20px',''],
		    area: ['565px', '702px'],
		    iframe: {src: GROUP+'/Step/indexCheck/no/'+$("#hid_no").val()+'/step/'+$(this).attr('data-id')}
		}); 
	});
 });
 </script>