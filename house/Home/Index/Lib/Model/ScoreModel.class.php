<?php 

/**
 * 评分模型
 *
 */
class ScoreModel extends BaseModel {
	
	/*
	 * 验证form字段规则
	 */
	protected $aValidate = array(
		//array('target', 'unique', '对不起，您已经评过分了！', array('uid' => '{uid}', 'type' => '{type}'), array('replace')),
	);
	
	/*
	 * 查询结果处理
	 */
	protected function _after_select(&$resultSet,$options) {
		foreach ($resultSet as &$value) {
			$avatar = D('User')->where(array('id'=>$value['uid']))->getField('avatar');
			$value['uid'] = getFileUrl($avatar, '24-24');
			$value['createtime'] = $this->time_tran($value['createtime']);
		}
	}

	
	//计算时间，规则如下，如果一小时内，显示分钟，如果大于1小时小于1天显示小时，如果大于天且小于3天，显示天数，否则显示日期时间
	public function time_tran($the_time){
		
		$dur = time() - $the_time;
		if($dur < 60){
			return $dur.'秒前';
		}else{
			if($dur < 3600){
				return floor($dur/60).'分钟前';
			}else{
				if($dur < 86400){
					return floor($dur/3600).'小时前';
				}else{
					if($dur < 259200){//3天内
						return floor($dur/86400).'天前';
					}else{
						$the_time = date("Y-m-d H:i:s",$value[createtime]);
						return $the_time;
					}
				}
			}
		}
	}
	/*
	 * 获取公司评分置顶记录
	* @param int $limit 获取记录条数
	*/
	public function getTop($cid, $limit) {
		$condition = array(
			'type' => 1,
			'target' => $cid,
		);
		return $this->where($condition)->order('isreal DESC, createtime DESC')->limit($limit)->select();
	}
	
	/*
	 * 获取施工队评分置顶记录
	* @param int $limit 获取记录条数
	*/
	public function getConsTop($consid, $limit) {
		$condition = array(
			'type' => 2,
			'target' => $consid,
		);
		return $this->where($condition)->order('isreal DESC, createtime DESC')->limit($limit)->select();
	}
	
	/*
	 * 获取样板工地评分置顶记录
	* @param int $limit 获取记录条数
	*/
	public function getCaseTop($caseid, $limit) {
		$condition = array(
				'type' => 3,
				'target' => $caseid,
		);
		return $this->where($condition)->order('isreal DESC, createtime DESC')->limit($limit)->select();
	}
	
}



?>