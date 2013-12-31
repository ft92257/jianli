<?php
/**
 * 上传文件表
 */

class FileModel extends BaseModel {
	
	/**
	 * 上传文件
	 * @param Array     $filename 表单file控件的name属性
	 * @param String   $type   上传文件类型  
	 * @return Array	  上传成功返回文件保存信息，失败返回错误信息
	 */
	public function upload($filename = 'filename',$type = 'image') {
		//上传文件配置
		$aUploadConfig = array(
			//图片，默认配置
			'image' => 	array(
				'allowExts' => array('jpg', 'gif', 'png', 'jpeg'),
				'maxSize' => 3145728,
				'savePath' => C('TMPL_PARSE_STRING.__FILES__') . '/image/',
				'basePath' => '/image/',
			),
			//声音文件
			'audio' =>	array(
				'allowExts' => array('aac','mp3'),
				'maxSize' => 3145728,
				'savePath' => C('TMPL_PARSE_STRING.__FILES__') . '/audio/',
				'basePath' => '/audio/',
			),
			//头像图片
			'face' => array(
				'allowExts' => array('jpg', 'gif', 'png', 'jpeg'),
				'maxSize' => 3145728,
				'savePath' => C('TMPL_PARSE_STRING.__FILES__') . '/face/',
				'basePath' => '/face/',
				'thumb' => true,
				'thumbMaxWidth' => '24,48,120',
				'thumbMaxHeight' => '24,48,120',
				'thumbPrefix' => '',
				'thumbSuffix' => '_24-24,_48-48,_120-120',
			),
		);
		
		$public_config = array(
			'autoSub' => true,
			'subType' => 'date',
			'saveRule' => 'uniqid',
		);
		$config = array_merge($public_config, $aUploadConfig[$type]);
		
		
		import('ORG.Net.UploadFile');				//引入上传类
		$upload = new UploadFile($config);
			
		//执行上传
		$info = $upload->uploadOne($_FILES[$filename]);
		//执行上传操作
		if(!$info) {						// 上传错误提示错误信息
			return array(
				'status' => 1001,
				'msg' => $upload->getErrorMsg(),
			);
		}else{// 上传成功 获取上传文件信息
			$info = $info[0];
			
			//添加数据库记录
			$data = array(
				'appid' => $this->oApp->id,
				'name' => $info['name'],
				'type' => $info['type'], 
				'extension' => $info['extension'],
				//删除下划线保存
				'thumbs' => str_replace('_', '', $config['thumbSuffix']),	
				'size' => $info['size'],
				'base_url' => $config['basePath'] . $info['savename'],
				'status' => 0,
				'createtime' => time(),
			);
			$info['fileid'] = $this->add($data);
			
			return array(
				'status' => 0,
				'msg' => '上传成功！',
				'data' => $info,
			);
		}
	}
	
	/*
	 * 获取图片（文件）访问地址
	 * @param $file 文件信息数组
	 * @thumb 缩略图标志
	 * @default 默认文件
	 */
	public function getUrl($file, $thumb = '', $default = '') {
		$base = C('HOST_URL') . C('TMPL_PARSE_STRING.__FILES__');
		
		//默认图片
		if (empty($file['base_url'])) {
			return $default ? $base . $default : $base . '/404.jpg';
		}

		if ($thumb == '') {
			return $base . $file['base_url'];
		} else {
			return $base . str_replace('.', '_' . $thumb . '.', $file['base_url']);
		}
	}
	
}
?>