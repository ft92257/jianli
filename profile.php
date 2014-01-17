<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once('function.php');
$f='profile.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php\';</script>';
	exit();
}
$cuid=$user_id;
$c='';
$js_c='';
$fj_c='';
$page_title=$udb['nc'].' ';
$d_v0='';
if(isset($_GET['privacy']) && $_GET['privacy']==1){
	$d_v0='privacy';
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$uSQL=sprintf('update %s set ys_0=%s, ys_1=%s where uid=%s', $yjl_dbprefix.'members',
			$_POST['ys_0'],
			$_POST['ys_1'],
			$udb['uid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		echo '<script type="text/javascript">location.href=\''.$f.'?'.$d_v0.'=1\';</script>';
		exit();
	}
	$c.='<div class="title"><h3>隐私设置</h3></div><br /><br />
			<div class="act_cont clearfix">
				<div class="tips"><span class="mn_ico ico19"></span>您可以控制哪些人能查看你个人主页上的内容 </div>
				<form method="post" class="main_form" action="">
				<table>
				<tbody>
					<tr>
						<th width="100">微博<b></b></th>
						<td>';
	foreach($a_privacyo as $k=>$v)$c.='<input type="radio" name="ys_0" value="'.$k.'"'.($k==$udb['ys_0']?' checked="checked"':'').' class="radio"><label>'.$v.'</label>';
	$c.='</td></tr><tr><th>关注的人<b></b></th><td>';
	foreach($a_privacyo as $k=>$v)$c.='<input type="radio" name="ys_1" value="'.$k.'"'.($k==$udb['ys_1']?' checked="checked"':'').' class="radio"><label>'.$v.'</label>';
	$c.='</td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_pre" value="保 存"/></td>
					</tr>
				</tbody>
				</table>
			</form><br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['sync']) && $_GET['sync']==1){
	$d_v0='sync';
	$c.='<div class="title"><h3>绑定账号</h3></div><br /><br />
			<div class="act_cont clearfix">
				<table>
				<tbody>';
	$sc='管理员没有开通绑定新浪微博功能'.($udb['qx']==10?'，<a href="'.$yjl_tpath.'admin.php?mod=setting&code=modify_sina">点击设置</a>':'');
	if(trim($r_main['sina_k'])!='' && trim($r_main['sina_s'])!=''){
		if(isset($_GET['lt_sina']) && $_GET['lt_sina']==1 && $udb['is_wb']!=1){
			$dSQL=sprintf('delete from %s where uid=%s', $dbprefix.'xwb_bind_info', $udb['uid']);
			$result=mysql_query($dSQL) or die(mysql_error());
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'已解除绑定新浪微博。\');location.href=\''.$f.'?sync=1\';</script>';
			exit();
		}
		require_once('lib/saetv2.ex.class.php');
		$isbd=0;
		$q_rex=sprintf('select sina_uid, access_token, profile, name, screen_name, domain, avatar_large from %s where uid=%s and length(access_token)>0 limit 1', $dbprefix.'xwb_bind_info', $udb['uid']);
		$rex=mysql_query($q_rex) or die(mysql_error());
		$r_rex=mysql_fetch_assoc($rex);
		if(mysql_num_rows($rex)>0){
			$sina_o=new SaeTClientV2($r_main['sina_k'], $r_main['sina_s'], $r_rex['access_token']);
			$sina_uid=$sina_o->get_uid();
			if(isset($sina_uid['uid']) && !isset($sina_uid['error'])){
				if(isset($_POST['sync_sina_sd']) && $_POST['sync_sina_sd']==1){
					$a_sync_sina_st['bind_setting']=(isset($_POST['sync_wb']) && $_POST['sync_wb']==1)?1:0;
					$a_sync_sina_st['synctopic_tojishigou']=$config['sina']['is_synctopic_tojishigou'];
					$a_sync_sina_st['syncreply_tojishigou']=$config['sina']['is_syncreply_tojishigou'];
					$sync_c=json_encode($a_sync_sina_st);
					$uSQL=sprintf('update %s set profile=%s where uid=%s', $dbprefix.'xwb_bind_info',
						yjl_SQLString($sync_c, 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					if($udb['qx']==10){
						if(isset($_POST['iswb']) && $_POST['iswb']==1){
							if($r_main['sina_uid']!=$sina_uid['uid']){
								$uSQL=sprintf('update %s set sina_uid=%s', $yjl_dbprefix.'main',
									yjl_SQLString($sina_uid['uid'], 'text'));
								$result=mysql_query($uSQL) or die(mysql_error());
							}
						}else{
							if($r_main['sina_uid']==$sina_uid['uid']){
								$uSQL=sprintf('update %s set sina_uid=%s', $yjl_dbprefix.'main',
									yjl_SQLString('', 'text'));
								$result=mysql_query($uSQL) or die(mysql_error());
							}
						}
					}
					echo '<script type="text/javascript">location.href=\''.$f.'?sync=1\';</script>';
					exit();
				}
				$isbd=1;
				$sina_u=$sina_o->show_user_by_id($sina_uid['uid']);
				if($r_rex['sina_uid']!=$sina_uid['uid'] || $r_rex['name']!=$sina_u['name'] || $r_rex['screen_name']!=$sina_u['screen_name'] || $r_rex['domain']!=$sina_u['domain'] || $r_rex['avatar_large']!=$sina_u['avatar_large']){
					$uSQL=sprintf('update %s set sina_uid=%s, name=%s, screen_name=%s, domain=%s, avatar_large=%s where uid=%s', $dbprefix.'xwb_bind_info',
						yjl_SQLString($sina_uid['uid'], 'text'),
						yjl_SQLString($sina_u['name'], 'text'),
						yjl_SQLString($sina_u['screen_name'], 'text'),
						yjl_SQLString($sina_u['domain'], 'text'),
						yjl_SQLString($sina_u['avatar_large'], 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
				}
				$a_sync_sina_st=json_decode($r_rex['profile'], true);
				if($r_main['sina_uid']!='' && $r_main['sina_uid']!=$sina_uid['uid']){
					$sina_ra=$sina_o->is_followed_by_id($r_main['sina_uid']);
					if(!isset($sina_ra['error_code']) && isset($sina_ra['source']['following']) && $sina_ra['source']['following']==false)$sina_o->follow_by_id($r_main['sina_uid']);
				}
				$sina_url='http://weibo.com/'.((isset($sina_u['domain']) && $sina_u['domain']!='')?$sina_u['domain']:$sina_uid['uid']);
				$sc='当前已绑定新浪微博账号:<a href="'.$sina_url.'" target="_blank">'.$sina_u['screen_name'].'</a>'.($udb['is_wb']==1?'':'（<a href="?sync=1&amp;lt_sina=1">取消绑定</a>）').'<form method="post" action="" class="main_form"><table><tr><td>'.($udb['qx']==10?'<input type="checkbox" class="radio" name="iswb" value="1"'.($r_main['sina_uid']==$sina_uid['uid']?' checked="checked"':'').'/>设置为官方微博<br/>':'').'<input type="checkbox" class="radio" name="sync_wb" value="1"'.((isset($a_sync_sina_st['bind_setting']) && $a_sync_sina_st['bind_setting']>0)?' checked="checked"':'').'/>同步新发微博</td></tr></table><input type="submit" value="设 置" class="submit sub_reg" /><input type="hidden" name="sync_sina_sd" value="1"/></form>';
			}else{
				if($udb['is_wb']!=1){
					$dSQL=sprintf('delete from %s where uid=%s', $dbprefix.'xwb_bind_info', $udb['uid']);
					$result=mysql_query($dSQL) or die(mysql_error());
				}
			}
		}
		mysql_free_result($rex);
		if($isbd==0){
			$sina_o=new SaeTOAuthV2($r_main['sina_k'], $r_main['sina_s']);
			$aurl=$sina_o->getAuthorizeURL($yjl_url.'sina_callback.php');
			$sc=($udb['is_wb']==1?'你绑定的新浪微博账号已超过授权有效期，请重新绑定':'你还未绑定新浪微博').'<br/><a href="'.$aurl.'">绑定'.($r_main['sina_uid']!=''?'并关注'.$r_main['site_name'].'官方微博':'新浪微博').'</a>';
		}
	}
	$c.='<tr><th width="100"><span style="font-weight: normal;line-height: 20px;padding: 4px 0 4px 18px;background: url(images/i-sina.gif) no-repeat left center;">新浪微博</span><b></b></th><td>'.$sc.'</td></tr>';
	$sc='管理员没有开通绑定腾讯微博功能'.($udb['qx']==10?'，<a href="'.$yjl_tpath.'admin.php?mod=setting&code=modify_qqwb">点击设置</a>':'');
	if(trim($r_main['tqq_k'])!='' && trim($r_main['tqq_s'])!=''){
		if(isset($_GET['lt_tqq']) && $_GET['lt_tqq']==1 && $udb['is_wb']!=2){
			$dSQL=sprintf('delete from %s where uid=%s', $dbprefix.'qqwb_bind_info', $udb['uid']);
			$result=mysql_query($dSQL) or die(mysql_error());
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'已解除绑定腾讯微博。\');location.href=\''.$f.'?sync=1\';</script>';
			exit();
		}
		require_once('lib/tqq_opent.php');
		$isbd=0;
		$q_rex=sprintf('select qqwb_username, token, tsecret, synctoqq from %s where uid=%s and length(token)>0 and length(tsecret)>0 limit 1', $dbprefix.'qqwb_bind_info', $udb['uid']);
		$rex=mysql_query($q_rex) or die(mysql_error());
		$r_rex=mysql_fetch_assoc($rex);
		if(mysql_num_rows($rex)>0){
			require_once('lib/tqq_client.php');
			$tqq_o=new MBApiClient($r_main['tqq_k'], $r_main['tqq_s'], $r_rex['token'], $r_rex['tsecret']);
			$tqq_a=$tqq_o->getUserInfo();
			if(isset($tqq_a['ret']) && $tqq_a['ret']==0 && isset($tqq_a['data']) && is_array($tqq_a['data'])){
				$isbd=1;
				if($r_rex['qqwb_username']!=$tqq_a['data']['name']){
					$uSQL=sprintf('update %s set qqwb_username=%s where uid=%s', $dbprefix.'qqwb_bind_info',
						yjl_SQLString($tqq_a['data']['name'], 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
				}
				if(isset($_POST['sync_tqq_sd']) && $_POST['sync_tqq_sd']==1){
					$synctoqq=(isset($_POST['sync_wb']) && $_POST['sync_wb']==1)?1:0;
					$uSQL=sprintf('update %s set synctoqq=%s where uid=%s', $dbprefix.'qqwb_bind_info',
						yjl_SQLString($synctoqq, 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					if($udb['qx']==10){
						if(isset($_POST['iswb']) && $_POST['iswb']==1){
							if($r_main['tqq_uid']!=$tqq_a['data']['name']){
								$uSQL=sprintf('update %s set tqq_uid=%s', $yjl_dbprefix.'main',
									yjl_SQLString($tqq_a['data']['name'], 'text'));
								$result=mysql_query($uSQL) or die(mysql_error());
							}
						}else{
							if($r_main['tqq_uid']==$tqq_a['data']['name']){
								$uSQL=sprintf('update %s set tqq_uid=%s', $yjl_dbprefix.'main',
									yjl_SQLString('', 'text'));
								$result=mysql_query($uSQL) or die(mysql_error());
							}
						}
					}
					echo '<script type="text/javascript">location.href=\''.$f.'?sync=1\';</script>';
					exit();
				}
				if($r_main['tqq_uid']!='' && $r_main['tqq_uid']!=$tqq_a['data']['name']){
					$a_tqqp=array('n'=>$r_main['tqq_uid'], 'type'=>1);
					$tqq_ra=$tqq_o->checkFriend($a_tqqp);
					if(isset($tqq_ra['data'][$r_main['tqq_uid']]) && $tqq_ra['data'][$r_main['tqq_uid']]==false && $tqq_ra['errcode']==0)$tqq_o->setMyidol($a_tqqp);
				}
				$tqq_url='http://t.qq.com/'.$tqq_a['data']['name'];
				$sc='当前已绑定腾讯微博账号:<a href="'.$tqq_url.'" target="_blank">'.$tqq_a['data']['nick'].'</a>'.($udb['is_wb']==2?'':'（<a href="?sync=1&amp;lt_tqq=1">取消绑定</a>）').'<form method="post" action="" class="main_form"><table><tr><td>'.($udb['qx']==10?'<input type="checkbox" class="radio" name="iswb" value="1"'.($r_main['tqq_uid']==$tqq_a['data']['name']?' checked="checked"':'').'/>设置为官方微博<br/>':'').'<input type="checkbox" class="radio" name="sync_wb" value="1"'.($r_rex['synctoqq']>0?' checked="checked"':'').'/>同步新发微博</td></tr></table><input type="submit" value="设 置" class="submit sub_reg" /><input type="hidden" name="sync_tqq_sd" value="1"/></form>';
			}else{
				if($udb['is_wb']!=2){
					$dSQL=sprintf('delete from %s where uid=%s', $dbprefix.'qqwb_bind_info', $udb['uid']);
					$result=mysql_query($dSQL) or die(mysql_error());
				}
			}
		}
		mysql_free_result($rex);
		if($isbd==0){
			$tqq_o=new MBOpenTOAuth($r_main['tqq_k'], $r_main['tqq_s']);
			$keys=$tqq_o->getRequestToken($yjl_url.'tqq_callback.php');
			$_SESSION['tqq_token']=$keys['oauth_token'];
			$_SESSION['tqq_secret']=$keys['oauth_token_secret'];
			$aurl=$tqq_o->getAuthorizeURL($keys['oauth_token'], false, '');
			$sc=($udb['is_wb']==2?'你绑定的腾讯微博账号已超过授权有效期，请重新绑定':'你还未绑定腾讯微博').'<br/><a href="'.$aurl.'">绑定'.($r_main['tqq_uid']!=''?'并关注'.$r_main['site_name'].'官方微博':'腾讯微博').'</a>';
		}
	}
	$c.='<tr><th></th><td><br/><br/><br/><br/></td></tr><tr><th><span style="font-weight: normal;line-height: 20px;padding: 4px 0 4px 18px;background: url(images/i-tqq.gif) no-repeat left center;">腾讯微博</span><b></b></th><td>'.$sc.'</td></tr>
	</tbody>
				</table><br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['photo']) && $_GET['photo']==1){
	$d_v0='photo';
	$t_face='face/'.$udb['uid'].'.jpg';
	if(!file_exists($t_face))copy(yjl_face($user_id, $udb['face'], 1), $t_face);
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if(isset($_FILES['fname']) && $_FILES['fname']['name']!=''){
			$url='';
			$f_i=$_FILES['fname'];
			if(is_uploaded_file($f_i['tmp_name']) && $f_i['error']==0){
				$e=0;
				if($f_i['size']>$max_file*1024)$e=2;
				$f_e=explode('.', $f_i['name']);
				$f_e=strtolower($f_e[count($f_e)-1]);
				if(isset($u_ea) && !in_array($f_e, $u_ea))$e=3;
				$u_f='file/temp/';
				if(!is_dir($u_f) && is_writeable($u_f))$e=4;
				if($e==0){
					$f_m=$user_id.'_'.md5(time().rand(0,1000));
					$photo=$u_f.$f_m.'.'.$f_e;
					$photo_t=$photo.'_t';
					$photo_o=$photo.'_o';
					
					if(@copy($f_i['tmp_name'], $photo_o)){
						$data=GetImageSize($photo_o);
						if($data && $data[2]<=3){
							$sw=$data[0];
							$sh=$data[1];
							$w=$a_wh_utxs[0];
							$h=$a_wh_utxs[1];
							if($sw>$w || $sh>$h){
								switch($data[2]){
									case 1:
										$im=@imagecreatefromgif($photo_o);
										break;
									case 2:
										$im=@imagecreatefromjpeg($photo_o);
										break;
									case 3:
										$im=@imagecreatefrompng($photo_o);
										break;
								}
								$xy=yjl_imgxy($sw, $sh, $w, $h);
								$ni=imagecreatetruecolor($xy[4],$xy[5]);
								imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
								imagejpeg($ni, $photo_t);
								imagedestroy($ni);
								imagedestroy($im);
							}else{
								copy($photo_o, $photo_t);
							}
							$w=$a_wh_utxb[0];
							$h=$a_wh_utxb[1];
							if($sw>$w || $sh>$h){
								switch($data[2]){
									case 1:
										$im=@imagecreatefromgif($photo_o);
										break;
									case 2:
										$im=@imagecreatefromjpeg($photo_o);
										break;
									case 3:
										$im=@imagecreatefrompng($photo_o);
										break;
								}
								$xy=yjl_imgxy($sw, $sh, $w, $h);
								$ni=imagecreatetruecolor($xy[4],$xy[5]);
								imagecopyresampled($ni,$im,$xy[0],$xy[1],0,0,$xy[2],$xy[3],$sw,$sh);
								imagejpeg($ni, $photo);
								imagedestroy($ni);
								imagedestroy($im);
							}else{
								copy($photo_o, $photo);
							}
							$up=yjl_imgpath($udb['uid']);
							foreach($up as $v){
								if(!is_dir($yjl_tpath.'images/face/'.$v))mkdir($yjl_tpath.'images/face/'.$v);
							}
							$nf='images/face/'.$up[1].$udb['uid'].'_';
							$uSQL=sprintf('update %s set face=%s where uid=%s', $dbprefix.'members', yjl_SQLString('./'.$nf.'s.jpg', 'text'), $udb['uid']);
							$result=mysql_query($uSQL) or die(mysql_error());
							if(file_exists($yjl_tpath.$nf.'b.jpg'))unlink($yjl_tpath.$nf.'b.jpg');
							if(file_exists($yjl_tpath.$nf.'s.jpg'))unlink($yjl_tpath.$nf.'s.jpg');
							copy($photo, $yjl_tpath.$nf.'b.jpg');
							copy($photo, $t_face);
							copy($photo_t, $yjl_tpath.$nf.'s.jpg');
							unlink($photo);
							unlink($photo_t);
						}else{
							$e=5;
						}
						unlink($photo_o);
					}else{
						$e=6;
					}
				}
			}
		}
		
		echo '<script type="text/javascript">location.href=\''.$f.'?photo=1&t=1\';</script>';
		exit();
	}
	$c.='<div class="title">
				<h3>修改头像</h3>
			</div>
			<div class="act_cont clearfix"><div id="face_0"'.((isset($_GET['t']) && $_GET['t']==1)?' style="display: none;"':'').'><iframe width="580" height="490" src="face/" frameborder="0"></iframe><br/>如果您不能上传图片，可以<a href="#" onclick="$(\'#face_0\').hide();$(\'#face_1\').show();return false;">点击这里</a>尝试普通模式上传</div><div id="face_1" style="'.((isset($_GET['t']) && $_GET['t']==1)?'':'display: none;').'margin-top: 40px;"><form method="post" enctype="multipart/form-data" action=""><input type="file" name="fname"/><input type="submit" value="上传"/><br/>如果您不能上传图片，可以<a href="#" onclick="$(\'#face_1\').hide();$(\'#face_0\').show();return false;">点击这里</a>尝试极速模式上传<br/>允许类型：'.join('、', $u_ea).'，最大：'.$max_file.'KB</form><br/><br/><b>当前头像</b><table width="300"><tr><td align="center"><img src="'.yjl_face($user_id, $udb['face'], 1).'?'.time().'" alt="" width="'.$a_wh_utxb[0].'" height="'.$a_wh_utxb[1].'" style="border: 1px solid #999;"/><td><td align="center"><img src="'.yjl_face($user_id, $udb['face']).'?'.time().'" alt="" width="'.$a_wh_utxs[0].'" height="'.$a_wh_utxs[1].'" style="border: 1px solid #999;"/><td></tr></table></div><br/><br/></div>';
}elseif(isset($_GET['password']) && $_GET['password']==1){
	//跳转到新版
	//header("Location:http://" . $_SERVER['HTTP_HOST'] . "/jianliapp/index.php?s=/User/password");
	//die;

	$d_v0='password';
	if($_SERVER['REQUEST_METHOD']=='POST'){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if($udb['is_wb']>0){
			if(isset($_POST['email']) && trim($_POST['email'])!='' && isset($_POST['npwd']) && trim($_POST['npwd'])!='' && isset($_POST['cpwd']) && trim($_POST['cpwd'])==trim($_POST['npwd'])){
				$email=htmlspecialchars(trim($_POST['email']),ENT_QUOTES);
				if(yjl_cemail($email)){
					$em_c=0;
					$q_res=sprintf('select uid from %s where email=%s limit 1', $dbprefix.'members', yjl_SQLString($email, 'text'));
					$res=mysql_query($q_res) or die(mysql_error());
					if(mysql_num_rows($res)>0)$em_c=1;
					mysql_free_result($res);
					if($em_c==0){
						$email_code=$udb['email_code'];
						if($email!=$udb['email_ls'])$email_code=md5(time().'|'.rand(0,9999).'|'.$email);
						$uSQL=sprintf('update %s set email_ls=%s, email_code=%s where uid=%s', $yjl_dbprefix.'members', yjl_SQLString($email, 'text'), yjl_SQLString($email_code, 'text'), $user_id);
						$result=mysql_query($uSQL) or die(mysql_error());
						if($yjl_isdebug==0){
							require_once('lib/smtp.php');
							$ec="您好：
您收到这封邮件，是因为在“".$r_main['site_name']."”网站的帐号设置中使用了该邮箱地址。

如果您没有进行上述操作，请忽略这封邮件。您不需要退订或进行其他进一步的操作。
------------------------------------------------------
邮箱验证说明：
为避免垃圾邮件或您的邮箱地址被滥用，我们需要对您的地址有效性进行验证。
您只需点击下面的链接即可激活您的帐号，并享有真正会员权限：
".$yjl_url."verify_email.php?key=".$email_code."

(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)

感谢您的访问，祝您使用愉快！

此致，
".$r_main['site_name']." 管理团队.
".$yjl_url."
";
							yjl_mail($email, $r_main['site_name'].'邮箱地址验证', $ec);
						}
						$npwd=htmlspecialchars(trim($_POST['npwd']),ENT_QUOTES);
						$password=md5($npwd);
						$uSQL=sprintf('update %s set password=%s where uid=%s', $dbprefix.'members', yjl_SQLString($password, 'text'), $user_id);
						$result=mysql_query($uSQL) or die(mysql_error());
						$aac=yjl_authcode("{$password}\t{$user_id}", 'ENCODE', $config['auth_key']);
						setcookie($config['cookie_prefix'].'auth', $aac, time()+365*86400);
						if($udb['qx']==10){
							if(!isset($config['safe_key']))$config['safe_key']='';
							$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
							$aac=yjl_authcode("{$password}\t{$user_id}", 'ENCODE', $ajhAuthKey);
							setcookie($config['cookie_prefix'].'ajhAuth', $aac, time()+365*86400);
						}
						echo '<script type="text/javascript">alert(\'邮箱和密码已修改，请查收验证邮件，经过验证后才可以使用邮箱和密码登录。\');</script>';
					}else{
						echo '<script type="text/javascript">alert(\'请使用其他的邮箱。\');</script>';
					}
				}else{
					echo '<script type="text/javascript">alert(\'邮箱格式错误。\');</script>';
				}
			}elseif(!isset($_POST['cpwd']) || trim($_POST['cpwd'])!=trim($_POST['npwd'])){
				echo '<script type="text/javascript">alert(\'确认密码必须和密码一样。\');</script>';
			}else{
				echo '<script type="text/javascript">alert(\'请输入邮箱和密码。\');</script>';
			}
		}else{
			if(isset($_POST['opwd']) && trim($_POST['opwd'])!='' && isset($_POST['npwd']) && trim($_POST['npwd'])!='' && isset($_POST['cpwd']) && trim($_POST['cpwd'])==trim($_POST['npwd'])){
				$opwd=htmlspecialchars(trim($_POST['opwd']),ENT_QUOTES);
				$npwd=htmlspecialchars(trim($_POST['npwd']),ENT_QUOTES);
				if(md5($opwd)==$udb['password']){
					$password=md5($npwd);
					$uSQL=sprintf('update %s set password=%s where uid=%s', $dbprefix.'members', yjl_SQLString($password, 'text'), $user_id);
					$result=mysql_query($uSQL) or die(mysql_error());
					$aac=yjl_authcode("{$password}\t{$user_id}", 'ENCODE', $config['auth_key']);
					setcookie($config['cookie_prefix'].'auth', $aac, time()+365*86400);
					if($udb['qx']==10){
						if(!isset($config['safe_key']))$config['safe_key']='';
						$ajhAuthKey=md5($config['auth_key'].$_SERVER['HTTP_USER_AGENT'].'_IN_ADMIN_PANEL_'.date('Y-m-Y-m').'_'.$config['safe_key']);
						$aac=yjl_authcode("{$password}\t{$user_id}", 'ENCODE', $ajhAuthKey);
						setcookie($config['cookie_prefix'].'ajhAuth', $aac, time()+365*86400);
					}
					echo '<script type="text/javascript">alert(\'密码已修改。\');</script>';
				}else{
					echo '<script type="text/javascript">alert(\'当前密码错误。\');</script>';
				}
			}elseif(!isset($_POST['cpwd']) || trim($_POST['cpwd'])!=trim($_POST['npwd'])){
				echo '<script type="text/javascript">alert(\'确认密码必须和新密码一样。\');</script>';
			}else{
				echo '<script type="text/javascript">alert(\'请输入当前密码和新密码。\');</script>';
			}
		}
		echo '<script type="text/javascript">location.href=\''.$f.'?'.$d_v0.'=1\';</script>';
		exit();
	}
	$c.='<div class="title"><h3>帐号设置</h3></div><br /><br />
			<div class="act_cont clearfix">';
	if($udb['is_wb']>0){
		$c.='<div class="tips">您目前使用';
		switch($udb['is_wb']){
			case 2:
				$c.='腾讯微博';
				break;
			case 1:
				$c.='新浪微博';
				break;
		}
		$c.='账号登录易监理，您也可以设置邮箱和密码登录</div>';
		$c.='<form method="post" class="main_form" action="" name="form1" onsubmit="if(document.form1.email.value==\'\' || document.form1.npwd.value==\'\'){alert(\'请输入邮箱和密码。\');return false;}else if(document.form1.npwd.value!=document.form1.cpwd.value){alert(\'确认密码必须和密码一样。\');return false;}">
				<table>
				<tbody>
					<tr>
						<th width="100">邮箱<b></b></th>
						<td><input type="text" class="text" name="email" value="'.$udb['email_ls'].'" />'.($yjl_isdebug>0 && $udb['email_code']!=''?'<a href="verify_email.php?key='.$udb['email_code'].'">点击验证</a>':'').'</td>
					</tr>
					<tr>
						<th width="100">密码<b></b></th>
						<td><input type="password" class="text" name="npwd" id="npwd" /></td>
					</tr>';
	}else{
		$c.='<form method="post" class="main_form" action="" name="form1" onsubmit="if(document.form1.opwd.value==\'\' || document.form1.npwd.value==\'\'){alert(\'请输入当前密码和新密码。\');return false;}else if(document.form1.npwd.value!=document.form1.cpwd.value){alert(\'确认密码必须和新密码一样。\');return false;}">
				<table>
				<tbody>
					<tr>
						<th width="100">注册邮箱<b></b></th>
						<td>'.$udb['email'].'</td>
					</tr>
					<tr>
						<th width="100">当前密码<b></b></th>
						<td><input type="password" class="text" name="opwd" /></td>
					</tr>
					<tr>
						<th width="100">新密码<b></b></th>
						<td><input type="password" class="text" name="npwd" id="npwd" /></td>
					</tr>';
	}
	$c.='<tr>
						<th width="100">确认密码<b></b></th>
						<td><input type="password" class="text" name="cpwd" id="cpwd" /></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_pre" value="保 存"/></td>
					</tr>
				</tbody>
				</table>
			</form><br class="clear" /><br /><br /></div>';
}elseif(isset($_GET['fix']) && $_GET['fix']==1 && $udb['qx']==0){
	$d_v0='fix';
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$mj=$udb['mj'];
		$ys=$_POST['ys'];
		$fg=$_POST['fg'];
		$fxid=isset($_POST['fxid'])?$_POST['fxid']:0;
		$uSQL=sprintf('update %s set fxid=%s, mj=%s, ys=%s, fg=%s where uid=%s', $yjl_dbprefix.'members',
			$fxid,
			$mj,
			$ys,
			$fg,
			$udb['uid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		$uSQL=sprintf('update %s set fxid=%s, mj=%s, ys=%s, fg=%s where hzid=%s', $yjl_dbprefix.'jl',
			$fxid,
			$mj,
			$ys,
			$fg,
			$udb['uid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'装修意向已修改\');location.href=\''.$f.'?'.$d_v0.'=1\';</script>';
		exit();
	}
	$fxc=yjl_fxop($udb['xqid'], $udb['fxid']);
	$c.='<div class="title"><h3>装修意向</h3></div><br /><br />
			<div class="act_cont clearfix">
				<div class="tips"><span class="mn_ico ico19"></span>通过装修意向调查可以让我们更好的为你服务，推荐给你真正适合的信息</div>
				<form method="post" class="main_form" action="">
				<table class="tgray">
				<tbody>
					<tr>
						<th width="100">预算<b></b></th>
						<td><select name="ys"><option value="0">选择预算</option>';
	foreach($a_ys as $k=>$v)$c.='<option value="'.$k.'"'.($udb['ys']==$k?' selected="selected"':'').'>'.$v.'</option>';
	$c.='</select>';
	if($fxc[0]>0)$c.='</td></tr><tr><th>户型<b></b></th><td>';
	$c.=$fxc[1].'</td></tr><tr><th valign="top">风格<b></b></th><td><ul class="list_style">';
	foreach($a_fg as $k=>$v)$c.='<li><input type="radio" name="fg" value="'.$k.'" class="radio" id="style'.$k.'"'.($udb['fg']==$k?' checked="checked"':'').'><label for="style'.$k.'">'.$v.'</label></li>';
	$c.='</ul></td></tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="保 存"/></td>
					</tr>
				</tbody>
				</table>
			</form><br class="clear" /><br /><br /></div>';
}else{
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$an='';
		$nc=htmlspecialchars(trim($_POST['nc']),ENT_QUOTES);
		if($nc=='')$nc=$udb['nc'];
		$gender=isset($_POST['gender'])?$_POST['gender']:0;
		$aboutme=htmlspecialchars(trim($_POST['aboutme']),ENT_QUOTES);
		$iswc=1;
		if($udb['qx']==5 || $udb['qx']==6){
			$age=(isset($_POST['age']) && intval($_POST['age'])>0)?intval($_POST['age']):0;
			$cysj=(isset($_POST['cysj']) && intval($_POST['cysj'])>0)?intval($_POST['cysj']):0;
			$iswc=($age>0 && $cysj>0 && $udb['misyz']>0)?1:0;
			$uSQL=sprintf('update %s set age=%s, cysj=%s where uid=%s', $yjl_dbprefix.'ujl',
				$age,
				$cysj,
				$udb['uid']);
			$result=mysql_query($uSQL) or die(mysql_error());
		}
		if($udb['qx']==0){
			$xq_0=htmlspecialchars(trim($_POST['xq_0']),ENT_QUOTES);
			$xq_1=htmlspecialchars(trim($_POST['xq_1']),ENT_QUOTES);
			if($xq_0=='')$xq_0=$udb['xq_0'];
			if($xq_1=='')$xq_1=$udb['xq_1'];
			$bday=$_POST['bir_y'].'-'.$_POST['bir_m'].'-'.$_POST['bir_d'];
			$zip=htmlspecialchars(trim($_POST['zip']),ENT_QUOTES);
			$zsname=htmlspecialchars(trim($_POST['zsname']),ENT_QUOTES);
			$uSQL=sprintf('update %s set validate_true_name=%s where uid=%s', $dbprefix.'memberfields',
				yjl_SQLString($zsname, 'text'),
				$udb['uid']);
			$result=mysql_query($uSQL) or die(mysql_error());
			if($udb['xqid']==0){
				$xqid=intval($_POST['xqid']);
				$q_rep=sprintf('select xqid from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)==0)$xqid=0;
				mysql_free_result($rep);
				if($xqid>0){
					$uSQL=sprintf('update %s set xqid=%s where uid=%s', $yjl_dbprefix.'members',
						$xqid,
						$udb['uid']);
					$result=mysql_query($uSQL) or die('');
					$uSQL=sprintf('update %s set c_user=c_user+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
					$result=mysql_query($uSQL) or die('');
				}
			}
			$iswc=($xqid>0 && $xq_0!='' && $xq_1!='' && $udb['misyz']>0)?1:0;
		}else{
			$xq_0=$udb['xq_0'];
			$xq_1=$udb['xq_1'];
			$bday=$udb['bday'];
			$mj=$udb['mj'];
			$ys=$udb['ys'];
			$fg=$udb['fg'];
			$zip=$udb['postcode'];
		}
		$mobile=htmlspecialchars(trim($_POST['mobile']),ENT_QUOTES);
		if($mobile=='')$mobile=$udb['mobile'];
		$qq=htmlspecialchars(trim($_POST['qq']),ENT_QUOTES);
		$msn=htmlspecialchars(trim($_POST['msn']),ENT_QUOTES);
		$uSQL=sprintf('update %s set gender=%s, bday=%s, aboutme=%s, qq=%s, msn=%s where uid=%s', $dbprefix.'members',
			$gender,
			yjl_SQLString($bday, 'text'),
			yjl_SQLString($aboutme, 'text'),
			yjl_SQLString($qq, 'text'),
			yjl_SQLString($msn, 'text'),
			$udb['uid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		$ys_0_name=isset($_POST['ys_0_name'])?$_POST['ys_0_name']:0;
		$ys_0_xq=isset($_POST['ys_0_xq'])?$_POST['ys_0_xq']:0;
		$tsgz=(isset($_POST['tsgz']) && $_POST['tsgz']==1)?0:1;
		$uSQL=sprintf('update %s set nc=%s, xq_0=%s, xq_1=%s, ys_0_name=%s, ys_0_xq=%s, iswc=%s, tsgz=%s, postcode=%s, ys_0_mob=%s, ys_0_qq=%s, ys_0_msn=%s where uid=%s', $yjl_dbprefix.'members',
			yjl_SQLString($nc, 'text'),
			yjl_SQLString($xq_0, 'text'),
			yjl_SQLString($xq_1, 'text'),
			$ys_0_name,
			$ys_0_xq,
			$iswc,
			$tsgz,
			yjl_SQLString($zip, 'text'),
			$_POST['ys_0_mob'],
			$_POST['ys_0_qq'],
			$_POST['ys_0_msn'],
			$udb['uid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		$an='资料已修改。';
		$uSQL=sprintf('update %s set name=%s where hzid=%s', $yjl_dbprefix.'jl',
			yjl_SQLString($nc.'的家', 'text'),
			$udb['uid']);
		$result=mysql_query($uSQL) or die(mysql_error());
		if($gender>0 && $udb['face']==''){
			$up=yjl_imgpath($udb['uid']);
			foreach($up as $v){
				if(!is_dir($yjl_tpath.'images/face/'.$v))mkdir($yjl_tpath.'images/face/'.$v);
			}
			$nf='images/face/'.$up[1].$udb['uid'].'_';
			$uSQL=sprintf('update %s set face=%s where uid=%s', $dbprefix.'members', yjl_SQLString('./'.$nf.'s.jpg', 'text'), $udb['uid']);
			$result=mysql_query($uSQL) or die(mysql_error());
			if(file_exists($yjl_tpath.$nf.'b.jpg'))unlink($yjl_tpath.$nf.'b.jpg');
			copy('images/dphoto_'.$gender.'_b.jpg', $yjl_tpath.$nf.'b.jpg');
			if(file_exists('face/'.$udb['uid'].'.jpg'))unlink('face/'.$udb['uid'].'.jpg');
			copy('images/dphoto_'.$gender.'_b.jpg', 'face/'.$udb['uid'].'.jpg');
			if(file_exists($yjl_tpath.$nf.'s.jpg'))unlink($yjl_tpath.$nf.'s.jpg');
			copy('images/dphoto_'.$gender.'_s.jpg', $yjl_tpath.$nf.'s.jpg');
		}
		if($nc!=$udb['nc'] && ($udb['qx']==5 || ($udb['qx']==6 && $udb['gzfl']==0))){
			$q_res=sprintf('select app_id from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
			$res=mysql_query($q_res) or die(mysql_error());
			$r_res=mysql_fetch_assoc($res);
			if(mysql_num_rows($res)>0){
				$gz=$a_tsgz[$udb['qx']][$udb['gzfl']];
				$uSQL=sprintf('update %s set app_name=%s, app_desc=%s where id=%s', $dbprefix.'app', yjl_SQLString('点评'.$gz.$nc, 'text'), yjl_SQLString('点评'.$gz.$nc, 'text'), $r_res['app_id']);
				$result=mysql_query($uSQL) or die(mysql_error());
			}
			mysql_free_result($res);
		}
		if($mobile!=''){
			if($mobile!=$udb['mobile']){
				$q_rep=sprintf('select uid from %s where mobile=%s and misyz=1 limit 1', $yjl_dbprefix.'members', yjl_SQLString($mobile, 'text'));
				$rep=mysql_query($q_rep) or die(mysql_error());
				if(mysql_num_rows($rep)>0){
					$an='请使用其他手机号。';
				}else{
					$mcode=htmlspecialchars(trim($_POST['mcode']),ENT_QUOTES);
					if($mobile==$udb['t_mobile'] && $mcode==$udb['t_mcode']){
						$misyz=1;
						$an='资料已修改。';
					}else{
						$misyz=0;
						if($mobile==$udb['t_mobile'] && $udb['t_mcode']!=''){
							$mcode=$udb['t_mcode'];
						}else{
							$mcode=rand(100000,999999);
						}
						$an='验证码错误。';
					}
					$iswc=1;
					if($udb['qx']==5 || $udb['qx']==6){
						$q_res=sprintf('select age, cysj from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
						$res=mysql_query($q_res) or die(mysql_error());
						$r_res=mysql_fetch_assoc($res);
						if(mysql_num_rows($res)>0){
							$udb['age']=$r_res['age'];
							$udb['cysj']=$r_res['cysj'];
						}
						mysql_free_result($res);
						$iswc=($udb['age']>0 && $udb['cysj']>0 && $misyz>0)?1:0;
					}elseif($udb['qx']==0){
						$iswc=($udb['xqid']>0 && $misyz>0 && $udb['xq_0']!='' && $udb['xq_1']!='')?1:0;
					}
					$uSQL=sprintf('update %s set mobile=%s, mcode=%s, misyz=%s, iswc=%s, t_mobile=%s, t_mcode=%s where uid=%s', $yjl_dbprefix.'members',
						yjl_SQLString($mobile, 'text'),
						yjl_SQLString($mcode, 'text'),
						$misyz,
						$iswc,
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					if($misyz=1){
						$uSQL=sprintf('update %s set mobile=%s, mcode=%s, t_mobile=%s, t_mcode=%s where mobile=%s and misyz=0 and uid<>%s', $yjl_dbprefix.'members',
							yjl_SQLString('', 'text'),
							yjl_SQLString('', 'text'),
							yjl_SQLString('', 'text'),
							yjl_SQLString('', 'text'),
							yjl_SQLString($mobile, 'text'),
							$udb['uid']);
						$result=mysql_query($uSQL) or die(mysql_error());
					}
				}
				mysql_free_result($rep);
			}elseif($udb['misyz']==0){
				$mcode=htmlspecialchars(trim($_POST['mcode']),ENT_QUOTES);
				if($mcode==$udb['mcode']){
					$iswc=1;
					if($udb['qx']==5 || $udb['qx']==6){
						$q_res=sprintf('select age, cysj from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
						$res=mysql_query($q_res) or die(mysql_error());
						$r_res=mysql_fetch_assoc($res);
						if(mysql_num_rows($res)>0){
							$udb['age']=$r_res['age'];
							$udb['cysj']=$r_res['cysj'];
						}
						mysql_free_result($res);
						$iswc=($udb['age']>0 && $udb['cysj']>0)?1:0;
					}elseif($udb['qx']==0){
						$iswc=($udb['xqid']>0 && $udb['xq_0']!='' && $udb['xq_1']!='')?1:0;
					}
					$uSQL=sprintf('update %s set misyz=1, iswc=%s where uid=%s', $yjl_dbprefix.'members',
						$iswc,
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					$uSQL=sprintf('update %s set mobile=%s, mcode=%s, t_mobile=%s, t_mcode=%s where mobile=%s and misyz=0 and uid<>%s', $yjl_dbprefix.'members',
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						yjl_SQLString($udb['mobile'], 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die(mysql_error());
					$an='资料已修改。';
				}else{
					$an='验证码错误。';
				}
			}
		}
		if(!isset($an) || $an=='')$an='资料已修改。';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">'.($an!=''?'alert(\''.$an.'\');':'').'location.href=\''.$f.'\';</script>';
		exit();
	}
	$c.='<div class="title"><h3>个人信息</h3></div><br /><br />
			<div class="act_cont clearfix">
				<div class="mem_inf">';
	if($udb['qx']==10){
		$c.='管理员';
	}elseif($udb['qx']==5 || $udb['qx']==6){
		$c.=$a_tsgz[$udb['qx']][$udb['gzfl']];
	}else{
		$c.='业主';
	}
	$c.='<br />会员ID：'.$udb['uid'].'<br />注册时间：'.date('Y-m-d H:i', $udb['regdate']).'</div>
				<form method="post" class="main_form">
					<table>
						<tr>
							<th width="70">'.(($udb['qx']==5 || $udb['qx']==6)?'真实姓名':'昵称').'<b>*</b></th>
							<td><input type="text" class="text" name="nc" value="'.$udb['nc'].'" /></td>
							<td colspan="9"></td>
						</tr>
						<tr>
							<th>性别<b></b></th>
							<td><input type="radio" name="gender" value="1"'.($udb['gender']==1?' checked="checked"':'').' class="radio" id="man"><label for="man">男</label>
								<input type="radio" name="gender" value="2"'.($udb['gender']==2?' checked="checked"':'').' class="radio" id="woman"><label for="woman">女</label>
								<input type="radio" name="gender" value="0"'.($udb['gender']==0?' checked="checked"':'').' class="radio" id="secret"><label for="secret">保密</label></td>
						</tr>';
	if($udb['qx']==5 || $udb['qx']==6){
		$q_res=sprintf('select age, cysj from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$udb['age']=$r_res['age'];
			$udb['cysj']=$r_res['cysj'];
		}
		mysql_free_result($res);
		$c.='<tr>
							<th>年龄<b></b></th>
							<td><input type="text" class="text" name="age" value="'.($udb['age']>0?$udb['age']:'').'" />岁</td>
						</tr>
						<tr>
							<th>从业时间<b></b></th>
							<td><input type="text" class="text" name="cysj" value="'.($udb['cysj']>0?$udb['cysj']:'').'" />年</td>
						</tr>';
	}elseif($udb['qx']==0){
		$q_res=sprintf('select validate_true_name from %s where uid=%s limit 1', $dbprefix.'memberfields', $udb['uid']);
		$res=mysql_query($q_res) or die(mysql_error());
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$udb['zsname']=$r_res['validate_true_name'];
		}
		mysql_free_result($res);
		$c.='<tr>
							<th>真实姓名<b></b></th>
							<td><input type="text" class="text" name="zsname" value="'.$udb['zsname'].'" /><select name="ys_0_name" style="margin-left: 5px;">';
		foreach($a_privacyo as $k=>$v)$c.='<option value="'.$k.'"'.($k==$udb['ys_0_name']?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select></td>
						</tr>
						<tr>
							<th>生日<b></b></th>
							<td><select name="bir_y"><option value="0000"></option>';
		$bir_a=explode('-', $udb['bday']);
		for($i=1920;$i<=date('Y');$i++)$c.='<option value="'.$i.'"'.($i==$bir_a[0]?' selected="selected"':'').'>'.$i.'</option>';
		$c.='</select><span class="yjl_ft">年</span><select name="bir_m"><option value="00"></option>';
		for($i=1;$i<=12;$i++)$c.='<option value="'.str_pad($i, 2, '0', STR_PAD_LEFT).'"'.(str_pad($i, 2, '0', STR_PAD_LEFT)==$bir_a[1]?' selected="selected"':'').'>'.$i.'</option>';
		$c.='</select><span class="yjl_ft">月</span><select name="bir_d"><option value="00"></option>';
		for($i=1;$i<=31;$i++)$c.='<option value="'.str_pad($i, 2, '0', STR_PAD_LEFT).'"'.(str_pad($i, 2, '0', STR_PAD_LEFT)==$bir_a[2]?' selected="selected"':'').'>'.$i.'</option>';
		$c.='</select><span class="yjl_ft">日</span></td>
						</tr>
						<tr>
							<th>居住小区<b>*</b></th>
							<td>';
		if($xqid>0){
			$c.=$xqdb['name'];
		}else{
			$js_c.='
	$(\'#chki_1\').blur(function(){
		if($.trim($(this).val())==\'\'){
			$(this).css(\'background-image\', \'url(images/ibg.gif)\');
		}else{
			$(this).css(\'background-image\', \'\');
		}
		$(\'#is_iblur\').val(\'0\');
		if($(\'#is_showsdiv\').val()==0){
			var c=$.trim($(\'#chki_1\').val());
			if(c!=\'\'){
				if($(\'#xqid\').val()==0){
					$(\'#msg_1\').html(\'\');
				}else{
					$(\'#msg_1\').html(\'\');
				}
			}else{
				$(\'#msg_1\').html(\'\');
			}
		}
		if($(\'#xqid\').val()>0)showermsg(\'\', 1);
	}).focus(function(){
		$(this).css(\'background-image\', \'\');
		$(\'#msg_1\').html(\'\');
		home_search_xq(\''.$d_l1id.'\');
	}).keyup(function(){
		home_search_xq(\''.$d_l1id.'\');
	});
	$(\'#xqs_sdiv\').click(function(){
		if($(\'#sr_c\').length>0){
			setTimeout("$(\'#is_showsdiv\').val(\'1\');",100);
		}else{
			$(\'#is_showsdiv\').val(\'0\');
		}
	});
	$(document).click(function(){
		$(\'#is_showsdiv\').val(\'0\');
		setTimeout("if($(\'#is_showsdiv\').val()==\'0\' && $(\'#sr_c\').length>0){$(\'#xqs_sdiv\').hide();if($(\'#is_iblur\').val()==\'0\')$(\'#chki_1\').blur();}",101);
	});';
			$c.='<input type="text" class="text" style="background: url(images/ibg.gif) no-repeat left center;" id="chki_1"><span id="msg_1"></span><input type="hidden" id="is_er_1" value="0"/><input type="hidden" id="xqid" value="0" name="xqid"/><input type="hidden" id="xqname" value=""/>';
		}
		$c.='</td>
						</tr>
						<tr>
							<th>详细地址<b>*</b></th>
							<td><input class="text" style="width: 80px;" name="xq_0" value="'.$udb['xq_0'].'"/><span class="yjl_ft">栋/号</span><input class="text" style="width: 80px;" name="xq_1" value="'.$udb['xq_1'].'"/><span class="yjl_ft">室</span><select name="ys_0_xq" style="margin-left: 5px;">';
		foreach($a_privacyo as $k=>$v)$c.='<option value="'.$k.'"'.($k==$udb['ys_0_xq']?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select></td>
						</tr>
						<tr>
							<th>邮编<b></b></th>
							<td><input type="text" class="text" name="zip" value="'.$udb['postcode'].'"></td>
						</tr>';
	}
	$js_c.='
	$(\'#mob_ei\').keyup(function(){
		var om=$(this).attr(\'jq_m\');
		var c=$.trim($(this).val());
		if(om!=c && c!=\'\'){
			$(\'#yzm_bt\').show();
			$(\'#yzm_tr\').show();
			$(\'#mob_right\').hide();
			$(\'#mob_ys\').hide();
		}else{
			if(c!=\'\'){
				$(\'#mob_right\').show();
				$(\'#mob_ys\').show();
			}else{
				$(\'#mob_right\').hide();
				$(\'#mob_ys\').hide();
			}
			$(\'#yzm_bt\').hide();
			$(\'#yzm_tr\').hide();
		}
	});
	$(\'#yzm_bt_0\').click(function(){
		var om=$(\'#mob_ei\').attr(\'jq_m\');
		var c=$.trim($(\'#mob_ei\').val());
		if(om!=c && c!=\'\'){
			$.get(\'j/chkmob.php\', {m:c}, function(data){
				if(data!=\'\')alert(data);
			})
		}
		$(\'#yzm_bt_0\').hide();
		$(\'#yzm_bt_1\').show();
		$(\'#yzm_bt_i\').html(\''.$yzm_sjjg.'\');
		yzm_sj();
	});';
	$lt=(isset($_SESSION['yzm_sj']) && intval($_SESSION['yzm_sj'])>0 && $_SESSION['yzm_sj']<time() && $_SESSION['yzm_sj']>(time()-$yzm_sjjg))?($yzm_sjjg-time()+$_SESSION['yzm_sj']):0;
	if($lt>0)$js_c.='
	yzm_sj();';
	$c.='<tr>
							<th>手机号<b></b></th>
							<td><input class="text" name="mobile" value="'.$udb['mobile'].'" id="mob_ei" jq_m="'.($udb['misyz']>0?$udb['mobile']:'').'"/><select name="ys_0_mob" id="mob_ys" style="margin-left: 5px;'.(($udb['misyz']>0 && $udb['mobile']!='')?'':'display: none;').'">';
	foreach($a_privacyo as $k=>$v)$c.='<option value="'.$k.'"'.($k==$udb['ys_0_mob']?' selected="selected"':'').'>'.$v.'</option>';
	$c.='</select>&nbsp;<span id="yzm_bt"'.(($udb['misyz']>0 || $udb['mobile']=='')?' style="display: none;"':'').'><a href="#" id="yzm_bt_0"'.($lt>0?' style="display: none;"':'').'>获取验证码</a><span id="yzm_bt_1"'.($lt>0?'':' style="display: none;"').'>请等待<span id="yzm_bt_i">'.$lt.'</span>秒后再重新获取验证码</span></span><span class="error" id="mob_right"'.(($udb['misyz']>0 && $udb['mobile']!='')?'':' style="display: none;"').'><span class="form_tip"><span class="ture">正确</span></span></span></td>
						</tr>
						<tr id="yzm_tr"'.(($udb['misyz']>0 || $udb['mobile']=='')?' style="display: none;"':'').'>
							<th>验证码<b></b></th>
							<td><input type="text" class="text" name="mcode" /></td>
						</tr>
						<tr>
							<th>QQ<b></b></th>
							<td><input type="text" class="text" name="qq" value="'.$udb['qq'].'"><select name="ys_0_qq" style="margin-left: 5px;">';
	foreach($a_privacyo as $k=>$v)$c.='<option value="'.$k.'"'.($k==$udb['ys_0_qq']?' selected="selected"':'').'>'.$v.'</option>';
	$c.='</select></td>
						</tr>
						<tr>
							<th>MSN<b></b></th>
							<td><input type="text" class="text" name="msn" value="'.$udb['msn'].'"><select name="ys_0_msn" style="margin-left: 5px;">';
	foreach($a_privacyo as $k=>$v)$c.='<option value="'.$k.'"'.($k==$udb['ys_0_msn']?' selected="selected"':'').'>'.$v.'</option>';
	$c.='</select></td>
						</tr>
						<tr>
							<th valign="top">'.(($udb['qx']==5 || $udb['qx']==6)?'特长':'自我介绍').'<b></b></th>
							<td><textarea name="aboutme">'.$udb['aboutme'].'</textarea></td>
						</tr>
						</tr>
						<tr>
							<th><b></b></th>
							<td><input type="checkbox" name="tsgz" class="radio" value="1"'.($udb['tsgz']>0?'':' checked="checked"').'/>关注照片式监理同时关注业主</td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" value="保 存" class="submit sub_reg" /></td>
						</tr>
					</table>
				</form><br class="clear" /><br /><br /></div>';
	if($udb['qx']==0 && $udb['xqid']==0)$c.='<div id="xqs_sdiv" style="border: 1px solid #999;background: #fff;"></div><input type="hidden" id="is_showsdiv" value="0"/><input type="hidden" id="is_iblur" value="0"/>';
}
$a_lelist[]=array('', '个人信息');
if($udb['qx']==0)$a_lelist[]=array('fix', '装修意向');
$a_lelist[]=array('photo', '修改头像');
$a_lelist[]=array('privacy', '隐私设置');
$a_lelist[]=array('sync', '绑定账号');
$a_lelist[]=array('password', '帐号设置');

$l ='<h2>设置</h2><ul class="list_centnav hover">';
foreach($a_lelist as $v){
	$l.='<li'.($d_v0==$v[0]?' class="current"':'').'><a href="'.($v[0]!=''?'?'.$v[0].'=1':$f).'">'.$v[1].'</a></li>';
	if($d_v0==$v[0]){
		$page_t=$v[1];
		$page_title.=$v[1];
	}
}
$l.='</ul>';

echo yjl_gehtml($l, $c, $page_t);
?>