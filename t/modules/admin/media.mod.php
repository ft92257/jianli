<?php
/**
 * 文件名：media.mod.php
 * 版本号：1.0
 * 最后修改时间：2010-7-22 14:48
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 媒体汇模块
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
		$this->FormHandler = new FormHandler();
		
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
			case 'view':
				$this->View();
				break;
			case 'del_media':
				$this->Del_media();
				break;
			case 'media_user':
				$this->Add_MedieUser();
				break;	
			case 'oredr':
				$this->Order();
				break;
			default:
				$this->Code = 'media_manage';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function Main()
	{
		$sql = "select `id`, `media_name`,`media_count`,`order` from `".TABLE_PREFIX."media` order by `id` desc";
	 	$query = $this->DatabaseHandler->Query($sql);
		$media_list=array();
	  	$_select = array();
		while($row=$query->GetRow())
		{	
			$media_list[]=$row;	
			$_select[$row['media_name']] = array('name' => $row['media_name'], 'value' => $row['id']);
		}
	
		$media_select = $this->FormHandler->Select('media_id',$_select);
		
		
		include $this->TemplateHandler->Template('admin/media');
	}
	

  	function Add()
	{   
		 $media_name  = $this->Post['media_name'];
			
		 if(empty($media_name))
		 {
		 		$this->Messager("媒体汇名称不能为空");	
		 }
				
		$sql = "insert into `".TABLE_PREFIX."media`(`media_name`) values ('{$media_name}')";
		$this->DatabaseHandler->Query($sql);
		
		$this->Messager("添加成功",'admin.php?mod=media');
			
	}
	
		function Add_MedieUser()
	{		
		 $media_id = (int) $this->Post['media_id'];
		 $NickName = $this->Post['nickname'];
		 
		 if(empty($NickName))
		 {
		 		$this->Messager("昵称不能为空");	
		 }
		 	 
		 $sql = "select `uid`,`ucuid`,`media_id`,`nickname`,`face` from `".TABLE_PREFIX."members` where nickname = '{$NickName}'";
	 	 $query = $this->DatabaseHandler->Query($sql);
	 	 $MedieUser = $query->GetRow();
	 	 
	 	 if(empty($MedieUser))
	 	 {
	 	 	 $this->Messager("输入的昵称 <font color='#ff0000'> {$NickName} </font>不存在");
	 	 }
	  	 		
	 	 if($MedieUser['media_id'] == $media_id)
	 	 {
	 	 		$this->Messager("用户已经在其中，不需要在添加");
	 	 }
	 	
	 	 
	 	 $sql = "update `".TABLE_PREFIX."members` set  `media_id`='{$media_id}'  where `uid`='".$MedieUser['uid']."'";	
		 $this->DatabaseHandler->Query($sql);

		 if($media_id  != $MedieUser['media_id'])
		 { 
			 
		 	 $sql = "update `".TABLE_PREFIX."media` set  `media_count` = if(`media_count`>1,`media_count`-1,0)  where `id`='".$MedieUser['media_id']."'";	
			 $this->DatabaseHandler->Query($sql);
		 }
		 
		 $sql = "update `".TABLE_PREFIX."media` set  `media_count` = `media_count` + 1  where `id`='".$media_id."'";	
		 $this->DatabaseHandler->Query($sql);
		
	 	 $this->Messager("添加成功",'admin.php?mod=media');
	 	 
	}
	
	function Modify()
	{	
		 $ids = max(0, (int) $this->Get['ids']);
		 if(!$ids) $this->Messager("请指定一个ID",'admin.php?mod=media');
		 
		 $TITLE_LIST = "编辑";
		 $action = "admin.php?mod=media&code=domodify";
		 
		 $sql="SELECT * FROM ".TABLE_PREFIX.'media'." WHERE id='{$ids}' ";
		 $query = $this->DatabaseHandler->Query($sql);
		 $media_info=$query->GetRow();
		
		 if($media_info==false) 
		 {
		 	$this->Messager("您要编辑的信息已经不存在!");
		 }
 
		 $media_id = $media_info['id'];
		 $media_name = $media_info['media_name'];
		 $order = $media_info['order'];
		 

		 		 $per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],10));
		 if($_GET['pn']) $pn = '&pn='.$_GET['pn'];
		 $where_list = array();
		 $query_link = "admin.php?mod=media&code=modify&ids={$ids}".$pn;
		 
		 		 $sql = " select count(*) as `total_record` from `".TABLE_PREFIX."members`  where `media_id` = '{$ids}' ";
		 $total_record = DB::result_first($sql);
		
		 $page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'10 20 50 100 200,500');		

		 $sql="select `uid`,`username`,`nickname`,`media_id` from ".TABLE_PREFIX.'members'." where `media_id` = '{$ids}' {$page_arr['limit']}";
		 $query = $this->DatabaseHandler->Query($sql);
		 $media_user = array();
		 while(false != ($row = $query->GetRow()))
		 {	
		 	$media_user[] = $row;
		 }
		 
			 	$media_count = count($media_user);
	
		if($total_record != $media_info['media_count'])
		{
			$sql = "update `".TABLE_PREFIX."media` set  `media_count` = '{$total_record}'  where `id`= '{$ids}' ";	
			$this->DatabaseHandler->Query($sql);	
		}

		 include $this->TemplateHandler->Template('admin/media_info');
		 
	}
	
	
		function Del_media()
	{
		$media_ids = (int) $this->Post['media_ids'];
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		$media_count = count($ids);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		
		$sql = "update `".TABLE_PREFIX."members` set  `media_id`='0'  where `uid` in('".implode("','",$ids)."')";	
		$this->DatabaseHandler->Query($sql);			
		
		
		$sql = "update `".TABLE_PREFIX."media` set  `media_count` = if(`media_count`>1,`media_count`-{$media_count},0)  where `id`='".$media_ids."'";	
		$this->DatabaseHandler->Query($sql);
	  
	  
		$this->Messager($return ? $return : "操作成功");
	
	}
	
	
	function DoModify()
	{	
		$order = $this->Post['order'];
		$media_id   = (int) $this->Post['media_id'];
		$media_name = trim($this->Post['media_name']);
		
	
		if(empty($media_name))
    	{
    	 		$this->Messager("媒体汇名称不能为空");	
    	}
		
		if (!preg_match("/^\d*$/", $order)) {
			$this->Messager("只允许数字","admin.php?mod=media&code=modify&ids={$media_id}");
		}
		
		$sql = "update `".TABLE_PREFIX."media` set  `media_name`='{$media_name}'  where `id`='".$media_id."'";	
		$this->DatabaseHandler->Query($sql);

		$this->Messager("编辑成功",'admin.php?mod=media');
		
	}
	
	
  	function Delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		$media_count = count($ids);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		
		$sql = "delete from `".TABLE_PREFIX."media` where `id` in(".jimplode($ids).")";
		$this->DatabaseHandler->Query($sql);			
		
		
		$this->Messager($return ? $return : "操作成功");
		
	}
	
		function Order() {
		
		$mdeiaid = (int) $this->Post['mdeiaid'];
		$order =  (int) $this->Post['order'];
		
								if($order)
		{
    		$sql = "update `".TABLE_PREFIX."media` set  `order`='{$order}'  where `id`='".$mdeiaid."'";	
    		$this->DatabaseHandler->Query($sql);
		}
	
	}
		
}

?>
