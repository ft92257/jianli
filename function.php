<?php
error_reporting(0);
select_yjl_db();
$charset_conn=0;

$surl_p='url/';
$dbprefix=$config['db_table_prefix'];

if (substr($_SERVER['HTTP_REFERER'], -11) == '/yjlnew.php') {
	$_SERVER['HTTP_REFERER'] = $_GET['referer'];
}

function select_yjl_db() {
	global $config;
	$hostname_conn=$config['db_host'];
	$database_conn=$config['db_name'];
	$username_conn=$config['db_user'];
	$password_conn=$config['db_pass'];
	$conn=mysql_connect($hostname_conn, $username_conn, $password_conn) or die(mysql_error());
	mysql_select_db($database_conn, $conn);
	mysql_query("SET NAMES 'utf8'", $conn);
}

//db
require_once "lib/dbmysql.class.php";
require_once "lib/session.class.php";

//新版修改
function getDb() {
	global $aNewDbConfig;
	$oDb = DbMysql::getInstance($aNewDbConfig);
	
	return $oDb;
}

/*
 * 同步退出
 */
function syn_logout() {
	global $udb;
	$oDb = getDb();
	$Session = new Session($oDb);
	$Session->destroy($udb['uid']);
	
	select_yjl_db();
}

/*
 * 同步登录
 */
function syn_login($r_res) {
	$oDb = getDb();
	checkUser($r_res);
	$Session = new Session($oDb);
	$Session->setKey($r_res['uid']);
	
	select_yjl_db();
}

/*
 * 如果新版没有该用户则添加用户
 */
function checkUser($r_res) {
	$oDb = getDb();
	$cWhere = "id = '" . $r_res['uid'] . "' AND status = 0";
	$cSql ="SELECT * FROM tb_user WHERE $cWhere";
	$aUser = $oDb->fetchFirstArray($cSql);
	if (empty($aUser)) {
		$data = array(
			'appid' => 1,//配置信息
			'id' => $r_res['uid'],
			'type' => ($r_res['qx'] >= 5) ? 2 : 1,//区分业主和监理师
			'account' => $r_res['email'] ? $r_res['email'] : $r_res['nickname'],
			//'password' => $r_res['password'],
			'email' => $r_res['email'],
			'reg_ip' => $r_res['regip'],
			'last_login' => time(),
			'createtime' => time(),
		);
		$oDb->insert('tb_user', $data);
	} else {
		$aSet = array(
			'last_login' => time(),
		);
		if ($aUser['last_login'] < strtotime(date('Y-m-d'))) {
			//每天登录积分加1
			$aSet['integral'] = $aUser['integral'] + 1;
			if ($aUser['usertype'] == 1 && $aSet['integral'] > 3) {
				$aSet['isreal'] = 1;
				//更新评分表
				$oDb->update('tb_score', array('isreal' => 1), "uid = " . $r_res['uid']);
			}
		}
		$oDb->update('tb_user', $aSet, $cWhere);
	}
}

/*
 * 反向同步【暂未使用】
 */
function checkSession() {
	$oDb = getDb();
	
	$key = $_COOKIE['SN_KEY'];
	if (empty($key)) {
		return false;
	}
	
	$data = $oDb->fetchFirstArray("SELECT * FROM tb_session WHERE `key` = '$key'");
	//$data = $this->where(array('key'=>$key))->find();
	if (empty($data)) {
		return false;
	}
	
	//超过2小时过期
	if (($data['expire'] + 7200) < time()) {
		return false;
	}
	//ip变化
	if (get_client_ip() != $data['ip']) {
		return false;
	}
	
	//更新有效期
	$oDb->update('tb_session', array('expire' => time() + 7200), "id = " . $data['id']);
	//$this->where(array('id'=>$data['id']))->data(array('expire' => time() + 7200))->save();
	
	return $data['uid'];
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
	$type       =  $type ? 1 : 0;
	static $ip  =   NULL;
	if ($ip !== NULL) return $ip[$type];
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos    =   array_search('unknown',$arr);
		if(false !== $pos) unset($arr[$pos]);
		$ip     =   trim($arr[0]);
	}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip     =   $_SERVER['HTTP_CLIENT_IP'];
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip     =   $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u",ip2long($ip));
	$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}

function chksqlv(){
	return version_compare(mysql_get_server_info(), '4.1.0', '>=');
}

if(chksqlv()){
	mysql_query("SET NAMES 'utf8'", $conn);
	$charset_conn=1;
}

$q_main=sprintf('select * from %s limit 1', $yjl_dbprefix.'main');
$main=mysql_query($q_main) or die(mysql_error());
$r_main=mysql_fetch_assoc($main);
mysql_free_result($main);
//$d_l1id=175;
//$d_l1title='杭州市';
$p_size=20;
$max_file=1024*2;
$u_ea=array('jpg', 'gif', 'png');
$u_jlea=array('jpg', 'doc', 'docx', 'pdf');
$a_filet=array('image/jpeg', 'application/msword', 'application/msword', 'application/pdf');
$a_wh_wbtp=array(80, 80);
$a_wh_jltt=array(63, 63);
$a_wh_jltpt=array(80, 80);
$a_wh_jltpw=600;
$a_wh_maxw=1000;
$a_wh_jlqt=array(128, 128);
$a_wh_utxb=array(128, 128);
$a_wh_utxs=array(50, 50);
$a_wh_utxm=array(65, 65);
$a_wh_xzhd=array(160, 160);
$a_wh_hdadw=250;
$a_wh_video=array(128, 96);
$pbl_w=200;
$pbl_s=9;
$pbl_p=2;
$fxt_w=230;
$yzm_sjjg=60;
$a_tsgz=array(
	5=>array('监理师'),
	array('设计师', '供应商', '施工企业')
);
$a_utv=array(2=>'监理', 1=>'业主', 0=>'友邻');
$a_gz=array(1=>'施工队长', '油漆工', '电工', '木工', '泥瓦工');
$a_cp=array(1=>'地板', '油漆', '饰品');
$a_lc=array(1=>'隐蔽', '泥木', '油漆', '安装', '竣工', '软装');
$a_tlc=array(7=>'验房', '文件储存', '视频监理');
$a_clys=array(1=>'木制品', '油漆', '墙地砖', '电器', '其他');
$a_file=array(1=>'效果图', '预算书', '合同', '施工图纸');
$a_txlc=array('设计师', '项目经理', '材料商', '施工人员', '业主', '其他');
$a_dpfl=array(
	array(1=>'专业', '服务'),
	array(1=>'专业', '品质'),
	array(1=>'服务', '价格')
);
$a_ys=array(1=>'5万元以下', '5万-10万元', '10万-20万元', '20万-25万元', '25万-50万元', '50万元以上');
$a_fg=array(1=>'现代简约', '欧式古典', '田园风格', '东南亚风格', '地中海', '美式乡村', '雅致风格', '日式风格', '中式风格', '新古典主义', '后现代风格', '其他');
$a_mj=array(1=>'90m&#178;以下', '90-120m&#178;', '120-150m&#178;', '150-200m&#178;', '200-500m&#178;', '500m&#178;以上');
$a_privacyo=array('所有人可见', '仅好友可见', '仅自己可见');
$maxpf=5;
$a_xqgc=array(1=>'新手上路', '装修设计', '装修过程', '软装', '晒账单');
$a_bank=array(1=>'中国农业银行', '中国工商银行', '中国建设银行', '招商银行');
$a_qzca=array(1=>'合同/预算', '施工问题');
$a_pay=array(
	1=>array('alipay', '支付宝')
);
$sy_img0=array('sy1.png', 150, 51);
$sy_img1=array('sy2.png', 100, 42);
$a_userzd=array(
	'nc'=>'',
	'qx'=>0,
	'gzfl'=>0,
	'iswc'=>0,
	'xqid'=>0,
	'fxid'=>0,
	'xq_0'=>'',
	'xq_1'=>'',
	'mobile'=>'',
	'mcode'=>'',
	'misyz'=>0,
	't_mobile'=>'',
	't_mcode'=>'',
	'postcode'=>'',
	'mj'=>0,
	'ys'=>0,
	'fg'=>0,
	'ys_0'=>0,
	'ys_1'=>0,
	'ys_2'=>0,
	'ys_3'=>0,
	'ys_0_name'=>2,
	'ys_0_xq'=>2,
	'ys_0_mob'=>2,
	'ys_0_qq'=>1,
	'ys_0_msn'=>1,
	'zsyz'=>0,
	'isxg'=>0,
	'iszxjl'=>0,
	'isnc'=>0,
	'tsgz'=>0,
	'is_wb'=>0,
	'email_ls'=>'',
	'email_code'=>''
);
$a_fx=array(
	array('新浪微博', 'http://v.t.sina.com.cn/share/share.php?url=[u]&title=[t]'),
	array('腾讯微博', 'http://v.t.qq.com/share/share.php?url=[u]&title=[t]'),
	array('QQ空间', 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=[u]&title=[t]'),
	array('人人', 'http://widget.renren.com/dialog/share?resourceUrl=[u]&srcUrl=[u]&title=[t]'),
	array('开心网', 'http://www.kaixin001.com/rest/records.php?url=[u]&style=11&content=[t]'),
	array('百度搜藏', 'http://cang.baidu.com/do/add?iu=[u]&it=[t]'),
	array('百度空间', 'http://apps.hi.baidu.com/share/?url=[u]&title=[t]'),
	array('百度贴吧', 'http://tieba.baidu.com/i/app/open_share_api?link=[u]&title=[t]'),
	array('MSN', 'http://profile.live.com/badge?url=[u]&title=[t]'),
	array('豆瓣', 'http://shuo.douban.com/!service/share?href=[u]&name=[t]'),
	array('网易微博', 'http://t.163.com/article/user/checkLogin.do?info=[t][u]'),
	array('搜狐微博', 'http://t.sohu.com/third/post.jsp?url=[u]&title=[t0]')
);
$a_py=array(1=>'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '#');
$jldimg_jg=3600*3;
$jldimg_zs=3600*6;
$q_res=sprintf('select jpid, url, t_url, o_url from %s where (isjl=0 or isjl=2) and is_del=1 and deltime<%s', $yjl_dbprefix.'jl_photo', (time()-$jldimg_zs));
$res=mysql_query($q_res) or die(mysql_error());
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_photo', $r_res['jpid']);
	$result=mysql_query($dSQL) or die(mysql_error());
	$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_topic', $r_res['jpid']);
	$result=mysql_query($dSQL) or die(mysql_error());
	unlink($r_res['url']);
	unlink($r_res['t_url']);
	unlink($r_res['o_url']);
}
mysql_free_result($res);
//$is_ie6=yjl_chkuag('MSIE 6')?1:0;

function yjl_SQLString($c, $t){
	$c=(function_exists('get_magic_quotes_gpc') && !get_magic_quotes_gpc())?addslashes($c):$c;
	switch($t){
		case 'text':
			$c=($c!='')?"'".str_replace("'", '&#039;', $c)."'":'NULL';
			break;
		case 'search':
			$c="'%%".$c."%%'";
			break;
		case 'int':
			$c=($c!='')?intval($c):'0';
			break;
	}
	return $c;
}

function yjl_writeText($f,$c){
	if(is_writable($f) || !file_exists($f)){
		if(!$h=fopen($f,'w'))return false;
		if(!fwrite($h,$c))return false;
		fclose($h);
	}else{
		return false;
	}
	return true;
}

function yjl_substrs($c, $zl=4){
	$l=$zl*3;
	if(strlen($c)>$l){
		$l-=3;
		$n=0;
		for($i=0;$i<$l;$i++){
			if(ord($c[$i])>127)$n++;
		}
		if($n%3>0)$l+=3-$n%3;
		$c=substr($c,0,$l).'…';
	}
	return $c;
}

function yjl_getIP(){
	global $_SERVER;
	if(getenv('HTTP_CLIENT_IP')){
		$ip=getenv('HTTP_CLIENT_IP');
	}elseif(getenv('HTTP_X_FORWARDED_FOR')){
		$ip=getenv('HTTP_X_FORWARDED_FOR');
	}elseif(getenv('REMOTE_ADDR')){
		$ip=getenv('REMOTE_ADDR');
	}else{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function yjl_imgpath($uid){
	$k='www.jishigou.com';
	$h=md5($k."\t".$uid."\t".strlen($uid)."\t".$uid%10);
	$f0=$h{$uid%32}.'/';
	$f1=$f0.abs(crc32($h)%100).'/';
	return array($f0, $f1);
}

function yjl_urlkey($id){
	$index='z6OmlGsC9xqLPpN7iw8UDAb4HIBXfgEjJnrKZSeuV2Rt3yFcMWhakQT1oY5v0d';
	$base=62;
	$out='';
	$c=floor(log10($id)/log10($base));
	for($t=$c;$t>=0;$t--){
		$a=floor($id/pow($base, $t));
		$out=$out.substr($index, $a, 1);
		$id=$id-($a*pow($base, $t));
	}
	return $out;
}

function yjl_sendpost($u, $d){
	$u=parse_url($u);
	$s=@fsockopen($u['host'], 80, $en, $es, 30);
	if(!$s){
		return false;
	}else{
		fwrite($s, 'POST '.$u['path']." HTTP/1.0\r\n");
		fwrite($s, 'Host: '.$u['host']."\r\n");
		fwrite($s, "Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n");
		fwrite($s, 'Content-length: '.strlen($d)."\r\n");
		fwrite($s, "Accept: */*\r\n");
		fwrite($s, "\r\n");
		fwrite($s, $d."\r\n");
		fwrite($s, "\r\n");
		$h='';
		while($t=trim(fgets($s, 4096)))$h.=$t."\n";
		$c='';
		while(!feof($s))$c.=fgets($s, 4096);
		fclose($s);
		return $c;
	}
}

function yjl_getfurl($u){
	$u=trim($u);
	if($u!='')return ((!strstr($u, '://') && !strstr($u, ':\\'))?'http://':'').$u;
}

function yjl_authcode($s, $o='DECODE', $k='', $e=0){
	$c_l=4;
	$k=md5($k);
	$ka=md5(substr($k, 0, 16));
	$kb=md5(substr($k, 16, 16));
	$kc=$c_l?($o=='DECODE'?substr($s, 0, $c_l):substr(md5(microtime()), -$c_l)):'';
	$c_k=$ka.md5($ka.$kc);
	$k_l=strlen($c_k);
	$s=$o=='DECODE'?base64_decode(substr($s, $c_l)):sprintf('%010d', $e?$e+time():0).substr(md5($s.$kb), 0, 16).$s;
	$s_l=strlen($s);
	$result='';
	$box=range(0, 255);
	$r_k=array();
	for($i=0;$i<=255;$i++)$r_k[$i]=ord($c_k[$i%$k_l]);
	for($j=$i=0;$i<256;$i++){
		$j=($j+$box[$i]+$r_k[$i])%256;
		$tmp=$box[$i];
		$box[$i]=$box[$j];
		$box[$j]=$tmp;
	}
	for($a=$j=$i=0;$i<$s_l;$i++){
		$a=($a+1)%256;
		$j=($j+$box[$a])%256;
		$tmp=$box[$a];
		$box[$a]=$box[$j];
		$box[$j]=$tmp;
		$result.=chr(ord($s[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($o=='DECODE'){
		if((substr($result, 0, 10)==0 || substr($result, 0, 10)-time()>0) && substr($result, 10, 16)==substr(md5(substr($result, 26).$kb), 0, 16)){
			return substr($result, 26);
		}else{
			return '';
		}
	}else{
		return $kc.str_replace('=', '', base64_encode($result));
	}
}

function yjl_getzs($c){
	$t=strlen($c);
	$j=0;
	$h=0;
	for($i=0;$i<$t;$i++){
		if(ord(substr($c,$i,1))>127){
			if($h==0){
				$h=1;
			}else{
				$h=0;
			}
		}else{
			$h=0;
		}
		if($h==0)$j++;
	}
	return $j;
}

function yjl_chklog(){
	global $_COOKIE, $config, $_SERVER, $dbprefix;
	$admin_id=0;
	if(isset($_COOKIE[$config['cookie_prefix'].'ajhAuth']) && $_COOKIE[$config['cookie_prefix'].'ajhAuth']!=''){
		$k=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_');
		$lk=yjl_authcode($_COOKIE[$config['cookie_prefix'].'ajhAuth'], 'DECODE', $k);
		$ak=explode("\t", $lk);
		if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
			$q_rep=sprintf('select uid from %s where uid=%s and password=%s and role_id=2 limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'int'));
			$rep=mysql_query($q_rep) or die(mysql_error());
			if(mysql_num_rows($rep)>0)$admin_id=$ak[1];
			mysql_free_result($rep);
		}
	}
	return $admin_id;
}

function yjl_chkulog(){
	global $_COOKIE, $config, $_SERVER, $dbprefix, $yjl_dbprefix, $a_userzd;
	$udb=array('uid'=>0);
	
	if(isset($_COOKIE[$config['cookie_prefix'].'auth']) && $_COOKIE[$config['cookie_prefix'].'auth']!=''){
		$lk=yjl_authcode($_COOKIE[$config['cookie_prefix'].'auth'], 'DECODE', $config['auth_key']);
		$ak=explode("\t", $lk);

		if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
			//登录验证成功
		}else {
			return $udb;
		}
	} else {
		return $udb;
	}
	
	//新版修改
	//$uid = checkSession();
	
	global $config;
	$hostname_conn=$config['db_host'];
	$database_conn=$config['db_name'];
	$username_conn=$config['db_user'];
	$password_conn=$config['db_pass'];
	$dbprefix=$config['db_table_prefix'];
	$conn=mysql_connect($hostname_conn, $username_conn, $password_conn) or die(mysql_error());
	mysql_select_db($database_conn, $conn);
	
	//if ($uid) {
		//$q_res=sprintf('select * from %s where uid=%s limit 1', $dbprefix.'members', $uid);
		$q_res=sprintf('select * from %s where uid=%s and password=%s limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'text'));
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		//print_r($q_res);die;
		if(mysql_num_rows($res)>0){
			$q_rep=sprintf('select * from %s where uid=%s limit 1', $yjl_dbprefix.'members', $r_res['uid']);
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				foreach($a_userzd as $k=>$v)$r_res[$k]=$r_rep[$k];
			}else{
				$r_res['qx']=$r_res['role_id']==2?10:0;
				$r_res['iswc']=$r_res['qx']==10?1:0;
				foreach($a_userzd as $k=>$v){
					if(!isset($r_res[$k]))$r_res[$k]=$v;
				}
				//新版修改 添加isnc,nc
				//$iSQL=sprintf("insert into %s (uid, qx, isnc, nc) values (%s, %s, 1, '%s')", $yjl_dbprefix.'members',
						//$r_res['uid'], $r_res['qx'], $r_res['nickname']);
				$iSQL=sprintf("insert into %s (uid, qx) values (%s, %s)", $yjl_dbprefix.'members',
						$r_res['uid'], $r_res['qx']);
				//echo $iSQL;
				
				$result=mysql_query($iSQL);
			}
			mysql_free_result($rep);
			$udb=$r_res;
			if($udb['qx']==5 || $udb['qx']==6){
				$q_rep=sprintf('select uid from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)==0){
					$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'ujl', $udb['uid']);
					$repult=mysql_query($iSQL) or die(mysql_error());
				}
				mysql_free_result($rep);
			}elseif($udb['qx']==0){
				$q_rep=sprintf('select uid from %s where uid=%s limit 1', $yjl_dbprefix.'uyz', $udb['uid']);
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)==0){
					$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'uyz', $udb['uid']);
					$repult=mysql_query($iSQL) or die(mysql_error());
				}
				mysql_free_result($rep);
			}

			//同步登录新版
			syn_login($r_res);
		}
		mysql_free_result($res);
	//}

	return $udb;
}

function yjl_udb($uid){
	global $dbprefix, $yjl_dbprefix, $a_userzd;
	$udb=array('uid'=>0);
	$q_res=sprintf('select * from %s where uid=%s limit 1', $dbprefix.'members', $uid);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$q_rep=sprintf('select * from %s where uid=%s limit 1', $yjl_dbprefix.'members', $r_res['uid']);
		$rep=mysql_query($q_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			foreach($a_userzd as $k=>$v)$r_res[$k]=$r_rep[$k];
		}else{
			$r_res['qx']=$r_res['role_id']==2?10:0;
			$r_res['iswc']=$r_res['qx']==10?1:0;
			foreach($a_userzd as $k=>$v){
				if(!isset($r_res[$k]))$r_res[$k]=$v;
			}
			$iSQL=sprintf('insert into %s (uid, qx) values (%s, %s)', $yjl_dbprefix.'members',
				$r_res['uid'],
				$r_res['qx']);
			$result=mysql_query($iSQL);
		}
		mysql_free_result($rep);
		$udb=$r_res;
		if($udb['qx']==5 || $udb['qx']==6){
			$q_rep=sprintf('select uid from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
			$rep=mysql_query($q_rep) or die(mysql_error());
			if(mysql_num_rows($rep)==0){
				$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'ujl', $udb['uid']);
				$repult=mysql_query($iSQL) or die(mysql_error());
			}
			mysql_free_result($rep);
		}elseif($udb['qx']==0){
			$q_rep=sprintf('select uid from %s where uid=%s limit 1', $yjl_dbprefix.'uyz', $udb['uid']);
			$rep=mysql_query($q_rep) or die(mysql_error());
			if(mysql_num_rows($rep)==0){
				$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'uyz', $udb['uid']);
				$repult=mysql_query($iSQL) or die(mysql_error());
			}
			mysql_free_result($rep);
		}
	}
	mysql_free_result($res);
	return $udb;
}

