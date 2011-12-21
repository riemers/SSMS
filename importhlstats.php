<?php

include("config.php");
include("lib/functions.php");

$start = head();
$status = $_POST["status"];


//error_reporting(E_ALL);
//ini_set("display_errors", 1); 


function data() {
        $get = mysql_query("SELECT name,address,port,rcon_password from hlstats_Servers");
        return $get;
}


if ($status == "verify") {

        $ipp = $_POST["ip"];
	if (!$ipp) { $ip = 'localhost'; }
        $dbp = $_POST["db"];
        $passp= $_POST["pass"];
        $userp = $_POST["user"];

        mysql_connect($ipp, $userp, $passp) or die(mysql_error());
        mysql_select_db($dbp);


	if(empty($userp) OR empty($dbp) OR empty($passp)){
        	echo '<b>Please go back and provide all needed data.<br />';
		bottom($start);
		die();
        }

	$get = data();

        while($row = mysql_fetch_array( $get )) {
                $name = $row['name'];
                $ip = $row['address'];
                $port = $row['port'];
                $rconpass = $row['rcon_password'];

        echo '<b>Server name:</b> '.$name.'<br />';
        echo '<b>Ip:port:</b> '.$ip.':'.$port.'<br />';
        echo '<b>Rcon Password:</b> '.$rconpass.'<br />';

        }

	
	print "<br/><b><font color=green>This will import all these entrys, you sure?</font></b><br/>\n\n";
	print "</pre>";

	print "<form method='post'>\n";
	print "<input type='hidden' name='ip' value='$ipp' />\n";
	print "<input type='hidden' name='db' value='$dbp' />\n";
	print "<input type='hidden' name='user' value='$userp' />\n";
	print "<input type='hidden' name='pass' value='$passp' />\n";
        print "<input type='hidden' name='status' value='import' />\n";
        echo '<input type=\'submit\' value=Yes /></form>';

}

elseif ($status == "import") { 

        $ipp = $_POST["ip"];
        $dbp = $_POST["db"];
        $passp= $_POST["pass"];
        $userp = $_POST["user"];

        mysql_connect($ipp, $userp, $passp) or die(mysql_error());
        mysql_select_db($dbp);

	
	$get = data();

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

        while($row = mysql_fetch_array( $get )) {
                $servername = $row['name'];
                $ip = $row['address'];
                $port = $row['port'];
                $rconpass = $row['rcon_password'];

        // call import script...
        $import = mysql_query("INSERT INTO servers (servername, ip, port, rconpass)
        VALUES('$servername', '$ip','$port','$rconpass')");
                if ($import) {
			print "<b>Imported....</b><br>\n";
                        print "Added server '$servername' correctly to the database<br>\n";
                }
                else {
                        print "<font color=red>$servername, Adding failed, perhaps its already there?</font><br>";
                }
        }

	print "<br><br><hr>\n\nMight be wise to update the information of all the servers again? Click <a href=renew.php?serverid=all>HERE</a>\n";

        }

else {
	echo '<table><tr>';
	echo '<form method=post>';
        echo '<td colspan=1>Ip / Host:</td><td><input type="text" name="ip"/></td>';
        echo '<td>Db:</td><td><input type="text" name="db" size="6"/></td>';
        echo '<td>User:</td><td><input type="text" name="user" size="6"/>';
        echo 'Password:</td><td><input type="text" name="pass"/></td>';
        echo '<td><input type="hidden" name="status" value="verify" /></td>';
	echo '<td></tr><tr><td><input type=\'submit\' /></td><td>(leave host empty for localhost)</form></td>';
	echo '</tr></table>';

}

bottom($start);
?>
