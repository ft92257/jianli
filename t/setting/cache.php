<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename cache.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 156450539 718457295 941 $
 *******************************************************************/

 
  
$config['cache']=array (
  'topic_index' => 
  array (
    'new_user' => '1800',
    'guanzhu' => '3600',
    'recommend_topic' => '600',
    'hot_tag' => '7200',
  ),
  'topic_new' => 
  array (
    'topic' => '300',
    'day_tag' => '2592000',
    'tag' => '600',
  ),
  'topic_hot' => 
  array (
    'day1' => '3600',
    'day7' => '43200',
    'day14' => '86400',
    'day30' => '172800',
  ),
  'reply_hot' => 
  array (
    'day1' => '3600',
    'day7' => '43200',
    'day14' => '86400',
    'day30' => '172800',
    'reply' => '14400',
  ),
  'topic_top' => 
  array (
    'guanzhu' => '7200',
    'renqi' => '43200',
    'huoyue' => '43200',
    'yingxiang' => '43200',
    'credits' => '7200',
  ),
  'tag_index' => 
  array (
    'guanzhu' => '7200',
    'hot' => '7200',
    'day7' => '7200',
    'day7_guanzhu' => '7200',
    'tag_tuijian' => '7200',
  ),
  'qun' => 
  array (
    'activity' => '7200',
  ),
);
 ?>