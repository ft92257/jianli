<?php 
/**
 * 监理师表
 * @author Administrator
 *
 */
class ProjectSupervisionModel extends Model{
	
    protected $trueTableName = 'yjl_project_supervision';
	
    //获取项目列表数据
    public function show() {
    	return $this->find();
    }
    
	public function edit() {
		return $this->where('1')->save();
	}
 
	
	
}
