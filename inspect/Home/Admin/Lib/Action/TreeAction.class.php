<?php
class TreeAction extends BaseAction {
	
	private $typeConf;
	private $subjectConf;
	private $maxLevel;
	
	public function __construct(){
		parent::__construct();
		$dataConf = require CONF_PATH.'dataConfig.inc.php';
		$this->typeConf = $arr = D('Node')->select();
		$this->subjectConf =$dataConf['questionSubject'];
	}
	
	public function select(){
		$field = array(
				'1'=>"{$_POST['act']}_L1",
				'2'=>"{$_POST['act']}_L2",
				'3'=>"{$_POST['act']}_L3"
		);
		$this->maxLevel = sizeof($field);
			$level = isset($_POST['data']['level']) ? $_POST['data']['level'] : 1;
			$pid = isset($_POST['data']['pid']) ? $_POST['data']['pid'] : 0;
			$default = isset($_POST['data']['default']) ? $_POST['data']['default'] : false;
		echo $this->get_chird($level, $pid, $default, $field, $_POST['act']);
		exit;
	}
	
	private function queryTypeConf($pid){
		$arr = $this->typeConf;
		foreach($this->typeConf as $k => $v){
			if ($v['pid'] != $pid){
				unset($arr[$k]);
			}
		}
		return $arr;
	}
	
	private function querySubjectConf($pid){
		$arr = $this->subjectConf;
		foreach($this->subjectConf as $k => $v){
			if ($v['pid'] != $pid){
				unset($arr[$k]);
			}
		}
		return $arr;
	}
	
	public function get_parent($act, $pid, &$str = ""){
		switch ($act){
			case 'type': $res = $this->queryTypeConf($pid); break;
			case 'subject': $res = $this->querySubjectConf($pid); break;
		}
		foreach($res as $v){
			if ($pid == $v['id']){
				$str .= ",{$v['id']}";
				$this->get_parent($act, $v['id'], $str);
			}
		}
		return $str;
	}
	
	private function get_chird($level, $pid, $default = false, $field, $act){
		switch ($act){
			case 'type': $res = $this->queryTypeConf($pid); break;
			case 'subject': $res = $this->querySubjectConf($pid); break;
		}
			$option = <<<begin
			<option value="-1">请选择</option>
begin;
		$field = $field[$level];
		if(!empty($res)){
			$html = <<<begin
			<select id="{$field}">{$option}
begin;
			foreach($res as $k=>$v){
				$checked = $default && $default == $v['id'] ? 'selected=selected' : '';
				$html .= <<<begin
					<option value="{$v['id']}" {$checked}>{$v['name']}</option>
begin;
			}
			$html .= <<<begin
			</select>
begin;
			if($level < ($this->maxLevel + 1)){
				$level == $this->maxLevel ? $this->maxLevel : $level++;
				$html .= <<<begin
			<script>
			$(function(){
				$('#{$field}').bind('change',function(){
					remove('{$act}', $(this));
					updateHide('{$act}');
					ajaxSelect('{$_POST['act']}',{'level':{$level},'pid':$(this).val()});
				});
			});
			</script>
begin;
			}
		}
		return $html;
	}
}