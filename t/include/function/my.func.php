<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename my.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1530403171 1143835090 2781 $
 *******************************************************************/


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}




function my_member_validate($uid,$email,$role_id='')
{
	if(1 > ($uid = (int) $uid)) return false;
	if(!($email = trim($email))) return false;
	
	$sys_config = ConfigHandler::get();
	if(!$sys_config['reg_email_verify']) return false;
	
	$DatabaseHandler = &Obj::registry('DatabaseHandler');
	$sql = "select * from `".TABLE_PREFIX."member_validate` where `uid`='{$uid}' order by `regdate` asc";
	$query = $DatabaseHandler->Query($sql);
	$data = array();
	if(($num_rows = $query->GetNumRows()) > 0) {
		while (false != ($row = $query->GetRow())) {
			$data = $row;
		}
		
		$_data = array(
			'email' => $email,
			'status' => 0,
			'verify_time' => 0,
			'regdate' => time(),
		);		
		$DatabaseHandler->SetTable(TABLE_PREFIX . 'member_validate');	
		$DatabaseHandler->Update($_data,"`key`='{$data['key']}' and `uid`='{$data['uid']}'");
		
		if($num_rows > 1) {
			$sql = "delete from `".TABLE_PREFIX."member_validate` where `uid`='{$data['uid']}' and `key`!='{$data['key']}'";
			$DatabaseHandler->Query($sql);
		}
	} else {
		$data['uid'] = $uid;
		$data['email'] = $email;
		$data['role_id'] = (int) ($role_id > 0 ? $role_id : $sys_config['normal_default_role_id']);
		$data['key'] = substr(md5(md5($uid . $email . $role_id) . md5(uniqid(mt_rand(),true))),3,16);
		$data['status'] = $data['verify_time'] = '0';
		$data['regdate'] = time();
		$data['type'] = 'email';
		
		$DatabaseHandler->SetTable(TABLE_PREFIX . 'member_validate');	
		$DatabaseHandler->Insert($data);
	}

	
	$email_message="您好：
您收到这封邮件，是因为在{$sys_config['site_url']}网站的用户注册中使用了该 Email 地址。
如果您有进行上述操作，请忽略这封邮件。您不需要退订或进行其他进一步的操作。
------------------------------------------------------
帐号激活说明：
为避免垃圾邮件或您的Email地址被滥用，我们需要对您的地址有效性进行验证以。
您只需点击下面的链接即可激活您的帐号，并享有真正会员权限：
{$sys_config['site_url']}/index.php?mod=member&code=verify&uid={$data['uid']}&key={$data['key']}

(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)
感谢您的访问，祝您使用愉快！

此致，
{$sys_config['site_name']} 管理团队.
{$sys_config['site_url']}
";
	Load::lib('mail');
	$send_result = send_mail(
		$email,
		" [{$sys_config['site_name']}]Email 地址验证",
		$email_message,
		$sys_config['site_name'],
		$sys_config['site_admin_email'],
		array(),
		3,
		$html=false);
		
	return $send_result;
}

?>