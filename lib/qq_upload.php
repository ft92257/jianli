<?php
class qqUploadedFileXhr {
	function save($path){
		$input=fopen("php://input", "r");
		$temp=tmpfile();
		$realSize=stream_copy_to_stream($input, $temp);
		fclose($input);
		if($realSize!=$this->getSize())return false;
		$target=fopen($path, "w");
		fseek($temp, 0, SEEK_SET);
		stream_copy_to_stream($temp, $target);
		fclose($target);
		return true;
	}

	function getName(){
		return $_GET['qqfile'];
	}

	function getSize(){
		if(isset($_SERVER["CONTENT_LENGTH"])){
			return (int)$_SERVER["CONTENT_LENGTH"];
		}else{
			throw new Exception('Getting content length is not supported.');
		}
	}
}

class qqUploadedFileForm {
	function save($path){
		if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
			return false;
		}
		return true;
	}

	function getName(){
		return $_FILES['qqfile']['name'];
	}

	function getSize(){
		return $_FILES['qqfile']['size'];
	}
}

class qqFileUploader {
	private $allowedExtensions=array();
	private $sizeLimit=10485760;
	private $file;

	function __construct(array $allowedExtensions=array(), $sizeLimit=10485760){
		$allowedExtensions=array_map("strtolower", $allowedExtensions);
		$this->allowedExtensions=$allowedExtensions;
		$this->sizeLimit=$sizeLimit;
		$this->checkServerSettings();
		if(isset($_GET['qqfile'])){
			$this->file=new qqUploadedFileXhr();
		}elseif(isset($_FILES['qqfile'])){
			$this->file=new qqUploadedFileForm();
		}else{
			$this->file=false;
		}
	}

	private function checkServerSettings(){
		$postSize=$this->toBytes(ini_get('post_max_size'));
		$uploadSize=$this->toBytes(ini_get('upload_max_filesize'));
		if($postSize<$this->sizeLimit || $uploadSize<$this->sizeLimit){
			$size=max(1, $this->sizeLimit/1024/1024).'M';
			die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
		}
	}

	private function toBytes($str){
		$val=trim($str);
		$last=strtolower($str[strlen($str)-1]);
		switch($last){
			case 'g':$val*=1024;
			case 'm':$val*=1024;
			case 'k':$val*=1024;
		}
		return $val;
	}

	function handleUpload($uploadDirectory, $name, $isjpg=0, $replaceOldFile=FALSE){
		if(!is_writable($uploadDirectory))return array('errorid'=>2, 'error'=>'上传路径不可用');
		if(!$this->file)return array('errorid'=>3, 'error'=>'没有文件');
		$size=$this->file->getSize();
		if($size==0)return array('errorid'=>3, 'error'=>'文件为空');
		if($size>$this->sizeLimit)return array('errorid'=>4, 'error'=>'文件太大');
		$pathinfo=pathinfo($this->file->getName());
		$filename=$pathinfo['filename'];
		$ext=$pathinfo['extension'];
		if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions))return array('errorid'=>5, 'error'=>'文件类型错误');
		$fext=$isjpg>0?'jpg':$ext;
		if(!$replaceOldFile){
			while(file_exists($uploadDirectory.$name.'.'.$fext)){
				$name.=rand(10, 99);
			}
		}
		if($this->file->save($uploadDirectory.$name.'.'.$fext)){
			return array('errorid'=>0, 'success'=>true, 'filename'=>$name, 'fileext'=>$fext, 'filesize'=>$size);
		}else{
			return array('errorid'=>1, 'error'=>'上传错误');
		}
	}
}
?>