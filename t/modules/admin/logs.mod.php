<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename logs.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 811046613 1216900804 4957 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config) {
		$this->MasterObject($config);

		$this->Execute();
	}

	function Execute() {
		ob_start();
		switch($this->Code) {
			

			case 'clean':
				$this->Clean();
			case 'view':
				$this->View();
				break;
			default:
				$this->Code = 'index';
				$this->DbLogList();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);
	}

	
	function DbLogList() {
		$per_page_num = 50;
		$page_link = 'admin.php?mod=logs';

		$wheres = array();

		$uid = get_param('uid');
		$nickname = get_param('nickname');
		if($nickname) {
			$info = jsg_member_info($nickname, 'nickname');
			if($info) {
				$uid = $info['uid'];
			}
		}
		if(isset($uid)) {
			$uid = max(0, (int) $uid);
			$wheres[] = " `uid`='$uid' ";
			$page_link .= "&uid=$uid";
		}
		$role_action_id = (int) get_param('role_action_id');
		if($role_action_id > 0) {
			$wheres[] = " `role_action_id`='$role_action_id' ";
			$page_link .= "&role_action_id=$role_action_id";
		}
		$s_mod = get_param('s_mod');
		if($s_mod) {
			$wheres[] = " `mod`='$s_mod' ";
			$page_link .= "&s_mod=$s_mod";
				
			$s_code = get_param('s_code');
			if($s_code) {
				$wheres[] = " `code`='$s_code' ";
				$page_link .= "&s_code=$s_code";
			}
		}
		$ip = get_param('ip');
		if($ip) {
			$wheres[] = " `ip`='$ip' ";
			$page_link .= "&ip=$ip";
		}
		$dateline_start = (int) get_param('dateline_start');
		if($dateline_start) {
			$dateline_s = strtotime($dateline_start);
			if($dateline_s) {
				$wheres[] = " `dateline`>='$dateline_s' ";
				$page_link .= "&dateline_start=$dateline_start";
			}
		}
		$dateline_end = (int) get_param('dateline_end');
		if($dateline_end) {
			$dateline_e = strtotime($dateline_end);
			if($dateline_e) {
				$wheres[] = " `dateline`<='".($dateline_e + 86400)."' ";
				$page_link .= "&dateline_end=$dateline_end";
			}
		}

		$sql_where = ($wheres ? (" WHERE " . implode(" AND ", $wheres)) : "");
		$count = DB::result_first("SELECT COUNT(1) AS `COUNT` FROM " . DB::table('log') . " $sql_where ");
		if($count > 0) {
			$page = page($count, $per_page_num, $page_link, array('return'=>'Array'));
				
			$sql_order = " ORDER BY `id` DESC ";
			$sql_limit = " {$page['limit']} ";
				
			$query = DB::query("SELECT * FROM " . DB::table('log') . " $sql_where $sql_order $sql_limit ");
			$list = array();
			while(false != ($row = DB::fetch($query))) {
				$list[] = $row;
			}
		}


		include template('admin/logs_index');
	}

	function View() {
		$id = (int) get_param('id');
		if($id < 1) {
			$this->Messager('请先指定一个要查看的LOG ID', null);
		}
		$log = DB::fetch_first("SELECT * FROM " . DB::table('log') . " WHERE `id`='$id' ");
		if(!$log) {
			$this->Messager("您要查看的LOG记录已经不存在了", null);
		}

		$info = DB::fetch_first("SELECT * FROM " . DB::table('log_data') . " WHERE `log_id`='$id' ");
		if(!$info) {
			$this->Messager("您要查看的LOG记录详情已经不存在了", null);
		}
		$log_data = unserialize(base64_decode($info['log_data']));
		

		$msg = "<div style='text-align: left;'>
			<button type='button' class='button' onclick='window.location=\"admin.php?mod=logs&code=view&id=".($id + 1)."\";return false;'>上一条</button> &nbsp;
			<button type='button' class='button' onclick='window.location=\"admin.php?mod=logs&code=view&id=".($id - 1)."\";return false;'>下一条</button> &nbsp; <br /><br />
			<font color='red'><b>LOG记录详情：</b></font><br />
			<b>详细地址：</b><a target='_blank' href='".($this->Config['site_url'] . str_replace(array('/'.'/', ), array('/', ), "/{$log['uri']}"))."'>{$log['uri']}</a><br /><br />
			<b>USER_AGENT: </b>{$info['user_agent']}<br />
			<b>_REQUEST: </b><pre>".var_export($log_data, true)."</pre><br /><br />
		<br /></div>";
		$this->Messager($msg, null);
	}

	function Clean() {
		$time = (TIMESTAMP - 86400 * 30 * 6);

		DB::query("DELETE FROM " . DB::table('log') . " WHERE `dateline`<'$time' ");
		DB::query("DELETE FROM " . DB::table('log_data') . " WHERE `dateline`<'$time' ");


		$this->Messager("清理成功");
	}

	function FileLogList(){
		$date = get_param('date');
		$yearmonth = $date ? $date : date('Ym',TIMESTAMP);
		$file = $yearmonth.'cplog';
		$log = array();
		$logdir = ROOT_PATH.'./data/log/';
		include($logdir.$file.'.php');

		$nickname = get_param('nickname');
		foreach ($log as $key=>$value) {
			if($nickname && $nickname != $value['nickname']){
				unset($log[$key]);
			}
		}

		include template('admin/logs_main');
	}
}
?>