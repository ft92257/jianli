<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename admincp.inc.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 348320064 121190354 2937 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
$id = jget('id','int','G');
if($_GET['pluginop'] == 'add')
{
	$data = array();
	$data['username'] = trim($_POST['username']);
	$data['companyname'] = trim($_POST['companyname']);
	$data['ceoname'] = trim($_POST['ceoname']);
	$data['tel'] = trim($_POST['tel']);
	if($data['username'])
	{
	$sql = "SELECT uid, ucuid FROM `" . TABLE_PREFIX . "members` WHERE username = '$data[username]'";
	$query = $this->DatabaseHandler->Query($sql);
	$user = $query->GetRow();
	$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin_company');
	if(empty($user))
	{
		$this->Messager("微博系统中找不到该用户，请检查后重新输入", -1);
	}
	else
	{
		$data['uid'] = $user['uid'];
		$data['ucuid'] = $user['ucuid'];
		$data['ptime'] = time();
		$data['ison'] = 1;
		$result = $this->DatabaseHandler->Insert($data);
	}
	}
	if($ids = jimplode($this->Post['delete']))
	{
		$sql = "DELETE FROM `" . TABLE_PREFIX . "plugin_company` WHERE `cid` IN ($ids)";
		$result2 = $this->DatabaseHandler->Query($sql);
	}
	if($result || $result2)
	{
		$this->Messager("操作成功", 'admin.php?mod=plugin&code=manage&id='.$id.'&identifier=company&pmod=admincp');
	}else{
		$this->Messager("操作失败", 'admin.php?mod=plugin&code=manage&id='.$id.'&identifier=company&pmod=admincp');
	}

}
elseif($_GET['pluginop'] == 'view' || $_GET['pluginop'] == 'mod')
{
	if($_GET['pluginop'] == 'view')
	{
		$view = true;
	}else{
		$mod = true;
	}
	$cid = jget('cid','int','G');
	$sqlc = "SELECT * FROM `" . TABLE_PREFIX . "plugin_company` WHERE cid = '$cid'";
	$queryc = $this->DatabaseHandler->Query($sqlc);
	$cominfo = $queryc->GetRow();
	$cominfo['ptime'] = my_date_format($cominfo['ptime']);
	if($_GET['pluginop'] == 'view')
	{
		$cominfo['descripction'] = str_replace("\n","<br>",$cominfo['descripction']);
	}
}
elseif($_GET['pluginop'] == 'modsave')
{	
	$data = array();
	$id = jget('id','int','G');
	$cid = trim($_POST['cid']);
	$data['companyname'] = trim($_POST['companyname']);
	$data['ceoname'] = trim($_POST['ceoname']);
	$data['companyid'] = trim($_POST['companyid']);
	$data['userid'] = trim($_POST['userid']);
	$data['tel'] = trim($_POST['tel']);
	$data['address'] = trim($_POST['address']);
	$data['ison'] = trim($_POST['ison']);
	$data['descripction'] = trim($_POST['descripction']);
	$this->DatabaseHandler->SetTable(TABLE_PREFIX.'plugin_company');
	$result = $this->DatabaseHandler->Update($data,"`cid`='$cid'");
	$this->Messager("企业资料修改成功", 'admin.php?mod=plugin&code=manage&id='.$id.'&identifier=company&pmod=admincp');
}
else
{
	$sql = "SELECT * FROM `" . TABLE_PREFIX . "plugin_company` ORDER BY cid DESC";
	$query = $this->DatabaseHandler->Query($sql);
	$i = 0;
	while(false != ($row = $query->GetRow()))
	{
		$companys[$i] = $row;
		$companys[$i]['ptime'] = my_date_format($row['ptime']);
		$i++;
	}
}
?>