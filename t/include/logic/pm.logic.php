<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename pm.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1408193477 592412252 23621 $
 *******************************************************************/



if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class PmLogic
{
	var $Config;
	var $DatabaseHandler;

		
	function PmLogic()
	{
		$this->DatabaseHandler = &Obj::registry("DatabaseHandler");
		$this->Config = &Obj::registry("config");
	}

	
	function doPmSend($post){
		if(trim($post['message']) == ''){
			return '请编辑要群发的私信内容';
		}
		$time = time();
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'pms');
		$data=array(
			"msgfrom"		=>MEMBER_NAME,
			"msgnickname"		=>MEMBER_NICKNAME,
			"msgfromid" => MEMBER_ID,  								"msgto" => '',					"tonickname" => '',				"msgtoid"   => 0,								"message"   => trim($post['message']),
			"new"=>'1',
			"dateline"=>$time,
			"plid"=>0,
		);
		$lastmessage = addslashes(serialize($data));
		$this->DatabaseHandler->Insert($data);

		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."pms_list where plid = 0");
		if($count){
			$this->DatabaseHandler->QUERY("update ".TABLE_PREFIX."pms_list set uid = ".MEMBER_ID.",pmnum = pmnum + 1,dateline = '$time' ,lastmessage = '$lastmessage' where plid = 0");
		}else{
			$this->DatabaseHandler->QUERY("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values(0,'".MEMBER_ID."',1,'$time','$lastmessage')");
		}
		return '';
	}

	
	function delNotice($pmid){
				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$time = time();
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where plid = 0 and folder = 'inbox' order by dateline desc limit 1");
		$pm_list = $query->GetRow();

		if($pm_list){
			$uid = $pmlist['msgfromid'];
			$lastmessage = addslashes(serialize($pm_list));
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set uid = '$uid',pmnum = pmnum - 1 ,dateline = '$time' , lastmessage = '$lastmessage' where plid = 0");
		}else{
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms_list where plid = 0");
		}

	}

	
	function getPmList($folder='inbox',$page=array(),$uid=0){
		$return_arr = array();
		$page_arr = array();
		$uid = $uid ? $uid : MEMBER_ID;
		$pm_list=array();
		$read = $page['read'];
		$read && $where_sql = " and is_new = 1 ";
		
		if($folder == 'inbox'){
			if($page) {
								if($page['count']) {
					$total_record = (int) $page['count'];
				} else {
					$sql = "select count(*) from ".TABLE_PREFIX."pms_list where `uid` = '{$uid}' and pmnum > 0 $where_sql or plid = 0";
					$total_record = $this->DatabaseHandler->ResultFirst($sql);
				}
				if($page['return_count']) {
					return $total_record;
				}

			  					$page_arr = page($total_record,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			}

			$sql="select *
			      from ".TABLE_PREFIX."pms_list
				  where (uid = '$uid' and pmnum > 0 $where_sql
				  		 or
				  		 plid = 0 )
				  ORDER BY dateline DESC
				  {$page_arr['limit']} ";
			$query = $this->DatabaseHandler->Query($sql);

			while($row=$query->GetRow()){
				$rsdb = unserialize($row['lastmessage']);
				if(is_array($rsdb)){
					foreach ($rsdb as $key=>$value) {
						$row[$key] = stripslashes($value);
					}
				}

								if($row['plid'] == 0){
					$row['uid'] = $row['msgfromid'];
					$row['face'] = face_get($row['msgfromid']);
					$row['username'] = $row['msgfrom'];
					$row['nickname'] = $row['msgnickname'];

								}else if($row['msgfromid'] == $uid){
					$row['uid'] = $row['msgtoid'];
					$row['face'] = face_get($row['msgtoid']);
					$row['username'] = $row['msgto'];
					$row['nickname'] = $row['tonickname'];
								}else{
					$row['uid'] = $row['msgfromid'];
					$row['face'] = face_get($row['msgfromid']);
					$row['username'] = $row['msgfrom'];
					$row['nickname'] = $row['msgnickname'];
				}
				$row['num'] = $row['pmnum'];
				
				$pm_list[$row['plid']."_".$row['uid']]=$row;
			}
		}else{
			if($page){
			  					$sql="SELECT count(*) FROM ".TABLE_PREFIX."pms WHERE msgfromid='$uid' AND folder='outbox'";
				$total_record = $this->DatabaseHandler->ResultFirst($sql);
				$page_arr = page($total_record,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			}

			$sql="SELECT * FROM ".TABLE_PREFIX."pms WHERE msgfromid='$uid' AND folder='outbox' ORDER BY dateline DESC $page_arr[limit]";
			$query = $this->DatabaseHandler->Query($sql);

			while($row=$query->GetRow()){
				$row['uid'] = $row['msgtoid'];
				$row['face'] = face_get($row['msgtoid']);
				$row['username'] = $row['msgto'];
				$row['nickname'] = $row['tonickname'];
				$pm_list[$row['pmid']]=$row;
			}
		}

				$return_arr['pm_list'] = $pm_list;
				$return_arr['page_arr'] = $page_arr;
		
		return $return_arr;
	}

	
	function getHistory($uid = MEMBER_ID,$touid = MEMBER_ID,$page=array(),$limit=''){
		$return_arr = array();
		$page_arr = array();

		if($page){
			if($page['count']) {
				$count = (int) $page['count'];
			} else {
				$sql = "select count(*) from ".TABLE_PREFIX."pms
						where ((msgfromid = '$uid' AND msgtoid = '$touid' AND delstatus != 1)
							     OR
							     (msgfromid = '$touid' AND msgtoid = '$uid' AND delstatus != 2))
							    AND folder = 'inbox' ";
				$count = $this->DatabaseHandler->ResultFirst($sql);
			}
			if($page['return_count']) {
				return $count;
			}

						$page_arr = page($count,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			$limit = $page_arr['limit'];
		}

		$sql = "select p.*,m1.nickname as msgnickname,m2.nickname as tonickname from ".TABLE_PREFIX."pms p
			    left join `".TABLE_PREFIX."members` m1 on m1.uid = p.msgfromid
				left join `".TABLE_PREFIX."members` m2 on m2.uid = p.msgtoid
				where ((p.msgfromid = '$uid' AND p.msgtoid = '$touid' AND p.delstatus != 1)
					     OR
					     (p.msgfromid = '$touid' AND p.msgtoid = '$uid' AND p.delstatus != 2))
					    AND p.folder = 'inbox'
				order by p.dateline desc $limit";

		$query = $this->DatabaseHandler->Query($sql);
		$pm_list = array();
		while (false != ($row = $query->GetRow())){
						if($row['msgfromid'] == $uid){
				$row['uid'] = $row['msgtoid'];
				$row['username'] = $row['msgto'];
				$row['nickname'] = $row['tonickname'];
						}else{
				$row['uid'] = $row['msgfromid'];
				$row['username'] = $row['msgfrom'];
				$row['nickname'] = $row['msgnickname'];
			}
			$row['face'] = face_get($row['msgfromid']);
			$nickname = $row['nickname'];
			$pm_list[$row['pmid']]=$row;
		}

		$return_arr['nickname'] = $nickname;
		$return_arr['pm_list'] = $pm_list;
		$return_arr['page_arr'] = $page_arr;
		
		return $return_arr;
	}

	
	function getNotice($page){
		$return_arr = array();
		$page_arr = array();

		if($page){
			$sql = "select count(*) from ".TABLE_PREFIX."pms
					where plid = 0";
			$count = $this->DatabaseHandler->ResultFirst($sql);

						$page_arr = page($count,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			$limit = $page_arr['limit'];
		}else{
			$limit = $page['limit'];
		}

		$sql = "select * from ".TABLE_PREFIX."pms
				where plid = 0
				order by dateline desc $limit ";
		$query = $this->DatabaseHandler->Query($sql);
		$pm_list = array();

		while (false != ($row = $query->GetRow())){
			$row['uid'] = $row['msgfromid'];
			$row['face'] = face_get($row['msgfromid']);
			$row['username'] = $row['msgfrom'];
			$row['nickname'] = $row['msgnickname'];

			$nickname = $row['nickname'];
			$pm_list[$row['pmid']]=$row;
		}

		$return_arr['nickname'] = $nickname;
		$return_arr['pm_list'] = $pm_list;
		$return_arr['page_arr'] = $page_arr;
		return $return_arr;
	}

	
	function pmSend($post,$suid=MEMBER_ID,$susername=MEMBER_NAME,$snickname=MEMBER_NICKNAME){
		if($suid == MEMBER_ID) {
						$MemberHandler = & Obj::registry('MemberHandler');
			if($MemberHandler && $MemberHandler->HasPermission('pm','send')==false) {
				return 6;
			}
		}
		
		$this->noticeConfig = ConfigHandler::get('email_notice');
		$to_user_list=array();
		$f_rets = filter($post['message']);
		if($f_rets)
		{
			if($f_rets['error'])
			{
				return $f_rets['msg'];
			}
		}
		$post['subject']=htmlspecialchars(trim($post['subject']));
		if($post['message']=='')
		{
			return 1;
		}
		if ($post['buddy_list']==false && $post['to_user']=="")
		{
			return 2;
		}

		if(trim($post['to_user'])!='')
		{

			$in=$this->DatabaseHandler->BuildIn($post['to_user'],"nickname");
						$sql="
			SELECT
				uid,username,nickname,notice_pm,email,newpm
			FROM
				".TABLE_PREFIX.'members'."
			WHERE
				$in";
			$query = $this->DatabaseHandler->Query($sql);

			while($row=$query->GetRow())
			{
								if($suid == MEMBER_ID){
					if(is_blacklist($suid,$row['uid'])){
						return '你在'.$row['nickname'].'的黑名单中，不被允许发私信';
					}
				}
								$rets = jsg_role_check_allow('sendpm', $row['uid'], $suid);
				if($rets && $rets['error']) {
					return $rets['error'];
				} else {
					$to_user_list[$row['uid']]=$row;
				}
			}
		}
		$to_user_list+=(array)$post['buddy_list'];
		if($to_user_list==false)
		{
			return 3;
		}
				
		$time = time();
		$post['message'] = strstr($post['message'],"\\") ? $post['message'] : addslashes($post['message']);
				foreach($to_user_list as $to_user_id => $to_user_name)
		{
			$data=array(
			"msgfrom"	 =>$susername,
			"msgnickname"=>$snickname,
			"msgfromid"  =>$suid,  								"msgto" => $to_user_name['username'],					"tonickname" => $to_user_name['nickname'],				"msgtoid"   => $to_user_id,								"subject"   => $post['subject'],
			"message"   => $post['message'],
			"new"=>'1',
			"dateline"=>$time,
			);

			if($post["save_to_outbox"])
			{
				$data['folder']="outbox";
				$msg="消息已经保存草稿箱";
			}
						$uids = '';
			if($suid > $to_user_id){
				$uids = $to_user_id.",".$suid;
			}else{
				$uids = $suid.",".$to_user_id;
			}

			$plid = 0;
									if(!$msg){
								$lastmessage = addslashes(serialize($data));
				$plid = $this->DatabaseHandler->ResultFirst("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");

				if($plid == 0){
										$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."pms_index (uids) values('$uids')");
					$plid = $this->DatabaseHandler->Insert_ID();
					if(0 != $plid){
												$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values('$plid','".$suid."',1,'$time','$lastmessage')");
						if($suid != $to_user_id){
							$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage,is_new) values('$plid','$to_user_id',1,'$time','$lastmessage',1)");
						}
					}
				}else{
										$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum + 1,dateline = '$time',lastmessage = '$lastmessage',is_new = 1 where plid = '$plid' and uid = '$to_user_id' ");
					if($suid != $to_user_id){
						$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum + 1,dateline = '$time',lastmessage = '$lastmessage',is_new = 0 where plid = '$plid'  and uid = '$suid' ");
					}
				}
			}

			$data['plid'] = $plid;

			DB::insert('pms',$data);
					}

		if($msg){
			return 4;
		}

		$num=$post["save_to_outbox"]?0:1;
		if($num > 0){
						$_tmps=array_keys($to_user_list);
			$to_user_id_list = array();
			foreach($_tmps as $_tmp) {
				$_tmp = (int) $_tmp;
				if($_tmp > 0) {
					$to_user_id_list[$_tmp] = $_tmp;
				}
			}
			$this->UpdateNewMsgCount($num,$to_user_id_list);

			

			foreach ($to_user_list as $user_notice)
			{
				 if($user_notice['notice_pm'] == 1)  				 {
						if($this->Config['notice_email'] == 1) 						{
							Load::lib('mail');
							$mail_to = $user_notice['email'];

							$mail_subject = "{$this->noticeConfig['pm']['title']}";
							$mail_content = "{$this->noticeConfig['pm']['content']}";
							$send_result = send_mail($mail_to,$mail_subject,$mail_content,array(),3,false);

														$sql = "update `".TABLE_PREFIX."members` set `last_notice_time`= ".time()."  where `uid` = '{$user_notice['uid']}'";
							$this->DatabaseHandler->Query($sql);
						}
						else
						{
							
							Load::logic('notice');
							$NoticeLogic = new NoticeLogic();
							$pm_content = '您有'.$user_notice['newpm'].'条站内短信没有查看，请立即查看。';
							$NoticeLogic->Insert_Cron($user_notice['uid'],$user_notice['email'],$pm_content,'pm');

						}
					}


				if($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config))
				{
					imjiqiren_send_message($user_notice,'m',$this->Config);
				}

				if($this->Config['sms_enable'] && sms_init($this->Config))
				{
					sms_send_message($user_notice,'m',$this->Config);
				}
			}

			if($this->Config['extcredits_enable'] && $suid > 0)
			{
				
				update_credits_by_action('pm',$suid,count($to_user_list));
			}
		}
		return 0;
	}

	
	function delUserMsg($uid){
		if($uid < 1){
			return '请选择要删除的聊天记录';
		}
		if($uid > MEMBER_ID){
			$uids = MEMBER_ID.",".$uid;
		}else{
			$uids = $uid.",".MEMBER_ID;
		}

		$plid = $this->DatabaseHandler->ResultFirst("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");
		if($plid < 1){
			return '数据已损坏';
		}

		$pm_list = array();
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where plid = '$plid' and folder = 'inbox'");
		while (false != ($row = $query->GetRow())){
			$pm_list[$row['pmid']] = $row;
		}

		foreach ($pm_list as $key=>$value) {
			if($value['msgfromid'] == $value['msgtoid']){
				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$key'");
			}else if($value['msgfromid'] == MEMBER_ID){
			    if($value['delstatus'] == 2){
				    $this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$key'");
				}else if($value['delstatus'] == 0){
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms set delstatus = 1 where pmid = '$key'");
				}
			}else if($value['msgtoid'] == MEMBER_ID){
				if($value['delstatus'] == 1){
					$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$key'");
				}else if($value['delstatus'] == 0){
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms set delstatus = 2 where pmid = '$key'");
				}
			}
		}

		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set pmnum = 0,dateline = 0,lastmessage = '' where plid='$plid' and uid = ".MEMBER_ID);
		return '';
	}

	
	function delMsg($pmid){

		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$pm_list = $query->GetRow();

		$uid = $pm_list['msgfromid'] == MEMBER_ID ? $pm_list['msgfromid'] : $pm_list['msgtoid'];
		$otheruid = $pm_list['msgfromid'] == MEMBER_ID ? $pm_list['msgtoid'] : $pm_list['msgfromid'];
		$plid = $pm_list['plid'];

		if(empty($pm_list)){
			return '私信内容不存在或已删除';
		}
		if($pm_list['msgfromid'] == $pm_list['msgtoid']){
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		}else if($pm_list['msgfromid'] == MEMBER_ID){
			if($pm_list['delstatus'] == 2){
				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
			}else if($pm_list['delstatus'] == 0){
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms set delstatus = 1 where pmid = '$pmid'");
			}
		}else if($pm_list['msgtoid'] == MEMBER_ID){
			if($pm_list['delstatus'] == 1){
				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
			}else if($pm_list['delstatus'] == 0){
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms set delstatus = 2 where pmid = '$pmid'");
			}
		}

				$this->setNewList($uid,$otheruid,$plid);

		return '';
	}

	
	function setNewList($uid,$otheruid,$plid){
		$sql = "select * from ".TABLE_PREFIX."pms
				where ((msgfromid = '$uid' AND msgtoid = '$otheruid' AND delstatus != 1)
					     OR
					     (msgfromid = '$otheruid' AND msgtoid = '$uid' AND delstatus != 2))
					    AND folder = 'inbox'
					    order by dateline desc
					    limit 1 ";
		$query = $this->DatabaseHandler->Query($sql);
		$pm = $query->GetRow();
		if($pm){
			$lastmessage = addslashes(serialize($pm));
			$time = time();
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum - 1,dateline = '$time',lastmessage = '$lastmessage' where plid='$plid' and uid = '$uid'");
		}else{
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set pmnum = 0,dateline = 0,lastmessage = '' where plid='$plid' and uid = '$uid'");
		}
	}

	
	function pmSendAgain($post){
		$this->noticeConfig = ConfigHandler::get('email_notice');
		$message = trim($post['message']);
		$time = time();

		if($message=='')
		{
			return 1;
		}

		$pmid = $post['pmid'];


		$pm = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$pm_list = $pm->GetRow();
		$pm_list['message'] = $message;

		$touid = $pm_list['msgtoid'];

		$uids = '';
		if($pm_list['msgtoid'] > $pm_list['msgfromid']){
			$uids = $pm_list['msgfromid'].",".$pm_list['msgtoid'];
		}else{
			$uids = $pm_list['msgtoid'].",".$pm_list['msgfromid'];
		}

		if($touid < 1){
			return 5;
		}
		$to_user_list = array();

				$sql="
		SELECT
			uid,username,nickname,notice_pm,email,newpm
		FROM
			".TABLE_PREFIX.'members'."
		WHERE
			uid = '$touid'";
		$query = $this->DatabaseHandler->Query($sql);

		while($row=$query->GetRow())
		{
			$to_user_list[$row['uid']]=$row;
		}

		if($to_user_list==false)
		{
			return 3;
		}

		$plid = $this->DatabaseHandler->ResultFirst("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");

		if($plid == 0){
						$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."pms_index (uids) values('$uids')");
			$plid = mysql_insert_id();
			$pm_list['plid'] = $plid;
			$lastmessage = addslashes(serialize($pm_list));
						$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values('$plid',".MEMBER_ID.",1,'$time','$lastmessage')");
			if($pm_list['msgtoid'] != $pm_list['msgfromid']){
				$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values('$plid','$touid',1,'$time','$lastmessage')");
			}
		}else{
			$lastmessage = addslashes(serialize($pm_list));
						$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum + 1,dateline = '$time',lastmessage = '$lastmessage' where plid = '$plid'");
		}

				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms set folder = 'inbox' ,message = '$message' ,dateline = '$time',plid = '$plid' where pmid = '$pmid'");

				$num = 1;
		$_tmps=array_keys($to_user_list);
		$to_user_id_list = array();
		foreach($_tmps as $_tmp) {
			$_tmp = (int) $_tmp;
			if($_tmp > 0) {
				$to_user_id_list[$_tmp] = $_tmp;
			}
		}
		$this->UpdateNewMsgCount($num,$to_user_id_list);

		

		foreach ($to_user_list as $user_notice)
		{
			 if($user_notice['notice_pm'] == 1)  			 {
					if($this->Config['notice_email'] == 1) 					{
						Load::lib('mail');
						$mail_to = $user_notice['email'];

						$mail_subject = "{$this->noticeConfig['pm']['title']}";
						$mail_content = "{$this->noticeConfig['pm']['content']}";
						$send_result = send_mail($mail_to,$mail_subject,$mail_content,array(),3,false);

												$sql = "update `".TABLE_PREFIX."members` set `last_notice_time`= ".time()."  where `uid` = '{$user_notice['uid']}'";
						$this->DatabaseHandler->Query($sql);
					}
					else
					{
						
						Load::logic('notice');
						$NoticeLogic = new NoticeLogic();
						$pm_content = '您有'.$user_notice['newpm'].'条站内短信没有查看，请立即查看。';
						$NoticeLogic->Insert_Cron($user_notice['uid'],$user_notice['email'],$pm_content,'pm');

					}
				}


			if($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config))
			{
				imjiqiren_send_message($user_notice,'m',$this->Config);
			}

			if($this->Config['sms_enable'] && sms_init($this->Config))
			{
				sms_send_message($user_notice,'m',$this->Config);
			}
		}

		if($this->Config['extcredits_enable'] && MEMBER_ID > 0)
		{
			
			update_credits_by_action('pm',MEMBER_ID,count($to_user_list));
		}
		return 0;
	}

		function UpdateNewMsgCount($num,$uids='')
	{
		if($uids=='')$uids=MEMBER_ID;

		$uids=$this->DatabaseHandler->BuildIn($uids,'uid');
		if(!$uids) return ;

		$strpos = strpos($num,'-');
		if($strpos!==0)
		{
			$num="+".$num;
		}

		$sql="
		UPDATE
			".TABLE_PREFIX.'members'."
		SET
			newpm=newpm $num
		WHERE
			$uids";

		$this->DatabaseHandler->Query($sql);

		$ret = $this->DatabaseHandler->AffectedRows();;

		if(0 === $strpos)
		{
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `newpm`=0 where $uids and `newpm`<0");
		}

		return $ret;
	}
	
	function setRead($uid){
		if($uid < 1){
			return '请选择你要设置的私信';
		}
		if($uid > MEMBER_ID){
			$uids = MEMBER_ID.",".$uid;
		}else{
			$uids = $uid.",".MEMBER_ID;
		}
		$plid = $this->DatabaseHandler->ResultFirst("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");
		if($plid < 1){
			return '数据已损坏';
		}
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."pms_list set is_new = 0 where plid='$plid' and uid = ".MEMBER_ID);
		return '';
	}
}
?>