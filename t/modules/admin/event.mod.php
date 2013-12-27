<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename event.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 1629757248 2027503962 12257 $
 *******************************************************************/




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{	
						case 'verify':
				$this->Verify();
				break;
			case 'doverify':
				$this->DoVerify();
				break;

			case 'info':
				$this->info();
				break;
			case 'addevent':
				$this->addEvent();
				break;
			case 'chooseinfo':
				$this->chooseInfo();
				break;
			case 'delevent':
				$this->delEvent();
				break;
			case 'event':
				$this->event();
				break;
			case 'add':
				$this->add();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'del':
				$this->del();
				break;
			case 'manage':
				$this->eventManage();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'setting':
				$this->setting();
				break;
			case 'dosetting':
				$this->doSetting();
				break;
			case 'editevent':
				$this->editEvent();
				break;
			case 'doeditevent':
				$this->doEditEvent();
				break;
			default:
				$this->Code = 'index';
				$this->index();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function Verify(){
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));;
		$page_url = "admin.php?mod=event&code=verify";
		$where = "";
		$id = $this->Get['id'];
		$rs = array();
		
		if(!empty($id)){
			$id = (int)$this->Get['id'];
			$where = " and a.id = '$id' " ;
		}
		$type = $this->Get['type'];
		if(!empty($type)){
			$where .= " and a.title like '%$type%'";			
		}
		$sql = "select a.id,a.title,s.type,a.fromt,a.tot,a.postip,m.nickname,m.username,m.uid 
				from ".TABLE_PREFIX."event a 
				left join ".TABLE_PREFIX."event_sort s on s.id = a.type_id 
				left join ".TABLE_PREFIX."members m on m.uid = a.postman 
				where a.verify = 0 
			    $where 
			    order by a.lasttime desc ";
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event a where 1 $where and verify = 0 ");
		if($count){
			$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array',),'20 50 100 200 500');
			$sql .= $page_arr['limit'];
			$query = $this->DatabaseHandler->Query($sql);
			while ($rsdb = $query->GetRow()){
				$rs[$rsdb['id']] = $rsdb;
			}
		}
		include template('admin/event_manage');
	}
	
	
	function DoVerify(){
		$ids  = (array) ($this->Post['ids'] ? $this->Post['ids'] : (int)$this->Get['id']);
		$act = $this->Get['act'];
		
		foreach ($ids as $val) {
						if($act == 1){
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event set verify = 1 where id = '$val'");
						}else{
								$image = $this->DatabaseHandler->ResultFirst("select image from ".TABLE_PREFIX."event where id = '$val' ");
				if($image){
					$type = trim(strtolower(end(explode(".",$image))));
					$name = explode("_",$rsdb['image']);
					$image_s = $name[0]."_s.".$type;
					
				    unlink($image);
				    unlink($image_s);
				}
								$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."event where id = '$val' ");
			}
		}
		$this->Messager("操作成功");
	}
	
	
	function editEvent(){
		$id = (int) $this->Get['id'];
		$query = $this->DatabaseHandler->Query("select title,content,address,image from ".TABLE_PREFIX."event where id = '$id'");
		$val = $query->GetRow();
		include template('admin/event_edit');
	}
	
	
	function doEditEvent(){
		$post = $this->Post;
		if(!$post['title']){
			$this->Messager("请输入活动标题",-1);
		}
		if(!$post['address']){
			$this->Messager("请输入活动具体地址",-1);
		}
		if(!$post['content']){
			$this->Messager("请输入活动描述",-1);
		}
		
		if($post['del_pic'] == 1){
			$image = " ,image = ''";
		}
		$sql = "update ".TABLE_PREFIX."event 
				set title = '$post[title]', 
					content = '$post[content]',
					address = '$post[address]' 
					$image 
				where id = '$post[id]'";
		$this->DatabaseHandler->Query($sql);
		$this->Messager("修改成功");
	}
	
	
	function setting(){
		$config = $this->Config;
		$checked['open'][$config['event_open'] ? $config['event_open'] : 0] = "checked";
		$checked['verify'][$config['event_verify'] ? $config['event_verify'] : 0] = "checked";
		$checked['vip'][$config['event_vip'] ? $config['event_vip'] : 0] = "checked";
		include template('admin/event_setting');
	}
	
	
	function doSetting(){
		$config = array();
		$config['event_open'] = $this->Post['open'] ? $this->Post['open'] : 0;
		$config['event_verify'] = $this->Post['verify'] ? $this->Post['verify'] : 0;		
		$config['event_vip'] = $this->Post['vip'] ? $this->Post['vip'] : 0;
		ConfigHandler::update($config);
		$this->Messager('操作成功了');
	}
	
	
	function index(){
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_sort order by id");
		while($rsdb = $query->GetRow()){
			$rsdb['count'] = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event where type_id = '$rsdb[id]'");
			$event[$rsdb['id']] = $rsdb;
		}

		include template('admin/event_main');
	}
	
	
	function addEvent(){
				$name = $this->Post['name'];
		$name_arr = explode("\r\n",$name);
		foreach ($name_arr as $value) {
			if($value){
				$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."event_sort (type) values ('$value')");
			}
		}
		$this->Messager("活动创建成功","admin.php?mod=event");
	}
	
	
	function chooseInfo(){
		$che_arr = $this->Post['che'];
		$need_arr = array();
		foreach ($che_arr as $key => $val) {
			$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_needinfo where id = '$key'");
			$rsdb = $query->GetRow();
			$need_arr[$rsdb['id']] = $rsdb;
		}
		$need_info = serialize($need_arr);
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event_info set need_info = '$need_info'");
		
		$this->Messager("提交成功","admin.php?mod=event&code=info");
	}
	
	
	function delEvent(){
		$id = (int)$this->Get['id'];
		$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."event_sort where id = '$id'");
		$this->Messager("删除活动主题成功","admin.php?mod=event");
	}
	
	
	function info(){
		$info = array();
		
		$id = (int)$this->Get['id'];
		if($id && $id != 0){
			$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_needinfo where id = '$id'");
			$rs_info = $query->GetRow();
			if($rs_info['form_type'] == 'text'){
				$text = 'selected';
			}elseif ($rs_info['form_type'] == 'select'){
				$select = 'selected';
			}elseif ($rs_info['form_type'] == 'checkbox'){
				$checkbox = 'selected';
			}
			$name = $rs_info['name'];
			$form_set = $rs_info['form_set'];
			$act = 'edit';
			$ename = $rs_info['ename'];
		}else{
			$act = 'new';
			$text = 'selected';
		}
		
				$need_info = $this->DatabaseHandler->ResultFirst("select need_info from ".TABLE_PREFIX."event_info");
		$info_arr = unserialize($need_info);
		foreach ($info_arr as $key => $val) {
			$key_arr[$key] = $key;
		}
		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."event_needinfo");
		while ($rs = $query->GetRow()){
						if(in_array($rs[id],$key_arr)){
				$rs['che'] = 'checked';
			}
			
			if($rs['form_type'] == 'text'){
				$rs['form_type'] = '文本框';
			}
			if($rs['form_type'] == 'select'){
				$rs['form_type'] = '下拉框';
			}
			if($rs['form_type'] == 'checkbox'){
				$rs['form_type'] = '复选框';
			}
			$info[$rs['id']] = $rs;
		}
		include template('admin/event_needinfo');
	}

	
	function event(){
		$post = $this->Post;
		$act = $post['act'];
		
		if(trim($post['name']) == ''){
			$this->Messager("请输入栏目名称",-1);
		}
		if(trim($post['ename']) == ''){
			$this->Messager("请输入栏目英文名称",-1);
		}
		if(!eregi("^[a-z]*",trim($post['ename']))){
			$this->Messager("请输入栏目英文名称",-1);
		}
		$hid_ename = $this->Post['hid_ename'];
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event_needinfo where `ename` = '".trim($this->Post[ename])."' and `ename` != '$hid_ename'");
		if($count>0){
			$this->Messager("栏目英文名已存在",-1);
		}
		
		if($post[form_type] == 'text'){
			$post[form_set] = '';
		}
		
		if($act == 'new'){
			$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."event_needinfo (name,form_type,form_set,ename) values ('$post[name]','$post[form_type]','$post[form_set]','".trim($this->Post[ename])."')");
			$this->Messager("创建成功","admin.php?mod=event&code=info");
		}elseif ($act == 'edit'){
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event_needinfo set name = '$post[name]' , form_type = '$post[form_type]' , form_set = '$post[form_set]' , ename='".trim($this->Post[ename])."' where id = '{$post[id]}'");
			$this->Messager("编辑成功","admin.php?mod=event&code=info");
		}
	}

	
	function del(){
		$id = (int)$this->Get['id'];
		$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."event_needinfo where id = '$id'");
		$this->Messager("项目删除成功","admin.php?mod=event&code=info");
	}
	
	
	function eventManage(){
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));;
		$page_url = "admin.php?mod=event&code=manage";
		$where = "";
		$id = $this->Get['id'];
		$rs = array();
				if(!empty($id)){
			$id = (int)$this->Get['id'];
			$page_url .= "&id=$id";
			$where .= " and a.id = '$id' " ;
		}
				$type = $this->Get['type'];
		if(!empty($type)){
			$page_url .= "&type=$type";
			$where .= " and a.title like '%$type%'";			
		}
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where .= " and `lasttime`>'$str_time_from'";
			$page_url .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where .= " and `lasttime`<'$str_time_to'";
			$page_url .= "&timeto=".$timeto;
		}
		$sql = "select a.id,a.title,s.type,a.app_num,a.play_num,a.fromt,a.tot,a.recd,m.nickname,a.postman,a.postip 
				from ".TABLE_PREFIX."event a 
				left join ".TABLE_PREFIX."event_sort s on s.id = a.type_id 
				left join ".TABLE_PREFIX."members m on m.uid = a.postman 
				where 1 
			    $where 
			    and a.verify = 1 
			    order by a.lasttime desc ";
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."event a where verify = 1 $where ");
		if($count){
			$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array',),'20 50 100 200,500');
			$sql .= $page_arr['limit'];
			$query = $this->DatabaseHandler->Query($sql);
			while ($rsdb = $query->GetRow()){
				if($rsdb['recd'] == 1){
					$rsdb['recd_checked'] = ' checked ';
				}
				$rs[$rsdb['id']] = $rsdb;
			}
		}
		include template('admin/event_manage');
	}
	
	
	function delete(){
		$ids = array();
		$up_ids = array();
		$id  = (int)$this->Get['id'];
		if($id){
			$ids[] = $id;
		}
		if($this->Post['cronssubmit']){
			$ids = array($this->Post['ids']);
			$up_ids = $this->Post['up_id'];
			$vid = $this->Post['vid'];
		}

		if(!empty($vid)){
			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event set recd = 0 where id in ('".implode("','",$vid)."')");
			foreach ($vid as $val) {
				if(!in_array($val,$ids) && in_array($val,$up_ids)){
					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."event set recd = 1 where id = '$val' ");
				}
			}
		}
		
		load::logic('event');
		$eventLogic = new EventLogic();

		if($ids){
			foreach ($ids as $val) {
				$eventLogic->delEvent($val,1);
			}
		}
		$this->Messager("操作成功");
	}
}
?>