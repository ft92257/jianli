<?php
/**
 * 文件名：setting.mod.php
 * 版本号：1.0
 * 最后修改时间：2009年10月27日 10时05分58秒
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 个人设置模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;

	var $ID = '';

	var $TopicLogic;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);

		$this->Member = $this->_member();


		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'do_modify_password':
				$this->DoModifyPassword();
				break;
			case 'do_modify_face':
				$this->DoModifyFace();
				break;
			case 'do_modify_profile':
				$this->DoModifyProfile();
				break;
			case 'do_notice':
				$this->DoNotice();
				break;
			case 'user_share':
				$this->DoUserShare();
				break;
			case 'invite_by_email':
				$this->InviteByEmail();
				break;
			case 'modify_email':
				$this->DoModifyEmail();
				break;
			case 'add_user_tag':
				$this->DoUserTag();
				break;
			case 'del_user_tag':
				$this->DelUserTag();
				break;
			case 'user_tag_view':
				$this->UserTagView();
				break;	
				
			default:
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$this->Messager($this->MemberHandler->GetError(),null);
		}
           
		
		$act = $this->Code ;

		$act_list = array('nick'=>'修改昵称','face'=>'修改头像','aboutme'=>'个人说明','signature'=>'个人签名','sex'=>'选择性别','user_tag'=>'选择标签',);
		
		
		$uid = (int) $this->Get['uid'] ? $this->Get['uid'] : MEMBER_ID;
		$member = $this->_member($uid);
		$member = wap_iconv($member);
		
		if($member['validate'])
		{
			$member['validate_html'] = "<img class='vipImg' title='' src='".$this->Config['site_url']."/images/vip.gif' />";
		}
		
		if('face' == $act)
		{
						if(true === UCENTER_FACE)
            {
			     include_once(ROOT_PATH . './api/uc_client/client.php');

								$uc_avatarflash = uc_avatar(MEMBER_UCUID,'avatar','returnhtml');

                $query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`='{$member['uid']}'");
                $_member_info = $query->GetRow();
                if($member['uid'] > 0 && MEMBER_UCUID > 0 && !($_member_info['face']))
                {
                    $uc_check_result = uc_check_avatar(MEMBER_UCUID);
                    if($uc_check_result)
                    {
                        $this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `face`='./images/noavatar.gif' where `uid`='{$member['uid']}'");
                    }
                }
			}
            else
            {
                $temp_face = '';
                if($this->Get['temp_face'] && is_image($this->Get['temp_face']))
                {
                    $temp_face = $this->Get['temp_face'];
                    
                    $member['face_original'] = $temp_face;
                }
            }
		}
				elseif('usertag' == $act)
		{
			
			$where_list = '';
			
			$type = $this->Get['type'];
			
			$user_tag_list = array();
			
						if($type == 'all')
			{
								$sql = "Select * From `".TABLE_PREFIX."user_tag` Order By id desc limit 0,20;";		
				$query = $this->DatabaseHandler->Query($sql);							
				while(false != ($row = $query->GetRow()))
				{   
					$row['name'] = wap_iconv($row['name']);
					$user_tag_list[] = $row;
				}	
				
			}
						elseif($type == 'myadd')
			{	
				$where_list = " where `uid` = '".MEMBER_ID."' ";			
			}
	
			if($where_list)
			{
				$sql = "Select * From `".TABLE_PREFIX."user_tag_fields` {$where_list} Order By id desc limit 0,20;";						
				$query = $this->DatabaseHandler->Query($sql);
				while(false != ($row = $query->GetRow()))
				{   
					$row['name'] = wap_iconv($row['tag_name']);
					$row['id'] = wap_iconv($row['tag_id']);
					$user_tag_list[] = $row;
				}
			}

		}
		
		
		elseif('base' == $act)
		{
			$uid = (int) $this->Get['uid'];
			
						$sql = "Select * From `".TABLE_PREFIX."user_tag_fields` where `uid` = '{$uid}' Order By id desc limit 0,10;";		    
			$query = $this->DatabaseHandler->Query($sql);
			$my_user_tag = array();
			while(false != ($row = $query->GetRow()))
			{   
				$row['tag_name'] = wap_iconv($row['tag_name']);
				$my_user_tag[] = $row;
			}
	
		}

		
		$setting_hb = 'hb';
		
		$this->Title = $act_list[$act];
		include($this->TemplateHandler->Template('setting_main'));
	}

    
    function DoModifyFace()
    {
        
        if(MEMBER_ID < 1)
        {
            $this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
        }

        $field = 'face';
 		$src_x = 0;
        $src_y = 0;
        $src_w = 200;
        $src_h = 200;

        if(!empty($_FILES[$field]['name']))
		{
			
			

			$type = trim(strtolower(end(explode(".",$_FILES[$field]['name']))));
			
			if($type != 'gif' && $type != 'jpg' && $type != 'png')
			{		
				$this->Messager("图片格式不对",'index.php?mod=settings&code=face');
			}
	
						$image_path = ROOT_PATH.'images/'.$field . '/' . face_path(MEMBER_ID);
			$image_name = MEMBER_ID . "_b.jpg";	        
			$src_file = $image_path.$image_name;
			
			if(!is_dir($image_path))
	        {
	            Load::lib('io', 1)->MakeDir($image_path);
	        }
			
	        			include_once(ROOT_PATH.'include/lib/upload.han.php');
			$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true,false);
			$UploadHandler->setMaxSize(2048);
			$UploadHandler->setNewName($image_name);			
			$result=$UploadHandler->doUpload();
		
			
	        $image_file_small = $dst_file = $image_path . MEMBER_ID . '_s.jpg';
	        $make_result = makethumb($src_file,$dst_file,50,50,0,0,$src_x,$src_y,$src_w,$src_h);

			 
       		 $image_file = $dst_file = $image_path . $image_name;
       		 $make_result = makethumb($src_file,$dst_file,max(50,min(128,$src_w)),max(50,min(128,$src_w)),0,0,$src_x,$src_y,$src_w,$src_h);

			if($result)
	        {
				$result = is_image($image_file);
			}

			if(!$result)
	        {
				Load::lib('io', 1)->DeleteFile($image_file);
				$this->Messager("图片上载失败",'index.php?mod=settings&code=face');	
			}

	        
	        $sql = "update `".TABLE_PREFIX."members` set  `face`='{$image_file}' where `uid`='".MEMBER_ID."'";
			$this->DatabaseHandler->Query($sql);
		}
		
		
        
        
        
        $this->Messager("头像设置成功",'index.php?mod=settings&code=base&uid='.MEMBER_ID);
       
    }
	
	
		function DoUserTag()
	{
		if(MEMBER_ID < 1)
        {
        	$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
        }
		
		$tag_name = trim($this->Post['user_tag']);
		
		if(empty($tag_name))
		{
			$this->Messager("个人标签不能为空",'index.php?mod=settings&code=usertag&type=all');
		}

		$tag_name_arr = explode(',',$tag_name);
		$tag_name_arr = wap_iconv($tag_name_arr,'utf-8',$this->Config['charset']);

		$uid = MEMBER_ID;
		$addtime = time();
	
		for ($i = 0; $i < count($tag_name_arr); $i++) 
		{	
			
						$sql = "select `uid`,`tag_name` from `".TABLE_PREFIX."user_tag_fields` where `tag_name`='{$tag_name_arr[$i]}' and `uid` = '{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$user_tag = $query->GetRow();
			
			if(!$user_tag)
			{
								$sql = "select `id`,`name` from `".TABLE_PREFIX."user_tag` where `name`='{$tag_name_arr[$i]}'";
				$query = $this->DatabaseHandler->Query($sql);
				$taginfo = $query->GetRow();
				
				if(!$taginfo)
				{
					$sql = "insert into `".TABLE_PREFIX."user_tag`(`name`,`dateline`) values ('{$tag_name_arr[$i]}','{$addtime}')";
					$this->DatabaseHandler->Query($sql);
					$taginfo_id = $this->DatabaseHandler->Insert_ID();
				}
				
								$tag_inserid = $taginfo_id ? $taginfo_id : $taginfo['id'];
				
				$sql = "insert into `".TABLE_PREFIX."user_tag_fields`(`tag_id`,`uid`,`tag_name`) values ('{$tag_inserid}','{$uid}','{$tag_name_arr[$i]}')";			
				$this->DatabaseHandler->Query($sql);
								
								$count = DB::result_first("SELECT count(*) FROM ".DB::table('user_tag_fields')." where `tag_name`='{$tag_name_arr[$i]}' ");
				
				$sql = "update `".TABLE_PREFIX."user_tag` set `count`='{$count}' where `id`='{$tag_inserid}'";
				$this->DatabaseHandler->Query($sql);
			}
			
		}
		
		$this->Messager("设置成功",'index.php?mod=settings&code=usertag&type=myadd');

	}
	
	function DelUserTag() 
	{	
		if(MEMBER_ID < 1)
        {
        	$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
        }
		
		$tag_id = (int) $this->Post['tagid'] ? $this->Post['tagid'] : $this->Get['tagid'];
		
		if(!$tag_id) {
			$this->Messager("请指定要删除的对象",'index.php?mod=settings&code=user_tag_view&tagid='.$tag_id);
		}

		$sql = "delete from `".TABLE_PREFIX."user_tag_fields` where `tag_id` = '{$tag_id}' and `uid` = '".MEMBER_ID."' ";	
		$this->DatabaseHandler->Query($sql);			
		
				$count = DB::result_first("SELECT count(*) FROM ".DB::table('user_tag_fields')." where `tag_id`='{$tag_id}'");
		
		$sql = "update `".TABLE_PREFIX."user_tag` set `count`='{$count}' where `id`='{$tag_id}'";
		$this->DatabaseHandler->Query($sql);
		
		$this->Messager("设置成功",'index.php?mod=settings&code=usertag&type=myadd');
	}
	
	
	function UserTagView()
	{
		if(MEMBER_ID < 1)
        {
        	$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
        }
        
        $uid = MEMBER_ID;
        
		$act = 'user_tag_view';
        
        $tagid = (int) $this->Get['tagid'];
        
        $sql = "select `id`,`name` from `".TABLE_PREFIX."user_tag` where `id`='{$tagid}'";     
		$query = $this->DatabaseHandler->Query($sql);
		$taginfo = $query->GetRow();
		
		if(empty($taginfo))
        {
        	$this->Messager("未找到指定信息",'index.php?mod=settings&code=usertag&type=all');
        }
		
		$user_tag_name = wap_iconv($taginfo['name']);
	 	
		
		if($taginfo)
		{
			$sql = "select `uid`,`tag_name` from `".TABLE_PREFIX."user_tag_fields` where `tag_id`='{$tagid}' and `uid` = '{$uid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$user_tag = $query->GetRow();
		}

        include($this->TemplateHandler->Template('setting_main'));
	}

	function DoModifyProfile()
	{
        if(MEMBER_ID < 1)
        {
        	$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
        }
	
		foreach($this->Post as $key=>$val)
        {
            $key = strip_tags($key);
            $val = strip_tags($val);

            $this->Post[$key] = $val;
        }
		
        $member = $this->Member;
		$member = wap_iconv($member);
		
		
                $nickname = trim($this->Post['nickname']);
		
        		$gender = in_array(($gender = (int) $this->Post['gender']),array(1,2)) ? $gender : 0;
	
				$aboutme = strlen(($aboutme = trim(strip_tags($this->Post['aboutme'])))) > 255 ? cutstr($aboutme,254).'': $aboutme;	
	
				$signature = strlen(($signature = trim(strip_tags($this->Post['signature'])))) > 48 ? cutstr($signature,48).'': $signature;
		
		
				if(($filter_msg = filter($aboutme)))
        {
            $this->Messager($filter_msg,'index.php?mod=settings');
        }
		
        		if(($filter_msg = filter($signature)))
        {
            $this->Messager($filter_msg,'index.php?mod=settings');
        }
 
        		if(($filter_msg = filter($nickname)))
        {
            $this->Messager($filter_msg,'index.php?mod=settings');
        }

				$sql = "select `uid`,`nickname`,`validate` from `".TABLE_PREFIX."members` where `nickname`='{$nickname}'";
		$query = $this->DatabaseHandler->Query($sql);
		$nickname_exists=$query->GetRow();

		if($nickname_exists && $nickname_exists['uid']!=MEMBER_ID)
		{
			$this->Messager("姓名/昵称(<b>{$nickname}</b>)已经存在,请选择其它姓名/昵称",'index.php?mod=settings&code=nick');
		}
		

		$arr = array (			
			'gender' 	=> $gender 		? $gender : $member['gender'],
			'nickname' 	=> $nickname 	? $nickname : $member['nickname'],
			'aboutme' 	=> $aboutme 	? addslashes($aboutme) : $member['aboutme'],
			'signature' => $signature 	? addslashes($signature) : $member['signature'],
		);
		
		$arr = wap_iconv($arr,'utf-8',$this->Config['charset']);
		$this->_update($arr);

		$arr1 = array();
		$sql = "select * from `".TABLE_PREFIX."memberfields` where `uid`='".MEMBER_ID."'";
		$query = $this->DatabaseHandler->Query($sql);
		$memberfields = $query->GetRow();
		if (!$memberfields['validate_true_name'] && $this->Post['validate_true_name'])
        {
			$arr1['validate_true_name'] = $this->Post['validate_true_name'];
		}
		if (!$memberfields['validate_card_type'] && $this->Post['validate_card_type'])
        {
			$arr1['validate_card_type'] = $this->Post['validate_card_type'];
		}
		if (!$memberfields['validate_card_id'] && $this->Post['validate_card_id'])
        {
			$arr1['validate_card_id'] = $this->Post['validate_card_id'];
		}
		if ($arr1)
        {
			$sets = array();
			if (is_array($arr1))
            {
				foreach ($arr1 as $key=>$val)
                {
					$val = addslashes($val);
					$sets[$key] = "`{$key}`='{$val}'";
				}
			}
			$sql = "update `".TABLE_PREFIX."memberfields` set ".implode(" , ",$sets)." where `uid`='".MEMBER_ID."'";
			
			$this->DatabaseHandler->Query($sql);
		}


		$this->Messager("修改成功",'index.php?mod=settings&code=base&uid='.$member['uid']);
	}

	function _update($arr)
	{
		$sets = array();
		if (is_array($arr)) {
			foreach ($arr as $key=>$val) {
				$val = addslashes($val);
				$sets[$key] = "`{$key}`='{$val}'";
			}

			if ($sets) {
				$sql = "update `".TABLE_PREFIX."members` set ".implode(" , ",$sets)." where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);
			}
		}
	}

	function _member($uid=0)
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null, $this->Config['wap_url'] . "/index.php?mod=login");
		}
		
		$uids = $uid ? $uid : MEMBER_ID;
		
		$member = $this->TopicLogic->GetMember($uids);

		return $member;
	}

}


?>
