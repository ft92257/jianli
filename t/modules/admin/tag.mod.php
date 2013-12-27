<?php

/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 标签管理
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.jishigou.net
 */
include_once(ROOT_PATH . 'include/function/misc.func.php');
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ID = 0;
	
	var $FormHandler;
	
	var $TopicLogic;
	
	var $TagExtraLogic;


	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		$this->ID = $this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];
		
		Load::lib('form');
		$this->FormHandler = new FormHandler();
		
		
		$this->TopicLogic = Load::logic('topic', 1);
		
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case "delete":
				$this->delete();
				break;
				
			case "num_setting":
				$this->NumSetting();
				break;
				
			case "tag_status":
				$this->Tag_Status();
				break;
				
			case 'extra':
				$this->Extra();
				break;
			case 'add_extra':
				$this->AddExtra();
				break;
			case 'modify_extra':
				$this->ModifyExtra();
				break;
			case 'delete_extra':
				$this->DeleteExtra();
				break;
				
			case 'recommend':
				$this->Recommend();
				break;
			case 'do_recommend':
				$this->DoRecommend();
				break;
				
				
			default:
				$this->Code='list';
				$this->DoList();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function DoList()
	{
				$keyword=trim($this->Get['keyword']);
		
		$where_list=array();
		if($keyword)$where_list[]="`name` like '%$keyword%'";
		$where="";
		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';

		$per_page_num= (int)$this->Get['pn'] ? (int)$this->Get['pn'] : 50;
		$offset=(max((int)$this->Get['page'],1)-1)*$per_page_num;
		
				$sql="SELECT count(1) total from ".TABLE_PREFIX."tag $where";
		$query = $this->DatabaseHandler->Query($sql);
		$row=$query->GetRow();
		$total=$row['total'];
		
				$order_by_list = array
		(
			'order_by_default' => 'default',
		
			'default' => array
			(
				'name' => '添加时间',
				'order_by' => 'id',
			),
			'thread_count' => array
			(
				'name' => '使用次数',
				'order_by' => 'total_count',
			),		
			'member_count' => array
			(
				'name' => '标签名称',
				'order_by' => 'name',
			),	
		);
		
		$order_condition = order
		(
			$order_by_list,
			"admin.php?mod=tag&keyword=".urlencode($keyword),
			array('display_un_href'=>true)
		);
		
		$sql="SELECT * 
		FROM ".TABLE_PREFIX."tag 
		$where 
		{$order_condition['order']} 
		limit $offset,$per_page_num";
		
		$query = $this->DatabaseHandler->Query($sql);
		$tag_list=array();
		while ($row=$query->GetRow()) 
		{
			
			$row['dateline']=my_date_format($row['dateline']);
			$row['tag_html'] = $this->_tag_html($row['id'],$row['status']);
			
			$tag_list[]=$row;
		}

		$pages=page($total,$per_page_num,'',array(),"20 50 100 200 300");
		
		$tag_item_list = ConfigHandler::get('tag','item_list');
		
		$tag_num = ConfigHandler::get('tag_num');
		foreach ($tag_item_list as $key=>$val)
		{
			$val["selected_" . (int) $tag_num[$val['value']]] = " selected ";
			
			$tag_item_list[$key] = $val;
			
		}
		
		include($this->TemplateHandler->Template('admin/tag'));
	}
	
	function delete()
	{
		$_tmp_arr = (array) ($this->Get['id'] ? $this->Get['id'] : $this->Post['delete']);
		$id_arr = array();
		foreach ($_tmp_arr as $_id) {
			$_id = (int) $_id;
			if($_id > 0) {
				$id_arr[$_id] = $_id;
			}
		}		
		if(!$id_arr) {
			$this->Messager("未指定删除的标签");
		}
		
		$sql = "SELECT * FROM `".TABLE_PREFIX."tag` WHERE `id` in('".implode("','",$id_arr)."')";
		$query = $this->DatabaseHandler->Query($sql);
		$tag_id_list = $tag_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$id = (int) $row['id'];
			if ($id < 1) {
				continue ;
			}
			
			$tag_id_list[$id] = $id;
			$tag_list[$id] = $row['name'];			
		}
		if (!$tag_id_list) {
			$this->Messager("请指定删除的标签");
		}		
		$ids = "'".implode("','",$tag_id_list)."'";		
		
				$sql="DELETE FROM `".TABLE_PREFIX."tag` WHERE `id` IN($ids)";
		$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
		
				$sql="DELETE FROM `".TABLE_PREFIX."my_tag` WHERE `tag_id` IN($ids)";
		$this->DatabaseHandler->Query($sql,"SKIP_ERROR");	
			
				$item_list=ConfigHandler::get('tag','item_list');
		foreach ($item_list as $item)
		{
			$sql="DELETE FROM `".$item['table_name']."_tag` WHERE `tag_id` IN($ids)";
			$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
			
			$sql="DELETE FROM `".TABLE_PREFIX."my_{$item['value']}_tag` WHERE `tag_id` IN($ids)";
			$this->DatabaseHandler->Query($sql,"SKIP_ERROR");	

			foreach ($tag_list as $tag) {
				if (!$tag) {
					continue ;
				}
				
								
				$sql = "UPDATE `{$item['table_name']}` SET `tag`=TRIM(LEADING ',' FROM REPLACE(CONCAT_WS('',',',tag),',{$tag}','')) , `tag_count`=if(`tag_count`>1,`tag_count`-1,0) WHERE `tag` LIKE '%{$tag}%'";
				$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
			}
		}
		
		cache_clear();
		
		$this->Messager("删除成功");
	}

	function NumSetting()
	{		
		ConfigHandler::set('tag_num',$_POST['tag_num']);
		
		$this->Messager("设置成功",'admin.php?mod=tag');
	}
	
	function Tag_Status()
	{ 
		$this->DatabaseHandler->SetTable(TABLE_PREFIX.'tag');
		$tag_info = $this-> DatabaseHandler->Select($this->Get['tag_ids']);
		
		if($tag_info['status']){	
		  $tag_status = 0;
		}
		else{
			$tag_status = 1;
		}
				$uptag_sql = "update `".TABLE_PREFIX."tag` set `status`='{$tag_status}' where `id`='".(int) $this->Get['tag_ids']."'";
		$this->DatabaseHandler->Query($uptag_sql);
		
		$this->Messager("设置成功",'admin.php?mod=tag');
	}
	
		function _tag_html($tag_id,$status=0,$addhtml=true) {
	
		$html = "";
			if ($status) {		
							$html = "<a href='admin.php?mod=tag&code=tag_status&tag_ids={$tag_id}'><font color='#008080'>已推荐,点此取消</font></a>";
			} else {
							$html = "<a href='admin.php?mod=tag&code=tag_status&tag_ids={$tag_id}'><font color='#804040'>未推荐,点此推荐</font></a>";
			}
			if($addhtml) $html = "<span id='tag_{$tag_id}'>{$html}</span>";
			
		return $html;
	}
	
	
	function Extra()
	{
		$this->_init_extra();
		
		$page_link = 'admin.php?mod=tag&code=extra';
		
		$p = array(
			'fields' => ' `id`,`name` ',
			'per_page_num' => 30,
			'page_link' => $page_link,
		);
		
		$rets = $this->TagExtraLogic->get($p);
		
		
		$tag_extra_list = $rets['list'];
		$page_html = $rets['page']['html'];
		
		include($this->TemplateHandler->Template('admin/tag_extra'));
	}
	function AddExtra()
	{
		$name = $this->Post['name'] ? $this->Post['name'] : $this->Get['name'];
		$name = trim($name);
		if(!$name)
		{
			$this->Messager('请指定一个话题');
		}
		
		$tag_info = $this->DatabaseHandler->FetchFirst("select `id` from ".TABLE_PREFIX."tag where `name`='$name'");
		if(!$tag_info)
		{
			$this->Messager('您指定的话题已经不存在了');
		}
		$id = $tag_info['id'];
		
		$this->_init_extra();
		
		$tag_extra_info = $this->TagExtraLogic->get_info($id);
		if(!$tag_extra_info)
		{
			$ret = $this->TagExtraLogic->add($id, $name);
		}
		
		
		$this->Messager(null, "admin.php?mod=tag&code=modify_extra&id={$id}");
	}
	function ModifyExtra()
	{		
		$extra = $this->_extra_info();
		$id = $extra['id'];
		$data = $extra['data'];
		
		if($this->Post['modifysubmit'])
		{
			$_data = $this->Post['data'];	

			if($_data['right_top_image']['list'])
			{
				$rets = array();
				foreach($_data['right_top_image']['list'] as $v)
				{
					$v = trim($v);
					
					if($v)
					{
						$rets[] = $v;
					}
				}
				$_data['right_top_image']['list'] = $rets;
			}
			
			if($_data['right_top_video']['list'])
			{
				$rs = array();
				$rets = array();
				foreach($_data['right_top_video']['list'] as $v)
				{
					$v = trim($v);
					
					if($v)
					{
												$r = $this->_extra_video($v, $data);
						
						if($r)
						{
							$rs[] = $r;
							$rets[] = $v;
						}
					}
				}
				$_data['right_top_video']['rlist'] = $_data['right_top_video']['vlist'] = $rs;
				$_data['right_top_video']['list'] = $rets;
			}
			
			if($_data['right_top_user']['list'])
			{
				$rs = array();
				$rets = array();
				foreach($_data['right_top_user']['list'] as $v)
				{
					$v = trim($v);
					
					if($v)
					{
												$r = $this->_extra_user($v);
						
						if($r)
						{
							$rs[] = $r;
							$rets[] = $v;
						}
					}
				}
				$_data['right_top_user']['rlist'] = $rs;
				$_data['right_top_user']['list'] = $rets;
			}
						
			$ret = $this->TagExtraLogic->modify($id, $_data);
			
			$this->Messager('编辑成功');
		}
		
		
		
		Load::lib('form');
		$FormHandler = new FormHandler();
		
		
		$left_top_image_enable_radio = $FormHandler->YesNoRadio('data[left_top_image][enable]', (int) $data['left_top_image']['enable']);
		$left_top_text_enable_radio = $FormHandler->YesNoRadio('data[left_top_text][enable]', (int) $data['left_top_text']['enable']);
		$right_top_text_enable_radio = $FormHandler->YesNoRadio('data[right_top_text][enable]', (int) $data['right_top_text']['enable']);
		$right_top_image_enable_radio = $FormHandler->YesNoRadio('data[right_top_image][enable]', (int) $data['right_top_image']['enable']);
		$right_top_video_enable_radio = $FormHandler->YesNoRadio('data[right_top_video][enable]', (int) $data['right_top_video']['enable']);
		$right_top_user_enable_radio = $FormHandler->YesNoRadio('data[right_top_user][enable]', (int) $data['right_top_user']['enable']);		 
		
		include($this->TemplateHandler->Template('admin/tag_extra_info'));
	}
	function DeleteExtra()
	{
		$extra = $this->_extra_info();
		
		$ret = $this->TagExtraLogic->delete($extra['id']);
		
		$this->Messager('删除成功');
	}
	function _init_extra()
	{
		Load::logic('tag_extra');
		$this->TagExtraLogic = new TagExtraLogic();
	}
	function _extra_info($id = 0)
	{
		$id = $id ? $id : $this->ID;
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) $this->Messager('ID 不能为空');
		
		$this->_init_extra();
		
		$extra = $this->TagExtraLogic->get_info($id);
		if(!$extra)
		{
			$this->Messager("请指定一个正确的ID");
		}
		
		return $extra;
	}
	function _extra_video($url, $data)
	{
		$vid = abs(crc32($url));		
		
		if(NULL === ($ret = $data['right_top_video']['vlist'][$vid]))
		{
			$ret = $this->TopicLogic->_parse_video($url);
			if($ret)
			{
				$ret['vid'] = $vid;
				
				if($ret['image_src'])
				{
					;
				}
			}
		}
		
		return $ret;
	} 
	function _extra_user($username)
	{
		$username = jaddslashes(trim($username));
		
		$rets = array();
		if($username)
		{
			$sql_where = " where `username`='{$username}' or `nickname`='{$username}' limit 1 ";
			$sql_fields = " `uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`gender`,`face`,`nickname` ";
			
			$rets = $this->TopicLogic->GetMember($sql_where, $sql_fields);
		}
			
		$ret = array();
		if($rets)
		{
			foreach($rets as $row)
			{
				if($row)
				{
					$ret = $row;
				}
			}
		}
		
		return $ret;
	}
	
	
	function Recommend()
	{
		$hot_tag_recommend = ConfigHandler::get('hot_tag_recommend');
        if(!$hot_tag_recommend['name']) {
            $hot_tag_recommend = array(
                'enable' => 0,
                'name' => '热门话题推荐',
                'num' => 10,
                'list' => array(),
            );
            
            ConfigHandler::set('hot_tag_recommend',$hot_tag_recommend);
        }
        
        $hot_tag_recommend_enable_radio = $this->FormHandler->YesNoRadio('hot_tag_recommend[enable]',$hot_tag_recommend['enable']);
        $_options = array();
        for($i=1;$i<=20;$i++)
        {
            $_options[$i] = array('name'=>$i,'value'=>$i);
        }
        $hot_tag_recommend_num_select = $this->FormHandler->Select('hot_tag_recommend[num]',$_options,$hot_tag_recommend['num']);
        
        $query_link = "admin.php?mod=setting&code=modify_hot_tag_recommend";
        
        $per_page_num = min(200,max(20,(int) $this->Get['pn']));
                
        $total_record = $this->DatabaseHandler->ResultFirst("select count(*) as `count` from ".TABLE_PREFIX."tag_recommend");
        
        if($total_record > 0)
        {
            $page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'Array'),"20 30 50 100 200");
                    
            $sql = "select tr.*,t.topic_count from ".TABLE_PREFIX."tag_recommend tr left join ".TABLE_PREFIX."tag t on t.name=tr.name order by `order` desc , `id` desc {$page_arr[limit]}";
            $query = $this->DatabaseHandler->Query($sql);
            $hot_tag_recommend_list = array();
            while(false != ($row = $query->GetRow()))
            {
                $hot_tag_recommend_list[$row['id']] = $row;
            }
        }
		
		include($this->TemplateHandler->Template('admin/tag_recommend'));
	}
	
	function DoRecommend()
	{
		$act = ($this->Post['act'] ? $this->Post['act'] : $this->Get['act']);
        $timestamp = time();
        $uid = MEMBER_ID;
        $username = MEMBER_NAME;
        
        $messager = "";
        if('delete' == $act)
        {
            $id = max(0, (int) get_param('id'));
            
            $info = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."tag_recommend where `id`='$id'");
            if(!$info)
            {
                $this->Messager("你要删除的内容已经不存在了");
            }
            $name = $info['name'];
            
            $this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."tag_recommend where `id`='$id'");
            
            $this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag set `status`=0 where `name`='$name'");
            
            $messager = "删除成功";                       
        }
        else
        {
            $hot_tag_recommend_config = ConfigHandler::get('hot_tag_recommend');
            
            $_arr = $this->Post['hot_tag_recommend'];
            
            $name = ($_arr['name'] ? $_arr['name'] : "热门话题推荐");
            $num = min(20,max(1,(int) $_arr['num']));
            
            $hot_tag_recommend = array(
                'enable' => ($_arr['enable'] ? 1 : 0),
                'name' => $name,
                'num' => $num,
            	'time' => time(),
            );            
            
            
            if($_arr['list'])
            {                
                $_list = $this->Post['_list'];                
                foreach($_arr['list'] as $k=>$v)
                {
                    if($v != $_list[$k])
                    {
                        $v['enable'] = $v['enable'] ? 1 : 0;
                        
                        $_sets = array();
                        foreach($v as $_k=>$_v)
                        {
                            if($_v != $_list[$k][$_k])
                            {
                                $_sets[$_k] = "`{$_k}`='{$_v}'";
                            }
                        }
                        
                        if($_sets)
                        {
                            $_sets['last_update'] = "`last_update`='$timestamp'";
                            
                            $this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag_recommend set ".implode(" , ",$_sets)." where `id`='$k'");
                            if($v['enable']!=$_list[$k]['enable'])
                            {
                                $this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag set `status`='{$v['enable']}' where `name`='$name'");
                            }
                        }
                    }
                }
            }
            
            $_new_arr = $this->Post['hot_tag_recommend_new'];
            foreach($_new_arr as $k=>$v)
            {
                if(($name = $v['name']) && ($this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."tag where `name`='$name'")))
                {
                    $_enable = $v['enable'] ? 1 : 0;
                    $desc = $v['desc'];
                    $order = (int) $v['order'];
                    
                    $this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."tag_recommend (`enable`,`name`,`desc`,`order`,`dateline`,`uid`,`username`) values ('$_enable','$name','$desc','$order','$timestamp','$uid','$username')");
                    
                    $this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag set `status`='$_enable' where `name`='$name'");
                }
            }                        
            
            
            $__list = array();
            if($hot_tag_recommend[num] > 0)
            {
                $sql = "select tr.*,t.id as tag_id,t.topic_count from ".TABLE_PREFIX."tag_recommend tr left join ".TABLE_PREFIX."tag t on t.name=tr.name where tr.enable=1 order by `order` desc , `id` desc limit {$hot_tag_recommend['num']}";
                $query = $this->DatabaseHandler->Query($sql);
                while(false != ($row = $query->GetRow()))
                {
                    $__list[$row['id']] = $row;
                }               
            }                
            $hot_tag_recommend['list'] = $__list;
            
            
            if($hot_tag_recommend_config != $hot_tag_recommend)
            {
                ConfigHandler::set('hot_tag_recommend',$hot_tag_recommend);
                
                if($hot_tag_recommend['enable'] != $this->Config['hot_tag_recommend_enable'])
                {
                    $config = array();
					$config['hot_tag_recommend_enable'] = $hot_tag_recommend['enable'];
                    
                    ConfigHandler::update($config);
                }
            }            
            
            
            $messager = "设置成功";
        }
        
        
        $this->Messager($messager);
	}
	
}

?>
