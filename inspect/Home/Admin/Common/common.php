<?php

/**
 * 获取数组val值中的字段
 * @param Array $arr
 * @param string $field
 * return Array
 */
function getArrayByField(&$arr,$field, $key = '') {
	$aRet = array();
	if ($key !== '') {
		foreach ($arr AS $aVal) {
			$aRet[$aVal[$key]] = $aVal[$field];
		}
	} else {
		foreach ($arr AS $aVal) {
			$aRet[] = $aVal[$field];
		}
	}
	return $aRet;
}

/**
 * 根据Val值，重新排序数组
 * @param Array $arr
 * @param unknown_type $k
 */
function setArrayKey(&$arr,$k) {
	$aRet = array();
	foreach ($arr AS $key=>$val) {
		$aRet[$val[$k]][] = $arr[$key];
	}
	return $aRet;
}

/*
 * 获取request变量
 */
function getRequest($name, $default = '') {
	if (isset($_POST[$name])) {
		return $_POST[$name];
	} elseif (isset($_GET[$name])) {
		return $_GET[$name];
	} else {
		return $default;
	}
}

/*
 * @param mixed fields 例：'name,logo,type',或者数组
 */
function getRequestData($fields) {
	$data = array();
	if (is_array($fields)) {
		$arr = $fields;
	} else {
		if ($fields !== '') {
			$arr = explode(',', $fields);
		} else {
			$arr = array();
		}
	}
	foreach ($arr as $name) {
		$data[$name] = getRequest($name);
	}
	
	return $data;
}

/*
 * 过滤逗号分割的数字字符串 如 1,2,3
 */
function filterNumList($numlist, $separator = ',') {
	$aNums = explode($separator, $numlist);
	
	foreach ($aNums as &$value) {
		$value = intval($value);
	}
	
	return join($separator, $aNums);
}


/*
 * 发送手机短信消息
* param $mod array 手机号码
* param $msg 消息
*/
function sendMobileMsg($mob, $msg){
	if (!is_array($mob)) {
		$mob = array($mob);
	}
	
	$r_main = array(
			'sms_qm' => '易监理',
			'sms_n' => 'SDK-SWW-010-00228',
			'sms_p' => '781516',
	);
	$p_max=10;
	$c_mob_a=count($mob);
	if($r_main['sms_qm']!='')$msg.='【'.$r_main['sms_qm'].'】';
	$msg=urlencode(@iconv('UTF-8', 'GB2312', $msg));
	$pwd=strtoupper(md5($r_main['sms_n'].$r_main['sms_p']));
	if($c_mob_a>$p_max){
		foreach($d_av as $v){
			$xu='http://sdk2.entinfo.cn/z_mdsmssend.aspx?sn='.$r_main['sms_n'].'&pwd='.$pwd.'&mobile='.join(',', $v).'&content='.$msg.'&stime=';
			$xc=@file_get_contents($xu);
		}
	}else{
		$xu='http://sdk2.entinfo.cn/z_mdsmssend.aspx?sn='.$r_main['sms_n'].'&pwd='.$pwd.'&mobile='.join(',', $mob).'&content='.$msg.'&stime=';
		$xc=@file_get_contents($xu);
	}
	if($xc!='' && trim($xc)!=''){
		if(intval(trim($xc))>0){
			$sid=1;
			$sinfo='发送成功';
		}else{
			$sid=trim($xc);
			$sinfo='';
		}
	}else{
		$sid=800;
		$sinfo='服务器连接失败';
	}
	return array($sid, $sinfo);
}

/*
 * 获取文件的URL访问地址
 * @param $fid file表的id
 * @param $thumb 缩略图标志，默认空返回原图（原文件）
 * @param default 默认图片
 */
function getFileUrl($fid, $thumb='', $default = '') {
	$File = D('File');
	$aFile = $File->getById($fid);
	$url = $File->getUrl($aFile, $thumb, $default);
	
	return $url;
}


/*
 * 替换{var}为$data['var']
 */
function _replaceValue(&$mVal, $data, $tag_begin = '{', $tag_end = '}') {
	if (is_array($mVal)) {
		foreach ($mVal as &$val) {
			_replaceValue($val, $data);
		}
	} else {
		preg_match_all("/\\".$tag_begin."\w+\\".$tag_end."/", $mVal, $matches);
		foreach ($matches[0] as $value) {
			$key = str_replace(array($tag_begin, $tag_end), '', $value);
			$mVal = str_replace($value, $data[$key], $mVal);
		}
	}
}

/*
 * 插入数组单元到某个键值的单元后面，$afterkey为空则插入最前面
 */
function insertArray($arr,$afterkey,$insertArr) {
	$ret = array();
	if(!$afterkey){
		$ret = $insertArr;
	}
	foreach($arr as $k=>$v) {
		$ret[$k] = $v;
		if($k = $afterkey){
			$ret[key($insertArr)] = current($insertArr);
		}
	}

	return $ret;
}

?>