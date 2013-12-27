<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename vote.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-24 16:33:35 447949020 381416262 18947 $
 *******************************************************************/



 

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);	
		$this->initMemberHandler();
		
		Load::logic('vote');
		$this->VoteLogic = new VoteLogic();
		
		
		$this->TopicLogic = Load::logic('topic', 1);
		
		$code = &$this->Code;
		if (empty($code)) {
			$code = 'index';
		}
		
		if (!in_array($code, array('joined', 'daren'))) {
						$this->_check_login();
		}
		
		if (method_exists('ModuleObject', $code)) {
			$this->$code();
		} else {
			exit;
		}
	}

	
	function create()
	{
                if(!($this->MemberHandler->HasPermission($this->Module,$this->Code)))
        {
            json_error($this->MemberHandler->GetError());
        }            	   
	   
		$data = $this->Post;
		
				$member = $this->MemberHandler->MemberFields;
		$data['username'] = jaddslashes($member['username']);
		$data['uid'] = $member['uid'];
		
				$data['item'] = $this->Get['item'];
		$data['item_id'] = $this->Get['item_id'];
		
		if($this->Config['vote_verify']){
			$data['verify'] = 0;
		}
		
		$result = array();
		$ret = $this->VoteLogic->create($data, $result);
		if ($ret > 0) {
			$sys_config = ConfigHandler::get();
			$value = '我发起了一个投票【'.$result['subject'].'】，地址：' . get_full_url($sys_config['site_url'],'index.php?mod=vote&code=view&vid='.$result['vid']);
						$values = array(
				'content' => $value,
				'vid' => $result['vid'],
				'item' => 'vote',
			);
			json_result('发布成功', $values);
		} else {
			if ($ret == -1) {
				json_error("投票主题长度不能小于两个字节。");
			} else if ($ret == -2) {
				json_error("只有一个投票项不允许发布。");
			} else if ($ret == -3) {
				json_error("投票截止时间小于当前时间。");
			}
		}
	}
	
		function edit()
	{
		$vid = intval($this->Post['vid']);
		$options = $this->Post['old_option'];
		$new_options = $this->Post['option'];
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote)|| $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		if ($vote['uid'] != MEMBER_ID && MEMBER_ROLE_TYPE != 'admin') {
			json_error('你没有权限');
		}
		
				$is_voted = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." WHERE vid='{$vid}'");
		
		$no_chk_maxchoice = false;
		if ($is_voted) {
			$no_chk_maxchoice = true;
		}
		
		$post_data = $this->Post;
		$params = array(
			'no_chk_option' => true,
			'no_chk_maxchoice' => $no_chk_maxchoice,
		);
		$ret = $this->VoteLogic->chk_post($post_data, 'modfiy', $params);
		if ($ret == -1) {
			json_error("投票主题长度不能小于两个字节。");
		} else if ($ret == -3) {
			json_error("投票截止时间小于当前时间。");
		}
		
		$where_ary = array('vid' => $vid);
				if ($no_chk_maxchoice) {
			$set_ary = array(
				'subject' => $post_data['subject'],
				'is_view' => $post_data['is_view'],
				'expiration' => $post_data['expiration'],
			);
		} else {
			$set_ary = array(
				'subject' => $post_data['subject'],
				'maxchoice' => $post_data['maxchoice'],
				'multiple' => $post_data['maxchoice'] > 1 ? 1 : 0,
				'is_view' => $post_data['is_view'],
				'expiration' => $post_data['expiration'],
			);
		}
		DB::update("vote", $set_ary, $where_ary);
		
				DB::update("vote_field", array('message'=>$post_data['message']), $where_ary);
		$this->VoteLogic->update_options($vid, $options, $new_options, $is_voted);
		json_result('编辑投票项成功');
	}
	
	
	function vote()
	{
	            if(!($this->MemberHandler->HasPermission($this->Module,$this->Code)))
        {
            json_error($this->MemberHandler->GetError());
        }            	   
	   	
                $chk_topic_type = true;
        
	   	$tid = empty($this->Post['tid']) ? 0 : trim($this->Post['tid']);
		$vid = empty($this->Get['vid']) ? 0 : intval($this->Get['vid']);
		$vote = $this->VoteLogic->id2voteinfo($vid);
		$member = $this->MemberHandler->MemberFields;
		if(empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		
		$toweibo = $this->Post['toweibo'] == 1 ? true : false;
		
				if (TIMESTAMP >= $vote['expiration']) {
			json_error('当前投票已经过期了');
		}
		
				$option = $this->Post['option'];
		if (empty($option)) {
			json_error('你还没有选择呢');	
		}
		
		$anonymous = $this->Post['anonymous'];
		
		$param = array(
			'vid' => $vid,
			'uid' => $member['uid'],
			'username' => $member['username'],
			'maxchoice' => $vote['maxchoice'],
			'option' => $option,
			'anonymous' => $anonymous,
			'create_uid' => $vote['uid'],			);
		
		$result = array();
		$ret = $this->VoteLogic->do_vote($param, $result);
		switch ($ret) {
			case 1:
				$msg = "投票成功";
				
								if ($toweibo && empty($anonymous)) {
					if (!empty($tid)) {
						$__handle_key = $tid;
					}
					$sys_config = ConfigHandler::get();
					
										$item = "vote";
					$item_id = $vid;
					
					include template('vote_toweibo');
					exit;
				} else {
					$retval = array(
						'toweibo' => false,
						'vid' => $vote['vid'],
					);
				}
				json_result($msg, $retval);
				break;
			case -1:
				json_error('您已经投过票了，不允许重复投票');	
				break;
			case -2:
				json_error("至多允许选择{$vote['maxchoice']}项目");
				break;
			case -3:
				json_error("投票项不存在");
				break;
		}	
	}
	
		function del()
	{
		$id = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		if ($id) {
			$ret = $this->VoteLogic->delete($id);
		} 
		if (!empty($ret)) {
			json_result('删除投票成功');
		} else {
			json_error('删除投票失败');
		}
	}
	
		function modify_date()
	{
		$vid = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		$expiration = empty($this->Post['expiration']) ? '' : trim($this->Post['expiration']);
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		
		if ($vote['uid'] != MEMBER_ID && MEMBER_ROLE_TYPE != 'admin') {
			json_error("你没有权限");
		}
		
		$ret = $this->VoteLogic->modify_expiration($vid, $expiration);
		if ($ret == 1) {
			json_result('修改截止日期成功');
		} else if ($ret == -1){
			json_error('截止时间不能小于当前时间');
		}
	}
	
			function add_opt()
	{
		$vid = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		$option = empty($this->Post['option']) ? '' : trim($this->Post['option']);
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		if ($vote['uid'] != MEMBER_ID && MEMBER_ROLE_TYPE != 'admin') {
			json_error('你没有权限');
		}
		$old_options = unserialize($vote['option']);
		$ret = $this->VoteLogic->add_opt($vid, $option);
		if ($ret == 1) {
			json_result('增加投票项成功');
		} else if ($ret == -1){
			json_error('超过了最大的投票项');
		} else if ($ret == -2) {
			json_error('新投票项的长度不符合要求');
		}
	}
	
			function edit_opt()
	{
		$vid = intval($this->Post['vid']);
		$options = $this->Post['option'];
		$new_options = $this->Post['new_option'];
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		if ($vote['uid'] != MEMBER_ID && MEMBER_ROLE_TYPE != 'admin') {
			json_error('你没有权限');
		}
		$old_options = unserialize($vote['option']);
		$preview_updata_flg = false;
		
				if (!empty($options)) {
						$count = 0;
			if (MEMBER_ROLE_TYPE != 'admin') {
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." WHERE vid='{$vid}'");
			}
			
			if (!$count) {
				$preview = array();
				$keys = array_keys($options);
				$options = array_unique($options);
				
								if (count($options) > 1) {
					foreach ($keys as $i) {
						if (!empty($options[$i])) {
														$val = $options[$i];
							$p = getstr(trim($val), 40, 1, 1);
							if (empty($p)) {
								continue;
							}
							DB::update('vote_option', array('option'=>$p), array('oid'=>$i));
						} else {
														DB::query("DELETE FROM ".DB::table('vote_option')." WHERE oid='{$i}'");
						}
					}
					$preview_updata_flg = true;
				}
			}
		}
		
				if (!empty($new_options)) {
			$new_options = array_unique($new_options);
			foreach ($new_options as $val) {
				$ret = $this->VoteLogic->add_opt($vid, $val);
			}
		}
		
		if ($preview_updata_flg) {
						$preview = array();
			$options = $this->VoteLogic->get_option_by_vid($vid);
			foreach ($options['option'] as $val) {
				if(count($preview) < 2 ) {
					$preview[] = $val['option'];
				}
			}
			$str_options = jaddslashes(serialize($preview));
			DB::update('vote_field', array('option'=>$str_options), array('vid'=>$vid));
		}
		json_result('编辑投票项成功');
	}
	
	
	function vote_publish()
	{
		if(MEMBER_ID < 1){
			$is_allowed = "请先登录或者注册一个帐号";	
		}		
		
				if (MEMBER_ROLE_TYPE != 'admin' && !$is_allowed) {
			load::logic('vote');
			$VoteLogic = new VoteLogic();
			$is_allowed = $VoteLogic->allowedCreate(MEMBER_ID);
		}
		if($is_allowed){
			exit($is_allowed);
		}
		
		$max_option = 20;
		$perpage = 5;
		$options = range(1, $perpage);
		$exp_info = $this->VoteLogic->get_publish_form_param();
		extract($exp_info);
		include (template('vote_publish'));	
	}
	
	
	function my_vote()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$perpage = 8;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $perpage;
		
		$uid = MEMBER_ID;
		$where_sql = " uid='{$uid}' AND verify = 1 ";
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote')." WHERE {$where_sql} ");
		if ($count) {
			$list = array();
			$sys_config = ConfigHandler::get();
			$query = DB::query("SELECT vid,subject 
								FROM ".DB::table('vote')." 
								WHERE {$where_sql} 
								ORDER BY dateline DESC 
								LIMIT $start,$perpage ");
			while ($value = DB::fetch($query)) {
				$value['vote_url'] = get_full_url($sys_config['site_url'],'index.php?mod=vote&code=view&vid='.$value['vid']);
				$value['radio_value'] = str_replace(array('"', '\''), '', $value['subject']).' - '.$value['vote_url'];
				$list[] = $value;
			}
			$multi = ajax_page($count, $perpage, $page, 'getMyVoteList');
		}
		include(template('vote_list_my_ajax'));
	}
	
	
	function my_join()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$perpage = 8;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $perpage;
		
		$uid = MEMBER_ID;
		$where_sql = " vu.uid='{$uid}' ";
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." AS vu WHERE {$where_sql}");
		if ($count) {
			$query = DB::query("SELECT v.vid,v.subject 
					   FROM ".DB::table('vote_user')." AS vu 
					   LEFT JOIN ".DB::table("vote")." AS v  
					   USING (vid)
					   WHERE $where_sql 
					   ORDER BY vu.dateline DESC 
					   LIMIT {$start},{$perpage}");
			while ($value = DB::fetch($query)) {
				$value['vote_url'] = get_full_url($sys_config['site_url'],'index.php?mod=vote&code=view&vid='.$value['vid']);
				$value['radio_value'] = str_replace(array('"', '\''), '', $value['subject']).' - '.$value['vote_url'];
				$list[] = $value;
			}
			$multi = ajax_page($count, $perpage, $page, 'getMyJoinList');
		}
		include(template('vote_list_my_ajax'));
	}
	
	
	function joined()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$type = trim($this->Get['type']);
		$vid = empty($this->Get['vid']) ? 0 : intval($this->Get['vid']);
		if ($page == 0) {
			$page = 1;
		}
		$prepage = 6;
		$start = ($page - 1) * $prepage;
		$where_sql = " 1 ";
		$page_param = array();
		if ($type == 'follow') {
			$this->_check_login();
			$buddy_ids = get_buddyids(MEMBER_ID);
			$where_sql .= " AND vu.vid='{$vid}' AND uid IN(".jimplode($buddy_ids).") ";
			$page_param = array('c'=>2);
		} else {
			$type = 'all';
			$where_sql .= " AND vu.vid='{$vid}' ";
			$page_param = array('c'=>1);
		}
		$order_sql = " vu.dateline DESC ";
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." AS vu WHERE {$where_sql}");
		if ($count) {
			$query = DB::query("SELECT vu.*,m.nickname  
					   FROM ".DB::table('vote_user')." AS vu 
					   LEFT JOIN ".DB::table("members")." AS m 
					   USING (uid)
					   WHERE $where_sql 
					   ORDER BY $order_sql 
					   LIMIT {$start},{$prepage}");
			while ($value = DB::fetch($query)) {
				$value['option'] = unserialize($value['option']);
				$value['option'] = '"'.implode('","', $value['option']).'"';
				$value['dateline'] = my_date_format2($value['dateline']); 
								if (empty($value['username'])) {
					$value['nickname'] = '**';
				}
				$list[] = $value;
			}
			$multi = ajax_page($count, $prepage, $page, 'getVoteJoined', $page_param);
		}
		include template('vote_ajax_joined');
	}
	
	
	function daren()
	{
		$uids = array();
		$param = array(
			'where' => ' voter_num>0 ',
			'order' => ' voter_num DESC ',
			'limit' => ' 12 ',
		);
		$info = $this->VoteLogic->get_list($param);
		if (!empty($info)) {
			foreach ($info as $val) {
				$uids[] = $val['uid'];
			}
			if (!empty($uids)) {
				$vote_darens = $this->TopicLogic->GetMember($uids);
				include(template('vote_daren_list_ajax'));
			}
		}
		exit;
	}
	
	
	function manage()
	{
		$op = empty($this->Get['op']) ? '' : $this->Get['op'];
		if (empty($op)) {
			exit;
		}
		$vid = empty($this->Get['vid']) ? 0 : intval($this->Get['vid']);
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		
		if ($vote['uid'] != MEMBER_ID && MEMBER_ROLE_TYPE != 'admin') {
			json_error("你没有权限");
		}
		
		if ($op == 'modify_date') {
			$exp_info = $this->VoteLogic->get_publish_form_param($vote['expiration']);
			extract($exp_info);
		} else if ($op == 'edit_opt') {
						$info = $this->VoteLogic->get_option_by_vid($vid);
			$options = $info['option'];
			$option_num = count($info['option']);
			
						if (MEMBER_ROLE_TYPE != 'admin') {
				$is_voted = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." WHERE vid='{$vid}'");
			}
		} else if ($op == 'edit') {
						$perpage = 5;
			$max_option = 20;
			$this->Get['arf'] = "edit";
			$opt_info = $this->VoteLogic->get_option_by_vid($vid);
			$opts= $opt_info['option'];
			
						$info = DB::fetch_first("SELECT message FROM ".DB::table('vote_field')." WHERE vid='{$vid}'");
			$vote['message'] = $info['message'];
			
						$options_num = count($opts);
			$maxchoice = array();
			if ($options_num > 1) {
				$maxchoice = range(1, $options_num);
			}
			
						if ($options_num <= 5) {
				$options = range(1, 5);
			} else if ($options_num > 5 && $options_num <= 10) {
				$options = range(1, 10);	
			} else if ($options_num > 10 && $options_num <= 15) {
				$options = range(1, 15);
			} else if ($options_num > 15 && $options_num <= 20) {
				$options = range(1, 20);
			}
			
						$is_voted = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." WHERE vid='{$vid}'");
			
			$checked = array();
			$checked['is_view'][$vote['is_view']] = 'checked="checked"';
			$checked['recd']= $vote['recd'] ? 'checked="checked"' : '';
			$selected[$vote['maxchoice']] = 'selected="selected"';
			$expiration = my_date_format($vote['expiration'], 'Y-m-d');
			$hour_select = mk_time_select('hour', my_date_format($vote['expiration'], 'H'));
			$min_select = mk_time_select('min', my_date_format($vote['expiration'], 'i'));
			include(template('vote_edit'));
			exit;
		}
		include(template('vote_manage'));
	}
	
	
	function detail()
	{
		$vid = intval($this->Post['vid']);
		$tid = trim($this->Post['tid']);
		$vote = $this->VoteLogic->id2voteinfo($vid);
		if(empty($vote) || $vote['verify'] == 0) {
			response_text('当前投票不存在或正在审核中!');
		}
		$ret = $this->VoteLogic->process_detail($vote, MEMBER_ID);
		extract($ret);
				$member = $this->TopicLogic->GetMember($vote['uid']);
		include(template('widgets_vote_view'));
	}
	
	
	function toweibo()
	{
		include (template('vote_toweibo'));
	}
	
	
	function _check_login()
	{
		if (MEMBER_ID < 1) {
			json_error("你需要先登录才能继续本操作");	
		}
	}

}
?>
