<?php
			$q_res=sprintf('select * from %s where xqid=%s', $yjl_dbprefix.'xq_fx', $xqid);
			$a_res=mysql_query($q_res) or die('');
			$tr_res=mysql_num_rows($a_res);
			if($tr_res>0){
				$c.='<ul class="list_hsmodel clearfix">';
				$p_size=9;
				$tp_res=ceil($tr_res/$p_size);
				if($page>$tp_res)$page=$tp_res;
				$q_l_res=sprintf('%s limit %d, %d', $q_res, ($page-1)*$p_size, $p_size);
				$res=mysql_query($q_l_res) or die('');
				$r_res=mysql_fetch_assoc($res);
				do{
					$c.='<li><img src="'.($r_res['url']!=''?$r_res['url']:'images/blank.gif" style="background: #fff url(images/jl_d.jpg) no-repeat center;').'" />
						<p>'.$r_res['name'].'<br />'.($r_res['content']!=''?$r_res['content']:'').'</p>
					</li>';
				}while($r_res=mysql_fetch_assoc($res));
				mysql_free_result($res);
				$c.='</ul>';
				if($tp_res>1)$c.=yjl_newhmpage('einfo-'.$xqid.'-style-p[p].html', $page, $tp_res, '', 1);
			}
			mysql_free_result($a_res);
			if($user_id>0 && ($udb['isxg']>0 || $udb['qx']==10))$c.='<br/><br/>&nbsp; &nbsp; &nbsp;<a href="a_fx.php?id='.$xqid.'" target="_blank">户型管理</a>';
?>