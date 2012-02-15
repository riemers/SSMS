<?php
	include("../config.php");
	
	// EDIBLES, NOM NOM
	define( "CRAWLER_DELAY", 60 * 60 * 24 * 2 ); // delay in seconds before a new match is first crawled 
	define( "CRAWLER_RANGE", 60 * 2 ); // range in seconds of the matches a crawl will cover
	define( "CRAWLER_REPEAT_PERIOD", 60 * 60 * 24 * 7 ); // period in seconds it will look back to recrawl matches
	define( "CRAWLER_REPEAT_COUNT", 4 ); // amount of previous periods it will recrawl matches of
	
	mysql_connect( "$host", "$user", "$pass" ) or die( mysql_error() );
	mysql_select_db( "$table" ) or die( mysql_error() );
	
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata_YouTube');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	
	$httpClient = Zend_Gdata_ClientLogin::getHttpClient( $googleUsername, $googlePassword, 'youtube', null, 'YouTube Replay Crawler', null,	null, 'https://www.google.com/accounts/ClientLogin'	);
	$yt = new Zend_Gdata_YouTube( $httpClient, "YouTube Replay Crawler", "Crawler", $developerKey );
	$yt->setMajorProtocolVersion( 2 );
	
	$period = time() - CRAWLER_DELAY;
	
	$crawl_string = '';
	for( $i = 0; $i < ( CRAWLER_REPEAT_COUNT + 1 ); $i++ )
		$crawl_string .= ( $i == 0 ? '' : ' OR ' ) . 'matchdate BETWEEN FROM_UNIXTIME(' . ( $period - CRAWLER_REPEAT_PERIOD * $i - CRAWLER_RANGE ) . ") AND FROM_UNIXTIME(" . ( $period - CRAWLER_REPEAT_PERIOD * $i ) . ")";
	
	$matches = array();
	$result = mysql_query( "SELECT * FROM matchids WHERE " . $crawl_string . " or sessionid = 680462533 LIMIT 0, 10" );
	while( $row = mysql_fetch_array( $result ) )
		if( $row[ 'sessionid' ] > 1000000 )
			$matches[ "match_" . strtolower( dechex( $row[ 'sessionid' ] ) ) ] = $row;
	
	echo 'Crawling ' . count( $matches ) . ' match(es)<br/>';
	$matchlist = implode( ' | ', array_keys( $matches ) ); 
	
	if( empty( $matchlist ) ) Die();
	
	echo $matchlist . "<br/>";
		
	$query = $yt->newVideoQuery();
	$query->setVideoQuery( $matchlist );
	$query->setMaxResults( 50 );

	$videoFeed = $yt->getVideoFeed( $query->getQueryUrl( 2 ) );
	
	foreach ( $videoFeed as $videoEntry ) {
		
		preg_match_all( "/(Scout|Soldier|Pyro|Demoman|Heavy|Engineer|Medic|Sniper|Spy)|match_([0-9a-f]{6,})/", implode( "|", $videoEntry->getVideoTags() ), $matchid );
		$authobj = $videoEntry->getAuthor();
		$matchinfo = $matches[ $matchid[ 0 ][ 1 ] ];
		
		if( mysql_num_rows( mysql_query( "SELECT youtubeid FROM videos WHERE youtubeid = '" . $videoEntry->getVideoId() . "'" ) ) != 0 ) {
			
			mysql_query( "UPDATE videos SET title = '" . mysql_real_escape_string( $videoEntry->getVideoTitle() ) . "', description = '" . mysql_real_escape_string( $videoEntry->getVideoDescription() ) . "' WHERE youtubeid = '" . $videoEntry->getVideoId() . "'" );
			echo mysql_error();
			
		} else {
		
			$nextmatch = mysql_fetch_array( mysql_query( "SELECT * FROM matchids WHERE matchdate > '" . $matchinfo[ 'matchdate' ] . "' AND serverid = " . $matchinfo[ 'serverid' ] . " LIMIT 1" ) );
			
			mysql_query( "INSERT INTO videos ( youtubeid, youtubeuser, map, sessionid, matchdate, matchduration, role, serverid, duration, title, description ) VALUES ( '" . $videoEntry->getVideoId() . "', '" . $authobj[ 0 ]->getName() . "', '" . $matchinfo[ 'mapname' ] . "', '" . $matchinfo[ 'sessionid' ] . "', '" . $matchinfo[ 'matchdate' ] . "', '" . ( strtotime( $nextmatch[ 'matchdate' ] ) - strtotime( $matchinfo[ 'matchdate' ] ) ) . "', '" . $matchid[ 0 ][ 0 ] . "', '" . $matchinfo[ 'serverid' ] . "', '" . $videoEntry->getVideoDuration() . "', '" . mysql_real_escape_string( $videoEntry->getVideoTitle() ) . "', '" . mysql_real_escape_string( $videoEntry->getVideoDescription() ) . "' )" );
			echo mysql_error();
			
			$yt->insertEntry( $videoEntry, $yt->getUserFavorites( "LethalZone" )->getSelfLink()->href );
		}
	}
	
 ?>
