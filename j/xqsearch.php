<?php
require_once('../config.php');
require_once('../'.$yjl_tpath.'setting/settings.php');
require_once('../function.php');
$f='xqsearch.php';
$q=(isset($_POST['q']) && trim($_POST['q'])!='')?htmlspecialchars(trim($_POST['q']),ENT_QUOTES):'';
$c='<i>没有符合条件的结果</i>';
if(isset($_GET['home']) && $_GET['home']==1){
	$l1id=intval($_GET['l1id']);
	if($q!=''){
		$c='';
		$ldb=' and (name like '.yjl_SQLString($q, 'search').' or address like '.yjl_SQLString($q, 'search').')';
		if ($l1id == 9) {
			$q_rep=sprintf('select xqid, name from %s where l1id=%s and iskf=1%s order by name', $yjl_dbprefix.'xq', $l1id, $ldb);
		} else {
			$q_rep=sprintf('select xqid, name from %s where l2id=%s and iskf=1%s order by name', $yjl_dbprefix.'xq', $l1id, $ldb);
		}
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		$c_rep=mysql_num_rows($rep);
		if(mysql_num_rows($rep)>0){
			do{
				$c.='<div style="padding: 5px;"><a href="#" data-xqid="'.$r_rep['xqid'].'" onclick="$(\'#xqid\').val(\''.$r_rep['xqid'].'\');$(\'#xqname\').val($(this).html());$(\'#chki_1\').val($(this).html());$(\'#xqs_sdiv\').hide();showermsg(\'\', 1);return false;">'.$r_rep['name'].'</a></div>';
			}while($r_rep=mysql_fetch_assoc($rep));
		}else{
			$c.='<i>没有符合条件的结果<br/>请重新输入</i>';
		}
		mysql_free_result($rep);
		$c.='<input type="hidden" id="sr_c" value="'.$c_rep.'"/>';
	}
}elseif(isset($_GET['l1id']) && intval($_GET['l1id'])>0){
	$ish=(isset($_GET['h']) && $_GET['h']==1)?1:0;
	$t='';
	$l1id=intval($_GET['l1id']);
	$l2id=(isset($_GET['l2id']) && intval($_GET['l2id'])>0)?intval($_GET['l2id']):0;
	$q_res=sprintf('select name from %s where id=%s and level=1 and upid=0 limit 1', $dbprefix.'common_district', $l1id);
	$res=mysql_query($q_res) or die('');
	$r_res=mysql_fetch_assoc($res);
	if(mysql_num_rows($res)>0){
		$t.=$r_res['name'];
		if($l2id>0){
			$q_rep=sprintf('select name from %s where id=%s and level=2 and upid=%s limit 1', $dbprefix.'common_district', $l2id, $l1id);
			$rep=mysql_query($q_rep) or die('');
			$r_rep=mysql_fetch_assoc($rep);
			if(mysql_num_rows($rep)>0){
				$t.='，'.$r_rep['name'];
			}else{
				$l2id=0;
			}
			mysql_free_result($rep);
		}
	}else{
		$l1id=0;
		$l2id=0;
	}
	mysql_free_result($res);
	if($l1id>0){
		$tid=(isset($_GET['t']) && intval($_GET['t'])>0)?intval($_GET['t']):0;
		$ldb=$l2id>0?' and l2id='.$l2id:'';
		if($q!=''){
			$t.='，搜索：'.$q;
			$ldb.=' and (name like '.yjl_SQLString($q, 'search').' or address like '.yjl_SQLString($q, 'search').')';
		}
		$c='<div style="clear: both;font-weight: bold;">'.$t.'</div>';
		$q_rep=sprintf('select xqid, name from %s where l1id=%s and iskf=1%s order by name', $yjl_dbprefix.'xq', $l1id, $ldb);
		$rep=mysql_query($q_rep) or die('');
		$r_rep=mysql_fetch_assoc($rep);
		$c_rep=mysql_num_rows($rep);
		if(mysql_num_rows($rep)>0){
			do{
				$c.='<a style="padding: 10px;" href="#" onclick="'.($tid==2?'$(\'#xqid\').val(\''.$r_rep['xqid'].'\');$(\'#xqname\').val($(this).html());$(\'#chki_2\').val($(this).html());$(\'#xqs_sdiv\').hide();if($(\'#err_div_2\').length>0){$(\'#err_div_2\').hide();$(\'#right_div_2\').show();}':'$(\'#xq_s\').html($(this).html());$(\'#xqid\').val(\''.$r_rep['xqid'].'\');'.($tid>0?'':'if($(\'#nxqtd\').is(\':visible\'))$(\'#nxqtd\').slideUp(500);$(\'#xq_form\').fadeOut(500);$(\'#lightbox_vbg\').fadeOut(500);$(\'#lightbox_v\').fadeOut(500);')).'return false;">'.$r_rep['name'].'</a>';
			}while($r_rep=mysql_fetch_assoc($rep));
			$c.='<div class="clear_v"></div>';
		}else{
			$c.='<i>没有符合条件的结果</i>';
			if($q!='' && $tid==0)$c.='<br/><br/>您可以添加“'.$q.'”，需等待管理员审核<br/><br/><a href="#" onclick="addxq();return false;">添加“<span id="xjxq_n">'.$q.'</a>”</a>';
		}
		mysql_free_result($rep);
	}
}
echo $c;
?>