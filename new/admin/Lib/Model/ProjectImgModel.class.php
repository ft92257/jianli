<?php 
class ProjectImgModel extends Model{
    protected $trueTableName = 'yjl_project_img';
	
    //获取项目列表数据
    public function show($array) {
    	return $this->where($array)->order('sort ASC')->select();
    }
	
    public function one($array) {
    	return $this->where($array)->find();
    }
    
    //添加一条项目
	public function add() {
		$mid = parent::add();
		if ($mid) {
			return $mid;
		} else {
			return false;
		}
	}
	
	//图片修改
	public function edit($where,$data) {
		return $this->where($where)->save($data);
	} 
	
	//删除数据
	public function del($array) {
		return $this->where($array)->delete();
	}
	
	
}
