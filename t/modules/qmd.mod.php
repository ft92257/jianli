<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename qmd.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:35 940392049 1878513031 4748 $
 *******************************************************************/




 

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
		$uid = MEMBER_ID;	
		
				$sql = "select `tid`,`uid`,`username`,`content`,`dateline` from `".TABLE_PREFIX."topic` where `uid` =  '{$uid}' order by `dateline` desc ";
		$query = $this->DatabaseHandler->Query($sql);
		$topic_list = $query->GetRow();		
		$topic_list['dateline'] = date('m月d日 H:s');
		
		
				$members = $this->TopicLogic->GetMember($uid,"`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`topic_count`,`fans_count`,`validate`,`province`,`city`");
	    if(false===strpos($members['face'],':/'.'/'))
	    {
	        $members['face'] = $this->Config['site_url'] . "/" . $members['face'];
	    }

	    	    $pic_path = "images/qmd.jpg";
	    
	    	    $string = strip_tags($members['nickname'].'：'.$topic_list['content']);
	    $content = cut_str($string,70);
	    
        		$qmd_list = $this->_qmd_img_list($pic_path,$members['uid'],$members['face'],$content,$topic_list['dateline']);    
		
		
	}
	
	
	
	function _qmd_img_list($pic_path='',$user_uid=0,$user_face='',$topic_content='',$topic_dateline) 
	{	
		header("Content-type: image/png"); 
		
		        $bg = imagecreatefromjpeg($pic_path);  
        
                $white = imagecolorallocate($bg, 000, 000, 000); 
     
                $content = str_split($topic_content,40);
        $content = array_iconv($this->Config['charset'],'utf-8',$content);
     
                $topic_url = $this->Config['site_url'];
        
                $topic_date = array_iconv($this->Config['charset'],'utf-8',$topic_dateline.' | '.'记事狗微博');
	
        imagettftext($bg, 9, 0, 130, 25, $white, "images/simsun.ttc", $content[0]);
        imagettftext($bg, 9, 0, 130, 45, $white, "images/simsun.ttc", $content[1]);
        
        imagettftext($bg, 9, 0, 130, 70, $white, "images/simsun.ttc", $topic_date);
        imagettftext($bg, 9, 0, 218, 90, $white, "images/simsun.ttc", $topic_url);

 
                $dst_im = imagecreatefromjpeg($bg); 
        $dst_info = getimagesize($bg); 
    
     	        $src = $user_face; 
        $src_im = imagecreatefromjpeg($src); 
        $src_info = getimagesize($src); 
     
              
                $dst_x = 20;
          
                $dst_y  = 12;
          
                $src_x = 0;
          
                $src_y = 0;
          
                $src_w = $src_info[0];
          
                $src_h  = $src_info[1];
          
                $alpha = 100; 
          
                imagecopymerge($bg,$src_im,$dst_x,$dst_y,$src_x,$src_y,$src_w, $src_h,$alpha); 

        
      	
        
        
        
	 	
        $image_path = RELATIVE_ROOT_PATH . 'images/qmd/' . face_path(MEMBER_ID);
        if(!is_dir($image_path))
        {
            Load::lib('io', 1)->MakeDir($image_path);
        }
        
        $image_file =  $image_path . MEMBER_ID . '_o.png';
                imagepng($bg,$image_file);
        imagedestroy($bg);
        
        return $image_file;
    
	}
	

	

}
?>
