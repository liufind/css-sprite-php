<?php
	require_once "plus/spriteimage.class.php";
	$param = array(
		'srcImages' => glob('./img/1/*'),
		'destImage' => './img/component.png',
		'destCss' => './css/component.css',
		'prefix' => 'cpt',
		'width' => 80,
		'height' => 80,
		'cssPath' => '../img/',
		'mode'=> false
		);
	$sprite = new SpriteImage($param);
	$sprite->sprite();
?>