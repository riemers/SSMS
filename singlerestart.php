<?
	include("config.php");
	include("lib/functions.php");
	require_once 'lib/steam-condenser/lib/steam-condenser.php';

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

        $settings = getsettings();
        $servercfg = getserver($_GET['serverid']);
        $servercfg = $servercfg['0'];

		if ($_GET['forks'] == "yes") {
				$timeout = '2';
				$ip = $servercfg['ip'];
				$port = $servercfg['port'];
				$netconport = $servercfg['netconport'];
				$netconpasswd = $servercfg['netconpasswd'];
				$usenet = fsockopen($ip, $netconport, $errno, $errstr, $timeout);
			if ($_GET['submit'] == "Shutdown ALL") {
				// shutdown forks baby!
				if(!$usenet) { 
					die();
				} else {
					// echo 'shit happens';
					// echo "ip : $ip, port : $port, netconport : $netconport, netconpasswd: $netconpasswd";
					fputs ($usenet, "PASS $netconpasswd\r\n");
					fputs ($usenet, "shutdown\r\n");
					mysql_query("UPDATE servers SET restartsend = 'optional', goingdown = 'yes' WHERE netconport = '" . $servercfg['netconport'] . "'") or die(mysql_error());
				}
			}				
			if ($_GET['submit'] == "Quit ALL") {
				// quit all forks baby! dont need optional here since the restart instantly
				if(!$usenet) { 
					die();
				} else {
					fputs ($usenet, "PASS $netconpasswd\r\n");
					fputs ($usenet, "quit\r\n");
					mysql_query("UPDATE servers SET restartsend='yes' WHERE netconport = '" . $servercfg['netconport'] . "'") or die(mysql_error());
				}
			}
		header("Location: servers.php");
		die();
		} 
		
		if ($_GET['submit'] == "Restart when empty") {
			$serverid = $_GET['serverid'];
			mysql_query("UPDATE servers SET restartsend = 'optional', goingdown = 'yes', restartsend = 'emptyserver' WHERE serverid = '$serverid'") or die(mysql_error());
			header("Location: servers.php");
			die();
        	}
                if ($_GET['submit'] == "Restart HARD") {
                        $serverid = $_GET['serverid'];
                        $ipnr = $_GET['ip'];
                        $port = $_GET['port'];
                        mysql_query("UPDATE servers SET restartsend = 'yes' WHERE serverid = '$serverid'") or die(mysql_error());
			$call = file_get_contents("http://$ipnr:4337/ssms?password=$server_connect&port=$port&ip=$ipnr");
                        header("Location: servers.php");
                        die();
                }

		
        if ($_GET['update'] == "yes") {
		$serverid = $_GET['serverid'];
                $serverIP = $servercfg['ip'];
                $port = $servercfg['port'];
				$rconpass = $servercfg['rconpass'];
                $server = new SourceServer($serverIP, $port);
		try {
		$server->rconAuth($rconpass);
		$server->rconExec('_restart');
		} catch (Exception $e) {
			//trigger_error('Could not authenticate with the game server. Or it is down',E_USER_ERROR);
		} 
		// have to do the update here, since this statement above is really buggy if you _restart it at the same time
		mysql_query("UPDATE servers SET restartsend = 'restart', goingdown = 'yes' WHERE serverid = '$serverid'") or die(mysql_error());
		header("Location: servers.php");
		die();
        }

?>
<body><div id="container">
<form action="singlerestart.php" class="niceform">
        <fieldset>
        <legend>Are you sure you want to Restart?</legend>
	<b><? echo $servercfg['servername'];?></b><br/>
	<? echo "IP: " . $servercfg['ip'] . ":" .$servercfg['port'] . "<br/><font size=+1 color=red> Currentplayers " . $servercfg['currentplayers'] . "/" . $servercfg['maxplayers'] . " Bots: " . $servercfg['currentbots'] . "</font><br/><br/>";?>
	<input type="hidden" name="update" value="yes">
	<input type="hidden" name="ip" value="<? echo $servercfg['ip'] ?>">
	<input type="hidden" name="port" value="<? echo $servercfg['port'] ?>">
	<input type="hidden" name="serverid" value="<? echo $_GET['serverid'];?>">
    <input type="submit" name="submit" id="submit" value="Restart NOW" />
	<input type="submit" name="submit" id="submit" value="Restart when empty" />
    <input type="submit" name="submit" id="submit" value="Restart HARD" />
	<input type="button" value="Cancel" align="right" onclick="$( '#serverall' ).dialog('close');">
    </fieldset>
<?
	if ($servercfg['type'] == 'left4dead' || $servercfg['type'] == 'left4dead2') {
		if (!empty($servercfg['netconpasswd'])) { 
			echo '<fieldset><legend>Fork Restart options</legend>';?>
			<input type="hidden" name="forks" value="yes">
			<input type="hidden" name="serverid" value="<? echo $_GET['serverid'];?>">
			<input type="submit" name="submit" id="submit" value="Shutdown ALL" />
			<input type="submit" name="submit" id="submit" value="Quit ALL" /><?
			echo "<br/><b>Forks involved for port " . $servercfg['netconport'] . "</b><br/>";
			$result = mysql_query("SELECT servername from servers where netconport = '" . $servercfg['netconport'] . "'") or die(mysql_error());	
			// echo "This will restart the following servers:<br/>";
			while($row = mysql_fetch_array( $result )) {
				$servername = $row['servername'];
				echo "$servername<br/>";
			}
			echo '</fieldset>';
			
		}
	}
	
	
?>	
	
</form>
</div></body>
</html>

<?	

        mysql_close();

?>
