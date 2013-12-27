<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename class.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1372961203 2133480542 5473 $
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

		$this->initMemberHandler();
		
		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->Execute();
	}

	
	function Execute()
	{
        ob_start();
        switch($this->Code){
        	case 'getfilter':
        		$this->getFilter();
        		break;
        	case 'keyword':
        		$this->KeyWord();
        		break;
        	case 'xiami':
        		$this->getXiaMimusic();
        		break;
        	case 'getsigntag':
        		$this->getSignTag();
        		break;
        	case 'app_medal':
        		$this->appMedal();
        		break;
        	case 'resultlist':
				$this->resultList();
				break;
        	case 'sel':
        		$this->makeSel();
        		break;
        	default:
        		break;
		}
        response_text(ob_get_clean());
	}	
	
	
	function getFilter(){
		$type = $this->Get['type'];
		$filter = ConfigHandler::get('filter');
		if($filter[$type]){
			json_result('',$filter[$type]);
		}else{
			json_error('no filters');
		}
	}
	
	
	
	function KeyWord(){
		$keyword = trim($this->Post['keyword']);

		$filter = ConfigHandler::get('filter');
		$return = array();
				foreach ($filter['keyword_list'] as $value) {
			if(jstrpos($value,$keyword) !== false){
				$keyword_f[] = $value;
			}
		}
		if($keyword_f){
			$return['keyword_f'] = implode("<br>",$keyword_f);
		}
		
				foreach ($filter['verify_list'] as $value) {
			if(jstrpos($value,$keyword) !== false){
				$verify_f[] = $value;
			}
		}
		if($verify_f){
			$return['verify_f'] = implode("<br>",$verify_f);
		}
		
				foreach ($filter['replace_list'] as $key=>$value) {
			if(jstrpos($key,$keyword) !== false){
				$replace_f[] = $key;
			}
		}
		if($replace_f){
			$return['replace_f'] = implode("<br>",$replace_f);
		}
		
				foreach ($filter['shield_list'] as $value) {
			if(jstrpos($value,$keyword) !== false){
				$shield_f[] = $value;
			}
		}
		if($shield_f){
			$return['shield_f'] = implode("<br>",$shield_f);
		}
		
		if($return){
			json_result('',$return);
		}else{
			json_error('没有匹配的关键字');
		}
	}
	
	
	function getXiaMimusic(){
		$page = (int) get_param('page');
		$page = $page ? $page : 1;
		
		$name = get_param('name');
				$name = urlencode(iconv($this->Config['charset'],"utf-8",$name));
		
		$url="http:/"."/www.xiami.com/app/nineteen/search/key/$name/page/$page/size/1?random=1123132112132";
		$file_contents = file_get_contents($url);
     	echo $file_contents;
	}
	
	
	function appMedal(){
		$medal_id = (int) $this->Get['medal_id'];
		$uid = (int) $this->Get['uid'];
		$nickname = MEMBER_NICKNAME;
		$time = TIMESTAMP;
		if($medal_id < 1){
			json_error("无效的勋章ID");
		}
		if($uid < 1){
			json_error("无效的用户ID");
		}
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."medal_apply where medal_id = '$medal_id' and uid = '$uid'");
		if($count){
		    json_error("勋章已申请，耐心等待哦");
		}
		$reslut = $this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."medal_apply (uid,nickname,medal_id,dateline) values ('$uid','$nickname','$medal_id','$time')");
		if($reslut){
			json_result("1");
		}else{
			json_error("申请失败：".$reslut);
		}
	}

	
	function resultList(){
		$area_id = (int) $this->Get['area_id'];
		$city_id = (int) $this->Get['city_id'];
		$zone_id = (int) $this->Get['zone_id'];
		$act = $this->Get['act'];
		
		if($area_id && $area_id != 0){
			$area = $area_id;
			$where = " where upid = '$area_id' ";
			$code = "cityorder";
			$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` $where order by list");
		} else if ($city_id && $city_id != 0){
			$area = $this->Get['area'];
			$city = $city_id;
			$where = " where upid = '$city_id' ";
			$code = "zoneorder";
			$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` $where order by list");
		} else if ($zone_id && $zone_id != 0){
			$area = $this->Get['area'];
			$city = $this->Get['city'];
			$zone = $zone_id;
			$where = " where upid = '$zone_id' ";
			$code = "streetorder";
			$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` $where order by list");
		}
		
		$rs = array();
		if($query){
			while ($rsdb = $query->GetRow()){
				$rs[$rsdb['id']] = $rsdb;
			}
		}
		include template('admin/resultList');
		exit;
	}
	
	
	function getSignTag(){
		$is_tag = 1;
		
		load::logic('other');
		$OtherLogic = new OtherLogic();
		
		$tag_arr  = $OtherLogic->getSignTag();
		

		include template('admin/resultList');
	}
}