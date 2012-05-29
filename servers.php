<?php
	
	include("config.php");
	include("lib/functions.php");
	require_once 'lib/steam-condenser/lib/steam-condenser.php';
	$ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        define("CLI", !isset($_SERVER['HTTP_USER_AGENT']));
        function mysql_query_trace($query) {
            /*$trace = debug_backtrace();
            echo __FILE__ .' '. $trace[count($trace) - 1]['line'] .': MySQL-query: '. $query;
            if (defined("CLI"))
                echo "\r\n";
            else
                echo "<br />";*/
            
            return mysql_query($query);
        }
	if(isset($_SERVER['argc'])) $args = getopt("u");
	if (!isset($args) && !$ajax) { $start = head(); }
	
	mysql_connect($host, $user, $pass) or die(mysql_error());
	mysql_select_db($table) or die(mysql_error());

	$config = getsettings();
	$gametypes = gametypes();

	//error_reporting(E_ALL);
	error_reporting(E_ERROR);
	ini_set("display_errors", 1); 
	
	function addserver( $ip, $port, $rcon ) {
		$port = intval( $port );
		if( $port < 65535 && $port > 0 ) {
			if(! preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip ) )
				$error = 'Supplied IP adress is invalid.';
		} else
			$error = 'Supplied port number is invalid.';
		
		if( isset( $error ) ) {
			echo "<script type=\"text/javascript\">
			\nwindow.onload = function() {
			\nalert('" . $error . "');\n}\n
			</script>\n";
			
			return false;
		}
		
		if( mysql_query_trace( "INSERT INTO servers ( ip, port, rconpass ) VALUES( '$ip','$port','$rcon' )" ) )
			renewserver( mysql_insert_id() );
		else
			echo "<script type=\"text/javascript\">
			\nwindow.onload = function() { 
			\nalert('Error adding server to the database.\\n" . mysql_real_escape_string( mysql_error() ) . "');\n}\n
			</script>\n";
	}
	
	function deleteserver( $serverid ) {
		mysql_query_trace( "DELETE FROM `servers` WHERE `servers`.`serverid` = " . $serverid . " LIMIT 1" );
		mysql_query_trace( "DELETE FROM `srv_mods` WHERE `srv_mods`.`serverid` = " . $serverid );
	}
	
	function checkversion() {
	$settings = getsettings();

        if ($settings['usegrowl']['config'] == 'yes') {
            require_once 'lib/growl/class.growl.php';
            $growlip = $settings['growlip']['config'];
            $growlpass = $settings['growlpass']['config'];
        }

        if ($settings['usetwitter']['config'] == 'yes') {
            require_once 'lib/twitter/twitter.php';
            $consumerkey = $settings['consumerkey']['config'];
            $consumersecret = $settings['consumersecret']['config'];
            $OAuthToken = $settings['OAuthToken']['config'];
            $OAuthTokenSecret = $settings['OAuthTokenSecret']['config'];
            $twitter = new Twitter("$consumerkey", "$consumersecret");
            $twitter->setOAuthToken("$OAuthToken");
            $twitter->setOAuthTokenSecret("$OAuthTokenSecret");
	}

	$gametypes = gametypes();
	foreach (array_keys($gametypes) as $game) {

	$version = $gametypes[$game][version];
	if (!$version) { $version = "1.0"; }
	$longname = $gametypes[$game][longname];
	$expired = $gametypes[$game][expired];
	$appid = $gametypes[$game][appid];
	$shortname = $gametypes[$game][shortname];

	print "$game for $version\n"; // simple feedback part if needed

	try {
		$game = SteamGame::checkUpToDate($appid, $version);
	} catch(Exception $e) {
		echo "Seems $appid with version $version doesn't like to be probed\n";
		echo "Setting gametype as 'up to date' , dont want to start stuff without proper knowledge\n";
		echo "Seems like this is the error $e\n";
		$game = "1";
	}

	if ($expired != "yes" ) { 
	
	                if ($game != "1") {
	                        mysql_query_trace( "UPDATE games SET expired='yes' WHERE shortname = '$shortname'");
							if ($settings['useemail']['config'] == 'yes') {
								$subject = "A update for $longname seems to be out, go check out the buzz...";
								$newstuff = getupdates($appid,'last');
								$message = "A update for $longname seems to be out, go check out the buzz...\n" . $newstuff;
								mail($settings['emailalert']['config'], $subject, $message, null);
							}
							if ($settings['usegrowl']['config'] == 'yes') {
								$growl = new Growl();
                                				//$growl->setAddress($growlip, $growlpass);
								$connection = array('address' => '$growlip', 'password' => '$growlpass');
								$growl->notify($connection, "$type", "UPDATE: $shortname", "A update for $longname seems to be out, go check out the buzz...");		
							}
							if ($settings['usetwitter']['config'] == 'yes') {
							$twitter->statusesUpdate("A update for $longname seems to be out, go check out the buzz...");
							}
			}
	 	 }
	}
}

	function renewserver( $server, $cmd = false ) {

	$settings = getsettings();

        if ($settings['usegrowl']['config'] == 'yes') {
            require_once 'lib/growl/class.growl.php';
            $growlip = $settings['growlip']['config'];
            $growlpass = $settings['growlpass']['config'];
        }

        if ($settings['usetwitter']['config'] == 'yes') {
            require_once 'lib/twitter/twitter.php';
            $consumerkey = $settings['consumerkey']['config'];
            $consumersecret = $settings['consumersecret']['config'];
            $OAuthToken = $settings['OAuthToken']['config'];
            $OAuthTokenSecret = $settings['OAuthTokenSecret']['config'];
            $twitter = new Twitter("$consumerkey", "$consumersecret");
            $twitter->setOAuthToken("$OAuthToken");
            $twitter->setOAuthTokenSecret("$OAuthTokenSecret");
	}
		
		$gametypes = gametypes();
		if( $server == "all" )
			$server = '%';
		
		//if (!$cmd == 'true') { $game = $_GET[ 'game' ];}
		//else { $game = '%'; }
		$fails = array();
		
		$result = mysql_query_trace( "SELECT * from servers where serverid like '$server' and type like '" . $_GET[ 'game' ]  . "'" ) or die(mysql_error());
		while( $row = mysql_fetch_array( $result ) ) {
			
			foreach ( $row as $key => $value ){
				$$key = $value;
			}

			$info = "";
			
			$serverIP = $ip;
			$server = new SourceServer($serverIP, $port); 
			try {
				$info = $server->getServerInfo();
			}

			catch(Exception $e) {
				// $fails[] = $serverid;
				// no longer needed, since we just want it to continue
			}

			if( $info && $info['serverName'] ) {

				$network = $info['networkVersion'];
				$version = $info['gameVersion'];
				$servername = trim( $info['serverName'] );
				$type = $info['gameDir'];
				$os = $info['operatingSystem'];
				$map = $info['mapName'];
				$pwpro = $info['passwordProtected'];
				$nplayers = $info['numberOfPlayers'];
				$mplayers = $info['maxPlayers'];
				$bots = $info['botNumber'];
				$protected = $info['passwordProtected'];
				$servertags = $info['serverTags'];

				if ($replaymatch == "yes") {
					$server->rconAuth($rconpass);
					$matchid = $server->rconExec('steamworks_sessionid_server');
					$pattern = '([0-9][0-9][0-9]+)';
					preg_match($pattern, $matchid, $matches);
					if ( $matches[0] ) {
					mysql_query_trace( "INSERT INTO matchids ( serverid, mapname, sessionid ) VALUES( '$serverid','$map','$matches[0]' )");
					}
				}
				
					if ($retries > "9") {
						try {
							if ($settings['useemail']['config'] == 'yes') {
											$subject = "$servername seems to be back up after it was down for $retries, which is in minutes";
											$message = "Like the topic says, $servername seems to be back up after it was down for $retries";
											mail($settings['emailalert']['config'], $subject, $message, null);
							}
							if ($settings['usegrowl']['config'] == 'yes') {
								$growl = new Growl();
								//$growl->setAddress($growlip, $growlpass);
								$connection = array('address' => '$growlip', 'password' => '$growlpass');
								$growl->notify($connection, "$type", "RESTORED: $servername", "Instance $servername was down for $retries minutes. It is now back up again");
							}
							if ($settings['usetwitter']['config'] == 'yes') {
							$twitter->statusesUpdate("RESTORED: $servername. It was down for $retries minutes.");
							}
						}	catch(Exception $e) {}
					}
				// since we are in this loop the server has been reached so we can reset retry's back to 0.
				$retries = "0";
				
				//store match ID
				
				
				if ($gametypes[$type]['expired'] == "yes" ) {
					if (version_compare( $version, $gametypes[$type]['version'], '>' )) {
						// if something was expired, check to see if a server has a newer version. If yes update version in games db and set expired to no.
						mysql_query_trace("UPDATE games SET version='$version', expired='no' WHERE shortname='$type'") ;
						// reset so it wont go restart if valve has the coffee break.
						$gametypes[$type]['expired'] = "no";
					}
					if (version_compare( $version, $gametypes[$type]['version'], '=' )) {
						// if for some reason the gametype was changed but not the version then get out of that loop (rare condition, still on yes but already updated to both new version)
						echo "test";
					}
				}				

				if ($restartsend == 'yes' || $restartsend == 'restart'  || $restartsend == 'optional') {
					// this is set after a _restart, so if we see it , then server is restarted and need to set restartsend=no.
					if ($goingdown == 'no') { 
                    mysql_query_trace("UPDATE servers SET restartsend='no' WHERE serverid = '$serverid'") ;
					} else {
								if ( $type == "left4dead" || $type == "left4dead2" ) { 
									// hate to do this part, but if the last 'fork' is restarted, it can all be up within a minute. So the fork responded normally
									// while all the other ones get caught in the 'else' routine below if the servers does not respond, setting the flag that its part of a forked stop.
									$server->rconAuth($rconpass);
									$uptime = $server->rconExec('stats');
									$pieces = explode("\n", $uptime);
									$pieces[1] = trim($pieces[1]);
									$morepieces = preg_split('/[\s]+/', $pieces[1]);
									$uptime = $morepieces[3];

									echo "Hey we zijn er";
									echo "uptime is $uptime";
									// can be buggy too, if you shutdown it quickly again and then servers are still full, it would make them optional again and send them into download state
									// need a better way for this.
									if ( $uptime < "2") { 
                                        					// if its up this short it restarted shortly ago, so reset it for this one.
										echo "Resetting all forks to normal since 1 servers has low uptime";
										mysql_query_trace("UPDATE servers SET restartsend='no',goingdown='no' WHERE netconport = '$netconport'");
									}
								} 		
								if ( $restartsend == 'restart') {
									 mysql_query_trace("UPDATE servers SET restartsend='no' WHERE serverid = '$serverid'") ;
								}
					}
				}
				if ($autoupdate == 'yes' || $dlyrestart =='yes') {
                    if ($restartsend == 'update') {
						if (!$netconport) {
							// if restartsend is 'update' or that means something triggered it, meaning a _restart will be send
							try {
								// choose which command to run, was it a optional/normal update or a daily restart that triggered the update?
								$server->rconAuth($rconpass);
								if ( $cmdtosend == "daily" ) {
									$server->rconExec("$dlycmd");
								}	elseif ( $cmdtosend == "normal" ) { 
									// replace _restart by config for restart command.
									$server->rconExec('_restart');
								}
								// restarten bug?
								} catch (RCONNoAuthException $e) {
									trigger_error('Could not authenticate with the game server.',E_USER_ERROR);
								} catch(TimeoutException $e) {}
								catch(Exception $e) {}
								// trigger the optional restarts to show as normal restarts instead of downloading.
								if ($goingdown == "yes") { 
								// do the update here, since after a _restart it throws a exception and wont update the DB otherwise.
									mysql_query_trace("UPDATE servers SET restartsend='restart',goingdown='no',cmdtosend='normal' WHERE serverid = '$serverid'");
								} else {
									mysql_query_trace("UPDATE servers SET restartsend='yes',cmdtosend='normal' WHERE serverid = '$serverid'");
								}
							next;
						} else { 	
								if (!$netforkrestart[$netconport]=="yes") {
									$timeout = '2';
									$usenet = fsockopen($ip, $netconport, &$errno, &$errstr, $timeout);
									if(!$usenet) { 
                                        // to make sure they dont stay in "update" state, or we get 2x a restart of netcon!
                                        // pretty pointless in the end, since netcon port is down = all is down.
                                        // mysql_query_trace("UPDATE servers SET restartsend='optional' WHERE netconport = '$netconport'");
										next;
									} else {
										$netconding = $settings['netconrestart']['config'];
										fputs ($usenet, "PASS $netconpasswd\r\n");
										fputs ($usenet, "$netconding\r\n");
										mysql_query_trace("UPDATE servers SET restartsend='yes',goingdown='yes' WHERE netconport = '$netconport'");
										$netforkrestart[$netconport]="yes";
									}
								}
						}
					}
					// we got 3 routines down here
					// 1. expired = yes, this to 'issue' the first signal if a update came out so it goes into 'update' fase.
					//  and version < version in the db, this to update any servers that came up later.
					// 2. reset the expired = no for the gametype if the version of the server is HIGHER then the one in the db.
					// Routine 2 is needed also to reset the version number to the correct one for gametypes that do not require auto update.
					// extra part is done for netcon ports for l4d(2) to only issue 1 command which is shutdown or quit for ALL instances.
					// 3. is for daily restarts, also we need to have a "optional" restart, meaning it will restart when the server is empty or less then xx players

					 if ($gametypes[$type]['expired'] == "yes" ||  version_compare( $version, $gametypes[$type]['version'], '<' ) ) {
							// if a server is already updated and THEN the update comes out then it would still update it again, check for this
							if ( version_compare( $version, $gametypes[$type]['version'], '=' ) ) { next;} 
							if (!$netconport) {
							try {
                                 $server->rconAuth($rconpass);
                                 $server->rconExec($settings['defaultannounce']['config']);
								 echo 'fout update kwam uit';
							     mysql_query_trace("UPDATE servers SET restartsend='update' WHERE serverid = '$serverid' AND autoupdate = 'yes'");
                              } catch (RCONNoAuthException $e) {
                                    //trigger_error('Could not authenticate with the game server.',E_USER_ERROR);
									echo 'error kan niet rconnen boeien, verder gaan';
                              } catch(Exception $e) {}
							} else {
								// we have found a gametype l4d(2) which uses forks. Use the netcon port
								if (!$netforkupdate[$netconport]=="yes") {
									$timeout = '2';
									$usenet = fsockopen($ip, $port, &$errno, &$errstr, $timeout);
									if(!$usenet) { 
										next;
									} else {
										$announcing = $settings['defaultannounce']['config'];
										fputs ($usenet, "PASS $netconpasswd\r\n");
										fputs ($usenet, "$announcing\r\n");
										mysql_query_trace("UPDATE servers SET restartsend='update' WHERE serverid = '$serverid'");
										$netforkupdate[$neGotconport]="yes";
									}
								} else { // since a broadcast is send, all the other nodes dont need to have this send out again.
									mysql_query_trace("UPDATE servers SET restartsend='update' WHERE serverid = '$serverid'");
							    }
							}
						}
					}

				if ($dlyrestart == "yes" || $restartsend == "optional" || $restartsend == "emptyserver") {
				// the daily restart part, or the optional part. First get the time as we want it.
					$playercount = $nplayers - $bots;
					//echo "dus $playercount zoveel players \n"; (debug stuff)
				
					$hhmm = date('H:i', strtotime($dlytime)); 
					$currenthhmm = date('H:i');
					
					if ($hhmm == $currenthhmm) {
					//	try {
					//		$server->rconAuth($rconpass);
					//		$server->rconExec($dlycmd);
					//	} catch(Exception $e) {}
						mysql_query_trace("UPDATE servers SET restartsend='optional',goingdown='yes' WHERE serverid = '$serverid'");
					}
					if ($restartsend == "optional" && $goingdown == "yes" ) {
						// check number of players online, if less it meets min players then go
						if ($playercount <= $dlyusers || $dlyusers = "NULL") {
						echo "ja dat klopt, we zitten onder de 10";
						// add new field in db, to say it was a daily 
							// add this part to not make l4d2 forks in update mode.
							//if ($goingdown != 'yes' ) {
                                echo "set update en daily\n";
                                if (!$netconport) {
                                    mysql_query_trace("UPDATE servers SET restartsend='update', cmdtosend='daily' WHERE serverid = '$serverid'");
                                }
							//}
						}
					}
					if ($restartsend == "emptyserver") {
						// check number of players online, if less it meets min players then go
						if ($playercount == '0' ) {
						echo "ja dat klopt, we zitten onder de 10";
						// add new field in db, to say it was a daily 
						mysql_query_trace("UPDATE servers SET restartsend='update', cmdtosend='normal' WHERE serverid = '$serverid'");
						}
					}
				}
				
					// we are going to check for the daily time 
					
				mysql_query_trace("UPDATE servers SET servername = '$servername', type = '$type', version = '$version', network = '$network', os = '$os', lastupdate = NOW(), currentmap = '$map', currentplayers = '$nplayers', maxplayers = '$mplayers', retries = '$retries', currentbots = '$bots', protected = '$protected', servertags = '$servertags' WHERE serverid = '$serverid'");
			} else	{
				if ( $goingdown == 'yes' && $restartsend != 'emptyserver' ) { 
					mysql_query_trace("UPDATE servers SET restartsend='optional',goingdown='no' WHERE serverid = '$serverid'");
				}
				if ( $restartsend == 'no' ) {
					$fails[] = $serverid;
					if ( $retries == "10" ) {
					try {
						if ($settings['useemail']['config'] == 'yes') {
							$subject = "$servername seems to be down after 10 retries";
							$message = "Like the topic says, $servername @ $serverIP seems to be down for 10 retries so thats 10 minutes\n Last map it was on: $currentmap";
							mail($settings['emailalert']['config'], $subject, $message, null);
						}
						if ($settings['usegrowl']['config'] == 'yes') {
							$growl = new Growl();
							//$growl->setAddress($growlip, $growlpass);
							$connection = array('address' => '$growlip', 'password' => '$growlpass');
							$growl->notify($connection, "$type", "DOWN: $servername", "Instance $servername is down for $retries minutes. Please check");		
						}
						if ($settings['usetwitter']['config'] == 'yes') {
						$twitter->statusesUpdate("DOWN: $servername. It has been down for 10 minutes");
						}
					} catch (Exception $e) {}
					}
					if ( !$_GET['update'] == 'all') { mysql_query_trace( "UPDATE servers SET retries=retries+1  WHERE serverid = '$serverid'");}
					// so that web updates for all dont screw up the retry count. Assuming people run the php in cron.
				}  
			}
		}
		
		if( $cmd ) {
			//echo $servername;
			die();
			// commandline, no need for fancy stuff
		} else {
			echo "<script type=\"text/javascript\">
			\nwindow.onload = function() { ";
			
			if( mysql_num_rows( $result ) == 1 )
				if ( count( $fails ) )
					echo "alert( 'Updating failed, perhaps its a solar flare?\\n" . mysql_real_escape_string( mysql_error() ) . "' );";
				else
					echo "alert( 'Server \'" . mysql_real_escape_string( $servername ) . "\' was updated succesfully.' );";
			else
				echo "alert( 'All servers" . ( !empty( $fails ) ? ' but ID ' . implode( $fails, ', ' ) : '' ) . " were updated succesfully.' );";
				//echo "<p>All servers" . ( !empty( $fails ) ? " but ID " . implode( $fails, ', ' ) : '' ) . " were updated succesfully.</p>";
				//echo "<p>
				
			echo "\n}\n
			</script>";
		}
	}

    if (! isset( $_GET[ 'game' ] ) )
		$_GET[ 'game' ] = '%';

	if( isset( $args[ 'u' ] ) ) {
		checkversion();
		renewserver( "all", true );
	}

     if ( isset( $_GET[ 'renew' ] ) ) {
                checkversion();
                renewserver( "all", true );
        }
	
	if( isset( $_POST[ 'add' ] ) )
		addserver( $_POST[ 'ip' ], $_POST[ 'port' ], $_POST[ 'rcon' ] );
	
	if( isset( $_GET[ 'update' ] ) )
		renewserver( $_GET[ 'update' ] );
		
	if( isset( $_GET[ 'delete' ] ) )
		deleteserver( $_GET[ 'delete' ] );

	function setwindow($url , $title) {
		return "\"$( '#serverall' ).load('$url', function() { $( '#serverall' ).dialog('open')}); $( '#serverall' ).dialog('option','title','$title');\"";
		// example format, easier to reuse all dialogs.
		// setwindow($serverid, "serverconfig.php?serverid=$serverid", "Mooi nieuw titel");
	}
	if (!$ajax) {
?>
<div class="addServer" id="popup">
	<form method="post" action="?" >
		<div style="float: left;">
			IP<br/>
			Port<br/>
			Rcon
		</div>
		<div style="float: right;">
			<input type="text" name="ip" size="12" maxlength="15" value="255.255.255.255" /><br/>
			<input type="text" name="port" size="12" maxlength="5" value="27015" /><br/>
			<input type="text" name="rcon" size="12" maxlength="255" />
		</div>
		<br/><br/><br/><hr/>
			<input type="submit" value="Add Server" name="add" />&nbsp;
			<input type="button" value="Cancel" onclick="document.getElementById('popup').style.display = 'none';" />
	</form>
</div>
<script type="text/javascript">
$(function() {
	$( "#serverall" ).dialog({
	height: 'auto',
    width: 'auto',
    modal: true,
	autoOpen: false
	});
	var countdown = 60;
	var timer = setInterval(function() {
		if (--countdown == -1) {
			countdown = 60;
			$.get('servers.php', '', function(data) {
				$('#refreshcontent').html(data);
			});
		}
		$('#countdown').html(countdown);
	}, 1000);
});
</script>
		
<div class="serverall" id="serverall" title="Working.....">
</div>

<span style=" right: 20px; margin-bottom:10px; float:right;">

		Auto-Refresh active: <span id="countdown">60</span> seconds <img src="images/loading.gif" alt="Auto-Refresh" />
	<a onclick="document.getElementById('popup').style.display = 'block';" class="tooltiptext" title="Add new server" style="cursor:pointer;cursor:hand"><img src="images/addserver.png" alt="Add New Server" ></a>
    
    <? echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" style=\"cursor: help;\" onclick=" . setwindow("legenda.php","Help for icon legenda") . "><img src=\"images/legendahelpicon.png\" title=\"Legenda\" alt=\"Legenda\" style=\"padding-bottom:5px;\" ></a>"; ?>
</span>

<?php
}
echo '<div id="refreshcontent">';
echo "<a href=\"servers.php\">All Servers</a>";
foreach (array_keys($gametypes) as $shortname) {
	echo " - <a href=\"?game=$shortname\">" . $gametypes["$shortname"]['longname'] . "</a>";
	$count[$shortname]['count'] = '0';
}
echo "<br/><br/>";
		
	$strgame = $_GET[ 'game' ] != '%' ? 'game=' . $_GET[ 'game' ] . '&amp;' : '';
	$desc = !isset( $_GET[ 'desc' ] ) ? '&amp;desc' : '';
	$sort = isset( $_GET[ 'sort' ] ) ? $_GET[ 'sort' ] : 'servername';
	$data = array( "retries" => "Status", "type" => "Game", "os" => "OS", "serverid" => "ID", "servername" => "Name", "pwpro" => "", "currentmap" => "Current Map", "currentplayers" => "Players", "lastupdate" => "Last Update" );
	//$imgs = array( "tf" => "tf", "cstrike" => "css", "left4dead" => "l4d", "left4dead2" => "l4d2", "dayofdefeat" => "dod" );
	echo '';
	
	echo "<table class=\"listtable\" align=\"left\">\n";
	echo "<tr class=\"headers\">\n";
	foreach( $data as $key => $value ) {
		echo "	<td><a href=\"?" . $strgame . 'sort=' . $key . ( $sort == $key ? $desc : '' ) . "\">" . $value . "</a></td>\n";
	}
	echo "<td><a href=\"javascript:void(0);\" onclick=\"if( confirm('Are you sure you want to refresh all the servers?') ) document.location='?$strgame\update=all';\" >Quick Tools</a></td>\n";
	echo "</tr>\n";

	$result = mysql_query_trace( "SELECT * from servers where type like '" . $_GET[ 'game' ] . "' ORDER BY " . $sort . ( !empty( $desc ) ? '' : ' DESC' ) ) or die(mysql_error());
	$num_rows = mysql_num_rows($result);
	while($row = mysql_fetch_array( $result )) {
		foreach ($row as $key => $value) {
			$$key = $value;
		}

		if ( $retries == "0" ) { // if its 0, it could be up but not correct version, check it.
			if (version_compare( $version, $gametypes[$type]['version'], '<' )) { $color = "outofdate.png\" title=\"Server is out of date, UPDATE MANUALLY\" alt=\"Server is out of date, update Manually\"";
			}
			elseif (version_compare( $version, $gametypes[$type]['version'], '>' )) { $color = "sad.gif\" title=\"Valve coffee break.. master server version isn't updated yet!! We are running a newer version, which is ok!\"";
			}
		else { $color = "green.png\" title=\"All good!\" alt=\"All is ok\"";} 
		}
		if ( $retries > 3 ) {$color = "red.png\" title=\"Number of retries $retries\" alt=\"Number of retries $retries\"";}
		if ( $restartsend == "yes" ) {$color = "ani_install.gif\" title=\"Server is updating\" alt=\"Server is updating\"";}
		if ( $restartsend == "update" ) {$color = "waiting.png\" title=\"Server is waiting to update/restart\" alt=\"Server is waiting to update/restart\"";}
		if ( $restartsend == "restart" ) {$color = "ani_restart.gif\" title=\"Server is restarting\" alt=\"Server is restart\"";}

		if ( $restartsend == "optional" || $restartsend == "emptyserver" ) {
			if ( $goingdown == "yes" ) { $color = "pending.png\" title=\"Server is waiting to restart once players are finished\" alt=\"Server is waiting to restart once players are finished\""; }
			else { $color = "pending_down.png\" title=\"Server is waiting to get automatically restarted, it is down at the moment\" alt=\"Server is waiting to get automatically restarted, it is down at the moment\"";} 
		}
		if ( $retries > 0 && $retries < 4 )  { $color = "orange.png\" title=\"Server could be changing map, is not respong for a few counts\"";}
		echo "	<tr class=\"elements\">\n   <td align=\"center\"><img src=\"images/" . $color  . " width=\"22\" height=\"22\"/></td>\n";
		echo "  <td align=\"center\"><img src=\"images/" . $type . ".png\" alt=\"imgs type\"  /></td>\n";
		// echo "  <td align=\"center\"><img src=\"images/" . $imgs[ $type ] . ".png\" alt=\"imgs type\"  /></td>\n";
		echo "	<td align=\"center\"><img src=\"images/" . $os . ".png\" alt=\"image os\" /></td>\n";
		echo "	<td>$serverid</td>\n";
		$servername = htmlspecialchars($servername);
		echo "	<td width=\"100%\" overflow=\"scroll\"><a href=\"steam://connect/$ip:$port\" title=\"$ip:$port\">$servername</a>";
if (preg_match("/_registered/i","$servertags")) { echo '<a class="tooltip" title="This server is registered"><img align=right src=images/registered.png alt="registered">';}  
echo "</td>";
		
		echo " <td align=\"center\" width=\"15\" nowrap=\"nowrap\">";
		// Is Server Password Protected - sleutel = key
		if ( $protected == "1") { echo '<a class="tooltip" title="This Server is Password Protected"><img src="images/sleutel.gif" alt="protected" /></a>'; }
		echo "</td>\n";
		
		if ($type == "left4dead" || $type == "left4dead2" ){ 
		echo "  <td>$currentmap</td>\n";
		} else {
		echo "	<td><a href=\"javascript:void(0);\" onclick=" . setwindow("maps.php?serverid=$serverid","Map information for $servername") . ">$currentmap</a></td>\n";
		
		}
			
                $playersColor = '#f60';
		if ($currentplayers / $maxplayers > 0.65) {
			$playersColor = '#390';
		} elseif ($currentplayers / $maxplayers < 0.15) {
			$playersColor = '#f00';
		}
                
		echo "  <td width=\"60\" nowrap=\"nowrap\"><a style=\"cursor:pointer;cursor:hand;color:$playersColor\" class=\"tooltiptext\" onclick=" . setwindow("players.php?serverid=$serverid","$currentplayers players/bots active on $servername") . 
		"title=\"$currentplayers players of the maximum $maxplayers are online";
		if ( $currentbots > 0 ) { echo " of which $currentbots are bot(s)"; } 
		echo "\">$currentplayers";
		if ( $currentbots > 0 ) { 
		echo "<font color=\"red\">-$currentbots</font>"; }
		echo " / $maxplayers</a></td>\n";
		
		echo "	<td width=\"140\" nowrap=\"nowrap\">$lastupdate</td>\n";
		
		echo "	<td id=\quicktools\" width=\"140\ style=\"white-space: nowrap; width:130px;\" nowrap=\"nowrap\">
		<a class=\"tooltiptext\" title=\"Restart Options\"><img class=\"link\" src=\"images/restart.png\"  alt=\"Restart\" width=\"18\" height=\"18\" onclick=" . setwindow("singlerestart.php?serverid=$serverid", "Restart $servername") . " /></a>
		<a class=\"tooltiptext\" title=\"Refresh Server \"><img class=\"link\" src=\"images/update.gif\" alt=\"Update\" onclick=\"if( confirm('Are you sure you want to update server \\'$servername\\'?') ) document.location='?update=$serverid'\"/>";
		$restarts = $config['showrestarts']['config'];
		if ($restarts == "yes") {
		echo "<a class=\"tooltiptext\" title=\"View restarts\"><img class=\"link\" src=\"images/list-error.png\" onclick=\"document.location='restarts.php?serverid=$serverid'\" alt=\"List Error\" />";
		} 
		echo "<a class=\"tooltiptext\" title=\"RCON\"><img class=\"link\" src=\"images/cmd.png\" alt=\"cmd\" onclick=\"document.location='rcon.php?serverid=$serverid'\" />
		<a class=\"tooltiptext\" title=\"Delete\"><img class=\"link\" src=\"images/delete.gif\" alt=\"Delete\" onclick=\"if( confirm('Are you sure you want to remove server \\'$servername\\' from the database? This will also remove all the links to the plugins on this server.') )document.location='?delete=$serverid'\"/>
		<a class=\"tooltiptext\" title=\"Settings\"><img class=\"link\" src=\"images/settings.png\" alt=\"Settings\" onclick=" . setwindow("serverconfig.php?serverid=$serverid", "Changing Settings for $servername") . " />
		</td>\n
		</tr>\n";
		$count[$type]['count']++;
	}
	echo "</table>\n";
	
	echo "<br/>&nbsp;<br/><center><b>Server Totals</b> ";
	$total = '0';
	foreach (array_keys($count) as $type) {
		echo " &#149; " . $gametypes["$type"]['longname'] . ": " . $count[$type]['count'];
		$total = $total + $count[$type]['count'];
	}
	echo " &#187; <b>In total $total Server(s)!</b></center>";
	echo '</div>';
	mysql_close();
	if (!$ajax)
		bottom( $start );
	
?>
