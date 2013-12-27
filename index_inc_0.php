<?php
		$page_title=$a_tsgz[$udb['qx']][$udb['gzfl']].'资料完善';
		$no_menu=1;
		$q_res=sprintf('select app_id, age, cysj from %s where uid=%s limit 1', $yjl_dbprefix.'ujl', $udb['uid']);
		$res=mysql_query($q_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			$udb['app_id']=$r_res['app_id'];
			$udb['age']=$r_res['age'];
			$udb['cysj']=$r_res['cysj'];
		}else{
			$udb['app_id']=0;
			$udb['age']=0;
			$udb['cysj']=0;
			$iSQL=sprintf('insert into %s (uid) values (%s)', $yjl_dbprefix.'ujl', $udb['uid']);
			$result=mysql_query($iSQL) or die('');
		}
		mysql_free_result($res);
		$q_res=sprintf('select uid from %s where uid=%s limit 1', $yjl_dbprefix.'uyz', $udb['uid']);
		$res=mysql_query($q_res) or die('');
		if(mysql_num_rows($res)>0){
			$dSQL=sprintf('delete from %s where uid=%s', $yjl_dbprefix.'uyz', $udb['uid']);
			$result=mysql_query($dSQL) or die('');
		}
		mysql_free_result($res);
		if($_SERVER['REQUEST_METHOD']=='POST'){
			$nc=htmlspecialchars(trim($_POST['nc']),ENT_QUOTES);
			$isnc=$nc!=''?1:0;
			if($nc!=''){
				$uSQL=sprintf('update %s set nc=%s where uid=%s', $yjl_dbprefix.'members', yjl_SQLString($nc, 'text'), $udb['uid']);
				$result=mysql_query($uSQL) or die('');
			}
			$age=(isset($_POST['age']) && intval($_POST['age'])>0)?intval($_POST['age']):0;
			$cysj=(isset($_POST['cysj']) && intval($_POST['cysj'])>0)?intval($_POST['cysj']):0;
			$mobile=htmlspecialchars(trim($_POST['mobile']),ENT_QUOTES);
			$aboutme=htmlspecialchars(trim($_POST['aboutme']),ENT_QUOTES);
			$ismyz=0;
			$an='';
			if($mobile!='' && $mobile!=$udb['mobile']){
				$q_res=sprintf('select uid from %s where mobile=%s and misyz=1 limit 1', $yjl_dbprefix.'members', yjl_SQLString($mobile, 'text'));
				$res=mysql_query($q_res) or die('');
				if(mysql_num_rows($res)>0){
					$an.='请使用其他手机号。';
				}else{
					$mcode=htmlspecialchars(trim($_POST['mcode']),ENT_QUOTES);
					if($mobile==$udb['t_mobile'] && $mcode==$udb['t_mcode']){
						$misyz=1;
						$ismyz=1;
						$an.='手机号已通过验证。';
					}else{
						$misyz=0;
						if($mobile==$udb['t_mobile'] && $udb['t_mcode']!=''){
							$mcode=$udb['t_mcode'];
						}else{
							$mcode=rand(100000,999999);
						}
						$an.='验证码错误。';
					}
					$uSQL=sprintf('update %s set mobile=%s, mcode=%s, misyz=%s, t_mobile=%s, t_mcode=%s where uid=%s', $yjl_dbprefix.'members',
						yjl_SQLString($mobile, 'text'),
						yjl_SQLString($mcode, 'text'),
						$misyz,
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die('');
					if($misyz=1){
						$uSQL=sprintf('update %s set mobile=%s, mcode=%s, t_mobile=%s, t_mcode=%s where mobile=%s and misyz=0 and uid<>%s', $yjl_dbprefix.'members',
							yjl_SQLString('', 'text'),
							yjl_SQLString('', 'text'),
							yjl_SQLString('', 'text'),
							yjl_SQLString('', 'text'),
							yjl_SQLString($mobile, 'text'),
							$udb['uid']);
						$result=mysql_query($uSQL) or die('');
					}
				}
				mysql_free_result($res);
			}elseif($mobile!='' && $udb['misyz']==0){
				$mcode=htmlspecialchars(trim($_POST['mcode']),ENT_QUOTES);
				if($mcode==$udb['mcode']){
					$uSQL=sprintf('update %s set misyz=1 where uid=%s', $yjl_dbprefix.'members', $udb['uid']);
					$result=mysql_query($uSQL) or die('');
					$uSQL=sprintf('update %s set mobile=%s, mcode=%s, t_mobile=%s, t_mcode=%s where mobile=%s and misyz=0 and uid<>%s', $yjl_dbprefix.'members',
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						yjl_SQLString('', 'text'),
						yjl_SQLString($udb['mobile'], 'text'),
						$udb['uid']);
					$result=mysql_query($uSQL) or die('');
					$ismyz=1;
					$an.='手机号已通过验证。';
				}else{
					$an.='验证码错误。';
				}
			}elseif($mobile!=''){
				$ismyz=1;
			}
			if($udb['iswc']==2 || $udb['iswc']==3){
				$iswc=3;
			}else{
				$iswc=($nc!='' && $age>0 && $cysj>0 && $ismyz>0)?1:0;
			}
			$uSQL=sprintf('update %s set iswc=%s, isnc=%s where uid=%s', $yjl_dbprefix.'members',
				$iswc,
				$isnc,
				$udb['uid']);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set validate=1, aboutme=%s where uid=%s', $dbprefix.'members', yjl_SQLString($aboutme, 'text'), $udb['uid']);
			$result=mysql_query($uSQL) or die('');
			$uSQL=sprintf('update %s set age=%s, cysj=%s where uid=%s', $yjl_dbprefix.'ujl',
				$age,
				$cysj,
				$udb['uid']);
			$result=mysql_query($uSQL) or die('');
			$gz=$a_tsgz[$udb['qx']][$udb['gzfl']];
			$uSQL=sprintf('update %s set validate_remark=%s where uid=%s', $dbprefix.'memberfields', yjl_SQLString($gz, 'text'), $udb['uid']);
			$result=mysql_query($uSQL) or die('');
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">'.($an!=''?'alert(\''.$an.'\');':'').'location.href=\'./\';</script>';
			exit();
		}
		$js_c.='
	$(\'#mob_ei\').keyup(function(){
		var om=$(this).attr(\'jq_m\');
		var c=$.trim($(this).val());
		if(om!=c && c!=\'\'){
			$(\'#yzm_bt\').show();
			$(\'#yzm_tr\').show();
			$(\'#mob_right\').hide();
		}else{
			if(c!=\'\'){
				$(\'#mob_right\').show();
			}else{
				$(\'#mob_right\').hide();
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
	});
	$(\'#aboutme_t\').focus(function(){
		if($(this).attr(\'jq_ise\')==\'1\')$(this).val(\'\');
	}).blur(function(){
		if($(this).val()!=\'\'){
			$(this).attr(\'jq_ise\', \'0\');
		}else{
			$(this).attr(\'jq_ise\', \'1\');
			$(this).val(\'一句话描述自己的特长\');
		}
	});
	$(\'#submit_bt\').click(function(){
		if($(\'#aboutme_t\').attr(\'jq_ise\')==\'1\')$(\'#aboutme_t\').val(\'\');
	});';
	$lt=(isset($_SESSION['yzm_sj']) && intval($_SESSION['yzm_sj'])>0 && $_SESSION['yzm_sj']<time() && $_SESSION['yzm_sj']>(time()-$yzm_sjjg))?($yzm_sjjg-time()+$_SESSION['yzm_sj']):0;
	if($lt>0)$js_c.='
	yzm_sj();';
		$c='<h2>'.$a_tsgz[$udb['qx']][$udb['gzfl']].'资料完善</h2>
		<div class="main clearfix">
			<form method="post" class="main_form">
				<table>
				<tbody>';
		switch($udb['iswc']){
			case 3:
				$c.='<tr><th></th><td><span class="form_tip" style="margin:0;">你的资料正在审核中</span></td></tr>';
				break;
			case 2:
				$c.='<tr><th></th><td><span class="form_tip" style="margin:0;">你提供的资料未通过审核，请更新资料</span></td></tr>';
				break;
			default:
				break;
		}
		$c.='<tr>
						<th width="100">姓名<b>*</b></th>
						<td><input type="text" class="text" name="nc" value="'.$udb['nc'].'" /></td>
					</tr>
					<tr>
						<th>手机号<b>*</b></th>
						<td><input class="text" name="mobile" value="'.$udb['mobile'].'" id="mob_ei" jq_m="'.($udb['misyz']>0?$udb['mobile']:'').'"/>&nbsp;<span id="yzm_bt"'.(($udb['misyz']>0 || $udb['mobile']=='')?' style="display: none;"':'').'><a href="#" id="yzm_bt_0"'.($lt>0?' style="display: none;"':'').'>获取验证码</a><span id="yzm_bt_1"'.($lt>0?'':' style="display: none;"').'>请等待<span id="yzm_bt_i">'.$lt.'</span>秒后再重新获取验证码</span></span><span class="error" id="mob_right"'.(($udb['misyz']>0 && $udb['mobile']!='')?'':' style="display: none;"').'><span class="form_tip"><span class="ture">正确</span></span></span></td>
					</tr>
					<tr id="yzm_tr"'.(($udb['misyz']>0 || $udb['mobile']=='')?' style="display: none;"':'').'>
						<th>验证码<b></b></th>
						<td><input type="text" class="text" name="mcode" /></td>
					</tr>
					<tr>
						<th>年龄<b>*</b></th>
						<td><input type="text" class="text" name="age" value="'.($udb['age']>0?$udb['age']:'').'" /><span class="form_tip" style="color:#666">岁</span></td>
					</tr>
					<tr>
						<th>从业时间<b>*</b></th>
						<td><input type="text" class="text" name="cysj" value="'.($udb['cysj']>0?$udb['cysj']:'').'" /><span class="form_tip" style="color:#666">年</span></td>
					</tr>
					<tr>
						<th valign="top">特长<b></b></th>
						<td><textarea name="aboutme" id="aboutme_t" jq_ise="'.($udb['aboutme']!=''?'0':'1').'">'.($udb['aboutme']!=''?$udb['aboutme']:'一句话描述自己的特长').'</textarea></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" id="submit_bt" value="下一步"/></td>
					</tr>
				</tbody>
				</table>
			</form>
		</div>';
?>