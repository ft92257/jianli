<?php
/**
 * 文件名：sina.func.php
 * 版本号：1.0
 * 作     者：狐狸<foxis@qq.com>
 * 修改时间：2012年1月10日 15:07:35
 * 功能描述: 新浪微博接口函数
 * @version $Id: sina.func.php 1142 2012-07-04 07:30:38Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


function sina_enable($sys_config = array())
{
	return sina_weibo_enable($sys_config);
}
function sina_weibo_enable($sys_config = array())
{
	if(!$sys_config)
	{
		$sys_config = ConfigHandler::get();
	}

	if(!$sys_config['sina_enable'])
	{
		return false;
	}

	if(!$sys_config['sina'])
	{
		$sys_config['sina'] = ConfigHandler::get('sina');
	}

	return $sys_config;
}


function sina_weibo_login($ico='s')
{
	$return = '';

	if (($sys_config = sina_weibo_enable()) && $sys_config['sina']['is_account_binding'])
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/xwb/bgimg/loginHeader_16.png',
			'm' => $sys_config['site_url'] . '/images/xwb/bgimg/loginHeader_24.png',
			'b' => $sys_config['site_url'] . '/images/xwb/bgimg/sina_login_btn.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];

		$login_url = $sys_config['site_url'] . '/index.php?mod=xwb&' . ($sys_config['sina']['oauth2_enable'] ? 'code=login' : 'm=xwbAuth.login');
		$return = '<a class="sinaweiboLogin" href="#" onclick="window.location.href=\''.$login_url.'\';return false;"><img src="'.$img_src.' "/><div class="tlb_sina">使用新浪微博帐号登录</div></a>';
	}

	return $return;
}

function sina_weibo_bind($uid=0)
{
	$bind_info = sina_weibo_bind_info($uid);

	return ($bind_info && $bind_info['sina_uid'] > 0);
}
function sina_weibo_has_bind($uid=0)
{
	return sina_weibo_bind($uid);
}

function sina_weibo_bind_setting($uid=0)
{
	$ret = true;
	$row = (is_array($uid) ? $uid : sina_weibo_bind_info((int) $uid));
	if(isset($row['profiles']['bind_setting']) && !$row['profiles']['bind_setting']) {
		$ret = false;
	}
	return $ret;
}
function sina_weibo_synctopic_tojishigou($uid=0)
{
	$row = (is_array($uid) ? $uid : sina_weibo_bind_info((int) $uid));
	return $row['profiles']['synctopic_tojishigou'] ? true : false;
}
function sina_weibo_syncreply_tojishigou($uid=0)
{
	$row = (is_array($uid) ? $uid : sina_weibo_bind_info((int) $uid));
	return $row['profiles']['syncreply_tojishigou'] ? true : false;
}

function sina_weibo_bind_info($uid=0) {
	$ret = array();
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	if($uid > 0) {
		if(false===($ret=Load::model('misc')->account_bind_info($uid, 'xwb'))) {
			$ret = DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_info where `uid`='{$uid}'");
			if($ret['profile']) {
				$ret['profiles'] = json_decode($ret['profile'], true);
			}
			 
			Load::model('misc')->update_account_bind_info($uid, 'xwb', $ret);
		}
	}
	if(false===$ret[0]) {
		return array();
	} else {
		return $ret;
	}
}
function sina_weibo_bind_topic($tid)
{
	static $sXWB_bind_topics=null;

	$return = array();

	$tid = max(0,(int) $tid);

	if($tid > 0)
	{
		if(null===($return = $sXWB_bind_topics[$tid]))
		{
			$return = DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_topic where `tid`='{$tid}'");
				
			$sXWB_bind_topics[$tid] = $return;
		}
	}

	return $return;
}


function sina_weibo_bind_icon($uid=0)
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = sina_weibo_enable()))
	{

		$return = "<img src='{$sys_config['site_url']}/images/xwb/bgimg/sinawebo_off.gif' alt='未绑定新浪微博' />";

		if (sina_weibo_bind($uid))
		{
			$return = "<img src='{$sys_config['site_url']}/images/xwb/bgimg/sinawebo_on.gif' alt='已经绑定新浪微博' />";

			$MemberHandler = & Obj::registry('MemberHandler');

			if($sys_config['sina']['is_synctopic_tojishigou'] && sina_weibo_synctopic_tojishigou($uid))
			{
				$_read_now = true;

				if($sys_config['sina']['syncweibo_tojishigou_time'] > 0)
				{
					$xwb_bind_info = sina_weibo_bind_info($uid);

					if($xwb_bind_info['last_read_time'] + $sys_config['sina']['syncweibo_tojishigou_time'] > time())
					{
						$_read_now = false;
					}
				}

				if($_read_now && !($MemberHandler->HasPermission('xwb','__synctopic',0,$uid)))
				{
					$_read_now = false;
				}

				if($_read_now)
				{
					$return .= "<img src='{$sys_config['site_url']}/index.php?mod=xwb&code=synctopic&uid={$uid}' width='0' height='0' style='display:none' />";
				}
			}

			if($sys_config['sina']['is_syncreply_tojishigou'] && is_numeric($_GET['code']) && sina_weibo_syncreply_tojishigou($uid) && ($xwb_bind_topic = sina_weibo_bind_topic($_GET['code'])) && ($topic_info = DB::fetch_first("select * from ".TABLE_PREFIX."topic where `tid`='{$_GET['code']}'")))
			{
				$_read_now = true;

				if($sys_config['sina']['syncweibo_tojishigou_time'] > 0)
				{
					if($xwb_bind_topic['last_read_time'] + $sys_config['sina']['syncweibo_tojishigou_time'] > time())
					{
						$_read_now = false;
					}
				}

				if($_read_now && !($MemberHandler->HasPermission('xwb','__syncreply',0,$topic_info['uid'])))
				{
					$_read_now = false;
				}

				if($_read_now)
				{
					$return .= "<img src='{$sys_config['site_url']}/index.php?mod=xwb&code=syncreply&tid={$_GET['code']}' width='0' height='0' style='display:none' />";
				}
			}
		}

		if (MEMBER_ID>0)
		{
			$return = "<a href='#' title='新浪微博绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=sina';return false;\">{$return}</a>";
		}
	}

	return $return;
}


function sina_weibo_syn()
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = sina_weibo_enable()) && (ConfigHandler::get('sina','is_synctopic_toweibo')))
	{
		$row = sina_weibo_bind_info($uid);

		$a = $b = $c = $d = $e = '';
		if ($row && $row['sina_uid'])
		{
			$b = "{$sys_config['site_url']}/images/xwb/bgimg/icon_on.gif";
				
			$d = "checked='checked'";
			if (!sina_weibo_bind_setting($row))
			{
				$d = "";
			}
			$e = "<label for='syn_to_sina'><i></i><img src='{$b}' title='同步发到新浪微博'/></label>";
		}
		else
		{
						$b = "{$sys_config['site_url']}/images/xwb/bgimg/icon_off.gif";
			$c = "disabled='disabled'";
			$e = "<a href='{$sys_config['site_url']}/index.php?mod=account&code=sina' title='开通此功能（将打开新窗口）'><i></i><img src='{$b}' title='同步发到新浪微博'/></a>";
		}

		$return = "{$a}{$e}<input type='checkbox' id='syn_to_sina' name='syn_to_sina' value='1' {$c} {$d} />";
	}

	return $return;
}


function sina_weibo_share($tid='')
{
	$return = '';

	if(($sys_config = sina_weibo_enable()) && (ConfigHandler::get('sina','is_rebutton_display')))
	{
		$tid = max(0,(int) ($tid ? $tid : $GLOBALS['jsg_tid']));

		$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

		$link = "javascript:void((function(s,d,e,r,l,p,t,z,c) {var%20f='http:/"."/v.t.sina.com.cn/share/share.php?appkey={$sys_config[sina][app_key]}',u=z||d.location,p=['&url=',e(u),'& title=',e(t||d.title),'&source=',e(r),'&sourceUrl=',e(l),'& content=',c||'gb2312','&pic=',e(p||'')].join('');function%20a() {if(!window.open([f,p].join(''),'mb', ['toolbar=0,status=0,resizable=1,width=440,height=430,left=',(s.width- 440)/2,',top=',(s.height-430)/2].join('')))u.href=[f,p].join('');}; if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else%20a();}) (screen,document,encodeURIComponent,'','','','','',''));";
		if ($uid > 0 && $tid > 0)
		{
			if (sina_weibo_bind($uid))
			{
				$link = "{$sys_config['site_url']}/index.php?mod=xwb&m=xwbSiteInterface.share&tid={$tid}";
				$link = "javascript:void( window.open('". urlencode($link). "', '', 'toolbar=0,status=0,resizable=1,width=680,height=500') );";
			}
		}

		$return = ' | <a title="转发到新浪微博" href="'.$link.'" id="sina_weibo_share">转发到<img src="'.$sys_config['site_url'].'/images/xwb/bgimg/icon_logo.png" /></a>';
	}

	return $return;
}


function sina_weibo_oauth($access_token = null, $refresh_token = null) {
	$oauth = null;

	$sys_config = sina_weibo_enable();
	if($sys_config) {
		$client_id = $sys_config['sina']['app_key'];
		$client_secret = $sys_config['sina']['app_secret'];

		Load::lib('oauth2');
		$oauth = new JishiGouOAuth($client_id, $client_secret, $access_token, $refresh_token);
		$oauth->host = 'https:/'.'/api.weibo.com/';
		$oauth->access_token_url = 'https:/'.'/api.weibo.com/oauth2/access_token';
		$oauth->authorize_url = 'https:/'.'/api.weibo.com/oauth2/authorize';
	}

	return $oauth;
}

function sina_weibo_api($url, $p, $method='POST', $oauth=null, $mutli=false) {
	$ret = '';

	$oauth = ($oauth ? $oauth : sina_weibo_oauth());
	if($oauth) {
		if('POST' == $method) {
			$ret = $oauth->post($url, $p, $mutli);
		} else {
			$ret = $oauth->get($url, $p);
		}
	}

	return $ret;
}

function sina_weibo_substr($str, $length) {
		if( strlen($str) > $length + 600 ){
		$str = substr($str, 0, $length + 600);
	}

	$p = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
	preg_match_all($p,$str,$o);
	$size = sizeof($o[0]);
	$count = 0;
	for ($i=0; $i<$size; $i++) {
		if (strlen($o[0][$i]) > 1) {
			$count += 1;
		} else {
			$count += 0.5;
		}

		if ($count  > $length) {
			$i-=1;
			break;
		}

	}
	return implode('', array_slice($o[0],0, $i));
}

function sina_weibo_sync_face($uid, $face='') {
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