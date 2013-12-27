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
 * @Date 2012-07-04 18:49:38 1879556668 1027376091 10204 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);		
		Load::logic('talk');
		$this->TalkLogic = new TalkLogic();		
		
		$this->TopicLogic = Load::logic('topic', 1);
		$this->Execute();		
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code){
			case 'view':
				$this->view();
				break;
			default:
				$this->main();
				break;			
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}
	
	
	function main()
	{
				$view = empty($this->Get['view']) ? 'all' : trim($this->Get['view']);
		$cat = jget('cat','int');
		$categoryall = $this->TalkLogic->category_list();
		if($cat > 0){
			$categorys = $this->TalkLogic->category_list($cat);
			$cate_info = $this->TalkLogic->id2category($cat);
		}
		$time = time();		
		$gets = array(
			'mod' => 'talk',
			'view' => $this->Get['view'],
			'cat' => $this->Get['cat'],
		);
		$page_url = 'index.php?'.url_implode($gets);
		$order_sql = ' ORDER BY lid DESC';
				$perpage = 10;
		switch ($view) {
			case 'go':
				$this->Title = '未开始';
				$where_sql = " starttime > '$time'";
				break;
			case 'on':
				$this->Title = '进行中';
				$where_sql = " starttime <= '$time' AND endtime >= '$time'";
				break;
			case 'no':
				$this->Title = '已完成';
				$where_sql = " endtime < '$time'";
				break;
			default:
				$this->Title = '全部';
				$where_sql = "";
				break;
		}
		if($cat > 0){
			if($ctid = $this->TalkLogic->id2cateid($cat)){
				$where_sql .= $where_sql ? ' AND ' : '';
				$where_sql .= is_array($ctid) ? " cat_id IN(".jimplode($ctid).") " : " cat_id = '$ctid' ";
			}
		}
		
		$param = array(
			'where' => $where_sql,
			'order' => $order_sql,
			'perpage' => $perpage,
			'page_url' => $page_url
		);
		$talk_info = $this->TalkLogic->get_list($param);
		$count = 0;
		$talk_list = array();
		$page_arr['html'] = '';
		$uid_ary = array();
		if (!empty($talk_info)) {
			$count = $talk_info['count'];
			$talk_list = $talk_info['list'];
			$page_arr['html'] = $talk_info['page']['html'];
		}
		$talk_config = ConfigHandler::get('talk');
		$talk_config['des'] = nl2br($talk_config['des']);
		$talk_config['ads'] = nl2br($talk_config['ads']);
		$user = $this->TopicLogic->GetMember($talk_config['uid'], "`uid`,`username`,`face`,`fans_count`,`validate`,`validate_category`");
		$user_host = array();
		$user_host['face'] = $user['face'];
		$user_host['username'] = $user['username'];
		$user_host['fans_count'] = $user['fans_count'];
		$user_host['validate_html'] = $user['validate_html'];
		$user_host['followed'] = $this->TalkLogic->is_followed($talk_config['uid']);

				if(MEMBER_STYLE_THREE_TOL){
			$member = $this->TopicLogic->GetMember(MEMBER_ID);
		}
		include template('talk_index');
	}
	
	
	function view()
	{
		$lid = jget('id','int','G');
		if(!$this->TalkLogic->is_exists($lid)){return false;}
		$list = $this->TalkLogic->Getguest($lid);
		$this->item = 'talk';
		$this->item_id = $item_id = $lid;
		$talk = $this->TalkLogic->id2talkinfo($lid,$list);
		$uids = array();
		$gets = array(
			'mod' => 'talk',
			'code' => 'view',
			'id' => $lid,
		);
		$page_url = 'index.php?'.url_implode($gets);
		$perpage = ($talk['status_css'] == 'ico_ongoing') ? 1000 : 20;
		$options = array(
			'page' => true,
			'perpage' => $perpage,
			'page_url' => $page_url,
			'order' => ' dateline DESC ',
			);
		if(empty($talk)) {
			$this->Messager('当前访谈不存在!');
		}elseif($talk['all']){
			$defaust_value = '&nbsp;<font color="#0080c7">我有一个想法和大家分享</font>';
			foreach($talk['all'] as $key => $val){
				$uids[$key] = $key;
			}
		}
		if($talk['status_css'] == 'ico_ongoing'){
			$talklistcss = ' talk-list';
			$asklistcss = ' ask-list';
		}
		$is_talk_hosts = (in_array(MEMBER_ID,$uids)) ? true : false;		$talkvisit_str = $this->Config['site_url'].'/index.php?mod=talk&code=view&id='.$lid;
		$cat = $this->TalkLogic->get_category($talk['cat_id'],'second');
		$catp = $this->TalkLogic->get_category($cat['parent_id'],'first');
		$talk_config = ConfigHandler::get('talk');

				if(MEMBER_STYLE_THREE_TOL){
			$member = $this->TopicLogic->GetMember(MEMBER_ID);
		}
		
				$param = array(
			'limit' => '5',
		);
		$talk_info = $this->TalkLogic->get_list($param);
		if (!empty($talk_info)) {
			$talk_count = $talk_info['count'];
			$talk_list = $talk_info['list'];
		}
		Load::functions('app');
				$options['talkwhere'] = ' uid NOT IN (' . jimplode($uids) . ')';
		$topic_info = app_get_topic_list('talk', $lid, $options);		$options['talkwhere'] = ' totid > 0';
		$answer_info = app_get_topic_list('talk', $lid, $options);		$options['talkwhere'] = ' istop = 1';
		$options['order'] = ' lastupdate DESC ';
		$question_info = app_get_topic_list('talk', $lid, $options);		$myanswerids = app_getmyanswerid($lid);
		
		if (!empty($topic_info['list'])) {
			$topic_list = $topic_info['list'];
			foreach($topic_list as $key => $val){
												if(in_array($val['tid'],$myanswerids) && $talk[status_css] == 'ico_ongoing'){
					$topic_list[$key]['reply_ok'] = true;
				}
			}
			$topic_count = $topic_info['count'];
			$topic_page_arr = $topic_info['page'];
		}
				if (!empty($question_info['list'])) {
			$question_list = $question_info['list'];
			$ask_count = $question_info['count'];
			$ask_page_arr = $question_info['page'];
			foreach($question_list as $key => $val){
				$ask_info = array();
				$u_t = $this->TalkLogic->id2usertype($lid,$val['uid'],$list);
				if($u_t == 'guest'){
					$question_list[$key]['user_str'] = '本期嘉宾';
				}else{
					$question_list[$key]['user_str'] = '&nbsp;';
				}
				$question_list[$key]['user_css'] = 'talk'.$u_t;
				if(empty($question_list[$key]['touid'])){
					$question_list[$key]['biank_css'] = 'talk_view_ping';
					$question_list[$key]['tubiao_css'] = 'talk_view_pin';
				}else{
					$question_list[$key]['biank_css'] = 'talk_view_wenda';
					$question_list[$key]['tubiao_css'] = 'talk_view_wen';
										if (!empty($answer_info['list'])) {
					foreach($answer_info['list'] as $k => $v){
						if($v['totid'] == $val['tid']){
							$ask_info['list'][$k] = $v;
						}
					}
					}
					if (!empty($ask_info)) {
						$question_list[$key]['ask_list'] = $ask_info['list'];
						foreach($question_list[$key]['ask_list'] as $k => $v){
							$u_ta = $this->TalkLogic->id2usertype($lid,$v['uid'],$list);
							if($u_ta == 'guest'){
								$question_list[$key]['ask_list'][$k]['user_str'] = '本期嘉宾';
							}else{
								$question_list[$key]['ask_list'][$k]['user_str'] = '&nbsp;';
							}
							$question_list[$key]['ask_list'][$k]['tubiao_css'] = 'talk_view_da';
							$question_list[$key]['ask_list'][$k]['user_css'] = 'talk'.$u_ta;
						}
					}
				}
			}
		}
		
		$this->Title = '访谈 - '.$talk['talkname'];
		include template("talk");
	}
}
?>
