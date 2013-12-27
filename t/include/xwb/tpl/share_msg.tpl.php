<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><?php if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){@header("Content-type: text/html; charset=utf-8");}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>转发到新浪微博</title>
<script type="text/javascript">
	var scope = {
		$lang		  : "zh",
		$severtime    : "<?php echo time();?>",
		$PRODUCT_NAME : "miniblogplatform",
		$pageid       : "shareSucess",
		$setDomain    : false,
		$devMode	  : 99,
        $localUrl     : '<?php echo XWB_plugin::getPluginUrl('images');?>'
	};
</script>
<script type="text/javascript" src="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/boot.js');?>"></script>
<script type="text/javascript">loadResource();renderPage();</script>
<link href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/public.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/shareout.css');?>" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="reg_wrap">
	<!-- 顶部 LOGO -->
	<div class="TopName">
        <div class="logo"></div>
        <a href="<?php echo ( ! empty($rst['id'])) ? "http://weibo.com/u/{$rst['id']}" : '#';?>" target="_blank" class="logoLink"></a>
        <div class="op">
            <?php if ( ! empty($rst['id'])):?>
        	<span>你正在使用 <a href="http://weibo.com/u/<?php echo $rst['id'];?>" target="_blank"  class="userID"><?php echo $rst['screen_name'];?></a> 帐号</span>
            <span class="line">|</span>
            <span><a href="http://v.t.sina.com.cn/share/sharechg.php?appkey=<?php echo XWB_APP_KEY;?>&url=&title=&source=&sourceUrl=&%20content=gb2312&pic=">换个帐号？</a></span>
            <?php endif;?>
        </div>
    </div>
    <!-- /顶部 LOGO -->
    <div class="reg_main">
    	<b class="bg_regTop">&nbsp;</b>
         <b class="bg_deco_s">&nbsp;</b>
        <div class="reg_pub">

            <div class="n_Box">
                <div class="n_login_sc2">
                    <h2><?php echo $tipMsg;?></h2>
                    <p><span id="timeout">3</span>秒后窗口自动关闭，<a href="javascript:void(0 );" onclick="window.opener = null; window.close();">点击这里</a>立即关闭</p>
                    <?php if ( ! empty($rst['id'])):?>
                    <div class="btn"><a href="http://weibo.com/u/<?php echo $rst['id'];?>/profile" class="MIB_bigBtn MIB_bigBtnB" target="_blank"><cite>去我的微博</cite></a></div>
                    <?php endif;?>
                </div>
            </div>
        </div>
        <b class="bg_regBot">&nbsp;</b>
    </div>
</div>

</body>
</html>
