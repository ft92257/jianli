<?php 
class ProjectModel extends Model{
    protected $trueTableName = 'yjl_project';
	
    //获取项目列表数据
    public function show($_sid) {
    	return $this->where(array('sid'=>$_sid))->select();
    }
	
    //添加一条项目
	public function add() {
		$pid = parent::add();
		if ($pid) {
			return $pid;
		} else {
			return false;
		}
	}

	//项目修改
	public function edit($arr) {
		return $this->where($arr)->save();
	} 
	
	//删除数据
	public function del($array) {

		return $this->where($array)->delete();
	}

	//获取一条数据
	public function getOne($arr) {
		return $this->where($arr)->find();
	}
	
}
