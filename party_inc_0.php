<?php
			if($_SERVER['REQUEST_METHOD']=='POST'){
				$iszz=(isset($_POST['iszz']) && $_POST['iszz']==1)?1:0;
				$zz_name=htmlspecialchars(trim($_POST['zz_name']),ENT_QUOTES);
				$zz_url=yjl_getfurl(htmlspecialchars(trim($_POST['zz_url']),ENT_QUOTES));
				if($zz_name=='')$iszz=0;
				$uSQL=sprintf('update %s set iszz=%s, zz_name=%s, zz_url=%s where hdid=%s', $yjl_dbprefix.'hd',
					$iszz,
					yjl_SQLString($zz_name, 'text'),
					yjl_SQLString($zz_url, 'text'),
					$r_res['hdid']);
				$result=mysql_query($uSQL) or die('');
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">alert(\'赞助商信息已修改。\');location.href=\'active-'.$xqid.'-'.$hdid.'-sponsor.html\';</script>';
				exit();
			}
			$isupimg=1;
			$js_a='upimg_a_7(response, \'tt_iv\');';
			$js_s=', \'m\':\'hd\', \'hdid\':\''.$r_res['hdid'].'\'';
			$js_c.=yjl_uploadjs($js_a, '', $js_s, 'uploadhdzzimg.php', 'hdtt_upload');
			$c.='<style type="text/css">#tt_iv img {width: '.$a_wh_hdadw.'px;}</style><div class="title">
					<h3>'.$r_res['name'].' 赞助商信息修改</h3>
				</div><form class="main_form cre_act" method="post" action="">
					<table>
						<tr>
							<th width="100"></th>
							<td><input type="checkbox" class="radio" name="iszz" value="1" onclick="if($(this).is(\':checked\')){$(\'#zzs_0\').show();$(\'#zzs_1\').show();}else{$(\'#zzs_0\').hide();$(\'#zzs_1\').hide();}"'.($r_res['iszz']>0?' checked="checked"':'').'/>使用赞助商</td>
						</tr>
						<tbody id="zzs_0"'.($r_res['iszz']>0?'':' style="display: none;"').'>
						<tr>
							<th></th>
							<td>请在<a href="active-'.$xqid.'-'.$hdid.'-edit.html">修改详情</a>中将活动名称修改为赞助商冠名，活动海报修改为赞助商标志</td>
						</tr>
						<tr>
							<th>赞助商名称<b>*</b></th>
							<td><input type="text" class="text" name="zz_name" value="'.$r_res['zz_name'].'"></td>
						</tr>
						<tr>
							<th>网站<b></b></th>
							<td><input type="text" class="text" name="zz_url" value="'.$r_res['zz_url'].'"></td>
						</tr>
						</tbody>
						<tr>
						<td></td>
						<td><input type="submit" class="submit sub_reg" value="修 改"/>&nbsp;<a href="active-'.$xqid.'-'.$hdid.'.html">返回</a></td>
					</tr>
					</table>
				</form><div id="zzs_1"'.($r_res['iszz']>0?'':' style="display: none;"').'><div class="title">
					<h3>修改赞助商广告</h3>
				</div><div id="tt_uv" style="padding-left: 40px;">'.yjl_uploadv_0(1, 'hdtt_upload', 'j/uploadhdzzimg.php', '<input type="hidden" name="is_nu" value="1"/><input type="hidden" name="hdid" value="'.$r_res['hdid'].'"/><input type="hidden" name="m" value="hd"/>').'</div><div id="tt_iv" style="padding-left: 40px;padding-top: 20px;">'.($r_res['zz_adimg']!=''?'<img src="'.$r_res['zz_adimg'].'" alt="" width="'.$a_wh_hdadw.'"/>':'').'</div></div>';
?>