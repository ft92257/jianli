<?php
/**
 *
 * 用户与第三方绑定的管理设置模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: account.mod.php 286 2012-03-08 10:40:04Z wuliyong $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{	
	var $FormHandler;

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		Load::lib('form');
		$this->FormHandler = new FormHandler();
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code)
        {        
        	case 'yy':
        		$this->YY();
        		break;
        	case 'do_modify_yy':
        		$this->DoModifyYY();
        		break;

        	case 'renren':
        		$this->Renren();
        		break;
        	case 'do_modify_renren':
        		$this->DoModifyRenren();
        		break;
        		
        	case 'kaixin':
        		$this->Kaixin();
        		break;
        	case 'do_modify_kaixin':
        		$this->DoModifyKaixin();
        		break;
        		
        	case 'baidu':
        		$this->Baidu();
        		break;
        	case 'do_modify_baidu':
        		$this->DoModifyBaidu();
        		break;
        		
        	case 'fjau':
        		$this->Fjau();
        		break;
        	case 'do_modify_fjau':
        		$this->DoModifyFjau();
        		break;
            
			default:
				$this->Code = 'index';
                $this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}
	
	/**
	 * 
	 * 用户与第三方绑定关系列表
	 * 
	 * @author 狐狸<foxis@qq.com>
	 */
	function Main()
	{
		$_item_list = array(
			'xwb' => array(
				'name' => '新浪微博',
				'value' => 'xwb',
				'enable_func' => 'sina_weibo_init',
				'table' => 'xwb_bind_info',
				'key' => 'sina_uid',				
			),
			'qqwb' => array(
				'name' => '腾讯微博',
				'value' => 'qqwb',
				'enable_func' => 'qqwb_init',
				'table' => 'qqwb_bind_info',
				'key' => 'qqwb_username',
				'dateline' => 'dateline',				
			),
			'sms' => array(
				'name' => '手机短信',
				'value' => 'sms',
				'enable_func' => 'sms_init',
				'table' => 'sms_client_user',
				'key' => 'user_im',
				'dateline' => 'last_try_bind_time',				
			),
			'imjiqiren' => array(
				'name' => 'QQ机器人',
				'value' => 'imjiqiren',
				'enable_func' => 'imjiqiren_init',
				'table' => 'imjiqiren_client_user',
				'key' => 'user_im',
				'dateline' => 'last_try_bind_time',				
			),
			'yy' => array(
				'name' => 'YY',
				'value' => 'yy',
				'enable_func' => 'yy_init',
				'table' => 'yy_bind_info',
				'key' => 'yy_uid',
				'field' => array('yy_no', 'yy_nick', 'yy_email'),	
				'dateline' => 'dateline',					
			),
			'renren' => array(
				'name' => '人人',
				'value' => 'renren',
				'enable_func' => 'renren_init',
				'table' => 'renren_bind_info',
				'key' => 'renren_uid',
				'field' => array('renren_name'),		
				'dateline' => 'dateline',				
			),
			'kaixin' => array(
				'name' => '开心',
				'value' => 'kaixin',
				'enable_func' => 'kaixin_init',
				'table' => 'kaixin_bind_info',
				'key' => 'kaixin_uid',	
				'field' => array('kaixin_name'),
				'dateline' => 'dateline',					
			),
			'fjau' => array(
				'name' => '福建农林',
				'value' => 'fjau',
				'enable_func' => 'fjau_init',
				'table' => 'fjau_bind_info',
				'key' => 'fjau_uid',	
				'field' => array('fjau_name'),
				'dateline' => 'dateline',
			),
		);
		
		$item_list_config = array();
		foreach ($_item_list as $k=>$vs) {
			$enable_func = $vs['enable_func'];
			if($enable_func($this->Config)) {
				$vs['enable'] = 1;
				
				$vs['field'] = array_merge((array) $vs['field'], (array) $vs['key']);
				
				$item_list_config[$k] = $vs;
			}
		}
		
		
		
		if($item_list_config) {		
			$where_list = array();
			$limit = '';
			$uids = (array) (get_param('uids') ? get_param('uids') : get_param('uid'));
			$total_record = null;
			$per_page_num = 20;	
			$page_link = 'admin.php?mod=account&code=index';	
			
			
			
			$item = get_param('item');
			$item_config = $item_list_config[$item];
			if($item_config && $item_config['enable']) {
				$page_link .= '&item='.$item;
				
				$_where = ' where `uid`>0 ';
				$_order = ' order by `uid` asc ';
				
				$value = addslashes(trim(get_param('value')));			
				if($value) {
					$page_link .= '&value='.urlencode($value);
					
					$_where .= " and `{$item_config['key']}`='{$value}' ";
				}
				
				$total_record = DB::result_first("select count(*) as total_record from ".DB::table($item_config['table'])." $_where ");
				if($total_record > 0) {
					$page_arr = page($total_record, $per_page_num, $page_link, array('return' => 'Array',));
					$_limit = $page_arr['limit'];
					
					$query = DB::query("select * from ".DB::table($item_config['table']). " $_where $_order $_limit ");
					$uids = array();
					while (false != ($row = DB::fetch($query))) {					
						$uids[$row['uid']] = $row['uid'];
						$item_list[$item][$row['uid']] = $row;
					}
				}
			}
			
			
			if($uids) {
				$page_link .= '&uids='.http_build_query(array('uids'=>$uids));
				$where_list[] = " `uid` in ('".implode("','", $uids)."') ";
			} else {
				$s_nickname = get_param('s_nickname');
				if($s_nickname) {
					$page_link .= '&s_nickname='.urlencode($s_nickname);
					$where_list[] = " `nickname` like '%{$s_nickname}%' ";
				}
			}
			
			
			$where = ($where_list ? ' where ' . implode(' and ', $where_list) : '');
			$order = ' order by `uid` asc ';
			if(is_null($total_record)) {
				$total_record = DB::result_first("select count(`uid`) as total_record from ".DB::table('members')." $where ");
			}
			
			if($total_record > 0 && !$page_arr) {
				$page_arr = page($total_record, $per_page_num, $page_link, array('return'=>'Array'));
				$limit = $page_arr['limit'];
			}
			
			
			
			$query = DB::query("select `uid`, `nickname`, `username`, `email`, `ucuid` from ".DB::table('members')." $where $order $limit ");
			$member_list = array();
			$_uids = array();
			while (false != ($row = DB::fetch($query))) {
				$_uids[$row['uid']] = $row['uid'];
				$member_list[$row['uid']] = $row;
			}
			
			
			
			if($_uids) {
				foreach($item_list_config as $_item=>$_item_config) {
					if($_item_config['enable'] && !isset($item_list[$_item])) {
						$query = DB::query("select * from ".DB::table($_item_config['table'])." where `uid` in ('".implode("','", $_uids)."')");
						while(false != ($row = DB::fetch($query))) {
							$item_list[$_item][$row['uid']] = $row;
						} 
					}
				}
			}			
			
			
			Load::lib('form');
			$FormHandler = new FormHandler();
			
			
			$item_list_radio = $FormHandler->Radio('item', $item_list_config, $item);
		}
		
		
		include template('admin/account_index');
		
	}
   
	function YY()
	{
		$yy = ConfigHandler::get('yy');
		if(!$yy)
		{
			$yy = array(
				'enable' => 0,
				'client_id' => '',
				'client_secret' => '',
				'reg_pwd_display' => 1,
			);
		}
		
		
		$yy_enable_radio = $this->FormHandler->YesNoRadio('yy[enable]', (int) ($yy['enable'] && $this->Config['yy_enable']));
		$yy_reg_pwd_display_radio = $this->FormHandler->YesNoRadio('yy[reg_pwd_display]', (int) $yy['reg_pwd_display']);
		
		
		include template('admin/account_yy');
	}
	function DoModifyYY()
	{
		
		$rets = $this->_yy_env();
		if($rets)
		{
			ConfigHandler::update('yy_enable', 0);
			
			$this->Messager($rets, null);
		}
		
		
		$yy_default = ConfigHandler::get('yy');
		
		
		$yy = $this->Post['yy'];
		$yy['enable'] = (($yy['enable'] && $yy['client_id'] && $yy['client_secret']) ? 1 : 0);
		
		
		if($yy['enable'] != $this->Config['yy_enable'])
		{
			ConfigHandler::update('yy_enable', $yy['enable']);
		}
		
		
		if($yy != $yy_default)
		{
			ConfigHandler::set('yy', $yy);
		}


		$this->Messager("配置修改成功");		
		
	}
	function _yy_env()
	{
		Load::functions('yy_env');
		
		return yy_env();
	}

   
	function Renren()
	{
		$renren = ConfigHandler::get('renren');
		if(!$renren)
		{
			$renren = array(
				'enable' => 0,
				'client_id' => '',
				'client_secret' => '',
				'reg_pwd_display' => 1,
				'is_sync_topic' => 1,
				'is_sync_image' => 1,
			);
		}
		
		
		$renren_enable_radio = $this->FormHandler->YesNoRadio('renren[enable]', (int) ($renren['enable'] && $this->Config['renren_enable']));
		$renren_reg_pwd_display_radio = $this->FormHandler->YesNoRadio('renren[reg_pwd_display]', (int) $renren['reg_pwd_display']);
		$renren_is_sync_topic_radio = $this->FormHandler->YesNoRadio('renren[is_sync_topic]', (int) $renren['is_sync_topic']);
		$renren_is_sync_image_radio = $this->FormHandler->YesNoRadio('renren[is_sync_image]', (int) $renren['is_sync_image']);
		
		
		
		include template('admin/account_renren');
	}
	function DoModifyRenren()
	{
		
		$rets = $this->_renren_env();
		if($rets)
		{
			ConfigHandler::update('renren_enable', 0);
			
			$this->Messager($rets, null);
		}
		
		
		$renren_default = ConfigHandler::get('renren');
		
		
		$renren = $this->Post['renren'];
		$renren['enable'] = (($renren['enable'] && $renren['client_id'] && $renren['client_secret']) ? 1 : 0);
		
		
		if($renren['enable'] != $this->Config['renren_enable'])
		{
			ConfigHandler::update('renren_enable', $renren['enable']);			
		}
		
		
		if($renren != $renren_default)
		{
			ConfigHandler::set('renren', $renren);
		}


		$this->Messager("配置修改成功");		
		
	}
	function _renren_env()
	{
		Load::functions('renren_env');
		
		return renren_env();
	}

   
	function Kaixin()
	{
		$kaixin_default = array(
			'enable' => 0,
			'client_id' => '',
			'client_secret' => '',
			'reg_pwd_display' => 1,
			'is_sync_topic' => 1,
			'is_sync_image' => 1,
		);
		
		$kaixin = ConfigHandler::get('kaixin');
		if(!$kaixin)
		{
			$kaixin = $kaixin_default;
		}
		
		
		
		$kaixin_enable_radio = $this->FormHandler->YesNoRadio('kaixin[enable]', (int) ($kaixin['enable'] && $this->Config['kaixin_enable']));
		$kaixin_reg_pwd_display_radio = $this->FormHandler->YesNoRadio('kaixin[reg_pwd_display]', (int) $kaixin['reg_pwd_display']);
		$kaixin_is_sync_topic_radio = $this->FormHandler->YesNoRadio('kaixin[is_sync_topic]', (int) $kaixin['is_sync_topic']);
		$kaixin_is_sync_image_radio = $this->FormHandler->YesNoRadio('kaixin[is_sync_image]', (int) $kaixin['is_sync_image']);
		
		
		
		include template('admin/account_kaixin');
	}
	function DoModifyKaixin()
	{
		
		$rets = $this->_kaixin_env();
		if($rets)
		{
			ConfigHandler::update('kaixin_enable', 0);
			
			$this->Messager($rets, null);
		}
		
		
		$kaixin_default = ConfigHandler::get('kaixin');
		
		
		$kaixin = $this->Post['kaixin'];
		$kaixin['enable'] = (($kaixin['enable'] && $kaixin['client_id'] && $kaixin['client_secret']) ? 1 : 0);
		
		
		if($kaixin['enable'] != $this->Config['kaixin_enable'])
		{
			ConfigHandler::update('kaixin_enable', $kaixin['enable']);		
		}
		
		
		if($kaixin != $kaixin_default)
		{
			ConfigHandler::set('kaixin', $kaixin);
		}


		$this->Messager("配置修改成功");		
		
	}
	function _kaixin_env()
	{
		Load::functions('kaixin_env');
		
		return kaixin_env();
	}
	
	function Baidu()
	{
		$baidu = ConfigHandler::get('baidu');
		if(!$baidu)
		{
			$baidu = array(
				'enable' => 0,
				'client_id' => '',
				'client_secret' => '',
				'reg_pwd_display' => 1,
			);
		}
		
		
		$baidu_enable_radio = $this->FormHandler->YesNoRadio('baidu[enable]', (int) ($baidu['enable'] && $this->Config['baidu_enable']));
		$baidu_reg_pwd_display_radio = $this->FormHandler->YesNoRadio('baidu[reg_pwd_display]', (int) $baidu['reg_pwd_display']);
		
		
		include template('admin/account_baidu');
	}
	function DoModifyBaidu()
	{
		
		$rets = $this->_baidu_env();
		if($rets)
		{
			ConfigHandler::update('baidu_enable', 0);
			
			$this->Messager($rets, null);
		}
		
		
		$baidu_default = ConfigHandler::get('baidu');
		
		
		$baidu = $this->Post['baidu'];
		$baidu['enable'] = (($baidu['enable'] && $baidu['client_id'] && $baidu['client_secret']) ? 1 : 0);
		
		
		if($baidu['enable'] != $this->Config['baidu_enable'])
		{
			ConfigHandler::update('baidu_enable', $baidu['enable']);			
		}
		
		
		if($baidu != $baidu_default)
		{
			ConfigHandler::set('baidu', $baidu);
		}


		$this->Messager("配置修改成功");	
		
	}
	function _baidu_env()
	{
		Load::functions('baidu');
		
		return baidu_env();
	}
	
	
	function Fjau()
	{
		$fjau = ConfigHandler::get('fjau');
		if(!$fjau)
		{
			$fjau = array(
				'enable' => 0,
				'reg_pwd_display' => 1,
			);
		}
		
		
		$fjau_enable_radio = $this->FormHandler->YesNoRadio('fjau[enable]', (int) ($fjau['enable'] && $this->Config['fjau_enable']));
		$fjau_reg_pwd_display_radio = $this->FormHandler->YesNoRadio('fjau[reg_pwd_display]', (int) $fjau['reg_pwd_display']);
		
		
		include template('admin/account_fjau');
	}
	function DoModifyFjau()
	{
		Load::functions('fjau');
		
		
		$rets = $this->_fjau_env();
		if($rets)
		{
			ConfigHandler::update('fjau_enable', 0);
			
			$this->Messager($rets, null);
		}
		
		
		$fjau_default = ConfigHandler::get('fjau');
		
		
		$fjau = $this->Post['fjau'];
		$fjau['enable'] = (($fjau['enable']) ? 1 : 0);
		
		$fjau = fjau_config($fjau);
		
		
		if($fjau['enable'] != $this->Config['fjau_enable'])
		{
			ConfigHandler::update('fjau_enable', $fjau['enable']);			
		}
		
		
		if($fjau != $fjau_default)
		{
			ConfigHandler::set('fjau', $fjau);
		}


		$this->Messager("配置修改成功");	
		
	}
	function _fjau_env()
	{
		return fjau_env();
	}
}


?>
