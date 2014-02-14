<?php
session_start();
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
$dtype = ' dtype in(0,1) and ';
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0]))$_GET['xqid']=$a_rg[0];
	if(isset($a_rg[1])){
		if(substr($a_rg[1], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[1], 1);
		}else{
			$_GET['id']=$a_rg[1];
		}
	}
	if(isset($a_rg[2])){
		if(substr($a_rg[2], 0, 1)=='s'){
			$s_rg=explode('_', substr($a_rg[2], 1));
			if(isset($s_rg[0]))$_GET['fxid']=$s_rg[0];
			if(isset($s_rg[1]))$_GET['s_ys']=$s_rg[1];
			if(isset($s_rg[2]))$_GET['s_fg']=$s_rg[2];
			if(isset($s_rg[3]))$_GET['s_jd']=$s_rg[3];
			if(isset($s_rg[4]))$_GET['s_he']=$s_rg[4];
		}elseif($a_rg[2]=='y' || $a_rg[2]=='j'){
			$_GET['u']=$a_rg[2];
		}elseif($a_rg[2]=='upload' || $a_rg[2]=='doc' || $a_rg[2]=='video' || $a_rg[2]=='home'){
			$_GET['t']=$a_rg[2];
		}else{
			$_GET['jpid']=$a_rg[2];
		}
	}
	if(isset($a_rg[3])){
		if($a_rg[3]=='upload'){
			$_GET['m']=$a_rg[3];
		}elseif(substr($a_rg[3], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[3], 1);
		}else{
			$_GET['clid']=$a_rg[3];
		}
	}
	if(isset($a_rg[4]) && substr($a_rg[4], 0, 1)=='p')$_GET['p']=substr($a_rg[4], 1);
}
require_once('function.php');
$f='photo.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}
if($xqid>0){
	if($user_id>0)yjl_vlog($xqid);
	$uSQL=sprintf('update %s set c_fw=c_fw+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
	$result=mysql_query($uSQL) or die();
	$page_title=$xqdb['name'].' 监理项目';
	$c_l1id=$xqdb['l1id'];
}else{
	$page_title='监理项目';
}
$c_lc=count($a_lc);
$js_c='';
$c='';
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$jlid=intval($_GET['id']);
	$q_res=sprintf('select * from %s where xqid=%s and jlid=%s limit 1', $yjl_dbprefix.'jl', $xqid, $jlid);
	$res=mysql_query($q_res) or die();
	$r_res=mysql_fetch_assoc($res);
	//hds
	$_COOKIE['isgz']?$r_res['name']=substr($r_res['name'],0,strlen($r_res['name'])-6):$r_res['name']=$r_res['name'];
	if(mysql_num_rows($res)>0){
		if($r_res['xqid']!=$xqid){
			echo '<script type="text/javascript">location.href=\'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html\';</script>';
			exit();
		}
		if($user_id>0){
			$uSQL=sprintf('update %s set c_ck=c_ck+1 where jlid=%s', $yjl_dbprefix.'jl', $jlid);
			$result=mysql_query($uSQL) or die();
		}
		$pu='images/jl_d.jpg';
		$q_reu=sprintf('select t_url from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
		$reu=mysql_query($q_reu) or die();
		$r_reu=mysql_fetch_assoc($reu);
		if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
		mysql_free_result($reu);
		$q_reu=sprintf('select t_url from %s where jlid=%s and is_del=0', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
		$reu=mysql_query($q_reu) or die();
		$c_reu=mysql_num_rows($reu);
		if($c_reu!=$r_res['c_zp']){
			$r_res['c_zp']=$c_reu;
			$uSQL=sprintf('update %s set c_zp=%s where jlid=%s', $yjl_dbprefix.'jl', $c_reu, $jlid);
			$result=mysql_query($uSQL) or die();
		}
		mysql_free_result($reu);
		if($user_id>0 && $user_id!=$r_res['hzid'])yjl_vlog($r_res['hzid'], 1);
		if(!isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
		if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
		$page_title.=' '.$r_res['name'];
		$c.='<div class="main_left">';
		if($r_res['fxid']>0){
			$q_rep=sprintf('select name, content from %s where fxid=%s limit 1', $yjl_dbprefix.'xq_fx', $r_res['fxid']);
			$rep=mysql_query($q_rep) or die();
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0)$a_jlda[]='户型：<span title="'.$r_rep['content'].'">'.$r_rep['name'].'</span>';
			mysql_free_result($rep);
		}
		if(isset($a_fg[$r_res['fg']]))$a_jlda[]='风格：'.$a_fg[$r_res['fg']];
		if(isset($a_ys[$r_res['ys']]))$a_jlda[]='预算：'.$a_ys[$r_res['ys']];
		//if(isset($a_mj[$r_res['mj']]))$a_jlda[]='面积：'.$a_mj[$r_res['mj']];
		if($r_res['hzqr']==0 || $r_res['jlqr']==0)require_once('photo_inc_0.php');
		$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
		$rep=mysql_query($q_rep) or die();
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$app_k=$r_rep['app_key'];
			$app_s=$r_rep['app_secret'];
		}else{
			$app_a=yjl_app('照片式监理 '.$r_res['name'], $jlid, $yjl_url.'photo-'.$xqid.'-'.$jlid.'.html');
			$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $jlid);
			$result=mysql_query($uSQL) or die();
			$app_k=$app_a[1];
			$app_s=$app_a[2];
		}
		mysql_free_result($rep);
		$issc=($user_id>0 && ($user_id==$r_res['uid'] || $user_id==$r_res['hzid']))?1:0;
		if(isset($_GET['t']) && $_GET['t']=='upload' && $issc>0){
			require_once('photo_inc_upload.php');
		}elseif(isset($_GET['t']) && $_GET['t']=='video'){
			require_once('photo_inc_video.php');
		}elseif(isset($_GET['t']) && $_GET['t']=='doc'){
			require_once('photo_inc_doc.php');
		}elseif(isset($_GET['t']) && $_GET['t']=='home'){
			require_once('photo_inc_home.php');
		}else{
			require_once('photo_inc_photo.php');
		}
		
		$c.='</div>
		<div class="main_right">
			<div class="box2 clearfix">
				<h2>'.$r_res['name'].'</h2>
				<div class="pic_text02 clearfix">
					<img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" />
					<p>';
		if(isset($a_jlda))$c.=join('<br/>', $a_jlda);
		$c.='</p>
				</div>
				<div class="share clearfix"><div style="float: left;" onmouseover="$(\'#fx_id\').val(\'0\');">'.yjl_fxdiv().'</div>';
		if($user_id>0 && $user_id!=$r_res['hzid'] && $user_id!=$r_res['uid']){
			$q_rep=sprintf('select uid from %s where jlid=%s and uid=%s', $yjl_dbprefix.'jl_gz', $jlid, $user_id);
			$rep=mysql_query($q_rep) or die();
			if(mysql_num_rows($rep)==0){
				if(isset($_GET['gz']) && $_GET['gz']==1){
					$iSQL=sprintf('insert into %s (uid, jlid, datetime) values (%s, %s, %s)', $yjl_dbprefix.'jl_gz',
						$user_id,
						$jlid,
						time());
					$result=mysql_query($iSQL) or die();
					$uSQL=sprintf('update %s set c_gz=c_gz+1 where jlid=%s', $yjl_dbprefix.'jl', $jlid);
					$result=mysql_query($uSQL) or die();
					if($udb['tsgz']==0){
						yjl_follow($user_id, $r_res['hzid']);
						yjl_follow($user_id, $r_res['uid']);
					}
					echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
					exit();
				}
				$c.='<div class="flt_rt"><a href="'.$f.'?xqid='.$xqid.'&amp;id='.$jlid.'&amp;gz=1" class="btn bt_smblue">关 注</a></div>';
			}
			mysql_free_result($rep);
		}
		$c.='</div>
				<ul class="perfocus">
					<li><a href="user-'.$r_res['hzid'].'.html"><img src="'.yjl_face($r_res['hzid'], $uadb[$r_res['hzid']]['face']).'" /></a>
						业主<br /><a href="user-'.$r_res['hzid'].'.html">'.$uadb[$r_res['hzid']]['nc'].'</a>
					</li>';
		if($r_res['uid']>0){
			$c.='<li><a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $uadb[$r_res['uid']]['face']).'"/></a>
						监理师<br /><a href="user-'.$r_res['uid'].'.html">'.$uadb[$r_res['uid']]['nc'].'</a>
					</li>';
		}elseif($user_id>0 && !isset($htmlfjc) && ($user_id==$r_res['hzid'] || $udb['qx']==10 || $udb['isxg']>0)){
			if(isset($_POST['is_xzjl']) && $_POST['is_xzjl']==1){
				if(isset($_POST['jluid']) && intval($_POST['jluid'])>0){
					$jluid=intval($_POST['jluid']);
					if(!isset($uadb[$jluid]))$uadb[$jluid]=yjl_udb($jluid);
					if($uadb[$jluid]['uid']>0 && $uadb[$jluid]['qx']==5 && $uadb[$jluid]['iswc']==1){
						if($udb['qx']==10 || $udb['isxg']>0){
							$jlqr=1;
						}else{
							if($user_id!=$jluid){
								yjl_follow($jluid, $user_id);
								$jlqr=0;
							}else{
								$jlqr=1;
							}
						}
						$uSQL=sprintf('update %s set uid=%s, lasttime=%s, jlqr=%s where jlid=%s', $yjl_dbprefix.'jl',
							$jluid,
							time(),
							$jlqr,
							$r_res['jlid']);
						$result=mysql_query($uSQL) or die();
						yjl_addlog('[uid]的监理项目选择监理师[luid]：<a href="photo-'.$xqid.'-'.$jlid.'.html">'.$r_res['name'].'</a>', md5('jlxzjls|'.$jluid.'|'.$r_res['hzid'].'|'.$jlid), 0, $jluid, $r_res['hzid']);
						if($jlqr==0)echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'请等待监理师确认。\');</script>';
					}
				}
				echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
				exit();
			}
			$htmlfjc='<div class="overlay" id="overlay_newct">
	<h3>设置监理师</h3>
	<div class="overlay_cont" style="height: 300px;overflow: auto;"><input class="codetext" id="q"/> <a href="#" onclick="var c=$.trim($(\'#q\').val());if(c!=\'\')$(\'#jl_jlsdiv\').load(\'j/jlsearch.php\', {q:c});return false;">搜索</a> <a href="#" onclick="$(\'#jl_jlsdiv\').load(\'j/jlsearch.php\');return false;">显示全部监理师</a><form method="post" action=""><div style="padding: 5px;height: 230px;overflow: auto;" id="jl_jlsdiv"></div><input type="submit" value="完 成" class="submit sub_reg" /><input type="hidden" name="is_xzjl" value="1"/></form></div></div>';
			$c.='<li><img src="images/no_50.gif"/>
						监理师<br /><a href="#" rel="#overlay_newct">选择监理师</a>
					</li>';
		}
		$c.='</ul><br class="clear" />';
		if($user_id>0 && ($udb['isxg']>0 || $udb['qx']==10)){
			if($r_main['d_jlid']!=$jlid){
				if(isset($_GET['mr']) && $_GET['mr']==1){
					$uSQL=sprintf('update %s set d_jlid=%s, d_jlxqid=%s', $yjl_dbprefix.'main', $jlid, $xqid);
					$result=mysql_query($uSQL) or die();
					echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
					exit();
				}
				$c.='<a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['jlid'].'&amp;mr=1" style="margin-left: 20px;">设置为首页默认项目</a><br />';
			}
			if($r_res['istj']==0){
				if(isset($_GET['tj']) && $_GET['tj']==1){
					$uSQL=sprintf('update %s set istj=1 where jlid=%s', $yjl_dbprefix.'jl', $jlid);
					$result=mysql_query($uSQL) or die();
					echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'-'.$jlid.'.html\';</script>';
					exit();
				}
				$c.='<a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['jlid'].'&amp;tj=1" style="margin-left: 20px;">设置为推荐项目</a><br />';
			}
		}
		$c.='<br />
			</div>
			<div class="per_mat">
				<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-home.html"><span class="ico38"></span><br />验房</a>
				<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-doc.html"><span class="ico39"></span><br />文档资料</a>
				<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-video.html"><span class="ico40"></span><br />监理视频</a>
			</div>'.yjl_newr_jltj(1);
		$q_rep=sprintf('select b.uid, b.face, c.nc from %s as a, %s as b, %s as c where a.uid=b.uid and a.vuid=%s and a.tid=1 and b.uid=c.uid and c.qx<10 order by a.datetime desc limit 14', $yjl_dbprefix.'vlog', $dbprefix.'members', $yjl_dbprefix.'members', $r_res['hzid']);
		$rep=mysql_query($q_rep) or die();
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$c.='<div class="box2">
				<h3>TA们刚刚来过</h3>
				<ul class="friend clearfix">';
			do{
				$c.='<li><a href="user-'.$r_rep['uid'].'.html" title="'.$r_rep['nc'].'"><img src="'.yjl_face($r_rep['uid'], $r_rep['face']).'" /><br />'.yjl_substrs($r_rep['nc'], 4).'</a></li>';
			}while($r_rep=mysql_fetch_assoc($rep));
			$c.='</ul></div>';
		}
		mysql_free_result($rep);
		$c.='</div><input type="hidden" id="jlid" value="'.$r_res['jlid'].'"/>';
	}else{
		echo '<script type="text/javascript">location.href=\'photo-'.$xqid.'.html\';</script>';
		exit();
	}
	mysql_free_result($res);
}else{
	$fxid=(isset($_GET['fxid']) && intval($_GET['fxid'])>0)?intval($_GET['fxid']):0;
	$ysid=(isset($_GET['s_ys']) && isset($a_ys[$_GET['s_ys']]))?$_GET['s_ys']:0;
	$fgid=(isset($_GET['s_fg']) && isset($a_fg[$_GET['s_fg']]))?$_GET['s_fg']:0;
	$jdid=(isset($_GET['s_jd']) && isset($a_lc[$_GET['s_jd']]))?$_GET['s_jd']:0;
	$heid=(isset($_GET['s_he']) && intval($_GET['s_he'])>0)?intval($_GET['s_he']):0;

	$iscj=0;
	if($user_id>0){
		if($udb['qx']==5){
			$iscj=1;
		}elseif($udb['qx']==0){
			if($udb['xqid']==$xqid){
				$q_res=sprintf('select hzid from %s where hzid=%s  and isjs=0 limit 1', $yjl_dbprefix.'jl', $udb['uid']);
				$res=mysql_query($q_res) or die();
				if(mysql_num_rows($res)==0)$iscj=1;
				mysql_free_result($res);
			}
		}
	}
	$c.='<div class="main_left"><h2 class="h2">小区监理项目</h2>';
	if($iscj>0)$c.='<div class="more_bt"><a href="photo_create.php?xqid='.$xqid.'" class="btn bt_nomgray">创建项目</a></div>';
	$fxc=yjl_fxop($xqid, $fxid, 1, 'var u=\'photo-'.$xqid.'-p1-s\'+$(this).val()+\'_\'+$(\'#s_ys\').val()+\'_\'+$(\'#s_fg\').val()+\'_\'+$(\'#s_jd\').val()+\'.html\';location.href=u;');
	$js_c.='
	$(\'#s_fg\').change(function(){
		var u=\'photo-'.$xqid.'-p1-s\'+$(\'#fxid\').val()+\'_\'+$(\'#s_ys\').val()+\'_\'+$(this).val()+\'_\'+$(\'#s_jd\').val()+\'.html\';
		location.href=u;
	});
	$(\'#s_ys\').change(function(){
		var u=\'photo-'.$xqid.'-p1-s\'+$(\'#fxid\').val()+\'_\'+$(this).val()+\'_\'+$(\'#s_fg\').val()+\'_\'+$(\'#s_jd\').val()+\'.html\';
		location.href=u;
	});
	$(\'#s_jd\').change(function(){
		var u=\'photo-'.$xqid.'-p1-s\'+$(\'#fxid\').val()+\'_\'+$(\'#s_ys\').val()+\'_\'+$(\'#s_fg\').val()+\'_\'+$(this).val()+\'.html\';
		location.href=u;
	});
	$(\'#s_he\').change(function(){
		var u=\'photo-'.$xqid.'-p1-s\'+0+\'_\'+0+\'_\'+0+\'_\'+0+\'_\'+$(this).val()+\'.html\';
		location.href=u;
	});';
	if(!$_COOKIE['isgz']){
		$c.='<div class="vilr_nav clearfix">
					<div class="flt_rt">
						'.$fxc[1].'<input type="hidden" id="fxid" value="'.$fxid.'"/> <select id="s_fg"><option value="0">选择风格</option>';
		foreach($a_fg as $k=>$v)$c.='<option value="'.$k.'"'.($fgid==$k?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select> <select id="s_ys"><option value="0">选择预算</option>';
		foreach($a_ys as $k=>$v)$c.='<option value="'.$k.'"'.($ysid==$k?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select> ';
		$c.='<select id="s_jd"><option value="0">选择进度</option>';
		foreach($a_lc as $k=>$v)$c.='<option value="'.$k.'"'.($jdid==$k?' selected="selected"':'').'>'.$v.'</option>';
		$c.='</select>';
		$c.='</div></div>';
	}else{
		
		$c.='<div class="vilr_nav clearfix">
			<div class="flt_rt">';
		$c.=' <select id="s_he"><option value="0">选择类型</option>';
		$c.='<option value="1"' .($heid==1?'selected="selected"':'').'>连锁店</option>';
		$c.='<option value="2"' .($heid==2?'selected="selected"':'').'>办公室</option>';
		$c.='<option value="3"' .($heid==3?'selected="selected"':'').'>实验室</option>';
		$c.='</select>';
		$c.='</div></div>';
	}
	if($fxid>0)$sdb[]='fxid='.$fxid;
	if($ysid>0)$sdb[]='ys='.$ysid;
	if($fgid>0)$sdb[]='fg='.$fgid;
	if($heid>0)$sdb[]='htype='.$heid;
	$smdb=isset($sdb)?' and '.join(' and ', $sdb):'';
	$jddb=$jdid>0?' and lid>'.$jdid:'';
	//echo $xqid;echo $jdid;die;
	if($xqid>0){
		if($jdid>0){
			$q_res=sprintf('select * from %s where xqid=%s and '.$dtype.' hzqr=1 and c_zp>4 and lid=%s%s order by lasttime desc', $yjl_dbprefix.'jl', $xqid, $jdid, $smdb);
			
			$res=mysql_query($q_res) or die();
			$r_res=mysql_fetch_assoc($res);
			if(mysql_num_rows($res)>0){
				do{
					if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
					if($r_res['hzid']>0 && !isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
					$a_res[$r_res['jlid']]=yjl_jllist($r_res);
				}while($r_res=mysql_fetch_assoc($res));
			}
			mysql_free_result($res);
		}
		$q_res=sprintf('select * from %s where xqid=%s and '.$dtype.' hzqr=1 and c_zp>4%s%s order by lasttime desc', $yjl_dbprefix.'jl', $xqid, $smdb, $jddb);
		$res=mysql_query($q_res) or die();
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			do{
				if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
				if($r_res['hzid']>0 && !isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
				$a_res[$r_res['jlid']]=yjl_jllist($r_res);
			}while($r_res=mysql_fetch_assoc($res));
		}
		mysql_free_result($res);
	}
	if($jdid>0){
		$q_res=sprintf('select * from %s where xqid<>%s and '.$dtype.' and hzqr=1 and c_zp>4 and lid=%s%s order by lasttime desc', $yjl_dbprefix.'jl', $xqid, $jdid, $smdb);
		$res=mysql_query($q_res) or die();
		$r_res=mysql_fetch_assoc($res);
		if(mysql_num_rows($res)>0){
			do{
				if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
				if($r_res['hzid']>0 && !isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
				$a_res[$r_res['jlid']]=yjl_jllist($r_res);
			}while($r_res=mysql_fetch_assoc($res));
		}
		mysql_free_result($res);
	}
	$_COOKIE['isgz']?$q_res=sprintf('select * from %s where   dtype=2 and  hzqr=1 and c_zp>4%s%s order by lasttime desc', $yjl_dbprefix.'jl', $smdb, $jddb):$q_res=sprintf('select * from %s where xqid<>%s and '.$dtype.' and  hzqr=1 and c_zp>4%s%s order by lasttime desc', $yjl_dbprefix.'jl', $xqid, $smdb, $jddb);
//echo $smdb;
//	echo $q_res;
	$res=mysql_query($q_res) or die();
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		do{
			if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
			if($r_res['hzid']>0 && !isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
			$a_res[$r_res['jlid']]=yjl_jllist($r_res);
		}while($r_res=mysql_fetch_assoc($res));
	}
	mysql_free_result($res);
	if(isset($a_res)){
		$c.='<ul class="list_vilrspr">';
		$tr_res=count($a_res);
		$p_size=10;
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$i=0;
		foreach($a_res as $v){
			if($i>=($page-1)*$p_size && $i<$page*$p_size)$c.=$v;
			$i++;
		}
		$c.='</ul>';
		if($tp_res>1)$c.=yjl_newhmpage('photo-'.$xqid.'-p[p]'.(($fxid>0 || $ysid>0 || $fgid>0 || $jdid>0)?'-s'.$fxid.'_'.$ysid.'_'.$fgid.'_'.$jdid:'').'.html', $page, $tp_res);
	}
	$c.='</div><div class="main_right">';
	if($xqid>0)$c.=yjl_newr_xq();
	$c.=yjl_newr_jlzx();
	if($xqid>0){
		$q_res=sprintf('select a.*, b.name as b_name from %s as a, %s as b where a.xqid<>%s and '.$dtype.' a.hzqr=1 and a.xqid=b.xqid and c_zp>4 order by a.lasttime desc limit 4', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $xqid);
	}else{
		$q_res=sprintf('select a.*, b.name as b_name from %s as a, %s as b where a.istj=1 and '.$dtype.' a.hzqr=1 and a.xqid=b.xqid and c_zp>4 order by a.lasttime desc limit 4', $yjl_dbprefix.'jl', $yjl_dbprefix.'xq', $xqid);
	}
	$res=mysql_query($q_res) or die();
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$c.='<div class="box2 clearfix"><h3>'.($xqid>0?'其他小区的':'推荐').'项目</h3><ul class="list_visit list_proj">';
		do{
			$pu='images/jl_d.jpg';
			$q_reu=sprintf('select * from %s where jlid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid']);
			$reu=mysql_query($q_reu) or die('yy');
			//hds
			$_COOKIE['isgz']?$r_res['name']=substr($r_res['name'],0,strlen($r_res['name'])-6):$r_res['name']=$r_res['name'];
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$pu=$r_reu['t_url'];
			mysql_free_result($reu);
			$c.='<li><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$pu.') no-repeat center;" /></a>
						<em class="percent'.$r_res['lid'].'"></em>
						<p><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html">'.$r_res['name'].'</a></p>
						<p>'.$r_res['b_name'].'</p>
					</li>';
		}while($r_res=mysql_fetch_assoc($res));
		$c.='</ul></div>';
	}
	mysql_free_result($res);
	$c.='</div>';
}
if(isset($htmlfjc))$c.=$htmlfjc;
echo yjl_html($c, 'supervisor', '', 2);
?>