<?php
	
        $host = "www.lethal-zone.eu";
        $user = "changeme";
        $pass = "changeme";
        $table = "changeme";

	mysql_connect($host, $user, $pass) or die(mysql_error());
	mysql_select_db($table) or die(mysql_error());


	//error_reporting(E_ALL);
	error_reporting(E_ERROR);
	ini_set("display_errors", 1); 
	
		
	$desc = !isset( $_GET[ 'desc' ] ) ? '&desc' : '';
	$sort = isset( $_GET[ 'sort' ] ) ? $_GET[ 'sort' ] : 'type';
	$data = array( "status" => "Status", "type" => "Game", "os" => "OS", "serverid" => "ID", "servername" => "Name", "currentmap" => "Current Map", "players" => "Players", "lastupdate" => "Last Update" );
	$imgs = array( "tf" => "tf", "cstrike" => "css", "left4dead" => "l4d", "left4dead2" => "l4d2" );
	
	echo "<table class=\"listtable\" align=\"left\">\n";
	echo "<tr>\n";
	foreach( $data as $key => $value ) {
		echo "	<td>" . $value . "</td>\n";
	}
	echo "</tr>\n";
	
	$result = mysql_query( "SELECT * from servers ORDER BY " . $sort . ( !empty( $desc ) ? '' : ' DESC' ) ) or die(mysql_error());
	//$result = mysql_query( "SELECT * from servers where type like '" . $_GET[ 'game' ] . "' ORDER BY " . $sort . ( !empty( $desc ) ? '' : ' DESC' ) ) or die(mysql_error());
	while($row = mysql_fetch_array( $result )) {
		$serverid = $row['serverid'];
		$servername = $row['servername'];
		$ip = $row['ip'];
		$port = $row['port'];
		$os = $row['os'];
		$type = $row['type'];
		$version = $row['version'];
		$network = $row['network'];
		$retrys = $row['retrys'];
		$currentmap = $row['currentmap'];
		$lastupdate = $row['lastupdate'];
		$maxplayers = $row['maxplayers'];
		$currentplayers = $row['currentplayers'];
		$autoupdate = $row['autoupdate'];
		$restartsend = $row['restartsend'];

		if ( $retrys == "0" ) {  
			$color = "green";
		}
		elseif ( $retrys > 3 ) {$color = "red";}
		elseif ( $restartsend == "yes" ) {$color = "updating";}
		elseif ( $restartsend == "update" ) {$color = "waiting";}
		else { $color = "orange";}

		echo "<tr>\n   <td align=\"center\"><img src=\"images/" . $color  . ".png\" title=\"Number of retrys: " . $retrys . "\"/></td>\n";
		echo "  <td><img src=\"images/" . $imgs[ $type ] . ".png\" /></td>\n";
		echo "	<td><img src=\"images/" . $os . ".png\" /></td>\n";
		echo "	<td>$serverid</td>\n";
		echo "	<td><a href=\"steam://connect/$ip:$port\" title=\"$ip:$port\">$servername</td>\n";
		echo "	<td>$currentmap</td>\n";
		echo "  <td>$currentplayers / $maxplayers</td>\n";
		echo "	<td>$lastupdate</td>\n";
	}
	echo "</table>\n";
	mysql_close();
	
?>
