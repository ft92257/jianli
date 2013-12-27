<?php
			$c.='<div class="vge_cnt">
					<h4><span class="mn_ico ico26"></span>基本信息</h4>
					<div class="main_table">
						<table class="tdhover">
							<tr>
								<th>所属区域：</th>
								<td>'.(isset($a_dq)?join(' ', $a_dq):'&nbsp;').'</td>
								<th>小区地址：</th>
								<td>'.($xqdb['address']!=''?$xqdb['address']:'&nbsp;').'</td>
							</tr>
						</table>
					</div>
					<h4><span class="mn_ico ico27"></span>小区简介</h4>
					<p>'.str_replace("\r\n", '<br/>', $xqdb['xqjj']).'</p>
					<h4><span class="mn_ico ico28"></span>交通状况</h4>
					<p>'.str_replace("\r\n", '<br/>', $xqdb['jtzk']).'</p>
					<h4><span class="mn_ico ico29"></span>周边信息</h4>
					<p>'.str_replace("\r\n", '<br/>', $xqdb['zbxx']).'</p>
				</div>';
			if($user_id>0 && ($udb['isxg']>0 || $udb['qx']==10))$c.='<br/><br/>&nbsp; &nbsp; &nbsp;<a href="a_xgxq.php?id='.$xqid.'" target="_blank">修改详情</a>';
?>