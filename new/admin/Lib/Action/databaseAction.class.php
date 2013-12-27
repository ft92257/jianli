<?php
class databaseAction extends baseAction{
	function execute(){
		if(isset($_REQUEST['dosubmit'])){
			$mod=new Model();
			$sql_str=$_REQUEST['sql'];
			$sql_str = str_replace("\r", '',$sql_str);
			$sql_str = str_replace('`pp_', '`'.c("DB_PREFIX"), $sql_str);
			
			$ret = explode(";\n", $sql_str);
			$ret_count = count($ret);

			for($i = 0; $i < $ret_count; $i++)
			{
				$ret[$i] = trim($ret[$i], " \r\n;"); //剔除多余信息
				if (!empty($ret[$i]))
				{
					$mod->query($ret[$i]);
				}
			}
			$this->success('执行成功!');
		}
		$this->display();
	}
}