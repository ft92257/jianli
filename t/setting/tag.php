<?php
/*************************************************************************************************
* 文件名： tag.php
* 版本号：
* 最后修改时间：2007-07-28 15:39:35 By Wilson Cong
* 作者：狐狸<foxis@qq.com>
* 功能描述：标签的配置文件
**************************************************************************************************/
/*------------------------- 基本配置开始 --------------------------------*/

/*　tag表名称　*/
$config['tag']['table_name'] = TABLE_PREFIX.'tag';

/* user表及主键设置 */
$config['tag']['user_table_name'] = TABLE_PREFIX.'members';
$config['tag']['user_table_pri'] = 'uid';

/* my_tag表名称 */
$config['tag']['my_tag_table_name'] = TABLE_PREFIX.'my_tag';

/*------------------------- 基本配置结束 --------------------------------*/

/*　页面默认的标题　*/
$config['tag']['page_title_default'] = "话题";
$config['tag']['per_page_num'] = 200;
$config['tag']['total_record'] = 1000;
$config['tag']['cache_time'] = 1800;

$config['tag']['list_similar_tag_count'] = 10;

$config['tag']['user_list_per_page_num'] = 100;
$config['tag']['item_list_per_page_num'] = 20;
$config['tag']['item_default'] = 'topic';
$config['tag']['item_list'] = array(
	
	'topic' => array(
		'table_name' => TABLE_PREFIX . 'topic',
		'table_pri' => 'tid',
		'name' => '微博',
		'value' => 'topic',
		'url' => 'index.php?mod=topic',
	),
);
?>