<?php
/**
 *
 * 用户注册登录函数
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member.func.php 1385 2012-08-17 08:02:39Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}



function jsg_member_register($nickname, $password, $email, $username = '', $ucuid = 0, $role_id = 0) {
	return Load::model('passport')->register($nickname, $password, $email, $username, $ucuid, $role_id);
}
function jsg_member_register_check_invite($invite_code='', $reset=0) {
	return Load::model('passport')->register_check_invite($invite_code, $reset);
}
function jsg_member_register_by_invite($invite_uid, $uid=MEMBER_ID, $check_result=array()) {
	return Load::model('passport')->register_by_invite($invite_uid, $uid, $check_result);
}
function jsg_member_register_check_status() {
	$rets = array();

	if($GLOBALS['_J']['config']['regstatus']) {
		foreach($GLOBALS['_J']['config']['regstatus'] as $v) {
			$rets["{$v}_enable"] = 1;
		}
	}

		if(!$rets && true!==JISHIGOU_FORCED_REGISTER) {	
		$msg = '本站暂时关闭了普通注册功能 ';
		$msg .= jsg_member_third_party_reg_msg();		
		
		$rets['error'] = ($GLOBALS['_J']['config']['regclosemessage'] ? $GLOBALS['_J']['config']['regclosemessage'] : $msg);
	}

	return $rets;
}

function jsg_member_third_party_reg_msg() {
	$msg = '';
	if($GLOBALS['_J']['config']['third_party_regstatus']) {
		$msg .= ' 或者您可以通过以下的第三方帐号进行：<br /><br />';
		if(in_array('sina', $GLOBALS['_J']['config']['third_party_regstatus']) && sina_weibo_init()) {
			$msg .= sina_weibo_login('b') . '<br /><br />';
		}
		if(in_array('qqwb', $GLOBALS['_J']['config']['third_party_regstatus']) && qqwb_init()) {
			$msg .= qqwb_login('b') . '<br /><br />';
		}
	}
	return $msg;
}


function jsg_member_login($nickname, $password, $is = '') {
	return Load::model('passport')->login($nickname, $password, $is);	
}


function jsg_member_login_check($nickname, $password, $is = '', $checkip = 1) {
	return Load::model('passport')->login_check($nickname, $password, $is, $checkip);	
}


function jsg_member_login_set_status($member) {
	return Load::model('passport')->login_set_status($member);
}

function jsg_member_logout() {
	return Load::model('passport')->logout();
}


function jsg_member_login_extract() {
	return Load::model('passport')->login_extract();
}


function jsg_member_checkname($username, $is_nickname = 0, $ucuid = 0, $check_exists = 1) {
	return Load::model('passport')->checkname($username, $is_nickname, $ucuid, $check_exists);
}



function jsg_member_checkemail($email, $ucuid = 0) {
	return Load::model('passport')->checkemail($email, $ucuid);
}


function jsg_member_delete($ids) {
	return Load::model('passport')->delete($ids);
}


function jsg_member_edit($oldnickname, $oldpw='', $nickname='', $password='', $email='', $username='', $ignoreoldpw=0) {
	return Load::model('passport')->edit($oldnickname, $oldpw, $nickname, $password, $email, $username, $ignoreoldpw);
}


function jsg_get_member($nickname, $is = '', $cache=0) {
	$fields = '`uid`,`nickname`,`username`,`password`,`email`,`ucuid`';

	$ret = jsg_member_info($nickname, $is, $fields, $cache);

	return $ret;
}


function jsg_member_info($uid, $is='uid', $fields='*', $cache=1)
{
	if(!$uid) {
		return array();
	}

	$iss = array('uid'=>1, 'username'=>1, 'nickname'=>1, 'email'=>1, 'phone'=>1, );
	if(!isset($iss[$is])) {
		$uid = jsg_member_nickname($uid, $cache);
		if($uid) {
			$is = 'nickname';
		} else {
			return array();
		}
	}

	$p = array(
		'fields' => $fields,
		$is => $uid,
		'count' => 1,
	);	
	$rets = jsg_member_get($p, 1, $cache);	
	
	return $rets['list'][0];
}


function jsg_member_nickname($nickname, $cache=1) {
	$nickname = trim($nickname);
	if(!$nickname) {
		return '';
	}

	$member_info = array();
		if(is_numeric($nickname)) {
		if($GLOBALS['_J']['config']['sms_enable'] && jsg_is_mobile($nickname)) {
			$member_info = jsg_member_info($nickname, 'phone', '*', $cache);
		} else {
			$member_info = jsg_member_info($nickname, 'uid', '*', $cache);
		}
	} else {
				if(false !== strpos($nickname, '@')) {
			$member_info = jsg_member_info($nickname, 'email', '*', $cache);
		}
	}
	if(!$member_info) {
		$member_info = jsg_member_info($nickname, 'nickname', '*', $cache);
		if(!$member_info) {
			$member_info = jsg_member_info($nickname, 'username', '*', $cache);
		}
	}
	if(!$member_info) {
		return '';
	}

	$ret = $member_info['nickname'];

	return $ret;
}


function jsg_member_get($p, $mark=1, $cache=1) {
	if($cache && $p['uid'] && $p['uid']==MEMBER_ID && $GLOBALS['_J']['member']) {
		return array('list'=>array($GLOBALS['_J']['member']));
	}
	
	static $S_members = array();
	
	if($cache) {
		$cache_id = md5(serialize($p).$mark);
		if(isset($S_members[$cache_id])) {
			return $S_members[$cache_id];
		}
	}

	$wheres = array();
	$ws = array('uid'=>1, 'username'=>1, 'nickname'=>1, 'email'=>1, 'phone'=>1, 'province'=>1, 'city'=>1, 'role_id'=>1, 'ucuid'=>1, 'invite_uid'=>1, );
	foreach($p as $k=>$v) {
		if(isset($ws[$k])) {
			$vs = (array) $v;
			$wheres[$k] = " `$k` IN ('".implode("','", $vs)."') ";
		}
	}
	
	$sql_where = ($wheres ? " WHERE " . implode(" AND ", $wheres) : "");

	$count = max(0, (int) $p['count']);
	if($count < 1) {
		$count = DB::result_first("SELECT COUNT(*) AS `count` FROM ".DB::table('members')." {$sql_where} ");
	}

	$rets = array();
	if($count > 0) {
		$page = array();
		$sql_limit = '';
		if($p['per_page_num']) {
			$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array', 'extra'=>$p['page_extra']));

			$sql_limit = " {$page['limit']} ";
		} elseif($p['limit']) {
			if(false !== strpos(strtolower($p['limit']), 'limit ')) {
				$sql_limit = " {$p['limit']} ";
			} else {
				$sql_limit = " limit {$p['limit']} ";
			}
		} elseif ($p['count']) {
			$sql_limit = " LIMIT {$p['count']} ";
		}

		$sql_order = '';
		if($p['order']) {
			if(false !== strpos(strtolower($p['order']), 'order by ')) {
				$sql_order = " {$p['order']} ";
			} else {
				$sql_order = " order by {$p['order']} ";
			}
		}

		$sql_fields = ($p['fields'] ? $p['fields'] : "*");

		$query = DB::query("select $sql_fields from ".DB::table('members')." $sql_where $sql_order $sql_limit ");
		$list = array();
		while(false != ($r = DB::fetch($query))) {
			if($mark) {
				$r = jsg_member_make($r);
			}
			$list[] = $r;
		}
		DB::free_result($query);

		if($list) {
			if($mark) {
				$list = Load::model('buddy')->follow_html($list, 'uid', (true === IN_JISHIGOU_WAP ? 'wap_follow_html' : 'follow_html'));
			}
			$rets = array('count'=>$count, 'list'=>$list, 'page'=>$page);
		}
	}

	if($cache && $cache_id) {
		$S_members[$cache_id] = $rets;
	}

	return $rets;
}

function jsg_member_make($row) {	
	if (isset($row['uid'])) {
				$row['__face__'] = $row['face'];
				if (true !== UCENTER_FACE && !$row['face']) {
			$row['face'] = $row['face_small'] = $row['face_original'] = face_get();		} else {
			$row['face_small'] = $row['face'] = face_get($row);
			$row['face_original'] = face_get($row, 'middle');
		}		
		
				if($row['validate']){
			$validate_id = ($row['validate_category'] ? $row['validate_category'] : $row['validate']);
		}
		if($validate_id) {
						$validate_category = ConfigHandler::get('validate_category');
			if(!$validate_category){
				$query = DB::query("SELECT *
									FROM ".DB::table('validate_category')." 
									ORDER BY id ASC");
				while ($value = DB::fetch($query)) {
					$validate_category[$value['id']] = $value;
				}
				ConfigHandler::set('validate_category', $validate_category);
			}
			
			$category_pic = $validate_category[$validate_id]['category_pic'];
			if(!$category_pic){
				$validate_id = $validate_category[$validate_id]['category_id'];
				$category_pic = $validate_category[$validate_id]['category_pic'];
			}
	
			if(!isset($row['validate_remark']) || !isset($row['validate_true_name'])) {
				$memberfields = DB::fetch_first("select `uid`,`validate_remark`,`validate_true_name` from `" . TABLE_PREFIX .
					"memberfields` where `uid`='{$row['uid']}'");
				$row['validate_remark'] = $memberfields['validate_remark'];
				$row['validate_true_name'] = $memberfields['validate_true_name'];
			}
	
			$row['validate_user'] = $row['validate_true_name'];
			$row['vip_info'] = $row['validate_remark'];
			$row['vip_pic'] = $GLOBALS['_J']['config']['site_url'] . '/' . ($category_pic ? $category_pic : 'images/vip.gif');
	
			$row['validate_html'] = "<a href='index.php?mod=other&code=vip_intro' target='_blank'><img class='vipImg' title='{$row['vip_info']}' src='{$row['vip_pic']}' /></a>";
		}
	}

		if (isset($row['province']) || isset($row['city'])) {
		$row['from_area'] = "{$row['province']} {$row['city']}";
	}		

		if(isset($row['gender'])) {
		if($row['gender'] == 1) {
			$row['gender_ta'] = '他';
		} else {
			$row['gender_ta'] = '她';
		}
	}

	return $row;
}


function jsg_member_info_by_mod() {
	$ret = array();
	$mr = ($_POST['mod_original'] ? $_POST['mod_original'] : $_GET['mod_original']);

	if($mr) {
		$mr = getSafeCode($mr);
		$is = is_numeric($mr) ? 'uid' : 'username';

		$ret = jsg_member_info($mr, $is);
		if(!$ret) {
			$ret = jsg_member_info($mr, 'nickname');
		}
		
		
	}

	return $ret;
}

function jsg_info($id, $table='', $pri='id', $cache_time = '-1') {
	if(!$id || !$table || !$pri) return array();

	$id = max(0, (int) $id);
	if($id < 1) return array();

	$cache_id = "{$table}/{$table}_{$pri}_{$id}";
	if(!$cache_time || false === ($info = cache_file('get', $cache_id))) {
		$info = DB::fetch_first("SELECT * FROM ".DB::table($table)." WHERE `{$pri}`='$id'");

		if($cache_time) {
			cache_file('set', $cache_id, $info, $cache_time);
		}
	}

	return $info;
}

function jsg_update_count($id, $table='', $pri='id', $field='', $value='0', $is_unsigned=1) {
	if(!$id || !$table || !$pri || !$field) return 0;
	if($pri == $field) return 0;

	$value = is_numeric($value) ? $value : 0;
	if(!$value) return 0;

	$info = jsg_info($id, $table, $pri, 0);
	if(!$info) return 0;

	if(!isset($info[$field])) return 0;
	$value_old = is_numeric($info[$field]) ? $info[$field] : 0;

	$signed = substr((string) $value, 0, 1);
	$value_new = (in_array($signed, array('-', '+')) ? $value_old + $value : $value);
	if($is_unsigned && $value_new < 0) {
		$value_new = 0;
	}

	$ret = 0;
	if($value_new != $value_old) {
		$ret = DB::query("update ".DB::table($table)." set `{$field}`='{$value_new}' where  `{$pri}`='$id'");
	}

	return $ret;
}

function jsg_member_update_count($uid, $field='', $value='0', $is_unsigned=1) {
	return jsg_update_count($uid, 'members', 'uid', $field, $value, $is_unsigned);
}

function jsg_role_info($id) {
	$info = jsg_info($id, 'role');

	return $info;
}


function jsg_role_check_allow($action, $to_uid, $from_uid = MEMBER_ID) {
	$rets = array();

	$to_uid = is_numeric($to_uid) ? $to_uid : 0;
	$from_uid = is_numeric($from_uid) ? $from_uid : 0;
	if($to_uid < 1 || $from_uid < 1 || $to_uid == $from_uid) {
		return $rets;
	}

		if(MEMBER_ID == $from_uid && true === JISHIGOU_FOUNDER) {
		return $rets;
	}

	$actions = array('sendpm'=>'私信', 'topic_forward'=>'转发', 'topic_reply'=>'评论', 'topic_at'=>'@', 'follow'=>'关注', );
	$action_name = $actions[$action];
	if(is_null($action_name)) {
		return $rets;
	}

	$to_member = jsg_member_info($to_uid);
	$from_member = jsg_member_info($from_uid);

	if($to_member && $from_member) {
		$to_role_id = $to_member['role_id'];
		$from_role_id = $from_member['role_id'];

		$to_role = jsg_role_info($to_role_id);
		$from_role = jsg_role_info($from_role_id);

		if($to_role && $from_role) {
			$to_field = "allow_{$action}_to";
			$from_field = "allow_{$action}_from";

			$allow_action_to = $from_role[$to_field];
			if($allow_action_to) {
				if(-2 == $allow_action_to || !jsg_find($allow_action_to, $to_role_id)) {
					$rets['error'] = "由于用户组权限设置，您没有 $action_name TA的权限";

					return $rets;
				}
			}

			
		}
	}

	return $rets;
}

function jsg_find($haystack, $needle, $append=',') {
	$haystack = $append.$haystack.$append;
	$needle = $append.$needle.$append;

	if(false !== strpos($haystack, $needle)) {
		return true;
	} else {
		return false;
	}
}


function jsg_get_vip_uids($limit=300, $day=30) {
	$limit = (int) $limit;
	if($limit < 1) {
		$limit = 300;
	}
	$day = (int) $day;
	if($day < 1) {
		$day = 30;
	}

	$vip_uids = array();
		$cache_id = "topic/hot-vip-uids-{$day}-{$limit}";
	if(false === ($vip_uids = cache_file('get', $cache_id))) {
		$query = DB::query("select `uid` from ".DB::table('members')." where `lastactivity`>'".(time() - 86400 * $day)."' and `validate`='1' order by `lastactivity` desc limit {$limit} ");
		while (false != ($row = DB::fetch($query))) {
			$vip_uids[$row['uid']] = $row['uid'];
		}

		cache_file('set', $cache_id, $vip_uids, 600);
	}

	return $vip_uids;
}

function jsg_member_is_founder($uid) {
	global $_J;	
	
	$uid = (is_numeric($uid) ? $uid : 0);
	
	$ret = (boolean) ($uid>0 && $_J['config']['jishigou_founder'] && jsg_find($_J['config']['jishigou_founder'], $uid, ','));
	
	return $ret;	
}

function jsg_is_mobile($num) {
	$ret = false;
	if($num && is_numeric($num)) {
		settype($num,'string');
		$num_len = strlen($num);
		if(11==$num_len || 12==$num_len) {
			$ret = preg_match('~^((?:13|15|18)\d{9}|0(?:10|2\d|[3-9]\d{2})[1-9]\d{6,7})$~',$num);
		}
	}
	return $ret;
}

?>