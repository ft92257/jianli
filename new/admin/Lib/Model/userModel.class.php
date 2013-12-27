<?php
class userModel extends Model{
	/*protected $_link = array(
        'items_comments' => array(
            'mapping_type'  => HAS_MANY ,
            'class_name'    => 'items_comments',
            'foreign_key'   => 'id',)
	);*/
	public function get_user($id){
		$mod=D('user');
		return $mod->where('id='.$id)->find();
	}
	function get_list($pagesize=20){
		var_dump(import("ORG.Util.Page"));

		$mod=D('user');
		$where=" 1=1 ";
		if(isset($_REQUEST['keyword'])){
			$keys = $_REQUEST['keyword'];
			$where.=" and name like '%$keys%'";
		}

		$count = $mod->count();
		$p = new Page($count,$pagesize);

		$list=$mod->where($where)->order("last_time desc")->limit($p->firstRow.','.$p->listRows)->select();
		return array('list'=>$list,'page'=>$p->show(),'count'=>$count);
	}
}