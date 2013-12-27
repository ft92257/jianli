<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename admincp.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 899779300 137496632 1930 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}


function get_sub_menu() {
	if(defined('IN_GET_SUB_MENU_FUNC')){
		return array();
	}
	define('IN_GET_SUB_MENU_FUNC', true);

	if (!@include_once(ROOT_PATH."./setting/admin_page_menu.php")) {
		return false;
	}

	$mod = ($_POST['mod'] ? $_POST['mod'] : $_GET['mod']);
	$code = ($_POST['code'] ? $_POST['code'] : $_GET['code']);
	$admin_link = 'admin.php';
	if($mod) {
		$admin_link .= '?mod='.$mod;
		if($code) {
			$admin_link .= '&code='.$code;
		}
	}

	foreach($menu_list as $menus) {
		foreach($menus as $k=>$v) {
			if(false !== strpos($v['link'], $admin_link)) {
				$v['current'] = 1;
				$menus[$k] = $v;

				return $menus;
			}
		}
	}

	return false;
}

function admin_check_allow($uid, $is_role_id=0) {
	global $_J;

		if(MEMBER_ID < 1) {
		return false;
	}

		if(true === JISHIGOU_FOUNDER) {
		return true;
	}

	$uid = (is_numeric($uid) ? $uid : 0);
	if($uid > 0) {
		if(!$is_role_id) {
						if($uid == MEMBER_ID) {
				return true;
			}
						if(true === jsg_member_is_founder($uid)) {
				return false;
			}
			$info = jsg_member_info($uid);
			$role_id = $info['role_id'];
		} else {
			$role_id = $uid;
		}
				if('normal' == $_J['member']['role_type']) {
			return false;
		}
				if($role_id == $_J['member']['role_id']) {
			return false;
		}
				$role_info = jsg_role_info($role_id);
		if('admin'==$role_info['type']) {
			return false;
		}
	}

	return true;
}

?>
