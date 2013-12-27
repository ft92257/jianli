<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename channel.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-08-17 19:12:46 1895727436 1946178927 3677 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ChannelLogic = Load::logic('channel',1);
		$this->TopicLogic = Load::logic('topic',1);
		$this->Execute();		
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code){
			default:
				$this->main();
				break;			
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}
	
	
	function main()
	{
		if(!$this->Channel_enable){$this->Messager("网站没有开启该功能",null);}
		$this->Title = '频道';
		$channel_id = jget('id','int','G');
		$this->Channel = ($channel_id ==0) ? '' : $channel_id;
		$filter_type = jget('filter_type');
		$filter_url = ($filter_type == 'pic') ? '&filter_type=pic' : '';
		$per_page_num = 20;
				$channel_list = array();
		$cachefile = ConfigHandler::get('channel');
		$cachefiles = ConfigHandler::get('channels');
		$channel_one = is_array($cachefile['first']) ? $cachefile['first'] : array();
		$channel_two = is_array($cachefile['second']) ? $cachefile['second'] : array();
		$channel_name = $channel_buddy = '';
		$my_channels = $this->ChannelLogic->mychannel();		if(in_array($channel_id,array_keys($cachefiles))){
			$channel_buddy = follow_channel($channel_id,in_array($channel_id,array_keys($my_channels)));
		}
		foreach($channel_one as $k => $v){
			$v['display']='none';			$v['str']='+';			if($channel_id == $v['ch_id']){
				$v['css']='cthish';				$v['display']='block';
				$v['str']='-';
				$channel_name = $v['ch_name'];
			}
			$v['child_num'] = 0;
			$channel_list[$k] = $v;
		}
		foreach($channel_two as $k => $two){
			if($channel_id == $two['ch_id']){
				$two['css']='cthish';
				$channel_list[$two['parent_id']]['str']='-';
				$channel_list[$two['parent_id']]['display']='block';
				$channel_name = $two['ch_name'];
			}
			$channel_list[$two['parent_id']]['child'][$k] = $two;
			$channel_list[$two['parent_id']]['child_num'] += 1;
		}
		$item_ids = $cachefiles[$channel_id] ? $cachefiles[$channel_id] : (strlen($this->Get['id']) == 0 && $cachefiles ? array() : array(0));
				$options = array('item'=>'channel','item_id'=>$item_ids);
		$TopicListLogic = Load::logic('topic_list', 1);
		$options['perpage'] = $per_page_num;
		$info = $TopicListLogic->get_data($options);
		$topics = array();
		$total_record = 0;
		if (!empty($info)) {
			$topics = $info['list'];
			$total_record = $info['count'];
			if($info['page']){
				$page_arr = $info['page'];
			}
		}
		$topics_count = 0;
		if ($topics) {
			$topics_count = count($topics);
			if (!$topic_parent_disable) {
								$parent_list = $this->TopicLogic->GetParentTopic($topics, ('mycomment' == $this->Code));
							}
		}
				if($filter_type=='pic'){
			if($page_arr['html']){
				$ajax_num = ceil($total_record/$per_page_num);
			}
			foreach ($topics as $key => $row) {				if($row['parent_id'] || $row['top_parent_id']) {
					unset($topics[$key]);
				}
			}
			$topic_pic_keys = array('ji','shi','gou');
			$params['id'] = base64_encode(serialize($item_ids));
			include template('channel_pic');
		}else{
			include template('channel');
		}
	}
}
?>
