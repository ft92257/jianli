<?php
/**
 * 文件名：kaixin.func.php
 * 版本号：1.0
 * 最后修改时间：2011年9月19日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 开心接口函数
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


function kaixin_enable($sys_config = array())
{
	if(!$sys_config)
	{
		$sys_config = ConfigHandler::get();
	}

	if(!$sys_config['kaixin_enable'])
	{
		return false;
	}

	if(!$sys_config['kaixin'])
	{
		$sys_config['kaixin'] = ConfigHandler::get('kaixin');
	}

	return $sys_config;
}


function kaixin_oauth($access_token = null, $refresh_token = null)
{
	$kaixin_oauth = null;

	$sys_config = kaixin_enable();
	if($sys_config)
	{
		$client_id = $sys_config['kaixin']['client_id'];
		$client_secret = $sys_config['kaixin']['client_secret'];

		Load::lib('oauth2');
		$kaixin_oauth = new JishiGouOAuth($client_id, $client_secret, $access_token, $refresh_token);
		$kaixin_oauth->host = 'https:/'.'/api.kaixin001.com/';
		$kaixin_oauth->access_token_url = 'https:/'.'/api.kaixin001.com/oauth2/access_token';
		$kaixin_oauth->authorize_url = 'https:/'.'/api.kaixin001.com/oauth2/authorize';
	}

	return $kaixin_oauth;
}


function kaixin_api($url, $p, $method = 'POST', $kaixin_oauth = null)
{
	$ret = false;

	$kaixin_oauth = $kaixin_oauth ? $kaixin_oauth : kaixin_oauth();
	if($kaixin_oauth)
	{
		if('POST' == $method)
		{
			$ret = $kaixin_oauth->post($url, $p);
		}
		else
		{
			$ret = $kaixin_oauth->get($url, $p);
		}
	}

	return $ret;
}


function kaixin_sync($data)
{
	$sys_config = kaixin_init();
	if(!$sys_config) {
		return 'kaixin_init is invalid';
	}

	$tid = is_numeric($data['tid']) ? $data['tid'] : 0;
	if($tid < 1) {
		return 'tid is invalid';
	}

	$uid = is_numeric($data['uid']) ? $data['uid'] : 0;
	if($uid < 1) {
		return 'uid is invalid';
	}

	$totid = is_numeric($data['totid']) ? $data['totid'] : 0;

	$content = $data['content'];
	if(false !== strpos($content, '[')) {
		$content = preg_replace('~\[([^\]]{1,6}?)\]~', '(#\\1)', $content);
	}
	$content = array_iconv($sys_config['charset'], 'UTF-8', trim(strip_tags($content)));
	if(!$content) {
		return 'content is invalid';
	}
	$content .= " " . get_full_url($sys_config['site_url'], 'index.php?mod=topic&code=' . $tid);


	$kaixin_bind_info = kaixin_bind_info($uid);
	if(!$kaixin_bind_info) {
		return 'bind_info is empty';
	}

	if(!kaixin_has_bind($uid)) {
		return 'bind_info is invalid';
	}

	$kaixin_bind_topic = DB::fetch_first("select * from ".DB::table('kaixin_bind_topic')." where `tid`='$tid'");
	if($kaixin_bind_topic) {
		return 'bind_topic is invalid';
	} else {
		DB::query("insert into ".DB::table('kaixin_bind_topic')." (`tid`) values ('$tid')");
	}

	$ret = array();
	if($totid < 1) {
		$p = array();
		$p['access_token'] = $kaixin_bind_info['token'];
		$p['content'] = $content;

		$imageid = (int) $data['imageid'];
		if($imageid > 0 && $sys_config['kaixin']['is_sync_image']) {
			$topic_image = topic_image($imageid, 'original');
			if(is_image(ROOT_PATH . $topic_image)) {
				$p['picurl'] = $sys_config['site_url'] . '/' . $topic_image;
				$p['save_to_album'] = 1;
			}
		}

		$ret = kaixin_api('records/add', $p);
	}

	$kaixin_id = is_numeric($ret['rid']) ? $ret['rid'] : 0;
	if($kaixin_id > 0) {
		DB::query("UPDATE ".DB::table('kaixin_bind_topic')." SET `kaixin_id`='$kaixin_id' WHERE `tid`='$tid'");
	}

	return $ret;
}


function kaixin_login($ico='s')
{
	$return = '';

	if (false != ($sys_config = kaixin_enable()))
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/kaixin/login16.png',
			'm' => $sys_config['site_url'] . '/images/kaixin/login24.gif',
			'b' => $sys_config['site_url'] . '/images/kaixin/login.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];

		$return = '<a class="kaixinLogin" href="#" onclick="window.location.href=\''.$sys_config['site_url'].'/index.php?mod=kaixin&code=login\';return false;"><img src="'.$img_src.'" /><div class="tlb_kaixin">使用开心帐号登录</div></a>';
	}

	return $return;
}

function kaixin_bind($uid=0)
{
	$bind_info = kaixin_bind_info($uid);

	return ($bind_info && $bind_info['kaixin_uid'] && $bind_info['token']);
}
function kaixin_has_bind($uid=0)
{
	return kaixin_bind($uid);
}


function kaixin_bind_info($uid=0)
{
	static $skaixin_bind_infos = null;

	$return = array();

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if($uid > 0)
	{
		if(null===($return = $skaixin_bind_infos[$uid]))
		{
			$return = DB::fetch_first("select * from ".DB::table('kaixin_bind_info')." where `uid`='{$uid}'");
				
			$skaixin_bind_infos[$uid] = $return;
		}
	}

	return $return;
}


function kaixin_bind_icon($uid=0)
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = kaixin_enable()))
	{

		$return = "<img src='{$sys_config['site_url']}/images/kaixin/off.gif' alt='未绑定开心' />";

		if (kaixin_bind($uid))
		{
			$return = "<img src='{$sys_config['site_url']}/images/kaixin/on.gif' alt='已经绑定开心' />";
		}

		if (MEMBER_ID>0)
		{
			$return = "<a href='#' title='开心绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=kaixin';return false;\">{$return}</a>";
		}
	}

	return $return;
}


function kaixin_syn_html($uid = 0)
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = kaixin_enable()) && $sys_config['kaixin']['is_sync_topic'])
	{
		$row = kaixin_bind_info($uid);

		$a = $b = $c = $d = $e = '';
		if ($row && $row['kaixin_uid'])
		{
			$b = "{$sys_config['site_url']}/images/kaixin/icon_on.gif";
				
						$d = "";
				
			$e = "<label for='syn_to_kaixin'><i></i><img src='{$b}' title='同步发到开心'/></label>";
		}
		else
		{
						$b = "{$sys_config['site_url']}/images/kaixin/icon_off.gif";
			$c = "disabled='disabled'";
			$e = "<a href='{$sys_config['site_url']}/index.php?mod=account&code=kaixin' title='开通此功能（将打开新窗口）'><i></i><img src='{$b}' title='同步发到开心'/></a>";
		}

		$return = "{$a}{$e}<input type='checkbox' id='syn_to_kaixin' name='syn_to_kaixin' value='1' {$c} {$d} />";
	}

	return $return;
}

?>