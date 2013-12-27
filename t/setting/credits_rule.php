<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename credits_rule.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 819497212 1715774984 2865 $
 *******************************************************************/

 
  
$config['credits_rule']=array (
  'topic' => 
  array (
    'rid' => '1',
    'rulename' => '发布原创微博',
    'action' => 'topic',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '2',
  ),
  'reply' => 
  array (
    'rid' => '2',
    'rulename' => '评论或转发微博',
    'action' => 'reply',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '1',
  ),
  'buddy' => 
  array (
    'rid' => '3',
    'rulename' => '关注好友',
    'action' => 'buddy',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '1',
  ),
  'register' => 
  array (
    'rid' => '4',
    'rulename' => '邀请注册',
    'action' => 'register',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '10',
  ),
  'login' => 
  array (
    'rid' => '6',
    'rulename' => '每天登录',
    'action' => 'login',
    'cycletype' => '1',
    'rewardnum' => '1',
    'extcredits2' => '2',
  ),
  'pm' => 
  array (
    'rid' => '7',
    'rulename' => '发送短消息',
    'action' => 'pm',
    'cycletype' => '1',
    'rewardnum' => '1',
    'extcredits2' => '1',
  ),
  'face' => 
  array (
    'rid' => '8',
    'rulename' => '设置头像',
    'action' => 'face',
    'rewardnum' => '1',
    'extcredits2' => '10',
  ),
  'vip' => 
  array (
    'rid' => '9',
    'rulename' => 'VIP认证',
    'action' => 'vip',
    'rewardnum' => '1',
    'extcredits2' => '20',
  ),
  '_T84202031' => 
  array (
    'rid' => '10',
    'rulename' => '发布指定话题',
    'action' => '_T84202031',
    'cycletype' => '1',
    'rewardnum' => '2',
    'extcredits2' => '5',
    'related' => '新人报到',
  ),
  '_U-2012344970' => 
  array (
    'rid' => '11',
    'rulename' => '关注指定用户',
    'action' => '_U-2012344970',
    'rewardnum' => '1',
    'extcredits2' => '5',
    'related' => 'admin',
  ),
  'topic_del' => 
  array (
    'rid' => '12',
    'rulename' => '删除微博',
    'action' => 'topic_del',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
  'buddy_del' => 
  array (
    'rid' => '13',
    'rulename' => '取消关注好友',
    'action' => 'buddy_del',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
  '_T' => 
  array (
    'rid' => '15',
    'rulename' => '发布指定话题',
    'action' => '_T',
    'cycletype' => '1',
    'rewardnum' => '1',
    'extcredits2' => '10',
  ),
  '_U' => 
  array (
    'rid' => '16',
    'rulename' => '关注指定用户',
    'action' => '_U',
    'cycletype' => '1',
    'rewardnum' => '1',
    'extcredits2' => '10',
  ),
  'vote_add' => 
  array (
    'rid' => '17',
    'rulename' => '发起投票',
    'action' => 'vote_add',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '2',
  ),
  'vote_del' => 
  array (
    'rid' => '18',
    'rulename' => '删除投票',
    'action' => 'vote_del',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
);
?>