<?php
/**
 *
 * 长文AJAX模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: longtext.mod.php 905 2012-05-07 02:45:34Z wuliyong $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ID = 0;

	var $LongtextLogic;

	function ModuleObject($config)
	{
		$this->MasterObject($config);


		$this->ID = max(0, (int) ($this->Post['id'] ? $this->Post['id'] : $this->Get['id']));

		$this->LongtextLogic = Load::logic('longtext', 1);

		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
            case 'add':
            	$this->Add();
            	break;
            case 'do_add':
            	$this->DoAdd();
            	break;

            case 'modify':
            	$this->Modify();
            	break;
            case 'do_modify':
            	$this->DoModify();
            	break;

            case 'view':
            	$this->View();
            	break;

			default:
				$this->Main();
				break;
		}
	}

	function Main()
    {
        response_text('正在建设中……');
    }

    function Add()
    {
    	$this->_show();
    }
    function DoAdd()
    {
    	$this->_check_login();

    	$longtext = $this->Post['longtext'] ? $this->Post['longtext'] : $this->Get['longtext'];
    	if('' == (trim(strip_tags($longtext))))
    	{
    		json_error('内容不能为空');
    	}
    	$f_rets = filter($longtext);
    	if($f_rets && $f_rets['error'])
    	{
    		json_error('内容 ' . $f_rets['msg']);
    	}

    	$data_length_limit = ($this->Config['topic_cut_length'] * 2);
    	$retval_data = trim(strip_tags($longtext));
    	$retval_data_length = strlen($retval_data);

    	$ret = 0;
    	$ret_msg = '';
    	if($retval_data_length > $data_length_limit)
    	{
	    	$ret = $this->LongtextLogic->add($longtext);
	    	if($ret < 1)
	    	{
	    		json_error('内容添加失败');
	    	}
	    	else
	    	{
	    		$ret_msg = '内容添加成功';
	    	}
    	}
    	else
    	{
    		$ret_msg = '内容长度过短，点击确定按钮直接发起一条微博';
    	}


    	$retval = array(
    		'id' => $ret,
    		'data' => cut_str($retval_data, $data_length_limit, ''),
    	);
    	json_result($ret_msg, $retval);
    }

    function Modify()
    {
    	$this->_show(1);
    }
    function DoModify()
    {
    	;
    }

    function _show($is_modify = 0)
    {
    	$this->_check_login();

    	$longtext_info = array();


    	$action = 'ajax.php?mod=longtext&code=do_add';
    	if($is_modify)
    	{
    		$action = 'ajax.php?mod=longtext&code=do_modify';

	    	$longtext_info = $this->LongtextLogic->get_info($this->ID);

    		if(!$longtext_info)
    		{
    			js_alert_output('请指定一个正确的ID');
    		}
    	}
    	else
    	{
    		$longtext = trim($this->Post['longtext'] ? $this->Post['longtext'] : $this->Get['longtext']);
    		if($longtext)
    		{
    			$longtext_info['longtext'] = $longtext;
    		}
    	}


    	$content_id = trim($this->Post['content_id'] ? $this->Post['content_id'] : $this->Get['content_id']);
    	if(!$content_id)
    	{
    		$content_id = 'i_already';
    	}

    	$button_id = trim($this->Post['button_id'] ? $this->Post['button_id'] : $this->Get['button_id']);
    	if(!$button_id)
    	{
    		$button_id = 'publishSubmit';
    	}

    	$from_cls = trim($this->Post['from_cls'] ? $this->Post['from_cls'] : $this->Get['from_cls']);


    	include($this->TemplateHandler->Template('topic_longtext_info_ajax'));
    }

    function _check_login()
    {
    	$this->initMemberHandler();

		if(MEMBER_ID < 1)
		{
			json_error("请先登录或者注册一个帐号");
		}
    }

    function View()
    {
    	$longtext_info = $this->LongtextLogic->get_info($this->ID, 1);
    	if(!$longtext_info)
    	{
    		    	}
    	else
    	{
    		$this->LongtextLogic->set_views($this->ID, (int) ($longtext_info['views'] + 1));
    	}

    	$longtext_info[longtext] = nl2br($longtext_info[longtext]);
    	    	    	
    	
    			



    	include($this->TemplateHandler->Template('topic_longtext_view_ajax'));
    }

}

?>
