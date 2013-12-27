<?php
/**
 * 文件名：user_tag.mod.php
 * 版本号：1.0
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 个性标签模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;	

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		
		$this->TopicLogic = Load::logic('topic', 1);
		
		Load::lib('form');
		$this->FormHandler = new FormHandler;
		
		$this->CacheConfig = ConfigHandler::get('cache');
		
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{	
			case 'delete':
				$this->Delete();
				break;			
			case 'add':
				$this->Add();
				break;
			case 'modify':
				$this->Modify();
				break;
		  case 'domodify':
				$this->DoModify();
				break;
			default:
				$this->Code = 'user_tag_manage';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	
	function Main()
	{ 
		
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],10));
		$where_list = array();
		$query_link = 'admin.php?mod=user_tag';
		
		$tagname = $this->Get['tagname'];
		if ($tagname) {
			$_GET['highlight'] = $tagname;

			$where_list['keyword'] = build_like_query('name',$tagname);
			$query_link .= "&keyword=".urlencode($tagname);
			$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';
			
		}
		
		$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."user_tag` {$where}";
		$total_record = DB::result_first($sql);
		
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200,500');		

		$sql = "select * from `".TABLE_PREFIX."user_tag` {$where} order by `id` desc {$page_arr['limit']}";
	 	$query = $this->DatabaseHandler->Query($sql);
	  $user_tag_list=array();
		while(false != ($row = $query->GetRow()))
		{	
			$user_tag_list[]=$row;
		}
		
		
		
		include($this->TemplateHandler->Template('admin/user_tag'));
	}

	function Add()
	{		
		$tagname = strip_tags($this->Post['tagname']);
		$addTime = time();
		
		if(empty($tagname))
		{
			$this->Messager("请输入标签名称",-1);
		}
		
		$sql = "select * from `".TABLE_PREFIX."user_tag` where `name` = '{$tagname}' ";
	 	$query = $this->DatabaseHandler->Query($sql);
	  $taglist=$query->GetRow();
		
		if($taglist)
		{
			$this->Messager("{$tagname} 标签已经存在",-1);
		}
		
		$sql = "insert into `".TABLE_PREFIX."user_tag`(`name`,`dateline`) values ('{$tagname}','{$addTime}')";
		$this->DatabaseHandler->Query($sql);

		$this->Messager("添加成功",'admin.php?mod=user_tag');
	}
	
	function Modify()
	{ 
		$ids = (int) $this->Get['ids'];
		
		$action = "admin.php?mod=user_tag&code=domodify";
		
		$sql = "select * from `".TABLE_PREFIX."user_tag` where `id` = '{$ids}' ";
	 	$query = $this->DatabaseHandler->Query($sql);
	  $taglist=$query->GetRow();
	  
		include $this->TemplateHandler->Template('admin/user_tag_info');
	}
	
	function DoModify()
	{
		$tagid = (int) $this->Post['tagid'];
		$tagname = strip_tags($this->Post['tagname']);
		
		$sql = "select * from `".TABLE_PREFIX."user_tag` where `name` = '{$tagname}' ";
	 	$query = $this->DatabaseHandler->Query($sql);
	  $taglist=$query->GetRow();
	  
		if($taglist)
		{
			$this->Messager("{$tagname} 标签已经存在",-1);
		}
		
		$sql = "update `".TABLE_PREFIX."user_tag` set  `name` = '{$tagname}'  where `id`='$tagid'";	
		$this->DatabaseHandler->Query($sql);	
		
		$this->Messager("编辑成功",'admin.php?mod=user_tag');
	}
	
	function Delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		$media_count = count($ids);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		
		$sql = "delete from `".TABLE_PREFIX."user_tag` where `id` in(".jimplode($ids).")";
		$this->DatabaseHandler->Query($sql);			
		
		
		$sql = "delete from `".TABLE_PREFIX."user_tag_fields` where `tag_id` in(".jimplode($ids).")";
		$this->DatabaseHandler->Query($sql);	
		
		$this->Messager($return ? $return : "操作成功");
		
	}

}

?>
