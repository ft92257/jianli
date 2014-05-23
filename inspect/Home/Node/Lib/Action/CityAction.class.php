<?php
class CityAction extends BaseAction {
	
	public function citySelect(){
		$field = array(
				'1'=>'province',
				'2'=>'city',
				'3'=>'county'
		);
			$level = isset($_POST['data']['level']) ? $_POST['data']['level'] : 1;
			$pid = isset($_POST['data']['pid']) ? $_POST['data']['pid'] : 0;
			$default = isset($_POST['data']['default']) ? $_POST['data']['default'] : false;
		echo $this->get_chird($level,$pid,$default,$field);
		exit;
	}
	
	private function get_chird($level, $pid, $default = false, $field){
		if($level == 1){
			$option = <<<begin
			<option value="">省/直辖市</option>
begin;
		}
		if($level == 2){
			$option = <<<begin
			<option value="">市/区</option>
begin;
		}
		if($level == 3){
			$option = <<<begin
			<option value="">区/县</option>
begin;
		}
		$field = $field[$level];
		$res = D('District')->where(array('upid' => $pid))->select();
		if(!empty($res)){
			$html = <<<begin
			<select class="validate[required]" name="{$field}" id="{$field}">{$option}
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
			if($level < 3){
				$level++;
				$html .= <<<begin
			<script>
				$('#{$field}').bind('change',function(){
					cityRemove($(this));
					cityAjax('{$_POST['act']}',{'level':{$level},'pid':$(this).val()});
				});
			</script>
begin;
			}
		}
		return $html;
	}
}