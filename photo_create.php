<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
require_once('function.php');
$f='photo_create.php';

if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'login.php?u='.urlencode($f.'?xqid='.$xqid).'\';</script>';
	exit();
}
if($udb['qx']==0 && $udb['xqid']!=$xqid){
	echo '<script type="text/javascript">location.href=\''.$f.'?xqid='.$udb['xqid'].'\';</script>';
	exit();
}
if($udb['qx']==0){
	$q_res=sprintf('select xqid, jlid from %s where hzid=%s and isjs=0 order by lasttime desc limit 1', $yjl_dbprefix.'jl', $udb['uid']);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		echo '<script type="text/javascript">location.href=\'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html\';</script>';
		exit();
	}
	mysql_free_result($res);
}elseif($udb['qx']==5){
	if($xqid==0){
		echo '<script type="text/javascript">location.href=\'estate-3.html\';</script>';
		exit();
	}
}else{
	echo '<script type="text/javascript">location.href=\'./\';</script>';
	exit();
}

$page_title=($xqid>0?$xqdb['name'].' ':'').'创建项目';
$js_c='';
if($_SERVER['REQUEST_METHOD']=='POST'){
	if($udb['qx']==0){
		if($udb['xqid']>0){
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
			$iSQL=sprintf('insert into %s (hzid, hzqr, xqid, name, ys, fg, fxid, mj, datetime, lasttime) values (%s, 1, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl',
				$user_id,
				$xqid,
				yjl_SQLString($udb['nc'].'的家', 'text'),
				$ys,
				$fg,
				$fxid,
				$mj,
				time(),
				time());
			$result=mysql_query($iSQL) or die('');
			$jlid=mysql_insert_id();
			yjl_addlog('[uid]创建建立项目：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$udb['nc'].'的家</a>', md5('jlcj|'.$user_id.'|'.$user_id.'|'.$jlid));
			$uSQL=sprintf('update %s set c_jl=c_jl+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
			$result=mysql_query($uSQL) or die('');
			$app_a=yjl_app('照片式监理 '.$udb['nc'].'的家', $jlid, $yjl_url.'photo-'.$xqid.'-'.$jlid.'.html');
			$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $jlid);
			$result=mysql_query($uSQL) or die('');
			require_once('lib/jishigouapi.class.php');
			$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_a[1], $app_a[2], $udb['nickname'], md5($udb['nickname'].$udb['password']));
			$content='建立照片式监理：'.$udb['nc'].'的家';
			$jsg_result=$JishiGouAPI->AddTopic($content);
			if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
				$tid=$jsg_result['result']['tid'];
				yjl_uwb($user_id, $content, $tid);
			}
		}else{
			$xqid=intval($_POST['xqid']);
			$q_rep=sprintf('select xqid from %s where xqid=%s and iskf=1 limit 1', $yjl_dbprefix.'xq', $xqid);
			$rep=mysql_query($q_rep) or die(mysql_error());
			if(mysql_num_rows($rep)==0)$xqid=0;
			mysql_free_result($rep);
			if($xqid>0){
				$iswc=($udb['xq_0']!='' && $udb['xq_1']!='' && $udb['misyz']>0)?1:0;
				$uSQL=sprintf('update %s set xqid=%s, iswc=%s where uid=%s', $yjl_dbprefix.'members',
					$xqid,
					$iswc,
					$udb['uid']);
				$result=mysql_query($uSQL) or die('');
				$uSQL=sprintf('update %s set c_user=c_user+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
				$result=mysql_query($uSQL) or die('');
			}
		}
	}else{
		if(isset($_POST['hzuid']) && intval($_POST['hzuid'])>0){
			$hzuid=intval($_POST['hzuid']);
			if(!isset($uadb[$hzuid]))$uadb[$hzuid]=yjl_udb($hzuid);
			if($uadb[$hzuid]['uid']>0 && $uadb[$hzuid]['qx']==0 && $uadb[$hzuid]['xqid']==$xqid && $uadb[$hzuid]['iswc']==1){
				$hzqr=0;
				if($user_id!=$hzuid){
					yjl_follow($hzuid, $user_id);
					$hzqr=0;
				}else{
					$hzqr=1;
				}
				$iSQL=sprintf('insert into %s (hzid, hzqr, uid, jlqr, xqid, name, ys, fg, fxid, mj, datetime, lasttime) values (%s, %s, %s, 1, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl',
					$hzuid,
					$hzqr,
					$user_id,
					$xqid,
					yjl_SQLString($uadb[$hzuid]['nc'].'的家', 'text'),
					$uadb[$hzuid]['ys'],
					$uadb[$hzuid]['fg'],
					$uadb[$hzuid]['fxid'],
					$uadb[$hzuid]['mj'],
					time(),
					time());
				$result=mysql_query($iSQL) or die('');
				$jlid=mysql_insert_id();
				yjl_addlog('[uid]创建[luid]的监理项目：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$uadb[$hzuid]['nc'].'的家</a>', md5('jlcj|'.$hzuid.'|'.$user_id.'|'.$jlid), 1, $hzuid);
				$app_a=yjl_app('照片式监理 '.$uadb[$hzuid]['nc'].'的家', $jlid, $yjl_url.'photo-'.$xqid.'-'.$jlid.'.html');
				$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $jlid);
				$result=mysql_query($uSQL) or die('');
				require_once('lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_a[1], $app_a[2], $udb['nickname'], md5($udb['nickname'].$udb['password']));
				$content='建立照片式监理：'.$uadb[$hzuid]['nc'].'的家';
				$jsg_result=$JishiGouAPI->AddTopic($content);
				if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
					$tid=$jsg_result['result']['tid'];
					yjl_uwb($user_id, $content, $tid);
				}
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'请等待业主确认。\');</script>';
			}
		}
	}
	echo '<script type="text/javascript">location.href=\''.(isset($jlid)?'photo-'.$xqid.'-'.$jlid.'.html':$f.'?xqid='.$xqid).'\';</script>';
	exit();
}
$c='<br />
		<div class="create">
				<h2>项目创建</h2>';
