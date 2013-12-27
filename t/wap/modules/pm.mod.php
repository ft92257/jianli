<?php
/**
 * 文件名：pm.mod.php
 * 版本号：2.6.6
 * 最后修改时间：2011-6-8
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 私信模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;

	var $ID = '';


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->CacheConfig = ConfigHandler::get('cache');

		$this->ShowConfig = ConfigHandler::get('show');

		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();


		if('view' == $this->Code) {
			$this->View();
		} elseif ('pmdel' == $this->Code) {
			$this->PmDelete();
		} elseif ('dosend' == $this->Code) {
			$this->DoSend();
		} elseif ('pmsend' == $this->Code) {
			$this->PmSend();
		} elseif ('isnew' == $this->Code) {
			$this->IsNew();
		} else {
			$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
		$member = $this->_topicLogicGetMember(MEMBER_ID);
		$topic_selected = 'pm';

				$per_page_num = 20;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&code={$this->Code}&folder=inbox" : "");

		if($member['newpm'])
		{
						$sql = "update `".TABLE_PREFIX."members` set `newpm`=0 where `uid`='{$member['uid']}'";
			$this->DatabaseHandler->Query($sql);
			$this->MemberHandler->MemberFields['newpm'] = 0;
		}

		load::logic('pm');
		$PmLogic = new PmLogic();
		$folder = "inbox";
		$page['per_page_num'] = 20;
		$page['query_link'] = $query_link;
		$return_arr = $PmLogic->getPmList($folder,$page);

		$page_html = $return_arr['page_arr']['html'];
		$pm_list = $return_arr['pm_list'];

		
		$pm_hb = 'hb';

		$this->Title = '我的私信';
		include $this->TemplateHandler->Template('pm_list');

	}


		function View()
	{
		$title = '回复私信';

		$uid = (int) $this->Get['uid'];
		load::logic('pm');
		$PmLogic = new PmLogic();
		$page = array();
		$page['per_page_num'] = 20;
		$page['query_link'] = "index.php?mod=pm&code=history&uid=$touid";
		if($uid == 0){
			$return_arr = $PmLogic->getNotice($page);
		}else{
			$return_arr = $PmLogic->getHistory(MEMBER_ID,$uid,$page);
		}

		$pm_list = $return_arr['pm_list'];
		$page_html = $return_arr['page_arr']['html'];
		$nickname= wap_iconv($return_arr['nickname']);
		$this->Title = $title;
		include($this->TemplateHandler->Template('pm_view'));
	}

	function PmSend()
	{
		if(MEMBER_ID < 0) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}

		$uid = (int) get_param('uid');
		$nickname = get_param('nickname');

		$to_member = array();
		if($uid > 0) {
			$to_member = jsg_member_info($uid, 'uid');
		} elseif($nickname) {
			$to_member = jsg_member_info($nickname, 'nickname');
		}
		if($to_member) {
			$nickname = wap_iconv($to_member['nickname']);
		}

		$this->Title = '发送私信';
		include $this->TemplateHandler->Template('pm_send');
	}


		function DoSend()
	{
		if(MEMBER_ID < 0)
		{
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}


				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$this->Messager($this->MemberHandler->GetError(),null);
		}
		$pm_message = array(
			"to_user"=> wap_iconv($this->Post['to_user'],'utf-8',$this->Config['charset']),
			"message"=> wap_iconv($this->Post['message'],'utf-8',$this->Config['charset'])
		);
		$uid = (int) $this->Post['uid'];
		load::logic('pm');
		$PmLogic = new PmLogic();
		$return = $PmLogic->pmSend($pm_message);
		switch ($return){
			case '1':
				$this->Messager("内容不能为空");
				break;
			case '2':
				$this->Messager("收件人不能为空");
				break;
			case '3':
				$this->Messager("收件人不存在");
				break;
			case '4':
				$this->Messager("消息已经保存草稿箱","index.php?mod=pm&code=list&folder=outbox");
				break;
			case '5':
				$this->Messager("信息不存在或已删除");
			case '6':
				$this->Messager("所在用户组没有发私信的权限");
			default:
				if($return && is_string($return)) {
					$return = wap_iconv($return);
						
					$this->Messager($return);
				}
				break;
		}

		if($uid > 0) {
			$this->Messager("消息已发送成功","index.php?mod=pm&code=view&uid=$uid");
		} else {
			$this->Messager("消息已发送成功","index.php?mod=pm");
		}
	}

		function PmDelete()
	{

		$pmid = (int) ($this->Post['pmid'] ? $this->Post['pmid'] : $this->Get['pmid']);

		if ($pmid < 1) {
			$this->Messager('请指定一个您要删除的私信','index.php?mod=pm');
		}

				$sql = " select * from `".TABLE_PREFIX."pms`  where  `pmid` = '{$pmid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$pmlist = $query->GetRow();

		if($pmlist['msgtoid'] == MEMBER_ID)
		{
			$sql = "delete from `".TABLE_PREFIX."pms` where `pmid`='{$pmid}' and `msgtoid` =".MEMBER_ID;
			$this->DatabaseHandler->Query($sql);
		}

		$this->Messager(NULL,'index.php?mod=pm');
	}


		function IsNew()
	{
		
		$pmid = (int) ($this->Post['pmid'] ? $this->Post['pmid'] : $this->Get['pmid']);

		$this->DatabaseHandler->Query("update `".TABLE_PREFIX."pms` set new=1 where `pmid`='{$pmid}' ");

		$this->Messager(NULL,'index.php?mod=pm');
	}


		function UpdateNewMsgCount($num,$uids='')
	{
		if($uids=='')$uids=MEMBER_ID;

		$uids=$this->DatabaseHandler->BuildIn($uids,'uid');
		if(!$uids) return ;

		$strpos = strpos($num,'-');
		if($strpos!==0)
		{
			$num="+".$num;
		}

		$sql="
		UPDATE
			".TABLE_PREFIX.'members'."
		SET
			newpm=newpm $num
		WHERE
		$uids";
			
		$this->DatabaseHandler->Query($sql);

		$ret = $this->DatabaseHandler->AffectedRows();;

		if(0 === $strpos)
		{
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `newpm`=0 where $uids and `newpm`<0");
		}

		return $ret;
	}

}

?>