function yjl_adminhtml($c){
	global $yjl_tpath, $js_c;
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="'.$yjl_tpath.'templates/default/styles/admincp.css" rel="stylesheet" type="text/css" /><link href="admin.css" rel="stylesheet" type="text/css" /><script type="text/javascript" src="lib/jquery.js"></script><script type="text/javascript" src="lib/function.js"></script>'.($js_c!=''?'<script type="text/javascript">
$(document).ready(function(){
	'.$js_c.'
});
</script>':'').'</head><body><table width="100%" border="0" cellpadding="2" cellspacing="6" style="_margin-left:-10px; "><tr><td><table width="100%" border="0" cellpadding="2" cellspacing="6"><tr><td>'.$c.'</td></tr></table></td></tr></table></body></html>';
}

function yjl_getMsg($c){
	return '<table cellspacing="1" cellpadding="4" width="100%" align="center" class="tableborder" id="msg_table"><tr><td style="padding-left:20px;">'.$c.' <a href="#" onclick="$(\'#msg_table\').hide();return false;">x</a></td></tr></table>';
}

function yjl_userl($uid, $mid=''){
	global $udb, $yjl_dbprefix;
	$s='<h2>个人中心</h2><ul class="list_centnav hover">
				<li'.($mid=='index'?' class="current"':'').'><a href="user-'.$uid.'.html">我的首页</a></li>
				<li'.($mid=='hd'?' class="current"':'').'>'.(($udb['uid']==$uid && ($udb['xqid']>0 || $udb['isxg']>0 || $udb['qx']>0))?'<span><a href="active_create.php?xqid='.$udb['xqid'].'">+发起</a></span>':'').'<a href="user_active.php?id='.$uid.'">我的活动</a></li>
				<li'.($mid=='jl'?' class="current"':'').'>';
	$iscj=0;
	if($udb['uid']==$uid){
		if($udb['qx']==5){
			$iscj=1;
		}elseif($udb['qx']==0){
			$q_res=sprintf('select hzid from %s where hzid=%s and isjs=0 limit 1', $yjl_dbprefix.'jl', $udb['uid']);
			$res=mysql_query($q_res) or die(mysql_error());
			if(mysql_num_rows($res)==0)$iscj=1;
			mysql_free_result($res);
		}
	}
	if($iscj>0)$s.='<span><a href="photo_create.php?xqid='.$udb['xqid'].'">+创建</a></span>';
	$s.='<a href="user_decoration.php?id='.$uid.'">我的项目</a></li>
			</ul>
			<hr class="dotted" />
			<ul class="list_centnav hover">
				<li'.($mid=='hy'?' class="current"':'').'><a href="follow.php?id='.$uid.'">好友</a></li>'.($udb['uid']==$uid?'
				<li'.($mid=='yx'?' class="current"':'').'><a href="msg.php">信箱</a></li>
				<li'.($mid=='dt'?' class="current"':'').'><a href="user_log.php">动态</a></li>
				<li'.($mid=='sc'?' class="current"':'').'><a href="myfav.php">收藏夹</a></li>
				<li'.($mid=='zx'?' class="current"':'').'><a href="address_book.php">装修通讯录</a></li>':'').'
			</ul>';
	return $s;
}

function yjl_gehtml($l, $c, $t){
	global $js_c, $isupimg, $yjl_tpath, $xqid, $xqdb, $page_title, $r_main, $a_fx, $udb, $uadb, $cuid;
	$s='<div class="location"><a href="user-'.$cuid.'.html">'.($cuid!=$udb['uid']?$uadb[$cuid]['nc'].'的':'').'个人中心</a>&gt;<a href="#">'.$t.'</a></div><div class="left">'.$l.'</div><div class="right">'.$c.'</div>';
	return yjl_html($s, 'per_center', 'center');
}

function yjl_html_head($c, $css='', $body_id='', $menu_id=0){
	global $js_c, $js_scrc, $isupimg, $yjl_tpath, $xqid, $xqdb, $page_title, $r_main, $a_fx, $udb, $is_home, $is_nologin, $is_mce, $yjl_isdebug, $d_l1title;
	$ptitle=($page_title!=''?$page_title.' | ':'').$r_main['site_name'];
	if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0)){
		$js_c.='
		jlmsg_v();';
		$js_scrc.='
		jlmsg_p();';
	}
	if($js_scrc!='')$js_c.='
	$(window).scroll(function(){
	'.$js_scrc.'
});';
	$s='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>'.$ptitle.' - 装修行业中代表良心的力量，家装监理，连锁店装修监理，别墅装修监理，别墅监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
	<meta name="keywords" content="易监理,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
	<meta name="description" content="易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="apple-touch-icon" href="images/iphone_logo.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="images/ipad_logo.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="images/iphone_retina_logo.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="images/ipad_retina_logo.png" />
	<meta property="wb:webmaster" content="4e74fb62f6f0d410" />
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<link href="css/old.css" rel="stylesheet" type="text/css" />'.($css!=''?'
			<link href="css/'.$css.'.css" rel="stylesheet" type="text/css" />':'').'
			<script type="text/javascript" src="scripts/jquery.dookay.min.js"></script>
			<script type="text/javascript" src="scripts/jquery.dookay.plugin.js"></script>
			<script type="text/javascript" src="lib/jquery.rotate.js"></script>
			<script type="text/javascript" src="lib/function.js"></script>
			'.($is_mce>0?'<script type="text/javascript" src="lib/tiny_mce/jquery.tinymce.js"></script>':'').($isupimg>0?'<script type="text/javascript" src="'.$yjl_tpath.'templates/default/js/swfobject.js"></script><script type="text/javascript" src="'.$yjl_tpath.'images/uploadify/jquery.uploadify.v2.1.4.min.js"></script><style type="text/css">@import "'.$yjl_tpath.'images/uploadify/uploadify.css";</style>':'').($js_c!=''?'<script type="text/javascript">
					$(document).ready(function(){
					'.$js_c.'
});
					</script>':'').'<!--[if IE 6]>
					<script type="text/javascript" src="scripts/dookay.png.js" ></script>
					<script type="text/javascript">
					DD_belatedPNG.fix(\' #phead,.hd_ico,.mn_ico,.show \');
					</script>
					<![endif]-->
					</head>
					<body>';
	if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0))$s.='<div id="jlmsg_v" style="display: none;"><a href="#" onclick="$(\'#jlmsg_v\').hide();$(\'#jlmsg_isl\').val(\'1\');return false;"><img src="images/list_style02.gif" style="float: right;"></a><span id="msg_cs">0</span>条新咨询'.(($udb['qx']==5 && $udb['iszxjl']==1)?'，<a href="faq-no.html">查看并回答</a>':'').'<input type="hidden" id="jlmsg_isl" value="0"/><input type="hidden" id="jlmsg_v" value="0"/></div>';
	$s.='<div id="wrap">

	<link rel="stylesheet" href="house/statics/index/css/style.css">
	<div class="wrap">
	<div class="pbar">
	<div class="top clearfix">
	<div class="left city_list">
     '.$d_l1title.'&nbsp;<a href="javascript:void(0)" onclick="$(\'#city_list_box\').show();" class="open_clos">[请选择地区]</a>
     <div id="city_list_box" class="city_list_box">
      <div style="position:relative"><em class="cityem_t"></em><em onclick="$(\'#city_list_box\').hide();" class="off_cityList"></em></div>
      <div>进入：<span style="font-size:20px; font-weight:bold"><a href="http://www.yijianli.com/index.php">上海</a></span></div>
      <h2 class="citytitle">选择您的对应地区</h2>
      <div class="city_content">
       <a href="http://www.yijianli.com/index.php" class="am_addstyle">上海</a><a href="http://sz.yijianli.com/index.php">苏州</a><a href="http://nj.yijianli.com/index.php">南京</a><a href="http://wx.yijianli.com/index.php">无锡</a><a href="http://hz.yijianli.com/index.php">杭州</a><a href="http://nb.yijianli.com/index.php">宁波</a><a href="http://sx.yijianli.com/index.php">绍兴</a>
      </div>
     </div>
    </div>
	<div class="left">
	<ul>
	<li><span><img src="house/statics/index/images/Construction/top1.fw.png" class="hmiddle"></span><span class="hmiddle">100%服务品质</span></li>
	<li><span><img src="house/statics/index/images/Construction/top2.fw.png" class="hmiddle"></span><span class="hmiddle">A级信誉</span></li>
	<li><span><img src="house/statics/index/images/Construction/top3.fw.png" class="hmiddle"></span><span class="hmiddle">提升20%质量</span></li>
	<li><span><img src="house/statics/index/images/Construction/top4.fw.png" class="hmiddle"></span><span class="hmiddle">100%省钱</span></li>
	</ul>
	</div>';

	if($udb['uid']>0){
		$iswc=(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0)?0:1;
		$s.='<div class="right login">'
		.($iswc>0 ? '<a href="msg.php"><span class="hd_ico ico05" style="vertical-align: bottom;">&nbsp;</span>'.($udb['newpm']>0?'<small style="color: #f00;">'.($udb['newpm']>9?'9+':$udb['newpm']).'</small>':'').'</a>':'').'
		<a href="user-'.$udb['uid'].'.html">'.($udb['nc']!=''?$udb['nc']:$udb['email']).'</a>
		<a href="profile.php">设置</a>
		<a href="user_decoration.php?id='.$udb['uid'].'">个人中心</a>
		'.(($udb['qx']==10 || $udb['isxg']>0)?'<a href="admin.php">后台管理</a>':'').'
		<a href="logout.php?referer='.$_SERVER['HTTP_REFERER'].'">退出登录</a>
		</div>';
	}else{
		$s .= '<div class="right login">已有账号？&nbsp;<a href="login.php?referer='.$_SERVER['HTTP_REFERER'].'" id="link_login" rel="#overlay_login">登陆</a>&nbsp;&nbsp;&nbsp;&nbsp;<span class="orange"><a href="reg.php?referer='.$_SERVER['HTTP_REFERER'].'">免费注册</a></span>&nbsp;&nbsp;<a href="login.php?t=sina" title="新浪微博登录"><span class="mn_ico ico11"></span></a>&nbsp;<a href="login.php?t=tqq" title="QQ登录"><span class="mn_ico ico12"></span></a></div>';
	}

	//<a href="house" data-match="/house"><span class="oco05"></span>谁施工好</a>
	$s .='</div>
	</div><!-- pbar结束 -->
	<div class="phead">
	<div class="head clearfix">
	<div class="logo left">装修从此容易！</div>
	
	<div class="menu left" id="main_menu">
      <a href="index.php" data-match="index.php$"><span class="oco01"></span>首页</a>
      <a href="new" data-match="/new"><span class="oco02"></span>选监理</a>
      <a href="photo-0.html" data-config="default"><span class="oco03"></span>监理项目</a>
      <a href="house/?s=/Active" data-match="/Active"><span class="oco04"></span>样板参观</a>
    </div>
	
	<div class="right" style="margin-top:15px"><img src="house/statics/index/images/Construction/Phone.png"></div>
	</div>
	</div><!-- phead 结束-->
	<div class="clear"></div>
	</div>

	</div>';

	$s.='<div class="overlay" id="overlay_login">
	<h3>用户登录</h3>
	<div class="overlay_cont">
	<form method="post" class="main_form" action="login.php">
	<table>
	<tr>
	<th width="80">邮箱</th>
	<td><input type="text" class="text" name="username" /></td>
	</tr>
	<tr>
	<th>密码</th>
	<td><input type="password" class="text" name="password" /></td>
	</tr>
	<tr>
	<td></td>
	<td><input type="checkbox" class="checkbox" name="rem" value="1" checked="checked" /><span class="form_tip">记住密码&nbsp;&nbsp;&nbsp;<a href="getpwd.php">忘记密码？</a></span></td>
	</tr>
	<tr>
	<th></th>
	<td><input type="submit" value="登 录" class="submit sub_reg" /><input type="hidden" name="u" value="'.urlencode('http://'.$_SERVER['HTTP_HOST'].$ru).'"/>&nbsp;&nbsp;&nbsp;还没有账号？<a href="reg.php">立即注册</a></td>
	</tr>
	</table>
	</form>
	</div></div>';
	
	return $s;
}

