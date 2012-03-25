<?php
	
	include("../config.php");
	
	// connect to stats database
        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

	$sth = mysql_query( "SELECT serverid,servername,ip,port,type,currentmap,currentplayers,currentbots,maxplayers,retries,restartsend FROM servers order by servername" );
	$rows = array();
		while($r = mysql_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	print json_encode($rows);
?>

