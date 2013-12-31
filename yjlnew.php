<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');

function yjl_html_new($c, $css='', $body_id='', $menu_id=0){
	global $js_c, $js_scrc, $isupimg, $yjl_tpath, $xqid, $xqdb, $page_title, $r_main, $a_fx, $udb, $is_home, $is_nologin, $is_mce, $yjl_isdebug, $d_l1title;
	$ptitle=($page_title!=''?$page_title.' | ':'').$r_main['site_name'];
	if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0)){
		$js_c.='
		jlmsg_v();';
		$js_scrc.='
		jlmsg_p();';
	}
	if($js_scrc!='')$js_c.='
	$(window).scroll(function(){
		'.$js_scrc.'
	});';
	$s='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'.$ptitle.' - 装修行业中代表良心的力量，家装监理，连锁店装修监理，别墅装修监理，别墅监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
<meta name="keywords" content="易监理,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
<meta name="description" content="易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="images/iphone_logo.png" />
<link rel="apple-touch-icon" sizes="72x72" href="images/ipad_logo.png" />
<link rel="apple-touch-icon" sizes="114x114" href="images/iphone_retina_logo.png" />
<link rel="apple-touch-icon" sizes="144x144" href="images/ipad_retina_logo.png" />
<meta property="wb:webmaster" content="4e74fb62f6f0d410" />
<link href="css/main.css" rel="stylesheet" type="text/css" />
<link href="css/old.css" rel="stylesheet" type="text/css" />'.($css!=''?'
<link href="css/'.$css.'.css" rel="stylesheet" type="text/css" />':'').'
<script type="text/javascript" src="scripts/jquery.dookay.min.js"></script>
<script type="text/javascript" src="scripts/jquery.dookay.plugin.js"></script>
<script type="text/javascript" src="lib/jquery.rotate.js"></script>
<script type="text/javascript" src="lib/function.js"></script>
'.($is_mce>0?'<script type="text/javascript" src="lib/tiny_mce/jquery.tinymce.js"></script>':'').($isupimg>0?'<script type="text/javascript" src="'.$yjl_tpath.'templates/default/js/swfobject.js"></script><script type="text/javascript" src="'.$yjl_tpath.'images/uploadify/jquery.uploadify.v2.1.4.min.js"></script><style type="text/css">@import "'.$yjl_tpath.'images/uploadify/uploadify.css";</style>':'').($js_c!=''?'<script type="text/javascript">
$(document).ready(function(){
	'.$js_c.'
});
</script>':'').'<!--[if IE 6]>
<script type="text/javascript" src="scripts/dookay.png.js" ></script>
<script type="text/javascript">
	DD_belatedPNG.fix(\' #phead,.hd_ico,.mn_ico,.show \');
</script>
<![endif]-->
</head>
<body>';
	if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0))$s.='<div id="jlmsg_v" style="display: none;"><a href="#" onclick="$(\'#jlmsg_v\').hide();$(\'#jlmsg_isl\').val(\'1\');return false;"><img src="images/list_style02.gif" style="float: right;"></a><span id="msg_cs">0</span>条新咨询'.(($udb['qx']==5 && $udb['iszxjl']==1)?'，<a href="faq-no.html">查看并回答</a>':'').'<input type="hidden" id="jlmsg_isl" value="0"/><input type="hidden" id="jlmsg_v" value="0"/></div>';
	$s.='<div id="wrap">
	
	<link rel="stylesheet" href="house/statics/css/style.css">
	<div class="wrap">
  <div class="pbar">
  <div class="top clearfix">
    <div class="left">
      <ul>
        <li><span><img src="house/statics/images/Construction/top1.fw.png" class="hmiddle"></span><span class="hmiddle">100%服务品质</span></li>
        <li><span><img src="house/statics/images/Construction/top2.fw.png" class="hmiddle"></span><span class="hmiddle">A级信誉</span></li>
        <li><span><img src="house/statics/images/Construction/top3.fw.png" class="hmiddle"></span><span class="hmiddle">提升20%质量</span></li>
        <li><span><img src="house/statics/images/Construction/top4.fw.png" class="hmiddle"></span><span class="hmiddle">100%省钱</span></li>
      </ul>
    </div>';
	
	if($udb['uid']>0){
		$iswc=(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0)?0:1;
		$s.='<div class="right login">'
					.($iswc>0 ? '<a href="msg.php"><span class="hd_ico ico05" style="vertical-align: bottom;">&nbsp;</span>'.($udb['newpm']>0?'<small style="color: #f00;">'.($udb['newpm']>9?'9+':$udb['newpm']).'</small>':'').'</a>':'').'
					<a href="user-'.$udb['uid'].'.html">'.($udb['nc']!=''?$udb['nc']:$udb['email']).'</a>
					<a href="profile.php">个人中心</a>
					<a href="logout.php">退出登录</a>
			</div>';	
	}else{
		$s .= '<div class="right login">已有账号？&nbsp;<a href="login.php">登陆</a>&nbsp;&nbsp;&nbsp;&nbsp;<span class="orange"><a href="reg.php">免费注册</a></span></div>';
	}
	
  $s .='</div>
  </div><!-- pbar结束 -->
  <div class="phead">
  <div class="head clearfix">
    <div class="logo left">装修从此容易！</div>
    <div class="menu left" id="main_menu">
	  <a href="/" data-config="index"><span><img src="house/statics/images/Construction/选监理.fw.png"></span>首页</a>
      <a href="/new" data-match="/new"><span><img src="house/statics/images/Construction/选监理.fw.png"></span>选监理</a>
      <a href="photo-0.html" data-config="default"><span><img src="house/statics/images/Construction/项目.fw.png"></span>监理项目</a>
      <a href="/house/?s=/Active" data-match="/Active" data-config="break"><span><img src="house/statics/images/Construction/选监理.fw.png"></span>样板房参观</a>
      <a href="/house" data-match="/house"><span><img src="house/statics/images/Construction/谁施工好.fw.png"></span>谁施工好</a>
    </div>
    <div class="right" style="margin-top:15px"><img src="house/statics/images/Construction/Phone.png"></div>
  </div>
  </div><!-- phead 结束-->
  <div class="clear"></div>
</div>
	
</div>';

	$s.='</body></html>';
	return $s;
}

echo yjl_html_new('', 'index');
echo "<script>$('a').attr('target', '_top');</script>";
?>