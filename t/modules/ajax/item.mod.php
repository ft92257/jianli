<?php
/**
 * 文件名：item.mod.php
 * 版本号：1.0
 * 最后修改时间：2011年12月28日
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 应用操作的AJAX类,目前在live与talk里用到
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
		
	function ModuleObject(& $config)
	{
		$this->MasterObject($config);
		$this->initMemberHandler();
		if (!MEMBER_ID) {
			js_alert_output("请先登录或者注册一个帐号");
		}
		Load::logic('live');
		$this->LiveLogic = new LiveLogic();
		Load::logic('talk');
		$this->TalkLogic = new TalkLogic();
		$this->Execute();
	}

	
	function Execute()
	{
		switch ($this->Code)
		{
			
			case 'checkname':
				$this->Checkname();
				break;
			case 'del':
				$this->Del();
				break;
			case 'sms':
				$this->Sms();
				break;
			case 'publishbox':
				$this->PublishBox();
				break;
			case 'second_cat':
				$this->second_cat();
				break;
			default:
				$this->Main();
				break;
		}
	}
	
	
	function Main()
	{
		response_text("正在建设中……");
	}

	
	function PublishBox()
	{
		$type = trim($this->Get['type']);
		$this->item = $item = trim($this->Get['item']);
		$this->item_id = $itemid = jget('itemid','int','G');
		$this->totid = $totid = jget('totid','int','G');
		$this->touid = $touid = jget('touid','int','G');
		$url = get_full_url('',$this->Config['site_url'].'/index.php?mod='.$item.'&code=view&id='.$itemid);
		$users = '';
		if($item == 'live'){
			$live = $this->LiveLogic->id2liveinfo($itemid);
			if(!empty($live) && $live['host_guest']){
				foreach($live['host_guest'] as $key => $val){
					$users .= ' @'.$val.' ';
				}
			}
			$defaust_value = '发条微博，把直播推荐给朋友';
			$content = '给大家推荐一个不错的直播，来看看吧：“'.$live['livename'].'”，直播时间:'.$live['date'].'&nbsp;'.$live['time'].'，主持和嘉宾们 '.$users.'都很给力呦。直播地址：'.$url;
		}elseif($item == 'talk'){
			$talk = $this->TalkLogic->id2talkinfo($itemid);
			if(!empty($talk) && $talk['guest']){
				foreach($talk['guest'] as $key => $val){
					$users .= ' @'.$val['nickname'].' ';
				}
			}
			if($type == 'ask'){
				if($touid > 0){
					$username = '@'.DB::result_first("SELECT nickname FROM ".DB::table('members')." WHERE uid='{$touid}'");
				}else{
					$username = $users;
				}
				$defaust_value = '发条微博，向嘉宾提问';
				$content = '向 '.$username.' 提问: ';
			}elseif($type == 'answer'){
				$username =  '@'.DB::result_first("SELECT nickname FROM ".DB::table('members')." WHERE uid='{$touid}'");
				$defaust_value = '发条微博，回答网友提问';
				$content = '对 '.$username.' 回复: ';
			}else{
				$defaust_value = '发条微博，把访谈推荐给朋友';
				$talk['time'] = str_replace('<br>','',$talk['time']);
				$content = '给大家推荐一个不错的访谈: “'.$talk['talkname'].'”，访谈嘉宾 '.$users.' 。访谈进行时间'.$talk['date'].'&nbsp;'.$talk['time'].'。赶紧去提问吧。'.$url;
			}
		}
		$this->Code = $type;
		include(template('topic_publish_ajax'));
		exit;
	}
	
	
	function Checkname()
	{
		$nickname=trim($this->Post['nickname']);
		$item=trim($this->Post['item']);
		$itemid=(int)$this->Post['itemid'];
		$uid = DB::result_first("SELECT uid FROM `".DB::table('members')."` WHERE nickname = '$nickname'");
		if($uid){
			$count = DB::result_first("SELECT COUNT(*) FROM `".DB::table('item_user')."` WHERE item = '$item' AND itemid = '$itemid' AND uid = '$uid'");
			if($count){
				$return = -1;
			}else{
				$return = $uid;
			}
		}else{
			$return = -2;
		}
		response_text($return);
	}
		
		function Del()
	{
		$id = (int)$this->Post['id'];
				if($id > 0) {
			DB::Query("DELETE FROM `".DB::table('item_user')."` WHERE iid ='$id'");
		}
	}

		function Sms()
	{
		$uid = (int)$this->Post['uid'];
		$item = trim($this->Post['item']);
		$itemid = (int)$this->Post['itemid'];
		if($item == 'live'){
			$isdesign = $this->LiveLogic->is_design($uid,$itemid);
		}elseif($item == 'talk'){
			$isdesign = $this->TalkLogic->is_design($uid,$itemid);
		}
				if($uid > 0 && $itemid > 0 && ($item == 'live' || $item == 'talk')) {
			$setuser = array(
			'item' => $item,
			'itemid' => $itemid,
			'uid' => $uid,
			);
			if(empty($isdesign)){
				DB::insert('item_sms', $setuser, true);
				$return = 1;
			}else{
				$return = -1;
			}
		}else{
			$return = 0;
		}
		response_text($return);
	}

	
	function second_cat()
	{	
		$cat_id = jget('cat_id','int','G');
		$groupselect = $this->TalkLogic->get_catselect($cat_id, 0, true);
		echo $groupselect['second'];
		exit;
	}
}
?>