<?php
/**
 *
 * 处理微博相关的数据逻辑类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic.logic.php 1375 2012-08-15 07:33:41Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class TopicLogic
{

	
	var $DatabaseHandler;


	
	var $Config;

	
	var $_cache;

	
	var $_len = 280;

	
	var $_len2 = 0;

	
	var $ForwardSeprator;


	
	
	function TopicLogic($base = null) {
		if ($base) {
			$this->DatabaseHandler = & $base->DatabaseHandler;
			$this->Config = & $base->Config;
		} else {
			$this->DatabaseHandler = & Obj::registry("DatabaseHandler");
			$this->Config = & Obj::registry("config");
		}

		if($this->Config['topic_cut_length'] > 0) {
			$this->_len = $this->Config['topic_cut_length'] * 2;		}
		if($this->Config['topic_input_length'] > 0) {
			$this->_len2 = $this->Config['topic_input_length'] * 2;
		}

		$this->ForwardSeprator = ' /'.'/@';
	}

		
	function Add($datas, $totid = 0, $imageid = 0, $attachid = 0, $from = 'web', $type = "first", $uid = 0, $item = '', $item_id = 0)
	{
		if(is_array($datas) && count($datas))
		{
						$ks = array(
        		'tid'=>1,
        		'uid'=>1,
			        		'content'=>1,
			        		'imageid'=>1,
				'attachid'=>1,
        		'videoid'=>1,
        		'musicid'=>1,
        		'longtextid'=>1,
									        		'totid'=>1,
        		'touid'=>1,
			        		'dateline'=>1,
			        		'from'=>1,
        		'type'=>1,
        		'item_id'=>1,
        		'item'=>1,

        		'postip'=>1,
        		'timestamp'=>1,
        		'managetype' => 1,
			        		'checkfilter' =>1,
			    'verify' => 1,
							'design' =>1,
        		'xiami_id' => 1,
				#标记有奖转发
				'is_reward' => 1,
			);
			foreach($datas as $k=>$v)
			{
				if(isset($ks[$k]))
				{
					${$k} = $v;
				}
			}
			$verify = $datas['verify'];
		}
		else
		{
			$content = $datas;
		}
		$is_verify = false;
		if($verify){
			$is_verify = false;
		}elseif($this->Config['verify']){
			$is_verify = true;
		}

				$content = $this->_content_strip($content);

				$content_length = strlen($content);

		if ($content_length < 2)
		{
			return "内容不允许为空";
		}

				if($this->_len2 > 0 && $content_length > $this->_len2) {
			$content = cut_str($content, $this->_len2, '');
		}

				if(!$checkfilter){
			$f_rets = filter($content);
			if($f_rets)
			{
				if($f_rets['verify']){
					$is_verify = true;
				}elseif($f_rets['error'])
				{
					return $f_rets['msg'];
				}
			}
		}

				$totid = max(0, (int)$totid);
		$data = array();

		if($managetype){
			$data['managetype'] = $managetype;
		}

		$is_new = 1;
		if($tid){
			$is_new = 0;
			$data['tid'] = $tid;
		}
		$parents = '';

		
		$_froms = array(
        	'web' => 1,
        	'wap' => 1,
        	'mobile' => 1,
			'sms' => 1,
        	'qq' => 1,
        	'msn' => 1,
        	'api' => 1,
        	'sina' => 1,
			'qqwb' => 1,
        	'vote'=>1,
        	'qun'=>1,
			'fenlei'=>1,
			'event'=>1,
        	'android'=>1,
        	'iphone'=>1,
        	'ipad'=>1,
        	'pad'=>1,
        	'androidpad'=>1,
			'reward' => 1,
		);
		$from = (($from && ($_froms[$from])) ? $from : 'web'); 
				if (empty ($item) || $item_id < 0)
		{
						if (!is_numeric($type)) {
				$_types = array('first' => 1, 'forward' => 1, 'reply' => 1, 'both' => 1);
				
				$type = (($totid < 1 && $type && isset($_types[$type])) ? 'first' :  $type);
				if (empty($type)) {
					$type = 'first';
				}
			}
		}

		
		if ($item == 'qun' && $item_id > 0)
		{
						$qun_closed = DB::result_first("SELECT closed FROM ".DB::table('qun')." WHERE qid='{$item_id}'");
			if ($qun_closed) {
				return "当前微群已经关闭，你无法发布内容";
			}
			$r = $this->is_qun_member($item_id, MEMBER_ID);
			if (!$r) {
				return "你没有权限进行当前操作";
			}
		}

		$data['from'] = $from; 		if (($type == 'forward' || $type == 'both')  && $item == 'qun') {
			$data['type'] = $item;
		} else {
			$data['type'] = $type; 		}
		$data['uid'] = $uid = max(0, (int)($uid ? $uid : MEMBER_ID));
		$data['videoid'] = $videoid = max(0, (int)$videoid);
		$data['longtextid'] = $longtextid = max(0 , (int) $longtextid);		$timestamp = $timestamp>0 ? $timestamp : $dateline;
		$data['dateline'] = $data['lastupdate'] = $timestamp = ($timestamp > 0 ? $timestamp : time());
		$data['totid'] = $totid;
		$data['touid'] = $touid;

				$data['item'] = $item;
		$data['item_id'] = $item_id;

				$member = $this->GetMember($data['uid']);
		if(!$member) {
			return "用户不存在";
		}

		if($this->Config['add_topic_need_face'] && !$member['__face__']){
    		return "本站需上传头像才可互动。";
    	}

				$MemberHandler = & Obj::registry('MemberHandler');
		if($MemberHandler) {
			if(!in_array($type, array('both', 'reply', 'forward'))) { 				if(!($MemberHandler->HasPermission('topic','add',0,$member))) {
					if(true!==IN_JISHIGOU_SMS) {
						return ($MemberHandler->GetError());
					}
				}
			} else {
				if(('reply'==$type || 'both'==$type) && !($MemberHandler->HasPermission('topic','reply',0,$member))) {
					return ($MemberHandler->GetError());
				} elseif(('forward'==$type || 'both'==$type) && !($MemberHandler->HasPermission('topic','forward',0,$member))) {
					return ($MemberHandler->GetError());
				}
			}
		}
		

				if(MEMBER_ROLE_TYPE != 'admin'){
			if($this->Config['topic_vip'] == 1){
				if(!$member['validate']){
					return "非V认证用户无法发布信息";
				}
			}elseif($this->Config['topic_vip'] == 2){
				$to_verify = 1;
				if(!$member['validate']){
					$f_rets['vip'] = 1;
					$f_rets['msg'] = '非V认证用户发言内容进入<a href="index.php?mod='.$member['uid'].'&type=my_verify" target="_blank">待审核</a>,
									<a href="'.$this->Config['site_url'].'/index.php?mod=other&code=vip_intro" target="_blank">点击申请认证</a>';
					$is_verify = true;
				}
			}
		}
		$data['username'] = $username = $member['username'];


		$topic_content_id = abs(crc32(md5($content)));

										if(!$verify){
			if($this->Config['lastpost_time']>0 && 'sina'!=$data['from'] && (($timestamp - $member['lastpost']) < $this->Config['lastpost_time'])) {
				return "您发布的太快了，请在在<b>{$this->Config['lastpost_time']}</b>秒后再发布";
			}
		}
				

		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			if (false != $deny = nlogic('feeds.app.jsg')->topic_publish_denied($data))
			{
				return $deny;
			}
		}
		#endif

				if($imageid) {
			if($verify){
				$data['imageid'] = $imageid;
			}else{
				$data['imageid'] = $imageid = Load::logic('image', 1)->get_ids($imageid, $data['uid']);
			}
		}
				if($attachid)
		{
			if($verify){
				$data['attachid'] = $attachid;
			}else{
				$data['attachid'] = $attachid = Load::logic('attach', 1)->get_ids($attachid, $data['uid']);
			}
		}

		$data['musicid'] = $musicid;

				if($xiami_id > 0){
			$this->DatabaseHandler->Query("insert into `" . TABLE_PREFIX .
            							  "topic_music`(`uid`,`username`,`dateline`,`xiami_id`) values ('" .
			$data['uid'] . "','" . $data['username'] . "',"."'{$timestamp}','{$xiami_id}')");

			$musicid = $data['musicid'] = $this->DatabaseHandler->Insert_ID();
		}


				$topic_more = array();
		$parents = '';
		$data['roottid'] = 0;

		if ($totid > 0)
		{
						$content = $this->GetForwardContent($content);


			$_type_names = array('both'=>'转发和评论', 'forward'=>'转发', 'reply'=>'评论');
			$_type_name = $_type_names[$type];

			$to_topic = $row = $this->Get($totid);
			if (!($to_topic)) {
				return "对不起,由于原微博已删除,不能{$_type_name}";
			}
						if(('reply' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_reply', $row['uid'], $data['uid']))) {
				return $rets['error'];
			} elseif (('forward' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_forward', $row['uid'], $data['uid']))) {
				return $rets['error'];
			}
			$topic_more = $this->GetMore($totid);


			$data['totid'] = $row['tid'];
			$data['touid'] = $row['uid'];
			$data['tousername'] = $row['nickname'];
			$parents = ($topic_more['parents'] ? ($topic_more['parents'] . ',' . $totid) : $totid);
			$data['roottid'] = ($topic_more['parents'] ? substr($parents, 0, strpos($parents,
                ',')) : $totid);

			$root_topic = $this->Get($data['roottid']);
						if ($root_topic['item'] == 'qun' && $root_topic['item_id'] > 0) {
								$qun_closed = DB::result_first("SELECT closed FROM ".DB::table('qun')." WHERE qid='{$root_topic['item_id']}'");
				if ($qun_closed) {
					return "当前微群已经关闭，你无法发布内容";
				}
			}

			if($data['totid']!=$data['roottid'])
			{
				$rrow = $this->Get($data['roottid']);
				if(!$rrow)
				{
					return "对不起,由于原始微博已删除,不能{$_type_name}";
				}

								if(('reply' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_reply', $rrow['uid'], $data['uid']))) {
					return $rets['error'];
				} elseif (('forward' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_forward', $rrow['uid'], $data['uid']))) {
					return $rets['error'];
				}

								if(('forward'==$type || 'both'==$type))
				{
					$content .= $this->ForwardSeprator . "{$row['nickname']} : " . jaddslashes($this->_content_strip($row['content']));
				}
			}
		}

		
		$_process_result = $this->_process_content($content, $data);

		$_content = $_process_result['content'];

		$at_uids = $_process_result['at_uids'];

		$tags = $_process_result['tags'];

		$urls = $_process_result['urls'];

				$longtextid = Load::logic('longtext', 1)->add($_content, $data['uid']);
		if(jstrlen($_content) > $this->_len) {
						$_content = cut_str($_content, $this->_len, '');			
			$_content = $this->_content_end($_content);
			if(strlen($_process_result['content']) > strlen($_content)) {
				$data['longtextid'] = $longtextid;
			} else {
				unset($data['longtextid']);
			}
		} else {
			unset($data['longtextid']);
		}

		if (strlen($_content) > 255) {
			$_content = cut_str($_content, 254 * 2, '');

			$data['content'] = cut_str($_content, 255, '');

			$data['content2'] = substr($_content, strlen($data['content']));
		} else {
			$data['content'] = $_content;
		}

		$data['postip'] = $postip ? $postip : client_ip();
		
				if($is_verify){
			$sql = "insert into `" . TABLE_PREFIX . "topic_verify` (`" . implode("`,`", array_keys
			($data)) . "`) values ('" . implode("','", $data) . "')";
			$this->DatabaseHandler->Query($sql);
			$tid = $this->DatabaseHandler->Insert_ID();
			$topic_id = $data['tid'] = $tid;

						if ($imageid)
			{
				DB::query("update ".TABLE_PREFIX."topic_image set `tid`='-1' where `id` in ($imageid)");
			}
						if ($attachid)
			{
				DB::query("update ".TABLE_PREFIX."topic_attach set `tid`='-1' where `id` in ($attachid)");
			}

						if($urls)
			{
				$date = $data;
				$date['id'] = $data['tid'];
				$date['tid'] = -1;
				$this->_process_urls($date,$urls,false,'topic_verify');
			}
			if($notice_to_admin = $this->Config['notice_to_admin']){
				$pm_post = array(
					'message' => $member['nickname']."有一条微博进入待审核状态，<a href='admin.php?mod=topic&code=verify' target='_blank'>点击</a>进入审核。",
					'to_user' => str_replace('|',',',$notice_to_admin),
				);
								$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
				load::logic('pm');
				$PmLogic = new PmLogic();
				$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
			if($f_rets['verify'] || $f_rets['vip']){
				return array($f_rets['msg']);
			}
		}else{
			$sql = "insert into `" . TABLE_PREFIX . "topic` (`" . implode("`,`", array_keys
			($data)) . "`) values ('" . implode("','", $data) . "')";
			$this->DatabaseHandler->Query($sql);
			$tid = $this->DatabaseHandler->Insert_ID();
			if ($tid < 1)
			{
				return "未知的错误";
			}
			$topic_id = $data['tid'] = $tid;

			if($is_new){
								if (!empty($item) && $item_id > 0 && !($design == 'design' || $design == 'btn_wyfx')) {					Load::functions('app');
					$param = array(
						'item' => $item,
						'item_id' => $item_id,
						'tid' => $tid,
						'uid' => $data['uid'],
					);
					if($item == 'talk'){
						$param['touid'] = $touid;
						$param['totid'] = $totid;
					}
					app_add_relation($param);
					unset($param);
				}

								$this->DatabaseHandler->Query("replace into `" . TABLE_PREFIX . "topic_more`(`tid`,`parents`) values('{$tid}','{$parents}')");

			}
			
			#有奖转发判断
			if($is_reward){
				$allowed_reward = 1;
				$reward_info = Load::logic('Reward',1)->getRewardInfo($is_reward);
				if($reward_info['rules']){
					foreach ($reward_info['rules'] as $key=>$val) {
						if($allowed_reward == 0){
							break;
						}
						switch ($key) {
							case 'at_num':
								if($val > count($at_uids)){
									$allowed_reward = 0;
								}
								break;
							case 'user':
								$my_buddyids = get_buddyids($data['uid']);
								if(!$my_buddyids){
									$allowed_reward = 0;
									break;
								}
								foreach ($val as $re_uid => $re_name) {
									if($re_uid == $data['uid']){continue;}
									if(!in_array($re_uid,$my_buddyids)){
										$allowed_reward = 0;
										break;
									}
								}
								break;
							case 'tag':
								foreach ($val as $re_tag) {
									if(!$tags){
										$allowed_reward = 0;
										break;
									}
									if(!in_array($re_tag,$tags)){
										$allowed_reward = 0;
										break;
									}
								}
								break;
							default:
								break;
						}
					}
				}
				#超时转发也不可进入有奖转发名单
				if(TIMESTAMP > $reward_info['tot']){
					$allowed_reward = 0;
				}
				
				#记录有奖转发
									DB::query(" insert into `".TABLE_PREFIX."reward_user` (`uid`,`tid`,`rid`,`on`,`dateline`) values('$data[uid]','$tid','$is_reward','$allowed_reward','".TIMESTAMP."')");
								
				DB::query(" update `".TABLE_PREFIX."reward` set `f_num` = `f_num`+1,`a_num`=`a_num`+$allowed_reward where `id` = '$is_reward' ");
				
			}

						$this->DatabaseHandler->Query("update `" . TABLE_PREFIX . "members` set ".(($data['type'] != 'reply') ? "`topic_count` = `topic_count` + 1 ," : "")." `lastactivity`='{$data['lastupdate']}',`lastpost`='{$data['lastupdate']}',`last_topic_content_id`='{$topic_content_id}' where `uid`='{$data['uid']}'");

			if('reply' != $data['type']) {
				$p = array(
					'buddyid' => $data['uid'],
				);
				Load::model('buddy')->update_lastuptime($p);
			}


						if ($data['type'] == 'both' || $data['type'] == 'reply' && $parents)
			{
				$sql = "insert into `" . TABLE_PREFIX . "topic_reply`(`tid`,`replyid`) values ";
				$_list = array();
				$_tids = (array) explode(',', $parents);
				foreach ($_tids as $_tid)
				{
					$_tid = max(0, (int) $_tid);

					if($_tid > 0)
					{
						$_list[] = "('{$_tid}','{$tid}')";
					}
				}

				if($_list)
				{
					$sql .= implode(" , ", $_list);
					$this->DatabaseHandler->Query($sql);
				}
			}

						if ($imageid)
			{
				Load::logic('image', 1)->set_tid($imageid, $tid);
			}
						if ($attachid)
			{
				Load::logic('attach', 1)->set_tid($attachid, $tid);
			}

						if($longtextid > 0)
			{
				Load::logic('longtext', 1)->set_tid($longtextid, $tid);
			}

						if($musicid){
				$sql = "update `".TABLE_PREFIX."topic_music` set `tid` = '{$tid}' where `id` = '$musicid' ";
				$this->DatabaseHandler->Query($sql);
			}

						if($urls)
			{
				$this->_process_urls($data,$urls);
			}
			if ($data['videoid'] > 0)
			{
				$sql = "update `" . TABLE_PREFIX . "topic_video` set `tid`='{$tid}' where `id`='{$data['videoid']}'";
				$this->DatabaseHandler->Query($sql);
			}

						if($totid > 0)
			{
								

				$reply_count_update = (($type == 'both' || $type == 'reply') ?
	                "`replys` = `replys` + 1 , " : "");
				$forward_count_update = (($type == 'both' || $type == 'forward') ?
	                "`forwards` = `forwards` + 1 , " : "");

								$update_sql = '';
				if ($type == 'reply') {
					$update_sql = " `replys` = `replys` + 1 ,`lastupdate`='{$data['lastupdate']}' ";
				} else if ($type == 'both') {
					$update_sql = " `replys` = `replys` + 1 ,`forwards` = `forwards` + 1,`lastupdate`='{$data['lastupdate']}' ";
				} else if ($type == 'forward') {
					$update_sql = " `forwards` = `forwards` + 1 ";
				}

								if ($parents && !empty($update_sql))
				{
					$sql = "update `" . TABLE_PREFIX . "topic` set {$update_sql} where `tid` in ($parents)";
					$this->DatabaseHandler->Query($sql);
				}

												if ($data['uid']!=$data['touid'] && ($data['type'] == 'both' || $data['type'] == 'reply'))
				{
					$sql = "update `" . TABLE_PREFIX .
	                    "members` set `comment_new`=`comment_new`+1 where `uid`='{$data['touid']}'";
					$this->DatabaseHandler->Query($sql);
				}

				$sql = "select `uid`,`username` from `" . TABLE_PREFIX . "members` where `uid`='{$data['touid']}'";
				$query = $this->DatabaseHandler->Query($sql);
				$row = $query->GetRow();
				if ($row)
				{
										if ($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config))
					{
						imjiqiren_send_message($row, 'p', $this->Config);
					}

										if ($this->Config['sms_enable'] && sms_init($this->Config))
					{
						sms_send_message($row, 'p', $this->Config);
					}
				}

				
				if($data['touid'] > 0)
				{
					$sql = "select `uid`,`comment_new`,`email`,`notice_reply` from `" . TABLE_PREFIX .
	                    "members` where `uid` = '{$data['touid']}'";
					$query = $this->DatabaseHandler->Query($sql);
					$reply_notice = $query->GetRow();

					if ($reply_notice['notice_reply'] == 1) 
					{

						if ($this->Config['notice_email'] == 1) 						{
							Load::lib('mail');
							$mail_to = $reply_notice['email'];

							$notice_config = ConfigHandler::get('email_notice');

							$mail_subject = "{$notice_config['reply']['title']}";
							$mail_content = "{$notice_config['reply']['content']}";
							$send_result = send_mail($mail_to, $mail_subject, $mail_content, array(), 3, false);
						}
						else
						{
														$pm_content = $reply_notice['comment_new'] . '人评论你的微博';
							Load::logic('notice', 1)->Insert_Cron($reply_notice['uid'], $reply_notice['email'], $pm_content,
	                            'raply');
						}
					}
				}
				
			}

						if ($at_uids)
			{
				$this->_process_at_uids($data,$at_uids);
			}

						if ($item == 'qun' && ($data['type'] == 'qun' || $data['type'] == 'first')) {
				if (!empty($item_id)) {
					$query = DB::query("SELECT uid FROM ".DB::table('qun_user')." WHERE qid='{$item_id}'");
					$uids = array();
					while ($value=DB::fetch($query)) {
						if ($value['uid'] != MEMBER_ID) {
							$uids[$value['uid']] = $value['uid'];
						}
					}

					if (!empty($uids)) {
						DB::query("UPDATE ".DB::table('members')."
	        					   SET qun_new=qun_new+1
	        					   WHERE uid IN(".jimplode($uids).")");
					}
				}
			}

						$update_credits = false;

			if ($tags)
			{
				Load::logic('tag');
				$TagLogic = new TagLogic('topic');
				$return = $TagLogic->Add(array('item_id' => $tid, 'tag' => $tags, ), false);

				if ($this->Config['extcredits_enable'] && $data['uid'] > 0)
				{
					
					if (is_array($tags) && count($tags)){
						
						if($this->is_sign_tag($tags)){
							$sign_credits = update_credits_by_action('_S', $data['uid']);
						}

						if(!$sign_credits['updatecredit']){
							foreach ($tags as $_t)
							{
								if ($_t)
								{
									$update_credits = (update_credits_by_action(('_T' . crc32($_t)), $data['uid']) ||
									$update_credits);
								}
							}
						}
					}
				}

								foreach($tags as $val) {
					$query = DB::query("SELECT uid FROM ".DB::table('tag_favorite')." WHERE tag='{$val}'");
					$tag_uids = array();
					while ($value = DB::fetch($query)) {
						if ($value['uid'] != MEMBER_ID) {
							$tag_uids[$value['uid']] = $value['uid'];
						}
					}
					if (!empty($tag_uids)) {
						DB::query("UPDATE ".DB::table('members')." SET topic_new=topic_new+1 WHERE uid IN(".jimplode($tag_uids).")");
					}
				}
			}

						if ($this->Config['extcredits_enable'])
			{
				if (!$update_credits && !$sign_credits && $data['uid'] > 0)
				{
					if ($totid > 0)
					{
						
						update_credits_by_action('reply', $data['uid']);
					}
					else
					{
						
						update_credits_by_action('topic', $data['uid']);
					}
				}
			}


						if ($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config))
			{
				$to_admin_robot = ConfigHandler::get('imjiqiren', 'admin_qq_robots');
				if ($to_admin_robot)
				{
					imjiqiren_send_message($to_admin_robot, 'to_admin_robot', array('site_url' => $this->
					Config['site_url'], 'username' => $data['username'], 'content' => $data['content'],
	                    'topic_id' => $topic_id));
				}
			}


						if ($this->Config['sms_enable'] && sms_init($this->Config))
			{
				$to_admin_mobile = ConfigHandler::get('sms', 'admin_mobile');
				if ($to_admin_mobile)
				{
					sms_send_message($to_admin_mobile, 'to_admin_mobile', array('site_url' => $this->
					Config['site_url'], 'username' => $data['username'], 'content' => $data['content'],
	                    'topic_id' => $topic_id));
				}
			}

						if($member['companyid']>0 || $member['departmentid']>0){
				$CPLogic = Load::logic('cp',1);
				if($member['companyid']>0){
					$CPLogic->update('company',$member['companyid'],0,1);
				}
				if($member['departmentid']>0){
					$CPLogic->update('department',$member['departmentid'],0,1);
				}
			}

			$this->_syn_to($data);

		}

				if('reply' != $data['type']) {
			cache_db('rm', "{$data['uid']}-topic-%", 1);
		}

		unset($this->_cache);

		#if NEDU
		defined('NEDU_MOYO') && nfevent('jsg.logic.topic.add', null, $data);
		#endif

		return $data;
	}

	
	function is_sign_tag($tag){
		$tag = (array) $tag;
		$tags = $this->DatabaseHandler->ResultFirst("select tag from ".TABLE_PREFIX."sign_tag limit 1");
		$sign_tag_arr = explode("\r\n",$tags);
		foreach ($tag as $value) {
			if(in_array($value,$sign_tag_arr)){
				return true;
			}else{
				continue;
			}
		}
		return false;
	}

	
	function Modify($tid,$content,$imageid=0,$attachid=0,$table="")
	{
		$sql_sets = array();

		$timestamp = time();

		$tid = max(0, (int) $tid);
		if($tid < 1)
		{
			return "微博ID错误";
		}

		$topic_info = $this->get($tid,'*','Make',$table,'tid');
		if(!$topic_info)
		{
			return "微博已经不存在了";
		}

		$content = $this->_content_strip($content);
		$content_length = strlen($content);
		if($content_length < 2)
		{
			return "微博内容不能为空";
		}

		if($this->_len2 > 0 && $content_length > $this->_len2) {
			$content = cut_str($content, $this->_len2, '');
		}

		$f_rets = filter($content);
		if($f_rets)
		{
			if($f_rets['error'])
			{
				return $f_rets['msg'];
			}
		}

				if($topic_info['totid'] > 0 && $topic_info['totid']!=$topic_info['roottid'])
		{
						$content = $this->GetForwardContent($content);

			$row = $this->Get($topic_info['totid'],'*','Make',$table);

			if($row && ('forward'==$topic_info['type'] || 'both'==$topic_info['type']))
			{
				$content .= $this->ForwardSeprator . "{$row['nickname']} : " . jaddslashes($this->_content_strip($row['content']));

				if(strlen($content) > $this->_len)
				{
									}
			}
		}


				if($imageid != $topic_info['imageid']) {
			if($imageid) {
				$imageid = Load::logic('image', 1)->get_ids($imageid, $topic_info['uid']);

				if($imageid) {
					Load::logic('image', 1)->set_tid($imageid, $tid);
				}
			}

						Load::logic('image', 1)->set_topic_imageid($tid);
		}
				if($attachid != $topic_info['attachid'])
		{
			if($attachid)
			{
				$attachid = Load::logic('attach', 1)->get_ids($attachid, $topic_info['uid']);

				if($attachid)
				{
					Load::logic('attach', 1)->set_tid($attachid, $tid);
				}
			}

						Load::logic('attach', 1)->set_topic_attachid($tid);
		}



		
		$_process_result = $this->_process_content($content, $topic_info);

		$_content = $_process_result['content'];

		$at_uids = $_process_result['at_uids'];

		$tags = $_process_result['tags'];

		$urls = $_process_result['urls'];

				$longtextid = Load::logic('longtext', 1)->modify($topic_info['tid'], $_content);
		if(jstrlen($_content) > $this->_len) {
						$_content = cut_str($_content, $this->_len, '');			
			$_content = $this->_content_end($_content);
			if(strlen($_process_result['content']) > strlen($_content)) {
				$sql_sets['longtextid'] = "`longtextid`='$longtextid'";
			} else {
				$sql_sets['longtextid'] = "`longtextid`='0'";
			}
		} else {
			$sql_sets['longtextid'] = "`longtextid`='0'";
		}

		if (strlen($_content) > 255) {
			$_content = cut_str($_content, 254 * 2, '');

			$content1 = cut_str($_content, 255, '');

			$content2 = substr($_content, strlen($content1));

			$sql_sets['content'] = "`content`='{$content1}'";

			$sql_sets['content2'] = "`content2`='$content2'";
		} else {
			$sql_sets['content'] = "`content`='{$_content}'";
			$sql_sets['content2'] = "`content2`=''";
		}

		
		$sql_sets['lastupdate'] = "`lastupdate`='$timestamp'";

		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."topic set ".(implode(" , " , $sql_sets))." where `tid`='$tid'");


		if($at_uids)
		{
			$this->_process_at_uids($at_uids);
		}


		if($tags)
		{
			Load::logic('tag');
			$TagLogic = new TagLogic('topic');

			$tags_old = array();

			if (false !== strpos($topic_info['content'], '#'))
			{
				preg_match_all('~<T>#(.+?)#</T>~', $topic_info['content'], $subpatterns);
				if ($subpatterns && is_array($subpatterns[1]))
				{
					$tags_old = $subpatterns['1'];
				}
			}

			$TagLogic->Modify(array('item_id' => $tid, 'tag' => $tags), $tags_old);
		}


				if($urls)
		{
			$this->_process_urls($topic_info,$urls,true);
		}


		return $topic_info;
	}

	
	function DeleteToBox($ids,$managetype=1){
		if(is_numeric($ids)){
			$where = " where tid = '$ids' ";
		}elseif(is_array($ids)){
			$where = " where tid in ('".implode("'.'",$ids)."') ";
		}elseif(is_string($ids)){
			$where = $ids;
		}
			
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."topic $where ");
		$topic = array();

		while ($rs = $query->GetRow()){
			$topic[$rs['tid']] = $rs;
		}

		foreach ($topic as $value) {
						$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic where tid = '$value[tid]'");

						$value['managetype'] = $managetype;
			$value['content'] = addslashes($value['content']);
			$value['content2'] = addslashes($value['content2']);
			$sql = "insert into `" . TABLE_PREFIX . "topic_verify` (`" . implode("`,`", array_keys
			($value)) . "`) values ('" . implode("','", $value) . "')";
			$this->DatabaseHandler->Query($sql);
						if ($value['imageid'])
			{
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."topic_image set `tid`='-1' where `id` in ($value[imageid])");
			}

			jsg_member_update_count($value['uid'], 'topic_count', '-1');
						if ($value['attachid'])
			{
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."topic_attach set `tid`='-1' where `id` in ($value[attachid])");
			}
			
						$topic_more = $this->GetMore($value['tid']);
			if ($topic_more['parents'])
			{
				$sql = "update `" . TABLE_PREFIX .
                    "topic` set `replys`=if(`replys`>1,`replys`-1,0) where `tid` in({$topic_more['parents']})";
				$this->DatabaseHandler->Query($sql);
			}
						if ($this->Config['extcredits_enable'] && $value['uid'] > 0){
				
				update_credits_by_action('topic_del', $value['uid']);
			}
		}
	}

	
	function Delete($ids)
	{
		$topic = $this->Get($ids,'*','Make',TABLE_PREFIX."topic_verify");

				
		if (!$topic)
		{
			return "微博已经不存在了";
		}
		if (!is_array($topic))
		{
			return (is_string($topic) ? $topic : "未知的错误");
		}
		if (isset($topic['tid']) && $topic['tid'] == $ids)
		{
			$topics[] = $topic;
		}
		else
		{
			$topics = $topic;
		}
		$topics = (array) $topics;

				$tbs = array(
        	'qqwb_bind_topic' => 'tid',
        	'report' => 'tid',
        	'sms_receive_log' => 'tid',
        	'topic_verify' => 'tid',               	'topic_favorite' => 'tid',
        	'topic_image' => 'tid',
			'topic_attach' => 'tid',
        	        	'topic_longtext' => 'tid',
        	'topic_mention' => 'tid',
        	'topic_more' => 'tid',
        	'topic_music' => 'tid',
        	'topic_qun' => 'tid',
        	'topic_reply' => array('tid', 'replyid'),
        	'topic_tag' => 'item_id',
        	'topic_url' => 'tid',
        	'topic_video' => 'tid',
        	'topic_vote' => 'tid',
        	'wall_draft' => 'tid',
        	'wall_playlist' => 'tid',
        	        	'topic_recommend' => 'tid',
			'topic_live' => 'tid',
			'topic_talk' => 'tid',
		);

		foreach ($topics as $topic)
		{

			$tid = $topic['tid'];

						if (!empty($topic['item']) &&  $topic['item_id'] > 0) {
				Load::functions('app');
				app_delete_relation($topic['item'], $topic['item_id'], $topic['tid']);
			}

			if (false !== strpos($topic['content'], '#'))
			{
				preg_match_all('~<T>#(.+?)#</T>~', $topic['content'], $subpatterns);
				if ($subpatterns && is_array($subpatterns[1]))
				{
					Load::logic('tag');
					$TagLogic = new TagLogic('topic');

					$TagLogic->Delete(array('item_id' => $topic['tid'], 'tag' => $subpatterns['1'], ));
				}
			}
						if ($topic['imageid']) {
				Load::logic('image', 1)->delete($topic['imageid']);
			}
						if ($topic['attachid'])
			{
				Load::logic('attach', 1)->delete($topic['attachid']);
			}
			if ($topic['videoid'])
			{
								$sql = "select `id`,`video_img` from `" . TABLE_PREFIX .
                    "topic_video` where `id`='" . $topic['videoid'] . "' ";
				$query = $this->DatabaseHandler->Query($sql);
				$topic_video = $query->GetRow();


				Load::lib('io', 1)->DeleteFile($topic_video['video_img']);
			}

						foreach($tbs as $k=>$vs)
			{
				$vs = (array) $vs;

				foreach($vs as $v)
				{
					$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."{$k}` where `{$v}`='{$tid}'", "SKIP_ERROR");
				}
			}


						$cpstring = DB::fetch_first("SELECT companyid,departmentid FROM ".DB::table('members')." WHERE uid = '".$topic['uid']."'");
			if($cpstring['companyid']>0 || $cpstring['departmentid']>0){
				$CPLogic = Load::logic('cp',1);
				if($cpstring['companyid']>0){
					$CPLogic->update('company',$cpstring['companyid'],0,-1);
				}
				if($cpstring['departmentid']>0){
					$CPLogic->update('department',$cpstring['departmentid'],0,-1);
				}
			}
		}

	}

	
	function Get($ids, $fields = '*', $process = 'Make', $table = "", $prikey = 'tid', $cache=0) {
		$table = ($table ? $table : TABLE_PREFIX . "topic");

		if($cache) {
			$cache_key = md5($fields . $process . $table . $prikey);
		}

		$condition = "";
		if (is_numeric($ids)) {
			if ($cache && isset($this->_cache[$cache_key][$ids])) {
				return $this->_cache[$cache_key][$ids];
			}
			$condition = "where `{$prikey}`='{$ids}'";
		} elseif (is_array($ids)) {
			$condition = "where `{$prikey}` in ('" . implode("','", $ids) . "')";
		} elseif (is_string($ids) && false !== strpos(strtolower($ids), ' limit ')) {
			$condition = $ids;
		} else {
			return false;
		}


		$sql = "select {$fields} from {$table} {$condition} ";

		$query = $this->DatabaseHandler->Query($sql);
		if (!$query || ($num_rows = $query->GetNumRows()) < 1) {
			return false;
		}
		$is_one = ((is_numeric($ids) && $num_rows < 2) ? 1 : 0);

		$list = array();
		while (false != ($row = $query->GetRow())) {
			if ('Make'!=$process || $is_one) {
				$row = (($cache && isset($this->_cache[$cache_key][$row[$prikey]])) ? $this->_cache[$cache_key][$row[$prikey]] :
				$this->$process($row));
			}
			if ($cache && isset($row[$prikey]) && !isset($this->_cache[$cache_key][$row[$prikey]])) {
				$this->_cache[$cache_key][$row[$prikey]] = $row;
			}

			if ($is_one) {
				$list = $row;
				break;
			} else {
				if(isset($row[$prikey])) {
					$list[$row[$prikey]] = $row;
				} else {
					$list[] = $row;
				}
			}
		}
		$query->FreeResult();
		if('Make'==$process && !$is_one) {
			$list = $this->MakeAll($list);
		}

		return $list;
	}

	
	function MakeAll($list, $make_row=1) {
		if(!$list) {
			return array();
		}

		$uids = array();
		$videoids = array();
		$musicids = array();
		foreach($list as $k=>$v) {
			if($make_row) {
				$v = $this->Make($v, array(), 1, 1);
			}

			if($v['uid']>0) {
				$uids[$v['uid']] = $v['uid'];
			}
			if($v['touid']>0) {
				$uids[$v['touid']] = $v['touid'];
			}
			if($v['videoid']>0) {
				$videoids[$v['videoid']] = $v['videoid'];
			}
			if($v['musicid']>0) {
				$musicids[$v['musicid']] = $v['musicid'];
			}

			$list[$k] = $v;
		}

		$member_list = array();
		if($uids) {
			$sql = "SELECT
  M.`uid`,
  M.`ucuid`,
  M.`username`,
  M.`nickname`,
  M.`signature`,
  M.`face_url`,
  M.`face`,
  M.`validate`,
  M.`validate_category`,
  M.`level`,
  MF.validate_true_name,
  MF.validate_remark
FROM ".DB::table('members')." M
  LEFT JOIN ".DB::table('memberfields')." MF
    ON MF.uid = M.uid
WHERE M.uid IN('".implode("','", $uids)."')";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
				$member_list[$row['uid']] = $this->MakeMember($row);
			}
		}

		$video_list = array();
		if($videoids) {
			$sql = "SELECT
  `id`,
  `video_hosts`,
  `video_link`,
  `video_img`,
  `video_img_url`,
  `video_url`
FROM ".DB::table('topic_video')."
WHERE `id` IN('".implode("','", $videoids)."')";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
				$video_list[$row['id']] = $row;
			}
		}

		$music_list = array();
		if($musicids) {
			$sql = "SELECT
  `id`,
  `music_url`,
  `xiami_id`
FROM ".DB::table('topic_music')."
WHERE `id`IN('".implode("','", $musicids)."')";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
				$music_list[$row['id']] = $row;
			}
		}

		if($member_list || $video_list || $music_list) {
			foreach($list as $k=>$v) {
				if($v['uid']>0 && $member_list[$v['uid']]) {
					$v = array_merge($v, $member_list[$v['uid']]);
				}
				if($v['touid']>0 && $member_list[$v['touid']]) {
					if ($v['tousername'] != $member_list[$v['touid']]['nickname']) {
						DB::query("UPDATE ".DB::table('topic')." SET `tousername`='{$member_list[$v['touid']]['nickname']}' WHERE `tid`='{$v['tid']}'");
					}
				}
				if($v['videoid']>0 && $video_list[$v['videoid']]) {
					$v['VideoID'] = $video_list[$v['videoid']]['id'];
					$v['VideoHosts'] = $video_list[$v['videoid']]['video_hosts'];
					$v['VideoLink'] = $video_list[$v['videoid']]['video_link'];
					$v['VideoUrl'] = $video_list[$v['videoid']]['video_url'];

					if ($video_list[$v['videoid']]['video_img']) {
						$v['VideoImg'] = ($video_list[$v['videoid']]['video_img_url'] ? $video_list[$v['videoid']]['video_img_url'] : $this->Config['site_url']) . '/' . $video_list[$v['videoid']]['video_img'];
					} else {
						$v['VideoImg'] = $this->Config['site_url'] . '/images/vd.gif';
					}
				}
				if($v['musicid']>0 && $music_list[$v['musicid']]) {
					$v['MusicID'] = $music_list[$v['musicid']]['id'];
					$v['MusicUrl'] = $music_list[$v['musicid']]['music_url'];
					$v['xiami_id'] = $music_list[$v['musicid']]['xiami_id'];
				}

				$list[$k] = $v;
			}
		}
		
				

		return $list;
	}

	
	function Make($topic, $actors = array(), $cut_content = 1, $merge_sql = 0)
	{
		global $rewriteHandler;

				$make_member_fields = "`uid`,`ucuid`,`username`,`nickname`,`signature`,`face_url`,`face`,`validate`,`validate_category`,`level`";


				$topic['content'] .= $topic['content2'];
		
				if($topic['longtextid'] > 0) {
			$topic['content'] = $this->_content_end($topic['content']);
		}
		
		$topic['raw_content'] = strip_tags($topic['content']);
		unset($topic['content2']);

				if($cut_content && defined(TOPIC_CONTENT_CUT_LENGTH) && TOPIC_CONTENT_CUT_LENGTH > 0)
		{
			$topic['content'] = cutstr($topic['content'], TOPIC_CONTENT_CUT_LENGTH);
			$topic['raw_content'] = cutstr($topic['raw_content'], TOPIC_CONTENT_CUT_LENGTH);
		}

				if ($topic['dateline'])
		{
			$topic['addtime'] = $topic['dateline'];
			$topic['dateline'] = my_date_format2($topic['dateline']);
		}
		
				$highlight = $_GET['highlight'];
		if (is_numeric($highlight))
		{
			
		}
		elseif (is_string($highlight))
		{
			$topic['content'] = str_replace($highlight, "<font color=red>{$highlight}</font>", $topic['content']);
		}
		
		$topic['is_vote'] = 0;
		if(!$topic['random']) {
			$topic['random'] = mt_rand();
		}

				if (false !== strpos($topic['content'], $this->Config['site_url']))
		{
			if (preg_match_all('~(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&_\~\`\:\+\#\.]*(?:[^\;\@\[\]\<\>\'\"\n\r\t\s\x7f-\xff])*)?~i',
			$topic['content'] . " ", $match))
			{
				$cont_rpl = $cont_sch = array();
				foreach ($match[0] as $v)
				{
					$v = trim($v);
					if (($vl = strlen($v)) < 8 || $vl > 200)
					{
						continue;
					}
					if (strtolower($this->Config['site_url']) == strtolower(substr($v, 0, strlen($this->
					Config['site_url']))))
					{
						
						$app_type = '';
						$tmp_vid = 0;
						if (MEMBER_ID > 0) {							if (preg_match("/mod=vote(?:&code=view)?&vid=([0-9]+)/", $v, $m) || preg_match("/vote(?:\/view)?\/vid\-([0-9]+)/", $v, $m)) {
								$app_type = 'vote';
								$tmp_vid = $m[1];
								if ($topic['is_vote'] === 0) {
									$topic['is_vote'] = $tmp_vid;
								}
							}
						}

												if ($app_type == 'vote') {
							$cont_sch[] = "{$v}";
							$vote_key = $topic['tid'].'_'.$topic['random'];
							if (IN_JISHIGOU_WAP === true || IN_JISHIGOU_MOBILE === true) {
								$cont_rpl[] = "<a href='{$v}'>{$v}<img src='{$this->Config['site_url']}/images/voteicon.gif'/></a>";
							} else {
								$cont_rpl[] = "<a onclick='return getVoteDetailWidgets(\"{$vote_key}\", {$tmp_vid});' href='{$v}'>{$v}<img src='{$this->Config['site_url']}/images/voteicon.gif'/></a>";
							}
						} else {
							$cont_sch[] = "{$v}";
							$cont_rpl[] = "<a href='{$v}'>{$v}</a>";
						}

					}
				}

				if ($cont_rpl && $cont_sch)
				{
										$cont_sch = array_unique($cont_sch);
					$cont_rpl = array_unique($cont_rpl);
					$topic['content'] = trim(str_replace($cont_sch, $cont_rpl, $topic['content']));
				}
			}
		}

				$this->_parseAt($topic);


				if (false !== strpos($topic['content'], '<T>#'))
		{
			static $topic_content_tag_href_pattern_static = '';
			if (!$topic_content_tag_href_pattern_static)
			{
				$topic_content_tag_href_pattern_static = "index.php?mod=tag&code=|REPLACE_VALUE|";

				
				if ($topic['item'] == 'qun') {
					$topic_content_tag_href_pattern_static = "index.php?mod=qun&qid={$topic['item_id']}&tag=|REPLACE_VALUE|";
				}

				if ($rewriteHandler)
				{
					$topic_content_tag_href_pattern_static = $rewriteHandler->formatURL($topic_content_tag_href_pattern_static);
				}

								if (defined("IN_JISHIGOU_MOBILE")) {
					$topic_content_tag_href_pattern_static = 'javascript:goToTopicList(\\\'|REPLACE_VALUE| . "\')"';
				}
			}
			$topic['content'] = preg_replace('~<T>#(.+?)#</T>~e', '\'<a href="' . str_replace('|REPLACE_VALUE|', 
				'\' . ' . (defined('IN_JISHIGOU_MOBILE') ? '' : 'urlencode') . '(strip_tags(\'\\1\'))', $topic_content_tag_href_pattern_static) . ' . \'">#\\1#</a>\'', $topic['content']);
		}

		if (false !== strpos($topic['content'], '</U>')) {
			static $topic_content_url_href_pattern_static = '';
			if (!$topic_content_url_href_pattern_static) {
				$topic_content_url_href_pattern_static =
                    "index.php?mod=url&code=|REPLACE_VALUE|";
				if ($rewriteHandler) {
					$topic_content_url_href_pattern_static = ltrim($rewriteHandler->formatURL($topic_content_url_href_pattern_static),
                        '/');
				}
			}
			$sys_site_url = $this->Config['site_url'];
			if ($rewriteHandler) {
				$sys_site_url = ((false !== ($_tmp_pos = strpos($sys_site_url, '/', 10))) ?
				substr($sys_site_url, 0, $_tmp_pos) : $sys_site_url);
			}
			$topic['content'] = preg_replace('~<U ([0-9a-zA-Z]+)>(.+?)</U>~e', '\'<a title="\'.htmlspecialchars(strip_tags(\'\\2\')).\'" href="' . 
				($sys_site_url . '/' . str_replace('|REPLACE_VALUE|', '\\1', $topic_content_url_href_pattern_static)) . '" target="_blank">' . 
				($sys_site_url . '/' . str_replace('|REPLACE_VALUE|', '\\1', $topic_content_url_href_pattern_static)) . '</a>\'', $topic['content']);
		}
		if(false !== strpos($topic['content'], '<T>')) {
			$topic['content'] = str_replace(array('<T>', '</T>', '</U>', '<T', '</T', ), '', $topic['content']);
		}
		if(false !== strpos($topic['content'], '<U')) {
			$topic['content'] = preg_replace('~(</U>|<U[^><]*?>|<U\s*)~', '', $topic['content']);
		}

				if (false !== strpos($topic['content'], '[')) {
			if (false === strpos($topic['content'], '#[')) {
				if (preg_match_all('~\[(.+?)\]~', $topic['content'], $match)) {
					static $face_conf=null;
					if(!$face_conf) {
						$face_conf = ConfigHandler::get('face');
					}
					foreach ($match[0] as $k => $v) {
						if (false != ($img_src = $face_conf[$match[1][$k]])) {
														if (defined("IN_JISHIGOU_MOBILE")) {
								$img_src = 'mobile/'.$img_src;
							}
							$topic['content'] = str_replace($v, '<img src="' . $this->Config['site_url'] .
                                '/' . $img_src . '" border="0"/>', $topic['content']);
						}
					}
				}
			}
		}

				if ($topic['touid'] && !$merge_sql) {
			$touser = $this->GetMember($topic['touid'], $make_member_fields);
			if ($topic['tousername'] != $touser['nickname']) {
				$updatatousername = "update `" . TABLE_PREFIX . "topic` set `tousername`='{$touser['nickname']}' where `tid`=" .
				$topic['tid'];
				$this->DatabaseHandler->Query($updatatousername);
			}
		}
		

				$topic = $this->_make_topic_from($topic);
		

				$topic['top_parent_id'] = $topic['roottid'];
		$topic['parent_id'] = $topic['totid'];
		

		if ($topic['imageid']) {
			$topic['image_list'] = Load::logic('image', 1)->image_list($topic['imageid']);
		}
		if ($topic['attachid']) {
			$topic['attach_list'] = Load::logic('attach', 1)->attach_list($topic['attachid']);
		}

				if ($topic['videoid'] > 0 && $this->Config['video_status'] && !$merge_sql)
		{
			$sql = "select `id`,`video_hosts`,`video_link`,`video_img`,`video_img_url`,`video_url` from `" .
			TABLE_PREFIX . "topic_video` where `id`='" . $topic['videoid'] . "' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_video = $query->GetRow();

			$topic['VideoID'] = $topic_video['id'];
			$topic['VideoHosts'] = $topic_video['video_hosts'];
			$topic['VideoLink'] = $topic_video['video_link'];
			$topic['VideoUrl'] = $topic_video['video_url'];

			if ($topic_video['video_img'])
			{
				$topic['VideoImg'] = ($topic_video['video_img_url'] ? $topic_video['video_img_url'] : $this->Config['site_url']) . '/' . $topic_video['video_img'];
			}
			else
			{
				$topic['VideoImg'] = $this->Config['site_url'] . '/images/vd.gif';
			}
		}

				if ($topic['musicid'] > 0 && !$merge_sql)
		{
			$sql = "select `id`,`music_url`,`xiami_id` from `" . TABLE_PREFIX .
                "topic_music` where `id`='" . $topic['musicid'] . "' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_music = $query->GetRow();

			$topic['MusicID'] = $topic_music['id'];
			$topic['MusicUrl'] = $topic_music['music_url'];
			$topic['xiami_id'] = $topic_music['xiami_id'];
		}


				if(!$merge_sql) {
			$topic = array_merge($topic, (array) $this->GetMember($topic['uid'], $make_member_fields));
		}


				return $topic;
	}

	
	function _make_topic_from($topic) {
		$topic['from_html'] = $topic['from_string'] = '';
			
		if($topic['item'] && $topic['item_id'] > 0) {
			if(!function_exists('item_topic_from')) {
				Load::functions('item');
			}
			$topic = item_topic_from($topic);
		} elseif($topic['from']) {
			static $topic_from_config=null;
			if(null===$topic_from_config) {
				$topic_from_config = ConfigHandler::get('topic_from');
			}
			$topic_from = $topic_from_config[$topic['from']];
			if($topic_from) {
				$topic['from_html'] = $topic['from_string'] = '来自'.$topic_from['name'];
				if($topic_from['link']) {
					$topic['from_html'] = '来自<a href="'.$topic_from['link'].'">'.$topic_from['name'].'</a>';
				}
			}
		}
			
		if(!$topic['from']) {
			$topic['from'] = 'web';
		}
		if(!$topic['from_string']) {
			$topic['from_string'] = '来自'.$this->Config['site_name'];
		}
		if(!$topic['from_html']) {
			$topic['from_html'] = '来自<a href="'.$this->Config['site_url'].'">'.$this->Config['site_name'].'</a>';
		}

		return $topic;
	}

	
	function _parseAt(&$topic)
	{
		global $rewriteHandler, $topic_content_member_href_pattern_static;
		if (false !== strpos($topic['content'], '</M>')) {
						if (defined("IN_JISHIGOU_MOBILE")) {
				$topic_content_member_href_pattern_static = "javascript:;";
									preg_match_all("/<M ([^>]+?)>/", $topic['content'], $matches);
					if ($matches[1]) {
						$sql = "Select `uid`,`username` From " . TABLE_PREFIX . 'members' .
		                    " Where `username` in ('" . implode("','", $matches[1]) . "')";
						$query = $this->DatabaseHandler->Query($sql);
						$_search = $_replace = array();
						while (false != ($row = $query->GetRow())) {
							$_replace[] = "<M {$row['uid']}>";
							$_search[] = "<M {$row['username']}>";
						}

						if ($_search && $_replace) {
							$topic['content'] = str_replace($_search, $_replace, $topic['content']);
							$topic_content_member_href_pattern_static = "javascript:goToUserInfo('|REPLACE_VALUE|')";
						}
					}
								$topic['content'] = preg_replace('~<M ([^>]+?)>\@(.+?)</M>~', '<a href="' .
				str_replace('|REPLACE_VALUE|', '\\1', $topic_content_member_href_pattern_static) .
                '" target="_blank">@\\2</a>', $topic['content']);
			} else {
				if(!$topic_content_member_href_pattern_static)
				{
					$topic_content_member_href_pattern_static = 'index.php?mod=|REPLACE_VALUE|';

					if($rewriteHandler)
					{
						$topic_content_member_href_pattern_static = $rewriteHandler->formatURL($topic_content_member_href_pattern_static);
					}
				}

				$topic['content'] = preg_replace('~<M ([^>]+?)>\@(.+?)</M>~', '<a href="' .
				                				str_replace('|REPLACE_VALUE|', '\\1', $topic_content_member_href_pattern_static) .
                '" target="_blank"'.(true!==IN_JISHIGOU_WAP ? '  onmouseover="get_at_user_choose(\'\\2\',this)"' : '').'>@\\2</a>', $topic['content']);			}
		}
	}

	
	function GetMore($ids)
	{
		return $this->Get($ids, '*', 'MakeMore', TABLE_PREFIX . "topic_more", 'tid');
	}
	
	function MakeMore($row)
	{
		if ($row['replyids']) {
			$row['replyids'] = unserialize($row['replyids']);
		}

		return $row;
	}

	
	function GetReply($tid) {
		$ids = array();
		$tid = (is_numeric($tid) ? $tid : 0);
		if($tid > 0) {
			$sql = "SELECT
	  t.`tid`
	FROM `".TABLE_PREFIX."topic_reply` tr
	  LEFT JOIN `".TABLE_PREFIX."topic` t
	    ON t.tid = tr.`replyid`
	WHERE tr.`tid` = '{$tid}'";
			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow())) {
				if($row['tid'] > 0) {
					$ids[$row['tid']] = $row['tid'];
				}
			}
		}
		return $ids;
	}
	
	function GetReplyIds($tid) {
		$topic_info = $this->Get($tid);
		if (!$topic_info) {
			return false;
		}

		$topic_more = $this->GetMore($tid);
		if (!$topic_more) {
			return false;
		}

		if ($topic_more['replyidscount'] != $topic_info['replys']) {
			$topic_more['replyids'] = $this->GetReply($tid);
			$topic_more['replyidscount'] = count($topic_more['replyids']);

			$sql = "update `" . TABLE_PREFIX . "topic` set `replys`='{$topic_more['replyidscount']}' where `tid`='{$tid}'";
			$this->DatabaseHandler->Query($sql);

			$sql = "update `" . TABLE_PREFIX . "topic_more` set `replyids`='" . serialize($topic_more['replyids']) .
                "' , `replyidscount`='{$topic_more['replyidscount']}' where `tid`='{$tid}'";
			$this->DatabaseHandler->Query($sql);
		}

		return $topic_more['replyids'];
	}

	
	function GetMember($ids, $fields = '*') {
		if(is_numeric($ids) && $ids==MEMBER_ID && $GLOBALS['_J']['member']) {
			return $GLOBALS['_J']['member'];
		} else {
			return $this->Get($ids, $fields, 'MakeMember', TABLE_PREFIX . "members", 'uid', 1);
		}
	}

	
	function MakeMember($row)
	{
		return jsg_member_make($row);
	}

	function _syn_to($data)
	{
		
		$this->_syn_to_sina($data);

		
		$this->_syn_to_qqwb($data);

		$this->_syn_to_kaixin($data);

		$this->_syn_to_renren($data);
	}

	
	
	function _syn_to_sina($data = array())
	{
		if ($this->Config['sina_enable'] && $data && $data['uid'] > 0 && $data['tid'] > 0 && 'sina'!=$data['from'] &&
		sina_weibo_init($this->Config) && sina_weibo_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['sina_update_disable'])
		{
			$sina_config = ConfigHandler::get('sina');
			if (($data['totid'] > 0 && $sina_config['is_syncreply_toweibo'] && sina_weibo_bind_setting($data['uid'])) || ($data['totid'] <
			1 && $sina_config['is_synctopic_toweibo'] && $_POST['syn_to_sina']))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule(array('data'=>$data),'syn_to_sina', $data['uid']);
				}
				else
				{
					include(ROOT_PATH . 'include/xwb/to_xwb.inc.php');
				}
			}
		}
	}

	function _syn_to_qqwb($data = array())
	{
		if ($this->Config['qqwb_enable'] && $data['uid'] > 0 && $data['tid'] > 0 && 'qqwb'!=$data['from'] &&
		qqwb_init($this->Config) && qqwb_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['qqwb_update_disable'])
		{
			$qqwb_config = ConfigHandler::get('qqwb');
			if (($data['totid'] > 0 && $qqwb_config['is_syncreply_toweibo'] && qqwb_synctoqq($data['uid'])) || ($data['totid'] <
			1 && $qqwb_config['is_synctopic_toweibo'] && $_POST['syn_to_qqwb']))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule($data,'syn_to_qqwb');
				}
				else
				{
					@extract($data);

					include(ROOT_PATH . 'include/qqwb/to_qqwb.inc.php');
				}
			}
		}
	}

	function _syn_to_kaixin($data = array())
	{
		if ($this->Config['kaixin_enable'] && $data['uid'] > 0 && $data['tid'] > 0 && 'kaixin'!=$data['from'] &&
		kaixin_init($this->Config) && kaixin_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['kaixin_update_disable'])
		{
			$kaixin_config = ConfigHandler::get('kaixin');
			if (($data['totid'] > 0 && $kaixin_config['is_sync_topic']) || ($data['totid'] <
			1 && $kaixin_config['is_sync_topic'] && $_POST['syn_to_kaixin']))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule($data, 'syn_to_kaixin');
				}
				else
				{
					kaixin_sync($data);
				}
			}
		}
	}

	function _syn_to_renren($data = array())
	{
		if ($this->Config['renren_enable'] && $data['uid'] > 0 && $data['tid'] > 0 && 'renren'!=$data['from'] &&
		renren_init($this->Config) && renren_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['renren_update_disable'])
		{
			$renren_config = ConfigHandler::get('renren');
			if (($data['totid'] > 0 && $renren_config['is_sync_topic']) || ($data['totid'] <
			1 && $renren_config['is_sync_topic'] && $_POST['syn_to_renren']))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule($data, 'syn_to_renren');
				}
				else
				{
					renren_sync($data);
				}
			}
		}
	}

	
	
	function _process_content($content, $topic_info=array())
	{
		$return = array();

		$content .= ' ';

		$cont_sch = $cont_rpl = $at_uids = $tags = $urls = array();

		$tuid = (int) $topic_info['uid'];
		# @user
		if (false !== strpos($content, '@')) {
			if (preg_match_all('~\@([\w\d\_\-\x7f-\xff]+)(?:[\r\n\t\s ]+|[\xa1\xa1]+|[\xa3\xac]|[\xef\xbc\x8c]|[\,\.\;\[\#])~', $content, $match)) {
				if (is_array($match[1]) && count($match[1])) {
					foreach ($match[1] as $k => $v) {
						$v = trim($v);
						if ('　' == substr($v, -2)) {
							$v = substr($v, 0, -2);
						}

						if ($v && strlen($v) < 16) {
							$match[1][$k] = $v;
						}
					}

					$sql = "select `uid`,`nickname`,`username` from `" .
					TABLE_PREFIX . "members` where `nickname` in ('" . implode("','", $match[1]) .
                        "') ";
					$query = $this->DatabaseHandler->Query($sql);
					while (false != ($row = $query->GetRow())) {
						if($row['uid']>0 && !is_blacklist($tuid, $row['uid']) && !jsg_role_check_allow('topic_at', $row['uid'], $tuid)) {
							$_at = "@{$row['nickname']}";
							$cont_sch[$_at] = $_at;
							$cont_rpl[$_at] = "<M {$row['username']}>@{$row['nickname']}</M> ";
							$at_uids[$row['uid']] = $row['uid'];
						}
					}
				}
			}
		}
				if($topic_info['roottid'] > 0 && in_array($topic_info['type'], array('both', 'forward'))) {
			$rtopic = $this->Get($topic_info['roottid']);
			$ruid = (int) $rtopic['uid'];
			if($ruid > 0 && $ruid != $tuid && !is_blacklist($tuid, $ruid) && !jsg_role_check_allow('topic_at', $ruid, $tuid)) {
				$at_uids[$ruid] = $ruid;
			}
		}
		
				if (false !== strpos($content, '#'))
		{
			$tag_num = ConfigHandler::get('tag_num', 'topic');
			if (preg_match_all('~\#([^\/\-\@\#\[\$\{\}\(\)\;\<\>\\\\]+?)\#~', $content, $match))
			{
				$i = 0;
				foreach ($match[1] as $v)
				{
					$v = trim($v);
					if (($vl = strlen($v)) < 2 || $vl > 50)
					{
						continue;
					}

					$tags[$v] = $v;
					$_tag = "#{$v}#";
					$cont_sch[$_tag] = $_tag;
					$cont_rpl[$_tag] = "<T>#{$v}#</T>";

					if (++$i >= $tag_num) {
						break;
					}
				}
			}
		}
						if (false !== strpos($content, ':/' . '/') || false !== strpos($content, 'www.'))
		{
						if (preg_match_all('~(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&\;_\~\`\:\+\#\.\@\[\]]*(?:[^\<\>\'\"\n\r\t\s\x7f-\xff])*)?~i',
			$content, $match))
			{
				foreach ($match[0] as $v)
				{
					$v = trim($v);
					if (($vl = strlen($v)) < 8)
					{
						continue;
					}
					if (strtolower($this->Config['site_url']) == strtolower(substr($v, 0, strlen($this->
					Config['site_url']))))
					{
						continue;
					}

					if (!($arr = get_url_info($v)))
					{
						continue;
					}

					$_process_result = array();
					if(!isset($urls[$v]) && ($_process_result = $this->_process_url($v)))
					{
						$urls[$v] = $_process_result;
					}

					$rpl = ($_process_result['content'] ? " {$_process_result['content']} " : "") . "<U {$arr['key']}>{$v}</U>";
					if('image' == $_process_result['type'])
					{
						$rpl = ' ';
						if(strlen(trim($content)) <= strlen($v))
						{
							$rpl = ' 分享图片 ';
						}
					}
					elseif('music' == $_process_result['type'])
					{
						$rpl = ' ';
						if(strlen(trim($content)) <= strlen($v))
						{
							$rpl = ' 分享音乐 ';
						}
					}

					$cont_sch[$v] = "{$v}";
					$cont_rpl[$v] = $rpl;
				}
			}
		}

		if($cont_sch && $cont_rpl) {			
						uasort($cont_sch, create_function('$a, $b', 'return (strlen($a)<strlen($b));'));

			foreach($cont_sch as $k=>$v) {
				if($v && isset($cont_rpl[$k])) {
					$content = str_replace($v, $cont_rpl[$k], $content);
				}
			}
		}

		$content = trim($content);

		$return['content'] = $content;

		$return['at_uids'] = $at_uids;

		$return['tags'] = $tags;

		$return['urls'] = $urls;

		return $return;
	}

		
	function _process_url($url)
	{
		$return = array();

		$type = '';

		$ext = trim(strtolower(substr($url,strrpos($url,'.'))));

		if('.swf'==$ext)
		{
			$type = 'flash';

			$type_result = array(
                'id' => $url,
                'host' => $type,
                'url' => $url,
			);
		}
		elseif(in_array($ext,array('.mp3','.wma')))
		{
			$type = 'music';
		}
		elseif(in_array($ext,array('.jpg','jpeg','.gif','.png','.bmp',)))
		{
			$type = 'image';
		}
		else
		{
						$type_result = $this->_parse_video($url);

			if($type_result)
			{
				$type = 'video';
			}
		}

		if($type)
		{
			$return['url'] = $url;
			$return['type'] = $type;

			if($type_result)
			{
				$return[$type] = $type_result;

				if($type_result['title'])
				{
					$return['content'] = $type_result['title'];
				}
			}
		}

		return $return;
	}

	
	function _parse_video($url) {
		$ret = Load::logic('video', 1)->parse($url);

		return $ret;
	}

	
	function _parse_video_image($image_url)
	{
		$return = '';

				if ($image_url)
		{
			$img_src_md5 = md5($image_url);
			$img_path = RELATIVE_ROOT_PATH . 'images/video_img/' . $img_src_md5[0] . $img_src_md5[1] .
                '/';
			$img_name = $img_src_md5[2] . $img_src_md5[3] . crc32($image_url) . '.jpg';
			$video_img = $img_path . $img_name;

			if (!is_file($video_img) && ($temp_image = dfopen($image_url)))
			{



				if (!is_dir($img_path))
				{
					Load::lib('io', 1)->MakeDir($img_path);
				}

				Load::lib('io', 1)->WriteFile($video_img,$temp_image);

				if (!is_image($video_img))
				{
					Load::lib('io', 1)->DeleteFile($video_img);
					$video_img = '';
				}
				else
				{
					if($this->Config['ftp_on'])
					{
						$ftp_result = ftpcmd('upload',$video_img);
						if($ftp_result > 0)
						{
							Load::lib('io', 1)->DeleteFile($video_img);
						}
					}
				}
			}

			$return = $video_img;
		}

		return $return;
	}

	
	function _parse_url_image($data,$image_url) {
		$p = array(
			'pic_url' => $image_url,
			'tid' => $data['tid'],			
		
			'uid' => $data['uid'],
			'username' => $data['username'],
		);
		$rets = Load::logic('image', 1)->upload($p);
		$image_id = max(0, (int) $rets['id']);
		
		return $image_id;		
	}
	
	function _parse_url_attach($data,$attach_url)
	{
		$__is_attach = false;


		$uid = $data['uid'];
		$username = $data['username'];
		$attach_id = Load::logic('attach', 1)->add($uid, $username);

		$p = array(
        	'id' => $attach_id,
        	'tid' => $data['tid'],
        	'file_url' => $attach_url,
		);
		Load::logic('attach', 1)->modify($p);

		$attach_path = RELATIVE_ROOT_PATH . 'data/attachs/topic/' . face_path($attach_id) . '/';
		$attach_type = strtolower(end(explode('.', $attach_url)));
		$attach_name = $attach_id . '.' . $attach_type;
		$attach_file = $attach_path . $attach_name;

		if (!is_file($attach_file))
		{
			if (!is_dir($attach_path))
			{
				Load::lib('io', 1)->MakeDir($attach_path);
			}

			if (($temp_attach = dfopen($attach_url)) && (Load::lib('io', 1)->WriteFile($attach_file,$temp_attach)) && is_attach($attach_file))
			{

				$attach_size = filesize($attach_file);
				$site_url = '';
				if($this->Config['ftp_on'])
				{
					$site_url = ConfigHandler::get('ftp','attachurl');
					$ftp_result = ftpcmd('upload',$attach_file);
				}

				$p = array(
		        	'id' => $attach_id,
        			'vtid' => $data['id'],		        	'site_url' => $site_url,
		        	'file' => $attach_file,
                	'name' => basename($attach_url),
					'filesize' => $attach_size,
					'filetype' => $attach_type,
				);
				Load::logic('attach', 1)->modify($p);

				$__is_attach = true;
			}
		}

		if (false === $__is_attach && $attach_id > 0)
		{
			Load::logic('attach', 1)->delete($attach_id);

			$attach_id = 0;
		}

		return $attach_id;
	}

	function _process_at_uids($data,$at_uids)
	{
		$_at_uids = array();

		$timestamp = time();

		$tid = $data['tid'];

		if($this->Config['notice_email']) {
			$notice_config = ConfigHandler::get('email_notice');
		}

		foreach ($at_uids as $at_uid)
		{
			if($at_uid > 0 && $at_uid!=$data['uid'] && !($this->DatabaseHandler->FetchFirst("select * from `" . TABLE_PREFIX .
            "topic_mention` where `tid`='$tid' and `uid`='$at_uid'")))
			{
				$_at_uids[$at_uid] = $at_uid;

				$this->DatabaseHandler->Query("insert into `" . TABLE_PREFIX . "topic_mention` (`tid`,`tuid`,`uid`,`dateline`) values ('{$tid}','{$data['uid']}','{$at_uid}','{$timestamp}')");

				$this->DatabaseHandler->Query("update `" . TABLE_PREFIX .
                    "members` set `at_new`=`at_new`+1 , `at_count`=`at_count`+1 where `uid`='$at_uid'");

								$user_notice = $this->DatabaseHandler->FetchFirst("select `uid`,`username`,`at_new`,`email`,`notice_at`,`nickname` from `" .
				TABLE_PREFIX . "members` where `uid`='$at_uid'");
				if($user_notice)
				{
					if ($user_notice['notice_at'] == 1) 					{
						if ($this->Config['notice_email'] == 1) 						{
							if(!function_exists('send_mail')) {
								Load::lib('mail');
								$mail_to = $user_notice['email'];
							}

							$mail_subject = "{$notice_config['at']['title']}";
							$mail_content = "{$notice_config['at']['content']}";
							$send_result = send_mail($mail_to, $mail_subject, $mail_content, array(), 3, false);
						}
						else
						{
														Load::logic('notice', 1)->Insert_Cron($user_notice['uid']);
						}
					}

										if ($this->Config['imjiqiren_enable'] && imjiqiren_init($this->Config)) {
						imjiqiren_send_message($user_notice, 't', $this->Config);
					}

										if ($this->Config['sms_enable'] && sms_init($this->Config)) {
						sms_send_message($user_notice, 't', $this->Config);
					}
				}
			}
		}
	}

	function _process_urls($data,$urls,$is_modify=false,$table='topic')
	{
		$tid = $data['tid'];
		$timestamp = time();

		foreach($urls as $k=>$v)
		{
			$url_type = $v['type'];

			if('flash'==$url_type || 'video'==$url_type)
			{
				$videos = $v[$url_type];
				$video_hosts = $videos['host'];
				$video_link = $videos['id'];
				$video_url = $videos['url'];
				if($is_modify && $data['videoid'] > 0) 				{
					$topic_video = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."topic_video where `id`='{$data['videoid']}'");
					if($topic_video['video_url']==$video_url)
					{
						return ;
					}
					else
					{
						$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_video where `id`='{$data['videoid']}'");
					}
				}
				$videos['image_local'] = '';
				if($videos['image_src'])
				{
					$videos['image_local'] = $this->_parse_video_image($videos['image_src']);
				}
				$video_img = $videos['image_local'];
				$video_img_url = '';
				if($video_img)
				{
					$video_img_url = ($this->Config['ftp_on'] ? ConfigHandler::get('ftp','attachurl') : "");
				}

				$this->DatabaseHandler->Query("insert into `" . TABLE_PREFIX .
                "topic_video`(`uid`,`tid`,`username`,`video_hosts`,`video_link`,`video_url`,`video_img`,`video_img_url`,`dateline`) values ('" .
				$data['uid'] . "','" . $data['tid'] . "','" . $data['username'] . "','" . $video_hosts . "','" . $video_link .
                "','" . $video_url . "','" . $video_img . "','$video_img_url','{$timestamp}')");

				$videoid = $this->DatabaseHandler->Insert_ID();

				if($videoid > 0)
				{
					if($table == 'topic_verify'){
						$this->DatabaseHandler->Query("update `" . TABLE_PREFIX . "topic_verify` set `videoid`='{$videoid}' where `id`='{$data['id']}'");
					}else{
						$this->DatabaseHandler->Query("update `" . TABLE_PREFIX . "topic` set `videoid`='{$videoid}' where `tid`='{$data['tid']}'");
					}
				}
			}
			elseif('music'==$url_type)
			{
				if($is_modify && $data['musicid'] > 0)
				{
					$topic_music = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."topic_music where `id`='{$data['musicid']}'");
					if($topic_music['music_url']==$v['url'])
					{
						return ;
					}
					else
					{
						$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_music where `id`='{$data['musicid']}'");
					}
				}

				$this->DatabaseHandler->Query("insert into `" . TABLE_PREFIX .
                "topic_music`(`uid`,`tid`,`username`,`music_url`,`dateline`) values ('" .
				$data['uid'] . "','" . $data['tid'] . "','" . $data['username'] . "','{$v['url']}','{$timestamp}')");

				$musicid = $this->DatabaseHandler->Insert_ID();

				if($musicid > 0)
				{
					if($table == 'topic_verify'){
						$this->DatabaseHandler->Query("update `" . TABLE_PREFIX . "topic_verify` set `musicid`='{$musicid}' where `id`='{$data['id']}'");
					}else{
						$this->DatabaseHandler->Query("update `" . TABLE_PREFIX . "topic` set `musicid`='{$musicid}' where `tid`='{$data['tid']}'");
					}
				}

			} elseif('image'==$url_type) {
				if($is_modify && $data['imageid']) {
					$topic_image = Load::logic('image', 1)->get_info($data['imageid']);
					if($topic_image['image_url']==$v['url']) {
						return ;
					} else {
						Load::logic('image', 1)->delete($data['imageid']);
					}
				}

				$this->_parse_url_image($data,$v['url']);
			}
			elseif('attach'==$url_type)
			{
				if($is_modify && $data['attachid'])
				{
					$topic_attach = Load::logic('attach', 1)->get_info($data['attachid']);
					if($topic_attach['attach_url']==$v['url'])
					{
						return ;
					}
					else
					{
						Load::logic('attach', 1)->delete($data['attachid']);
					}
				}

				$this->_parse_url_attach($data,$v['url']);
			}
		}
	}

	
	function is_qun_member($qid, $uid)
	{
		return Load::logic('qun', 1)->is_qun_member($qid, $uid);
	}

	
	function check_view_perm($uid, $type)
	{
		return true;
	}

	function GetMedal($medalid=0,$uid=0)
	{
		
		
		

		$uid = (is_numeric($uid) ? $uid : 0);

		$medal_list = array();

		if($uid > 0)
		{
			$sql = "select  U_MEDAL.dateline ,  MEDAL.medal_img , MEDAL.conditions
            			  , MEDAL.medal_name ,MEDAL.medal_depict ,MEDAL.id , U_MEDAL.*
            		from `".TABLE_PREFIX."medal` MEDAL
            		left join `".TABLE_PREFIX."user_medal` U_MEDAL on MEDAL.id=U_MEDAL.medalid
            		where U_MEDAL.uid='{$uid}'
            		and U_MEDAL.is_index = 1
            		and MEDAL.is_open = 1 ";

			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow()))
			{
				$row['dateline'] = date('m-d日 H:s ',$row['dateline']);
				$medal_list[$row['id']] = $row;
			}
		}

		return $medal_list;
	}

	
	function GetParentTopic($topic_list, $get_parent = 0)
	{
		$parent_list = array();
		if ($topic_list)
		{
						$parent_id_list = array();
			foreach ($topic_list as $row)
			{
				if($get_parent && 0 < ($p = (int) $row['parent_id']))
				{
					$parent_id_list[$p] = $p;
				}
				if (0 < ($p = (int) $row['top_parent_id']))
				{
					$parent_id_list[$p] = $p;
				}
			}

			if ($parent_id_list)
			{
				$parent_list = $this->Get($parent_id_list);
			}
		}

		return $parent_list;
	}

	
	function GetForwardContent($content) {
				$seprator = $this->ForwardSeprator;
		$seprator = trim($seprator);
		$strpos = strpos($content, $seprator);

		if(false !== $strpos) {
			$content = substr($content, 0, $strpos);
		}

		return $content;
	}

	
	function atMyUser($uid=MEMBER_ID,$limit=10){
		$cache_id = $uid.'-atmyuser-7days-'.$limit;
				$time = TIMESTAMP;
		$time = $time - 7*86400;
		if(!($user = cache_db('get', $cache_id))){
			$user = array();
			$sql = "SELECT COUNT(*) AS at_count ,m.uid,m.username,m.nickname
					FROM ".TABLE_PREFIX."topic_mention tm 
					LEFT JOIN ".TABLE_PREFIX."topic t ON t.tid = tm.tid 
					LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.uid 
					WHERE tm.uid = '$uid' AND tm.dateline > '$time'
					GROUP BY t.uid 
					ORDER BY at_count desc 
					LIMIT $limit ";
			$query = DB::query($sql);
			while($rs = DB::fetch($query)){
				if($rs['uid'] > 0) {
					$rs['face'] = face_get($rs['uid']);
					$user[$rs['uid']] = $rs;
				}
			}

			cache_db('set', $cache_id,$user,3600);
		}
		return $user;
	}

	
	function getCommentUser($uid=MEMBER_ID,$limit=10){
		$cache_id = $uid.'-commentuser-7days-'.$limit;
				$time = TIMESTAMP;
		$time = $time - 7*86400;
		if(!($user = cache_db('get', $cache_id))){
			$user = array();
			$sql = "SELECT
					  COUNT(*) AS c_count,t.uid,m.username,m.nickname 
					FROM `".TABLE_PREFIX."topic` t 
					LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.uid 
					WHERE t.`touid` = '$uid'  
					    AND t.`type` IN ('reply','both') 
					    AND t.dateline > $time  
					GROUP BY t.`uid` 
					ORDER BY c_count DESC 
					LIMIT $limit  ";
			$query = DB::query($sql);
			while($rs = DB::fetch($query)){
				if($rs['uid'] > 0) {
					$rs['face'] = face_get($rs['uid']);
					$user[$rs['uid']] = $rs;
				}
			}

			cache_db('set', $cache_id,$user,3600);
		}
		return $user;
	}

	
	function getMyCommentUser($uid=MEMBER_ID,$limit=10){
		$cache_id = $uid.'-mycommentuser-7days-'.$limit;
				$time = TIMESTAMP;
		$time = $time - 7*86400;
		if(!($user = cache_db('get', $cache_id))){
			$user = array();
			$sql = "SELECT
					  COUNT(*) AS mc_count,t.touid as uid,m.username,m.nickname 
					FROM `".TABLE_PREFIX."topic` t 
					LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.touid 
					WHERE t.`uid` = '$uid'  
					    AND t.`type` IN ('reply','both') 
					    AND t.dateline > $time  
					GROUP BY t.`touid` 
					ORDER BY mc_count DESC 
					LIMIT $limit  ";
			$query = DB::query($sql);
			while($rs = DB::fetch($query)){
				if($rs['uid'] > 0) {
					$rs['face'] = face_get($rs['uid']);
					$user[$rs['uid']] = $rs;
				}
			}

			cache_db('set', $cache_id,$user,3600);
		}
		return $user;
	}

	function getMusicUser($limit=10){
				$time = TIMESTAMP;
		$time = $time - 30*86400;
		$user = array();
		$sql = "SELECT
				  COUNT(*) AS m_count,t.uid,m.username,m.nickname 
				FROM `".TABLE_PREFIX."topic_music` t 
				LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.uid 
				WHERE t.dateline > $time  
				GROUP BY t.`uid` 
				ORDER BY m_count DESC 
				LIMIT $limit  ";
		$query = DB::query($sql);
		while($rs = DB::fetch($query)){
			if($rs['uid'] > 0) {
				$rs['face'] = face_get($rs['uid']);
				$user[$rs['uid']] = $rs;
			}
		}

		return $user;
	}
	
	function _content_end($c) {
		$_srrp = strrpos($c, '<');
		if(false !== $_srrp) {
			$_r = substr($c, $_srrp);
			if(substr_count($_r, '<') != substr_count($_r, '>')) {
				$c = substr($c, 0, $_srrp);
			}
		}
		return $c;
	}
	function _content_strip($c) {
		$c = trim(strip_tags((string) $c));
		if($c && false!==strpos($c, '<')) {
			$c = htmlspecialchars($c);
			$c = str_replace('&amp;', '&', $c);
		}
		return $c;
	}
}

?>