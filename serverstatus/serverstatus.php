<span class="corners-top">Server overview</span>
<div class="panel">
<?php
	
        $host = "changeme";
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
	$data = array( "retries" => "Status", "type" => "Game", "servername" => "Name", "currentmap" => "Current Map", "currentplayers" => "Players");
	$imgs = array( "tf" => "tf", "cstrike" => "css", "left4dead" => "l4d", "left4dead2" => "l4d2", 'nucleardawn' => 'nucleardawn');
	
	echo "<table style=\"color:black;\">\n";
	echo "<tr>\n";


	foreach( $data as $key => $value ) {
		echo "	<td><a href=\"?" . $strgame . 'sort=' . $key . ( $sort == $key ? $desc : '' ) . "\">" . $value . "</a></td>\n";
	}
	echo '<td>Connect</td>';
	echo "</tr>\n";
	
	$result = mysql_query( "SELECT * from servers ORDER BY " . $sort . ( !empty( $desc ) ? '' : ' DESC' ) ) or die(mysql_error());
	//$result = mysql_query( "SELECT * from servers where type like '" . $_GET[ 'game' ] . "' ORDER BY " . $sort . ( !empty( $desc ) ? '' : ' DESC' ) ) or die(mysql_error());
	while($row = mysql_fetch_array( $result )) {
		$servername = $row['servername'];
		$ip = $row['ip'];
		$port = $row['port'];
		$type = $row['type'];
		$version = $row['version'];
		$network = $row['network'];
		$retries = $row['retries'];
		$currentmap = $row['currentmap'];
		$lastupdate = $row['lastupdate'];
		$maxplayers = $row['maxplayers'];
		$currentplayers = $row['currentplayers'];
		$autoupdate = $row['autoupdate'];
		$restartsend = $row['restartsend'];
		
		$playersColor = '#390';
		if ($currentplayers >= $maxplayers) {
			$playersColor = '#f00';
		} elseif ($currentplayers / $maxplayers < 0.25) {
			$playersColor = '#f60';
		}

		if ( $retries == "0" ) {  
			$color = "green";
		}
		elseif ( $retries > 3 ) {$color = "red";}
		elseif ( $restartsend == "yes" ) {$color = "updating";}
		elseif ( $restartsend == "update" ) {$color = "waiting";}
		else { $color = "orange";}
 		
		echo "<tr>\n   <td width=\"60px\" align=\"left\"><img src=\"images/" . $color  . ".png\" title=\"Number of retries: " . $retries . "\"/></td>\n";
		echo "  <td><img src=\"images/" . $imgs[ $type ] . ".png\" /></td>\n";
		echo "	<td width=\"40%\"><a style=\"color:black;\" href=\"steam://connect/$ip:$port\" title=\"$ip:$port\">$servername</td>\n";
		echo "	<td>$currentmap</td>\n";
		echo "  <td style=\"color:$playersColor;\">$currentplayers / $maxplayers</td>\n";
		echo "  <td style=\"text-align:center;\"><a href=\"steam://connect/$ip:$port\"><img height=\"16\" width=\"16\" src=\"images/steam.png\" /></a></td>\n";
		
		
	}
	echo "</table>\n";
	mysql_close();
	
?>
<br />
</div>
<span class="corners-bottom"></span>
