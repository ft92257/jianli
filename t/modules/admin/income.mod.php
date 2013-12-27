<?php
/**
 * 文件名：income.mod.php
 * 版本号：1.0
 * 最后修改时间：Tue Oct 30 13:16:22 CST 2007
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：广告模块管理
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ID = 0;
	var $_config=array();
	var $configPath="";

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ID = $this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];
		$this->_setConfig();
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'modify':
				$this->Main();
				break;
			case 'domodify':
				$this->DoModify();
				break;
			case 'google':
				$this->Google();
				break;
			case 'baidu':
				$this->Baidu();
				break;
			case 'aijuhe':
				$this->Aijuhe();
				break;
			case 'alimama':
				$this->Alimama();
				break;
			case 'vodone':
				$this->Vodone();
				break;			
			case 'other':
				$this->Other();
				break;
			default:
				$this->Code = 'income_setting';
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function Main()
	{
		$this->Modify();
	}
	
	function Modify()
	{
		$_AD=ConfigHandler::get('ad','ad_list');
		Load::lib('form');
		$enable_radio=FormHandler::YesNoRadio("enable",(int) ((boolean) ConfigHandler::get('ad','enable')));
		
		
		include($this->TemplateHandler->Template('admin/advertisement'));
	}
	
	function DoModify()
	{
		$ad_list_group=$this->Post['ad'];
		$enable = ($this->Post['enable'] ? 1 : 0);
		$domain_list=(array)$this->Post['domain_list'];
		
		foreach ($ad_list_group as $type=>$ad_list)
		{
			foreach ($ad_list as $pos=>$ad)
			{
				$_ad_list_group[$type][$pos]=jstripslashes($ad);
			}
		}
		
		ConfigHandler::set("ad",array('enable'=>$enable,'ad_list'=>$_ad_list_group));
		
		
        
        if($enable != $this->Config['ad_enable'])
        {
            ConfigHandler::update('ad_enable', $enable);
        }    		
		
		
		$this->Messager("修改成功");
	}
	
	
	function _setConfig()
	{
		$ad_list=array(
					'header'=>array("name"=>"顶部广告",'value'=>'header','width'=>"540px"),

					'middle_center'=>array("name"=>"中间广告",'value'=>'middle_center','width'=>"540px"),
					
					'middle_center1'=>array("name"=>"中间广告1",'value'=>'middle_center1','width'=>"540px"),

					'middle_right'=>array("name"=>"右侧广告",'value'=>'middle_right','width'=>"190px"),
					
					'middle_right1'=>array("name"=>"右侧广告1",'value'=>'middle_right1','width'=>"190px"),

					'footer'=>array("name"=>"底部横幅广告",'value'=>'footer','width'=>"800px"),
		);
		
		$ad_list_topic_new=array(
					'header'=>array("name"=>"顶部广告",'value'=>'header','width'=>"540px"),
					
					'middle_left'=>array("name"=>"左侧广告",'value'=>'middle_left','width'=>"150px"),

					'middle_center'=>array("name"=>"中间广告",'value'=>'middle_center','width'=>"540px"),
					
					'middle_center1'=>array("name"=>"中间广告1",'value'=>'middle_center1','width'=>"540px"),

					'middle_right_top'=>array("name"=>"右侧头部广告",'value'=>'middle_right_top','width'=>"190px"),
		
					'middle_right_center'=>array("name"=>"右侧中部广告",'value'=>'middle_right_center','width'=>"190px"),
		
					'middle_right'=>array("name"=>"右侧广告",'value'=>'middle_right','width'=>"190px"),
					
					'middle_right1'=>array("name"=>"右侧广告1",'value'=>'middle_right1','width'=>"190px"),

					'footer'=>array("name"=>"底部横幅广告",'value'=>'footer','width'=>"800px"),
		);
		
		$topic_index_guest = array(
		
						
			'middle_right'=>array("name"=>"右侧广告",'value'=>'middle_right','width'=>"230px"),
			
			
			'footer'=>array("name"=>"底部横幅广告",'value'=>'footer','width'=>"800px"),
		);

				
		$this->_config['topic_']=array('name'=>"网站首页","ad_list"=>$topic_index_guest);
		
		$this->_config['tag_view']=array('name'=>"话题显示页","ad_list"=>$ad_list);
		
		$this->_config['group_new']=array('name'=>"微博广场","ad_list"=>$ad_list_topic_new);
		
		
				
		$this->_config['group_myhome']=array('name'=>"我的首页","ad_list"=>$ad_list);
		
		$this->_config['messager']=array('name'=>"消息提示页面","ad_list"=>array("middle"=>array("name"=>"中间广告",'value'=>'middle','width'=>"800px"),));
		
				$this->_config['vote'] = array( 
			'name' => "投票",
			"ad_list" => array(
				"middle_right" => array(
					"name"=>"右边栏广告",'value'=>'middle_right','width'=>"230px"
				),
				'footer' => array(
					"name"=>"底部横幅广告",'value'=>'footer','width'=>"800px"
				),
			)
		);
		
		
		$this->_config['qun'] = array( 
			'name' => "微群",
			"ad_list" => array(
				"middle_right" => array(
					"name"=>"右边栏广告",'value'=>'middle_right','width'=>"230px"
				),
				'footer' => array(
					"name"=>"底部横幅广告",'value'=>'footer','width'=>"800px"
				),
			)
		);
		
	}
	function Google() 
	{
		include($this->TemplateHandler->Template('admin/income_google'));
		exit;
	}
	function Baidu() 
	{
		include($this->TemplateHandler->Template('admin/income_baidu'));
		exit;
	}
	function Other() 
	{
		include($this->TemplateHandler->Template('admin/income_other'));
		exit;
	}
	function Alimama() 
	{
		include($this->TemplateHandler->Template('admin/income_alimama'));
		exit;
	}
	function Vodone()
	{
		include($this->TemplateHandler->Template('admin/income_vodone'));
		exit;
	}
	function Aijuhe() 
	{
		include($this->TemplateHandler->Template('admin/income_aijuhe'));
		exit;
	}
	
}
?>
