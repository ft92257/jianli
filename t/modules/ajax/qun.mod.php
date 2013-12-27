<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename qun.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-05-16 17:13:55 868291984 303721391 14874 $
 *******************************************************************/



 

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $item = 'qun';
	function ModuleObject($config)
	{
		$this->MasterObject($config);	
		$this->initMemberHandler();
		
				if ($this->Code != 'widgets') {
			if (MEMBER_ID < 1) {
				json_error("请先登录或者注册一个帐号");	
			}
		}
		
		$this->TopicLogic = Load::logic('topic', 1);
		
		Load::logic('qun');
		$this->QunLogic = new QunLogic();
		$this->Execute();
	}

	
		

	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'setvoterecd':
				$this->setVoteRecd();
				break;
			case 'seteventrecd':
				$this->setEventRecd();
				break;
			case 'second_cat':
				$this->second_cat();
				break;
			case 'join':
				$this->join();
				break;
			case 'quit':
				$this->quit();
				break;
			case 'recd2w':
				$this->recd2w();
				break;
			case 'recdqun':
				$this->recdqun();
				break;
			case 'tcqun':
				$this->tcQun();
				break;
			case 'block':
				$this->block();
				break;
			case 'userfriends':
				$this->userfriends();
				break;
			case 'userfans':
				$this->userfans();
				break;
			case 'send_invite':
				$this->send_invite();
				break;
			case 'widgets':
				$this->widgets();
				break;
			default:
				exit();
				break;
		}
		response_text(ob_get_clean());
	}
	
	
	function setVoteRecd(){
		$qid = (int) $this->Post['qid'];
		$vid = (int) $this->Post['vid'];
		if($qid < 1 || $vid < 1){
			json_error('错误群投票ID');
		}
		
		$recd = DB::result_first("select recd from ".DB::table('qun_vote')." where qid = '$qid' and vid = '$vid' ");
		$msg = $recd ? '取消推荐成功' : '推荐成功、可在群右侧页面看到';
		$recd = $recd ? 0 : 1;
		DB::query("update ".DB::table('qun_vote')." set recd = '$recd' where qid = '$qid' and vid = '$vid' ");
		json_result($msg);
	}
	
	
	function setEventRecd(){
		$qid = (int) $this->Post['qid'];
		$eid = (int) $this->Post['eid'];
		if($qid < 1 || $eid < 1){
			json_error('错误群活动ID');
		}
		
		$recd = DB::result_first("select recd from ".DB::table('qun_event')." where qid = '$qid' and eid = '$eid' ");
		$msg = $recd ? '取消推荐成功' : '推荐成功、可在群右侧页面看到';
		$recd = $recd ? 0 : 1;
		DB::query("update ".DB::table('qun_event')." set recd = '$recd' where qid = '$qid' and eid = '$eid' ");
		json_result($msg);
	}
	
	
	function second_cat()
	{	
		$cat_id = $this->Get['cat_id'];
		$groupselect = $this->QunLogic->get_catselect($cat_id, 0, true);
		echo $groupselect['second'];
		exit;
	}
	
	
	function join()
	{
		$qid = empty($this->Post['qid']) ? 0 : intval(trim($this->Post['qid']));
		if ($qid == 0) {
			json_error('错误的操作');
		}
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			json_error('当前微群不存在或已经被删除');
		}
		$r = $this->QunLogic->is_qun_member($qid, MEMBER_ID);
		if ($r) {
			json_error('你已经是当前微群成员了');
		}
		
				$join_type = $qun_info['join_type'];
		$tmp = $this->MemberHandler->MemberFields;
		
		$message = '';
		if ($join_type == 1) {
			$message = trim($this->Post['message']);
			if (empty($message)) {
				$message = '什么都没有写';
							}
			$message = getstr($message, 280, 1, 1);
		}
		$member = array(
			'uid' => MEMBER_ID,
			'username' => $tmp['username'],
			'message' => $message,
		);
		unset($tmp);
		
		$level = $this->QunLogic->qun_level($qid);
		if ($level['member_num'] <= $qun_info['member_num']) {
			json_error('已经达到人数上限无法再加入');
		}
		
		$this->QunLogic->join_qun($qid, $member, $join_type);
		if ($join_type == 0) {
									$nickname = DB::result_first("SELECT nickname 
										  FROM ".DB::table('members')." 
										  WHERE uid='{$qun_info['founderuid']}'");
			$data = array(
				'qid' => $qid,
				'nickname' => $nickname,
				'qun_name' => $qun_info['name'],
			);
			$txt_content = $this->_recd_msg('join_success', $data);
			
			
			$recd = true;
			$value = $txt_content;
						include template('qun/response_join');
								} else if ($join_type == 1) {
						json_result('申请加入成功，正在等待审核');
		}
		exit;
	}
	
	
	function quit()
	{
		$qid = empty($this->Post['qid']) ? 0 : intval(trim($this->Post['qid']));
		if ($qid == 0) {
			json_error('错误的操作');
		}
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			json_error('当前微群不存在或已经被删除');
		}
		$r = $this->QunLogic->is_qun_member($qid, MEMBER_ID);
		if ($r == 0) {
			json_error('错误的操作');
		}
		$this->QunLogic->quit_qun($qid, MEMBER_ID);
		json_result("退出微群成功");
	}
	
		function create()
	{
	}
	
		function _recd_msg($type, $parma)
	{
		$message = '';
		$sys_config = ConfigHandler::get();
		$qun_url = $sys_config['site_url'].'/index.php?mod=qun&qid='.$parma['qid'];
		if ($type == 'join_success') {
			$message = '我刚加入了 @'.$parma['nickname'].' 的微群 “'.$parma['qun_name'].'” 挺不错的 '.$qun_url.' 推荐大家也来看看~ ';
		} else if ($type == 'recd2w') {
			$message = '@'.$parma['nickname'].' 的微群 "'.$parma['qun_name'].'" 挺不错的 '.$qun_url.' 推荐大家也来看看~ ';
		}
		return $message;
	}
	
	
	function userfriends()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$qid = empty($this->Get['qid']) ? 0 : intval(trim($this->Get['qid']));
		if ($qid == 0) {
			js_alert_output('错误的操作');
		}
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			js_alert_output('当前微群不存在或已经被删除');
		}
		
				$prepage = 12;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $prepage;
		
		$buddyids = array();
		$uid = MEMBER_ID;
		$count = DB::result_first("SELECT COUNT(*)  
								   FROM ".DB::table("buddys")." 
								   WHERE `uid`='{$uid}' AND buddyid!='{$qun_info[founderuid]}'");
		
		if ($count) {
			$query = DB::query("SELECT `buddyid`  
								FROM ".DB::table("buddys")." 
								WHERE `uid`='{$uid}' AND buddyid!='{$qun_info[founderuid]}'   
								LIMIT $start,$prepage");
			while ($value = DB::fetch($query)) {
				$buddyids[] = $value['buddyid'];
			}
			$members = $this->TopicLogic->GetMember($buddyids);
			$multi = ajax_page($count, $prepage, $page, 'getUserFriends', array('qid' => $qid));
			include_once(template("qun/userfriends"));
		}
	}
	
	
	function userfans()
	{
				$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$qid = empty($this->Get['qid']) ? 0 : intval(trim($this->Get['qid']));
		if ($qid == 0) {
			json_error('错误的操作');
		}
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			json_error('当前微群不存在或已经被删除');
		}
		
		$prepage = 20;		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $prepage;
		$fans_ids = array();
		$uid = MEMBER_ID;
		$count = DB::result_first("SELECT count(*)   
							FROM ".DB::table("buddys")." 
							WHERE `buddyid`='{$uid}' AND uid!='{$qun_info[founderuid]}'");
		if ($count) {
			$query = DB::query("SELECT `uid`  
								FROM ".DB::table("buddys")." 
								WHERE `buddyid`='{$uid}' AND uid!='{$qun_info[founderuid]}'    
								LIMIT $start,$prepage");
			while ($value = DB::fetch($query)) {
				$fans_ids[] = $value['uid'];
			}
			$members = $this->TopicLogic->GetMember($fans_ids);
			$multi = ajax_page($count, $prepage, $page, 'getUserFans', array('qid' => $qid));
		}
		include_once(template("qun/userfans"));
	}
	
	
	function send_invite()
	{
		$qid = empty($this->Post['qid']) ? 0 : intval(trim($this->Post['qid']));
		if ($qid == 0) {
			js_alert_output('错误的操作');
		}
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			js_alert_output('当前微群不存在或已经被删除');
		}
		
				if (!$this->QunLogic->is_qun_member($qid, MEMBER_ID)) {
			js_alert_output('没有权限进行当前操作');
		}
		
		$my = $this->MemberHandler->MemberFields;
		$config = ConfigHandler::get();
		$qun_url = $config['site_url']."/index.php?mod=qun&qid=".$qid;
		$message = "你好，{$my['nickname']} 邀请你加入 \"{$qun_info['name']}\" 微群，点击{$qun_url}进入。{$my['nickname']} 说：\"\"";

	}
	
		function recd2w()
	{
		$qid = empty($this->Get['qid']) ? 0 : intval(trim($this->Get['qid']));
		if ($qid == 0) {
			js_alert_output('错误的操作');
		}
		$qun_info = $this->QunLogic->get_qun_info($qid);
		if (empty($qun_info)) {
			js_alert_output('当前微群不存在或已经被删除');
		}
		$nickname = DB::result_first("SELECT nickname 
									  FROM ".DB::table('members')." 
									  WHERE uid='{$qun_info['founderuid']}'");
		$data = array(
			'qid' => $qid,
			'nickname' => $nickname,
			'qun_name' => $qun_info['name'],
		);
		$value = $this->_recd_msg('recd2w', $data);
						include(template('qun/recommend2weibo'));
		
	}
	
		function recdqun()
	{
		$cat_id = empty($this->Get['cat_id']) ? 0 : intval(trim($this->Get['cat_id']));
		if (empty($cat_id)) {
			response_text('错误的操作');	
		}
		$cat_ary = $this->QunLogic->get_category();		if (empty($cat_ary)) {
						exit;
		}
		
		$top_cat_ary = $cat_ary['first'];
		$sub_cat_ary = $cat_ary['second'];
		if (!isset($top_cat_ary[$cat_id])) {
			response_text('当前分类不存在或者已经被删除');
		}
		
		if (empty($sub_cat_ary)) {
			response_text('当前分类下不存在微群');
		}
		
		$cat_ids = array();
		foreach ($sub_cat_ary as $value) {
			if ($cat_id == $value['parent_id']) {
				$cat_ids[] = $value['cat_id'];
			}
		}
		$cat_ids[] = $cat_id;
		
		if (empty($cat_ids)) {
						exit;
		}
	
		$where_sql = "cat_id IN(".jimplode($cat_ids).")";
		
				$where_sql .= ' AND gview_perm=0 ';
		
				$where_sql .=' AND recd = 1 ';
		
				$limit = 10;
		$qun_list = array();
		$query = DB::query("SELECT * 
							FROM ".DB::table('qun')." 
							WHERE {$where_sql} 
							ORDER BY member_num DESC
							LIMIT {$limit}");
		while ($value = DB::fetch($query)) {
			$value['icon'] = $this->QunLogic->qun_avatar($value['qid'], 's');
			$value['desc'] = getstr($value['desc'], 80);
			$qun_list[] = $value;
		}
		include_once template('qun/qun_list');
	}
	
		function tcQun(){
		$tc = 1;
		$province = (int) trim($this->Get['province']);
		$city = (int) trim($this->Get['city']);
		if (empty($province) && empty($city)) {
			response_text('错误的操作');	
			exit;
		}
		
		$filed = $city ? "city" : "province";
		$id = $city ? $city : $province;
		
		$name = DB::result_first("select name from ".DB::table('common_district')." where id='$id'");;
		
		$limit = 10;
		$qun_list = array();
		$query = DB::query("SELECT * 
							FROM ".DB::table('qun')." 
							WHERE {$filed} = '$name' 
							ORDER BY member_num DESC 
							LIMIT {$limit}");
		while ($value = DB::fetch($query)) {
			$value['icon'] = $this->QunLogic->qun_avatar($value['qid'], 's');
			$value['desc'] = getstr($value['desc'], 80);
			$qun_list[] = $value;
		}
		include_once template('qun/qun_list');
	}
	
	
	function block()
	{
		$type = trim($this->Get['type']);
		$qun_list = array();
		if ($type == '24hot') {
			$qun_list = $this->QunLogic->get_hot_list();
			include_once template('qun/qun_block_list');	
		}
		exit;
	}
	
	
	function widgets()
	{
		$op_ary = array('simple_desc', 'my_qun');
		$op = $this->Get['op'];
		if (!in_array($op, $op_ary)) {
						exit;
		}
		if ($op == 'simple_desc') {
			$qid = intval($this->Get['qid']);
			if (!$this->QunLogic->is_qun_member($qid, MEMBER_ID)) {
								exit;
			}
			$qun_info = $this->QunLogic->get_qun_info($qid);
			if (!empty($qun_info)) {
				$qun_info['icon'] = $this->QunLogic->qun_avatar($qun_info['qid'], 's');
			} else {
								exit;
			}
			include template('qun/widgets_simple_desc');
		} else if ($op == 'my_qun') {
						$uid = intval($this->Post['uid']);
			$where_sql = '';
			$limit_sql = '';
			$order_sql = '';
			if (!empty($uid)) {
				$where_sql = ' AND q.gview_perm=0 ';
								$limit_sql = ' LIMIT 12';
				$order_sql = ' ORDER BY q.topic_num DESC,q.member_num DESC';
			} else {
				if (MEMBER_ID < 1) {
					js_alert_output("请先登录或者注册一个帐号");	
				}
				$uid = MEMBER_ID;
			}
			$type = intval($this->Get['type']);
			$query = DB::query("SELECT q.qid,q.name 
								FROM ".DB::table('qun_user')." AS qu 
								LEFT JOIN ".DB::table('qun')." AS q 
								USING(qid) 
								WHERE qu.uid='{$uid}' {$where_sql} 
								{$order_sql} 
								{$limit_sql}");
			$myquns = array();
			while ($value = DB::fetch($query)) {
				$value['icon'] = $this->QunLogic->qun_avatar($value['qid'], 's');
				$myquns[] = $value; 	
			}
			
			if (!empty($uid)) {
				include template('qun/widgets_my_qun');
			} else {
				include template('qun/widgets_my_qun');
			}
		}
	}
}
?>