function yjl_html_gz_head($c, $css='', $body_id='', $menu_id=0){
	global $js_c, $js_scrc, $isupimg, $yjl_tpath, $xqid, $xqdb, $page_title, $r_main, $a_fx, $udb, $is_home, $is_nologin, $is_mce, $yjl_isdebug, $d_l1title;
	$ptitle=($page_title!=''?$page_title.' | ':'').$r_main['site_name'];
	if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0)){
		$js_c.='
		jlmsg_v();';
		$js_scrc.='
		jlmsg_p();';
	}
	if($js_scrc!='')$js_c.='
	$(window).scroll(function(){
	'.$js_scrc.'
});';
	$s='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>'.$ptitle.' - 装修行业中代表良心的力量，家装监理，连锁店装修监理，别墅装修监理，别墅监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理</title>
	<meta name="keywords" content="易监理,上海家装监理公司,家装监理公司,上海装修监理公司,家装监理,装潢监理,上海家装监理,上海装潢监理,上海装饰监理,上海装修监理,上海家庭装潢监理,装修监理,上海装修监理,验房,上海验房,家装监理师,装饰监理师,别墅监理,别墅装饰监理,家装工程监理,家庭装修监理,水电监理,家装监理费,家装施工监理,装修第三方监理"/>
	<meta name="description" content="易监理，上海家装监理公司，家装监理公司，上海装修监理公司，家装监理，装潢监理，上海家装监理，上海装潢监理，上海装饰监理，上海装修监理，上海家庭装潢监理，装修监理，上海装修监理，验房，上海验房，家装监理师，装饰监理师，别墅监理，别墅装饰监理，家装工程监理，家庭装修监理，水电监理，家装监理费，家装施工监理，装修第三方监理"/>
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="apple-touch-icon" href="images/iphone_logo.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="images/ipad_logo.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="images/iphone_retina_logo.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="images/ipad_retina_logo.png" />
	<meta property="wb:webmaster" content="4e74fb62f6f0d410" />
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<link href="css/old.css" rel="stylesheet" type="text/css" />'.($css!=''?'
			<link href="css/'.$css.'.css" rel="stylesheet" type="text/css" />':'').'
			<script type="text/javascript" src="scripts/jquery.dookay.min.js"></script>
			<script type="text/javascript" src="scripts/jquery.dookay.plugin.js"></script>
			<script type="text/javascript" src="lib/jquery.rotate.js"></script>
			<script type="text/javascript" src="lib/function.js"></script>
			'.($is_mce>0?'<script type="text/javascript" src="lib/tiny_mce/jquery.tinymce.js"></script>':'').($isupimg>0?'<script type="text/javascript" src="'.$yjl_tpath.'templates/default/js/swfobject.js"></script><script type="text/javascript" src="'.$yjl_tpath.'images/uploadify/jquery.uploadify.v2.1.4.min.js"></script><style type="text/css">@import "'.$yjl_tpath.'images/uploadify/uploadify.css";</style>':'').($js_c!=''?'<script type="text/javascript">
					$(document).ready(function(){
					'.$js_c.'
});
					</script>':'').'<!--[if IE 6]>
					<script type="text/javascript" src="scripts/dookay.png.js" ></script>
					<script type="text/javascript">
					DD_belatedPNG.fix(\' #phead,.hd_ico,.mn_ico,.show \');
					</script>
					<![endif]-->
					</head>
					<body>';
	if($udb['uid']>0 && $udb['iswc']==1 && (($udb['qx']==5 && $udb['iszxjl']==1) || $udb['qx']==10 || $udb['isxg']>0))$s.='<div id="jlmsg_v" style="display: none;"><a href="#" onclick="$(\'#jlmsg_v\').hide();$(\'#jlmsg_isl\').val(\'1\');return false;"><img src="images/list_style02.gif" style="float: right;"></a><span id="msg_cs">0</span>条新咨询'.(($udb['qx']==5 && $udb['iszxjl']==1)?'，<a href="faq-no.html">查看并回答</a>':'').'<input type="hidden" id="jlmsg_isl" value="0"/><input type="hidden" id="jlmsg_v" value="0"/></div>';
	$s.='<div id="wrap">

	<link rel="stylesheet" href="house/statics/index/css/style.css">
	<div class="wrap">
	<div class="pbar">
	<div class="top clearfix">
	<div class="left city_list">
     '.$d_l1title.'&nbsp;<a href="javascript:void(0)" onclick="$(\'#city_list_box\').show();" class="open_clos">[请选择地区]</a>
     <div id="city_list_box" class="city_list_box">
      <div style="position:relative"><em class="cityem_t"></em><em onclick="$(\'#city_list_box\').hide();" class="off_cityList"></em></div>
      <div>进入：<span style="font-size:20px; font-weight:bold"><a href="http://www.yijianli.com/gz.php">上海</a></span></div>
      <h2 class="citytitle">选择您的对应地区</h2>
      <div class="city_content">
       <a href="http://www.yijianli.com/gz.php" class="am_addstyle">上海</a><a href="http://sz.yijianli.com/gz.php">苏州</a><a href="http://nj.yijianli.com/gz.php">南京</a><a href="http://wx.yijianli.com/gz.php">无锡</a><a href="http://hz.yijianli.com/gz.php">杭州</a><a href="http://nb.yijianli.com/gz.php">宁波</a><a href="http://sx.yijianli.com/gz.php">绍兴</a>
      </div>
     </div>
    </div>
	<div class="left">
	<ul>
	<li><span><img src="house/statics/index/images/Construction/top1.fw.png" class="hmiddle"></span><span class="hmiddle">100%服务品质</span></li>
	<li><span><img src="house/statics/index/images/Construction/top2.fw.png" class="hmiddle"></span><span class="hmiddle">A级信誉</span></li>
	<li><span><img src="house/statics/index/images/Construction/top3.fw.png" class="hmiddle"></span><span class="hmiddle">提升20%质量</span></li>
	<li><span><img src="house/statics/index/images/Construction/top4.fw.png" class="hmiddle"></span><span class="hmiddle">100%省钱</span></li>
	</ul>
	</div>';

	if($udb['uid']>0){
		$iswc=(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0)?0:1;
		$s.='<div class="right login">'
		.($iswc>0 ? '<a href="msg.php"><span class="hd_ico ico05" style="vertical-align: bottom;">&nbsp;</span>'.($udb['newpm']>0?'<small style="color: #f00;">'.($udb['newpm']>9?'9+':$udb['newpm']).'</small>':'').'</a>':'').'
		<a href="user-'.$udb['uid'].'.html">'.($udb['nc']!=''?$udb['nc']:$udb['email']).'</a>
		<a href="profile.php">设置</a>
		<a href="user_decoration.php?id='.$udb['uid'].'">个人中心</a>
		'.(($udb['qx']==10 || $udb['isxg']>0)?'<a href="admin.php">后台管理</a>':'').'
		<a href="logout.php?referer='.$_SERVER['HTTP_REFERER'].'">退出登录</a>
		</div>';
	}else{
		$s .= '<div class="right login">已有账号？&nbsp;<a href="login.php?referer='.$_SERVER['HTTP_REFERER'].'" id="link_login" rel="#overlay_login">登陆</a>&nbsp;&nbsp;&nbsp;&nbsp;<span class="orange"><a href="reg.php?referer='.$_SERVER['HTTP_REFERER'].'">免费注册</a></span>&nbsp;&nbsp;<a href="login.php?t=sina" title="新浪微博登录"><span class="mn_ico ico11"></span></a>&nbsp;<a href="login.php?t=tqq" title="QQ登录"><span class="mn_ico ico12"></span></a></div>';
	}

	$s .='</div>
	</div><!-- pbar结束 -->
	<div class="phead">
	<div class="head clearfix">
	<div class="logo left">装修从此容易！</div>

	<div class="menu left" id="main_menu">
	<a href="gz.php" data-match="gz.php$"><span class="oco01"></span>首页</a>
	<a href="new/gz.php" data-match="/new/gz.php"><span class="oco02"></span>选监理</a>
	<a href="photo_gz-0.html" data-config="default"><span class="oco03"></span>监理项目</a>
	</div>

	<div class="right" style="margin-top:15px"><img src="house/statics/index/images/Construction/Phone.png"></div>
	</div>
	</div><!-- phead 结束-->
	<div class="clear"></div>
	</div>

	</div>';

	$s.='<div class="overlay" id="overlay_login">
	<h3>用户登录</h3>
	<div class="overlay_cont">
	<form method="post" class="main_form" action="login.php">
	<table>
	<tr>
	<th width="80">邮箱</th>
	<td><input type="text" class="text" name="username" /></td>
	</tr>
	<tr>
	<th>密码</th>
	<td><input type="password" class="text" name="password" /></td>
	</tr>
	<tr>
	<td></td>
	<td><input type="checkbox" class="checkbox" name="rem" value="1" checked="checked" /><span class="form_tip">记住密码&nbsp;&nbsp;&nbsp;<a href="getpwd.php">忘记密码？</a></span></td>
	</tr>
	<tr>
	<th></th>
	<td><input type="submit" value="登 录" class="submit sub_reg" /><input type="hidden" name="u" value="'.urlencode('http://'.$_SERVER['HTTP_HOST'].$ru).'"/>&nbsp;&nbsp;&nbsp;还没有账号？<a href="reg.php">立即注册</a></td>
	</tr>
	</table>
	</form>
	</div></div>';

	return $s;
}


function yjl_html($c, $css='', $body_id='', $menu_id=0){
	global $js_c, $js_scrc, $isupimg, $yjl_tpath, $xqid, $xqdb, $page_title, $r_main, $a_fx, $udb, $is_home, $is_nologin, $is_mce, $yjl_isdebug, $d_l1title;
	if ($_COOKIE['isgz']) {
		$s = yjl_html_gz_head($c, $css, $body_id, $menu_id);
	} else {
		$s = yjl_html_head($c, $css, $body_id, $menu_id);
	}
	$s .= '<!--内容-->
	<div id="pbody'.($body_id!=''?'_'.$body_id:'').'">'.$c.'</div>
</div>
<!--底部-->
<div id="pfoot" style="clear: both;">
	<div class="left">
		<address>'.$r_main['site_name'].'<a href="http://www.miitbeian.gov.cn/" target="_blank">沪ICP备12034482号</a> <a href="/about-yijianli.html">关于易监理</a> |  <a href="/about-service.html">服务流程</a> |  <a href="/about-pay.html">收费标准</a> | <a href="/about-paypal.html">支付宝支付</a> | <a href="/about-contact.html">联系我们</a> | <a href="/new/index.php?s=/supervisor/consult/type/recruit">诚聘精英</a></address>
	</div>
	<div class="right">Copyright &copy; '.date('Y').' '.$r_main['site_name'].'</div><br /><a href="http://www.sgs.gov.cn/lz/licenseLink.do?method=licenceView&entyId=20130520141953244"><img src="icon.gif" border=0></a>
</div><div id="fx_div" style="display: none;" onmouseover="$(this).show();if($(\'#fx_id\').val()>0)$(\'#plike_v_\'+$(\'#fx_id\').val()).show();" onmouseout="$(this).hide();if($(\'#fx_id\').val()>0)$(\'#plike_v_\'+$(\'#fx_id\').val()).hide();"><div class="fx_title">分享到</div><div class="fx_list"><div class="clear"></div>';
	foreach($a_fx as $k=>$v)$s.='<a title="分享到'.$v[0].'" href="#" onclick="fx_link(\''.$v[1].'\');return false;" class="fx_link" style="background-position: 3px -'.(37+($k+1)*40).'px;">'.$v[0].'</a>';
	$ru=$_SERVER['REQUEST_URI'];
	if(isset($_SERVER['HTTP_X_REWRITE_URL']) && trim($_SERVER['HTTP_X_REWRITE_URL'])!='')$ru=$_SERVER['HTTP_X_REWRITE_URL'];
	$s.='<input type="hidden" id="fx_url_0" value="'.urlencode('http://'.$_SERVER['HTTP_HOST'].$ru).'"/><input type="hidden" id="fx_title_0" value="'.urlencode($ptitle).'"/><input type="hidden" id="fx_title0_0" value="'.urlencode(@iconv('UTF-8', 'GB2312', $ptitle)).'"/><input type="hidden" id="fx_id" value="0"/><div class="clear"></div></div></div>';
	
	if($yjl_isdebug==0)$s.='<div style="display: none;"><script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src=\'" + _bdhmProtocol + "hm.baidu.com/h.js%3Fd67b450510aefabf934d8fbd93d7aa94\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<noscript><a href="http://www.51.la/?15211264" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/15211264.asp" style="border:none" /></a></noscript></div>';
	$s .= '<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src=\'" + _bdhmProtocol + "hm.baidu.com/h.js%3F1823814145addf5267230aa05878c1b6\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>';
	$s.='</body></html>';
	return $s;
}

function yjl_html_gz($c, $css='', $body_id='', $menu_id=0){
	global $js_c, $js_scrc, $isupimg, $yjl_tpath, $xqid, $xqdb, $page_title, $r_main, $a_fx, $udb, $is_home, $is_nologin, $is_mce, $yjl_isdebug, $d_l1title;
	$s = yjl_html_gz_head($c, $css, $body_id, $menu_id);
	$s .= '<!--内容-->
	<div id="pbody'.($body_id!=''?'_'.$body_id:'').'">'.$c.'</div>
	</div>
	<!--底部-->
	<div id="pfoot" style="clear: both;">
	<div class="left">
	<address>'.$r_main['site_name'].'<a href="http://www.miitbeian.gov.cn/" target="_blank">沪ICP备12034482号</a> <a href="/about-yijianli.html">关于易监理</a> |  <a href="/about-service.html">服务流程</a> |  <a href="/about-pay.html">收费标准</a> | <a href="/about-paypal.html">支付宝支付</a> | <a href="/about-contact.html">联系我们</a> | <a href="/new/index.php?s=/supervisor/consult/type/recruit">诚聘精英</a></address>
	</div>
	<div class="right">Copyright &copy; '.date('Y').' '.$r_main['site_name'].'</div><br /><a href="http://www.sgs.gov.cn/lz/licenseLink.do?method=licenceView&entyId=20130520141953244"><img src="icon.gif" border=0></a>
	</div><div id="fx_div" style="display: none;" onmouseover="$(this).show();if($(\'#fx_id\').val()>0)$(\'#plike_v_\'+$(\'#fx_id\').val()).show();" onmouseout="$(this).hide();if($(\'#fx_id\').val()>0)$(\'#plike_v_\'+$(\'#fx_id\').val()).hide();"><div class="fx_title">分享到</div><div class="fx_list"><div class="clear"></div>';
	foreach($a_fx as $k=>$v)$s.='<a title="分享到'.$v[0].'" href="#" onclick="fx_link(\''.$v[1].'\');return false;" class="fx_link" style="background-position: 3px -'.(37+($k+1)*40).'px;">'.$v[0].'</a>';
	$ru=$_SERVER['REQUEST_URI'];
	if(isset($_SERVER['HTTP_X_REWRITE_URL']) && trim($_SERVER['HTTP_X_REWRITE_URL'])!='')$ru=$_SERVER['HTTP_X_REWRITE_URL'];
	$s.='<input type="hidden" id="fx_url_0" value="'.urlencode('http://'.$_SERVER['HTTP_HOST'].$ru).'"/><input type="hidden" id="fx_title_0" value="'.urlencode($ptitle).'"/><input type="hidden" id="fx_title0_0" value="'.urlencode(@iconv('UTF-8', 'GB2312', $ptitle)).'"/><input type="hidden" id="fx_id" value="0"/><div class="clear"></div></div></div>';

	if($yjl_isdebug==0)$s.='<div style="display: none;"><script type="text/javascript">
	var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
	document.write(unescape("%3Cscript src=\'" + _bdhmProtocol + "hm.baidu.com/h.js%3Fd67b450510aefabf934d8fbd93d7aa94\' type=\'text/javascript\'%3E%3C/script%3E"));
	</script>
	<noscript><a href="http://www.51.la/?15211264" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/15211264.asp" style="border:none" /></a></noscript></div>';
	$s .= '<script type="text/javascript">
	var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
	document.write(unescape("%3Cscript src=\'" + _bdhmProtocol + "hm.baidu.com/h.js%3F1823814145addf5267230aa05878c1b6\' type=\'text/javascript\'%3E%3C/script%3E"));
	</script>';
	$s.='</body></html>';
	return $s;
}

function yjl_getpage($p, $t, $m=4){
	global $_GET;
	$qs='';
	if(count($_GET)>0){
		foreach($_GET as $k=>$v){
			if($k!='p')$qs.='&amp;'.$k.'='.$v;
		}
	}
	$c=($p!=1?'<a href="?p=1'.$qs.'">首页</a>':'首页').' |';
	if($t>($m*2+1)){
		if($p>1)$c.=' <a href="?p=1'.$qs.'">1</a>';
		if($p>($m+2))$c.=' ...';
		if($p>2){
			for($i=0;$i<$m;$i++){
				$j=$p-$m+$i;
				if($j>1)$c.=' <a href="?p='.$j.$qs.'">'.$j.'</a>';
			}
		}
		$c.=' ['.$p.']';
		if($p<($t-1)){
			for($i=0;$i<$m;$i++){
				$j=$p+$i+1;
				if($j<$t)$c.=' <a href="?p='.$j.$qs.'">'.$j.'</a>';
			}
		}
		if($p<($t-$m-1))$c.=' ...';
		if($p!=$t)$c.=' <a href="?p='.$t.$qs.'">'.$t.'</a>';
	}else{
		for($i=0;$i<$t;$i++)$c.=' '.($p!=($i+1)?'<a href="?p='.($i+1).$qs.'">':'[').($i+1).($p!=($i+1)?'</a>':']');
	}
	return $c.' | '.($p!=$t?'<a href="?p='.$t.$qs.'">尾页</a>':'尾页');
}

function yjl_newpage($p, $t, $pf='', $css=0, $m=3){
	global $_GET;
	$qs='';
	if(count($_GET)>0){
		foreach($_GET as $k=>$v){
			if($k!='p')$qs.='&amp;'.$k.'='.$v;
		}
	}
	if($pf!='')$qs.='#'.$pf;
	$c='<div class="pager'.($css>0?' marg':'').' clearfix" style="clear: both;"><div class="info">共 '.$t.' 页 页次:'.$p.'/'.$t.' 页</div><ul>';
	$c.=$p!=1?'<li class="prev"><a href="?p=1'.$qs.'">首页</a></li>':'';
	if($t>($m*2+1)){
		if($p>1)$c.='<li><a href="?p=1'.$qs.'">1</a></li>';
		if($p>($m+2))$c.='<li><span>…</span></li>';
		if($p>2){
			for($i=0;$i<$m;$i++){
				$j=$p-$m+$i;
				if($j>1)$c.='<li><a href="?p='.$j.$qs.'">'.$j.'</a></li>';
			}
		}
		$c.='<li class="current"><a href="?p='.$p.$qs.'">'.$p.'</a></li>';
		if($p<($t-1)){
			for($i=0;$i<$m;$i++){
				$j=$p+$i+1;
				if($j<$t)$c.='<li><a href="?p='.$j.$qs.'">'.$j.'</a></li>';
			}
		}
		if($p<($t-$m-1))$c.='<li><span>…</span></li>';
		if($p!=$t)$c.='<li><a href="?p='.$t.$qs.'">'.$t.'</a></li>';
	}else{
		for($i=0;$i<$t;$i++)$c.='<li'.($p!=($i+1)?'':' class="current"').'><a href="?p='.($i+1).$qs.'">'.($i+1).'</a></li>';
	}
	$c.=$p!=$t?'<li class="next"><a href="?p='.$t.$qs.'">尾页</a></li>':'';
	$c.='</ul></div>';
	return $c;
}

function yjl_newhmpage($f, $p, $t, $pf='', $css=0, $m=3){
	global $_GET;
	$qs='';
	if($pf!='')$qs='#'.$pf;
	$c='<div class="pager'.($css>0?' marg':'').' clearfix" style="clear: both;"><div class="info">共 '.$t.' 页 页次:'.$p.'/'.$t.' 页</div><ul>';
	$c.=$p!=1?'<li class="prev"><a href="'.str_replace('[p]', '1', $f).$qs.'">首页</a></li>':'';
	if($t>($m*2+1)){
		if($p>1)$c.='<li><a href="'.str_replace('[p]', '1', $f).$qs.'">1</a></li>';
		if($p>($m+2))$c.='<li><span>…</span></li>';
		if($p>2){
			for($i=0;$i<$m;$i++){
				$j=$p-$m+$i;
				if($j>1)$c.='<li><a href="'.str_replace('[p]', $j, $f).$qs.'">'.$j.'</a></li>';
			}
		}
		$c.='<li class="current"><a href="'.str_replace('[p]', $p, $f).$qs.'">'.$p.'</a></li>';
		if($p<($t-1)){
			for($i=0;$i<$m;$i++){
				$j=$p+$i+1;
				if($j<$t)$c.='<li><a href="'.str_replace('[p]', $j, $f).$qs.'">'.$j.'</a></li>';
			}
		}
		if($p<($t-$m-1))$c.='<li><span>…</span></li>';
		if($p!=$t)$c.='<li><a href="'.str_replace('[p]', $t, $f).$qs.'">'.$t.'</a></li>';
	}else{
		for($i=0;$i<$t;$i++)$c.='<li'.($p!=($i+1)?'':' class="current"').'><a href="'.str_replace('[p]', ($i+1), $f).$qs.'">'.($i+1).'</a></li>';
	}
	$c.=$p!=$t?'<li class="next"><a href="'.str_replace('[p]', $t, $f).$qs.'">尾页</a></li>':'';
	$c.='</ul></div>';
	return $c;
}

