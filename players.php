<?php
// Be sure that the paths are correct
include("config.php");
include("lib/functions.php");
require_once 'lib/steam-condenser.php';

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

        $settings = getsettings();

//error_reporting(E_ALL);
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

?>

<style type="text/css">
tr.d0 td {
	background-color: #D3D3D3; color: black;
}
tr.d1 td {
	background-color: #C0C0C0; color: black;
}
</style>
<?


$ipAddress = $ip;
$server = new SourceServer($ipAddress, $portNumber);
echo '<link rel="stylesheet" href="css/main.css">';
try {
    $players = $server->getPlayers($rconpass);
} catch (Exception $e) {
    echo 'Server unreachable.'; exit;
}
if (empty($players)) { echo 'No active players found on server'; exit;} 

        if ($settings['usestats']['config'] == 'yes') {
                $statsinfo[0] = $settings['statsprogram']['config'];
                $statsinfo[1] = $settings['statsurl']['config'];
        }

echo "<table>";
echo "<tr bgcolor=FF8C00><th width=14%>Name</th><th width=25%>Score</th><th width=14%>Ping</th><th width=14%>steamid</th><th width=14%>connect time</th><th width=14%>state</th><th width=14%>ip</th></tr>";
$i=0;
foreach($players as $player) {
        $steamid = $player->getsteamid();
	$statsinfo[2] = $steamid;
	$name = $player->getName();
	if ($i%2 == 0) { echo "<tr class=d0>"; $i++; }
	else { echo "<tr class=d1>"; $i++; }
if ($name ==  "") { echo "<td>New player connecting</td>"; }
else { echo "<td>{$player->getName()}" . getstatsurl($statsinfo) . "</td>"; }
    echo "<td>{$player->getScore()}</td>";
    echo "<td>{$player->getPing()}</td>";
    try {
	$profile = SteamId::convertSteamIdToCommunityId($steamid);
        }
    catch(Exception $e) {
	$profile = 'ERROR'; 
    }
//    $steamid = $player->getsteamid();
    echo "<td><a href=\"http://steamcommunity.com/profiles/$profile\" title=\"$name\" target=_blank>$steamid</a></td>";

    $seconds=$player->getconnectTime();
    $time=sec2hms($seconds);
    echo "<td>$time</td>";
    echo "<td>{$player->getstate()}</td>";
    echo "<td>{$player->getipaddress()}</td>";
    echo "</tr>";
}
echo "</table>";


?>
