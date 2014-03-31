<?php
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set(PRC);
require_once ("classes/RequestHandler.class.php");
require_once ("tenpay_config.php");

$curDateTime = date("YmdHis");
//4位随机数
$randNum = rand(1000, 9999);
//10位序列号,可以自行调整。
//$strReq = $strTime . $randNum;
/* 商家的定单号 */
$mch_vno = $curDateTime . $randNum;

$strDate = date("Ymd");
$strTime = date("His");

//---------------------------------------------------------
//财付通即时到帐支付请求示例，商户按照此文档进行开发即可
//---------------------------------------------------------

/* 获取提交的订单号 */
$out_trade_no = $mch_vno;
/* 获取提交的商品名称 */
$product_name = "易监理充值" . $_REQUEST["order_price"] . "元";
/* 获取提交的商品价格 */
$order_price = $_REQUEST["order_price"];
/* 获取提交的备注信息 */
//$remarkexplain = "欢迎使用易监理充值功能，" . $product_name;
/* 支付方式 */
$trade_mode=1;

/* 商品价格（包含运费），以分为单位 */
$total_fee = $order_price*100;

/* 商品名称 */
$desc = "商品：".$product_name;//.";,备注:".$remarkexplain;

/* 创建支付请求对象 */
$reqHandler = new RequestHandler();
$reqHandler->init();
$reqHandler->setKey($key);
$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

//----------------------------------------
//设置支付参数 
//----------------------------------------
$reqHandler->setParameter("partner", $partner);
$reqHandler->setParameter("out_trade_no", $out_trade_no);
$reqHandler->setParameter("total_fee", $total_fee);  //总金额
$reqHandler->setParameter("return_url", $return_url);
$reqHandler->setParameter("notify_url", $notify_url);
$reqHandler->setParameter("body", $desc);
$reqHandler->setParameter("bank_type", $_REQUEST['bank_type_value']);  	  //银行类型，默认为财付通
//用户ip
$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
$reqHandler->setParameter("fee_type", "1");               //币种
$reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）

//系统可选参数
$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
$reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

//业务可选参数
$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
$reqHandler->setParameter("product_fee", "");        	  //商品费用
$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
$reqHandler->setParameter("time_expire", "");             //订单失效时间
$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
$reqHandler->setParameter("goods_tag", "");               //商品标记
$reqHandler->setParameter("trade_mode",$trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
$reqHandler->setParameter("transport_desc","");              //物流说明
$reqHandler->setParameter("trans_type","1");              //交易类型
$reqHandler->setParameter("agentid","");                  //平台ID
$reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
$reqHandler->setParameter("seller_id","");                //卖家的商户号



//请求的URL
$reqUrl = $reqHandler->getRequestURL();
$debugInfo = $reqHandler->getDebugInfo();

//---保存数据库操作
require_once("classes/dbmysql.class.php");
$oDb = DbMysql::getInstance($aDbConfig);
$data = array(
			'type' => $_REQUEST['type'],//1业主，2装修公司
			'target' => $_REQUEST['target'],//uid,mid用户id
			'money' => $order_price,
			'mch_vno' => $mch_vno,//订单号
			'req_url' => $reqUrl,
			'debug_info' => $debugInfo,
			'createtime' => time(),
		);

$ret = $oDb->insert('tb_pay', $data);
if (!$ret) {
	file_put_contents('error.txt', var_export($data, true) . "\n", FILE_APPEND);
}

//---跳转
header("Location:" . $reqUrl);
exit;

//获取debug信息,建议把请求和debug信息写入日志，方便定位问题
/**/

echo "<br/>" . $reqUrl . "<br/>";
echo "<br/>" . $debugInfo . "<br/>";


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>财付通即时到帐程序演示</title>
</head>
<body>
<br/><a href="<?php echo $reqUrl ?>" target="_blank">财付通支付</a>
<form action="<?php echo $reqHandler->getGateUrl() ?>" method="post" target="_blank">
<?php
$params = $reqHandler->getAllParameters();
foreach($params as $k => $v) {
echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
}
?>
<input type="submit" value="财付通支付">
</form>
</body>
</html>

