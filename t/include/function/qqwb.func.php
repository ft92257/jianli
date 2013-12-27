<?php
/**
 * 文件名：qqwb.func.php
 * 版本号：1.0
 * 最后修改时间：2010年3月3日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 腾讯微博接口函数
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


function qqwb_enable($sys_config = array())
{
	if(!$sys_config)
	{
		$sys_config = ConfigHandler::get();
	}

	if(!$sys_config['qqwb_enable'])
	{
		return false;
	}

	if(!$sys_config['qqwb'])
	{
		$sys_config['qqwb'] = ConfigHandler::get('qqwb');
	}

	return $sys_config;
}


function qqwb_login($ico='s')
{
	$return = '';

	if (($sys_config = qqwb_enable()) && $sys_config['qqwb']['is_account_binding'])
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/qqwb/login16.png',
			'm' => $sys_config['site_url'] . '/images/qqwb/login24.gif',
			'b' => $sys_config['site_url'] . '/images/qqwb/login.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];

		$return = '<a class="qqweiboLogin" href="#" onclick="window.location.href=\''.$sys_config['site_url'].'/index.php?mod=qqwb&code=login\';return false;"><img src="'.$img_src.'" /><div class="tlb_qq">使用腾讯微博帐号登录</div></a>';
	}

	return $return;
}

function qqwb_bind($uid=0)
{
	$bind_info = qqwb_bind_info($uid);

	return ($bind_info && $bind_info['qqwb_username'] && $bind_info['token'] && $bind_info['tsecret']);
}
function qqwb_has_bind($uid=0)
{
	return qqwb_bind($uid);
}
function qqwb_synctoqq($uid=0)
{
	$return = true;

	$row = (is_array($uid) ? $uid : qqwb_bind_info((int) $uid));

	if($row)
	{
		$return = $row['synctoqq'];
	}

	return $return;
}

function qqwb_bind_setting($uid=0)
{
	$return = true;

	$row = (is_array($uid) ? $uid : qqwb_bind_info((int) $uid));

	if($row['profile'] && false!==strpos($row['profile'],'bind_setting') && preg_match('~[\"\']bind_setting[\"\']\s*\:\s*0~',$row['profile']))
	{
		$return = false;
	}

	return $return;
}
function qqwb_synctopic_tojishigou($uid=0)
{
	$return = false;

	$row = (is_array($uid) ? $uid : qqwb_bind_info((int) $uid));

	if($row['profile'] && false!==strpos($row['profile'],'synctopic_tojishigou') && preg_match('~[\"\']synctopic_tojishigou[\"\']\s*\:\s*1~',$row['profile']))
	{
		$return = true;
	}

	return $return;
}

function qqwb_bind_info($uid=0) {
	$ret = array();
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	if($uid > 0) {
		if(false===($ret=Load::model('misc')->account_bind_info($uid, 'qqwb'))) {
			$ret = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_info where `uid`='{$uid}'");
			 
			Load::model('misc')->update_account_bind_info($uid, 'qqwb', $ret);
		}
	}
	if(false===$ret[0]) {
		return array();
	} else {
		return $ret;
	}
}



function qqwb_bind_icon($uid=0)
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = qqwb_enable()))
	{

		$return = "<img src='{$sys_config['site_url']}/images/qqwb/qqwb_off.gif' alt='未绑定腾讯微博' />";

		if (qqwb_bind($uid))
		{
			$return = "<img src='{$sys_config['site_url']}/images/qqwb/qqwb_on.gif' alt='已经绑定腾讯微博' />";

			if($sys_config['qqwb']['is_synctopic_tojishigou'] && qqwb_synctopic_tojishigou($uid))
			{
				$return .= "<img src='{$sys_config['site_url']}/index.php?mod=qqwb&code=synctopic&uid={$uid}' width='0' height='0' style='display:none' />";
			}
		}

		if (MEMBER_ID>0)
		{
			$return = "<a href='#' title='腾讯微博绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=qqwb';return false;\">{$return}</a>";
		}
	}

	return $return;
}


function qqwb_syn()
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = qqwb_enable()) && (ConfigHandler::get('qqwb','is_synctopic_toweibo')))
	{
		$row = qqwb_bind_info($uid);

		$a = $b = $c = $d = $e = '';
		if ($row && $row['qqwb_username'])
		{
			$b = "{$sys_config['site_url']}/images/qqwb/icon_on.gif";
				
			$d = "checked='checked'";
			if (!($row['synctoqq']))
			{
				$d = "";
			}
			$e = "<label for='syn_to_qqwb'><i></i><img src='{$b}' title='同步发到腾讯微博'/></label>";
		}
		else
		{
						$b = "{$sys_config['site_url']}/images/qqwb/icon_off.gif";
			$c = "disabled='disabled'";
			$e = "<a href='{$sys_config['site_url']}/index.php?mod=account&code=qqwb' title='开通此功能（将打开新窗口）'><i></i><img src='{$b}' title='同步发到腾讯微博'/></a>";
		}

		$return = "{$a}{$e}<input type='checkbox' id='syn_to_qqwb' name='syn_to_qqwb' value='1' {$c} {$d} />";
	}

	return $return;
}

function qqwb_sync_face($uid, $face='') {
	$uid = max(0, (int) $uid);
	if($uid < 1) return 0;
	$user_info = jsg_member_info($uid);
	if(!$user_info || $user_info['__face__']) {
		return 0;
	}
	 
	$face = trim(strip_tags($face));
	if(false === strpos($face, ':/'.'/')) {
		return 0;
	}
	 
	$image_path = RELATIVE_ROOT_PATH . 'images/face/' . face_path($uid);
	$image_file_big = $image_path . $uid . "_b.jpg";

	if(!file_exists($image_file_big)) {
		if (!is_dir($image_path)) {
			Load::lib('io', 1)->MakeDir($image_path);
		}

		$temp_image = dfopen($face, 99999999, '', '', true, 3, $_SERVER['HTTP_USER_AGENT']);
		if(!$temp_image) {
			return 0;
		}
		Load::lib('io', 1)->WriteFile($image_file_big, $temp_image);
			
		if(is_image($image_file_big)) {
			$image_file_small = $image_path . $uid . '_s.jpg';
			$make_result = makethumb($image_file_big, $image_file_small, 50, 50);
			if(!is_image($image_file_small)) {
				return 0;
			}

						$face_url = '';
			if($GLOBALS['_J']['config']['ftp_on']) {
				$face_url = ConfigHandler::get('ftp','attachurl');

				$ftp_result = ftpcmd('upload',$image_file_big);
				if($ftp_result > 0) {
					ftpcmd('upload',$image_file_small);

					Load::lib('io', 1)->DeleteFile($image_file_big);
					Load::lib('io', 1)->DeleteFile($image_file_small);
				}
			}

			
			$sql = "update `".TABLE_PREFIX."members` set `face_url`='{$face_url}', `face`='{$image_file_small}' where `uid`='$uid'";
			DB::query($sql);

			
			if($GLOBALS['_J']['config']['extcredits_enable'] && $uid > 0) {
				
				update_credits_by_action('face', $uid);
			}
		}
	}

	return 0;
}


?>