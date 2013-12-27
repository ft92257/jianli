<?php
/**
 * 文件名：theme.mod.php
 * 版本号：1.0
 * 最后修改时间：2010年11月22日 16:21:03
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 个人模板设置模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic($this);
		
		if (MEMBER_ID < 1) 
		{
			$this->Messager("请先登录",null);
		}
		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`=".MEMBER_ID);
		$this->Member = $query->GetRow();
        
        		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();		
		switch ($this->Code) {
			case 'modify':
				$this->Modify();
				break;
			case 'do_modify':
				$this->DoModify();
				break;				
			
			default:
				$this->Main();
		}				
		$body=ob_get_clean();
		
		$this->ShowBody($body);
        
	}
	
	
	function Main()
	{
		$this->Modify();
	}
	
	function Modify()
	{
		$theme_id = $this->Member['theme_id'];
		$theme_bg_image = $this->Member['theme_bg_image'];
		$theme_bg_color = $this->Member['theme_bg_color'];
		$theme_text_color = $this->Member['theme_text_color'];
		$theme_link_color = $this->Member['theme_link_color'];
		$theme_bg_image_type = $this->Member['theme_bg_image_type'];
        $theme_bg_repeat = $this->Member['theme_bg_repeat'];
        $theme_bg_fixed = $this->Member['theme_bg_fixed'];
	
		
		$themelist = array
		(
			array("theme_id"=>'t1',"theme_bg_color"=>'#B5CE4C',"theme_text_color"=>'#333333',"theme_link_color"=>'#2E75BC',"theme_bg_image_type"=>'center',),
			array("theme_id"=>'t2',"theme_bg_color"=>'#73CFF1',"theme_text_color"=>'#333333',"theme_link_color"=>'#2E75BC',"theme_bg_image_type"=>'center ',),
			array("theme_id"=>'t3',"theme_bg_color"=>'#BDE2F8',"theme_text_color"=>'#333333',"theme_link_color"=>'#2965B1',"theme_bg_image_type"=>'center',),
			array("theme_id"=>'t4',"theme_bg_color"=>'#F5BC49',"theme_text_color"=>'#333333',"theme_link_color"=>'#0088CC',"theme_bg_image_type"=>'center',),
			array("theme_id"=>'t5',"theme_bg_color"=>'#FFFFFF',"theme_text_color"=>'#333333',"theme_link_color"=>'#2965B1',"theme_bg_image_type"=>'left',),
			array("theme_id"=>'t6',"theme_bg_color"=>'#C4CD58',"theme_text_color"=>'#333333',"theme_link_color"=>'#007FA9',"theme_bg_image_type"=>'center',),
			array("theme_id"=>'t7',"theme_bg_color"=>'#87C8EE',"theme_text_color"=>'#333333',"theme_link_color"=>'#0066CC',"theme_bg_image_type"=>'left',),
			array("theme_id"=>'t8',"theme_bg_color"=>'#FFFFFF',"theme_text_color"=>'#333333',"theme_link_color"=>'#2E75BC',"theme_bg_image_type"=>'bottom',),
		);
		foreach($themelist as $k=>$v)
		{
			$v['element'] = "{$v[theme_bg_color]},{$v[theme_text_color]},{$v[theme_link_color]},{$v[theme_id]},{$v[theme_bg_image_type]}";

			$themelist[$k] = $v;
		}
		
		
		$my_bg_image = RELATIVE_ROOT_PATH . 'images/theme/' . face_path(MEMBER_ID) . MEMBER_ID . '_o.jpg';
		if (is_file($my_bg_image)) 
		{
			$my_bg_image = $this->Config['site_url'] . "/" . $my_bg_image;
		}
		else 
		{
			$my_bg_image = '';
		}

		if(MEMBER_ID > 0) 
		{ 
			if(MEMBER_STYLE_THREE_TOL == 1)
			{
				$member = $this->TopicLogic->GetMember(MEMBER_ID);
				if ($member['medal_id']) {
					$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
				}
			}
		}
		  
		$this->Title = "个人模板设置";	
		include($this->TemplateHandler->Template('topic_theme'));
	}
	
	function DoModify()
	{
		Load::lib('io');
		$IoHandler = new IoHandler();
		
		$field = 'theme';
		$image_id = MEMBER_ID;
		$theme_bg_image = str_replace($this->Config['site_url'].'/','',$this->Post['theme_bg_image']);
		
		$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/' . face_path($image_id);
		$image_name = $image_id . "_o.jpg";
		$image_file = $image_path . $image_name;
		$image_file_small = $image_path.$image_id . "_s.jpg";
		
		if ($_FILES && $_FILES[$field]['name']) 
		{		
			if (!is_dir($image_path)) 
			{				
				$IoHandler->MakeDir($image_path);
			}
			
			Load::lib('upload');
			$UploadHandler = new UploadHandler($_FILES,$image_path,$field,true);
			$UploadHandler->setMaxSize(2048);
			$UploadHandler->setNewName($image_name);
			$result=$UploadHandler->doUpload();
			
			if($result) 
			{
				$result = is_image($image_file);
			}
			
			if (!$result) 
			{
				$IoHandler->DeleteFile($image_file);
				
				$this->Messager("[图片上载失败]".implode(" ",(array) $UploadHandler->getError()),null);
			}
			else 
			{				
				$theme_bg_image = $image_file;
			}
		}
		else 
		{
			if ($theme_bg_image!=$image_file) 
			{
			}
		}		
		
		$theme_id = $this->Post['theme_id'];	
		$theme_bg_color = $this->Post['theme_bg_color'];
		$theme_text_color = $this->Post['theme_text_color'];
		$theme_link_color = $this->Post['theme_link_color'];	
		$theme_bg_image_type = $this->Post['theme_bg_image_type'];
        $theme_bg_repeat = $this->Post['theme_bg_repeat'] ? 1 : 0;
        $theme_bg_fixed = $this->Post['theme_bg_fixed'] ? 1 : 0;

		$sql = "update ".TABLE_PREFIX."members set `theme_bg_image`='$theme_bg_image', `theme_bg_color`='$theme_bg_color', `theme_text_color`='$theme_text_color', `theme_link_color`='$theme_link_color' , theme_id='$theme_id' , theme_bg_image_type='$theme_bg_image_type' , `theme_bg_repeat`='$theme_bg_repeat' , `theme_bg_fixed`='$theme_bg_fixed' where `uid`='".MEMBER_ID."'";
		$this->DatabaseHandler->Query($sql);
		
		
		
		if ('admin'==MEMBER_ROLE_TYPE && $this->Post['set_default']) 
		{
			unset($config);
			include(ROOT_PATH . './setting/settings.php');
			$default_config = $config;
			$config['theme_id'] = $theme_id;
			$config['theme_bg_image'] = $theme_bg_image;
			$config['theme_bg_color'] = $theme_bg_color;
			$config['theme_text_color'] = $theme_text_color;
			$config['theme_link_color'] = $theme_link_color;
		    $config['theme_bg_image_type'] = $theme_bg_image_type;
			
			if($config!=$default_config) 
			{
				ConfigHandler::set($config);
			}
		}
		
		
		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`=".MEMBER_ID);
		$this->_initTheme($query->GetRow());
		
		
		$this->Messager("设置成功",'index.php?mod=topic&code=myhome');
	}

}


?>
