<?php
/**
 * @author tuguska
 */

class MBApiClient
{
	public $host='open.t.qq.com';
	function __construct( $wbakey , $wbskey , $accecss_token , $accecss_token_secret ){
		$this->oauth=new MBOpenTOAuth( $wbakey , $wbskey , $accecss_token , $accecss_token_secret );
	}

	public function getTimeline($p){
		if(!isset($p['name'])){
			$url='http://open.t.qq.com/api/statuses/home_timeline?f=1';
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'pageflag'=>$p['f'],
				'reqnum'=>$p['n'],
				'pagetime'=>$p['t']
			);
		}else{
			$url='http://open.t.qq.com/api/statuses/user_timeline?f=1';
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'pageflag'=>$p['f'],
				'reqnum'=>$p['n'],
				'pagetime'=>$p['t'],
				'name'=>$p['name']
			);
		}
		return $this->oauth->get($url,$params);
	}

	public function getPublic($p){
		$url='http://open.t.qq.com/api/statuses/public_timeline?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'pos'=>$p['p'],
			'reqnum'=>$p['n']
		);
		return $this->oauth->get($url,$params);
	}

	public function getMyTweet($p){
		$p['type']==0?$url='http://open.t.qq.com/api/statuses/mentions_timeline?f=1':$url='http://open.t.qq.com/api/statuses/broadcast_timeline?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'pageflag'=>$p['f'],
			'reqnum'=>$p['n'],
			'pagetime'=>$p['t'],
			'lastid'=>$p['l'],
			'type'=>20
		);
		return $this->oauth->get($url,$params);
	}

	public function getTopic($p){
		$url='http://open.t.qq.com/api/statuses/ht_timeline?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'pageflag'=>$p['f'],
			'reqnum'=>$p['n'],
			'httext'=>$p['t'],
			'pageinfo'=>$p['p']
		);
		return $this->oauth->get($url,$params);
	}

	public function getOne($p){
		$url='http://open.t.qq.com/api/t/show?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'id'=>$p['id']
		);
		return $this->oauth->get($url,$params);
	}

	public function postOne($p){
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'content'=>$p['c'],
			'clientip'=>$p['ip'],
			'jing'=>$p['j'],
			'wei'=>$p['w']
		);
		switch($p['type']){
			case 2:
				$url='http://open.t.qq.com/api/t/re_add?f=1';
				$params['reid']=$p['r'];
				return $this->oauth->post($url,$params);
				break;
			case 3:
				$url='http://open.t.qq.com/api/t/reply?f=1';
				$params['reid']=$p['r'];
				return $this->oauth->post($url,$params);
				break;
			case 4:
				$url='http://open.t.qq.com/api/t/comment?f=1';
				$params['reid']=$p['r'];
				return $this->oauth->post($url,$params);
				break;
			default:
				if(!empty($p['p'])){
					$url='http://open.t.qq.com/api/t/add_pic?f=1';
					$params['pic']=$p['p'];
					return $this->oauth->post($url,$params,true);
				}else{
					$url='http://open.t.qq.com/api/t/add?f=1';
					return $this->oauth->post($url,$params);
				}
			break;
		}

	}

	public function delOne($p){
		$url='http://open.t.qq.com/api/t/del?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'id'=>$p['id']
		);
		return $this->oauth->post($url,$params);
	}

	public function getReplay($p){
		$url='http://open.t.qq.com/api/t/re_list?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'rootid'=>$p['reid'],
			'pageflag'=>$p['f'],
			'reqnum'=>$p['n'],
			'flag'=>$p['flag']
		);
		if(isset($p['t'])){
			$params['pagetime']=$p['t'];
		}
		if(isset($p['tid'])){
			$params['twitterid']=$p['tid'];
		}
		return $this->oauth->get($url,$params); 
	}

	public function getUserInfo($p=false){
		if(!$p || !$p['n']){
			$url='http://open.t.qq.com/api/user/info?f=1';
			$params=array(
				'format'=>MB_RETURN_FORMAT
			);
		}else{
			$url='http://open.t.qq.com/api/user/other_info?f=1';
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'name'=>$p['n']
			);
		}
		return $this->oauth->get($url,$params); 
	}

	public function updateMyinfo($p){
		$url='http://open.t.qq.com/api/user/update?f=1';
		$p['format']=MB_RETURN_FORMAT;
		return $this->oauth->post($url,$p); 
	}

	public function updateUserHead($p){
		$url='http://open.t.qq.com/api/user/update_head?f=1';
		$p['format']=MB_RETURN_FORMAT;
		return $this->oauth->post($url, $p, true); 
	}

	public function getMyfans($p){
		try{
			if($p['n']==''){
				$p['type']?$url='http://open.t.qq.com/api/friends/idollist':$url='http://open.t.qq.com/api/friends/fanslist';
			}else{
				$p['type']?$url='http://open.t.qq.com/api/friends/user_idollist':$url='http://open.t.qq.com/api/friends/user_fanslist';
			}
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'name'=>$p['n'],
				'reqnum'=>$p['num'],
				'startindex'=>$p['start']
			);
			return $this->oauth->get($url,$params);
		} catch(MBException $e){
			$ret=array("ret"=>0, "msg"=>"ok"
					, "data"=>array("timestamp"=>0, "hasnext"=>1, "info"=>array()));
			return $ret;
		}
	}

	public function setMyidol($p){
		switch($p['type']){
			case 0:
				$url='http://open.t.qq.com/api/friends/del?f=1';
				break;
			case 1:
				$url='http://open.t.qq.com/api/friends/add?f=1';
				break;
			case 2:
				$url='http://open.t.qq.com/api/friends/addspecail?f=1';
				break;
		}
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'name'=>$p['n']
		);
		return $this->oauth->post($url,$params);
	}

	public function checkFriend($p){
		$url='http://open.t.qq.com/api/friends/check?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'names'=>$p['n'],
			'flag'=>$p['type']
		);
		return $this->oauth->get($url,$params);
	}

	public function postOneMail($p){
		$url='http://open.t.qq.com/api/private/add?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'content'=>$p['c'],
			'clientip'=>$p['ip'],
			'jing'=>$p['j'],
			'wei'=>$p['w'],
			'name'=>$p['n']
			);
		return $this->oauth->post($url,$params);
	}

	public function delOneMail($p){
		$url='http://open.t.qq.com/api/private/del?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'id'=>$p['id']
		);
		return $this->oauth->post($url,$params);
	}

	public function getMailBox($p){
		if($p['type']){
			$url='http://open.t.qq.com/api/private/recv?f=1';
		}else{
			$url='http://open.t.qq.com/api/private/send?f=1';
		}
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'pageflag'=>$p['f'],
			'pagetime'=>$p['t'],
			'reqnum'=>$p['n']
		);
		return $this->oauth->get($url,$params);
	}

	public function getSearch($p){
		switch($p['type']){
			case 0:
				$url='http://open.t.qq.com/api/search/user?f=1';
				break;
			case 1:
				$url='http://open.t.qq.com/api/search/t?f=1';
				break;
			case 2:
				$url='http://open.t.qq.com/api/search/ht?f=1';
				break;
			default:
				$url='http://open.t.qq.com/api/search/t?f=1';
				break;
		}

		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'keyword'=>$p['k'],
			'pagesize'=>$p['n'],
			'page'=>$p['p']
		);
		return $this->oauth->get($url,$params);
	}

	public function getHotTopic($p){
		$url='http://open.t.qq.com/api/trends/ht?f=1';
		if($p['type']<1 || $p['type']>3){
			$p['type']=1;
		}
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'type'=>$p['type'],
			'reqnum'=>$p['n'],
			'pos'=>$p['pos']
		);
		return $this->oauth->get($url,$params);
	}

	public function getUpdate($p){
		$url='http://open.t.qq.com/api/info/update?f=1';
		if(isset($p['type'])){
			if($p['op']){
				$params=array(
					'format'=>MB_RETURN_FORMAT,
					'op'=>$p['op'],
					'type'=>$p['type']
				);
			}else{
				$params=array(
					'format'=>MB_RETURN_FORMAT,
					'op'=>$p['op']
				);
			}
		}else{
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'op'=>$p['op']
			);
		}
		return $this->oauth->get($url,$params);
	}

	public function postFavMsg($p){
		if($p['type']){
			$url='http://open.t.qq.com/api/fav/addt?f=1';
		}else{
			$url='http://open.t.qq.com/api/fav/delt?f=1';
		}
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'id'=>$p['id']
		);
		return $this->oauth->post($url,$params);
	}

	public function postFavTopic($p){
		if($p['type']){
			$url='http://open.t.qq.com/api/fav/addht?f=1';
		}else{
			$url='http://open.t.qq.com/api/fav/delht?f=1';
		}
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'id'=>$p['id']
		);
		return $this->oauth->post($url,$params);
	}

	public function getFav($p){
		if($p['type']){
			$url='http://open.t.qq.com/api/fav/list_ht?f=1';
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'reqnum'=>$p['n'],
				'pageflag'=>$p['f'],
				'pagetime'=>$p['t'],
				'lastid'=>$p['lid']
				);
		}else{
			$url='http://open.t.qq.com/api/fav/list_t?f=1';
			$params=array(
				'format'=>MB_RETURN_FORMAT,
				'reqnum'=>$p['n'],
				'pageflag'=>$p['f'],
				'pagetime'=>$p['t']
				);
		}
		return $this->oauth->get($url,$params);
	}

	public function getTopicId($p){
			$url='http://open.t.qq.com/api/ht/ids?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'httexts'=>$p['list']
		);
		return $this->oauth->get($url,$params);
	}

	public function getTopicList($p){
			$url='http://open.t.qq.com/api/ht/info?f=1';
		$params=array(
			'format'=>MB_RETURN_FORMAT,
			'ids'=>$p['list']
		);
		return $this->oauth->get($url,$params);
	}
}
?>