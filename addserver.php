<?php

include("config.php");
include("lib/functions.php");
require_once 'lib/steam-condenser/lib/steam-condenser.php';

$start = head();

mysql_connect($host, $user, $pass) or die(mysql_error());
mysql_select_db($table) or die(mysql_error());

//error_reporting(E_ALL);
//ini_set("display_errors", 1); 

        $status = $_POST["status"];
        $ip = $_POST["ip"];
        $port = $_POST["port"];
        $rconpass = $_POST["rconpass"];
        $servername = $_POST["servername"];
        $type = $_POST["type"];
        $network = $_POST["network"];
        $version = $_POST["version"];
	    $os = $_POST["os"];

if ($status == "verify") {

	if(empty($ip) OR empty($port) OR empty($rconpass)){
        	echo '<b>Please go back and provide all the needed data.<br />';
		bottom($start);
		die();
    }
//    require_once('scripts/source_query.php');
	$server = new SourceServer($ip, $port);
	try {   $server->rconAuth('$rconpass');
	} catch(RCONNoAuthException $e) {
		trigger_error('Could not authenticate with the game server.',
		E_USER_ERROR);
                bottom($start);
                die();
	}
	$server->rconExec('status');
	$info   = $server->GetInfo();
	if (!$info['name']) {
		echo '<b> There is an error with the information you supplied, please go back and verify.</b>';
		bottom($start);
		die();
	}
	
	echo '<b>Network version:</b> '.$info['net_ver'].'<br />';
	echo '<b>Server name:</b> '.$info['name'].'<br />';
	echo '<b>Game directory:</b> '.$info['dir'].'<br />';
	echo '<b>Game description:</b> '.$info['desc'].'<br />';
	echo '<b>Server OS:</b> '.$info['os'].'<br />';
	echo '<b>Rcon Password:</b> '.$rconpass.'<br />';
	echo '<b>Private:</b>';
	if ($info['private']) {
		print "Yes<br />";
	} else {
		print "No<br />";
	}
	print "<b>Game version:</b> ".$info['game_ver']."<br />\n";
	print "<br/><b><font color=green>Is this information correct?</font></b><br/>\n\n";
	print "</pre>";

	print "<form method='post'>\n";
        print "<input type='hidden' name='ip' value='$ip'/>\n";
        print "<input type='hidden' name='port' value='$port'/>\n";
        print "<input type='hidden' name='rconpass' value='$rconpass' />\n";
        print "<input type='hidden' name='type' value='".$info['dir']."'/>\n";
        print "<input type='hidden' name='os' value='".$info['os']."'/>\n";
        print "<input type='hidden' name='network' value='".$info['net_ver']."'/>\n";
        print "<input type='hidden' name='version' value='".$info['game_ver']."'/>\n";
        print "<input type='hidden' name='servername' value='".$info['name']."'/>\n";
        print "<input type='hidden' name='status' value='import' />\n";
        echo '<input type=\'submit\' value=Yes /></form>';

}

elseif ($status == "import") { 
	// call import script...
	print "<b> Importing..</b><br />";
        $import = mysql_query("INSERT INTO servers (servername, ip, port, rconpass, type, os, version, network)
        VALUES('$servername', '$ip','$port','$rconpass','$type','$os','$version','$network')");
	$lastid = mysql_insert_id();
		if ($import) {
			print "Added server '$servername' correctly to the database\n";
			print "\n\nDo you want to search plugins for it? Click <a href=walkserver.php?serverid=$lastid>HERE</a>\n";
		}
		else {
			print "Adding failed, perhaps it's already there?";
		}
	}
else {
	echo '<table><tr>';
	echo '<form method=post>';
        echo '<td>Ip:</td><td><input type="text" name="ip" value="'.$ip.'"/></td>';
        echo '</tr><tr><td>Port:</td><td><input type="text" name="port" size="6" value="'.$port.'"/></td>';
        echo '</tr><tr><td>Rcon Password:</td><td><input type="text" name="rconpass" value="'.$rconpass.'" /></td>';
        echo '<td><input type="hidden" name="status" value="verify" />';
	echo '<input type=\'submit\' /></form></td>';
	echo '</tr></table>';

}

mysql_close();
bottom($start);

?>
