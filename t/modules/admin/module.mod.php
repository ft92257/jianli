<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename module.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:38 1667015248 791773829 24003 $
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
		
		$this->Execute();
	}
	
	
	function Execute()
	{
		ob_start();
		
		switch ($this->Get['code'])
		{
			case 'newmodule':
				$this->newModule();
				break;
			case 'addmod':
				$this->addMod();
				break;
			case 'newziduan':
				$this->newZiDuan();
				break;
			case 'addziduan':
				$this->addziduan();
				break;
			case 'editlist':
				$this->editList();
				break;
			case 'editorder':
				$this->editOrder();
				break;
			case 'editname':
				$this->editName();
				break;
			case 'deletesort':
				$this->deleteSort();
				break;
			case 'delziduan':
				$this->delZiDuan();
				break;
			case 'deletefield':
				$this->deleteField();
				break;
			case 'viewmodule':
				$this->viewModule();
				break;
			default:
				$this->main();
				break;		
		}
		
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	
	function main(){
		$sql = "select * from `" . TABLE_PREFIX . "fenlei_module` ORDER BY `list`";
		$query = $this->DatabaseHandler->Query($sql);
		$sort_list = array();
		while($rs = $query->GetRow()){
			$num=$this->DatabaseHandler->ResultFirst("SELECT count(*) AS NUM FROM " . TABLE_PREFIX . "fenlei_sort WHERE mid='$rs[id]' ");
			$sort_list[$rs['id']]['num'] = $num;
			$sort_list[$rs['id']]['name'] = $rs['name'];
			$sort_list[$rs['id']]['sort_id'] = $rs['sort_id'];
			$sort_list[$rs['id']]['list'] = $rs['list'];
			$sort_list[$rs['id']]['style'] = $rs['style'];
			$sort_list[$rs['id']]['config'] = $rs['config'];
			$sort_list[$rs['id']]['comment_type'] = $rs['comment_type'];
		}
		include template('admin/module');
	}
	
		function editList(){
		$list = $this->Post['list'];
		foreach( $list AS $key=>$value){
			$this->DatabaseHandler->Query("UPDATE `" . TABLE_PREFIX . "fenlei_module` SET `list`='$value' WHERE `id`='$key' ");
		}
		$this->Messager("排序修改成功", "admin.php?mod=module");
	}
	
		function editOrder(){
		$id = (int) $this->Get['id'];
		$rs=$this->DatabaseHandler->ResultFirst("SELECT config FROM " . TABLE_PREFIX . "fenlei_module WHERE id='$id' ");
		$array=unserialize($rs);
		$orderList = $this->Post['orderlist'];
		$_listdb = array();
		foreach ($orderList as $key=>$ol){
			$array['field_db'][$key]['orderlist'] = $ol;
			$_listdb[$ol][$key] = $array['field_db'][$key];
		}
		krsort($_listdb);
		$listdb = array();
		foreach ($_listdb as $key=>$rs){
			foreach ($rs as $val) {
				$listdb[$val['field_name']] = $val;
			}
		}
		
		$array['field_db'] = $listdb;
		$config=addslashes(serialize($array));
		$this->DatabaseHandler->Query("UPDATE `" . TABLE_PREFIX . "fenlei_module` SET config='$config' WHERE id='$id' ");
		$this->Messager("排序修改成功","admin.php?mod=module&code=newmodule&id=$id");
	}
	
		function editName(){
		$id = (int) $this->Get['id'];
		$name = $this->Post["name"];
		$hid_name = $this->Post['hid_name'];
		$query = $this->DatabaseHandler->Query("SELECT name from `".TABLE_PREFIX."fenlei_module");
		$name_list = array();
		while(false != ($row = $query->GetRow())){
			$name_list[$row['name']] = $row['name'];
		}
		if(in_array($name,$name_list) && $name != $hid_name){
			$this->Messager("模块的名称已存在，请更换模块名称", -1);
		}
		
		$sql = " update `" . TABLE_PREFIX . "fenlei_module` set `name` = '$name'  where `id` = '$id'";
		$this->DatabaseHandler->Query($sql);
		$this->Messager("修改模块名称成功", "admin.php?mod=fenlei&code=sortlist&id=$id");
	}
	
		function deleteSort(){
		$id = jget('id', 'int');
		$num=$this->DatabaseHandler->ResultFirst("SELECT count(*) AS num FROM " . TABLE_PREFIX . "fenlei_sort WHERE mid='$id'");
		if($num){
			$this->Messager("本模型已有栏目了,你不可以删除,要想删除此模型,你必须先把属于此模型的栏目先删除");
		}
		
		$this->DatabaseHandler->Query(" DELETE FROM `" . TABLE_PREFIX . "fenlei_module` WHERE id='$id'");
		
		if($id>0) $this->DatabaseHandler->Query(" DROP TABLE IF EXISTS `" . TABLE_PREFIX . "fenlei_content_{$id}`");

				$dirname = dirname(__FILE__);
		$dirname_arr = explode('\\modules\\',$dirname);
		$dirname = $dirname_arr[0] . "\\";
		
		unlink($dirname."templates\default\\fenlei\post_$id.html");
		unlink($dirname."templates\default\\fenlei\list_$id.html");
		unlink($dirname."templates\default\\fenlei\detail_$id.html");

		$this->Messager("模块删除成功", "admin.php?mod=module");
	}

		function deleteField(){
		$id = jget('id', 'int');
		$field_name = $this->Get['field'];

		$query=$this->DatabaseHandler->Query("SELECT config FROM `" . TABLE_PREFIX . "fenlei_module` WHERE `id`='$id' ");
		$rsdb = $query->GetRow();
		$array=unserialize($rsdb['config']);
		
		unset($array[field_db][$field_name]);
		unset($array[search_db][$field_name]);
		unset($array[IfListShow][$field_name]);
		
		$config=addslashes(serialize($array));
		$query=$this->DatabaseHandler->Query("UPDATE `" . TABLE_PREFIX . "fenlei_module` SET `config`='$config' WHERE id='$id' ");
		$query=$this->DatabaseHandler->Query("ALTER TABLE `" .TABLE_PREFIX . "fenlei_content_$id` DROP `$field_name`");
		
		$this->Messager("删除字段成功","admin.php?mod=module&code=newmodule&id=$id");
	}
	
		function delZiDuan(){
		$id = jget('id', 'int');
		$hid_id = $this->Get['hid_id'];
		$this->DatabaseHandler->Query(" delete from `".TABLE_PREFIX."fenlei_ziduan` where `id` = '$id' ");
		if($hid_id){
			$this->Messager("删除字段成功","admin.php?mod=module&code=newmodule&id=$hid_id");
		}else{
			$this->Messager("删除字段成功");
		}
	}
	
		function newModule(){
		$id = jget('id', 'int');
		$listdb = array();		
		$fields = array();
		if($id){
			
						$sql = "select * from `" . TABLE_PREFIX ."fenlei_module` where `id` = '$id'" ;
			$query = $this->DatabaseHandler->Query($sql);
			$rs = $query->GetRow();
						$array=unserialize($rs[config]);
			$listdb=$array[field_db];
			
			$query = $this->DatabaseHandler->query("select `id`,`name`,`config` from `".TABLE_PREFIX."fenlei_module` where `id` = '$id' ");
			$rsdb = $query->GetRow();
			
			$array = unserialize($rsdb[config]);
			foreach ($array[field_db] as $key=>$value) {
				$fields[$key] = $key;
			}
		}
		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."fenlei_ziduan ");
		$field_list = array();
		while ($rs = $query->GetRow()){
			$i = 0;
			$field_list[$rs['id']] = $rs;
			if(in_array($rs['field_name'],$fields)){
				$field_list[$rs['id']][che] = " checked ";
			}
						if($rs[form_type]=='text')
			{
				$field_list[$rs['id']]['type'] =" <input type='text'>";
			}
						elseif($rs[form_type]=='textarea')
			{
				$field_list[$rs['id']]['type'] = "<textarea></textarea>";
			}
						elseif($rs[form_type]=='select')
			{
				$detail=explode("\r\n",$rs[form_set]);
				foreach( $detail AS $key=>$value){
					if($value===''){
						continue;
					}
					list($v1,$v2)=explode("|",$value);
					$v2 || $v2=$v1;
					$selshow[$rs['id']].="<option value='$v1' {\$rsdb[{$rs[field_name]}]['{$v1}']}>$v2</option>";
				}
				$field_list[$rs['id']]['type']="<select>".$selshow[$rs['id']]."</select>";
				
			}
						elseif($rs[form_type]=='radio')
			{
				$detail=explode("\r\n",$rs[form_set]);
				foreach( $detail AS $key=>$value){
					if($i>1){
						break;
					}
					if($value===''){
						continue;
					}
					list($v1,$v2)=explode("|",$value);
					$v2 || $v2=$v1;
					$radshow.="<input type='radio' name='rad' value='$v1'>$v2";
					$i++;
				}
				$field_list[$rs['id']]['type']=$radshow;
				unset($radshow);
			}
						elseif($rs[form_type]=='checkbox')
			{
				$detail=explode("\r\n",$rs[form_set]);
				foreach( $detail AS $key=>$value){
					if($i>1){
						break;
					}
					if($value===''){
						continue;
					}
					list($v1,$v2)=explode("|",$value);
					$v2 || $v2=$v1;
					$cheshow.="<input type='checkbox' name='postdb[{$rs[field_name]}][]' value='$v1' {\$rsdb[{$rs[field_name]}]['{$v1}']}>$v2";
					$i++;
				}
				$field_list[$rs['id']]['type']=$cheshow;
				unset($cheshow);
			}
		}
		include template('admin/new_module');
	}
	
		function addMod(){
		
		$postInfo = $this->Post;
		$name = $postInfo['name'];
		$hid_name = $postInfo['hid_name'];
		$id = (int) $postInfo['id'];
		if($name == ''){
			$this->Messager("请输入所创建模块的名称", -1);
		}
				$query = $this->DatabaseHandler->Query("SELECT name from `".TABLE_PREFIX."fenlei_module");
		$name_list = array();
		while(false != ($row = $query->GetRow())){
			$name_list[$row['name']] = $row['name'];
		}
		if(in_array($name,$name_list) && $name != $hid_name){
			$this->Messager("模块的名称已存在，请更换模块名称", -1);
		}
		
		$config = "";	
		$sort_id = "";
		$array = array();
		if($id){
			$table = TABLE_PREFIX . "fenlei_content_" . $id;
		    			$config2 = $this->DatabaseHandler->ResultFirst("select config from `".TABLE_PREFIX."fenlei_module` where `id` = '$id'");
			$che_arr = unserialize($config2);
			$array  = $che_arr;
		}
		$sql_add = "";

				foreach ($postInfo['che'] as $key=>$value) {
			$query = $this->DatabaseHandler->Query("SELECT * from `".TABLE_PREFIX."fenlei_ziduan` where `id` = '$key'");
			$rs = $query->GetRow();
									if($rs['field_type']=='int')
			{
				if( $rs['field_leng']>10 || $rs['field_leng']<1 ){
					$rs['field_leng']=10;
				}
				if($rs['field_leng']<4){
					$sql_add .= "`{$rs['field_name']}` tinyint( $rs[field_leng] ) NOT NULL default '0',";
					if($id){
						if($che_arr[field_db][$rs['field_name']]['field_name']){
						    $this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` CHANGE `{$che_arr[field_db][$rs['field_name']]['field_name']}` `{$rs['field_name']}` tinyint ( $rs[field_leng] ) NOT NULL ");
						}else{
							$this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` ADD `{$rs['field_name']}` tinyint ( $rs[field_leng] ) NOT NULL");
						}
					}
				}else{
					$sql_add .= "`{$rs['field_name']}` INT NOT NULL default '0',";
					if($id){
						if($che_arr[field_db][$rs['field_name']]['field_name']){
						    $this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` CHANGE `{$che_arr[field_db][$rs['field_name']]['field_name']}` `{$rs['field_name']}` INT ( $rs[field_leng] ) NOT NULL ");
						}else{
							$this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` ADD `{$rs['field_name']}` INT ( $rs[field_leng] ) NOT NULL");
						}
					}
				}
			}
			elseif($rs['field_type']=='varchar')
			{
				if( $rs[field_leng]>255 || $rs[field_leng]<1 ){
					$rs[field_leng]=255;
				}
				$sql_add .= "`{$rs['field_name']}` VARCHAR( $rs[field_leng] ) NOT NULL default '0',";
				if($id){
					if($che_arr[field_db][$rs['field_name']]['field_name']){
					    $this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` CHANGE `{$che_arr[field_db][$rs['field_name']]['field_name']}` `{$rs['field_name']}` VARCHAR ( $rs[field_leng] ) NOT NULL ");
					}else{
						$this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` ADD `{$rs['field_name']}` VARCHAR ( $rs[field_leng] ) NOT NULL");
					}
				}
			}
			elseif($rs['field_type']=='mediumtext')
			{
				$sql_add .= "`{$rs['field_name']}` MEDIUMTEXT NOT NULL,";
				if($id){
					if($che_arr[field_db][$rs['field_name']]['field_name']){
					    $this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` CHANGE `{$che_arr[field_db][$rs['field_name']]['field_name']}` `{$rs['field_name']}` MEDIUMTEXT  NOT NULL ");
					}else{
						$this->DatabaseHandler->Query("ALTER TABLE `" . $table ."` ADD `{$rs['field_name']}` MEDIUMTEXT NOT NULL");
					}
				}
			}
			foreach ($rs as $key => $value) {
				$array[field_db][$rs['field_name']][$key] = $value;
			}
			
						if($rs['show']){
				$array[IfListShow][$rs['field_name']]=$rs['field_name'];
			}
						if($rs['search']){
				$array[search_db][$rs['field_name']]=$rs['field_name'];
			}
			
			if($rs['mustfill']){
				$array[mustfill][$rs['field_name']]=$rs['name'];
			}
			
		}
		$config=addslashes(serialize($array));
		
				if($id){
			$this->DatabaseHandler->Query(" update ". TABLE_PREFIX ."fenlei_module set name = '$name' , config = '$config' where id = '$id' ");
			
		}else{
			$sql = "INSERT INTO " . TABLE_PREFIX ."fenlei_module (name,config,sort_id) VALUES ('$name','$config','$sort_id') ";
			$this->DatabaseHandler->Query($sql);
			$id = mysql_insert_id();
			$sql_CT = " CREATE TABLE `" . TABLE_PREFIX . "fenlei_content_{$id}` (
					   `rid` mediumint(7) NOT NULL auto_increment,
					   `id` int(10) NOT NULL default '0',
					   `fid` mediumint(7) NOT NULL default '0',
					   `uid` mediumint(7) NOT NULL default '0',
					   $sql_add
					   PRIMARY KEY  (`rid`),
					   KEY `fid` (`fid`),
					   KEY `id` (`id`),
					   KEY `uid` (`uid`)
				   ) TYPE=MyISAM {$sql_CT} AUTO_INCREMENT=1 ;";
			$this->DatabaseHandler->Query($sql_CT);
		}

				foreach( $array[field_db] AS $key=>$rs){
			$add = "";
			$tpl_p.=$this->make_post_table($rs);
			$tpl_s.=$this->make_show_table($rs);
			if($array[IfListShow][$key]){
				$Temp_list_rs.="<li>{\$rs[{$key}]}</li> ";
				if($rs[form_units]){
					$add = "(".$rs[form_units].")";
					
				}
				$Temp_list_top.="<li>{$rs[name]}$add</li> ";
			}
		}
		$TempSearch_1=$this->make_search_list($array);
		
		$dirname = dirname(__FILE__);
		$dirname_arr = explode('\\modules\\',$dirname);
		$dirname = $dirname_arr[0] . "\\";
		
				$listF=$dirname . "templates\default\\fenlei\list_0.html";
		$sort_tpl=$this->read_file($listF);
		$sort_tpl=str_replace('$TempSearch_1',$TempSearch_1,$sort_tpl);		
		$sort_tpl=str_replace('$Temp_list_rs',$Temp_list_rs,$sort_tpl);
		$sort_tpl=str_replace('$Temp_list_top',$Temp_list_top,$sort_tpl);	

				$postF=$dirname . "templates\default\\fenlei\post_0.html";
		$post_tpl=$this->read_file($postF);	
		$post_tpl=str_replace('$TempLate',$tpl_p,$post_tpl);
		
				$detailF=$dirname . "templates\default\\fenlei\detail_0.html";
		$show_tpl=$this->read_file($detailF);
		$show_tpl=str_replace('$TempLate',$tpl_s,$show_tpl);
		
		$tpl_post=stripslashes($post_tpl);
		$tpl_sort=stripslashes($sort_tpl);
		$tpl_show=stripslashes($show_tpl);

		$this->write_file($dirname."templates\default\\fenlei\post_$id.html",$tpl_post);
		$this->write_file($dirname."templates\default\\fenlei\list_$id.html",$tpl_sort);
		$this->write_file($dirname."templates\default\\fenlei\detail_$id.html",$tpl_show);

		if(!is_writable($dirname."templates\default\\fenlei\post_$id.html")){
			$this->Messager("template\default\\fenlei\post_$id.html模板生成失败",-1);
		}
		if(!is_writable($dirname."templates\default\\fenlei\list_$id.html")){
			$this->Messager("template\default\fenlei\\list_$id.html模板生成失败",-1);
		}
		if(!is_writable($dirname."templates\default\\fenlei\detail_$id.html")){
			$this->Messager("template\default\fenlei\\detail_$id.html模板生成失败",-1);
		}
		$this->Messager("模板生成完毕","admin.php?mod=module&code=newmodule&id=$id");
	}
	
		function newZiDuan(){
		$id = (int) $this->Get['id'];
		$hid_id = $this->Get['hid_id'];
		if($id){
			$query = $this->DatabaseHandler->Query(" select * from `".TABLE_PREFIX."fenlei_ziduan` where id = '$id' ");
			$_rs = $query->GetRow();
			$field_type[$_rs['field_type']] = " selected ";
			$form_type[$_rs['form_type']] = " selected ";
			$mustfill[$_rs['mustfill']] = " checked ";
			$IfListShow[$_rs['show']] = " checked ";
			$IfListSearch[$_rs['search']] = " checked ";
			$action = "edit";
		}else{
						$field_type[mediumtext] = " selected ";
						$mustfill[0] = " checked ";
						$IfListShow[0] = " checked ";
						$IfListSearch[0] = " checked ";
		}
		include template('admin/new_ziduan');
	}
	
		function addziduan(){
        
		$postList = $this->Post;

		if(!strip_tags($postList['name'])){

			$this->Messager("字段名称不能为空",-1);
		}
		if(!ereg("^([a-z])([a-z0-9_]{2,})$",$postList['field_name'])){

			$this->Messager("字段ID字段ID必须为3个字母以上,字母后面可以跟数字",-1);
		}
		$count = $this->DatabaseHandler->ResultFirst("select count(*) from ".TABLE_PREFIX."fenlei_ziduan where field_name = '$postList[field_name]'");
		if($count){
			if($postList[field_name]!=$postList['hid_field_name']){

				$this->Messager("此字段ID已受保护或已存在,请更换一个",-1);
			}
		}
		if($postList['field_type'] <> 'mediumtext'){
			if(trim($postList['field_leng']) == ''){
				$this->Messager("请输入数据库字段长度",-1);
			} else if (!is_numeric(trim($postList['field_leng']))){
				$this->Messager("数据库字段长度必须是数字型",-1);
			}
		}
		if($postList['form_type'] == 'text' && !is_numeric($postList['field_inputleng'])){
			$this->Messager("表单输入框长度必须是数字型",-1);
		}
		$postList[form_units] = strip_tags($postList[form_units]);
		if($postList['action'] == "edit"){
			$sql = " update `".TABLE_PREFIX."fenlei_ziduan` 
					 set 
					     `name` = '$postList[name]',
						 `field_name` = '$postList[field_name]',
						 `field_type` = '$postList[field_type]',
						 `field_leng` = '$postList[field_leng]',
						 `form_type` = '$postList[form_type]',
						 `field_inputleng` = '$postList[field_inputleng]',
						 `form_set` = '$postList[form_set]',
						 `form_units` = '$postList[form_units]',
						 `mustfill` = '$postList[mustfill]',
						 `show` = '$postList[IfListShow]',
						 `search` = '$postList[IfListSearch]'
				     where id = '{$postList[id]}'
						";
		} else {
			$sql = "insert into `".TABLE_PREFIX."fenlei_ziduan` 
						(`name`,`field_name`,`field_type`,`field_leng`,
						`form_type`,`field_inputleng`,`form_set`,`form_units`,
						`mustfill`,`show`,`search`) 
					values('$postList[name]','$postList[field_name]','$postList[field_type]','$postList[field_leng]',
						   '$postList[form_type]','$postList[field_inputleng]','$postList[form_set]','$postList[form_units]',
						   '$postList[mustfill]','$postList[IfListShow]','$postList[IfListSearch]')";
		}
		$this->DatabaseHandler->Query($sql);

		$this->Messager("字段创建成功", "admin.php?mod=module&code=newmodule&id=$postList[hid_id]");
	}
		function viewModule(){
		$mid = (int) $this->Get['mid'];
		$rsdb = array();
		$disabled = "disabled";
		include($this->TemplateHandler->Template("/fenlei/post_$mid"));
	}
	
		function make_post_table($rs){
		if($rs[mustfill]=='2'){
			return ;
		}elseif($rs[mustfill]=='1'){
			$mustfill='<font color=red>*</font>';
		}
				if($rs[form_type]=='text')
		{
			$rs[field_inputleng]>0 || $rs[field_inputleng]=10;
			$show="<tr> 
				     <td width='20%' align='right'>$mustfill{$rs[name]}:<br>{$rs[form_title]}</td>
				     <td width='80%'> 
				       <input type='text' name='postdb[{$rs[field_name]}]' id='atc_{$rs[field_name]}' size='$rs[field_inputleng]' maxlength='$rs[field_inputleng]' value='\$rsdb[{$rs[field_name]}]'> $rs[form_units] 
				     </td>
				   </tr>";
		}
				elseif($rs[form_type]=='time')	
		{	
			$show="<tr> <td width='20%' align='right'>$mustfill{$rs[name]}:</td> <td width='80%'> <input  onclick=\"setday(this,1)\" type='text' name='post_db[{$rs[field_name]}]' id='atc_{$rs[field_name]}' size='20' value='\$rsdb[{$rs[field_name]}]'> $rs[form_units] {$rs[form_title]}</td></tr>";	
		}
				elseif($rs[form_type]=='textarea')
		{
			$show="<tr><td width='20%' align='right'>$mustfill{$rs[name]}:</td><td width='80%'><textarea name='postdb[{$rs[field_name]}]' id='atc_{$rs[field_name]}' cols='50%' rows='8'>\$rsdb[{$rs[field_name]}]</textarea>$rs[form_units] {$rs[form_title]}</td></tr>";
		}
				elseif($rs[form_type]=='select')
		{
			$detail=explode("\r\n",$rs[form_set]);
			foreach( $detail AS $key=>$value){
				if($value===''){
					continue;
				}
				list($v1,$v2)=explode("|",$value);
				$v2 || $v2=$v1;
				$_show.="<option value='$v1' {\$_rs[{$rs[field_name]}]['{$v1}']}>$v2</option>";
			}
			$show="<tr> <td width='20%' align='right'>$mustfill{$rs[name]}:</td><td width='80%'> <select name='postdb[{$rs[field_name]}]' id='atc_{$rs[field_name]}'>$_show</select>$rs[form_units] {$rs[form_title]}</td> </tr>";
		}
				elseif($rs[form_type]=='radio')
		{
			$detail=explode("\r\n",$rs[form_set]);
			foreach( $detail AS $key=>$value){
				if($value===''){
					continue;
				}
				list($v1,$v2)=explode("|",$value);
				$v2 || $v2=$v1;
				$_show.="<input type='radio' name='postdb[{$rs[field_name]}]' value='$v1' {\$_rs[{$rs[field_name]}]['{$v1}']}>$v2";
			}
			$show="<tr> <td width='20%' align='right'>$mustfill{$rs[name]}:</td> <td width='80%'>$_show $rs[form_units] {$rs[form_title]}</td></tr>";
		}
				elseif($rs[form_type]=='checkbox')
		{
			$detail=explode("\r\n",$rs[form_set]);
			foreach( $detail AS $key=>$value){
				if($value===''){
					continue;
				}
				list($v1,$v2)=explode("|",$value);
				$v2 || $v2=$v1;
				$_show.="<input type='checkbox' name='postdb[{$rs[field_name]}][]' value='$v1' {\$_rs[{$rs[field_name]}]['{$v1}']}>$v2";
			}
			$show="<tr> <td width='20%' align='right'>$mustfill{$rs[name]}:</td> <td width='80%'>$_show $rs[form_units] {$rs[form_title]}</td></tr>";
		}
		return $show;
	}
	
		function make_show_table($rs){

		$show="
		<span>{$rs[name]}:</span>{\$rsdb[{$rs[field_name]}]} {$rs[form_units]}<br>
		";
		return $show;
	}
	
		function make_search_list($array)
	{
		$show = "";
		$_show = "";
		foreach($array[field_db] AS $key=>$rs){
			if($array[search_db][$key]){
				$detail=explode("\r\n",$rs[form_set]);

				foreach( $detail AS $key1=>$value){
					$_show1 = "<a href=\"index.php?mod=fenlei&code=\$fid\$addurl&$key=\"><span \$class[$key]['不限']>不限</span></a>";
					if(!$value){
						continue;
					}
					list($v1,$v2)=explode("|",$value);
					$v2 || $v2=$v1;
					$_show .= "<a href=\"index.php?mod=fenlei&code=\$fid\$addurl&$key=$v1\"><span \$class[$key][$v1]>$v2</span></a>";
				}
			    $show.="<div class='cp_h'>
          			  <div class='cp_h_1'>{$rs['name']}</div>
          			  $_show1
          			  $_show
        			</div>";
          			  unset($_show);
			}
		}
		return $show;
	}

	
	function read_file($filename,$method="rb"){
		if($handle=@fopen($filename,$method)){
			@flock($handle,LOCK_SH);
			$filedata=@fread($handle,@filesize($filename));
			@fclose($handle);
		}
		return $filedata;
	}
	
	
	function write_file($filename,$data,$method="rb+",$iflock=1){
		@touch($filename);
		$handle=@fopen($filename,$method);
		if($iflock){
			@flock($handle,LOCK_EX);
		}
		@fputs($handle,$data);
		if($method=="rb+") @ftruncate($handle,strlen($data));
		@fclose($handle);
		@chmod($filename,0777);	
		if( is_writable($filename) ){
			return 1;
		}else{
			return 0;
		}
	}
}
	