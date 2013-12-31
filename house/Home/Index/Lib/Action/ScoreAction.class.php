<?php 

/**
 * 	评分相关页面
 */
class ScoreAction extends BaseAction {

	//需要验证的方法
	protected $aVerify = array(
			'add',
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->model = D('Score');
	}
	
	/*
	 * 计算评分
	 */
	private function _calculate($aCom) {
		$updata = array();
		if ($this->oUser->type == 1 && $this->oUser->isreal) {
			//业主
			$updata['score_owner_count'] = $aCom['score_owner_count'] + 1;
			$updata['score_owner'] = ($aCom['score_owner_count'] * $aCom['score_owner'] + $score)/$updata['score_owner_count'];
			$updata['score_owner'] = round($updata['score_owner'], 2);
			
			$updata['score_complex'] = ($updata['score_owner'] + $aCom['score_expert'])/2;
			$updata['score_complex'] = round($updata['score_complex'], 2);
		} elseif ($this->oUser->type == 2) {
			//监理师
			$updata['score_expert_count'] = $aCom['score_expert_count'] + 1;
			$updata['score_expert'] = ($aCom['score_expert_count'] * $aCom['score_expert'] + $score)/$updata['score_expert_count'];
			$updata['score_expert'] = round($updata['score_expert'], 2);
			
			$updata['score_complex'] = ($aCom['score_owner'] + $updata['score_expert'])/2;
			$updata['score_complex'] = round($updata['score_complex'], 2);
		}
		
		return $updata;
	}
	
	/*
	 * 添加评分
	 */
	public function add() {
		//TODO 追加评分
		$target = (int) getRequest('id');
		$type = (int) getRequest('type');
		$score = (int) getRequest('score');
		$comment = getRequest('comment');
		$isreal = $this->oUser->isreal;//是否真实客户
		
		if ($type == 1) {
			$cid = $target;
			$aCom = D('Company')->getById($cid);
			if (empty($aCom)) {
				$this->error('装修公司不存在！');
			}
			//重新计算分数，结果保存在$updata
			$updata = $this->_calculate($aCom);
			$Model = D('Company');
		} elseif ($type == 2) {
			$aCons = D('Construction')->getById($target);
			if (empty($aCons)) {
				$this->error('施工队不存在！');
			}
				
			$cid = $aCons['cid'];
			//重新计算分数，结果保存在$updata
			$updata = $this->_calculate($aCons);
			$Model = D('Construction');
		} elseif ($type == 3) {
			//样板房
			$aCase = D('Case')->getById($target);
			if (empty($aCase)) {
				$this->error('样板工地不存在！');
			}
				
			$cid = $aCase['cid'];
			//重新计算分数，结果保存在$updata
			$updata = $this->_calculate($aCase);
			$Model = D('Case');
		} else {
			$this->error('type参数不正确！');
		}
		
		//获取评分过的记录
		$where = array(
					'uid' => $this->oUser->id,
					'type' => $type,
					'target' => $target,
				);
		$aScore = $this->model->where($where)->find();
		if (!empty($aScore)) {
			//已存在记录
			if ($aScore['additional']) {
				$this->error('您已经评论过了！不能重复评论！');
			} else {
				//追评 TODO 30天内限制
				$this->model->where($where)->data(array('additional' => $comment, 'reply_type' => 0))->save();
				$this->success('成功追加了评论！');
			}
		}
		
		$data = array(
					'cid' => $cid,
					'target' => $target,
					'type' => $type,
					'uid' => $this->oUser->id,
					'score' => $score,
					'comment' => $comment,
					'isreal' => $this->oUser->isreal,
					'usertype' => $this->oUser->type,
				);
		if (!$this->model->addData($data)) {
			$this->error($this->model->getError());
		}

		//保存计算结果
		if (!empty($updata)) {
			$Model->where(array('id' => $target))->data($updata)->save();
		}
		
		$this->success('评分成功！');
	}
	
}

?>