<?php
/**
 * 文件名：yy.func.php
 * 版本号：1.0
 * 最后修改时间：2011年9月14日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: YY接口函数
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}


function yy_enable($sys_config = array())
{
	if(!$sys_config) 
	{
		$sys_config = ConfigHandler::get();
	}
	
	if(!$sys_config['yy_enable']) 
	{
		return false;
	}
    
    if(!$sys_config['yy'])
    {
        $sys_config['yy'] = ConfigHandler::get('yy');
    }
	
	return $sys_config;
}


function yy_login($ico='s')
{
	$return = '';
	
	if (false != ($sys_config = yy_enable())) 
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/yy/login16.png',
			'm' => $sys_config['site_url'] . '/images/yy/login24.gif',
			'b' => $sys_config['site_url'] . '/images/yy/login.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];
		
		$return = '<a class="yyLogin" href="#" onclick="window.location.href=\''.$sys_config['site_url'].'/index.php?mod=yy&code=login\';return false;"><img src="'.$img_src.'"  /><div class="tlb_yy">使用YY帐号登录</div></a>';
	}
	
	return $return;
}

function yy_bind($uid=0)
{
    $bind_info = yy_bind_info($uid);
    
    return ($bind_info && $bind_info['yy_uid'] && $bind_info['token']);
}
function yy_has_bind($uid=0)
{
    return yy_bind($uid);
}


function yy_bind_info($uid=0)
{
    static $syy_bind_infos = null;
	
	$return = array();
	
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	
    if($uid > 0)
    {		
        if(null===($return = $syy_bind_infos[$uid]))
		{
			$return = DB::fetch_first("select * from ".DB::table('yy_bind_info')." where `uid`='{$uid}'");
			
			$syy_bind_infos[$uid] = $return;
		}
    }
    
    return $return;
}


function yy_bind_icon($uid=0)
{	
	$return = '';
	
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	
	if ($uid > 0 && ($sys_config = yy_enable())) 
	{
		
		$return = "<img src='{$sys_config['site_url']}/images/yy/off.gif' alt='未绑定YY' />";
		
		if (yy_bind($uid)) 
		{
			$return = "<img src='{$sys_config['site_url']}/images/yy/on.gif' alt='已经绑定YY' />";            
		}
		
		if (MEMBER_ID>0) 
		{
			$return = "<a href='#' title='YY绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=yy';return false;\">{$return}</a>";
		}
	}
	
	return $return;
}

?>