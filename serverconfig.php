<?
	include("config.php");
	include("lib/functions.php");

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());



        if ($_POST['update'] == "yes") {
		$serverid = $_POST['serverid'];
		unset($_POST['update']);
		unset($_POST['submit']);
		foreach ($_POST as $key => $value) {
		//$$key = $value;
				if ($value == "NULL") { 
					mysql_query("UPDATE servers SET `$key` = NULL where serverid = '$serverid'") or die(mysql_error());
				} else {
					mysql_query("UPDATE servers SET `$key` = '$value' where serverid = '$serverid'") or die(mysql_error());
				}
		}
		header("Location: servers.php");
		die();
        }

	$settings = getsettings();
	$servercfg = getserver($_GET['serverid']);
	$servercfg = $servercfg['0'];

	//print_r($servercfg);
	//if (!empty($_POST)) header("Location: servers.php");

?>
<body><div id="container">
<form method="post" action="serverconfig.php" class="niceform">
        <fieldset>
        <legend>Changing individual server configuration for</legend>
	<b><? echo $servercfg['servername'];?></b><br/>
	<? echo "IP: " . $servercfg['ip'] . ":" .$servercfg['port'] . "<br/>Version " . $servercfg['version'] . "/" . $servercfg['network'] . "<br/>Type: " . $servercfg['type'];
	if ($servercfg['os'] == 'l') { echo '<br/> Running on Linux'; } 
	elseif ($servercfg['os'] == 'w') { echo '<br/> Running on Windows'; }
        echo '</legend>';
        echo '</fieldset>';

	if ($servercfg['type'] == 'left4dead' || $servercfg['type'] == 'left4dead2') {
?>
        <fieldset>
        <legend>l4d(2) options (LEAVE BLANK IF YOU DONT HAVE FORKS)</legend>
        <dl>
                <dt><label for="netconport">Netcon Port</label></dt>
            <dd><input type="text" name="netconport" id="netconport" size="40" maxlength="255" value="<? echo $servercfg['netconport'];?>" /></dd>
        </dl>
        <dl>
                <dt><label for="netconpasswd">Netcon Password</label></dt>
            <dd><input type="text" name="netconpasswd" id="netconpasswd" size="40" maxlength="255" value="<? echo $servercfg['netconpasswd'];?>" /></dd>
        </dl>
	</fieldset>
	</legend>
	<? } ?>

        <fieldset>
        <legend>Options</legend>
        <dl>
                <dt><label for="rconpass">Rcon Password</label></dt>
            <dd><input type="text" name="rconpass" id="rconpass" size="40" maxlength="255" value="<? echo $servercfg['rconpass'];?>" /></dd>
        </dl>

        <dl>
                <dt><label for="showmotd">Show server in MOTD?</label></dt>
            <dd>
                    <select size="1" name="showmotd" id="showmotd">
                    <? if ($servercfg['showmotd'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select>
            </dd>
        </dl>

        <dl>
                <dt><label for="autoupdate">Auto update this server?</label></dt>
            <dd>
                    <select size="1" name="autoupdate" id="autoupdate">
                    <? if ($servercfg['autoupdate'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select>
            </dd>
        </dl>
        <dl>
                <dt><label for="updatemessage">Update message to send before restart</label></dt>
            <dd><input type="text" name="updatemessage" id="updatemessage" size="40" maxlength="255" value="<? echo $servercfg['updatemessage'];?>" /></dd>
        </dl>

    </fieldset>
	<fieldset>
		<legend>Daily restarts</legend>
        <dl>
                <dt><label for="dlyrestart">Daily Restart?</label></dt>
            <dd>
                    <select size="1" name="dlyrestart" id="dlyrestart">
                    <? if ($servercfg['dlyrestart'] == 'yes') {
                        echo '<option value="yes" selected="selected">Yes</option>';
                        echo '<option value="no">No</option>';}
                    else {
                        echo '<option value="no" selected="selected">No</option>';
                        echo '<option value="yes">Yes</option>';
                    }?>
                </select>
            </dd>
        </dl>
		
		<dl>
                <dt><label for="dlytime">Daily time to Restart</label></dt>
				<? $hhmm = date('H:i', strtotime($servercfg['dlytime'])); ?>

            <dd><input type="text" name="dlytime" id="dlytime" size="5" maxlength="5" value="<? echo $hhmm;?>" /></dd>
        </dl>
		        <dl>
                <dt><label for="dlycmd">Restart Command</label></dt>
            <dd><input type="text" name="dlycmd" id="dlycmd" size="40" maxlength="255" value="<? echo $servercfg['dlycmd'];?>" /></dd>
        </dl>
		        <dl>
                <dt><label for="dlyusers">Max. Player Count</label></dt>
            <dd><?
				echo '<select size="4" name="dlyusers" id="dlyusers">';
				$players=1;
				if ($servercfg['dlyusers'] == NULL) { 
					echo '<option value=NULL id=dlyusers selected=selected>Dont Check</option>';
				} else {
					echo '<option value=NULL id=dlyusers>Dont Check</option>';
				}
				while($players<=$servercfg['maxplayers']) {
					if ($servercfg['dlyusers'] == $players) { 
						echo "<option value=\"" . $players . "\" id=\"dlyusers\" selected>" . $players . " " . ( $players == "1" ? "Player" : "Players") . "</option>";
					}	else  {
						echo "<option value=\"" . $players . "\" id=\"dlyusers\">" . $players . " " . ( $players == "1" ? "Player" : "Players") . "</option>";
					}
				$players++;
				}
				echo '<br/>';
				?></dd>
        </dl>
	</fieldset>
    <fieldset class="action">
	<input type="hidden" name="update" value="yes">
	<input type="hidden" name="serverid" value="<? echo $_GET['serverid'];?>">
        <div style="clear: both;"></div>
        <div style="width:200px;"><input type="submit" name="submit" id="submit" value="Submit" />
	<input type="button" value="Cancel" onclick="$( '#serverall' ).dialog('close');"></div>
    </fieldset>
</form>
</div></body>
</html>

<?	

        mysql_close();

?>
