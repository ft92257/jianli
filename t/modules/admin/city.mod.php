<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename city.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-07-04 18:49:37 1775934459 506640258 10099 $
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
			case 'addarea':
				$this->addArea();
				break;
			case 'areaorder':
				$this->areaOrder();
				break;
			case 'delarea':
				$this->delArea();
				break;

			case 'city':
				$this->city();
				break;
			case 'addcity':
				$this->addCity();
				break;
			case 'cityorder':
				$this->cityOrder();
				break;
			case 'delcity':
				$this->delCity();
				break;

			case 'zone':
				$this->zone();
				break;
			case 'addzone':
				$this->addZone();
				break;
			case 'zoneorder':
				$this->zoneOrder();
				break;
			case 'delzone':
				$this->delZone();
				break;

			case 'street':
				$this->street();
				break;
			case 'addstreet':
				$this->addStreet();
				break;
			case 'streetorder':
				$this->streetOrder();
				break;
			case 'delstreet':
				$this->delStreet();
				break;
				
			default:
				$this->main();
				break;		
		}
		
		$body = ob_get_clean();
		
		$this->ShowBody($body);
		
	}
	
	function main(){
		$id = (int) $this->Get['id'];
		if($id){
			$province = $this->DatabaseHandler->ResultFirst("select name from `".TABLE_PREFIX."common_district` where id = '{$id}'");
		}
		$query = $this->DatabaseHandler->Query("select * from `".TABLE_PREFIX."common_district` where `upid` = '0' order by list");
		while ($rsdb = $query->GetRow()){
			$rs[$rsdb['id']] = $rsdb;
		}
		include template('admin/area');
	}
	
		function addArea(){
		$area = $this->Post['area'];
		$id = (int) $this->Post['id'];
		if($id){
			$this->DatabaseHandler->Query(" update `".TABLE_PREFIX."common_district` set `name` = '$area' where id = '{$id}'");
			$this->Messager("省份修改成功","admin.php?mod=city");
		}else{
			$area_arr = explode("\r\n",$area);
			foreach ($area_arr as $key => $value) {
				if(!$value){
					continue;
				}
				$this->DatabaseHandler->Query(" insert into `".TABLE_PREFIX."common_district` (`name`,`level`) values ('$value',1)");
			}
			$this->Messager("省份创建成功","admin.php?mod=city");
		}
	}
	
		function areaOrder(){
		$order = $this->Post['order'];
		
		foreach ($order as $key=>$value) {
			$key = (int) $key;
			$value = (int) $value;
			$this->DatabaseHandler->Query("update `".TABLE_PREFIX."common_district` set `list` = '$value' where id = '$key'");
		}
		$this->Messager("排序修改成功","admin.php?mod=city");
	}
	
	function delArea(){
		$fid = (int) $this->Get['fid'];
		$id_arr = array();
		if($fid){
			$id_arr = $this->getNextId($fid);
			$id_arr[$fid] = $fid;
		}
		if($id_arr){
			$id_list = jimplode($id_arr);
			$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."common_district` where id in ($id_list)");
		}
		$this->Messager("省份删除成功","admin.php?mod=city");
	}

	
	function getNextId($upid){
		$id_arr = array();
		$query = $this->DatabaseHandler->Query("select id from ".TABLE_PREFIX."common_district where upid = '$upid'");
		while ($rsdb = $query->GetRow()){
			$id_arr[$rsdb['id']] = $rsdb['id'];
			$id_arr_son = array();
			$id_arr_son = $this->getNextId($rsdb['id']);
			if($id_arr_son){
			    foreach ($id_arr_son as $key=>$val){
				  $id_arr[$val] = $val;
			    }
			}
		}
		return $id_arr;
	}
	
	function city(){
		$area = $this->Get['area'];
		$id = (int) $this->Get['id'];
		if($id){
			$name = $this->DatabaseHandler->ResultFirst("select name from `".TABLE_PREFIX."common_district` where id = '{$id}'");
		}
		$area_option = $this->makeAreaSel($area);		
		include template('admin/city');
	}
		function addCity(){
		$city = $this->Post['city'];
		$id = (int) $this->Post['id'];
		$fup = (int) $this->Post['area'];
		
		if($id){
			$this->DatabaseHandler->Query(" update `".TABLE_PREFIX."common_district` set `name` = '$city',`upid` = '$fup' where id = '{$id}'");
			$this->Messager("城市修改成功","admin.php?mod=city&code=city&area=$fup");
		}else{
		$city_arr = explode("\r\n",$city);
			if($fup == 0){
				$this->Messager("请选择省份",-1);
			}
			foreach ($city_arr as $key => $value) {
				if(!$value){
					continue;
				}
				$this->DatabaseHandler->Query(" insert into `".TABLE_PREFIX."common_district` (`upid`,`name`,`level`) values ('$fup','$value',2)");
			}
			$this->Messager("城市创建成功","admin.php?mod=city&code=city&area=$fup");
		}
	}
	
		function cityOrder(){
		$order = $this->Post['order'];
		$area = $this->Get['area'];
		foreach ($order as $key=>$value) {
			$key = (int) $key;
			$value = (int) $value;
			$this->DatabaseHandler->Query("update `".TABLE_PREFIX."common_district` set `list` = '$value' where id = '$key'");
		}
		$this->Messager("排序修改成功","admin.php?mod=city&code=city&area=$area");
	}
		function delCity(){
		$fid = $this->Get['fid'];
		$area = $this->Get['area'];
		$id_arr = array();
		if($fid){
			$id_arr = $this->getNextId($fid);
			$id_arr[$fid] = $fid;
		}
		if($id_arr){
			$id_list = jimplode($id_arr);
			$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."common_district` where id in ($id_list)");
		}
		$this->Messager("城市删除成功","admin.php?mod=city&code=city&area=$area");
	}

	function zone(){
		$area = $this->Get['area'];
		$city = $this->Get['city'];
		$id = (int) $this->Get['id'];
		if($id){
			$name = $this->DatabaseHandler->ResultFirst("select name from `".TABLE_PREFIX."common_district` where id = '{$id}'");
		}
		$area_option = $this->makeAreaSel($area);	
		include template('admin/zone');
	}
	
		function addZone(){
		$zone = $this->Post['zone'];
		$zone_arr = explode("\r\n",$zone);
				$area = $this->Post['area'];
				$fup = (int) $this->Post['city'];
		
		$id = (int) $this->Post['id'];
		if($id){
			$this->DatabaseHandler->Query(" update `".TABLE_PREFIX."common_district` set `name` = '$zone',`upid` = '$fup' where id = '{$id}'");
			$this->Messager("区域修改成功","admin.php?mod=city&code=zone&area=$area&city=$fup");
		}else{
			if($fup == 0){
				$this->Messager("请选择城市",-1);
			}
			foreach ($zone_arr as $key => $value) {
				if(!$value){
					continue;
				}
				$this->DatabaseHandler->Query(" insert into `".TABLE_PREFIX."common_district` (`upid`,`name`,`level`) values ('$fup','$value',3)");
			}
			$this->Messager("区域创建成功","admin.php?mod=city&code=zone&area=$area&city=$fup");
		}
	}
	
		function zoneOrder(){
		$area = $this->Get['area'];
		$city = $this->Get['city'];
		$order = $this->Post['order'];
		foreach ($order as $key=>$value) {
			$key = (int) $key;
			$value = (int) $value;
			$this->DatabaseHandler->Query("update `".TABLE_PREFIX."common_district` set `list` = '$value' where id = '$key'");
		}
		$this->Messager("排序修改成功","admin.php?mod=city&code=zone&area=$area&city=$city");
	}
		function delZone(){
		$area = $this->Get['area'];
		$city = $this->Get['city'];
		$fid = $this->Get['fid'];
		$id_arr = array();
		if($fid){
			$id_arr = $this->getNextId($fid);
			$id_arr[$fid] = $fid;
		}
		if($id_arr){
			$id_list = jimplode($id_arr);
			$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."common_district` where id in ($id_list)");
		}
		$this->Messager("区域删除成功","admin.php?mod=city&code=zone&area=$area&city=$city");
	}
	
	function street(){
		$area = $this->Get['area'];
		$city = $this->Get['city'];
		$zone = $this->Get['zone'];
		$id = (int) $this->Get['id'];
		if($id){
			$name = $this->DatabaseHandler->ResultFirst("select name from `".TABLE_PREFIX."common_district` where id = '{$id}'");
		}
		$area_option = $this->makeAreaSel($area);
		include template('admin/street');
	}
		function addStreet(){
		$area = $this->Post['area'];
		$city = $this->Post['city'];
		$street = $this->Post['street'];
		$street_arr = explode("\r\n",$street);
		$fup = (int) $this->Post['zone'];
		
		$id = (int) $this->Post['id'];
		if($id){
			$this->DatabaseHandler->Query(" update `".TABLE_PREFIX."common_district` set `name` = '$street',`upid` = '$fup' where id = '{$id}'");
			$this->Messager("街道修改成功","admin.php?mod=city&code=street&area=$area&city=$city&zone=$fup");
		}else{
			if($fup == 0){
				$this->Messager("请选择区域",-1);
			}
			foreach ($street_arr as $key => $value) {
				if(!$value){
					continue;
				}
				$this->DatabaseHandler->Query(" insert into `".TABLE_PREFIX."common_district` (`upid`,`name`,`level`) values ('$fup','$value',4)");
			}
			$this->Messager("街道创建成功","admin.php?mod=city&code=street&area=$area&city=$city&zone=$fup");
		}
	}
	
		function streetOrder(){
		$area = $this->Get['area'];
		$city = $this->Get['city'];
		$zone = $this->Get['zone'];
		$order = $this->Post['order'];
		foreach ($order as $key=>$value) {
			$key = (int) $key;
			$value = (int) $value;
			$this->DatabaseHandler->Query("update `".TABLE_PREFIX."common_district` set `list` = '$value' where id = '$key'");
		}
		$this->Messager("排序修改成功","admin.php?mod=city&code=street&area=$area&city=$city&zone=$zone");
	}
		function delStreet(){
		$area = $this->Get['area'];
		$city = $this->Get['city'];
		$zone = $this->Get['zone'];
		
		$fid = (int) $this->Get['fid'];
		$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."common_district` where id = '$fid'");
		$this->Messager("街道删除成功","admin.php?mod=city&code=street&area=$area&city=$city&zone=$zone");
	}
	
		function makeAreaSel($area){
		$query = $this->DatabaseHandler->Query(" select * from `".TABLE_PREFIX."common_district` where `upid` = '0' order by list ");
		while ($rs = $query->GetRow()){
			if($area == $rs['id']){
			    $area_option .= "\t<option value='{$rs['id']}' selected>{$rs['name']}</option>\t\n";
			}else{
				$area_option .= "\t<option value='{$rs['id']}'>{$rs['name']}</option>\t\n";
			}
		}
		return $area_option;
	}
}