function yjl_adduser($n, $p, $ip, $email='', $isjl=0){
	global $yjl_dbprefix, $dbprefix, $r_main, $yjl_url, $yjl_isdebug;
	$uid=0;
	if($r_main['site_email_verify']==0 || $email!='' || $isjl>0){
		$pwd=md5($p);
		$ic=substr(md5(time().rand(1,1000)), 0, 16);
		$role_id=($r_main['site_email_verify']>0 && $isjl==0)?5:3;
		$iSQL=sprintf('insert into %s (username, nickname, password, email, regip, regdate, role_id, invitecode) values (%s, %s, %s, %s, %s, %s, %s, %s)', $dbprefix.'members',
			yjl_SQLString($n, 'text'),
			yjl_SQLString($n, 'text'),
			yjl_SQLString($pwd, 'text'),
			yjl_SQLString($email, 'text'),
			yjl_SQLString($ip, 'text'),
			time(),
			$role_id,
			yjl_SQLString($ic, 'text'));
		$result=mysql_query($iSQL) or die(mysql_error());
		$uid=mysql_insert_id();
		$uSQL=sprintf('update %s set username=uid where uid=%s', $dbprefix.'members', $uid);
		$result=mysql_query($uSQL) or die(mysql_error());
		$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'members', $uid);
		$result=mysql_query($iSQL) or die(mysql_error());
		if($r_main['site_email_verify']>0 && $isjl==0){
			$role_id=3;
			$key=substr(md5(md5($uid.$email.$role_id).md5(uniqid(mt_rand(),true))),3,16);
			$iSQL=sprintf('insert into %s (uid, email, role_id, `key`, status, verify_time, regdate, type) values (%s, %s, %s, %s, 0, 0, %s, %s)', $dbprefix.'member_validate',
				$uid,
				yjl_SQLString($email, 'text'),
				$role_id,
				yjl_SQLString($key, 'text'),
				time(),
				yjl_SQLString('email', 'text'));
			$result=mysql_query($iSQL) or die(mysql_error());
			if($yjl_isdebug==0)yjl_vmail($uid, $email, $key);
		}
		$iSQL=sprintf('insert into %s (uid) values (%s)', $dbprefix.'memberfields', $uid);
		$result=mysql_query($iSQL) or die(mysql_error());
	}
	return $uid;
}

function yjl_addwbuser($n, $p, $ip){
	global $yjl_dbprefix, $dbprefix, $r_main, $yjl_url;
	$uid=0;
	$pwd=md5($p);
	$ic=substr(md5(time().rand(1,1000)), 0, 16);
	$iSQL=sprintf('insert into %s (username, nickname, password, regip, regdate, role_id, invitecode) values (%s, %s, %s, %s, %s, 3, %s)', $dbprefix.'members',
		yjl_SQLString($n, 'text'),
		yjl_SQLString($n, 'text'),
		yjl_SQLString($pwd, 'text'),
		yjl_SQLString($ip, 'text'),
		time(),
		yjl_SQLString($ic, 'text'));
	$result=mysql_query($iSQL) or die(mysql_error());
	$uid=mysql_insert_id();
	$uSQL=sprintf('update %s set username=uid where uid=%s', $dbprefix.'members', $uid);
	$result=mysql_query($uSQL) or die(mysql_error());
	$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'members', $uid);
	$result=mysql_query($iSQL) or die(mysql_error());
	$iSQL=sprintf('insert into %s (uid) values (%s)', $dbprefix.'memberfields', $uid);
	$result=mysql_query($iSQL) or die(mysql_error());
	return $uid;
}

function yjl_vmail($uid, $email, $key){
	global $r_main, $yjl_url;
	$ec="您好：
您收到这封邮件，是因为在“".$r_main['site_name']."”网站的用户注册中使用了该邮箱地址。

如果您没有进行上述操作，请忽略这封邮件。您不需要退订或进行其他进一步的操作。
------------------------------------------------------
帐号激活说明：
为避免垃圾邮件或您的邮箱地址被滥用，我们需要对您的地址有效性进行验证。
您只需点击下面的链接即可激活您的帐号，并享有真正会员权限：
".$yjl_url."verify.php?uid=".$uid."&key=".$key."

(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)

感谢您的访问，祝您使用愉快！

此致，
".$r_main['site_name']." 管理团队.
".$yjl_url."
";
	if($email!='')yjl_mail($email, $r_main['site_name'].'邮箱地址验证', $ec);
}

function yjl_mail($email, $title, $msg){
	global $r_main;
	if($r_main['smtp_isa']>0){
		$smtp=new smtp($r_main['smtp_server'], $r_main['smtp_port'], true, $r_main['smtp_user'], $r_main['smtp_pwd']);
	}else{
		$smtp=new smtp($r_main['smtp_server'], $r_main['smtp_port'], false);
	}
	$smtp->debug=false;
	$smtp->sendmail($email, $r_main['smtp_email'], $title, $msg, 'TXT');
}

function yjl_follow($u, $i){
	global $dbprefix;
	$q_res=sprintf('select uid from %s where uid=%s and buddyid=%s limit 1', $dbprefix.'buddys', $i, $u);
	$res=mysql_query($q_res) or die(mysql_error());
	if(mysql_num_rows($res)==0){
		yjl_addlog('[uid]关注了[luid]', md5('gz|'.$u.'|'.$i), 0, $u, $i);
		$iSQL=sprintf('insert into %s (uid, buddyid, dateline, buddy_lastuptime) values (%s, %s, %s, %s)', $dbprefix.'buddys',
			$i,
			$u,
			time(),
			time());
		$result=mysql_query($iSQL) or die(mysql_error());
		$uSQL=sprintf('update %s set follow_count=follow_count+1 where uid=%s', $dbprefix.'members', $i);
		$result=mysql_query($uSQL) or die(mysql_error());
		$uSQL=sprintf('update %s set fans_count=fans_count+1 where uid=%s', $dbprefix.'members', $u);
		$result=mysql_query($uSQL) or die(mysql_error());
	}
	mysql_free_result($res);
}

function yjl_addurl($u){
	global $dbprefix;
	$hash=md5($u);
	$q_res=sprintf('select `key` from %s where url_hash=%s limit 1', $dbprefix.'url', yjl_SQLString($hash, 'text'));
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$k=$r_res['key'];
	}else{
		$sid=0;
		$a=parse_url($u);
		if(isset($a['host']) && trim($a['host'])!=''){
			$q_rep=sprintf('select id from %s where host=%s limit 1', $dbprefix.'site', yjl_SQLString(trim($a['host']), 'text'));
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$sid=$r_rep['id'];
				$uSQL=sprintf('update %s set url_count=url_count+1 where id=%s', $dbprefix.'site', $r_rep['id']);
				$repult=mysql_query($uSQL) or die(mysql_error());
			}else{
				$iSQL=sprintf('insert into %s (host, dateline, url_count) values (%s, %s, 1)', $dbprefix.'site',
					yjl_SQLString(trim($a['host']), 'text'),
					time());
				$repult=mysql_query($iSQL) or die(mysql_error());
				$sid=mysql_insert_id();
			}
			mysql_free_repult($rep);
		}
		$iSQL=sprintf('insert into %s (`key`, url, dateline, url_hash, siteid) values (%s, %s, %s, %s, %s)', $dbprefix.'url',
			yjl_SQLString($hash, 'text'),
			yjl_SQLString($u, 'text'),
			time(),
			yjl_SQLString($hash, 'text'),
			$sid);
		$result=mysql_query($iSQL) or die(mysql_error());
		$id=mysql_insert_id();
		$k=yjl_urlkey($id);
		$uSQL=sprintf('update %s set `key`=%s where id=%s', $dbprefix.'url', yjl_SQLString($k, 'text'), $id);
		$result=mysql_query($uSQL) or die(mysql_error());
	}
	mysql_free_result($res);
	return $k;
}

function yjl_wbdecode($c){
	global $config, $yjl_tpath, $yjl_url, $surl_p;
	$c=preg_replace('~<U ([0-9a-zA-Z]+)>(.+?)</U>~', '<a title="\\2" href="'.$surl_p.'\\1'.'" target="_blank">'.$yjl_url.$surl_p.'\\1'.'</a>', $c);
	$c=preg_replace('~<T>#(.+?)#</T>~e', '"<b title=\"".urlencode(strip_tags("\\1"))'.'."\">#\\1#</b>"', $c);
	$c=preg_replace('~<M ([^>]+?)>\@(.+?)</M>~e', '"".yjl_getuurl("\\1")'.'.""', $c);
	if(isset($config['face'])){
		foreach($config['face'] as $k=>$v)$c=str_replace('['.$k.']', '<img src="'.$yjl_tpath.$v.'" alt=""/>', $c);
	}
	return $c;
}

function yjl_getuurl($n){
	global $dbprefix, $yjl_dbprefix;
	$q_res=sprintf('select b.uid, b.nc from %s as a, %s as b where a.username=%s and a.uid=b.uid limit 1', $dbprefix.'members', $yjl_dbprefix.'members', yjl_SQLString($n, 'text'));
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c='<a href="user-'.$r_res['uid'].'.html">@'.$r_res['nc'].'</a>';
	}else{
		$c='易监理';
	}
	mysql_free_result($res);
	return $c;
}

function yjl_app($n, $i, $u, $f='jl', $d=''){
	global $dbprefix;
	$q_res=sprintf('select uid, username from %s where role_id=2 limit 1', $dbprefix.'members');
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0)$admin=$r_res;
	mysql_free_result($res);
	$app_s=md5(time().'-'.rand(1,1000).'-'.$f.'-'.$i);
	$app_k=md5($app_s);
	if($d=='')$d=$n;
	$iSQL=sprintf('insert into %s (uid, username, app_name, source_url, show_from, app_desc, app_key, app_secret, status, create_time) values (%s, %s, %s, %s, 1, %s, %s, %s, 1, %s)', $dbprefix.'app',
		$admin['uid'],
		yjl_SQLString($admin['username'], 'text'),
		yjl_SQLString($n, 'text'),
		yjl_SQLString($u, 'text'),
		yjl_SQLString($d, 'text'),
		yjl_SQLString($app_k, 'text'),
		yjl_SQLString($app_s, 'text'),
		time());
	$result=mysql_query($iSQL) or die(mysql_error());
	$app_id=mysql_insert_id();
	return array($app_id, $app_k, $app_s);
}

function yjl_getdqse($a, $l=1){
	global $dbprefix;
	$id=(isset($a[($l-1)]) && $a[($l-1)]>0)?$a[($l-1)]:0;
	$c='';
	$cid=0;
	$q_res=sprintf('select id, name from %s where level=%s and upid=%s order by list', $dbprefix.'common_district', $l, $id);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<span id="dqse_'.$l.'"><select name="l'.$l.'"><option value="0">----</option>';
		do{
			if(isset($a[$l]) && $a[$l]==$r_res['id'])$cid=$r_res['id'];
			$c.='<option value="'.$r_res['id'].'"'.($cid==$r_res['id']?' selected="selected"':'').'>'.$r_res['name'].'</option>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</select>';
		if($cid>0){
			$l++;
			$c.=yjl_getdqse($a, $l);
		}
		$c.='</span>';
	}
	mysql_free_result($res);
	return $c;
}

function yjl_uwb($u, $c, $i, $e='', $p=''){
	global $r_main, $yjl_tpath, $dbprefix, $yjl_url, $yjl_isdebug;
	if($yjl_isdebug==0){
		if(trim($r_main['sina_k'])!='' && trim($r_main['sina_s'])!=''){
			$q_rex=sprintf('select profile, access_token from %s where uid=%s and length(access_token)>0 limit 1', $dbprefix.'xwb_bind_info', $u);
			$rex=mysql_query($q_rex) or die(mysql_error());
			$r_rex=mysql_fetch_assoc($rex);
			if(mysql_num_rows($rex)>0){
				$a_xwb=json_decode($r_rex['profile'], true);
				if(isset($a_xwb['bind_setting']) && $a_xwb['bind_setting']==1){
					require_once($e.'lib/saetv2.ex.class.php');
					$so=new SaeTClientV2($r_main['sina_k'], $r_main['sina_s'], $r_rex['access_token']);
					if($p!=''){
						$rs=$so->upload($c, $e.$p);
					}else{
						$rs=$so->update($c);
					}
					if(isset($rs['mid']) && $rs['mid']!=''){
						$iSQL=sprintf('insert into %s (tid, mid) values (%s, %s)', $dbprefix.'xwb_bind_topic',
							$i,
							$rs['mid']);
						$result=mysql_query($iSQL) or die(mysql_error());
					}
				}
			}
			mysql_free_result($rex);
		}
		if(trim($r_main['tqq_k'])!='' && trim($r_main['tqq_s'])!=''){
			$q_rex=sprintf('select token, tsecret from %s where uid=%s and length(token)>0 and length(tsecret)>0 and synctoqq>0 limit 1', $dbprefix.'qqwb_bind_info', $u);
			$rex=mysql_query($q_rex) or die(mysql_error());
			$r_rex=mysql_fetch_assoc($rex);
			if(mysql_num_rows($rex)>0){
				require_once($e.'lib/tqq_opent.php');
				require_once($e.'lib/tqq_client.php');
				$tqq=new MBApiClient($r_main['tqq_k'], $r_main['tqq_s'], $r_rex['token'], $r_rex['tsecret']);
				$tqq_a=array('c'=>$c, 'ip'=>$_SERVER['REMOTE_ADDR'], 'j'=>'', 'w'=>'', 'type'=>1);
				if($p!=''){
					$ps=getimagesize($p);
					$p_data=file_get_contents($p);
					$tqq_a['p']=array($ps['mime'], $p, $p_data);
				}
				$rq=$tqq->postOne($tqq_a);
				if(isset($rq['data']['id']) && $rq['data']['id']!=''){
					$iSQL=sprintf('insert into %s (tid, qqwb_id) values (%s, %s)', $dbprefix.'qqwb_bind_topic',
						$i,
						$rq['data']['id']);
					$result=mysql_query($iSQL) or die(mysql_error());
				}
			}
			mysql_free_result($rex);
		}
	}
}

function yjl_emailurl($email=''){
	global $a_eu;
	$url='#';
	$a=explode('@', strtolower($email));
	if(isset($a[1]) && isset($a_eu[$a[1]]))$url='http://'.$a_eu[$a[1]];
	return $url;
}

function yjl_face($u, $f, $t=0){
	global $yjl_tpath;
	$face='images/no_'.($t>0?'128':'50').'.gif';
	if($f!=''){
		$pa=yjl_imgpath($u);
		$face='images/face/'.$pa[1].$u;
		if($f=='./images/face/'.$pa[1].$u.'_s.jpg')$face=$yjl_tpath.'images/face/'.$pa[1].$u.'_'.($t>0?'b':'s').'.jpg';
	}
	return $face;
}

