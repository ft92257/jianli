<?php
require_once('config.php');
require_once($yjl_tpath.'setting/settings.php');
$no_getxq=1;
if(isset($_GET['rg']) && trim($_GET['rg'])!=''){
	$a_rg=explode('-', trim($_GET['rg']));
	if(isset($a_rg[0])){
		if($a_rg[0]=='new' || $a_rg[0]=='my' || $a_rg[0]=='no' || $a_rg[0]=='hot'){
			$_GET['t']=$a_rg[0];
		}elseif(substr($a_rg[0], 0, 1)=='c'){
			$_GET['t']=substr($a_rg[0], 1);
		}else{
			$_GET['id']=$a_rg[0];
		}
	}
	if(isset($a_rg[1])){
		if(substr($a_rg[1], 0, 1)=='p'){
			$_GET['p']=substr($a_rg[1], 1);
		}else{
			$_GET['jluid']=$a_rg[1];
		}
	}
}
require_once('function.php');
$f='faq.php';
if($udb['uid']>0){
	if(($udb['iswc']!=1 && ($udb['qx']==5 || $udb['qx']==6)) || $udb['isnc']==0){
		echo '<script type="text/javascript">location.href=\'./\';</script>';
		exit();
	}
}
$page_title='咨询监理';
$js_c='';
$c='<div class="main_left"><h2 class="h2">咨询监理</h2>';
if($user_id==0 || $udb['qx']==0)$c.='<div class="more_bt"><a href="'.($user_id==0?'login.php?u='.urlencode('help.php').'" rel="#overlay_login':'help.php').'" class="btn bt_bgblue">请监理师到现场</a></div>';
$c.='<div class="vge_inf">';
$page=(isset($_GET['p']) && intval($_GET['p'])>0)?intval($_GET['p']):1;
if(isset($_GET['id']) && intval($_GET['id'])>0){
	$id=intval($_GET['id']);
	$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.c_hd, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.qzid=%s', $yjl_dbprefix.'qz', $dbprefix.'topic', $id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$q_rep=sprintf('select qzid from %s where qzid=%s and uid<>%s', $yjl_dbprefix.'qz_topic', $r_res['qzid'], $r_res['uid']);
		$rep=mysql_query($q_rep) or die(mysql_error());
		$c_rep=mysql_num_rows($rep);
		if($c_rep!=$r_res['c_hd']){
			$uSQL=sprintf('update %s set c_hd=%s where qzid=%s', $yjl_dbprefix.'qz', $c_rep, $r_res['qzid']);
			$result=mysql_query($uSQL) or die('');
		}
		mysql_free_result($rep);
		$c.='<ul class="list_comment">';
		if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
		$c.='<li id="wb_'.$r_res['tid'].'">
						<div class="left">
							<a href="user-'.$r_res['uid'].'.html"><img src="'.yjl_face($r_res['uid'], $uadb[$r_res['uid']]['face']).'" /></a>';
		if($uadb[$r_res['uid']]['qx']==5)$c.='<div class="m_sp1">监理师</div>';
		$c.='</div>
						<div class="right">
							<h3><a href="user-'.$r_res['uid'].'.html">'.$uadb[$r_res['uid']]['nc'].'</a>:';
		if($r_res['longtextid']>0){
			$q_reu=sprintf('select `longtext` from %s where id=%s and tid=%s limit 1', $dbprefix.'topic_longtext', $r_res['longtextid'], $r_res['tid']);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0){
				$c.=$r_reu['longtext'];
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
				$reu=mysql_query($q_reu) or die('');
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
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0)$img_a[$r_res['tid']]['v'.$r_res['videoid']]='<a href="'.$r_reu['video_url'].'" target="_blank" title="点击查看视频"><img src="images/blank.gif" style="background: #fff url('.($r_reu['video_img']!=''?$yjl_tpath.str_replace('./', '', $r_reu['video_img']):'images/vi_d.jpg').') no-repeat center;" alt=""/></a>';
			mysql_free_result($reu);
		}
		if(isset($img_a[$r_res['tid']]))$c.=join(' ', $img_a[$r_res['tid']]);
		$a[$r_res['qzid']][]='<a href="faq-c'.$r_res['cid'].'.html">'.$a_qzca[$r_res['cid']].'</a>';
		if($r_res['c_hd']>0){
			if($user_id>0 && $uadb[$user_id]['qx']==0){
				$a[$r_res['qzid']][]='<a href="#" onclick="$(this).load(\'j/zanqz.php?id='.$r_res['qzid'].'\');return false;">赞'.($r_res['c_zan']>0?'('.$r_res['c_zan'].')':'').'</a>';
			}else{
				if($r_res['c_zan']>0)$a[$r_res['qzid']][]='赞('.$r_res['c_zan'].')';
			}
		}
		if($user_id>0 && ($udb['qx']==10 || $udb['isxg']>0 || ($user_id==$r_res['uid'] && $r_res['c_hd']==0))){
			if(isset($_GET['delwt']) && $_GET['delwt']==1){
				require_once('lib/jishigouapi.class.php');
				if($r_res['tid']>0){
					$q_rep=sprintf('select b.nickname, b.password, c.app_key, c.app_secret from %s as a, %s as b, %s as c where a.tid=%s and a.uid=b.uid and a.item_id=c.id', $dbprefix.'topic_api', $dbprefix.'members', $dbprefix.'app', $r_res['tid']);
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0){
						$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
						$jsg_result=$JishiGouAPI->DeleteTopic($r_res['tid']);
					}
					mysql_free_result($rep);
				}
				$q_reu=sprintf('select tid from %s where qzid=%s and tid>0', $yjl_dbprefix.'qz_topic', $r_res['qzid']);
				$reu=mysql_query($q_reu) or die('');
				$r_reu=mysql_fetch_assoc($reu);
				if(mysql_num_rows($reu)>0){
					do{
						if($r_reu['tid']>0){
							$q_rep=sprintf('select b.nickname, b.password, c.app_key, c.app_secret from %s as a, %s as b, %s as c where a.tid=%s and a.uid=b.uid and a.item_id=c.id', $dbprefix.'topic_api', $dbprefix.'members', $dbprefix.'app', $r_reu['tid']);
							$rep=mysql_query($q_rep) or die('');
							$r_rep=mysql_fetch_assoc($rep);
							if(mysql_num_rows($rep)>0){
								$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
								$jsg_result=$JishiGouAPI->DeleteTopic($r_reu['tid']);
							}
							mysql_free_result($rep);
						}
					}while($r_reu=mysql_fetch_assoc($reu));
				}
				mysql_free_result($reu);
				$dSQL=sprintf('delete from %s where qzid=%s', $yjl_dbprefix.'qz_topic', $r_res['qzid']);
				$result=mysql_query($dSQL) or die(mysql_error());
				$dSQL=sprintf('delete from %s where qzid=%s', $yjl_dbprefix.'qz', $r_res['qzid']);
				$result=mysql_query($dSQL) or die(mysql_error());
				echo '<script type="text/javascript">location.href=\'faq-new.html\';</script>';
				exit();
			}
			$a[$r_res['qzid']][]='<a href="'.$f.'?id='.$r_res['qzid'].'&amp;delwt=1" onclick="if(!confirm(\'确认删除？\'))return false;" style="color: #f00;">删除</a>';
		}
		if($user_id>0 && isset($_GET['delhf']) && intval($_GET['delhf'])>0){
			$qztid=intval($_GET['delhf']);
			$q_reu=sprintf('select qztid, tid, uid from %s where qzid=%s and qztid=%s limit 1', $yjl_dbprefix.'qz_topic', $r_res['qzid'], $qztid);
			$reu=mysql_query($q_reu) or die('');
			$r_reu=mysql_fetch_assoc($reu);
			if(mysql_num_rows($reu)>0){
				if($udb['qx']==10 || $udb['isxg']>0 || $user_id==$r_reu['uid']){
					if($r_reu['tid']>0){
						$q_rep=sprintf('select b.nickname, b.password, c.app_key, c.app_secret from %s as a, %s as b, %s as c where a.tid=%s and a.uid=b.uid and a.item_id=c.id', $dbprefix.'topic_api', $dbprefix.'members', $dbprefix.'app', $r_reu['tid']);
						$rep=mysql_query($q_rep) or die('');
						$r_rep=mysql_fetch_assoc($rep);
						if(mysql_num_rows($rep)>0){
							require_once('lib/jishigouapi.class.php');
							$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
							$jsg_result=$JishiGouAPI->DeleteTopic($r_reu['tid']);
						}
						mysql_free_result($rep);
						$dSQL=sprintf('delete from %s where qztid=%s', $yjl_dbprefix.'qz_topic', $r_reu['qztid']);
						$result=mysql_query($dSQL) or die(mysql_error());
					}
				}
			}
			mysql_free_result($reu);
			echo '<script type="text/javascript">location.href=\'faq-'.$r_res['qzid'].'.html\';</script>';
			exit();
		}
		$c.='<p class="other">'.(isset($a[$r_res['qzid']])?'<span>'.join('|', $a[$r_res['qzid']]).'</span>':'').yjl_wbdate($r_res['dateline']).'</p></div></li></ul>';
		if($user_id>0 && ($user_id==$r_res['uid'] || ($uadb[$user_id]['qx']==5 && $udb['iszxjl']>0 && ($user_id==$r_res['jluid'] || $r_res['jluid']==0)))){
			$isupimg=1;
			$js_a='upimg_a_0(response);';
			$js_ac='upimg_ac_0();';
			$js_c.=yjl_uploadjs($js_a, $js_ac);
			$c.='<div class="broadcast">
				<table>
					<tr>
						<td>'.($user_id==$r_res['uid']?'补充':'回答').'问题</td>
					</tr>
					<tr>
						<td><textarea style="padding: 5px;" id="content"></textarea></td>
					</tr>
					<tr>
						<td><input type="submit" value="'.($user_id==$r_res['uid']?'提 交':'回 答').'" class="submit sub_smbe" id="submit_fb" onclick="postqzhd(\''.$id.'\');" /></td>
					</tr>
				</table>
				<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a></div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
			</div>';
		}
		$c.='<div id="faq_topic">'.yjl_qzdf($r_res).'</div>';
	}else{
		echo '<script type="text/javascript">location.href=\'faq-new.html\';</script>';
		exit();
	}
	mysql_free_result($res);
}else{
	if($user_id>0 && $uadb[$user_id]['qx']==0 && isset($_GET['t']) && $_GET['t']=='my'){
		$cid='my';
		$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.uid=%s order by a.datetime desc, a.qzid desc', $yjl_dbprefix.'qz', $dbprefix.'topic', $user_id);
	}elseif($user_id>0 && $uadb[$user_id]['qx']==5 && $udb['iszxjl']>0 && isset($_GET['t']) && $_GET['t']=='no'){
		$cid='no';
		$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.c_hd=0 and (a.jluid=0 or a.jluid=%s) order by a.datetime desc, a.qzid desc', $yjl_dbprefix.'qz', $dbprefix.'topic', $user_id);
	}elseif(isset($_GET['t']) && $_GET['t']=='hot'){
		$cid='hot';
		$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.c_hd>0 and a.c_zan>0 order by a.c_zan desc, a.datetime desc', $yjl_dbprefix.'qz', $dbprefix.'topic');
	}else{
		$cid=(isset($_GET['t']) && isset($a_qzca[$_GET['t']]))?$_GET['t']:'new';
		if($user_id>0 && $uadb[$user_id]['qx']==0){
			$isupimg=1;
			$js_a='upimg_a_0(response);';
			$js_ac='upimg_ac_0();';
			$js_c.=yjl_uploadjs($js_a, $js_ac);
			$c.='<div class="broadcast">
				<table>
					<tr>
						<td width="80">问题分类</td>
								<td><select id="cid">';
			foreach($a_qzca as $k=>$v)$c.='<option value="'.$k.'"'.($k==$cid?' selected="selected"':'').'>'.$v.'</option>';
			$c.='</select></td>';
			$q_res=sprintf('select a.nc, b.uid, b.face from %s as a, %s as b where a.uid=b.uid and a.qx=5 and a.iswc=1 and a.iszxjl=1', $yjl_dbprefix.'members', $dbprefix.'members');
			$res=mysql_query($q_res) or die($q_res);
			$r_res=mysql_fetch_assoc($res);
			$c_res=mysql_num_rows($res);
			if($c_res>0){
				$xzjlid=0;
				do{
					if(isset($_GET['jluid']) && $_GET['jluid']==$r_res['uid'])$xzjlid=$r_res['uid'];
					$xzjl_a[]='<option value="'.$r_res['uid'].'"'.($xzjlid==$r_res['uid']?' selected="selected"':'').'>'.yjl_substrs($r_res['nc']).'</option>';
				}while($r_res=mysql_fetch_assoc($res));
				$c.='<td width="80">选择监理师</td><td><select id="jluid"><option value="0">不指定监理师</option>'.join('', $xzjl_a).'</select></td></tr>';
			}
			mysql_free_result($res);
			$c.='</tr>
					<tr>
						<td colspan="'.($c_res>0?'4':'2').'"><textarea style="padding: 5px;" id="content"></textarea></td>
					</tr>
					<tr>
						<td colspan="'.($c_res>0?'4':'2').'"><input type="submit" value="提 问" class="submit sub_smbe" id="submit_fb" onclick="postqzwt();" /></td>
					</tr>
				</table>
				<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a></div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
			</div>';
		}
		if($cid=='new'){
			$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.c_hd>0 order by a.datetime desc, a.qzid desc', $yjl_dbprefix.'qz', $dbprefix.'topic');
		}else{
			$q_res=sprintf('select a.qzid, a.c_zan, a.jluid, a.cid, b.* from %s as a, %s as b where a.tid=b.tid and a.c_hd>0 and a.cid=%s order by a.datetime desc, a.qzid desc', $yjl_dbprefix.'qz', $dbprefix.'topic', $cid);
		}
	}
	$c.='<div class="vge_nav">
					<a href="faq-new.html" class="btn bt_gray'.($cid=='new'?' current':'').'"><span class="mn_ico ico30"></span>最新问答</a>';
	foreach($a_qzca as $k=>$v)$c.='<a href="faq-c'.$k.'.html" class="btn bt_gray'.($cid==$k?' current':'').'"><span class="mn_ico ico3'.$k.'"></span>'.$v.'</a>';
	$c.='<a href="faq-hot.html" class="btn bt_gray'.($cid=='hot'?' current':'').'"><span class="mn_ico ico33"></span>好评问答</a>';
	if($user_id>0 && $uadb[$user_id]['qx']==5 && $udb['iszxjl']>0)$c.='<a href="faq-no.html" class="btn bt_gray'.($cid=='no'?' current':'').'"><span class="mn_ico ico30"></span>没有答复</a>';
	if($user_id>0 && $uadb[$user_id]['qx']==0)$c.='<a href="faq-my.html" class="btn bt_gray'.($cid=='my'?' current':'').'"><span class="mn_ico ico30"></span>我的咨询</a>';
	$c.='</div><div id="faq_topic">';
	$a_res=mysql_query($q_res) or die('');
	$tr_res=mysql_num_rows($a_res);
	if($tr_res>0){
		$c.='<ul class="list_comment">';
		$tp_res=ceil($tr_res/$p_size);
		if($page>$tp_res)$page=$tp_res;
		$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
		$res=mysql_query($q_l_res) or die('');
		$r_res=mysql_fetch_assoc($res);
		do{
			if(!isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
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
			$c.='<li id="wb_'.$r_wb['tid'].'">
							<div class="left">
								<a href="user-'.$r_wb['uid'].'.html"><img src="'.yjl_face($r_wb['uid'], $uadb[$r_wb['uid']]['face']).'" /></a>';
			if($uadb[$r_wb['uid']]['qx']==5)$c.='<div class="m_sp1">监理师</div>';
			$c.='</div>
							<div class="right">
								<h3><a href="user-'.$r_wb['uid'].'.html">'.$uadb[$r_wb['uid']]['nc'].'</a>:';
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
				$c.='<div class="active_bg"></div>
									<div class="active clearfix">
										<h3><a href="user-'.$r_z['uid'].'.html">@ '.$uadb[$r_z['uid']]['nc'].'</a>：';
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
			$a[$r_res['qzid']][]='<a href="faq-c'.$r_res['cid'].'.html">'.$a_qzca[$r_res['cid']].'</a>';
			if($c_hd>0){
				if($user_id>0 && $uadb[$user_id]['qx']==0){
					$a[$r_res['qzid']][]='<a href="#" onclick="$(this).load(\'j/zanqz.php?id='.$r_res['qzid'].'\');return false;">赞'.($r_res['c_zan']>0?'('.$r_res['c_zan'].')':'').'</a>';
				}else{
					if($r_res['c_zan']>0)$a[$r_res['qzid']][]='赞('.$r_res['c_zan'].')';
				}
			}elseif($user_id>0 && $uadb[$user_id]['qx']==5 && $udb['iszxjl']>0 && ($r_res['jluid']==0 || $r_res['jluid']==$user_id)){
				$a[$r_res['qzid']][]='<a href="faq-'.$r_res['qzid'].'.html">回答</a>';
			}
			$a[$r_res['qzid']][]='<a href="faq-'.$r_res['qzid'].'.html">详情</a>';
			$c.='<p class="other">'.(isset($a[$r_res['qzid']])?'<span>'.join('|', $a[$r_res['qzid']]).'</span>':'').yjl_wbdate($r_wb['dateline']).'</p>
							</div>
						</li>';
		}while($r_res=mysql_fetch_assoc($res));
		mysql_free_result($res);
		$c.='</ul>';
		//if($tp_res>1)$c.=yjl_newpage($page, $tp_res, 'faq_topic');
		if($tp_res>1)$c.=yjl_newhmpage('faq-new-p[p].html', $page, $tp_res, 'faq_topic');
		
	}
	mysql_free_result($a_res);
	$c.='</div><br /><br />';
}
$c.='</div>
		</div>
		<div class="main_right">'.yjl_newr_jlzx().yjl_newr_jltj().'</div>';
echo yjl_html($c, 'supervisor');
?>