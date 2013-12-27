<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic_manage.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-06 20:53:39 531420139 298148033 5099 $
 *******************************************************************/




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TopicManageLogic
{
	var $TopicLogic;
	
	function TopicManageLogic()
	{
		load::logic('topic');
		$this->TopicLogic = Load::logic('topic', 1);
		
	}
	
	
	function doManage($tid,$type=1){
		$topic_list = array();
		$sql = "select * from ".DB::table('topic')." where tid = '$tid'";
		$topic_list = DB::fetch_first($sql);
		
		if(!$topic_list){
			return false;
		}else{
						if($topic_list['managetype'] != $type){
				$this->manageDetail($tid,$topic_list['managetype'],$type);
			}
			
			
						if($type == 3){
				$this->TopicLogic->DeleteToBox($tid,0);
				
			}
						elseif($type == 4){
				$this->TopicLogic->DeleteToBox($tid);
				
			}else{
				$sql = "update ".DB::table('topic')." set managetype = '$type' where tid = '$tid'";
			}
			DB::query($sql);
		}
	}
	
	
	function doForceOut($nickname_arr=array(),$cause="没有理由",$role_id=4){
		
		$force_out_list = ConfigHandler::get('login_enable');

		foreach ($nickname_arr as $val) {
			$sql = "select uid,username,nickname,role_id,role_type from ".TABLE_PREFIX."members where nickname = '$val'";
			$member_list = DB::fetch_first($sql);
			
			if(!$member_list){
				continue;
			}
			
			if(jsg_member_is_founder($member_list['uid'])) {
				return 1;
			}
			
			if('admin' == $member_list['role_type']) {
				return 1;
			}
			
			$old_role_id = $member_list['role_id'];
			$member_list['douid'] = MEMBER_ID;
			$member_list['dousername'] = MEMBER_NAME;
			$member_list['donickname'] = MEMBER_NICKNAME;
			$member_list['cause'] = $cause;
			$member_list['role_id'] = $role_id;
			$member_list['dateline'] = time();

						if($role_id == 4){
				$force_out_list['4'][$member_list['uid']] = $member_list;
				if($force_out_list['118'][$member_list['uid']]){
					unset($force_out_list['118'][$member_list['uid']]);
				}
						}elseif($role_id == 118){
				$force_out_list[118][$member_list['uid']] = $member_list;
				if($force_out_list[4][$member_list['uid']]){
					unset($force_out_list[4][$member_list['uid']]);
				}
			}else{
				continue;
			}
			
						$count = DB::result_first("select count(*) from `".TABLE_PREFIX."force_out` where `uid` = '$member_list[uid]'");
			if($count){
				DB::query("update ".TABLE_PREFIX."force_out set douid='$member_list[douid]',cause='$member_list[cause]',dateline='$member_list[dateline]',role_id = '$role_id' where uid = '".$member_list['uid']."'");
			}else{
				DB::query("insert into ".TABLE_PREFIX."force_out (uid,role_id,douid,cause,dateline) values('$member_list[uid]','$role_id','$member_list[douid]','$member_list[cause]','$member_list[dateline]')");
			}
			
			if($old_role_id != $role_id){
				DB::query("update ".TABLE_PREFIX."members set role_id = '$role_id' where uid = '".$member_list['uid']."'");
			}
		}
		ConfigHandler::set('login_enable',$force_out_list);
		
		return 2;
	}
	
	
	function doUserFree($uid){
		$force_out_list = ConfigHandler::get('login_enable');
		unset($force_out_list[4][$uid]);
		unset($force_out_list[118][$uid]);
		DB::query("delete from ".TABLE_PREFIX."force_out where uid = '$uid'");
		ConfigHandler::set('login_enable',$force_out_list);
	}
	
	
	function manageDetail($tid = 0,$type1,$type2,$del=0){
		$type_arr = array(
				 0=>'未审',
				 1=>'显示',
				 2=>'停止',
				 3=>'私密',
				 4=>'删除',
				 5=>'彻底删除');
		$type = $type_arr[$type1]."-".$type_arr[$type2];
				 
		$sql = "select * from ".DB::table('manage_detail')." where tid = '$tid' order by dateline desc limit 1";
		$detail_list = DB::fetch_first($sql);
		
		$table = $del == 1 ? DB::table('topic_verify') : DB::table('topic');
		
		if($type == $detail_list['type']){
					}else{
			$sql = "select m.uid,m.username,m.nickname from $table t 
					left join ".DB::table('members')." m on m.uid = t.uid 
					where t.tid = '$tid'";
			$member = array();
			$member = DB::fetch_first($sql);
			
			$sql = "insert into ".DB::table('manage_detail')." (
						tid,type,tuid,tusername,
						uid,username,dateline,postip) 
					values(
						'$tid','$type','$member[uid]','$member[username]',
						'".MEMBER_ID."','".MEMBER_NAME."','".time()."','".client_ip()."'
						)";
			DB::query($sql);
		}
	}
}