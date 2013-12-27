<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?> 
<div> <div class="mainR"> <?php if($this->Config['ad_enable']) { ?> <?php if('myhome'== $this->Code) { ?> <?php if($this->Config['ad']['ad_list']['group_myhome']['middle_right']) { ?> <div class="R_AD"> <?php echo $this->Config['ad']['ad_list']['group_myhome']['middle_right']; ?></div> <?php } ?> <?php } elseif('tag'== $this->Get['mod']) { ?> <?php if($this->Config['ad']['ad_list']['tag_view']['middle_right']) { ?> <div class="R_AD"><?php echo $this->Config['ad']['ad_list']['tag_view']['middle_right']; ?></div> <?php } ?> <?php } ?> <?php } ?> <div id="topic_right_ajax_list"> <?php if(MEMBER_STYLE_THREE_TOL != 1) { ?> <div class="t_col_main_ln <?php echo $t_col_main_lb; ?>"> <?php if($member) { ?> <div class="sideBox"> <div class="avatar2"> <a href="index.php?mod=<?php echo $member['username']; ?>" title="<?php echo $member['username']; ?>"><img src="<?php echo $member['face']; ?>" alt="<?php echo $member['nickname']; ?>" onerror="javascript:faceError(this);"/></a> </div> <div class="avatar2_info"> <a href="index.php?mod=<?php echo $member['username']; ?>" title="@<?php echo $member['nickname']; ?>"><b><?php echo $member['nickname']; ?></b></a><?php echo $member['validate_html']; ?> <script type="text/javascript">
$(document).ready(function(){
$(".member_exp").mouseover(function(){$(".member_exp_c").show();});
$(".member_exp").mouseout(function(){$(".member_exp_c").hide();});
});
</script> <div class="member_exp"> <?php if($this->Config['level_radio']) { ?> <?php if($this->Config['topic_level_radio']) { ?> <a href="index.php?mod=settings&code=exp" title="点击查看微博等级"  target="_blank" class="ico_level wbL<?php echo $member['level']; ?>"><?php echo $member['level']; ?></a> <?php } ?> <?php } ?> <?php if($member['credits']) { ?>
积分：<a title="点击查看我的积分" href="index.php?mod=settings&code=extcredits"><b><?php echo $member['credits']; ?></b></a> <?php } ?> </div> </div> <div class="edit_sign"> <?php $member_signature = cut_str($member['signature'],20); ?> <?php if($member['uid'] == MEMBER_ID ) { ?> <span  title="个人签名：<?php echo $member['signature']; ?>"> <a href="javascript:viod(0);" onclick="follower_choose(<?php echo $member['uid']; ?>,'<?php echo $member['nickname']; ?>','topic_signature'); return false;" > <?php if($member['signature']) { ?> <?php echo $member_signature; ?> <?php } else { ?>编辑个人签名
<?php } ?> </a></span> <?php } else { ?><span  title="个人签名：<?php echo $member['signature']; ?>"> <?php if($member['signature']) { ?> <?php if('admin' == MEMBER_ROLE_TYPE) { ?> <a href="javascript:void(0);" onclick="follower_choose(<?php echo $member['uid']; ?>,'<?php echo $member['nickname']; ?>','topic_signature');" title="点击修改个人签名"> <em ectype="user_signature_ajax_<?php echo $member['uid']; ?>">(<?php echo $member_signature; ?>)</em> </a> <?php } ?> <?php } else { ?><?php echo $member['gender_ta']; ?>没有填写个人签名
<?php } ?> </span> <?php } ?> </div> </div> <div class="sideBox"> <div class="person_atten_l"> <p><span class="num"><a href="index.php?mod=<?php echo $member['username']; ?>&code=follow" title="<?php echo $member['nickname']; ?>关注的"><?php echo $member['follow_count']; ?></a></span></p> <p><a href="index.php?mod=<?php echo $member['username']; ?>&code=follow" title="<?php echo $member['nickname']; ?>关注的">关注</a> </p> </div> <div class="person_atten_l"> <p><span class="num"><a href="index.php?mod=<?php echo $member['username']; ?>&code=fans" title="关注<?php echo $member['nickname']; ?>的"><?php echo $member['fans_count']; ?></a></span></p> <p><a href="index.php?mod=<?php echo $member['username']; ?>&code=fans" title="关注<?php echo $member['nickname']; ?>的">粉丝</a> </p> </div> <div class="person_atten_r"> <p><span class="num"><a href="index.php?mod=<?php echo $member['username']; ?>" title="<?php echo $member['nickname']; ?>的微博"><?php echo $member['topic_count']; ?></a></span></p> <p><a href="index.php?mod=<?php echo $member['username']; ?>" title="<?php echo $member['nickname']; ?>的微博">微博</a> </p> </div> </div> <?php } ?> <script type="text/javascript">
$(document).ready(function(){
$(".sina_weibo").mouseover(function(){$(".sina_weibo_c").show();});
$(".sina_weibo").mouseout(function(){$(".sina_weibo_c").hide();});
$(".qqwb").mouseover(function(){$(".qqwb_c").show();});
$(".qqwb").mouseout(function(){$(".qqwb_c").hide();});
$(".qqim").mouseover(function(){$(".qqim_c").show();});
$(".qqim").mouseout(function(){$(".qqim_c").hide();});
$(".tel").mouseover(function(){$(".tel_c").show();});
$(".tel").mouseout(function(){$(".tel_c").hide();});
<?php if(is_array($medal_list)) { foreach($medal_list as $v) { ?>
$(".medal_<?php echo $v['id']; ?>").mouseover(function(){$(".medal_c_<?php echo $v['id']; ?>").show();});
$(".medal_<?php echo $v['id']; ?>").mouseout(function(){$(".medal_c_<?php echo $v['id']; ?>").hide();});
<?php } } ?>
});
</script> <ul class="Vimg"> <?php if('tag'!=$this->Get['mod']) { ?> <?php if($this->Config['sina_enable'] && sina_weibo_init($this->Config)) { ?> <li class="sina_weibo"> <?php echo sina_weibo_bind_icon($member['uid']); ?>
&nbsp; 
<div class="sina_weibo_c"> <div class="VM_box"> <div class="VM_top"> <div class="med_img"><img src="./templates/default/images/medal/M_sina.gif"></div> <div class="med_intro"> <p>新浪微博</p>
绑定后，可以使用新浪微博帐号进行登录，在本站发的微博可以同步发到新浪微博<br /> <?php $sina_return  = sina_weibo_has_bind($member['uid']); ?> <?php if(!$sina_return) { ?> <a href="index.php?mod=account&code=sina">绑定新浪微博</a> |
<?php } ?> <a target="_blank" href="index.php?mod=settings&code=user_medal">查看我的勋章</a> </div> </div> </div> </div> </li> <?php } ?> <?php if($this->Config['qqwb_enable'] && qqwb_init($this->Config)) { ?> <li class="qqwb"> <?php echo qqwb_bind_icon($member['uid']); ?>
&nbsp; 
<div class="qqwb_c"> <div class="VM_box"> <div class="VM_top"> <div class="med_img"><img src="./templates/default/images/medal/qqwb.png"></div> <div class="med_intro"> <p>腾讯微博</p>
绑定后，可以使用腾讯微博帐号进行登录，在本站发的微博可以同步发到腾讯微博<br /> <?php $qqwb_return  = qqwb_bind_icon($member['uid']); ?> <?php if(!$qqwb_return) { ?> <a href="index.php?mod=account&code=qqwb">绑定腾讯微博</a> |
<?php } ?> <a target="_blank" href="index.php?mod=settings&code=user_medal">查看我的勋章</a> </div> </div> </div> </div> </li> <?php } ?> <?php if($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config)) { ?> <li class="qqim"> <?php echo imjiqiren_bind_icon($member['uid']); ?>
&nbsp; 
<div class="qqim_c"> <div class="VM_box"> <div class="VM_top"> <div class="med_img"><img src="./templates/default/images/medal/M_qq.gif"></div> <div class="med_intro"> <p>QQ机器人</p>
用自己的QQ发微博、通过QQ签名发微博，如果有人@你、评论你、关注你、给你发私信，你都可以第一时间收到QQ机器人的通知<br /> <?php $imjiqiren_return  = imjiqiren_has_bind($member['uid']); ?> <?php if(!$imjiqiren_return) { ?> <a href="index.php?mod=tools&code=imjiqiren">绑定QQ机器人</a> |
<?php } ?> <a target="_blank" href="index.php?mod=settings&code=user_medal">查看我的勋章</a> </div> </div> </div> </div> </li> <?php } ?> <?php if($this->Config['sms_enable'] && sms_init($this->Config)) { ?> <li class="tel"> <?php echo sms_bind_icon($member['uid']); ?>
&nbsp; 
<div class="tel_c"> <div class="VM_box"> <div class="VM_top"> <div class="med_img"><img src="./templates/default/images/medal/Tel.gif"></div> <div class="med_intro"> <p>手机短信</p>
用自己的手机发微博、通过手机签名发微博，如果有人@你、评论你、关注你、给你发私信，你都可以第一时间收到手机短信的通知<br /> <?php $sms_return  = sms_has_bind($member['uid']); ?> <?php if(!$sms_return) { ?> <a href="index.php?mod=other&code=sms">绑定手机短信</a> |
<?php } ?> <a target="_blank" href="index.php?mod=settings&code=user_medal">查看我的勋章</a> </div> </div> </div> </div> </li> <?php } ?> <?php if($this->Config['yy_enable'] && yy_init($this->Config)) { ?> <li class="yy"> <?php echo yy_bind_icon($_mymember['uid']); ?>
&nbsp;</li> <?php } ?> <?php if($this->Config['renren_enable'] && renren_init($this->Config)) { ?> <li class="renren"> <?php echo renren_bind_icon($_mymember['uid']); ?>
&nbsp;</li> <?php } ?> <?php if($this->Config['kaixin_enable'] && kaixin_init($this->Config)) { ?> <li class="kaixin"> <?php echo kaixin_bind_icon($_mymember['uid']); ?>
&nbsp;</li> <?php } ?> <?php if($this->Config['fjau_enable'] && fjau_init($this->Config)) { ?> <li class="fjau"> <?php echo fjau_bind_icon($_mymember['uid']); ?>
&nbsp;</li> <?php } ?> <?php } ?> <?php if($member['validate'] || $medal_list) { ?> <?php if(is_array($medal_list)) { foreach($medal_list as $val) { ?> <?php $medal_type = unserialize($val['conditions']); ?> <li class="medal_<?php echo $val['id']; ?>"><a href="index.php?mod=other&code=medal" target="_blank"><img src="<?php echo $val['medal_img']; ?>"/></a> &nbsp; 
<div class="medal_c medal_c_<?php echo $val['id']; ?>"> <div class="VM_box"> <div class="VM_top"> <div class="med_img"><img src="<?php echo $val['medal_img']; ?>"/></div> <div class="med_intro"> <p><?php echo $val['medal_name']; ?></p> <?php echo $val['medal_depict']; ?> <br /> <?php if(MEMBER_ID != $member['uid']) { ?>
(他于：<?php echo $val['dateline']; ?> 获得) <br /> <?php if($medal_type['type'] == 'topic') { ?> <a href="index.php?mod=topic&code=myhome" target="_blank">我要发微博</a> |<?php } elseif($medal_type['type'] == 'reply') { ?><a href="index.php?mod=topic&code=new" target="_blank">我要发评论</a> |<?php } elseif($medal_type['type'] == 'tag') { ?><a href="index.php?mod=tag&code=<?php echo $medal_type['tagname']; ?>" target="_blank">我要发话题</a> |<?php } elseif($medal_type['type'] == 'invite') { ?><a href="index.php?mod=profile&code=invite" target="_blank">马上去邀请好友</a> |<?php } elseif($medal_type['type'] == 'shoudong') { ?>管理员手动发放  |	
<?php } ?> <?php } else { ?>(我于：<?php echo $val['dateline']; ?> 获得) <br /> <?php } ?> <a target="_blank" href="index.php?mod=settings&code=user_medal">查看我的勋章</a> </div> </div> </div> </div> </li> <?php } } ?> <?php } ?> </ul> </div> <?php } ?> </div> <?php echo $this->hookall_temp['global_usernav_extra2']; ?> <script language="javascript">
$(document).ready(function(){
/*
* ajax 右侧显示数据
* 这里的函数可以随便更改位置，加载的顺序也会不同。
*/
//可能感兴趣
get_refresh();
//热门话题推荐
get_hot_tag();
//人气用户推荐
//get_recommend_user();			
});		
function get_refresh(){
//可能感兴趣
right_show_ajax('<?php echo $member['uid']; ?>','refresh','refresh');
}
function get_hot_tag(){
//热门话题
right_show_ajax('<?php echo MEMBER_ID; ?>','hot_tag','hot_tag');
}
function get_recommend_user(){
//人气用户推荐
right_show_ajax('<?php echo MEMBER_ID; ?>','recommend_user','recommend_user');
}
</script> <script type="text/javascript">
$(document).ready(function(){
$(".SC_guanxingqu").click(function(){$(this).parent().toggleClass("fold_guanxingqu");$(".SC_guanxingqu_box").toggle();});
});
</script> <div class="side"> <h3>可能感兴趣的人<a class="btn SC_guanxingqu" href="javascript:void(0);"></a></h3> <div class="FTL SC_guanxingqu_box"> <div id="<?php echo $member['uid']; ?>_refresh"></div> </div> </div> <script type="text/javascript">
$(document).ready(function(){
$(".SC_rementuijian").click(function(){$(this).parent().toggleClass("fold_rementuijian");$(".SC_rementuijian_box").toggle();});
});
</script> <div class="side" id="hot_tag_div"> <h3>热门话题推荐<a class="btn SC_rementuijian" href="javascript:void(0);"></a></h3> <ul class="FTL SC_rementuijian_box"> <div id="<?php echo MEMBER_ID; ?>_hot_tag" style="padding:0 2px"></div> </ul> </div> <?php if(false!=($recommend_topic_user=Load::model('data_block')->recommend_topic_user(10))) { ?> <script type="text/javascript">
$(document).ready(function(){
$(".SC_renqituijian").click(function(){$(this).parent().toggleClass("fold_renqituijian");$(".SC_renqituijian_box").toggle();});
});
</script> <div class="side"> <h3>人气用户推荐<a class="btn SC_renqituijian" href="javascript:void(0);"></a></h3> <ul class="FTL FTL3 SC_renqituijian_box"> <div id="<?php echo MEMBER_ID; ?>_recommend_user"> <?php if(is_array($recommend_topic_user)) { foreach($recommend_topic_user as $val) { ?> <li> <a href="index.php?mod=<?php echo $val['username']; ?>" target="_blank"><img onerror="javascript:faceError(this);" src="<?php echo $val['face']; ?>" class="manface" onmouseover="get_user_choose(<?php echo $val['uid']; ?>,'_user',<?php echo $val['uid']; ?>)" onmouseout="clear_user_choose()"/></a> <b><a href="index.php?mod=<?php echo $val['username']; ?>" target="_blank"><?php echo $val['nickname']; ?></a></b> <div id="user_<?php echo $val['uid']; ?>_user"></div> <div id="alert_follower_menu_<?php echo $val['uid']; ?>"></div> <div id="global_select_<?php echo $val['uid']; ?>" class="alertBox alertBox_2" style="display:none"></div> <div id="get_remark_<?php echo $val['uid']; ?>" ></div> <div id="button_<?php echo $val['uid']; ?>" onclick="get_group_choose(<?php echo $val['uid']; ?>);"></div> <div id="Pmsend_to_user_area"></div> </li> <?php } } ?> </div> </ul> </div> <?php } ?> <?php if($fans_list) { ?> <script type="text/javascript">
$(document).ready(function(){
$(".SC_guanzhuta").click(function(){$(this).parent().toggleClass("fold_guanzhuta");$(".SC_guanzhuta_box").toggle();});
});
</script> <div class="side"> <h3>关注<?php echo $member['nickname']; ?>的人<a class="btn SC_guanzhuta" href="javascript:void(0)"></a></h3> <ul class="FTL FTL2 SC_guanzhuta_box"> <?php if(is_array($fans_list)) { foreach($fans_list as $val) { ?> <li class="h_h2"> <a href="index.php?mod=<?php echo $val['username']; ?>" target="_blank"> <img onerror="javascript:faceError(this);" src="<?php echo $val['face']; ?>" class="manface"/></a><span><a href="index.php?mod=<?php echo $val['username']; ?>" target="_blank"><?php echo $val['nickname']; ?></a></span> </li> <?php } } ?> <li><a href="index.php?mod=<?php echo $member['username']; ?>&code=fans">更多</a></li> </ul> </div> <?php } ?> <script type="text/javascript">
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
</script> <div class="side"> <h3>手机访问<?php echo $this->Config['site_name']; ?></h3> <div class="tabmenu"> <ul> <li onmouseover="tabChange(this,'tabcontent')" class="cli" ><img src="images/transparents.gif" class="icon_pf icpf_mclient" title="手机客户端"></li> <li onmouseover="tabChange(this,'tabcontent')" ><img src="images/transparents.gif" class="icon_pf icpf_3g" title="3G访问"></li> <li onmouseover="tabChange(this,'tabcontent')" ><img src="images/transparents.gif" class="icon_pf icpf_mphone" title="WAP访问"></li> <li onmouseover="tabChange(this,'tabcontent')"><img src="images/transparents.gif" class="icon_pf icpf_message" title="短信微博"></li> </ul> </div> <div id="tabcontent"> <ul name="tabul"><b>客户端</b>：更好的用户体验，支持<a href="index.php?mod=other&code=android" target="_blank">Android<a/>、<a href="index.php?mod=other&code=iphone" target="_blank">iPhone</a>手机即拍即传</ul> <ul class="hidden"><b>3G版</b>：智能手机访问<a href="index.php?mod=other&code=mobile" target="_blank"><?php echo $this->Config['site_url']; ?>/mobile</a>，享受类客户端的体验</ul> <ul class="hidden"><b>WAP版</b>：手机WAP访问地址<b><a href="index.php?mod=other&code=wap" target="_blank"><?php echo $this->Config['wap_url']; ?></a></b></ul> <ul class="hidden"><b>短信版</b>：<a href="index.php?mod=other&code=sms" rel="nofollow" target="_blank">手机短信</a> <?php if($this->Config['sms_enable'] && sms_init($this->Config)) { ?> <br />页绑定手机后，就可以发短信到<b><?php echo SMS_ID; ?></b>发微博啦！
<?php } ?> </ul> </div> </div> <script type="text/javascript">
$(document).ready(function(){
$(".SC_yijianfankui").click(function(){$(this).parent().toggleClass("fold_yijianfankui");$(".SC_yijianfankui_box").toggle();});
});
</script> <div class="side"> <h3><?php echo $this->Config['site_name']; ?>意见反馈<a class="btn SC_yijianfankui" href="javascript:void(0)"></a></h3> <ul class="FTL SC_yijianfankui_box"> <li>众人拾柴火焰高，如您有任何建议欢迎点<a href="index.php?mod=tag&code=意见反馈" target="_blank">意见反馈</a>告诉我们。</li> <li>&nbsp;</li> </ul> </div> <?php if($member['uid']) { ?> <div id="add_remark_<?php echo $member['uid']; ?>" class="alertBox" style="display:none" > <ul class="manBox"> <li> <div class="tt1"> <span>设置备注名</span> <div class="mclose" onclick="getElementById('add_remark_<?php echo $member['uid']; ?>').style.display=(getElementById('add_remark_<?php echo $member['uid']; ?>').style.display=='none')?'':'none'"></div> </div> <div class="mWarp"> <form action="ajax.php?mod=topic&code=add_remark" method="POST"  name="remarkform"   onsubmit="publishSubmit_remark('remark_<?php echo $member['uid']; ?>',<?php echo $member['uid']; ?>);return false;"> <input type="hidden" name="FORMHASH" value='<?php echo FORMHASH; ?>'/>
给朋友加个备注名，方便认出他是谁（0~6个字符）
<table > <tr> <td><input name="remark_<?php echo $member['uid']; ?>" type="text" id="remark_<?php echo $member['uid']; ?>" class="text-area2" value="<?php echo $buddys['remark']; ?>" size="6" maxlength="6"/> </td> <td align="left"> <input type="button" class="shareI" value="保 存" onclick="publishSubmit_remark('remark_<?php echo $member['uid']; ?>',<?php echo $member['uid']; ?>);return false;" /> </td> </tr> </table> </form> </div> </li> </ul> </div> <?php } ?> </div> </div>