if($udb['qx']==0){
	if($udb['xqid']>0){
		$fxc=yjl_fxop($udb['xqid'], $udb['fxid']);
		$c.='<form method="post" class="main_form" action=""><table>
							<tr>
								<th>预算：</th>
								<td><select name="ys"><option value="0">选择预算</option>';
		foreach($a_ys as $k=>$v)$c.='<option value="'.$k.'"'.($udb['ys']==$k?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select>';
		if($fxc[0]>0)$c.='</td></tr><tr><th>户型：</th><td>';
		$c.=$fxc[1].'</td></tr><tr><th valign="top">风格：</th><td><select name="fg"><option value="0">选择风格</option>';
		foreach($a_fg as $k=>$v)$c.='<option value="'.$k.'"'.($udb['fg']==$k?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select></td></tr>
							<tr>
								<th></th>
								<td><input type="submit" value="创 建" class="submit sub_reg" /></td>
							</tr>
						</table>';
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
		$c.='<form method="post" class="main_form" action=""><table>
							<tr>
								<th>小区：</th>
								<td><input type="text" class="text" style="background: url(images/ibg.gif) no-repeat left center;" id="chki_1"><span id="msg_1"></span><input type="hidden" id="is_er_1" value="0"/><input type="hidden" id="xqid" value="0" name="xqid"/><input type="hidden" id="xqname" value=""/></td>
							</tr>
							<tr>
								<th></th>
								<td><input type="submit" value="下一步" class="submit sub_reg" /></td>
							</tr>
						</table>';
	}
}else{
	$js_c.='
	$(\'#aform\').submit(function(){
		var hzuid=0;
		if($("input[name=\'hzuid\']").length>0){
			var isjl=0;
			$("input[name=\'hzuid\']").each(function(){
				if($(this).is(\':checked\')){
					hzuid=$(this).val();
					isjl=$(this).attr(\'jq_jl\');
				}
			});
			if(isjl==\'1\'){
				if(!confirm(\'此业主已有监理项目，确认要再创建一个？\'))return false;
			}
		}else{
			alert(\'请先选择业主。\');
			return false;
		}
	});';
	$c.='<input class="text" id="q"/> <a href="#" onclick="var c=$.trim($(\'#q\').val());if(c!=\'\')$(\'#jl_jlsdiv\').load(\'j/yzsearch.php?xqid='.$xqid.'\', {q:c});return false;">搜索</a> <a href="#"" onclick="$(\'#jl_jlsdiv\').load(\'j/yzsearch.php?xqid='.$xqid.'\');return false;">显示本小区全部业主</a><form method="post" id="aform" action=""><div style="height: 250px;padding: 5px;overflow: auto;" id="jl_jlsdiv"></div><div style="clear: both;"></div><input type="submit" value="创 建" id="submit_bt" class="submit sub_reg" />';
}
$c.='</form>
				<br /><br />
			</div>';
if($udb['qx']==0 && $udb['xqid']==0)$c.='<div id="xqs_sdiv" style="border: 1px solid #999;background: #fff;"></div><input type="hidden" id="is_showsdiv" value="0"/><input type="hidden" id="is_iblur" value="0"/>';
echo yjl_html($c, 'supervisor', '', 2);
?>