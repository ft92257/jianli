<?php
/**
 * 文件名：uploadattach.mod.php
 * 版本号：1.0
 * 最后修改时间：2012年01月08日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 附件上传模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $AttachLogic;
	
	var $type;
	

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		Load::logic('attach');
		$this->AttachLogic = new AttachLogic();

		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'attach':
				$this->Attach();
				break;

			case 'delete_attach':
				$this->DeleteAttach();
				break;
				
			case 'html':
				$this->Html();
				break;

			case 'down':
				$this->Down();
				break;

			case 'score':
				$this->Score();
				break;

			default:
				$this->Main();
				break;
		}
	}
	
	function Main()
	{
		response_text('正在建设中……');
		
	}
	function Down()
	{
		$this->initMemberHandler();
				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$response = '0,0,0';
		}
		else
		{
			$uid = MEMBER_ID;
			$aid = jget('aid','int');
			$dos = ('yes' == $this->Post['dos']) ? true : false;
			$down_attach_info = DB::fetch_first("SELECT * FROM ".DB::table('topic_attach')." WHERE id = '{$aid}' AND tid > 0");
			if(empty($down_attach_info))
			{
				$response = '0,0,0';
			}
			else
			{
				$score = $down_attach_info['score'];
				$dfurl = base64_encode($down_attach_info['filesize'].'|'.$aid.'|'.$down_attach_info['dateline'].'|'.$down_attach_info['download']);
				$url = 'index.php?mod=attach&code=download&downfile='.$dfurl;
			
				$credits = DB::result_first("SELECT credits FROM ".DB::table('members')." WHERE uid = '{$uid}'");
				$points = (int)$credits - (int)$score;
				$points = ($points == 0) ? 1 : $points;				$response = $points . ',' . $score. ',' . $url;
						}
		}
		response_text($response);
	}

	function Score()
	{
		$id = jget('id','int');
		$score = max(0, (int) ($this->Post['score'] ? $this->Post['score'] : $this->Get['score']));
		DB::query("update `".DB::table('topic_attach')."` set `score` = '$score'  where `id`='{$id}'");
	}
	
	function Html()
	{
		$tid = jget('tid','int');
		$this->item = $this->Post['item'] ? $this->Post['item'] : $this->Get['item'];
		$this->item_id = jget('itemid','int');
		$attach_uploadify_topic = array();
		if($tid > 0)
		{
			
			$TopicLogic = Load::logic('topic', 1);
			
			$attach_uploadify_topic = $TopicLogic->Get($tid);
		}
				
		
		$from = (get_param('attach_uploadify_from') ? get_param('attach_uploadify_from') : get_param('from'));
		$attach_uploadify_from = '';
		if('topic_publish' == $from)
		{
			$attach_uploadify_from = $from;
		}
		
		
		$only_js = (get_param('attach_uploadify_only_js') ? get_param('attach_uploadify_only_js') : get_param('only_js'));
		$attach_uploadify_only_js = 0;
		if($only_js)
		{
			$attach_uploadify_only_js = 1;
		}
		
		
		$topic_uid = max(0, (int) (get_param('attach_uploadify_topic_uid') ? get_param('attach_uploadify_topic_uid') : get_param('topic_uid')));
		$attach_uploadify_topic_uid = 0;
		if($topic_uid)
		{
			$attach_uploadify_topic_uid = $attach_uploadify_topic['uid'];
		}
		
		
		$attach_list_siz = max(0, (int) (get_param('attach_img_siz') ? get_param('attach_img_siz') : get_param('attach_list_siz')));
		$attach_img_siz = 32;
		if($attach_list_siz)
		{
			$attach_img_siz = $attach_list_siz;
		}
		
		
		$attach_uploadify_new = (get_param('attach_uploadify_new') ? get_param('attach_uploadify_new') : get_param('new'));
		
		$attach_uploadify_modify = (get_param('attach_uploadify_modify') ? get_param('attach_uploadify_modify') : get_param('modify'));
		
		$attach_uploadify_type = (get_param('attach_uploadify_type') ? get_param('attach_uploadify_type') : get_param('type'));
		
		$topic_textarea_id = (get_param('topic_textarea_id') ? get_param('topic_textarea_id') : get_param('content_id'));
		
		if(!is_null(get_param('topic_textarea_empty_val')))
		{
			$topic_textarea_empty_val = get_param('topic_textarea_empty_val');
		}		
		include($this->TemplateHandler->Template('attach_uploadify.inc'));
	}
	
	function Attach()
	{
		$attachtype = ('normal' == $this->Get['type']) ? false : true;
		$item = $this->Get['aitem'];
		$itemid = max(0, (int)($this->Get['aitemid']));
				$this->_init_auth();
				
				$field = 'topic';
		if (empty($_FILES) || !$_FILES[$field]['name']) 
		{
			$this->_attach_error('FILES is empty');
		}
		if($attachtype)
		{
			$_FILES[$field]['name'] = array_iconv('UTF-8', $this->Config['charset'], $_FILES[$field]['name']); 		}
		
				$uid = MEMBER_ID;
		$username = MEMBER_NAME;
		
				$attach_id = $this->AttachLogic->add($uid, $username, $item, $itemid);
		if($attach_id < 1)
		{
			$this->_attach_error('write database is invalid');
		}
		
		
		$this->AttachLogic->clear_invalid();
		$attach_size = min((is_numeric($this->Config['attach_size_limit']) ? $this->Config['attach_size_limit'] : 1024),5120);		
		
				
		
		$attach_path = RELATIVE_ROOT_PATH . 'data/attachs/' . $field . '/' . face_path($attach_id);
		$attach_type = strtolower(end(explode('.', $_FILES[$field]['name'])));
		$attach_name = $attach_id . '.' . $attach_type;
		$attach_file = $attach_path . $attach_name;
		if (!is_dir($attach_path)) 
		{
			Load::lib('io', 1)->MakeDir($attach_path);
		}
		
				Load::lib('upload');
		$UploadHandler = new UploadHandler($_FILES,$attach_path,$field,false,true);		
		$UploadHandler->setMaxSize($attach_size);
		$UploadHandler->setNewName($attach_name);
		$ret = $UploadHandler->doUpload();
		if($ret) 
		{
						$ret = true;
		}
		
				if(!$ret)
		{
			Load::lib('io', 1)->DeleteFile($attach_file);
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_attach where `id`='$attach_id'");
			$rets = $UploadHandler->getError();
			$ret = ($rets ? implode(" ", (array) $rets) : 'attach file is invalid');
			
			$this->_attach_error($ret);
		}
				
				$site_url = '';
		if($this->Config['ftp_on'])
		{
			$site_url = ConfigHandler::get('ftp','attachurl');
			
			$ftp_result = ftpcmd('upload',$attach_file);
			if($ftp_result > 0)
			{
				ftpcmd('upload',$attach_file_small);				
				Load::lib('io', 1)->DeleteFile($attach_file);
			}
		}
		
				$attach_size = filesize($attach_file);
		$name = addslashes($_FILES[$field]['name']);
		
		$p = array(
			'id' => $attach_id,		
			'site_url' => $site_url,
			'file' => $attach_file,
			'name' => $name,
			'filetype' => $attach_type,
			'filesize' => $attach_size,
		);
		$this->AttachLogic->modify($p);
		
		
				$retval = array(
			'id' => $attach_id,
			'src' => 'images/filetype/'.$attach_type.'.gif',
			'name' => $name,
		);
		$this->_attach_result('ok',$retval);
	}
	
		function DeleteAttach()
	{
		$this->initMemberHandler();
		if(MEMBER_ID < 1)
		{
			json_error("请先登录或者注册一个帐号");
		}
		
		$id = jget('id','int');
		$topic_attach = $this->AttachLogic->get_info($id);
		if(!$topic_attach)
		{
			json_error('请指定一个正确的文件ID');
		}
		
		if($topic_attach['uid'] != MEMBER_ID && 'admin' != MEMBER_ROLE_TYPE)
		{
			json_error('您无权删除该文件');
		}
		
		
		$ret = $this->AttachLogic->delete($id);
		if(!$ret)
		{
			json_error('删除失败');
		}
		
		
		json_result('删除成功');		
	}
	
	function _init_auth()
	{		
		$type = ($this->Post['type'] ? $this->Post['type'] : $this->Get['type']);
		$this->Type = $type;
		
		if('normal' == $type)
		{
			$this->initMemberHandler();
						if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
			{
				$this->_attach_error('您没有上传文件的权限，无法继续操作！');
			}
		}
		else 
		{
			$uid = 0;
			$password = '';		
			$members = array();
			
			$cookie_auth = ($this->Post['cookie_auth'] ? $this->Post['cookie_auth'] : $this->Get['cookie_auth']);
					
			list($password,$uid) = ($cookie_auth ? explode("\t", authcode(str_replace(' ', '+', $cookie_auth), 'DECODE')) : array('', 0));
			
			if($uid > 0)
			{
				$members = $this->DatabaseHandler->FetchFirst("select `uid`, `username`, `nickname`, `role_type`, `role_id` from ".TABLE_PREFIX."members where `uid`='$uid'");
			}
			
			if(!$members)
			{
				json_error('auth is invalid');
			}
			else 
			{
				$role_id = $members['role_id'];
				$role_privilege = $this->DatabaseHandler->ResultFirst("select `privilege` from ".TABLE_PREFIX."role where `id`='$role_id'");
				$current_action_id = $this->DatabaseHandler->ResultFirst("select `id` from ".TABLE_PREFIX."role_action where `module`='uploadattach' and `action`='attach'");
				if(strpos(",".$role_privilege.",",",".$current_action_id.",")===false)
				{
					json_error('forbidden');
				}
				else
				{
					$topic_uid = jget('topic_uid','int');
					if($topic_uid > 0 && $topic_uid != $uid && 'admin'==$members['role_type'])
					{
						$members = $this->DatabaseHandler->FetchFirst("select `uid`, `username`, `nickname`, `role_type` from ".TABLE_PREFIX."members where `uid`='$topic_uid'");
					}
					define('MEMBER_ID', $members['uid']);
					define('MEMBER_NAME', $members['username']);
					define('MEMBER_NICKNAME', $members['nickname']);
					define('MEMBER_ROLE_TYPE', $members['role_type']);
				}
			}
		}
	}
	
	function _attach_error($msg)
	{
		if('normal' == $this->Type)
		{
						echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');
			window.parent.attachUploadifyAllComplete{$attach_uploadify_id}();</script>";
			exit ;
		}
		else 
		{
			json_error($msg);
		}
	}
	function _attach_result($msg, $retval=null)
	{
		if('normal' == $this->Type)
		{
						$attach_uploadify_id = ($this->Post['attach_uploadify_id'] ? $this->Post['attach_uploadify_id'] : $this->Get['attach_uploadify_id']);
			
			echo "<script type='text/javascript'>
			window.parent.attachUploadifyComplete{$attach_uploadify_id}('{$retval['id']}', '{$retval['src']}', '{$retval['name']}');
			window.parent.attachUploadifyAllComplete{$attach_uploadify_id}('{$retval['name']}');
			</script>";
			exit ;
		}
		else 
		{
			json_result($msg, $retval);
		}
	}
	
}

?>
