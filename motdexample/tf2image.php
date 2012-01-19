<?php
	
	// edible stuffz, nom nom
	define( "SPACING", 17 );			// vertical spacing between the servers
	define( "POS_SERVER", 0 );			// horizontal position of the server name
	define( "POS_MAP", 200 ); 			// horizontal position of the map
	define( "POS_PLAYERS", 450 ); 		// horizontal position of the player count
	define( "FONT", '/home/lethal/public_html/motd/tf2/tf2build.ttf' ); // the font to use USE FULL PATH!!!
	define( "FONT_SIZE", 12 );			// size of the font
	define( "IMG_HEIGHT", 350 );		// the height of the image
	define( "FILENAME", "tf2motdservers.png" );				// filename of the image
	
	function drawText( $img, $x, $y, $str, $font_size = FONT_SIZE ) {
		
		$shadow = imagecolorallocate( $img, 0, 0, 0 );
		$text	= imagecolorallocate( $img, 128, 128, 128 );
		
		//imagettftext( $img, $font_size, 0, $x, $y, $shadow, FONT, $str );
		imagettftext( $img, $font_size, 0, $x, $y+1, $text, FONT, $str );
		
	}
	
	// connect to stats database
	mysql_connect( "<servername>", "<username>", "<password>" );
	mysql_select_db( "<databasename>" );

	// get server stats
	$result = mysql_query( "SELECT * FROM servers where type='tf' and showmotd='yes' order by servername" );

	$width = POS_PLAYERS + FONT_SIZE * 4;					// image width
	$height = ( mysql_num_rows( $result ) + 1 ) * SPACING;	// image height
	
	$img = imagecreatetruecolor( $width, IMG_HEIGHT ); // create image
	
	$white = imagecolorallocate( $img, 255, 255, 255 );		// white color
	$grey  = imagecolorallocate( $img, 128, 128, 128 );		// gray color
	$black = imagecolorallocate( $img, 0, 0, 0 );			// black color
	$trans = imagecolorallocatealpha( $img, 0, 0, 0, 127 );	// transparent 'color'
	
	imagesavealpha( $img, true ); // make image transparent
	imagefill( $img, 0, 0, $trans ); // fill the image with it
	
	$y = 15;
	
	drawText( $img, POS_SERVER, $y, 'SERVERS' );
	drawText( $img, POS_MAP, $y, 'MAP' );
	drawText( $img, POS_PLAYERS-25, $y, 'PLAYERS' );
	$y += SPACING * 1.5;
	
	// loop through the servers
	while( $row = mysql_fetch_array( $result ) ) {
		
		$name = explode( "|", $row[ 'servername' ] );
		$name = str_replace( "[EU] Lethal-Zone.eu TF2 ", "", $name[ 0 ] );
		drawText( $img, POS_SERVER, $y, $name );
		
		//imagefill( $img, POS_MAP, $y, $white );
		drawText( $img, POS_MAP, $y, substr( $row[ 'currentmap' ], 0, 16 ) );
		
		//imagefill( $img, POS_PLAYERS, $y, $white );
		drawText( $img, POS_PLAYERS, $y, sprintf( '%02d', $row[ 'currentplayers' ] ) . "/" . sprintf( '%02d', $row[ 'maxplayers' ] ) );
		
		$y += SPACING;
		
	}
	
	drawText( $img, 5, $y + 5, "Type !rules ingame to see our rules", 10 );
	
	//create image
	imagepng( $img, FILENAME );

	//destroy image
	ImageDestroy( $img );
	
	echo "Succes, image saved as " . FILENAME;
?>

