<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='deljpimg.php';
$user_id=0;
$udb=yjl_chkulog();
if($udb['uid']>0){
	$user_id=$udb['uid'];
	$clid=(isset($_GET['cl']) && intval($_GET['cl'])>0)?intval($_GET['cl']):0;
	$adb=($udb['qx']==10 || $udb['isxg']>0)?'':' and a.uid='.$user_id;
	$q_res=sprintf('select a.clid, a.url, a.t_url, a.o_url, a.tid, a.datetime, a.uid from %s as a, %s as b where a.clid=%s and a.jlid=b.jlid%s limit 1', $yjl_dbprefix.'jl_cl', $yjl_dbprefix.'jl', $clid, $adb);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$dSQL=sprintf('delete from %s where clid=%s', $yjl_dbprefix.'jl_cl', $r_res['clid']);
		$result=mysql_query($dSQL) or die('');
		$dSQL=sprintf('delete from %s where clid=%s', $yjl_dbprefix.'jl_topic', $r_res['clid']);
		$result=mysql_query($dSQL) or die('');
		unlink('../'.$r_res['url']);
		unlink('../'.$r_res['t_url']);
		unlink('../'.$r_res['o_url']);
		if($r_res['tid']>0){
			$q_rep=sprintf('select a.tid, b.username, b.password, d.app_key, d.app_secret from %s as a, %s as b, %s as c, %s as d where a.tid=%s and a.uid=b.uid and a.tid=c.tid and c.item_id=d.id limit 1', $dbprefix.'topic', $dbprefix.'members', $dbprefix.'topic_api', $dbprefix.'app', $r_res['tid']);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				require_once('../lib/jishigouapi.class.php');
				$JishiGouAPI=new JishiGouAPI($yjl_url.$yjl_tpath.'api.php', $r_rep['app_key'], $r_rep['app_secret'], $r_rep['nickname'], md5($r_rep['nickname'].$r_rep['password']));
				$jsg_reqult=$JishiGouAPI->DeleteTopic($r_rep['tid']);
			}
			mysql_free_result($rep);
		}
	}
	mysql_free_result($res);
	$jlid=(isset($_GET['id']) && intval($_GET['id'])>0)?intval($_GET['id']):1;
	$lid=(isset($_GET['l']) && isset($a_clys[$_GET['l']]))?$_GET['l']:1;
	$hs=5;
	$zs=$hs*4;
	$q_rep=sprintf('select * from %s where jlid=%s and lid=%s order by datetime desc', $yjl_dbprefix.'jl_cl', $jlid, $lid);
	$rep=mysql_query($q_rep) or die('');
	$r_rep=mysql_fetch_assoc($rep);
	$c_rep=mysql_num_rows($rep);
	if($c_rep>0){
		$tp=ceil($c_rep/$zs);
		$j=0;
		do{
			if(($j%$zs)==0){
				if($j>0){
					echo '</ul>';
					if($tp>1)echo '<div class="jlleftimg_pv"><a href="#" onclick="$(\'#climg_p_'.$lid.'_'.($j/$zs-1).'\').hide();$(\'#climg_p_'.$lid.'_'.($j/$zs).'\').show();return false;" style="display: block;float: right;">下一页</a>'.(($j/$zs)>1?'<a href="#" onclick="$(\'#climg_p_'.$lid.'_'.($j/$zs-1).'\').hide();$(\'#climg_p_'.$lid.'_'.($j/$zs-2).'\').show();return false;">上一页</a>':'').'</div>';
					echo '</span>';
				}
				echo '<span id="climg_p_'.$lid.'_'.($j/$zs).'"'.($j>0?' style="display: none;"':'').'><ul>';
			}
			echo '<li><a href="#" onclick="openclimg(\''.$r_rep['clid'].'\', \''.$r_rep['lid'].'\', \''.$jlid.'\');return false;" class="climg_a"><img src="images/blank.gif" id="climg_s_'.$r_rep['clid'].'" jq_o="'.$r_rep['o_url'].'" jq_w="'.$r_rep['width'].'" jq_h="'.$r_rep['height'].'" width="'.$a_wh_jltpt[0].'" height="'.$a_wh_jltpt[1].'" title="'.$r_rep['content'].'" class="user_pic_v" style="background-image: url('.$r_rep['t_url'].');"/></a></li>';
			$j++;
		}while($r_rep=mysql_fetch_assoc($rep));
		echo '</ul>';
		if($tp>1)echo '<div class="jlleftimg_pv"><a href="#" onclick="$(\'#climg_p_'.$lid.'_'.($tp-1).'\').hide();$(\'#climg_p_'.$lid.'_'.($tp-2).'\').show();return false;">上一页</a></div>';
		echo '</span><input type="hidden" id="cl_cjp_'.$lid.'" value="1"/>';
	}else{
		echo '<input type="hidden" id="cl_cjp_'.$lid.'" value="0"/>';
	}
	mysql_free_result($rep);
}
?>