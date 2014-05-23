<?php
class House_typeAction extends BaseAction {
	
	public function house_typeSelect(){
		$field = array(
				'1'=>'room',
				'2'=>'apartment',
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
			<option value="">房屋类型</option>
begin;
		}
		if($level == 2){
			$option = <<<begin
			<option value="">户型结构</option>
begin;
		}
		$field = $field[$level];
		$res = D('House_type')->where(array('pid' => $pid))->select();
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
					house_typeRemove($(this));
					house_typeAjax('{$_POST['act']}',{'level':{$level},'pid':$(this).val()});
				});
			</script>
begin;
			}
		}
		return $html;
	}
}