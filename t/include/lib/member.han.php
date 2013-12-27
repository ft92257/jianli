<?php
/**
 *
 * 底层权限、用户操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member.han.php 1377 2012-08-16 09:37:06Z wuliyong $
 */


if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class MemberHandler {
	var $ID = 0;	var $sid = '';
	var $SessionExists = false;
	var $MemberPassword = '';
	var $MemberFields = array();
	var $ActionList = array();
	var $CurrentAction = array();	var $_Error = array();

	function MemberHandler() {
		$this->setSessionId();
	}
	function setSessionId($sid=null) {
		if(!is_null($sid)) {
			$this->sid = $sid;
			jsg_setcookie('sid', $sid, 86400000);
		} else {
			$this->sid = get_param('sid') ? get_param('sid') : jsg_getcookie('sid');
		}
	}
	function FetchMember($id, $pass) {
		$this->ID   = max(0, (int) $id);
		$this->MemberPassword = trim($pass);
		$this->GetMember();
		if($this->MemberFields) {
			define("MEMBER_ID",(int) $this->MemberFields['uid']);
			define("MEMBER_UCUID",(int) $this->MemberFields['ucuid']);
			define("MEMBER_NAME",$this->MemberFields['username']);
			define("MEMBER_NICKNAME",$this->MemberFields['nickname']);
			define("MEMBER_ROLE_TYPE",$this->MemberFields['role_type']);
			define("MEMBER_STYLE_THREE_TOL", (int) (1 == $this->MemberFields['style_three_tol'] ? 1 :
			(-1 == $this->MemberFields['style_three_tol'] ? 0 : $GLOBALS['_J']['config']['style_three_tol'])));
				
			define('JISHIGOU_FOUNDER', jsg_member_is_founder(MEMBER_ID));
		}

		return $this->MemberFields;
	}

	function UpdateSessions() {
		if (jsg_getcookie('sid')=='' || $this->sid!=jsg_getcookie('sid')) {
			$this->setSessionId($this->sid);
		}

				$timestamp = TIMESTAMP;
		$member = $this->MemberFields;
		$member['slastactivity'] = $timestamp;
		$member['action'] = (int) $this->CurrentAction['id'];
		if($this->SessionExists) {
			if(($member['action']>0 && $member['action'] != $this->MemberFields['action']) || ($timestamp - $this->MemberFields['slastactivity'] > 300)) {
				DB::query("UPDATE ".DB::table('sessions')." SET `action`='{$member['action']}', `slastactivity`='{$member['slastactivity']}' WHERE `sid`='{$this->sid}'");
			}
		} else {
			global $_J;

			$uid = MEMBER_ID;
			$onlinehold		= 1800;			$ip = $_J['client_ip'];
			$ips = explode('.',$ip);
			$sql="DELETE FROM ".TABLE_PREFIX.'sessions'."
			WHERE
				sid='{$this->sid}'
				OR slastactivity<($timestamp-$onlinehold)
				OR 	('".$uid."'<>'0' AND uid='".$uid."')
				OR 	(uid='0' AND ip1='$ips[0]' AND ip2='$ips[1]' AND ip3='$ips[2]' AND ip4='$ips[3]' AND slastactivity>$timestamp-60)";
			DB::query($sql, 'SILENT');

			DB::query("REPLACE INTO ".DB::table('sessions')." 
				SET `sid`='{$this->sid}', `ip1`='{$ips[0]}', `ip2`='{$ips[1]}', `ip3`='{$ips[2]}', `ip4`='{$ips[3]}', 
				`uid`='{$member['uid']}', `action`='{$member['action']}', `slastactivity`='{$member['slastactivity']}'", 
			'SILENT');

						if($uid && ($ip != $this->MemberFields['lastip'] || ($timestamp - $this->MemberFields['lastactivity'] > $onlinehold))) {
				$sql="
				UPDATE
					".TABLE_PREFIX.'members'."
				SET
					lastip='$ip',
					lastactivity='$timestamp'
				WHERE
					uid='".$uid."'";
				DB::query($sql, 'SILENT');
			}
		}
	}

	
	function HasPermission($mod, $code, $is_admin=0, $uid=0) {
		$MemberFields = array();
		if($uid) {
			if(is_array($uid)) {
				$MemberFields = $uid;
			} elseif(($uid = max(0, (int) $uid)) > 0) {
				$MemberFields = jsg_member_info($uid);
			}
			if($MemberFields && (false !== ($_role_info = cache_file('get', "role/role_{$MemberFields['role_id']}"))) && is_array($_role_info) && count($_role_info)) {
				$MemberFields = array_merge($MemberFields, $_role_info);
			}
		}
		if(!$MemberFields || $MemberFields['uid'] < 1) {
			$MemberFields = $this->MemberFields;
		}

		$mod = trim($mod);
		$action = trim($code);
		$role_id = (int) $MemberFields['role_id'];
		$role_name = $MemberFields['role_name'];
		$role_privilege = $MemberFields['role_privilege'];

		if($role_id < 1 && true !== JISHIGOU_FOUNDER) {
			$this->_SetError("角色编号不能为空,或者该编号在服务器上已经删除");
			cache_clear();
			return false;
		}

		$is_admin = ($is_admin ? 1 : 0);
		if(!($this->ActionList[$mod])) {
			$cache_id = "role_action/{$mod}-{$is_admin}";
			if(false === ($cache_data = cache_file('get', $cache_id))) {
				$sql="
				SELECT
					*
				FROM
					".TABLE_PREFIX.'role_action'."
				WHERE
					`module`='$mod' AND `is_admin`='$is_admin'
				ORDER BY
					`module`, `action`";
				$query = DB::query($sql);
				$action_list=array();
				while(false != ($row=DB::fetch($query))) {
					$action_id = $row['id'];
					unset($row['id'], $row['module'], $row['is_admin']);
					if(!$row['describe']) unset($row['describe']);
					if(!$row['message']) unset($row['message']);
					if(!$row['allow_all']) unset($row['allow_all']);
					if(!$row['credit_require']) unset($row['credit_require']);
					if(!$row['credit_update']) unset($row['credit_update']);
					if(!$row['log']) unset($row['log']);
					if(strpos($row['action'],'|')!==false) {
						$act_list=explode('|',$row['action']);
						foreach($act_list as $_action) {
							$action_list[(string)$_action]=$action_id;
						}
					} else {
						$action_list[(string)$row['action']]=$action_id;
					}
					unset($row['action']);
					$ActionList[$action_id]=$row;
				}
				cache_file('set', $cache_id, array($action_list,$ActionList));
			} else {
				list($action_list,$ActionList)=$cache_data;
			}

			$this->ActionList[$mod]=array('index'=>$action_list,'info'=>$ActionList);
		}


		if((($current_action_id=$this->ActionList[$mod]['index'][$action])!==null) || (($current_action_id=$this->ActionList[$mod]['index']["*"])!==null)) {
			$current_action = $this->ActionList[$mod]['info'][$current_action_id];
			$current_action['id'] = $current_action_id;
			$current_action['mod'] = $mod;
			$this->_SetCurrentAction($current_action);

			if(true === JISHIGOU_FOUNDER) {
				return true;
			}
			if($current_action['allow_all']==1) {
				return true;
			}
			if($current_action['allow_all']=='-1') {
				$this->_SetError("系统已经禁止<B>{$current_action['name']}</B>的任何操作");
				return false;
			}
						if($MemberFields['role_privilege']=="*") {
				return true;
			}
						if(false===jsg_find($role_privilege, $current_action_id, ',')) {
				if($ActionList[$current_action_id]['message']) {
					$message = $ActionList[$current_action_id]['message'];
				} else {
					$message = "您的角色({$role_name})没有{$current_action['name']}权限；<br />如果您是(待验证会员)，请先通过<a href='index.php?mod=settings&code=base#modify_email_area'>邮件验证<a>或者<a href='index.php?mod=other&code=contact'>联系我们</a>";
				}
				$this->_SetError($message);
				return false;
			}
		} else { 						$unrs = array("login_logout", "login_dologin", "role_admin", "role_do_modify_by_admin", );
			if(true===DEBUG && true===JISHIGOU_FOUNDER && $is_admin && $mod && $action && !is_numeric($action) && !in_array("{$mod}_{$action}", $unrs)) {
				$row = DB::fetch_first("select * from ".TABLE_PREFIX."role_action where `module`=\"$mod\" and `action` like \"%{$action}%\" and `is_admin`=\"$is_admin\"");
				if(!$row) {
					DB::query("insert into ".TABLE_PREFIX."role_action (`name`,`module`,`action`,`is_admin`) values (\"{$_SERVER["REQUEST_METHOD"]}_{$mod}_{$action}\",\"$mod\",\"$action\",\"$is_admin\")");
					$role_action_id = DB::insert_id();

					if(!(DB::fetch_first("select * from ".TABLE_PREFIX."role_module where `module`=\"$mod\""))) {
						DB::query("insert into ".TABLE_PREFIX."role_module (`module`,`name`) values (\"$mod\",\"$mod\")");
					}

					$row = DB::fetch_first("select * from ".TABLE_PREFIX."role where `id`=2");
					DB::query("update ".TABLE_PREFIX."role set `privilege`=\"".$this->_iddstrs($row,$role_action_id)."\" where `id`={$row[id]}");
					if(!$is_admin) {
						$row = DB::fetch_first("select * from ".TABLE_PREFIX."role where `id`=3");
						DB::query("update ".TABLE_PREFIX."role set `privilege`=\"".$this->_iddstrs($row,$role_action_id)."\" where `id`={$row[id]}");
					}

					cache_clear();
				}
			}

			if(!$GLOBALS['_J']['config']['safe_mode']) {
				return true; 			}
			if(!$is_admin) {
				return true; 			}
			if('POST' != $_SERVER['REQUEST_METHOD']) {
				return true; 			}
			if(!$GLOBALS['_J']['config']['jishigou_founder']) {
				return true; 			}

			$error = "操作模块:{$mod}<br>操作指令:{$action}<br><br>";
			$error.= "由于此操作在系统中没有权限控制,您暂时无法执行该操作,请联系网站的超级管理员。";
			$this->_SetError($error);
				
			return false;
		}

		return true;
	}
	function _iddstrs($row,$id=0) {
		$_ids = explode(",", $row["privilege"]);
		$ids = array();
		foreach($_ids as $_id) {
			$_id = (is_numeric($_id) ? $_id : 0);
			if($_id > 0) {
				$ids[$_id] = $_id;
			}
		}
		$id = (is_numeric($id) ? $id : 0);
		if($id > 0) {
			$ids[$id] = $id;
		}
		sort($ids);

		return implode(",",$ids);
	}
	function _SetCurrentAction($action) {
		$this->CurrentAction=$action;
	}
	function GetMemberFields() {
		return $this->MemberFields;
	}
	function GetMember() {
		global $_J;

		$this->MemberFields = array();
		if($this->sid) {
			if($this->ID) {
				$sql = "SELECT * FROM ".DB::table("members")." `M` LEFT JOIN ".DB::table("memberfields")." `MF` ON MF.uid=M.uid
						LEFT JOIN ".DB::table("sessions")." `S` ON S.uid=M.uid 
					WHERE M.uid='{$this->ID}' AND M.password='{$this->MemberPassword}' AND S.sid='{$this->sid}' AND 
						CONCAT_WS('.', S.ip1, S.ip2, S.ip3, S.ip4)='{$_J['client_ip']}'";
			} else {
				$sql = "SELECT * FROM ".DB::table("sessions")." WHERE sid='{$this->sid}' AND CONCAT_WS('.', ip1, ip2, ip3, ip4)='{$_J['client_ip']}'";
			}
						$query = DB::query($sql, 'SILENT');
			if(false===$query) {
				DB::query("REPLACE INTO ".TABLE_PREFIX."memberfields (`uid`) SELECT M.uid FROM ".TABLE_PREFIX."members M LEFT JOIN ".TABLE_PREFIX."memberfields MF ON MF.uid = M.uid WHERE MF.uid IS NULL");
				DB::query("DROP TABLE `".TABLE_PREFIX."sessions`");
				DB::query("CREATE TABLE `".TABLE_PREFIX."sessions` (
  `sid` char(6) NOT NULL DEFAULT '',
  `ip1` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip2` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip3` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip4` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `action` smallint(4) unsigned NOT NULL DEFAULT '0',
  `slastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `sid` (`sid`),
  KEY `uid` (`uid`)
) ENGINE=MEMORY");
				
				$query = DB::query($sql);
			}
			$this->MemberFields = DB::fetch($query);

			if($this->MemberFields && !$this->ID && $this->MemberFields['uid'] > 0) {
				$row = DB::fetch_first("SELECT * FROM ".DB::table("members")." `M` LEFT JOIN ".DB::table("memberfields")." `MF` ON MF.uid=M.uid WHERE M.uid='{$this->MemberFields['uid']}'");
				if($row) {
					$this->MemberFields = array_merge($row, $this->MemberFields);
				}
			}
		}
		$this->SessionExists = (($this->MemberFields && $this->MemberFields['uid']==$this->ID) ? true : false);


		if(!$this->SessionExists) {
			jsg_setcookie('sid', '', -86400000);

			if($this->ID) {
				$sql = "SELECT * FROM ".DB::table("members")." `M` LEFT JOIN ".DB::table("memberfields")." `MF` ON MF.uid=M.uid
					WHERE M.uid='{$this->ID}' AND M.password='{$this->MemberPassword}'";
				$this->MemberFields = DB::fetch_first($sql);
				if(!$this->MemberFields) {
					jsg_setcookie('auth', '', -86400000);
				}
			} else {
				jsg_setcookie('auth', '', -86400000);
			}

			$this->sid = $this->MemberFields['sid'] = random(6);
		}


		$this->MemberFields['role_id'] = (int) $this->MemberFields['role_id'];
		if($this->MemberFields['role_id'] < 1) {
			$this->MemberFields = array_merge($this->MemberFields, $this->_getGuestRole());
		} else {
			$cache_id = "role/role_".$this->MemberFields['role_id'];
			if(false === ($role = cache_file('get', $cache_id))) {
				$sql="SELECT
					`id` role_id,
					`name` role_name,
					`type` role_type,
					`creditshigher` role_creditshigher,
					`creditslower` role_creditslower,
					`privilege` role_privilege
				FROM
					".TABLE_PREFIX.'role'."
				WHERE `id`='{$this->MemberFields['role_id']}'";
				$role = DB::fetch_first($sql);

				cache_file('set', $cache_id, $role);
			}

			if(is_array($role) && count($role)) {
				$this->MemberFields = array_merge($this->MemberFields, $role);
			}
		}

		if($this->MemberFields['uid'] > 0) {
			$this->MemberFields = jsg_member_make($this->MemberFields);
		}

		$_J['uid'] = $this->MemberFields['uid'];
		$_J['username'] = $this->MemberFields['username'];
		$_J['nickname'] = $this->MemberFields['nickname'];
		$_J['role_id'] = $this->MemberFields['role_id'];

		$_J['member'] = & $this->MemberFields;
	}

	function _getGuestRole() {
		$cache_id = 'role/role_guest_1';
		if(false === ($fields = cache_file('get', $cache_id))) {
			$sql="SELECT
				`id` role_id,
				`name` role_name,
				`type` role_type,
				`creditshigher` role_creditshigher,
				`creditslower` role_creditslower,
				`privilege` role_privilege
	        FROM
				".TABLE_PREFIX.'role'."
	        WHERE `id` = '1'";
			$fields = DB::fetch_first($sql);

			$fields['role_id'] = 1;
			$fields['uid'] = 0;
			$fields['username'] = "guest";
			$fields['nickname'] = "游客";

			cache_file('set', $cache_id, $fields);
		}
		return $fields;
	}
	function _SetError($error)
	{
		$this->_Error[]=$error;
	}
	function GetError()
	{
		return $this->_Error;
	}
}
?>