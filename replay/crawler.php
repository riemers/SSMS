<?php
	include("../config.php");
	
	// EDIBLES, NOM NOM
	define( "CRAWLER_DELAY", 60 * 60 * 24 * 2 ); // delay in seconds before a new match is first crawled 
	define( "CRAWLER_RANGE", 60 * 2 ); // range in seconds of the matches a crawl will cover
	define( "CRAWLER_REPEAT_PERIOD", 60 * 60 * 24 * 7 ); // period in seconds it will look back to recrawl matches
	define( "CRAWLER_REPEAT_COUNT", 4 ); // amount of previous periods it will recrawl matches of
	
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata_YouTube');
	
	mysql_connect( "$host", "$user", "$pass" ) or die( mysql_error() );
	mysql_select_db( "$table" ) or die( mysql_error() );
	
	$period = time() - CRAWLER_DELAY;
	
	$crawl_string = '';
	for( $i = 0; $i < ( CRAWLER_REPEAT_COUNT + 1 ); $i++ )
		$crawl_string .= ( $i == 0 ? '' : ' OR ' ) . 'matchdate BETWEEN FROM_UNIXTIME(' . ( $period - CRAWLER_REPEAT_PERIOD * $i - CRAWLER_RANGE ) . ") AND FROM_UNIXTIME(" . ( $period - CRAWLER_REPEAT_PERIOD * $i ) . ")";
	
	$matches = array();
	$result = mysql_query( "SELECT * FROM matchids WHERE " . $crawl_string . " LIMIT 0, 10" );
	while( $row = mysql_fetch_array( $result ) )
		if( $row[ 'sessionid' ] > 1000000 )
			$matches[ "match_" . strtoupper( dechex( $row[ 'sessionid' ] ) ) ] = array( $row[ 'serverid' ], $row[ 'mapname' ], $row[ 'matchdate' ] );
	
	echo 'Crawling ' . count( $matches ) . ' match(es)<br/>';
	$matchlist = implode( ' | ', array_keys( $matches ) ); 
	
	if( empty( $matchlist ) ) Die();
	
	echo $matchlist . "<br/>";
	$yt = new Zend_Gdata_YouTube();
	$yt->setMajorProtocolVersion( 2 );
	
	$query = $yt->newVideoQuery();
	$query->setVideoQuery( $matchlist );
	$query->setMaxResults( 50 );

	$videoFeed = $yt->getVideoFeed( $query->getQueryUrl( 2 ) );
	
	foreach ( $videoFeed as $videoEntry ) {
		preg_match( "/match_([0-9a-f]{6,})/", implode( "\n", $videoEntry->getVideoTags() ), $matchid );
		$authobj = $videoEntry->getAuthor();
		$matchinfo = $matches[ 'match_' . strtoupper( $matchid[ 1 ] ) ];
		mysql_query( "INSERT INTO videos ( youtubeid, youtubeuser, map, sessionid, matchdate, serverid, duration, title ) VALUES ( '" . $videoEntry->getVideoId() . "', '" . $authobj[ 0 ]->getName() . "', '" . $matchinfo[ 1 ] . "', '" . hexdec( $matchid[ 1 ] ) . "', '" . $matchinfo[ 2 ] . "', '" . $matchinfo[ 0 ] . "', '" . $videoEntry->getVideoDuration() . "', '" . $videoEntry->getVideoTitle() . "' )" );
		echo mysql_error();
	}
	
 ?>
