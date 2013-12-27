<?php
/*******************************************************************
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename xwb_plugins_publish.class.php $
 *
 * @Author 鐙愮嫺<foxis@qq.com> $
 *
 * @Date 2010-12-06 04:58:24 $
 *******************************************************************/ 

class xwb_plugins_publish{
	function xwb_plugins_publish(){}
	
	
	function topic( $tid, $totid = 0, $message = '', $imageid = '') 
	{   
        $tid = max(0,(int) $tid);
        $totid = max(0, (int) $totid);	   
		if($tid < 1)
		{
			return false;
		}

		if ($this->_isSyn($tid)) 
		{
			return false;
		}
		
		$db = XWB_plugin::getDB();
		
		$baseurl = XWB_plugin::siteUrl();
				
		if( empty($message) ) 
		{
									return false;
		}
		else
		{
						$postinfo = array();
			$postinfo['message'] = $message;
		}
		
				$message = $this->_convert($postinfo['message']);
		
				$message = $this->_filter($message);

				$message = strip_tags($message);

		
		$link = ' ' .$baseurl . 'index.php?mod=topic&code=' . $tid;
        
        if(function_exists('get_full_url'))
        {
            $link = ' ' . get_full_url($baseurl,'index.php?mod=topic&code=' . $tid);
        }
        
		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   		$message = $this->_substr($message, $length);
		
				$message = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);
		
		$message .= $link;		
			
		
		$wb = XWB_plugin::getWB();
		
		
		if ($totid < 1 && XWB_plugin::pCfg('is_synctopic_toweibo')) 
		{
						$first_img_url = '';
			if( XWB_plugin::pCfg('is_upload_image') && $imageid)
			{
				if(is_numeric($imageid))
				{
					$image_file = "/images/topic/" . jsg_face_path($imageid) . $imageid . "_o.jpg";
					if(is_file(XWB_S_ROOT . $image_file)) 
					{
						$first_img_url = $baseurl . $image_file;
					}
				}
				elseif (is_string($imageid) && false!==strpos($imageid,':/'.'/'))
				{
					$first_img_url = $imageid;
				}
			}
			
			
			$ret = array();
			
						if ($first_img_url) 
			{
				$ret = $wb->upload($message, $first_img_url, null, null, false);
				
				if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] ) 
				{
					$ret = $wb->update($message, false);
				}
			} 
			else 
			{
				$ret = $wb->update($message, false);
			}
			
						if ($ret['id']) 
			{
				                $mid = $ret['id'];              
                $this->_setSynId($tid, $mid);				
						
			}
		}
		
		
		elseif ($totid>0 && XWB_plugin::pCfg('is_syncreply_toweibo')) 
		{	  
			$mid = $this->_isSyn($totid);
			if (!$mid) 
			{
				return false;
			}            
            
			$rs = $wb->comment($mid, $message,null, false);
            
            if($rs['id'])
            {
                $this->_setSynId($tid,$rs['id']);
            }
		}
				
	}


    
    function forShare($tid)
    {
        
        $baseurl = XWB_plugin::siteUrl();
        $topic_url = $baseurl . 'index.php?mod=topic&code=' . $tid;
        if(function_exists('get_full_url'))
        {
            $topic_url = get_full_url($baseurl,'index.php?mod=topic&code=' . $tid);
        }
        $url = ' ' . $topic_url;

        
        $db = XWB_plugin::getDB();
		$topic = $db->fetch_first("SELECT `tid`,`content`,`imageid` FROM " . XWB_S_TBPRE . "topic WHERE tid='{$tid}'");
        if (empty($topic)) return FALSE;

        
		$message = $this->_convert(trim($topic['content']));

		
		$message = $this->_filter($message);

		$message = strip_tags($message);

        
		$message = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);

        
        $message = $message . $url;

        		$img_urls = array();
		if($topic['imageid'] && XWB_plugin::pCfg('is_upload_image'))
        {
			$image_file = "/images/topic/" . jsg_face_path($topic['imageid']) . $topic['imageid'] . "_o.jpg";
			if(is_file(XWB_S_ROOT . $image_file)) 
			{
				$img_urls[] = $baseurl . $image_file;
			}
		}

        return array(
            'url' => $topic_url,
            'message' => $message,
            'pics' => array_map('trim', $img_urls)
        );
    }

    
    function sendShare($message, $pic = '')
    {
        if (empty($message)) return false;

        		$message = $this->_filter($message);

                $wb = XWB_plugin::getWB();
		$ret = array();
        
		if ( ! empty($pic))
        {
			$ret = $wb->upload($message, $pic, null, null, false);
            if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] )
            {
				$ret = $wb->update($message, false);
			}
		}
        else
        {
			$ret = $wb->update($message, false);
		}

        return $ret;
    }
	
	
	
	function _convert($msg) {
		return XWB_plugin::convertEncoding($msg, XWB_S_CHARSET, 'UTF-8');
	}
	
	
	
	function _filter($content) {
				$content = preg_replace('!\[(attachimg|attach)\]([^\[]+)\[/(attachimg|attach)\]!', '', $content);

        
        $content = preg_replace('|\[img(?:=[^\]]*)?](.*?)\[/img\]|', '\\1 ', $content);
        
				$re ="#\[([a-z]+)(?:=[^\]]*)?\](.*?)\[/\\1\]#sim";
		while(preg_match($re, $content)) {
			$content = preg_replace($re, '\2', $content);
		}
		
				$content = preg_replace("#\s+#", ' ', $content);
		$content = trim($content);
		
		return $content;
	}
	
	
	
	
	function _mergeMessage( $subject, $message ){
		$result = '';
		
		if( $subject != '' ){
						if( false !== strpos( $subject , $message ) ){
				$result = $subject;
				return $result;
			}

						if( 0 === strpos( $message, $subject ) ){
				$result = $message;
				return $result;
			}
		}
		
				$result = $subject . ' | ' . $message;
		return $result;
	}
	
	
	
	function _substr($str, $length) {
				if( strlen($str) > $length + 600 ){
			$str = substr($str, 0, $length + 600);
		}
		
		$p = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
		preg_match_all($p,$str,$o);
		$size = sizeof($o[0]);
		$count = 0;
		for ($i=0; $i<$size; $i++) {
			if (strlen($o[0][$i]) > 1) {
				$count += 1;
			} else {
				$count += 0.5;
			}
			
			if ($count  > $length) {
				$i-=1;
				break;
			}
			
		}
		return implode('', array_slice($o[0],0, $i));
	}
	
	
	
	function _isSyn($tid) {
		$db = XWB_plugin::getDB();
		$sql = 'SELECT * FROM ' . XWB_S_TBPRE . 'xwb_bind_topic WHERE `tid`=' . "'{$tid}'";
		$rs = $db->fetch_first($sql);
		if (!$rs) 
		{
			return false;
		}
		return $rs['mid'];
	}

	
	function _setSynId($tid, $mid) 
    {
        $tid = (is_numeric($tid) ? $tid : 0);
        $mid = (is_numeric($mid) ? $mid : 0);
        
        if($tid > 0 && $mid > 0)
        {
            $db = XWB_plugin::getDB();
    		$sql = 'INSERT INTO ' . XWB_S_TBPRE . 'xwb_bind_topic(`tid`,`mid`) VALUES("' .$tid. '", "' . mysql_real_escape_string($mid) . '")';
    		$db->query($sql);
    		if ($db->affected_rows()) 
    		{
    			return true;
    		}
        }
            		
		return false;
	}
	


}
