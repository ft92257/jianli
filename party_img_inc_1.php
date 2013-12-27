<?php
			$pid=intval($_GET['pid']);
			$q_rep=sprintf('select a.*, c.content from %s as a, %s as b, %s as c where a.tid=b.tid and b.tid=c.tid and b.hdid=%s order by a.dateline desc, a.id desc', $dbprefix.'topic_image', $yjl_dbprefix.'hd_topic', $dbprefix.'topic', $hdid);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				do{
					$up=yjl_imgpath($r_rep['id']);
					$r_rep['up']=$up;
					$a_imgl[$r_rep['id']]=$r_rep;
				}while($r_rep=mysql_fetch_assoc($rep));
			}
			mysql_free_result($rep);
			if(isset($a_imgl[$pid])){
				$pdb=$a_imgl[$pid];
				if(!isset($uadb[$pdb['uid']]))$uadb[$pdb['uid']]=yjl_udb($pdb['uid']);
				$c.='<div class="box1 clearfix">
				<div class="pr_pic">
					<a class="mn_ico prev"></a>
					<div class="scrollable" id="scrollable">
						<div class="items">';
				foreach($a_imgl as $k=>$v)$c.='<a href="activeimg-'.$xqid.'-'.$hdid.'-'.$v['id'].'.html" '.($user_id>0?'onclick="loadhdp(\''.$v['id'].'\', \''.$r_res['hdid'].'\');return false;"':'rel="#overlay_login"').'><img src="'.$yjl_tpath.'images/topic/'.$v['up'][1].$v['id'].'_s.jpg" /></a>';
				$c.='</div>
					</div>
					<a class="mn_ico next"></a>
				</div>
				<div class="image_wrap" id="image_wrap">'.yjl_hdimgrightu($pdb, $r_res).'</div>
				<br />
				<div class="comm_wrap clearfix"><div id="hd_topic">'.yjl_hdimgrightc($pdb, $r_res, $page).'</div><br /><br />';
				if($iscy>0){
					$isupimg=1;
					$js_a='upimg_a_0(response);';
					$js_ac='upimg_ac_0();';
					$js_c.=yjl_uploadjs($js_a, $js_ac);
					$c.='<div class="broadcast" style="margin: 0 20px 15px 0;">
				<table>
					<tr>
						<td><textarea style="padding: 5px;" id="content"></textarea></td>
					</tr>
					<tr>
						<td><input type="submit" value="评 论" class="submit sub_smbe" id="submit_fb" onclick="posthdpwb(\''.$r_res['hdid'].'\');" /><input type="hidden" id="pid" value="'.$pdb['id'].'"/></td>
					</tr>
				</table>
				<div class="spdin"><a href="#" onclick="if($(\'#imgu_div\').is(\':hidden\'))$(\'#imgu_div\').show();return false;"><span class="mn_ico ico22"></span>图片</a><a href="#" onclick="if($(\'#wb_v_div\').is(\':hidden\'))$(\'#wb_v_div\').show();return false;"><span class="mn_ico ico23"></span>视频</a><input type="checkbox" name="iszf" id="tpiszf" checked="checked" class="radio"/>同时转发微博</div><div class="wb_imgv">'.yjl_uploadv_3().'</div><div id="wb_v_div" style="display: none;padding-top: 10px;">请复制视频播放页网站地址即可 <input class="text" id="vurl"/></div>
			</div>';
				}
				$c.='<br /><br />
				</div>
			</div>
		</div>
		<div class="main_right">
			<div class="back"><a href="active-'.$xqid.'-'.$hdid.'.html">返回活动页 &#187;</a></div>
			<div class="box2" id="hp_author">'.yjl_hdimgside($pdb, $r_res).'</div>
				<br /><br />
			</div>';
			}else{
				echo '<script type="text/javascript">location.href=\'activeimg-'.$xqid.'-'.$hdid.'.html\';</script>';
				exit();
			}
?>