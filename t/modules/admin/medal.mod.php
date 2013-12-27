<?php
/**
 * 文件名：topic.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年9月28日 14时10分42秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博模块
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
			case 'delete':				$this->Delete();
				break;		
			case 'add':				$this->Add();
				break;
			case 'modify':				$this->Modify();
				break;
			case 'domodify':				$this->DoModify();
				break;
			case 'verify':				$this->verify();
				break;
			case 'doverify':				$this->doVerify();
				break;
			case 'user':				$this->DoView();
				break;
			case 'delmedaluser':				$this->DelMdealUser();
				break;
			case 'isopen':				$this->IsOpen();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function Main()
	{

		$sql = "select * from `".TABLE_PREFIX."medal`";
	 	$query = $this->DatabaseHandler->Query($sql);
		$medal_list=array();
	  	$_select = array();
		while($row=$query->GetRow())
		{	
			$medal_list[]=$row;
		}
		
		include $this->TemplateHandler->Template('admin/medal');
	}
	
		function verify(){
		
				$sql = "select id,medal_name as name from ".TABLE_PREFIX."medal order by id";
		$query = $this->DatabaseHandler->Query($sql);
		$all_medal_list = array();
		while ($rsdb = $query->GetRow()){
			$all_medal_list[$rsdb['id']] = $rsdb['name'];
		}
		
				$url = '';
		$medal_id = $this->Post['medal_id']?$this->Post['medal_id']:$this->Get['medal_id'];
		if($medal_id){
			$where .= " and a.medal_id = '$medal_id' "; 
			$url = "&medal_id=$medal_id";
		}
		$nickname = $this->Post['nickname']?$this->Post['nickname']:$this->Get['nickname'];
		if($nickname){
			$where .= " and a.nickname like '%$nickname%'";		
			$url .="&nickname=$nickname";	
		}
		
				$_config = array('return' => 'array',);
		$per_page_num = 20;
		$page_url = "admin.php?mod=medal&code=verify".$url;
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."medal_apply a where 1 $where ");
		$page_arr = page($count,$per_page_num,$page_url,$_config);
		
		$sql = "select * from ".TABLE_PREFIX."medal_apply a 
				left join ".TABLE_PREFIX."medal m on m.id = a.medal_id 
				where 1 
				$where 
				order by a.dateline 
				$page_arr[limit]";
		$query = $this->DatabaseHandler->Query($sql);
		$medal_list = array();
		while ($rsdb = $query->GetRow()){
			$medal_list[$rsdb['apply_id']] = $rsdb;
		}
		include $this->TemplateHandler->Template('admin/medal_verify');
	}
	
		function doVerify()
	{
		$uid = (int) $this->Get['uid'];
		$medal_id = (int) $this->Get['medal_id'];
		$action = $this->Get['action'];
		$timestamp = time();
		
				if($action == 'yes'){
		        		$sql = " select count(*) from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medal_id}' and `uid` = '{$uid}'";
    		$count = $this->DatabaseHandler->ResultFirst($sql);
			if($count<1){
        		$sql = "insert into `".TABLE_PREFIX."user_medal` (
        					`uid`,
        					`nickname`,
        					`medalid`,
        					`dateline`) 
        				values (
        					'{$uid}',
        					'".MEMBER_NICKNAME."',
        					'{$medal_id}',
        					'{$timestamp}')";
    			$query = $this->DatabaseHandler->Query($sql);
			}
	 			 		$sql = "select * from `".TABLE_PREFIX."user_medal` where `uid` = '{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$user_medal = array();
			while (false != ($row = $query->GetRow())) {
				$user_medal[] = $row['medalid'];
			}
			$new_medal_id = implode(",",$user_medal);
			$sql = "update `".TABLE_PREFIX."members` set  `medal_id`='{$new_medal_id}'  where `uid` = '{$uid}'";	
			$this->DatabaseHandler->Query($sql);
		}
				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."medal_apply where `medal_id` = '{$medal_id}' and `uid` = '{$uid}'");
		
		$this->Messager("设置用户勋章成功",'admin.php?mod=medal&code=verify');
			
	}
	
		function Add()
	{
		
		if(empty($this->Post['postFlag']))
		{
			$TITLE_LIST = "添加";
			$action = 'admin.php?mod=medal&code=add';
			
			if (empty($this->Post['type'])) {
				$this->Messager("请选择类别",-1);
			}
			
			$chactype = $this->Post['type'];

			include $this->TemplateHandler->Template('admin/medal_info');	
		}
		else
		{
			
				$error_msg = '';
				$field = 'medal';
				$datetime = time();
				if(empty($_FILES) || !$_FILES[$field]['name'])
				{
						$this->Messager("请设置图片",-1);
				}
				
				
				$medal_name 	= $this->Post['medal_name'];
				$medal_depict = $this->Post['medal_depict'];
				
			  
			   $checkvalue = array(
  							'type'		=>	$this->Post['type'],
  							'day'		=>	$this->Post['day'],
  							'endday'	=>	$this->Post['endday'],
  							'tagname'	=>	$this->Post['tagname'],
  							'invite'	=>  $this->Post['invite'],
   							'fans'		=>  $this->Post['fans'],
   							'sign'		=>	$this->Post['sign'],
			  				);
			 
				$checkvalue = serialize($checkvalue);
				
				$sql = "insert into `".TABLE_PREFIX."medal`(`medal_name`,`medal_depict`,`conditions`,`dateline`) values ('{$medal_name}','{$medal_depict}','{$checkvalue}','{$datetime}')";
				$this->DatabaseHandler->Query($sql);
				$image_id = $this->DatabaseHandler->Insert_ID();
				
				
				

				$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/'.$datetime.'/';
				$image_name = $image_id . "_o.jpg";
				$image_file = $image_path . $image_name;
				$image_file_small = $image_path.$image_id . "_s.jpg";
				
				if (!is_dir($image_path)) {
					Load::lib('io', 1)->MakeDir($image_path);
				}
	
				Load::lib('upload');
				$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true);
				$UploadHandler->setMaxSize(2048);
				$UploadHandler->setNewName($image_name);
				$result=$UploadHandler->doUpload();
				
				if($result) {
					$result = is_image($image_file);
				}
	
				if (!$result) {
					$this->Messager("上传图片失败","admin.php?mod=medal");
				}
				makethumb($image_file,$image_file_small,60,60,0,0,0,0,0,0);
				
	        	Load::lib('image');
	       	 	$image = new image();
	       	 	$image->Thumb($image_file,$image_file,60,60);
	       	 	$image->Thumb($image_file_small,$image_file_small,60,60);
	       	 	
				$image_file = addslashes($image_file);				$image_file_small = addslashes($image_file_small);					
								$img = grayJpeg($image_file_small);
				imagejpeg($img,$image_file_small,100);
				imagedestroy($img);
					
				$sql = "update `".TABLE_PREFIX."medal` set `medal_img`='{$image_file}',`medal_img2`='{$image_file_small}' where `id`='{$image_id}'";
				$this->DatabaseHandler->Query($sql);

				$this->Messager("添加成功",'admin.php?mod=medal');
			}
			
	}
	
	
	function Modify()
	{	 
		 $TITLE_LIST = "编辑";
		 $action = "admin.php?mod=medal&code=domodify";
		 $cheack_type = $this->Post['type'];
		 
		 $datetime = time();
		
		 for($i=1; $i <= 30; $i++) 
		 { 
		 		$option .= "<option value='".$i."'>$i</option>" ; 
		 }
 
		
		 $ids = max(0, (int) $this->Get['ids']);
		 if(!$ids) $this->Messager("请指定一个ID",null);
		
		 $sql="SELECT * FROM ".TABLE_PREFIX.'medal'." WHERE id='$ids'";
		 $query = $this->DatabaseHandler->Query($sql);
		 $medal_info=$query->GetRow();
		
		 $chackvalue = unserialize($medal_info['conditions']);
		 $chactype = $chackvalue['type'];
		 if($medal_info==false) 
		 {
		 		$this->Messager("您要编辑的信息已经不存在!");
		 }


		 include $this->TemplateHandler->Template('admin/medal_info');
		 
	}
	
		function DoModify(){
		$medal_id = jget('medal_id', 'int');
		$sql="SELECT * FROM ".TABLE_PREFIX.'medal'." WHERE id='$medal_id'";
	    $query = $this->DatabaseHandler->Query($sql);
	    $medal_info=$query->GetRow();
				 
		$error_msg = '';
		$field = 'medal';
	
		$datetime = time();
		$medal_name 	= $this->Post['medal_name'];
		$medal_depict = $this->Post['medal_depict'];

	    $checkvalue = array(
	  											'type'				=>	$this->Post['type'],
	  											'day'				=>	$this->Post['day'],
	  											'endday'			=>	$this->Post['endday'],
	  											'tagname'			=>	$this->Post['tagname'],
	  											'invite'			=>  $this->Post['invite'],
	  											'fans'				=>  $this->Post['fans'],
	    										'sign'				=>  $this->Post['sign'],
	  										);
		
		$checkvalue = serialize($checkvalue);
		
		$medal_name 	= $this->Post['medal_name'];
		$medal_depict = $this->Post['medal_depict'];

	
		if(empty($_FILES) || !$_FILES[$field]['name'])
		{
				 $image_file = $medal_info['medal_img'];
				 $image_file_small = $medal_info['medal_img2'];
		} 
		else
		{
				
				
				
								Load::lib('io', 1)->DeleteFile($medal_info['medal_img']);
       			Load::lib('io', 1)->DeleteFile($medal_info['medal_img2']);
        
				$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/'.$datetime.'/';
				
				$image_name = $medal_info['id'] . "_o.jpg";
				$image_file = $image_path . $image_name;
				$image_file_small = $image_path.$medal_info['id'] . "_s.jpg";
				
				if (!is_dir($image_path)) {
					Load::lib('io', 1)->MakeDir($image_path);
				}
	
				Load::lib('upload');
				$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true);
				$UploadHandler->setMaxSize(2048);
				$UploadHandler->setNewName($image_name);
				$result=$UploadHandler->doUpload();
				
				if($result) {
					$result = is_image($image_file);
				}
	
				if (!$result) {
					$this->Messager("上传图片失败","admin.php?mod=medal");
				}
				makethumb($image_file,$image_file_small,60,60,0,0,0,0,0,0);
				
	        	Load::lib('image');
	       	 	$image = new image();
	       	 	$image->Thumb($image_file,$image_file,60,60);
	       	 	$image->Thumb($image_file_small,$image_file_small,60,60);
	       	 	
				$image_file = addslashes($image_file);				$image_file_small = addslashes($image_file_small);					
								$img = grayJpeg($image_file_small);
				imagejpeg($img,$image_file_small,100);
				imagedestroy($img);
	
		}			
	
		$sql = "update `".TABLE_PREFIX."medal` set  `medal_img`='{$image_file}' ,`medal_img2` = '{$image_file_small}', `medal_name`='{$medal_name}' ,`medal_depict` = '{$medal_depict}' , `conditions` = '{$checkvalue}'   where `id`='".(int)$this->Post['medal_id']."'";	
		$this->DatabaseHandler->Query($sql);

		if ($error_msg) {
			$this->Messager($error_msg);
		}
		$this->Messager("编辑成功",'admin.php?mod=medal');
		
	}

	
	
	
		function DoView()
	{
				$sql = "select id,medal_name as name from ".TABLE_PREFIX."medal order by id";
		$query = $this->DatabaseHandler->Query($sql);
		$all_medal_list = array();
		while ($rsdb = $query->GetRow()){
			$all_medal_list[$rsdb['id']] = $rsdb['name'];
		}
		
				$url = '';
		$medal_id = $this->Post['medal_id']?$this->Post['medal_id']:$this->Get['medal_id'];
		if($medal_id){
			$where .= " and a.medalid = '$medal_id' "; 
			$url = "&medal_id=$medal_id";
		}
		$nickname = $this->Post['nickname']?$this->Post['nickname']:$this->Get['nickname'];
		if($nickname){
			$where .= " and a.nickname like '%$nickname%'";		
			$url .="&nickname=$nickname";	
		}
		
				$_config = array('return' => 'array',);
		$per_page_num = 20;
		$page_url = "admin.php?mod=medal&code=user".$url;
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."user_medal a where 1 $where ");
		$page_arr = page($count,$per_page_num,$page_url,$_config);
		
		$sql = "select 
					a.medalid as medal_id, 
					a.uid, 
					a.nickname, 
					a.dateline, 
					i.medal_img, 
					i.medal_name as name, 
					i.conditions
			    from ".TABLE_PREFIX."user_medal a 
			    left join ".TABLE_PREFIX."medal i on i.id = a.medalid 
				where 1 $where order by a.dateline desc,a.uid 
				$page_arr[limit] ";
		$query = $this->DatabaseHandler->Query($sql);
		$medal_list = array();
		while ($rsdb = $query->GetRow()){
			$rsdb['conditions'] = unserialize($rsdb['conditions']);
			$medal_list[] = $rsdb;
		}
		include $this->TemplateHandler->Template('admin/medal_view');
	}
	
	
	
		function Delete()
	{
		$ids = (int) $this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids'];
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		
				$query = $this->DatabaseHandler->Query("select medal_img,medal_img2 from ".TABLE_PREFIX."medal where id = '$ids'");
		$medal_img = $query->GetRow();
		if($medal_img){
			unlink($medal_img['medal_img']);
			unlink($medal_img['medal_img2']);
		}
		
				$sql = "select * from `".TABLE_PREFIX."members` where `medal_id` in ('$ids') ";
		$query = $this->DatabaseHandler->Query($sql);
		$uids = array();
		while (false != ($row = $query->GetRow())) {
			$uids[] = $row['uid'] ;
		}
		
		if($uids){
    		    		$return = $this->_Update_Medal_Id($uids,$ids);
		}
				$sql="delete from `".TABLE_PREFIX."medal` where `id` = '{$ids}'";
		$this->DatabaseHandler->Query($sql);	
		
				$sql = "delete from `".TABLE_PREFIX."user_medal` where `medalid` = '{$ids}'";
		$this->DatabaseHandler->Query($sql);
		
	
				$return = $this->_MedalCount($ids);
		
		$this->Messager($return ? $return : "操作成功");
		
	}
	
		function DelMdealUser()
	{
		$uid = (int) $this->Get['uid'];
		$medalid = (int) $this->Get['medal_id'];
		
		
		if(empty($uid)) {
			$this->Messager("请指定要删除的对象");
		}

				$sql = "delete from `".TABLE_PREFIX."user_medal` where `uid` = '$uid' and `medalid` = '{$medalid}'";	
		$this->DatabaseHandler->Query($sql);

				$sql = "select medalid from ".TABLE_PREFIX."user_medal where uid = '$uid'";
		$query = $this->DatabaseHandler->Query($sql);
		$id_arr = array();
		while ($rsdb = $query->GetRow()){
			$id_arr[$rsdb['medalid']] = $rsdb['medalid'];
		}
		if($id_arr){
			$new_medal_id = implode(",",$id_arr);
		}else{
			$new_medal_id = '';
		}
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set medal_id = '$new_medal_id' where uid = '$uid'");
		
				$return = $this->_MedalCount($medalid);
		
		$this->Messager("勋章摘除成功",'admin.php?mod=medal&code=user');
	}
	
		function Add_Mdeal_User()
	{
		 $nickname = $this->Post['nickname'];
		 $medal_id = (array) $this->Post['medal_id'];
		 $dateline = time();
		 if(empty($nickname))
		 {
		 	 $this->Messager("用户昵称不能为空！");
		 }
		 if(empty($medal_id))
		 {
		 	$this->Messager("勋章类别不能为空");
		 }
 		 
 		
		 $sql = "select `uid`,`medal_id`,`nickname`,`face` from `".TABLE_PREFIX."members` where `nickname` = '{$nickname}'";
		 $query = $this->DatabaseHandler->Query($sql);
		 $members=$query->GetRow();

		 if(empty($members)){
	 	 	 $this->Messager("输入的昵称 <font color='#ff0000'> {$nickname} </font>不存在"); 
	 	 }	
	
		
		    	for ($i = 0; $i < count($medal_id); $i++) {
    		
        	    		$sql = " select * from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medal_id[$i]}' and `uid` = '{$members['uid']}'";
    		$query = $this->DatabaseHandler->Query($sql);
    		$row = $query->GetRow();
    		
			if(empty($row))
			{
        		$sql = "insert into `".TABLE_PREFIX."user_medal` (`uid`,`nickname`,`medalid`,`dateline`) values ('{$members['uid']}','{$members['nickname']}','{$medal_id[$i]}','{$dateline}')";
    			$query = $this->DatabaseHandler->Query($sql);
    			
    			    			$return = $this->_MedalCount($medal_id[$i]);
			}
    	}

 		 		$sql = "select * from `".TABLE_PREFIX."user_medal` where `uid` = '{$members['uid']}'";
		$query = $this->DatabaseHandler->Query($sql);
		$user_medal = array();
		while (false != ($row = $query->GetRow())) {
			$user_medal[] = $row['medalid'];
		}
		
		$new_medalid = implode(",",$user_medal);
		
		$sql = "update `".TABLE_PREFIX."members` set  `medal_id`='{$new_medalid}'  where `uid` = '{$members['uid']}'";	
		$this->DatabaseHandler->Query($sql);
		
		$this->Messager("编辑成功",'admin.php?mod=medal');
			
	}
	
	
		function _MedalCount($medalid=0)
	{
		if($medalid){	
    		    		$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medalid}'";
    		$total_record = DB::result_first($sql);
    		
    		    		$sql = "update `".TABLE_PREFIX."medal` set  `medal_count`='{$total_record}'  where `id` = '{$medalid}'";	
    		$this->DatabaseHandler->Query($sql);
		}else{
		
			$this->Messager("勋章已删除",'admin.php?mod=medal');
		}	
	}

	function IsOpen()
	{
		$medalid = (int) $this->Get['ids'];	
		
		$sql = "select * from `".TABLE_PREFIX."medal` where `id` = '{$medalid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$row = $query->GetRow();
		
		$is_open = $row['is_open'] ? 0 : 1;
		
		$sql = "update `".TABLE_PREFIX."medal` set  `is_open`='{$is_open}'  where `id` = '{$medalid}'";	
		$this->DatabaseHandler->Query($sql);
		
		$this->Messager("编辑成功",'admin.php?mod=medal');
		
	}
	
	
	
	
	

		
}

?>
