<?php
/**
 *
 * 文件上传相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: upload.han.php 1061 2012-06-28 09:48:25Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class UploadHandler
{
	
    var $_error;

	
	var $_new_name;

	
	var $_save_name;

   
    var $_file;

   
    var $_path;

    
    var $_field;

   
    var $_max_size;

   
    var $_image;

   
    var $_ext;

   
    var $_ext_types;

   
    var $_image_types;



   
    function UploadHandler(& $file, $path, $field = 'upload', $image = false, $attach = false)
    {
    	if(!is_dir($path)) {
    		jmkdir($path);
    	}
    	
        $this->_file       = & $file;
        $this->_path       =  $path;
        $this->_field      =  $field;
        $this->_max_size   =  50;         $this->_image      =  $image;
		$this->_attach     =  $attach;
        $this->_ext        =  '';
        $this->_new_name   =  '';
        $this->_save_name  =  '';
		$this->_attach_types = array('rar','zip','txt','doc','xls','pdf','ppt','docx','xlsx','pptx');
        $this->_ext_types   = array('cgi', 'pl', 'js', 'asp', 'php', 'html', 'htm', 'jsp', 'jar', 'txt', 'rar', 'zip');
        $this->_image_types = array('gif', 'jpg', 'jpeg', 'png');
    }

	
    function setMaxSize($size)
    {
        $this->_max_size = (int) $size;
        return true;
    }

	
    function setExtTypes($array)
    {
        if(false == is_array($array))
        {
            return false;
        }

        $this->_ext_types =& $array;
        return true;
    }

	
    function setImgTypes($array)
    {
        if(false == is_array($array))
        {
            return false;
        }

        $this->_image_types =& $array;
        return true;
    }

	
    function setAttachTypes($array)
    {
        if(false == is_array($array))
        {
            return false;
        }

        $this->_attach_types =& $array;
        return true;
    }
  
	
    function setNewName($name)
    {
        $this->_new_name = trim($name);
        return true;
    }

	
    function getExt()
    {
        return $this->_ext;
    }

	
    function getSaveName()
    {
        return $this->_save_name;
    }


	
	function doUpload()
    {
        if(false == is_writable($this->_path))
        {
            $this->_setError(504);
            return false;
        }

        if(false == isset($this->_file[$this->_field]))
        {
            $this->_setError(501);
            return false;
        }

        $name = $this->_file[$this->_field]['name'];
        $size = $this->_file[$this->_field]['size'];
        $type = $this->_file[$this->_field]['type'];
        $temp = $this->_file[$this->_field]['tmp_name'];

        $type = preg_replace("/^(.+?);.*$/", "\\1", $type);

        if(false == $name || $name == 'none')
        {
            $this->_setError(501);
            return false;
        }

		$this->_ext = strtolower(end(explode('.', $name)));
		
		if(false == $this->_ext)
		{
            $this->_setError(502);
            return false;
		}
        if(false == $this->_image)
        {
            if(false == $this->_attach) {
				if(false == in_array($this->_ext, array_merge($this->_image_types, $this->_ext_types))) {
					$this->_setError(502);
					return false;
				}
			} else {
				if(false == in_array($this->_ext, $this->_attach_types)){
					$this->_setError(508);
					return false;
				}
			}
        } else {
            if(false == in_array($this->_ext, $this->_image_types))
            {
                $this->_setError(507);
                return false;
            }

            if(function_exists('exif_imagetype') && !exif_imagetype($temp)) {
				 $this->_setError(507);
                 return false;
			} elseif (function_exists('getimagesize') && !getimagesize($temp)) {
				$this->_setError(507);
                 return false;
			}           
        }
        

        if($this->_max_size && $this->_max_size * 1000 < $size)
        {
            $this->_setError(503);
            return false;
        }

        if(false == $this->_new_name)
        {
            $this->_save_name = $name;
            $full_path        = $this->_path . $name;
        }
        else {
            $this->_save_name = $this->_new_name;
            $full_path        = $this->_path     . $this->_save_name;
        }
		

		if(false == move_uploaded_file($temp, $full_path))
		{
			if(false == copy($temp,$full_path))
			{
	            $this->_setError(505);
	            return false;
			}
		}

        $this->_setError(506);
        return true;
    }

	
    function getError()
    {
        return $this->_error;
    }
	
	function _GetError() 
	{
		$type=$this->_file[$this->_field]['error'];
		$error_types=array(0=>'没有错误发生，文件上传成功。',
							1=>'上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。',
							2=>'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。',
							3=>'文件只有部分被上传。',
							4=>'没有文件被上传。',
							6=>'找不到临时文件夹。',
							7=>'文件写入失败');
        if(false == isset($error_types[$type]))
        {
            $error_types[$type] = $val;
        }
        $this->_error = $error_types[$type];
        return true;

	}
	


   
    function _setError($type, $val = '')
    {

        $error_types = array(501 => '没有上载的文件',
                             502 => '不允许的扩展名',
                             503 => '上载的文件超过了服务器最大限制的值，上载失败！'.$val,
                             504 => '目录不可写',
                             505 => '移动文件时出错！'.$val,
                             506 => '上载成功',
                             507 => '上载的图片文件不是有效的图片文件',
                             508 => '上载的文件不是有效的附件文件',
			);

        if(false == isset($error_types[$type]))
        {
            $error_types[$type] = $val;
        }
        $this->_error_no=$type;

        $this->_error = $error_types[$type];
        return true;
    }
    
    function getErrorNo()
    {
    	return $this->_error_no;
    }
}

?>