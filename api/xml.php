<?php
	
	include("../config.php");
	
	// connect to stats database
        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

	$result = mysql_query( "SELECT serverid,servername,ip,port,type,currentmap,currentplayers,currentbots,maxplayers,retries,restartsend FROM servers order by servername" );
	$num_rows = mysql_num_rows($result);

	header("Content-Type: application/xml; charset=ISO-8859-1");
	echo '<?xml version="1.0" standalone="yes"?>';
	echo '<rss version="2.0">';
	echo '<channel>';
	echo '<title>Serverlisting</title>';
	while($row = mysql_fetch_array( $result )) {
		foreach ($row as $key => $value) {
			$$key = $value;
		} 

	echo "\n<item>";
	echo "\n<serversid>$serverid</serversid>";
	echo "\n<title>$servername</title>";
	echo "\n<link>steam://connect/$ip$port</link>";
	echo "\n<description>Type: $type, Active players: $currentplayers</description>";
	echo "\n</item>";
	}
	echo "\n</channel>";
	echo "\n</rss>";
?>

