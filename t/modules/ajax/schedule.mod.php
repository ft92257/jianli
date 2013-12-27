<?php
/**
 * 文件名：schedule.mod.php
 * 版本号：1.0
 * 作     者：狐狸<foxis@qq.com>
 * 创建时间：2010年10月29日
 * 功能描述: 即时任务执行模块
 * @todo 增加同步到人人、开心接口 2011年9月22日
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
    var $ScheduleInfo = array();


	function ModuleObject($config)
	{
		$this->MasterObject($config);
        

		$this->Execute();
	}

	function Execute()
	{
        ob_start();	
           
		switch($this->Code)
		{
            case 'execute':
                $this->DoExecute();
                break;		      

			default:
				$this->Main();
				break;
		}
        
        response_text(ob_get_clean());
	}

	function Main()
    {
        response_text('正在建设中……');
    }
    
    function DoExecute()
    {
        $id = ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']);        
        
                Load::functions('schedule');
        
                if(!($this->ScheduleInfo = schedule_get($id, 1)))
        {
            return false;
        }
        
                if($this->ScheduleInfo['type'] && method_exists($this,($method = "_exec_{$this->ScheduleInfo['type']}")))
        {
                        $this->_init_uid($this->ScheduleInfo['uid']);
            
            $ret = $this->$method();
            
                        response_text((string) $ret);
        }                    
    }
    function _init_uid($uid = 0)
    {
        $uid = max(0, (int) $uid);
        if(!defined('MEMBER_ID') && $uid > 0 && ($members = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."members where `uid`='$uid'")))
        {
            define("MEMBER_ID",(int) $members['uid']);
			define("MEMBER_UCUID",(int) $members['ucuid']);
			define("MEMBER_NAME",$members['username']);
			define("MEMBER_NICKNAME",$members['nickname']);
			define("MEMBER_ROLE_TYPE",$members['role_type']);
        }
        else
        {
            response_text('');
        }            
    }    
    function _exec_imjiqiren_send()
    {
        $return = '';
        $to = '';
        $message = '';
        
        if(imjiqiren_init($this->Config))
        {
            @extract($this->ScheduleInfo['vars']);
            
            $return = imjiqiren_send($to,$message);
        }
        
        return $return;
    }    
    function _exec_sms_send()
    {
        $return = '';
        $to = '';
        $message = '';
        
        if(sms_init($this->Config))
        {
            @extract($this->ScheduleInfo['vars']);
            
            $return = sms_send($to,$message);
        }
        
        return $return;
    }    
    function _exec_syn_to_sina()
    {
        $return = '';
        
        $data = array();
        if(sina_weibo_init($this->Config))
        {
            @extract($this->ScheduleInfo['vars']);
            
            include(ROOT_PATH . 'include/xwb/to_xwb.inc.php');
        }
        
        return $return;
    }
    function _exec_syn_sina_face()
    {
        $return = '';
        if(qqwb_init($this->Config) && (ConfigHandler::get('qqwb','is_sync_face'))) {
            @extract($this->ScheduleInfo['vars']);
            
            $uid = ($uid ? $uid : MEMBER_ID);
            $face = ($face ? $face : jget('face'));            
            $return = sina_weibo_sync_face($uid, $face);
        }        
        return $return;
    }
    
    function _exec_syn_to_qqwb()
    {
        $return = '';
        
        if(false != ($qqwb_config = qqwb_init($this->Config)))
        {
            @extract($this->ScheduleInfo['vars']);
            
            include(ROOT_PATH . 'include/qqwb/to_qqwb.inc.php');
        }        
        
        return $return;
    }
    function _exec_syn_qqwb_face()
    {
        $return = '';
        if(qqwb_init($this->Config) && (ConfigHandler::get('qqwb','is_sync_face'))) {
            @extract($this->ScheduleInfo['vars']);
            
            $uid = ($uid ? $uid : MEMBER_ID);
            $face = ($face ? $face : jget('face'));            
            $return = qqwb_sync_face($uid, $face);
        }        
        return $return;
    }    
	
    function _exec_syn_to_kaixin()
    {
    	$ret = '';
    	
    	if(kaixin_init($this->Config))
    	{
    		$data = $this->ScheduleInfo['vars'];
    		
    		$ret = kaixin_sync($data);
    	}
    	
    	return $ret;
    }
	
    function _exec_syn_to_renren()
    {
    	$ret = '';
    	
    	if(renren_init($this->Config))
    	{
    		$data = $this->ScheduleInfo['vars'];
    		
    		$ret = renren_sync($data);
    	}
    	
    	return $ret;
    }

}

?>
