<?php
	
	header ("Content-type: image/png"); 

	$width = 15;
	$height = 200;
	
	// imagecreate (x width, y width)
	$img_handle = @imagecreatetruecolor ( $width, $height ) or die ("Cannot Create image"); 

	// ImageColorAllocate (image, red, green, blue)
	$back_color = ImageColorAllocate ( $img_handle, 0, 0, 0 ); 
	$txt_color = ImageColorAllocate ( $img_handle, 0, 0, 1 );
	
	imagefill( $img_handle, $width, $height, $back_color );
	imagecolortransparent( $img_handle, $back_color );
	ImageStringUp ( $img_handle, 2, 1, 195, $_GET['text'], $txt_color ); 
	
	ImagePng ( $img_handle ); 
	ImageDestroy( $img_handle );
	
?>