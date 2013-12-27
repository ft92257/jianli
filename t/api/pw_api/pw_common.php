<?php
!defined('P_W') && exit('Forbidden');


function Pcv($fileName, $ifCheck = true) {
	return S::escapePath($fileName, $ifCheck);
}

function pwConvert($str, $toEncoding, $fromEncoding, $ifMb = true) {
	if (strtolower($toEncoding) == strtolower($fromEncoding)) {return $str;}
	if (is_array($str)) {
		foreach ($str as $key => $value) {
			$str[$key] = pwConvert($value, $toEncoding, $fromEncoding, $ifMb);
		}
		return $str;
	} else {
		return mb_convert_encoding($str, $toEncoding, $fromEncoding);
	}
}

function pwCreditNames($creditType = null) {
	static $sCreditNames = null;
	if (!isset($sCreditNames)) {
			$sCreditNames['credit'] = '积分';
	}
	return isset($creditType) ? $sCreditNames[$creditType] : $sCreditNames;
}


?>