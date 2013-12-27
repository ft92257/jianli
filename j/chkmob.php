<?php
session_start();
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='chkmob.php';
$_SESSION['yzm_sj']=time();
if($udb['uid']>0){
	if(isset($_GET['m']) && trim($_GET['m'])!=''){
		$mobile=htmlspecialchars(trim($_GET['m']),ENT_QUOTES);
		if($mobile!=$udb['mobile']){
			$q_res=sprintf('select uid from %s where mobile=%s and misyz=1 limit 1', $yjl_dbprefix.'members', yjl_SQLString($mobile, 'text'));
			$res=mysql_query($q_res) or die('');
			if(mysql_num_rows($res)>0){
				echo '请使用其他手机号。';
			}else{
				if($mobile==$udb['t_mobile'] && $udb['t_mcode']!=''){
					$mcode=$udb['t_mcode'];
				}else{
					$mcode=rand(100000,999999);
				}
				$uSQL=sprintf('update %s set t_mobile=%s, t_mcode=%s where uid=%s', $yjl_dbprefix.'members',
					yjl_SQLString($mobile, 'text'),
					yjl_SQLString($mcode, 'text'),
					$udb['uid']);
				$result=mysql_query($uSQL) or die('');
				if($r_main['sms_n']!='' && $r_main['sms_p']!='' && $yjl_isdebug==0){
					$msg='感谢您使用'.$r_main['site_name'].'，您的手机号验证码为：'.$mcode;
					$a_s=yjl_sendmcode(array($mobile), $msg);
					$iSQL=sprintf('insert into %s (uid, mobile, c_mob, datetime, content, sid, sinfo) values (%s, %s, 1, %s, %s, %s, %s)', $yjl_dbprefix.'rlog',
						$udb['uid'],
						yjl_SQLString($mobile, 'text'),
						time(),
						yjl_SQLString($msg, 'text'),
						yjl_SQLString($a_s[0], 'text'),
						yjl_SQLString($a_s[1], 'text'));
					$result=mysql_query($iSQL) or die(mysql_error());
				}
				echo '验证码已发送。';
				if($yjl_isdebug>0)echo '验证码：'.$mcode;
			}
			mysql_free_result($res);
		}elseif($udb['misyz']==0){
			if($r_main['sms_n']!='' && $r_main['sms_p']!='' && $yjl_isdebug==0){
				$msg='感谢您使用'.$r_main['site_name'].'，您的手机号验证码为：'.$udb['mcode'];
				$a_s=yjl_sendmcode(array($udb['mobile']), $msg);
				$iSQL=sprintf('insert into %s (uid, mobile, c_mob, datetime, content, sid, sinfo) values (%s, %s, 1, %s, %s, %s, %s)', $yjl_dbprefix.'rlog',
					$udb['uid'],
					yjl_SQLString($mobile, 'text'),
					time(),
					yjl_SQLString($msg, 'text'),
					yjl_SQLString($a_s[0], 'text'),
					yjl_SQLString($a_s[1], 'text'));
				$result=mysql_query($iSQL) or die(mysql_error());
			}
			echo '验证码已发送。';
			if($yjl_isdebug>0)echo '验证码：'.$udb['mcode'];
		}
	}else{
		echo '请输入手机号。';
	}
}
?>