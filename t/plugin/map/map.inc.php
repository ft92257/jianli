<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename map.inc.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 629501341 1263824827 774 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

$sql = "select t.*,m.province,m.city,m.area from ".TABLE_PREFIX."topic AS t, ".TABLE_PREFIX."members AS m WHERE t.uid = m.uid AND t.uid IN(SELECT uid FROM ".TABLE_PREFIX."members WHERE province <>'' OR city <>'') ORDER BY t.tid DESC LIMIT 20";
$query = $this->DatabaseHandler->Query($sql);
$list = array();
$i = 0;
while (false != ($row = $query->GetRow())) 
{
	$list[$i] = $this->TopicLogic->Make($row);
	$list[$i]['address'] = ($row['province'].$row['city'] == '其他其他') ? '浙江省杭州市西湖区' : $row['province'].$row['city'].$row['area'];
	$list[$i] = str_replace("'","\'",str_replace("\\","",str_replace("\n","",str_replace("\r","",$list[$i]))));
	$i = $i + 1;
}
$topics = $list;
?>