<?php

//db
require_once "./lib/dbmysql.class.php";
$oDb = DbMysql::getInstance($aDbConfig);

function _getStepPage($step, $aJpids) {
	global $r_res;
	$s = '';
	//$s = '&nbsp;<a href="#">首次</a>';
	//$s .= '&nbsp;<a href="#">末次</a>';
	foreach ($aJpids as $iKey => $aValue) {
		if ($iKey == $step) {
			$s .= '&nbsp;&nbsp;' . $iKey;
		} else {
			$s .= '&nbsp;&nbsp;<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$aValue['jpid'].'.html">'. $iKey .'</a>';
		}
	}
	
	return $s;
}

				$gu=0;
				if($r_res['uid']>0 && $r_res['jlqr']>0){
					$q_rep=sprintf('select * from %s where jlid=%s and uid=%s and is_del=0 limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $r_res['uid']);
					$rep=mysql_query($q_rep) or die('');
					if(mysql_num_rows($rep)>0)$gu=1;
					mysql_free_result($rep);
				}
				if(isset($_GET['u']) && $_GET['u']=='y')$gu=0;
				if(isset($_GET['u']) && $_GET['u']=='j' && $r_res['uid']>0 && $r_res['jlqr']>0)$gu=1;
				if(isset($_GET['jpid']) && intval($_GET['jpid'])>0){
					$q_rep=sprintf('select * from %s where jlid=%s and jpid=%s and is_del=0 limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid'], intval($_GET['jpid']));
					$rep=mysql_query($q_rep) or die('');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0){
						$d_jpdb=$r_rep;
						if($d_jpdb['uid']==$r_res['uid']){
							$gu=1;
						}elseif($d_jpdb['uid']==$r_res['hzid']){
							$gu=0;
						}
					}
					mysql_free_result($rep);
				}
				$guid=$gu>0?$r_res['uid']:$r_res['hzid'];
				if($issc>0 && $guid!=$user_id)$issc=0;
				if(!isset($d_jpdb)){
					$q_rep=sprintf('select * from %s where jlid=%s and uid=%s and is_del=0 order by datetime desc, jpid desc limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $guid);
					$rep=mysql_query($q_rep) or die('2');
					$r_rep=mysql_fetch_assoc($rep);
					if(mysql_num_rows($rep)>0)$d_jpdb=$r_rep;
					mysql_free_result($rep);
				}
				$jd=0;
				foreach($a_lc as $k=>$v){
					$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and is_del=0 limit 1', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $k);
					$reu=mysql_query($q_reu) or die('');
					if(mysql_num_rows($reu)>0)$jd++;
					mysql_free_result($reu);
					$q_reu=sprintf('select * from %s where jlid=%s and lid=%s and uid=%s and is_del=0 order by datetime desc, jpid desc', $yjl_dbprefix.'jl_photo', $r_res['jlid'], $k, $guid);
					$reu=mysql_query($q_reu) or die('');
					$r_reu=mysql_fetch_assoc($reu);
					$c_reu=mysql_num_rows($reu);
					if($c_reu>0){
						$a_lci[$k]=1;
						do{
							if(!isset($a_lszp[$k]))$a_lszp[$k]=$r_reu['jpid'];
							$a_jlp[$r_res['jlid']][$k][]='<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$r_reu['jpid'].'.html#piclist" onclick="loadjlp(\''.$r_reu['jpid'].'\', \''.$r_res['jlid'].'\');return false;"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: url('.$r_reu['t_url'].') no-repeat center;" /></a>';
						}while($r_reu=mysql_fetch_assoc($reu));
					}
					mysql_free_result($reu);
					$a_jlm[$r_res['jlid']][]='<a'.($c_reu>0?' href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-'.$a_lszp[$k].'.html"':' class="hide"').' id="jl_'.$r_res['jlid'].'_'.$k.'"><span class="mn_ico ico1'.($k+2).'"></span><br />'.$v.'</a>';
				}
				$cjd=isset($d_jpdb)?$d_jpdb['lid']:0;
				if($cjd>1)$js_c.='
		$(\'#jl_'.$r_res['jlid'].'_'.$cjd.'\').mouseover();';
				if(!isset($d_jpdb) && $issc>0){
					for($i=0;$i<6;$i++)$a_jlp[$r_res['jlid']][1][]='<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-upload.html"><img src="images/blank.gif" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" style="background: #fafafa url(images/upload.jpg) no-repeat center;" title="上传照片" /></a>';
				}
				if($r_res['uid']>0 && $r_res['jlqr']>0)$c.='<div class="uplood"><a href="photo-'.$xqid.'-'.$jlid.'-y.html" class="btn bt_nom'.($gu==0?'blue':'gray').'">业主上传</a><a href="photo-'.$xqid.'-'.$jlid.'-j.html" class="btn bt_nom'.($gu==0?'gray':'blue').'">监理师上传</a></div>';
				$c.='<div class="pr_cont" id="piclist">
				<div class="pr_tit'.(isset($a_jlp)?' tabs':'').'">'.join(' ', $a_jlm[$r_res['jlid']]).'</div>
				<div class="progress"'.($issc>0?' style="width: 200px;"><a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-upload.html">上传照片</a>&nbsp; &nbsp;':'>').'完成度<strong>'.round($jd*100/$c_lc).'%</strong></div>';
				if(isset($a_jlp)){
					foreach($a_lc as $k=>$v){
						$c.='<div class="pr_pic tabcnt"'.($k==$cjd?'':' style="display: none;"').'><a class="mn_ico prev"></a><div class="scrollable" id="scrollable"><div class="items spt">';
						if(isset($a_jlp[$r_res['jlid']][$k]))$c.=join(' ', $a_jlp[$r_res['jlid']][$k]);
						$c.='</div></div><a class="mn_ico next"></a></div>';
					}
					$c.='<div class="image_wrap" id="image_wrap">';
					if(isset($d_jpdb)){
						if($user_id>0 && (($user_id==$d_jpdb['uid'] && $d_jpdb['datetime']>(time()-$jldimg_jg)) || $user_id==$r_res['hzid'] || $udb['isxg']>0 || $udb['qx']==10)){
							if(isset($_GET['del']) && $_GET['del']==1){
								if($user_id==$d_jpdb['uid'] || $udb['isxg']>0 || $udb['qx']==10){
									//监理报告相关处理
									if ($r_rep['step'] > 0) {
										$cSql = "SELECT jpid FROM " . $yjl_dbprefix . "jl_report WHERE jlid = $jlid AND step = " . $r_rep['step'];
										$step_jpid = $oDb->fetchFirstField($cSql);
										
										//如果是当前阶段第一张照片
										if ($step_jpid == $jpid) {
											$cSql = "SELECT jpid FROM " . $yjl_dbprefix . "jl_photo WHERE jlid = $jlid AND step = ". $r_rep['step'] ." AND jpid <> $jpid ORDER BY datetime DESC LIMIT 1";
											$next_jpid = $oDb->fetchFirstField($cSql);
											
											if (empty($next_jpid)) {
												//最后一张禁止删除
												header("Content-type:text/html;charset=utf-8");
												echo '<script type="text/javascript">alert("删除后当前阶段就没有图片了！");history.back(-1);</script>';
												exit();
											} else {
												//更新当前阶段jpid
												$oDb->update($yjl_dbprefix . 'jl_report', array('jpid' => $next_jpid), "jlid = $jlid AND step = " . $r_rep['step']);
											}
										}
									}
								
									
									$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_photo', $d_jpdb['jpid']);
									$result=mysql_query($dSQL) or die('');
									$dSQL=sprintf('delete from %s where jpid=%s', $yjl_dbprefix.'jl_topic', $d_jpdb['jpid']);
									$result=mysql_query($dSQL) or die('');
									unlink($d_jpdb['url']);
									unlink($d_jpdb['t_url']);
									unlink($d_jpdb['o_url']);
									if($d_jpdb['tid']>0){
										$q_rep=sprintf('select a.tid, b.nickname, b.password, d.app_key, d.app_secret from %s as a, %s as b, %s as c, %s as d where a.tid=%s and a.uid=b.uid and a.tid=c.tid and c.item_id=d.id limit 1', $dbprefix.'topic', $dbprefix.'members', $dbprefix.'topic_api', $dbprefix.'app', $d_jpdb['tid']);
										$rep=mysql_query($q_rep) or die('');
										$r_rep=mysql_fetch_assoc($rep);
										if(mysql_num_rows($rep)>0){
											require_once('lib/jishigouapi.class.php');
											$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
											$jsg_reqult=$JishiGouAPI->DeleteTopic($r_rep['tid']);
										}
										mysql_free_result($rep);
									}
								}elseif($r_res['datetime']>(time()-$jldimg_jg)){
									$uSQL=sprintf('update %s set is_del=1, deltime=%s where jpid=%s', $yjl_dbprefix.'jl_photo', time(), $d_jpdb['jpid']);
									$result=mysql_query($uSQL) or die('');
								}
								echo '<script type="text/javascript">location.href=\'photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'.html\';</script>';
								exit();
							}
						}
						$c.=yjl_jlimgrightu($d_jpdb, $r_res, 'photo.php');
					}else{
						$c.='<a href="photo-'.$r_res['xqid'].'-'.$r_res['jlid'].'-upload.html"><img src="images/blank.gif" style="height: 450px;background: #fafafa url(images/upload.jpg) no-repeat center;" title="上传照片"/></a>';
					}
					$c.='</div>';

			if (!empty($d_jpdb)) {
					
			//监理报告相关变量初始化
			$cTable = $yjl_dbprefix . "jl_report";
			$step = $r_rep['step'];
			$cWhere = "jlid = $jlid AND step = $step";
			
			if ($step) {
				//保存评分
				if ($_POST['report_score']) {
					if ($user_id==$d_jpdb['uid']) {
						$oDb->update($cTable,array('score_owner' => $_POST['report_score']), $cWhere);
					} elseif ($udb['qx']==10) {
						$oDb->update($cTable,array('score_director' => $_POST['report_score']), $cWhere);
					}
					
					unset($_POST['save_report']);
				}
				
				if ($_POST['save_report']) {

					//保存监理报告
					if ($user_id==$r_res['uid']) {
						if ($_POST['report_type'] == 2) {
							//图片类型
							require_once "./lib/UploadFile.class.php";
							$upload = new UploadFile();
							//上传检测
							$upload->maxSize  = 3145728 ;									// 设置附件上传大小
							$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');		// 上传文件的(后缀)（留空为不限制），
							// 上传文件的(类型),m（留空为不限制），
							$upload->allowTypes = array ('image/jpeg','image/pjpeg','image/png','image/x-png','image/gif');
							//上传保存
							$upload->savePath =  dirname(__FILE__) . '/file/';			// 设置附件上传目录
							$upload->autoSub = true;											// 是否使用子目录保存上传文件
							$upload->subType = 'date';											// 子目录创建方式，默认为hash，可以设置为hash或者date日期格式的文件夹名
	
							if(!$upload->upload()) {// 上传错误提示错误信息
								header("Content-type:text/html;charset=utf-8");
								echo '<script type="text/javascript">alert("'.$upload->getErrorMsg().'");history.back(-1);</script>';
								exit();
							}else{// 上传成功 获取上传文件信息
								$info =  $upload->getUploadFileInfo();
							}
													
							$cImg = $info[0]['savename'];
							$cReportImg = $yjl_url . '/file/' . $cImg;
							$oDb->update($cTable, array('img' => $cImg, 'type' => 2), $cWhere);
						} else {
							//文字类型
							$content = htmlspecialchars($_POST['report_content'], ENT_QUOTES);
							$oDb->update($cTable, array('content' => $content, 'type' => 1), $cWhere);
							$cReport = $content;
						}
					}
				} else {		
					//读取监理报告数据	
					$cSql = "SELECT type,content,img FROM $cTable WHERE $cWhere";
					$aReport = $oDb->fetchFirstArray($cSql);
					if ($aReport['type'] != 2) {
						$cReport = $aReport['content'];
					} else {
						$cReportImg = $yjl_url . '/file/' .$aReport['img'];
					}
				}
			} else {
				$cReport = '还没有任何报告内容！';
			}
			
			$cStepSql = "AND step > " . max(0, $step - 5) . " AND step < " . ($step + 5);
			$cSql = "SELECT step,jpid FROM $cTable WHERE jlid = $jlid $cStepSql";
			$aJpids = $oDb->fetchAllArrayWithKey($cSql, 'step');
			$stepPage = _getStepPage($step, $aJpids);

			$cSql = "SELECT count(*) FROM ".$yjl_dbprefix."jl_topic WHERE jpid = " . $d_jpdb['jpid'];
			$comment_count = $oDb->fetchFirstField($cSql);
			
			$c .= '<div class="photo_report_head"><a href="javascript:showReport(false)" class="reportTab" >评论(<span id="comment_count">'.$comment_count.'</span>)</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:showReport(true)" class="reportTabSelected">监理报告</a></div>';
			
			$c.='<form method="post" enctype="multipart/form-data" id="form_report">
					<input type="hidden" name="save_report" value="1" />
					<div id="div_report">
					<div class="report_page">
						<span>阶段</span>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="reportPageArrow"><</span>'. $stepPage .'&nbsp;&nbsp;<span class="reportPageArrow">></span>
					</div>';
				//文字或图片内容
				if (isset($cReport)) {
					$c .= '<textarea name="report_content" '. ($user_id==$r_res['uid'] ? '' : 'readonly') .' class="reportContent">'.$cReport.'</textarea>';
				} else {
					$c .= '<div class="reportImage"><img src="'.$cReportImg.'" /></div>';
				}
			$c .= '</div>';
			if ($user_id==$r_res['uid']) {
				$c .='<div style="padding:10px;margin-bottom:10px;" class="comm_wrap clearfix" >
			内容类型：<input type="radio" name="report_type" value=1>文字
			<input id="report_type_2" type="radio" name="report_type" value=2>图片 
			<input type="file" name="report_file" onclick="report_type_2.checked = \'checked\';" />
			<input style="" type="submit" value="保 存" class="submit sub_smbe" />
			</div>';
			}elseif ($user_id==$r_res['hzid'] || $udb['qx']==10) {
				$c .='<div style="padding:10px;margin-bottom:10px;" class="comm_wrap clearfix" >
			对本次服务打分：<select name="report_score">
				<option value="10">10</option>
				<option value="9">9</option>
				<option value="8">8</option>
				<option value="7">7</option>
				<option value="6">6</option>
				<option value="5">5</option>
				<option value="4">4</option>
				<option value="3">3</option>
				<option value="2">2</option>
				<option value="1">1</option>
			</select> 分
			<input style="" type="submit" value="评 分" class="submit sub_smbe" />
			</div>';
			} else {
				$c .= '<div style="height:10px;">&nbsp;</div>';
			}
			
			$cSql = "SELECT * FROM $cTable WHERE jlid = $jlid";
			$aScores = $oDb->fetchAllArray($cSql);
			
			$s = '';
			$total_average = 0;$count_average = 0;
			$total_owner = 0;$count_owner = 0;
			$total_director = 0;$count_director = 0;
			foreach ($aScores as $aValue) {
				$total_owner += $aValue['score_owner'];
				$total_director += $aValue['score_director'];
				if ($aValue['score_owner'] && $aValue['score_director']) {
					$aValue['score_average'] = ($aValue['score_owner'] + $aValue['score_director']) / 2;
				} else {
					$aValue['score_average'] = $aValue['score_owner'] + $aValue['score_director'];
				}
				$total_average += $aValue['score_average'];
				
				if ($aValue['score_owner'] > 0) {
					$count_owner++;
				}
				if ($aValue['score_director'] > 0) {
					$count_director++;
				}
				if ($aValue['score_average'] > 0) {
					$count_average++;
				}
				
				$s .= '<tr>
					<td>第'.$aValue['step'].'次</td>
					<td>'.($aValue['score_owner'] ? $aValue['score_owner'] : '未评') .'分</td>
					<td>'.($aValue['score_director'] ? $aValue['score_director'] : '未评').'分</td>
					<td>'.($aValue['score_average'] ? $aValue['score_average'] : '未评').'分</td>
				</tr>';
			}	
			
			$c .= '<div class="report_total">平均评分：<span>'.round($total_average/$count_average, 1).'</span>分。 业主平均评分：<span>'.round($total_owner/$count_owner, 1).'</span>分。总监平均评分：<span>'.round($total_director/$count_director, 1).'</span>分。<a href="javascript:void(0);" onclick="document.getElementById(\'report_score\').style.display=\'block\';">查看详细信息</a></div>';
			$c .= '<table id="report_score" style="display:none;" class="report_score" cellspacing=0>';
			$c .= '<tr><th>监理阶段</th><th>业主评分</th><th>总监评分</th><th>平均得分</th></tr>';
			$c .= $s;
			$c .= '</table>';
			$c .= '</form>';
			
			}//--end
			
			$c .= '</div><div class="comm_wrap clearfix" id="div_comment" style="display:none;">';
					if(isset($d_jpdb)){
						$c.='<div id="jp_topic">'.yjl_jlimgrightc($d_jpdb, $r_res, $page).'</div><br /><br />';
						if($user_id>0){
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
						<td><input type="submit" value="评 论" class="submit sub_smbe" id="submit_fb" onclick="postjpwb();" /><input type="hidden" id="jpid" value="'.$d_jpdb['jpid'].'"/></td>
					</tr>
				</table>
				<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a><input type="checkbox" name="iszf" id="tpiszf" checked="checked" class="radio"/>同时转发微博'.($uadb[$user_id]['qx']==0?' <input type="checkbox" name="isqz" id="tpisqz" class="radio"/>咨询监理':'').'</div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
			</div>';
						}
					}
					$c.='<br /><br /></div>';
				}else{
					$c.='<div class="pr_pic tabcnt" style="height: 1px;line-height; 1px;"></div></div><div class="comm_wrap clearfix" style="padding-bottom: 40px;">该项目'.($gu>0?'监理师':'业主').'还没有上传照片</div>';
				}
?>