function yjl_newwb($r_res, $ispl=0, $pcid=0, $fjc=''){
	global $yjl_tpath, $dbprefix, $yjl_dbprefix, $uadb, $user_id, $udb, $isplink;
	$c='<li id="wb_'.$r_res['tid'].'"'.$fjc.'>
							<div class="left">
								<a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $uadb[$r_res['uid']]['face']).'" /></a>'.($uadb[$r_res['uid']]['qx']==5?'<div class="m_sp1">监理师</div>':'').'
							</div>
							<div class="right">
								<h3><a href="user-'.$r_res['uid'].'.html">'.$uadb[$r_res['uid']]['nc'].'</a>:';
	if($r_res['longtextid']>0){
		$q_reu=sprintf('select `longtext` from %s where id=%s and tid=%s limit 1', $dbprefix.'topic_longtext', $r_res['longtextid'], $r_res['tid']);
		$reu=mysql_query($q_reu) or die(mysql_error());
		$r_reu=mysql_fetch_assoc($reu);
		if(mysql_num_rows($reu)>0){
			if($isplink>0){
				$c.=$r_reu['longtext'];
			}else{
				$c.=yjl_wbdecode($r_res['content2']).' <a href="mblog-'.$r_res['uid'].'-'.$r_res['tid'].'.html">查看全文</a>';
			}
		}else{
			$r_res['longtextid']=0;
		}
		mysql_free_result($reu);
	}
	if($r_res['longtextid']==0)$c.=yjl_wbdecode($r_res['content']);
	$c.='</h3>';
	if($r_res['imageid']!=''){
		$ai=explode(',', $r_res['imageid']);
		foreach($ai as $v){
			$q_reu=sprintf('select photo, width, height from %s where id=%s limit 1', $dbprefix.'topic_image', $v);
			$reu=mysql_query($q_reu) or die(mysql_error());
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0){
				$ou=str_replace('./', '', $r_reu['photo']);
				$bu=str_replace('_o.jpg', '_s.jpg', $ou);
				$img_a[$r_res['tid']][$v]='<a href="#" onclick="openimg(\''.$yjl_tpath.$ou.'\', '.$r_reu['width'].', '.$r_reu['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.$bu.'" alt=""/></a>';
			}
			mysql_free_result($reu);
		}
	}
	if($r_res['videoid']>0){
		$q_reu=sprintf('select video_url, video_img from %s where id=%s limit 1', $dbprefix.'topic_video', $r_res['videoid']);
		$reu=mysql_query($q_reu) or die(mysql_error());
		$r_reu=mysql_fetch_assoc($reu);
		if(mysql_num_rows($reu)>0)$img_a[$r_res['tid']]['v'.$r_res['videoid']]='<a href="'.$r_reu['video_url'].'" target="_blank" title="点击查看视频"><img src="images/blank.gif" style="background: #fff url('.($r_reu['video_img']!=''?$yjl_tpath.str_replace('./', '', $r_reu['video_img']):'images/vi_d.jpg').') no-repeat center;" alt=""/></a>';
		mysql_free_result($reu);
	}
	if(isset($img_a[$r_res['tid']]))$c.=join(' ', $img_a[$r_res['tid']]);
	if(($r_res['type']=='forward' || $r_res['type']=='both') && $r_res['totid']>0 && $r_res['totid']!=$pcid){
		$q_rez=sprintf('select a.*, b.nc from %s as a, %s as b where a.tid=%s and a.uid=b.uid limit 1', $dbprefix.'topic', $yjl_dbprefix.'members', $r_res['totid']);
		$rez=mysql_query($q_rez) or die(mysql_error());
		$r_rez=mysql_fetch_assoc($rez);
		if(mysql_num_rows($rez)>0){
			$c.='<div class="active_bg"></div><div class="active clearfix"><h3><a href="user-'.$r_rez['uid'].'.html">@ '.$r_rez['nc'].'</a>：'.yjl_wbdecode($r_rez['content']).'</h3>';
			if($r_rez['imageid']!=''){
				$ai=explode(',', $r_rez['imageid']);
				foreach($ai as $v){
					$q_reu=sprintf('select photo, width, height from %s where id=%s limit 1', $dbprefix.'topic_image', $v);
					$reu=mysql_query($q_reu) or die(mysql_error());
					$r_reu=mysql_fetch_assoc($reu);
					if(mysql_num_rows($reu)>0){
						$ou=str_replace('./', '', $r_reu['photo']);
						$bu=str_replace('_o.jpg', '_s.jpg', $ou);
						$img_a[$r_res['tid'].'|'.$r_rez['tid']][$v]='<a href="#" onclick="openimg(\''.$yjl_tpath.$ou.'\', '.$r_reu['width'].', '.$r_reu['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.$bu.'" alt=""/></a>';
					}
					mysql_free_result($reu);
				}
			}
			if($r_rez['videoid']>0){
				$q_reu=sprintf('select video_url, video_img from %s where id=%s limit 1', $dbprefix.'topic_video', $r_rez['videoid']);
				$reu=mysql_query($q_reu) or die(mysql_error());
				$r_reu=mysql_fetch_assoc($reu);
				if(mysql_num_rows($reu)>0)$img_a[$r_res['tid'].'|'.$r_rez['tid']]['v'.$r_res['videoid']]='<a href="'.$r_reu['video_url'].'" target="_blank" title="点击查看视频"><img src="images/blank.gif" style="background: #fff url('.($r_reu['video_img']!=''?$yjl_tpath.str_replace('./', '', $r_reu['video_img']):'images/vi_d.jpg').') no-repeat center;" alt=""/></a>';
				mysql_free_result($reu);
			}
			if(isset($img_a[$r_res['tid'].'|'.$r_rez['tid']]))$c.=join(' ', $img_a[$r_res['tid'].'|'.$r_rez['tid']]);
			$c.='<p class="other"><span><a href="mblog-'.$r_rez['uid'].'-'.$r_rez['tid'].'.html">原文转发('.$r_rez['forwards'].')</a>|<a href="mblog-'.$r_rez['uid'].'-'.$r_rez['tid'].'.html">原文评论('.$r_rez['replys'].')</a></span><a href="mblog-'.$r_rez['uid'].'-'.$r_rez['tid'].'.html">'.yjl_wbdate($r_rez['dateline']).'</a></p></div>';
		}
		mysql_free_result($rez);
	}
	if($user_id>0){
		$a[]='<a href="#" onclick="show_forward(\''.$r_res['tid'].'\''.($isplink>0?', 1':'').');return false;" id="zfcm_'.$r_res['tid'].'">转发'.($r_res['forwards']>0?'('.$r_res['forwards'].')':'').'</a>';
	}else{
		$a[]='转发('.$r_res['forwards'].')';
	}
	if(($user_id>0 && $ispl==0) || $r_res['replys']>0){
		$a[]='<a href="#" onclick="show_reply(\''.$r_res['tid'].'\', '.(($user_id>0 && $ispl==0)?'1':'0').''.($isplink>0?', 1':'').');return false;" id="plcm_'.$r_res['tid'].'">评论'.($r_res['replys']>0?'('.$r_res['replys'].')':'').'</a>';
	}else{
		$a[]='评论';
	}
	if($user_id>0 && ($udb['qx']==10 || $udb['isxg']>0 || $user_id==$r_res['uid']))$a[]='<a href="#" onclick="if(confirm(\'确认删除？\'))delwb(\''.$r_res['tid'].'\''.($isplink>0?', \''.$r_res['uid'].'\'':'').');return false;" style="color: #f00;">删除</a>';
	$c.='<p class="other">'.(isset($a)?'<span>'.join('|', $a).'</span>':'').'<a href="mblog-'.$r_res['uid'].'-'.$r_res['tid'].'.html">'.yjl_wbdate($r_res['dateline']).'</a></p>';
	if($user_id>0)$c.='<div id="wbfdiv_'.$r_res['tid'].'" style="padding: 10px;display: none;clear: both;"><div id="zfc_'.$r_res['tid'].'"'.($r_res['forwards']>0?'':' style="display: none;"').'>原文共'.$r_res['forwards'].' 次转发</div><input class="text" style="width: 400px;" id="content_f_'.$r_res['tid'].'" size="50"><br/>'.($ispl>0?'':'<input type="checkbox" name="ispl_'.$r_res['tid'].'" id="ispl_'.$r_res['tid'].'"/>同时作为评论发布 ').'<input id="submit_fb_'.$r_res['tid'].'" type="button" value="发表" onclick="postzf(\''.$r_res['tid'].'\');"/> <input id="submit_cl_'.$r_res['tid'].'" type="button" value="取消" onclick="show_forward(\''.$r_res['tid'].'\''.($isplink>0?', 1':'').');"/></div>';
	if(($user_id>0 && $ispl==0) || $r_res['replys']>0)$c.='<div id="wbrdiv_'.$r_res['tid'].'" style="padding: 10px;display: none;clear: both;"></div>';
	$c.='</div></li>';
	return $c;
}

function yjl_wbdate($t){
	if(date('Y')!=date('Y', $t)){
		return date('Y年n月j日 H:i', $t);
	}elseif($t<=(time()-3600)){
		return date('n月j日 H:i', $t);
	}elseif($t<(time()-60)){
		return round((time()-$t)/60).'分钟前';
	}else{
		return '刚刚';
	}
}

function yjl_ajaxpage($p, $t, $v, $u, $m=4){
	$mid=md5($u);
	$c=($p!=1?'<a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p=1\');return false;">首页</a>':'首页').' |';
	if($t>($m*2+1)){
		if($p>1)$c.=' <a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p=1\');return false;">1</a>';
		if($p>($m+2))$c.=' ...';
		if($p>2){
			for($i=0;$i<$m;$i++){
				$j=$p-$m+$i;
				if($j>1)$c.=' <a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$j.'\');return false;">'.$j.'</a>';
			}
		}
		$c.=' ['.$p.']';
		if($p<($t-1)){
			for($i=0;$i<$m;$i++){
				$j=$p+$i+1;
				if($j<$t)$c.=' <a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$j.'\');return false;">'.$j.'</a>';
			}
		}
		if($p<($t-$m-1))$c.=' ...';
		if($p!=$t)$c.=' <a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$t.'\');return false;">'.$t.'</a>';
	}else{
		for($i=0;$i<$t;$i++)$c.=' '.($p!=($i+1)?'<a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.($i+1).'\');return false;">':'[').($i+1).($p!=($i+1)?'</a>':']');
	}
	return $c.' | '.($p!=$t?'<a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$t.'\');return false;">尾页</a>':'尾页').'<span id="pl_s_'.$mid.'" style="display: none;"> 正在载入…</span>';
}

function yjl_newajaxpage($p, $t, $v, $u, $m=4){
	$mid=md5($u);
	$c='<div class="pager clearfix" style="clear: both;"><div class="info" id="pl_s_'.$mid.'" style="display: none;">正在载入…</div><ul>';
	if($p!=1)$c.='<li class="prev"><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p=1\');return false;">首页</a></li>';
	if($t>($m*2+1)){
		if($p>1)$c.='<li><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p=1\');return false;">1</a></li>';
		if($p>($m+2))$c.=' ...';
		if($p>2){
			for($i=0;$i<$m;$i++){
				$j=$p-$m+$i;
				if($j>1)$c.='<li><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$j.'\');return false;">'.$j.'</a></li>';
			}
		}
		$c.='<li class="current"><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$p.'\');return false;">'.$p.'</a></li>';
		if($p<($t-1)){
			for($i=0;$i<$m;$i++){
				$j=$p+$i+1;
				if($j<$t)$c.='<li><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$j.'\');return false;">'.$j.'</a></li>';
			}
		}
		if($p<($t-$m-1))$c.=' ...';
		if($p!=$t)$c.='<li><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$t.'\');return false;">'.$t.'</a></li>';
	}else{
		for($i=0;$i<$t;$i++)$c.='<li'.($p!=($i+1)?'':' class="current"').'><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.($i+1).'\');return false;">'.($i+1).'</a></li>';
	}
	if($p!=$t)$c.='<li class="next"><a href="#" onclick="$(\'#pl_s_'.$mid.'\').show();$(\'#'.$v.'\').load(\''.$u.'&p='.$t.'\');return false;">尾页</a></li>';
	$c.='</ul></div>';
	return $c;
}

function yjl_uploadjs($c, $ac='', $sc='', $f='uploadimg.php', $v='file_upload', $se='', $ismf=0){
	global $yjl_tpath, $max_file, $u_ea, $_COOKIE, $config, $yjl_url;
	$s='
	$(\'#'.$v.'\').uploadify({
		\'uploader\':\''.$yjl_url.$yjl_tpath.'images/uploadify/uploadify.swf?id=&random=\'+Math.random(),
		\'script\':\''.urlencode($yjl_url.'j/'.$f).'\',
		\'cancelImg\':\''.$yjl_tpath.'images/uploadify/cancel.png\',
		\'buttonImg\':\'images/upload_i.gif\',
		\'width\':133,
		\'height\':30,
		\'auto\':true,'.($ismf>0?'
		\'multi\':true,':'').'
		\'sizeLimit\':\''.($max_file*1024).'\',
		\'fileExt\':\'';
	unset($ue);
	foreach($u_ea as $v){
		$s.='*.'.$v.';';
		$ue[]='*.'.$v;
	}
	$s.='\',
		\'fileDesc\':\'请选择图片文件('.join(';', $ue).')\',
		\'scriptData\':{\'cookie_auth\':\''.urlencode($_COOKIE[$config['cookie_prefix'].'auth']).'\''.$sc.'},
		\'onComplete\':function(event, ID, fileObj, response, data){
			if(response!=\'\'){
				'.$c.'
			}
		}';
	if($ac!='')$s.=',
		\'onAllComplete\':function(event, data){
			'.$ac.'
		}';
	if($se!='')$s.=',
		\'onSelect\':function(event, queueID, fileObj){
			'.$se.'
		}';
	$s.='
	});';
	return $s;
}

function yjl_uploadv_0($i, $n, $f, $ie){
	global $u_ea, $max_file;
	return '<div id="flashupload_'.$i.'"><input id="'.$n.'" type="file" name="'.$n.'"/>如果您不能上传图片，可以<a href="#" onclick="upload_switch(\''.$i.'\');return false;">点击这里</a>尝试普通模式上传</div><form method="post" enctype="multipart/form-data" target="normalupload_i_'.$i.'" action="'.$f.'" onsubmit="return normalupload(\''.$i.'\');" id="normalupload_'.$i.'" style="display: none;"><input type="file" id="normalUploadf_'.$i.'" name="Filedata"/><input type="submit" value="上传"/><br/>如果您不能上传图片，可以<a href="#" onclick="upload_switch(\''.$i.'\');return false;">点击这里</a>尝试极速模式上传'.$ie.'<div style="display: none;" class="nu_pdiv" id="normaluploadv_'.$i.'">正在上传</div></form><iframe src="about:blank;" style="display: none;" id="normalupload_i_'.$i.'" name="normalupload_i_'.$i.'"></iframe>允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB';
}

function yjl_uploadv_2($i, $n, $ie=''){
	global $u_ea, $max_file;
	return '<div id="flashupload_'.$i.'"><div id="jlpu_form_'.$i.'"><input id="'.$n.'" type="file" name="'.$n.'"/>如果您不能上传图片，可以<a href="#" onclick="upload_switch(\''.$i.'\');$(\'#upload_t_'.$i.'\').val(\'1\');return false;">点击这里</a>尝试普通模式上传<br/>允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB</div>'.$ie.'</div><div id="normalupload_'.$i.'" style="display: none;"><input type="file" id="normalUploadf_'.$i.'" name="Filedata"/><br/>如果您不能上传图片，可以<a href="#" onclick="upload_switch(\''.$i.'\');$(\'#upload_t_'.$i.'\').val(\'0\');return false;">点击这里</a>尝试极速模式上传<br/>允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB</div>';
}

function yjl_uploadv_3(){
	global $u_ea, $max_file;
	return '<div id="imgu_div" style="display: none;"><div id="imgu_form">'.yjl_uploadv_0(0, 'file_upload', 'j/uploadimg.php', '<input type="hidden" name="is_nu" value="1"/>').'<input type="hidden" name="img_c" id="img_c" value="0"/></div><div id="imgu_upload" style="display: none;padding: 3px;border: 1px solid #eee;background: #fff;"></div></div>';
}

function yjl_getsq($l1=0, $l2=0, $l3=0, $l4=0){
	global $dbprefix;
	if($l1>0){
		$q_res=sprintf('select name from %s where level=1 and id=%s limit 1', $dbprefix.'common_district', $l1);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$a_n[]=$r_res['name'];
		mysql_free_result($res);
	}
	if($l2>0){
		$q_res=sprintf('select name from %s where level=2 and id=%s limit 1', $dbprefix.'common_district', $l2);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$a_n[]=$r_res['name'];
		mysql_free_result($res);
	}
	if($l3>0){
		$q_res=sprintf('select name from %s where level=3 and id=%s limit 1', $dbprefix.'common_district', $l3);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$a_n[]=$r_res['name'];
		mysql_free_result($res);
	}
	if($l4>0){
		$q_res=sprintf('select name from %s where level=4 and id=%s limit 1', $dbprefix.'common_district', $l4);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$a_n[]=$r_res['name'];
		mysql_free_result($res);
	}
	if(isset($a_n))return join(' ', $a_n);
}

function yjl_sqse($a, $l1=0, $l2=0, $l3=0, $l4=0){
	global $dbprefix;
	$c='<select name="l1id" id="o_l1id" onchange="och1id();"><option value="0">-选择省-</option>';
	$l1id=0;
	foreach($a as $k=>$v){
		if($l1==$k)$l1id=$l1;
		$c.='<option value="'.$k.'"'.($l1==$k?' selected="selected"':'').'>'.$v.'</option>';
	}
	$c.='</select> <span id="ndq2">';
	if($l1id>0){
		$q_res=sprintf('select id, name from %s where level=2 and upid=%s', $dbprefix.'common_district', $l1id);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$l2id=0;
			$c.='<select name="l2id" id="o_l2id" onchange="och2id();"><option value="0">-选择地区-</option>';
			do{
				if($r_res['id']==$l2)$l2id=$l2;
				$c.='<option value="'.$r_res['id'].'"'.($r_res['id']==$l2?' selected="selected"':'').'>'.$r_res['name'].'</option>';
			}while($r_res=mysql_fetch_assoc($res));
			$c.='</select> <span id="ndq3">';
			if($l2id>0){
				$q_rep=sprintf('select id, name from %s where level=3 and upid=%s', $dbprefix.'common_district', $l2id);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					$l3id=0;
					$c.='<select name="l3id" id="o_l3id" onchange="och3id();"><option value="0">-不限地区-</option>';
					do{
						if($r_rep['id']==$l3)$l3id=$l3;
						$c.='<option value="'.$r_rep['id'].'"'.($r_rep['id']==$l3?' selected="selected"':'').'>'.$r_rep['name'].'</option>';
					}while($r_rep=mysql_fetch_assoc($rep));
					$c.='</select> <span id="ndq4">';
					if($l3id>0){
						$q_req=sprintf('select id, name from %s where level=4 and upid=%s', $dbprefix.'common_district', $l3id);
						$req=mysql_query($q_req) or die(mysql_error());
						$r_req=mysql_fetch_assoc($req);
						if(mysql_num_rows($req)>0){
							$c.='<select name="l4id"><option value="0">-不限地区-</option>';
							do{
								$c.='<option value="'.$r_req['id'].'"'.($r_req['id']==$l4?' selected="selected"':'').'>'.$r_req['name'].'</option>';
							}while($r_req=mysql_fetch_assoc($req));
							$c.='</select>';
						}
						mysql_free_result($req);
					}
					$c.='</span>';
				}
				mysql_free_result($rep);
			}
			$c.='</span>';
		}
		mysql_free_result($res);
	}
	$c.='</span>';
	return $c;
}

