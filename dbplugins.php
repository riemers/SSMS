<?php
        include("config.php");
        include("lib/functions.php");
        require_once 'lib/steam-condenser/lib/steam-condenser.php';

		mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());
		
        if(isset($_SERVER['argc'])) $args = getopt("u");
		//$args = $args[ 'u' ];
        if (!$args) { $start = head(); }

	function dbpage() {

        error_reporting(E_ALL);
        //error_reporting(E_ERROR);
        ini_set("display_errors", 1);

        $result = mysql_query( "SELECT * from plugindb,mods where mods.t != 'NULL' and mods.threadid=plugindb.threadid and mods.author != 'AlliedModders LLC'" ) or die(mysql_error());
        while( $row = mysql_fetch_array( $result ) ) {
                foreach ($row as $key => $value) {
                        $$key = $value;
                }
			echo "$pluginid - \n";
			echo "$author\n";
			echo "$version\n";
			echo "$description\n<br/>";
		}
	}

	function openplugins() {
                        $list = mysql_query( "SELECT modid,filename,title from mods where threadid is not NULL order by filename" ) or die(mysql_error());
                        while ( $list2 = mysql_fetch_array( $list ) ) {
                                // $selects .= "<option value=\"" . $list2['modid'] . " - " . $list2['filename'] . " - " . $list2['title'] . "\" </option>";
							$selects .= "<option value=\"" . $list2['modid'] . "\">" . $list2['filename'] . "</option>\n";
                        }

	//$list = mysql_query( "SELECT * from mods where threadid != NULL" ) or die(mysql_error());
        $result = mysql_query( "SELECT * from mods where threadid is NULL and author != 'AlliedModders LLC'" ) or die(mysql_error());
	if (mysql_num_rows($result)) {
		echo '<fieldset><legend>Plugins without TreadID, fill in \'0\' to stop checking, or before you continue try the <a href="dbplugins.php?action=matches">Automatic matcher</a> in case you filled in threadid\'s before.</legend>';
        while( $row2 = mysql_fetch_array( $result ) ) {
                foreach ($row2 as $key => $value) {
                        $$key = $value;
                }
		echo "<fieldset><legend>$filename</legend>";
			echo "<form method=get action=''>";
                        echo "Title  : $title<br/>\n";
                        echo "Author : $author<br/>\n";
                        echo "Version : $version<br/>\n";
                        echo "URL    : <a href=\"$url\">$url</a></br>\n";
			echo "<input name=\"action\" type=\"hidden\" value=\"modthreadid\"><br/>";
			echo "<input name=\"modid\" type=\"hidden\" value=\"$modid\"><br/>";
			echo "<input name=\"threadid\" size=\"6\"> <input type=\"submit\" value=\"<- Threadid |OR| Select to which plugin it belongs ->\">";
			echo "<select name=\"pluginmatch\">\n<option value=\"Select\">Select</option>\n";
			echo $selects;
			echo "</select>";
			echo "</form>";
			echo "</fieldset>";
	        }
		echo '</fieldset>';
	    } else {
				echo '<br/><b>All plugins are accounted for. All have either a threadid or the check disabled for it.</b><br/>'; 
			}
		
    }

	function dbcompare() {
		$settings = getsettings();
		$result = mysql_query("select plugindb.description as dbdescr, plugindb.version as dbvers, mods.version as modvers, mods.filename, plugindb.threadid, plugindb.send from plugindb, mods where mods.threadid = plugindb.threadid and mods.version != plugindb.version and plugindb.threadid != '0'") or die (mysql_error());
		if (mysql_num_rows($result)) {
		echo "<table class=\"listtable\" align=\"left\"><tr class=\"headers\"><td></td><td>Plugin</td><td>Version</td><td>Current Version</td></tr>";
		
			while( $row2 = mysql_fetch_array( $result ) ) {
				foreach ($row2 as $key => $value) {
					$$key = $value;
				}
				if (version_compare($modvers,$dbvers,'<')) {
					echo "
						<tr class=\"elements\"><td><img src=images/new.png></td><td><a href=\"http://forums.alliedmods.net/showthread.php?t=$threadid\">$filename</a></td><td>$dbvers</td><td>$modvers</td></tr>";
					if ( $send == 'no' ) {
						$subject = "Plugin $filename has a newer version on alliedmodders";
						$message = "A update for $filename ($dbdescr) seems to be out, $dbvers is the latest. We have $modvers running....\nhttp://forums.alliedmods.net/showthread.php?t=$threadid";
						mail($settings['emailalert']['config'], $subject, $message, null);
						mysql_query("UPDATE plugindb SET send = 'yes' where threadid = '$threadid'") or die (mysql_error());
					}
				} else { mysql_query("UPDATE plugindb SET send = 'no' where threadid = '$threadid'") or die (mysql_error()); }
			
			
			}
			
				echo "</table><br/><br/><br /><br /><br/>";
		} else { echo '<br/><b>-- No plugins are outdated, your a champ!</b><br/>'; }
	}

	function doplugin($threadid,$args) {

        $html = file_get_contents("http://forums.alliedmods.net/showthread.php?t=$threadid&postcount=1");

        $url = array();
        preg_match("/Plugin ID.*?<div.*?>(.*?)<\/div>.*?Plugin Version.*?<div.*?>(.*?)<\/div>.*?Plugin Description:.*?<div.*?>(.*?)<\/div>.*?(\d{2}-\d{2}-\d{4}).*?/si", $html, $url);

        array_shift($url);
        print_r($url);

		//if ($json == "NOTFOUND") { echo "MISS -> Threadid not found, make sure you use THREADID, not POSTID\n"; return; }
		//$data = json_decode($json);
		//$changefiles = str_replace(".sp",".smx", $data->files);
		//$uniqfiles = array_unique($changefiles);
		//foreach($uniqfiles as $allfiles) {
	//		if (preg_match( '/\.smx/i', $allfiles)) {
	//			$alles[] = $allfiles;
	//		}
	//	}
		echo "Plugin information:\n";
		
	//	if (!empty($alles)) { 
	//		$comma = implode(",", $alles); 
	//	} else	{
	//		echo ' ^ No files found : ';
	//	}
	//	$lastupdate =  date("Y-m-d H:i:s",intval($data->last_updated));
		$description = mysql_escape_string($url[2]);
		if ($url) { 
			echo "HIT -> $url[2]\n";
		}
		$result = mysql_query( "INSERT INTO plugindb (pluginid,threadid,description,version) VALUES('$url[0]','$threadid','$description','$url[1]') ON DUPLICATE KEY UPDATE description='$description',version='$url[1]'") or die(mysql_error());
	}

	function threadmatch() {
		$settings = getsettings();
                $result = mysql_query("select threadid,filename from plugindb") or die (mysql_error());
		while($dbres = mysql_fetch_assoc($result)){
			$filepieces = explode(",", $dbres['filename']);
			$threadarray[] = array(threadid=> $dbres['threadid'],files => $filepieces);
		}
		$result = mysql_query("select modid,filename from mods where threadid = ''") or die (mysql_error());
		while( $row = mysql_fetch_array( $result ) ) {
			foreach($threadarray as $thread) {
				//print_r($thread);
				foreach($thread['files'] as $files) {
					if ($row['filename'] == "$files") {
						echo "matched " . $row['filename'] . " with threaid " . $thread['threadid'] . "<br/>\n";
						mysql_query( "UPDATE mods set threadid = " . $thread['threadid'] . " where modid = " . $row['modid'] ) or die(mysql_error());
					}
				}
			}
		}
		echo "<pre>";
		//print_r($threadarray);
		echo "</pre>";
	}


	if( isset( $args[ 'u' ] ) ) {
		$result = mysql_query( "select distinct threadid from mods where threadid != 'NULL' and threadid != '0'" ) or die(mysql_error());
		while( $row = mysql_fetch_array( $result ) ) {
			echo "Updating " . $row['threadid'] . "... ";
			doplugin($row['threadid'],$args);
		}
		dbcompare();
	}
	
	if (!$args) { 
		if ($_GET['action']) {
			if ($_GET['action'] == "modthreadid") {
				if ($_GET['pluginmatch'] == "Select") {
				$result = mysql_query( "UPDATE mods set threadid = '" . $_GET['threadid'] . "' WHERE modid = " .  $_GET['modid'] ) or die(mysql_error());
				echo "<b> ModID " . $_GET['modid'] . " Updated Correctly with threadid : " . $_GET['threadid'] . "</b><br/>";
				} else {
				// this is the matched plugin select
					$getinfo = mysql_query( "SELECT threadid, filename,modid from mods where modid = '" . $_GET['pluginmatch'] . "' or modid = '" . $_GET['modid'] ."'");
				while( $check = mysql_fetch_array( $getinfo ) ) {
					if ($check['modid'] == $_GET['modid']) {
					$newfilenames = $check['filename'];
					$newthreadid = $check['threadid'];
					} else {
					$oldfilenames = $check['filename'];
					$oldthreadid = $check['threadid'];
					}
				}
					$result = mysql_query( "UPDATE mods set threadid = '" . $newthreadid . "' where modid = '" .  $_GET['modid'] . "'" ) or die(mysql_error());
					// also update the filenames..
					$totalfile = $oldfilenames . "," . $newfilenames;
					$result = mysql_query( "UPDATE mods set filename = '" . $totalfile . "' where modid = '" .  $_GET['pluginmatch'] . "'" ) or die(mysql_error());	

				}
			}
			if ($_GET['action'] == "matches") {
				threadmatch();
			}
		}
	
		//dbpage();
		dbcompare();
		openplugins();
		bottom( $start );
	}
	
	mysql_close();
		
?>
