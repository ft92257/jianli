<?php
/**
 *
 * 上墙模块AJAX操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: wall.mod.php 861 2012-04-27 02:28:31Z wuliyong $
 */

/**
 * ModuleObject
 *
 * @package www.jishigou.com
 * @author 狐狸<foxis@qq.com>
 * @copyright 2010
 * @version $Id: wall.mod.php 861 2012-04-27 02:28:31Z wuliyong $
 * @access public
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{	
	var $WallLogic;
	
	var $WallInfo = array();
	
	var $WallId = 0;

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		Load::logic('wall');
		$this->WallLogic = new WallLogic();

		$this->Execute();
	}

	
	function Execute()
	{
		switch ($this->Code)
        {          
        	case 'set_status':
        		$this->SetStatus();
        		break;
        	case 'set_wall':
        		$this->SetWall();
        		break; 
        	case 'do_set_wall':
        		$this->DoSetWall();
        		break;  
        	
        	case 'add_key':
        		$this->AddKey();
        		break;
        	case 'del_key':
        		$this->DelKey();
        		break;
        		
        	case 'add_draft':
        		$this->AddDraft();
        		break;
        	case 'del_draft':
        		$this->DelDraft();
        		break;
        		
        	case 'add_playlist':
        		$this->AddPlaylist();
        		break;
        	case 'add_playlist_all':
        		$this->AddPlaylistAll();
        		break;
        	case 'del_playlist':
        		$this->DelPlaylist();
        		break;
        	case 'del_playlist_all':
        		$this->DelPlaylistAll();
        		break;
        	case 'load_playlist':
        		$this->LoadPlaylist();
        		break;
        		
        	case 'newly':
        		$this->Newly();
        		break;

			default:
				$this->Main();
		}
	}

	function Main()
	{		
        response_text("正在建设中");        
	}
	
	
	function _init_wall()
	{
		$this->initMemberHandler();
		if(MEMBER_ID < 1)
		{
			json_error("请先登录或者注册一个帐号");
		}
		
		$this->WallInfo = $this->WallLogic->get_wall_info(MEMBER_ID, 1);
		if(!$this->WallInfo)
		{
			json_error('素材库为空');
		}

		$this->WallId = $this->WallInfo['id'];
		if($this->WallId < 1)
		{
			json_error('素材库不能为空');
		}
		
	}
	
	function SetStatus()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$status = ($this->Post['status'] ? $this->Post['status'] : $this->Get['status']);
		$status = $status ? 1 : 0;
		
		$ret = $this->WallLogic->set_wall_status($wall_id, $status);
		
		json_result('设置成功');
	}
	
	function SetWall()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$wall_info = $this->WallInfo;
		
		
		include($this->TemplateHandler->Template('wall_set_ajax'));
	}
	
	function DoSetWall()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$wall_info = $this->WallInfo;
		
		$p = $this->Post;
		if(isset($p['id']) && $p['id']!=$wall_id) {
			json_error('请指定一个正确的ID');
		}
		$p['id'] = $wall_id;
		
				$p['auto_wall_tag'] = trim(strip_tags($p['auto_wall_tag']));
		if($p['auto_wall_tag'] != $wall_info['auto_wall_tag']) {
			$p['auto_wall_tid'] = 0;
		}
		
		$ret = $this->WallLogic->modify_wall($p);
		
		json_result('设置成功');
	}
	
	function AddKey()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$type = max(0 , (int) ($this->Post['type'] ? $this->Post['type'] : $this->Get['type']));
		
		$key = ($this->Post['key'] ? $this->Post['key'] : $this->Get['key']);
		if(!$key)
		{
			json_error('请指定一个关键词');
		}
		
		$ret = $this->WallLogic->add_wall_material($wall_id, $type, $key);
		if(-1 == $ret)
		{
			json_error('已经存在了，无需再次添加');
		}
		elseif(0 == $ret)
		{
			json_error('添加失败');
		}
		elseif(0 < $ret)
		{
			json_result('添加成功');
		}
	}
	
	function DelKey()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$type = max(0 , (int) ($this->Post['type'] ? $this->Post['type'] : $this->Get['type']));
		
		$key = ($this->Post['key'] ? $this->Post['key'] : $this->Get['key']);
		if(!$key)
		{
			json_error('请指定一个关键词');
		}

		$ret = $this->WallLogic->del_wall_material($wall_id, $type, $key);
		if($ret < 1)
		{
			json_error('删除失败');
		}
		else 
		{
			json_result('删除成功');
		}
	}
	
	function AddDraft()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$mark = max(0 , (int) ($this->Post['mark'] ? $this->Post['mark'] : $this->Get['mark']));
		if($mark < 1 || $mark > 3)
		{
			json_error('标记错误');
		}
		
		$tid = max(0 , (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
		if($tid < 1)
		{
			json_error('请指定一个正确的微博ID');
		}
		
		
		$ret = $this->WallLogic->add_wall_draft($wall_id, $tid, $mark);
		if(-1 == $ret)
		{
			json_error('已经存在了，无需再次添加');
		}
		elseif(0 == $ret)
		{
			json_error('添加失败');
		}
		elseif(0 < $ret)
		{
			json_result('添加成功');
		}
	}
	
	function DelDraft()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$mark = max(0 , (int) ($this->Post['mark'] ? $this->Post['mark'] : $this->Get['mark']));
		if($mark < 1 || $mark > 3)
		{
			json_error('标记错误');
		}
		
		$tid = max(0 , (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
		if($tid < 1)
		{
			json_error('请指定一个正确的微博ID');
		}
		
		
		$ret = $this->WallLogic->del_wall_draft($wall_id, $tid, $mark);
		if($ret < 1)
		{
			json_error('删除失败');
		}
		else 
		{
			json_result('删除成功');
		}
	}
	
	function AddPlaylist()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$tid = max(0, (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
		if($tid < 1)
		{
			json_error('请指定一个ID');
		}
		$unshift = max(0, (int) ($this->Post['unshift'] ? $this->Post['unshift'] : $this->Get['unshift']));
		
		$ret = $this->WallLogic->add_wall_playlist($wall_id, $tid, $unshift);
		if(-1 == $ret)
		{
			json_error('已经存在了，无需再次添加');
		}
		elseif(0 == $ret)
		{
			json_error('添加失败');
		}
		elseif(0 < $ret)
		{
			json_result('添加成功');
		}
	}
	
	function AddPlaylistAll()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$tids = trim($this->Post['tids'] ? $this->Post['tids'] : $this->Get['tids'],", ");
		if(!$tids)
		{
			json_error('请指定一个正确的ID');
		}
		
		$_tids = @explode(",",$tids);
		if($_tids)
		{
			settype($_tids, 'array');
			rsort($_tids);
			
			foreach($_tids as $tid)
			{
				$ret = $this->WallLogic->add_wall_playlist($wall_id, $tid);
			}
		}
		
		json_result('添加成功');
	}

	function DelPlaylist()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$tid = max(0, (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
		if($tid < 1)
		{
			json_error('请指定一个ID');
		}
		
		$ret = $this->WallLogic->del_wall_playlist($wall_id, $tid);
		if($ret)
		{
			json_result('删除成功');
		}
		else
		{
			json_error('删除失败');
		}		
	}
	
	function DelPlaylistAll()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$ret = $this->WallLogic->clear_wall_playlist($wall_id);
		
		json_result('清空成功');		
	}
	
	function LoadPlaylist()
	{
		$this->_init_wall();
		
		$wall_id = $this->WallId;
		
		$wall_playlist_tids = $this->WallLogic->get_wall_playlist_tids($wall_id);
		
		if($wall_playlist_tids)
		{
			
			$TopicLogic = Load::logic('topic', 1);
			
			$topic_list = $TopicLogic->Get($wall_playlist_tids);
			
			$topic_list_count = count($topic_list);
			if($topic_list)
			{
				$_tids = $wall_playlist_tids;
				foreach ($topic_list as $row) 
				{					
					unset($_tids[$row['tid']]);
				}
				
				if($_tids)
				{
					foreach($_tids as $_tid)
					{
						$this->WallLogic->del_wall_playlist($wall_id, $_tid);
					}
				}
				
								$parent_list = $TopicLogic->GetParentTopic($topic_list);
							}
		}
		
		
		include($this->TemplateHandler->Template('wall_playlist_ajax'));
	}
	
	function Newly()
	{
		$time = time();
		$wall_id = max(0, (int) ($this->Post['wall_id'] ? $this->Post['wall_id'] : $this->Get['wall_id']));
		$last_tid = ($this->Post['last_tid'] ? $this->Post['last_tid'] : $this->Get['last_tid']);
		$last_tid = is_numeric($last_tid) ? $last_tid : (int) substr($last_tid, 3);
		$last_tid = max(0 , (int) $last_tid);
		$wall_info = $this->WallLogic->get_wall_info($wall_id);
		if($wall_info && $wall_info['status'])
		{
			$wall_playlist_tids = array();
			if($wall_info['last_load_time'] + $wall_info['wall_reload_time'] < $time)
			{
				$this->WallLogic->set_wall_last_load_time($wall_id, $time);
				
				$wall_playlist_tids = $this->WallLogic->get_wall_playlist_tids($wall_id, 0, 1);
				
				$this->WallLogic->set_wall_last_load_tid($wall_id, (int) implode('', $wall_playlist_tids));
				
				$this->_auto_wall($wall_info);
			}
			else 
			{
				if($wall_info['last_load_tid'] != $last_tid)
				{
					$wall_playlist_tids	= (array) $wall_info['last_load_tid'];
				}
			}
			
			
			if($wall_playlist_tids)
			{				
				$this->WallLogic->del_wall_playlist($wall_id, $wall_playlist_tids);
				
				
				$TopicLogic = Load::logic('topic', 1);
				
				$topic_list = $TopicLogic->Get($wall_playlist_tids);
				
				$topic_list_count = count($topic_list);
				if($topic_list)
				{
										$parent_list = $TopicLogic->GetParentTopic($topic_list);
									}
			}
		}
		
		
		include($this->TemplateHandler->Template('wall_screen_ajax'));
	}
	
	function _auto_wall($wall_info)
	{
		$tids = array();
		if($wall_info['auto_wall_tag'])
		{
			$tids = $this->_auto_wall_get_topic_ids_by_tag($wall_info['auto_wall_tag'],$wall_info['auto_wall_tid'],$tids);
		}
		
		if($tids)
		{
			settype($tids, 'array');
			rsort($tids);
			
			$auto_wall_tid = 0;
			foreach($tids as $tid)
			{
				if($auto_wall_tid < 1) $auto_wall_tid = $tid;
				
				$ret = $this->WallLogic->add_wall_playlist($wall_info['id'], $tid);
			}
			
			if($auto_wall_tid > 0 && $auto_wall_tid != $wall_info['auto_wall_tid'])
			{
				$ret = $this->WallLogic->set_wall_auto_wall_tid($wall_info['id'], $auto_wall_tid);
			}
		}
	}	
	function _auto_wall_get_topic_ids_by_tag($key, $tid = 0, $return_tids = array())
	{
		$key = trim(strip_tags($key),' ,#');
		if(!$key) return $return_tids;
		
		$tid = max(0, (int) $tid);
		
		$akey = addslashes($key);
		
		$tag_info = DB::fetch_first("select * from ".DB::table('tag')." where `name`='$akey'");
		if(!$tag_info) return $return_tids;
		
		$query = DB::query("select * from ".DB::table('topic_tag')." where `tag_id`='{$tag_info[id]}' and `item_id`>'$tid' order by `item_id` desc limit 20");
		while(false != ($row = DB::fetch($query)))
		{
			$return_tids[$row['item_id']] = $row['item_id'];
		}
		
		return $return_tids;
	}
	
}


?>
