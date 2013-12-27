<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename memcp.inc.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-06-20 16:52:55 573103129 1285087357 2066 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
$pluginop = $_GET['pluginop'];
$cid = trim($_POST['cid']);
if(MEMBER_ID > 0)
{
	$sql = "select * from ".TABLE_PREFIX."plugin_company where `uid`='".MEMBER_ID."'";
	$query = $this->DatabaseHandler->Query($sql);
	$cominfo = $query->GetRow();
	if($cominfo){$hasinfo = true;}else{$hasinfo = false;}
	$cominfo['descripction'] = nl2br($cominfo['descripction']);
	$cominfo['ptime'] = my_date_format($cominfo['ptime']);
	if($pluginop == 'add')
	{
		$data = array();
		$data['companyname'] = trim($_POST['companyname']);
		$data['ceoname'] = trim($_POST['ceoname']);
		$data['companyid'] = trim($_POST['companyid']);
		$data['userid'] = trim($_POST['userid']);
		$data['tel'] = trim($_POST['tel']);
		$data['address'] = trim($_POST['address']);
		$data['descripction'] = trim($_POST['descripction']);
		if(empty($data['companyname']) || empty($data['ceoname']) || empty($data['companyid']) || empty($data['userid']) || empty($data['tel']) || empty($data['address']) || empty($data['descripction']))
		{
			$this->Messager("<font color=red>请把资料填写完整！</font>", 'index.php?mod=plugin&plugin=company:memcp&require=topic',1);
		}
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin_company');
		if(!empty($cid) && ($cid == $cominfo['cid']))
		{
			$result = $this->DatabaseHandler->Update($data,"`cid`='$cid'");
			$this->Messager("企业资料修改成功", 'index.php?mod=plugin&plugin=company:memcp&require=topic',1);
		}
		else
		{
			$data['uid'] = MEMBER_ID;
			$sql = "SELECT nickname, ucuid FROM `" . TABLE_PREFIX . "members` WHERE uid = '$data[uid]'";
			$query = $this->DatabaseHandler->Query($sql);
			$user = $query->GetRow();
			$data['ucuid'] = $user['ucuid'];
			$data['username'] = $user['nickname'];
			$data['ptime'] = time();
			$result = $this->DatabaseHandler->Insert($data);
			$this->Messager("企业资料添加成功", 'index.php?mod=plugin&plugin=company:memcp&require=topic',1);
		}
	}
	if($pluginop == 'edit')
	{
		$edit = true;
	}
}
?>