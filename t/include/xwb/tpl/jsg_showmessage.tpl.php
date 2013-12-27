<?php if (!defined('IS_IN_XWB_PLUGIN')) {die('access deny!');}?><?php if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){@header("Content-type: text/html; charset=utf-8");}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo XWB_S_TITLE; ?>提示消息</title>
<?php echo $url_redirect; ?>
<link href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/public.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $GLOBALS['_J']['site_url'] . ('/images/xwb/shareout.css');?>" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="reg_wrap">
	<!-- 顶部 LOGO -->
	<div class="TopName">
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
    </div>
    <!-- /顶部 LOGO -->

    <div class="reg_main">
    	<b class="bg_regTop">&nbsp;</b>
         <b class="bg_deco_s">&nbsp;</b>
        <div class="reg_pub">

            <div class="n_Box">
                <div class="n_login_sc2">
                    <h2><?php echo XWB_S_TITLE; ?>提示消息</h2>
					
					<div><?php echo $message; ?></div>
					<div>
						<?php if($time!==null) { ?>
							<a href="<?php echo $redirectto; ?>">
							<?php if($time!==0) { ?>
								<span id='redirect_status'><span id='msg_time'><?php echo $time; ?></span>秒后<?php echo $to_title; ?></span>，
								<script language="JavaScript" type="text/javascript">
								function showTimeStatus()
								{
									var timeObj=document.getElementById('msg_time');
									if(timeObj.innerHTML==1)
									{
										document.getElementById('redirect_status').innerHTML="正在<?php echo $to_title; ?>";
										return false;
									}
									timeObj.innerHTML-=1;
									
									setTimeout(showTimeStatus,1000);
								}
								showTimeStatus();
								</script>							
							<?php } ?>
						如果您的浏览器没有自动跳转，请点这里继续</a>
					<?php } ?>
					</div>
                </div>
            </div>
        </div>
        <b class="bg_regBot">&nbsp;</b>
    </div>
</div>

</body>
</html>
