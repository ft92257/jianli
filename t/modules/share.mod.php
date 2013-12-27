<?php

/**
 * 站外调用
 *
 * @author 狐狸<foxis@qq.com>
 * @package jishigou.net
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);

		
		$this->TopicLogic = Load::logic('topic', 1);

		$this->CacheConfig = ConfigHandler::get('cache');


		$this->Execute();
	}


	

	function Execute()
	{
		switch($this->Code)
		{
			case 'share':
				$this->ShareLink();
				break;
			case 'show':
				$this->Show();
				break;
			case 'doshare':
				$this->DoShareLink();
				break;
			case 'endshare':
				$this->EndShare();
				break;
			case 'doshare':
				$this->DoShareLink();
				break;
			case 'recommend':
				$this->iframe_recommend();
				break;
			default:
				$this->Main();
				break;
		}

		exit;
	}

		function Main()
	{

	die('建设中。。。');

	}

		function ShareLink()
	{

		$action = 'index.php?mod=share&code=doshare';

		$url     = $this->Get['url'];
		$sbuject = array_iconv('utf-8',$this->Config['charset'],$this->Get['t']);

		$content = $sbuject.' '.$url;

		$return_url = $_SERVER["QUERY_STRING"];

		include  $this->TemplateHandler->Template('share');

	}

		function DoShareLink()
	{
		$action = 'index.php?mod=share&code=doshare';


		        $content = trim(strip_tags((string) $this->Post['content']));

        		$f_rets = filter($content);
		if($f_rets && $f_rets['error']){
          $filter_msg = str_replace("\'",'',$f_rets['msg']);
        }

	 	        $content_length = strlen($content);
        if ($content_length < 2){
            $filter_msg =  "内容不允许为空";
        }

        		$return = $this->TopicLogic->Add($content);

		if(is_array($return))
		{
			$this->Messager(NULL,"{$this->Config['site_url']}/index.php?mod=share&code=endshare");
		}
		else
		{
						$content = trim(strip_tags((string) $this->Post['return_content']));
						$error = $return ? $return : $filter_msg;
			include  $this->TemplateHandler->Template('share');
		}
	}

		function EndShare() {

		include  $this->TemplateHandler->Template('share');
	}



		function iframe_recommend()
	{
		$ids = (int) $this->Get['ids'];

				$sql = " select * from `".TABLE_PREFIX."share` where `id` = '{$ids}' ";
    	$query = $this->DatabaseHandler->Query($sql);
    	$sharelist = $query->GetRow();

    			$share = @unserialize($sharelist['show_style']);
		$topic_charset = $share['topic_charset'];

		
    	if ($sharelist['nickname'])
    	{
    		$nickname = $sharelist['nickname'];

        	if(!empty($nickname))
    		{
    			$nickname = explode('|',$nickname);

    			$sql = "select `uid`,`nickname` from `".TABLE_PREFIX."members` where  `nickname` in ('".implode("','",$nickname)."')";
    			$query = $this->DatabaseHandler->Query($sql);
    			$uids = array();
    			while (false != ($row = $query->GetRow()))
    			{
    				$uids[$row['uid']] = $row['uid'];
    			}

    			if($uids)
    			{

    				$user_topic_list = " `uid` in ('".implode("','",$uids)."') and ";


    			} else{

    				echo('相关用户不存在，请重新指定。');die();
    			}

    		}
    	}


		

    	if ($sharelist['tag'])
    	{
    		$tag  = $sharelist['tag'];

        	if(!empty($tag))
        	{
        		$tagname = explode('|',$tag);

        		$sql = "select * from `".TABLE_PREFIX."tag` where  `name` in ('".implode("','",$tagname)."')";
        		$query = $this->DatabaseHandler->Query($sql);
        		$tagids = array();
        		while (false != ($row = $query->GetRow()))
        		{
        			$tagids[$row['id']] = $row['id'];
        		}

        		if($tagids)
        		{
        			$tag_where_list = " where `tag_id` in ('".implode("','",$tagids)."') ";
        		}
        	}

        	    		$sql = "select `item_id`,`tag_id` from `".TABLE_PREFIX."topic_tag` {$tag_where_list} order by `dateline` desc limit 0,{$share['limit']}";
    		$query = $this->DatabaseHandler->Query($sql);
    		$tids = array();
    		while (false != ($row = $query->GetRow()))
    		{
    			$tids[$row['item_id']] = $row['item_id'];
    		}

    		if($tids)
    		{
    			$tag_condition = "`tid` in  ('".implode("','",$tids)."')";
    		}

    	}

    			if($tag_condition)
		{
			$condition = $tag_condition ." and `type` = 'first' ";

		} elseif($user_topic_list){

			$condition = " {$user_topic_list} `type` = 'first' ";
		} else {
			$condition = " `type` = 'first' ";
		}

				$share['limit'] = max(0, (int) $share['limit']);
		if($share['limit'] < 1)
		{
			$share['limit'] = 20;
		}
		$where = "where {$condition} order by `dateline` desc limit 0,{$share['limit']}";

				$share['string'] = max(0, (int) $share['string']);

		$topic_list = $this->TopicLogic->Get($where);
		
				foreach($topic_list as $k=>$v)
		{
			$topic_list[$k]['content'] = stripslashes($topic_list[$k]['content']);
			if($share['string'] && $share['string'] > 0){
				$topic_list[$k]['content'] = cut_str(strip_tags($topic_list[$k]['content']),$share['string'],'<a>');
			}
		}

		include  $this->TemplateHandler->Template('share/sharetemp_'.$ids);

		$content = ob_get_contents();
		ob_clean();
		
				$content = str_replace(array("\r\n", "\n", "\r"), "", $content);
		$content = str_replace("'","\'",$content);
		$content = str_replace("/http", "http", $content);		
		$content = str_replace("index.php?",$this->Config['site_url'].'/index.php?',$content);
		$content = str_replace($this->Config['site_url']."/".$this->Config['site_url'],$this->Config['site_url'],$content);
		#开启伪静态以后加网址
		$content = str_replace('href="/','href="'.$this->Config['site_url'].'/',$content);
		
		$content = str_replace('target="_blank"',"",$content);
		$content = str_replace('<a', '<a target="_blank"',$content);
			
		$content = preg_replace(array("/[\r\n]+/s","/\>\s+\</","/\>\s+/","/\s+\</"),array("","><",">","<"),$content);
		
				if(strtoupper($this->Config['charset']) != strtoupper($share['topic_charset']))
		{
	        			@header('Content-Type: text/html; charset=' . $share['topic_charset']);

			$content  = array_iconv($this->Config['charset'],$share['topic_charset'],$content);
		}

		echo "document.write('{$content}');";
		die;

	}

}
?>
