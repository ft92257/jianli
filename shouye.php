<?php
if ($_COOKIE['isgz']) {
	$url = 'gz.php';
} else {
	$url = 'index.php';
}

header("Location:" . $url);die;
?>