<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename uc_client.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-24 16:33:35 1038501846 1789216099 10583 $
 *******************************************************************/

  
define('UC_CLIENT_ROOT', dirname(__FILE__) . '/');
define('UC_CLIENT_VERSION', '0.1.0');
define('UC_CLIENT_API', '20090609');


function uc_user_login($username, $password, $logintype = 0, $checkques = 0, $question = '', $answer = '') {
	return uc_data_request('user', 'login', array($username, $password, $logintype, $checkques, $question, $answer));
}


function uc_user_synlogout() {
	return uc_data_request('user', 'synlogout');
}


function uc_user_register($username, $password, $email) {
	$args = func_get_args();
	return uc_data_request('user', 'register', $args);
}

function uc_user_get($username, $bytype = 0) {
	return uc_data_request('user', 'get', array($username, $bytype));
}


function uc_user_check($uid, $checkstr) {
	$args = func_get_args();
	return uc_data_request('user', 'check', $args);
}

function uc_check_email($email) {
	$args = func_get_args();
		return uc_data_request('user', 'checkEmail', $args);
}

function uc_check_maxuid(){
	return uc_data_request('user', 'CheckMaxUid', $args);
}


function uc_check_username($username) {
	$args = func_get_args();
	return uc_data_request('user', 'checkName', $args);
}


function uc_user_edit($uid, $oldname, $pwd, $email) {
	return uc_data_request('user', 'edit', array($uid, $oldname, $pwd, $email));
}


function uc_user_delete($uids) {
	return uc_data_request('user', 'delete', array($uids));
}


function uc_friend_add($uid, $friendid, $descrip) {
    $args = func_get_args();
    return uc_data_request('friend', 'add', $args);
}


function uc_friend_delete($uid, $friendids) {
    $args = func_get_args();
    return uc_data_request('friend', 'delete', $args);
}


function uc_friend_totalnum($uid, $status = -1) {
    return uc_data_request('friend', 'totalnum', array($uid, $status));
}


function uc_friend_list($uid, $page, $perpage, $status) {
    $args = func_get_args();
    return uc_data_request('friend', 'get_list', $args);
}


function uc_friend_add_type($ftid, $uid, $friendid) {
    $args = func_get_args();
    return uc_data_request('friend', 'add_type', $args);
}


function uc_friend_type_create($uid, $name) {
    $args = func_get_args();
    return uc_data_request('friend_type', 'create', $args);
}


function uc_friend_type_delete($uid, $ftid) {
    $args = func_get_args();
    return uc_data_request('friend_type', 'delete', $args);
}




function uc_msg_send($fromuid, $username, $touid, $title, $content, $save_to_sebox) {
    $args = func_get_args();
    return uc_data_request('message', 'send', $args);
}


function uc_msg_delete_rebox($ids, $uid) {
    $args = func_get_args();
    return uc_data_request('message', 'delete_rebox', $args);
}


function uc_msg_delete_sebox($ids, $uid) {
    $args = func_get_args();
    return uc_data_request('message', 'delete_sebox', $args);
}


function uc_msg_count_rebox($uid) {
    $args = func_get_args();
    return uc_data_request('message', 'count_rebox', $args);
}


function uc_msg_count_sebox($uid) {
    $args = func_get_args();
    return uc_data_request('message', 'count_sebox', $args);
}


function uc_msg_rebox_list($uid, $page, $num_per_page) {
    $args = func_get_args();
    return uc_data_request('message', 'rebox_list', $args);
}


function uc_msg_sebox_list($uid, $page, $num_per_page) {
    $args = func_get_args();
    return uc_data_request('message', 'sebox_list', $args);
}


function uc_msg_get_rebox($id, $uid) {
    $args = func_get_args();
    return uc_data_request('message', 'get_rebox', $args);
}


function uc_msg_get_sebox($id, $uid) {
    $args = func_get_args();
    return uc_data_request('message', 'get_sebox', $args);
}


function uc_msg_send_public($title, $content) {
    $args = func_get_args();
    return uc_data_request('message', 'send_public', $args);
}


function uc_msg_delete_public($ids, $uid) {
    $args = func_get_args();
    return uc_data_request('message', 'delete_public', $args);
}


function uc_msg_public_list($uid, $gid, $regdate) {
    $args = func_get_args();
    return uc_data_request('message', 'public_list', $args);
}


function uc_msg_get_public($mid) {
    $args = func_get_args();
    return uc_data_request('message', 'get_public', $args);
}


function uc_credit_add($credit, $isAdd = true) {
	return uc_data_request('credit', 'add', array($credit, $isAdd));
}

function uc_credit_get($uid) {
	return uc_data_request('credit', 'get', array($uid));
}

function uc_data_request($class,$method,$args = array()) {
	static $uc = null;
	if (empty($uc)) {
		require_once UC_CLIENT_ROOT . 'class_core.php';
		$uc = new UC();
	}
	$class = $uc->control($class);

	if (method_exists($class, $method)) {
		return call_user_func_array(array(&$class, $method), $args);
	} else {
		return 'error';
	}
}
function uc_avatar() {
	$uc_avatarflash = UC_API.'/js/face.swf';
	$save_param = rawurlencode(UC_API.'/job.php?action=uploadicon&step=2&');
	$icon_encode_url = 'saveFace='.$save_param.'&url=images/facebg.jpg&imgsize=20480&';
	return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase=" http:		<param name="movie" value="'.$uc_avatarflash.'" />
		<param name="FlashVars" value="'.$icon_encode_url.'" />
		<embed  width="560" height="450" src="'.$uc_avatarflash.'" FlashVars="'.$icon_encode_url.'" pluginspage="http:	</object>';
}
?>