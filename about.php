<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$no_getxq=1;
require_once('function.php');
$f='about.php';
$a_abm=array(
	'yijianli'=>'关于易监理',
	'service'=>'服务流程',
	'pay'=>'收费标准',
	'paypal'=>'支付宝支付',
	'contact'=>'联系我们',
);
$tid=(isset($_GET['t']) && isset($a_abm[$_GET['t']]))?$_GET['t']:'yijianli';
$c='<div class="about">
			<div class="left">
				<ul>';
foreach($a_abm as $k=>$v)$c.='<li><a href="about-'.$k.'.html"'.($k==$tid?' class="current"':'').'>'.$v.'</a></li>';
$c.='</ul>
			</div>
			<div class="right">
				<h2 class="h2">'.$a_abm[$tid].'</h2>
				<div class="about_bg clearfix">
					<div class="angle"></div>
					<div class="about_cnt">';
switch($tid){
	case 'contact':
		$c.='上海易至居工程监理有限公司<br/><br/>地　　址：上海市国康路46号<br/>客服电话：400-990-2013<br/>电　　话：021-65975962<div id="allmap" style="width: 600px;height: 450px;margin-top: 20px;overflow: hidden;"></div><script type="text/javascript" src="http://api.map.baidu.com/api?v=1.3"></script><script type="text/javascript">
var sContent="<h3>上海易至居工程监理有限公司</h3>地　　址：上海市国康路46号<br/>客服电话：400-990-2013<br/>电　　话：021-65975962";
var map = new BMap.Map("allmap");
var point = new BMap.Point(121.510076,31.292237);
var marker = new BMap.Marker(point);
var infoWindow = new BMap.InfoWindow(sContent);
map.centerAndZoom(point, 18);
map.addControl(new BMap.NavigationControl());
map.addOverlay(marker);
marker.addEventListener("click", function(){          
	this.openInfoWindow(infoWindow);
});
</script>';
		break;
	case 'paypal':
		$c.='公司名称：上海易至居工程监理有限公司<br/><br/>支付宝账号：5678107@qq.com';
		break;
	case 'pay':
		header("Location:new/index.php?s=/supervisor/consult/type/16");exit;
		require_once('jg.php');
		$c.='<style>.jgtd {background: #808080;}.jgtd td {background: #fff;padding: 5px;font-size: 12px;line-height: 25px;}.jgtd th {background: #ee6c00;color: #fff;font-weight: bold;padding: 5px;font-size: 12px;line-height: 25px;}</style><h1 style="font-size:20px;padding-bottom:20px;">公寓房</h1><table width="636" cellspacing="1" class="jgtd">
<tr>
<th width="30" align="center">序号</th>
<th width="120" align="center">监理类型</th>
<th align="center">服务内容</th>
<th width="138" align="center">收费标准</th>
</tr>'.$jg_gy.'</table><br/><br/>
<h1 style="font-size:20px;padding-bottom:20px;">别墅</h1><table width="636" cellspacing="1" class="jgtd">
<tr>
<th width="30" align="center">序号</th>
<th width="120" align="center">监理类型</th>
<th align="center">服务内容</th>
<th width="138" align="center">收费标准</th>
</tr>'.$jg_bs.'</table><br/><br/>
<h1 style="font-size:20px;padding-bottom:20px;">验房</h1><table width="636" cellspacing="1" class="jgtd">'.$jg_yf.'</table>';
		break;
	case 'service':
		$c.='<img src="images/about-lc.gif" alt=""/>';
		break;
	default:
		$c.='<div style="float:left;margin-right:20px;"><img src="images/image001.jpg" /><p style=" line-height:22px;padding-top:5px;">易监理合伙人<br />澳洲执业监理师<br /><a>张文海</a></p></div>
						<div style="float:right; width:490px;">
						<h1 style="font-size:20px;padding-bottom:20px;">易监理是什么？</h1>
						<p>1、易监理为业主提供线上监理咨询，线下实体解决方案的第三方监理公司。<br />
						2、易监理是精英监理：我们代表着行业里最优秀一批人，讲良心，讲责任！<br />
						3、线下监理采用澳洲的监理服务模式：注重过程控制，防患于未然，比如：这次问题列出来，提供解决方案，然后根据进度列出下次要检查的内容和标准，下次过来时优先检查上次预告检查的项目，如此类推！</p>
						<br />
						<p style="text-indent:2em;">易监理对施工问题采取追溯制，注重施工进度各节点的照片和资料完备，比如，施工图和竣工图都要求非常完备，任何地方出问题，都可很方便的找到责任人和相关资料，并确定合适方案去解决问题。</p>
						<br />
						<p style="text-indent:2em;">易监理网透明的照片式监理和监理报告接受社会专业人士的监督，我们眼里只有合格或不合格，这是目前最透明公正的方式来为业主服务！</p>
						<br />
						<p>我们希望用精英的力量去改良这个行业，让业主能得到本该得到的更好的品质！</p>
						<br />
						<p>谢谢！</p>
						<br />
						<p>相信正能量，相信易监理！</p>
						<br />
						<p>注：“易监理”隶属于“上海易至居工程监理有限公司”</p>
						</div>';
		break;
}
$c.='</div>
				</div>
			</div>
		</div>';
echo yjl_html($c, 'index');
?>