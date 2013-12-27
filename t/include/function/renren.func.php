<?php
/**
 * 文件名：renren.func.php
 * 版本号：1.0
 * 最后修改时间：2011年9月14日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 人人接口函数
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}


function renren_enable($sys_config = array())
{
	if(!$sys_config) 
	{
		$sys_config = ConfigHandler::get();
	}
	
	if(!$sys_config['renren_enable']) 
	{
		return false;
	}
    
    if(!$sys_config['renren'])
    {
        $sys_config['renren'] = ConfigHandler::get('renren');
    }
	
	return $sys_config;
}


function renren_oauth($access_token = null, $refresh_token = null)
{
	$renren_oauth = null;
	
	$sys_config = renren_enable();
	if($sys_config)
	{
		$client_id = $sys_config['renren']['client_id'];
		$client_secret = $sys_config['renren']['client_secret'];
		
		Load::lib('oauth2');
		$renren_oauth = new JishiGouOAuth($client_id, $client_secret, $access_token, $refresh_token);
		$renren_oauth->host = 'https:/'.'/graph.renren.com/';
		$renren_oauth->access_token_url = 'https:/'.'/graph.renren.com/oauth/token';
		$renren_oauth->authorize_url = 'https:/'.'/graph.renren.com/oauth/authorize';
	}
	
	return $renren_oauth;
}


function renren_api($method, $p, $request = 'POST', $renren_oauth = null)
{
	$ret = false;
	
	$sys_config = renren_enable();
	if($sys_config)
	{
		$renren_oauth = $renren_oauth ? $renren_oauth : renren_oauth();
		if($renren_oauth)
		{
			$url = 'http:/'.'/api.renren.com/restserver.do';
    	
	    	$p['api_key'] = $sys_config['renren']['client_id'];
	    	$p['method'] = $method;
	    	$p['v'] = '1.0';
	    	$p['format'] = 'json';
	    	
	    	$p = renren_sign($p, $sys_config['renren']['client_secret']);
	    	
			if('POST' == $request)
			{
				$ret = $renren_oauth->post($url, $p);
			}
			else 
			{
				$ret = $renren_oauth->get($url, $p);
			}
		}
	}		
	
	return $ret;
}


function renren_sync($data)
{
	$sys_config = renren_init();
	if(!$sys_config)
	{
		return 'renren_init is invalid';
	}
	
	$tid = is_numeric($data['tid']) ? $data['tid'] : 0;
	if($tid < 1)
	{
		return 'tid is invalid';
	}
	
	$uid = is_numeric($data['uid']) ? $data['uid'] : 0;
	if($uid < 1)
	{
		return 'uid is invalid';
	}
	
	$totid = is_numeric($data['totid']) ? $data['totid'] : 0;
	
	$content = $data['content'];
	if(false !== strpos($content, '['))	
	{
		$content = preg_replace('~\[([^\]]{1,6}?)\]~', '(\\1)', $content);
	}
	$content = trim(strip_tags($content));
	
	$name = array_iconv($sys_config['charset'], 'UTF-8', cutstr($content, 50));
	
	$content = array_iconv($sys_config['charset'], 'UTF-8', $content);
	if(!$content)
	{
		return 'content is invalid';
	}
	
	$url = get_full_url($sys_config['site_url'], 'index.php?mod=topic&code=' . $tid);
	
		
	
	$renren_bind_info = renren_bind_info($uid);
	if(!$renren_bind_info)
	{
		return 'bind_info is empty';
	}
	
	if(!renren_has_bind($uid))
	{
		return 'bind_info is invalid';
	}
	
	$renren_bind_topic = DB::fetch_first("select * from ".DB::table('renren_bind_topic')." where `tid`='$tid'");
	if($renren_bind_topic)
	{
		return 'bind_topic is invalid';
	}
	else 
	{
		DB::query("insert into ".DB::table('renren_bind_topic')." (`tid`) values ('$tid')");
	}
	
	$ret = array();
	if($totid < 1)
	{
		$p = array();
		$p['access_token'] = $renren_bind_info['token'];
		$p['name'] = $name;
		$p['description'] = $content;
		$p['url'] = $url;		
		
		$p['action_name'] = array_iconv($sys_config['charset'], 'UTF-8', '来自：'.$sys_config['site_name']);
				$p['action_link'] = $url;
		
		
		$imageid = (int) $data['imageid'];
		if($imageid > 0 && $sys_config['renren']['is_sync_image'])
		{
			$topic_image = topic_image($imageid, 'original');
			if(is_image(ROOT_PATH . $topic_image))
			{
				$p['image'] = $sys_config['site_url'] . '/' . $topic_image;
			}
		}
		
		$ret = renren_api('feed.publishFeed', $p);
	}
	
	
	$renren_id = is_numeric($ret['post_id']) ? $ret['post_id'] : 0;
	if($renren_id > 0)
	{
		DB::query("UPDATE ".DB::table('renren_bind_topic')." SET `renren_id`='$renren_id' WHERE `tid`='$tid'");
	}
	
	return $ret;
}


function renren_login($ico='s')
{
	$return = '';
	
	if (false != ($sys_config = renren_enable())) 
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/renren/login16.png',
			'm' => $sys_config['site_url'] . '/images/renren/login24.gif',
			'b' => $sys_config['site_url'] . '/images/renren/login.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];
		
		$return = '<a class="renrenLogin" href="#" onclick="window.location.href=\''.$sys_config['site_url'].'/index.php?mod=renren&code=login\';return false;"><img src="'.$img_src.'" /><div class="tlb_renren">使用人人帐号登录</div></a>';
	}
	
	return $return;
}

function renren_bind($uid=0)
{
    $bind_info = renren_bind_info($uid);
    
    return ($bind_info && $bind_info['renren_uid'] && $bind_info['token']);
}
function renren_has_bind($uid=0)
{
    return renren_bind($uid);
}


function renren_bind_info($uid=0)
{
    static $srenren_bind_infos = null;
	
	$return = array();
	
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	
    if($uid > 0)
    {		
        if(null===($return = $srenren_bind_infos[$uid]))
		{
			$return = DB::fetch_first("select * from ".DB::table('renren_bind_info')." where `uid`='{$uid}'");
			
			$srenren_bind_infos[$uid] = $return;
		}
    }
    
    return $return;
}


function renren_bind_icon($uid=0)
{	
	$return = '';
	
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	
	if ($uid > 0 && ($sys_config = renren_enable())) 
	{
		
		$return = "<img src='{$sys_config['site_url']}/images/renren/off.gif' alt='未绑定人人' />";
		
		if (renren_bind($uid)) 
		{
			$return = "<img src='{$sys_config['site_url']}/images/renren/on.gif' alt='已经绑定人人' />";            
		}
		
		if (MEMBER_ID>0) 
		{
			$return = "<a href='#' title='人人绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=renren';return false;\">{$return}</a>";
		}
	}
	
	return $return;
}


function renren_syn_html($uid = 0)
{
	$return = '';
	
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	
	if ($uid > 0 && ($sys_config = renren_enable()) && $sys_config['renren']['is_sync_topic']) 
	{		
		$row = renren_bind_info($uid);
		
		$a = $b = $c = $d = $e = '';
		if ($row && $row['renren_uid']) 
		{
			$b = "{$sys_config['site_url']}/images/renren/icon_on.gif";
			
						$d = "";
			
			$e = "<label for='syn_to_renren'><i></i><img src='{$b}' title='同步发到人人'/></label>";			
		}
		else 
		{
			$b = "{$sys_config['site_url']}/images/renren/icon_off.gif";
			$c = "disabled='disabled'";
			$e = "<a href='{$sys_config['site_url']}/index.php?mod=account&code=renren' title='开通此功能（将打开新窗口）'><i></i><img src='{$b}' title='同步发到人人'/></a>";			
		}
		
		$return = "{$a}{$e}<input type='checkbox' id='syn_to_renren' name='syn_to_renren' value='1' {$c} {$d} />";
	}
	
	return $return;
}


function renren_sign($p, $secret_key, $signk = 'sig')
{
	ksort($p);
	reset($p);
	
	$str = '';
	foreach($p as $k=>$v)
	{
		$str .= $k.'='.$v;
	}
	
	$signv = md5($str . $secret_key);
	
	if($signk)
	{
		$p[$signk] = $signv;		
		return $p;
	}
	else 
	{
		return $signv;
	}
}

function renren_session_key($access_token)
{
	return substr($access_token, strpos($access_token, '|') + 1);
}

?>