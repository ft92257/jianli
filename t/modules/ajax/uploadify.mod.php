<?php
/**
 * 文件名：uploadify.mod.php
 * 版本号：1.0
 * 最后修改时间：2011年6月22日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 测试模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ImageLogic;
	
	var $Type;
	

	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		$this->ImageLogic = Load::logic('image', 1);

		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'image':
				$this->Image();
				break;
			case 'delete_image':
				$this->DeleteImage();
				break;
				
			case 'html':
				$this->Html();
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
	
	function Html()
	{
		$tid = max(0, (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
		$image_uploadify_topic = array();
		if($tid > 0)
		{
			
			$TopicLogic = Load::logic('topic', 1);
			
			$image_uploadify_topic = $TopicLogic->Get($tid);
		}
				
		
		$from = (get_param('image_uploadify_from') ? get_param('image_uploadify_from') : get_param('from'));
		$image_uploadify_from = '';
		if('topic_publish' == $from)
		{
			$image_uploadify_from = $from;
		}
		
		
		$only_js = (get_param('image_uploadify_only_js') ? get_param('image_uploadify_only_js') : get_param('only_js'));
		$image_uploadify_only_js = 0;
		if($only_js)
		{
			$image_uploadify_only_js = 1;
		}
		
		
		$topic_uid = max(0, (int) (get_param('image_uploadify_topic_uid') ? get_param('image_uploadify_topic_uid') : get_param('topic_uid')));
		$image_uploadify_topic_uid = 0;
		if($topic_uid)
		{
			$image_uploadify_topic_uid = $image_uploadify_topic['uid'];
		}
		
		
		$image_small_size = max(0, (int) (get_param('image_uploadify_image_small_size') ? get_param('image_uploadify_image_small_size') : get_param('image_small_size')));
		$image_uploadify_image_small_size = 45;
		if($image_small_size)
		{
			$image_uploadify_image_small_size = $image_small_size;
		}
		
		
		$image_uploadify_new = (get_param('image_uploadify_new') ? get_param('image_uploadify_new') : get_param('new'));
		
		$image_uploadify_modify = (get_param('image_uploadify_modify') ? get_param('image_uploadify_modify') : get_param('modify'));
		
		$image_uploadify_type = (get_param('image_uploadify_type') ? get_param('image_uploadify_type') : get_param('type'));
		
		$content_textarea_id = (get_param('content_textarea_id') ? get_param('content_textarea_id') : get_param('content_id'));
		
		if(!is_null(get_param('content_textarea_empty_val')))
		{
			$content_textarea_empty_val = get_param('content_textarea_empty_val');
		}
		
		
		
		include($this->TemplateHandler->Template('image_uploadify.inc'));
	}
	
	function Image() {
				$this->_init_auth();
		
		$imgtype = ('normal' == $this->Type) ? false : true;
		if($imgtype) {
			$_FILES[$field]['name'] = array_iconv('UTF-8', $this->Config['charset'], $_FILES[$field]['name']); 		}
		$item = $this->Get['iitem'];
		$itemid = max(0, (int)($this->Get['iitemid']));
		$p = array(
			'item' => $item,
			'itemid' => $itemid,			
		);
		$rets = $this->ImageLogic->upload($p);
		if($rets['code']<0 && $rets['error']) {
			$this->_image_error($rets['error']);
		}
		
				$retval = array(
			'id' => $rets['id'],
			'src' => $rets['src'],
			'name' => $rets['name'],
		);
		$this->_image_result('ok',$retval);
	}
	
		function DeleteImage()
	{
		$this->initMemberHandler();
		if(MEMBER_ID < 1)
		{
			json_error("请先登录或者注册一个帐号");
		}
		
		$id = max(0, (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']));
		$topic_image = $this->ImageLogic->get_info($id);
		if(!$topic_image)
		{
			json_error('请指定一个正确的图片ID');
		}
		
		if($topic_image['uid'] != MEMBER_ID && 'admin' != MEMBER_ROLE_TYPE)
		{
			json_error('您无权删除该图片');
		}
		
		
		$ret = $this->ImageLogic->delete($id);
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
				$members = $this->DatabaseHandler->FetchFirst("select `uid`, `username`, `nickname`, `role_type` from ".TABLE_PREFIX."members where `uid`='$uid'");
			}
			
			if(!$members)
			{
				json_error('auth is invalid');
			}
			else 
			{
				$topic_uid = max(0, (int) ($this->Post['topic_uid'] ? $this->Post['topic_uid'] : $this->Get['topic_uid']));
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
		
	function _image_error($msg)
	{
		if('normal' == $this->Type)
		{
						echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');</script>";
			exit ;
		}
		else 
		{
			json_error($msg);
		}
	}
	function _image_result($msg, $retval=null)
	{
		if('normal' == $this->Type)
		{
			$image_uploadify_id = ($this->Post['image_uploadify_id'] ? $this->Post['image_uploadify_id'] : $this->Get['image_uploadify_id']);
			
			echo "<script type='text/javascript'>
			window.parent.imageUploadifyComplete{$image_uploadify_id}('{$retval['id']}', '{$retval['src']}', '{$retval['name']}');
			window.parent.imageUploadifyAllComplete{$image_uploadify_id}();
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
