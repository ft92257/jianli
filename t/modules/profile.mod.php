<?php
/**
 * 文件名：profile.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年10月27日 10时05分58秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 个人设置模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;

	var $ID = '';

	var $TopicLogic;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);

		$this->Member = $this->_member();

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'do_notice':
				$this->DoNotice();
				break;
			case 'user_share':
				$this->DoUserShare();
				break;
			case 'invite_by_email':
				$this->InviteByEmail();
				break;
			case 'qqrobot':
				$this->Messager(null,"index.php?mod=tools&code=imjiqiren");
				break;

			default:
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$this->Messager($this->MemberHandler->GetError(),null);
		}

		$act_list = array('search'=>'同城用户','maybe_friend'=>'同兴趣','usertag'=>'同类人','invite'=>'邀请好友',);

		$act = isset($act_list[$this->Code]) ? $this->Code : 'search';


		$member = $this->Member;

				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$member_nickname = $member['nickname'];

		Load::lib('form');
		$FormHandler = new FormHandler();


		
		if ('invite' == $act) {
			$sql = "delete from `".TABLE_PREFIX."invite` where `fuid`<'1' and `dateline`>'0' and `dateline`<'".(time() - 86400 * 7)."'";
			$this->DatabaseHandler->Query($sql);
				
			$sql = "select count(*) as my_invite_count from `".TABLE_PREFIX."invite` where `uid`='{$member['uid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();
			$my_invite_count = $row['my_invite_count'];
				
			$can_invite_count = max(0,$this->Config['invite_count_max']-$my_invite_count);
				
			if ($my_invite_count > 0) {

				$per_page_num = 5;

				$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

				$_config = array(
					'return' => 'array',
				);

				$page_arr = page($my_invite_count,$per_page_num,$query_link,$_config);

								$sql = "select i.*,m.province,m.city,m.topic_count,fans_count from `".TABLE_PREFIX."invite` i 
						left join `".TABLE_PREFIX."members` m on m.uid = i.fuid 
					 	where i.`uid`='{$member['uid']}' order by i.`id` desc {$page_arr['limit']}";
				$query = $this->DatabaseHandler->Query($sql);
				$invite_list = array();
				while (false != ($row = $query->GetRow())) {
					$row['from_area'] = $row['province'] ? ($row['province'].' '.$row['city']) : '无';
					$row['face'] = face_get($row['fuid']);
					
					$invite_list[] = $row;						}

								$invite_list = Load::model('buddy')->follow_html($invite_list, 'fuid');
			}

						$MEMBER_INVITE_CODE = '';
			if((!$this->Config['invite_count_max'] || $this->Config['invite_count_max'] > $member['invite_count'])) {
				$MEMBER_INVITE_CODE = $member['invitecode'];
			}
			if (!$MEMBER_INVITE_CODE) {
				$MEMBER_INVITE_CODE = random(16);
				$sql = "update `".TABLE_PREFIX."members` set `invitecode`='{$MEMBER_INVITE_CODE}' where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);
			}				
			$inviteURL = "index.php?mod=member&code=".urlencode(MEMBER_ID."|".$MEMBER_INVITE_CODE);
				
			$inviteURL = get_invite_url($inviteURL,$this->Config['site_url']);
				
						$invite = ConfigHandler::get('invite');
			$invite_msg = empty($invite) ? '' : jstripslashes($invite['invite_msg']);
			if (!empty($invite_msg)) {
				$replaces = array(
    				'nickname' => $member['nickname'],
    				'inviteurl' => $inviteURL,
    				'invite_num' => $this->Config['invite_limit'],
    				'site_name' => $this->Config['site_name'],
				);
				foreach ($replaces as $key => $val) {
					$invite_msg = str_replace("#".$key."#", $val, $invite_msg);
				}
			}

		}
			
		
		elseif ('maybe_friend' == $act) {
				
			$sql = "select * from `".TABLE_PREFIX."tag_favorite` where `uid`='".MEMBER_ID."' order by `id` desc limit 20";
			$query = $this->DatabaseHandler->Query($sql);
			$my_favorite_tags = array();
			while (false != ($row = $query->GetRow())) {
				$my_favorite_tags[$row['tag']] = $row['tag'];
			}
				
			if($my_favorite_tags) {
				$sql = "select distinct(`uid`) as `uid` from `".TABLE_PREFIX."tag_favorite` where `tag` in ('".implode("','",$my_favorite_tags)."') order by `id` desc limit 30";
				$query = $this->DatabaseHandler->Query($sql);
				$uids = array();
				while (false != ($row = $query->GetRow())) {
					$uids[$row['uid']] = $row['uid'];
				}

				if($uids) {
					$p = array(
						'fields' => 'buddyid',
						'uid' => MEMBER_ID,
						'buddyid' => $uids,
					);
					$buddyids = Load::model('buddy')->get_ids($p);						
						
					$sql = "select `uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`nickname` from `".TABLE_PREFIX."members` where `uid` in('".implode("','",$uids)."')";
					$query = $this->DatabaseHandler->Query($sql);
					$member_list = array();
					while (false != ($row = $query->GetRow())) {
						$buddy_status = isset($buddyids[$row['uid']]);
						if(!$buddy_status && MEMBER_ID!=$row['uid']) {
							$row['follow_html'] = follow_html($row['uid'],$buddy_status);
								
							$row = $this->TopicLogic->MakeMember($row);
							$member_list[$row['uid']] = $row;
							$tag_favorite[$row['uid']] = $row['uid'];
						}
					}
				}
			}

						$user_favorite = array();
			if($tag_favorite) {
				$sql = "select * from `".TABLE_PREFIX."tag_favorite` where `uid` in ('".implode("','",$tag_favorite)."')";
				$query = $this->DatabaseHandler->Query($sql);
				while (false != ($row = $query->GetRow())) {
					$user_favorite[] = $row;
				}
			}
		}

		
		elseif ('search' == $act) {
				
			$per_page_num = 10;
			$query_link = 'index.php?mod=profile&code=search';
			$where_list = array();

			$province_name = $member['province'];
			$city_name = $member['city'];
			$area_name = $member['area'];
			$street_name = $member['street'];

			$province = $this->Get['province'];
			$city = $this->Get['city'];
			$area = $this->Get['area'];
			$street = $this->Get['street'];
				
			if($province){
				$province_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$province'");

				if($city){
					$city_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$city'");
						
					if($area){
						$area_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$area'");
							
						if($street){
							$street_name = $this->DatabaseHandler->ResultFirst("select name from ".TABLE_PREFIX."common_district where id = '$street'");
						}else{
							$street_name = '';
						}
					}else{
						$area_name = '';
						$street_name = '';
					}
				}else{
					$city_name = '';
					$area_name = '';
					$street_name = '';
				}
			}

			if(empty($where_list))
			{
				if($province_name){
					$where_list['province'] = "`province`='".addslashes("$province_name")."'";
					$query_link .= "&province=" . $province;
						
					if($city_name){
						$where_list['city'] = "`city`='".addslashes("$city_name")."'";
						$query_link .= "&city=" . $city;

						if($area_name){
							$where_list['area'] = "`area`='".addslashes("$area_name")."'";
							$query_link .= "&area=" . $area;
								
							if($street_name){
								$where_list['street'] = "`street`='".addslashes("$street_name")."'";
								$query_link .= "&street=" . $street;
							}
						}
					}
				}
			}

			Load::lib('form');
			$FormHandler = new FormHandler();
							
			$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province_arr[$rsdb['id']]['value']  = $rsdb['id'];
				$province_arr[$rsdb['id']]['name']  = $rsdb['name'];
				if($member['province'] == $rsdb['name']){
					$province_id = $rsdb['id'];
				}
			}
			$province_id = $province ? $province:$province_id;
			$province_list = $FormHandler->Select("province",$province_arr,$province_id,"onchange=\"changeProvince();\"");
				
			$hid_area = '';
			$hid_city = '';
			$hid_street = '';
				
			if(!$province && $province_id){
				if($member['city']){
					$hid_city = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '$member[city]' and upid = '$province_id'");				}

				if($hid_city){
					if($member['area']){
						$hid_area = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '$member[area]' and upid = '$hid_city'");					}
						
					if($hid_area){
						if($member['street']){
							$hid_street = $this->DatabaseHandler->ResultFirst("select id from ".TABLE_PREFIX."common_district where name = '$member[street]' and upid = '$hid_area'");						}
					}
				}
			}
				
			$hid_city = $city ? $city : $hid_city;
			$hid_area = $area ? $area : $hid_area;
			$hid_street = $street ? $street : $hid_street;

			$member_list = array();
			if($where_list) {

				$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';

				$order = " order by `uid` desc ";
				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where} ";
				$total_record = DB::result_first($sql);

				if($total_record > 0) {
					$_config = array (
						'return' => 'array',
					);
					$page_arr = page($total_record,$per_page_num,$query_link,$_config);
						
					$uids = array();
					$member_list = $this->TopicLogic->GetMember("{$where} {$order} {$page_arr['limit']}","`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`aboutme`");
					foreach ($member_list as $_m) {
						$uids[$_m['uid']] = $_m['uid'];
					}
						
						
					if($uids && MEMBER_ID>0) {
						$member_list = Load::model('buddy')->follow_html($member_list);
						
						$province = isset($_GET['province']) ? $province : $member['province'];
						$city = isset($_GET['city']) ? $city : $member['city'];

						
						$sql ="select * from (select * from `".TABLE_PREFIX."topic` where `uid` in (".jimplode($uids).") and `type` != 'reply' order by `dateline` desc) a group by `uid` ";
						$query = $this->DatabaseHandler->Query($sql);
						$tids = array();
						while (false != ($row = $query->GetRow())) {
							$tids[$row['tid']] = $row['tid'];
						}
						$topic_list = $this->TopicLogic->Get($tids);
					}
				}
			}
			$gender_radio = $FormHandler->Radio('gender',array(0=>array('name'=>'不限','value'=>0),1=>array('name'=>'男','value'=>1,),2=>array('name'=>'女','value'=>2,),),$gender);

		}


		elseif('usertag' == $act) {

			$per_page_num = 10;
			$query_link = 'index.php?mod=profile&code=usertag';
			$order = " order by `fans_count` desc ";

						$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$mytag = array();
			$user_tagid = array();
			while(false != ($row = $query->GetRow()))
			{
				$mytag[] = $row;
				$user_tagid[$row['tag_id']] = $row['tag_id'];
			}

			if($user_tagid)
			{
								$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` != '".MEMBER_ID."' and `tag_id` in (".jimplode($user_tagid).") ";
				$query = $this->DatabaseHandler->Query($sql);
					
				$member_uids = array();
				while(false != ($row = $query->GetRow()))
				{
					$member_uids[$row['uid']] = $row['uid'];
				}

				$where = $where_list = " where `uid` in (".jimplode($member_uids).")";
			}
				

						if($member_uids)
			{
				$member_list = array();

				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where}";
				$total_record = DB::result_first($sql);
				if($total_record > 0) {
					$_config = array (
							'return' => 'array',
					);

					$page_arr = page($total_record,$per_page_num,$query_link,$_config);

					$member_list = $this->TopicLogic->GetMember("{$where} {$order} {$page_arr['limit']}","`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`validate`");
					
					$member_list = Load::model('buddy')->follow_html($member_list);
				}
					
								$sql = "select * from `".TABLE_PREFIX."user_tag_fields` {$where}";
				$query = $this->DatabaseHandler->Query($sql);
				$member_tag = array();

				while(false != ($row = $query->GetRow()))
				{
					$member_tag[] = $row;
				}

			}

						$mytag = $this->_MyUserTag(MEMBER_ID);
		}

		$this->Title = $act_list[$act];
		include($this->TemplateHandler->Template('profile_main'));
	}

	function InviteByEmail()
	{
		$inviteEmail = trim($this->Post['inviteEmail'] ? $this->Post['inviteEmail'] : $this->Get['inviteEmail']);


		if (!$inviteEmail) {
			$this->Messager("请填入Email地址",-1);
		}
		Load::lib('mail');
		$email_list = explode(';',$inviteEmail);
		$send_success = $send_failed = 0;
		foreach($email_list as $email) {
			$email = trim($email);

			if (preg_match("~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i",$email)) {

				
				$_sql = "select * from `".TABLE_PREFIX."invite` where `femail`='{$email}' and `uid`='".MEMBER_ID."'";
				$_query = $this->DatabaseHandler->Query($_sql);
				$_row = $_query->GetRow();

				if ($_row) {
										if ($_row['fuid'] > 0 || $_row['dateline'] + 600 > time()) {
						continue ;
					}
						
					$_row['dateline'] = time();
					$_sql = "update `".TABLE_PREFIX."invite` set `dateline`='{$_row['dateline']}' where `id`='{$_row['id']}'";
					$this->DatabaseHandler->Query($_sql);
				} else {
					$_row['uid'] = MEMBER_ID;
					$_row['code'] = substr(md5(MEMBER_ID . random(16) . time()),0,16);
					$_row['dateline'] = time();
					$_row['femail'] = $email;
					$_sql = "insert into `".TABLE_PREFIX."invite` (`uid`,`code`,`dateline`,`femail`) values ('{$_row['uid']}','{$_row['code']}','{$_row['dateline']}','{$_row['femail']}')";
					$this->DatabaseHandler->Query($_sql);
					$_row['id'] = $this->DatabaseHandler->Insert_ID();
				}
				if($_row['id'] < 1 || !$_row['code'] || !$_row['femail']) {
					continue ;
				}
				

				$invite_id = $_row['id'];
				$invite_hash = md5($_row['id'].$_row['code'].$_row['dateline'].$_row['femail']);
				$invite_email = $_row['femail'];
				$invite_url = "{$this->Config['site_url']}/index.php?mod=member&code=".urlencode("{$invite_id}#{$invite_hash}")."&email=" . urlencode($invite_email);

				$mail_from_username = $this->Member['nickname'];
				$mail_from_email = 'no-reply@jishigou.net';
				$mail_from_email = $this->Config['site_admin_email'];

				$mail_to = $email;
				$mail_subject = "来自好友{$this->Member['nickname']}的{$this->Config[site_name]}微博邀请";
				$mail_content = "{$this->Member['nickname']}邀请你加入{$this->Config[site_name]}微博!
 
快来{$this->Config[site_name]}微博吧，随时随地分享身边的新鲜事儿，请点击以下链接注册：
				{$invite_url}


验证码仅能使用一次，请妥善保管。
如果以上链接无法访问，请复制链接并粘贴到浏览器地址栏访问。
 
 
如果已开通{$this->Config[site_name]}微博，请访问我的微博页面：
				{$this->Config['site_url']}/index.php?mod={$this->Member['username']}
 
 
-------------------------------------------------------------------------
这是一封系统自动发送的邮件，请不要直接回复。

				{$this->Config[site_name]}微博　{$this->Config['site_url']}
" . my_date_format(time(),'Y-m-d');

				$send_result = send_mail($mail_to,$mail_subject,$mail_content,$mail_from_username,$mail_from_email,array(),3,false);

				if ($send_result) {
					$send_success++;
				} else {
					$send_failed++;
				}

			}
				
		}

		$this->Messager("{$send_failed}份邀请发送失败，成功发送{$send_success}份邀请",'',5);
			
	}

		function DoNotice()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}
		$notice_at			= $this->Post['notice_at'];
		$notice_pm			= $this->Post['notice_pm'] ;
		$notice_reply		= $this->Post['notice_reply'];
		$user_notice_time		= $this->Post['user_notice_time'];

		$sql = "update `".TABLE_PREFIX."members` set `notice_at`='{$notice_at}',`notice_pm`='{$notice_pm}',`notice_reply`='{$notice_reply}',`user_notice_time`='{$user_notice_time}' where `uid`='".MEMBER_ID."'";
		$this->DatabaseHandler->Query($sql);

		$this->Messager(null,"index.php?mod=settings&code=notice");

	}

	function _member()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}

		$member = $this->TopicLogic->GetMember(MEMBER_ID);

		return $member;
	}


		function _MyUserTag($uid)
	{
		$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$mytag = array();
		$mytag_ids = array();
		while(false != ($row = $query->GetRow()))
		{
			$mytag[] = $row;
			$mytag_ids[$row['tag_id']] = $row['tag_id'];
		}
			
		return $mytag;
	}
		function _myGroup($where='',$orede='',$limit='')
	{
		$where = "where `uid` = ".MEMBER_ID."";
		$order = 'order by `id` desc';

		$sql="Select `id`,`group_name`,`group_count` From ".TABLE_PREFIX.'group'." {$where} {$order} {$limit}";

		$query = $this->DatabaseHandler->Query($sql);
		$list = $query->GetAll();

		return $list;
	}

}


?>
