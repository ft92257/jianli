<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename images.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:34 1033899987 1577424617 16828 $
 *******************************************************************/

 
  
 
 if( !function_exists('___throwException') ){
 	function ___throwException($str){
 		trigger_error($str, 256);
 	}
 }



 class images { 
         var $img;            var $info;    
         function images($file=null) { 
                 if(!extension_loaded('gd')) {
                 	___throwException("GD库没有加载."); 
                 }
                 if($file){
                 	$this->loadFile($file); 
                 }
                                  register_shutdown_function(array(&$this, '__destruct'));
         } 
          
         function __destruct() { 
                 if(is_resource($this->img)) 
                         imagedestroy($this->img); 
         }
         
         
		          function __call($method, $arg) { 
                 if(substr($method, 0, 3) == 'get') { 
                         $attr = substr($method, 3); 
                         return $this->getInfo($attr); 
                 } 
         }
         
         
         
         function getInfo($attr) {
             $attr = strtolower ( $attr );
             if(isset ( $this->info [$attr] )){
                 return $this->info [$attr];
             }else{
                 return null;
             }
         }
         
         function getWidth(){
         	return $this->getInfo('width');
         }
         
         function getHeight(){
         	return $this->getInfo('height');
         }
         
         function getType(){
         	return $this->getInfo('type');
         }
         
                  function getResource() { 
                 if(isset($this->img)) 
                         return $this->img; 
                 return null; 
         }
  		
		 		 function getImgInfo($key=false){
			if ($key){
				return  isset($this->info[$key]) ? $this->info[$key] : null ;
			}else {
				return $this->info;
			}
		 }
          
         function save($path) { 
                 return $this->_output($path); 
         } 
  
          
         function output($type='gif') {                
                 return $this->_output('stream', $type); 
         } 
  
          
         function loadFile($file) { 
                 if(!file_exists($file)) 
                         ___throwException("指定的文件不存在 => $file"); 
                 $string = file_get_contents($file); 
                 $key = array('width', 'height', 'type'); 
                 $this->info = array_combine($key, array_slice(getimagesize($file), 0, 3)); 
                 $this->info['file'] = $file; 
                 $this->img = imagecreatefromstring($string); 
                 return $this; 
         } 
  
          
         function resize($width, $height, $keepScale=true) { 
                 $srcw   = $this->getWidth(); 
                 $srch   = $this->getHeight(); 
                 if($keepScale) {                        
                                                  if((double)($srcw/$srch) > (double)($width/$height)) 
                         { 
                                 $height = ceil($srch * $width / $srcw); 
                         }else{ 
                                                                  $width = ceil($srcw * $height / $srch); 
                         } 
                 } 
  
                                  $newimg = $this->_createAlphaImage($width, $height); 
                                  imagecopyresampled($newimg, $this->img, 0, 0, 0, 0, $width, $height, $srcw, $srch); 
                 imagedestroy($this->img); 
                 $this->img = $newimg; 
                 $this->info['width'] = $width; 
                 $this->info['height'] = $height; 
                 return $this; 
         } 
  
          
         function _createAlphaImage($width, $height){ 
                 $newimg = imagecreatetruecolor($width, $height); 
                 if($this->getType() == 1){                          $colorCount = imagecolorstotal($this->img); 
                         imagetruecolortopalette($newimg, true, $colorCount); 
                         imagepalettecopy($newimg,$this->img); 
                         $transparentcolor = imagecolortransparent($this->img); 
                         imagefill($newimg,0,0,$transparentcolor); 
                         imagecolortransparent($newimg,$transparentcolor);  
                 }elseif($this->getType() == 3){                          imagealphablending($newimg,false); 
                         $col=imagecolorallocatealpha($newimg,255,255,255,127); 
                         imagefilledrectangle($newimg,0,0,$width,$height,$col); 
                         imagealphablending($newimg,true); 
                 } 
                 return $newimg; 
         } 
  
          
         function thumbnail($width=128,$height=128, $crop=true, $center=true, $path=null) { 
  
                 $destw  = min($this->getWidth(), $width); 
                 $desth = min($this->getHeight(), $height); 
                 if($crop){ 
                         $srcw = $this->getWidth(); 
                         $srch = $this->getHeight();                      
                         $x = $y = 0; 
                         if((double)($srcw/$srch) > (double)($width/$height)) 
                         { 
                                                                  $srcw = ceil($width * $srch/ $height); 
                                                                  if($center) $x = ceil(($this->getWidth() - $srcw) / 2); 
                         }else{ 
                                                                  $srch = ceil($height * $srcw / $width); 
                                                                  if($center) $y = ceil(($this->getHeight() - $srch) / 2); 
                         } 
                                                  $newimg = $this->_createAlphaImage($width, $height); 
                                                  imagecopyresampled($newimg, $this->img, 0, 0, $x, $y, $width, $height, $srcw, $srch); 
                         imagedestroy($this->img); 
                         $this->img = $newimg; 
                         $this->info['width'] = $width; 
                         $this->info['height'] = $height; 
                 }else{ 
                         $this->resize($destw, $desth); 
                 } 
                 if($path) return $this->save($path); 
                 return $this; 
         } 
  
          
         function crop($x, $y, $w, $h){ 
                 $w = min($w, $this->getWidth()); 
                 $h = min($h, $this->getHeight()); 
                 $newimg = $this->_createAlphaImage($w, $h); 
                 imagecopy($newimg, $this->img, 0, 0, $x, $y, $w, $h); 
                 imagedestroy($this->img); 
                 $this->img = $newimg; 
                 $this->info['width'] = $w; 
                 $this->info['height'] = $h; 
                 return $this; 
         } 
  
          
         function wave($grade=5, $dir="h"){ 
                 $w = $this->getWidth(); 
                 $h = $this->getHeight(); 
                 if($dir=="h"){ 
                         for($i=0;$i<$w;$i+=2){ 
                                 imagecopyresampled($this->img,$this->img, $i-2, sin($i/10)*$grade,$i,0,2,$h,2,$h); 
                         } 
                 }else{ 
                         for($i=0;$i<$h;$i+=2){ 
                                 imagecopyresampled($this->img,$this->img, sin($i/10)*$grade,$i-2,0,$i,$w,2,$w,2); 
                         } 
                 } 
                 return $this; 
         } 
  
          
         function textMark($text, $font, $color="#000000", $size=9, $path=null) { 
                 if(!file_exists($font)) 
                         ___throwException("字体文件不可用 => $font"); 
  
                                  $mwidth = $this->getWidth(); 
                 $mheight= $this->getHeight(); 
                  
                 $color = $this->_hexColor($color); 
                 $color = imagecolorallocate($this->img, $color['r'], $color['g'], $color['b']); 
                 $black = imagecolorallocate($this->img, 0, 0, 0); 
                 $alpha = imagecolorallocatealpha($this->img, 230, 230, 230, 40); 
                                  $box = imagettfbbox($size, 0, $font, $text); 
                                  $padding    = 6; 
                 $textheight = $box[1] - $box[7]; 
                 $bgheight   = $textheight + $padding * 2; 
                                  imagefilledrectangle($this->img, 0, $mheight-$bgheight, $mwidth, $mheight, $alpha); 
                                  imagefilledrectangle($this->img, 10, $mheight-$padding-$textheight, 15, $mheight-$padding, $color); 
                                  $texty = $mheight - $bgheight/2 + $textheight/2; 
                 imagettftext($this->img, $size, 0, 20, $texty, $color, $font, $text); 
                 if($path) return $this->save($path); 
                 return $this; 
         } 
  
          
         function waterMark($markImg, $hp='center', $vp='center', $pct=50, $path=null) { 
                                  $srcw = $this->getWidth(); 
                 $srch = $this->getHeight(); 
  
                                  $mark = new self($markImg); 
                 $markw = $mark->getWidth(); 
                 $markh = $mark->getHeight(); 
  
                                  if($markw > $srcw || $markh > $srch) { 
                                                  $mark->resize($srcw-10, $srch-10, true); 
                         $markw = $mark->getWidth(); 
                         $markh = $mark->getHeight(); 
                 } 
          
                                  $arrx = array('left' => 0, 'center' => floor(($srcw - $markw) / 2), 'right' => $srcw - $markw); 
                 $arry = array('top'  => 0, 'center' => floor(($srch - $markh) / 2), 'bottom' => $srch - $markh); 
                 $x = isset($arrx[$hp]) ? $arrx[$hp] : $arrx['center']; 
                 $y = isset($arry[$vp]) ? $arry[$vp] : $arry['center']; 
                  
                                  if($mark->getType() == 3){ 
                                                  imagealphablending($this->img, true); 
                         imagecopy($this->img, $mark->getResource(), $x, $y, 0, 0, $markw, $markh); 
                 }else{ 
                         imagecopymerge($this->img, $mark->getResource(), $x, $y, 0, 0, $markw, $markh, $pct); 
                 } 
                 unset($mark); 
                 if($path) return $this->save($path); 
                 return $this; 
         } 
  
                  function _hexColor($hex) { 
                 $color = hexdec(substr($hex, 1)); 
         return array( 
             "r"     => ($color & 0xFF0000) >> 16, 
             "g" => ($color & 0xFF00) >> 8, 
             "b" => $color & 0xFF 
                 );       
         } 
  
  
                  function _pngalpha($format) { 
                                  if($format == 'png') { 
                         imagealphablending($this->img, false); 
                         imagesavealpha($this->img, true); 
                 } 
         } 
  
                  function _output($path, $type=null) {                
                 $toFile = false; 
                                  if($path !='stream') { 
                         if(!is_dir(dirname($path))) 
                                 ___throwException('指定的路径不可用 =>'.$path); 
                         $type = pathinfo($path, PATHINFO_EXTENSION); 
                         $toFile = true; 
                 } 
                                  $this->_pngalpha($type); 
  
                 if($type == "jpg") $type = "jpeg"; 
                 $func = "image".$type; 
                 if(!function_exists($func)) { 
                         $type = 'gif'; 
                         $func = 'imagegif'; 
                 } 
                 if($toFile) {                        
                         call_user_func($func, $this->img, $path); 
                 } 
                 else 
                 { 
                         if(!headers_sent()) 
							@header("Content-type:image/".$type); 
                         call_user_func($func, $this->img); 
                 } 
                 return $this; 
         } 
 } 
 ?>