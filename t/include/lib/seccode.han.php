<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename seccode.han.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-24 16:33:35 1300789719 1332638806 7760 $
 *******************************************************************/




if (!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class Seccode
{
	var $code;

	var $width = 100;

	var $height = 40;

	var $adulterate = '1';
	var $angle = '0';
	var $shadow = '1';
	var $datapath = '';

	var $im;
	var $fontcolor;


	
	function __construct(){}
	function Seccode(){}

		function display()
	{
		$seccode = $this->code;
		if(function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') &&
		function_exists('imagecolorallocate') && function_exists('imagechar') && function_exists('imagecolorsforindex') &&
		function_exists('imageline') && function_exists('imagecreatefromstring') && (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg'))) {

			$bgcontent = $this->background();

			$this->im = imagecreatefromstring($bgcontent);
			if($this->adulterate) {
				$this->adulterate();
			}
			$this->giffont();

			if(function_exists('imagepng')) {
				header('Content-type: image/png');
				imagepng($this->im);
			} else {
				header('Content-type: image/jpeg');
				imagejpeg($this->im, '', 100);
			}
			imagedestroy($this->im);
				
		} else {
				
			$numbers = array
			(
				'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
				'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
				'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
				'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
				'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
				'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
				'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
				'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
				'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
				'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
				'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
				'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
				'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
				'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
				'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
				'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
				'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
				'2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
				'3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
				'4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
				'6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
				'7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
				'8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
				'9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
			);

			foreach($numbers as $i => $number) {
				for($j = 0; $j < 6; $j++) {
					$a1 = substr('012', mt_rand(0, 2), 1).substr('012345', mt_rand(0, 5), 1);
					$a2 = substr('012345', mt_rand(0, 5), 1).substr('0123', mt_rand(0, 3), 1);
					mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
					mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
				}
			}

			$bitmap = array();
			for($i = 0; $i < 20; $i++) {
				for($j = 0; $j < 4; $j++) {
					$n = substr($seccode, $j, 1);
					$bytes = $numbers[$n][$i];
					$a = mt_rand(0, 14);
					array_push($bitmap, $bytes);
				}
			}

			for($i = 0; $i < 8; $i++) {
				$a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
				array_unshift($bitmap, $a);
				array_push($bitmap, $a);
			}

			$image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
					'0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));

			header('Content-Type: image/bmp');
			echo $image;
		}
	}

	
	function background()
	{
		$im = imagecreatetruecolor($this->width, $this->height);
		$backgroundcolor = imagecolorallocate($im, 255, 255, 255);

		for($i = 0;$i < 3;$i++) {
			$start[$i] = mt_rand(200, 255);
			$end[$i] = mt_rand(100, 245);
			$step[$i] = ($end[$i] - $start[$i]) / $this->width;
			$c[$i] = $start[$i];
		}
		for($i = 0;$i < $this->width;$i++) {
			$color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
			imageline($im, $i, 0, $i-$this->angle, $this->height, $color);
			$c[0] += $step[0];
			$c[1] += $step[1];
			$c[2] += $step[2];
		}
		$c[0] -= 20;
		$c[1] -= 20;
		$c[2] -= 20;

		ob_start();
		if(function_exists('imagepng')) {
			imagepng($im);
		} else {
			imagejpeg($im, '', 100);
		}
		imagedestroy($im);
		$bgcontent = ob_get_contents();
		ob_end_clean();
		$this->fontcolor = $c;
		return $bgcontent;
	}

	function adulterate()
	{
		$linenums = $this->height / 10;
		for($i=0; $i <= $linenums; $i++) {
			$color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
			$x = mt_rand(0, $this->width);
			$y = mt_rand(0, $this->height);
			if(mt_rand(0, 1)) {
				imagearc($this->im, $x, $y, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, 360), mt_rand(0, 360), $color);
			} else {
				imageline($this->im, $x, $y, mt_rand(0, 20), mt_rand(0, mt_rand($this->height, $this->width)), $color);
			}
		}
	}

	function giffont()
	{
		$seccode = $this->code;
		$seccodedir = array();
		if(function_exists('imagecreatefromgif')) {
			$seccoderoot = $this->datapath.'gif/';
			$dirs = opendir($seccoderoot);
			while($dir = readdir($dirs)) {
				if($dir != '.' && $dir != '..' && file_exists($seccoderoot.$dir.'/9.gif')) {
					$seccodedir[] = $dir;
				}
			}
		}
		$widthtotal = 0;
		for($i = 0; $i <= 3; $i++) {
			$imcodefile = $seccodedir ? $seccoderoot.$seccodedir[array_rand($seccodedir)].'/'.strtolower($seccode[$i]).'.gif' : '';
			if(!empty($imcodefile) && file_exists($imcodefile)) {
				$font[$i]['file'] = $imcodefile;
				$font[$i]['data'] = getimagesize($imcodefile);
				$font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
				$font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
				$font[$i]['width'] += mt_rand(0, $this->width / 5 - $font[$i]['width']);
				$widthtotal += $font[$i]['width'];
			} else {
				$font[$i]['file'] = '';
				$font[$i]['width'] = 8 + mt_rand(0, $this->width / 5 - 5);
				$widthtotal += $font[$i]['width'];
			}
		}
		$x = mt_rand(1, $this->width - $widthtotal);
		$c = $this->fontcolor;
		for($i = 0; $i <= 3; $i++) {
			if($font[$i]['file']) {
				$imcode = imagecreatefromgif($font[$i]['file']);
				$y = mt_rand(0, $this->height - $font[$i]['height']);
				if($this->shadow) {
					$imcodeshadow = $imcode;
					imagecolorset($imcodeshadow, 0 , 255 - $c[0], 255 - $c[1], 255 - $c[2]);
					imagecopyresized($this->im, $imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
				}
				imagecolorset($imcode, 0 , $c[0], $c[1], $c[2]);
				imagecopyresized($this->im, $imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
			} else {
				$y = mt_rand(0, $this->height - 20);
				if($this->shadow) {
					$text_shadowcolor = imagecolorallocate($this->im, 255 - $c[0], 255 - $c[1], 255 - $c[2]);
					imagechar($this->im, 5, $x + 1, $y + 1, $seccode[$i], $text_shadowcolor);
				}
				$text_color = imagecolorallocate($this->im, $c[0], $c[1], $c[2]);
				imagechar($this->im, 5, $x, $y, $seccode[$i], $text_color);
			}
			$x += $font[$i]['width'];
		}
	}
}

?>