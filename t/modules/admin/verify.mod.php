<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename verify.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-24 16:33:35 1960876292 1526252134 5622 $
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
		
		$this->Execute();
	}

	
	function Execute(){
		ob_start();
		switch($this->Code)
		{	
			case 'edit':
				$this->edit();
				break;
			case 'doedit':
				$this->doEdit();
				break;
			case 'deletepic':
				$this->deletePic();
				break;
			case 'doverify':
				$this->doVerify();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		$this->ShowBody($body);
	}
	
		function Main(){
		$code = $this->Code;
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$query_link = 'admin.php?mod=topic&code='.$this->Code;

		$total_record = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."members_verify");
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200,500');
		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members_verify order by uid $page_arr[limit]");
		$members_verify = array();
		while ($rsdb = $query->GetRow()){
			$members_verify[$rsdb['id']] = $rsdb;
		}
		
		
		$this->Code = 'fs_verify';
		include($this->TemplateHandler->Template('admin/face_sign_verify'));
	}
	
	
	function doVerify(){
		$act = $this->Get['act'];
		$uids = array();
		$uid = (int) $this->Get['uid'];
		$uids = $this->Post['uids'];
		if($uid){
			$uids[$uid] = $uid;
		}

				if($act == 'yes'){
			foreach ($uids as $uid) {
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members_verify where uid = '$uid'");
				$member_verify = $query->GetRow();
				if($member_verify){
					if($member_verify['face']){
												$image_path = RELATIVE_ROOT_PATH . 'images/face/' . face_path($uid);
						if(!is_dir($image_path))
						{
						     
							
							
							Load::lib('io', 1)->MakeDir($image_path);
						}
												$image_file_b = $dst_file = $image_path . $uid . '_b.jpg';
												$image_file_s = $dst_file = $image_path . $uid . '_s.jpg';
												$image_verify_path = RELATIVE_ROOT_PATH . 'images/face_verify/' . face_path($uid);
												$image_verify_file_b = $dst_file = $image_verify_path . $uid . '_b.jpg';
												$image_verify_file_s = $dst_file = $image_verify_path . $uid . '_s.jpg';

						@copy($image_verify_file_b,$image_file_b);
						@copy($image_verify_file_s,$image_file_s);
						
						 
						$sql = "update `".TABLE_PREFIX."members` set `face`='{$image_file_s}' where `uid`='".$uid."'";
						$this->DatabaseHandler->Query($sql);
						
					    
				        if($this->Config['extcredits_enable'] && MEMBER_ID > 0)
						{
							
							update_credits_by_action('face',MEMBER_ID);
						}
					}
					
					if($member_verify['face_url']){
						
						 
						$sql = "update `".TABLE_PREFIX."members` set `face_url`='{$member_verify[face_url]}' where `uid`='".$uid."'";
						$this->DatabaseHandler->Query($sql);
						
					    
				        if($this->Config['extcredits_enable'] && $member_verify['uid'] > 0)
						{
							
							update_credits_by_action('face',$member_verify['uid']);
						}
					}
					
					if($member_verify["signature"]){
												$sql = "update ".TABLE_PREFIX."members set signature = '$member_verify[signature]',signtime = '".time()."' where uid = '$uid' ";
						$this->DatabaseHandler->Query($sql);
					}
					$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."members_verify where uid = '$uid'");
				}else{
					break;
				}
				
			}
		}
				else{
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."members_verify where uid = '$uid'");
		}
		$this->Messager("审核通过");
	}
	
	
	function edit(){
		$uid = (int) $this->Get['uid'];
		if($uid < 0){
			$this->Messager("请选择要编辑的用户资料");
		}
		$sql = "select * from ".TABLE_PREFIX."members_verify where uid = '$uid'";
		$query = $this->DatabaseHandler->Query($sql);
		$member_verify = $query->GetRow();
		
		include($this->TemplateHandler->Template('admin/setting_verify'));
	}
	
	function doEdit(){
		$uid = (int) $this->Post['uid'];
		$signature = $this->Post['signature'];
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members_verify set signature = '$signature' where uid = '$uid'");
		$this->Messager("编辑成功");
	}
	
		function deletePic(){
		$uid = (int) $this->Get['uid'];
		if($uid < 0){
			$this->Messager("请选择要编辑的用户资料");
		}
		
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members_verify set face = '', face_url = '' where uid = '$uid'");
		$this->Messager("图片删除成功");
	}
}