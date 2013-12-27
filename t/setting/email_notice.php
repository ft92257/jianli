<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename email_notice.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 1112815106 1899806312 502 $
 *******************************************************************/

 
  
$config['email_notice']=array (
  'is_email' => '0',
  'at' => 
  array (
    'content' => '你的好友对你发了一条@信息。复制连接查看 http://您记事狗微博的地址/index.php?mod=topic&code=myat',
  ),
  'pm' => 
  array (
    'content' => '你的好友发了一条站内短信给你。复制连接查看 http://您记事狗微博的地址/index.php?mod=pm&code=list',
  ),
  'reply' => 
  array (
    'content' => '你的好友评论了你的微博。复制连接查看 http://您记事狗微博的地址/index.php?mod=topic&code=mycomment',
  ),
);
?>