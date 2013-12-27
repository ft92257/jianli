<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <head> <?php $__my=$this->MemberHandler->MemberFields; ?> <base href="<?php echo $this->Config['site_url']; ?>/" /> <?php $conf_charset=$this->Config['charset']; ?> <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $conf_charset; ?>" /> <meta http-equiv="x-ua-compatible" content="ie=7" /> <title><?php echo $this->Config['site_name']; ?>(<?php echo $this->Config['site_domain']; ?>)<?php echo $this->Config['page_title']; ?></title> <meta name="Keywords" content="<?php echo $this->MetaKeywords; ?>,<?php echo $this->Config['site_name']; ?><?php echo $this->Config['meta_keywords']; ?>" /> <meta name="Description" content="<?php echo $this->MetaDescription; ?>,<?php echo $this->Config['site_notice']; ?><?php echo $this->Config['meta_description']; ?>" /> <script type="text/javascript">
var thisSiteURL = '<?php echo $this->Config['site_url']; ?>/';
var thisTopicLength = '<?php echo $this->Config['topic_input_length']; ?>';
var thisMod = '<?php echo $this->Module; ?>';
var thisCode = '<?php echo $this->Code; ?>';
var thisFace = '<?php echo $__my['face_small']; ?>';
<?php $qun_setting = ConfigHandler::get('qun_setting'); ?> <?php if($qun_setting['qun_open']) { ?>
var isQunClosed = false;
<?php } else { ?>var isQunClosed = true;
<?php } ?>
function faceError(imgObj)
{
var errorSrc = '<?php echo $this->Config['site_url']; ?>/images/noavatar.gif';
imgObj.src = errorSrc;
}
</script> <link rel="shortcut icon" href="favicon.ico" /> <link href="templates/default/styles/main.css?build+20120829" rel="stylesheet" type="text/css" /> <link href="templates/default/styles/index.css?build+20120829" rel="stylesheet" type="text/css" /> <script type="text/javascript" src="templates/default/js/min.js?build+20120829"></script> <script type="text/javascript" src="templates/default/js/common.js?build+20120829"></script> <?php if($this->Config['theme_id']) { ?> <link href="theme/<?php echo $this->Config['theme_id']; ?>/theme.css?v=<?php echo SYS_BUILD; ?>" rel="stylesheet" type="text/css" /> <?php } ?> <style type="text/css"> <?php if($this->Config['theme_text_color']) { ?>
body{ color:<?php echo $this->Config['theme_text_color']; ?>;}
<?php } ?> <?php if($this->Config['theme_bg_color']) { ?>
body{ background-color:<?php echo $this->Config['theme_bg_color']; ?>;}
<?php } ?> <?php if($this->Config['theme_bg_image']) { ?>
body{ background-image:url(<?php echo $this->Config['theme_bg_image']; ?>);}
<?php } ?> <?php if($this->Config['theme_bg_position']) { ?>
body{ background-position:<?php echo $this->Config['theme_bg_position']; ?>;}
<?php } ?> <?php if($this->Config['theme_bg_repeat']) { ?>
body{ background-repeat:<?php echo $this->Config['theme_bg_repeat']; ?>;}
<?php } ?> <?php if($this->Config['theme_text_color']) { ?>
body{ color:<?php echo $this->Config['theme_text_color']; ?>;}
<?php } ?> <?php if($this->Config['theme_link_color']) { ?>
a:link{ color:<?php echo $this->Config['theme_link_color']; ?>;}
<?php } ?>
a.artZoom{ cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_b.cur), pointer; }
.artZoomBox a.maxImgLink { cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_s.cur), pointer; }
a.artZoom2{ cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_b.cur), pointer; }
a.artZoom3{ cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_b.cur), pointer; }
.artZoomBox a.maxImgLink3 { cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_s.cur), pointer; }
a.artZoomAll{ cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_b.cur), pointer; }
.artZoomBox a.maxImgLinkAll { cursor:url(<?php echo $this->Config['site_url']; ?>/templates/default/images/magnifier_s.cur), pointer; }
</style> </head> <?php echo $additional_str; ?> <body> <div id="headWrap"> <?php if(defined('NEDU_MOYO')) { ?> <h1 id="logo_nedu"><a title="<?php echo $this->Config['site_name']; ?>" href="index.php" ></a></h1> <?php } else { ?><h1 id="logo"><a title="<?php echo $this->Config['site_name']; ?>" href="index.php"></a></h1> <?php } ?> </div> <div id="mainWrapper"> <div id="topbox"> <div id="tleft"> <?php if($this->Config['slide_index_enable'] && ($slide_config=ConfigHandler::get('slide_index')) && $slide_config['enable'] && $slide_config['list']) { ?> <script src="templates/default/js/kinslideshow.js?build+20120829" type="text/javascript"></script> <script type="text/javascript">
$(function(){
$("#KinSlideshow").KinSlideshow({
moveStyle:"down",			//切换方向 可选值：【 left | right | up | down 】
intervalTime:3,   			//设置间隔时间为5秒 【单位：秒】 [默认为5秒]
moveSpeedTime:400 , 		//切换一张图片所需时间，【单位：毫秒】[默认为400毫秒]
isHasTitleFont:false,		//是否显示标题文字 可选值：【 true | false 】
isHasTitleBar:false,		//是否显示标题背景 可选值：【 true | false 】[默认为true]	
//标题文字样式，(isHasTitleFont = true 前提下启用)  
titleBar:{titleBar_height:30,titleBar_bgColor:"#08355c",titleBar_alpha:0.3},
titleFont:{TitleFont_size:12,TitleFont_color:"#FFFFFF",TitleFont_weight:"normal"},
//按钮样式设置，(isHasBtn = true 前提下启用) 
btn:{btn_bgColor:"#FFFFFF",btn_bgHoverColor:"#1072aa",btn_fontColor:"#000000",btn_fontHoverColor:"#FFFFFF",btn_borderColor:"#cccccc",btn_borderHoverColor:"#1188c0",btn_borderWidth:1}
});
});
</script> <div id="KinSlideshow" style="visibility:hidden;width:680px;height:220px;overflow:hidden;position:relative;"> <?php if(is_array($slide_config['list'])) { foreach($slide_config['list'] as $_v) { ?> <?php if($_v['enable'] == 1) { ?> <a href="<?php echo $_v['href']; ?>" target="_blank"><img src="<?php echo $_v['src']; ?>" alt="" width="680" height="220" onerror="javascript:faceError(this);"/></a> <?php } ?> <?php } } ?> </div> <?php } ?> <div class="talking"> <strong>正在热议：</strong> <div id="Tacticle" class="acticle"> <div id="indemo"> <div id="Tacticle_s"> <?php if($r_tags) { ?> <?php if(is_array($r_tags)) { foreach($r_tags as $val) { ?> <a href="index.php?mod=tag&code=<?php echo $val['name']; ?>" class="Ts"><?php echo $val['name']; ?></a> <?php } } ?> <?php } ?> </div> <div id="demo2"></div> </div> <script type="text/javascript">
/*
两秒后再执行
*/
$(document).ready(function(){
var speed=40;
var tab=document.getElementById("Tacticle");
var tab1=document.getElementById("Tacticle_s");
var tab2=document.getElementById("demo2");
tab2.innerHTML=tab1.innerHTML;
function Marquee(){
if(tab2.offsetWidth-tab.scrollLeft<=0)
tab.scrollLeft-=tab1.offsetWidth
else{
tab.scrollLeft++;
}
}
var MyMar=setInterval(Marquee,speed);
tab.onmouseover=function() {clearInterval(MyMar)};
tab.onmouseout=function() {MyMar=setInterval(Marquee,speed)};
});
function guestLoginSubmit()
{
<?php if($this->Config['seccode_login']) { ?>
var username = $("#username_input").val();
var psw = $("#password_input").val();
if (username.length<1 || psw.length<1) {
location.href = "index.php?mod=login";
} else {
showSeccodeDialog();
}
<?php } else { ?>$('#guest_login').submit();
<?php } ?>
}
</script> </div> </div> </div> <div class="loginbox"> <a title="注册<?php echo $this->Config['site_name']; ?>" onclick="window.location.href='<?php echo $this->Config['site_url']; ?>/index.php?mod=member';return false;" class="btn_reg">注册<?php echo $this->Config['site_name']; ?></a> <div class="login_area"> <?php $login_extract=jsg_member_login_extract(); ?> <?php if($login_extract) { ?> <?php $login_extract_forms=$login_extract['login_forms']; ?> <form method="<?php echo $login_extract_forms['method']; ?>" action="<?php echo $login_extract_forms['action']; ?>" id="guest_login"> <?php if(is_array($login_extract_forms['hidden_inputs'])) { foreach($login_extract_forms['hidden_inputs'] as $v) { ?> <input type="hidden" name="<?php echo $v['name']; ?>" value="<?php echo $v['value']; ?>" /> <?php } } ?> <div class="item"> <label for="username" class="labelTag">昵称：</label> <span class="enterInput"> <input type="text" name="<?php echo $login_extract_forms['username_input']['name']; ?>" class="inputtextTag" id="username_input"> </span> </div> <div class="item"> <label for="password" class="labelTag">密&nbsp;&nbsp;&nbsp;码：</label> <span class="enterInput"> <input type="password" name="<?php echo $login_extract_forms['password_input']['name']; ?>" class="inputtextTag" id="password_input"> </span> </div> <div class="item pos_rel"> <label class="labelTag">&nbsp;</label> <span class="enterInput"></span> <div class="login-tiparea autologin-tiparea" id="autologin_tiparea"> <div class="login-tiparea-top"></div> </div> </div> <div class="clearfix"> <label class="labelTag">&nbsp;</label> <!-- <span class="enterInput"> <input type="submit" class="btn_login" value="<?php echo $login_extract_forms['submit_input']['value']; ?>" name="<?php echo $login_extract_forms['submit_input']['name']; ?>"/></span> --> <span class="enterInput"> <input type="submit" class="btn_login" value="" name="<?php echo $login_extract_forms['submit_input']['name']; ?>"/></span> </div> <div class="clearfix2"> <label class="labelTag">&nbsp;</label> </div> </form> <?php } else { ?> <form method="POST"  action="<?php echo $this->Config['site_url']; ?>/index.php?mod=login&code=dologin" id="guest_login" onKeyDown="if(event.keyCode==13) guestLoginSubmit();" autocomplete="off"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/> <?php if($this->Config['seccode_login']) { ?> <input type="hidden" name="seccode" id="seccode_input" value=""> <?php } ?> <div class="item"> <label for="username" class="labelTag">昵&nbsp;&nbsp;称：</label> <span class="enterInput"> <input type="text" name="username" class="inputtextTag" id="username_input"> </span> </div> <div class="item"> <label for="password" class="labelTag">密&nbsp;&nbsp;码：</label> <span class="enterInput"> <input type="password" name="password" class="inputtextTag" id="password_input"> </span> </div> <div class="item pos_rel"> <label class="labelTag">&nbsp;</label> <span class="enterInput"> <label id="savelogin"> <input type="checkbox" name="savelogin" class="inputcheckTag" checked title="请不要在公共电脑上使用自动登录功能">
下次自动登录 </label> <a onclick="window.location.href='<?php echo $this->Config['site_url']; ?>/index.php?mod=get_password';return false;" href="javascript:void(0)" class="forgetPass" title="点此可通过2种方式重设密码">忘记密码？</a> </span> <div class="login-tiparea autologin-tiparea" id="autologin_tiparea"> <div class="login-tiparea-top"></div> </div> </div> <div class="clearfix"> <label class="labelTag">&nbsp;</label> <span class="enterInput"> <input type="button" class="btn_login" value="" onclick="guestLoginSubmit();"/></span> </div> <div class="clearfix2"> <p class="tlb_P">或使用如下帐号登录：</p> <label class="labelTag">&nbsp;</label> <?php if($this->Config['sina_enable'] && sina_weibo_init()) { ?> <?php echo sina_weibo_login('s'); ?> <?php } ?> <?php if($this->Config['qqwb_enable'] && qqwb_init()) { ?> <?php echo qqwb_login('s'); ?> <?php } ?> <?php if($this->Config['yy_enable'] && yy_init()) { ?> <?php echo yy_login('s'); ?> <?php } ?> <?php if($this->Config['renren_enable'] && renren_init()) { ?> <?php echo renren_login('s'); ?> <?php } ?> <?php if($this->Config['kaixin_enable'] && kaixin_init()) { ?> <?php echo kaixin_login('s'); ?> <?php } ?> <?php if($this->Config['fjau_enable'] && fjau_init()) { ?> <?php echo fjau_login('s'); ?> <?php } ?> </div> </form> <?php } ?> </div> </div> </div> <div id="midBox"> <div class="mleft"> <h1 class="htitle2">最受关注用户</h1> <ul class="userlist"> <?php if(is_array($r_users)) { foreach($r_users as $val) { ?> <?php ++$_floor; ?> <li><a target="_blank" href="index.php?mod=<?php echo $val['username']; ?>"><img  title="<?php echo $val['nickname']; ?>" src="<?php echo $val['face']; ?>"  onerror="javascript:faceError(this);"><?php echo $val['nickname']; ?></a></li> <?php } } ?> </ul> <h1 class="htitle4">近期活跃用户</h1> <ul class="userlist"> <?php if(is_array($day2_r_users)) { foreach($day2_r_users as $val) { ?> <li><a target="_blank" href="index.php?mod=<?php echo $val['username']; ?>"><img  title="<?php echo $val['nickname']; ?>" src="<?php echo $val['face_original']; ?>"  onerror="javascript:faceError(this);"><?php echo $val['nickname']; ?></a></li> <?php } } ?> </ul> </div> <div class="mcenter"> <h1 class="htitle">大家都在说</h1> <script type="text/javascript">
var _recommendCount = parseInt("<?php echo $recommend_count; ?>");
function action()
{
var strhtml;
strhtml = $('#ajaxcontent .indexrow').eq(_recommendCount-1).html();
if(strhtml == null){
return false;
}
//$('#ajaxcontent .indexrow').eq(0).appendTo("#ajaxcontent");
$('#ajaxcontent .indexrow').eq(_recommendCount-1).remove();
$('#ajaxcontent').prepend('<div class="indexrow" style="display:none;" id="indexrowid">'+strhtml+'</div>');
$('#ajaxcontent .indexrow').eq(0).slideDown(500);
}
$(document).ready(function(){
var Interval;
Interval = setInterval('action()', 4000);
$("#ajaxcontent").hover(
function(){clearInterval(Interval);},
function(){Interval = setInterval('action()',4000);}
);
});
</script> <style type="text/css">
.oriTxt{ margin:0;}
.feedCell .from{ padding:0;}
.option{ width:10px; display:none; overflow:hidden;}
</style> <?php if($recommend_topics) { ?> <div class="comBox" id="ajaxcontent" style="margin:10px 0; overflow:hidden;"> <?php if(is_array($recommend_topics)) { foreach($recommend_topics as $val) { ?> <?php $_ad++; ?> <div class="indexrow" id="topic_list_<?php echo $val['tid']; ?>"> <div class="feedCell" style="width:404px; overflow:hidden"> <div class="avatar"><a href="index.php?mod=<?php echo $val['username']; ?>"><img onerror="javascript:faceError(this);" src="<?php echo $val['face']; ?>" /></a></div> <div class="Contant"> <div class="oriTxt"> <p><a title="<?php echo $val['username']; ?>" href="index.php?mod=<?php echo $val['username']; ?>"><?php echo $val['nickname']; ?></a><?php echo $val['validate_html']; ?>: <span><?php echo $val['content']; ?></span></p> </div> <?php if($val['attachid'] && $val['attach_list']) { ?> <?php $val['attach_key']=$val['tid'].'_'.mt_rand(); ?> <ul class="attachList" id="attach_area_<?php echo $val['attach_key']; ?>"> <?php if(is_array($val['attach_list'])) { foreach($val['attach_list'] as $iv) { ?> <li><img src="<?php echo $iv['attach_img']; ?>" class="attachList_img"/><div class="attachList_att"><p class="attachList_att_name"><b><?php echo $iv['attach_name']; ?></b> <a href="javascript:void(0);"  onclick="downattach(<?php echo $iv['id']; ?>);">下载</a>(<?php echo $iv['attach_down']; ?>次)</p><p class="attachList_att_doc"><?php echo $iv['attach_size']; ?> 积分：<?php echo $iv['attach_score']; ?>分</p></div></li> <?php } } ?> </ul> <?php } ?> <?php if($val['imageid'] && $val['image_list']) { ?> <?php $val['image_key']=$val['tid'].'_'.mt_rand(); ?> <ul class="imgList" id="image_area_<?php echo $val['image_key']; ?>"> <?php if(is_array($val['image_list'])) { foreach($val['image_list'] as $iv) { ?> <li> <a href="index.php?mod=topic&code=<?php echo $val['tid']; ?>"  class="miniImg"> <img style="width:<?php echo $this->Config['thumbwidth']; ?>px; height:<?php echo $this->Config['thumbheight']; ?>px;" src="<?php echo $iv['image_small']; ?>" /></a> </li> <?php } } ?> </ul> <?php } ?> <?php if($val['is_vote'] > 0) { ?> <?php $val['vote_key']=$val['tid'].'_'.$val['random'] ?> <ul class="imgList" id="vote_detail_<?php echo $val['vote_key']; ?>"> <li><a href="javascript:;" onclick="getVoteDetailWidgets('<?php echo $val['vote_key']; ?>', <?php echo $val['is_vote']; ?>);"><img src='./images/vote_pic_01.gif'/></a> </li> </ul> <div id="vote_area_<?php echo $val['vote_key']; ?>" style="display:none;"> <div class="blogTxt"> <div class="top"></div> <div class="mid" id="vote_content_<?php echo $val['vote_key']; ?>"> </div> <div class="bottom"></div> </div> </div> <?php } ?> <?php if($val['videoid'] and $this->Config['video_status']) { ?> <div class="feedUservideo"> <a href="index.php?mod=topic&code=<?php echo $val['tid']; ?>" class="miniImg"> <div id="play_<?php echo $val['VideoID']; ?>" class="vP"><img src="images/feedvideoplay.gif"  /></div> <img src="<?php echo $val['VideoImg']; ?>" style="border:none"/> </a></div> <?php } ?> <?php if($val['musicid']) { ?> <?php if($val['xiami_id']) { ?> <div class="feedUserImg"><embed width="257" height="33" wmode="transparent" type="application/x-shockwave-flash" src="http://www.xiami.com/widget/0_<?php echo $val['xiami_id']; ?>/singlePlayer.swf"></embed></div> <?php } else { ?><div class="feedUserImg"><div id="play_<?php echo $val['MusicID']; ?>"></div><img src="images/music.gif" title="点击播放音乐" onClick="javascript:showFlash('music', '<?php echo $val['MusicUrl']; ?>', this, '<?php echo $val['MusicID']; ?>');" style="cursor:pointer; border:none;" /> </div> <?php } ?> <?php } ?> <script type="text/javascript"> var __TOPIC_VIEW__ = '<?php echo $topic_view; ?>'; </script> <?php if(($tpid=$val['top_parent_id'])>0 && !in_array($this->Code, array('forward', 'reply'))) { ?> <?php if(('mycomment'==$this->Code || $topic_view) && 'reply'==$val['type'] && $tpid!=($pid=$val['parent_id']) && $parent_list[$pid]) { ?> <p class="feedP">回复{<a
href="index.php?mod=<?php echo $parent_list[$pid]['username']; ?>"><?php echo $parent_list[$pid]['nickname']; ?>：</a><span><?php echo $parent_list[$pid]['content']; ?></span>}</p> <?php } ?> <?php if(!$topic_view) { ?> <?php $pt=$parent_list[$tpid]; ?> <div class="blogTxt"> <div class="top"></div> <div class="mid"><p> <?php if($pt) { ?> <span> <a
href="index.php?mod=<?php echo $pt['username']; ?>"
onmouseover="get_user_choose(<?php echo $pt['uid']; ?>,'_reply_user',<?php echo $val['tid']; ?>);"
onmouseout="clear_user_choose();"> <?php echo $pt['nickname']; ?> </a> <?php echo $pt['validate_html']; ?> :   <span
id="user_<?php echo $val['tid']; ?>_reply_user"></span> </span> <?php $TPT_ = $TPT_ ? $TPT_ : 'TPT_'; ?> <span id="topic_content_<?php echo $TPT_; ?><?php echo $pt['tid']; ?>_short"><?php echo $pt['content']; ?></span> <span
id="topic_content_<?php echo $TPT_; ?><?php echo $pt['tid']; ?>_full"></span> <?php if($pt['longtextid'] > 0) { ?> <span> <a href="javascript:;"
onclick="view_longtext('<?php echo $pt['longtextid']; ?>', '<?php echo $pt['tid']; ?>', this, '<?php echo $TPT_; ?>', '<?php echo $val['tid']; ?>');return false;">查看全文</a> </span> <?php } ?> <?php if($pt['attachid'] && $pt['attach_list']) { ?> <table class="attachst" style="width:440px;"> <?php if(is_array($pt['attach_list'])) { foreach($pt['attach_list'] as $iv) { ?> <tr> <td class="attachst_img"><img src="<?php echo $iv['attach_img']; ?>" /></td> <td class="attachst_att"> <p class="attachList_att_name"><b><?php echo $iv['attach_name']; ?></b>&nbsp;（<?php echo $iv['attach_size']; ?>）</p> <p class="attachList_att_doc"><a href="javascript:void(0);"
onclick="downattach(<?php echo $iv['id']; ?>);">点此下载</a>（需<?php echo $iv['attach_score']; ?>积分，已下载<?php echo $iv['attach_down']; ?>次）</p> </td> </tr> <?php } } ?> </table> <?php } ?> <?php if($pt['imageid'] && $pt['image_list']) { ?> <?php $pt['image_key']=$pt['tid'].'_'.mt_rand(); ?> <ul class="imgList" id="image_area_<?php echo $pt['image_key']; ?>"> <?php if(is_array($pt['image_list'])) { foreach($pt['image_list'] as $iv) { ?> <?php $ivw=min(440, $iv['image_width']); ?> <li><a href="<?php echo $iv['image_original']; ?>" class="artZoomAll"
rel="<?php echo $iv['image_small']; ?>" rev="<?php echo $pt['image_key']; ?>"><img
src="./images/grey.gif" data-original="<?php echo $iv['image_small']; ?>" /></a> <div class="artZoomBox" style="display:none;"> <div class="tool"><a title="向左转" href="#" class="imgLeft">向左转</a><a 
title="向右转" href="#" class="imgRight">向右转</a><a title="查看原图"
href="<?php echo $iv['image_original']; ?>" class="viewImg">查看原图</a></div> <a class="maxImgLinkAll" href="<?php echo $iv['image_original']; ?>"> <img
src="./images/grey.gif" data-original="<?php echo $iv['image_original']; ?>" maxWidth="440" width="<?php echo $ivw; ?>" class="maxImg"></a></div> </li> <?php } } ?> </ul> <?php } ?> <?php if($pt['is_vote'] > 0) { ?> <?php $__vote_key = $pt['tid'].'_'.$pt['random'] ?> <ul class="imgList" id="vote_detail_<?php echo $__vote_key; ?>"> <li><a href="javascript:;" onclick="getVoteDetailWidgets('<?php echo $__vote_key; ?>', <?php echo $pt['is_vote']; ?>);"><img src='./images/vote_pic_01.gif' /></a></li> </ul> <div id="vote_area_<?php echo $__vote_key; ?>" style="display: none;"> <div class="vote_zf_box" id="vote_content_<?php echo $__vote_key; ?>"></div> </div> <?php } ?> <?php if($pt['videoid'] and $this->Config['video_status']) { ?> <div class="feedUservideo"> <a onClick="javascript:showFlash('<?php echo $pt['VideoHosts']; ?>', '<?php echo $pt['VideoLink']; ?>', this, '<?php echo $pt['VideoID']; ?>','<?php echo $pt['VideoUrl']; ?>');"> <div id="play_<?php echo $pt['VideoID']; ?>" class="vP"><img src="images/feedvideoplay.gif" /></div> <img src="<?php echo $pt['VideoImg']; ?>" style="border: none" /> </a></div> <?php } ?> <?php if($pt['musicid']) { ?> <?php if($pt['xiami_id']) { ?> <div class="feedUserImg"> <embed width="257" height="33" wmode="transparent" type="application/x-shockwave-flash" src="http://www.xiami.com/widget/0_<?php echo $pt['xiami_id']; ?>/singlePlayer.swf"></embed></div> <?php } else { ?><div class="feedUserImg"> <div id="play_<?php echo $pt['MusicID']; ?>"></div> <img src="images/music.gif" title="点击播放音乐" onClick="javascript:showFlash('music', '<?php echo $pt['MusicUrl']; ?>', this, '<?php echo $pt['MusicID']; ?>');" style="cursor: pointer; border: none;" /></div> <?php } ?> <?php } ?> </p> <div style="float:left; padding:5px 0; margin:0;"> <a href="index.php?mod=topic&code=<?php echo $tpid; ?>" target="_blank">原文评论(<?php echo $pt['replys']; ?>)</a>&nbsp;
<a onclick="get_forward_choose(<?php echo $tpid; ?>);return false;"href="index.php?mod=topic&code=<?php echo $tpid; ?>" target="_blank">转发原文(<?php echo $pt['forwards']; ?>)</a>&nbsp;
<?php echo $pt['from_html']; ?></div> <?php } else { ?> <?php $val['reply_disable']=0; ?> <p><span>原始微博已删除</span></p> <?php } ?> </div> <div class="bottom"></div> </div> <?php } ?> <?php } ?> <div class="from"> <div class="mycome"> <?php if(!$no_from) { ?> <?php echo $val['from_html']; ?> <?php } ?> </div> <span style="float:right;"><a href="index.php?mod=topic&code=<?php echo $val['tid']; ?>"><?php echo $val['dateline']; ?></a></span> </div> </div> </div> <div class="mBlog_linedot" style="width:404px; overflow:hidden"></div> </div> <?php } } ?> </div> <?php } ?> <div id="Pcontent" style="z-index:100; position:absolute; height:550px; overflow:hidden; margin:0;"></div> </div> <div class="mright"> <div class="dlblank"> <h1 class="htitle3">公告</h1> <p></p> <?php if(is_array($list_notice)) { foreach($list_notice as $notice) { ?> <p><a href="index.php?mod=other&code=notice&ids=<?php echo $notice['id']; ?>" title="<?php echo $notice['titles']; ?>"><?php echo $notice['title']; ?></a></p> <?php } } ?> </div> <script type="text/javascript">
function tabChange(obj,id)
{
var arrayli = obj.parentNode.getElementsByTagName("li"); //获取li数组
var arrayul = document.getElementById(id).getElementsByTagName("ul"); //获取ul数组
for(i=0;i<arrayul.length;i++)
{
if(obj==arrayli[i])
{
arrayli[i].className = "cli";
arrayul[i].className = "";
}
else
{
arrayli[i].className = "";
arrayul[i].className = "hidden";
}
}
}
</script> <h3 style=" width:230px; float:left; margin:15px 0 5px 0; color:#555; font-weight:600;">手机访问<?php echo $this->Config['site_name']; ?></h3> <div class="guest_table"> <div class="tabmenu"> <ul> <li onmouseover="tabChange(this,'tabcontent')" class="cli" ><img src="images/transparents.gif" class="icon_pf icpf_mclient" title="手机客户端"></li> <li onmouseover="tabChange(this,'tabcontent')" ><img src="images/transparents.gif" class="icon_pf icpf_3g" title="3G访问"></li> <li onmouseover="tabChange(this,'tabcontent')" ><img src="images/transparents.gif" class="icon_pf icpf_mphone" title="WAP访问"></li> <li onmouseover="tabChange(this,'tabcontent')"><img src="images/transparents.gif" class="icon_pf icpf_message" title="短信微博"></li> </ul> </div> <div id="tabcontent"> <ul name="tabul"><b>客户端</b>：更好的用户体验，支持<a href="index.php?mod=other&code=android" target="_blank">Android<a/>、<a href="index.php?mod=other&code=iphone" target="_blank">iPhone</a>手机即拍即传</ul> <ul class="hidden"><b>3G版</b>：智能手机访问<a href="index.php?mod=other&code=mobile" target="_blank"><?php echo $this->Config['site_url']; ?>/mobile</a>，享受类客户端的体验</ul> <ul class="hidden"><b>WAP版</b>：手机WAP访问地址<b><a href="index.php?mod=other&code=wap" target="_blank"><?php echo $this->Config['wap_url']; ?></a></b></ul> <ul class="hidden"><b>短信版</b>：<a href="index.php?mod=other&code=sms" rel="nofollow" target="_blank">手机短信</a> <?php if($this->Config['sms_enable'] && sms_init($this->Config)) { ?> <br />页绑定手机后，就可以发短信到<b><?php echo SMS_ID; ?></b>发微博啦！
<?php } ?> </ul> </div> </div> <?php if($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config)) { ?> <div class="visBox"> <p>使用<a href="index.php?mod=tools&code=imjiqiren" rel="nofollow"><?php echo $this->Config['site_name']; ?>QQ机器人</a> <br /> 加QQ号：<b> <?php echo imjiqiren_user_qq_robot(); ?> </b> 为好友，按提示进行绑定便可用QQ发微博啦！</p> </div> <?php } ?> <?php if($this->Config['ad_enable']) { ?> <div style="text-align:center" class="Ir_AD"> <?php echo $this->Config['ad']['ad_list']['topic_']['middle_right']; ?> </div> <?php } ?> </div> </div> <?php if($this->Config['ad_enable']) { ?> <div style="text-align:center" class="I_AD"><?php echo $this->Config['ad']['ad_list']['topic_']['footer']; ?></div> <?php } ?> <div class="mlink"> <span class="lf"> <?php if($this->
Config['default_module']==$this->Module && !$this->Code) { ?> <?php $link_config=ConfigHandler::get('link'); ?> <?php if($link_config) { ?>
友情链接：
<?php if(is_array($link_config)) { foreach($link_config as $link) { ?> <?php if(!empty($link['logo'])) { ?> <a href="<?php echo $link['url']; ?>" target="_blank"><img src="<?php echo $link['logo']; ?>" width="88" height="31" border="0" alt="<?php echo $link['name']; ?>"></a> <?php } else { ?><a href="<?php echo $link['url']; ?>" target="_blank"><?php echo $link['name']; ?></a> <?php } ?>
&nbsp;
<?php } } ?> <?php } ?> <?php } ?> </span> <span class="rt"> <?php $about_link = ConfigHandler::get('about_link'); ?> <?php if(is_array($about_link)) { foreach($about_link as $about_id => $_about) { ?> <?php global $rewriteHandler; if($rewriteHandler) $_about['link']=$rewriteHandler->
formatURL($_about['link'],null); ?> <a href="<?php echo $_about['link']; ?>"><?php echo $_about['name']; ?></a>&nbsp;
<?php } } ?> <?php echo $this->Config['copyright']; ?>&nbsp;<a href="http://www.miibeian.gov.cn/" rel="nofollow" target="_blank" title="网站备案"><?php echo $this->Config['icp']; ?></a> <?php echo $this->Config['tongji']; ?><?php $__server_execute_time = round(microtime(true) - $GLOBALS['_J']['time_start'], 5) . " Second "; ?> <?php $__gzip_tips = ((defined('GZIP') && GZIP) ? "&nbsp;Gzip Enable." : "Gzip Disable."); ?> <span class="f10" title="<?php echo $__server_execute_time; ?>,<?php echo $__gzip_tips; ?>">执行信息</span> </span> </div> <script type="text/javascript">
$(document).ready(function(){
//图片延迟加载
$("ul.imgList img, div.avatar img.lazyload").lazyload({
skip_invisible : false,
threshold : 200,
effect : "fadeIn"
});
$(".sinaweiboLogin").mouseover(function(){$(".tlb_sina").show();});$(".sinaweiboLogin").mouseout(function(){$(".tlb_sina").hide();});
$(".qqweiboLogin").mouseover(function(){$(".tlb_qq").show();});$(".qqweiboLogin").mouseout(function(){$(".tlb_qq").hide();});
$(".yyLogin").mouseover(function(){$(".tlb_yy").show();});$(".yyLogin").mouseout(function(){$(".tlb_yy").hide();});
$(".renrenLogin").mouseover(function(){$(".tlb_renren").show();});$(".renrenLogin").mouseout(function(){$(".tlb_renren").hide();});
$(".kaixinLogin").mouseover(function(){$(".tlb_kaixin").show();});$(".kaixinLogin").mouseout(function(){$(".tlb_kaixin").hide();});
$(".fjauLogin").mouseover(function(){$(".tlb_fj").show();});$(".fjauLogin").mouseout(function(){$(".tlb_fj").hide();});
});
</script> </div> </div> </body> </html>