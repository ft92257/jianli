<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename talk.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 996476363 1241930424 7009 $
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

		Load::logic('talk');
		$this->TalkLogic = new TalkLogic($this);
		
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
			case 'category':
				$this->category();
				break;
			case 'docategory':
				$this->docategory();
				break;
			case 'delcat':
				$this->delcat();
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
		$config = ConfigHandler::get('talk');
		include template('admin/talk');
	}

	function doconfig()
	{
		$config = $this->Post['config'];
		$talk_config['user'] = trim($config['user']);
		$talk_config['des'] = trim($config['des']);
		$talk_config['ads'] = trim($config['ads']);
		$uid = DB::result_first("SELECT uid FROM ".DB::table("members")." WHERE nickname = '$talk_config[user]'");
		if($uid){
			$talk_config['uid'] = $uid;
			ConfigHandler::set('talk',$talk_config);
			$this->Messager("配置成功");
		}else{
			$this->Messager("用户 <b>".$talk_config['user']."</b> 不存在！");
		}
	}
	
	function index()
	{
		$config = ConfigHandler::get('talk');
		if(!$config){
			$setting = true;
			include template('admin/talk');
		}else{
		$per_page_num = min(500,max((int) $_GET['per_page_num'],(int) $_GET['pn'],20));
		$gets = array(
			'mod' => 'talk',
			'pn' => $this->Get['pn'],
		);
		$page_url = 'admin.php?'.url_implode($gets);

		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table("talk"));
		$list = array();
		if ($count) {
			$page_arr = page($count,$per_page_num,$page_url,array('return'=>'array',),'20 50 100 200,500');
			$query = DB::query("SELECT * 
								FROM ".DB::table("talk")."
								ORDER BY lid DESC 
								{$page_arr['limit']}");
			$lids = array();
			while ($value = DB::fetch($query)) {
				$value['datetime'] = date('Y-m-d H:i',$value['starttime']).'：'.date('Y-m-d H:i',$value['endtime']);
				$lids[]=$value['lid'];
				$list[$value['lid']] = $value;
			}
			$guestall = $this->TalkLogic->Getguest($lids);
			foreach($guestall as $key => $val){
				$h = '';
				foreach($val['guest'] as $k => $v){
					$h .= $v['nickname'].' ';
				}
				$list[$key]['guests'] = $h;
			}
		}
		include template('admin/talk_index');
		}
	}
	
		function batch()
	{
		$del_ids = $this->Post['del_ids'];
		$recd_ids = $this->Post['recd_ids'];
		$lids = $this->Post['lids'];
		if (!empty($del_ids)) {
			$this->TalkLogic->delete($del_ids);
		}
		$this->Messager("操作成功了");
	}
	
	
	function delete()
	{
		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		if(!$ids) {
			$this->Messager("请指定要删除的对象");
		}
		$return = $this->TalkLogic->delete($ids);
		if (empty($return)) {
			$this->Messager("操作失败");
		}
		$this->Messager("操作成功");
	}
	
		function edit()
	{
		$lid = jget('lid','int');
		if(!$this->TalkLogic->is_exists($lid)){$this->Messager("当前访谈不存在");}
		$talk = $this->TalkLogic->get_talkinfo($lid);
		$catselect = $this->TalkLogic->get_catselect(0, $talk['cat_id']);
		$hosts = $talk['host'];
		$guests = $talk['guest'];
		$medias = $talk['media'];
		$do = 'doedit';
		include template('admin/talk');
	}
	
	function doedit()
	{
		$lid = jget('lid','int');
		if(!$this->TalkLogic->is_exists($lid)){$this->Messager("当前访谈不存在");}
		$talk = $this->TalkLogic->get_talkinfo($lid);
		$post = &$this->Post;
		$return = $this->TalkLogic->dopost($post,'edit');
		$this->Messager($return);
	}

		function add()
	{
		$do = 'doadd';
		$lid = 0;
		$catselect = $this->TalkLogic->get_catselect();
		include template('admin/talk');
	}
	
	function doadd()
	{
		$post = &$this->Post;
		$return = $this->TalkLogic->dopost($post);
		$this->Messager($return);
	}
	
	
	function category()
	{
		$tree = $this->TalkLogic->get_category_tree();
		include template('admin/talk_category');
	}

	function docategory()
	{
				$cat_ary = &$this->Post['cat'];
		if (!empty($cat_ary)) {
			$cat_order_ary = &$this->Post['cat_order'];
			foreach ($cat_ary as $key => $cat) {
				$cat_name = getstr($cat, 30, 1, 1);
								$display_order = intval($cat_order_ary[$key]);
				$this->TalkLogic->update_category($key, $cat_name, $display_order);
			}
		}
		
				$tcat_ary = &$this->Post['new_tcat'];
		if (!empty($tcat_ary)) {
			$tcat_order_ary = &$this->Post['new_tcat_order'];
			$this->_batch_add_category($tcat_ary, $tcat_order_ary);
		}
		
				$scat_ary = &$this->Post['new_scat'];
		if (!empty($scat_ary)) {
			$scat_order = &$this->Post['new_scat_order'];
			foreach ($scat_ary as $p => $cats) {
				$this->_batch_add_category($cats, $scat_order[$p], $p);
			}
		}
		
				$this->TalkLogic->update_category_cache();
		$this->Messager('操作成功了');
	}
	
	
	function _batch_add_category($cat_ary, $order_ary, $parent_id = 0)
	{
		foreach ($cat_ary as $key => $cat) {
						$cat_name = getstr($cat, 30, 1, 1);
			if (empty($cat_name) || $this->TalkLogic->category_exists($cat_name, $parent_id)) {
				continue;
			}
			$display_order = intval($order_ary[$key]);
			$this->TalkLogic->add_category($cat_name, $display_order, $parent_id);
		}
	}
	
	
	function delcat()
	{
		$cat_id = jget('cat_id','int');
		if (empty($cat_id)) {
			$this->Messager('没有指定分类ID');
		}
		
		$ret = $this->TalkLogic->delete_category($cat_id);
		
				$this->TalkLogic->update_category_cache();
		
		if ($ret == 1) {
			$this->Messager('删除分类成功');
		} else if ($ret == -1) {
			$this->Messager('当前分类不存在');
		} else if ($ret == -2) {
			$this->Messager('当前分类下面已经有访谈节目存在，不能被删除');
		} else if ($ret == -3) {
			$this->Messager('当前分类存在下级子分类，请先删除该分类下的子分类');
		}
	}
}
?>
