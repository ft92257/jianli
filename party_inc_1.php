<?php
			if($_SERVER['REQUEST_METHOD']=='POST'){
				$name=(isset($_POST['name']) && trim($_POST['name'])!='')?htmlspecialchars(trim($_POST['name']),ENT_QUOTES):$r_res['name'];
				$content=trim($_POST['content']);
				$isgf=$r_res['isgf'];
				if(($udb['isxg']>0 || $udb['qx']==10) && isset($_POST['isgf']) && $_POST['isgf']==1)$isgf=1;
				$hxqid=$r_res['xqid'];
				if(($udb['isxg']>0 || $udb['qx']==10) && isset($_POST['isnoxq']) && $_POST['isnoxq']==1)$hxqid=0;
				$isxzrs=$_POST['isxzrs'];
				$xzrs=intval($_POST['xzrs'])>0?intval($_POST['xzrs']):0;
				if($xzrs==0)$isxzrs=0;
				$sjtid=$_POST['sjtid'];
				$datetime=mktime(0,0,0,$_POST['datetime_m'],$_POST['datetime_d'],$_POST['datetime_y']);
				$metime=mktime(0,0,0,$_POST['datetime1_m'],$_POST['datetime1_d'],$_POST['datetime1_y']);
				if($metime<=$datetime && $sjtid==1)$sjtid=0;
				$cs=intval($_POST['cs'])>0?intval($_POST['cs']):1;
				if($cs<=1 && $sjtid==2)$sjtid=0;
				switch($sjtid){
					case 2:
						$etime=$datetime+86400-1;
						$etime+=($cs-1)*86400*7;
						break;
					case 1:
						$etime=$metime+86400-1;
						break;
					default:
						$etime=$datetime+86400-1;
						break;
				}
				$address=htmlspecialchars(trim($_POST['address']),ENT_QUOTES);
				if($name!=$r_res['name'] && $r_res['app_id']>0){
					$uSQL=sprintf('update %s set app_name=%s, app_desc=%s where id=%s', $dbprefix.'app',
						yjl_SQLString('活动 '.$name, 'text'),
						yjl_SQLString('活动 '.$name, 'text'),
						$r_res['app_id']);
					$result=mysql_query($uSQL) or die('');
				}
				$uSQL=sprintf('update %s set name=%s, content=%s, isgf=%s, xqid=%s, isxzrs=%s, xzrs=%s, datetime=%s, sjtid=%s, cs=%s, etime=%s, address=%s, lasttime=%s where hdid=%s', $yjl_dbprefix.'hd',
					yjl_SQLString($name, 'text'),
					yjl_SQLString($content, 'text'),
					$isgf,
					$hxqid,
					$isxzrs,
					$xzrs,
					$datetime,
					$sjtid,
					$cs,
					$etime,
					yjl_SQLString($address, 'text'),
					time(),
					$r_res['hdid']);
				$result=mysql_query($uSQL) or die('');
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'活动详情已修改。\');location.href=\'active-'.$xqid.'-'.$hdid.'-edit.html\';</script>';
				exit();
			}
			$isupimg=1;
			$js_a='upimg_a_7(response, \'tt_iv\');';
			$js_s=', \'m\':\'hd\', \'hdid\':\''.$r_res['hdid'].'\'';
			$is_mce=1;
			$js_c.=yjl_uploadjs($js_a, '', $js_s, 'uploadxzhdimg.php', 'hdtt_upload').'
	$(\'#form_text\').tinymce({
		script_url : \'lib/tiny_mce/tiny_mce.js\',
		theme : "advanced",
		plugins : "inlinepopups,paste,xhtmlxtras",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,forecolor,backcolor",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "none",
		theme_advanced_resizing : false,
		language : "cn",
	});';
			$c.='<div class="title">
					<h3>'.$r_res['name'].' 修改详情</h3>
				</div><form class="main_form cre_act" method="post" action="">
					<table>
						<tr>
							<th width="100">标题<b></b></th>
							<td><input type="text" class="text" name="name" value="'.$r_res['name'].'"></td>
						</tr>'.(($udb['isxg']>0 || $udb['qx']==10)?'
						<tr>
							<th></th>
							<td><input type="checkbox" class="radio" name="isgf" value="1"'.($r_res['isgf']>0?' checked="checked"':'').'/>易监理官方活动</td>
						</tr>':'').'
						<tr>
							<th>人数<b></b></th>
							<td><input type="radio" name="isxzrs" value="0" onclick="$(\'#xzrs_i\').hide();"'.($r_res['isxzrs']==0?' checked="checked"':'').' class="radio" id="style01"><label for="style01">不限制</label>
								<input type="radio" name="isxzrs" value="1" onclick="$(\'#xzrs_i\').show();"'.($r_res['isxzrs']==1?' checked="checked"':'').' class="radio" id="style02"><label for="style02">限制人数</label>
								<input type="text" class="codetext" id="xzrs_i" name="xzrs" value="'.$r_res['xzrs'].'"'.($r_res['isxzrs']==1?'':' style="display: none;"').'/>
							</td>
						</tr>
						<tr>
							<th>时间<b></b></th>
							<td><input type="radio" name="sjtid" value="0" onclick="$(\'#lxqs\').hide();$(\'#lxspan_1\').hide();$(\'#lxspan_2\').hide();"'.($r_res['sjtid']==0?' checked="checked"':'').' class="radio"><label>单日</label>
								<input type="radio" name="sjtid" value="1" onclick="$(\'#lxqs\').hide();$(\'#lxspan_1\').show();$(\'#lxspan_2\').hide();"'.($r_res['sjtid']==1?' checked="checked"':'').' class="radio"><label>连续多日</label>
								<input type="radio" name="sjtid" value="2" onclick="$(\'#lxqs\').show();$(\'#lxspan_1\').hide();$(\'#lxspan_2\').show();"'.($r_res['sjtid']==2?' checked="checked"':'').' class="radio"><label>每周举行</label></td>
						</tr>
						<tr>
							<th></th>
							<td class="chk">
								<span id="lxqs" style="float: left;'.($r_res['sjtid']==2?'':'display: none;').'">起始日期</span><select name="datetime_y">';
			for($i=date('Y');$i<=2014;$i++)$c.='<option value="'.$i.'"'.($i==date('Y', $r_res['datetime'])?' selected="selected"':'').'>'.$i.'</option>';
			$c.='</select><span class="yjl_ft">年</span><select name="datetime_m">';
			for($i=1;$i<=12;$i++)$c.='<option value="'.$i.'"'.($i==date('n', $r_res['datetime'])?' selected="selected"':'').'>'.$i.'</option>';
			$c.='</select><span class="yjl_ft">月</span><select name="datetime_d">';
			for($i=1;$i<=31;$i++)$c.='<option value="'.$i.'"'.($i==date('j', $r_res['datetime'])?' selected="selected"':'').'>'.$i.'</option>';
			$c.='</select><span class="yjl_ft">日</span><span id="lxspan_1"'.($r_res['sjtid']==1?'':' style="display: none;"').'><span class="yjl_ft">&nbsp;到&nbsp;</span><select name="datetime1_y">';
			for($i=date('Y');$i<=2014;$i++)$c.='<option value="'.$i.'"'.($i==date('Y', $r_res['etime'])?' selected="selected"':'').'>'.$i.'</option>';
			$c.='</select><span class="yjl_ft">年</span><select name="datetime1_m">';
			for($i=1;$i<=12;$i++)$c.='<option value="'.$i.'"'.($i==date('n', $r_res['etime'])?' selected="selected"':'').'>'.$i.'</option>';
			$c.='</select><span class="yjl_ft">月</span><select name="datetime1_d">';
			for($i=1;$i<=31;$i++)$c.='<option value="'.$i.'"'.($i==date('j', $r_res['etime'])?' selected="selected"':'').'>'.$i.'</option>';
			$c.='</select><span class="yjl_ft">日</span></span><span id="lxspan_2"'.($r_res['sjtid']==2?'':' style="display: none;"').'><span class="yjl_ft">&nbsp; &nbsp;连续</span><input type="text" class="codetext" name="cs" value="'.$r_res['cs'].'"/><span class="yjl_ft">周</span></span>
							</td>
						</tr>
						<tr>
							<th>地点<b></b></th>
							<td>';
			if($xqid>0){
				$c.=(($udb['isxg']>0 || $udb['qx']==10)?'<input type="radio" class="radio" name="isnoxq" value="0"'.($r_res['xqid']==$xqid?' checked="checked"':'').'/><span class="yjl_ft">':'').$xqdb['name'].(($udb['isxg']>0 || $udb['qx']==10)?'&nbsp;&nbsp;</span><input type="radio" class="radio" name="isnoxq" value="1"'.($r_res['xqid']==$xqid?'':' checked="checked"').'/>全部小区':'');
			}else{
				$c.='全部小区';
			}
			$c.='</td>
						</tr>
						<tr>
							<th></th>
							<td><input type="text" class="text" name="address" value="'.$r_res['address'].'"></td>
						</tr>
						<tr>
							<th valign="top">介绍<b></b></th>
							<td><textarea name="content" id="form_text" style="height: 200px;">'.htmlspecialchars(trim($r_res['content']),ENT_QUOTES).'</textarea></td>
						</tr>
						<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="修 改"/>&nbsp;<a href="active-'.$xqid.'-'.$hdid.'.html">返回</a></td>
					</tr>
					</table>
				</form><div class="title">
					<h3>修改海报</h3>
				</div><div id="tt_uv" style="padding-left: 40px;">'.yjl_uploadv_0(1, 'hdtt_upload', 'j/uploadxzhdimg.php', '<input type="hidden" name="is_nu" value="1"/><input type="hidden" name="hdid" value="'.$r_res['hdid'].'"/><input type="hidden" name="m" value="hd"/>').'</div><div id="tt_iv" style="padding-left: 40px;padding-top: 20px;">'.($r_res['url']!=''?'<img src="'.$r_res['url'].'" alt="" width="'.$a_wh_xzhd[0].'" height="'.$a_wh_xzhd[1].'"/>':'').'</div>';
?>