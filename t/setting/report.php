<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename report.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 204101774 908588423 357 $
 *******************************************************************/


$config['report']=array (
	'type_list' => array(
		3 => '用户',
		1 => '微博',
		2 => '评论',
	),
	
	'reason_list' => array(
		1 => '内容涉及色情或暴力',
		2 => '政治反动举报',
		3 => '内容可能侵权',
		4 => '内容涉及其他违规事项',
	),
	
	'process_result_list' => array(
		1 => '已处理',
		0 => '未处理',
		3 => '待处理',
	),
);
?>