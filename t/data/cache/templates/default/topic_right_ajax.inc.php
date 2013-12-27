<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?> 
<div class="t_col_main_ln <?php echo $t_col_main_lb; ?>"> <?php if($member) { ?> <div class="sideBox"> <div class="avatar2"> <a href="index.php?mod=<?php echo $member['username']; ?>" title="<?php echo $member['username']; ?>"><img src="<?php echo $member['face']; ?>" alt="<?php echo $member['nickname']; ?>" onerror="javascript:faceError(this);"/></a> </div> <div class="avatar2_info"> <a href="index.php?mod=<?php echo $member['username']; ?>" title="@<?php echo $member['nickname']; ?>"><b><?php echo $member['nickname']; ?></b></a><?php echo $member['validate_html']; ?> <script type="text/javascript">
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
<?php } ?> <?php } else { ?>(我于：<?php echo $val['dateline']; ?> 获得) <br /> <?php } ?> <a target="_blank" href="index.php?mod=settings&code=user_medal">查看我的勋章</a> </div> </div> </div> </div> </li> <?php } } ?> <?php } ?> </ul> </div>