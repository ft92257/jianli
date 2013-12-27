<?php
/*******************************************************************
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename xwbUserProfile.class.php $
 *
 * @Author 鐙愮嫺<foxis@qq.com> $
 *
 * @Date 2010-12-06 04:58:24 $
 *******************************************************************/ 


class xwbUserProfile{
	var $uid, $db, $tablepre;
	var $info = null;
	
	function xwbUserProfile() {
		$this->uid = XWB_S_UID;
		$this->db = XWB_plugin::getDB();
		$this->tablepre = XWB_S_TBPRE;
	}

	function get($index = null, $default = null) {
		if( $this->info === null ){
			$sql = 'SELECT `profile` FROM ' . $this->tablepre . 'xwb_bind_info WHERE uid=' . "'{$this->uid}'";
			$this->info = $this->db->fetch_first ( $sql );
		}

		if (empty($this->info['profile'])) {
			if ($default !== null) {
				return $default;
			}
			return array();
		}
		$object = @json_decode($this->info['profile'], true);
		if (!$object ) {
			if ($default !== null) {
				return $default;
			}
			return array();
		}
		if ($index) {
			if (isset($object[$index])) {
				return $object[$index];
			}
			if ($default !== null) {
				return $default;
			}
		}
		return $object;
	}

	function set($key, $value = null) {
		if (!is_array($key)) {
			$key = array($key => $value);
		}
		$data = $this->get();
		foreach ($key as $key=>$value) {
			$data[$key] = $value;
		}
		$data = json_encode($data);
		$sql = 'UPDATE ' . $this->tablepre . 'xwb_bind_info SET `profile`=\'' . addslashes($data). '\' WHERE `uid`='. "'{$this->uid}'";
		$this->db->query($sql);
	}
	
	function del($key) {
		if (!is_array($key)) {
			$key = array($key);
		}
		$data = $this->get();
		foreach ($key as $value) {
			if (isset($data[$value])) {
				unset($data[$value]);
			}
		}
		$data = json_encode($data);
		$sql = 'UPDATE ' . $this->tablepre . 'xwb_bind_info SET `profile`=\'' . $data. '\' WHERE `uid`='. "'{$this->uid}'";
		$this->db->query($sql);
	}

    function get4Tip($sina_uid, $index = null, $default = null) {
		if( $this->info === null ){
			$sql = 'SELECT `profile` FROM ' . $this->tablepre . 'xwb_bind_info WHERE sina_uid=' . "'{$sina_uid}'";
			$this->info = $this->db->fetch_first ( $sql );
        }

		if (empty($this->info['profile'])) {
			if ($default !== null) {
				return $default;
			}
			return array();
		}
		$object = @json_decode($this->info['profile'], true);
		if (!$object ) {
			if ($default !== null) {
				return $default;
			}
			return array();
		}
		if ($index) {
			if (isset($object[$index])) {
				return $object[$index];
			}
			if ($default !== null) {
				return $default;
			}
		}
		return $object;
	}

	function set4Tip($sina_uid, $key, $value = null) {
		if (!is_array($key)) {
			$key = array($key => $value);
		}
		$data = $this->get();
		foreach ($key as $key=>$value) {
			$data[$key] = $value;
		}
		$data = json_encode($data);
		$sql = 'UPDATE ' . $this->tablepre . 'xwb_bind_info SET `profile`=\'' . addslashes($data). '\' WHERE `sina_uid`='. "'{$sina_uid}'";
		$this->db->query($sql);
	}
}
