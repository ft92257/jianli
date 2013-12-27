<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename wap_global.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 1756768811 1546444133 6714 $
 *******************************************************************/


function wap_page($total_record,$per_page_num,$url='',$_config=array(),$per_page_nums="")
{
	
    $SystemConfig = ConfigHandler::get();

	$result = array();
	$url = wap_iconv($url);
	$total_record = intval($total_record);
	$per_page_num = intval($per_page_num);
	if($per_page_num < 1) $per_page_num = 10;
	$config['total_page'] = max(0,(int) (isset($_config['total_page']) ? $_config['total_page'] : $SystemConfig['total_page_default']));	$config['page_display'] = isset($_config['page_display']) ? (int) $_config['page_display'] : 3;	$config['char'] = isset($_config['char']) ? (string) $_config['char'] : ' ';	$config['url_postfix'] = isset($_config['url_postfix']) ? (string) $_config['url_postfix'] : '';	$config['extra'] = isset($_config['extra']) ? (string) $_config['extra'] : '';	$config['idencode'] = (bool) $_config['idencode'];	$config['var'] = isset($_config['var']) ? (string) $_config['var'] : 'page';	$config['return'] = isset($_config['return']) ? (string) $_config['return'] : 'html';	extract($config);

	$total_page = ceil($total_record / $per_page_num);
	if($config['total_page']>1 && $total_page > $config['total_page'])
	{
		$total_page = $config['total_page'];
	}

	$result['total_page'] = $total_page;
	$current_page=$_GET[$var]?$_GET[$var]:$_POST[$var];
	$current_page = max(1,(int) ((true == $idencode) ? iddecode($current_page) :$current_page));
	$current_page = ($total_page > 0 && $current_page > $total_page) ? $total_page : $current_page;
	$result['current_page'] = $current_page;
	$result['title_postfix'] = $current_page > 1 ? "_第{$current_page}页" : "";
	$result['offset'] = (int) (($current_page - 1) * $per_page_num);

	$result['limit'] = " LIMIT ".$result['offset'].",{$per_page_num} ";

	if(isset($result[$return])) return $result[$return];

	if('' == $url)
	{
		$request = count($_POST) ? array_merge($_GET,$_POST) : $_GET;
		$query_string = '';
		foreach($request as $_var => $_val)
		{
			if(is_string($_val) && $var!==$_var) $query_string .= "&{$_var}=" . urlencode($_val);
		}
		$url = '?'.($result['query_string'] = trim($query_string,'&'));
	}

	$p_val = "V01001page10010V";
	if('/#'!=$url) {
		$url = ('' == $url) ? "?$var={$p_val}" : (($url_no_page = (false !== strpos($url,"&{$var}=") ? preg_replace("/\&?{$var}\=[^\&]*/i",'',$url) : $url)) . "&amp;{$var}={$p_val}");
		if($rewriteHandler)
		{
			$url_no_page = $rewriteHandler->formatURL($url_no_page,false);
			$url=$rewriteHandler->formatURL($url,false);
		}
	} else {
		$url_no_page = $url;
	}
	$result['url'] = $url;

	if(isset($result[$return])) return $result[$return];

	$html = '';
	if($total_record > $per_page_num)
	{
		$halfper = (int) ($config['page_display'] / 2);

		$html=($current_page - 1 >= 1) ? "\n<a href='{$url_no_page}{$url_postfix}'  {$extra}>首页</a>{$char}\n<a href='".(1 == ($previous_page = ($current_page - 1)) ? $url_no_page : str_replace($p_val,(true===$idencode?idencode($previous_page):$previous_page),$url))."{$url_postfix}' {$extra}>上一页</a>{$char}" : "首页{$char}上一页{$char}";

		for ($i=$current_page-$halfper,$i>0 or $i=1,$j=$current_page + $halfper,$j<$total_page or $j=$total_page;$i<=$j;$i++) {
			$html.=($i==$current_page)?"\n<B>".($i)."</B>{$char}":"\n<a href='".(1 == $i ? $url_no_page : str_replace($p_val,(true===$idencode?idencode($i):$i),$url))."{$url_postfix}'  {$extra}>".($i)."</a>{$char}";
		}

		$html.=(($next_page=($current_page + 1)) > $total_page)?"下一页{$char}尾页":"\n<a href='".str_replace($p_val,(true===$idencode?idencode($next_page):$next_page),$url)."{$url_postfix}'  {$extra}>下一页</a>{$char}\n<a href='".str_replace($p_val,(true===$idencode?idencode($total_page):$total_page),$url)."{$url_postfix}'  {$extra}>尾页</a>";

		if(!empty($per_page_nums))
		{
			$per_page_num_list=is_array($per_page_nums)?$per_page_nums:explode(" ",$per_page_nums);
			$current_url=str_replace($p_val,(true===$idencode?idencode($current_page):$current_page),$url).$url_postfix;
			$pn_postfix=$rewriteHandler?$rewriteHandler->argSeparator."pn".$rewriteHandler->varSeparator:"&pn=";
			$per_page_num_select="<select name='per_page_num' onchange=\"window.location='{$current_url}{$pn_postfix}'+this.value\">";
			foreach ($per_page_num_list as $_per_page_num)
			{
				$selected=$_per_page_num==$per_page_num?"selected":"";
				$per_page_num_select.="<option value={$_per_page_num} $selected>{$_per_page_num}";
			}
			$per_page_num_select.="</select>";
		}
		else {
			$per_page_num_select="<B>{$per_page_num}</B>";
		}

		$html ="<div id='page'>{$html}</div>";
	}
	$result['html'] = $html;

	if(isset($result[$return])) return $result[$return];

	return $result;
}


function wap_follow_html($uid,$follow=0,$follow_me=0, $addhtml=true){
	$html = "";
	if(MEMBER_ID>0 && MEMBER_ID!=$uid) {
		if ($follow) {
			if($follow_me) {
				$html = "已互相关注，<a title='已互相关注，点此取消关注' href='index.php?mod=topic&amp;code=dofollow&amp;id={$uid}&amp;act=fans'>取消</a>";
			} else {
								$html = "已关注，<a title='已关注，点此取消' href='index.php?mod=topic&amp;code=dofollow&amp;id={$uid}&amp;act=fans'>取消</a>";
			}
		} else {
						$html = "<a title='点此加关注' href='index.php?mod=topic&amp;code=dofollow&amp;id={$uid}&amp;act=follow'>加关注</a>";
		}
		if($addhtml) $html = "<span id='follow_{$uid}'>{$html}</span>";
	}

	return $html;
}

function wap_my_date_format($time,$format='m月d日 H时i分')
{
	$now = time();

	$t = $now - $time;

    if($t >= 3600)
    {
        $time = my_date_format($time,$format);
    }
    
    elseif ($t < 3600 && $t >= 60)
    {
		$time = floor($t / 60) . "分钟前";
	}
	else
    {
		$time = "刚刚";
	}

	return $time;
}


function wap_iconv($data,$in_charset='',$out_charset='')
{
    if(!$in_charset)
    {
        $sys_config = ConfigHandler::get();
        $in_charset = $sys_config['charset'];

        $out_charset = 'utf-8';
    }

	if($data && strtolower($in_charset)!=strtolower($out_charset))
	{
		$data = array_iconv($in_charset,$out_charset,$data);
	}

    return $data;
}


?>