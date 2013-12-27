<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename company.inc.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 776268624 346732252 813 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
$comid = jget('uid','int','P');
if($comid)
{
	$sql = "select * from ".TABLE_PREFIX."plugin_company where `uid`='".$comid."' OR username = '".$comid."'";
	$query = $this->DatabaseHandler->Query($sql);
	$cominfo = $query->GetRow();
	$cominfo['ptime'] = my_date_format($cominfo['ptime']);
}
else
{
	$sql = "select c.*,u.face from ".TABLE_PREFIX."plugin_company c LEFT JOIN ".TABLE_PREFIX."members u ON c.uid = u.uid ORDER BY c.cid DESC";
	$query = $this->DatabaseHandler->Query($sql);
	$i = 0;
	while(false != ($row = $query->GetRow()))
	{
		$companys[$i] = $row;
		$companys[$i]['ptime'] = my_date_format($row['ptime']);
		$companys[$i]['face'] = empty($row['face']) ? 'images/noavatar.gif' : $row['face'];
		$i++;
	}
}
?>