<?php

function _getStepPage($step, $aJpids) {
	global $r_res;
	$s = '';
	foreach ($aJpids as $iKey => $aValue) {
		if ($iKey == $step) {
			$s .= '<li class="colorli">'.$iKey.'</li>';
		} else {
			$s .= '<li><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$aValue['jpid'].'.html">'. $iKey .'</a></li>';
		}
	}
	
	return $s;
}

require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../'.$yjl_tpath.'setting/face.php');
require_once('../function.php');
$f='jp.php';
$jlid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
$q_res=sprintf('select * from %s where jlid=%s limit 1', $yjl_dbprefix.'jl', $jlid);
$res=mysql_query($q_res) or die('');
$r_res=mysql_fetch_assoc($res);
if(mysql_num_rows($res)>0){
	$q_rep=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_res['app_id']);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		$app_k=$r_rep['app_key'];
		$app_s=$r_rep['app_secret'];
	}else{
		$app_a=yjl_app('照片式监理 '.$r_res['name'], $jlid, $yjl_url.'photo-'.$r_res['xqid'].'-'.$jlid.'.html');
		$uSQL=sprintf('update %s set app_id=%s where jlid=%s', $yjl_dbprefix.'jl', $app_a[0], $jlid);
		$result=mysql_query($uSQL) or die('');
		$app_k=$app_a[1];
		$app_s=$app_a[2];
	}
	mysql_free_result($rep);
	if($r_res['uid']>0 && !isset($uadb[$r_res['uid']]))$uadb[$r_res['uid']]=yjl_udb($r_res['uid']);
	if(!isset($uadb[$r_res['hzid']]))$uadb[$r_res['hzid']]=yjl_udb($r_res['hzid']);
	$jpid=(isset($_GET['jpid']) && intval($_GET['jpid'])>0)?intval($_GET['jpid']):1;
	$q_rep=sprintf('select * from %s where jlid=%s and is_del=0 and jpid=%s limit 1', $yjl_dbprefix.'jl_photo', $jlid, $jpid);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	if(mysql_num_rows($rep)>0){
		if(isset($_GET['t']) && $_GET['t']=='top'){
			echo yjl_jlimgrightu($r_rep, $r_res, 'photo.php');
		} elseif (isset($_GET['t']) && $_GET['t']=='report') {
			
			//获取报告内容
			$oDb = DbMysql::getInstance($aDbConfig);
			
			//只获取评论数量
			if ($_GET['getcount']) {
				$cSql = "SELECT count(*) FROM ".$yjl_dbprefix."jl_topic WHERE jpid = " . $r_rep['jpid'];
				$comment_count = $oDb->fetchFirstField($cSql);
				die($comment_count);
			}
			
			
			//读取监理报告数据
			$cTable = $yjl_dbprefix . "jl_report";
			$step = $r_rep['step'];
			$cWhere = "jlid = $jlid AND step = $step";
			$cSql = "SELECT type,content,img FROM $cTable WHERE $cWhere";
			$aReport = $oDb->fetchFirstArray($cSql);
			if ($aReport['type'] != 2) {
				$cReport = $aReport['content'];
			} else {
				$cReportImg = $yjl_url . '/file/' .$aReport['img'];
			}
			$bAuth = $user_id==$r_res['uid'] ;
			
			$cStepSql = "AND step > " . max(0, $step - 5) . " AND step < " . ($step + 5);
			$cSql = "SELECT step,jpid FROM $cTable WHERE jlid = $jlid $cStepSql";
			$aJpids = $oDb->fetchAllArrayWithKey($cSql, 'step');
			$stepPage = _getStepPage($step, $aJpids);
			
			echo '<div class="yuefen">
					<div class="left lefbox_yuefen">
       					 <span>阶段</span>
       				</div>
						
					<div class="left rightbox_yuefen">
						<img src="images/left_bgimg0.png" width="7" height="8" class="left bximg prev1">
						<div class="left widyufen">
							<ul class="widyufen_ul" style="margin-left:0">
							'. $stepPage .'
							</ul>
						</div>
						<img src="images/right_bgimg0.png" width="7" height="8" class="left bximg next1">
					</div>
				</div>';
			if (isset($cReport)) {
				echo '<textarea name="report_content" '. ($bAuth ? '' : 'readonly') .' class="reportContent">'.$cReport.'</textarea>';
			} else {
				echo '<div class="reportImage"><img src="'.$cReportImg.'" /></div>';
			}
			
		} elseif(isset($_GET['t']) && $_GET['t']=='bottom'){
			if($user_id>0 && isset($_POST['c']) && trim($_POST['c'])!=''){
				$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
				require_once('../lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $app_k, $app_s, $udb['nickname'], md5($udb['nickname'].$udb['password']));
				if($r_rep['tid']>0){
					$iszf=(isset($_GET['iszf']) && $_GET['iszf']==1)?1:0;
					$fc=$iszf>0?'both':'reply';
				}else{
					$fc='first';
				}
				$jsg_result=$JishiGouAPI->AddTopic($_POST['c'], $r_rep['tid'], $fc);
				if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
					$tid=$jsg_result['result']['tid'];
					if(isset($_GET['imgid']) && trim($_GET['imgid'])!=''){
						$imga=explode('_', trim($_GET['imgid']));
						foreach($imga as  $v){
							if(trim($v)!='' && intval($v)>0){
								$q_rem=sprintf('select id, tid from %s where id=%s limit 1', $dbprefix.'topic_image', intval($v));
								$rem=mysql_query($q_rem) or die('');
								$r_rem=mysql_fetch_assoc($rem);
								if(mysql_num_rows($rem)>0){
									$a_imgid[]=$r_rem['id'];
									if($r_rem['tid']==0){
										$uSQL=sprintf('update %s set tid=%s where id=%s', $dbprefix.'topic_image', $tid, $r_rem['id']);
										$result=mysql_query($uSQL) or die('');
									}
								}
								mysql_free_result($rem);
							}
						}
						if(isset($a_imgid)){
							$uSQL=sprintf('update %s set imageid=%s where tid=%s', $dbprefix.'topic', yjl_SQLString(join(',', $a_imgid), 'text'), $tid);
							$result=mysql_query($uSQL) or die('');
						}
					}
					$oid=0;
					if($user_id==$r_res['uid']){
						$oid=1;
						$uSQL=sprintf('update %s set oid=0 where oid>0 and jpid=%s', $yjl_dbprefix.'jl_topic', $r_rep['jpid']);
						$result=mysql_query($uSQL) or die('');
					}
					$iSQL=sprintf('insert into %s (tid, jpid, uid, datetime, content, oid) values (%s, %s, %s, %s, %s, %s)', $yjl_dbprefix.'jl_topic',
						$tid,
						$r_rep['jpid'],
						$user_id,
						time(),
						yjl_SQLString($content, 'text'),
						$oid);
					$result=mysql_query($iSQL) or die('');
					$uSQL=sprintf('update %s set lasttime=%s where jlid=%s', $yjl_dbprefix.'jl', time(), $r_res['jlid']);
					$result=mysql_query($uSQL) or die('');
					/**
					$iSQL=sprintf('insert into %s (tid, xqid, uid, datetime, content) values (%s, %s, %s, %s, %s)', $yjl_dbprefix.'xq_topic',
						$tid,
						$r_res['xqid'],
						$user_id,
						time(),
						yjl_SQLString($content, 'text'));
					$result=mysql_query($iSQL) or die('');
					$uSQL=sprintf('update %s set c_wb=c_wb+1 where xqid=%s', $yjl_dbprefix.'xq', $r_res['xqid']);
					$result=mysql_query($uSQL) or die('');
					**/
					yjl_addlog('[uid]评论了监理项目照片：<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$r_rep['jpid'].'.html">'.yjl_substrs($content, 10).'</a>', md5('pljp|'.$r_rep['uid'].'|'.$user_id.'|'.$jpid), 0, $r_rep['uid']);
					yjl_uwb($user_id, $content, $tid, '../');
				}
				if($uadb[$user_id]['qx']==0 && isset($_GET['isqz']) && $_GET['isqz']==1){
					$q_req=sprintf('select app_key, app_secret from %s where id=%s limit 1', $dbprefix.'app', $r_main['qz_app_id']);
					$req=mysql_query($q_req) or die('');
					$r_req=mysql_fetch_assoc($req);
					if(mysql_num_rows($req)>0){
						$app_k=$r_req['app_key'];
						$app_s=$r_req['app_secret'];
					}else{
						$app_a=yjl_app('咨询监理', 0, $yjl_url.'faq-new.html', 'yjl');
						$uSQL=sprintf('update %s set qz_app_id=%s', $yjl_dbprefix.'main', $app_a[0]);
						$result=mysql_query($uSQL) or die('');
						$app_k=$app_a[1];
						$app_s=$app_a[2];
					}
					mysql_free_result($req);
					$content=htmlspecialchars(trim($_POST['c']),ENT_QUOTES);
					$jsg_result=$JishiGouAPI->AddTopic('向易监理提问：'.$_POST['c'], 0, 'first', $yjl_url.$r_rep['o_url']);
					if(!isset($jsg_result['error']) && isset($jsg_result['result']['tid'])){
						$tid=$jsg_result['result']['tid'];
						$iSQL=sprintf('insert into %s (uid, xqid, cid, content, datetime, tid) values (%s, %s, 2, %s, %s, %s)', $yjl_dbprefix.'qz',
							$user_id,
							$udb['xqid'],
							yjl_SQLString($content, 'text'),
							time(),
							$tid);
						$result=mysql_query($iSQL) or die('');
						$qzid=mysql_insert_id();
						yjl_addlog('[uid]向易监理提问：<a href="faq-'.$qzid.'.html">'.yjl_substrs($content, 10).'</a>', md5('qztw|'.$user_id.'|'.$user_id.'|'.$qzid));
					}
				}
			}
			unset($_GET['t']);
			$_GET['id']=$jlid;
			$_GET['jpid']=$jpid;
			$_GET['xqid']=$r_res['xqid'];
			$_GET['lid']=$r_rep['lid'];
			echo yjl_jlimgrightc($r_rep, $r_res);
		}
	}
	mysql_free_result($rep);
}
mysql_free_result($res);
?>