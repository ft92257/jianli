<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename vipintro.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-29 18:47:48 738213669 84938765 37253 $
 *******************************************************************/




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
			case 'addvip':
				$this->addVip();
				break;
			case 'doaddvip':
				$this->doAddVip();
				break;
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
			
				

						case 'categorylist':
				$this->CategoryList();
				break;	

						case 'category':
				$this->DoCategory();
				break;
				
						case 'modifycategory':
				$this->ModifyCategory();
				break;
			
			 			case 'categoryclass':
				$this->DoCategoryClass();
				break;	
				
						case 'modifycategoryclass':
				$this->ModifyCategoryClass();
				break;	
				
						case 'memberview':
				$this->MemberView();
				break;	

				
						case 'people_setting':
				$this->People_Setting();
				break;	
				
						case 'people':
				$this->DoPeople();
				break;	
						
						case 'validate_setting':
				$this->Validate_Setting();
				break;	
						case 'validate':
				$this->DoValidate();
				break;	
	
			case 'check_category':
				$this->CheckCategory();
				break;		

			case 'tuijian':
				$this->DoTuiJian();
				break;		

						case 'insert_validate_user':
				$this->Insert_Validate_User();
				break;	
			
			case 'delcategory':
				$this->delCategory();
				break;
			default:
				$this->Code = 'vipintro_manage';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function addVip(){
				$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = ''";
		$query = $this->DatabaseHandler->Query($sql);
		$category_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$category_list[] = $row;
		}
		
		include template('admin/admin_add_vip');
	}
	function doAddVip(){
		$nickname = trim($this->Post['nickname']);
		$nickname_arr = array();
		if(!$nickname){
			$this->Messager('请输入用户昵称',-1);
		} else {
			$nickname_arr = explode("\r\n",$nickname);
		}
		
		$category_fid = (int) $this->Post['category_fid'];
		$category_id =  (int) $this->Post['category_id'];
		
		if(empty($category_id))
		{
			$this->Messager("请选择分类",-1);
		}
		#添加失败的昵称
		$f_name = array();
		$true = false;
		if($nickname_arr){
			foreach ($nickname_arr as $k=>$name) {
				$member_info = DB::fetch_first(" select `uid`,`city`,`province` from `".TABLE_PREFIX."members` where `nickname`='$name'");
				$uid = $member_info['uid'];
				if($uid < 1) {
					$f_name[] = $name.':不存在';
					continue;
				}
				$validate_info = DB::fetch_first("select * from ".DB::table('validate_category_fields')." where `uid`='$uid' ");
				
				if(empty($validate_info))
				{					
					$province_info = DB::result_first("select `id` from ".DB::table('common_district')." where `name`='".$member_info['province']."' ");
					$city_info = DB::result_first("select `id` from ".DB::table('common_district')." where `name`='".$member_info['city']."' ");
					
					 					$sql = "update `".TABLE_PREFIX."members` set `validate` = '$category_fid',`validate_category` = '{$category_id}' where `uid`='$uid'";					
					$this->DatabaseHandler->Query($sql);
		
										$data = array(
		
						'uid' 			=> $uid,
						'category_fid'  => $category_fid,
						'category_id'   => $category_id,
						'province' 		=> $province_info['id'],
						'city'			=> $city_info['id'],
						'validate_info' => '',
						'is_audit'		=> 1,
						'audit_info'	=> '',
						'order'			=> '',
						'is_push'		=> 0,
						'dateline'	    => Time(),
		
					);
					DB::insert('validate_category_fields',$data);
					$true = true;
				} else {
					$f_name[] = $name.':已认证';
				}
			}
		}
		
		if($true){
			if($f_name){
				$msg = '添加成功，其中'.implode('<br>',$f_name);
				$this->Messager($msg);
			} else {
				$this->Messager('添加成功');
			}
		}
		
		$this->Messager("添加失败",-1);
	}

	function Main()
	{
		$act = 'vipintro';
		
				$type = $this->Get['type'] = $this->Get['type'] ? $this->Get['type'] : 0;
		$wherelist =  " where 1 ";
		
				$typeid = get_param('typeids');
		if($typeid > 0){
			$wherelist .= " AND VCF.category_id = '$typeid' ";
					}
		
		$nickname = get_param('nickname');
		if($nickname){
			$wherelist .= " AND MB.nickname like '%$nickname%' ";
		} else {
			$wherelist .=  " and VCF.is_audit = '$type'" ;
		}

		$total_record = DB::result_first("select count(*) 
										  from `".TABLE_PREFIX."validate_category_fields` VCF 
										  left join `".TABLE_PREFIX."members` MB on VCF.uid=MB.uid 
										  {$wherelist} ");

		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],10));
		if($_GET['pn']) $pn = '&pn='.$_GET['pn'];
		$query_link = 'admin.php?mod=vipintro&type='.$type.$pn;
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'10 20 50 100 200 500');		

		$sql = "select  MB.uid,MB.nickname,VCF.* from `".TABLE_PREFIX."validate_category_fields` VCF 
		left join `".TABLE_PREFIX."members` MB on VCF.uid=MB.uid
		{$wherelist} order by VCF.dateline desc  {$page_arr['limit']}";
		$query = $this->DatabaseHandler->Query($sql);
		$category_list = array();
		while (false != ($row = $query->GetRow()))
		{	
			if($row['is_audit'] == 1){
				$row['audit_show'] = '<font color="#339966">审核 - 已通过</font>';
			}
			elseif($row['is_audit'] == -1){
				$row['audit_show'] = '<font color="#FF0000">审核 - 未通过</font>';
			}
			else {
				$row['audit_show'] = '<font color="#ff6600">审核 - 待审核</font>';
			}
			$row['dateline'] = date('Y-m-d H:i:s',$row['dateline']);
			$category_list[] = $row;
		}

		
		include $this->TemplateHandler->Template('admin/vipintro');
	}
	
	
		function MemberView() 
	{
		$act = 'category_user_list';
		$ids = (int) $this->Get['ids'];

		if(!$ids)
		{
			$this->Messager("未找到指定信息",-1, 3);
		}

		$category_ary = DB::fetch_first("SELECT *  
					FROM ".DB::table('validate_category')." 
					where `id` = '{$ids}' ");	
		
		$categoryname =	$category_ary['category_name'];

				$where = "where `category_id` = '{$ids}'";
		$category_user_count = DB::result_first("SELECT count(*) FROM ".DB::table('validate_category_fields')." {$where} ");
		$sql = "update `".TABLE_PREFIX."validate_category` set `num` = '{$category_user_count}' where `id` = '{$ids}'";					
		$update = $this->DatabaseHandler->Query($sql);

		$sql = "select * from `".TABLE_PREFIX."validate_category_fields` where `category_id`='{$ids}' order by `dateline` desc";
		$query = $this->DatabaseHandler->Query($sql);
		$uids = array();
		while (false != ($row = $query->GetRow())) {
			$uids[$row['uid']] = $row['uid'];
		}
		
				$where = " where `uid` in ('".implode("','",$uids)."')  ";
		$members = $this->_memberlist($where,20);
		
		$member_list = $members['member_list'];
		$page_html = $members['page_list']['html'];

		
		include $this->TemplateHandler->Template('admin/vipintro_user');
	}

	function Vipintro_Member()
	{
		$type = $this->Get[''];
		
		include $this->TemplateHandler->Template('admin/vipintro');
		
	}
		
		function Modify() 
	{	
		$ids = $this->Get['ids'];

		Load::lib('form');
		$FormHandler = new FormHandler();
		
				$sql = "select * from `".TABLE_PREFIX."validate_category_fields` where `id` = '{$ids}'";
 	 	$query = $this->DatabaseHandler->Query($sql);
  		$category_info = $query->GetRow();
  		$category_info['is_audit'] = $category_info['is_audit'] ? $category_info['is_audit'] : -1;

  				$member = $this->TopicLogic->GetMember($category_info['uid']);
		
				$sql = "select * from `".TABLE_PREFIX."memberfields` where `uid`='{$category_info['uid']}'";
		$query = $this->DatabaseHandler->Query($sql);
		$memberfields = $query->GetRow();

		$_options = array(
				'0' => array(
					'name' => '请选择',
					'value' => '0',
				),
				'身份证' => array(
					'name' => '身份证',
					'value' => '身份证',
				),
				'学生证' => array(
					'name' => '学生证',
					'value' => '学生证',
				),
				'军官证' => array(
					'name' => '军官证',
					'value' => '军官证',
				),
				'护照' => array(
					'name' => '护照',
					'value' => '护照',
				),
				'其他' => array(
					'name' => '其他',
					'value' => '其他',
				),
			);
		
		$validate_card_type_select = $FormHandler->Select('validate_card_type',$_options,$memberfields['validate_card_type']);

				$_province = DB::fetch_first("SELECT * FROM ".DB::table('common_district')." WHERE `id`='{$category_info['province']}'");
		$_city = DB::fetch_first("SELECT * FROM ".DB::table('common_district')." WHERE `id`='{$category_info['city']}'");
		
		$member_province = $_province['name'];
		$member_city = $_city['name'];

				$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = ''";
		$query = $this->DatabaseHandler->Query($sql);
		$category_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$category_list[] = $row;
		}
		
				$sql = "select * from `".TABLE_PREFIX."validate_category` where `id` = '{$category_info['category_id']}'";
		$query = $this->DatabaseHandler->Query($sql);
		$subclass_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$subclass_list[] = $row;
		}

		  		$meb_fields = @unserialize($memberfields['validate_extra']);

		include $this->TemplateHandler->Template('admin/modify_vipintro');
	}
	
	
		function DoModify() 
	{
				$uid			= (int) $this->Post['uid'];
				$is_pm_notice 	= (int) $this->Post['is_pm_notice'];
				$is_audit 		= $this->Post['is_audit'];
				$category_fid 	= (int) $this->Post['category_fid'];
				$category_id	= (int) $this->Post['category_id'];
				$audit_info 	=  $this->Post['to_message'];
		
		if(!$category_fid || !$category_id){
			$this->Messager("请确认认证类别",-1);
		}
		
				$validate_info	=  $this->Post['validate_info'];
		
				$sql = "update `".TABLE_PREFIX."validate_category_fields` 
				set `audit_info` = '{$audit_info}',
					`category_fid` = '{$category_fid}',
					`category_id` = '{$category_id}',
					`is_audit` = '{$is_audit}',
					`is_push` = 0 
				where `uid` = '{$uid}'";					
		$update = $this->DatabaseHandler->Query($sql);
		
				if($is_audit != 1)
		{
						$sql = "update `".TABLE_PREFIX."members` set `validate`='0',`validate_category`='0' where `uid`='{$uid}'";
			$this->DatabaseHandler->Query($sql);

						if($is_pm_notice)
			{	
				 				$message = $this->Post['to_message'] ? $this->Post['to_message'] : "没有理由！";
				$data=array(
						'to_user' => $this->Post['nickname'],
						'message' =>  "您的身份验证不通过，拒绝理由：".$message,
				);
				
				load::logic('pm');
				$PmLogic = new PmLogic();
				$return = $PmLogic->pmSend($data);
				
			}
			
			$this->Messager('已设置为审核未通过','admin.php?mod=vipintro');
		}		
		
		
				$member_extra = '';
		if($this->Post['member_extra'])
		{
			$member_extra = @serialize($this->Post['member_extra']);
			
		}
		
				$sql = "update `".TABLE_PREFIX."members` set `validate`='{$category_fid}',`validate_category` = '{$category_id}' where `uid`='{$uid}'";
		$this->DatabaseHandler->Query($sql);
		
				$sql = "update `".TABLE_PREFIX."memberfields` set `validate_true_name`='{$this->Post['validate_true_name']}' ,`validate_card_id` = '{$this->Post['validate_card_id']}' ,`validate_card_type` = '{$this->Post['validate_card_type']}' , `validate_remark` = '{$validate_info}',`validate_extra` = '{$member_extra}'  where `uid` = '{$uid}'";			
		$update = $this->DatabaseHandler->Query($sql);
		

		
		
				$category_count = DB::result_first("SELECT count(*) FROM ".DB::table('validate_category_fields')." where `category_fid` = '{$category_fid}' ");	
		
				$subclass_count = DB::result_first("SELECT count(*) FROM ".DB::table('validate_category_fields')." where `category_id` = '{$category_id}' ");	

				$sql = "update `".TABLE_PREFIX."validate_category` set `num`='{$category_count}' where `id`='{$category_fid}'";
		$this->DatabaseHandler->Query($sql);
		
				$sql = "update `".TABLE_PREFIX."validate_category` set `num`='{$subclass_count}' where `id`='{$category_id}'";
		$this->DatabaseHandler->Query($sql);
		
				$this->Messager('审核成功','admin.php?mod=vipintro');
		
	}

	
			
	function CategoryList() 
	{	
	
		$category = $this->get_category_tree();
		
				$act = 'categorylist';
		
		include $this->TemplateHandler->Template('admin/validate_category');
	}
	
	
	function &get_category_tree()
	{
		$tree = $cat_ary = array();
		$query = DB::query("SELECT *  
							FROM ".DB::table('validate_category')." 
							ORDER BY id ASC");
		while ($value = DB::fetch($query)) {
			$cat_ary[$value['id']] = $value;
		}
		ConfigHandler::set('validate_category',$cat_ary);
		
		if (!empty($cat_ary)) {
			$tree = $this->category_tree($cat_ary);
		}
		return $tree;
	}
	
	
	function category_tree($data, $parent_id = 0)
	{
		$tree = array();
		foreach ($data as $value) {
			if ($value['category_id'] == $parent_id) {
				$tmp = array();
				$tmp = $value;
				$tmp['child'] = $this->category_tree($data, $value['id']);
				$tree[$value['id']] = $tmp;
			}
		}
		return $tree;
	}

	
	
		
	function DoCategory() 
	{	
		
				if($this->Post['postFlag'])
		{	
						$type = $this->Post['type'];
			
						$category_name = trim($this->Post['category_name']);
			
			if(empty($category_name))
			{
				$this->Messager("类别名称不能为空",-1);
			}
			
						$sql = "select `category_name` from `".TABLE_PREFIX."validate_category` where category_name = '{$category_name}'";
 		 	$query = $this->DatabaseHandler->Query($sql);
 	 		$check_category = $query->GetRow();
 	 		if($check_category)
 	 		{	
 	 			$this->Messager("输入的 <font color='#ff0000'> {$category_name} </font> 类别已经存在,请重新添加",-1);
 	 		}
 	 
			$field = 'validate';
			$datetime = time();
	
			if(empty($_FILES) || !$_FILES[$field]['name'])
			{
				$this->Messager("请设置图片",-1);
			}
	
			
						$sql = "insert into `".TABLE_PREFIX."validate_category`(`category_name`,`dateline`) 
			 values 
			('{$category_name}','{$datetime}')";
			$this->DatabaseHandler->Query($sql);
			$category_pic_id = $this->DatabaseHandler->Insert_ID();

			$return = $this->uploadPic($field,$category_pic_id);
			$image_file = '';
			if(is_array($return)){
				$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."validate_category` where id = '$category_pic_id'");
				$this->Messager("上传图片失败",-1);
			}else{
				$image_file = $return;
			}
			
						$sql = "update `".TABLE_PREFIX."validate_category` set `category_pic`='{$image_file}' where `id`='{$category_pic_id}'";
			$this->DatabaseHandler->Query($sql);

						$validate_category = ConfigHandler::get('validate_category');
 	 		$validate_category[$category_pic_id] = array(
			    'id' => $category_pic_id,
			    'category_id' => 0,
			    'category_name' => $category_name,
			    'category_pic' => $image_file,
			    'num' => '0',
			    'dateline' => $datetime,
 	 		);
			ConfigHandler::get('validate_category',$validate_category);
			
			$this->Messager("添加成功",'admin.php?mod=vipintro&code=categorylist');
		}
		
				$code = 'category';
		
		$button = '添加';
		
		$act = 'admin.php?mod=vipintro&code=category';
		
		include $this->TemplateHandler->Template('admin/validate_category');
		
	}
	
		function ModifyCategory()
	{	
		$ids = (int) $this->Get['ids'] ? $this->Get['ids'] : $this->Post['ids'];
				$sql = " select * from `".TABLE_PREFIX."validate_category`  where  `id` = '{$ids}'";
		$query = $this->DatabaseHandler->Query($sql);
		$category_info = $query->GetRow();

				$where = "where `category_fid` = '{$ids}'";
		$category_user_count = DB::result_first("SELECT count(*) FROM ".DB::table('validate_category_fields')." {$where} ");
		$sql = "update `".TABLE_PREFIX."validate_category` set `num` = '{$category_user_count}' where `id` = '{$ids}'";					
		$update = $this->DatabaseHandler->Query($sql);
	
				if($this->Post['postFlag'])
		{	
			$category_name = trim($this->Post['category_name']);
						if($category_name != $category_info['category_name']){
				$sql = "select `category_name` from `".TABLE_PREFIX."validate_category` where category_name = '{$category_name}'";
	 		 	$query = $this->DatabaseHandler->Query($sql);
	 	 		$check_category = $query->GetRow();
	 	 		if($check_category)
	 	 		{	
	 	 			$this->Messager("输入的 <font color='#ff0000'> {$category_name} </font> 类别已经存在,请重新添加",-1);
	 	 		}
			}
	 	 		
			$field = 'validate';
			
			if($_FILES[$field]['name'])
			{
				$return = $this->uploadPic($field,$ids);
				if(is_array($return)){
					$this->Messager("上传图片失败",-1);
				}else{
					$image_file = $return;
				}
			}
			
			$pic = $image_file ? $image_file : $category_info['category_pic'];

						$sql = "update `".TABLE_PREFIX."validate_category` set `category_pic`='{$pic}',`category_name` = '{$category_name}' where `id`='{$ids}'";				
			$this->DatabaseHandler->Query($sql);

						$validate_category = ConfigHandler::get('validate_category');
	  		$validate_category[$ids] = array(
			    'id' => $ids,
			    'category_id' => 0,
			    'category_name' => $category_name,
			    'category_pic' => $pic,
			    'num' => '0',
			    'dateline' => TIMESTAMP,
	  		);
			ConfigHandler::get('validate_category',$validate_category);

			$this->Messager("认证类别，修改成功","admin.php?mod=vipintro&code=categorylist&typeid={$typeid}");
				
		}

		$code = 'category';
		
		$button = '编辑';
		
		$act = 'admin.php?mod=vipintro&code=modifycategory';
		
		include $this->TemplateHandler->Template('admin/validate_category');
	}
	
	
	
	
		function DoCategoryClass() 
	{	
				if($this->Post['postFlag'])
		{	
			$category_fid = (int) $this->Post['category_fid'];
			$category_name = trim($this->Post['category_name']);
		
			if(empty($category_name))
			{
				$this->Messager("类别名称不能为空",-1);
			}

						$sql = "select `category_name` from `".TABLE_PREFIX."validate_category` where `category_name` = '{$category_name}' and `category_id` = '$category_fid'";
			$query = $this->DatabaseHandler->Query($sql);
 	 		$check_category = $query->GetRow();
 	 		if($check_category)
 	 		{	
 	 			$this->Messager("输入的 <font color='#ff0000'> {$category_name} </font> 类别已经存在,请重新添加",-1);
 	 		}
 			
			$datetime = time();
			
						$sql = "insert into `".TABLE_PREFIX."validate_category` (`category_id`,`category_name`,`dateline`) 
			 values 
			('{$category_fid}','{$category_name}','{$datetime}')";
			
			$this->DatabaseHandler->Query($sql);
			$category_pic_id = $this->DatabaseHandler->Insert_ID();
			
			$field = 'validate';
			$image_file = '';
			if($_FILES[$field]['name'])
			{	       				
				$return = $this->uploadPic($field,$category_pic_id);
				if(is_array($return)){
					$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."validate_category` where id = '$category_pic_id'");
					$this->Messager("上传图片失败",-1);
				}else{
					$image_file = $return;
				}
			}
			
						$sql = "update `".TABLE_PREFIX."validate_category` set `category_pic`='{$image_file}' where `id`='{$category_pic_id}'";				
			$this->DatabaseHandler->Query($sql);
			
						$validate_category = ConfigHandler::get('validate_category');
 	 		$validate_category[$category_pic_id] = array(
			    'id' => $category_pic_id,
			    'category_id' => $category_fid,
			    'category_name' => $category_name,
			    'category_pic' => $image_file,
			    'num' => '0',
			    'dateline' => $datetime,
 	 		);
			ConfigHandler::get('validate_category',$validate_category);
			
			$typeid = $this->Post['type'];
			$this->Messager("子类添加成功","admin.php?mod=vipintro&code=categorylist&typeid={$typeid}");
			
		}

				$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = 0 ";
		$query = $this->DatabaseHandler->Query($sql);
		$category_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$category_list[] = $row;
		}
		
		
		$code = 'categoryclass';	
		
		$button = '添加';
		
		$act = 'admin.php?mod=vipintro&code=categoryclass';
		
		include $this->TemplateHandler->Template('admin/validate_category');
	}

	function DoTuiJian() 
	{
		
		$uids = $this->Post['uids'];
		
		$type = $this->Post['type'];
		
		if(empty($uids))
		{
			$this->Messager("请选择用户",-1);
		}
		
		if(empty($type))
		{
			$this->Messager("请选择操作选项",-1);
		}
	
		if($type == 'people')
		{	
						$is_push = '1';
			
		} elseif($type == 'city_people'){
			
						$is_push = '2';
			
		} elseif($type == 'del'){
			
						$is_push = '0';
		}		
		
				if($this->Post['type'] == 'deluser')
		{
			foreach ($uids as $v) 
			{
								$sql = "delete from `".TABLE_PREFIX."validate_category_fields` where `uid`='{$v}' ";
	    		$this->DatabaseHandler->Query($sql);
	    
	    						$sql = "update `".TABLE_PREFIX."members` set `validate` = '0',`validate_category` = '0',`open_extra` = '0' where `uid`='{$v}'";	
				$this->DatabaseHandler->Query($sql);
				
								$sql = "update `".TABLE_PREFIX."memberfields` set `validate_extra` = '',`validate_card_pic` = '' where `uid`='{$v}'";	
				$this->DatabaseHandler->Query($sql);
				
								$sql = "delete from `".TABLE_PREFIX."validate_extra` where `id`='{$v}' ";
	    		$this->DatabaseHandler->Query($sql);

			}
						$category_fid = $this->Post['category_fid'];
			
						$category_id = $this->Post['category_id'];
			
						foreach ($category_fid as $fid)
			{
				 $where = "where `category_fid` = '{$fid}' ";
				 $category_fid_count = DB::result_first("SELECT count(*) FROM ".DB::table('validate_category_fields')." {$where} ");
				 
				 				 $sql = "update `".TABLE_PREFIX."validate_category` set `num` = '{$category_fid_count}' where `id`='{$fid}'";	
				 $this->DatabaseHandler->Query($sql);
			}
			
			
						foreach ($category_id as $cid) 
			{
				 $where = "where `category_id` = '{$cid}' ";
				 $category_id_count = DB::result_first("SELECT count(*) FROM ".DB::table('validate_category_fields')." {$where} ");
				 
				 				 $sql = "update `".TABLE_PREFIX."validate_category` set `num` = '{$category_id_count}' where `id`='{$cid}'";	
				 $this->DatabaseHandler->Query($sql);
			}
	
		}
		else{
			foreach ($uids as $v) 
			{
				$sql = "update `".TABLE_PREFIX."validate_category_fields` set `is_push` = '{$is_push}' where `uid`='{$v}'";	
				$this->DatabaseHandler->Query($sql);	
			}
		}
		
		$this->Messager("设置成功",'admin.php?mod=vipintro');

	}

		function ModifyCategoryClass() 
	{
		$ids = (int) $this->Get['ids'] ? $this->Get['ids'] : $this->Post['ids'];

				$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = ''";
		$query = $this->DatabaseHandler->Query($sql);
		$category_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$category_list[] = $row;
		}
		
				$sql = "select * from `".TABLE_PREFIX."validate_category` where `id` = '{$ids}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$category_info = $query->GetRow();
		
				$category_id  = $category_info['category_id'];
		
				$category_name  = $category_info['category_name'];
		
		
		if($this->Post['postFlag'])
		{	
			$fids = $this->Post['category_fid'];
			
			$new_category_name = trim($this->Post['category_name']);
			
						if($new_category_name != $category_name){
				$sql = "select `category_name` from `".TABLE_PREFIX."validate_category` where category_name = '{$new_category_name}'";
	 		 	$query = $this->DatabaseHandler->Query($sql);
	 	 		$check_category = $query->GetRow();
	 	 		if($check_category){	
	 	 			$this->Messager("输入的 <font color='#ff0000'> {$new_category_name} </font> 类别已经存在,请重新添加",-1);
	 	 		}
			}

			$field = 'validate';
			if($_FILES[$field]['name'])
			{
				$return = $this->uploadPic($field,$ids);
				if(is_array($return)){
					$this->Messager("上传图片失败",-1);
				}else{
					$image_file = $return;
				}
			}
			
			$pic = $image_file ? $image_file : $category_info['category_pic'];
			
						$sql = "update `".TABLE_PREFIX."validate_category` set `category_id` = '{$fids}',`category_pic`='{$pic}',`category_name` = '{$new_category_name}' where `id`='{$ids}'";					
			$this->DatabaseHandler->Query($sql);
			
						$validate_category = ConfigHandler::get('validate_category');
 	 		$validate_category[$ids] = array(
			    'id' => $ids,
			    'category_id' => $fids,
			    'category_name' => $new_category_name,
			    'category_pic' => $pic,
			    'num' => '0',
			    'dateline' => TIMESTAMP,
 	 		);
			ConfigHandler::set('validate_category',$validate_category);
		
			$this->Messager("认证分类，修改成功","admin.php?mod=vipintro&code=categorylist");
				
		}
		
		
		$code = 'categoryclass';
		
		$button = '编辑';
		
		$act = 'admin.php?mod=vipintro&code=modifycategoryclass';
		
		include $this->TemplateHandler->Template('admin/validate_category');
	}
		
	
		function Validate_Setting() 
	{
				$code = 'validate_setting';
		
		$validate_config = $this->Config['card_pic_enable'];

		
		include $this->TemplateHandler->Template('admin/vipintro');
	}
	
	
		function People_Setting() 
	{
		$code = 'people_setting';
		
				$config = ConfigHandler::get();
		$people_config = $this->Config['validate_people_setting'];
		
				$query = DB::query("SELECT *  
							FROM ".DB::table('common_district')." 
							where `upid` = 0  order by list ");		
		$proviect_ary = array();
		while ($value = DB::fetch($query)) 
		{
			$proviect_ary[] = $value;
		}
	
		
		
		include $this->TemplateHandler->Template('admin/vipintro');
	}
	
		function DoPeople() 
	{	
		
				$proviect_type = $this->Post['proviect_type'];
		
				$proviect_id = $this->Post['proviect_id'];
		
		
			  	$data = array(
	  			
	  					'proviect_type' 		=> $proviect_type,
	  					'proviect_id'			=> $proviect_id,
	  					
	  					'people_user_orderby' 	=> $this->Post['people_user_orderby'],
	  					'proviect_user_orderby' => $this->Post['proviect_user_orderby'],
	  						
	  					'people_user_limit' 	=> $this->Post['people_user_limit'] ? $this->Post['people_user_limit'] : 20,
	  					'proviect_user_limit' 	=> $this->Post['proviect_user_limit'] ? $this->Post['proviect_user_limit'] : 20,
	  	
	  					'people_topic_limit' 	=> $this->Post['people_topic_limit'] ? $this->Post['people_topic_limit'] : 10,
	  					'proviect_topic_limit' 	=> $this->Post['proviect_topic_limit'] ? $this->Post['proviect_topic_limit'] : 10,
	  	);
	  		  	$new_config = array();
	  	$new_config['validate_people_setting'] = $data;
	  	ConfigHandler::update($new_config);
		
		$this->Messager("名人堂设置成功",'admin.php?mod=vipintro&code=people_setting');
		
	}
	
		function DoValidate() 
	{	
				$data = array(

	  				'is_card_pic' =>(int) $this->Post['is_card_pic'],
	  	);
			  	$new_config = array();
	  	$new_config['card_pic_enable'] = $data;
		ConfigHandler::update($new_config);
		
		$this->Messager("设置成功",'admin.php?mod=vipintro&code=validate_setting',-1);
	}
	
		function CheckCategory() 
	{
		$category_id = (int) $this->Post['category_id'];
		
				
				 
		
		$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = '{$category_id}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$subclass_list = array();
		while (false != ($row = $query->GetRow()))
		{
			$subclass_list[] = $row;
		}
		
	   echo '<select name="category_id" id="category_id" style="width:100px;" notnull="true" >';
	   if($subclass_list)
	   {
		   for ($i = 0; $i < count($subclass_list); $i++)
		   {
		  	 echo '<option value="'.$subclass_list[$i]['id'].'">'.$subclass_list[$i]['category_name'].'</option>';
		   }
	   }
	   else 
	   {
	   		echo '<option value="0" selected="selected">没有分类</option>';
	   }
	   echo '</select>';
	   exit;
		
	}
	
	
		function Insert_Validate_User() 
	{	
		
		$code	= 'insert_validate_user';

				$where = " where  `validate` != '' and `validate_category` = '' ";
		$members = $this->_memberlist($where,10);
		
		$member_list = $members['member_list'];
		$page_html = $members['page_list']['html'];
		
				$sql = "select * from `".TABLE_PREFIX."validate_category` where `category_id` = ''";
		$query = $this->DatabaseHandler->Query($sql);
		$category_list = array();
		while (false != ($row = $query->GetRow())) 
		{
			$category_list[] = $row;
		}
		
		
		if($this->Post['postFlag'])
		{	
			$uids = $this->Post['uids'];
			
			$category_fid = (int) $this->Post['category_fid'];
			$category_id =  (int) $this->Post['category_id'];
			
			if(empty($category_id))
			{
				$this->Messager("请选择分类",'admin.php?mod=vipintro&code=insert_validate_user',3);
			}
			
			Load::logic('validate_category');
			$this->ValidateLogic = new ValidateLogic($this);
		
			for ($i = 0; $i < count($uids); $i++) 
			{
				$validate_info = DB::fetch_first("select * from ".DB::table('validate_category_fields')." where `uid`='".$uids[$i]."' ");
				
				if(empty($validate_info))
				{
					
					$member_info = DB::fetch_first("select `uid`,`city`,`province` from ".DB::table('members')." where `uid`='".$uids[$i]."' ");
					
					$province_info = DB::result_first("select id from ".DB::table('common_district')." where `name`='".$member_info['province']."' ");
					$city_info = DB::result_first("select id from ".DB::table('common_district')." where `name`='".$member_info['city']."' ");
					
					 					$sql = "update `".TABLE_PREFIX."members` set `validate` = '$category_fid',`validate_category` = '{$category_id}' where `uid`='{$uids[$i]}'";					
					$this->DatabaseHandler->Query($sql);
		
										$data = array(
		
						'uid' 			=> $uids[$i],
						'category_fid'  => (int) $this->Post['category_fid'],
						'category_id'   => (int) $this->Post['category_id'],
						'province' 		=> $province_info['id'],
						'city'			=> $city_info['id'],
						'validate_info' => '',
						'is_audit'		=> 1,
						'audit_info'	=> '',
						'order'			=> '',
						'is_push'		=> 0,
						'dateline'	    => Time(),
		
					);
					DB::insert('validate_category_fields',$data);
				}
	
			}
			
			$this->Messager("设置成功",'admin.php?mod=vipintro&code=insert_validate_user',3);
		}
		
		
		include $this->TemplateHandler->Template('admin/vipintro');
	}
	
	
		function _check_member($nickname='') 
	{	
		
		if($nickname)
		{	
			$sql = " select `validate`,`uid`,`nickname`,`validate_category` from `".TABLE_PREFIX."members`  where  `nickname` = '{$nickname}'";
			$query = $this->DatabaseHandler->Query($sql);
			$member = $query->GetRow();	
				
						
			return $member;
						
		}
		
		return false;
		
	}
	
	function _memberlist($where='',$limit=20) 
	{	
		
		$per_page_num = $limit;
		
		$query_link = "admin.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&ids={$this->Get['ids']}" : "");

				$sql = " select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where} ";
		$total_record = DB::result_first($sql);
				
		$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));
		
		$wherelist = " {$where} {$page_arr['limit']} ";

		$members = $this->TopicLogic->GetMember($wherelist,"`uid`,`ucuid`,`nickname`,`validate`");
		
		
		$ret_ary = array('member_list'=>$members,'page_list'=>$page_arr);
		
		return $ret_ary;
	}
	
	
	function uploadPic($field,$category_pic_id){
		
		
	
		$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/'. face_path($category_pic_id);		 
		$image_name = $category_pic_id . "_o.gif";
		$image_file = $image_path . $image_name;
		
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
			return array('err'=>'图片上传失败');
		}
		makethumb($image_file,16,16,0,0,0,0,0,0);
		
        Load::lib('image');
        $image = new image();
        $image->Thumb($image_file,$image_file,16,16);
        	
		$image_file = addslashes($image_file);
		
		return $image_file;
	}
	
	
	function delCategory(){
		$ids = get_param('ids');
		if($ids){
			foreach ($ids as $id) {
				$category_info = DB::fetch_first("select `id`,`category_id`,`category_pic` from `".TABLE_PREFIX."validate_category` where `id` = '$id'");
								if($category_info['category_id'] > 0){
					DB::query("delete from ".TABLE_PREFIX."validate_category_fields where category_id = '$id'");
				}else{
					DB::query("delete from ".TABLE_PREFIX."validate_category_fields where category_fid = '$id'");
				}
								if($category_info['category_pic']){
					unlink($category_info['category_pic']);
				}
			}
			DB::query("delete from ".TABLE_PREFIX."validate_category where id in ('".implode("','",$ids)."')");
		}
		
		$this->Messager('删除V认证分类成功','admin.php?mod=vipintro&code=categorylist');
	}
}

?>
