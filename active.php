<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
require_once($yjl_tpath.'setting/face.php');
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
		if($a_rg[2]=='edit' || $a_rg[2]=='sponsor'){
			$_GET['t']=$a_rg[2];
		}elseif(substr($a_rg[2], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[2], 1);
		}
	}
}
require_once('function.php');
$f='active.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}
if($xqid>0){
	if($user_id>0)yjl_vlog($xqid);
	$uSQL=sprintf('update %s set c_fw=c_fw+1 where xqid=%s', $yjl_dbprefix.'xq', $xqid);
	$result=mysql_query($uSQL) or die('');
	$page_title=$xqdb['name'].' 小区活动';
	$c_l1id=$xqdb['l1id'];
}else{
	$page_title='小区活动';
}
$js_c='';
$c='';
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$hdid=intval($_GET['id']);
	$q_res=sprintf('select * from %s where hdid=%s', $yjl_dbprefix.'hd', $hdid);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		if($r_res['xqid']>0 && $r_res['xqid']!=$xqid){
			echo '<script type="text/javascript">location.href=\'active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html\';</script>';
			exit();
		}
		if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
		if($r_res['jluid']>0 && !isset($uadb[$r_res['jluid']]))$uadb[$r_res['jluid']]=yjl_udb($r_res['jluid']);
		$page_title.=' '.$r_res['name'];
		$iscy=0;
		$ispz=0;
		if($user_id>0){
			$q_rep=sprintf('select iscy from %s where uid=%s and hdid=%s limit 1', $yjl_dbprefix.'hd_user', $user_id, $hdid);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				if($r_rep['iscy']==0){
					$iscy=1;
				}else{
					$ispz=1;
				}
			}
			mysql_free_result($rep);
		}
		$c.='<div class="main_left"><div class="box1 clearfix">';
		$isym=0;
		if(isset($_GET['t']) && $_GET['t']=='sponsor' && $user_id>0 && ($udb['isxg']>0 || $udb['qx']==10)){
			require_once('party_inc_0.php');
		}elseif(isset($_GET['t']) && $_GET['t']=='edit' && $user_id>0 && ($user_id==$r_res['uid'] || $udb['isxg']>0 || $udb['qx']==10)){
			require_once('party_inc_1.php');
		}else{
			$isym=1;
			if($r_res['isgf']>0)$c.='<div class="hot_pic off">官方活动</div>';
			$c.='<div class="pic_text2 act_tit">
					<a href="active-'.$xqid.'-'.$hdid.'.html"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: url(images/pe_d.jpg) no-repeat center;').'" /></a>
					<h2>'.$r_res['name'].'</h2>
					<p>时间：'.yjl_hd_date($r_res).'<br />
					地点：'.($r_res['xqid']>0?'<a href="square-'.$xqid.'.html">'.$xqdb['name'].'</a>':'').$r_res['address'].'<p>
					<div class="bt_act"><a href="#" onclick="$(this).load(\'j/zan.php?id='.$r_res['hdid'].'\');return false;"><span class="mn_ico gd"></span><br />'.$r_res['c_zan'].'</a><a href="#" onclick="$(this).load(\'j/zan.php?id='.$r_res['hdid'].'&t=1\');return false;"><span class="mn_ico bd"></span><br />'.$r_res['c_zan1'].'</a></div>
					<div class="bt_active">';
			if($user_id>0){
				$isgz=0;
				$q_rep=sprintf('select uid from %s where hdid=%s and uid=%s', $yjl_dbprefix.'hd_fuser', $r_res['hdid'], $user_id);
				$rep=mysql_query($q_rep) or die('');
				if(mysql_num_rows($rep)>0)$isgz=1;
				mysql_free_result($rep);
				$c.='<div class="bt_active">';
				if($isgz==0){
					if(isset($_GET['gz']) && $_GET['gz']==1){
						$iSQL=sprintf('insert into %s (uid, hdid, datetime) values (%s, %s, %s)', $yjl_dbprefix.'hd_fuser',
							$user_id,
							$hdid,
							time());
						$result=mysql_query($iSQL) or die('');
						$uSQL=sprintf('update %s set c_gz=c_gz+1 where hdid=%s', $yjl_dbprefix.'hd', $hdid);
						$result=mysql_query($uSQL) or die('');
						yjl_addlog('[uid]关注活动：<a href="active-'.$xqid.'-'.$hdid.'.html">'.$r_res['name'].'</a>', md5('hdgz|'.$r_res['uid'].'|'.$user_id.'|'.$hdid), 0, $r_res['uid'], $user_id);
						echo '<script type="text/javascript">location.href=\'active-'.$xqid.'-'.$hdid.'.html\';</script>';
						exit();
					}
					$c.='<a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;gz=1">我感兴趣('.$r_res['c_gz'].')</a>';
				}
			}else{
				$c.='<a href="login.php?u='.urlencode('active-'.$r_res['xqid'].'-'.$r_res['hdid'].'.html').'" rel="#overlay_login">我感兴趣('.$r_res['c_gz'].')</a>';
			}
			if($r_res['isxzrs']==0 || $r_res['c_cy']<$r_res['xzrs']){
				if($user_id==0 || $udb['xqid']==$xqid || $r_res['xqid']==0 || $udb['qx']>0){
					if($user_id==0 || ($iscy==0 && $ispz==0)){
						if(isset($_POST['iscj']) && $_POST['iscj']==1){
							echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">';
							if(isset($_POST['name']) && trim($_POST['name'])!='' && isset($_POST['lxfs']) && trim($_POST['lxfs'])!='' && isset($_POST['email']) && trim($_POST['email'])!='' && isset($_POST['xqname']) && trim($_POST['xqname'])!=''){
								$name=htmlspecialchars(trim($_POST['name']),ENT_QUOTES);
								$lxfs=htmlspecialchars(trim($_POST['lxfs']),ENT_QUOTES);
								$email=htmlspecialchars(trim($_POST['email']),ENT_QUOTES);
								$xqid=$user_id>0?$udb['xqid']:0;
								$xqname=htmlspecialchars(trim($_POST['xqname']),ENT_QUOTES);
								$iscy=$r_res['isxzrs']>0?1:0;
								$iSQL=sprintf('insert into %s (uid, hdid, name, lxfs, email, xqid, xqname, iscy, datetime) values (%s, %s, %s, %s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'hd_user',
									$user_id,
									$hdid,
									yjl_SQLString($name, 'text'),
									yjl_SQLString($lxfs, 'text'),
									yjl_SQLString($email, 'text'),
									$xqid,
									yjl_SQLString($xqname, 'text'),
									$iscy,
									time());
								$result=mysql_query($iSQL) or die('');
								if($iscy>0){
									if($user_id>0){
										yjl_addlog('[uid]申请参加活动：<a href="active-'.$xqid.'-'.$hdid.'.html">'.$r_res['name'].'</a>', md5('hdcj|'.$r_res['uid'].'|'.$user_id.'|'.$hdid), 1, $r_res['uid'], $user_id);
									}else{
										yjl_addlog($name.'申请参加活动：<a href="active-'.$xqid.'-'.$hdid.'.html">'.$r_res['name'].'</a>', md5('hdcj|'.$r_res['uid'].'|'.time().'|'.$hdid), 1, $r_res['uid']);
									}
									echo 'alert(\'已申请参加，请等待审核。\');';
								}else{
									$uSQL=sprintf('update %s set c_cy=c_cy+1, lasttime=%s where hdid=%s', $yjl_dbprefix.'hd', time(), $r_res['hdid']);
									$result=mysql_query($uSQL) or die('');
									if($user_id>0){
										yjl_addlog('[uid]参加活动：<a href="active-'.$xqid.'-'.$hdid.'.html">'.$r_res['name'].'</a>', md5('hdcj|'.$r_res['uid'].'|'.$user_id.'|'.$hdid), 0, $r_res['uid'], $user_id);
									}else{
										yjl_addlog($name.'参加活动：<a href="active-'.$xqid.'-'.$hdid.'.html">'.$r_res['name'].'</a>', md5('hdcj|'.$r_res['uid'].'|'.time().'|'.$hdid), 0, $r_res['uid']);
									}
									echo 'alert(\'祝贺您已报名成功，请您注意活动时间，欢迎您准时参加！\');';
								}
							}else{
								echo 'alert(\'请输入姓名、联系方式、邮箱、小区名。\');';
							}
							echo 'location.href=\'active-'.$xqid.'-'.$hdid.'.html\';</script>';
							exit();
						}
						if(isset($_GET['j']) && $_GET['j']==1)$js_c.='
	$(\'#cj_link\').click();
	$(\'.close\').click(function(){
		location.href=\'active-'.$xqid.'-'.$hdid.'.html\';
	});';
						$c.='<a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;j=1" rel="#overlay_newct" id="cj_link">我要参加('.$r_res['c_cy'].')</a>';
						$xqname='';
						if($user_id>0 && $udb['xqid']>0){
							if($udb['xqid']==$xqid && $xqid>0){
								$xqname=$xqdb['name'];
							}else{
								$q_reu=sprintf('select name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', $uadb[$cuid]['xqid']);
								$reu=mysql_query($q_reu) or die('');
								$r_reu=mysql_fetch_assoc($reu);
								if(mysql_num_rows($reu)>0)$xqname=$r_reu['name'];
								mysql_free_result($reu);
							}
						}
						$htmlfj='<div class="overlay" id="overlay_newct">
		<h3>参加活动</h3>
		<div class="overlay_cont">
			<form method="post" class="main_form" name="form1" action="" onsubmit="if(document.form1.name.value==\'\' || document.form1.lxfs.value==\'\' || document.form1.email.value==\'\' || document.form1.xqname.value==\'\'){alert(\'请输入姓名、联系方式、邮箱、小区名。\');return false;}">
				<table>
				<tr>
					<th width="50">姓名</th>
					<td><input type="text" class="text" name="name" value="'.($user_id>0?$udb['nc']:'').'" /><span class="form_tip"><b>*</b></span></td>
				</tr>
				<tr>
					<th>联系方式</th>
					<td><input type="text" class="text" name="lxfs" value="'.($user_id>0?$udb['mobile']:'').'" /><span class="form_tip"><b>*</b></span></td>
				</tr>
				<tr>
					<th>邮箱</th>
					<td><input type="text" class="text" name="email" value="'.($user_id>0?$udb['email']:'').'" /><span class="form_tip"><b>*</b></span></td>
				</tr>
				<tr>
					<th>小区名</th>
					<td><input type="text" class="text" name="xqname" value="'.$xqname.'"'.(($user_id>0 && $udb['xqid']>0)?' readonly="readonly"':'').' /><span class="form_tip"><b>*</b></span></td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" value="参 加" class="submit sub_reg" /><input type="hidden" name="iscj" value="1"/></td>
				</tr>
			</table>
			</form>
		</div></div>';
					}
				}
			}
			if($user_id>0)$c.='</div>';
			$c.='</div>
					<div class="share">
					</div>';
			if($user_id>0 && ($user_id==$r_res['uid'] || $udb['isxg']>0 || $udb['qx']==10))$c.='<br/><a href="active-'.$xqid.'-'.$hdid.'-edit.html">修改详情</a> <a href="#" rel="#overlay_usercy">查看活动成员</a>'.($r_res['isxzrs']>0?' <a href="#" rel="#overlay_newct">审核用户</a>':'');
			if($user_id>0 && ($udb['isxg']>0 || $udb['qx']==10)){
				$c.=' <a href="active-'.$xqid.'-'.$hdid.'-sponsor.html">赞助商信息修改</a>';
				if($r_main['d_hdid']!=$hdid){
					if(isset($_GET['mr']) && $_GET['mr']==1){
						$uSQL=sprintf('update %s set d_hdid=%s, d_hdxqid=%s', $yjl_dbprefix.'main', $hdid, $r_res['xqid']);
						$result=mysql_query($uSQL) or die('');
						echo '<script type="text/javascript">location.href=\'active-'.$xqid.'-'.$hdid.'.html\';</script>';
						exit();
					}
					$c.=' <a href="'.$f.'?xqid='.$r_res['xqid'].'&amp;id='.$r_res['hdid'].'&amp;mr=1">设置为首页默认活动</a>';
				}
			}
			$c.='</div><div class="active_cnt clearfix">';
			$q_rep=sprintf('select a.id from %s as a, %s as b where a.tid=b.tid and b.hdid=%s order by a.dateline desc', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', $hdid);
			$a_rep=mysql_query($q_rep) or die('');
			$tr_rep=mysql_num_rows($a_rep);
			if($tr_rep>0){
				$c.='<p class="tit">活动照片<span>( <a href="activeimg-'.$xqid.'-'.$hdid.'.html"'.($user_id>0?'':' rel="#overlay_login"').'>全部 '.$tr_rep.' 张</a>)</span></p><div class="act_pic">';
				$q_l_rep=sprintf('%s limit 6', $q_rep);
				$rep=mysql_query($q_l_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				do{
					$up=yjl_imgpath($r_rep['id']);
					$c.='<a href="activeimg-'.$xqid.'-'.$hdid.'-'.$r_rep['id'].'.html"'.($user_id>0?'':' rel="#overlay_login"').'><img src="'.$yjl_tpath.'images/topic/'.$up[1].$r_rep['id'].'_s.jpg" /></a>';
				}while($r_rep=mysql_fetch_assoc($rep));
				mysql_free_result($rep);
				$c.='</div>';
			}
			mysql_free_result($a_rep);
			$c.='<div class="act_detl">
						<p class="stit">活动详情</p>
						<p>'.$r_res['content'].'</p>
					</div>
				</div>';
			if($iscy>0){
				$isupimg=1;
				$js_a='upimg_a_0(response);';
				$js_ac='upimg_ac_0();';
				$js_c.=yjl_uploadjs($js_a, $js_ac);
				$c.='<div class="broadcast">
					<table>
						<tr>
							<td><textarea style="padding: 5px;" id="content"></textarea></td>
						</tr>
						<tr>
							<td><input type="submit" value="广 播" class="submit sub_smbe" id="submit_fb" onclick="posthdwb(\''.$r_res['hdid'].'\');" /></td>
						</tr>
					</table>
					<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a></div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
				</div>';
			}
			$c.='<div id="hdtopic_'.$hdid.'">';
			$ispl=$iscy>0?0:1;
			$q_rep=sprintf('select b.* from %s as a, %s as b where a.tid=b.tid and a.hdid=%s order by a.datetime desc', $yjl_dbprefix.'hd_topic', $dbprefix.'topic', $hdid);
			$a_rep=mysql_query($q_rep) or die('');
			$tr_rep=mysql_num_rows($a_rep);
			if($tr_rep>0){
				$tp_rep=ceil($tr_rep/$p_size);
				if($page>$tp_rep)$page=$tp_rep;
				$q_l_rep=sprintf('%s limit %d, %d', $q_rep, ($page-1)*$p_size, $p_size);
				$rep=mysql_query($q_l_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				$c.='<ul class="list_comment">';
				do{
					if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
					$c.=yjl_newwb($r_rep, $ispl, $r_res['tid']);
				}while($r_rep=mysql_fetch_assoc($rep));
				mysql_free_result($rep);
				$c.='</ul>';
				if($tp_rep>1)$c.=yjl_newhmpage('active-'.$xqid.'-'.$hdid.'-p[p].html', $page, $tp_rep, 'hdtopic_'.$hdid, 1);
			}
			mysql_free_result($a_rep);
			$c.='</div>';
		}
		$c.='<br /><br />
			</div>
		</div>
		<div class="main_right">
			<div class="box2">
				<h4>'.($r_res['iszz']>0?'赞助商':'发起人').'</h4>
				<div class="pic_text clearfix">';
		if($r_res['xqid']>0){
			if($xqdb['l1id']>0){
				$q_rep=sprintf('select name from %s where level=1 and id=%s limit 1', $dbprefix.'common_district', $xqdb['l1id']);
				$rep=mysql_query($q_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0)$a_dz[]=$r_rep['name'];
				mysql_free_result($rep);
			}
			if($xqdb['l2id']>0){
				$q_rep=sprintf('select name from %s where level=2 and id=%s limit 1', $dbprefix.'common_district', $xqdb['l2id']);
				$rep=mysql_query($q_rep) or die('');
				$r_rep=mysql_fetch_assoc($rep);
				if(mysql_num_rows($rep)>0){
					$a_dz[]=$r_rep['name'];
					$a_mu[]=$r_rep['name'];
				}
				mysql_free_result($rep);
			}
			$a_mu[]=$xqdb['name'];
		}elseif($r_res['address']!=''){
			$a_mu[]=$r_res['address'];
		}
		if($r_res['iszz']>0){
			$zz_url=$r_res['zz_url']!=''?$r_res['zz_url']:'?xqid='.$xqid.'&amp;id='.$hdid;
			$c.='<a href="'.$zz_url.'" target="_blank"><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: #fff url(images/jl_d.jpg) no-repeat center;').'" /></a><p><strong><a href="'.$zz_url.'" target="_blank">'.$r_res['zz_name'].'</a></strong></p>';
			if($r_res['zz_url']!='')$c.='<br /><a href="'.$r_res['zz_url'].'" target="_blank">进入网站</a>';
		}else{
			$c.='<a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $uadb[$r_res['uid']]['face']).'" /></a>
					<p class="memb"><a href="user-'.$r_res['uid'].'.html">'.$uadb[$r_res['uid']]['nc'].'</a></p>';
			if($uadb[$r_res['uid']]['xqid']==$xqid && $xqid>0)$c.='<p class="memb">'.(isset($a_dz)?join(' ', $a_dz):'&nbsp;').'</p><a href="square-'.$xqid.'.html">'.$xqdb['name'].'</a>';
		}
		$c.='</div>
				<br />
			</div>';
		if(isset($a_mu)){
			$map_q=($r_res['xqid']>0 && $xqdb['map_q']!='')?$xqdb['map_q']:join('，', $a_mu);
			$c.='<div class="map">
				<h3>'.($r_res['xqid']>0?'小区':'活动').'位置</h3>
				<div class="map_cnt">
					<a href="http://maps.baidu.com/?newmap=1&amp;s='.urlencode('con&wd='.join(',', $a_mu)).'" target="_blank"><img src="http://api.map.baidu.com/staticimage?center='.urlencode($map_q).'&amp;markers='.urlencode($map_q).'&amp;width=250&amp;height=200&amp;zoom=16" /></a>
				</div>
			</div>';
		}
		if($r_res['iszz']>0 && $r_res['zz_adimg']!='')$c.='<div class="map">
			<a href="'.$zz_url.'" target="_blank"><img src="'.$r_res['zz_adimg'].'" width="'.$a_wh_hdadw.'"/></a>
			</div>';
		$q_rep=sprintf('select b.uid, b.face, c.nc from %s as a, %s as b, %s as c where a.hdid=%s and a.iscy=0 and a.uid=b.uid and b.uid=c.uid order by a.datetime, b.uid', $yjl_dbprefix.'hd_user', $dbprefix.'members', $yjl_dbprefix.'members', $hdid);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		if(mysql_num_rows($rep)>0){
			$c.='<div class="box2">
				<h3>活动成员（'.$r_res['c_cy'].'人参加·'.$r_res['c_gz'].'人感兴趣）</h3>
				<ul class="friend clearfix">';
			do{
				$c.='<li><a href="user-'.$r_rep['uid'].'.html" title="'.$r_rep['nc'].'"><img src="'.yjl_face($r_rep['uid'], $r_rep['face']).'" /><br />'.yjl_substrs($r_rep['nc'], 4).'</a></li>';
			}while($r_rep=mysql_fetch_assoc($rep));
			$c.='</ul></div>';
		}
		mysql_free_result($rep);
		$c.='</div>';
		if($isym>0 && $user_id>0 && ($user_id==$r_res['uid'] || $udb['isxg']>0 || $udb['qx']==10)){
			$c.='<div class="overlay" id="overlay_newct">
	<h3>审核用户</h3>
	<div class="overlay_cont" style="height: 300px;overflow: auto;">';
			$q_rep=sprintf('select huid, name, lxfs, email, xqname, uid from %s where hdid=%s and iscy=1 order by datetime, uid', $yjl_dbprefix.'hd_user', $hdid);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			$c_rep=mysql_num_rows($rep);
			$c.='<span id="nomsg_v"'.($c_rep>0?' style="display: none;"':'').'>没有待审核用户<input type="hidden" id="c_dsh" value="'.$c_rep.'"/></span>';
			if(mysql_num_rows($rep)>0){
				do{
					if($r_rep['uid']>0){
						if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
						if($uadb[$r_rep['uid']]['uid']==0)$r_rep['uid']=0;
					}
					$xqname=$r_rep['xqname'];
					if($r_rep['uid']>0 && $uadb[$r_rep['uid']]['xqid']>0){
						if(!isset($a_xqname[$uadb[$r_rep['uid']]['xqid']])){
							if($uadb[$r_rep['uid']]['xqid']==$xqid && $xqid>0){
								$a_xqname[$uadb[$r_rep['uid']]['xqid']]=$xqdb['name'];
							}else{
								$q_reu=sprintf('select name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', $uadb[$r_rep['uid']]['xqid']);
								$reu=mysql_query($q_reu) or die('');
								$r_reu=mysql_fetch_assoc($reu);
								if(mysql_num_rows($reu)>0)$a_xqname[$uadb[$r_rep['uid']]['xqid']]=$r_reu['name'];
								mysql_free_result($reu);
							}
						}
						$xqname=$a_xqname[$uadb[$r_rep['uid']]['xqid']];
					}
					$c.='<div style="border-bottom: 1px dotted #eee;padding-top: 10px;padding-bottom: 10px;" id="shu_'.$r_rep['huid'].'"><div style="width: 80px;text-align: center;float: left;">'.($r_rep['uid']>0?'<a href="user-'.$r_rep['uid'].'.html" title="'.$uadb[$r_rep['uid']]['nc'].'"><img src="'.yjl_face($r_rep['uid'], $uadb[$r_rep['uid']]['face']).'" width="50" height="50" /><br />'.yjl_substrs($uadb[$r_rep['uid']]['nc'], 4).'</a>':'<img src="images/no_50.gif" width="50" height="50" /><br />未注册').'</div><div style="width: 240px;float: left;"><div style="padding: 5px;">姓　　名：'.$r_rep['name'].'<br/>联系方式：'.$r_rep['lxfs'].'<br/>邮　　箱：'.$r_rep['email'].'<br/>小&nbsp;区&nbsp;名：'.$xqname.'</div><a href="#" onclick="shyh(\'hd\', \''.$hdid.'\', \''.$r_rep['huid'].'\');return false;">通过审核</a> | <a href="#" onclick="shyh(\'hd\', \''.$hdid.'\', \''.$r_rep['huid'].'\', 1);return false;">取消审核</a></div><div style="clear: both;"></div></div>';
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
			$c.='</div></div>';
			$c.='<div class="overlay" id="overlay_usercy">
	<h3>活动成员</h3>
	<div class="overlay_cont" style="height: 300px;overflow: auto;">';
			$q_rep=sprintf('select huid, name, lxfs, email, xqname, uid from %s where hdid=%s and iscy=0 order by datetime desc, uid', $yjl_dbprefix.'hd_user', $hdid);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			$c_rep=mysql_num_rows($rep);
			if(mysql_num_rows($rep)>0){
				do{
					if($r_rep['uid']>0){
						if(!isset($uadb[$r_rep['uid']]))$uadb[$r_rep['uid']]=yjl_udb($r_rep['uid']);
						if($uadb[$r_rep['uid']]['uid']==0)$r_rep['uid']=0;
					}
					$xqname=$r_rep['xqname'];
					if($r_rep['uid']>0 && $uadb[$r_rep['uid']]['xqid']>0){
						if(!isset($a_xqname[$uadb[$r_rep['uid']]['xqid']])){
							if($uadb[$r_rep['uid']]['xqid']==$xqid && $xqid>0){
								$a_xqname[$uadb[$r_rep['uid']]['xqid']]=$xqdb['name'];
							}else{
								$q_reu=sprintf('select name from %s where xqid=%s limit 1', $yjl_dbprefix.'xq', $uadb[$r_rep['uid']]['xqid']);
								$reu=mysql_query($q_reu) or die('');
								$r_reu=mysql_fetch_assoc($reu);
								if(mysql_num_rows($reu)>0)$a_xqname[$uadb[$r_rep['uid']]['xqid']]=$r_reu['name'];
								mysql_free_result($reu);
							}
						}
						$xqname=$a_xqname[$uadb[$r_rep['uid']]['xqid']];
					}
					$c.='<div style="border-bottom: 1px dotted #eee;padding-top: 10px;padding-bottom: 10px;" id="shu_'.$r_rep['huid'].'"><div style="width: 80px;text-align: center;float: left;">'.($r_rep['uid']>0?'<a href="user-'.$r_rep['uid'].'.html" title="'.$uadb[$r_rep['uid']]['nc'].'"><img src="'.yjl_face($r_rep['uid'], $uadb[$r_rep['uid']]['face']).'" width="50" height="50" /><br />'.yjl_substrs($uadb[$r_rep['uid']]['nc'], 4).'</a>':'<img src="images/no_50.gif" width="50" height="50" /><br />未注册').'</div><div style="width: 240px;float: left;"><div style="padding: 5px;">'.($r_rep['uid']==$r_res['uid']?'发起人':'姓　　名：'.$r_rep['name'].'<br/>联系方式：'.$r_rep['lxfs'].'<br/>邮　　箱：'.$r_rep['email'].'<br/>小&nbsp;区&nbsp;名：'.$xqname).'</div></div><div style="clear: both;"></div></div>';
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
			$c.='</div></div>';
		}
		if(isset($htmlfj))$c.=$htmlfj;
	}else{
		echo '<script type="text/javascript">location.href=\'active-'.$xqid.'.html\';</script>';
		exit();
	}
	mysql_free_result($res);
}else{
	require_once('party_inc_2.php');
}
echo yjl_html($c, 'active', '', 3);
?>