function yjl_sqse1($l1, $l2=0, $l3=0, $l4=0){
	global $dbprefix;
	$c='';
	$q_res=sprintf('select id, name from %s where level=2 and upid=%s', $dbprefix.'common_district', $l1);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$l2id=0;
		$c.='<select name="l2id" id="o_l2id" onchange="och2id();"><option value="0">-选择地区-</option>';
		do{
			if($r_res['id']==$l2)$l2id=$l2;
			$c.='<option value="'.$r_res['id'].'"'.($r_res['id']==$l2?' selected="selected"':'').'>'.$r_res['name'].'</option>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</select> <span id="ndq3">';
		if($l2id>0){
			$q_rep=sprintf('select id, name from %s where level=3 and upid=%s', $dbprefix.'common_district', $l2id);
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$l3id=0;
				$c.='<select name="l3id" id="o_l3id" onchange="och3id();"><option value="0">-不限地区-</option>';
				do{
					if($r_rep['id']==$l3)$l3id=$l3;
					$c.='<option value="'.$r_rep['id'].'"'.($r_rep['id']==$l3?' selected="selected"':'').'>'.$r_rep['name'].'</option>';
				}while($r_rep=mysql_fetch_assoc($rep));
				$c.='</select> <span id="ndq4">';
				if($l3id>0){
					$q_req=sprintf('select id, name from %s where level=4 and upid=%s', $dbprefix.'common_district', $l3id);
					$req=mysql_query($q_req) or die(mysql_error());
					$r_req=mysql_fetch_assoc($req);
					if(mysql_num_rows($req)>0){
						$c.='<select name="l4id"><option value="0">-不限地区-</option>';
						do{
							$c.='<option value="'.$r_req['id'].'"'.($r_req['id']==$l4?' selected="selected"':'').'>'.$r_req['name'].'</option>';
						}while($r_req=mysql_fetch_assoc($req));
						$c.='</select>';
					}
					mysql_free_result($req);
				}
				$c.='</span>';
			}
			mysql_free_result($rep);
		}
		$c.='</span>';
	}
	mysql_free_result($res);
	return $c;
}

function yjl_cemail($email){
	return strlen($email)>8 && preg_match("/^[-_+.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]]).)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]).){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email);
}

function yjl_chkusername($u, $t=0){
	global $dbprefix;
	$un=$u.($t>0?'_'.$t:'');
	$q_res=sprintf('select uid from %s where username=%s limit 1', $dbprefix.'members', yjl_SQLString($un, 'text'));
	$res=mysql_query($q_res) or die(mysql_error());
	if(mysql_num_rows($res)>0){
		$t++;
		$u=yjl_chkusername($u, $t);
	}else{
		$u=$un;
	}
	mysql_free_result($res);
	return $u;
}

function yjl_sendmcode($mob, $msg){
	global $r_main;
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

function yjl_fxop($xqid, $fxid=0, $t=0, $js=''){
	global $yjl_dbprefix;
	$q_res=sprintf('select * from %s where xqid=%s', $yjl_dbprefix.'xq_fx', $xqid);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			$a[$r_res['fxid']]=$r_res;
		}while($r_res=mysql_fetch_assoc($res));
	}else{
		$q_rep=sprintf('select * from %s where xqid=0', $yjl_dbprefix.'xq_fx');
		$rep=mysql_query($q_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			do{
				$a[$r_rep['fxid']]=$r_rep;
			}while($r_rep=mysql_fetch_assoc($rep));
		}
		mysql_free_result($rep);
	}
	mysql_free_result($res);
	if($fxid>0 && !isset($a[$fxid])){
		$q_res=sprintf('select * from %s where fxid=%s limit 1', $yjl_dbprefix.'xq_fx', $fxid);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$a[$r_res['fxid']]=$r_res;
		mysql_free_result($res);
	}
	if(isset($a)){
		$i=1;
		$c='<select name="fxid"'.($js!=''?' onchange="'.$js.'"':'').'>'.((isset($a[$fxid]) && $t==0)?'':'<option value="0">选择户型</option>');
		foreach($a as $v)$c.='<option value="'.$v['fxid'].'"'.($fxid==$v['fxid']?' selected="selected"':'').'>'.$v['name'].($v['content']!=''?' ('.$v['content'].')':'').'</option>';
		$c.='</select>';
	}else{
		$i=0;
		$c='<input type="hidden" name="fxid" value="'.$fxid.'"/>';
		$a=array();
	}
	return array($i, $c, $a);
}

function yjl_chkuag($a='MSIE'){
	global $_SERVER;
	if(isset($_SERVER['HTTP_USER_AGENT'])){
		$age=strtoupper($_SERVER['HTTP_USER_AGENT']);
		if(strstr($age, $a))return true;
	}
	return false;
}

function yjl_getys($a, $u){
	global $dbprefix;
	$y[]=0;
	if($a['uid']==$u['uid']){
		$y[]=1;
		$y[]=2;
	}else{
		$q_res=sprintf('select uid from %s where uid=%s and buddyid=%s limit 1', $dbprefix.'buddys', $u['uid'], $a['uid']);
		$res=mysql_query($q_res) or die(mysql_error());
		if(mysql_num_rows($res)>0)$y[]=1;
		mysql_free_result($res);
	}
	return $y;
}

function yjl_vlog($u, $tid=0){
	global $user_id, $yjl_dbprefix;
	if($user_id>0 && ($user_id!=$u || $tid==0)){
		$q_res=sprintf('select id from %s where uid=%s and vuid=%s and tid=%s', $yjl_dbprefix.'vlog', $user_id, $u, $tid);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$uSQL=sprintf('update %s set datetime=%s where id=%s', $yjl_dbprefix.'vlog', time(), $r_res['id']);
			$result=mysql_query($uSQL) or die(mysql_error());
		}else{
			$iSQL=sprintf('insert into %s (uid, vuid, tid, datetime) values (%s, %s, %s, %s)', $yjl_dbprefix.'vlog',
				$user_id,
				$u,
				$tid,
				time());
			$result=mysql_query($iSQL) or die(mysql_error());
		}
		mysql_free_result($res);
	}
}

function yjl_fxdiv($s=''){
	global $a_fx;
	$m=4;
	$c='<div class="fx_sdiv"'.($s!=''?' style="'.$s.'"':'').'><table cellspacing="0" cellpadding="0"><tr><td><span onmouseover="showfxv($(this), 1);" onmouseout="$(\'#fx_div\').hide();">分享到：</span></td>';
	$i=0;
	foreach($a_fx as $k=>$v){
		if($i<$m)$c.='<td><a title="分享到'.$v[0].'" href="#" onclick="fx_link(\''.$v[1].'\');return false;"><img src="images/blank.gif" width="16" height="16" class="fx_img" style="background-position: 0 -'.(40+($k+1)*40).'px;"></a></td>';
		$i++;
	}
	$c.='</tr></table></div>';
	return $c;
}

function yjl_imgxy($sw, $sh, $w, $h, $t=0){
	if($t>0){
		if($sw>$w && $sh>$h){
			if(($sh/$h)>($sw/$w)){
				$dh=$h;
				$dw=round(($sw*$dh)/$sh);
				if($w>$dw)$w=$dw;
			}else{
				$dw=$w;
				$dh=round(($sh*$dw)/$sw);
				if($h>$dh)$h=$dh;
			}
		}elseif($sw>$w){
			$dw=$w;
			$dh=round(($sh*$dw)/$sw);
			if($h>$dh)$h=$dh;
		}else{
			$dh=$h;
			$dw=round(($sw*$dh)/$sh);
			if($w>$dw)$w=$dw;
		}
	}else{
		if($sw>$w && $sh>$h){
			if(($sh/$h)>($sw/$w)){
				$dw=$w;
				$dh=round(($sh*$dw)/$sw);
				if($h>$dh)$h=$dh;
			}else{
				$dh=$h;
				$dw=round(($sw*$dh)/$sh);
				if($w>$dw)$w=$dw;
			}
		}elseif($sw>$w){
			$dw=$sw;
			$dh=$sh;
			$h=$sh;
		}else{
			$dw=$sw;
			$dh=$sh;
			$w=$sw;
		}
	}
	return array(($w-$dw)/2, ($h-$dh)/2, $dw, $dh, $w, $h);
}

function yjl_jlimgrightu($d_jpdb, $r_res, $f){
	global $user_id, $udb, $uadb, $yjl_dbprefix, $jldimg_jg;
	if(!isset($uadb[$d_jpdb['uid']]))$uadb[$d_jpdb['uid']]=yjl_udb($d_jpdb['uid']);
	$ph=round(600*$d_jpdb['height']/$d_jpdb['width']);
	$c='<a href="#" onclick="openimg(\''.$d_jpdb['o_url'].'\', '.$d_jpdb['width'].', '.$d_jpdb['height'].');return false;" title="点击查看大图"><img src="'.$d_jpdb['url'].'" onmouseover="showjpv($(this));" onmouseout="hidejpv();" height="'.$ph.'" id="jp_big"/></a><p>上传者：<a href="user-'.$d_jpdb['uid'].'.html">'.$uadb[$d_jpdb['uid']]['nc'].'</a>';
	if($d_jpdb['uid']==$r_res['uid']){
		$c.='(监理师)';
	}elseif($d_jpdb['uid']==$r_res['hzid']){
		$c.='(业主)';
	}else{
		$c.='(友邻)';
	}
	if($user_id>0)$c.=' <a href="#" onclick="addplike('.$d_jpdb['jpid'].', 0);return false;">喜欢</a>(<span id="c_plike_0_'.$d_jpdb['jpid'].'">'.$d_jpdb['c_like'].'</span>) <a href="#" onclick="addplike('.$d_jpdb['jpid'].', 1);return false;">要加油</a>(<span id="c_plike_1_'.$d_jpdb['jpid'].'">'.$d_jpdb['c_unlike'].'</span>) <a href="#" onclick="addplike('.$d_jpdb['jpid'].', 2);return false;">分享</a>(<span id="c_plike_2_'.$d_jpdb['jpid'].'">'.$d_jpdb['c_share'].'</span>)';
	if($user_id>0 && (($user_id==$d_jpdb['uid'] && $d_jpdb['datetime']>(time()-$jldimg_jg)) || $user_id==$r_res['hzid'] || $udb['isxg']>0 || $udb['qx']==10))$c.=' | <a href="'.$f.'?id='.$r_res['jlid'].'&amp;xqid='.$r_res['xqid'].'&amp;lid='.$d_jpdb['lid'].'&amp;jpid='.$d_jpdb['jpid'].'&amp;del=1" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a>';
	$c .= '<span style="float:right;margin-right:50px;">日期：' . date('Y-m-d', $d_jpdb['datetime']) . '</span>';
	$c.='</p>';
	$pw=250;
	$id_l=0;
	$id_r=0;
	$isn=0;
	$q_rep=sprintf('select jpid from %s where jlid=%s and lid=%s and uid=%s and is_del=0 order by datetime desc, jpid desc', $yjl_dbprefix.'jl_photo', $d_jpdb['jlid'], $d_jpdb['lid'], $d_jpdb['uid']);
	$rep=mysql_query($q_rep) or die(mysql_error());
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		do{
			if($r_rep['jpid']==$d_jpdb['jpid'])$isn=1;
			if($isn==0)$id_l=$r_rep['jpid'];
			if($isn==1 && $r_rep['jpid']!=$d_jpdb['jpid'] && $id_r==0)$id_r=$r_rep['jpid'];
		}while($r_rep=mysql_fetch_assoc($rep));
	}
	mysql_free_result($rep);
	$c.='<div id="jpv_l" class="jpv" style="width: '.$pw.'px;height: '.$ph.'px;display: none;'.($id_l>0?'cursor: url(images/left.cur),auto;" onclick="loadjlp(\''.$id_l.'\', \''.$r_res['jlid'].'\');" title="':'" title="没有').'上一页" onmouseover="$(this).show();" onmouseout="$(this).hide();"></div>';
	$c.='<div id="jpv_r" class="jpv" style="width: '.$pw.'px;height: '.$ph.'px;display: none;'.($id_r>0?'cursor: url(images/right.cur),auto;" onclick="loadjlp(\''.$id_r.'\', \''.$r_res['jlid'].'\');" title="':'" title="没有').'下一页" onmouseover="$(this).show();" onmouseout="$(this).hide();"></div>';
	return $c;
}

