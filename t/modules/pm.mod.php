<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename pm.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 1170394986 1826950122 8936 $
 *******************************************************************/



if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $Code = array();

	
	var $ID = 0;
	
	
	var $Stat=array();
	
	var $Folder='inbox';
	
	var $FolderName='';


	
	var $IDS;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = jget('id', 'int');
		
		if (MEMBER_ID < 1) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",null);
		}

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'list':
				$this->PmList();
				break;
			case 'send':
				$this->Send();
				break;
			case 'dosend':
				$this->DoSend();
				break;
			case 'sendagain':
				$this->sendAgain();
				break;
			case 'history':
				$this->History();
				break;
			default:
				$this->PmList();
				break;
		}
		$Contents=ob_get_clean();
		$this->ShowBody($Contents);
	}

		function History(){
		$uid = MEMBER_ID;
		$touid = (int) $this->Get['uid'];
		
		load::logic('pm');
		$PmLogic = new PmLogic();
		
		$page = array();
		$page['per_page_num'] = 20;
		$page['query_link'] = "index.php?mod=pm&code=history&uid=$touid";
		
		if($touid == 0){
			$return_arr = $PmLogic->getNotice($page);
		}else{
			$return_arr = $PmLogic->getHistory($uid,$touid,$page);
		}
		extract($return_arr);
		
		$member = jsg_member_info(MEMBER_ID);
				
		$TopicLogic = Load::logic('topic', 1);
		if ($member['medal_id']) {
			$medal_list = $TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		
		$this->Title = '私信对话列表';
		include $this->TemplateHandler->Template('pm_history_list');
	}
	
	
	function sendAgain(){
		$pmid = (int) $this->Get['pmid'];
		$pm_list = array();
		$query = $this->DatabaseHandler->Query("select * FROM ".TABLE_PREFIX."pms WHERE pmid = '$pmid'");
		$pm_list = $query->GetRow();
		if($pm_list){
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid' ");
			$this->Post['to_user'] = $pm_list['tonickname'];
			$this->Post['message'] = $pm_list['message'];
			
			$this->DoSend('outbox');
		}else{
			$this->Messager("私信不存在或已删除");
		}
	}

	
	function PmList()
	{
		load::logic('pm');
		$PmLogic = new PmLogic();
		$member = jsg_member_info(MEMBER_ID);
		$folder = $this->Get['folder'] ? $this->Get['folder'] : 'inbox';
		$read = get_param('read');
		
				
		$TopicLogic = Load::logic('topic', 1);
		if ($member['medal_id']) {
			$medal_list = $TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$topic_selected = 'pm';
		
		if($member['newpm'])
		{
						$sql = "update `".TABLE_PREFIX."members` set `newpm`=0 where `uid`='{$member['uid']}'";
			$this->DatabaseHandler->Query($sql);
			$this->MemberHandler->MemberFields['newpm'] = 0;
		}
			
				$page['per_page_num'] = 20;
		$return_arr = array();
		
		if($folder == 'inbox')
		{
			$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&code={$this->Code}&folder=inbox" : "") . ($read ? "&read=1" : "");	
			$page['query_link'] = $query_link;
			$page['read'] = $read;
			$return_arr = $PmLogic->getPmList($folder,$page);
			$this->Title = '我的私信';
		}
		elseif($folder=='outbox')
		{
			$query_link = "mod=pm&code=list&folder=outbox";
			$page['query_link'] = $query_link;
			$return_arr = $PmLogic->getPmList($folder,$page);
			$this->Title = '草稿箱';
		}
		
		extract($return_arr);

		$left_menu=$this->LeftMenu();

		include $this->TemplateHandler->Template('pm_list');

	}

	
	function Send()
	{
		$member = jsg_member_info(MEMBER_ID);
				
		$TopicLogic = Load::logic('topic', 1);
		if ($member['medal_id']) {
			$medal_list = $TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		
		$topic_selected = 'pm';
		
		$this->Title='发送新消息';
		$action="index.php?mod=pm&code=dosend";
		$to_user=$this->Get['to_user']?$this->Get['to_user']:$this->Post['to_user'];
		$subject=$this->Get['subject']?$this->Get['subject']:$this->Post['subject'];
	
		if($this->ID!=1)
		{
			$sql="select msgtoid,msgfrom,subject,message from ".TABLE_PREFIX.'pms'." where pmid = '{$this->ID}'";
			$query = $this->DatabaseHandler->Query($sql);
			$pm=$query->GetRow();
			
			if ($pm!=false) 
			{
				$to_user = $pm['msgfrom'];
				$subject="回复:".$pm['subject'];
				$pm['message'] = $pm['message'];
			}
		}
		$left_menu=$this->LeftMenu();

				$sql="select `id`,`uid`,`group_name`,`group_count` from ".TABLE_PREFIX.'group'." where `uid` = '".MEMBER_ID."' ";
		$query = $this->DatabaseHandler->Query($sql);
		$my_grouplist = array();
        while (false != ($row = $query->GetRow())) {
        	$my_grouplist[] = $row;
        }

		include $this->TemplateHandler->Template('pm_write');
	}

	
	function DoSend($folder='')
	{	
		if(MEMBER_ID < 0)
		{
		  $this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}		
		
		load::logic('pm');
		$PmLogic = new PmLogic();
		$return = $PmLogic->pmSend($this->Post);
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
			case '7':
			default:
				if($return && is_string($return)) {
					$this->Messager($return);
				}
				break;
		}
		$folder = $folder ? $folder : 'inbox';
  		$this->Messager(NULL,"index.php?mod=pm&code=list&folder=$folder");
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


	
	function LeftMenu()
	{
		$folder=$this->Get['folder']?$this->Get['folder']:'inbox';


		$left_menu_list=
		array
		(
			"短信操作"=>
			array
			(
				'send'=>array('name'=>"发送新消息",'link'=>"?mod=pm&code=send&folder=send",'icon'=>'write'),
			),
			"信箱类型"=>
			array
			(
				'inbox'=>array('name'=>"我的私信",'link'=>"?mod=pm&code=list&folder=inbox",'icon'=>'inbox','stat'=>" [<a  href='index.php?mod=pm&code=list&folder=inbox&filter=newpm' title='未读'>未读 <span id='pm_inbox_unread'>{$this->Stat['inbox_unread_count']}</span></a>] [总 <span id='pm_inbox'>{$this->Stat['inbox_count']}</span>]"),
				'outbox'=>array('name'=>"草稿箱",'link'=>"?mod=pm&code=list&folder=outbox",'icon'=>'send','stat'=>" [{$this->Stat['outbox_count']}]")
			)
		);
	
		ob_start();
		
		include $this->TemplateHandler->Template('pm_left_menu');
		Return ob_get_clean();
	}
}

?>