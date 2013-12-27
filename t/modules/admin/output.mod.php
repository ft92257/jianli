<?php
/**
 *
 * 微博站外评论调用
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}


class ModuleObject extends MasterObject {

	
	function ModuleObject($config) {
		$this->MasterObject($config);

		$this->Execute();
	}

	
	function Execute() {
		ob_start();
		switch($this->Code) {
			case 'add':
				$this->Add();
				break;
			case 'modify':
				$this->Modify();
				break;
			case 'do_modify':
				$this->DoModify();
				break;
			case 'delete':
				$this->Delete();
				break;
			case 'output_setting':
				$this->OutputSetting();
				break;

			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);
	}

	function Main() {
		$this->Messager('正在建设中……', null);
	}

	function OutputSetting() {
		$query_link = 'admin.php?mod=output&code=output_setting';
		$per_page_num = 50;

		$total_record = DB::result_first("select count(*) as total_record from ".DB::table('output')." ");
		if($total_record) {
			$_config = array(
				'return' => 'Array',
			);
			$page_arr = page($total_record, $per_page_num, $query_link, $_config);

			$query = DB::query("select * from ".DB::table('output')." order by `id` asc {$page_arr['limit']} ");
			$output_list = array();
			while (false != ($row = DB::fetch($query))) {
				$row['output_code'] = $this->_output_code($row);

				$output_list[] = $row;
			}
		}

		include template('admin/output');
	}

	function Add() {
		$data = array(
			'name' => '请填写站外评论调用的名称或描述',
			'hash' => md5(random(32)),
			'dateline' => TIMESTAMP,
			'uid' => MEMBER_ID,
			'type_first' => 1,
		);
		$id = DB::insert('output', $data, 1);

		if($id < 1) {
			$this->Messager('添加失败');
		} else {
			$this->Messager('添加成功，现在为您转入编辑页面', "admin.php?mod=output&code=modify&id=$id");
		}
	}

	function Modify() {
		$id = (int) get_param('id');
		$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
		if(!$info) {
			$this->Messager('您要编辑的内容已经不存在了');
		}
		$info['output_code'] = $this->_output_code($info);

		Load::lib('form');
		$FormHandler = new FormHandler();

		$type_first_radio = $FormHandler->YesNoRadio('data[type_first]', (int) $info['type_first']);

		include template('admin/output_modify');
	}

	function DoModify() {
		$id = (int) get_param('id');
		$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
		if(!$info) {
			$this->Messager('您要编辑的内容已经不存在了');
		}

		$data = get_param('data');
		$data['name'] = trim($data['name']);
		$data['lock_host'] = trim(strtolower($data['lock_host']));
		$data['content_default'] = strip_tags($data['content_default']);
		$data['type_first'] = $data['type_first'] ? 1 : 0;

		DB::update('output', $data, array('id'=>$id));

		$this->Messager('修改成功');
	}

	function Delete() {
		$id = (int) get_param('id');
		$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
		if(!$info) {
			$this->Messager('您要删除的内容已经不存在了');
		}

		DB::query("delete from ".DB::table('output')." where `id`='$id'");

		$this->Messager('删除成功');
	}

	function _output_code($row, $ret_row=0) {
		$row['output_code'] = '<div id="jishigou_div">内容正在加载中，请稍候……</div><script type="text/javascript" src="'.get_full_url($this->Config['site_url'], "index.php?mod=output&code=url_js&id={$row['id']}&hash={$row['hash']}&per_page_num={$row['per_page_num']}&content_default=".urlencode($row['content_default'])).'" charset="'.$this->Config['charset'].'"></script>';
		$row['output_code'] = htmlspecialchars($row['output_code']);

		if($ret_row) {
			return $row;
		} else {
			return $row['output_code'];
		}
	}

}

?>
