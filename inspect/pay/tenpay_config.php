<?php
$spname="财付通双接口测试";
$partner = "1900000113";                                  	//财付通商户号
$key = "e82573dc7e6136ba414f2e2affbe39fa";											//财付通密钥

$return_url = "http://yijianli.com/house/pay/payReturnUrl.php";			//显示支付结果页面,*替换成payReturnUrl.php所在路径
$notify_url = "http://yijianli.com/house/pay/payNotifyUrl.php";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径

$aDbConfig = array(
			'host' => '127.0.0.1',
			'user' => 'root',
			'password' => 'ft63351510',
			'dbname' => 'jianliht',
			'charset' => 'utf8',
		);
?>