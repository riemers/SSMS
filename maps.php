<?php
// Be sure that the paths are correct
include("config.php");
include("lib/functions.php");
require_once 'lib/steam-condenser.php';

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());
        $settings = getsettings();

error_reporting(E_ERROR);
ini_set("display_errors", 1);
	$serverid = $_GET['serverid'];

        $result = mysql_query( "SELECT * from servers where serverid=$serverid") or die(mysql_error());
        while($row = mysql_fetch_array( $result )) {
                $portNumber = $row['port'];
                $ip = $row['ip'];
                $rconpass = $row['rconpass'];
                $servername = $row['servername'];
	}

$ipAddress = $ip;
$server = new SourceServer($ipAddress, $portNumber);
$server->rconAuth($rconpass);

if ($_GET['maps'] == "changelevel") {
	try {
		$server->rconExec("changelevel " . $_GET['map']);
	}
	catch(Exception $e) {
	}
	$result = mysql_query( "UPDATE servers SET currentmap = '" . $_GET['map'] . "' WHERE serverid = '$serverid'") or die(mysql_error());
	header("Location: servers.php");
	die;
}
//$nextmapbegin = $server->rconExec('sm_nextmap');
$timeleft = $server->rconExec('timeleft');
$mapsraw = $server->rconExec('maps *');
$testje = $server->rconExec('sm_nextmap');print_r($testje);

preg_match('/= "([a-z0-9_]+)"/', $nextmapbegin, $nextmap);

echo "<table>
		<tr><td>Next Map:</td><td>";
if ($nextmap[1] != "") {
	echo "$nextmap[1]";
} else {
	echo "Pending Vote";
}
echo "</td></tr>";

echo "<tr><td>Time Left:</td><td>$timeleft</td></tr>";

$mappieces = preg_split('/PENDING:\s+\(fs\)[\s]/', $mapsraw);
$mapend = array_shift($mappieces);
$mapend = str_replace(".bsp","", $mappieces);
echo "</table>";

echo '<form method="get" action="maps.php">';
echo '<select size="1" name="map">';

foreach($mapend as $maps) {
	echo "<option value=\"$maps\">$maps</option>";
}
echo '</select>';
echo '<input type="hidden" name="maps" value="changelevel">';
echo "<input type=\"hidden\" name=\"serverid\" value=\"$serverid\">";
echo '<input type="submit" name="submit" value="Change Current Map" />';
echo '</form>';

?>
