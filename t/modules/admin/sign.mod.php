<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename sign.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 102556564 729942644 7275 $
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
		
		load::logic('topic');
		$this->TopicLogic = Load::logic('topic', 1);
		
		$this->Execute();
	}
	
	
	function Execute()
	{
		ob_start();
		
		switch ($this->Get['code'])
		{
			case 'credits_top':
			case 'sign_list':
				$this->SignList();
				break;
			case 'setfiled':
				$this->setFiled();
				break;
			case 'doact':
				$this->doAct();
				break;
			case 'dosetting':
				$this->doSetting();
				break;
			default:
				$this->main();
				break;		
		}
		
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
		function main(){
				$credits = array();
		$credits = ConfigHandler::get('credits');
		if(count($credits['ext']) < 1){
			$this->Messager("请先开启可用的积分字段","admin.php?mod=setting&code=modify_credits");
		}
		
		$sign[$this->Config['sign']['sign_enable'] ? $this->Config['sign']['sign_enable'] : 0] = " checked ";
		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."sign_tag limit 1");
		while ($rs = $query->GetRow()){
			$sign_tag = $rs['tag'];
			$ext[$rs['credits']] = " selected ";
		}

		include template('admin/sign_main');
	}

	
	function doSetting(){
		
				$sign_tag_arr = array();
		$sign_tag = get_param('sign_tag');
		if($sign_tag){
			$sign_tag_arr = explode("\r\n",$sign_tag);
		}
		$new_tag_arr = array();
		foreach ($sign_tag_arr as $val) {
			$val = trim($val);
			if($val){
				$new_tag_arr[$val] = $val;
			}
		}
		$sign_tag = implode("\r\n",$new_tag_arr);
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."sign_tag limit 1");
		$rs = $query->GetRow();
		$extcredits = get_param('extcredits');
		if($rs){
			$this->DatabaseHandler->Query("update `".TABLE_PREFIX."sign_tag` set `tag` = '".trim($sign_tag)."',`credits` = '$extcredits' where `id` = '{$rs[id]}'");
		}else{
			$this->DatabaseHandler->Query("insert into `".TABLE_PREFIX."sign_tag` set `tag` = '$sign_tag',`credits` = '$extcredits'");
		}
		$sign_enable = get_param('sign_enable');
				$config = array();
		$config['sign']['sign_enable'] = $sign_enable;
		ConfigHandler::update($config);
		
		$this->Messager("设置成功");
	}
	
	
	function setFiled(){
				$extcredits = $this->Post['extcredits'];
		$config = array();
		$config['credits_filed'] = $extcredits;
		
		ConfigHandler::update($config);
		
		unlink(ROOT_PATH."cache\misc\top_day7_r_sign.cache.php");
		cache_file('rm', "misc/top_day7_r_sign");
		$this->Messager("设置成功");
	}
	
	
	function SignList(){
				$nickname = $this->Get['nickname'] ? $this->Get['nickname'] : $this->Post['nickname'];
				$credits = $this->Post['credits'] ? (int) $this->Post['credits'] : $this->Get['credits'];
				$credit_rule = $this->Post['credits_rule'] ? $this->Post['credits_rule'] : $this->Get['credits_rule'];
		$field = $credit_rule ? $credit_rule : $this->Config['credits_filed'];
		$field = $field ? $field : 'extcredits2';
				$credits_list = ConfigHandler::get('credits');
		$credits_list['ext']['credits'] = array('name'=>'总积分');
		foreach ($credits_list['ext'] as $key=>$value) {
			if($credit_rule){
				$credits_list['ext'][$credit_rule]['che'] = " selected ";
			}else{
				if($key == $field){
					$credits_list['ext'][$field]['che'] = " selected ";
				}
			}
			if($this->Config['credits_filed'] == $key){
				$credits_list['ext'][$key]['select'] = " selected ";
			}
		}
		
				$sql = "select * from `".TABLE_PREFIX."medal` ";
		$query = $this->DatabaseHandler->Query($sql);
		$sign_medal = array();
		while ($rs = $query->GetRow()){
			$conditions = unserialize($rs['conditions']);
			$sign_medal[$rs['id']] = $rs['medal_name'];
		}
		
		$where_arr = array();		
				if($nickname){
			$where_arr['nickname'] = " m.`nickname` = '$nickname' ";
		}
				if($credits){
			$where_arr['credit'] = " m.`$field` >= '$credits' ";
		}
		
				$earned = $this->Get['earned'] ? $this->Get['earned'] : $this->Post['earned'];
		if($earned){
			$medal_arr[$earned] = " selected ";
			$medal_where = " LEFT JOIN ".TABLE_PREFIX."user_medal um ON um.uid = m.uid and um.medalid = '$earned' ";
			$select_sql = " ,um.dateline  ";
			$where_arr['earned'] = " um.`dateline` IS NULL ";
		}
		
		$where = "";
		if($where_arr){
			$where = " where ".implode(" and ",$where_arr);
		}
		
		$query_link = "admin.php?mod=sign&code=".$this->Get['code'];
		$per_page_num = 20;
		$sql = "
		  SELECT
			  count(*) as total_record 
		  FROM
			  " . TABLE_PREFIX.'members' . " m 
	      $medal_where 
		  $where ";
		$total_record = $this->DatabaseHandler->ResultFirst($sql);
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200,500');
		
		$sql = "SELECT
			        m.* $select_sql
		 		FROM " . TABLE_PREFIX."members m 
	      $medal_where 
		  $where 
		  order by m.`$field` desc 
		  $page_arr[limit] ";
		$query = $this->DatabaseHandler->Query($sql);
		$members = array();
		while ($rs = $query->GetRow()){
			$members[$rs['uid']] = $rs;
		}
		$msg = "你的积分系数已达到$credits";
		
		include template('admin/sign_list');
	}
	
		function doAct(){
	    $uids = get_param('uids');		
	    $act = get_param('act');
	    $msg = get_param('msg');
	    
	    if($act == 'sendmsg'){
	    	if($msg == ''){
	    		$this->Messager("请输入私信的内容",-1);
	    	}
	    	
	    	$admin_nickname = $this->DatabaseHandler->ResultFirst("select `nickname` from `".TABLE_PREFIX."members` where uid = 1 ");
	    	load::logic("pm");
			$PmLogic = new PmLogic();
	    	
			if($uids){
				$query = $this->DatabaseHandler->Query("select `nickname` from `".TABLE_PREFIX."members` where uid in (".jimplode($uids).")");
				$nickname_arr = array();
				while ($rs = $query->GetRow()){
					$nickname_arr[] = $rs['nickname'];
				}
			}
			if($nickname_arr){
				$post['to_user'] = implode(",",$nickname_arr);
				$post['message'] = $msg;
				$PmLogic->pmSend($post,1,'admin','admin',$admin_nickname);
			}
	    }elseif($act == 'setmedal'){
	    	$medal_id = get_param('medal_id');
	    	if($medal_id == ''){
	    		$this->Messager("请选择要发放的勋章",-1);
	    	}
	    	
	    	load::logic('other');
	    	$OtherLogic = new OtherLogic();
	    	
	    	foreach($uids as $val){
	    		$OtherLogic->giveUserMedal($medal_id,$val);
	    	}
	    	
	    }else{
	    	$this->Messager("请选择要执行的操作",-1);
	    }
	    
	    $this->Messager("操作成功");
	}
}