function yjl_jlimgrightc($d_jpdb, $r_res, $page=1){
	global $a_lc, $r_main, $yjl_url, $user_id, $udb, $uadb, $yjl_dbprefix, $dbprefix, $p_size;
	$pt=$r_res['name'].' '.$a_lc[$d_jpdb['lid']].'阶段 | '.$r_main['site_name'];
	$c='<div class="share" onmouseover="$(\'#fx_id\').val(\''.$d_jpdb['jpid'].'\');">'.yjl_fxdiv().'<input type="hidden" id="fx_url_'.$d_jpdb['jpid'].'" value="'.urlencode($yjl_url.'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$d_jpdb['jpid'].'.html').'"/><input type="hidden" id="fx_title_'.$d_jpdb['jpid'].'" value="'.urlencode($pt).'"/><input type="hidden" id="fx_title0_'.$d_jpdb['jpid'].'" value="'.urlencode(@iconv('UTF-8', 'GB2312', $pt)).'"/></div>';
	$ispl=$user_id>0?0:1;
	$q_rep=sprintf('select a.oid as a_oid, b.* from %s as a, %s as b where a.tid=b.tid and a.jpid=%s order by a.oid desc, a.datetime desc', $yjl_dbprefix.'jl_topic', $dbprefix.'topic', $d_jpdb['jpid']);
	$a_rep=mysql_query($q_rep) or die(mysql_error());
	$tr_rep=mysql_num_rows($a_rep);
	if($tr_rep>0){
		$c.='<ul class="list_comment sprcomt clearfix hover">';
		$tp_rep=ceil($tr_rep/$p_size);
		if($page>$tp_rep)$page=$tp_rep;
		$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
		$rep=mysql_query($q_l_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		do{
			if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
			$fjc=$r_rep['a_oid']>0?' class="current"':'';
			$c.=yjl_newwb($r_rep, $ispl, $d_jpdb['tid'], $fjc);
		}while($r_rep=mysql_fetch_assoc($rep));
		mysql_free_result($rep);
		$c.='</ul>';
		if($tp_rep>1)$c.=yjl_newhmpage('photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$d_jpdb['jpid'].'-p[p].html', $page, $tp_rep, 'jp_topic');
	}
	mysql_free_result($a_rep);
	return $c;
}

function yjl_yfimgrightu($d_cldb, $r_res){
	global $user_id, $udb, $uadb, $yjl_dbprefix, $jldimg_jg;
	$id_l=0;
	$id_r=0;
	$isn=0;
	$f='photo.php';
	$q_rep=sprintf('select clid from %s where jlid=%s order by datetime desc, clid desc', $yjl_dbprefix.'jl_cl', $d_cldb['jlid']);
	$rep=mysql_query($q_rep) or die(mysql_error());
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		do{
			if($r_rep['clid']==$d_cldb['clid'])$isn=1;
			if($isn==0)$id_l=$r_rep['clid'];
			if($isn==1 && $r_rep['clid']!=$d_cldb['clid'] && $id_r==0)$id_r=$r_rep['clid'];
		}while($r_rep=mysql_fetch_assoc($rep));
	}
	mysql_free_result($rep);
	if(!isset($uadb[$d_cldb['uid']]))$uadb[$d_cldb['uid']]=yjl_udb($d_cldb['uid']);
	$ph=round(600*$d_cldb['height']/$d_cldb['width']);
	$c='<a href="#" onclick="openimg(\''.$d_cldb['o_url'].'\', '.$d_cldb['width'].', '.$d_cldb['height'].');return false;" title="点击查看大图"><img src="'.$d_cldb['url'].'" onmouseover="showjpv($(this));" onmouseout="hidejpv();" height="'.$ph.'" id="jp_big"/></a><p>'.($d_cldb['content']!=''?$d_cldb['content'].' | ':'').'上传者：<a href="user-'.$d_cldb['uid'].'.html">'.$uadb[$d_cldb['uid']]['nc'].'</a>';
	if($d_cldb['uid']==$r_res['uid']){
		$c.='(监理师)';
	}elseif($d_cldb['uid']==$r_res['hzid']){
		$c.='(业主)';
	}else{
		$c.='(友邻)';
	}
	if($user_id>0 && ($user_id==$d_cldb['uid'] || $user_id==$r_res['hzid'] || $udb['isxg']>0 || $udb['qx']==10))$c.=' | <a href="'.$f.'?id='.$r_res['jlid'].'&amp;xqid='.$r_res['xqid'].'&amp;t=home&amp;clid='.$d_cldb['clid'].'&amp;del=1" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a>';
	$c.='</p>';
	$pw=250;
	$c.='<div id="jpv_l" class="jpv" style="width: '.$pw.'px;height: '.$ph.'px;display: none;'.($id_l>0?'cursor: url(images/left.cur),auto;" onclick="loadclp(\''.$id_l.'\', \''.$r_res['jlid'].'\');" title="':'" title="没有').'上一页" onmouseover="$(this).show();" onmouseout="$(this).hide();"></div>';
	$c.='<div id="jpv_r" class="jpv" style="width: '.$pw.'px;height: '.$ph.'px;display: none;'.($id_r>0?'cursor: url(images/right.cur),auto;" onclick="loadclp(\''.$id_r.'\', \''.$r_res['jlid'].'\');" title="':'" title="没有').'下一页" onmouseover="$(this).show();" onmouseout="$(this).hide();"></div>';
	return $c;
}

function yjl_yfimgrightc($d_cldb, $r_res, $page=1){
	global $a_lc, $r_main, $yjl_url, $user_id, $udb, $uadb, $yjl_dbprefix, $dbprefix, $p_size;
	$pt=$r_res['name'].' 验房 '.$d_cldb['content'].' | '.$r_main['site_name'];
	$c='<div class="share" onmouseover="$(\'#fx_id\').val(\''.$d_cldb['clid'].'\');">'.yjl_fxdiv().'<input type="hidden" id="fx_url_'.$d_cldb['clid'].'" value="'.urlencode($yjl_url.'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-home-'.$d_cldb['clid'].'.html').'"/><input type="hidden" id="fx_title_'.$d_cldb['clid'].'" value="'.urlencode($pt).'"/><input type="hidden" id="fx_title0_'.$d_cldb['clid'].'" value="'.urlencode(@iconv('UTF-8', 'GB2312', $pt)).'"/></div>';
	$ispl=$user_id>0?0:1;
	$q_rep=sprintf('select a.oid as a_oid, b.* from %s as a, %s as b where a.tid=b.tid and a.clid=%s order by a.oid desc, a.datetime desc', $yjl_dbprefix.'jl_topic', $dbprefix.'topic', $d_cldb['clid']);
	$a_rep=mysql_query($q_rep) or die(mysql_error());
	$tr_rep=mysql_num_rows($a_rep);
	if($tr_rep>0){
		$c.='<ul class="list_comment sprcomt clearfix hover">';
		$tp_rep=ceil($tr_rep/$p_size);
		if($page>$tp_rep)$page=$tp_rep;
		$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
		$rep=mysql_query($q_l_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		do{
			if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
			$fjc=$r_rep['a_oid']>0?' class="current"':'';
			$c.=yjl_newwb($r_rep, $ispl, $d_cldb['tid'], $fjc);
		}while($r_rep=mysql_fetch_assoc($rep));
		mysql_free_result($rep);
		$c.='</ul>';
		if($tp_rep>1)$c.=yjl_newhmpage('photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-home-'.$d_cldb['clid'].'-p[p].html', $page, $tp_rep, 'jp_topic');
	}
	mysql_free_result($a_rep);
	return $c;
}

function yjl_hdimgrightu($pdb, $r_res){
	global $user_id, $udb, $uadb, $yjl_dbprefix, $dbprefix, $yjl_tpath;
	$id_l=0;
	$id_r=0;
	$isn=0;
	$q_rep=sprintf('select a.id from %s as a, %s as b, %s as c where a.tid=b.tid and b.tid=c.tid and b.hdid=%s order by a.dateline desc, a.id desc', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', $dbprefix.'topic', $r_res['hdid']);
	$rep=mysql_query($q_rep) or die(mysql_error());
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		do{
			if($r_rep['id']==$pdb['id'])$isn=1;
			if($isn==0)$id_l=$r_rep['id'];
			if($isn==1 && $r_rep['id']!=$pdb['id'] && $id_r==0)$id_r=$r_rep['id'];
		}while($r_rep=mysql_fetch_assoc($rep));
	}
	mysql_free_result($rep);
	if(!isset($uadb[$pdb['uid']]))$uadb[$pdb['uid']]=yjl_udb($pdb['uid']);
	$ph=round(612*$pdb['height']/$pdb['width']);
	$c='<a href="#" onclick="openimg(\''.$yjl_tpath.'images/topic/'.$pdb['up'][1].$pdb['id'].'_o.jpg\', '.$pdb['width'].', '.$pdb['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.'images/topic/'.$pdb['up'][1].$pdb['id'].'_o.jpg" onmouseover="showjpv($(this));" onmouseout="hidejpv();" height="'.$ph.'" id="jp_big"/></a><p>'.yjl_wbdecode($pdb['content']).'</p>';
	$pw=250;
	$c.='<div id="jpv_l" class="jpv" style="width: '.$pw.'px;height: '.$ph.'px;display: none;'.($id_l>0?'cursor: url(images/left.cur),auto;" onclick="loadhdp(\''.$id_l.'\', \''.$r_res['hdid'].'\');" title="':'" title="没有').'上一页" onmouseover="$(this).show();" onmouseout="$(this).hide();"></div>';
	$c.='<div id="jpv_r" class="jpv" style="width: '.$pw.'px;height: '.$ph.'px;display: none;'.($id_r>0?'cursor: url(images/right.cur),auto;" onclick="loadhdp(\''.$id_r.'\', \''.$r_res['hdid'].'\');" title="':'" title="没有').'下一页" onmouseover="$(this).show();" onmouseout="$(this).hide();"></div>';
	return $c;
}

function yjl_hdimgrightc($pdb, $r_res, $page=1){
	global $r_main, $yjl_url, $user_id, $udb, $uadb, $yjl_dbprefix, $dbprefix, $p_size, $iscy;
	$pt=$r_res['name'].' 活动照片';
	$c='<div class="share" onmouseover="$(\'#fx_id\').val(\''.$pdb['id'].'\');">'.yjl_fxdiv().'<input type="hidden" id="fx_url_'.$pdb['id'].'" value="'.urlencode($yjl_url.'activeimg-'.$r_res['xqid'].'-'.$r_res['hdid'].'-'.$pdb['id'].'.html').'"/><input type="hidden" id="fx_title_'.$pdb['id'].'" value="'.urlencode($pt).'"/><input type="hidden" id="fx_title0_'.$pdb['id'].'" value="'.urlencode(@iconv('UTF-8', 'GB2312', $pt)).'"/></div>';
	$ispl=$iscy>0?0:1;
	$q_rep=sprintf('select b.* from %s as a, %s as b where a.tid=%s and a.replyid=b.tid order by b.dateline desc', $dbprefix.'topic_reply', $dbprefix.'topic', $pdb['tid']);
	$a_rep=mysql_query($q_rep) or die(mysql_error());
	$tr_rep=mysql_num_rows($a_rep);
	if($tr_rep>0){
		$c.='<ul class="list_comment sprcomt clearfix hover">';
		$tp_rep=ceil($tr_rep/$p_size);
		if($page>$tp_rep)$page=$tp_rep;
		$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
		$rep=mysql_query($q_l_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		do{
			if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
			$c.=yjl_newwb($r_rep, $ispl, $pdb['tid']);
		}while($r_rep=mysql_fetch_assoc($rep));
		mysql_free_result($rep);
		$c.='</ul>';
		if($tp_rep>1)$c.=yjl_newhmpage('activeimg-'.$r_res['xqid'].'-'.$r_res['hdid'].'-'.$pdb['id'].'-p[p].html', $page, $tp_rep, 'hd_topic');
	}
	mysql_free_result($a_rep);
	return $c;
}

function yjl_hdimgside($pdb, $r_res){
	global $uadb, $xqid, $xqdb, $yjl_dbprefix;
	$c='<h4>上传者<span>('.yjl_wbdate($pdb['dateline']).') </span></h4>
				<div class="pic_text clearfix">
					<a href="user-'.$pdb['uid'].'.html"><img src="'.yjl_face($pdb['uid'], $uadb[$pdb['uid']]['face']).'" /></a>
					<p class="memb"><a href="user-'.$pdb['uid'].'.html">'.$uadb[$pdb['uid']]['nc'].'</a></p>';
	if($uadb[$pdb['uid']]['qx']==5)$c.='<p class="memb">监理师</p>';
	if($uadb[$pdb['uid']]['xqid']>0){
		if($uadb[$pdb['uid']]['xqid']==$xqid){
			$xqname=$xqdb['name'];
		}else{
			$q_reu=sprintf('select name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', $uadb[$pdb['uid']]['xqid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$xqname=$r_reu['name'];
			mysql_free_result($reu);
		}
		$c.='<a href="active-'.$uadb[$pdb['uid']]['xqid'].'.html">'.$xqname.'</a>';
	}
	return $c;
}

function yjl_pbl($r_rep){
	global $uadb, $a_lc, $r_rep, $pbl_w, $yjl_url, $yjl_dbprefix, $dbprefix;
	$pt=$uadb[$r_rep['hzid']]['nc'].'的家 '.$a_lc[$r_rep['lid']].'阶段';
	$c='<div class="pbl" jq_newh="1" jq_id="'.$r_rep['jpid'].'" style="display: none;"><a href="photo-'.$r_rep['xqid'].'-'.$r_rep['jlid'].'-'.$r_rep['jpid'].'.html"><img src="'.$r_rep['url'].'" width="'.$pbl_w.'" height="'.round($pbl_w*$r_rep['height']/$r_rep['width']).'"/></a><br/><a href="#" onclick="addplike('.$r_rep['jpid'].', 0);return false;">喜欢</a>(<span id="c_plike_0_'.$r_rep['jpid'].'">'.$r_rep['c_like'].'</span>) <a href="#" onclick="addplike('.$r_rep['jpid'].', 1);return false;">要加油</a>(<span id="c_plike_1_'.$r_rep['jpid'].'">'.$r_rep['c_unlike'].'</span>) <a href="#" onclick="addplike('.$r_rep['jpid'].', 2);return false;">分享</a>(<span id="c_plike_2_'.$r_rep['jpid'].'">'.$r_rep['c_share'].'</span>)<div class="share" style="margin-top: 10px;margin-bottom: 10px;" onmouseover="$(\'#fx_id\').val(\''.$r_rep['jpid'].'\');">'.yjl_fxdiv().'<input type="hidden" id="fx_url_'.$r_rep['jpid'].'" value="'.urlencode($yjl_url.'photo-'.$r_rep['xqid'].'-'.$r_rep['jlid'].'-'.$r_rep['jpid']).'.html"/><input type="hidden" id="fx_title_'.$r_rep['jpid'].'" value="'.urlencode($pt).'"/><input type="hidden" id="fx_title0_'.$r_rep['jpid'].'" value="'.urlencode(@iconv('UTF-8', 'GB2312', $pt)).'"/></div>'.$pt;
	$q_req=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.jpid=%s order by a.oid desc, a.datetime desc limit 5', $yjl_dbprefix.'jl_topic', $dbprefix.'topic', $r_rep['jpid']);
	$req=mysql_query($q_req) or die('');
	$r_req=mysql_fetch_assoc($req);
	if(mysql_num_rows($req)>0){
		do{
			if(!isset($uadb[$r_req['uid']]))$uadb[$r_req['uid']]=yjl_udb($r_req['uid']);
			$c.='<div class="pbl_l"><a href="user-'.$r_req['uid'].'.html">'.$uadb[$r_req['uid']]['nc'].'</a>：'.yjl_wbdecode($r_req['content']).'</div>';
		}while($r_req=mysql_fetch_assoc($req));
		$c.='<div class="pbl_l"><a href="photo-'.$r_rep['xqid'].'-'.$r_rep['jlid'].'-'.$r_rep['jpid'].'.html#jp_topic">更多评论…</a></div>';
	}
	mysql_free_result($req);
	$c.='</div>';
	return $c;
}

function yjl_addlog($c, $mid, $isgk=0, $luid=0, $uid=0){
	global $user_id, $yjl_dbprefix;
	if($user_id>0 && $uid==0)$uid=$user_id;
	$q_rep=sprintf('select loid from %s where uid=%s and luid=%s and mid=%s and isgk=%s limit 1', $yjl_dbprefix.'log', $uid, $luid, yjl_SQLString($mid, 'text'), $isgk);
	$rep=mysql_query($q_rep) or die(mysql_error());
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$uSQL=sprintf('update %s set content=%s, datetime=%s, isnew=1 where loid=%s', $yjl_dbprefix.'log', yjl_SQLString($c, 'text'), time(), $r_rep['loid']);
		$result=mysql_query($uSQL) or die(mysql_error());
	}else{
		$iSQL=sprintf('insert into %s (uid, luid, datetime, mid, content, isnew, isgk) values (%s, %s, %s, %s, %s, 1, %s)', $yjl_dbprefix.'log',
			$uid,
			$luid,
			time(),
			yjl_SQLString($mid, 'text'),
			yjl_SQLString($c, 'text'),
			$isgk);
		$result=mysql_query($iSQL) or die(mysql_error());
	}
	mysql_free_result($rep);
}

function yjl_newr_xq(){
	global $xqid, $xqdb;
	return '<div class="box2">
				<h2><a href="einfo-'.$xqid.'.html">小区信息 &#187;</a>'.$xqdb['name'].'</h2>
				<div class="count">
					<a href="photo-'.$xqid.'.html"><b>'.$xqdb['c_jl'].'</b><br />项目</a><a href="active-'.$xqid.'.html"><b>'.$xqdb['c_hd'].'</b><br />活动</a><a href="square-'.$xqid.'.html" style="border:none;"><b>'.$xqdb['c_wb'].'</b><br />微博</a>
				</div>
			</div>';
}

function yjl_newr_jlzx(){
	global $yjl_dbprefix, $dbprefix, $user_id, $udb;
	$c='<div class="box2"><h3>值班监理师</h3>';
	$q_res=sprintf('select a.nc, b.uid, b.face from %s as a, %s as b where a.uid=b.uid and a.qx=5 and a.iswc=1 and a.iszxjl=1', $yjl_dbprefix.'members', $dbprefix.'members');
	$res=mysql_query($q_res) or die($q_res);
	$r_res=mysql_fetch_assoc($res);
	$c_res=mysql_num_rows($res);
	if(mysql_num_rows($res)>0){
		do{
			$c.='<div class="pic_text clearfix">
					<a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" /></a>
					<p class="memb"><a href="faq-new-'.$r_res['uid'].'.html">'.$r_res['nc'].'</a></p>
					<p><span class="mn_ico ico24"></span>高级监理师</p>
				</div>';
			}while($r_res=mysql_fetch_assoc($res));
		}
	mysql_free_result($res);
	$c.='<div class="spr_consul"'.($c_res>0?'':' style="margin-top: 15px;"').'><a href="faq-new.html" class="btn bt_orange"><span class="mn_ico ico25"></span>向监理咨询</a>';
	if($user_id==0 || $udb['qx']==0)$c.=' <a href="'.($user_id==0?'login.php?u='.urlencode('help.php').'" rel="#overlay_login':'help.php').'" class="btn bt_bgblue"><span class="mn_ico ico25"></span>请监理到现场</a>';
	$c.='</div>
			</div>';
	return $c;
}

function yjl_newr_tjhd(){
	global $xqid, $xqdb, $yjl_dbprefix, $user_id, $udb;
	$q_res=sprintf('select * from %s where (xqid=%s or xqid=0) and etime>=%s and isgf=1 order by c_zan desc, etime desc limit 3', $yjl_dbprefix.'hd', $xqid, time());
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c='<div class="box2"><h3>活动推荐</h3><ul class="list_maybe">';
		do{
			$c.='<li><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
						<p><a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>
						<br />
						<span>'.yjl_hd_date($r_res).'</span>
						</p>';
			if($user_id>0){
				$isgz=0;
				$q_rep=sprintf('select uid from %s where hdid=%s and uid=%s', $yjl_dbprefix.'hd_fuser', $r_res['hdid'], $user_id);
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)>0)$isgz=1;
				mysql_free_result($rep);
				$c.='<div class="bt_ltblue">'.($isgz>0?'':'<a href="active.php?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;gz=1">感兴趣</a>');
				if($udb['xqid']==$xqid || $r_res['xqid']==0 || $udb['qx']>0){
					$iscy=0;
					$q_rep=sprintf('select uid from %s where hdid=%s and uid=%s and iscy=0', $yjl_dbprefix.'hd_user', $r_res['hdid'], $user_id);
					$rep=mysql_query($q_rep) or die(mysql_error());
					if(mysql_num_rows($rep)>0)$iscy=1;
					mysql_free_result($rep);
					if($iscy==0)$c.='<a href="active.php?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;j=1">参 加</a>';
				}
				$c.='</div>';
			}else{
				$c.='<div class="bt_ltblue"><a href="login.php?u='.urlencode('active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html').'" rel="#overlay_login">感兴趣</a><a href="active.php?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;j=1">参 加</a></div>';
			}
			$c.='</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
		return $c;
	}
	mysql_free_result($res);
}

function yjl_newr_yh(){
	global $xqid, $xqdb, $yjl_dbprefix, $dbprefix;
	$q_res=sprintf('select a.nc, b.uid, b.face from %s as a, %s as b where a.xqid=%s and a.qx=0 and a.iswc=1 and a.uid=b.uid order by b.uid desc limit 9', $yjl_dbprefix.'members', $dbprefix.'members', $xqid);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c='<div class="box2"><h3><a href="einfo-'.$xqid.'-user.html">查看全部</a>TA们住在这里（'.$xqdb['c_user'].'）</h3><ul class="friend clearfix">';
		do{
			$c.='<li><a href="user-'.$r_res['uid'].'.html" title="'.$r_res['nc'].'"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" /><br />'.yjl_substrs($r_res['nc'], 4).'</a></li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
		return $c;
	}
	mysql_free_result($res);
}

function yjl_newr_visitor(){
	global $xqid, $xqdb, $yjl_dbprefix, $dbprefix;
	$q_res=sprintf('select b.uid, b.face, c.nc from %s as a, %s as b, %s as c where a.uid=b.uid and b.uid=c.uid and a.vuid=%s and a.tid=0 and c.qx<10 order by a.datetime desc limit 9', $yjl_dbprefix.'vlog', $dbprefix.'members', $yjl_dbprefix.'members', $xqid);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c='<div class="box2"><h3>TA们刚刚来过</h3><ul class="friend clearfix">';
		do{
			$c.='<li><a href="user-'.$r_res['uid'].'.html" title="'.$r_res['nc'].'"><img src="'.yjl_face($r_res['uid'], $r_res['face']).'" /><br />'.yjl_substrs($r_res['nc'], 4).'</a></li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
		return $c;
	}
	mysql_free_result($res);
}

function yjl_newr_jltj($isgz=0){
	global $udb, $yjl_dbprefix, $dbprefix, $a_wh_jltpt, $user_id;
	$cuid=$udb['uid'];
	$tj_m=4;
	$tj_i=0;
	if($udb['uid']>0){
		$u_lid=0;
		$q_res=sprintf('select lid from %s where uid=%s or hzid=%s order by lasttime desc limit 1', $yjl_dbprefix.'jl', $cuid, $cuid);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$u_lid=$r_res['lid'];
		mysql_free_result($res);
		if($udb['fxid']>0 && $udb['fg']>0 && $udb['ys']>0){
			$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.ys=%s and a.fg=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $udb['ys'], $udb['fg'], $u_lid, $cuid, $cuid, $tj_m);
			$rep=mysql_query($q_rep) or die(mysql_error());
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if(!isset($a_tj[$r_rep['jlid']])){
						$a_tj[$r_rep['jlid']]=$r_rep;
						$a_tj[$r_rep['jlid']]['ly']='户型风格预算相同';
						$tj_i++;
					}
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
		}
		if($tj_i<$tj_m){
			if($udb['fxid']>0 && $udb['fg']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.fg=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $udb['fg'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$a_tj[$r_rep['jlid']]['ly']='户型风格相同';
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
		if($tj_i<$tj_m){
			if($udb['fxid']>0 && $udb['ys']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.ys=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $udb['ys'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$a_tj[$r_rep['jlid']]['ly']='户型预算相同';
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
		if($tj_i<$tj_m){
			if($udb['fg']>0 && $udb['ys']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fg=%s and a.ys=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fg'], $udb['ys'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$a_tj[$r_rep['jlid']]['ly']='风格预算相同';
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
		if($tj_i<$tj_m){
			if($udb['fxid']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fxid=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fxid'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$a_tj[$r_rep['jlid']]['ly']='户型相同';
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
		if($tj_i<$tj_m){
			if($udb['fg']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.fg=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['fg'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$a_tj[$r_rep['jlid']]['ly']='风格相同';
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
		if($tj_i<$tj_m){
			if($udb['ys']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.ys=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['ys'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$a_tj[$r_rep['jlid']]['ly']='预算相同';
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
		if($tj_i<$tj_m){
			if($udb['xqid']>0){
				$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.xqid=%s and a.lid>=%s and a.xqid=b.xqid and a.c_zp>4 and a.hzid<>%s and a.uid<>%s order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $udb['xqid'], $u_lid, $cuid, $cuid, $tj_m);
				$rep=mysql_query($q_rep) or die(mysql_error());
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					do{
						if(!isset($a_tj[$r_rep['jlid']]) && $tj_i<$tj_m){
							$a_tj[$r_rep['jlid']]=$r_rep;
							$tj_i++;
						}
					}while($r_rep=mysql_fetch_assoc($rep));
				}
				mysql_free_result($rep);
			}
		}
	}else{
		$q_rep=sprintf('select a.name, a.jlid, a.xqid, a.lid, b.name as b_name from %s as a, %s as b where a.xqid=b.xqid and a.c_zp>4 order by a.lasttime desc limit %s', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $tj_m);
		$rep=mysql_query($q_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			do{
				$a_tj[$r_rep['jlid']]=$r_rep;
				$tj_i++;
			}while($r_rep=mysql_fetch_assoc($rep));
		}
		mysql_free_result($rep);
	}
	if($tj_i>0){
		$c='<div class="box2 clearfix">
				<h3>项目推荐</h3>
				<ul class="list_visit list_proj">';
		foreach($a_tj as $v){
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $v['jlid']);
			$reu=mysql_query($q_reu) or die(mysql_error());
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$c.='<li><a href="photo-'.$v['xqid'].'-'.$v['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
					<em class="percent'.$v['lid'].'"></em>
					<p><a href="photo-'.$v['xqid'].'-'.$v['jlid'].'.html" title="'.$v['name'].'">'.yjl_substrs($v['name'], 7).'</a></p>
					<p title="'.$v['b_name'].'">'.yjl_substrs($v['b_name'], 7).'</p>';
			if($udb['uid']>0 && $isgz>0)$c.='<a href="photo.php?id='.$v['jlid'].'&amp;xqid='.$v['xqid'].'&amp;gz=1" class="btn bt_smblue">关 注</a>';
			$c.='<p>'.(isset($v['ly'])?$v['ly']:'&nbsp;').'</p></li>';
		}
		$c.='</ul></div>';
		return $c;
	}
}

function yjl_hd_date($r){
	$a=array(1=>'一', '二', '三', '四', '五', '六', '日');
	$c=date('n月j日', $r['datetime']);
	if(date('Ymd', $r['datetime'])!=date('Ymd', $r['etime']))$c.=' 至 '.date('n月j日', $r['etime']);
	if($r['sjtid']==2 && $r['cs']>0)$c.=' 每星期'.$a[date('N', $r['datetime'])];
	return $c;
}

function yjl_qzdf($r_res){
	global $uadb, $dbprefix, $yjl_dbprefix, $_GET, $yjl_tpath, $a_qzca, $user_id, $udb, $p_size, $page;
	$q_rep=sprintf('select a.qztid, b.* from %s as a, %s as b where a.tid=b.tid and a.qzid=%s order by a.datetime desc', $yjl_dbprefix.'qz_topic', $dbprefix.'topic', $r_res['qzid']);
	$a_rep=mysql_query($q_rep) or die(mysql_error());
	$tr_rep=mysql_num_rows($a_rep);
	if($tr_rep>0){
		$c='<ul class="list_comment">';
		$tp_rep=ceil($tr_rep/$p_size);
		if($page>$tp_rep)$page=$tp_rep;
		$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
		$rep=mysql_query($q_l_rep) or die(mysql_error());
		$r_rep=mysql_fetch_assoc($rep);
		do{
			if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
			$c.='<li id="wb_'.$r_rep['tid'].'">
							<div class="left">
								<a href="user-'.$r_rep['uid'].'.html"><img src="'.yjl_face($r_rep['uid'], $uadb[$r_rep['uid']]['face']).'" /></a>';
			if($uadb[$r_rep['uid']]['qx']==5)$c.='<div class="m_sp1">监理师</div>';
			$c.='</div>
							<div class="right">
								<h3><a href="user-'.$r_rep['uid'].'.html">'.$uadb[$r_rep['uid']]['nc'].'</a>:';
			if($r_rep['longtextid']>0){
				$q_reu=sprintf('select `longtext` from %s where id=%s and tid=%s limit 1', $dbprefix.'topic_longtext', $r_rep['longtextid'], $r_rep['tid']);
				$reu=mysql_query($q_reu) or die(mysql_error());
				$r_reu=mysql_fetch_assoc($reu);
				if(mysql_num_rows($reu)>0){
					$c.=$r_reu['longtext'];
				}else{
					$r_rep['longtextid']=0;
				}
				mysql_free_result($reu);
			}
			if($r_rep['longtextid']==0)$c.=yjl_wbdecode($r_rep['content']);
			$c.='</h3>';
			if($r_rep['imageid']!=''){
				$ai=explode(',', $r_rep['imageid']);
				foreach($ai as $v){
					$q_reu=sprintf('select photo, width, height from %s where id=%s limit 1', $dbprefix.'topic_image', $v);
					$reu=mysql_query($q_reu) or die(mysql_error());
					$r_reu=mysql_fetch_assoc($reu);
					if(mysql_num_rows($reu)>0){
						$ou=str_replace('./', '', $r_reu['photo']);
						$bu=str_replace('_o.jpg', '_s.jpg', $ou);
						$img_a[$r_rep['tid']][$v]='<a href="#" onclick="openimg(\''.$yjl_tpath.$ou.'\', '.$r_reu['width'].', '.$r_reu['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.$bu.'" alt=""/></a>';
					}
					mysql_free_result($reu);
				}
			}
			if($r_rep['videoid']>0){
				$q_reu=sprintf('select video_url, video_img from %s where id=%s limit 1', $dbprefix.'topic_video', $r_rep['videoid']);
				$reu=mysql_query($q_reu) or die(mysql_error());
				$r_reu=mysql_fetch_assoc($reu);
				if(mysql_num_rows($reu)>0)$img_a[$r_rep['tid']]['v'.$r_rep['videoid']]='<a href="'.$r_reu['video_url'].'" target="_blank" title="点击查看视频"><img src="images/blank.gif" style="background: #fff url('.($r_reu['video_img']!=''?$yjl_tpath.str_replace('./', '', $r_reu['video_img']):'images/vi_d.jpg').') no-repeat center;" alt=""/></a>';
				mysql_free_result($reu);
			}
			if(isset($img_a[$r_rep['tid']]))$c.=join(' ', $img_a[$r_rep['tid']]);
			$c.='<p class="other">';
			if($user_id>0 && ($udb['qx']==10 || $udb['isxg']>0 || $user_id==$r_rep['uid']))$c.='<span><a href="faq.php?id='.$r_res['qzid'].'&amp;delhf='.$r_rep['qztid'].'" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a></span>';
			$c.=yjl_wbdate($r_rep['dateline']).'</p></div></li>';
		}while($r_rep=mysql_fetch_assoc($rep));
		mysql_free_result($rep);
		$c.='</ul>';
		if($tp_rep>1)$c.=yjl_newhmpage('faq-'.$r_res['qzid'].'-p[p].html', $page, $tp_rep, 'faq_topic');
		return $c;
	}
	mysql_free_result($a_rep);
}

function yjl_jllist($r_res){
	global $yjl_dbprefix, $a_wh_jltpt, $a_fg, $a_ys, $dbprefix, $uadb;
	$pu='images/jl_d.jpg';
	$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
	$reu=mysql_query($q_reu) or die('');
	$r_reu=mysql_fetch_assoc($reu);
	if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
	mysql_free_result($reu);
	$c='<li>
					<div class="flt_lt">
						<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
						<em class="percent'.$r_res['lid'].'"></em>
						<p><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html">'.$r_res['name'].'</a></p>
					</div>
					<div class="flt_rt">
						<div class="detl">';
	if($r_res['fxid']>0){
		$q_rep=sprintf('select name, content from %s where fxid=%s limit 1', $yjl_dbprefix.'xq_fx', $r_res['fxid']);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0)$c.='<a title="户型：'.$r_rep['content'].'"><span class="mn_ico ico36"></span><br />'.$r_rep['name'].'</a>';
		mysql_free_result($rep);
	}
	if(isset($a_fg[$r_res['fg']]))$c.='<a title="风格"><span class="mn_ico ico34"></span><br />'.$a_fg[$r_res['fg']].'</a>';
	if(isset($a_ys[$r_res['ys']]))$c.='<a title="预算"><span class="mn_ico ico35"></span><br />'.$a_ys[$r_res['ys']].'</a>';
	$c.='</div><div class="comt_wrap"><div class="comt">';
	$q_reu=sprintf('select b.* from %s as a, %s as b, %s as c where a.tid=b.tid and a.jpid=c.jpid and c.jlid=%s and c.is_del=0 order by a.datetime desc limit 1', $yjl_dbprefix.'jl_topic', $dbprefix.'topic', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
	$reu=mysql_query($q_reu) or die('');
	$r_reu=mysql_fetch_assoc($reu);
	if(mysql_num_rows($reu)>0){
		if(!isset($uadb[$r_reu['uid']]))$uadb[$r_reu['uid']]=yjl_udb($r_reu['uid']);
		$c.='<div class="comter clearfix">
								<div class="comter_detl">
									<a href="user-'.$r_reu['uid'].'.html"><img src="'.yjl_face($r_reu['uid'], $uadb[$r_reu['uid']]['face']).'" /></a>';
		$fjc='&nbsp;';
		if($r_reu['uid']==$r_res['hzid']){
			$fjc='业主';
		}elseif($r_reu['uid']==$r_res['uid']){
			$fjc='监理师';
		}
		$c.=$fjc.'<p><a href="user-'.$r_reu['uid'].'.html">'.$uadb[$r_reu['uid']]['nc'].'</a><span>'.yjl_wbdate($r_reu['dateline']).'</span></p>
								</div>
								<div class="detler"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><span class="mn_ico ico37"></span></a></div>
							</div>
							<p>'.yjl_wbdecode($r_reu['content']).'</p>';
	}else{
		$c.='<div class="comter clearfix">
								<div class="comter_detl">
									<a href="user-'.$r_res['hzid'].'.html"><img src="'.yjl_face($r_res['hzid'], $uadb[$r_res['hzid']]['face']).'" /></a>
									业主<p><a href="user-'.$r_res['hzid'].'.html">'.$uadb[$r_res['hzid']]['nc'].'</a><span>'.yjl_wbdate($r_res['datetime']).'</span></p>
								</div>
								<div class="detler"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><span class="mn_ico ico37"></span></a></div>
							</div>
							<p>&nbsp;</p>';
	}
	mysql_free_result($reu);
	$c.='</div></div></div></li>';
	return $c;
}

function yjl_homeqz($r_res){
	global $yjl_dbprefix, $dbprefix, $uadb, $yjl_tpath, $a_qzca;
	$c_hd=0;
	$q_reu=sprintf('select b.* from %s as a, %s as b where a.qzid=%s and a.tid=b.tid and a.uid<>%s order by a.datetime desc limit 1', $yjl_dbprefix.'qz_topic', $dbprefix.'topic', $r_res['qzid'], $r_res['uid']);
	$reu=mysql_query($q_reu) or die('');
	$r_reu=mysql_fetch_assoc($reu);
	if(mysql_num_rows($reu)>0){
		$c_hd=1;
		$r_wb=$r_reu;
		$r_zf[$r_wb['tid']]=$r_res;
		if(!isset($uadb[$r_reu['uid']]))$uadb[$r_reu['uid']]=yjl_udb($r_reu['uid']);
	}else{
		$r_wb=$r_res;
	}
	mysql_free_result($reu);
	$c='<li id="wb_'.$r_wb['tid'].'"><div class="left">
								<a href="user-'.$r_wb['uid'].'.html"><img src="'.yjl_face($r_wb['uid'], $uadb[$r_wb['uid']]['face']).'" /></a>
								<div class="m_sp1">监理师</div>
							</div><div class="right">
								<h3><a href="#"><a href="user-'.$r_wb['uid'].'.html">'.yjl_substrs($uadb[$r_wb['uid']]['nc']).'</a></a>:';
	if($r_wb['longtextid']>0){
		$q_reu=sprintf('select `longtext` from %s where id=%s and tid=%s limit 1', $dbprefix.'topic_longtext', $r_wb['longtextid'], $r_wb['tid']);
		$reu=mysql_query($q_reu) or die('');
		$r_reu=mysql_fetch_assoc($reu);
		if(mysql_num_rows($reu)>0){
			$c.=$r_reu['longtext'];
		}else{
			$r_wb['longtextid']=0;
		}
		mysql_free_result($reu);
	}
	if($r_wb['longtextid']==0)$c.=yjl_wbdecode($r_wb['content']);
	$c.='</h3>';
	if($r_wb['imageid']!=''){
		$ai=explode(',', $r_wb['imageid']);
		foreach($ai as $v){
			$q_reu=sprintf('select photo, width, height from %s where id=%s limit 1', $dbprefix.'topic_image', $v);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0){
				$ou=str_replace('./', '', $r_reu['photo']);
				$bu=str_replace('_o.jpg', '_s.jpg', $ou);
				$img_a[$r_wb['tid']][$v]='<a href="#" onclick="openimg(\''.$yjl_tpath.$ou.'\', '.$r_reu['width'].', '.$r_reu['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.$bu.'" alt=""/></a>';
			}
			mysql_free_result($reu);
		}
	}
	if($r_wb['videoid']>0){
		$q_reu=sprintf('select video_url, video_img from %s where id=%s limit 1', $dbprefix.'topic_video', $r_wb['videoid']);
		$reu=mysql_query($q_reu) or die('');
		$r_reu=mysql_fetch_assoc($reu);
		if(mysql_num_rows($reu)>0)$img_a[$r_wb['tid']]['v'.$r_wb['videoid']]='<a href="'.$r_reu['video_url'].'" target="_blank" title="点击查看视频"><img src="images/blank.gif" style="background: #fff url('.($r_reu['video_img']!=''?$yjl_tpath.str_replace('./', '', $r_reu['video_img']):'images/vi_d.jpg').') no-repeat center;" alt=""/></a>';
		mysql_free_result($reu);
	}
	if(isset($img_a[$r_wb['tid']]))$c.=join(' ', $img_a[$r_wb['tid']]);
	if(isset($r_zf[$r_wb['tid']])){
		$r_z=$r_zf[$r_wb['tid']];
		$c.='<div class="active_bg"></div><div class="active clearfix"><h3><a href="user-'.$r_z['uid'].'.html">'.$uadb[$r_z['uid']]['nc'].'</a>：';
		if($r_z['longtextid']>0){
			$q_reu=sprintf('select `longtext` from %s where id=%s and tid=%s limit 1', $dbprefix.'topic_longtext', $r_z['longtextid'], $r_z['tid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0){
				$c.=$r_reu['longtext'];
			}else{
				$r_z['longtextid']=0;
			}
			mysql_free_result($reu);
		}
		if($r_z['longtextid']==0)$c.=yjl_wbdecode($r_z['content']);
		$c.='</h3>';
		if($r_z['imageid']!=''){
			$ai=explode(',', $r_z['imageid']);
			foreach($ai as $v){
				$q_reu=sprintf('select photo, width, height from %s where id=%s limit 1', $dbprefix.'topic_image', $v);
				$reu=mysql_query($q_reu) or die('');
				$r_reu=mysql_fetch_assoc($reu);
				if(mysql_num_rows($reu)>0){
					$ou=str_replace('./', '', $r_reu['photo']);
					$bu=str_replace('_o.jpg', '_s.jpg', $ou);
					$img_a[$r_z['tid']][$v]='<a href="#" onclick="openimg(\''.$yjl_tpath.$ou.'\', '.$r_reu['width'].', '.$r_reu['height'].');return false;" title="点击查看大图"><img src="'.$yjl_tpath.$bu.'" alt=""/></a>';
				}
				mysql_free_result($reu);
			}
		}
		if($r_z['videoid']>0){
			$q_reu=sprintf('select video_url, video_img from %s where id=%s limit 1', $dbprefix.'topic_video', $r_z['videoid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$img_a[$r_z['tid']]['v'.$r_z['videoid']]='<a href="'.$r_reu['video_url'].'" target="_blank" title="点击查看视频"><img src="images/blank.gif" style="background: #fff url('.($r_reu['video_img']!=''?$yjl_tpath.str_replace('./', '', $r_reu['video_img']):'images/vi_d.jpg').') no-repeat center;" alt=""/></a>';
			mysql_free_result($reu);
		}
		if(isset($img_a[$r_z['tid']]))$c.=join(' ', $img_a[$r_z['tid']]);
		$c.='<p class="other">'.yjl_wbdate($r_z['dateline']).'</p></div>';
	}
	$c.='<p class="other">';
	$a[$r_res['qzid']][]='<a href="faq-c'.$r_res['cid'].'.html">'.$a_qzca[$r_res['cid']].'</a>';
	if($r_res['c_zan']>0)$a[$r_res['qzid']][]=' 赞（'.$r_res['c_zan'].'）';
	$a[$r_res['qzid']][]='<a href="faq-'.$r_res['qzid'].'.html">详情</a>';
	$c.=(isset($a[$r_res['qzid']])?'<span>'.join(' |', $a[$r_res['qzid']]):'').'</span>';
	$c.=yjl_wbdate($r_wb['dateline']);
	$c.='</p></div></li>';
	return $c;
}

if('http://'.$_SERVER['HTTP_HOST'].'/'!=$yjl_url){
	//header('Location:'.$yjl_url);
	//exit();
}
$xqid=0;
$user_id=0;
$udb=yjl_chkulog();
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$uadb[$user_id]=$udb;
	$xqid=$udb['xqid'];
}
if(isset($_GET['xqid']) && intval($_GET['xqid'])>0 && (!isset($no_getxq) || $no_getxq==0))$xqid=intval($_GET['xqid']);
if($xqid>0){
	$q_res=sprintf('select * from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
	$res=mysql_query($q_res) or die(mysql_error());
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$xqdb=$r_res;
	}else{
		$xqid=0;
	}
	mysql_free_result($res);
}

?>