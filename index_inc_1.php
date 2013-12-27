<?php
		$page_title='个人资料完善';
		if($udb['xqid']>0){
			$q_res=sprintf('select * from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $udb['xqid']);
			$res=mysql_query($q_res) or die('');
			$r_res=mysql_fetch_assoc($res);
			if(mysql_num_rows($res)>0){
				$xqdb=$r_res;
			}else{
				$udb['xqid']=0;
			}
			mysql_free_result($res);
		}
		if($_SERVER['REQUEST_METHOD']=='POST'){
			$an='';
			$xq_0=htmlspecialchars(trim($_POST['xq_0']),ENT_QUOTES);
			$xq_1=htmlspecialchars(trim($_POST['xq_1']),ENT_QUOTES);
			$mobile=htmlspecialchars(trim($_POST['mobile']),ENT_QUOTES);
			$nc=htmlspecialchars(trim($_POST['nc']),ENT_QUOTES);
			if($nc=='')$nc=$udb['nc'];
			$gender=isset($_POST['gender'])?$_POST['gender']:2;
			//$isnc=($nc!='' && $xq_0!='' && $xq_1!='' && $gender>0)?1:0;
			$ismyz=0;
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
				if($mcode==$udb['mcode'] && $udb['mcode']!=''){
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
				}elseif($mcode!=$udb['mcode'] && $udb['mcode']!=''){
					$an.='验证码错误。';
				}
			}elseif($mobile!=''){
				$ismyz=1;
			}
			$iswc=($xqid>0 && $ismyz>0 && $xq_0!='' && $xq_1!='')?1:0;
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
			$uSQL=sprintf('update %s set iswc=%s, xq_0=%s, xq_1=%s where uid=%s', $yjl_dbprefix.'members',
				$iswc,
				yjl_SQLString($xq_0, 'text'),
				yjl_SQLString($xq_1, 'text'),
				$udb['uid']);
			$result=mysql_query($uSQL) or die('');
			if($nc!=''){
				$uSQL=sprintf('update %s set nc=%s where uid=%s', $yjl_dbprefix.'members',
					yjl_SQLString($nc, 'text'),
					$udb['uid']);
				$result=mysql_query($uSQL) or die('');
			}
			$uSQL=sprintf('update %s set gender=%s where uid=%s', $dbprefix.'members',
				$gender,
				$udb['uid']);
			$result=mysql_query($uSQL) or die(mysql_error());
			if($gender>0 && $gender!=$udb['gender']){
				$up=yjl_imgpath($udb['uid']);
				foreach($up as $v){
					if(!is_dir($yjl_tpath.'images/face/'.$v))mkdir($yjl_tpath.'images/face/'.$v);
				}
				$nf='images/face/'.$up[1].$udb['uid'].'_';
				$uSQL=sprintf('update %s set face=%s where uid=%s', $dbprefix.'members', yjl_SQLString('./'.$nf.'s.jpg', 'text'), $udb['uid']);
				$result=mysql_query($uSQL) or die(mysql_error());
				if(file_exists($yjl_tpath.$nf.'b.jpg'))unlink($yjl_tpath.$nf.'b.jpg');
				copy('images/dphoto_'.$gender.'_b.jpg', $yjl_tpath.$nf.'b.jpg');
				if(file_exists($yjl_tpath.$nf.'s.jpg'))unlink($yjl_tpath.$nf.'s.jpg');
				copy('images/dphoto_'.$gender.'_s.jpg', $yjl_tpath.$nf.'s.jpg');
			}
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">'.($an!=''?'alert(\''.$an.'\');':'').'location.href=\'reg_step2.php\';</script>';
			exit();
		}
		if($udb['lastactivity']==0){
			$uSQL=sprintf('update %s set lastip=%s, lastactivity=%s where uid=%s', $dbprefix.'members',
				yjl_SQLString(yjl_getIP(), 'text'),
				time(),
				$udb['uid']);
			$result=mysql_query($uSQL) or die('');
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
	});';
		$lt=(isset($_SESSION['yzm_sj']) && intval($_SESSION['yzm_sj'])>0 && $_SESSION['yzm_sj']<time() && $_SESSION['yzm_sj']>(time()-$yzm_sjjg))?($yzm_sjjg-time()+$_SESSION['yzm_sj']):0;
		if($lt>0)$js_c.='
	yzm_sj();';
		$c='<div class="regist_step">
			<div class="step current"><span>1</span>个人资料完善</div>
			<div class="step"><span>2</span>装修意向调查</div>
			<div class="right">轻松两步<br />让我们更好的为你服务</div>
		</div>
		<div class="main clearfix">
			<form method="post" class="main_form">
				<table>
				<tbody>
					<tr>
						<th width="100">昵称<b></b></th>
						<td><input type="text" class="text" name="nc" value="'.$udb['nc'].'"></td>
					</tr>
					<tr>
						<th>性别<b></b></th>
						<td><input type="radio" name="gender" value="1"'.($udb['gender']==1?' checked="checked"':'').' class="radio" id="man"><label for="man">男</label>
								<input type="radio" name="gender" value="2"'.($udb['gender']==2?' checked="checked"':'').' class="radio" id="woman"><label for="woman">女</label></td>
					</tr>
					<tr>
						<th>居住小区<b></b></th>
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
						<th>详细地址<b></b></th>
						<td><input class="text" style="width: 80px;" name="xq_0" value="'.$udb['xq_0'].'"/><span class="yjl_ft">栋/号</span><input class="text" style="width: 80px;" name="xq_1" value="'.$udb['xq_1'].'"/><span class="yjl_ft">室</span></td>
					</tr>
					<tr>
						<th>手机号<b></b></th>
						<td><input class="text" name="mobile" value="'.$udb['mobile'].'" id="mob_ei" jq_m="'.($udb['misyz']>0?$udb['mobile']:'').'"/>&nbsp;<span id="yzm_bt"'.(($udb['misyz']>0 || $udb['mobile']=='')?' style="display: none;"':'').'><a href="#" id="yzm_bt_0"'.($lt>0?' style="display: none;"':'').'>获取验证码</a><span id="yzm_bt_1"'.($lt>0?'':' style="display: none;"').'>请等待<span id="yzm_bt_i">'.$lt.'</span>秒后再重新获取验证码</span></span><span class="error" id="mob_right"'.(($udb['misyz']>0 && $udb['mobile']!='')?'':' style="display: none;"').'><span class="form_tip"><span class="ture">正确</span></span></span></td>
					</tr>
					<tr id="yzm_tr"'.(($udb['misyz']>0 || $udb['mobile']=='')?' style="display: none;"':'').'>
						<th>验证码<b></b></th>
						<td><input type="text" class="text" name="mcode" /></td>
					</tr>
					<tr>
					<td></td>
					<td><input type="submit" class="submit sub_reg" value="下一步"/></td>
					</tr>
				</tbody>
				</table>
			</form>
			<div class="jump_step"><a href="user-'.$user_id.'.html">跳过</a>以后填写，直接进入我的首页</div>
		</div>';
		if($udb['xqid']==0)$c.='<div id="xqs_sdiv" style="border: 1px solid #999;background: #fff;"></div><input type="hidden" id="is_showsdiv" value="0"/><input type="hidden" id="is_iblur" value="0"/>';
?>