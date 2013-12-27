<?php

$langSet = C('DEFAULT_LANG');
if (is_file(LANG_PATH . $langSet . '/common.php')) {
	L(include LANG_PATH . $langSet . '/common.php');
}

function curPageURL()
{
	$pageURL = 'http';

	if ($_SERVER["HTTPS"] == "on")
	{
		$pageURL .= "s";
	}
	$pageURL .= "://";

	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

/**
 * 5.把HTML标签转换为字符串
 * @param Array、Object、Str  $_date
 * @return Array、Object、Str
 */
function htmlString ($_date) {
	if (is_array($_date)) {
		foreach ($_date as $_key => $_value) {
			$_string[$_key] =  htmlString($_value);
		}
	} else if (is_object($_date)) {
		foreach ($_date as $_key => $_value) {
			$_string->$_key =  htmlString($_value);
		}
	} else {
		$_string = htmlspecialchars($_date);
	}
	return $_string;//传入的是对象，返回对象、是数组，返回数组、是字符串则返回字符串
}

/*
 * 发送手机短信消息
 * param $mod array 手机号码
 * param $msg 消息
 */
function sendMobileMsg($mob, $msg){
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
