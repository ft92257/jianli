<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='uploadhdimg.php';
if(isset($_POST['cookie_auth']) && trim($_POST['cookie_auth'])!=''){
	$udb=array('uid'=>0);
	$auth=str_replace(' ', '+', $_POST['cookie_auth']);
	$lk=yjl_authcode($auth, 'DECODE', $config['auth_key']);
	$ak=explode("\t", $lk);
	if(isset($ak[0]) && trim($ak[0])!='' && isset($ak[1]) && intval($ak[1])>0){
		$q_res=sprintf('select uid, nickname, password from %s where uid=%s and password=%s limit 1', $dbprefix.'members', yjl_SQLString($ak[1], 'int'), yjl_SQLString(trim($ak[0]), 'int'));
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0)$udb=$r_res;
		mysql_free_result($res);
	}
	$user_id=$udb['uid'];
}
if($udb['uid']>0){
	$id=(isset($_POST['hdid']) && intval($_POST['hdid'])>0)?intval($_POST['hdid']):1;
	$q_res=sprintf('select b.* from %s as a, %s as b where a.hdid=b.hdid and a.uid=%s and a.iscy=0 and a.hdid=%s limit 1', $yjl_dbprefix.'hd_user', $yjl_dbprefix.'hd', $user_id, $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if(isset($_FILES['Filedata']) && $_FILES['Filedata']['name']!=''){
			$url='';
			$f_i=$_FILES['Filedata'];
			if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
				$e=0;
				if($f_i['size']>$max_file*1024)$e=2;
				$f_e=explode('.', $f_i['name']);
				$f_e=strtolower($f_e[count($f_e)-1]);
				if(isset($u_ea) && !in_array($f_e, $u_ea))$e=3;
				$u_f='file/temp/';
				if(!is_dir('../'.$u_f) && is_writeable('../'.$u_f))$e=4;
				if($e==0){
					$f_m=$user_id.'_'.md5(time().rand(0,1000));
					$photo='../'.$u_f.$f_m.'.'.$f_e;
					if(@copy($f_i['tmp_name'], $photo)){
						$data=GetImageSize($photo);
						if($data && $data[2]<=3){
							$pu=$yjl_url.$u_f.$f_m.'.'.$f_e;
							$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
							$req=mysql_query($q_req) or die('');
							$r_req=mysql_fetch_assoc($req);
							if(mysql_num_rows($req)>0){
								$app_k=$r_req['app_key'];
								$app_s=$r_req['app_secret'];
							}else{
								$app_a=yjl_app('活动 '.$r_res['name'], $r_res['hdid'], $yjl_url.'active-'.$xqid.'-'.$r_res['hdid'].'.html', 'hd');
								$uSQL=sprintf('update %s set app_id=%s where hdid=%s', $yjl_dbprefix.'hd', $app_a[0], $r_res['hdid']);
								$result=mysql_query($uSQL) or die('');
								$app_k=$app_a[1];
								$app_s=$app_a[2];
							}
							mysql_free_result($req);
							require_once('../lib/jishigouapi.class.php');
							$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
							$type=$r_res['tid']>0?'both':'first';
							$jsg_result=$JishiGouAPI->AddTopic('分享图片', $r_res['tid'], $type, $pu);
							if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
								$tid=$jsg_result['result']['tid'];
								$iSQL=sprintf('insert into %s (tid, hdid, uid, datetime, content) values (%s, %s, %s, %s, %s)', $yjl_dbprefix.'hd_topic',
									$tid,
									$r_res['hdid'],
									$user_id,
									time(),
									yjl_SQLString('分享图片', 'text'));
								$result=mysql_query($iSQL) or die('');
								$uSQL=sprintf('update %s set lasttime=%s, c_wb=c_wb+1 where hdid=%s', $yjl_dbprefix.'hd', time(), $r_res['hdid']);
								$result=mysql_query($uSQL) or die('');
								yjl_addlog('[uid]上传照片到小区活动：<a href="active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html">'.$r_res['name'].'</a>', md5('plhd|'.$r_res['uid'].'|'.$user_id.'|'.$r_res['hdid']), 0, $r_res['uid']);
								yjl_uwb($user_id, '分享图片', $tid, '', $photo);
								$q_rep=sprintf('select imageid from %s where tid=%s limit 1', $dbprefix.'topic', $tid);
								$rep=mysql_query($q_rep) or die('');
								$r_rep=mysql_fetch_assoc($rep);
								if(mysql_num_rows($rep)>0){
									$imgid=$r_rep['imageid'];
									if($imgid!=''){
										if(isset($_POST['is_nu']) && $_POST['is_nu']==1){
											echo '<script type="text/javascript">window.parent.upimg_a_11(\''.$imgid.'\');window.parent.hidenuv(\'0\');window.parent.upimg_ac_3();</script>';
											exit();
										}else{
											echo $imgid;
										}
									}
								}
								mysql_free_result($rep);
							}
						}
						unlink($photo);
					}
				}
			}
		}
	}
	mysql_free_result($res);
}
if(isset($_POST['is_nu']) && $_POST['is_nu']==1)echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">window.parent.hidenuv(\'0\');alert(\'上传错误\');</script>';
?>