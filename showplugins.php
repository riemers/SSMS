<?php

include("config.php");
include("lib/functions.php");

$start = head();

mysql_connect($host, $user, $pass) or die(mysql_error());
mysql_select_db($table) or die(mysql_error());

//error_reporting(E_ALL);
//ini_set("display_errors", 1); 

function showmods($serverid) {
        $result = mysql_query("SELECT * from mods where modid in (select modid from srv_mods where serverid = '$serverid')") or die(mysql_error());
        while($row = mysql_fetch_array( $result )) {
			$filename = $row['filename'];
			$title = $row['title'];
			$author = $row['author'];
			$version = $row['version'];
			$url = $row['url'];
			$status = $row['status'];
			$reloads = $row['reloads'];
			$timestamp = $row['timestamp'];
			print "------- Sourcemod Plugin -----\n";
			print "$filename\n";
			print "$title\n";
			print "$author\n";
			print "$version\n";
			print "$url\n";
			print "$status\n";
			print "$reloads\n";
			print "$timestamp\n<br/>";
        }
}

function showmetas($serverid) {

        $result = mysql_query("SELECT * from metamods where metaid in (select metaid from srv_mods where serverid = '$serverid')") or die(mysql_error());
        while($row = mysql_fetch_array( $result )) {
			$name = $row['name'];
			$version = $row['version'];
			$description = $row['description'];
			$url = $row['url'];
			$details = $row['details'];
			$file = $row['file'];
			print "------- Metamod Plugin -------\n";
			print "$name\n";
			print "$version\n";
			print "$description\n";
			print "$url\n";
			print "$details\n";
			print "$file\n<br/>";
        }

}

$serverid = $_GET['serverid'];
$mod = $_GET['mod'];


?>

<form>

	<select name="serverid">

<?php

	$result = mysql_query( "SELECT * from servers ORDER BY servername" ) or die( mysql_error() ); // retrieve all servers
	while( $row = mysql_fetch_array( $result ) ) // for each result
		echo "<option value=\"" . $row['serverid'] . "\"" . ( $row['serverid'] == $serverid ? "selected" : "") . ">" . $row['servername'] . "</option>";

?>
	</select>
	
	<select name="mod">
		<option value="source" <?php if( $mod == "source" ) echo 'selected'; ?>>Sourcemod</option>
		<option value="meta" <?php if( $mod == "meta" ) echo 'selected'; ?>>Metamod</option>
		<option value="both" <?php if( $mod == "both" ) echo 'selected'; ?>>Both</option
	</select>
	
	<input type="submit" value="Retrieve Plugins"/>
</form>
<pre>
<?php

if ($mod == "source")
	showmods($serverid);
elseif ($mod == "meta")
	showmetas($serverid);
elseif ($mod == "both") {
	showmods($serverid);
	print "<b>========================================================================</b>\n";
	print "<b>========================================================================</b>\n";
	showmetas($serverid);
}
print "</pre>";
mysql_close();
bottom($start);

?>
