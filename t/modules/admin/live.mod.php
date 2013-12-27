<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename live.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 607466306 1003973716 4511 $
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

		Load::logic('live');
		$this->LiveLogic = new LiveLogic($this);
		
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{	
			case 'delete':
				$this->delete();
				break;			
			case 'edit':
				$this->edit();
				break;
		  	case 'doedit':
				$this->doedit();
				break;
		  	case 'batch':
		  		$this->batch();
		  		break;
		  	case 'add':
		  		$this->add();
		  		break;
		  	case 'doadd':
		  		$this->doadd();
		  		break;
			case 'config':
		  		$this->config();
		  		break;
		  	case 'doconfig':
		  		$this->doconfig();
		  		break;
			default:
				$this->Code = 'index';
				$this->index();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}

	function config()
	{
		$setting = true;
		$config = ConfigHandler::get('live');
		include template('admin/live');
	}

	function doconfig()
	{
		$config = $this->Post['config'];
		$live_config['user'] = trim($config['user']);
		$live_config['des'] = trim($config['des']);
		$live_config['ads'] = trim($config['ads']);
		$uid = DB::result_first("SELECT uid FROM ".DB::table("members")." WHERE nickname = '$live_config[user]'");
		if($uid){
			$live_config['uid'] = $uid;
			ConfigHandler::set('live',$live_config);
			$this->Messager("配置成功");
		}else{
			$this->Messager("用户 <b>".$live_config['user']."</b> 不存在！");
		}
	}
	
	function index()
	{
		$config = ConfigHandler::get('live');
		if(!$config){
			$setting = true;
			include template('admin/live');
		}else{
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$gets = array(
			'mod' => 'live',
			'pn' => $this->Get['pn'],
		);
		$page_url = 'admin.php?'.url_implode($gets);

		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table("live"));
		$list = array();$guestall = array();
		if ($count) {
			$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array',),'20 50 100 200,500');
			$query = DB::query("SELECT * 
								FROM ".DB::table("live")."
								ORDER BY lid DESC 
								{$page_arr['limit']}");
			$lids = array();
			while ($value = DB::fetch($query)) {
				$value['datetime'] = date('Y-m-d H:i',$value['starttime']).'：'.date('Y-m-d H:i',$value['endtime']);
				$lids[]=$value['lid'];
				$list[$value['lid']] = $value;
			}
			$guestall = $this->LiveLogic->Getguest($lids);
			foreach($guestall as $key => $val){
				$h = '';
				foreach($val['host'] as $k => $v){
					$h .= $v['nickname'].' ';
				}
				$list[$key]['hosts'] = $h;
			}
		}
		include template('admin/live_index');
		}
	}
	
		function batch()
	{
		$del_ids = $this->Post['del_ids'];
		$recd_ids = $this->Post['recd_ids'];
		$lids = $this->Post['lids'];
		if (!empty($del_ids)) {
			$this->LiveLogic->delete($del_ids);
		}
		$this->Messager("操作成功了");
	}
	
	
	function delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		$return = $this->LiveLogic->delete($ids);
		if (empty($return)) {
			$this->Messager("操作失败");
		}
		$this->Messager("操作成功");
	}
	
		function edit()
	{
		$lid = jget('lid','int');
		if(!$this->LiveLogic->is_exists($lid)){$this->Messager("当前直播不存在");}
		$live = $this->LiveLogic->get_liveinfo($lid);
		$hosts = $live['host'];
		$guests = $live['guest'];
		$medias = $live['media'];

		$do = 'doedit';
		include template('admin/live');
	}
	
	function doedit()
	{
		$lid = jget('lid','int');
		if(!$this->LiveLogic->is_exists($lid)){$this->Messager("当前直播不存在");}
		$live = $this->LiveLogic->get_liveinfo($lid);
		$post = &$this->Post;
		$return = $this->LiveLogic->dopost($post,'edit');
		$this->Messager($return);
	}

		function add()
	{
		$do = 'doadd';
		$lid = 0;
		include template('admin/live');
	}
	
	function doadd()
	{
		$post = &$this->Post;
		$return = $this->LiveLogic->dopost($post);
		$this->Messager($return);
	}
}
?>
